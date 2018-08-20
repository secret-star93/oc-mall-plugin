<?php namespace OFFLINE\Mall\Components;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;
use Url;

class Category extends MallComponent
{
    /**
     * @var CategoryModel
     */
    public $category;
    /**
     * @var Product|Variant
     */
    public $items;
    /**
     * @var integer
     */
    public $perPage;
    /**
     * @var integer
     */
    public $pageNumber;
    /**
     * @var string
     */
    public $productPage;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.category.details.name',
            'description' => 'offline.mall::lang.components.category.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'category'      => [
                'title'   => 'offline.mall::lang.common.category',
                'default' => ':slug',
                'type'    => 'dropdown',
            ],
            'product_page'  => [
                'title'       => 'offline.mall::lang.components.category.properties.product_page.title',
                'description' => 'offline.mall::lang.components.category.properties.product_page.description',
                'type'        => 'dropdown',
            ],
            'show_variants' => [
                'title'       => 'offline.mall::lang.components.category.properties.show_variants.title',
                'description' => 'offline.mall::lang.components.category.properties.show_variants.description',
                'default'     => '0',
                'type'        => 'checkbox',
            ],
            'include_children' => [
                'title'       => 'offline.mall::lang.components.category.properties.include_children.title',
                'description' => 'offline.mall::lang.components.category.properties.include_children.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'per_page'      => [
                'title'       => 'offline.mall::lang.components.category.properties.per_page.title',
                'description' => 'offline.mall::lang.components.category.properties.per_page.description',
                'default'     => '9',
                'type'        => 'string',
            ],
        ];
    }

    public function getCategoryOptions()
    {
        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + CategoryModel::get()->pluck('name', 'id')->toArray();
    }

    public function onRun()
    {
        $this->setData();

        $this->page->title            = $this->category->meta_title ?: $this->category->name;
        $this->page->meta_description = $this->category->meta_description;
    }

    protected function setData()
    {
        $this->setVar('category', $this->getCategory());
        $this->setVar('productPage', GeneralSettings::get('product_page'));
        $this->setVar('pageNumber', (int)request('page', 1));
        $this->setVar('perPage', (int)$this->property('per_page'));
        $this->setVar('items', $this->paginate($this->getItems()));
    }

    protected function getItems(): Collection
    {
        $showVariants = (bool)$this->property('show_variants');
        $includeChildren = (bool)$this->property('include_children');

        return $this->applyFilters($this->category->getProducts($showVariants, $includeChildren));
    }

    protected function getCategory()
    {
        return CategoryModel::bySlugOrId($this->param('slug'), $this->property('category'));
    }

    protected function paginate(Collection $items)
    {
        $paginator = new LengthAwarePaginator(
            $this->getPaginatorSlice($items),
            $items->count(),
            $this->perPage,
            $this->pageNumber
        );

        $filter = request()->get('filter', []);
        $paginator->appends('filter', $filter);

        $pageUrl = $this->controller->pageUrl($this->page->fileName, ['slug' => $this->param('slug'),]);

        return $paginator->setPath($pageUrl);
    }

    protected function getPaginatorSlice($items)
    {
        return $items->slice(($this->pageNumber - 1) * $this->perPage, $this->perPage);
    }

    protected function applyFilters($items)
    {
        $filter = request()->get('filter', []);
        if ( ! is_array($filter)) {
            $filter = [];
        }

        $filters = (new QueryString())->deserialize($filter, $this->category);
        foreach ($filters as $propertyId => $filter) {
            $items = $filter->apply($items);
        }

        return $items;
    }
}
