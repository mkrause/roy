<?php

return array(
    // One of: Roy::MODE_DEBUG, Roy::MODE_PRODUCTION.
    'mode' => Roy::MODE_DEBUG,
    
    // The output's character encoding. E.g. utf-8, iso-8859-1, etc.
    'encoding' => 'utf-8',
    
    // The language to use for text
    'language' => 'en-us',
    
    // Path to file to which errors should be logged.
    // Set to false to turn off error logging.
    // E.g. Path::concat(Roy::module('app'), 'tmp/logs/errors.log')
    'error_log_file' => Path::concat(Roy::module('app'), 'tmp/logs/errors.log'),
    
    // Base URL. For example, '/myprojects/myapp'. Used to generate
    // domain-relative URLs.
    //
    // Defaults to using dirname($_SERVER['SCRIPT_NAME']); change this if
    // you're using URL rewriting to alter the base url.
    'base_url' => null,
    
    // Paths to views used by Roy
    'views' => array(
        'debug' => array(
            'exception_layout' => '/roy/debug/layout.php',
            'exception' => '/roy/debug/exception.php',
            '404' => '/roy/debug/exception.php',
        ),
        'exception' => '/roy/exception.php',
        'exception_layout' => '/roy/layout.php',
        '404' => '/roy/404.php',
    ),
);
