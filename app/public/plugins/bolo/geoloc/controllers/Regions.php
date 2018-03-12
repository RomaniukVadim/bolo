<?php namespace Bolo\Geoloc\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\Backend;
use Backend\Facades\BackendMenu;
use Bolo\Geoloc\Classes\GeoUser;
use Bolo\Geoloc\Models\GeoRegion;
use Bolo\Geoloc\Models\GeoRegionCountry;
use Illuminate\Support\Facades\Session;
use System\Classes\SettingsManager;

class Regions extends Controller {

    public $implement = [
        'Backend.Behaviors.ListController'
    ];

    public $requiredPermissions = ['bolo.geoloc.manage_regions'];

    public $listConfig = 'config_list.yaml';

    function __construct(){
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Bolo.Geoloc', 'geo_regions');

    }

    function index(){
        return $this->asExtension('ListController')->index();
    }

    public function listInjectRowClass($record, $definition = null)
    {
        $cls = '';

        if (!$record->active) {
            $cls = 'safe disabled';
        }

        return $cls;
    }

//    public function listFilterExtendScopes($filterWidget){
//        $continents = $filterWidget->getScopeValue('continent');
//
//        $oldContinents = GeoRegionCountry::getContinents();
//        GeoRegionCountry::setContinents(array_keys($continents));
//
//        if(join('|', $continents) != join('|', $oldContinents)){
//            $filterWidget->setScopeValue('country', []);
//        }
//    }

    public function index_onToggleActive() {
        $id = post('id');

        if($id){
            $item = GeoRegion::find($id);

            if($item){
                $status = (bool)$item->active;

                $item->active = (int)!$status;

                if(!$item->active){
                    if(GeoRegion::descendants($item->id)->active()->exists()) {
                        \Flash::error('Cannot disable this region as there are sub-regions enabled.');
                        return;
                    }

                    list($depends, $count) = $item->getDependentItems();

                    if(isset($depends['geo_rules']['items'][1])) {
                        \Flash::error('Cannot disable this region because it assigned to default "Everywhere" region rule.');
                        return;
                    }
                }

                $item->save();

                $ids[] = $item->id;

                if($item->active){
                    $relatives = GeoRegion::parents($item->id)->active(false)->get();

                    foreach($relatives as $r){
                        $ids[] = $r->id;
                        $r->active = $item->active;
                        $r->save();
                    }
                }
            }
        }


        return $this->listRefresh();
    }

    public function index_onSetTest(){
        $place = post('id', null);
        if($place){
            $place = GeoRegion::find($place);

            $cont = GeoRegion::find(substr($place->id, 0, 2));
            $count = GeoRegion::find(substr($place->id, 0, 4));

            if($place->id != $count->id){
                $place = [$place->id];
            } else {
                $place = [];
            }

            $place[] = $count->id;
            $place[] = $cont->id;

            GeoUser::setAdminTestPlace($place);
        } else {
            GeoUser::setAdminTestPlace(null);
        }

        return \Redirect::to(Backend::url('bolo/geoloc/regions'));
    }
}
