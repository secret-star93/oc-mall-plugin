<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;


use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Property;

abstract class Filter
{
    public $property;
    public $specialProperties = ['price'];

    public function __construct($property)
    {
        if ($property instanceof Property) {
            $property = $property->id;
        }

        $this->property = $property;
    }

    public function setFilterValues(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $item->setAttribute('filter_value', $this->getFilterValue($item));

            return $item;
        })->reject(function ($item) {
            return $item->filter_value === null;
        });
    }

    public function getFilterValue($item)
    {
        if (\in_array($this->property, $this->specialProperties, true)) {
            return $item->getOriginal($this->property);
        }

        $value = $item->property_values->where('property_id', $this->property)->first();

        return $value ? $value->value : null;
    }

    abstract public function apply(Collection $items): Collection;
}