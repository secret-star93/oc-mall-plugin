<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;

class PriceCategory extends Model
{
    use Validation;
    use Sortable;
    use Sluggable;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $table = 'offline_mall_price_categories';
    public $translatable = ['name'];
    public $rules = [
        'name' => 'required',
        'code' => 'required',
    ];
    public $fillable = [
        'name',
        'code',
    ];
    public $slugs = [
        'code' => 'name',
    ];
    public $hasMany = [
        'prices' => [Price::class, 'key' => 'price_category_id']
    ];
}
