<?php namespace Bolo\Geoloc\Models;

class GeoRegionContinent extends GeoRegion
{
    public function newQuery($_ = true){
        return parent::newQuery($_)->where('level', 0);
    }
}
