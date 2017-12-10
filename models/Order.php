<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\OrderStatus\InProgressState;
use OFFLINE\Mall\Classes\PaymentStatus\PendingState;
use OFFLINE\Mall\Classes\Traits\Price;

/**
 * Model
 */
class Order extends Model
{
    use Validation;
    use SoftDelete;
    use Price;

    protected $dates = ['deleted_at'];

    public $rules = [
        'currency'                         => 'required',
        'shipping_address_same_as_billing' => 'required|boolean',
        'billing_address'                  => 'required',
        'lang'                             => 'required',
        'ip_address'                       => 'required',
        'customer_id'                      => 'required|exists:offline_mall_customers,id',
    ];

    public $jsonable = ['billing_address', 'shipping_address', 'custom_fields', 'taxes', 'discounts', 'shipping'];

    public $table = 'offline_mall_orders';

    public $hasMany = [
        'products' => OrderProduct::class,
    ];

    public $casts = [
        'shipping_address_same_as_billing' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $order) {
            if ( ! $order->order_number) {
                $order->setOrderNumber();
            }
        });
    }

    public static function fromCart(Cart $cart): self
    {
        $order                                   = new static;
        $order->currency                         = 'CHF';
        $order->lang                             = 'de';
        $order->shipping_address_same_as_billing = $cart->shipping_address_same_as_billing;
        $order->billing_address                  = $cart->billing_address;
        $order->shipping_address                 = $cart->shipping_address;
        $order->shipping                         = $cart->shipping_method;
        $order->taxes                            = $cart->totals->taxes();
        $order->discounts                        = $cart->discounts;
        $order->ip_address                       = request()->ip();
        $order->customer_id                      = 1;
        $order->payment_status                   = PendingState::class;
        $order->order_status                     = InProgressState::class;
        $order->shipping_pre_taxes               = $cart->totals->shippingTotal()->total();
        $order->shipping_taxes                   = $cart->totals->shippingTotal()->taxes();
        $order->total_shipping                   = $cart->totals->shippingTotal()->price();
        $order->product_taxes                    = $cart->totals->productTaxes();
        $order->total_product                    = $cart->totals->productTotal();
        $order->total_pre_taxes                  = $cart->totals->totalPreTaxes();
        $order->total_taxes                      = $cart->totals->totalTaxes();
        $order->total_post_taxes                 = $cart->totals->totalPostTaxes();
        $order->total_weight                     = $cart->totals->weightTotal();

        $cart->delete(); // We can empty the cart once the order is created.

        return $order;
    }

    /**
     * Sets the order number to the next higher value.
     */
    protected function setOrderNumber()
    {
        $start = self::max('order_number');
        if ($start === 0) {
            $start = 0; // @TODO: Add custom starting point for numbers
        }

        $this->order_number = (int)$start + 1;
    }

    public function getPriceColumns(): array
    {
        return [
            'total_pre_taxes',
            'total_post_taxes',
            'total_product',
            'total_taxes',
            'total_shipping',
        ];
    }
}
