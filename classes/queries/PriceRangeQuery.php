<?php

namespace OFFLINE\Mall\Classes\Queries;

use DB;
use October\Rain\Database\QueryBuilder;
use OFFLINE\Mall\Models\Currency;

/**
 * This query fetches the max and min prices of a category.
 */
class PriceRangeQuery
{
    /**
     * The currently active Currency.
     *
     * @var Currency
     */
    protected $currency;
    /**
     * Categories to filter by.
     *
     * @var array
     */
    protected $categories;

    public function __construct(array $categories, Currency $currency)
    {
        $this->currency   = $currency;
        $this->categories = $categories;
    }

    /**
     * Return the query to filter the max and min price values.
     *
     * @return QueryBuilder
     */
    public function query()
    {
        return DB
            ::table('offline_mall_product_prices')
            ->selectRaw(DB::raw('min(price) as min, max(price) as max'))
            ->join(
                'offline_mall_products',
                'offline_mall_product_prices.product_id',
                '=',
                'offline_mall_products.id'
            )
            ->whereIn('offline_mall_products.category_id', $this->categories)
            ->where('offline_mall_product_prices.currency_id', $this->currency->id);
    }
}
