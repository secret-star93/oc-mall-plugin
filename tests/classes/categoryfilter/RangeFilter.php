<?php

namespace OFFLINE\Mall\Tests\Classes\Totals;

use Auth;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use PluginTestCase;

class RangeFilterTest extends PluginTestCase
{
    public function test_it_filters_by_price()
    {
        $hit1        = Product::first();
        $hit1->price = ['CHF' => 200];
        $hit1->save();

        $hit2        = $hit1->replicate();
        $hit1->price = ['CHF' => 300];
        $hit2->save();

        $miss        = $hit2->replicate();
        $hit1->price = ['CHF' => 100];
        $miss->save();

        $collection = collect([$hit1, $hit2, $miss]);

        $filter = new RangeFilter('price', 20000, 35000);
        $result = $filter->apply($collection);

        $this->assertCount(2, $result);
        $this->assertEquals($hit1->id, $result->first()->id);
        $this->assertEquals($hit2->id, $result->last()->id);
    }

    public function test_it_filters_by_property_values()
    {
        $property = Property::where('name', 'Height')->first();

        $value              = new PropertyValue();
        $value->property_id = $property->id;
        $value->value       = 200;

        $hit1 = Product::first();
        $hit1->save();
        $hit1->property_values()->save($value);

        $value        = $value->replicate();
        $value->value = 300;

        $hit2 = $hit1->replicate();
        $hit2->save();
        $hit2->property_values()->save($value);

        $value        = $value->replicate();
        $value->value = 100;

        $miss = $hit2->replicate();
        $miss->save();
        $miss->property_values()->save($value);

        $collection = collect([$hit1, $hit2, $miss]);

        $filter = new RangeFilter($property, 200, 350);
        $result = $filter->apply($collection);

        $this->assertCount(2, $result);
        $this->assertEquals($hit1->id, $result->first()->id);
        $this->assertEquals($hit2->id, $result->last()->id);
    }
}
