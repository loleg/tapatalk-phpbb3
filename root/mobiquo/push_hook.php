<?php
/**
 * 
 * push reply
 * @param int $post_id  the current post_id
 * @param array $current_topic_info
 */
function tapatalk_push_reply($post_id,$current_topic_info,$subject)
{
	global $db, $user, $config,$table_prefix,$phpbb_root_path,$phpEx;
	$boardurl = $config['server_protocol'].$config['server_name'].$config['script_path'];
	if(!check_push())
	{
		return false;
	}
	$return_status = false;
    if (!empty($current_topic_info) && $post_id && (function_exists('curl_init') || ini_get('allow_url_fopen')))// mobi_table_exists('tapatalk_users')
    {
    	$sql = "SELECT t.userid FROM " . $table_prefix . "tapatalk_users AS t  LEFT JOIN " .TOPICS_WATCH_TABLE . " AS w 
    	ON t.userid = w.user_id
    	WHERE w.topic_id = '".$current_topic_info['topic_id']."' AND t.subscribee=1";
    	$result = $db->sql_query($sql);
    	while($row = $db->sql_fetchrow($result))
    	{
    		 if ($row['userid'] == $user->data['user_id']) continue;
    		 if ($row['userid'] == $current_topic_info['topic_poster']) continue;
    		 $ttp_data = array(
                'userid'    => $row['userid'],
                'type'      => 'sub',
                'id'        => $current_topic_info['topic_id'],
                'subid'     => $post_id,
                'title'     => tt_push_clean($current_topic_info['topic_title']),
                'author'    => tt_push_clean($user->data['username']),
                'dateline'  => time(),
            );
            $ttp_post_data = array(
                'url'  => $boardurl,
                'data' => base64_encode(serialize(array($ttp_data))),
            );
            $return_status = tt_do_post_request($ttp_post_data);
    	}
    	unset($row);
    	$db->sql_freeresult($result);
        $sql = "SELECT userid FROM " . $table_prefix . "tapatalk_users WHERE userid = '".$current_topic_info['topic_poster']."' and subscribee=1";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        if ($row['userid'] == $user->data['user_id']) return 'from user_id is same with to user_id ';
        $db->sql_freeresult($result);
        if(!empty($row))
        {
            $ttp_data = array(
                'userid'    => $row['userid'],
                'type'      => 'sub',
                'id'        => $current_topic_info['topic_id'],
                'subid'     => $post_id,
                'title'     => tt_push_clean($current_topic_info['topic_title']),
                'author'    => tt_push_clean($user->data['username']),
                'dateline'  => time(),
            );
            $ttp_post_data = array(
                'url'  => $boardurl,
                'data' => base64_encode(serialize(array($ttp_data))),
            );
            $return_status = tt_do_post_request($ttp_post_data);
        }
    }
    return $return_status;
}
/**
 * 
 * push watch forum
 * @param array $current_topic_info
 */
function tapatalk_push_newtopic($post_id,$current_topic_info,$subject)
{
	global $db, $user, $config,$table_prefix,$phpbb_root_path,$phpEx;
	$boardurl = $config['server_protocol'].$config['server_name'].$config['script_path'];
	$return_status = false;
	if(!check_push())
	{
		return false;
	}
    if (!empty($current_topic_info) && (function_exists('curl_init') || ini_get('allow_url_fopen')))// mobi_table_exists('tapatalk_users')
    {
    	$sql = "SELECT t.userid FROM " . $table_prefix . "tapatalk_users AS t  LEFT JOIN " .FORUMS_WATCH_TABLE . " AS w 
    	ON t.userid = w.user_id
    	WHERE w.forum_id = '".$current_topic_info['forum_id']."' AND t.newtopic = 1";
    	$result = $db->sql_query($sql);
    	while($row = $db->sql_fetchrow($result))
    	{
    		 if ($row['userid'] == $user->data['user_id']) continue;
    		 if ($row['userid'] == $current_topic_info['topic_poster']) continue;
    		 $ttp_data = array(
                'userid'    => $row['userid'],
                'type'      => 'newtopic',
                'id'        => $current_topic_info['topic_id'],
                'subid'     => $post_id,
                'title'     => tt_push_clean($subject),
                'author'    => tt_push_clean($user->data['username']),
                'dateline'  => time(),
            );
            $ttp_post_data = array(
                'url'  => $boardurl,
                'data' => base64_encode(serialize(array($ttp_data))),
            );
            $return_status = tt_do_post_request($ttp_post_data);
    	}
    }
    return $return_status;
}
/**
 * 
 * push the private message
 * @param int $userid
 * @param int $pm_id
 * @param string $subject
 */
function tapatalk_push_pm($userid,$pm_id,$subject)
{
    global $db, $user, $config,$table_prefix,$boardurl,$phpbb_root_path,$phpEx;
	$boardurl = $config['server_protocol'].$config['server_name'].$config['script_path'];
	if(!check_push())
	{
		return false;
	}
	$return_status = false;
    if ($userid && !empty($subject) && (function_exists('curl_init') || ini_get('allow_url_fopen')))// mobi_table_exists('tapatalk_users')
    {         
         $sql = "SELECT userid FROM " . $table_prefix . "tapatalk_users WHERE userid = '".$userid."' and `pm` =1";
         $result = $db->sql_query($sql);
         $row = $db->sql_fetchrow($result);
         if ($row['userid'] == $user->data['user_id']) return 'from user_id is same with to user_id and `pm` =1';
         $db->sql_freeresult($result);
         if(!empty($row))
         {
         	 $ttp_data = array(
              'userid'    => $row['userid'],
                    'type'      => 'pm',
                    'id'        => $pm_id,
                    'title'     => tt_push_clean($subject),
                    'author'    => tt_push_clean($user->data['username']),
                    'dateline'  => time(),
              );
             $ttp_post_data = array(
                    'url'  => $boardurl,
                    'data' => base64_encode(serialize(array($ttp_data))),
              );
        	 $return_status = tt_do_post_request($ttp_post_data);
         }
    }
    return $return_status;     
}
function tapatalk_push_quote($post_id,$current_topic_info,$subject,$user_name_arr,$type="quote")
{
	if(($type == 'quote') || ($type == 'tag'))
	{
		$subject = $current_topic_info['topic_title'];
	}
	global $db, $user, $config,$table_prefix,$phpbb_root_path,$phpEx;
	$boardurl = $config['server_protocol'].$config['server_name'].$config['script_path'];
	$return_status = false;
	if(!check_push())
	{
		return false;
	}
	if(!empty($user_name_arr) && !empty($current_topic_info) && (function_exists('curl_init') || ini_get('allow_url_fopen')))
	{
		foreach ($user_name_arr as $username)
		{			
			$user_id = get_user_id($username);
			if ($user_id == $user->data['user_id']) continue;
			if ($user_id == $current_topic_info['topic_poster']) continue;
			$sql = "SELECT userid FROM " . $table_prefix . "tapatalk_users WHERE userid = '".$user_id."' AND " . $type . " = 1" ;
	        $result = $db->sql_query($sql);
	        $row = $db->sql_fetchrow($result);
	        $db->sql_freeresult($result);
	        if(!empty($row))
	        {
	            $ttp_data = array(
	                'userid'    => $row['userid'],
	                'type'      => $type,
	                'id'        => empty($current_topic_info['topic_id']) ? $current_topic_info['forum_id'] : $current_topic_info['topic_id'],
	                'subid'     => $post_id,
	                'title'     => tt_push_clean($subject),
	                'author'    => tt_push_clean($user->data['username']),
	                'dateline'  => time(),
	            );
	            $ttp_post_data = array(
	                'url'  => $boardurl,
	                'data' => base64_encode(serialize(array($ttp_data))),
	            );
	            $return_status = tt_do_post_request($ttp_post_data);
	        }
			
		}
	}
	return $return_status;
}

function check_push()
{
	global $db,$config,$table_prefix,$phpbb_root_path,$phpEx;
	require_once($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
	$db_tools = new phpbb_db_tools($db);
    if(method_exists($db_tools, 'sql_table_exists') && !$db_tools->sql_table_exists($table_prefix.'tapatalk_users'))
    {
    	return false;   		
    }
    elseif (!method_exists($db_tools, 'sql_table_exists')) {
    	$sql = "show tables";
    	$result = $db->sql_query($sql);
    	$tables_arr = array();
    	while($row = $db->sql_fetchrow($result))
    	{
    		foreach ($row as $value)
    		{
    			$tables_arr[] = $value;
    		}
    	}
    	$db->sql_freeresult($result);
    	if(!in_array($table_prefix.'tapatalk_users', $tables_arr))
    	{
    		return false;
    	}
    }
    if(!$config['mobiquo_push'])
        return false;
    return true;
}

function tt_do_post_request($data)
{
	$push_url = 'http://push.tapatalk.com/push.php';

	$response = 'CURL is disabled and PHP option "allow_url_fopen" is OFF. You can enable CURL or turn on "allow_url_fopen" in php.ini to fix this problem.';
	if (function_exists('curl_init'))
	{
		$ch = curl_init($push_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);

		$response = curl_exec($ch);
		curl_close($ch);
	}
	elseif (ini_get('allow_url_fopen'))
	{
		$params = array('http' => array(
			'method' => 'POST',
			'content' => http_build_query($data, '', '&'),
		));

		$ctx = stream_context_create($params);
		$timeout = 10;
		$old = ini_set('default_socket_timeout', $timeout);
		$fp = @fopen($push_url, 'rb', false, $ctx);
		ini_set('default_socket_timeout', $old);
		stream_set_timeout($fp, $timeout);
		stream_set_blocking($fp, 0); 
		
		if (!$fp) return false;
		$response = @stream_get_contents($fp);
	}
	return $response;
}

function tt_push_clean($str)
{
    $str = strip_tags($str);
    $str = trim($str);
    return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
}

function get_user_id($username)
{
    global $db;
    
    if (!$username)
    {
        return false;
    }
    
    $username_clean = $db->sql_escape(utf8_clean_string($username));
    
    $sql = 'SELECT user_id
            FROM ' . USERS_TABLE . "
            WHERE username_clean = '$username_clean'";
    $result = $db->sql_query($sql);
    $user_id = $db->sql_fetchfield('user_id');
    $db->sql_freeresult($result);
    
    return $user_id;
}
?>