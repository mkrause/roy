Roy PHP Framework v0.4  
Copyright (c) 2011 Maikel Krause (maikelkrause.com).  
Licensed under the MIT license.

Requires PHP 5.3+.

A minimal web framework, providing the structure and basic elements of a web
application, without the complexity of a more feature-rich framework. This
framework is meant for projects that need a quick start and full flexibility.

Roy aims to be:

* Minimal (bring your own libraries)
* Modular
* Transparent
* Easy to use

# Installation

Download the source:
https://github.com/mkrause/roy

On a production server, it is recommended to only place the contents of
the `public/` directory in your public_html. All other directories should be
placed somewhere not publicly accessible. Update the paths in `index.php` if
necessary.

In a development environment, you can run the application from
`public/index.php` or just run it straight from `/` using the built-in
`/.htaccess` file.

For a quick start, you can use the `_example/` directory as a stub application
by overwriting `app/`. This example app includes the Flourish and PHP
ActiveRecord libraries and some stub code. Otherwise, delete `_example/`.

# Upgrading

Download the latest source, then overwrite the `roy/` directory with the new
version. All other directories generally won't need to updated.

# User Guide

Roy was written for a personal project because we wanted to use
[Flourish](http://flourishlib.com)'s excellent functionality but with an
(H)MVC architecture. The framework attempts to alleviate the programmer
from having to worry about the very basics; error/exception handling, class
autoloading, URL routing, etc.

We believe that a collection of third-party libraries each focused on one
specific task is better than a large, complex framework. Each library can
then be swapped out at will if the need arises.

## Modules

The framework is structured into separate modules, each with the same basic
directory structure:

    my_module/
        classes/        Used by the autoloader to load class definitions.
        config/         Configuration key-value pairs.
        strings/        Localization strings.
        thirdparty/     Third-party libraries.
        views/          Used by the View class to loads templates.

Roy manages a list of modules ordered by priority. In the case of a naming
conflict, the class, config value, string, etc. in the higher-priority module
will be used. The standard module list is `[app, roy]`, where the former
takes priority over the latter. The `roy` core module will always have the
least priority. This allows the application to overwrite parts of the
framework such as classes or view files, without having to mess with the
core `roy/` module.

## Autoloader

If enabled (via `Roy::enable_autoload()` in index.php), Roy will set up a
PHP autoloader that, when called, will search for a class definition in the
/classes directory of each module in priority order. Roy will, by default,
map class names to files as following:

    'MyClass' -> 'classes/myclass.php'
    'Book_Model' -> 'classes/model/book.php'
    'Users_Controller' -> 'classes/controller/users.php'
    'Users_Admin_Controller' -> 'classes/controller/admin/users.php'

## Configuration

Config files are stored in the `config/` directory of each module. Each
config file returns an array with key-value pairs. For example, we might have
the following config file:

    file: app/config/main.php
    <?php
    return array(
        'foo' => 'bar',
        'multi' => array(
            'level' => 42,
        ),
    );

This config value can be retrieved using:

    Roy::config('main.foo'); // Returns 'bar'
    Roy::config('main.multi.level'); // Returns 42

Again, in the case of a conflict, the config value in the module with the
highest priority is used.
