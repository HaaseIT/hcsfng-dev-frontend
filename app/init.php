<?php

/*
    HCSF - A multilingual CMS and Shopsystem
    Copyright (C) 2014  Marcus Haase - mail@marcus.haase.name

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

ini_set('display_errors', 0);
ini_set('xdebug.overload_var_dump', 0);
ini_set('xdebug.var_display_max_depth', 10);
ini_set('html_errors', 0);
error_reporting(E_ALL);
//error_reporting(0);

mb_internal_encoding('UTF-8');
header("Content-Type: text/html; charset=UTF-8");

if (ini_get('session.auto_start') == 1) {
    die('Please disable session.autostart for this to work.');
}

require_once __DIR__.'/../vendor/autoload.php';

$container = new Pimple\Container();

$AuraLoader = new \Aura\Autoload\Loader;
$AuraLoader->register();
$AuraLoader->addPrefix('\HaaseIT\HCSFNG\Frontend', __DIR__.'/../src');

// PSR-7 Stuff
// Init request object
$container['request'] = \Zend\Diactoros\ServerRequestFactory::fromGlobals();

// cleanup request
$requesturi = urldecode($container['request']->getRequestTarget());
$parsedrequesturi = \substr($requesturi, \strlen(\dirname($_SERVER['PHP_SELF'])));
if (substr($parsedrequesturi, 1, 1) != '/') {
    $parsedrequesturi = '/'.$parsedrequesturi;
}
$container['request'] = $container['request']->withRequestTarget($parsedrequesturi);

use Symfony\Component\Yaml\Yaml;
$container['conf'] = function ($c) {
    $conf = Yaml::parse(file_get_contents(__DIR__.'/config/core.dist.yml'));
    if (is_file(__DIR__.'/config/core.yml')) $conf = array_merge($conf, Yaml::parse(file_get_contents(__DIR__.'/config/core.yml')));
    //$conf = array_merge($conf, Yaml::parse(file_get_contents(__DIR__.'/config/config.countries.yml')));
    //$conf = array_merge($conf, Yaml::parse(file_get_contents(__DIR__.'/config/config.scrts.yml')));
    $conf['directory_images'] = trim($conf['directory_images'], " \t\n\r\0\x0B/"); // trim this

    if (!empty($conf['maintenancemode']) && $conf['maintenancemode']) {
        $conf["templatecache_enable"] = false;
        $conf["debug"] = false;
        $conf['textcatsverbose'] = false;
    } else {
        $conf['maintenancemode'] = false;
    }

    return $conf;
};

require_once __DIR__.'/config/constants.php';

// ----------------------------------------------------------------------------
// Begin Twig loading and init
// ----------------------------------------------------------------------------

$container['twig'] = function ($c) {
    // todo: dynamic environment
    $loader = new Twig_Loader_Filesystem([__DIR__ . '/../repository/']);
    $twig_options = [
        'autoescape' => false,
        'debug' => (isset($c['conf']["debug"]) && $c['conf']["debug"] ? true : false)
    ];
    if (isset($c['conf']["templatecache_enable"]) && $c['conf']["templatecache_enable"] &&
        is_dir(PATH_TEMPLATECACHE) && is_writable(PATH_TEMPLATECACHE)) {
        $twig_options["cache"] = PATH_TEMPLATECACHE;
    }
    $twig = new Twig_Environment($loader, $twig_options);

    if ($c['conf']['allow_parsing_of_page_content']) {
        $twig->addExtension(new Twig_Extension_StringLoader());
    } else { // make sure, template_from_string is callable
        $twig->addFunction('template_from_string', new Twig_Function_Function('\HaaseIT\HCSF\Helper::reachThrough'));
    }

    if (isset($c['conf']["debug"]) && $c['conf']["debug"]) {
        //$twig->addExtension(new Twig_Extension_Debug());
    }
    //$twig->addFunction('T', new Twig_Function_Function('$c[\'textcats\']->T'));
    //$twig->addFunction(new Twig_SimpleFunction('T', [$c['textcats'], 'T']));

    return $twig;
};
