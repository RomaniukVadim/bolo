<?php namespace Bolo\Geoloc\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use Bolo\Geoloc\Models\GeoRegion;
use Bolo\Geoloc\Models\GeoRegionCountry;
use Bolo\Geoloc\Models\GeoRule;
use Bolo\Geoloc\Models\GeoRuleItem;
use Illuminate\Support\Facades\Session;
use System\Classes\SettingsManager;

class Rules extends Controller {

    public $implement = [
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.FormController'
    ];

    public $requiredPermissions = ['bolo.geoloc.manage_rules'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    function __construct(){
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Bolo.Geoloc', 'geo_rules');

    }

    public function listInjectRowClass($record, $definition = null)
    {
        if (!$record->active) {
            return 'safe disabled';
        }
    }

    public function index_onToggleActive() {
        $item = GeoRule::find(post('id'));

        if($item){
            $status = (bool)$item->active;

            $item->active = (int)!$status;

            if($item->active && $item->getUsedPlaces(false)->count()){
                \Flash::error('Rule have disabled regions assigned to it. Enable them before enable rule.');
                return;
            }

            $item->save();
        }


        return $this->listRefresh();
    }

    public function index_onRemove() {
        $id = post('id');

        if($id == 1){
            \Flash::error('Everywhere rule can not be removed');
            return;
        }

        if($id && ($item = GeoRule::find($id))){
            GeoRuleItem::where('rule_id', $id)->delete();
            $item->delete();
            \Flash::success("Item deleted");

            return $this->listRefresh();
        }
    }

    public function formAfterSave($model){
        $regs[0] = post('GeoRule._region_in', []);
        $regs[1] = post('GeoRule._region_ex', []);

        $model->saveItems($regs);
    }

    public function formBeforeCreate($model){
        $model->active = 1;
    }
}
