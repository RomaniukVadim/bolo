<?php namespace Bolo\Bolo\Classes;

class Box extends \GDText\Box{

    /**
     * Fix for asian support
     * @param string $text
     * @return string[]
     */
    protected function wrapTextWithOverflow($text)
    {
        $lines = array();
        // Split text explicitly into lines by \n, \r\n and \r
        $explicitLines = preg_split('/\n|\r\n?/', $text);
        foreach ($explicitLines as $line) {
            // Check every line if it needs to be wrapped
            if(preg_match_all('/([\w]+)|(.)/u', $text, $matches)){
                $words = $matches[0];
                //$words = explode(" ", $line);
                $line = $words[0];
                for ($i = 1; $i < count($words); $i++) {
                    $box = $this->calculateBox($line." ".$words[$i]);
                    if (($box[4]-$box[6]) >= $this->box['width']) {
                        $lines[] = $line;
                        $line = $words[$i];
                    } else {
                        $line .= $words[$i];
                    }
                }
                $lines[] = $line;
            }
        }
        return $lines;
    }

}
