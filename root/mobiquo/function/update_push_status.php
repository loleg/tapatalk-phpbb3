<?php
/**
*
* @copyright (c) 2009, 2010, 2011 Quoord Systems Limited
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License (GPLv2)
*
*/

defined('IN_MOBIQUO') or exit;
function update_push_status_func($xmlrpc_params)
{
	global $db, $auth, $user, $config,$table_prefix;
	$params = php_xmlrpc_decode($xmlrpc_params);
	if(!empty($params[1]) && !empty($params[2]) && empty($user->data['is_registered']))
	{
		$user->setup('ucp');
	    
	    $username = $params[1];
	    $password = $params[2];
	    $viewonline =  1;
	    set_var($username, $username, 'string', true);
	    set_var($password, $password, 'string', true);
	    header('Set-Cookie: mobiquo_a=0');
	    header('Set-Cookie: mobiquo_b=0');
	    header('Set-Cookie: mobiquo_c=0');
	    $auth->login($username, $password, true, $viewonline);	
	}
	if($user->data['is_registered'] == 1)
	{
		$update_params = array();
		if(isset($params[0]['all']))
		{
			$update_params['announcement'] = $params[0]['all'] ? 1 : 0;
            $update_params['pm'] = $params[0]['all'] ? 1 : 0;
            $update_params['subscribe'] = $params[0]['all'] ? 1 : 0;
            $update_params['quote'] = $params[0]['all'] ? 1 : 0;
            $update_params['tag'] = $params[0]['all'] ? 1 : 0;
            $update_params['newtopic'] = $params[0]['all'] ? 1 : 0;		
		}
		else 
		{
			$update_params['announcement'] = isset($params[0]['ann']) ? $params[0]['ann'] : 1;
            $update_params['pm'] = isset($params[0]['pm']) ? $params[0]['pm'] : 1;
            $update_params['subscribe'] = isset($params[0]['sub']) ? $params[0]['sub'] : 1;
            $update_params['quote'] = isset($params[0]['quote']) ? $params[0]['quote'] : 1;
            $update_params['tag'] = isset($params[0]['tag']) ? $params[0]['tag'] : 1;
            $update_params['newtopic'] = isset($params[0]['newtopic']) ? $params[0]['newtopic'] : 1;
            	
		}
		$sql = 'UPDATE '. $table_prefix . "tapatalk_users SET announcement = '".$update_params['announcement']."',pm='".$update_params['pm']."',
		subscribe = '".$update_params['subscribe']."',quote = '".$update_params['quote']."',tag = '".$update_params['tag']."',newtopic='".$update_params['newtopic']."'
		WHERE userid = '".$user->data['user_id']."'";
		$result = $db->sql_query($sql);
		if($result)
		{
			return new xmlrpcresp(new xmlrpcval(true, 'boolean'));
		}
		else 
		{
			return new xmlrpcresp(new xmlrpcval(false, 'boolean'));
		}
	}
}