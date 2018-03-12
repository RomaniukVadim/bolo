<?php
Route::post('/payment/notify/{gateway}', function($gateway){
    $gateway = preg_replace('/[^a-zA-Z]+/', '', $gateway);

    $gate = \Bolo\Bolo\Gateways\Base::create($gateway);

    if(!$gate){
        return response('Not found', 404);
    }

    return $gate->handleNotification(post());
});

//Route::get('/test-email', function(){
//    $user = new \RainLab\User\Models\User();
//    $user->name = 'aasas';
//    $user->email = "asdsd@dssfsdfd.com";
//    $user->message = "sdasd asd asd asd asd asd sfsd";
//    $user->lang = \RainLab\Translate\Classes\Translator::instance()->getLocale();
//
//    $user->sendEmail('mail-admin-contact', [], true);
//
//    return 'ok';
//});
