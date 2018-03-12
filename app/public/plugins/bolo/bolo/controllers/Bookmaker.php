<?php
namespace Bolo\Bolo\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use System\Classes\SettingsManager;

class Bookmaker extends Controller
{
    public $implement = ['Backend.Behaviors.FormController', 'Backend.Behaviors.ListController'];

    public $formConfig = 'form_config.yaml';

    public $listConfig = 'config_list.yaml';

    function __construct(){
        parent::__construct();

        BackendMenu::setContext('RainLab.User', 'user', 'users');
        SettingsManager::setContext('Bolo.Bolo', 'bookmakers');
    }
}