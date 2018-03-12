<?php namespace Bolo\Bolo\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use System\Classes\SettingsManager;

class UserModLog extends Controller {

    public $implement = [
        'Backend.Behaviors.ListController'
    ];

    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bolo.bolo.access_orders'];

    function __construct(){
        parent::__construct();

        BackendMenu::setContext('RainLab.User', 'user', 'users');
        SettingsManager::setContext('Bolo.Bolo', 'user_mod_log');
    }

}
