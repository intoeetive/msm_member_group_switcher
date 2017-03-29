<?php

/*
=====================================================
 MSM Member Group Switcher
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2012 Yuri Salimovskiy
=====================================================
 This software is intended for usage with
 ExpressionEngine CMS, version 2.0 or higher
=====================================================
 File: upd.msm_member_group_switcher.php
-----------------------------------------------------
 Purpose: Switch member's group depending on what MSM site is he at
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'msm_member_group_switcher/config.php';

class Msm_member_group_switcher_upd {

    var $version = MSM_MEMBER_GROUP_SWITCHER_ADDON_VERSION;
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
    } 
    
    function install() { 

        $data = array( 'module_name' => 'Msm_member_group_switcher' , 'module_version' => $this->version, 'has_cp_backend' => 'y'); 
        $this->EE->db->insert('modules', $data); 
        
        return TRUE; 
        
    } 
    
    function uninstall() { 
		
		$this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Msm_member_group_switcher')); 
        
        $this->EE->db->where('module_id', $query->row('module_id')); 
        $this->EE->db->delete('module_member_groups'); 
        
        $this->EE->db->where('module_name', 'Msm_member_group_switcher'); 
        $this->EE->db->delete('modules'); 
        
        $this->EE->db->where('class', 'Msm_member_group_switcher'); 
        $this->EE->db->delete('actions'); 

        return TRUE; 
    } 
    
    
    function update($current='') 
	{ 
        if ($current < 2.0) 
		{ 
            // Do your 2.0 version update queries 
        } 
        return TRUE; 
    } 
	

}
/* END */
?>