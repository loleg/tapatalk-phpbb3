<?php
defined('IN_MOBIQUO') or exit;
class mobi_ucp_remind
{
	public $result = false;
	public $result_text = '';
	public $verify = true;

	public function main()
	{
		global $config, $phpbb_root_path, $phpEx;
		global $db, $user, $auth, $template;

		$username	= request_var('username', '', true);
		$email		= tt_register_verify($_POST['tt_token'], $_POST['tt_code']);
		if(empty($email))
		{
			$this->verify = false;
			$this->result_text = 'Please login Tapatalk first.';
			return ;
		}
		$sql = 'SELECT user_id, username, user_permissions, user_email, user_jabber, user_notify_type, user_type, user_lang, user_inactive_reason
			FROM ' . USERS_TABLE . "
			WHERE user_email_hash = '" . $db->sql_escape(phpbb_email_hash($email)) . "'
			AND username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if (!$user_row)
		{
			trigger_error('NO_EMAIL_USER');
		}

		if ($user_row['user_type'] == USER_IGNORE)
		{
			trigger_error('NO_USER');
		}

		if ($user_row['user_type'] == USER_INACTIVE)
		{
			if ($user_row['user_inactive_reason'] == INACTIVE_MANUAL)
			{
				trigger_error('ACCOUNT_DEACTIVATED');
			}
			else
			{
				trigger_error('ACCOUNT_NOT_ACTIVATED');
			}
		}

		// Check users permissions
		$auth2 = new auth();
		$auth2->acl($user_row);

		if (!$auth2->acl_get('u_chgpasswd'))
		{
			trigger_error('NO_AUTH_PASSWORD_REMINDER');
		}
		
		$this->verify = true;
		$this->result = true;
		return ;
	}
}