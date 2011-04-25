<?php
/*
 * Roy PHP Framework
 * Copyright 2011 Maikel Krause (maikelkrause.com).
 * Licensed under the MIT license.
 */

// Explicitly load all built-in exceptions (skip the autoloader)
require_once 'exceptions.php';

// The autoloader depends on the Path class, so explicitly include it
require_once 'path.php';

/**
 * Short-hand function for Roy::string().
 */
function str() {
    $args = func_get_args();
    return call_user_func_array('Roy::string', $args);
}

/**
 * High level functionality pertaining to the framework itself and the global
 * PHP/server environment (error/exception handling, class autoloading,
 * configuration, etc.)
 */
class Roy
{
    const VERSION = '0.4';
    
    /**
     * All (paths to) modules. Modules are directories containing class
     * definitions, view files, etc. The autoloader will automatically search
     * these modules for class definition files.
     */
    static public $_modules = array();
    
    const MODE_PRODUCTION = 'production';
    const MODE_DEBUG = 'debug';
    
    /**
     * Current 'mode' (production or debug).
     */
    static public $_mode = self::MODE_DEBUG;
    
    /**
     * Feature flags.
     */
    static public $_autoload_enabled = false;
    static public $_error_handling_enabled = false;
    
    /**
     * Use a private constructor to force use as a static class.
     */
    private function __construct() {}
    
    /**
     * Initialize the framework.
     * 
     * @param callback A callback giving the application the opportunity to
     *     configure the framework (add modules, enable autoloading, etc.)
     *     after essential bootstrapping but before standard initialisation.
     */
    public static function init($init_callback = null)
    {
        if (version_compare(PHP_VERSION, '5.3', '<')) {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Error: Roy requires PHP 5.3 or newer.';
            exit;
        }
        
        // Add the Roy module (the module containing this file)
        $path_roy = dirname(dirname(__FILE__));
        self::add_module($path_roy, 'roy');
        
        if (is_callable($init_callback)) {
            $init_callback();
        }
        
        try {
            $mode = self::config('main.mode');
            self::set_mode($mode);
        } catch (NotFoundException $e) {
            // Leave mode as is
        }
    }
    
    /**
     * Reset everything to its initial state. Useful for testing.
     */
    public static function reset()
    {
        self::$_modules = array();
        self::$_mode = self::MODE_DEBUG;
        
        self::disable_autoload();
        self::disable_error_handling();
        
        self::$_autoload_enabled = false;
        self::$_error_handling_enabled = false;
    }
    
    /**
     * Enable the class autoloader.
     */
    public static function enable_autoload()
    {
        if (!self::autoload_enabled()) {
            spl_autoload_register('Roy::autoload');
            self::$_autoload_enabled = true;
        }
    }
    
    /**
     * Disable the class autoloader.
     */
    public static function disable_autoload()
    {
        if (self::autoload_enabled()) {
            spl_autoload_unregister('Roy::autoload');
            self::$_autoload_enabled = false;
        }
    }
    
    /**
     * Return whether or not the autoloader is enabled.
     * 
     * @return bool True if the autoloader is enabled.
     */
    public static function autoload_enabled()
    {
        return self::$_autoload_enabled;
    }
    
    /**
     * Enable error and exception handling.
     */
    public static function enable_error_handling()
    {
        if (!self::error_handling_enabled()) {
            set_error_handler('Roy::handle_error', E_ALL);
            set_exception_handler('Roy::handle_exception');
            self::$_error_handling_enabled = true;
        }
    }
    
    /**
     * Disable error and exception handling.
     */
    public static function disable_error_handling()
    {
        if (self::error_handling_enabled()) {
            restore_error_handler();
            restore_exception_handler();
            self::$_error_handling_enabled = false;
        }
    }
    
    /**
     * Return whether or not error handling is enabled.
     * 
     * @return bool True if error handling is enabled.
     */
    public static function error_handling_enabled()
    {
        return self::$_error_handling_enabled;
    }
    
    /**
     * Current mode.
     * 
     * @return string The current mode.
     */
    public static function mode()
    {
        return self::$_mode;
    }
    
    /**
     * Set the debugging mode. Possible values:
     * - Roy::MODE_PRODUCTION
     * - Roy::MODE_DEBUG
     * 
     * @param string mode The mode.
     * @throws ProgrammerException
     */
    public static function set_mode($mode)
    {
        if ($mode !== self::MODE_PRODUCTION and
            $mode !== self::MODE_DEBUG) {
            throw new ProgrammerException(str('roy.invalid-mode', $mode));
        }
        
        /*
        //XXX probably better to leave error_reporting as is and let the
        // error handler decide whether or not to display it
        if ($mode === self::MODE_PRODUCTION) {
            error_reporting(0);
        } else {
            error_reporting(E_ALL);
        }
        */
        
        self::$_mode = $mode;
    }
    
    /**
     * Helper method. Build an include path with all modules, ordered by
     * priority from high to low.
     * 
     * @return string The include path.
     */
    public static function _build_include_path()
    {
        $module_paths = self::modules();
        
        // Collect all other paths that were already in the include path
        $include_paths = explode(PATH_SEPARATOR, get_include_path());
        $misc_paths = array();
        foreach ($include_paths as $path) {
            // Note the ===, array_search returns an int (the found index)
            // on success
            $not_module = (array_search($path, $module_paths) === false);
            if (!empty($path) and $not_module) {
                $misc_paths[] = $path;
            }
        }
        
        // Join the two arrays by path separators to form an include path
        $misc_paths_str = implode(PATH_SEPARATOR, $misc_paths);
        $module_paths_str = implode(PATH_SEPARATOR, $module_paths);
        $include_path = $misc_paths_str . PATH_SEPARATOR . $module_paths_str;
        
        return $include_path;
    }
    
    /**
     * Return the module with the specified key.
     * 
     * @param string key The module key.
     * @return The path to the module with the given key.
     * @throws NotFoundException
     */
    public static function module($key)
    {
        if (!isset(self::$_modules[$key])) {
            throw new NotFoundException("Module '%s' not found", $key);
        }
        return self::$_modules[$key];
    }
    
    /**
     * Return the list of all modules.
     *
     * @return array The module array.
     */
    public static function modules()
    {
        return self::$_modules;
    }
    
    /**
     * Add a new module to the module list and include path. Modules that are
     * added sooner get priority over those that are added later.
     * 
     * @param string Path to the module directory.
     * @param string Key used to index the module.
     */
    public static function add_module($path_in, $key = null)
    {
        $path = realpath($path_in);
        
        if ($path === false) {
            throw new ProgrammerException(str('roy.no-such-module-directory', 
                $path));
        }
        
        if (!$key) {
            // Use the directory name as the default module key, but only
            // if we're not overriding an existing module
            $directory = basename($path);
            if (!isset(self::$_modules[$directory])) {
                $key = basename($path);
            } else {
                $key = count(self::$_modules);
            }
        }
        
        $key = (string)$key;
        
        $modules = self::$_modules;
        $modules[$key] = $path;
        
        // Re-add the Roy module to give it the lowest priority
        if (isset(self::$_modules['roy'])) {
            unset($modules['roy']);
            $modules['roy'] = self::$_modules['roy'];
        }
        
        self::$_modules = $modules;
        set_include_path(self::_build_include_path());
    }
    
    /**
     * Search all modules for the given file.
     * 
     * @param string file Module-relative path to the file.
     * @return Absolute path to the sought file, or false if not found.
     */
    public static function find_file($file)
    {
        $modules = self::modules();
        $found_path = false;
        
        foreach ($modules as $module) {
            $path = Path::concat($module, $file);
            if (file_exists($path)) {
                $found_path = $path;
                break;
            }
        }
        
        return $found_path;
    }
    
    /**
     * Helper method. Map the given class name to a module-relative path where
     * the respective class definition might be found.
     * 
     * @param string class_name The name of the class for which we want a
     *     definition file.
     * @return The path of the respective class definition.
     * @throws ProgrammerException
     */
    public static function class_name_to_path($class_name)
    {
        // If an app-defined callback is given, use that
        try {
            $callback = self::config('callbacks.class_name_to_path');
        } catch (NotFoundException $e) {
            throw new ProgrammerException(str('roy.no-such-callback',
                'callbacks.class_name_to_path'), $e);
        }
        
        if (!is_callable($callback)) {
            throw new ProgrammerException(str('roy.invalid-callback'));
        }
        
        return $callback($class_name);
    }
    
    /**
     * Search all modules for a class definition file for the given class
     * name. Uses the the user-configurable 'callbacks.class_name_to_path'
     * callback function to determine how to map class names to paths.
     * 
     * @param string class_name The name of the class for which we want to
     *     find the definition file.
     * @return Path to the sought file, or false if it could not be found.
     */
    public static function find_class($class_name)
    {
        $class_path = self::class_name_to_path($class_name);
        $search_path = Path::concat('classes', $class_path);
        
        return self::find_file($search_path);
    }
    
    /**
     * Attempt to load the class definition for the given class name.
     * 
     * @param string class_name The name of the class to autoload.
     * @throws NotFoundException
     */
    public static function autoload($class_name)
    {    
        $file = self::find_class($class_name);
        
        if (!$file) {
            throw new NotFoundException(str('roy.no-such-class',
                $class_name));
        }
        require_once $file;
        
        if ($file !== false) {
            require_once $file;
        }
    }
    
    /**
     * Error handler.
     * 
     * @param enum level Error level; one of E_NOTICE, E_WARNING, etc.
     * @param string message Error message.
     * @param string file Source file in which the error occured.
     * @param int line Line in $file on which the error occured.
     * @return bool Whether or not to run the internal PHP error handler.
     */
    public static function handle_error($level, $message, $file, $line)
    {
        // Throw an exception for all (recoverable) fatal errors
        if ($level === E_USER_ERROR or $level === E_RECOVERABLE_ERROR) {
            $error_exception = new ErrorException($message, 0, $level, $file,
                $line);
            
            // Due to a limitation in PHP, we can't throw an exception from
            // a __toString method, so we need to call the exception handler
            // explicitly
            $trace = debug_backtrace();
            if (isset($trace[2]['class'])
                    and $trace[2]['function'] === '__toString') {
                self::handle_exception($error_exception);
                return;
            }
            
            throw $error_exception;
        }
        
        // Log non-fatal errors and continue execution; also output the error
        // message if we're running in debug mode and error_reporting is on
        // for this error type
        
        // Don't log or display error if error_reporting is off for this type
        //XXX use seperate values for reporting level and logging level?
        if (!(error_reporting() & $level)) {
            return true;
        }
        
        // Reverse engineer the name of the error type from the value
        // of $level
        $type = 'ERROR';
        if ($level === E_WARNING) {
            $type = 'E_WARNING';
        } elseif ($level === E_NOTICE) {
            $type = 'E_NOTICE';
        }
        
        if (self::mode() === self::MODE_DEBUG) {
            echo "<b>$type [$level]</b> $message in $file on line"
                . " $line.<br>\n";
        }
        
        try {
            $log_file = self::config('main.error_log_file');
        } catch (NotFoundException $e) {
            $log_file = false;
        }
        
        if ($log_file) {
            if (!file_exists($log_file)) {
                touch($log_file);
            }
            
            $handle = fopen($log_file, 'a+');
            $datetime = date('Y-m-d H:i:s');
            $log = "[$datetime] $type [$level] $message in $file on line"
                . " $line\n";
            fwrite($handle, $log);
            fclose($handle);
        }
        
        // Don't run PHP's internal error handler
        return true;
    }
    
    /**
     * Helper method. Clear any buffered output.
     */
    public static function _clear_buffered()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
    
    /**
     * Exception handler.
     * 
     * @param Exception exception The exception object to handle.
     */
    public static function handle_exception($exception)
    {
        // Clear any buffered output so we can render a complete exception
        // notification page
        self::_clear_buffered();
        
        try {
            $roy_exception = $exception;
            if (!is_a($exception, 'RoyException')) {
                $roy_exception = new RoyException('Unknown exception',
                    $exception);
            }
            
            throw new Exception('boooo');
            
            if (!headers_sent()) {
                $roy_exception->headers();
            }
            echo $roy_exception->render();
        } catch (Exception $e) {
            // Oh boy. An exception was thrown while trying to display
            // the exception page...
            self::_clear_buffered();
            
            if (self::mode() === self::MODE_DEBUG) {
                echo "<p><b>An error occured while trying to display the"
                    . " page:</b><br>";
                echo "[" . get_class($exception) . "] ";
                echo $exception->getMessage();
                echo "</p>";
                echo "<p><b>Additionally, an error occurred while"
                    . " trying to display the error page:</b><br>\n";
                echo "[" . get_class($e) . "] ";
                echo $e->getMessage();
                echo "</p>";
            } else {
                echo "An error occured while trying to display the page.";
            }
        }
        
        // Log the exception
        try {
            $log_file = self::config('main.error_log_file');
        } catch (NotFoundException $e) {
            $log_file = false;
        }
        
        if ($log_file) {
            if (!file_exists($log_file)) {
                touch($log_file);
            }
            
            $handle = fopen($log_file, 'a+');
            $datetime = date('Y-m-d H:i:s');
            $log = "[$datetime] Exception of type " . get_class($exception)
                . ": " . $exception->getMessage() . "\n"
                . $exception->getTraceAsString() . "\n";
            fwrite($handle, $log);
            fclose($handle);
        }
        
        exit;
    }
    
    /**
     * Return the base URL (domain-relative base, e.g. /my/project) for the
     * current application.
     * 
     * @return string The base URL.
     */
    public static function base_url()
    {
        $base = '';
        
        try {
            $base = Path::normalize(self::config('main.base_url'));
            
            if (empty($base)) {
                throw new NotFoundException('No base URL configured');
            }
        } catch (NotFoundException $e) {
            // If no configured value, fall back using SCRIPT_NAME
            $base = dirname($_SERVER['SCRIPT_NAME']);
        }
        
        return $base;
    }
    
    /**
     * Helper method: get the right item in an array specified
     * by an array of indices.
     * 
     * @param array config The array.
     * @param array indices Array of indices specifying the item.
     * @return The item.
     * @throws NotFoundException
     */
    public static function _get_array_item($arr, $indices_in)
    {
        $item = $arr;
        $indices = unserialize(serialize($indices_in)); // Hard copy
        
        // Drill down in to this array as specified by the indices
        while (count($indices) > 0) {
            $index = array_shift($indices);
            
            // Note: don't use isset(), as it treats explicit null-values as
            // not set
            if (!array_key_exists($index, $item)) {
                throw new NotFoundException("Item not found");
            }
            
            $item = $item[$index];
        }
        
        return $item;
    }
    
    /**
     * Retrieve a config item, specified by a key like
     * "config_file.index1.index2", which refers to the item in config file
     * "config/config_file.php" with array index [index1][index2].
     * 
     * @param string key The config key.
     * @param mixed default Optionally, a default value to return when no
     *     config item was found.
     * @return The config item.
     * @throws NotFoundException
     */
    public static function config($key, $default = null)
    {
        $parts = explode('.', $key);
        $file_name = array_shift($parts);
        $config_path = "config/{$file_name}.php";
        
        // Find a config file with the given key
        $modules = self::modules();
        $config_found = false;
        $config_value = null;
        
        foreach ($modules as $module) {
            $config_file = Path::concat($module, $config_path);
            if (file_exists($config_file)) {
                $config_values = include $config_file;
                try {
                    $config_value = self::_get_array_item($config_values,
                        $parts);
                    $config_found = true;
                    break;
                } catch (NotFoundException $e) {
                    // Continue iterating
                }
            }
        }
        
        if ($config_found === false) {
            // Note: we use func_num_args so that we can differentiate between
            // config('key') and config('key', null)
            if (func_num_args() > 1) {
                $config_value = $default;
            } else {
                throw new NotFoundException(str('roy.config-item-not-found',
                    $key));
            }
        }
        
        return $config_value;
    }
    
    /**
     * Retrieve a string in the given language.
     * 
     * @param string key Key (e.g. "myapp.error-message") of the string.
     * @param string lang Language tag for the language of the string.
     * @param args Parameter values to fill in the string.
     * @return The found string value.
     * @throws NotFoundException
     */
    public static function _string_in_language($key, $lang, $args)
    {
        $parts = explode('.', $key);
        $file_name = array_shift($parts);
        $path = "strings/{$lang}/{$file_name}.php";
        
        $modules = self::modules();
        $found = false;
        
        foreach ($modules as $module) {
            $file = Path::concat($module, $path);
            if (file_exists($file)) {
                $values = include $file;
                try {
                    $found = self::_get_array_item($values, $parts);
                    break;
                } catch (NotFoundException $e) {
                    // Continue iterating
                }
            }
        }
        
        if ($found === false) {
            throw new NotFoundException('No such string in the given lang.');
        }
        
        return $found;
    }
    
    /**
     * Retrieve a language string.
     * 
     * @param string key Key (e.g. "myapp.error-message") of the string.
     * @param [args...] Parameter values to fill in the string.
     * @return The specified string value.
     * @throws NotFoundException
     */
    public static function string($key)
    {
        $lang = self::config('main.language', 'en-us');
        $args = func_get_args();
        array_shift($args);
        
        try {
            $string = self::_string_in_language($key, $lang, $args);
        } catch (NotFoundException $e) {
            // Try falling back to english
            try {
                $string = self::_string_in_language($key, 'en-us', $args);
            } catch (NotFoundException $e) {
                //XXX avoid an infinite loop here by not using Roy::string()
                // inside Roy::string()
                throw new NotFoundException("Could not find a string"
                    . " for '%s'", $key);
            }
        }
        
        $text = vsprintf($string, $args);
        return $text;
    }
}
