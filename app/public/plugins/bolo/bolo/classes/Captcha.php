<?php namespace Bolo\Bolo\Classes;

class Captcha {
    public static function get($name = 'default'){
        return new CaptchasDotNetCached(
            config('captcha.'.$name.'.key', 'demo'),
            config('captcha.'.$name.'.secret', 'secret'),
            storage_path('temp'),
            config('captcha.'.$name.'.timeout', '3600'),
            config('captcha.'.$name.'.chars', 'abcdefghkmnopqrstuvwxyz0123456789'),
            config('captcha.'.$name.'.length', '5'),
            config('captcha.'.$name.'.width', '160'),
            config('captcha.'.$name.'.height', '80'),
            config('captcha.'.$name.'.color', '000060')
        );
    }
}
