<?php namespace Bolo\Bolo;


use Backend\Facades\Backend;
use Backend\Models\EditorSetting;
use Bolo\Bolo\Behaviors\UserModel;
use Bolo\Bolo\Classes\AuthManager;
use Illuminate\Support\Facades\Validator;
use RainLab\Translate\Classes\Translator;
use Yaml;
use File;
use System\Classes\PluginBase;
use RainLab\User\Models\User as UserBase;
use RainLab\User\Controllers\Users as UsersController;
use RainLab\User\Models\Settings as UserSettings;

class Plugin extends PluginBase
{

    public $require = ['RainLab.User', 'RainLab.Translate'];


    protected $userFields;
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Bolo Bet Helper',
            'description' => 'Bolo Bet Helper',
            'author'      => '',
            'icon'        => 'icon-user-plus',
            'homepage'    => ''
        ];
    }

    public function boot()
    {
        if(\App::environment() == 'production') {
            $this->userSessionExpirationHack();
        }

        \App::singleton('user.auth', function() {
            return AuthManager::instance();
        });

        if(UserSettings::get('welcome_template')){
            UserSettings::set('welcome_template', '');
        }

        Validator::extend('reg_opts', function($attr, $value, $params){
            $opts = UserModel::getRegFormOpts($attr.'_opts');
            return isset($opts[$value]);
        });

        UserBase::extend(function($model) {
            $model->implement[] = 'Bolo.Bolo.Behaviors.UserModel';
        });

        EditorSetting::extend(function($model){
            $model->implement[] = 'Bolo.Bolo.Behaviors.ThemeEditorSetting';
        });

//        \Event::listen('rainlab.user.activate', function($user){
//            $user->sendEmail('mail-user-activated');
////            $user->sendEmail('mail-admin-user-activated', [], true);
//        });

        \Event::listen('backend.filter.extendScopes', function ($widget)  {
                $widgetController = $widget->getController();
                $widgetControllerClass = get_class($widgetController);
                if($widgetControllerClass == 'RainLab\User\Controllers\Users'){
                    $widget->removeScope('groups');
                    $widget->removeScope('activated');
                    $widget->addScopes(
                        [
                            'activated' => [
                                'label' => 'rainlab.user::lang.user.status_activated',
                                'type'  => 'checkbox',
                                'conditions' => 'is_activated = 1',
                            ],
                            'not_activated' => [
                                'label' => 'Not Activated',
                                'type'  => 'checkbox',
                                'conditions' => 'is_activated = 0',
                            ]
                        ]
                    );
                }
            }
        );


        \Event::listen('backend.form.extendFields', function($widget) {
            if($widget->model instanceof \RainLab\Pages\Classes\Content){
                $flds = ['name', 'username', 'surname', 'email'];

                $flds = array_merge($flds, array_keys($this->customUserFields()));

                if(strpos($widget->model->fileName, 'mail-admin-') === 0){
                    $widget->addFields([
                        'viewBag[admin_email]' => [
                            'label'   => 'Admin Email',
                            'type'    => 'text',
                            'required' => true
                        ]
                    ]);
                }

                if(strpos($widget->model->fileName, 'mail-') === 0){
                    if(strpos($widget->model->fileName, '-payment-') !== false){
                        $flds = array_merge($flds, [
                            'order_id',
                            'order_status',
                            'order_amount',
                            'order_currency',
                            'order_message',
                            'order_gateway_name',
                            'order_gateway_transaction_id'
                        ]);
                    }


                    $widget->addFields([
                        'plHint' => [
                            'label' => 'Variables',
                            'type' => 'section',
                            'comment' => '{{ '.join(' }}, {{ ', $flds).' }}'
                        ],

                        'viewBag[subject]' => [
                            'label'   => 'Subject',
                            'type'    => 'mltext'
                        ],
                    ]);
                }



            }

            if ($widget->getController() instanceof \RainLab\Pages\Controllers\Index
                && $widget->model instanceof \RainLab\Pages\Classes\MenuItem
            ) {
                $widget->addTabFields([
                    'viewBag[suffix]' => [
                        'tab' => 'Display',
                        'label' => 'Suffix',
                        'comment' => 'Add something to url. #link for example.',
                        'type' => 'text'
                    ]
                ]);
            }


//            if($widget->model instanceof \RainLab\Pages\Classes\Page){
//                if($widget->model->layout == 'home'){
//                    $flds = [];
//
//                    foreach(range(1, 20) as $n){
//                        $flds['viewBag[subject]'] = [
//                            'label'   => 'Subject',
//                            'type'    => 'mltext'
//                        ];
//                    }
//                }
//            }
//
//            logger(get_class($widget->model));
//            logger($widget->model->toArray());
        });

        UsersController::extendFormFields(function($widget) {
            // Prevent extending of related form instead of the intended User form
            if (!$widget->model instanceof UserBase) {
                return;
            }

            $widget->removeField('surname');
            $fields = $this->customUserFields();
            $fields['transactions'] = [
                'type' => 'partial',
                'tab' => 'Transactions',
                'path' => '$/bolo/bolo/models/admintransaction/_transaction_tab.htm',
            ];
            $widget->addTabFields($fields);
        });

        \Event::listen('backend.list.extendColumns', function($widget) {
            if (!$widget->getController() instanceof UsersController) {
                return;
            }

            if (!$widget->model instanceof UserBase) {
                return;
            }
            $newItems = [
                'created_at' => [
                    'label' => 'rainlab.user::lang.user.created_at',
                    'type' => 'datetime',
                    'format' => 'd/m/Y'
                ],
                'last_seen' => [
                    'label' => 'rainlab.user::lang.user.last_seen',
                    'type' => 'datetime',
                    'format' => 'd/m/Y'
                ]
            ];
            $config = [];
            foreach($this->customUserFields() as $key=>$conf){
                $item = [
                    'label' => $conf['label']
                ];
                if($key=='dob'){
                    $item['type'] = 'datetime';
                    $item['format'] = 'd/m/Y';
                }
                $config[$key] = $item;
            }
            $widget->addColumns($newItems);
            $widget->addColumns($config);
            $widget->defaultSort['direction'] = 'DESC';
            $widget->defaultSort['column'] = 'created_at';
        });

        \Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
            if (!$controller instanceof UsersController) {
                return;
            }

            $controller->addJs('/plugins/bolo/bolo/assets/js/list_fix.js');
        });

        \Event::listen('backend.menu.extendItems', function($manager) {
            $manager->addSideMenuItems('RainLab.User', 'user', [
                'export' => [
                    'label'       => 'Export Users',
                    'icon'        => 'icon-cloud-d1ownload',
                    'url'         => \Backend::url('bolo/bolo/export'),
                    'permissions' => ['rainlab.users.access_users']
                ],

                'orders' => [
                    'label'       => 'Orders / Report',
                    'icon'        => 'icon-dollar',
                    'url'         => \Backend::url('bolo/bolo/orders'),
                    'permissions' => ['bolo.bolo.access_orders']
                ],

                'user_mod_log' => [
                    'label'       => 'Log',
                    'icon'        => 'icon-history',
                    'url'         => \Backend::url('bolo/bolo/usermodlog'),
                    'permissions' => ['bolo.bolo.access_user_mod_log']
                ],

                'bookmaker' => [
                    'label'       => 'Bookmakers',
                    'icon'        => 'icon-dollar',
                    'url'         => \Backend::url('bolo/bolo/bookmaker'),
                    'permissions' => ['bolo.bolo.access_bookmakers']
                ],
            ]);


        });

//        \Event::listen('*', function(){
//            file_put_contents(storage_path('logs').'/events.log', \Event::firing()."\n", FILE_APPEND);
//        });

//        Event::listen('eloquent.deleting: RainLab\Pages\Classes\Content', function ($record) {
//        });
    }

    public function registerNavigation()
    {
        return [
            'theme_settings' => [
                'label'       => 'Bolo Settings',
                'url'         => \Backend::url('cms/themes/update/bolo'),
                'icon'        => 'icon-cog',
                'iconSvg'     => 'modules/system/assets/images/cog-icon.svg',
                'permissions' => ['cms.manage_themes'],
                'order'       => 1000,
            ]
        ];
    }


    public function registerComponents()
    {
        return [
            'Bolo\Bolo\Components\BoloAccount' => 'boloAccount',
            'Bolo\Bolo\Components\BoloResetPassword' => 'boloResetPassword',
            'Bolo\Bolo\Components\BoloSession' => 'boloSession',
            'Bolo\Bolo\Components\BoloContact' => 'boloContact',
            'Bolo\Bolo\Components\BoloPayment' => 'boloPayment',
        ];
    }

    public function register(){
        // Remove url translation
        \Event::listen('backend.form.extendFieldsBefore', function($widget) {
            if (!$model = $widget->model) {
                return;
            }

            if (
                $model instanceof Page &&
                isset($widget->fields['settings[url]'])
            ) {
                $widget->fields['settings[url]']['type'] = 'text';
            }
            elseif (
                $model instanceof \RainLab\Pages\Classes\Page &&
                isset($widget->fields['viewBag[url]'])
            ) {
                //logger($widget->fields['viewBag[url]']);

                unset($widget->fields['viewBag[url]']['preset']['field']);

                $widget->fields['viewBag[url]']['type'] = 'text';
            }
        }, -2);

    }

    public function registerPermissions()
    {
        return [
            'bolo.bolo.access_orders'  => [
                'tab'   => 'Bolo',
                'label' => 'Access Bolo Orders'
            ],
            'bolo.bolo.access_user_mod_log'  => [
                'tab'   => 'Bolo',
                'label' => 'Access User Modification Log'
            ],
            'bolo.bolo.access_transaction'  => [
                'tab'   => 'Bolo',
                'label' => 'Access Transaction'
            ],
            'bolo.bolo.access_bookmakers'  => [
                'tab'   => 'Bolo',
                'label' => 'Access Bookmakers'
            ]
        ];
    }


    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'addLocale'  => [$this, 'addLocale']
            ]
        ];
    }

    public function addLocale($url, $params=null){
        if(preg_match('@^(([a-z]+)s?://([^/:]+)(:\d+)?)?(/[^\?#]*)(\?[^#]*)?(#.*)?$@', $url, $res)){
            $host = @$res[1];
            $prot = @$res[2];
            $hostName = @$res[3];
            $port = @$res[4];
            $path = @$res[5];
            $query = @$res[6];
            $hash = @$res[7];

            if($prot && $prot != 'http'){
                return $url;
            }

            if($hostName && isset($_SERVER['HTTP_HOST']) && $hostName != $_SERVER['HTTP_HOST']){
                return $url;
            }

            if($params)
                $path = Translator::instance()->getPathInLocale($path, $params[0]);
            else
                $path = Translator::instance()->getPathInLocale($path, Translator::instance()->getLocale());

            if($path[0] != '/')
                $path = '/'.$path;

            $url = $host.$path.$query.$hash;
        }

        return $url;
    }

    public function customUserFields(){

        if(!$this->userFields){
            $configFile = __DIR__ . '/config/profile_fields.yaml';
            $this->userFields = Yaml::parse(File::get($configFile));
        }

        return $this->userFields;
    }

    protected function userSessionExpirationHack(){
        \App::before(function ($request) {
            $cookieKey = \App::runningInBackend()? 'admin_auth': 'user_auth';

            if($val = \Cookie::get($cookieKey)){
                //logger($cookieKey.' - ', $val);

                \Cookie::queue($cookieKey, $val, \Config::get("session.lifetime", 30));
            }
        });
    }

}
