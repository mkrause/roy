<?php

//====================
// Set up Flourish
//====================

spl_autoload_register(function($class_name) {
    if ($class_name[0] === 'f') {
        $file = Roy::find_file('thirdparty/flourish/' . $class_name . '.php');
        if ($file !== false and file_exists($file)) {
            require $file;
        }
    }
}, true, true);

fSession::setPath(Roy::config('forecasting.paths.session'));
fSession::setLength('2 hour');

//===========================
// Set up PHP ActiveRecord
//===========================

require_once('thirdparty/php-activerecord/ActiveRecord.php');

ActiveRecord\Config::initialize(function($cfg)
{
    $conn_string = Roy::config('example.db.connection_string');
    
    $model_dir = Path::concat(Roy::module('app'), 'classes/model');
    $cfg->set_model_directory($model_dir);
    $cfg->set_connections(array(
        'development' => $conn_string,
    ));
    
    // Use UTF8 as the connection's character set
    $conn = ActiveRecord\ConnectionManager::get_connection();
    $conn->query("SET NAMES utf8");
    
    //$cfg->set_logging(true);
    //$cfg->set_logger(new ARLogger());
});
