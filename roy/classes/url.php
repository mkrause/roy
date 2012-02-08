<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

class Url
{
    public static function route($route_in)
    {
        if (is_a($route_in, 'Route')) {
            $route_in = $route_in->get_route();
        }
        
        $base = Roy::base_url();
        $route = Route::normalize($route_in);
        $url = Path::concat($base, $route);
        return $url;
    }
    
    public static function self()
    {
        try {
            $self = Request::current()->self();
        } catch (NotFoundException $e) {
            $self = Roy::config('main.base_url');
        }
        
        $url = self::route($self);
        return $url;
    }
    
    public static function base()
    {
        return self::route('/');
    }
}
