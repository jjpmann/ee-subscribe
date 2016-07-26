<?php

namespace Subscribe\Drivers;

abstract class Driver 
{
    abstract public function groups();

    abstract public function group($id);

    //abstract public function lists();
}