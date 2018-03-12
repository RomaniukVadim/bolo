<?php

$cnt = file_get_contents('php://stdin');

$path = __DIR__.'/log';

file_put_contents($path.'/'.date('Ymd_His').'_'.rand(1000, 9999).'.eml', $cnt);