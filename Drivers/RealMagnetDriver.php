<?php

namespace Subscribe\Drivers;

class RealMagnetDriver extends Driver
{
    protected $client;

    protected $active = false;

    protected $fields = [
        'first_name' => 'First_Name',
        'last_name' => 'Last_Name',
        'email' => 'Email',
    ];

    public function __construct()
    {
        $username = env('REALMAGNET_USERNAME', ee()->config->item('realmagnet_username'));
        $password = env('REALMAGNET_PASSWORD', ee()->config->item('realmagnet_password'));

        $this->client = new \RealMagnet\RealMagnet($username, $password, new \RealMagnet\RealMagnetClient());

        try {
            $this->client->init();
            $this->active = true;
        } catch (\RealMagnet\RealMagnetException $e) {
            //echo $e->getMessage();
        }
    }

    public function isActive()
    {
        return $this->active;
    }

    public function groups()
    {
        if (!$this->isActive()) {
            return [];
        }

        $groups = $this->client->getGroups();

        return $groups->data->map(function ($group) {
            $group['id'] = $group['GroupID'];
            $group['name'] = $group['GroupName'];

            return $group;
        });
    }

    public function group($id)
    {
        return $this->client->getGroupDetails($id);
    }

    public function fields()
    {
        $fields = $this->client->getRecipientFields(1);

        if ($fields->isSuccessful()) {
            return $fields->data;
        }

        return [];

    }

    public function user($email)
    {
        $user = new \stdClass();
        $user->email = $email;

        $find = $this->client->searchRecipients($user);

        return $find;
    }

    public function signup($user, $groups)
    {
        $u = new \stdClass();
        $u->firstName = $user['first_name'];
        $u->lastName = $user['last_name'];
        $u->email = $user['email'];
        $u->groups = $groups;

        // does user exists??
        $find = $this->user($user['email']);

        if ($find->isSuccessful()) {
            return $this->client->editRecipientGroups((int) $find->data->first()->get('ID'), $groups);
        }

        return $this->client->addRecipient($u);

    }
}
