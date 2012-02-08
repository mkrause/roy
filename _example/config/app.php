<?php

return array(
    'shortname' => 'example',
    'longname' => 'My Example App',
    'base_urls' => array(
        'stylesheets' => Path::concat(Url::route('/'), 'scripts'),
        'scripts' => Path::concat(Url::route('/'), 'style'),
    ),
    'paths' => array(
        'session' => Path::concat(Roy::module('app'), 'tmp/session'),
    ),
    'db' => array(
        'connection_string' => null, //'mysql://[user]:[pass]@localhost/[db_name]',
    ),
);
