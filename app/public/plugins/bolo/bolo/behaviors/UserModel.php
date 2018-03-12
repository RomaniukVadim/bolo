<?php namespace Bolo\Bolo\Behaviors;
use Backend\Models\User;
use Backend\Models\UserGroup;
use Bolo\Bolo\Models\UserModLog;
use Illuminate\Support\Facades\Mail;
use October\Rain\Exception\ApplicationException;
use October\Rain\Support\Facades\Twig;
use RainLab\Translate\Classes\Translator;
use RainLab\Translate\Models\Message;
use RainLab\User\Models\User as BaseUser;
use System\Classes\ModelBehavior;



class UserModel extends ModelBehavior
{

    public static function regRules(){
        return [
            'username' => 'required|between:2,255|unique:users',
            'password' => 'required:create|between:6,32|confirmed',
//            'password_confirmation' => 'required_with:password|between:6,32',
            'email' => 'email|between:6,255',
            //'email2' => 'email',
            //'name' => 'required|between:2,255',
            //'surname' => 'required|between:2,255',
            //'title' => 'required|reg_opts:title',
            //'nationality' => 'required|reg_opts',
            //'dob' => 'required',
            //'addr' => 'required|between:2,255',
            //'city' => 'required|between:2,255',
            //'zip' => 'required|between:2,64',
            //'country' => 'required|reg_opts',
            //'phone' => 'required|between:2,64',
            //'mobile' => 'required|between:2,64',
            //'currency' => 'required|reg_opts',
            'how_hear' => 'required|reg_opts',
            //'tos' => 'accepted|in:1', //manual check
            //'sec_question' => 'required|reg_opts',
            //'sec_question2' => 'required|reg_opts',
            //'sec_answer' => 'required|between:2,255',
            //'sec_answer2' => 'required|between:2,255',
            'chat_username' => 'required|between:2,255',
            'chat_type' => 'required|in:skype,wechat,qq'
        ];
    }

    public function __construct($model)
    {
        parent::__construct($model);
        $model->addFillable([
            'lang',
            'mobile',
            'chat_username',
            'chat_type',
            'how_hear',
            'tos',
        ]);

        //allow empty email
        $model->rules = array_merge($model->rules, [
            'email' => 'email|between:6,255'
        ]);

        $model->bindEvent('model.beforeSave', [$this, 'nullEmail']);

    }

    public function nullEmail()
    {
        if(isset($this->model->attributes['email']) && empty($this->model->attributes['email'])){
            if($this->model->exists){
                $this->model->attributes['email'] = null;
            } else {
                unset($this->model->attributes['email']);
            }

        }

        return true;
    }

    public static function getRegFormOpts($fieldName, $page = null){

        if(is_null($page))
            $page = \RainLab\Pages\Classes\Page::loadCached(\Cms\Classes\Theme::getEditTheme(), 'register');

        $orig = $page->getViewBag()->$fieldName;
        $trans = $page->$fieldName;

        $orig = array_filter(array_map('trim', explode("\n", $orig)));
        $trans = array_filter(array_map('trim', explode("\n", $trans)));

        if(count($orig) != count($trans))
            $trans = $orig;

        return array_combine($orig, $trans);
    }

    public function sendEmail($contentFile, $params = [], $toAdmin = false, $layout = null){
        if(is_null($layout)){
            $layout = 'default';
        }

        $userData = $this->model->toArray();

        $data = array_merge($userData, $params);

        $locale = Translator::instance()->getLocale();
        $mailLocale = $toAdmin ? 'en': ($userData['lang'] ?: 'en');
        Translator::instance()->setLocale($mailLocale);

        $mail = \RainLab\Pages\Classes\Content::loadCached(\Cms\Classes\Theme::getEditTheme(), $contentFile);
        if(!$mail){
            throw new ApplicationException("Cant find email template: ".$contentFile);
        }

        $subject = $mail->subject;

        $mailContent = Twig::parse($mail->parseMarkup(), $data);
        $subject = Twig::parse($subject, $data);
        $data['subject'] = $subject;

        if($toAdmin){
            $data['to'] = array_map('trim', explode(',', $mail->admin_email));
            if(!$data['to']||(count($data['to']) == 1&&empty($data['to'][0]))){
                $admins = UserGroup::where('name', 'Admins')->first();
                foreach ($admins->users as $admin){
                    $data['to'][] = $admin->email;
                }
            }
            $data['to_name'] = '';
        } else {
            if(empty($data['email']))
                return true;

            $data['to'] = [$data['email']];
            $data['to_name'] = @$data['name'] ?: '';
        }

        Translator::instance()->setLocale($locale);

        $params['activeLocale'] = $mailLocale;
        $params['mail_content'] = $mailContent;
        Message::setContext($locale);
        return Mail::send('bolo.bolo::mail.'.$layout, $params, function($msg) use($data){
            foreach($data['to'] as $to){
                if(!$to){
                    continue;
                }
                $msg->to($to, $data['to_name']);
            }

            $msg->subject($data['subject']);
        });
    }

    public function afterSave(){
//        if(!\App::runningInBackend()) {
//            return;
//        }

        UserModLog::logUser($this->model);
    }
}

