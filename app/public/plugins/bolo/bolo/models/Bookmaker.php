<?php namespace Bolo\Bolo\Models;

use Bolo\Bolo\Behaviors\UserModel;
use Carbon\Carbon;
use RainLab\User\Models\User;

class Bookmaker extends \Model
{
    use \October\Rain\Database\Traits\Validation;

    public $rules = [
        'name'                  => 'required',
    ];

    protected $table = 'bookmakers';

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
