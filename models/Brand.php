<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use System\Models\File;

class Brand extends Model
{
    use Validation;
    use Sortable;
    use Sluggable;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        'description',
        'website',
    ];
    public $slugs = [
        'slug' => 'name',
    ];
    public $rules = [
        'name'    => 'required',
        'website' => 'url',
    ];
    public $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'sort_order',
    ];
    public $table = 'offline_mall_brands';
    public $attachOne = [
        'logo' => File::class,
    ];
    public $hasMany = [
        'products' => Product::class,
    ];
}
