<?php
function get_tapatlk_location()
{
	global $user,$phpbb_root_path;
	$location = $user->extract_current_page($phpbb_root_path);
	$param_arr = array();
	switch ($location['page_name'])
    {
        case "viewforum.php":
        	if(!empty($_GET['f']))
        	{
        		$param_arr['fid'] = $_GET['f'];
        	} 
            $param_arr['location'] = 'forum';
            break;
        case "index.php":
        case '':
            $param_arr['location'] = 'index';
            break;
        case "ucp.php":
            if($_GET['i'] == "pm")
            {
                $param_arr['location'] = 'message';
                if(!empty($_GET['p']))
                $param_arr['mid'] = $_GET['p'];
            }
            if($_GET['mode'] == 'login')
            {
            	$param_arr['location'] = 'login';
            }
            break;
        case "search.php":
            $param_arr['location'] = "search";
            break;
        case "viewtopic.php":
            if(!empty($_GET['t']))
            {               
                //$param_arr['fid'] = $parameters['fid'];
                $param_arr['location'] = 'topic';
                $param_arr['tid'] = $_GET['t'];
            }
            break;
        case "memberlist.php":
           	if($_GET['mode'] == "viewprofile" && !empty($_GET['u']))
            {
                $param_arr['location'] = 'profile';
                $param_arr['uid'] = $_GET['u'];
            }

            break;
        case "viewonline.php":
            $param_arr['location'] = 'online';
            break;
        default:
            $param_arr['location'] = 'index';
            break;
    }
    $queryString = http_build_query($param_arr);
    $url = generate_board_url() . '/?' .$queryString;
    $url = preg_replace('/^(http|https)/isU', 'tapatalk', $url);
    return $url;
}