<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Discounts extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_discounts',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-discounts');
    }
}
