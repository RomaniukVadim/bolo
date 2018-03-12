<?php
namespace Bolo\Bolo\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendAuth;
use Backend\Facades\BackendMenu;
use Bolo\Bolo\Models\AdminTransaction;
use Bolo\Bolo\Models\Order;
use Illuminate\Http\Request;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\ValidationException;
use RainLab\User\Models\User;
use System\Classes\SettingsManager;

class Transaction extends Controller
{
    private $validationRules = [
        'type'                 => 'required',
        'currency'              => 'required',
        'amount' => 'required|numeric',
        'description' => 'required',
    ];

    public $implement = ['Backend.Behaviors.FormController'];

    public $formConfig = 'form_config.yaml';

    function __construct(){
        parent::__construct();

        BackendMenu::setContext('RainLab.User', 'user', 'users');
        SettingsManager::setContext('Bolo.Bolo', 'transaction');
    }


    public function onCreate()
    {
        $data = post();
        if(!isset($data['AdminTransaction'])||!isset($data['user_id'])){
            throw new ApplicationException('Something went wrong');
        }

        $validator = \Validator::make($data['AdminTransaction'], $this->validationRules);
        if($validator->fails()){
            throw new ValidationException($validator);
        }

        $user = User::find($data['user_id']);
        if(!$user){
            throw new ApplicationException('User not fount');
        }

        $this->validateAmount($data['AdminTransaction']['amount'], $data['AdminTransaction']['type'], $user->balance);
        $this->validateBookmaker($data['AdminTransaction']['bookmaker_id'], $data['AdminTransaction']['type']);

        $balance = $user->balance + (float) $data['AdminTransaction']['amount'];
        try{
            $adminTransaction = AdminTransaction::create([
                'user_id' => $user->id,
                'admin_id' => BackendAuth::getUser()->id,
                'type' => $data['AdminTransaction']['type'],
                'currency' => $data['AdminTransaction']['currency'],
                'amount' => $data['AdminTransaction']['amount'],
                'bookmaker_id' => $data['AdminTransaction']['bookmaker_id']?$data['AdminTransaction']['bookmaker_id']:null,
                'description' => $data['AdminTransaction']['description'],
                'balance' => $balance
            ]);
            $user->balance = $balance;
            $user->save();
            Order::createWith($user, $adminTransaction->amount, $adminTransaction->currency, $adminTransaction->description, null, null, $adminTransaction->type, $adminTransaction->id, $balance, 'confirmed', $adminTransaction->bookmaker_id);
        }catch (\Exception $ex){
            throw new ApplicationException($ex->getMessage());
        }

        return redirect(\Backend::url('rainlab/user/users/preview/'.$user->id));
    }

    protected function validateAmount($amount, $type, $balance)
    {
        $amount = (float) $amount;
        switch($type){
            case 'deposit':
                if($amount<0){
                    throw new ValidationException(['amount' => 'Amount must be positive']);
                }
                break;
            case 'withdrawal':
                if($amount>0){
                    throw new ValidationException(['amount' => 'Amount must be negative']);
                }
                break;
            case 'good_will':
                if($amount<0){
                    throw new ValidationException(['amount' => 'Amount must be positive']);
                }
                break;
            case '':
                break;
        }
        if($balance+$amount<0){
            throw new ValidationException(['amount' => ucfirst($type).' cannot be greater than the balance.']);
        }
    }

    protected function validateBookmaker($bookmaker, $type)
    {
        if($type=='transfer'&&!$bookmaker){
            throw new ValidationException(['bookmaker' => 'You should choose a bookmaker']);
        }
    }
}