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
            if (is_file(PATH_PAGES.$this->container['defaultlang'].$jsonfilename)) {
                $return = json_decode(file_get_contents(PATH_PAGES.DIRECTORY_SEPARATOR.$this->container['defaultlang'].$jsonfilename), true);
                // todo: add textcat for following message
                $return['content'] = 'Not Found in '.$this->container['lang'].', displaying in '.$this->container['defaultlang'].'.<br>'.$return['content'];
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
            // the condition in the following line should make directory traversal impossible.
            if (substr(realpath(PATH_PAGES . dirname($this->sPath)), 0, strlen(PATH_PAGES)) == PATH_PAGES) {

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
