<?php namespace Bolo\Bolo\Classes;

use GDText\Color;

class Text2Img {
    static function convert($text, $opts=[]){
        $opts = array_merge([
            'w' => 100,
            'h' => 50,
            'fz' => 13,
            'color' => [50, 50, 50],
            'font' => 'msyhl.ttc',
            'trim' => true
        ], $opts);

        $opts['text'] = $text;

        $key = 'text2img_'.md5(serialize($opts));

        return \Cache::remember($key, 24 * 60 * 30, function() use($opts, $text){
            $im = imagecreatetruecolor($opts['w'], $opts['h']);
            imagealphablending($im, false);
            $transparency = imagecolorallocatealpha($im, 0, 0, 0, 127);
            imagefill($im, 0, 0, $transparency);
            imagesavealpha($im, true);

            $box = new Box($im);
            $box->setFontFace(__DIR__.'/../config/'.$opts['font']); // http://www.dafont.com/franchise.font
            $box->setFontColor(new Color($opts['color'][0],$opts['color'][1],$opts['color'][2]));
            //$box->setTextShadow(new Color(0, 0, 0, 50), 2, 2);
            $box->setFontSize($opts['fz']);
            $box->setBox(0, 0, $opts['w'], $opts['h']);
            $box->setTextAlign('center', 'center');
            $box->draw($text);

            if($opts['trim']){
                $im = static::trim($im);
            }

            ob_start();
            imagepng($im);
            $im = ob_get_clean();

            return 'data:image/png;base64,'.base64_encode($im);
        });
    }

    protected static function trim($img){
        $box = static::imageTrimBox($img);

        $img2 = imagecreate($box['w'], $box['h']);
        imagecopy($img2, $img, 0, 0, $box['l'], $box['t'], $box['w'], $box['h']);

        return $img2;
    }

    protected static function imageTrimBox($img, $hex=null){
        if (!ctype_xdigit($hex)) $hex = imagecolorat($img, 0,0);

        $b_top = $b_lft = 0;
        $b_rt = $w1 = $w2 = imagesx($img);
        $b_btm = $h1 = $h2 = imagesy($img);

        do {
            //top
            for(; $b_top < $h1; ++$b_top) {
                for($x = 0; $x < $w1; ++$x) {
                    if(imagecolorat($img, $x, $b_top) != $hex) {
                        break 2;
                    }
                }
            }

            // stop if all pixels are trimmed
            if ($b_top == $b_btm) {
                $b_top = 0;
                $code = 2;
                break 1;
            }

            // bottom
            for(; $b_btm >= 0; --$b_btm) {
                for($x = 0; $x < $w1; ++$x) {
                    if(imagecolorat($img, $x, $b_btm-1) != $hex) {
                        break 2;
                    }
                }
            }

            // left
            for(; $b_lft < $w1; ++$b_lft) {
                for($y = $b_top; $y <= $b_btm; ++$y) {
                    if(imagecolorat($img, $b_lft, $y) != $hex) {
                        break 2;
                    }
                }
            }

            // right
            for(; $b_rt >= 0; --$b_rt) {
                for($y = $b_top; $y <= $b_btm; ++$y) {
                    if(imagecolorat($img, $b_rt-1, $y) != $hex) {
                        break 2;
                    }
                }

            }

            $w2 = $b_rt - $b_lft;
            $h2 = $b_btm - $b_top;
            $code = ($w2 < $w1 || $h2 < $h1) ? 1 : 0;
        } while (0);

        // result codes:
        // 0 = Trim Zero Pixels
        // 1 = Trim Some Pixels
        // 2 = Trim All Pixels
        return array(
            '#'     => $code,   // result code
            'l'     => $b_lft,  // left
            't'     => $b_top,  // top
            'r'     => $b_rt,   // right
            'b'     => $b_btm,  // bottom
            'w'     => $w2,     // new width
            'h'     => $h2,     // new height
            'w1'    => $w1,     // original width
            'h1'    => $h1,     // original height
        );
    }

    protected static function isThisChineseText($text) {
        return preg_match('/\p{Han}+/u', $text);
    }


}
