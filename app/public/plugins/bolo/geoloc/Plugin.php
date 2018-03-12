<?php namespace Bolo\Geoloc;


use Bolo\Bolo\Behaviors\UserModel;
use Illuminate\Support\Facades\Validator;
use Yaml;
use File;
use System\Classes\PluginBase;
use RainLab\User\Models\User as UserBase;
use RainLab\User\Controllers\Users as UsersController;


class Plugin extends PluginBase
{

    public $require = ['RainLab.User'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Bolo Bet Geoloc',
            'description' => 'Bolo Bet Geoloc',
            'author'      => '',
            'icon'        => 'icon-user-plus',
            'homepage'    => ''
        ];
    }

    public function boot()
    {
    }

    public function registerComponents()
    {
        return [
            'Bolo\Geoloc\Components\GeoSettings' => 'geoSettings'
        ];
    }

    public function registerSettings()
    {
        return [
            'geo_rules' => [
                'label'       => 'Rules',
                'description' => 'Visibility rules',
                'icon'        => 'icon-eye-slash',
                'url'         => \Backend::url('bolo/geoloc/rules'),
                'order'       => 100,
                'category'    => 'Geolocation',
                'permissions' => ['bolo.geoloc.manage_rules']
            ],
            'geo_regions' => [
                'label'       => 'Regions',
                'description' => 'Enable regions used in rules',
                'icon'        => 'icon-globe',
                'url'         => \Backend::url('bolo/geoloc/regions'),
                'order'       => 110,
                'category'    => 'Geolocation',
                'permissions' => ['bolo.geoloc.manage_regions']
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'bolo.geoloc.manage_rules'  => [
                'tab'   => 'Geolocation',
                'label' => 'Geolocation Manage Rules'
            ],
            'bolo.geoloc.manage_regions' => [
                'tab'   => 'Geolocation',
                'label' => 'Geolocation Manage Regions'
            ]
        ];
    }

    public function register(){

    }
}
