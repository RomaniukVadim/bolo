<?php namespace Bolo\Geoloc\Classes;

use Bolo\Geoloc\Models\GeoRule;
use RainLab\Translate\Classes\Translator;

class GeoUser
{
    protected $regions;
    protected $cacheKey;
    protected $ruleIds;
    protected $rules;
    protected $sections;
    protected $location;

    /**
     * @return GeoUser
     */
    public static function current(){
        static $user = null;

        if(is_null($user)){
            $user = self::create();
        }

        return $user;
    }

    public static function create(){

        $adminTest = self::getAdminTestPlace();
        $location = null;

        if(!$adminTest) {
            $ip = \Request::getClientIp();

            $location = GeoIp::getRawLocation($ip);

            $regions = [];

            if(!$location->isError){
                $cn = $location->continent->code;
                $country = $location->country->isoCode;

                foreach (array_reverse($location->subdivisions) as $sub)
                    $regions[] = $cn.$country.$sub->isoCode;

                $regions[] = $cn.$country;
                $regions[] = $cn;
            } else {
                $regions = ['EU', 'GB'];
            }
        } else {
            $regions = $adminTest['regions'];
        }

        return new self($regions, $location);
    }

    public static function getAdminTestPlace(){
        return \Session::get('adminGeoTest');
    }

    public static function setAdminTestPlace($regions){
        if(is_null($regions)){
            \Session::remove('adminGeoTest');
            return;
        }

        \Session::set('adminGeoTest', ['regions' => $regions]);
    }

    public function __construct($regions, $location = null){
        $this->regions = $regions;
        $this->cacheKey = join("_", $regions).'_'.\Session::get('lang');

        if(\App::environment() != 'production'){
            $this->cacheKey .= rand(1, 10000);
        }

        $this->location = $location;

        list($this->ruleIds, $this->rules, $this->sections) = $this->getRules();
    }

    public function allowedRuleIds(){
        return $this->ruleIds;
    }

    public function general(){
        return $this->sections['general'];
    }

    public function getLang(){
        return @$this->sections['general']['lang'] ?: 'en';
    }

    public function isBlocked(){
        //Ignore on internal admin site
        if(strpos(\Request::getHost(), 'internal.') === 0)
            return false;

        return (boolean)@$this->sections['blocked'];
    }

    public function getRegions(){
        return $this->regions;
    }

    public function getRules(){
        $self = $this;
        return \Cache::tags('geo_rules')->remember('geo_r_'.$this->cacheKey, 24 * 60, function() use($self){
            $rules = GeoRule::select('geo_rules.*', \DB::raw('MAX(gri.exclude) AS exclude'), \DB::raw('GROUP_CONCAT(gri.region_id) as regions'), \DB::raw('MAX(gp.level) as max_level'))
                ->join('geo_rule_items AS gri', 'gri.rule_id', '=', 'geo_rules.id')
                ->join('geo_regions AS gp', 'gri.region_id', '=', 'gp.id')
                ->whereIn('gri.region_id', $self->regions)
                ->where('geo_rules.active', 1)
                ->groupBy('geo_rules.id')
                ->orderBy('max_level', 'desc')
                ->orderBy('geo_rules.priority', 'desc')
                ->having('exclude', '=', 0)
                ->get();

            $res = [];

            foreach($self->regions as $p){
                foreach($rules as $r){
                    if(strpos($r->regions, $p) !== false && !isset($res[$r->id])){
                        $res[$r->id] = $r;
                    }
                }
            }

            $rulesIds = array_keys($res);

            $sectionRules = [];
            $sectionValues = [];
            $sections = array_flip(GeoRule::getSections());

            foreach($res as $r){
                $add = false;
                foreach($sections as $sect=>$val){
                    if(true){ //!empty($r->$sect) || $r->$sect === '0'){
                        $add = true;
                        $sectionValues[$sect] = $r->$sect;
                        unset($sections[$sect]);
                    }
                }
                if($add)
                    $sectionRules[] = $r;

                if(empty($sections))
                    break;
            }

            $sectionValues = $this->fillDefaultSections($sectionValues);

            return [$rulesIds, $sectionRules, $sectionValues];
        });
    }

    protected function fillDefaultSections($sections){
        $sections['general'] = array_merge(['lang' => 'en'], (array)@$sections['general']);

        return $sections;
    }
}
