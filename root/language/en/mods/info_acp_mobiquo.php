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
	'ACP_MOBIQUO'                   => 'Tapatalk Settings',
    'ACP_MOBIQUO_SETTINGS'          => 'Tapatalk General',
	'ACP_MOBIQUO_SETTINGS_EXPLAIN'  => 'Default Tapatalk general settings can be changed here.',
	'ACP_MOBIQUO_MOD_VER'           => 'MOD version',
	'LOG_CONFIG_MOBIQUO'            => 'Update tpatalk settings',
	'acl_a_mobiquo'                 => array('lang' => 'Can manage Tapatalk settings', 'cat' => 'misc'),

	'TP_PUSHENABLED'                => 'push enabled',
	'TP_PUSHENABLED_EXPLAIN' 		=> 'If push enabled,you will push message to users',
    'MOBIQUO_IS_CHROME' 			=> 'Enable Tapatalk Notifier in Chrome',
    'MOBIQUO_IS_CHROME_EXPLAIN' 	=> 'Users of your forum on Chome will be notified with "Tapatalk Notifier". Tapatalk Notifier for Chrome is a web browser extension that notify you with a small alert when you received a new Private Message from your forum members.',
	'MOBIQUO_GUEST_OKAY' 			=> 'Allow Guest',
    'MOBIQUO_GUEST_OKAY_EXPLAIN' 	=> 'Allow guest to browser your fourm on tapatalk. If forum is closed for guest, this setting will not work.',
	'MOBIQUO_HIDE_FORUM_ID' 		=> 'Hide Forums',
	'MOBIQUO_HIDE_FORUM_ID_EXPLAIN' => 'Hide forums you don\'t want them to be listed in Tapatalk app.',
	'MOBIQUO_NAME' 					=> 'Tapatalk plugin directory',
	'MOBIQUO_NAME_EXPLAIN'			=> 'Never change it if you did not rename the Tapatalk plugin directory. And the default value is \'mobiquo\'. If you renamed the Tapatalk plugin directory, you also need to update the same setting for this forum in tapatalk forum owner area.(http://tapatalk.com/landing.php)',
	'MOBIQUO_REG_URL' 				=> 'Your forum regisiter url',
	'MOBIQUO_REG_URL_EXPLAIN' 		=> 'If your forum reg url is not default,please change',
	'MOBIQUO_PUSH' 					=> 'Enable Tapatalk Push Notification',
	'MOBIQUO_PUSH_EXPLAIN' 			=> 'Tapatalk users on your forum can get instant notification with new reply of subscribed topic and new pm if this setting was enabled.',
	'TAPATALK_PUSH_KEY' 			=> 'Tapatalk push key',
    'TAPATALK_PUSH_KEY_EXPLAIN' 	=> 'A push_key to verify your forum push certification, you can fill here with the push key you registered in Tapatalk.com. This is not mandatory but if you enter this key, it will make push feature perfect .',

	'ACP_TAPATALK_REBRANDING'          => 'Tapatalk BYO Options',
	'ACP_TAPATALK_REBRANDING_EXPLAIN'  => 'Tapatalk - Build Your Own - Options',
	'TAPATALK_FORUM_READ_ONLY'         => 'Disable New Topic' ,
	'TAPATALK_FORUM_READ_ONLY_EXPLAIN' => 'Prevent Tapatalk users to create new topic in the selected sub-forums. This feature is useful if certain forums requires additional topic fields or permission that Tapatalk does not support.',

	'TAPATALK_IPAD_MESSAGE'         => 'iPad Product Message',
	'TAPATALK_IPAD_URL'             => 'iPad Product URL',
	'TAPATALK_ANDROID_MESSAGE'      => 'Android Product Message',
	'TAPATALK_ANDROID_URL'          => 'Android Product URL',
	'TAPATALK_IPHONE_MESSAGE'       => 'iPhone Product Message',
	'TAPATALK_IPHONE_URL'           => 'iPhone Product URL',
	'TAPATALK_KINDLE_MESSAGE'       => 'Kindle Fire Product Message',
	'TAPATALK_KINDLE_URL'           => 'Kindle Fire Product URL',

	'TAPATALK_IPAD_MESSAGE_EXPLAIN'         => 'Customize this message if you are Tapatalk Rebranding Customer and has published your App to Apple App Store',
	'TAPATALK_IPAD_URL_EXPLAIN'             => 'Change this URL if you are Tapatalk Rebranding Customer and has obtained your App URL from Apple App Store',
	'TAPATALK_ANDROID_MESSAGE_EXPLAIN'      => 'Customize this message if you are Tapatalk Rebranding Customer and has published your App to Google Play',
	'TAPATALK_ANDROID_URL_EXPLAIN'          => 'Change this URL if you are Tapatalk Rebranding Customer and has obtained your App URL from Google Play',
	'TAPATALK_IPHONE_MESSAGE_EXPLAIN'       => 'Customize this message if you are Tapatalk Rebranding Customer and has published your App to Apple App Store',
	'TAPATALK_IPHONE_URL_EXPLAIN'           => 'Change this URL if you are Tapatalk Rebranding Customer and has obtained your App URL from Apple App Store',
	'TAPATALK_KINDLE_MESSAGE_EXPLAIN'       => 'Customize this message if you are Tapatalk Rebranding Customer and has published your App to Amazon App Store',
	'TAPATALK_KINDLE_URL_EXPLAIN'           => 'Change this URL if you are Tapatalk Rebranding Customer and has obtained your App URL from Amazon App Store',
	
	'TAPATALK_ALLOW_REGISTER'               => 'In-app Registration',
	'TAPATALK_ALLOW_REGISTER_EXPLAIN'       => 'Allows Tapatalk users to create new account, change password and update email address in-app.',
	
	'TAPATALK_CUSTOM_REPLACE'               => 'Thread Content Replacement (Advanced)',
	'TAPATALK_CUSTOM_REPLACE_EXPLAIN'       => 'Ability to match and replace thread content using PHP preg_replace function(http://www.php.net/manual/en/function.preg-replace.php). E.g. "\'pattern\',\'replacement\'" . You can define more than one replace rule on each line.',
	
	'TAPATALK_IPHONE_APP_ID'                => 'BYO App ID',
	'TAPATALK_IPHONE_APP_ID_EXPLAIN'        => 'Enter your product ID in Apple App Store, to be used on iPhone device', 
	'TAPATALK_APP_NAME'                     => 'BYO App Name',
	'TAPATALK_APP_NAME_EXPLAIN'             => 'Please limit this name to within 20 characters', 
	'TAPATALK_APP_DESC'                     => 'BYO App Description',
	'TAPATALK_APP_DESC_EXPLAIN'             => 'A short description of the product, ideally within five words. Less is better for small screen',
	'TAPATALK_APP_ICON_URL'                 => 'BYO App Icon URL',
	'TAPATALK_APP_ICON_URL_EXPLAIN'         => 'Icon URL to be loaded and displayed on Smart App Banner',
	
	'TAPATALK_KINDLE_HD_MESSAGE'            => 'Kindle Fire HD Product Message',
	'TAPATALK_KINDLE_HD_URL'                => 'Kindle Fire HD Product URL',
	'TAPATALK_KINDLE_HD_MESSAGE_EXPLAIN'    => 'Customize this message if you are Tapatalk BYO Customer and has published your App to Amazon App Store',
	'TAPATALK_KINDLE_HD_URL_EXPLAIN'        => 'Change this URL if you are Tapatalk BYO Customer and has obtained your App URL from Amazon App Store',

	'TAPATALK_ANDROID_HD_MESSAGE'           => 'Android HD Product Message',
	'TAPATALK_ANDROID_HD_URL'               => 'Android HD Product URL',
	'TAPATALK_ANDROID_HD_MESSAGE_EXPLAIN'   => 'Customize this message if you are Tapatalk BYO Customer and has published your App to Google Play',
	'TAPATALK_ANDROID_HD_URL_EXPLAIN'       => 'Change this URL if you are Tapatalk BYO Customer and has obtained your App URL from Google Play',
	
	)
);
?>