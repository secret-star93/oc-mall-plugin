<?php

namespace OFFLINE\Mall\Classes\Index;


use Illuminate\Support\Collection;
use Nahid\JsonQ\Jsonq;
use OFFLINE\Mall\Classes\CategoryFilter\Filter;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Models\Currency;

class Filebase implements Index
{
    protected $db;
    protected $jsonq;

    public function __construct()
    {
        $dir = storage_path('app/index');
        if ( ! is_dir($dir)) {
            mkdir($dir);
        }

        $this->db = new \Filebase\Database([
            'dir'    => $dir,
            'pretty' => false,
        ]);

        $this->jsonq = new Jsonq();

        $this->addMacros();
    }

    public function insert(string $index, Entry $entry)
    {
        $data = $entry->data();
        $item = $this->db->get($this->key($index, $data['id']));

        return $item->save($data);
    }

    public function update(string $index, $id, Entry $entry)
    {
        $data = $entry->data();
        $item = $this->db->get($this->key($index, $id));

        return $item->save($data);
    }

    public function delete(string $index, $id)
    {
        $item = $this->db->get($this->key($index, $id));

        return $item->delete();
    }

    public function fetch(string $index, Collection $filters, int $perPage, int $forPage): IndexResult
    {
        $skip  = $perPage * ($forPage - 1);
        $items = $this->search($index, $filters);

        $slice = array_map(function ($item) {
            return $item['id'];
        }, array_slice($items, $skip, $perPage));

        return new IndexResult($slice, count($items));
    }

    protected function search(string $index, Collection $filters)
    {
        $this->jsonq->collect($this->db->query()->results());
        $this->jsonq->where('index', '=', $index);

        if ($filters->has('category_id')) {
            $category = $filters->pull('category_id');
            $this->jsonq->whereIn($category->property, $category->values());
        }
        if ($filters->has('price')) {
            $currency = Currency::activeCurrency()->code;
            $price = $filters->pull('price');
            ['min' => $min, 'max' => $max] = $price->values();
            $this->jsonq->where($price->property . '.' . $currency, '>=', (int)($min * 100));
            $this->jsonq->where($price->property . '.' . $currency, '<=', (int)($max * 100));
        }

        $this->applyFilters($filters);

        return $this->jsonq->get();
    }

    protected function key(string $index, $id): string
    {
        return $index . '-' . $id;
    }

    protected function addMacros()
    {
        $this->jsonq->macro('includes', function ($val, $comp) {
            if (is_array($val)) {
                if (is_array($val[0])) {
                    $val = array_map(function ($val) {
                        return json_encode($val);
                    }, $val);
                }

                return count(array_intersect($val, $comp)) > 0;
            }

            return in_array($comp, $val);
        });

        $this->jsonq->macro('includes between', function ($val, $comp) {
            foreach ($val as $value) {
                if ($value >= $comp[0] && $value <= $comp[1]) {
                    return true;
                }
            }

            return false;
        });
    }

    protected function applyFilters(Collection $filters)
    {
        $filters->each(function (Filter $filter) {
            if ($filter instanceof SetFilter) {
                $this->jsonq->where('property_values.' . $filter->property->id, 'includes', $filter->values());
            }
            if ($filter instanceof RangeFilter) {
                $this->jsonq->where('property_values.' . $filter->property->id, 'includes between', [
                    $filter->minValue,
                    $filter->maxValue,
                ]);
            }
        });
    }
}