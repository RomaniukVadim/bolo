<?php namespace Bolo\Bolo\Gateways;

use Bolo\Bolo\Models\Order;
use Carbon\Carbon;

class Base {

    public static $configName = '';

    protected $config;
    protected $gatewayOptions;
    protected $postData;
    protected $tpl;
    protected $payer;
    protected $name;

    function __construct($config){
        $this->config = $config;
        $this->tpl = new \stdClass();
        $this->name = last(explode('\\', static::class));
    }

    function setGatewayOptions($gatewayOptions){
        $this->gatewayOptions = $gatewayOptions;
    }

    function prepareForm($postData, $payer){

    }

    function handleNotification($postData){
        return response('Not found', 404);
    }

    function vars(){
        return $this->tpl;
    }

    function createOrder($gatewayOptions=[]){
        $gatewayOptions = array_merge($this->gatewayOptions, $gatewayOptions);
        $user = \Auth::getUser();
        $balance = $user->balance;
        return Order::createWith($this->payer, $this->postData['amount'], $this->postData['currency'], $this->postData['message'], $this->name, $gatewayOptions, 'deposit', null, $balance);
    }

    /**
     * @param $name
     * @param null $configOverride
     * @param null $extraOpts
     * @return Base
     */
    public static function create($name, $configOverride = null){
        $cls = '\Bolo\Bolo\Gateways\\'.$name;

        if(!class_exists($cls)){
            return false;
        }

        $conf = config('gateway.'.$cls::$configName, []);

        if(is_array($configOverride))
            $conf = array_merge($conf, $configOverride);

        return new $cls($conf);
    }
}
