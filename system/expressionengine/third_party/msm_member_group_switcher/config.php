<?php

if ( ! defined('MSM_MEMBER_GROUP_SWITCHER_ADDON_NAME'))
{
	define('MSM_MEMBER_GROUP_SWITCHER_ADDON_NAME',         'MSM Member Group Switcher');
	define('MSM_MEMBER_GROUP_SWITCHER_ADDON_VERSION',      '0.1');
}

$config['name'] = MSM_MEMBER_GROUP_SWITCHER_ADDON_NAME;
$config['version']= MSM_MEMBER_GROUP_SWITCHER_ADDON_VERSION;

$config['nsm_addon_updater']['versions_xml']='http://www.intoeetive.com/index.php/update.rss/125';