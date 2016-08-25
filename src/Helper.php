<?php

namespace HaaseIT\HCSFNG\Frontend;


class Helper
{
    public static $langprefixset = false, $singlelangmode = true;

    public static function enrichPagePayload($container, $P)
    {
        $P->payload['lang'] = $container['lang'];
        $P->payload['defaultlang'] = $container['defaultlang'];

        // this comes last or some data will be missing from debug output
        if ($container['conf']['debug']) {
            ob_start();
            var_dump($P->payload);
            $content = htmlspecialchars(ob_get_contents());
            ob_end_clean();
            $P->payload['debug'] = $content;
        }

        return $P;
    }

    public static function getLanguage($container)
    {
        $langavailable = $container['conf']["lang_available"];
        $sLang = key($langavailable);

        if (count($langavailable) > 1) {
            self::$singlelangmode = false;
            if ($container['requesturi'][0] == '/' && $container['requesturi'][3] == '/') { // todo: fix notice
                $substr = substr($container['requesturi'], 1, 2);
                if (isset($langavailable[$substr])) {
                    self::$langprefixset = true;
                    return $substr;
                }
            }
        }

        return $sLang;
    }

    // don't remove this, this is the fallback for unavailable twig functions
    public static function reachThrough($string) {
        return $string;
    }

    public static function normalizePath($path) {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function initRepository($container)
    {
        if (
            $container['conf']['repository']['type'] == 'localrelative'
            ||
            $container['conf']['repository']['type'] == 'localabsolute'
        ) {
            if ($container['conf']['repository']['type'] == 'localrelative') {
                $basepath = realpath(PATH_BASEDIR.DIRECTORY_SEPARATOR.$container['conf']['repository']['url']);
            } elseif ($container['conf']['repository']['type'] == 'localabsolute') {
                $basepath = realpath($container['conf']['repository']['url']);
            }

            if (!empty($basepath)) {
                $adapter = new \League\Flysystem\Adapter\Local($basepath);
            }
        } elseif ($container['conf']['repository']['type'] == 'guzzle') {
            $adapter = new \Twistor\Flysystem\GuzzleAdapter($container['conf']['repository']['url']);
        }

        if (!empty($adapter)) {
            return new \League\Flysystem\Filesystem($adapter);
        }

        return false;
    }

    public static function initTwig($container)
    {
        $loader = new \CedricZiel\TwigLoaderFlysystem\FlysystemLoader($container['repository'], DIRNAME_TEMPLATECACHE.DIRECTORY_SEPARATOR);

        $twig_options = [
            'autoescape' => false,
            'debug' => (isset($container['conf']["debug"]) && $container['conf']["debug"] ? true : false)
        ];
        if (isset($container['conf']["templatecache_enable"]) && $container['conf']["templatecache_enable"] &&
            is_dir(PATH_TEMPLATECACHE) && is_writable(PATH_TEMPLATECACHE)) {
            $twig_options["cache"] = PATH_TEMPLATECACHE;
        }

        $twig = new \Twig_Environment($loader);

        if ($container['conf']['allow_parsing_of_page_content']) {
            $twig->addExtension(new \Twig_Extension_StringLoader());
        } else { // make sure, template_from_string is callable
            $twig->addFunction('template_from_string', new \Twig_Function_Function('\HaaseIT\HCSF\Helper::reachThrough'));
        }

        if (isset($container['conf']["debug"]) && $container['conf']["debug"]) {
            //$twig->addExtension(new Twig_Extension_Debug());
        }
        $twig->addFunction(new \Twig_SimpleFunction('T', [$container['textcats'], 'T']));

        return $twig;
    }
}
