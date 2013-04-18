<?php
/**
*
* @copyright (c) 2009, 2010, 2011 Quoord Systems Limited
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License (GPLv2)
*
*/

defined('IN_MOBIQUO') or exit;

function get_config_func()
{    
    global $mobiquo_config, $config, $auth;
    
    $config_list = array(
        'sys_version'=> new xmlrpcval($config['version'], 'string'),
    	'is_open'    => new xmlrpcval($mobiquo_config['is_open'] ? true : false, 'boolean'),
        'guest_okay' => new xmlrpcval($mobiquo_config['guest_okay'] ? true : false, 'boolean'),
    );
	foreach($mobiquo_config as $key => $value)
    {
        if (!in_array($key, array('is_open', 'guest_okay', 'php_extension', 'shorten_quote', 'hide_forum_id', 'check_dnsbl')))
        {
            $config_list[$key] = new xmlrpcval($value, 'string');
        }
    }
    if(!$mobiquo_config['is_open'])
    {
    	$config_list['is_open'] = new xmlrpcval(0, 'string');
    	$config_list['result_text'] =  new xmlrpcval('Tapatalk pulgin is disabled','base64');
    }
    if($config['board_disable'])
    {
    	$config_list['is_open'] = new xmlrpcval(0, 'string');
    	$config_list['result_text'] = new xmlrpcval($config['board_disable_msg'],'base64');
    }
 	
    if(isset($config['mobiquo_push']) && push_table_exists())
    {
    	$config_list['alert'] = new xmlrpcval(1, 'string');
    }
    if ($auth->acl_get('u_search') && $auth->acl_getf_global('f_search') && $config['load_search'])
    {
        $config_list['guest_search'] = new xmlrpcval('1', 'string');
    }
    
    if ($auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
    {
        $config_list['guest_whosonline'] = new xmlrpcval('1', 'string');
    }
	if(!empty($config['tapatalk_allow_register']))
    {
    	$config_list['inappreg'] = new xmlrpcval('1', 'string');
    }
    if ($config['search_type'] == 'fulltext_native')
    {
        $config_list['min_search_length'] = new xmlrpcval($config['fulltext_native_min_chars'], 'int');
    }
    else if ($config['search_type'] == 'fulltext_mysql')
    {
        $config_list['min_search_length'] = new xmlrpcval($config['fulltext_mysql_min_word_len'], 'int');
    }
    $response = new xmlrpcval($config_list, 'struct');
    
    return new xmlrpcresp($response);
}
