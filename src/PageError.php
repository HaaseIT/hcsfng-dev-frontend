<?php

namespace HaaseIT\HCSFNG\Frontend;


class PageError extends Page
{
    public function __construct($container, $payload)
    {
        parent::__construct($container, $payload);
        $this->status = 404; //todo: choose the right error, load texts, fill payload...
    }
}