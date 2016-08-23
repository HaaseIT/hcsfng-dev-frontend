<?php

namespace HaaseIT\HCSFNG\Frontend;


class Textcat
{
    protected $T, $sLang, $sDefaultlang, $bVerbose, $logdir;

    public function __construct($container, $defaultlang, $verbose = false, $logdir = '')
    {
        $this->sLang = \filter_var($container['lang'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        $this->sDefaultlang = \filter_var($defaultlang, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        $this->bVerbose = $verbose;
        $this->logdir = $logdir;
    }

    public function loadTextcats()
    {
        // get textcat for the current language
        if (is_file(PATH_TEXTCATS.$this->sLang.'.json')) {
            $aTextcat[$this->sLang] = json_decode(file_get_contents(PATH_TEXTCATS.$this->sLang.'.json'), true);
        }

        // if the current language is not the default language, get the defauld language aswell
        if ($this->sLang != $this->sDefaultlang) {
            if (is_file(PATH_TEXTCATS.$this->sDefaultlang.'.json')) {
                $aTextcat[$this->sDefaultlang] = json_decode(file_get_contents(PATH_TEXTCATS.$this->sDefaultlang.'.json'), true);
            }
        }

        if (isset($aTextcat)) {
            $this->T = $aTextcat;
        }
    }

    /**
     * @param $sTextkey
     * @param bool $bReturnFalseIfNotAvailable
     * @return bool|string
     */
    public function T($sTextkey, $bReturnFalseIfNotAvailable = false)
    {
        $return = '';
        $sTextkey = \filter_var($sTextkey, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        if (isset($_GET["showtextkeys"])) {
            $return = '['.$sTextkey.']';
        } else {
            if (!empty($this->T[$this->sLang][$sTextkey]) && \trim($this->T[$this->sLang][$sTextkey]) != '') {
                $return = \trim($this->T[$this->sLang][$sTextkey]);
            } elseif (!empty($this->T[$this->sDefaultlang][$sTextkey]) && \trim($this->T[$this->sDefaultlang][$sTextkey]) != '') {
                $return = \trim($this->T[$this->sDefaultlang][$sTextkey]);
            }
            if (!isset($return) || $return == '') {
                if ($this->logdir != '' && is_dir($this->logdir) && is_writable($this->logdir)) {
                    error_log(date('c').' Missing Text: '.$sTextkey.PHP_EOL, 3, $this->logdir.DIRECTORY_SEPARATOR.'errors_textcats.log');
                }
                if ($bReturnFalseIfNotAvailable) return false;
                elseif ($this->bVerbose) $return = 'Missing Text: '.$sTextkey;
            }
        }

        return $return;
    }
}