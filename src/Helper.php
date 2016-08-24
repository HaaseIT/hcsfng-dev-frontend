<?php

namespace HaaseIT\HCSFNG\Frontend;


class Helper
{
    public static $langprefixset = false, $singlelangmode = true;

    public static function getLanguage($container)
    {
        $langavailable = $container['conf']["lang_available"];
        $sLang = key($langavailable);

        if (count($langavailable) > 1) {
            self::$singlelangmode = false;
            if ($container['requesturi'][0] == '/' && $container['requesturi'][3] == '/') {
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

}
