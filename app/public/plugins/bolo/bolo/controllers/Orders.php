<?php namespace Bolo\Bolo\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendAuth;
use Backend\Facades\BackendMenu;
use Bolo\Bolo\Models\Order;
use RainLab\User\Controllers\Users;
use System\Classes\SettingsManager;

class Orders extends Controller {

    public $implement = [
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ImportExportController',
    ];

    public $importExportConfig = 'config_export.yaml';

    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bolo.bolo.access_orders'];

    function __construct(){
        parent::__construct();

        BackendMenu::setContext('RainLab.User', 'user', 'users');
        SettingsManager::setContext('Bolo.Bolo', 'bolo_orders');
    }

    function index(){
        Order::cleanUp();

        return parent::index();
    }

    public function listInjectRowClass($record, $definition = null)
    {
        $cls = '';

        if ($record->status == 'pending') {
            $cls = 'frozen';
        }

        if ($record->status == 'confirmed') {
            $cls = 'positive';
        }

        return $cls;
    }

}
