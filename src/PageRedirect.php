<?php

namespace HaaseIT\HCSFNG\Frontend;


class PageRedirect extends Page
{
    public function __construct($container, $payload)
    {
        parent::__construct($container, $payload);
        $this->status = 301;
        $this->headers = $payload['headers'];
    }
}