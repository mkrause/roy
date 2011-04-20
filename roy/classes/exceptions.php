<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

/**
 * General class of exceptions related to Roy.
 */
class RoyException extends Exception
{
    /**
     * Constructor. Takes a message string, optionally formatted printf-style
     * with any extra arguments. If the last argument is an Exception, it will
     * be used as the previous exception in the exception chain.
     */
    public function __construct($message = "")
    {
        // Get the rest of the arguments (sans $message)
        $args = func_get_args();
        array_shift($args);
        
        // If the last argument is an Exception, use it as the previous
        // exception in the exception chain
        $previous = null;
        if (count($args) > 0 and is_a($args[count($args)-1], 'Exception')) {
            $previous = array_pop($args);
        }
        
        // Format the message if extra arguments were given
        //TODO: allow localization, perhaps some security hooks
        if (count($args) > 0) {
            $message = vsprintf($message, $args);
        }
        
        parent::__construct($message, 0, $previous);
    }
    
    public function headers()
    {
        header("HTTP/1.1 500 Internal Server Error");
    }
    
    /**
     * In debug mode, show some technical diagnostics.
     */
    protected function _render_debug($path_layout, $path_content)
    {
        $layout = new View($path_layout);
        $content = new View($path_content);
        $content->set_layout($layout);
        
        $exceptions = array();
        $e = $this;
        do {
            $info = array();
            $info['class'] = get_class($e);
            $info['message'] = $e->getMessage();
            $info['code'] = $e->getCode();
            $info['file'] = $e->getFile();
            $info['line'] = $e->getLine();
            $info['trace'] = $e->getTraceAsString();
            
            $exceptions[] = $info;
        } while ($e = $e->getPrevious());
        
        $content->exceptions = $exceptions;
        
        return $content->render();
    }
    
    /**
     * In production mode, show a user-friendly but technically opaque
     * error message.
     */
    protected function _render_production($path_layout, $path_content)
    {
        $layout = new View($path_layout);
        
        $content = new View($path_content);
        $content->set_layout($layout);
        $content->message = 'The page could not be loaded.';
        
        return $content->render();
    }
    
    public function render()
    {
        $output = '';
        if (Roy::mode() === Roy::MODE_DEBUG) {
            $output .= $this->_render_debug(
                Roy::config('main.views.debug.exception_layout'),
                Roy::config('main.views.debug.exception')
            );
        } else {
            $output .= $this->_render_production(
                Roy::config('main.views.exception_layout'),
                Roy::config('main.views.exception')
            );
        }
        return $output;
    }
}

// An exception expected to be handled in application code
class ExpectedException extends RoyException {}

// An exception NOT expected to be handled in application code
class UnexpectedException extends RoyException {}

// An exception indicating the sought item does not exist
class NotFoundException extends ExpectedException {}

// An exception a validation error
class ValidationException extends ExpectedException {}

// An exception indicating a programmer's mistake, e.g. wrong parameters were
// given to a function
class ProgrammerException extends UnexpectedException {}

// An exception indicating a failure of the environment (e.g. network failure)
class EnvironmentException extends UnexpectedException {}

// An exception indicating a user's mistake, e.g. an invalid request
class UserException extends UnexpectedException {}

// An exception indicating that a request should be broken off prematurely
class InterruptedRequestException extends UnexpectedException {}

// An exception indicating a 404 response should be sent
class PageNotFoundException extends UnexpectedException
{
    public function headers()
    {
        parent::headers();
        header("HTTP/1.1 404 Not Found");
    }
    
    public function render()
    {
        $output = '';
        if (Roy::mode() === Roy::MODE_DEBUG) {
            $output .= $this->_render_debug(
                Roy::config('main.views.debug.exception_layout'),
                Roy::config('main.views.debug.404')
            );
        } else {
            $output .= $this->_render_production(
                Roy::config('main.views.exception_layout'),
                Roy::config('main.views.404')
            );
        }
        return $output;
    }
}
