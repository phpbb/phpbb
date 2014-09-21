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
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

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
$user->setup('viewtopic');

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
$phpbb_feed_helper = $phpbb_container->get('feed.helper');
$board_url = $phpbb_feed_helper->get_board_url();

// Get correct feed object
$phpbb_feed_factory = $phpbb_container->get('feed.factory');
$feed = $phpbb_feed_factory->get_feed($mode, $forum_id, $topic_id);

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

	$display_attachments = ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && isset($row['post_attachment']) && $row['post_attachment']) ? true : false;

	$item_row = array(
		'author'		=> ($feed->get('creator') !== NULL) ? $row[$feed->get('creator')] : '',
		'published'		=> ($published > 0) ? $phpbb_feed_helper->format_date($published) : '',
		'updated'		=> ($updated > 0) ? $phpbb_feed_helper->format_date($updated) : '',
		'link'			=> '',
		'title'			=> censor_text($title),
		'category'		=> ($config['feed_item_statistics'] && !empty($row['forum_id'])) ? $board_url . '/viewforum.' . $phpEx . '?f=' . $row['forum_id'] : '',
		'category_name'	=> ($config['feed_item_statistics'] && isset($row['forum_name'])) ? $row['forum_name'] : '',
		'description'	=> censor_text($phpbb_feed_helper->generate_content($row[$feed->get('text')], $row[$feed->get('bbcode_uid')], $row[$feed->get('bitfield')], $options, $row['forum_id'], ($display_attachments ? $feed->get_attachments($row['post_id']) : array()))),
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
	'SELF_LINK'				=> $phpbb_feed_helper->append_sid('feed.' . $phpEx, $params),
	'FEED_LINK'				=> $board_url . '/index.' . $phpEx,
	'FEED_TITLE'			=> $config['sitename'],
	'FEED_SUBTITLE'			=> $config['site_desc'],
	'FEED_UPDATED'			=> $phpbb_feed_helper->format_date($feed_updated_time),
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
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

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
