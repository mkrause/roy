<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

class View
{
    protected $_data = array();
    protected $_view;
    protected $_layout;
    
    public function __construct($view = null, $data = null)
    {
        if ($view) {
            $this->set_view($view);
        }
        
        if ($data) {
            $this->_data = $data;
        }
    }
    
    public static function factory($view = null, $data = null)
    {
        return new View($view, $data);
    }
    
    public function &__get($key)
    {
        if (!isset($this->_data[$key])) {
            throw new NotFoundException("View variable '%s' not found", $key);
        }
        
        return $this->_data[$key];
    }
    
    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }
    
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }
    
    public function __unset($key)
    {
        unset($this->_data[$key]);
    }
    
    public function data()
    {
        return $this->_data;
    }
    
    public function get_view()
    {
        if (!$this->_view) {
            throw new NotFoundException('No view file was set');
        }
        
        return $this->_view;
    }
    
    public function set_view($view)
    {
        if (is_a($view, 'View')) {
            $this->_view = $view->get_view();
            $this->_data = $view->data();
        } else {
            if (substr($view, -4) !== '.php') {
                $view .= '.php';
            }
            
            $view = Route::normalize($view);
            $this->_view = Path::concat('views', $view);
        }
    }
    
    public function layout()
    {
        if (!$this->_layout) {
            throw new NotFoundException('View does not have a layout');
        }
        
        return $this->_layout;
    }
    
    public function set_layout($layout)
    {
        if (empty($layout)) {
            $this->_layout = null;
        } else if (is_a($layout, 'View')) {
            $this->_layout = $layout;
        } else {
            $this->_layout = new View($layout);
        }
    }
    
    public function render($view_file = null, $render_layout = true)
    {
        if ($view_file) {
            $this->set_view($view_file);
        }
        
        if (!Roy::find_file($this->get_view())) {
            throw new NotFoundException("View file '%s' does not exist",
                $this->get_view());
        }
        
        ob_start();
        extract($this->_data);
        try {        
            require $this->get_view();
        } catch (Exception $e) {       
            throw new ProgrammerException("View '%s' could not be rendered",
                $this->get_view(), $e);
        }
        
        $output = ob_get_clean();
        
        if ($this->_layout !== null and $render_layout) {
            $this->_layout->content = $this;
            $this->_layout->content_output = $output;
            $output = $this->_layout->render();
        }
        
        return $output;
    }
}
