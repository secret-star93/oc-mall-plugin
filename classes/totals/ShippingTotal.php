<?php

namespace OFFLINE\Mall\Classes\Totals;

use Carbon\Carbon;
use OFFLINE\Mall\Classes\Cart\DiscountApplier;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Tax;

class ShippingTotal implements \JsonSerializable
{
    /**
     * @var TotalsCalculator
     */
    private $totals;
    /**
     * @var ShippingMethod
     */
    private $method;
    /**
     * @var int
     */
    private $preTaxes;
    /**
     * @var int
     */
    private $total;
    /**
     * @var int
     */
    private $taxes;
    /**
     * @var int
     */
    private $appliedDiscount;

    public function __construct(?ShippingMethod $method, TotalsCalculator $totals)
    {
        $this->method = $method;
        $this->totals = $totals;

        $this->calculate();
    }

    protected function calculate()
    {
        $this->total    = $this->calculateTotal();
        $this->taxes    = $this->calculateTaxes();
        $this->preTaxes = $this->calculatePreTax();
    }

    protected function calculatePreTax()
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->total;

        return $price - $this->taxes;
    }

    protected function calculateTaxes(): float
    {
        if ( ! $this->method) {
            return 0;
        }

        $price = $this->total;

        $totalTaxPercentage = $this->method->taxes->sum('percentageDecimal');

        return $this->method->taxes->reduce(function ($total, Tax $tax) use ($price, $totalTaxPercentage) {
            return $total += $price / (1 + $totalTaxPercentage) * $tax->percentageDecimal;
        }, 0);
    }

    protected function calculateTotal(): float
    {
        if ( ! $this->method) {
            return 0;
        }

        $method = $this->method;
        $price  = $method->priceInCurrencyInteger();

        // If there are special rates let's see if they
        // need to be applied.
        if ($method->rates->count() > 0) {
            $weight = $this->totals->weightTotal();

            $matchingRate = $method->rates->first(function (ShippingMethodRate $rate) use ($weight) {
                $compareFrom = $rate->from_weight === null || $rate->from_weight <= $weight;
                $compareTo   = $rate->to_weight === null || $rate->to_weight >= $weight;

                return $compareFrom && $compareTo;
            });

            if ($matchingRate) {
                $price = $matchingRate->priceInCurrencyInteger();
            }
        }

        $price = $this->applyDiscounts($price);

        return $price > 0 ? $price : 0;
    }

    public function totalPreTaxes(): float
    {
        return $this->preTaxes;
    }

    public function totalPreTaxesOriginal(): float
    {
        return $this->preTaxes;
    }

    public function totalTaxes(): float
    {
        return $this->taxes;
    }

    public function totalPostTaxes(): float
    {
        return $this->total;
    }

    public function appliedDiscount()
    {
        return $this->appliedDiscount;
    }

    /**
     * Get the effective ShippingMethod including changes
     * made by any applied discounts.
     *
     * @return ShippingMethod
     */
    public function method(): ShippingMethod
    {
        if ( ! $this->appliedDiscount) {
            return $this->method;
        }

        $method = $this->method->replicate(['id', 'name', 'price']);

        $discount     = $this->appliedDiscount['discount'];
        $method->name = $discount->shipping_description;
        $method->setRelation('prices', $discount->shipping_price);

        return $method;
    }

    private function applyDiscounts(int $price): ?float
    {
        $discounts = Discount::whereIn('trigger', ['total', 'product'])
                             ->where('type', 'shipping')
                             ->where(function ($q) {
                                 $q->whereNull('expires')
                                   ->orWhere('expires', '>', Carbon::now());
                             })->get();

        $codeDiscount = $this->totals->getCart()->discounts->where('type', 'shipping')->first();
        if ($codeDiscount) {
            $discounts->push($codeDiscount);
        }

        if ($discounts->count() < 1) {
            return $price;
        }

        $applier = new DiscountApplier(
            $this->totals->getCart(),
            $this->totals->productPostTaxes(),
            $price
        );

        $this->appliedDiscount = optional($applier->applyMany($discounts))->first();

        return $applier->reducedTotal();
    }

    public function jsonSerialize()
    {
        return [
            'method'          => $this->method(),
            'preTaxes'        => $this->preTaxes,
            'taxes'           => $this->taxes,
            'total'           => $this->total,
            'appliedDiscount' => $this->appliedDiscount,
        ];
    }
}
