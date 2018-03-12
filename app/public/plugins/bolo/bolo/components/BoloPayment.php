<?php namespace Bolo\Bolo\Components;

use Bolo\Bolo\Classes\TransHelper;
use Bolo\Bolo\Gateways\Base;
use Bolo\Geoloc\Classes\GeoUser;
use Cms\Classes\ComponentBase;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\Session;
use RainLab\Pages\Classes\Page;
use RainLab\Translate\Classes\Translator;
use RainLab\User\Models\User;

class BoloPayment extends ComponentBase
{
    public $form = null;

    public function componentDetails()
    {
        return [
            'name'        => 'Payment Form',
            'description' => 'Bolo Payment Form'
        ];
    }

    public function defineProperties()
    {
        return [
            'gateway' => [
                'title'       => 'Gateway Class',
                'description' => 'Payment Gateway Class',
                'type'        => 'dropdown',
                'default'     => 'RPNPay'
            ],

            'return_url' => [
                'title' => 'Return Url',
                'type' => 'text'
            ],

            'gatewayOptMerchant' => [
                'title' => 'Merchant',
                'type' => 'text'
            ]
        ];
    }

    public function getGatewayOptions()
    {
        return [
            'RPNPayWechat' => 'RPN Pay Wechat',
            'RPNPayUnionPayDesktop' => 'RPN Pay Union Pay Desktop',
            'RPNPayUnionPayMobile' => 'RPN Pay Union Pay Mobile',
        ];
    }

    public function onRender()
    {
        //Save component tag props for ajax use
        \Session::put('bolo_payment_props_'.$this->controller->getPage()->getAttribute('url'), $this->getProperties());
    }

    public function onProceed(){

        if(\Request::method() != 'POST')
            return '';

        if(is_null($this->form)){
            $conf = null;

            $props = \Session::get('bolo_payment_props_'.$this->controller->getPage()->getAttribute('url'), null);
            if($props){
                $this->setProperties($props);
            }

            if($this->property('return_url', null)) {
                $conf['returnUrl'] = Page::url($this->property('return_url', null));
            }

            $opts = [];
            foreach($this->getProperties() as $k=>$v){
                if(strpos($k, 'gatewayOpt') === 0){
                    $opts[$k] = $v;
                }
            }

            $this->form = Base::create($this->property('gateway'), $conf);

            $this->form->setGatewayOptions($opts);

            try {
                $data = \Input::get('payment');
                $data['currency'] = 'CNY';
                $this->form->prepareForm($data, \Auth::getUser());
            }catch (\Exception $ex) {
                $ex = TransHelper::translateValidationEx($ex, 'pay.');
                throw $ex;
            }

        }

        return ['#boloPayment' => $this->renderPartial('::gateway')];
    }
}
