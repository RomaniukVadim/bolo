<?php namespace Bolo\Bolo\Models;


use Backend\Facades\BackendAuth;
use October\Rain\Database\Traits\SimpleTree;

class UserModLog extends \Model{
    public $table = 'user_mod_log';

    public $belongsTo = [
        'user' => 'RainLab\User\Models\User',
        'admin' => 'Backend\Models\User'
    ];

    protected $fillable = ['user_id', 'admin_id', 'action_id', 'field', 'old_value', 'new_value'];

    const PARENT_ID = 'action_id';

    use SimpleTree;

    public static function logUser($model){
        $fields = $model->getDirty();

        $admin = BackendAuth::getUser();

        $admin = @$admin->id ?: null;

        if(!\App::runningInBackend()) {
            $admin = null;
        }

        $items = [];

        if(isset($fields['id'])){
            $items[] = ['user_id' => $fields['id'], 'admin_id' => $admin, 'action_id' => 0, 'field' => 'created_at', 'old_value' => '', 'new_value' => $fields['created_at']];
            $fields = [];
        }

        foreach($fields as $k=>$v){
            if(in_array($k, [
                'updated_at',
                'persist_code',
                'last_login',
                'last_seen'
            ])) continue;

            $old = $model->getOriginal($k) ?: '';
            $new = $v ?: '';

            if(strlen($old) > 255)
                $old = substr($old, 0, 250).'[...]';

            if(strlen($new) > 255)
                $new = substr($new, 0, 250).'[...]';

            $items[] = [
                'user_id' => $model->id,
                'admin_id' => $admin,
                'field' => $k,
                'old_value' => $old,
                'new_value' => $new
            ];
        }

        $first = array_shift($items);

        if(!$first)
            return;

        $base = static::create($first);

        foreach($items as $item){
            $item['action_id'] = $base->id;
            static::create($item);
        }
    }

}
