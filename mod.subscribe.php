<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package     Subscribe
 * @subpackage  ThirdParty
 * @category    Modules
 * @author      Jerry Price
 * @link        https://github.com/jjpmann
 */
class Subscribe {

    var $return_data;

    public function __construct()
    {       
        ee()->load->model('subscribe_model');
    }

    public function subscribe() 
    {
        $vars=array();
        ee()->load->library('table');
        ee()->load->library('session');                                   
        ee()->load->helper('form');
        $message="";
        $message_color="red";
        $member_id='';
        if(isset($_POST['subscribe_subscribe']))
        {                                    
           $group_data[]=intval(ee()->config->item('subscribe_default_group'));
           $email = $_POST['email'];             
           if($email)
           {
               $fields['first_name']  = $_POST['name'];
               $response = ee()->subscribe_model->createEmmaUser($email,$fields,$group_data);                                                 
               if(!isset($response_add->error))
               {
                   if( isset($response->member_id) )
                   {
                       $group_data[] = ee()->config->item('subscribe_default_group');
                       $response = ee()->subscribe_model->addMemberToGroups($response->member_id,$group_data);  
                       $message=lang("Data Updated successfully !!!");                                                                                                                        $message_color="green";
                   }
               }
               else
               {
                   $message=lang("Oooops Something went wrong");

           }
        }                                                                   
        $vars=array(
           'member_id'=>$member_id,
           'message_color'=>$message_color,
           'message'=>$message,
           'action_url'=>"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
             );
        return ee()->load->view('subscribe_subscribe_form',$vars,TRUE); 
          
    }  

    public function subscribe_old() 
    {
        $vars=array();
        ee()->load->library('table');
        ee()->load->library('session');
        #$user_email=ee()->session->userdata('email');
        //var_dump(ee()->session->userdata);
        ee()->load->helper('form');
        $message="";
        $message_color="red";
        $member_id='';
        $group_data=$member_group_ids=array();
        # if($user_email)
        #{
            if(isset($_POST['subscribe_subscribe']))
            {
               foreach($_POST['group_list'] as $val)
               {
                    if($val)
                    {
                        $group_data[]=intval($val);
                    }    
               }           
               if($_POST['subscribe_member_id'])
               {
                    $member_id=$_POST['subscribe_member_id'];
                   $response_remove= ee()->subscribe_model->removeMemberFromAllGroups($_POST['subscribe_member_id']);
                   $response_add=ee()->subscribe_model->addMemberToGroups($_POST['subscribe_member_id'],$group_data);
                   if(!isset($response_add->error))
                   {
                        $message=lang("Data Updated successfully !!!");
                        $member_group_ids=$group_data;
                        $message_color="green";
                   }
                    else
                    {
                        $message=lang("Oooops Something went wrong");
                    }               
               }
               else
               {
                    $members = array(   
                                        array('email'=>$user_email),
                                    );
             
                    $response=ee()->subscribe_model->importMemberList($members, 'EE-import'.time(), 1, $group_data);
                    if(isset($response->import_id))
                    {
                        $message=lang("Data Updated successfully !!!");
                        $member_group_ids=$group_data;
                        $message_color="green";
                    }
                    else
                    {
                        $message=lang("Oooops Something went wrong");
                    }
               }
            }   
            if(!$message)
            {        
                $member_details=ee()->subscribe_model->get_member_detail_by_email($user_email);
                if(isset($member_details->member_id))
                {
                    $member_id=$member_details->member_id;
                    $groups=  ee()->subscribe_model->getMemberGroups($member_details->member_id);
                    foreach($groups as $group)
                    {
                        $member_group_ids[$group->member_group_id]=$group->member_group_id;
                    }  
                }       
            }
            $groups=ee()->subscribe_model->getGroups();
            foreach($groups as $group)
            {
                $groups_list[$group->member_group_id]=$group->group_name;
            }        
            $vars=array(
                'groups'=>$groups_list,
                'member_group_ids'=>$member_group_ids,
                'member_id'=>$member_id,
                'message_color'=>$message_color,
                'message'=>$message,
                'action_url'=>"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            );
            return ee()->load->view('subscribe_subscribe_form', $vars, TRUE); 
      #  }
    }

    /**
     * Helper function for getting a parameter
     */      
    protected function _get_param($key, $default_value = '')
    {
        $val = ee()->TMPL->fetch_param($key);
        
        if($val == '') {
            return $default_value;
        }
        return $val;
    }

    /**
     * Helper funciton for template logging
     */ 
    protected function _error_log($msg)
    {       
        ee()->TMPL->log_item("Subscribe ERROR: ".$msg);     
    }

}
