<?php

/**
 * @package     Subscribe
 * @subpackage  ThirdParty
 * @category    Modules
 * @author      Jerry Price
 * @link        https://github.com/jjpmann
 */
class Subscribe_upd {
        
    var $version        = '1.0'; 
    var $module_name    = "Subscribe";
    
    public function __construct( $switch = TRUE ) 
    { 
        
    } 

    public function install() 
    {
        $data = array(
            'module_name'    => $this->module_name,
            'module_version' => $this->version,
            'has_cp_backend' => 'y'
        );

        ee()->db->insert('modules', $data);        

        //
        // Add additional stuff needed on module install here
        // 

        return TRUE;
    }


    public function uninstall() 
    {               
        
        ee()->db->select('module_id');
        $query = ee()->db->get_where('modules', array('module_name' => $this->module_name));
        
        ee()->db->where('module_id', $query->row('module_id'));
        ee()->db->delete('module_member_groups');
        
        ee()->db->where('module_name', $this->module_name);
        ee()->db->delete('modules');
        
        ee()->db->where('class', $this->module_name);
        ee()->db->delete('actions');
        
        ee()->db->where('class', $this->module_name.'_mcp');
        ee()->db->delete('actions');

        return TRUE;
    }
    

    public function update($current = '')
    {
        return FALSE;
    }

}