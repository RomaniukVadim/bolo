<?php namespace Bolo\Bolo\Classes;

class CaptchasDotNetCached extends CaptchasDotNet
{
    function random()
    {
        for ($remaining = 20; $remaining > 0; $remaining--) {
            // generate a new random string.
            $random = $this->__random_string();

            if(\Cache::add($this->__cache_key($random), 1, $this->__cleanup_time / 60)){
                break;
            }

            if($remaining == 1){
                logger("Captcha random generation error");
            }
        }

        // return the successfully registered random string.
        $this->__random = $random;
        return $random;
    }

    function __cache_key($random){
        return static::class.$random;
    }

    function validate($random)
    {
        $this->__random = $random;

        return \Cache::has($this->__cache_key($random));
    }

    function verify($input, $random = False)
    {
        if (!$random) {
            $random = $this->__random;
        }
        $password_letters = $this->__alphabet;
        $password_length = $this->__letters;

        // If the user input has the wrong lenght, it can't be correct.
        if (strlen($input) != $password_length) {
            return False;
        }

        // Calculate the MD5 digest of the concatenation of secret key and
        // random string. The digest is a hex string.
        $encryption_base = $this->__secret . $random;
        // This extension is needed for secure use of optional parameters
        // In case of standard use we do not append the values, to be
        // compatible to existing implementations
        if (($password_letters != 'abcdefghijklmnopqrstuvwxyz') || ($password_length != '6')) {
            $encryption_base = $encryption_base . ':' . $password_letters . ':' . $password_length;
        }
        $digest = md5($encryption_base);

        // Check the password according to the rules from the first
        // positions of the digest.
        for ($pos = 0; $pos < $password_length; $pos++) {
            $letter_num
                = hexdec(substr($digest, 2 * $pos, 2)) % strlen($password_letters);

            // If the letter at the current position is wrong, the user
            // input isn't correct.
            if ($input[$pos] != $password_letters[$letter_num]) {
                return False;
            }
        }

        \Cache::forget($this->__cache_key($random));

        // The user input was correct.
        return True;
    }

}
