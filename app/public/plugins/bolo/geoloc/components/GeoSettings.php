<?php namespace Bolo\Geoloc\Components;

use Bolo\Bolo\Classes\TransHelper;
use Bolo\Geoloc\Classes\GeoUser;
use Cms\Classes\ComponentBase;
use RainLab\Translate\Classes\Translator;
use RainLab\User\Models\User;

class GeoSettings extends ComponentBase
{
    protected $restrictedPage = 'restricted';

    public function componentDetails()
    {
        return [
            'name'        => 'Geolocation Settings',
            'description' => 'Must be first component on the page'
        ];
    }

    public function defineProperties()
    {
        return [
        ];
    }

    public function onRun()
    {
        $u = GeoUser::create();

        $loc = $u->getLang();

        if($u->isBlocked()){
            //prevent redirects with
            if(!preg_match('#([az-]+/)?'.$this->restrictedPage.'$#', \Request::path())){
                return \Redirect::to('/'.$this->restrictedPage);
            }
        }

        if(!Translator::instance()->loadLocaleFromSession()){
            Translator::instance()->setLocale($loc);
        }
    }

}
