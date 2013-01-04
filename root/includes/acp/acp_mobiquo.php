<?php
/**
* @ignore
*/
error_reporting(0);
if (!defined('IN_PHPBB'))
{
	exit;
}
/**
* @package acp
*/
class acp_mobiquo
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_mobiquo';
		add_form_key($form_key);
		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
			case 'mobiquo':
				$display_vars = array(
					'title'	=> 'ACP_MOBIQUO_SETTINGS',
					'vars'	=> array(
					'legend'				=> 'GENERAL_OPTIONS',
					'mobiquo_is_chrome'		=> array('lang' => 'MOBIQUO_IS_CHROME', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
					'mobiquo_guest_okay'	=> array('lang' => 'MOBIQUO_GUEST_OKAY', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
					'mobiquo_hide_forum_id'	=> array('lang' => 'MOBIQUO_HIDE_FORUM_ID', 'validate' => 'string',	'type' => 'custom',	'explain' => true,	'method' => 'select_box'),
					'tapatalkdir'			=> array('lang' => 'MOBIQUO_NAME', 'validate' => 'string', 'type' => 'text:10:12', 'explain' => true),
					'mobiquo_reg_url'		=> array('lang' => 'MOBIQUO_REG_URL', 'validate' => 'string', 'type' => 'text:30:40', 'explain' => true),
					'mobiquo_push'			=> array('lang' => 'MOBIQUO_PUSH', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),	
				    'tapatalk_push_key'		=> array('lang' => 'TAPATALK_PUSH_KEY', 'validate' => 'string','type' => 'text:40:60','explain' => true),
					'tapatalk_forum_read_only'	=> array('lang' => 'TAPATALK_FORUM_READ_ONLY', 'validate' => 'string',	'type' => 'custom',	'explain' => true,	'method' => 'select_box'),
					)
				);
				break;
			case 'rebranding':
				$display_vars = array(
					'title'	=> 'ACP_TAPATALK_REBRANDING',
					'vars'	=> array(
					'legend'				=> 'GENERAL_OPTIONS',
					'tapatalk_ipad_msg'		=> array('lang' => 'TAPATALK_IPAD_MESSAGE', 'validate' => 'string', 'type' => 'textarea:4:250', 'explain' => true),
					'tapatalk_ipad_url'	    => array('lang' => 'TAPATALK_IPAD_URL', 'validate' => 'string', 'type' => 'text:40:250', 'explain' => true),
					'tapatalk_android_msg'	=> array('lang' => 'TAPATALK_ANDROID_MESSAGE', 'validate' => 'string',	'type' => 'textarea:4:250',	'explain' => true),
					'tapatalk_android_url'	=> array('lang' => 'TAPATALK_ANDROID_URL', 'validate' => 'string', 'type' => 'text:40:250', 'explain' => true),
					'tapatalk_iphone_msg'	=> array('lang' => 'TAPATALK_IPHONE_MESSAGE', 'validate' => 'string', 'type' => 'textarea:4:250', 'explain' => true),
					'tapatalk_iphone_url'	=> array('lang' => 'TAPATALK_IPHONE_URL', 'validate' => 'string', 'type' => 'text:40:250', 'explain' => true),	
				    'tapatalk_kindle_msg'	=> array('lang' => 'TAPATALK_KINDLE_MESSAGE', 'validate' => 'string','type' => 'textarea:4:250','explain' => true),
					'tapatalk_kindle_url'   => array('lang' => 'TAPATALK_KINDLE_URL', 'validate' => 'string','type' => 'text:40:250','explain' => true),
					)
				);
				break;
		}
		

		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}
		
		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}
			if(isset($_REQUEST['mobiquo_hide_forum_id']))
			{
				$hide_forum_id = implode(',',$_REQUEST['mobiquo_hide_forum_id']);
				$cfg_array['mobiquo_hide_forum_id'] = $hide_forum_id;
			}
			elseif ($submit && empty($_REQUEST['mobiquo_hide_forum_id']))
			{
				$cfg_array['mobiquo_hide_forum_id'] = '';
			}
			if(isset($_REQUEST['tapatalk_forum_read_only']))
			{
				$forum_read_only = implode(',',$_REQUEST['tapatalk_forum_read_only']);
				$cfg_array['tapatalk_forum_read_only'] = $forum_read_only;
			}
			elseif ($submit && empty($_REQUEST['tapatalk_forum_read_only']))
			{
				$cfg_array['tapatalk_forum_read_only'] = '';
			}
			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				set_config($config_name, $config_value);

			}
		}

		if ($submit)
		{
			add_log('admin', 'LOG_CONFIG_' . strtoupper($mode));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_mobiquo';
		$this->page_title = $display_vars['title']; 

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],
			'L_ACP_MOBIQUO_MOD_VER'	=> $user->lang['ACP_MOBIQUO_MOD_VER'],
			'MOBIQUO_MOD_VERSION'	=> $config['mobiquo_version'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}
			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
	}
	
	function select_box($value, $key)
	{
		global $user, $config, $phpbb_root_path,$db,$strSelect;
		$strSelect = '<select id="' . $key . '" name="' . $key . '[]" multiple="multiple" size="8">';
		$forum_filter = '';
        $root_forum_id = 0;
		$sql = 'SELECT f.* '. ($user->data['is_registered'] ? ', fw.notify_status' : '') . '
            FROM ' . FORUMS_TABLE . ' f ' .
            ($user->data['is_registered'] ? ' LEFT JOIN ' . FORUMS_WATCH_TABLE . ' fw ON (fw.forum_id = f.forum_id AND fw.user_id = ' . $user->data['user_id'] . ')' : '') . 
            $forum_filter . '
            ORDER BY f.left_id ASC';
	    $result = $db->sql_query($sql, 600);
	    
	    $forum_rows = array();
	    while ($row = $db->sql_fetchrow($result))
	    {
	        $forum_id = $row['forum_id'];
	        $forum_rows[$forum_id] = $row;
	    }
	    $this->display_select_forum($forum_rows,0,$key);
	    $strSelect .= '</select>';
	    return $strSelect;
	} 
	
	function display_select_forum($rows,$parent_id,$key)
	{
		global $user, $config,$db,$strSelect;
		$selected = explode(',', $config[$key]);
		$i = 0;
		static $i;
		$topArr = $this->getChild($rows,$parent_id);
		foreach ($topArr as $info)
		{
			$strTag = '';
			for($j = 0;$j < $i;$j++)
			{
				$strTag .= '--';
			}
			$info['forum_name'] = $strTag . $info['forum_name'];
			$strSelect .= '<option value="' . $info['forum_id'] . '"' . ((in_array($info['forum_id'], $selected)) ? ' selected="selected"' : '') . '>' . $info['forum_name'] . '</option>';
			$childArr = $this->getChild($rows,$info['forum_id']);
			if(!empty($childArr))
			{
				$i++;
				$this->display_select_forum($rows, $info['forum_id'],$key);
				$i--;
			}
			else
			{
				continue;
			}					
		}
	}
	
	function getChild($row,$parent_id)
	{
		$temp = array();
		foreach ($row as $info) 
		{
			if($parent_id == $info['parent_id'])
			{
				$temp[] = $info; 
			}
		}
		return $temp;
	}
}
?>