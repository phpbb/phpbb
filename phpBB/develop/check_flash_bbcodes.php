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
* This script will check your database for potentially dangerous flash BBCode tags
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it\n");

/**
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

if (php_sapi_name() != 'cli')
{
	header('Content-Type: text/plain');
}

check_table_flash_bbcodes(POSTS_TABLE, 'post_id', 'post_text', 'bbcode_uid', 'bbcode_bitfield');
check_table_flash_bbcodes(PRIVMSGS_TABLE, 'msg_id', 'message_text', 'bbcode_uid', 'bbcode_bitfield');
check_table_flash_bbcodes(USERS_TABLE, 'user_id', 'user_sig', 'user_sig_bbcode_uid', 'user_sig_bbcode_bitfield');
check_table_flash_bbcodes(FORUMS_TABLE, 'forum_id', 'forum_desc', 'forum_desc_uid', 'forum_desc_bitfield');
check_table_flash_bbcodes(FORUMS_TABLE, 'forum_id', 'forum_rules', 'forum_rules_uid', 'forum_rules_bitfield');
check_table_flash_bbcodes(GROUPS_TABLE, 'group_id', 'group_desc', 'group_desc_uid', 'group_desc_bitfield');

echo "If potentially dangerous flash bbcodes were found, please reparse the posts using the Support Toolkit (http://www.phpbb.com/support/stk/) and/or file a ticket in the Incident Tracker (http://www.phpbb.com/incidents/).\n";

function check_table_flash_bbcodes($table_name, $id_field, $content_field, $uid_field, $bitfield_field)
{
	echo "Checking $content_field on $table_name\n";

	$ids = get_table_flash_bbcode_pkids($table_name, $id_field, $content_field, $uid_field, $bitfield_field);

	$size = sizeof($ids);
	if ($size)
	{
		echo "Found $size potentially dangerous flash bbcodes.\n";
		echo "$id_field: " . implode(', ', $ids) . "\n";
	}
	else
	{
		echo "No potentially dangerous flash bbcodes found.\n";
	}

	echo "\n";
}

function get_table_flash_bbcode_pkids($table_name, $id_field, $content_field, $uid_field, $bitfield_field)
{
	global $db;

	$ids = array();

	$sql = "SELECT $id_field, $content_field, $uid_field, $bitfield_field
		FROM $table_name
		WHERE $content_field LIKE '%[/flash:%'
			AND $bitfield_field <> ''";

	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$uid = $row[$uid_field];

		// thanks support toolkit
		$content = html_entity_decode_utf8($row[$content_field]);
		set_var($content, $content, 'string', true);
		$content = utf8_normalize_nfc($content);

		$bitfield_data = $row[$bitfield_field];

		if (!is_valid_flash_bbcode($content, $uid) && has_flash_enabled($bitfield_data))
		{
			$ids[] = (int) $row[$id_field];
		}
	}
	$db->sql_freeresult($result);

	return $ids;
}

function get_flash_regex($uid)
{
	return "#\[flash=([0-9]+),([0-9]+):$uid\](.*?)\[/flash:$uid\]#";
}

// extract all valid flash bbcodes
// check if the bbcode content is a valid URL for each match
function is_valid_flash_bbcode($cleaned_content, $uid)
{
	$regex = get_flash_regex($uid);

	$url_regex = get_preg_expression('url');
	$www_url_regex = get_preg_expression('www_url');

	if (preg_match_all($regex, $cleaned_content, $matches))
	{
		foreach ($matches[3] as $flash_url)
		{
			if (!preg_match("#^($url_regex|$www_url_regex)$#i", $flash_url))
			{
				return false;
			}
		}
	}

	return true;
}

// check if a bitfield includes flash
// 11 = flash bit
function has_flash_enabled($bitfield_data)
{
	$bitfield = new bitfield($bitfield_data);
	return $bitfield->get(11);
}

// taken from support toolkit
function html_entity_decode_utf8($string)
{
	static $trans_tbl;

	// replace numeric entities
	$string = preg_replace_callback('~&#x([0-9a-f]+);~i', function ($match) {
		return code2utf8(hexdec($match[1]));
	}, $string);
	$string = preg_replace_callback('~&#([0-9]+);~', function ($match) {
		return code2utf8($match[1]);
	}, $string);

	// replace literal entities
	if (!isset($trans_tbl))
	{
		$trans_tbl = array();

		foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key)
			$trans_tbl[$key] = utf8_encode($val);
	}
	return strtr($string, $trans_tbl);
}

// taken from support toolkit
// Returns the utf string corresponding to the unicode value (from php.net, courtesy - romans@void.lv)
function code2utf8($num)
{
	if ($num < 128) return chr($num);
	if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
	if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	return '';
}
