<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

/**
 * This class represents an HTTP request for a certain resource, such as
 * an HTML page. Given a route as input it will locate the right action
 * (controller method) to invoke, and will make its output and HTTP headers
 * available to the caller.
 */
class Request
{
    /**
     * The Route object that indicates the location for this request.
     */
    protected $route;
    
    /**
     * The original route, unrerouted, as a string, that was passed to this
     * request.
     */
    protected $self;
    
    /**
     * The currently active Request, if any.
     */
    protected static $current = null;
    
    /**
     * The current Controller object, if we're currently running an action.
     */
    protected $controller = null;
    
    /**
     * List of HTTP headers that should be sent with this request.
     */
    protected $headers = array();
    
    /**
     * Constructor.
     * @param string route A route indicating where to dispatch to.
     */
    public function __construct($route)
    {
        $route = new Route($route);
        $this->route = $route->rerouted();
        $this->self = $route->get_route();
    }
    
    /**
     * Return the Route object corresponding to this Request.
     */
    public function route()
    {
        return $this->route;
    }
    
    /**
     * Return the original, unrerouted route that was passed to this request,
     * as a string.
     */
    public function self()
    {
        return $this->self;
    }
    
    /**
     * Return the current Request object.
     * @throws NotFoundException
     */
    public static function current()
    {
        $current = self::$current;
        
        if (!$current) {
            throw new NotFoundException('There is currently no active'
                . ' request.');
        }
        
        return $current;
    }
    
    /**
     * Return the current Controller object, if we're currently running an
     * action, or null otherwise.
     */
    public function controller()
    {
        $controller = $this->controller;
        
        if (!$controller) {
            throw new NotFoundException('There is no controller '
                . 'currently linked to this request.');
        }
        
        return $controller;
    }
    
    /**
     * Send the HTTP headers to redirect to the given location.
     * 
     * @param string location The URL to redirect to.
     * @param bool interrupt Whether or not to interrupt the current
     *     request.
     * @throws InterruptedRequestException
     */
    public function redirect($location, $interrupt = true)
    {
        if (is_a($location, 'Route')) {
            $base = Roy::base_url();
            $location = Path::concat($base, $location->get_route());
        }
        
        if (!headers_sent()) {
            header('HTTP/1.1 303 See Other');
            header('Location: ' . $location);
        }
        
        if ($interrupt) {
            // Brake off the current request
            throw new InterruptedRequestException('Redirecting');
        }
    }
    
    /**
     * Return the HTTP request method used (lowercase).
     */
    public static function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
    
    /**
     * True if the HTTP method is GET.
     */
    public static function is_get()
    {
        return self::method() === 'get';
    }
    
    /**
     * True if the HTTP method is POST.
     */
    public static function is_post()
    {
        return self::method() === 'post';
    }
    
    /**
     * Save the given list as headers.
     */
    private function set_headers($headers)
    {
        $this->headers = $headers;
    }
    
    /**
     * Send all headers that were collected during this request.
     */
    public function send_headers()
    {
        if (headers_sent()) {
            return;
        }
        
        foreach ($this->headers as $header) {
            header($header);
        }
    }
    
    private function get_output()
    {
        $route = $this->route;
        try {
            $controller_class = ucfirst($route->controller()) . '_Controller';
        } catch (NotFoundException $e) {
            $controller_class = null;
        }
        
        if (!is_string($controller_class) or Roy::find_class($controller_class)
            === false) {
            throw new PageNotFoundException("Could not find a controller" .
                " matching '%s'", $controller_class);
        }
        
        $controller = new $controller_class($this);
        
        $action = $route->action();
        
        $params = $route->params();
        
        // Begin
        self::$current = $this;
        $this->controller = $controller;
        
        ob_start();
        
        // Call invoke_action(), so that the application itself can have
        // control over this process by overriding invoke_action(). Example:
        // so that apps can catch exceptions thrown in actions.
        $controller->invoke_action($action, $params);
        
        $output = ob_get_clean();
        
        // End
        self::$current = null;
        $this->controller = null;
        
        return $output;
    }
    
    /**
     * Clear any buffered output.
     */
    public function clear_output()
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
    
    /**
     * Dispatch to a controller and return its output.
     */
    public function dispatch()
    {
        try {
            $encoding = Roy::config('main.encoding');
        } catch (NotFoundException $e) {
            $encoding = 'utf-8';
        }
        
        if (!headers_sent()) {
            // Some default headers
            header('Content-Type: text/html; charset=' . $encoding);
        }
        
        return $this->get_output();
    }
    
    /**
     * Same as dispatch(), but just return the output and ignore any headers
     * set in the process.
     */
    public function output()
    {
        // Not much we can do if the headers were already sent
        if (headers_sent()) {
            return $this->get_output();
        }
        
        // Save the current headers
        $old_headers = headers_list();
        
        // Some default headers
        try {
            $encoding = Roy::config('main.encoding');
        } catch (NotFoundException $e) {
            $encoding = 'utf-8';
        }
        
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=' . $encoding);
        }
        
        $output = $this->get_output();
        
        // Collect all headers that were sent in the process
        $this->set_headers(headers_list());
        
        if (!headers_sent()) {
            // Restore the old headers
            header_remove();
            foreach ($old_headers as $header) {
                header($header);
            }
        }
        
        return $output;
    }
}
