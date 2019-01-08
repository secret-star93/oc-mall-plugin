<?php

namespace OFFLINE\Mall\Classes\Traits;

use Closure;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Variant;

trait NullPrice
{
    protected $defaultCurrency = null;

    protected function nullPrice(
        $currency,
        $related,
        string $relation = 'prices',
        ?Closure $filter = null
    ) {
        $price   = null;
        $default = $this->getDefaultCurrency();

        $model = new Price();

        // Add missing prices only when running the frontend.
        if (! app()->runningInBackend() && ! app()->runningInConsole()) {
            $base = $related->where('currency_id', $default->id)->first();
            if ($base !== null) {
                $price                = (int)($base->price * $currency->rate);
                $model->autoGenerated = true;
            } elseif ($this instanceof Variant) {
                // Variants can inherit their product's pricing information.
                return $this->product->price($currency, $relation, $filter);
            }
        }

        return $model->setRawAttributes([
            'price'       => $price,
            'currency_id' => optional($currency)->id ?? Currency::activeCurrency()->id,
        ], true);
    }

    protected function getDefaultCurrency()
    {
        if ( ! $this->defaultCurrency) {
            $this->defaultCurrency = Currency::defaultCurrency();
        }

        return $this->defaultCurrency;
    }
}
