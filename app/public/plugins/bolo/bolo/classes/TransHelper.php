<?php namespace Bolo\Bolo\Classes;

use October\Rain\Exception\ValidationException;
use RainLab\Translate\Models\Message;

class TransHelper {
    public static function translateValidationEx($ex, $prefix){
        if($ex instanceof ValidationException){
            $flds = $ex->getFields();

            $out = [];
            foreach($flds as $f=>$msgs){
                foreach($msgs as $m)
                    $out[] = str_replace(str_replace('_', ' ', $f), '"'.Message::trans($prefix.$f).'"', $m);
            }

            $msg = implode("\r\n", $out);
            $ex = new ValidationException([$msg]);
        }

        return $ex;
    }


}
