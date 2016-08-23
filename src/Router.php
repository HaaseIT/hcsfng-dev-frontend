<?php

namespace HaaseIT\HCSFNG\Frontend;


class Router
{
    private $container, $sPath, $P;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getPage()
    {
        $aURL = parse_url($this->container['request']->getRequestTarget());
        $this->sPath = $aURL["path"];

        $aPath = explode('/', $this->sPath);

        if (mb_strpos($aPath[count($aPath) - 1], '.') === false && $aPath[count($aPath) - 1] != '') $this->sPath .= '/';

        if ($this->sPath[strlen($this->sPath) - 1] == '/') $this->sPath .= 'index.html';

        if (substr($this->sPath, -5) == '.html') {
            $jsonfilename = substr($this->sPath, 0, -4).'json';
            if (is_file(PATH_PAGES.$this->container['lang'].$jsonfilename)) {
                $payload = json_decode(file_get_contents(PATH_PAGES.$this->container['lang'].$jsonfilename), true);
            } elseif ($this->container['lang'] != $this->container['defaultlang']) {
                if (is_file(PATH_PAGES.$this->container['defaultlang'].$jsonfilename)) {
                    $payload = json_decode(file_get_contents(PATH_PAGES.$this->container['defaultlang'].$jsonfilename), true);
                    // todo: add textcat for following message
                    $payload['content'] = 'Not Found in '.$this->container['lang'].', displaying in '.$this->container['defaultlang'].'.<br>'.$payload['content'];
                }
            }

        }

        if (!empty($payload)) {
            if (empty($payload['type']) || $payload['type'] == 'content') {
                $this->P = new PageStatic($this->container, $payload);
            }
        }

        if (empty($this->P)) {
            $this->P = new PageError($this->container, []);
        }

        $this->P->payload['requesturi'] = $this->container['requesturi'];

        return $this->P;
    }
}
