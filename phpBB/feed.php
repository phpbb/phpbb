<?php
/**
* @package phpBB3
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* Idea and original RSS Feed 2.0 MOD (Version 1.0.8/9) by leviatan21
* Original MOD: http://www.phpbb.com/community/viewtopic.php?f=69&t=1214645
* MOD Author Profile: http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
* MOD Author Homepage: http://www.mssti.com/phpbb3/
*
**/

/**
* @ignore
**/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

if (!$config['feed_enable'])
{
	trigger_error('NO_FEED_ENABLED');
}

// Start session
$user->session_begin();

if (!empty($config['feed_http_auth']) && request_var('auth', '') == 'http')
{
	phpbb_http_login(array(
		'auth_message'	=> 'Feed',
		'viewonline'	=> request_var('viewonline', true),
	));
}

$auth->acl($user->data);
$user->setup();

// Initial var setup
$forum_id	= request_var('f', 0);
$topic_id	= request_var('t', 0);
$mode		= request_var('mode', '');

// We do not use a template, therefore we simply define the global template variables here
$global_vars = $item_vars = array();
$feed_updated_time = 0;

// Generate params array for use in append_sid() to correctly link back to this page
$params = false;
if ($forum_id || $topic_id || $mode)
{
	$params = array(
		'f'		=> ($forum_id) ? $forum_id : NULL,
		't'		=> ($topic_id) ? $topic_id : NULL,
		'mode'	=> ($mode) ? $mode : NULL,
	);
}

// This boards URL
$board_url = generate_board_url();

// Get correct feed object
$feed = phpbb_feed_factory::init($mode, $forum_id, $topic_id);

// No feed found
if ($feed === false)
{
	trigger_error('NO_FEED');
}

// Open Feed
$feed->open();

// Iterate through items
while ($row = $feed->get_item())
{
	// BBCode options to correctly disable urls, smilies, bbcode...
	if ($feed->get('options') === NULL)
	{
		// Allow all combinations
		$options = 7;

		if ($feed->get('enable_bbcode') !== NULL && $feed->get('enable_smilies') !== NULL && $feed->get('enable_magic_url') !== NULL)
		{
			$options = (($row[$feed->get('enable_bbcode')]) ? OPTION_FLAG_BBCODE : 0) + (($row[$feed->get('enable_smilies')]) ? OPTION_FLAG_SMILIES : 0) + (($row[$feed->get('enable_magic_url')]) ? OPTION_FLAG_LINKS : 0);
		}
	}
	else
	{
		$options = $row[$feed->get('options')];
	}

	$title = (isset($row[$feed->get('title')]) && $row[$feed->get('title')] !== '') ? $row[$feed->get('title')] : ((isset($row[$feed->get('title2')])) ? $row[$feed->get('title2')] : '');

	$published = ($feed->get('published') !== NULL) ? (int) $row[$feed->get('published')] : 0;
	$updated = ($feed->get('updated') !== NULL) ? (int) $row[$feed->get('updated')] : 0;

	$item_row = array(
		'author'		=> ($feed->get('creator') !== NULL) ? $row[$feed->get('creator')] : '',
		'published'		=> ($published > 0) ? feed_format_date($published) : '',
		'updated'		=> ($updated > 0) ? feed_format_date($updated) : '',
		'link'			=> '',
		'title'			=> censor_text($title),
		'category'		=> ($config['feed_item_statistics'] && !empty($row['forum_id'])) ? $board_url . '/viewforum.' . $phpEx . '?f=' . $row['forum_id'] : '',
		'category_name'	=> ($config['feed_item_statistics'] && isset($row['forum_name'])) ? $row['forum_name'] : '',
		'description'	=> censor_text(feed_generate_content($row[$feed->get('text')], $row[$feed->get('bbcode_uid')], $row[$feed->get('bitfield')], $options)),
		'statistics'	=> '',
	);

	// Adjust items, fill link, etc.
	$feed->adjust_item($item_row, $row);

	$item_vars[] = $item_row;

	$feed_updated_time = max($feed_updated_time, $published, $updated);
}

// If we do not have any items at all, sending the current time is better than sending no time.
if (!$feed_updated_time)
{
	$feed_updated_time = time();
}

// Some default assignments
// FEED_IMAGE is not used (atom)
$global_vars = array_merge($global_vars, array(
	'FEED_IMAGE'			=> '',
	'SELF_LINK'				=> feed_append_sid('/feed.' . $phpEx, $params),
	'FEED_LINK'				=> $board_url . '/index.' . $phpEx,
	'FEED_TITLE'			=> $config['sitename'],
	'FEED_SUBTITLE'			=> $config['site_desc'],
	'FEED_UPDATED'			=> feed_format_date($feed_updated_time),
	'FEED_LANG'				=> $user->lang['USER_LANG'],
	'FEED_AUTHOR'			=> $config['sitename'],
));

$feed->close();

// Output page

// gzip_compression
if ($config['gzip_compress'])
{
	if (@extension_loaded('zlib') && !headers_sent())
	{
		ob_start('ob_gzhandler');
	}
}

// IF debug extra is enabled and admin want to "explain" the page we need to set other headers...
if (defined('DEBUG') && request_var('explain', 0) && $auth->acl_get('a_'))
{
	header('Content-type: text/html; charset=UTF-8');
	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');

	$mtime = explode(' ', microtime());
	$totaltime = $mtime[0] + $mtime[1] - $starttime;

	if (method_exists($db, 'sql_report'))
	{
		$db->sql_report('display');
	}

	garbage_collection();
	exit_handler();
}

header("Content-Type: application/atom+xml; charset=UTF-8");
header("Last-Modified: " . gmdate('D, d M Y H:i:s', $feed_updated_time) . ' GMT');

if (!empty($user->data['is_bot']))
{
	// Let reverse proxies know we detected a bot.
	header('X-PHPBB-IS-BOT: yes');
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="' . $global_vars['FEED_LANG'] . '">' . "\n";
echo '<link rel="self" type="application/atom+xml" href="' . $global_vars['SELF_LINK'] . '" />' . "\n\n";

echo (!empty($global_vars['FEED_TITLE'])) ? '<title>' . $global_vars['FEED_TITLE'] . '</title>' . "\n" : '';
echo (!empty($global_vars['FEED_SUBTITLE'])) ? '<subtitle>' . $global_vars['FEED_SUBTITLE'] . '</subtitle>' . "\n" : '';
echo (!empty($global_vars['FEED_LINK'])) ? '<link href="' . $global_vars['FEED_LINK'] .'" />' . "\n" : '';
echo '<updated>' . $global_vars['FEED_UPDATED'] . '</updated>' . "\n\n";

echo '<author><name><![CDATA[' . $global_vars['FEED_AUTHOR'] . ']]></name></author>' . "\n";
echo '<id>' . $global_vars['SELF_LINK'] . '</id>' . "\n";

foreach ($item_vars as $row)
{
	echo '<entry>' . "\n";

	if (!empty($row['author']))
	{
		echo '<author><name><![CDATA[' . $row['author'] . ']]></name></author>' . "\n";
	}

	echo '<updated>' . ((!empty($row['updated'])) ? $row['updated'] : $row['published']) . '</updated>' . "\n";

	if (!empty($row['published']))
	{
		echo '<published>' . $row['published'] . '</published>' . "\n";
	}

	echo '<id>' . $row['link'] . '</id>' . "\n";
	echo '<link href="' . $row['link'] . '"/>' . "\n";
	echo '<title type="html"><![CDATA[' . $row['title'] . ']]></title>' . "\n\n";

	if (!empty($row['category']) && isset($row['category_name']) && $row['category_name'] !== '')
	{
		echo '<category term="' . $row['category_name'] . '" scheme="' . $row['category'] . '" label="' . $row['category_name'] . '"/>' . "\n";
	}

	echo '<content type="html" xml:base="' . $row['link'] . '"><![CDATA[' . "\n";
	echo $row['description'];

	if (!empty($row['statistics']))
	{
		echo '<p>' . $user->lang['STATISTICS'] . ': ' . $row['statistics'] . '</p>';
	}

	echo '<hr />' . "\n" . ']]></content>' . "\n";
	echo '</entry>' . "\n";
}

echo '</feed>';

garbage_collection();
exit_handler();

/**
* Run links through append_sid(), prepend generate_board_url() and remove session id
**/
function feed_append_sid($url, $params)
{
	global $board_url;

	return append_sid($board_url . $url, $params, true, '');
}

/**
* Generate ISO 8601 date string (RFC 3339)
**/
function feed_format_date($time)
{
	static $zone_offset;
	static $offset_string;

	if (empty($offset_string))
	{
		global $user;

		$zone_offset = $user->create_datetime()->getOffset();
		$offset_string = phpbb_format_timezone_offset($zone_offset);
	}

	return gmdate("Y-m-d\TH:i:s", $time + $zone_offset) . $offset_string;
}

/**
* Generate text content
**/
function feed_generate_content($content, $uid, $bitfield, $options)
{
	global $user, $config, $phpbb_root_path, $phpEx, $board_url;

	if (empty($content))
	{
		return '';
	}

	// Prepare some bbcodes for better parsing
	$content	= preg_replace("#\[quote(=&quot;.*?&quot;)?:$uid\]\s*(.*?)\s*\[/quote:$uid\]#si", "[quote$1:$uid]<br />$2<br />[/quote:$uid]", $content);

	$content = generate_text_for_display($content, $uid, $bitfield, $options);

	// Add newlines
	$content = str_replace('<br />', '<br />' . "\n", $content);

	// Convert smiley Relative paths to Absolute path, Windows style
	$content = str_replace($phpbb_root_path . $config['smilies_path'], $board_url . '/' . $config['smilies_path'], $content);

	// Remove "Select all" link and mouse events
	$content = str_replace('<a href="#" onclick="selectCode(this); return false;">' . $user->lang['SELECT_ALL_CODE'] . '</a>', '', $content);
	$content = preg_replace('#(onkeypress|onclick)="(.*?)"#si', '', $content);

	// Firefox does not support CSS for feeds, though

	// Remove font sizes
//	$content = preg_replace('#<span style="font-size: [0-9]+%; line-height: [0-9]+%;">([^>]+)</span>#iU', '\1', $content);

	// Make text strong :P
//	$content = preg_replace('#<span style="font-weight: bold?">(.*?)</span>#iU', '<strong>\1</strong>', $content);

	// Italic
//	$content = preg_replace('#<span style="font-style: italic?">([^<]+)</span>#iU', '<em>\1</em>', $content);

	// Underline
//	$content = preg_replace('#<span style="text-decoration: underline?">([^<]+)</span>#iU', '<u>\1</u>', $content);

	// Remove embed Windows Media Streams
	$content	= preg_replace( '#<\!--\[if \!IE\]>-->([^[]+)<\!--<!\[endif\]-->#si', '', $content);

	// Do not use &lt; and &gt;, because we want to retain code contained in [code][/code]

	// Remove embed and objects
	$content	= preg_replace( '#<(object|embed)(.*?) (value|src)=(.*?) ([^[]+)(object|embed)>#si',' <a href=$4 target="_blank"><strong>$1</strong></a> ',$content);

	// Remove some specials html tag, because somewhere there are a mod to allow html tags ;)
	$content	= preg_replace( '#<(script|iframe)([^[]+)\1>#siU', ' <strong>$1</strong> ', $content);

	// Remove Comments from inline attachments [ia]
	$content	= preg_replace('#<div class="(inline-attachment|attachtitle)">(.*?)<!-- ia(.*?) -->(.*?)<!-- ia(.*?) -->(.*?)</div>#si','$4',$content);

	// Replace some entities with their unicode counterpart
	$entities = array(
		'&nbsp;'	=> "\xC2\xA0",
		'&bull;'	=> "\xE2\x80\xA2",
		'&middot;'	=> "\xC2\xB7",
		'&copy;'	=> "\xC2\xA9",
	);

	$content = str_replace(array_keys($entities), array_values($entities), $content);

	// Remove CDATA blocks. ;)
	$content = preg_replace('#\<\!\[CDATA\[(.*?)\]\]\>#s', '', $content);

	// Other control characters
	$content = preg_replace('#(?:[\x00-\x1F\x7F]+|(?:\xC2[\x80-\x9F])+)#', '', $content);

	return $content;
}
