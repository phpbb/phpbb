<?php
/** 
*
* acp_ban [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE 
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

// Banning
$lang = array_merge($lang, array(
	'1_HOUR'		=> '1 Hour',
	'30_MINS'		=> '30 Minutes',
	'6_HOURS'		=> '6 Hours',

	'ACP_BAN_EXPLAIN'	=> 'Here you can control the banning of users by name, IP or email address. These methods prevent a user reaching any part of the board. You can give a short (255 character) reason for the ban if you wish. This will be displayed in the admin log. The length of a ban can also be specified. If you want the ban to end on a specific date rather than after a set time period select <u>Until</u> for the ban length and enter a date in yyyy-mm-dd format.',

	'BAN_EXCLUDE'			=> 'Exclude from banning',
	'BAN_LENGTH'			=> 'Length of ban',
	'BAN_REASON'			=> 'Reason for ban',
	'BAN_GIVE_REASON'		=> 'Reason shown to the banned',
	'BAN_UPDATE_SUCESSFUL'	=> 'The banlist has been updated successfully',

	'EMAIL_BAN'					=> 'Ban one or more email addresses',
	'EMAIL_BAN_EXCLUDE_EXPLAIN'	=> 'Enable this to exclude the entered email address from all current bans.',
	'EMAIL_BAN_EXPLAIN'			=> 'To specify more than one email address enter each on a new line. To match partial addresses use * as the wildcard, e.g. *@hotmail.com, *@*.domain.tld, etc.',
	'EMAIL_NO_BANNED'			=> 'No banned email addresses',
	'EMAIL_UNBAN'				=> 'Un-ban or Un-exclude Emails',
	'EMAIL_UNBAN_EXPLAIN'		=> 'You can unban (or un-exclude) multiple email addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser. Excluded email addresses have a grey background.',

	'IP_BAN'					=> 'Ban one or more ips',
	'IP_BAN_EXCLUDE_EXPLAIN'	=> 'Enable this to exclude the entered IP from all current bans.',
	'IP_BAN_EXPLAIN'			=> 'To specify several different IP\'s or hostnames enter each on a new line. To specify a range of IP addresses separate the start and end with a hyphen (-), to specify a wildcard use *',
	'IP_NO_BANNED'				=> 'No banned IP addresses',
	'IP_UNBAN'					=> 'Un-ban or Un-exclude IPs',
	'IP_UNBAN_EXPLAIN'			=> 'You can unban (or un-exclude) multiple IP addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser. Excluded IP\'s have a grey background.',

	'PERMANENT'		=> 'Permanent',
	
	'UNTIL'						=> 'Until',
	'USER_BAN'					=> 'Ban one or more usernames',
	'USER_BAN_EXCLUDE_EXPLAIN'	=> 'Enable this to exclude the entered users from all current bans.',
	'USER_BAN_EXPLAIN'			=> 'You can ban multiple users in one go by entering each name on a new line. Use the <u>Find a Username</u> facility to look up and add one or more users automatically.',
	'USER_NO_BANNED'			=> 'No banned usernames',
	'USER_UNBAN'				=> 'Un-ban or Un-exclude usernames',
	'USER_UNBAN_EXPLAIN'		=> 'You can unban (or un-exclude) multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser. Excluded users have a grey background.',
));

?>