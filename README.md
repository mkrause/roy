
# Roy Framework for PHP

Roy is a minimal web framework written in PHP. Its aim is to provide a quick
start to a project by taking care of basic functionality without overly
limiting the developer in their flexibility.

The framework can provide an application with:

* Standard directory structure
* Class autoloading
* URL routing
* Error handling
* HTML templates

## Installation

Requires PHP 5.3+.

Download the latest source from GitHub:
https://github.com/mkrause/roy

On a production server, it is recommended to only place the contents of
the `public/` directory under your public document root. All other directories
should be placed somewhere not publicly accessible.

In a development environment, you can run the application from
`public/index.php` or just run it straight from `/` using the provided
`/.htaccess` file.

For a quick start, you can use the `_example/` directory as a stub application
by overwriting `app/`. This example app includes the Flourish and PHP
ActiveRecord libraries and some stub code. Otherwise, delete `_example/`.

## Upgrading

Download the latest source, and overwrite the files in the core `roy/`
directory with the new version.

## User Guide

Roy was written for a personal project because we wanted to use
[Flourish](http://flourishlib.com)'s excellent functionality but with an
MVC architecture. The framework attempts to alleviate the programmer
from having to worry about the very basics; error/exception handling, class
autoloading, URL routing, etc.

We believe that a collection of third-party libraries each focused on one
specific task is better than a large, monolithic framework. Each library can
then be swapped out at will if the need arises.

### Modules

The framework can be structured into separate modules, each with the same
basic directory structure:

    my_module/
        classes/        Used by the autoloader to load class definitions.
        config/         Configuration key-value pairs.
        strings/        Localization strings.
        thirdparty/     Third-party libraries.
        views/          Templates loaded via the View class.

Roy manages a list of modules ordered by priority. In the case of a naming
conflict, the class, config value, string, etc. in the higher-priority module
will be used. The standard module list is `["app/", "roy/"]`, where the former
takes priority over the latter. The `roy` core module will always have the
least priority. This allows the application to overwrite parts of the
framework such as classes or view files, without having to mess with the
core `roy/` module.

### Autoloader

Roy will set up a PHP autoloader that, when called, will search for a class
definition in the /classes directory of each module in priority order. Roy
will, by default, map class names to files as following:

    'MyClass' -> 'classes/myclass.php'
    'Book_Model' -> 'classes/model/book.php'
    'Users_Controller' -> 'classes/controller/users.php'
    'Users_Admin_Controller' -> 'classes/controller/admin/users.php'

### Configuration

Config files are stored in the `config/` directory of each module. Each
config file returns an array with key-value pairs. For example, we might have
the following config file:

    file: app/config/main.php
    <?php
    return array(
        'foo' => 'bar',
        'multi' => array(
            'level' => 42
        )
    );

This config value can be retrieved using:

    Roy::config('main.foo'); // Returns 'bar'
    Roy::config('main.multi.level'); // Returns 42

Again, in the case of a conflict, the config value in the module with the
highest priority is used.
