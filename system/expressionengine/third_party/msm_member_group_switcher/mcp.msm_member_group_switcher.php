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
 File: mcp.msm_member_group_switcher.php
-----------------------------------------------------
 Purpose: Switch member's group depending on what MSM site is he at
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'msm_member_group_switcher/config.php';

class Msm_member_group_switcher_mcp {

    var $version = MSM_MEMBER_GROUP_SWITCHER_ADDON_VERSION;
    
    var $settings = array();
    
    var $perpage = 50;
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance();  
        
        $this->EE->lang->loadfile('members');  
        $this->EE->lang->loadfile('msm_member_group_switcher'); 
        
        if (version_compare(APP_VER, '2.6.0', '>='))
        {
        	$this->EE->view->cp_page_title = lang('msm_member_group_switcher_module_name');
        }
        else
        {
        	$this->EE->cp->set_variable('cp_page_title', lang('msm_member_group_switcher_module_name'));
        }

    } 
    
    

    function index()
    {
    	//exit();
        $this->EE->load->helper('form');
    	$this->EE->load->library('table');  
        $this->EE->load->library('pagination');
        
        $site_id = $this->EE->config->item('site_id');
        
        $p_config['base_url'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=msm_member_group_switcher'.AMP.'method=index';
		
    	$vars = array();
        
        $vars['table_headings'] = array(
                        $this->EE->lang->line('username'),
                        $this->EE->lang->line('screen_name'),
                        $this->EE->lang->line('email')
                    );
		
		$assigned_sites = array_flip($this->EE->session->userdata('assigned_sites'));
        $sites = $this->EE->db->select('site_id, site_label')->from('sites')->where_in('site_id', $assigned_sites)->get();
        $sql_what = 'exp_members.member_id, username, screen_name, email, exp_member_groups.group_title';
        $join[] = array('exp_member_groups', 'exp_member_groups.group_id=exp_members.group_id AND exp_member_groups.site_id='.$site_id, 'left');
        foreach ($sites->result_array() as $row)
        {
			$vars['table_headings'][] = lang('group_at').NBS.$row['site_label'];
        	$sql_what .= ', group_names'.$row['site_id'].'.group_title AS site_'.$row['site_id'].'_group_title';
        	$join[] = array('exp_msm_member_groups AS groups'.$row['site_id'], 'groups'.$row['site_id'].'.member_id=exp_members.member_id AND groups'.$row['site_id'].'.site_id='.$row['site_id'], 'left');
        	$join[] = array('exp_member_groups AS group_names'.$row['site_id'], 'group_names'.$row['site_id'].'.group_id=groups'.$row['site_id'].'.group_id AND group_names'.$row['site_id'].'.site_id='.$row['site_id'], 'left');
        }
        
        $vars['filter_by'] = array(
                            'any' => $this->EE->lang->line('any_field'),
                            'username' => $this->EE->lang->line('username'),
                            'screen_name' => $this->EE->lang->line('screen_name'),
                            'email' => $this->EE->lang->line('email')
                            );

    	$vars['selected'] = array();
        $vars['selected']['keyword']=$this->EE->input->get_post('keyword');
        $vars['selected']['filter_by']=$this->EE->input->get_post('filter_by');
        
        $vars['selected']['rownum']=($this->EE->input->get_post('rownum')!='')?$this->EE->input->get_post('rownum'):0;
        
        //$this->EE->db->start_cache();
        if ($vars['selected']['keyword']!='')
        {
            switch($vars['selected']['filter_by'])
            {
                case 'username':
                    $this->EE->db->where("exp_members.username LIKE '%".$vars['selected']['keyword']."%'");
                    break;
                case 'screen_name':
                    $this->EE->db->where("exp_members.screen_name LIKE '%".$vars['selected']['keyword']."%'");
                    break;
                case 'email':
                    $this->EE->db->where("exp_members.email LIKE '%".$vars['selected']['keyword']."%'");
                    break;
                default:
                    $this->EE->db->where("exp_members.username LIKE '%".$vars['selected']['keyword']."%' OR exp_members.screen_name LIKE '%".$vars['selected']['keyword']."%' OR exp_members.email LIKE '%".$vars['selected']['keyword']."%'");
                    break;
            }
        }
        //$this->EE->db->stop_cache();
        
        $this->EE->db->select('COUNT(DISTINCT exp_members.member_id) AS cnt');
        $this->EE->db->from('members');
        //echo $this->EE->db->_compile_select();
        $query = $this->EE->db->get();
        
        $vars['total_count'] = $query->row('cnt');
        
        $this->EE->db->distinct();
        $this->EE->db->select($sql_what);
        $this->EE->db->from('members');
        foreach ($join as $a)
		{
			$this->EE->db->join($a[0], $a[1], $a[2]);
		}
        if ($vars['selected']['keyword']!='')
        {
            switch($vars['selected']['filter_by'])
            {
                case 'username':
                    $this->EE->db->where("exp_members.username LIKE '%".$vars['selected']['keyword']."%'");
                    break;
                case 'screen_name':
                    $this->EE->db->where("exp_members.screen_name LIKE '%".$vars['selected']['keyword']."%'");
                    break;
                case 'email':
                    $this->EE->db->where("exp_members.email LIKE '%".$vars['selected']['keyword']."%'");
                    break;
                default:
                    $this->EE->db->where("exp_members.username LIKE '%".$vars['selected']['keyword']."%' OR exp_members.screen_name LIKE '%".$vars['selected']['keyword']."%' OR exp_members.email LIKE '%".$vars['selected']['keyword']."%'");
                    break;
            }
        }
        $this->EE->db->order_by('screen_name', 'asc');
        $this->EE->db->limit($this->perpage, $vars['selected']['rownum']);

        $query = $this->EE->db->get();
        
        //$this->EE->db->flush_cache();
        
        $i = 0;
      
        foreach ($query->result() as $obj)
        {
			$vars['members'][$i]['username'] = "<a href=\"".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=msm_member_group_switcher'.AMP.'method=edit'.AMP.'member_id='.$obj->member_id."\">".$obj->username."</a>";
			$vars['members'][$i]['screen_name'] = $obj->screen_name;
			$vars['members'][$i]['email'] = "<a href=\"mailto:".$obj->email."\">".$obj->email."</a>";
			foreach ($sites->result_array() as $row)
			{
				$varname = 'site_'.$row['site_id'].'_group_title';
				$vars['members'][$i]['site'.$row['site_id']] = ($obj->$varname!=NULL)?$obj->$varname:$obj->group_title;
			}

           $i++;
        }
        
        $p_config['total_rows'] = $vars['total_count'];
		$p_config['per_page'] = $this->perpage;
		$p_config['page_query_string'] = TRUE;
		$p_config['query_string_segment'] = 'rownum';
		$p_config['full_tag_open'] = '<p id="paginationLinks">';
		$p_config['full_tag_close'] = '</p>';
		$p_config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$p_config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$p_config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$p_config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';
        

		$this->EE->pagination->initialize($p_config);
        
		$vars['pagination'] = $this->EE->pagination->create_links();
        
    	return $this->EE->load->view('index', $vars, TRUE);
	
    }
    
   
    
    

    function edit()
    {
    	$site_id = $this->EE->config->item('site_id');
        
        $this->EE->load->helper('form');
    	$this->EE->load->library('table');  
        $this->EE->load->library('api');
        
        $this->EE->db->select('member_id, exp_members.group_id, username, screen_name, email, group_title');
        $this->EE->db->from('exp_members');
        $this->EE->db->join('exp_member_groups', 'exp_members.group_id=exp_member_groups.group_id', 'left');
        $this->EE->db->where('member_id', $this->EE->input->get('member_id'));
        $query = $this->EE->db->get();
        
        $vars['data'] = array(	
            'username'	=> form_hidden('member_id', $query->row('member_id'))."<a href=\"".BASE.AMP.'C=myaccount'.AMP.'id='.$query->row('member_id')."\">".$query->row('username')."</a>",
            'screen_name'	=> $query->row('screen_name'),
            'email'	=> "<a href=\"mailto:".$query->row('email')."\">".$query->row('email')."</a>",
            'master_group'	=> $query->row('group_title').NBS.NBS."<a href=\"".BASE.AMP.'C=myaccount'.AMP.'M=member_preferences'.AMP.'id='.$query->row('member_id')."\">".lang('edit')."</a>"
			);

        $assigned_sites = array_flip($this->EE->session->userdata('assigned_sites'));
		$sites = $this->EE->db->select('site_id, site_label')->from('sites')->where_in('site_id', $assigned_sites)->get();
        foreach ($sites->result_array() as $row)
        {
        	$member_groups = array();
	        $this->EE->db->select('group_id, group_title');
	        $this->EE->db->from('member_groups');
	        $this->EE->db->where('site_id', $row['site_id']);  
	        $query = $this->EE->db->get();
	        foreach ($query->result() as $obj)
	        {
				if ($this->EE->session->userdata('group_id')==1 || $obj->group_id!=1)
				{
					$member_groups[$obj->group_id] = $obj->group_title;
				}
	        }
			
			$group_q = $this->EE->db->select('group_id')->from('exp_msm_member_groups')->where('member_id', $this->EE->input->get('member_id'))->where('site_id', $row['site_id'])->limit(1)->get();
			if ($group_q->num_rows()==0)
			{
				$group_q = $this->EE->db->select('group_id')->from('members')->where('member_id', $this->EE->input->get('member_id'))->get();
			}
	
			$current_group = $group_q->row('group_id');
			
			$vars['data'][lang('group_at').NBS.$row['site_label']] = form_dropdown('member_group_site_'.$row['site_id'], $member_groups, $current_group);
        }
        
    	return $this->EE->load->view('edit', $vars, TRUE);
	
    }        
    
    
    function save()
    {
        //does member exist?
        $this->EE->db->select('member_id');
        $this->EE->db->from('members');
        $this->EE->db->where('member_id', $this->EE->input->get_post('member_id'));
        $query = $this->EE->db->get();
        if ($query->num_rows()==0)
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('member_not_exist'));
        }
        else
        {
            $assigned_sites = array_flip($this->EE->session->userdata('assigned_sites'));
			$this->EE->db->where('member_id', $this->EE->input->get_post('member_id'));
            $this->EE->db->where_in('site_id', $assigned_sites);
            $this->EE->db->delete('msm_member_groups');
        }
        
        $data = array();
        
        foreach ($this->EE->session->userdata('assigned_sites') as $site_id=>$site_label)
        {
            if ($this->EE->session->userdata('group_id')==1 || $this->EE->input->get_post('member_group_site_'.$site_id)!=1)
            {
				$data = array(
				   	'member_id'	=> $this->EE->input->get_post('member_id'),
			 		'group_id'	=> $this->EE->input->get_post('member_group_site_'.$site_id),
			 		'site_id'	=> $site_id
				   );
			   	$this->EE->db->insert('msm_member_groups', $data);
		   	}
        }
        
        $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('groups_set'));
        
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=msm_member_group_switcher'.AMP.'method=index');
    }
    
    


}
/* END */
?>