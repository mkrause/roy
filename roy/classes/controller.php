<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

class Controller
{
    public $request;
    
    /**
     * Constructor. Note: avoid overriding this constructor in subclasses,
     * use the before() callback instead.
     */
    public function __construct($request)
    {
        $this->request = $request;
    }
    
    public function request()
    {
        return $this->request;
    }
    
    public function route()
    {
        return $this->request()->route();
    }
    
    public function self()
    {
        return $this->request()->self();
    }
    
    public function invoke_action($action, $params)
    {
        try {
            $this->before();
            
            // Action method names are prefixed by 'action_' so as not to
            // expose other methods to the user.
            $action = 'action_' . $action;
            $content = call_user_func_array(array($this, $action), $params);
        } catch (InterruptedRequestException $e) {
            // Call the after callback, even when the request was interrupted
        }
        
        if (isset($content) and is_a($content, 'View')) {
            echo $content->render();
        }
        
        $this->after();
    }
    
    /**
     * Callback called just before running an action.
     */
    public function before() {}
    
    /**
     * Callback called after an action has returned.
     */
    public function after() {}
    
    public function __call($method, $args)
    {
        throw new PageNotFoundException("Method '%s' not found.", $method);
    }
}
