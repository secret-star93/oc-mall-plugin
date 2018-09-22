<?php namespace OFFLINE\Mall\Models;

use DB;
use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use OFFLINE\Mall\Classes\Traits\StockAndQuantity;
use OFFLINE\Mall\Classes\Traits\UserSpecificPrice;
use System\Models\File;

class Product extends Model
{
    use Validation;
    use SoftDelete;
    use Sluggable;
    use UserSpecificPrice;
    use Images;
    use CustomFields;
    use HashIds;
    use Nullable;
    use PriceAccessors;
    use StockAndQuantity;

    const MORPH_KEY = 'mall.product';

    protected $dates = ['deleted_at'];
    public $jsonable = ['links'];
    public $nullable = ['stock', 'group_by_property_id'];
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        ['slug', 'index' => true],
        'description_short',
        'description',
        'meta_title',
        'meta_description',
    ];
    public $slugs = [
        'slug' => 'name',
    ];
    public $rules = [
        'name'   => 'required',
        'slug'   => ['regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
        'weight' => 'integer|nullable',
    ];
    public $casts = [
        'price_includes_tax'           => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'weight'                       => 'integer',
        'id'                           => 'integer',
        'stackable'                    => 'boolean',
        'stock'                        => 'integer',
        'sales_count'                  => 'integer',
        'shippable'                    => 'boolean',
    ];
    public $fillable = [
        'category_id',
        'brand_id',
        'user_defined_id',
        'name',
        'slug',
        'description_short',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'weight',
        'inventory_management_method',
        'quantity_default',
        'quantity_min',
        'quantity_max',
        'stock',
        'allow_out_of_stock_purchases',
        'links',
        'stackable',
        'shippable',
        'price_includes_tax',
        'group_by_property_id',
        'published',
    ];
    public $table = 'offline_mall_products';
    public $with = ['image_sets', 'prices'];
    public $attachMany = [
        'downloads' => File::class,
    ];
    public $belongsTo = [
        'category'          => Category::class,
        'brand'             => Brand::class,
        'group_by_property' => [
            Property::class,
            'key' => 'group_by_property_id',
        ],
    ];
    public $hasManyThrough = [
        'custom_field_options' => [
            CustomFieldOption::class,
            'key'        => 'product_id',
            'through'    => Variant::class,
            'throughKey' => 'custom_field_id',
        ],
    ];
    public $morphMany = [
        'customer_group_prices' => [CustomerGroupPrice::class, 'name' => 'priceable'],
        'additional_prices'     => [Price::class, 'name' => 'priceable'],
    ];
    public $hasMany = [
        'prices'          => [ProductPrice::class, 'conditions' => 'variant_id is null'],
        'variants'        => Variant::class,
        'cart_products'   => CartProduct::class,
        'image_sets'      => ImageSet::class,
        'property_values' => PropertyValue::class,
    ];
    public $belongsToMany = [
        'custom_fields'   => [
            CustomField::class,
            'table'    => 'offline_mall_product_custom_field',
            'key'      => 'product_id',
            'otherKey' => 'custom_field_id',
        ],
        'accessories'     => [
            Product::class,
            'table'      => 'offline_mall_product_accessory',
            'key'        => 'accessory_id',
            'otherKey'   => 'product_id',
            'conditions' => 'published = 1',
        ],
        'is_accessory_of' => [
            Product::class,
            'table'      => 'offline_mall_product_accessory',
            'key'        => 'product_id',
            'otherKey'   => 'accessory_id',
            'conditions' => 'published = 1',
        ],
        'taxes'           => [
            Tax::class,
            'table'    => 'offline_mall_product_tax',
            'key'      => 'product_id',
            'otherKey' => 'tax_id',
        ],
        'carts'           => [
            Cart::class,
            'table'      => 'offline_mall_cart_products',
            'key'        => 'product_id',
            'otherKey'   => 'cart_id',
            'deleted'    => true,
            'pivot'      => ['id', 'quantity', 'price'],
            'pivotModel' => CartProduct::class,
        ],
    ];

    public function afterDelete()
    {
        $this->prices()->delete();
        $this->additional_prices()->delete();
        $this->variants()->delete();
        $this->property_values()->delete();
        $this->property_values()->delete();
        DB::table('offline_mall_product_accessory')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_product_tax')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_cart_products')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_product_custom_field')->where('product_id', $this->id)->delete();
    }

    /**
     * This setter makes it easier to set price values
     * in different currencies by providing an array of
     * prices. It is mostly used for unit testing.
     *
     * @internal
     *
     * @param $value
     */
    public function setPriceAttribute($value)
    {
        if ( ! is_array($value)) {
            return;
        }
        foreach ($value as $currency => $price) {
            ProductPrice::updateOrCreate([
                'product_id'  => $this->id,
                'currency_id' => Currency::where('code', $currency)->firstOrFail()->id,
            ], [
                'price' => $price,
            ]);
        }
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getVariantOptionsAttribute()
    {
        return $this->custom_fields()->whereIn('type', ['dropdown', 'color', 'image'])->get();
    }

    /**
     * We are using a simple dropdown for this attribute since the relation
     * widget has some problems with the emptyOption option.
     * @return array
     */
    public function getGroupByPropertyIdOptions()
    {
        return ['' => trans('offline.mall::lang.common.none')]
            + $this->category->properties->pluck('name', 'id')->toArray();
    }

    public function groupPriceInCurrency($group, $currency)
    {
        if ($group instanceof CustomerGroup) {
            $group = $group->id;
        }
        if ($currency instanceof Currency) {
            $currency = $currency->id;
        }

        $prices = $this->customer_group_prices;

        return optional($prices->where('currency_id', $currency)->where('customer_group_id', $group)->first())
            ->decimal;
    }

    public function additionalPriceInCurrency($category, $currency)
    {
        if ($category instanceof PriceCategory) {
            $category = $category->id;
        }
        if ($currency instanceof Currency) {
            $currency = $currency->id;
        }

        $prices = $this->additional_prices;

        return optional($prices->where('currency_id', $currency)->where('price_category_id', $category)->first())
            ->decimal;
    }
}
