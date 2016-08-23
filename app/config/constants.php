<?php

const DIRNAME_REPOSITORY = 'repository';
const DIRNAME_TEMPLATECACHE = 'templates';
const DIRNAME_CACHE = 'cache';
const DIRNAME_DOCROOT = 'web';
const DIRNAME_LOGS = 'logs';
const DIRNAME_TEXTCATS = 'textcats';
const DIRNAME_PAGES = 'pages';

define('PATH_BASEDIR', __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
define('PATH_DOCROOT', PATH_BASEDIR.DIRNAME_DOCROOT.DIRECTORY_SEPARATOR);
define('PATH_REPOSITORY', PATH_BASEDIR.DIRNAME_REPOSITORY.DIRECTORY_SEPARATOR);
define('PATH_CACHE', PATH_REPOSITORY.DIRNAME_CACHE.DIRECTORY_SEPARATOR);
define('PATH_TEMPLATECACHE', PATH_CACHE.DIRNAME_TEMPLATECACHE);
define('PATH_LOGS', PATH_BASEDIR.DIRNAME_LOGS.DIRECTORY_SEPARATOR);
define('PATH_TEXTCATS', PATH_REPOSITORY.DIRNAME_TEXTCATS.DIRECTORY_SEPARATOR);
define('PATH_PAGES', PATH_REPOSITORY.DIRNAME_PAGES.DIRECTORY_SEPARATOR);