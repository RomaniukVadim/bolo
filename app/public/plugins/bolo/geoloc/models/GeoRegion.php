<?php namespace Bolo\Geoloc\Models;

class GeoRegion extends \Model
{
    public $table = 'geo_regions';

    public function scopeByContinent($query, $continent){
        if(!$continent)
            return;

        if(is_array($continent)){
            $query->where(function($w) use($continent){
                foreach($continent as $c){
                    $w->orWhere('id', 'LIKE', $c.'%');
                }
            });
        } else {
            $query->where('id', 'LIKE', $continent.'%');
        }
    }

    public function scopeByCountry($query, $country){
        if(!$country)
            return;

        if(is_array($country)){
            $query->where(function($w) use($country){
                foreach($country as $c){
                    if (strlen($c) == 2)
                        $w->where('id', 'LIKE', '__' . $c . '%');
                    elseif (strlen($c) == 4)
                        $w->where('id', 'LIKE', $c . '%');
                }
            });
        } else {
            if (strlen($country) == 2)
                $query->where('id', 'LIKE', '__' . $country . '%');
            elseif (strlen($country) == 4)
                $query->where('id', 'LIKE', $country . '%');
        }
    }

    public function scopeLevel($query, $level){
        if(!is_array($level))
            $level = [$level];


        $query->whereIn('level', $level);
    }

    public function scopeActive($query, $active=true){
        if(is_null($active))
            return;

        $query->whereActive((int)$active);
    }

    public function scopeDescendants($query, $id){
        $query->byContinent($id)->where('id', '<>', $id);
    }

    public function scopeParents($query, $id){
        $ids[] = substr($id, 0, 2);
        $ids[] = substr($id, 0, 4);
        $query->whereIn('id', $ids)->where('id', '<>', $id);
    }

    public function getDependentRules(){
        return [];//GeoRule::byPlace($this->id)->active()->get();
    }

    public function getDependentItems(){
        $rules = $this->getDependentRules();

        if(!$rules){
            return [[], 0];
        }

        $res = [
            'geo_rules' => ['title' => 'Region Rules', 'name' => 'name', 'items' => $rules->all()]
        ];

        $count = $rules->count();

        foreach($rules as $rule){
            list($items, $cnt) = $rule->getDependentItems();

            $count += $cnt;

            if(count($res) == 1)
                $res = array_merge($res, $items);
            else{
                foreach($items as $tbl=>$opts){
                    $res[$tbl]['items'] = array_merge($res[$tbl]['items'], $opts['items']);
                }
            }
        }

        return [$res, $count];
    }

    public function disableDependentItems(){
        list($items, $cnt) = $this->getDependentItems();

        if(!$cnt){
            return;
        }

        foreach($items['geo_rules']['items'] as $rule){
            $rule->disableDependentItems();
            $rule->active = 0;
            $rule->save();
        }
    }

    public function type(){
        switch($this->level){
            case 0:
                return 'Continent';
            case 1:
                return 'Country';
            case 2:
                return 'Subdivision';
        }
    }

    public static function getForRule($ruleId){
        $q = self::active()->orderBy('id', 'asc');

        $rule = GeoRule::find($ruleId);

        if($rule){
            $places = $rule->getUsedPlaces();

            if($places->count())
                $q->orWhereIn('id', $places->fetch('id')->all());
        }

        return $q->get();
    }

}
