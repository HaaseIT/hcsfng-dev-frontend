<?php

namespace HaaseIT\HCSFNG\Frontend;


class Page
{
    protected $container;
    public $payload, $status;

    public function __construct($container, $payload)
    {
        $this->status = 200;
        $this->container = $container;
        $this->payload = $payload;
    }
}