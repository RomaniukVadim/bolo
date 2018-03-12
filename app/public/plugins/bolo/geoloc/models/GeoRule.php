<?php namespace Bolo\Geoloc\Models;

class GeoRule extends \Model
{
    public $table = 'geo_rules';

    protected static $formSections = ['general'];
    protected static $hiddenSections = ['blocked'];

    public function scopeByPlace($query, $place){
        if(!$place)
            return;

        $ids = GeoRuleItem::byPlace($place)->lists('rule_id');

        $query->whereIn('id', $ids);
    }

    public function scopeHidden($query, $yes = true){
        if(is_null($yes)){
            return;
        }

        $query->where('is_hidden', $yes);
    }

    public function scopeActive($query, $yes = true){
        if(is_null($yes)){
            return;
        }

        $query->where('active', $yes);
    }

    public function scopeActiveOrId($query, $id){
        $query->where(function($q)use($id){
            $q->where('active', 1)->orWhere('id', $id);
        });
    }

    public function getUsedPlaces($active = null){
        $res = GeoRegion::select('geo_regions.*')->join('geo_rule_items AS gri', 'gri.region_id', '=', 'geo_regions.id')->where('gri.rule_id', $this->id);

        $res->active($active);

        return $res->get();
    }

    public function types(){
        $result = [];

        foreach(self::getSections() as $t){
            if(empty($this->$t) || $this->$t === 'N')
                continue;

            if($t == 'blocked')
                return ['blocked'];

            $result[] = str_replace('_', ' ', $t);
        }

        return $result;
    }

    public function getGeneralAttribute($v){
        if(empty($v))
            return null;

        return json_decode($v);
    }

    public function setGeneralAttribute($v){
        if(empty($v))
            $this->attributes['general'] = null;

        $this->attributes['general'] = json_encode($v);
    }
    public function getContentAttribute($v){
        if(empty($v))
            return null;

        return json_decode($v);
    }

    public function setContentAttribute($v){
        if(empty($v))
            $this->attributes['content'] = null;

        $this->attributes['content'] = json_encode($v);
    }

    public function getRegionInAttribute(){
        return GeoRuleItem::getIncluded($this->id);
    }

    public function getRegionExAttribute(){
        return GeoRuleItem::getExcluded($this->id);
    }

    public function save(array $options = null, $sessionKey = null){
        \Cache::tags('geo_rules')->flush();

        return parent::save($options, $sessionKey);
    }

    public static function getSections($formOnly=false){
        if($formOnly)
            return self::$formSections;

        return array_merge(self::$formSections, self::$hiddenSections);
    }

    public function saveItems($items = null){
        GeoRuleItem::where('rule_id', $this->id)->delete();

        if(is_null($items))
            $items = input('places', []);

        foreach($items as $ex=>$places){
            if(is_array($places))
                foreach($places as $placeId){
                    $place = new GeoRuleItem();
                    $place->rule_id = $this->id;
                    $place->region_id = $placeId;
                    $place->exclude = $ex;
                    $place->save();
                }
        }
    }

    public function getRegionInOptions(){
        $reg = GeoRegion::getForRule($this->id);

        $res = [];

        foreach($reg as $r){
            $res[$r->id] = str_repeat('&nbsp;', $r->level * 6). $r->name;
        }

        return $res;
    }

    public function getRegionExOptions(){
        return $this->getRegionInOptions();
    }

}
