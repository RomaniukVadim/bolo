<?php namespace Bolo\Bolo\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendAuth;
use RainLab\User\Controllers\Users;

class Export extends Controller {
    public $suppressView = true;

    function __construct(){
        $this->user = BackendAuth::getUser();
        $this->requiredPermissions = ['rainlab.users.access_users'];
    }

    function index(){
        $fileName = 'bolo_users_'.date('Y_m_d_H_i_s');

        $usersCont = new Users();

        $list = $usersCont->makeList()->prepareModel();

        $firstFields = [
            'id',
            'username',
            'email',
            'email2',
            'title',
            'name',
            'surname',
            'dob',
            'kyc',
            'is_activated',
            'activated_at',
            'created_at',
            'last_login',
            'last_seen'
        ];

        $list->select($firstFields);

        $configFile = __DIR__ . '/../config/profile_fields.yaml';
        $config = \Yaml::parse(\File::get($configFile));

        $otherFields = [];

        foreach($config as $key=>$conf){
            if(!in_array($key, $firstFields))
                $otherFields[] = $key;
        }

        $list->addSelect($otherFields);

        $list = $list->get()->toArray();

        return \Excel::create($fileName, function($ex) use ($list){
            $ex->sheet('Sheet1', function($sh) use ($list){
                $sh->fromArray($list);
            });
        })->download('csv');

        //return response($res)->header('Content-Type', 'text/csv')->header('Content-Disposition', 'attachment; filename="'.$fileName.'""');
    }
}
