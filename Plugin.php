<?php namespace OFFLINE\Mall;


use Event;
use October\Rain\Database\Model;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Customer;
use RainLab\User\Models\User;
use Rainlab\User\Models\User as UserModel;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $require = ['RainLab.Translate', 'RainLab.User', 'OFFLINE.Cashier'];

    public function boot()
    {
        $this->registerStaticPagesEvents();
        $this->extendUserModel();
    }

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    protected function registerStaticPagesEvents()
    {
        Event::listen('pages.menuitem.listTypes', function () {
            return [
                'all-mall-categories' => trans('offline.mall::lang.menu_items.all_categories'),
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function ($type) {
            if ($type == 'all-mall-categories') {
                return Category::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function ($type, $item, $url, $theme) {
            if ($type == 'all-mall-categories') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
        });
    }

    /**
     * Extend RainLab's User Model with the needed
     * relationships.
     */
    protected function extendUserModel()
    {
        if ( ! class_exists('RainLab\User\Models\User')) {
            return;
        }
        UserModel::extend(function (Model $model) {
            $model->hasOne['customer'] = [
                Customer::class,
            ];
        });
    }

}
