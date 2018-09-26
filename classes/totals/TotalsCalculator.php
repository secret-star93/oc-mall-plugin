<?php

namespace OFFLINE\Mall\Classes\Totals;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Cart\DiscountApplier;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Tax;

class TotalsCalculator
{
    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var Collection<TaxTotal>
     */
    protected $taxes;
    /**
     * @var Collection<TaxTotal>
     */
    protected $detailedTaxes;
    /**
     * @var ShippingTotal
     */
    protected $shippingTotal;
    /**
     * @var int
     */
    protected $weightTotal;
    /**
     * @var int
     */
    protected $totalPreTaxes;
    /**
     * @var int
     */
    protected $totalPostTaxes;
    /**
     * @var int
     */
    protected $totalTaxes;
    /**
     * @var int
     */
    protected $totalDiscounts;
    /**
     * @var int
     */
    protected $productPreTaxes;
    /**
     * @var int
     */
    protected $productTaxes;
    /**
     * @var int
     */
    protected $productPostTaxes;
    /**
     * @var Collection
     */
    protected $appliedDiscounts;

    public function __construct(Cart $cart)
    {
        $this->cart  = $cart->load(
            'products',
            'products.data.taxes',
            'shipping_method',
            'shipping_method.taxes',
            'shipping_method.rates',
            'discounts'
        );
        $this->taxes = new Collection();

        $this->calculate();
    }

    protected function calculate()
    {
        $this->weightTotal      = $this->calculateWeightTotal();
        $this->productPreTaxes  = $this->calculateProductPreTaxes();
        $this->productTaxes     = $this->calculateProductTaxes();
        $this->productPostTaxes = $this->productPreTaxes + $this->productTaxes;

        $this->shippingTotal = new ShippingTotal($this->cart->shipping_method, $this);
        $this->totalPreTaxes = $this->productPreTaxes + $this->shippingTotal->totalPreTaxes();

        $this->taxes = $this->getTaxTotals();

        $this->totalDiscounts = $this->productPostTaxes - $this->applyTotalDiscounts($this->productPostTaxes);

        $this->totalPostTaxes = $this->productPostTaxes - $this->totalDiscounts + $this->shippingTotal->totalPostTaxes();
    }

    protected function calculateProductPreTaxes(): float
    {
        $total = $this->cart->products->sum('totalPreTaxes');

        return $total > 0 ? $total : 0;
    }

    protected function calculateProductTaxes(): float
    {
        return $this->cart->products->sum('totalTaxes');
    }

    protected function getTaxTotals(): Collection
    {
        $shippingTaxes = new Collection();
        $shippingTotal = $this->shippingTotal->totalPreTaxesOriginal();
        if ($this->cart->shipping_method) {
            $shippingTaxes = optional($this->cart->shipping_method)->taxes
                ->map(function (Tax $tax) use ($shippingTotal) {
                    return new TaxTotal($shippingTotal, $tax);
                });
        }

        $productTaxes = $this->cart->products->flatMap(function (CartProduct $product) {
            return $product->data->taxes->map(function (Tax $tax) use ($product) {
                return new TaxTotal($product->totalPreTaxes, $tax);
            });
        });

        $combined = $productTaxes->concat($shippingTaxes);

        $this->totalTaxes = $combined->sum(function (TaxTotal $tax) {
            return $tax->total();
        });

        $this->detailedTaxes = $combined;

        return $this->consolidateTaxes($combined);
    }

    /**
     * This method consolidates the same taxes on shipping
     * and products down to one combined TaxTotal.
     */
    protected function consolidateTaxes(Collection $taxTotals)
    {
        return $taxTotals->groupBy(function (TaxTotal $taxTotal) {
            return $taxTotal->tax->id;
        })->map(function (Collection $grouped) {
            $tax    = $grouped->first()->tax;
            $preTax = $grouped->sum(function (TaxTotal $tax) {
                return $tax->preTax();
            });

            return new TaxTotal($preTax, $tax);
        })->values();
    }

    protected function calculateWeightTotal(): int
    {
        return $this->cart->products->sum(function (CartProduct $product) {
            return $product->weight * $product->quantity;
        });
    }

    public function shippingTotal(): ShippingTotal
    {
        return $this->shippingTotal;
    }

    public function weightTotal(): int
    {
        return $this->weightTotal;
    }

    public function totalPreTaxes(): float
    {
        return $this->totalPreTaxes;
    }

    public function totalTaxes(): float
    {
        return $this->totalTaxes;
    }

    public function productPreTaxes(): float
    {
        return $this->productPreTaxes;
    }

    public function productTaxes(): float
    {
        return $this->productTaxes;
    }

    public function productPostTaxes(): float
    {
        return $this->productPostTaxes;
    }

    public function totalPostTaxes(): float
    {
        return $this->totalPostTaxes;
    }

    public function taxes(bool $detailed = false): Collection
    {
        return $detailed ? $this->detailedTaxes : $this->taxes;
    }

    public function detailedTaxes(): Collection
    {
        return $this->taxes(true);
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function appliedDiscounts(): Collection
    {
        return $this->appliedDiscounts;
    }

    /**
     * Process the discounts that are applied to the cart's total.
     */
    protected function applyTotalDiscounts($total): ?float
    {
        $nonCodeTriggers = Discount::whereIn('trigger', ['total', 'product'])
                                   ->where(function ($q) {
                                       $q->whereNull('expires')
                                         ->orWhere('expires', '>', Carbon::now());
                                   })->get();

        $discounts = $this->cart->discounts->merge($nonCodeTriggers)->reject(function ($item) {
            return $item->type === 'shipping';
        });

        $applier                = new DiscountApplier($this->cart, $total);
        $this->appliedDiscounts = $applier->applyMany($discounts);

        return $applier->reducedTotal();
    }
}
