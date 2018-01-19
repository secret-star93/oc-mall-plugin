<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Nullable;
use OFFLINE\Mall\Classes\Traits\HashIds;

/**
 * Model
 */
class Property extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use HashIds;

    protected $dates = ['deleted_at'];

    public $jsonable = ['options'];

    public $rules = [
        'name' => 'required',
        'type' => 'required|in:text,textarea,dropdown,checkbox,color,image',
    ];

    public $table = 'offline_mall_properties';

    public $hasMany = [
        'property_values' => PropertyValue::class,
    ];

    public function getValuesAttribute()
    {
        return $this->property_values->reject(function (PropertyValue $value) {
            return $value->value === '' || $value->value === null;
        })->unique('value');
    }

    public function getMinValueAttribute()
    {
        return $this->values->min('value');
    }

    public function getMaxValueAttribute()
    {
        return $this->values->max('value');
    }

    public function getTypeOptions()
    {
        return [
            'text'     => trans('offline.mall::lang.custom_field_options.text'),
            'textarea' => trans('offline.mall::lang.custom_field_options.textarea'),
            'dropdown' => trans('offline.mall::lang.custom_field_options.dropdown'),
            'checkbox' => trans('offline.mall::lang.custom_field_options.checkbox'),
            'color'    => trans('offline.mall::lang.custom_field_options.color'),
//            'image'    => trans('offline.mall::lang.custom_field_options.image'),
        ];
    }
}
