<?php

namespace Subscribe\Drivers;

class RealMagnetDriver extends Driver
{

    protected $client;

    protected $active = false;

    protected $fields = [
        'first_name'    => 'First_Name',
        'last_name'     => 'Last_Name',
        'email'         => 'Email',
    ];

    public function __construct()
    {
        $username = env('REALMAGNET_USERNAME', ee()->config->item('realmagnet_username'));
        $password = env('REALMAGNET_PASSWORD', ee()->config->item('realmagnet_password'));

        $this->client  = new \RealMagnet\RealMagnet($username, $password, new \RealMagnet\RealMagnetClient());

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
            return array();
        }
        $groups = array();

        $_groups = $this->client->getGroups();
        
        foreach ($_groups as $group) {
            $g = new \stdClass;
            $g->id      = $group['GroupID'];
            $g->name    = $group['GroupName'];
            $g->data    = $group;
            $groups[] = $g;
        }

        return $groups;
    }

    public function group($id)
    {
        return $this->client->getGroupDetails($id);
    } 

    public function fields()
    {
        $fields = array();
        $_fields = $this->client->getRecipientFields(1);

        if (isset($_fields['CustomFields'])) {
            foreach ($_fields['CustomFields'] as $field) {

                $f = new \stdClass;
                $f->id      = strtolower($field['FieldName']);
                $f->name    = $field['FieldName'];
                $f->label   = $field['Label'];
                //$f->data    = $group;
                $fields[] = $f;
            }
        }
        
        return $fields;
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

        if (empty($find)) {
            $add =  $this->client->addRecipient($u);
            return $add;
        }  

        $current = $find[0];
        $id = $current['ID'];
        $edit =  $this->client->editRecipient($id, $u);
        
        // Recipient updated successfully 
        return $edit;

    }

}