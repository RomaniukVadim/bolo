<?php namespace Bolo\Bolo\Components;

use Bolo\Bolo\Classes\TransHelper;
use Cms\Classes\ComponentBase;
use RainLab\Translate\Classes\Translator;
use RainLab\User\Models\User;

class BoloContact extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Bolo Contact',
            'description' => 'Contact Form'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => 'Redirect',
                'description' => 'Redirect after submit',
                'type'        => 'dropdown',
                'default'     => ''
            ]
        ];
    }

    public function getRedirectOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
    }

    public function onContactSend()
    {
        try {
            $data = post();

            $rules = [
                'name' => 'required|between:2,255',
                'email'    => 'required|email|between:6,255',
                'message' => 'required|between:8,255'
            ];

            $validation = \Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new \ValidationException($validation);
            }

            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->message = $data['message'];
            $user->lang = Translator::instance()->getLocale();

            $user->sendEmail('mail-admin-contact', [], true);

            return $this->makeRedirection();
        }
        catch (\Exception $ex) {
            throw TransHelper::translateValidationEx($ex, 'cont.');
        }
    }

    protected function makeRedirection()
    {
        $redirectUrl = $this->pageUrl($this->property('redirect'))
            ?: $this->property('redirect');

        if ($redirectUrl = post('redirect', $redirectUrl)) {
            return \Redirect::to($redirectUrl);
        }
    }

}
