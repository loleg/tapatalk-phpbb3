<?php
$phpbb_root_path = dirname(dirname(__FILE__)).'/';
define('IN_PHPBB', 1);
$phpEx = 'php';
define('IN_MOBIQUO', 1);
require_once 'xmlrpcresp.' . $phpEx;
include($phpbb_root_path . 'common.php');
//error_reporting(E_ALL & ~E_NOTICE);
if (isset($_GET['checkip']))
{
    print do_post_request(array('ip' => 1));
}
else if(isset($_GET['checkurl']))
{
	$url = generate_board_url();
	echo $url;
}
else if(isset($_GET['checkpush']))
{
	$return_status = do_post_request(array('test' => 1));
	$return_ip = do_post_request(array('ip' => 1));
	$string = file_get_contents($phpbb_root_path . 'includes/functions_posting.php');
	$url = generate_board_url();
	echo 'push enabled :' . ((!empty($config['mobiquo_push'])) ? ' on' : ' off' ) . '<br/>';
    echo 'test push data be send :' . (($return_status == 1) ? ' sucess' : ' failed ' . $return_status ). '<br/>';
    echo 'your server ip is : ' . $return_ip. '<br/>';
    echo 'push table is exist :' . (push_table_exists() ? ' yes' :  ' no' ). '<br/>';
    echo 'push code have been added in phpbb : ' . (strstr($string , 'tapatalk_push_reply($data);') ? 'yes' : 'no' ). '<br/>';
    echo 'your forum url is : ' . $url . '<br/>';
}
else
{
    echo 'Tapatalk push notification test: <b>';
    $return_status = do_post_request(array('test' => 1));
    if ($return_status === '1')
        echo 'Success</b>';
    else
        echo 'Failed</b><br />'.$return_status;
}

function do_post_request($data)
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