Roy PHP Framework v0.4
Copyright 2011 Maikel Krause (maikelkrause.com).
Licensed under the MIT license.

https://github.com/mkrause/roy
Requires PHP 5.3+.

A minimal web framework, providing the structure and basic elements of a
web application without the complexity of a more feature-rich framework. This
framework is meant for projects that need a quick start and full control.

Roy aims to be:
- Minimal (bring your own libraries)
- Modular
- Transparent
- Easy to use

# Installation

First, download the source from https://github.com/mkrause/roy.

If you want a quick start, use the /_example application module: delete /app
and rename /_example to /app. This app includes the Flourish and PHP
ActiveRecord libraries and some stub code. Otherwise, delete /_example and
use /app.

To run in production, make sure to set the main.mode configuration value to
Roy::MODE_PRODUCTION. Place the contents of /public in your public_html
directory and all other directories elsewhere on the server. Update the file
paths in public/index.php if necessary.

# Upgrading

Download the latest source, then overwrite the /roy directory with the new
version. All other directories contain your custom code and generally won't
need updates.

# Modules

The framework (Roy) is structured into separate modules, each with the same
directory structure:

    my_module/
        classes/
        config/
        strings/
        thirdparty/
        etc.

The module list is ordered by priority, so a class/configuration/string in a
higher-priority module is used in case of a conflict. The standard module
list is ["app", "roy"], where the former takes priority over the latter. The
"roy" core module will always have the least priority.

# Autoloader

If enabled (via Roy::enable_autoload() in index.php), Roy will set up an
PHP autoloader that, when called, will search for a class definition in the
/classes directory of each module in priority order. Roy will, by default,
map class names to files as following:

'MyClass' => 'classes/myclass.php'
'Users_Controller' => 'classes/controller/users.php'

# Configuration

Configuration files are stored in the /config directory of each module. Each
config file returns an array with key-value pairs. For example, we might have
the following config file:

    file: app/config/main.php
    <?php
    return array(
        'foo' => 'bar',
    );

This config value can be retrieved using:

    Roy::config('main.foo'); // Returns 'bar'

Again, in the case of a conflict, the config value in the module with the
highest priority is used.
