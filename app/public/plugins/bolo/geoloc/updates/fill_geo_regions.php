<?php namespace Bolo\Geoloc\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class FillGeoRegions extends Migration
{

    public function up()
    {
        $continents = file(__DIR__.'/geo/continent.tsv');
        $contCountry = file(__DIR__.'/geo/continent-country.tsv');
        $subs = file_get_contents(__DIR__.'/geo/iso_3166-2.json');
        $country = file_get_contents(__DIR__.'/geo/iso_3166-1.json');
        $subs = json_decode($subs, JSON_OBJECT_AS_ARRAY);
        $country = json_decode($country, JSON_OBJECT_AS_ARRAY);

        $common = ['active' => 0, 'created_at' => \DB::raw('NOW()'), 'updated_at' => \DB::raw('NOW()')];

        $tmp = [];

        foreach(reset($country) as $cnt){
            $tmp[$cnt['alpha_2']] = $cnt;
        }
        $country = $tmp;

        foreach($continents as $cnt){
            $cnt = explode("\t", $cnt);

            \DB::table('geo_regions')->insert(array_merge(['id' => $cnt[0], 'iso' => $cnt[0], 'name' => trim($cnt[1]), 'full_name' => trim($cnt[1]), 'level' => 0], $common));
        }

        $tmp = [];

        foreach($contCountry as $cnt){
            $cnt = explode("\t", $cnt);

            if($cnt[1] == 'null' || $cnt[2] == 'null')
                continue;

            $tmp[$cnt[1]] = ['cont' => $cnt[0]];

            $count = @$country[$cnt[1]];

            if(!isset($count['name'])){
                print_r($count);
                continue;
            }

            if(!isset($count['official_name'])){
                $count['official_name'] = $count['name'];
            }

            \DB::table('geo_regions')->insert(array_merge(['id' => $cnt[0].$cnt[1], 'iso' => $cnt[1], 'name' => $count['name'], 'full_name' => $count['official_name'], 'level' => 1], $common));
        }

        foreach(reset($subs) as $cnt){
            $code = explode('-', $cnt['code']);

            $cont = $tmp[$code[0]]['cont'];

            \DB::table('geo_regions')->insert(array_merge(['id' => $cont.$code[0].$code[1], 'iso' => $code[1], 'name' => $cnt['name'], 'full_name' => $cnt['name'], 'level' => 2], $common));
        }
    }

    public function down()
    {
        \DB::table('geo_regions')->truncate();
    }

}


