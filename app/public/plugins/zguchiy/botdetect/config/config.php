<?php return [
    // This contains the Laravel Packages that you want this plugin to utilize listed under their package identifiers
    'packages' => [
        'captcha-com/laravel-captcha' => [
            // Service providers to be registered by your plugin
            'providers' => [
                LaravelCaptcha\Providers\LaravelCaptchaServiceProvider::class,
            ],

            // The configuration file for the package itself. Start this out by copying the default one that comes with the package and then modifying what you need.
            'config' => [
                'ExampleCaptcha' => [
                    'UserInputID' => 'CaptchaCode',
                    'ImageWidth' => 250,
                    'ImageHeight' => 50,
                ],
            ],
        ],
    ],
];