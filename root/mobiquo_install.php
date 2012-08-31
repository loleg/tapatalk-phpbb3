<?php

/**
*
* @author jeffrey jack15083@gmail.com 
* @package mobiquo
* @version $Id mobiquo_install.php
* @copyright (c) 2012 www.tapatalk.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();


if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/

$language_file = 'mods/info_acp_mobiquo';


// The name of the mod to be displayed during installation.
$mod_name = 'ACP_MOBIQUO_TITLE';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'mobiquo_version';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	// Version 1.2.10
	'3.4.0'	=> array(
	
		// Lets add a config setting and set it to true
		'config_add' => array(
			array('mobiquo_push', 1),
			array('mobiquo_hide_forum_id',''),
			array('mobiquo_guest_okay',1),
			array('mobiquo_reg_url','ucp.php?mode=register'),
			array('tapatalkdir','mobiquo'),
			array('mobiquo_is_chrome',1)
		),
		
		// Now lets add some modules to the ACP
		'module_add' => array(
            array('acp', 'ACP_CAT_DOT_MODS', 'ACP_MOBIQUO'),
		    array('acp', 'ACP_MOBIQUO', array(
					'module_basename'	=> 'mobiquo',
					'module_langname'	=> 'ACP_MOBIQUO_SETTINGS',
					'module_mode'		=> 'mobiquo',
					'module_auth'		=> 'acl_a_board',
				),
			),		
		),
		// now install the mobiquo table
		// see if it exists from prior versions
		'custom'	=> 'mobiquo_table',
		
		// Now to add some tables
		// one will hold the chats, the other the config
		'table_add' => array(
			array($table_prefix.'tapatalk_users', array(
					'COLUMNS'		=> array(
						'userid'		=> array('INT:10', 0),
						'announcement'	=> array('INT:5', 1),
						'pm'			=> array('INT:5', 1),
						'subscribe'	=> array('INT:5', 1),
						'quote'         => array('INT:5', 1),
			            'newtopic'      => array('INT:5', 1),
						'tag'           => array('INT:5', 1),
						'updated'	    => array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY'	=> 'userid',
				),
			),			
		),
	),
	'3.4.1' => array(
		// Now to add some permission settings
		'permission_add' => array(
			array('a_mobiquo'),
		),

		// Admins can do anything with mobiquo
		'permission_set' => array(
			array('ADMINISTRATORS', 'a_mobiquo', 'group'),
			// Global Role permissions for admins
			array('ROLE_ADMIN_FULL', 'a_mobiquo'),
		),
	),
);		

		

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

/*
* Here is our custom function that will be called
*
* @param string $action The action (install|update|uninstall) will be sent through this.
* @param string $version The version this is being run for will be sent through this.
*/
function mobiquo_table($action, $version)
{
	global $db, $table_prefix, $umil;

	if ($action == 'install')
	{
		// Run this when installing

		if ($umil->table_exists($table_prefix.'tapatalk_users'))
		{
			//table from previous version exists...delete it.
			$sql = 'DROP TABLE ' . $table_prefix.'tapatalk_users';
			$db->sql_query($sql);
			return 'MOBIQUO_TABLE_DELETED';
		}			
		
		return 'MOBIQUO_NOTHING_TO_UPDATE';
	}
}
	
?>