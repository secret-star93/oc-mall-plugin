<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class Country extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    public $timestamps = false;

    public $rules = [
        'code' => 'required|size:2',
        'name' => 'required'
    ];

    public $table = 'offline_mall_countries';

    public $belongsToMany = [
        'taxes' => [
            Tax::class,
            'table'    => 'offline_mall_country_tax',
            'key'      => 'country_id',
            'otherKey' => 'tax_id',
        ],
    ];
}
