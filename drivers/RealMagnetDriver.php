<?php

namespace Subscribe\Drivers;

class RealMagnetDriver extends Driver
{

    protected $client;

    public function __construct()
    {
        $username = env('REALMAGNET_USERNAME', ee()->config->item('realmagnet_username'));
        $password = env('REALMAGNET_PASSWORD', ee()->config->item('realmagnet_password'));

        $this->client  = new \RealMagnet\RealMagnet($username, $password, new \RealMagnet\RealMagnetClient());
    }

    public function groups()
    {
        return $this->client->getGroups();
    }

    public function group($id)
    {

    } 

}