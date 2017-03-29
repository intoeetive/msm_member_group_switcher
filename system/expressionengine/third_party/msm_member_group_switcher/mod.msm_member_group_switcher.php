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
 File: mod.msm_member_group_switcher.php
-----------------------------------------------------
 Purpose: Switch member's group depending on what MSM site is he at
=====================================================
*/


if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}


class Msm_member_group_switcher {

    var $return_data	= ''; 						// Bah!
    
    var $settings = array();
    
    var $perpage = 25;
    

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
    	$this->EE =& get_instance(); 
    }
    /* END */

}
/* END */
?>