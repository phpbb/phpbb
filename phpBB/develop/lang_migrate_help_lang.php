<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
#die("Please read the first lines of this script for instructions on how to enable it");

define('IN_PHPBB', true);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path='./../';
include($phpbb_root_path . 'common.'.$phpEx);

$help_bbcode = load_help('bbcode');
$lang_bbcode = array(
	'HELP_BBCODE_BLOCK_INTRO' => $help_bbcode[0][1],
	'HELP_BBCODE_INTRO_BBCODE_QUESTION' => $help_bbcode[1][0],
	'HELP_BBCODE_INTRO_BBCODE_ANSWER' => $help_bbcode[1][1],

	'HELP_BBCODE_BLOCK_TEXT' => $help_bbcode[2][1],
	'HELP_BBCODE_TEXT_BASIC_QUESTION' => $help_bbcode[3][0],
	'HELP_BBCODE_TEXT_BASIC_ANSWER' => $help_bbcode[3][1],
	'HELP_BBCODE_TEXT_COLOR_QUESTION' => $help_bbcode[4][0],
	'HELP_BBCODE_TEXT_COLOR_ANSWER' => $help_bbcode[4][1],
	'HELP_BBCODE_TEXT_COMBINE_QUESTION' => $help_bbcode[5][0],
	'HELP_BBCODE_TEXT_COMBINE_ANSWER' => $help_bbcode[5][1],

	'HELP_BBCODE_BLOCK_QUOTES' => $help_bbcode[6][1],
	'HELP_BBCODE_QUOTES_TEXT_QUESTION' => $help_bbcode[7][0],
	'HELP_BBCODE_QUOTES_TEXT_ANSWER' => $help_bbcode[7][1],
	'HELP_BBCODE_QUOTES_CODE_QUESTION' => $help_bbcode[8][0],
	'HELP_BBCODE_QUOTES_CODE_ANSWER' => $help_bbcode[8][1],

	'HELP_BBCODE_BLOCK_LISTS' => $help_bbcode[9][1],
	'HELP_BBCODE_LISTS_UNORDERER_QUESTION' => $help_bbcode[10][0],
	'HELP_BBCODE_LISTS_UNORDERER_ANSWER' => $help_bbcode[10][1],
	'HELP_BBCODE_LISTS_ORDERER_QUESTION' => $help_bbcode[11][0],
	'HELP_BBCODE_LISTS_ORDERER_ANSWER' => $help_bbcode[11][1],

	'HELP_BBCODE_BLOCK_LINKS' => $help_bbcode[13][1],
	'HELP_BBCODE_LINKS_BASIC_QUESTION' => $help_bbcode[14][0],
	'HELP_BBCODE_LINKS_BASIC_ANSWER' => $help_bbcode[14][1],

	'HELP_BBCODE_BLOCK_IMAGES' => $help_bbcode[15][1],
	'HELP_BBCODE_IMAGES_BASIC_QUESTION' => $help_bbcode[16][0],
	'HELP_BBCODE_IMAGES_BASIC_ANSWER' => $help_bbcode[16][1],
	'HELP_BBCODE_IMAGES_ATTACHMENT_QUESTION' => $help_bbcode[17][0],
	'HELP_BBCODE_IMAGES_ATTACHMENT_ANSWER' => $help_bbcode[17][1],

	'HELP_BBCODE_BLOCK_OTHERS' => $help_bbcode[18][1],
	'HELP_BBCODE_OTHERS_CUSTOM_QUESTION' => $help_bbcode[19][0],
	'HELP_BBCODE_OTHERS_CUSTOM_ANSWER' => $help_bbcode[19][1],
);
write_help('bbcode', $lang_bbcode);

$help_phpbb = load_help('faq');
$lang_phpbb = array(
	'HELP_FAQ_BLOCK_LOGIN' => $help_phpbb[0][1],
	'HELP_FAQ_LOGIN_REGISTER_QUESTION' => $help_phpbb[1][0],
	'HELP_FAQ_LOGIN_REGISTER_ANSWER' => $help_phpbb[1][1],
	'HELP_FAQ_LOGIN_COPPA_QUESTION' => $help_phpbb[2][0],
	'HELP_FAQ_LOGIN_COPPA_ANSWER' => $help_phpbb[2][1],
	'HELP_FAQ_LOGIN_CANNOT_REGISTER_QUESTION' => $help_phpbb[3][0],
	'HELP_FAQ_LOGIN_CANNOT_REGISTER_ANSWER' => $help_phpbb[3][1],
	'HELP_FAQ_LOGIN_REGISTER_CONFIRM_QUESTION' => $help_phpbb[4][0],
	'HELP_FAQ_LOGIN_REGISTER_CONFIRM_ANSWER' => $help_phpbb[4][1],
	'HELP_FAQ_LOGIN_CANNOT_LOGIN_QUESTION' => $help_phpbb[5][0],
	'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANSWER' => $help_phpbb[5][1],
	'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANYMORE_QUESTION' => $help_phpbb[6][0],
	'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANYMORE_ANSWER' => $help_phpbb[6][1],
	'HELP_FAQ_LOGIN_LOST_PASSWORD_QUESTION' => $help_phpbb[7][0],
	'HELP_FAQ_LOGIN_LOST_PASSWORD_ANSWER' => $help_phpbb[7][1],
	'HELP_FAQ_LOGIN_AUTO_LOGOUT_QUESTION' => $help_phpbb[8][0],
	'HELP_FAQ_LOGIN_AUTO_LOGOUT_ANSWER' => $help_phpbb[8][1],
	'HELP_FAQ_LOGIN_DELETE_COOKIES_QUESTION' => $help_phpbb[9][0],
	'HELP_FAQ_LOGIN_DELETE_COOKIES_ANSWER' => $help_phpbb[9][1],

	'HELP_FAQ_BLOCK_USERSETTINGS' => $help_phpbb[10][1],
	'HELP_FAQ_USERSETTINGS_CHANGE_SETTINGS_QUESTION' => $help_phpbb[11][0],
	'HELP_FAQ_USERSETTINGS_CHANGE_SETTINGS_ANSWER' => $help_phpbb[11][1],
	'HELP_FAQ_USERSETTINGS_HIDE_ONLINE_QUESTION' => $help_phpbb[12][0],
	'HELP_FAQ_USERSETTINGS_HIDE_ONLINE_ANSWER' => $help_phpbb[12][1],
	'HELP_FAQ_USERSETTINGS_TIMEZONE_QUESTION' => $help_phpbb[13][0],
	'HELP_FAQ_USERSETTINGS_TIMEZONE_ANSWER' => $help_phpbb[13][1],
	'HELP_FAQ_USERSETTINGS_SERVERTIME_QUESTION' => $help_phpbb[14][0],
	'HELP_FAQ_USERSETTINGS_SERVERTIME_ANSWER' => $help_phpbb[14][1],
	'HELP_FAQ_USERSETTINGS_LANGUAGE_QUESTION' => $help_phpbb[15][0],
	'HELP_FAQ_USERSETTINGS_LANGUAGE_ANSWER' => $help_phpbb[15][1],
	'HELP_FAQ_USERSETTINGS_AVATAR_QUESTION' => $help_phpbb[16][0],
	'HELP_FAQ_USERSETTINGS_AVATAR_ANSWER' => $help_phpbb[16][1],
	'HELP_FAQ_USERSETTINGS_AVATAR_DISPLAY_QUESTION' => $help_phpbb[17][0],
	'HELP_FAQ_USERSETTINGS_AVATAR_DISPLAY_ANSWER' => $help_phpbb[17][1],
	'HELP_FAQ_USERSETTINGS_RANK_QUESTION' => $help_phpbb[18][0],
	'HELP_FAQ_USERSETTINGS_RANK_ANSWER' => $help_phpbb[18][1],
	'HELP_FAQ_USERSETTINGS_EMAIL_LOGIN_QUESTION' => $help_phpbb[19][0],
	'HELP_FAQ_USERSETTINGS_EMAIL_LOGIN_ANSWER' => $help_phpbb[19][1],

	'HELP_FAQ_BLOCK_POSTING' => $help_phpbb[20][1],
	'HELP_FAQ_POSTING_CREATE_QUESTION' => $help_phpbb[21][0],
	'HELP_FAQ_POSTING_CREATE_ANSWER' => $help_phpbb[21][1],
	'HELP_FAQ_POSTING_EDIT_DELETE_QUESTION' => $help_phpbb[22][0],
	'HELP_FAQ_POSTING_EDIT_DELETE_ANSWER' => $help_phpbb[22][1],
	'HELP_FAQ_POSTING_SIGNATURE_QUESTION' => $help_phpbb[23][0],
	'HELP_FAQ_POSTING_SIGNATURE_ANSWER' => $help_phpbb[23][1],
	'HELP_FAQ_POSTING_POLL_CREATE_QUESTION' => $help_phpbb[24][0],
	'HELP_FAQ_POSTING_POLL_CREATE_ANSWER' => $help_phpbb[24][1],
	'HELP_FAQ_POSTING_POLL_ADD_QUESTION' => $help_phpbb[25][0],
	'HELP_FAQ_POSTING_POLL_ADD_ANSWER' => $help_phpbb[25][1],
	'HELP_FAQ_POSTING_POLL_EDIT_QUESTION' => $help_phpbb[26][0],
	'HELP_FAQ_POSTING_POLL_EDIT_ANSWER' => $help_phpbb[26][1],
	'HELP_FAQ_POSTING_FORUM_RESTRICTED_QUESTION' => $help_phpbb[27][0],
	'HELP_FAQ_POSTING_FORUM_RESTRICTED_ANSWER' => $help_phpbb[27][1],
	'HELP_FAQ_POSTING_NO_ATTACHMENTS_QUESTION' => $help_phpbb[28][0],
	'HELP_FAQ_POSTING_NO_ATTACHMENTS_ANSWER' => $help_phpbb[28][1],
	'HELP_FAQ_POSTING_WARNING_QUESTION' => $help_phpbb[29][0],
	'HELP_FAQ_POSTING_WARNING_ANSWER' => $help_phpbb[29][1],
	'HELP_FAQ_POSTING_REPORT_QUESTION' => $help_phpbb[30][0],
	'HELP_FAQ_POSTING_REPORT_ANSWER' => $help_phpbb[30][1],
	'HELP_FAQ_POSTING_DRAFT_QUESTION' => $help_phpbb[31][0],
	'HELP_FAQ_POSTING_DRAFT_ANSWER' => $help_phpbb[31][1],
	'HELP_FAQ_POSTING_QUEUE_QUESTION' => $help_phpbb[32][0],
	'HELP_FAQ_POSTING_QUEUE_ANSWER' => $help_phpbb[32][1],
	'HELP_FAQ_POSTING_BUMP_QUESTION' => $help_phpbb[33][0],
	'HELP_FAQ_POSTING_BUMP_ANSWER' => $help_phpbb[33][1],

	'HELP_FAQ_BLOCK_FORMATTING' => $help_phpbb[34][1],
	'HELP_FAQ_FORMATTING_BBOCDE_QUESTION' => $help_phpbb[35][0],
	'HELP_FAQ_FORMATTING_BBOCDE_ANSWER' => $help_phpbb[35][1],
	'HELP_FAQ_FORMATTING_HTML_QUESTION' => $help_phpbb[36][0],
	'HELP_FAQ_FORMATTING_HTML_ANSWER' => $help_phpbb[36][1],
	'HELP_FAQ_FORMATTING_SMILIES_QUESTION' => $help_phpbb[37][0],
	'HELP_FAQ_FORMATTING_SMILIES_ANSWER' => $help_phpbb[37][1],
	'HELP_FAQ_FORMATTING_IMAGES_QUESTION' => $help_phpbb[38][0],
	'HELP_FAQ_FORMATTING_IMAGES_ANSWER' => $help_phpbb[38][1],
	'HELP_FAQ_FORMATTING_GLOBAL_ANNOUNCE_QUESTION' => $help_phpbb[39][0],
	'HELP_FAQ_FORMATTING_GLOBAL_ANNOUNCE_ANSWER' => $help_phpbb[39][1],
	'HELP_FAQ_FORMATTING_ANNOUNCEMENT_QUESTION' => $help_phpbb[40][0],
	'HELP_FAQ_FORMATTING_ANNOUNCEMENT_ANSWER' => $help_phpbb[40][1],
	'HELP_FAQ_FORMATTING_STICKIES_QUESTION' => $help_phpbb[41][0],
	'HELP_FAQ_FORMATTING_STICKIES_ANSWER' => $help_phpbb[41][1],
	'HELP_FAQ_FORMATTING_LOCKED_QUESTION' => $help_phpbb[42][0],
	'HELP_FAQ_FORMATTING_LOCKED_ANSWER' => $help_phpbb[42][1],
	'HELP_FAQ_FORMATTING_ICONS_QUESTION' => $help_phpbb[43][0],
	'HELP_FAQ_FORMATTING_ICONS_ANSWER' => $help_phpbb[43][1],

	'HELP_FAQ_BLOCK_GROUPS' => $help_phpbb[45][1],
	'HELP_FAQ_GROUPS_ADMINISTRATORS_QUESTION' => $help_phpbb[46][0],
	'HELP_FAQ_GROUPS_ADMINISTRATORS_ANSWER' => $help_phpbb[46][1],
	'HELP_FAQ_GROUPS_MODERATORS_QUESTION' => $help_phpbb[47][0],
	'HELP_FAQ_GROUPS_MODERATORS_ANSWER' => $help_phpbb[47][1],
	'HELP_FAQ_GROUPS_USERGROUPS_QUESTION' => $help_phpbb[48][0],
	'HELP_FAQ_GROUPS_USERGROUPS_ANSWER' => $help_phpbb[48][1],
	'HELP_FAQ_GROUPS_USERGROUPS_JOIN_QUESTION' => $help_phpbb[49][0],
	'HELP_FAQ_GROUPS_USERGROUPS_JOIN_ANSWER' => $help_phpbb[49][1],
	'HELP_FAQ_GROUPS_USERGROUPS_LEAD_QUESTION' => $help_phpbb[50][0],
	'HELP_FAQ_GROUPS_USERGROUPS_LEAD_ANSWER' => $help_phpbb[50][1],
	'HELP_FAQ_GROUPS_COLORS_QUESTION' => $help_phpbb[51][0],
	'HELP_FAQ_GROUPS_COLORS_ANSWER' => $help_phpbb[51][1],
	'HELP_FAQ_GROUPS_DEFAULT_QUESTION' => $help_phpbb[52][0],
	'HELP_FAQ_GROUPS_DEFAULT_ANSWER' => $help_phpbb[52][1],
	'HELP_FAQ_GROUPS_TEAM_QUESTION' => $help_phpbb[53][0],
	'HELP_FAQ_GROUPS_TEAM_ANSWER' => $help_phpbb[53][1],

	'HELP_FAQ_BLOCK_PMS' => $help_phpbb[54][1],
	'HELP_FAQ_PMS_CANNOT_SEND_QUESTION' => $help_phpbb[55][0],
	'HELP_FAQ_PMS_CANNOT_SEND_ANSWER' => $help_phpbb[55][1],
	'HELP_FAQ_PMS_UNWANTED_QUESTION' => $help_phpbb[56][0],
	'HELP_FAQ_PMS_UNWANTED_ANSWER' => $help_phpbb[56][1],
	'HELP_FAQ_PMS_SPAM_QUESTION' => $help_phpbb[57][0],
	'HELP_FAQ_PMS_SPAM_ANSWER' => $help_phpbb[57][1],

	'HELP_FAQ_BLOCK_FRIENDS' => $help_phpbb[58][1],
	'HELP_FAQ_FRIENDS_BASIC_QUESTION' => $help_phpbb[59][0],
	'HELP_FAQ_FRIENDS_BASIC_ANSWER' => $help_phpbb[59][1],
	'HELP_FAQ_FRIENDS_MANAGE_QUESTION' => $help_phpbb[60][0],
	'HELP_FAQ_FRIENDS_MANAGE_ANSWER' => $help_phpbb[60][1],

	'HELP_FAQ_BLOCK_SEARCH' => $help_phpbb[61][1],
	'HELP_FAQ_SEARCH_FORUM_QUESTION' => $help_phpbb[62][0],
	'HELP_FAQ_SEARCH_FORUM_ANSWER' => $help_phpbb[62][1],
	'HELP_FAQ_SEARCH_NO_RESULT_QUESTION' => $help_phpbb[63][0],
	'HELP_FAQ_SEARCH_NO_RESULT_ANSWER' => $help_phpbb[63][1],
	'HELP_FAQ_SEARCH_BLANK_QUESTION' => $help_phpbb[64][0],
	'HELP_FAQ_SEARCH_BLANK_ANSWER' => $help_phpbb[64][1],
	'HELP_FAQ_SEARCH_MEMBERS_QUESTION' => $help_phpbb[65][0],
	'HELP_FAQ_SEARCH_MEMBERS_ANSWER' => $help_phpbb[65][1],
	'HELP_FAQ_SEARCH_OWN_QUESTION' => $help_phpbb[66][0],
	'HELP_FAQ_SEARCH_OWN_ANSWER' => $help_phpbb[66][1],

	'HELP_FAQ_BLOCK_BOOKMARKS' => $help_phpbb[67][1],
	'HELP_FAQ_BOOKMARKS_DIFFERENCE_QUESTION' => $help_phpbb[68][0],
	'HELP_FAQ_BOOKMARKS_DIFFERENCE_ANSWER' => $help_phpbb[68][1],
	'HELP_FAQ_BOOKMARKS_TOPIC_QUESTION' => $help_phpbb[69][0],
	'HELP_FAQ_BOOKMARKS_TOPIC_ANSWER' => $help_phpbb[69][1],
	'HELP_FAQ_BOOKMARKS_FORUM_QUESTION' => $help_phpbb[70][0],
	'HELP_FAQ_BOOKMARKS_FORUM_ANSWER' => $help_phpbb[70][1],
	'HELP_FAQ_BOOKMARKS_REMOVE_QUESTION' => $help_phpbb[71][0],
	'HELP_FAQ_BOOKMARKS_REMOVE_ANSWER' => $help_phpbb[71][1],

	'HELP_FAQ_BLOCK_ATTACHMENTS' => $help_phpbb[72][1],
	'HELP_FAQ_ATTACHMENTS_ALLOWED_QUESTION' => $help_phpbb[73][0],
	'HELP_FAQ_ATTACHMENTS_ALLOWED_ANSWER' => $help_phpbb[73][1],
	'HELP_FAQ_ATTACHMENTS_OWN_QUESTION' => $help_phpbb[74][0],
	'HELP_FAQ_ATTACHMENTS_OWN_ANSWER' => $help_phpbb[74][1],

	'HELP_FAQ_BLOCK_ISSUES' => $help_phpbb[75][1],
	'HELP_FAQ_ISSUES_WHOIS_PHPBB_QUESTION' => $help_phpbb[76][0],
	'HELP_FAQ_ISSUES_WHOIS_PHPBB_ANSWER' => $help_phpbb[76][1],
	'HELP_FAQ_ISSUES_FEATURE_QUESTION' => $help_phpbb[77][0],
	'HELP_FAQ_ISSUES_FEATURE_ANSWER' => $help_phpbb[77][1],
	'HELP_FAQ_ISSUES_LEGAL_QUESTION' => $help_phpbb[78][0],
	'HELP_FAQ_ISSUES_LEGAL_ANSWER' => $help_phpbb[78][1],
	'HELP_FAQ_ISSUES_ADMIN_QUESTION' => $help_phpbb[79][0],
	'HELP_FAQ_ISSUES_ADMIN_ANSWER' => $help_phpbb[79][1],

);
write_help('faq', $lang_phpbb);

trigger_error('Successfully migrated help_bbcode and help_faq to help/bbcode and help/phpbb');

/**
 * @param string $help
 * @return array
 */
function load_help($help)
{
	global $phpbb_root_path;
	include($phpbb_root_path . 'language/en/help_' . $help . '.php');
	return $help;
}

/**
 * @param string $help
 * @param array $lang
 */
function write_help($help, array $lang)
{
	global $phpbb_root_path;
	$fp = fopen($phpbb_root_path . 'language/en/help/' . $help . '.php', 'wb');
	fwrite($fp, get_language_file_header());
	fwrite($fp, '$lang = array_merge($lang, array(' . "\n");

	$last_key = '';
	ksort($lang);
	foreach ($lang as $key => $translation)
	{
		$key_sections = explode('_', $key, 4);
		unset($key_sections[3]);
		$key_start = implode('_', $key_sections);

		if ($last_key !== '' && $key_start !== $last_key)
		{
			fwrite($fp, "\n");
		}
		fwrite($fp, "\t'" . $key . "'\t=> '" . $translation . "',\n");
		$last_key = $key_start;
	}

	fwrite($fp, '));' . "\n");
	#fwrite($fp, $lang);
	fclose($fp);
}

/**
 * @return string
 */
function get_language_file_header()
{
	return <<<EOT
<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty(\$lang) || !is_array(\$lang))
{
	\$lang = array();
}


EOT;
}
