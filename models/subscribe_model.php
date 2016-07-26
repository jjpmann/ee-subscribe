<?php

class Subscribe_model extends CI_Model 
{

    protected $emma;
    
    public function __construct()
    {
        $this->emma = $this->emma_get_api_object();
    }
    
    public function emma_get_api_object()
    {
        
        // $this->q = new EMMAAPI(
        //         ee()->config->item('emma_api_key'), 
        //         ee()->config->item('emma_username'),
        //         ee()->config->item('emma_password')
        // );

        // return $this->q;
    }        


    public function lists()
    {
        return array();
    }


    public function getGroups()
    {
        return $this->emma->list_groups('g,t');
    }

    public function getGroupMembers($group_id)
    {
        return $this->emma->list_group_members($group_id);
    }    
    
    public function get_member_detail($member_id)
    {
        return $this->emma->get_member_detail($member_id);
    }
    
    public function get_member_detail_by_email($email)
    {
        return $this->emma->get_member_detail_by_email($email);
    }    
    
    public function getMailingLists()
    {
        return $this->emma->get_mailing_list('true','m,t','s,c','');
    }
    
    public function getMailingDetails($mailing_id)
    {
        return $this->emma->get_response_overview($mailing_id);
    }
    
    public function getTypeDetails($mailing_id,$type)
    {
        $function="get_".$type;
        return $this->emma->$function($mailing_id);        
    }
    
    public function get_links($mailing_id)
    {
        return $this->emma->get_links($mailing_id);
    }
    
    public function createEmmaGroup($group=array())
    {
        return $response=$this->emma->create_groups($group);
    }
    
    public function editEmmaGroup($group_id,$group_name)
    {
        return $this->emma->update_group($group_id, $group_name);
    }    

    public function deleteEmmaGroup($group_id)
    {
        return $this->emma->delete_group($group_id);
    }        
    
    public function getEmmaGroupInfo($group_id)
    {
        return ($this->emma->get_group_detail($group_id));
    }    
    
    public function getFields()
    {
        return ($this->emma->get_field_list(1));
    }
    
    public function getMemberGroups($member_id)
    {
        return $this->emma->list_member_groups($member_id);     
    }

    public function removeMemberFromAllGroups($member_id)
    {
        return $this->emma->remove_member_from_all_groups($member_id);
    }

    public function addMemberToGroups($member_id,$groups)
    {
        return $this->emma->add_member_to_groups($member_id,$groups);
    }    

    public function signupEmmaUser($email, $field_data, $groups, $signup_form_id = NULL, $opts = array())
    {
        return $this->emma->signup_single_member($email, $field_data, $groups, $signup_form_id, $opts);
    }

    public function createEmmaUser($email, $field_data, $groups)
    {
        return $this->emma->import_single_member($email, $field_data, $groups);
    }
    
    public function deleteEmmaUsers($members=array())
    {
        return $this->emma->delete_members($members);      
    }
    
    public function updateMembersStatus($members=array(),$status)
    {
        return $this->emma->update_members_status($members,$status);     
    }  
    
    public function updateMember($member_id,$email,$status,$field_data=array())
    {
        return $this->emma->update_member($member_id,$email,$status,$field_data);    
    }

    public function importMemberList($members=array(),$import_name,$sign_up,$groups)
    {
        return $this->emma->import_member_list($members, $import_name, $sign_up, $groups);
    }
}
