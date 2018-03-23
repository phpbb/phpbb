<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACP_VIGLINK_SETTINGS'			=> 'VigLink settings',
	'ACP_VIGLINK_SETTINGS_EXPLAIN'	=> 'VigLink is third-party service that discretely monetises links posted by users of your forum without any change to the user experience. When users click on your outbound links to products or services and buy something, the merchants pay VigLink a commission, of which a share is donated to the phpBB project. By choosing to enable VigLink and donating proceeds to the phpBB project, you are supporting our open source organisation and ensuring our continued financial security.',
	'ACP_VIGLINK_SETTINGS_CHANGE'	=> 'You can change these settings at any time in the “<a href="%1$s">VigLink settings</a>” panel.',
	'ACP_VIGLINK_SUPPORT_EXPLAIN'	=> 'You will no longer be redirected to this page once you submit your preferred options below, by clicking the Submit button.',
	'ACP_VIGLINK_ENABLE'			=> 'Enable VigLink',
	'ACP_VIGLINK_ENABLE_EXPLAIN'	=> 'Enables use of VigLink services.',
	'ACP_VIGLINK_EARNINGS'			=> 'Claim your own earnings (optional)',
	'ACP_VIGLINK_EARNINGS_EXPLAIN'  => 'You can claim your own earnings by signing up for a VigLink Convert account.',
	'ACP_VIGLINK_DISABLED_PHPBB'	=> 'VigLink services have been disabled by phpBB.',
	'ACP_VIGLINK_CLAIM'				=> 'Claim your earnings',
	'ACP_VIGLINK_CLAIM_EXPLAIN'		=> 'You can claim your forum’s earnings from VigLink monetised links, instead of donating the earnings to the phpBB project. To manage your account settings, sign up for a “VigLink Convert” account by clicking on “Convert account”.',
	'ACP_VIGLINK_CONVERT_ACCOUNT'	=> 'Convert account',
	'ACP_VIGLINK_NO_CONVERT_LINK'	=> 'VigLink convert account link could not be retrieved.',
));
