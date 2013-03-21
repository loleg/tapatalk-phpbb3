<?php
/**
*
* @copyright (c) 2009, 2010, 2011 Quoord Systems Limited
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License (GPLv2)
*
*/

defined('IN_MOBIQUO') or exit;

include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions_module.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);


$user->setup('mcp');

$module = new p_master();

// Basic parameter data
$id = request_var('i', '');

if (isset($_REQUEST['mode']) && is_array($_REQUEST['mode']))
{
    $mode = request_var('mode', array(''));
    list($mode, ) = each($mode);
}
else
{
    $mode = request_var('mode', '');
}

// Only Moderators can go beyond this point
if (!$user->data['is_registered'])
{
    trigger_error('LOGIN_EXPLAIN_MCP');
}

$quickmod = (isset($_REQUEST['quickmod'])) ? true : false;
$action = request_var('action', '');
$action_ary = request_var('action', array('' => 0));

$forum_action = request_var('forum_action', '');
if ($forum_action !== '' && !empty($_POST['sort']))
{
    $action = $forum_action;
}

if (sizeof($action_ary))
{
    list($action, ) = each($action_ary);
}
unset($action_ary);

if ($mode == 'topic_logs')
{
    $id = 'logs';
    $quickmod = false;
}

$post_id = request_var('p', 0);
$topic_id = request_var('t', 0);
$forum_id = request_var('f', 0);
$report_id = request_var('r', 0);
$user_id = request_var('u', 0);
$username = utf8_normalize_nfc(request_var('username', '', true));

if ($post_id)
{
    // We determine the topic and forum id here, to make sure the moderator really has moderative rights on this post
    $sql = 'SELECT topic_id, forum_id
        FROM ' . POSTS_TABLE . "
        WHERE post_id = $post_id";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    $_REQUEST['t'] = $topic_id = (int) $row['topic_id'];
    $_REQUEST['f'] = $forum_id = (int) ($row['forum_id']) ? $row['forum_id'] : $forum_id;
}
else if ($topic_id)
{
    $sql = 'SELECT forum_id, topic_first_post_id
        FROM ' . TOPICS_TABLE . "
        WHERE topic_id = $topic_id";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    $_REQUEST['f'] = $forum_id = (int) $row['forum_id'];
    if ($request_method == 'm_approve_topic') $_REQUEST['post_id_list'] = array($row['topic_first_post_id']);
}

// transform username to ban id and delete the users's posts 
if ($request_method == 'm_ban_user' && $request_params[1] == 2) {
	$ban_userid = get_user_id_by_name($request_params[0]);
	//judge the user post num is or not > 50
	$sql = "SELECT COUNT(*) AS tp_count FROM " . POSTS_TABLE . " p WHERE p.poster_id = '".$ban_userid."'";
	$result = $db->sql_query($sql);
	$countRow = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	if($countRow['tp_count'] > 50) {
		trigger_error('USER_POSTS_NUM GERATER THAN 50');
	}
	else {
		$sql = "SELECT post_id,topic_id,forum_id FROM " . POSTS_TABLE . " p WHERE p.poster_id = '".$ban_userid."'";
		$result1 = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result1)) {		
			$sql = 'SELECT f.*, t.*, p.*, u.username, u.username_clean, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield
				FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
				WHERE p.post_id = '".$row['post_id']."'
					AND t.topic_id = p.topic_id
					AND u.user_id = p.poster_id
					AND (f.forum_id = t.forum_id
						OR f.forum_id = '".$row['forum_id']."')" .
					(($auth->acl_get('m_approve', $row['forum_id'])) ? '' : 'AND p.post_approved = 1');
			$result2 = $db->sql_query($sql);
			$post_data = $db->sql_fetchrow($result2);
			$db->sql_freeresult($result2);
			handle_post_delete($row['forum_id'], $row['topic_id'], $row['post_id'], $post_data);
		}
		unset($row);
		$db->sql_freeresult($result1);
	}
		
}

// If the user doesn't have any moderator powers (globally or locally) he can't access the mcp
if (!$auth->acl_getf_global('m_'))
{
    // Except he is using one of the quickmod tools for users
    $user_quickmod_actions = array(
        'lock'            => 'f_user_lock',
        'make_sticky'    => 'f_sticky',
        'make_announce'    => 'f_announce',
        'make_global'    => 'f_announce',
        'make_normal'    => array('f_announce', 'f_sticky')
    );

    $allow_user = false;
    if ($quickmod && isset($user_quickmod_actions[$action]) && $user->data['is_registered'] && $auth->acl_gets($user_quickmod_actions[$action], $forum_id))
    {
        $topic_info = get_topic_data(array($topic_id));
        if ($topic_info[$topic_id]['topic_poster'] == $user->data['user_id'])
        {
            $allow_user = true;
        }
    }

    if (!$allow_user)
    {
        trigger_error('NOT_AUTHORISED');
    }
}

// if the user cannot read the forum he tries to access then we won't allow mcp access either
if ($forum_id && !$auth->acl_get('f_read', $forum_id))
{
    trigger_error('NOT_AUTHORISED');
}

if ($forum_id)
{
    $module->acl_forum_id = $forum_id;
}

// Instantiate module system and generate list of available modules
$module->list_modules('mcp');

if ($quickmod)
{
    $mode = 'quickmod';

    switch ($action)
    {
        case 'lock':
        case 'unlock':
        case 'lock_post':
        case 'unlock_post':
        case 'make_sticky':
        case 'make_announce':
        case 'make_global':
        case 'make_normal':
        case 'fork':
        case 'move':
        case 'delete_post':
        case 'delete_topic':
            $module->load('mcp', 'main', 'quickmod');
            return;
        break;

        case 'topic_logs':
            // Reset start parameter if we jumped from the quickmod dropdown
            if (request_var('start', 0))
            {
                $_REQUEST['start'] = 0;
            }

            $module->set_active('logs', 'topic_logs');
        break;

        case 'merge_topic':
            $module->set_active('main', 'forum_view');
        break;

        case 'split':
        case 'merge':
            $module->set_active('main', 'topic_view');
        break;

        default:
            trigger_error("$action not allowed as quickmod", E_USER_ERROR);
        break;
    }
}
else
{
    // Select the active module
    $module->set_active($id, $mode);
}

$module->load_active();


function m_get_moderate_topic_func()
{
    global $template, $auth, $user;
    
    $posts = array();
    foreach($template->_tpldata['postrow'] as $postinfo) {
        $posts[] = $postinfo['POST_ID'];
    }
    
    $posts = get_post_data($posts);
    
    $post_list = array();
    foreach($template->_tpldata['postrow'] as $postinfo)
    {
        $post = $posts[$postinfo['POST_ID']];
        
        if (empty($post['forum_id']))
        {
            $post['forum_id'] = 0;
            $post['forum_name'] = $user->lang['ANNOUNCEMENTS'];
        }
        
        $post_list[] = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($post['forum_id']),
            'forum_name'        => new xmlrpcval(basic_clean($post['forum_name']), 'base64'),
            'topic_id'          => new xmlrpcval($post['topic_id']),
            'topic_title'       => new xmlrpcval(basic_clean($post['topic_title']), 'base64'),
            'post_id'           => new xmlrpcval($post['post_id']),
            'post_title'        => new xmlrpcval(basic_clean($post['post_subject']), 'base64'),
            'topic_author_name' => new xmlrpcval(basic_clean($post['username']), 'base64'),
            'icon_url'          => new xmlrpcval(get_user_avatar_url($post['user_avatar'], $post['user_avatar_type'])),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($post['post_time']), 'dateTime.iso8601'),
            'short_content'     => new xmlrpcval(process_short_content($post['post_text']), 'base64'),
            'can_delete'        => new xmlrpcval($auth->acl_get('m_delete', $post['forum_id']), 'boolean'),
        ), 'struct');
    }
    
    preg_match('/(\d+)\D/', $template->_rootref['TOTAL'], $total);
    
    $response = new xmlrpcval(
        array(
            'total_topic_num' => new xmlrpcval($total[1], 'int'),
            'topics'          => new xmlrpcval($post_list, 'array'),
        ), 'struct'
    );

    return new xmlrpcresp($response);
}

function m_rename_topic_func($xmlrpc_params) 
{
	global $db, $auth, $user, $config, $template, $cache, $phpEx, $phpbb_root_path, $phpbb_home;
    
    $user->setup('posting');
    
    include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

    $params = php_xmlrpc_decode($xmlrpc_params);

    $submit     = true;
    $preview    = false;
    $refresh    = false;
    $mode       = 'edit';

    // get topic information from parameters
    $topic_id        = intval($params[0]);
    $post_title     = $params[1];
    //$post_content   = $params[2];
    $GLOBALS['return_html'] = isset($params[3]) ? $params[3] : false;
	
    $sql = 'SELECT * FROM ' . POSTS_TABLE .' WHERE topic_id = ' . $topic_id .' ORDER BY post_time ASC';
    $result = $db->sql_query_limit($sql, 1);
    $first_post_data = $db->sql_fetchrow($result);
    $post_id = $first_post_data['post_id'];
    if(empty($post_id))
    {
    	trigger_error('NO_POST');
    }
    $post_data = array();

    $sql = 'SELECT p.*, t.*, f.*, u.username
            FROM ' . POSTS_TABLE . ' p
                LEFT JOIN ' . TOPICS_TABLE . ' t ON (p.topic_id = t.topic_id) 
                LEFT JOIN ' . FORUMS_TABLE . ' f ON (t.forum_id = f.forum_id OR (t.topic_type = ' . POST_GLOBAL . ' AND f.forum_type = ' . FORUM_POST . '))
                LEFT JOIN ' . USERS_TABLE  . ' u ON (p.poster_id = u.user_id)' . "
            WHERE p.post_id = $post_id";

    $result = $db->sql_query_limit($sql, 1);
    $post_data = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    if (!$post_data) trigger_error('NO_POST');
    
    // Use post_row values in favor of submitted ones...
    $forum_id = (int) $post_data['forum_id'];
    $topic_id = (int) $post_data['topic_id'];
    $post_id  = (int) $post_id;

    // Need to login to passworded forum first?
    if ($post_data['forum_password'] && !check_forum_password($forum_id))
    {
        trigger_error('LOGIN_FORUM');
    }

    // Is the user able to read within this forum?
    if (!$auth->acl_get('f_read', $forum_id))
    {
        trigger_error('USER_CANNOT_READ');
    }

    // Permission to do the action asked?
    if (!($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $forum_id)))
    {
        trigger_error('USER_CANNOT_EDIT');
    }

    // Forum/Topic locked?
    if (($post_data['forum_status'] == ITEM_LOCKED || (isset($post_data['topic_status']) && $post_data['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $forum_id))
    {
        trigger_error(($post_data['forum_status'] == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED');
    }

    // Can we edit this post ... if we're a moderator with rights then always yes
    // else it depends on editing times, lock status and if we're the correct user
    if (!$auth->acl_get('m_edit', $forum_id))
    {
        if ($user->data['user_id'] != $post_data['poster_id'])
        {
            trigger_error('USER_CANNOT_EDIT');
        }

        if (!($post_data['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time']))
        {
            trigger_error('CANNOT_EDIT_TIME');
        }

        if ($post_data['post_edit_locked'])
        {
            trigger_error('CANNOT_EDIT_POST_LOCKED');
        }
    }

    // Determine some vars
    if (isset($post_data['poster_id']) && $post_data['poster_id'] == ANONYMOUS)
    {
        $post_data['quote_username'] = (!empty($post_data['post_username'])) ? $post_data['post_username'] : $user->lang['GUEST'];
    }
    else
    {
        $post_data['quote_username'] = isset($post_data['username']) ? $post_data['username'] : '';
    }

    $post_data['post_edit_locked']    = (isset($post_data['post_edit_locked'])) ? (int) $post_data['post_edit_locked'] : 0;
    $post_data['post_subject']        = (in_array($mode, array('quote', 'edit'))) ? $post_data['post_subject'] : ((isset($post_data['topic_title'])) ? $post_data['topic_title'] : '');
    $post_data['topic_time_limit']    = (isset($post_data['topic_time_limit'])) ? (($post_data['topic_time_limit']) ? (int) $post_data['topic_time_limit'] / 86400 : (int) $post_data['topic_time_limit']) : 0;
    $post_data['poll_length']        = (!empty($post_data['poll_length'])) ? (int) $post_data['poll_length'] / 86400 : 0;
    $post_data['poll_start']        = (!empty($post_data['poll_start'])) ? (int) $post_data['poll_start'] : 0;
    $post_data['icon_id']            = (!isset($post_data['icon_id']) || in_array($mode, array('quote', 'reply'))) ? 0 : (int) $post_data['icon_id'];
    $post_data['poll_options']        = array();

    // Get Poll Data
    if ($post_data['poll_start'])
    {
        $sql = 'SELECT poll_option_text
            FROM ' . POLL_OPTIONS_TABLE . "
            WHERE topic_id = $topic_id
            ORDER BY poll_option_id";
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result))
        {
            $post_data['poll_options'][] = trim($row['poll_option_text']);
        }
        $db->sql_freeresult($result);
    }

    $orig_poll_options_size = sizeof($post_data['poll_options']);

    $message_parser = new parse_message();

    if (isset($post_data['post_text']))
    {
        $message_parser->message = &$post_data['post_text'];
        unset($post_data['post_text']);
    }

    // Set some default variables
    $uninit = array('post_attachment' => 0, 'poster_id' => $user->data['user_id'], 'enable_magic_url' => 0, 'topic_status' => 0, 'topic_type' => POST_NORMAL, 'post_subject' => '', 'topic_title' => '', 'post_time' => 0, 'post_edit_reason' => '', 'notify_set' => 0);

    foreach ($uninit as $var_name => $default_value)
    {
        if (!isset($post_data[$var_name]))
        {
            $post_data[$var_name] = $default_value;
        }
    }
    unset($uninit);

    // Always check if the submitted attachment data is valid and belongs to the user.
    // Further down (especially in submit_post()) we do not check this again.
    $message_parser->get_submitted_attachment_data($post_data['poster_id']);

    if ($post_data['post_attachment'] && !$refresh && !$preview && $mode == 'edit')
    {
        // Do not change to SELECT *
        $sql = 'SELECT attach_id, is_orphan, attach_comment, real_filename
            FROM ' . ATTACHMENTS_TABLE . "
            WHERE post_msg_id = $post_id
                AND in_message = 0
                AND is_orphan = 0
            ORDER BY filetime DESC";
        $result = $db->sql_query($sql);
        $message_parser->attachment_data = array_merge($message_parser->attachment_data, $db->sql_fetchrowset($result));
        $db->sql_freeresult($result);
    }

    if ($post_data['poster_id'] == ANONYMOUS)
    {
        $post_data['username'] = ($mode == 'quote' || $mode == 'edit') ? trim($post_data['post_username']) : '';
    }
    else
    {
        $post_data['username'] = ($mode == 'quote' || $mode == 'edit') ? trim($post_data['username']) : '';
    }

    $post_data['enable_urls'] = $post_data['enable_magic_url'];

    $post_data['enable_magic_url'] = $post_data['drafts'] = false;

    $check_value = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);

    // Check if user is watching this topic
    if ($mode != 'post' && $config['allow_topic_notify'] && $user->data['is_registered'])
    {
        $sql = 'SELECT topic_id
            FROM ' . TOPICS_WATCH_TABLE . '
            WHERE topic_id = ' . $topic_id . '
                AND user_id = ' . $user->data['user_id'];
        $result = $db->sql_query($sql);
        $post_data['notify_set'] = (int) $db->sql_fetchfield('topic_id');
        $db->sql_freeresult($result);
    }

    // Do we want to edit our post ?
    if ($post_data['bbcode_uid'])
    {
        $message_parser->bbcode_uid = $post_data['bbcode_uid'];
    }

    // HTML, BBCode, Smilies, Images and Flash status
    $bbcode_status    = ($config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
    $smilies_status    = ($bbcode_status && $config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
    $img_status        = ($bbcode_status && $auth->acl_get('f_img', $forum_id)) ? true : false;
    $url_status        = ($config['allow_post_links']) ? true : false;
    $flash_status    = ($bbcode_status && $auth->acl_get('f_flash', $forum_id) && $config['allow_post_flash']) ? true : false;
    $quote_status    = ($auth->acl_get('f_reply', $forum_id)) ? true : false;

    $solved_captcha = false;

    $post_data['topic_cur_post_id']    = request_var('topic_cur_post_id', 0);
    $post_data['post_subject']        = utf8_normalize_nfc($post_title);
    //$message_parser->message        = utf8_normalize_nfc(htmlspecialchars($post_content));

    $post_data['username']            = utf8_normalize_nfc(request_var('username', $post_data['username'], true));
    $post_data['post_edit_reason']    = (!empty($_POST['edit_reason']) && $mode == 'edit' && $auth->acl_get('m_edit', $forum_id)) ? utf8_normalize_nfc(request_var('edit_reason', '', true)) : '';

    $post_data['orig_topic_type']    = $post_data['topic_type'];
    $post_data['topic_type']        = request_var('topic_type', (($mode != 'post') ? (int) $post_data['topic_type'] : POST_NORMAL));
    $post_data['topic_time_limit']    = request_var('topic_time_limit', (($mode != 'post') ? (int) $post_data['topic_time_limit'] : 0));
    $post_data['icon_id']            = request_var('icon', 0);

    $post_data['enable_bbcode']        = (!$bbcode_status || isset($_POST['disable_bbcode'])) ? false : true;
    $post_data['enable_smilies']    = (!$smilies_status || isset($_POST['disable_smilies'])) ? false : true;
    $post_data['enable_urls']        = (isset($_POST['disable_magic_url'])) ? 0 : 1;
    $post_data['enable_sig']        = (!$config['allow_sig'] || !$auth->acl_get('f_sigs', $forum_id) || !$auth->acl_get('u_sig')) ? false : ($user->data['is_registered'] ? true : false);

    if ($config['allow_topic_notify'] && $user->data['is_registered'])
    {
        $notify = (isset($_POST['notify'])) ? true : false;
    }
    else
    {
        $notify = false;
    }

    $topic_lock     = (isset($_POST['lock_topic'])) ? true : false;
    $post_lock      = (isset($_POST['lock_post'])) ? true : false;
    $poll_delete    = (isset($_POST['poll_delete'])) ? true : false;

    $status_switch = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);
    $status_switch = ($status_switch != $check_value);

    //$post_data['poll_title']        = utf8_normalize_nfc(request_var('poll_title', '', true));
    //$post_data['poll_length']        = request_var('poll_length', 0);
    //$post_data['poll_option_text']    = utf8_normalize_nfc(request_var('poll_option_text', '', true));
    $post_data['poll_option_text'] = implode("\n", $post_data['poll_options']);
    //$post_data['poll_max_options']    = request_var('poll_max_options', 1);
    //$post_data['poll_vote_change']    = ($auth->acl_get('f_votechg', $forum_id) && isset($_POST['poll_vote_change'])) ? 1 : 0;

    // Parse Attachments - before checksum is calculated
    $message_parser->parse_attachments('fileupload', $mode, $forum_id, $submit, $preview, $refresh);

    // Grab md5 'checksum' of new message
    //$message_md5 = md5($message_parser->message);

    // Check checksum ... don't re-parse message if the same
    $update_message = ($mode != 'edit' || $status_switch || strlen($post_data['bbcode_uid']) < BBCODE_UID_LEN) ? true : false;

    // Parse message
    if ($update_message)
    {
        if (sizeof($message_parser->warn_msg))
        {
            trigger_error(join("\n", $message_parser->warn_msg));
        }

        $message_parser->parse($post_data['enable_bbcode'], ($config['allow_post_links']) ? $post_data['enable_urls'] : false, $post_data['enable_smilies'], $img_status, $flash_status, $quote_status, $config['allow_post_links']);
    }
    else
    {
        $message_parser->bbcode_bitfield = $post_data['bbcode_bitfield'];
    }

    // Validate username
    if (($post_data['username'] && !$user->data['is_registered']) || ($mode == 'edit' && $post_data['poster_id'] == ANONYMOUS && $post_data['username'] && $post_data['post_username'] && $post_data['post_username'] != $post_data['username']))
    {
        include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

        if (($result = validate_username($post_data['username'], (!empty($post_data['post_username'])) ? $post_data['post_username'] : '')) !== false)
        {
            $user->add_lang('ucp');
            trigger_error($result . '_USERNAME');
        }
    }

    // Parse subject
    if (utf8_clean_string($post_data['post_subject']) === '' && $post_data['topic_first_post_id'] == $post_id)
    {
        trigger_error('EMPTY_SUBJECT');
    }

    $post_data['poll_last_vote'] = (isset($post_data['poll_last_vote'])) ? $post_data['poll_last_vote'] : 0;

    if ($post_data['poll_option_text'] && $post_id == $post_data['topic_first_post_id']
        && $auth->acl_get('f_poll', $forum_id))
    {
        $poll = array(
            'poll_title'        => $post_data['poll_title'],
            'poll_length'        => $post_data['poll_length'],
            'poll_max_options'    => $post_data['poll_max_options'],
            'poll_option_text'    => $post_data['poll_option_text'],
            'poll_start'        => $post_data['poll_start'],
            'poll_last_vote'    => $post_data['poll_last_vote'],
            'poll_vote_change'    => $post_data['poll_vote_change'],
            'enable_bbcode'        => $post_data['enable_bbcode'],
            'enable_urls'        => $post_data['enable_urls'],
            'enable_smilies'    => $post_data['enable_smilies'],
            'img_status'        => $img_status
        );

        $message_parser->parse_poll($poll);

        $post_data['poll_options'] = (isset($poll['poll_options'])) ? $poll['poll_options'] : '';
        $post_data['poll_title'] = (isset($poll['poll_title'])) ? $poll['poll_title'] : '';
    }
    else
    {
        $poll = array();
    }

    // Check topic type
    if ($post_data['topic_type'] != POST_NORMAL && $post_data['topic_first_post_id'] == $post_id)
    {
        switch ($post_data['topic_type'])
        {
            case POST_GLOBAL:
            case POST_ANNOUNCE:
                $auth_option = 'f_announce';
            break;

            case POST_STICKY:
                $auth_option = 'f_sticky';
            break;

            default:
                $auth_option = '';
            break;
        }

        if (!$auth->acl_get($auth_option, $forum_id))
        {
            // There is a special case where a user edits his post whereby the topic type got changed by an admin/mod.
            // Another case would be a mod not having sticky permissions for example but edit permissions.
            // To prevent non-authed users messing around with the topic type we reset it to the original one.
            $post_data['topic_type'] = $post_data['orig_topic_type'];
        }
    }

    // DNSBL check
    if ($config['check_dnsbl'])
    {
        if (($dnsbl = $user->check_dnsbl('post')) !== false)
        {
            trigger_error(sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]));
        }
    }

    // Check if we want to de-globalize the topic... and ask for new forum
    if ($post_data['topic_type'] != POST_GLOBAL)
    {
        $sql = 'SELECT topic_type, forum_id
            FROM ' . TOPICS_TABLE . "
            WHERE topic_id = $topic_id";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if ($row && !$row['forum_id'] && $row['topic_type'] == POST_GLOBAL)
        {
            $to_forum_id = request_var('to_forum_id', 0);

            if ($to_forum_id)
            {
                $sql = 'SELECT forum_type
                    FROM ' . FORUMS_TABLE . '
                    WHERE forum_id = ' . $to_forum_id;
                $result = $db->sql_query($sql);
                $forum_type = (int) $db->sql_fetchfield('forum_type');
                $db->sql_freeresult($result);

                if ($forum_type != FORUM_POST || !$auth->acl_get('f_post', $to_forum_id))
                {
                    $to_forum_id = 0;
                }
            }

            if (!$to_forum_id)
            {
                //include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

                $template->assign_vars(array(
                    'S_FORUM_SELECT'    => make_forum_select(false, false, false, true, true, true),
                    'S_UNGLOBALISE'        => true)
                );

                $submit = false;
                $refresh = true;
            }
            else
            {
                if (!$auth->acl_get('f_post', $to_forum_id))
                {
                    // This will only be triggered if the user tried to trick the forum.
                    trigger_error('NOT_AUTHORISED');
                }

                $forum_id = $to_forum_id;
            }
        }
    }

    // Lock/Unlock Topic
    $change_topic_status = $post_data['topic_status'];
    $perm_lock_unlock = ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && !empty($post_data['topic_poster']) && $user->data['user_id'] == $post_data['topic_poster'] && $post_data['topic_status'] == ITEM_UNLOCKED)) ? true : false;

    if ($post_data['topic_status'] == ITEM_LOCKED && !$topic_lock && $perm_lock_unlock)
    {
        $change_topic_status = ITEM_UNLOCKED;
    }
    else if ($post_data['topic_status'] == ITEM_UNLOCKED && $topic_lock && $perm_lock_unlock)
    {
        $change_topic_status = ITEM_LOCKED;
    }

    if ($change_topic_status != $post_data['topic_status'])
    {
        $sql = 'UPDATE ' . TOPICS_TABLE . "
            SET topic_status = $change_topic_status
            WHERE topic_id = $topic_id
                AND topic_moved_id = 0";
        $db->sql_query($sql);

        $user_lock = ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $post_data['topic_poster']) ? 'USER_' : '';

        add_log('mod', $forum_id, $topic_id, 'LOG_' . $user_lock . (($change_topic_status == ITEM_LOCKED) ? 'LOCK' : 'UNLOCK'), $post_data['topic_title']);
    }

    // Lock/Unlock Post Edit
    if ($mode == 'edit' && $post_data['post_edit_locked'] == ITEM_LOCKED && !$post_lock && $auth->acl_get('m_edit', $forum_id))
    {
        $post_data['post_edit_locked'] = ITEM_UNLOCKED;
    }
    else if ($mode == 'edit' && $post_data['post_edit_locked'] == ITEM_UNLOCKED && $post_lock && $auth->acl_get('m_edit', $forum_id))
    {
        $post_data['post_edit_locked'] = ITEM_LOCKED;
    }
	
    $data = array(
        'topic_title'            => (empty($post_data['topic_title'])) ? $post_data['post_subject'] : $post_data['topic_title'],
        'topic_first_post_id'    => (isset($post_data['topic_first_post_id'])) ? (int) $post_data['topic_first_post_id'] : 0,
        'topic_last_post_id'    => (isset($post_data['topic_last_post_id'])) ? (int) $post_data['topic_last_post_id'] : 0,
        'topic_time_limit'        => (int) $post_data['topic_time_limit'],
        'topic_attachment'        => (isset($post_data['topic_attachment'])) ? (int) $post_data['topic_attachment'] : 0,
        'post_id'                => (int) $post_id,
        'topic_id'                => (int) $topic_id,
        'forum_id'                => (int) $forum_id,
        'icon_id'                => (int) $post_data['icon_id'],
        'poster_id'                => (int) $post_data['poster_id'],
        'enable_sig'            => (bool) $post_data['enable_sig'],
        'enable_bbcode'            => (bool) $post_data['enable_bbcode'],
        'enable_smilies'        => (bool) $post_data['enable_smilies'],
        'enable_urls'            => (bool) $post_data['enable_urls'],
        'enable_indexing'        => (bool) $post_data['enable_indexing'],
        'message_md5'            => $post_data['post_checksum'],
        'post_time'                => (isset($post_data['post_time'])) ? (int) $post_data['post_time'] : time(),
        'post_checksum'            => (isset($post_data['post_checksum'])) ? (string) $post_data['post_checksum'] : '',
        'post_edit_reason'        => $post_data['post_edit_reason'],
        'post_edit_user'        => ($mode == 'edit') ? $user->data['user_id'] : ((isset($post_data['post_edit_user'])) ? (int) $post_data['post_edit_user'] : 0),
        'forum_parents'            => $post_data['forum_parents'],
        'forum_name'            => $post_data['forum_name'],
        'notify'                => $notify,
        'notify_set'            => $post_data['notify_set'],
        'poster_ip'                => (isset($post_data['poster_ip'])) ? $post_data['poster_ip'] : $user->ip,
        'post_edit_locked'        => (int) $post_data['post_edit_locked'],
        'bbcode_bitfield'        => $message_parser->bbcode_bitfield,
        'bbcode_uid'            => $message_parser->bbcode_uid,
        //'message'                => $message_parser->message,
        'attachment_data'        => $message_parser->attachment_data,
        'filename_data'            => $message_parser->filename_data,

        'topic_approved'        => (isset($post_data['topic_approved'])) ? $post_data['topic_approved'] : false,
        'post_approved'            => (isset($post_data['post_approved'])) ? $post_data['post_approved'] : false,
    );

    $data['topic_replies_real'] = $post_data['topic_replies_real'];
    $data['topic_replies'] = $post_data['topic_replies'];

    //include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
    $cwd = getcwd();
    chdir('../');
    $redirect_url = submit_post($mode, $post_data['post_subject'], $post_data['username'], $post_data['topic_type'], $poll, $data, $update_message);
    chdir($cwd);
    
    // Check the permissions for post approval, as well as the queue trigger where users are put on approval with a post count lower than specified. Moderators are not affected.
    $approved = true;
    if ((($config['enable_queue_trigger'] && $user->data['user_posts'] < $config['queue_trigger_posts']) || !$auth->acl_get('f_noapprove', $data['forum_id'])) && !$auth->acl_get('m_approve', $data['forum_id']))
    {
        $approved = false;
    }

    $reply_success = false;
	if($redirect_url)
	{
		$reply_success = true;
	}
    $xmlrpc_reply_topic = new xmlrpcval(array(
        'result'    => new xmlrpcval($reply_success, 'boolean'),
        'is_login_mod'  => new xmlrpcval(true, 'boolean'),
        'result_text'   => new xmlrpcval("", 'base64')
    ), 'struct');

    return new xmlrpcresp($xmlrpc_reply_topic);
}

function m_get_moderate_post_func()
{
    global $template, $auth, $user;
    
    $posts = array();
    foreach($template->_tpldata['postrow'] as $postinfo) {
        $posts[] = $postinfo['POST_ID'];
    }
    
    $posts = get_post_data($posts);
    
    $post_list = array();
    foreach($template->_tpldata['postrow'] as $postinfo)
    {
        $post = $posts[$postinfo['POST_ID']];
        
        if (empty($post['forum_id']))
        {
            $post['forum_id'] = 0;
            $post['forum_name'] = $user->lang['ANNOUNCEMENTS'];
        }
        
        $post_list[] = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($post['forum_id']),
            'forum_name'        => new xmlrpcval(basic_clean($post['forum_name']), 'base64'),
            'topic_id'          => new xmlrpcval($post['topic_id']),
            'topic_title'       => new xmlrpcval(basic_clean($post['topic_title']), 'base64'),
            'post_id'           => new xmlrpcval($post['post_id']),
            'post_title'        => new xmlrpcval(basic_clean($post['post_subject']), 'base64'),
            'post_author_name'  => new xmlrpcval(basic_clean($post['username']), 'base64'),
            'icon_url'          => new xmlrpcval(get_user_avatar_url($post['user_avatar'], $post['user_avatar_type'])),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($post['post_time']), 'dateTime.iso8601'),
            'short_content'     => new xmlrpcval(process_short_content($post['post_text']), 'base64'),
            'can_delete'        => new xmlrpcval($auth->acl_get('m_delete', $forum_id), 'boolean'),
        ), 'struct');
    }
    
    preg_match('/(\d+)\D/', $template->_rootref['TOTAL'], $total);
    
    $response = new xmlrpcval(
        array(
            'total_post_num' => new xmlrpcval($total[1], 'int'),
            'posts'          => new xmlrpcval($post_list, 'array'),
        ), 'struct'
    );

    return new xmlrpcresp($response);
}

function m_get_report_post_func()
{
    global $template, $auth, $user;
    
    $posts = array();
    foreach($template->_tpldata['postrow'] as $postinfo) {
        $posts[] = $postinfo['POST_ID'];
    }
    
    $posts = get_post_data($posts);
    
    $post_list = array();
    foreach($template->_tpldata['postrow'] as $postinfo)
    {
        $post = $posts[$postinfo['POST_ID']];
        
        if (empty($post['forum_id']))
        {
            $user->setup('viewforum');
            $post['forum_id'] = 0;
            $post['forum_name'] = $user->lang['ANNOUNCEMENTS'];
        }
        
        $post_list[] = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($post['forum_id']),
            'forum_name'        => new xmlrpcval(basic_clean($post['forum_name']), 'base64'),
            'topic_id'          => new xmlrpcval($post['topic_id']),
            'topic_title'       => new xmlrpcval(basic_clean($post['topic_title']), 'base64'),
            'post_id'           => new xmlrpcval($post['post_id']),
            'post_title'        => new xmlrpcval(basic_clean($post['post_subject']), 'base64'),
            'post_author_name'  => new xmlrpcval(basic_clean($post['username']), 'base64'),
            'icon_url'          => new xmlrpcval(get_user_avatar_url($post['user_avatar'], $post['user_avatar_type'])),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($post['post_time']), 'dateTime.iso8601'),
            'short_content'     => new xmlrpcval(process_short_content($post['post_text']), 'base64'),
            'can_delete'        => new xmlrpcval($auth->acl_get('m_delete', $forum_id), 'boolean'),
        ), 'struct');
    }
    
    $response = new xmlrpcval(
        array(
            'total_report_num' => new xmlrpcval($template->_rootref['TOTAL'], 'int'),
            'reports'          => new xmlrpcval($post_list, 'array'),
        ), 'struct'
    );

    return new xmlrpcresp($response);
}


function extra_url()
{
    global $forum_id, $topic_id, $post_id, $report_id, $user_id;
    
    $url_extra = '';
    $url_extra .= ($forum_id) ? "&amp;f=$forum_id" : '';
    $url_extra .= ($topic_id) ? "&amp;t=$topic_id" : '';
    $url_extra .= ($post_id) ? "&amp;p=$post_id" : '';
    $url_extra .= ($user_id) ? "&amp;u=$user_id" : '';
    $url_extra .= ($report_id) ? "&amp;r=$report_id" : '';
    
    return $url_extra;
}

/**
* Get simple topic data
*/
function get_topic_data($topic_ids, $acl_list = false, $read_tracking = false)
{
    global $auth, $db, $config, $user;
    static $rowset = array();

    $topics = array();

    if (!sizeof($topic_ids))
    {
        return array();
    }

    // cache might not contain read tracking info, so we can't use it if read
    // tracking information is requested
    if (!$read_tracking)
    {
        $cache_topic_ids = array_intersect($topic_ids, array_keys($rowset));
        $topic_ids = array_diff($topic_ids, array_keys($rowset));
    }
    else
    {
        $cache_topic_ids = array();
    }

    if (sizeof($topic_ids))
    {
        $sql_array = array(
            'SELECT'    => 't.*, f.*',

            'FROM'        => array(
                TOPICS_TABLE    => 't',
            ),

            'LEFT_JOIN'    => array(
                array(
                    'FROM'    => array(FORUMS_TABLE => 'f'),
                    'ON'    => 'f.forum_id = t.forum_id'
                )
            ),

            'WHERE'        => $db->sql_in_set('t.topic_id', $topic_ids)
        );

        if ($read_tracking && $config['load_db_lastread'])
        {
            $sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

            $sql_array['LEFT_JOIN'][] = array(
                'FROM'    => array(TOPICS_TRACK_TABLE => 'tt'),
                'ON'    => 'tt.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tt.topic_id'
            );

            $sql_array['LEFT_JOIN'][] = array(
                'FROM'    => array(FORUMS_TRACK_TABLE => 'ft'),
                'ON'    => 'ft.user_id = ' . $user->data['user_id'] . ' AND t.forum_id = ft.forum_id'
            );
        }

        $sql = $db->sql_build_query('SELECT', $sql_array);
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result))
        {
            if (!$row['forum_id'])
            {
                // Global Announcement?
                $row['forum_id'] = request_var('f', 0);
            }

            $rowset[$row['topic_id']] = $row;

            if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
            {
                continue;
            }

            $topics[$row['topic_id']] = $row;
        }
        $db->sql_freeresult($result);
    }

    foreach ($cache_topic_ids as $id)
    {
        if (!$acl_list || $auth->acl_gets($acl_list, $rowset[$id]['forum_id']))
        {
            $topics[$id] = $rowset[$id];
        }
    }

    return $topics;
}

/**
* Get simple post data
*/
function get_post_data($post_ids, $acl_list = false, $read_tracking = false)
{
    global $db, $auth, $config, $user;

    $rowset = array();

    if (!sizeof($post_ids))
    {
        return array();
    }

    $sql_array = array(
        'SELECT'    => 'p.*, u.*, t.*, f.*',

        'FROM'        => array(
            USERS_TABLE        => 'u',
            POSTS_TABLE        => 'p',
            TOPICS_TABLE    => 't',
        ),

        'LEFT_JOIN'    => array(
            array(
                'FROM'    => array(FORUMS_TABLE => 'f'),
                'ON'    => 'f.forum_id = t.forum_id'
            )
        ),

        'WHERE'        => $db->sql_in_set('p.post_id', $post_ids) . '
            AND u.user_id = p.poster_id
            AND t.topic_id = p.topic_id',
    );

    if ($read_tracking && $config['load_db_lastread'])
    {
        $sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

        $sql_array['LEFT_JOIN'][] = array(
            'FROM'    => array(TOPICS_TRACK_TABLE => 'tt'),
            'ON'    => 'tt.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tt.topic_id'
        );

        $sql_array['LEFT_JOIN'][] = array(
            'FROM'    => array(FORUMS_TRACK_TABLE => 'ft'),
            'ON'    => 'ft.user_id = ' . $user->data['user_id'] . ' AND t.forum_id = ft.forum_id'
        );
    }

    $sql = $db->sql_build_query('SELECT', $sql_array);
    $result = $db->sql_query($sql);
    unset($sql_array);

    while ($row = $db->sql_fetchrow($result))
    {
        if (!$row['forum_id'])
        {
            // Global Announcement?
            $row['forum_id'] = request_var('f', 0);
        }

        if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
        {
            continue;
        }

        if (!$row['post_approved'] && !$auth->acl_get('m_approve', $row['forum_id']))
        {
            // Moderators without the permission to approve post should at least not see them. ;)
            continue;
        }

        $rowset[$row['post_id']] = $row;
    }
    $db->sql_freeresult($result);

    return $rowset;
}

/**
* Get simple forum data
*/
function get_forum_data($forum_id, $acl_list = 'f_list', $read_tracking = false)
{
    global $auth, $db, $user, $config;

    $rowset = array();

    if (!is_array($forum_id))
    {
        $forum_id = array($forum_id);
    }

    if (!sizeof($forum_id))
    {
        return array();
    }

    if ($read_tracking && $config['load_db_lastread'])
    {
        $read_tracking_join = ' LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . '
            AND ft.forum_id = f.forum_id)';
        $read_tracking_select = ', ft.mark_time';
    }
    else
    {
        $read_tracking_join = $read_tracking_select = '';
    }

    $sql = "SELECT f.* $read_tracking_select
        FROM " . FORUMS_TABLE . " f$read_tracking_join
        WHERE " . $db->sql_in_set('f.forum_id', $forum_id);
    $result = $db->sql_query($sql);

    while ($row = $db->sql_fetchrow($result))
    {
        if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
        {
            continue;
        }

        if ($auth->acl_get('m_approve', $row['forum_id']))
        {
            $row['forum_topics'] = $row['forum_topics_real'];
        }

        $rowset[$row['forum_id']] = $row;
    }
    $db->sql_freeresult($result);

    return $rowset;
}

/**
* Get simple pm data
*/
function get_pm_data($pm_ids)
{
    global $db, $auth, $config, $user;

    $rowset = array();

    if (!sizeof($pm_ids))
    {
        return array();
    }

    $sql_array = array(
        'SELECT'    => 'p.*, u.*',

        'FROM'        => array(
            USERS_TABLE            => 'u',
            PRIVMSGS_TABLE        => 'p',
        ),

        'WHERE'        => $db->sql_in_set('p.msg_id', $pm_ids) . '
            AND u.user_id = p.author_id',
    );

    $sql = $db->sql_build_query('SELECT', $sql_array);
    $result = $db->sql_query($sql);
    unset($sql_array);

    while ($row = $db->sql_fetchrow($result))
    {
        $rowset[$row['msg_id']] = $row;
    }
    $db->sql_freeresult($result);

    return $rowset;
}

function mcp_sorting($mode, &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id = 0, $topic_id = 0, $where_sql = 'WHERE')
{
    global $db, $user, $auth, $template, $config;

    $sort_days = request_var('st', 0);
    $min_time = ($sort_days) ? time() - ($sort_days * 86400) : 0;

    switch ($mode)
    {
        case 'viewforum':
            $type = 'topics';
            $default_key = 't';
            $default_dir = 'd';

            $sql = 'SELECT COUNT(topic_id) AS total
                FROM ' . TOPICS_TABLE . "
                $where_sql forum_id = $forum_id
                    AND topic_type NOT IN (" . POST_ANNOUNCE . ', ' . POST_GLOBAL . ")
                    AND topic_last_post_time >= $min_time";

            if (!$auth->acl_get('m_approve', $forum_id))
            {
                $sql .= 'AND topic_approved = 1';
            }
        break;

        case 'viewtopic':
            $type = 'posts';
            $default_key = 't';
            $default_dir = 'a';

            $sql = 'SELECT COUNT(post_id) AS total
                FROM ' . POSTS_TABLE . "
                $where_sql topic_id = $topic_id
                    AND post_time >= $min_time";

            if (!$auth->acl_get('m_approve', $forum_id))
            {
                $sql .= 'AND post_approved = 1';
            }
        break;

        case 'unapproved_posts':
            $type = 'posts';
            $default_key = 't';
            $default_dir = 'd';
            $where_sql .= ($topic_id) ? ' p.topic_id = ' . $topic_id . ' AND' : '';

            $sql = 'SELECT COUNT(p.post_id) AS total
                FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
                $where_sql " . $db->sql_in_set('p.forum_id', ($forum_id) ? array($forum_id) : array_intersect(get_forum_list('f_read'), get_forum_list('m_approve'))) . '
                    AND p.post_approved = 0
                    AND t.topic_id = p.topic_id
                    AND t.topic_first_post_id <> p.post_id';

            if ($min_time)
            {
                $sql .= ' AND post_time >= ' . $min_time;
            }
        break;

        case 'unapproved_topics':
            $type = 'topics';
            $default_key = 't';
            $default_dir = 'd';

            $sql = 'SELECT COUNT(topic_id) AS total
                FROM ' . TOPICS_TABLE . "
                $where_sql " . $db->sql_in_set('forum_id', ($forum_id) ? array($forum_id) : array_intersect(get_forum_list('f_read'), get_forum_list('m_approve'))) . '
                    AND topic_approved = 0';

            if ($min_time)
            {
                $sql .= ' AND topic_time >= ' . $min_time;
            }
        break;

        case 'pm_reports':
        case 'pm_reports_closed':
        case 'reports':
        case 'reports_closed':
            $pm = (strpos($mode, 'pm_') === 0) ? true : false;

            $type = ($pm) ? 'pm_reports' : 'reports';
            $default_key = 't';
            $default_dir = 'd';
            $limit_time_sql = ($min_time) ? "AND r.report_time >= $min_time" : '';

            if ($topic_id)
            {
                $where_sql .= ' p.topic_id = ' . $topic_id . ' AND ';
            }
            else if ($forum_id)
            {
                $where_sql .= ' p.forum_id = ' . $forum_id . ' AND ';
            }
            else if (!$pm)
            {
                $where_sql .= ' ' . $db->sql_in_set('p.forum_id', get_forum_list(array('!f_read', '!m_report')), true, true) . ' AND ';
            }

            if ($mode == 'reports' || $mode == 'pm_reports')
            {
                $where_sql .= ' r.report_closed = 0 AND ';
            }
            else
            {
                $where_sql .= ' r.report_closed = 1 AND ';
            }

            if ($pm)
            {
                $sql = 'SELECT COUNT(r.report_id) AS total
                    FROM ' . REPORTS_TABLE . ' r, ' . PRIVMSGS_TABLE . " p
                    $where_sql r.post_id = 0
                        AND p.msg_id = r.pm_id
                        $limit_time_sql";
            }
            else
            {
                $sql = 'SELECT COUNT(r.report_id) AS total
                    FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . " p
                    $where_sql " . (version_compare($config['version'], '3.0.6', '<') ? '1' : " r.pm_id = 0") . "
                        AND p.post_id = r.post_id
                        $limit_time_sql";
            }
        break;

        case 'viewlogs':
            $type = 'logs';
            $default_key = 't';
            $default_dir = 'd';

            $sql = 'SELECT COUNT(log_id) AS total
                FROM ' . LOG_TABLE . "
                $where_sql " . $db->sql_in_set('forum_id', ($forum_id) ? array($forum_id) : array_intersect(get_forum_list('f_read'), get_forum_list('m_'))) . '
                    AND log_time >= ' . $min_time . '
                    AND log_type = ' . LOG_MOD;
        break;
    }

    $sort_key = request_var('sk', $default_key);
    $sort_dir = request_var('sd', $default_dir);
    $sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

    switch ($type)
    {
        case 'topics':
            $limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
            $sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'tt' => $user->lang['TOPIC_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);

            $sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_time', 'tt' => 't.topic_time', 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_replies_real' : 't.topic_replies'), 's' => 't.topic_title', 'v' => 't.topic_views');
            $limit_time_sql = ($min_time) ? "AND t.topic_last_post_time >= $min_time" : '';
        break;

        case 'posts':
            $limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
            $sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
            $sort_by_sql = array('a' => 'u.username_clean', 't' => 'p.post_time', 's' => 'p.post_subject');
            $limit_time_sql = ($min_time) ? "AND p.post_time >= $min_time" : '';
        break;

        case 'reports':
            $limit_days = array(0 => $user->lang['ALL_REPORTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
            $sort_by_text = array('a' => $user->lang['AUTHOR'], 'r' => $user->lang['REPORTER'], 'p' => $user->lang['POST_TIME'], 't' => $user->lang['REPORT_TIME'], 's' => $user->lang['SUBJECT']);
            $sort_by_sql = array('a' => 'u.username_clean', 'r' => 'ru.username', 'p' => 'p.post_time', 't' => 'r.report_time', 's' => 'p.post_subject');
        break;

        case 'pm_reports':
            $limit_days = array(0 => $user->lang['ALL_REPORTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
            $sort_by_text = array('a' => $user->lang['AUTHOR'], 'r' => $user->lang['REPORTER'], 'p' => $user->lang['POST_TIME'], 't' => $user->lang['REPORT_TIME'], 's' => $user->lang['SUBJECT']);
            $sort_by_sql = array('a' => 'u.username_clean', 'r' => 'ru.username', 'p' => 'p.message_time', 't' => 'r.report_time', 's' => 'p.message_subject');
        break;

        case 'logs':
            $limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
            $sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);

            $sort_by_sql = array('u' => 'u.username_clean', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');
            $limit_time_sql = ($min_time) ? "AND l.log_time >= $min_time" : '';
        break;
    }

    if (!isset($sort_by_sql[$sort_key]))
    {
        $sort_key = $default_key;
    }

    $sort_order_sql = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

    $s_limit_days = $s_sort_key = $s_sort_dir = $sort_url = '';
    gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $sort_url);

    $template->assign_vars(array(
        'S_SELECT_SORT_DIR'        => $s_sort_dir,
        'S_SELECT_SORT_KEY'        => $s_sort_key,
        'S_SELECT_SORT_DAYS'    => $s_limit_days)
    );

    if (($sort_days && $mode != 'viewlogs') || in_array($mode, array('reports', 'unapproved_topics', 'unapproved_posts')) || $where_sql != 'WHERE')
    {
        $result = $db->sql_query($sql);
        $total = (int) $db->sql_fetchfield('total');
        $db->sql_freeresult($result);
    }
    else
    {
        $total = -1;
    }
}

function check_ids(&$ids, $table, $sql_id, $acl_list = false, $single_forum = false)
{
    global $db, $auth;

    if (!is_array($ids) || empty($ids))
    {
        return false;
    }

    $sql = "SELECT $sql_id, forum_id FROM $table
        WHERE " . $db->sql_in_set($sql_id, $ids);
    $result = $db->sql_query($sql);

    $ids = array();
    $forum_id = false;

    while ($row = $db->sql_fetchrow($result))
    {
        if ($acl_list && $row['forum_id'] && !$auth->acl_gets($acl_list, $row['forum_id']))
        {
            continue;
        }

        if ($acl_list && !$row['forum_id'] && !$auth->acl_getf_global($acl_list))
        {
            continue;
        }

        // Limit forum? If not, just assign the id.
        if ($single_forum === false)
        {
            $ids[] = $row[$sql_id];
            continue;
        }

        // Limit forum to a specific forum id?
        // This can get really tricky, because we do not want to create a failure on global topics. :)
        if ($row['forum_id'])
        {
            if ($single_forum !== true && $row['forum_id'] == (int) $single_forum)
            {
                $forum_id = (int) $single_forum;
            }
            else if ($forum_id === false)
            {
                $forum_id = $row['forum_id'];
            }

            if ($row['forum_id'] == $forum_id)
            {
                $ids[] = $row[$sql_id];
            }
        }
        else
        {
            // Always add a global topic
            $ids[] = $row[$sql_id];
        }
    }
    $db->sql_freeresult($result);

    if (!sizeof($ids))
    {
        return false;
    }

    // If forum id is false and ids populated we may have only global announcements selected (returning 0 because of (int) $forum_id)

    return ($single_forum === false) ? true : (int) $forum_id;
}
/**
* Do the various checks required for removing posts as well as removing it
*/
function handle_post_delete($forum_id, $topic_id, $post_id, &$post_data)
{
	global $user, $db, $auth, $config;
	global $phpbb_root_path, $phpEx;
	$flag = false;
	// If moderator removing post or user itself removing post, present a confirmation screen
	if ($auth->acl_get('m_delete', $forum_id) || ($post_data['poster_id'] == $user->data['user_id'] 
	&& $user->data['is_registered'] && $auth->acl_get('f_delete', $forum_id) && 
	$post_id == $post_data['topic_last_post_id'] && !$post_data['post_edit_locked'] && 
	($post_data['post_time'] > time() - ($config['delete_time'] * 60) || !$config['delete_time'])))
	{
		$s_hidden_fields = build_hidden_fields(array(
			'p'		=> $post_id,
			'f'		=> $forum_id,
			'mode'	=> 'delete')
		);
		$data = array(
			'topic_first_post_id'	=> $post_data['topic_first_post_id'],
			'topic_last_post_id'	=> $post_data['topic_last_post_id'],
			'topic_replies_real'	=> $post_data['topic_replies_real'],
			'topic_approved'		=> $post_data['topic_approved'],
			'topic_type'			=> $post_data['topic_type'],
			'post_approved'			=> $post_data['post_approved'],
			'post_reported'			=> $post_data['post_reported'],
			'post_time'				=> $post_data['post_time'],
			'poster_id'				=> $post_data['poster_id'],
			'post_postcount'		=> $post_data['post_postcount']
		);

		$next_post_id = delete_post($forum_id, $topic_id, $post_id, $data);
		$post_username = ($post_data['poster_id'] == ANONYMOUS && !empty($post_data['post_username'])) ? $post_data['post_username'] : $post_data['username'];

		if ($next_post_id === false)
		{
			add_log('mod', $forum_id, $topic_id, 'LOG_DELETE_TOPIC', $post_data['topic_title'], $post_username);
		}
		else
		{
			add_log('mod', $forum_id, $topic_id, 'LOG_DELETE_POST', $post_data['post_subject'], $post_username);

		}
		$flag = true;
	}

	// If we are here the user is not able to delete - present the correct error message
	if($flag == false) 
	{
		if ($post_data['poster_id'] == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id))
		{
			trigger_error('DELETE_OWN_POSTS');
		}
	
		if ($post_data['poster_id'] == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id) && $post_id != $post_data['topic_last_post_id'])
		{
			trigger_error('CANNOT_DELETE_REPLIED');
		}
		trigger_error('USER_CANNOT_DELETE');
	}
	
}