<?php

namespace HaaseIT\HCSFNG\Frontend;


class Router
{
    private $container, $sPath, $P;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @return bool|array
     */
    protected function tryFetchingJson()
    {
        $jsonfilename = $this->sPath.'.json';

        // if the page file is found and had valid json, return it
        if ($this->container['repository']->has(DIRNAME_PAGES.$jsonfilename)) {
            $pagedata = json_decode($this->container['repository']->read(DIRNAME_PAGES.$jsonfilename), true);
            if ($this->checkForValidPage($pagedata)) {
                return $pagedata;
            }
        }

        // if this is not the default language, try again with the default language
        if ($this->container['lang'] != $this->container['defaultlang']) {
            //rewrite the language prefix to the default language
            $jsonfilename = '/'.$this->container['defaultlang'].substr($jsonfilename, 3);

            if ($this->container['repository']->has(DIRNAME_PAGES.$jsonfilename)) {
                $pagedata = json_decode($this->container['repository']->read(DIRNAME_PAGES.$jsonfilename), true);
                if ($this->checkForValidPage($pagedata)) {
                    // add language warning
                    $pagedata['warning_language'] = true;
                    return $pagedata;
                }
            }
        }

        return false;
    }

    protected function checkForValidPage($pagedata)
    {
        // if the pagedata is empty, not an array or has no type set, return false
        if (empty($pagedata) || !is_array($pagedata) || empty($pagedata['type'])) {
            return false;
        }
        // if the pagedata is of type shorturl, a target is required
        if ($pagedata['type'] == 'shorturl' && !empty($pagedata['target'])) {
            return true;
        }
        if ($pagedata['type'] == 'content') {
            return true;
        }

        return false;
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
                } elseif ($payload['type'] == 'shorturl') {
                    $this->P = new PageRedirect($this->container,
                        ['headers' => ['Location' => $payload['target']]]);
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
