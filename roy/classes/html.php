<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

class Html
{
    public static function encode($html)
    {
        $result = '';
        
        if (is_a($html, 'Text')) {
            $result = $html->render('htmlspecialchars');
        } else {
            $result = htmlspecialchars($html);
        }
        
        return $result;
    }
}
