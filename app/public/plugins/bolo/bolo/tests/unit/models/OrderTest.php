<?php namespace Bolo\Bolo\Tests\Unit\Models;

use Bolo\Bolo\Gateways\Base;
use Bolo\Bolo\Models\Order;
use Bolo\Bolo\Tests\TestCase;
use RainLab\User\Models\User;

class OrderTest extends TestCase {

    protected function createGateway(){
        $user = User::first();

        $gate = Base::create('RPNPay', [
            'merchants' => [
                'Wechat' => [
                    'merchantId' => 'qeqwewqe',
                    'key' => '123456',
                ],
            ],
            'gatewayUrl' => 'http://bolo.dev',
            'gatewayUrlDev' => 'http://bolo.dev',
            'returnUrl' => '/'
        ]);

        $gate->setGatewayOptions(['gatewayOptMerchant' => 'Wechat']);

        $gate->prepareForm([
            'amount' => 100,
            'currency' => 'CNY',
            'message' => "Please fill my Facebook account\nAnd twitter too\n\n"
        ], $user);

        return $gate;
    }

    function testOrderCreate(){
        $gate = $this->createGateway();

        $orderId = $gate->vars()->hidden['order_id'];

        $order = Order::find($orderId);

        $this->assertNotEmpty($order);


    }

    function testOrderNotifyPending(){
        $gate = $this->createGateway();

        $orderId = $gate->vars()->hidden['order_id'];

        $order = Order::find($orderId);

        $this->assertNotEmpty($order);

        $flds = [
            'order_id' => $orderId,
            'order_time' => date('YmdHis'),
            'order_amount' => $order->amount * 100,
            'deal_id' => mt_rand(111111, 999999),
            'deal_time' => date('YmdHis'),
            'pay_amount' => $order->amount * 100,
            'pay_result' => 1
        ];

        $flds['signature'] = $gate->getSignature($flds);

        $res = $gate->handleNotification($flds);

        $this->assertInstanceOf(\Illuminate\Http\Response::class, $res);

        $this->assertEquals(200, $res->getStatusCode());

        $order->reload();

        $this->assertEquals('pending', $order->status);
    }

    function testOrderNotifyConfirmed(){
        $gate = $this->createGateway();

        $orderId = $gate->vars()->hidden['order_id'];

        $order = Order::find($orderId);

        $this->assertNotEmpty($order);

        $flds = [
            'order_id' => $orderId,
            'order_time' => date('YmdHis'),
            'order_amount' => $order->amount * 100,
            'deal_id' => mt_rand(111111, 999999),
            'deal_time' => date('YmdHis'),
            'pay_amount' => $order->amount * 100,
            'pay_result' => 3
        ];

        $flds['signature'] = $gate->getSignature($flds);

        $res = $gate->handleNotification($flds);

        $this->assertInstanceOf(\Illuminate\Http\Response::class, $res);

        $this->assertEquals(200, $res->getStatusCode());

        $order->reload();

        $this->assertEquals('confirmed', $order->status);
    }


}
