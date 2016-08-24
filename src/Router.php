<?php

namespace HaaseIT\HCSFNG\Frontend;


class Router
{
    private $container, $sPath, $P;

    public function __construct($container)
    {
        $this->container = $container;
    }

    protected function tryFetchingJson()
    {
        $return = false;

        $jsonfilename = $this->sPath.'.json';
        if (is_file(PATH_PAGES.$jsonfilename)) {
            $return = json_decode(file_get_contents(PATH_PAGES.$jsonfilename), true);
        } elseif ($this->container['lang'] != $this->container['defaultlang']) {
            $jsonfilename = '/'.$this->container['defaultlang'].substr($jsonfilename, 3);
            if (is_file(PATH_PAGES.$jsonfilename)) {
                $return = json_decode(file_get_contents(PATH_PAGES.$jsonfilename), true);
                $return['warning_language'] = true;
            }
        }

        return $return;
    }

    public function getPage()
    {
        $aURL = parse_url($this->container['request']->getRequestTarget());
        $this->sPath = $aURL["path"];

        if (!Helper::$langprefixset && !Helper::$singlelangmode) {
            $this->P = new PageRedirect($this->container, ['headers' => ['Location' => '/' . $this->container['lang'] . $this->sPath]]);
        } else {
            if (!Helper::$langprefixset && Helper::$singlelangmode) {
                $this->sPath = '/' . $this->container['lang'] . $this->sPath;
            }
            $foo = substr(Helper::normalizePath(PATH_PAGES . dirname($this->sPath)), 0, strlen(PATH_PAGES));
            // the condition in the following line should make directory traversal impossible.
            if (substr(Helper::normalizePath(PATH_PAGES . dirname($this->sPath)), 0, strlen(PATH_PAGES)) == PATH_PAGES) {

                // try to fetch json
                $payload = $this->tryFetchingJson();

                // if the previous fetch failed and the last part of the path has no . in it, append /index.html and try again
                if (empty($payload)) {
                    $retryfetchpayload = false;
                    $aPath = explode('/', $this->sPath);
                    if (mb_strpos($aPath[count($aPath) - 1], '.') === false && $aPath[count($aPath) - 1] != '') {
                        $this->sPath .= '/';
                        $retryfetchpayload = true;
                    }
                    if ($this->sPath[strlen($this->sPath) - 1] == '/') {
                        $this->sPath .= 'index.html';
                        $retryfetchpayload = true;
                    }
                    if ($retryfetchpayload) {
                        $payload = $this->tryFetchingJson();
                    }
                }

                if (!empty($payload)) {
                    if (empty($payload['type']) || $payload['type'] == 'content') {
                        $this->P = new PageStatic($this->container, $payload);
                    } elseif ($payload['type'] == 'shorturl' && !empty($payload['config']['target'])) {
                        $this->P = new PageRedirect($this->container,
                            ['headers' => ['Location' => $payload['config']['target']]]);
                    }
                }
            }
        }

        if (empty($this->P)) {
            $this->P = new PageError($this->container, []);
        }

        $this->P->payload['requesturi'] = $this->container['requesturi'];

        return $this->P;
    }
}
