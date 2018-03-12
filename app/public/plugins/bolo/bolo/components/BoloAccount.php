<?php namespace Bolo\Bolo\Components;

use Bolo\Bolo\Behaviors\UserModel;
use Bolo\Bolo\Classes\AuthManager;
use Bolo\Bolo\Classes\Captcha;
use Bolo\Bolo\Classes\NotActivatedException;
use Bolo\Bolo\Classes\TransHelper;
use Cms\Classes\CmsException;
use Cms\Classes\Content;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use League\Flysystem\Exception;
use October\Rain\Auth\AuthException;
use October\Rain\Exception\AjaxException;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Flash;
use October\Rain\Support\Facades\Twig;
use October\Rain\Support\Str;
use RainLab\Translate\Classes\MLContent;
use RainLab\Translate\Classes\Translator;
use RainLab\Translate\Models\Locale;
use RainLab\Translate\Models\Message;
use RainLab\User\Facades\Auth;
use RainLab\User\Models\Settings as UserSettings;

class BoloAccount extends \RainLab\User\Components\Account{

    public function onRender()
    {
        if($this->property('form') == 'sign-in'){
            $this->page['capt'] = \Bolo\Bolo\Classes\Captcha::get();

            return $this->renderPartial('::sign-in');
        }

        return '';
    }

    public function onSigninReload(){
        $this->page['capt'] = \Bolo\Bolo\Classes\Captcha::get();
        $this->page['signedInData'] = \Session::get('signedInData');
        return ['#SignInForm' => $this->renderPartial('::sign-in-form')];
    }

    public function onRegister()
    {
        try {
            if (!UserSettings::get('allow_registration', true)) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_disabled'));
            }

            /*
             * Validate input
             */
            $data = post();

            $rules = UserModel::regRules();

            $validation = Validator::make($data, $rules, [
                'reg_opts' => 'Invalid :attribute'
            ]);

            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            if(@$data['tos'] != 1){
                throw new ValidationException([Message::trans("reg.tos_not_accepted")]);
            }

            /*
             * Register user
             */
            $data['lang'] = Translator::instance()->getLocale();
            $requireActivation = UserSettings::get('require_activation', true);
            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;
            $user = Auth::register($data, $automaticActivation);

            $user->sendEmail('mail-user-welcome');
            $user->sendEmail('mail-admin-new-user', [], true);

            //throw new ValidationException([Message::trans("reg.no_chat_specified")]);

            /*
             * Automatically activated or not required, log the user in
             */
            if ($automaticActivation || !$requireActivation) {
                Auth::login($user);
            }

            return $this->makeRedirection();
        }
        catch (\Exception $ex) {
            $ex = TransHelper::translateValidationEx($ex, 'reg.');

            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    public function onSignin(){
        $data = post();
        \Session::forget('signedInData');
        try {
            $capt = Captcha::get();
            if(!$capt->validate(post('capt_rnd')) || !$capt->verify(post('capt_code'))){
                unset($data['capt_code']);
                throw new \ValidationException([Message::trans("login.capt_invalid")]);
            }
            return parent::onSignin();
        } catch (AuthException $ex){
            unset($data['password']);
            unset($data['login']);
            throw new ApplicationException(Message::trans("login.invalid"));
        } catch (NotActivatedException $ex){
            throw new ApplicationException(Message::trans("login.require_activation"));
        }finally{
            \Session::put('signedInData', $data);
        }
    }

    public function onUpdate()
    {
        if (!$user = $this->user()) {
            return;
        }

//        $oldPass = post('old_password', '');
//        if($oldPass&&!$user->checkPassword($oldPass)){
//            throw new AuthException(Message::trans("profile.invalid_password"));
//        }

        $data = [];
        $allowedFields = [
            'mobile', 'chat_username', 'chat_type'
        ];

        if(post('password')||post('password_confirmation')){
            $allowedFields = array_merge($allowedFields, ['password', 'password_confirmation']);
        }

        foreach($allowedFields as $k){
            $data[$k] = post()[$k];
        }

        try {
            $rules = [];
            $regRules = UserModel::regRules();
            foreach ($allowedFields as $f) {
                if (isset($regRules[$f])) {
                    $rules[$f] = $regRules[$f];
                }
            }

            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            $user->fill($data);
            $user->save();

        }catch (\Exception $ex) {
            throw TransHelper::translateValidationEx($ex, 'reg.');
        }
        /*
         * Password has changed, reauthenticate the user
         */
        if (strlen(post('password'))) {
            Auth::login($user->reload(), true);
        }

        /*
         * Redirect
         */
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }
}
