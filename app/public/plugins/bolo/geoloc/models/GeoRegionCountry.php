<?php namespace Bolo\Geoloc\Models;

class GeoRegionCountry extends GeoRegion
{
    public function newQuery($_ = true){

        $continents = static::getContinents();

        $q = parent::newQuery($_)->where('level', 1);

        if($continents){
            $q->byContinent($continents);
        }

        return $q;
    }

    public static function setContinents($continents){
        \Session::put('geo_region_country_continent', $continents);
    }

    public static function getContinents(){
        return \Session::get('geo_region_country_continent', []);
    }
}
