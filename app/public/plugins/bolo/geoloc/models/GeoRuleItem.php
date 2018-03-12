<?php namespace Bolo\Geoloc\Models;

class GeoRuleItem extends \Model
{
    public $table = 'geo_rule_items';


    public function scopeByPlace($query, $place){
        if(!$place)
            return;

        $query->where('place_id', $place);
    }

    public function save(array $options = null, $sessionKey = null){
        \Cache::tags('geo_rules')->flush();

        return parent::save($options, $sessionKey);
    }

    public static function getExcluded($ruleId){
        return self::where('rule_id', $ruleId)->where('exclude', 1)->lists('region_id');
    }

    public static function getIncluded($ruleId){
        return self::where('rule_id', $ruleId)->where('exclude', 0)->lists('region_id');
    }

}
