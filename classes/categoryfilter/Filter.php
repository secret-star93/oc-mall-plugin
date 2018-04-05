<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Property;

abstract class Filter
{
    public $property;
    public static $specialProperties = ['price'];

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
        if (\in_array($this->property, self::$specialProperties, true)) {
            return $item->getAttribute($this->property);
        }

        $item->load('property_values');
        $value = $item->property_values->where('property_id', $this->property)->first();
        if ($value === null) {
            // The filtered property is specified on the product, not on the variant
            $value = $item->product->property_values->where('property_id', $this->property)->first();
        }

        $value = $value ? $value->value : null;

        return \is_array($value) ? json_encode($value) : $value;
    }

    abstract public function apply(Collection $items): Collection;

    abstract public function getValues(): array;
}
