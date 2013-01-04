<?php
/**
 * 
 * push reply
 * @param int $post_id  the current post_id
 * @param array $current_topic_info
 */
function tapatalk_push_reply($data)
{
	global $db, $user, $config,$table_prefix,$phpbb_root_path,$phpEx;
	$is_only_alert = false;
	if(!function_exists('push_table_exists'))
	{
		define('IN_MOBIQUO', 1);
		require_once $phpbb_root_path . $config['tapatalkdir'] . '/xmlrpcresp.' . $phpEx;
	}
	if(!push_table_exists())
		return false;
	if(!check_push() || !(function_exists('curl_init') || ini_get('allow_url_fopen')))
	{
		$is_only_alert = true;
	}
	$return_status = false;
    if (!empty($data))// mobi_table_exists('tapatalk_users')
    {
    	$sql = "SELECT t.userid FROM " . $table_prefix . "tapatalk_users AS t  LEFT JOIN " .TOPICS_WATCH_TABLE . " AS w 
    	ON t.userid = w.user_id
    	WHERE w.topic_id = '".$data['topic_id']."' AND t.subscribe=1";
    	$result = $db->sql_query($sql);
    	while($row = $db->sql_fetchrow($result))
    	{
    		if ($row['userid'] == $user->data['user_id']) continue;
            $return_status = tt_send_push_data($row['userid'], 'sub', $data['topic_id'], $data['post_id'], $data['topic_title'], $user->data['username'],$is_only_alert);
    	}
    }
    return $return_status;
}
/**
 * 
 * push watch forum
 * @param array $current_topic_info
 */
function tapatalk_push_newtopic($data)
{
	global $db, $user, $config,$table_prefix,$phpbb_root_path,$phpEx;
	$return_status = false;
	$is_only_alert = false;
	if(!function_exists('push_table_exists'))
	{
		define('IN_MOBIQUO', 1);
		require_once $phpbb_root_path . $config['tapatalkdir'] . '/xmlrpcresp.' . $phpEx;
	}
	if(!push_table_exists())
		return false;
	if(!check_push() || !(function_exists('curl_init') || ini_get('allow_url_fopen')))
	{
		$is_only_alert = true;
	}	
    if (!empty($data))// mobi_table_exists('tapatalk_users')
    {
    	$sql = "SELECT t.userid FROM " . $table_prefix . "tapatalk_users AS t  LEFT JOIN " .FORUMS_WATCH_TABLE . " AS w 
    	ON t.userid = w.user_id
    	WHERE w.forum_id = '".$data['forum_id']."' AND t.newtopic = 1";
    	$result = $db->sql_query($sql);
    	while($row = $db->sql_fetchrow($result))
    	{
    		if ($row['userid'] == $user->data['user_id']) continue;
            $return_status = tt_send_push_data($row['userid'], 'newtopic', $data['topic_id'], $data['post_id'], $data['topic_title'], $user->data['username'],$is_only_alert);
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
    $is_only_alert = false;
	if(!function_exists('push_table_exists'))
	{
		define('IN_MOBIQUO', 1);
		require_once $phpbb_root_path . $config['tapatalkdir'] . '/xmlrpcresp.' . $phpEx;
	}
    if(!push_table_exists())
		return false;
	if(!check_push() || !(function_exists('curl_init') || ini_get('allow_url_fopen')))
	{
		$is_only_alert = true;
	}
	$return_status = false;
    if ($userid)
    {         
         $sql = "SELECT userid FROM " . $table_prefix . "tapatalk_users WHERE userid = '".$userid."' and pm =1";
         $result = $db->sql_query($sql);
         $row = $db->sql_fetchrow($result);
         if ($row['userid'] == $user->data['user_id']) return false;
         $db->sql_freeresult($result);
         if(!empty($row))
         {
        	 $return_status = tt_send_push_data($row['userid'], 'pm', $pm_id, '', $subject, $user->data['username'],$is_only_alert);
         }
    }
    return $return_status;     
}
function tapatalk_push_quote($data,$user_name_arr,$type="quote")
{
	global $db, $user, $config,$table_prefix,$phpbb_root_path,$phpEx;
	$return_status = false;
	$is_only_alert = false;
	if(!function_exists('push_table_exists'))
	{
		define('IN_MOBIQUO', 1);
		require_once $phpbb_root_path . $config['tapatalkdir'] . '/xmlrpcresp.' . $phpEx;
	}
	if(!push_table_exists())
		return false;
	if(!check_push() || !(function_exists('curl_init') || ini_get('allow_url_fopen')))
	{
		$is_only_alert = true;
	}
	if(!empty($user_name_arr) && !empty($data))
	{
		foreach ($user_name_arr as $username)
		{			
			$user_id = tt_get_user_id($username);
			if ($user_id == $user->data['user_id']) continue;
			if (empty($user_id)) continue;
			$sql = "SELECT userid FROM " . $table_prefix . "tapatalk_users WHERE userid = '".$user_id."' AND " . $type . " = 1" ;
	        $result = $db->sql_query($sql);
	        $row = $db->sql_fetchrow($result);
	        $db->sql_freeresult($result);
	        if(!empty($row))
	        {
	            $id = empty($data['topic_id']) ? $data['forum_id'] : $data['topic_id'];
	            $return_status = tt_send_push_data($row['userid'], $type, $id, $data['post_id'], $data['topic_title'], $user->data['username'],$is_only_alert);
	        }
			
		}
	}
	return $return_status;
}

function check_push()
{
	global $db,$config,$phpbb_root_path,$phpEx;
    if(!$config['mobiquo_push'])
        return false;
    return true;
}

function tt_do_post_request($data)
{
	$push_url = 'http://push.tapatalk.com/push.php';
	$timeout = isset($data['test']) || isset($data['ip']) ? 10 : 1;
	$response = 'CURL is disabled and PHP option "allow_url_fopen" is OFF. You can enable CURL or turn on "allow_url_fopen" in php.ini to fix this problem.';
	if (function_exists('curl_init'))
	{
		$ch = curl_init($push_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);

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
		$old = ini_set('default_socket_timeout', $timeout);
		$fp = @fopen($push_url, 'rb', false, $ctx);
		if (!$fp) return false;
		
		ini_set('default_socket_timeout', $old);
		stream_set_timeout($fp, $timeout);
		stream_set_blocking($fp, 0); 
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

function tt_get_user_id($username)
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

function tt_get_tag_list($str)
{
    if ( preg_match_all( '/(?<=^@|\s@)(#(.{1,50})#|\S{1,50}(?=[,\.;!\?]|\s|$))/U', $str, $tags ) )
    {
        foreach ($tags[2] as $index => $tag)
        {
            if ($tag) $tags[1][$index] = $tag;
        }
        
        return array_unique($tags[1]);
    }
    
    return array();
}

function tt_insert_push_data($data)
{
	global $table_prefix,$db;	
	if($data['type'] == 'pm')
	{
		$data['subid'] = $data['id'];
	}
	$sql_data[$table_prefix . "tapatalk_push_data"]['sql'] = array(
        'author' => $data['author'],
		'user_id' => $data['userid'],
		'data_type' => $data['type'],
		'title' => $data['title'],
		'data_id' => $data['subid'],
		'create_time' => $data['dateline']		
    );
    $sql = 'INSERT INTO ' . $table_prefix . "tapatalk_push_data" . ' ' .
    $db->sql_build_array('INSERT', $sql_data[$table_prefix . "tapatalk_push_data"]['sql']);
	$db->sql_query($sql);	
}

function tt_send_push_data($user_id,$type,$id,$sub_id,$title,$author,$is_only_alert=false)
{
	global $config;
    $boardurl = generate_board_url();
	$title = tt_push_clean($title);
	$author = tt_push_clean($author);
	$ttp_data = array(
                'userid'    => $user_id,
                'type'      => $type,
                'id'        => $id,
                'subid'     => $sub_id,
                'title'     => tt_push_clean($title),
                'author'    => tt_push_clean($author),
                'dateline'  => time(),
    );
    if(push_data_table_exists())
    	tt_insert_push_data($ttp_data);
    if($is_only_alert)
    {
    	return ;
    }
    $ttp_post_data = array(
          'url'  => $boardurl,
          'data' => base64_encode(serialize(array($ttp_data))),
       );
    if(!empty($config['tapatalk_push_key']))
    {
    	$ttp_post_data['key'] = $config['tapatalk_push_key'];
    }
    $return_status = tt_do_post_request($ttp_post_data);
    return $return_status;
}
function tt_get_user_push_type($userid)
{
	global $table_prefix,$db;
	if(!check_push())
	{
		return array();
	}
	$sql = "SELECT pm,subscribe as sub,quote,newtopic,tag FROM " . $table_prefix . "tapatalk_users WHERE userid = '".$userid."'";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    return $row;
}
?>