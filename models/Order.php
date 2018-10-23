<?php namespace OFFLINE\Mall\Models;

use DB;
use Event;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Classes\PaymentState\PendingState;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\JsonPrice;
use OFFLINE\Mall\Classes\Utils\Money;
use RuntimeException;
use System\Classes\PluginManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Order extends Model
{
    use Validation;
    use SoftDelete;
    use JsonPrice {
        useCurrency as fallbackCurrency;
    }
    use HashIds;

    protected $dates = ['deleted_at', 'shipped_at'];
    public $rules = [
        'currency'                         => 'required',
        'shipping_address_same_as_billing' => 'required|boolean',
        'billing_address'                  => 'required',
        'lang'                             => 'required',
        'ip_address'                       => 'required',
        'customer_id'                      => 'required|exists:offline_mall_customers,id',
    ];
    public $jsonable = [
        'billing_address',
        'shipping_address',
        'custom_fields',
        'taxes',
        'payment',
        'currency',
        'discounts',
        'shipping',
        'payment_data',
    ];
    public $table = 'offline_mall_orders';
    public $hasOne = ['payment_log' => PaymentLog::class];
    public $hasMany = [
        'products'     => OrderProduct::class,
        'payment_logs' => [PaymentLog::class, 'order' => 'created_at DESC'],
    ];
    public $belongsTo = [
        'payment_method' => [PaymentMethod::class, 'deleted' => true],
        'order_state'    => [OrderState::class, 'deleted' => true],
        'customer'       => [Customer::class, 'deleted' => true],
        'cart'           => [Cart::class, 'deleted' => true],
    ];
    public $casts = [
        'shipping_address_same_as_billing' => 'boolean',
    ];
    /**
     * Use to define if the shipping notification should be sent.
     * @var bool
     */
    public $shippingNotification = false;

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $order) {
            if ( ! $order->order_number) {
                $order->setOrderNumber();
            }
        });
        static::updated(function (self $order) {
            if ($order->isDirty('order_state_id')) {
                Event::fire('mall.order.state.changed', [$order]);
            }
            if ($order->isDirty('tracking_url') || $order->isDirty('tracking_number')) {
                Event::fire('mall.order.tracking.changed', [$order]);
            }
            if ($order->isDirty('payment_state') && $order->wasRecentlyCreated === false) {
                Event::fire('mall.order.payment_state.changed', [$order]);
            }
            if ($order->getOriginal('shipped_at') === null && $order->isDirty('shipped_at')) {
                Event::fire('mall.order.shipped', [$order]);
            }
        });
    }

    public function getIsShippedAttribute()
    {
        return $this->shipped_at !== null;
    }

    public static function byCustomer(Customer $customer)
    {
        return static::where('customer_id', $customer->id);
    }

    public static function fromCart(Cart $cart): self
    {
        $order = DB::transaction(function () use ($cart) {

            $initialOrderStatus = OrderState::where('flag', OrderState::FLAG_NEW)->first();
            if ( ! $initialOrderStatus) {
                throw new RuntimeException('You have to create an order state with the "new" flag before accepting orders!');
            }

            if ($cart->products->count() < 1) {
                throw new ValidationException(['Your order is empty. Please add a product to the cart.']);
            }

            $totals = $cart->totals;

            $order                                          = new static;
            $order->session_id                              = session()->getId();
            $order->currency                                = Currency::activeCurrency();
            $order->lang                                    = $order->getLocale();
            $order->shipping_address_same_as_billing        = $cart->shipping_address_same_as_billing;
            $order->billing_address                         = $cart->billing_address;
            $order->shipping_address                        = $cart->shipping_address;
            $order->shipping                                = $totals->shippingTotal();
            $order->payment                                 = $totals->paymentTotal();
            $order->taxes                                   = $totals->taxes();
            $order->discounts                               = $totals->appliedDiscounts();
            $order->ip_address                              = request()->ip();
            $order->customer_id                             = $cart->customer->id;
            $order->payment_method_id                       = $cart->payment_method_id;
            $order->payment_state                           = PendingState::class;
            $order->order_state_id                          = $initialOrderStatus->id;
            $order->attributes['total_shipping_pre_taxes']  = $order->round($totals->shippingTotal()->totalPreTaxes());
            $order->attributes['total_shipping_taxes']      = $order->round($totals->shippingTotal()->totalTaxes());
            $order->attributes['total_shipping_post_taxes'] = $order->round($totals->shippingTotal()->totalPostTaxes());
            $order->attributes['total_payment_pre_taxes']   = $order->round($totals->paymentTotal()->totalPreTaxes());
            $order->attributes['total_payment_taxes']       = $order->round($totals->paymentTotal()->totalTaxes());
            $order->attributes['total_payment_post_taxes']  = $order->round($totals->paymentTotal()->totalPostTaxes());
            $order->attributes['total_product_pre_taxes']   = $order->round($totals->productPreTaxes());
            $order->attributes['total_product_taxes']       = $order->round($totals->productTaxes());
            $order->attributes['total_product_post_taxes']  = $order->round($totals->productPostTaxes());
            $order->attributes['total_pre_payment']         = $order->round($totals->totalPrePayment());
            $order->attributes['total_pre_taxes']           = $order->round($totals->totalPreTaxes());
            $order->attributes['total_taxes']               = $order->round($totals->totalTaxes());
            $order->attributes['total_post_taxes']          = $order->round($totals->totalPostTaxes());
            $order->total_weight                            = $order->round($totals->weightTotal());
            $order->save();

            $cart->products->each(function (CartProduct $entry) use ($order) {
                $entry->moveToOrder($order);
            });

            $cart->updateDiscountUsageCount();

            $cart->delete(); // We can empty the cart once the order is created.

            return $order;
        });

        // Drop any saved payment information since the order has been
        // created successfully.
        session()->forget('mall.payment_method.data');

        Event::fire('mall.order.created', [$order]);

        return $order;
    }

    /**
     * This is here to provide custom rounding options for the
     * end-user in future versions (like round to .05)
     */
    protected function round($amount)
    {
        return round($amount);
    }

    /**
     * Sets the order number to the next higher value.
     */
    protected function setOrderNumber()
    {
        $numbers = DB::table($this->getTable())->selectRaw('max(cast(order_number as unsigned)) as max')->first();
        $start   = $numbers->max;

        if ($start === 0) {
            $start = (int)GeneralSettings::get('order_start');
        }

        $this->order_number = $start + 1;
    }

    public function getPriceColumns(): array
    {
        return [
            'total_shipping_pre_taxes',
            'total_shipping_taxes',
            'total_shipping_post_taxes',
            'total_payment_pre_taxes',
            'total_payment_taxes',
            'total_payment_post_taxes',
            'total_product_pre_taxes',
            'total_product_taxes',
            'total_product_post_taxes',
            'total_taxes',
            'total_post_taxes',
            'total_pre_taxes',
        ];
    }

    protected function useCurrency()
    {
        if ($this->currency) {
            return new Currency($this->currency);
        }

        return $this->fallbackCurrency();
    }

    /**
     * Returns the amount of the order in the selected currency.
     * This is used in the PaymentProvider classes.
     *
     * @return float
     */
    public function getTotalInCurrencyAttribute()
    {
        $total = (int)$this->getOriginal('total_post_taxes');
        $total *= (float)$this->currency['rate'];

        return app(Money::class)->round($total, $this->currency['decimals']);
    }

    public function getPaymentStateLabelAttribute()
    {
        return $this->payment_state::label();
    }

    public function getOrderStateLabelAttribute()
    {
        return $this->order_state->name;
    }

    public function getShippingAddressStringAttribute()
    {
        return implode("\n", $this->shipping_address);
    }

    public function getIsPaidAttribute()
    {
        return $this->payment_state === PaidState::class;
    }

    public function totalPreTaxes()
    {
        return $this->toPriceModel('total_pre_taxes');
    }

    public function totalTaxes()
    {
        return $this->toPriceModel('total_taxes');
    }

    public function totalPostTaxes()
    {
        return $this->toPriceModel('total_post_taxes');
    }

    public function totalProductPreTaxes()
    {
        return $this->toPriceModel('total_product_pre_taxes');
    }

    public function totalProductTaxes()
    {
        return $this->toPriceModel('total_product_taxes');
    }

    public function totalProductPostTaxes()
    {
        return $this->toPriceModel('total_product_post_taxes');
    }

    public function totalShippingPreTaxes()
    {
        return $this->toPriceModel('total_shipping_pre_taxes');
    }

    public function totalShippingTaxes()
    {
        return $this->toPriceModel('total_shipping_taxes');
    }

    public function totalShippingPostTaxes()
    {
        return $this->toPriceModel('total_shipping_post_taxes');
    }

    public function totalPaymentPreTaxes()
    {
        return $this->toPriceModel('total_payment_pre_taxes');
    }

    public function totalPaymentTaxes()
    {
        return $this->toPriceModel('total_payment_taxes');
    }

    public function totalPaymentPostTaxes()
    {
        return $this->toPriceModel('total_payment_post_taxes');
    }

    protected function toPriceModel(string $key): Price
    {
        return new Price([
            'currency_id' => $this->useCurrency()->id,
            'price'       => $this->getOriginal($key) / 100,
        ]);
    }

    protected function getLocale()
    {
        if (PluginManager::instance()->exists('RainLab.Translate')) {
            return \RainLab\Translate\Classes\Translator::instance()->getLocale();
        }

        return 'default';
    }
}
