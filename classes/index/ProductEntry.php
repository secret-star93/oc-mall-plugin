<?php

namespace OFFLINE\Mall\Classes\Index;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\Product;

class ProductEntry implements Entry
{
    const INDEX = 'products';

    protected $product;
    protected $data;

    public function __construct(Product $product)
    {
        $this->product = $product;

        // Make sure variants inherit product data again.
        session()->forget('mall.variants.disable-inheritance');

        $product->loadMissing(['brand', 'variants.prices.currency', 'prices.currency', 'property_values.property']);

        $data          = $product->attributesToArray();
        $data['index'] = self::INDEX;

        $data['property_values'] = $this->mapProps($product->property_values);
        $data['prices']          = $this->mapPrices($product);
        $data['customer_group_prices'] = $this->mapCustomerGroupPrices($product);
        if ($product->brand) {
            $data['brand'] = ['id' => $product->brand->id, 'slug' => $product->brand->slug];
        }

        $data['sort_orders'] = $product->getSortOrders();

        $this->data = $data;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function withData(array $data): Entry
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    protected function mapPrices(Product $product): Collection
    {
        return $product->prices->mapWithKeys(function($price)  {
            return [$price->currency->code => $price->integer];
        });
    }

    protected function mapCustomerGroupPrices($model): Collection
    {
        return CustomerGroup::get()->mapWithKeys(function ($group) use ($model) {
            return [
                $group->id => Currency::getAll()->mapWithKeys(function ($currency) use ($model, $group) {
                    $price = $model->groupPrice($group, $currency);
                    if ($price) {
                        return [$price->currency->code => $price->integer];
                    }

                    return null;
                })->filter(),
            ];
        });
    }

    protected function mapProps(?Collection $input): Collection
    {
        if ($input === null) {
            return collect();
        }

        return $input->groupBy('property_id')->map(function ($value) {
            return $value->pluck('index_value')->unique()->filter()->values();
        })->filter();
    }
}
