<?php

return array(
    /**
     * Used by the autoloader to determine the class definition file of the
     * class with the name $class_name.
     */
    'class_name_to_path' => function($class_name) {
        // Examples:
        // Foo -> foo.php
        // Users_Admin_Controller -> /controller/admin/users.php
        
        $segments = explode('_', $class_name);
        $segments_reverse = array_reverse($segments);
        
        $class_path = strtolower(implode('/', $segments_reverse)) . '.php';
        return $class_path;
    },
);
