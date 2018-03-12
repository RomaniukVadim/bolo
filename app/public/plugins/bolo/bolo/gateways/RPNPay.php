<?php namespace Bolo\Bolo\Gateways;

use Bolo\Bolo\Models\Order;
use October\Rain\Exception\ValidationException;

class RPNPay extends Base {

    public static $configName = 'rpnpay';

    function setGatewayOptions($gatewayOptions){
        parent::setGatewayOptions($gatewayOptions);

        $merchant = @$gatewayOptions['gatewayOptMerchant'];

        $merchants = array_keys($this->config['merchants']);

        if(!in_array($merchant, $merchants)){
            throw new \Exception("RPN Payment gatewayOptMerchant param is missing. Required one of: ".join(', ', $merchants));
        }

        $this->config = array_merge($this->config, $this->config['merchants'][$merchant]);
    }


    public function prepareForm($postData, $user){
        $this->postData = $postData;
        $this->payer = $user;

        $min = (int)$this->gatewayOptions['gatewayOptMinAmount'] ?: 10;
        $max = (int)$this->gatewayOptions['gatewayOptMaxAmount'] ?: 99999;
        $rule = 'between:'.$min.','.$max;

        $valid = \Validator::make($this->postData, [
            'amount' => 'required|numeric|'.$rule,
            'currency' => 'required|in:CNY',
            'message' => 'required|between:4,255'
        ]);

        if($valid->fails()){
            throw new ValidationException($valid);
        }

        if(\App::environment() == 'production')
            $this->tpl->action = @$this->config['gatewayUrl'] ?: '';
        else
            $this->tpl->action = @$this->config['gatewayUrlDev'] ?: '';

        $order = $this->createOrder();

        $hidden = [
            'version' => '1.0',
            'sign_type' => 'MD5',
            'mid' => $this->config['merchantId'],
            'order_id' => $order->id,
            'order_amount' => (int)($order->amount * 100),
            'order_time' => $order->created_at->format('YmdHis'),
            'return_url' => $this->config['returnUrl'],
            'notify_url' => url('payment/notify/'.$this->name)
        ];

        $hidden['signature'] = $this->getSignature($hidden);

        $this->tpl->hidden = $hidden;
        $this->tpl->amount = $this->postData['amount'];
    }

    public function handleNotification($postData){
        $check = [];

        $order = Order::where('id', $postData['order_id'])->where('created_at', '>', \DB::raw('NOW()- INTERVAL 7 DAY'))->first();

        if(!$order){
            logger("[RPNPay] Incorrect Order", $postData);
            return response('Order not Found', 404);
        }

        $this->setGatewayOptions($order->gateway_options);

        foreach(['order_id', 'order_time', 'order_amount', 'deal_id', 'deal_time', 'pay_amount', 'pay_result'] as $f){
            $check[$f] = @$postData[$f];
        }

        if($postData['signature'] != $this->getSignature($check)){
            logger("[RPNPay] Incorrect Notification Signature", $postData);
            return response('Incorrect post data', 405);
        }

        $resp = Order::atomicOrderProcess($postData['order_id'], function($order) use($postData){
            if(!in_array($order->status, ['created', 'pending'])){
                if($order->status != 'confirmed'){
                    logger("[RPNPay] Forbidden order status: " .$order->status, $postData);
                }
                //Skip duplicate notifications
                return response('[Success]', 200);
            }

            $order->gateway_transaction_id = $postData['deal_id'];

            if($postData['pay_result'] == 1){
                $order->saveAsPending($postData);
            } elseif($postData['pay_result'] == 3){
                $order->saveAsConfirmed($postData);
            } else {
                logger("[RPNPay] Unknown Result Code", $postData);
                return response('Unknown Result Code', 405);
            }

            return response('[Success]', 200);
        });

        if($resp instanceof \Exception){
            logger("[RPNPay] Notification exception", [$resp->getMessage()]);
            throw $resp;
//            logger("[RPNPay] Notification exception", [$resp->getMessage()]);
//            return response('Not Found', 404);
        }

        return $resp;
    }

    public function getSignature($data){
        $sign = [];

        foreach($data as $k=>$v){
            $sign[] = $k.'='.$v;
        }

        $sign[] = 'key='.$this->config['key'];

        return md5(join('|', $sign));
    }
}
