<?php namespace Bolo\Bolo\Models;

use Bolo\Bolo\Behaviors\UserModel;
use Carbon\Carbon;
use October\Rain\Database\Traits\SoftDelete;
use RainLab\User\Models\User;

class Order extends \Model{
    public $table = 'bolo_orders';
    public $incrementing = false;

    protected $casts = [
        'gateway_options' => 'array',
        'gateway_result' => 'array'
    ];

    public $belongsTo = [
        'user' => 'RainLab\User\Models\User',
        'adminTransaction' => ['Bolo\Bolo\Models\AdminTransaction', 'key' => 'admin_transaction'],
        'bookmaker' => ['Bolo\Bolo\Models\Bookmaker', 'key' => 'bookmaker_id']
    ];

    use SoftDelete;

    public $implement = ['RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = ['status', 'type', 'gateway_name'];

    public static function atomicOrderProcess($orderId, callable $process, $allowedStatuses = null){
        $key = 'bolo_order_lock_'.$orderId;

        while(!\Cache::add($orderId, 1, 1)){
            usleep(500000);
        }

        $order = static::where('id', $orderId);
        if(is_array($allowedStatuses))
            $order->whereIn('status', $allowedStatuses);

        $order = $order->first();
        $ex = null;
        $resp = null;
        if($order){
            try{
                $resp = $process($order);
            } catch (\Exception $e){
                $ex = $e;
            }
        }

        \Cache::forget($key);

        if(!$order)
            return false;

        if($ex)
            return $ex;

        return $resp;
    }

    public static function cleanUp(){
        static::byStatus('created')->whereRaw('created_at < NOW() - INTERVAL 7 DAY')->update(['deleted_at' => \DB::raw('NOW()')]);
    }

    public static function createWith($user, $amount, $currency, $message, $gatewayName, $gatewayOptions, $type='deposit', $adminTransactionId=null, $balance = 0, $status = 'created', $bookmakerId = null){
        $order = new static();
        $order->id = date('YmdHis').mt_rand(1111,9999);
        $order->user_id = $user->id;
        $order->status = $status;
        $order->amount = floatval($amount);
        $order->currency = $currency;
        $order->message = strip_tags($message);
        $order->gateway_name = $gatewayName;
        $order->gateway_options = $gatewayOptions;
        $order->type = $type;
        $order->admin_transaction = $adminTransactionId;
        $order->balance = $balance;
        $order->bookmaker_id = $bookmakerId;
        $order->save();

        $order->reload();

        return $order;
    }

    public function saveAsPending($gatewayResult){
        $this->updateGatewayResult($gatewayResult);

        if($this->status == 'created')
            $this->status = 'pending';

        $this->pending_at = Carbon::now();

        $this->save();
    }

    public function saveAsConfirmed($gatewayResult){
        $user = $this->user;
        $this->updateGatewayResult($gatewayResult);
        $this->status = 'confirmed';
        $this->confirmed_at = Carbon::now();
        $balance = $user->balance + $this->amount;
        $this->balance = $balance;
        $user->balance = $balance;
        $this->save();
        $user->save();
        $mailFlds = [
            'id',
            'amount',
            'currency',
            'message',
            'gateway_name',
            'gateway_transaction_id'
        ];

        $mailTpl = [];
        foreach($mailFlds as $f){
            $mailTpl['order_'.$f] = $this->$f;
        }

        //logger($this->user->implement);

         //new UserModel($this->user); //User::where('id', $this->user_id)->first();

        $user->sendEmail('mail-user-payment-confirmed', $mailTpl);
        $user->sendEmail('mail-admin-payment-confirmed', $mailTpl, true);
    }

    public function updateGatewayResult($gatewayResult){
        $result = $this->gateway_result;
        if(!$result){
            $result = [];
        }

        $result[date('Y-m-d H:i:s')] = $gatewayResult;
        $this->gateway_result = $result;
    }

    public function scopeByStatus($q, $status){
        if(!is_array($status)){
            $status = [$status];
        }

        $q->whereIn('status', $status);
    }

    public function scopeByType($q, $type){
        if(!is_array($type)){
            $type = [$type];
        }

        $q->whereIn('type', $type);
    }
}
