<?php namespace Bolo\Bolo\Components;

use RainLab\Pages\Classes\Page;
use RainLab\User\Components\Session;

class BoloSession extends Session {
    public function onRun()
    {
        $redirectUrl = Page::url($this->property('redirect'));

        $allowedGroup = $this->property('security', self::ALLOW_ALL);
        $isAuthenticated = \Auth::check();

        if (!$isAuthenticated && $allowedGroup == self::ALLOW_USER) {
            return \Redirect::guest($redirectUrl);
        }
        elseif ($isAuthenticated && $allowedGroup == self::ALLOW_GUEST) {
            return \Redirect::guest($redirectUrl);
        }

        $this->page['user'] = $this->user();
    }
}
