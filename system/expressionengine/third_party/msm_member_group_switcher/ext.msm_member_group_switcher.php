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
 File: ext.msm_member_group_switcher.php
-----------------------------------------------------
 Purpose: Switch member's group depending on what MSM site is he at
=====================================================
*/

if ( ! defined('BASEPATH'))
{
	exit('Invalid file request');
}

require_once PATH_THIRD.'msm_member_group_switcher/config.php';

class Msm_member_group_switcher_ext {

	var $name	     	= MSM_MEMBER_GROUP_SWITCHER_ADDON_NAME;
	var $version 		= MSM_MEMBER_GROUP_SWITCHER_ADDON_VERSION;
	var $description	= 'Switch member\'s group depending on what MSM site is he at';
	var $settings_exist	= 'n';
	var $docs_url		= 'http://www.intoeetive.com/';
    
    var $settings 		= array();
    var $site_id		= 1;
    
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
		$this->site_id = $this->EE->config->item('site_id'); 
	}
    
    /**
     * Activate Extension
     */
    function activate_extension()
    {
        $this->EE->load->dbforge(); 
        
        $hooks = array(

    		array(
    			'hook'		=> 'sessions_end',
    			'method'	=> 'set_group',
    			'priority'	=> 10
    		)
    	);
    	
        foreach ($hooks AS $hook)
    	{
    		$data = array(
        		'class'		=> __CLASS__,
        		'method'	=> $hook['method'],
        		'hook'		=> $hook['hook'],
        		'settings'	=> '',
        		'priority'	=> $hook['priority'],
        		'version'	=> $this->version,
        		'enabled'	=> 'y'
        	);
            $this->EE->db->insert('extensions', $data);
    	}	
    	
    	//exp_msm_member_groups
        $fields = array(
			'member_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'site_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 1),
			'group_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 5)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('member_id');
		$this->EE->dbforge->add_key('site_id');
		$this->EE->dbforge->add_key('group_id');
		$this->EE->dbforge->create_table('msm_member_groups', TRUE);
        
    }
    
    /**
     * Update Extension
     */
    function update_extension($current = '')
    {
    	if ($current == '' OR $current == $this->version)
    	{
    		return FALSE;
    	}
    	
    	if ($current < '2.0')
    	{
    		// Update to version 1.0
    	}
    	
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update(
    				'extensions', 
    				array('version' => $this->version)
    	);
    }
    
    
    /**
     * Disable Extension
     */
    function disable_extension()
    {
    	$this->EE->load->dbforge(); 
		
		$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->delete('extensions');

    }
    
    
    
    function settings()
    {
		$settings = array();
        
        
        return $settings;
    }
    
    
    function set_group($session)
    {
    	//are we logged in at all?
    	//var_dump($session->userdata);
    	if ($session->userdata['member_id']==0)
    	{
    		return $session;
    	}
    	
    	//$this->EE->load->library('session');
    	
    	//is there a record for this member and site? if no, create one
    	$this->EE->db->select('group_id')
    		->from('msm_member_groups')
    		->where('member_id', $session->userdata['member_id'])
    		->where('site_id', $this->site_id)
			->limit(1);
   		$q = $this->EE->db->get();
   		if ($q->num_rows()==0)
   		{
   			$q->free_result();
		   	/*$data = array(
			   	'member_id'	=> $session->userdata['member_id'],
		 		'group_id'	=> $session->userdata['group_id'],
		 		'site_id'	=> $this->site_id
			   );
		   	$this->EE->db->insert('msm_member_groups', $data);*/
		   	return $session;
   		}
   		
   		//same group as master? no reason to continue
   		if ($q->row('group_id')==$session->userdata['group_id'])
   		{
			$q->free_result();
   			return $session;
   		}
    	
    	//set the group and update session
    	$session->userdata['group_id'] = $q->row('group_id');
    	
    	$this->EE->db->from('member_groups')
			->where('site_id', (int) $this->EE->config->item('site_id'))
			->where('group_id', $session->userdata['group_id']);
		$group_query = $this->EE->db->get();
    	
    	foreach ($group_query->row_array() as $key => $val)
		{
			if ($key != 'crypt_key')
			{
				$session->userdata[$key] = $val;
			}
		}
			    	
    	// Assign Sites, Channel, Template, and Module Access Privs	
		if (REQ == 'CP')
		{
			$this->_setup_cp_privs($session);
		}
		
		// Does the member have admin privileges?
		if ($group_query->row('can_access_cp') == 'y')
		{
			$session->access_cp = TRUE;
			$session->userdata['admin_sess'] = 1; 
		}
		else
		{
			$session->userdata['admin_sess'] = 0; 
			$session->access_cp = FALSE;
		}		
		
		$session->update_session();
		
		//var_dump($session);

		$group_query->free_result();
    	
    	return $session;
    }
    
    
    
    function _setup_cp_privs($session)
    {

		$assigned_template_groups = array();
		
		$this->EE->db->select('template_group_id');
		$qry = $this->EE->db->get_where('template_member_groups',
										array('group_id' => $session->userdata['group_id']));

		
		if ($qry->num_rows() > 0)
		{
			foreach ($qry->result() as $row)
			{
				$assigned_template_groups[$row->template_group_id] = TRUE;
			}
		}
			
		$session->userdata['assigned_template_groups'] = $assigned_template_groups;
		
		$qry->free_result();	
		
		
		$assigned_sites = array();
		
		if ($session->userdata['group_id'] == 1)
		{
			$qry = $this->EE->db->select('site_id, site_label')
								->order_by('site_label')
								->get('sites');
		}
		else
		{
			// Groups that can access the Site's CP, see the site in the 'Sites' pulldown
			$qry = $this->EE->db->select('es.site_id, es.site_label')
								->from(array('sites es', 'member_groups mg'))
								->where('mg.site_id', ' es.site_id', FALSE)
								->where('mg.group_id', $session->userdata['group_id'])
								->where('mg.can_access_cp', 'y')
								->order_by('es.site_label')
								->get();
		}
		
		if ($qry->num_rows() > 0)
		{
			foreach ($qry->result() as $row)
			{
				$assigned_sites[$row->site_id] = $row->site_label;
			}
		}
		
		$session->userdata['assigned_sites'] = $assigned_sites;

		
		$assigned_channels = array();
	 
		if ($session->userdata['group_id'] == 1)
		{
			$this->EE->db->select('channel_id, channel_title');
			$this->EE->db->order_by('channel_title');
			$res = $this->EE->db->get_where(
				'channels', 
				array('site_id' => $this->site_id)
			);
		}
		else
		{
			$res = $this->EE->db->select('ec.channel_id, ec.channel_title')
				->from(array('channel_member_groups ecmg', 'channels ec'))
				->where('ecmg.channel_id', 'ec.channel_id',  FALSE)
				->where('ecmg.group_id', $session->userdata['group_id'])
				->where('site_id', $this->site_id)
				->order_by('ec.channel_title')
				->get();
		}
		
		if ($res->num_rows() > 0)
		{
			foreach ($res->result() as $row)
			{
				$assigned_channels[$row->channel_id] = $row->channel_title;
			}
		}
		
		$res->free_result();

		$session->userdata['assigned_channels'] = $assigned_channels;		


		$assigned_modules = array();
		
		$this->EE->db->select('module_id');
		$qry = $this->EE->db->get_where('module_member_groups',
										array('group_id' => $session->userdata['group_id']));
		
		if ($qry->num_rows() > 0)
		{
			foreach ($qry->result() as $row)
			{
				$assigned_modules[$row->module_id] = TRUE;
			}
		}

		$session->userdata['assigned_modules'] = $assigned_modules;
		
		$qry->free_result();		

    }
    
  

}
// END CLASS
