<?php
if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}
$lang = array_merge($lang, array(

	// UMIL stuff
	'ACP_MOBIQUO_TITLE'				=> 'Tapatalk',
	'ACP_MOBIQUO_TITLE_EXPLAIN'		=> 'A Tapatalk plugin for your forum',
	'MOBIQUO_TABLE_DELETED'			=> 'The Tapatalk table was successfully deleted',
	'MOBIQUO_TABLE_CREATED'			=> 'The Tapatalk table was successfully created',
	'MOBIQUO_TABLE_UPDATED'			=> 'The Tapatalk table was successfully updated',
	'MOBIQUO_NOTHING_TO_UPDATE'		=> 'Nothing to do....continuing',
	'UCP_CAT_MOBIQUO'					=> 'Tapatalk Prefs',
	'UCP_MOBIQUO_CONFIG'				=> 'User Tapatalk Prefs',
	'ACP_MOBIQUO'                   => 'Tapatalk',
    'ACP_MOBIQUO_SETTINGS'           => 'Tapatalk Settings',
	'ACP_MOBIQUO_SETTINGS_EXPLAIN'		=> 'Default Tapatalk settings can be changed here.',
	'ACP_MOBIQUO_MOD_VER'             => 'MOD version',

	'TP_PUSHENABLED'      => 'push enabled',
	'TP_PUSHENABLED_EXPLAIN' => 'If push enabled,you will push message to users',
    'MOBIQUO_IS_CHROME' => 'Enable Tapatalk Notifier in Chrome',
    'MOBIQUO_IS_CHROME_EXPLAIN' => 'Users of your forum on Chome will be notified with "Tapatalk Notifier". Tapatalk Notifier for Chrome is a web browser extension that notify you with a small alert when you received a new Private Message from your forum members.',
	'MOBIQUO_GUEST_OKAY' => 'Allow Guest',
    'MOBIQUO_GUEST_OKAY_EXPLAIN' => 'Allow guest to browser your fourm on tapatalk. If forum is closed for guest, this setting will not work.',
	'MOBIQUO_HIDE_FORUM_ID' => 'Hide Forums',
	'MOBIQUO_HIDE_FORUM_ID_EXPLAIN' => 'Hide forums you don\'t want them to be listed in Tapatalk app.',
	'MOBIQUO_NAME' => 'Tapatalk plugin directory',
	'MOBIQUO_NAME_EXPLAIN' => 'Never change it if you did not rename the Tapatalk plugin directory. And the default value is \'Tapatalk\'. If you renamed the Tapatalk plugin directory, you also need to update the same setting for this forum in tapatalk forum owner area.（http://tapatalk.com/landing.php）',
	'MOBIQUO_REG_URL' => 'Your forum regisiter url',
	'MOBIQUO_REG_URL_EXPLAIN' => 'If your forum reg url is not default,please change',
	'MOBIQUO_PUSH' => 'Enable Tapatalk Push Notification',
	'MOBIQUO_PUSH_EXPLAIN' => 'Tapatalk users on your forum can get instant notification with new reply of subscribed topic and new pm if this setting was enabled.',
	
	)
);
?>