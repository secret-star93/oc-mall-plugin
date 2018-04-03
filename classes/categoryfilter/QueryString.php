<?php

namespace OFFLINE\Mall\Classes\CategoryFilter;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Property;

/**
 * This class is used to (de)serialize a `Filter` class into a query string.
 */
class QueryString
{
    use HashIds;

    public function deserialize(array $query, Category $category): Collection
    {
        if (count($query) < 1) {
            return collect([]);
        }

        $query = collect($query)->mapWithKeys(function ($values, $id) {
            if ( ! $this->isSpecialProperty($id)) {
                $id = $this->decode($id);
            }

            return [$id => $values];
        });

        $specialProperties = $query
            ->keys()
            ->intersect(Filter::$specialProperties)
            ->map(function ($property) use ($query) {
                $values = $query->get($property);

                return new RangeFilter($property, $values['min'] ?? null, $values['max'] ?? null);
            });

        $properties = $category->load('properties')->properties->whereIn('id', $query->keys());

        return $properties->map(function (Property $property) use ($query) {
            if ($property->pivot->filter_type === 'set') {
                return new SetFilter($property->id, $query->get($property->id));
            } elseif ($property->pivot->filter_type === 'range') {
                $values = $query->get($property->id);

                return new RangeFilter($property->id, $values['min'] ?? null, $values['max'] ?? null);
            }
        })->concat($specialProperties)->keyBy('property');
    }

    public function serialize(Collection $filter)
    {
        $filter = $filter->mapWithKeys(function (Filter $filter) {
            $property = $this->isSpecialProperty($filter->property)
                ? $filter->property
                : $this->encode($filter->property);

            return [
                $property => $filter->getValues(),
            ];
        });

        return http_build_query(['filter' => $filter->toArray()]);
    }

    protected function isSpecialProperty(string $prop): bool
    {
        return \in_array($prop, Filter::$specialProperties, true);
    }
}
