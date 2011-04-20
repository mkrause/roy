<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

/**
 * Represents a route, a string like '/foo/bar/baz' that locates a certain
 * resource, such as an HTML page.
 */
class Route
{
    protected $route = '';
    
    /**
     * Return the normalized form of the given route, according to the
     * following rules:
     * - Backslashes are replaced with forward slashes
     * - Any trailing slashes and double slashes and are removed
     *   (/foo///bar/ => /foo/bar).
     *
     * @param string route The route to normalize.
     * @return string The normalized string.
     * @throws ProgrammerException
     */
    public static function normalize($route_in)
    {
        $route = $route_in;
        
        if (!is_string($route)) {
            throw new ProgrammerException('Invalid route given');
        }
        
        if ($route !== '' and substr($route, 0, 1) !== '/') {
            throw new ProgrammerException("Routes can't be relative");
        }
        
        if ($route === '') {
            $route = '/';
        }
        
        // Convert backslashes to forward slashes
        $route = str_replace('\\', '/', $route);
        
        // Trim any trailing slashes
        $route_trimmed = rtrim($route, '/');
        if ($route_trimmed !== '') {
            $route = $route_trimmed;
        }
        
        // Get rid of double slashes
        $route = preg_replace('#//+#', '/', $route);
                
        return $route;
    }
    
    /**
     * Construct a new Route from a string or another Route object (copy
     * construct).
     * @param mixed route The route. 
     */
    public function __construct($route)
    {
        if (is_a($route, 'Route')) {
            // Copy construct
            $this->route = $route->get_route();
        } else {
            $this->route = self::normalize($route);
        }
    }
    
    /**
     * Factory method.
     */
    public static function factory($route = '')
    {
        return new Route($route);
    }
    
    /**
     * Split the given route string into its '/'-separated component segments.
     */
    protected function _split()
    {
        $route = $this->get_route();
        if ($route === '/') {
            $segments = array();
        } else {
            $segments = explode('/', trim($route, '/'));
        }
        
        return $segments;
    }
    
    /**
     * Return the route as a string.
     * @return string route The route.
     */
    public function get_route()
    {
        // Note: this method can't be named just 'route()' because it would
        // conflict with PHP's old constructor naming convention.
        return $this->route;
    }
    
    /**
     * Return the number of segments of this Route.
     */
    public function num_segments()
    {
        $route = $this->get_route();
        $num_segments = count($this->_split());
        return $num_segments;
    }
    
    /**
     * Return the i-th segment of the route.
     * @param int index The index (zero based) of the requested route segment.
     * @return string The index.
     * @throws NotFoundException
     */
    public function segment($index)
    {
        $segments = $this->_split();
        if (!isset($segments[$index])) {
            throw new NotFoundException("No such segment '%s'", $index);
        }
        return $segments[$index];
    }
    
    /**
     * Get the (normalized) array of user-specified reroutes.
     */
    protected static function _get_reroutes()
    {
        $reroutes = Roy::config('routes.routes', array());
        $reroutes_normal = array();
        
        // Normalize all
        foreach ($reroutes as $key => $value) {
            $key_normal = self::normalize($key);
            $reroutes_normal[$key_normal] = self::normalize($value);
        }
        
        return $reroutes_normal;
    }
    
    /**
     * Return the rerouted route (using the routes.php config file).
     */
    public function rerouted()
    {
        $reroutes = self::_get_reroutes();
        
        $route = $this->get_route();
        $rerouted = $route;
        
        foreach ($reroutes as $from => $to) {
            if (preg_match("#^{$from}$#", $route)) {
                $rerouted = preg_replace("#^{$from}$#", $to, $route);
                break;
            }
        }
        
        return new Route($rerouted);
    }
    
    /**
     * Return the controller indicated by this route.
     */
    public function controller()
    {
        return $this->segment(0);
    }
    
    /**
     * Return the action indicated by this route.
     */
    public function action()
    {
        try {
            $action = $this->segment(1);
        } catch (NotFoundException $e) {
            $action = 'index';
        }
        
        return $action;
    }
    
    /**
     * Return the parameters indicated by this route.
     */
    public function params()
    {
        $segments = $this->_split();
        
        if (count($segments) <= 2) {
            return array();
        }
        
        array_shift($segments);
        array_shift($segments);
        
        return $segments;
    }
}
