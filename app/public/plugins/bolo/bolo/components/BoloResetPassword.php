<?php namespace Bolo\Bolo\Components;

use Auth;
use Mail;
use Validator;
use ValidationException;
use ApplicationException;
use RainLab\User\Models\User as UserModel;
use Bolo\Bolo\Classes\Captcha;
use Bolo\Bolo\Classes\TransHelper;
use Illuminate\Support\Facades\Request;
use October\Rain\Support\Facades\Flash;
use RainLab\Translate\Models\Message;

class BoloResetPassword extends \RainLab\User\Components\ResetPassword{
    /**
     * Trigger the password reset email
     */
    public function onRestorePassword()
    {
        $capt = Captcha::get();

        if(!$capt->validate(post('capt_rnd')) || !$capt->verify(post('capt_code'))){
            throw new ApplicationException(Message::trans("login.capt_invalid"));
        }

        if (!$user = UserModel::findByEmail(post('email'))) {
            throw new ApplicationException(Message::trans("reset.invalid_user"));
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);


        $link = request()->path();

        $link = url($link).'?code='.$code;

        $data = [
            'reset_link' => $link,
            'reset_code' => $code
        ];

        //logger($data);

        $user->sendEmail('mail-user-reset-password', $data);
    }

    /**
     * Perform the password reset
     */
    public function onResetPassword()
    {
        try {
            $rules = [
                'code'     => 'required',
                'password' => 'required:create|between:6,32|confirmed',
                'password_confirmation' => 'required_with:password|between:6,32',
            ];

            $validation = Validator::make(post(), $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Break up the code parts
             */
            $parts = explode('!', post('code'));
            if (count($parts) != 2) {
                throw new ApplicationException(Message::trans("reset.invalid_activation_code"));
            }

            list($userId, $code) = $parts;

            if (!$code || !strlen(trim($code))) {
                throw new ApplicationException(Message::trans("reset.invalid_activation_code"));
            }

            if (!strlen(trim($userId)) || !($user = Auth::findUserById($userId))) {
                throw new ApplicationException(Message::trans("reset.invalid_user"));
            }

            if (!$user->attemptResetPassword($code, post('password'))) {
                throw new ApplicationException(Message::trans("reset.invalid_activation_code"));
            }

        }
        catch (\Exception $ex) {
            $ex = TransHelper::translateValidationEx($ex, 'reg.');

            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }


}
