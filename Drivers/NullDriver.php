<?php

namespace Subscribe\Drivers;

class NullDriver extends Driver
{
    protected $active = false;

    public function __construct()
    {
    }

    public function isActive()
    {
        return $this->active;
    }

    public function groups()
    {
        return [];
    }

    public function group($id)
    {
        return false;
    }
}
