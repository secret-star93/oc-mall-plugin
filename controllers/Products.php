<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use BackendMenu;
use October\Rain\Database\Models\DeferredBinding;
use OFFLINE\Mall\Classes\Traits\ProductPriceTable;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;

class Products extends Controller
{
    use ProductPriceTable;

    public $implement = [
        ListController::class,
        FormController::class,
        RelationController::class,
    ];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $requiredPermissions = [
        'offline.mall.manage_products',
    ];
    protected $optionFormWidget;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-products');

        $model                  = post('option_id') ? CustomFieldOption::find(post('option_id')) : null;
        $this->optionFormWidget = $this->createOptionFormWidget($model);
        $this->addCss('/plugins/offline/mall/assets/backend.css');

        if (count($this->params) > 0) {
            // This is pretty hacky but it works. To get the original data from the Variant
            // this session variable is flashed. The Variant model checks for the
            // existence and doesn't inherit the parent product's data if it exists.
            session()->flash('mall.variants.disable-inheritance');

            if (str_contains(\Request::header('X-OCTOBER-REQUEST-HANDLER'), 'PriceTable')) {
                $this->preparePriceTable();
            }
        }
    }

    /**
     * Save the initial price into the prices table.
     *
     * @param Product $model
     */
    public function formAfterCreate(Product $model)
    {
        $this->updateProductPrices($model, null, '_initial_price');
    }

    public function formAfterUpdate(Product $model)
    {
        $values = post('PropertyValues');
        if ($values === null) {
            return;
        }

        $properties = Property::whereIn('id', array_keys($values))->get();

        foreach ($values as $id => $value) {
            $pv = PropertyValue::firstOrNew([
                'product_id'  => $model->id,
                'property_id' => $id,
            ]);

            $pv->value = $value;
            $pv->save();

            // Transfer any deferred media
            $property = $properties->find($id);
            if ($property->type === 'image') {
                $media = DeferredBinding::where('master_type', PropertyValue::class)
                                        ->where('master_field', 'image')
                                        ->where('session_key', post('_session_key'))
                                        ->get();

                foreach ($media as $m) {
                    $slave                  = $m->slave_type::find($m->slave_id);
                    $slave->field           = 'image';
                    $slave->attachment_type = PropertyValue::class;
                    $slave->attachment_id   = $pv->id;
                    $slave->save();
                    $m->delete();
                }
            }
        }
    }

    public function onCreateOption()
    {
        $data  = $this->optionFormWidget->getSaveData();
        $model = CustomFieldOption::findOrNew(post('edit_id'));
        $model->fill($data);
        $model->save(null, $this->optionFormWidget->getSessionKey());

        $this->updatePrices($model);

        $field = $this->getCustomFieldModel();
        $field->custom_field_options()->add($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    public function onDeleteOption()
    {
        $recordId = post('record_id');
        $model    = CustomFieldOption::find($recordId);
        $order    = $this->getCustomFieldModel();
        $order->custom_field_options()->remove($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    protected function refreshOptionsList()
    {
        $items = $this->getCustomFieldModel()
                      ->custom_field_options()
                      ->withDeferred($this->optionFormWidget->getSessionKey())
                      ->get();

        $this->vars['items'] = $items;
        $this->vars['type']  = post('type');

        return ['#optionList' => $this->makePartial('$/offline/mall/controllers/customfields/_options_list.htm')];
    }

    protected function getCustomFieldModel()
    {
        $manageId = post('manage_id');
        $order    = $manageId
            ? CustomField::find($manageId)
            : new CustomField();

        return $order;
    }

    public function onLoadCreateOptionForm()
    {
        $this->vars['optionFormWidget'] = $this->optionFormWidget;
        $this->vars['customFieldId']    = post('manage_id');
        $this->vars['type']             = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    public function onLoadEditOptionForm()
    {
        $this->vars['optionFormWidget']    = $this->optionFormWidget;
        $this->vars['customFieldId']       = post('manage_id');
        $this->vars['customFieldOptionId'] = post('option_id');
        $this->vars['type']                = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    protected function createOptionFormWidget(CustomFieldOption $model = null)
    {
        $config                    = $this->makeConfig('$/offline/mall/models/customfieldoption/fields.yaml');
        $config->alias             = 'optionForm';
        $config->arrayName         = 'Option';
        $config->model             = $model ?? new CustomFieldOption();
        $config->model->field_type = post('type');
        $widget                    = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();

        $this->optionFormWidget = $widget;

        return $widget;
    }

    protected function relationExtendRefreshResults($field)
    {
        if ($field !== 'variants') {
            return;
        }

        return [
            '#Products-update-RelationController-images-view' => $this->relationRenderView('images'),
        ];
    }

    public function onRelationManageUpdate()
    {
        $parent = parent::onRelationManageUpdate();

        // Store the pricing information with the custom fields.
        if ($this->relationName === 'custom_fields') {
            $model = $this->relationModel->find($this->vars['relationManageId']);
            $this->updatePrices($model, '_prices');
        } elseif ($this->relationName === 'variants') {
            $variant = $this->relationModel->find($this->vars['relationManageId']);
            $this->updateProductPrices($this->vars['formModel'], $variant);
        }

        return $parent;
    }

    protected function updatePrices($model, $key = 'prices')
    {
        $data = post('MallPrice');
        foreach ($data as $currency => $_data) {
            $value = array_get($_data, $key);
            if ($value === "") {
                $value = null;
            }

            Price::updateOrCreate([
                'price_category_id' => null,
                'priceable_id'      => $model->id,
                'priceable_type'    => $model::MORPH_KEY,
                'currency_id'       => $currency,
            ], [
                'price' => $value,
            ]);
        }
    }

    protected function updateProductPrices($product, $variant, $key = '_prices')
    {
        $data = post('MallPrice');
        foreach ($data as $currency => $_data) {
            $value = array_get($_data, $key);

            ProductPrice::updateOrCreate([
                'currency_id' => $currency,
                'product_id'  => $product->id,
                'variant_id'  => $variant->id ?? null,
            ], [
                'price' => $value,
            ]);
        }
    }
}
