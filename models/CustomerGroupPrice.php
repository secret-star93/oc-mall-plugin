<?php namespace OFFLINE\Mall\Models;

use Model;

class CustomerGroupPrice extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $rules = [
        'price' => 'required',
    ];
    public $table = 'offline_mall_customer_group_prices';
    public $morphTo = [
        'priceable' => [],
    ];
    public $fillable = [
        'customer_group_id',
        'currency_id',
        'priceable_id',
        'priceable_type',
        'price',
    ];

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (int)$value * 100;
    }

    public function getFloatAttribute()
    {
        if ($this->price === null) {
            return null;
        }

        return (float)($this->price / 100);
    }

    public function getDecimalAttribute()
    {
        if ($this->price === null) {
            return null;
        }

        return number_format($this->price / 100, 2, '.', '');
    }

    public function getIntegerAttribute()
    {
        if ($this->price === null) {
            return null;
        }

        return (int)$this->price;
    }
}
