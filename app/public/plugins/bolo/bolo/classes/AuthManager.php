<?php namespace Bolo\Bolo\Classes;


class AuthManager extends \RainLab\User\Classes\AuthManager {

    public function login($user, $remember = true)
    {
        if ($this->requireActivation && !$user->is_activated) {
            throw new NotActivatedException;
        }

        parent::login($user, $remember);
    }
}
