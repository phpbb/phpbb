<?php
/**
* @package phpBB3
* @version $Id$
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
$auth->acl($user->data);
$user->setup();

// Initial var setup
$forum_id	= request_var('f', 0);
$topic_id	= request_var('t', 0);
$mode		= request_var('mode', '');

// Feed date format for PHP > 5 and PHP4
$feed_date_format = (PHP_VERSION >= 5) ? 'c' : "Y-m-d\TH:i:sO";
$params = false;

// We do not use a template, therefore we simply define the global template variables here
$global_vars = $item_vars = array();

// Generate params array for use in append_sid() to correctly link back to this page
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

// Some default assignments
// FEED_IMAGE is not used (atom)
$global_vars = array(
	'FEED_IMAGE'			=> ($user->img('site_logo', '', false, '', 'src')) ? $board_url . '/' . substr($user->img('site_logo', '', false, '', 'src'), strlen($phpbb_root_path)) : '',
	'SELF_LINK'				=> feed_append_sid('/feed.' . $phpEx, $params),
	'FEED_LINK'				=> $board_url . '/index.' . $phpEx,
	'FEED_TITLE'			=> $config['sitename'],
	'FEED_SUBTITLE'			=> $config['site_desc'],
	'FEED_UPDATED'			=> $user->format_date(time(), $feed_date_format, true),
	'FEED_LANG'				=> $user->lang['USER_LANG'],
	'FEED_AUTHOR'			=> $config['sitename'],
);

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

	$title = ($row[$feed->get('title')]) ? $row[$feed->get('title')] : ((isset($row[$feed->get('title2')])) ? $row[$feed->get('title2')] : '');
	$title = censor_text($title);

	$item_row = array(
		'author'		=> ($feed->get('creator') !== NULL) ? $row[$feed->get('creator')] : '',
		'pubdate'		=> $user->format_date($row[$feed->get('date')], $feed_date_format, true),
		'link'			=> '',
		'title'			=> censor_text($title),
		'category'		=> ($config['feed_item_statistics']) ? $board_url . '/viewforum.' . $phpEx . '?f=' . $row['forum_id'] : '',
		'category_name'	=> ($config['feed_item_statistics']) ? utf8_htmlspecialchars($row['forum_name']) : '',
		'description'	=> censor_text(feed_generate_content($row[$feed->get('text')], $row[$feed->get('bbcode_uid')], $row[$feed->get('bitfield')], $options)),
		'statistics'	=> '',
	);

	// Adjust items, fill link, etc.
	$feed->adjust_item($item_row, $row);

	$item_vars[] = $item_row;
}

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
if (!defined('DEBUG_EXTRA') || !request_var('explain', 0) || !$auth->acl_get('a_'))
{
	header("Content-Type: application/atom+xml; charset=UTF-8");
	header("Last-Modified: " . gmdate('D, d M Y H:i:s', time()) . ' GMT');
}
else
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

	echo '<updated>' . $row['pubdate'] . '</updated>' . "\n";
	echo '<id>' . $row['link'] . '</id>' . "\n";
	echo '<link href="' . $row['link'] . '"/>' . "\n";
	echo '<title type="html"><![CDATA[' . $row['title'] . ']]></title>' . "\n\n";

	if (!empty($row['category']))
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

	// Relative Path to Absolute path, Windows style
	$content = str_replace('./', $board_url . '/', $content);

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
	// $content = preg_replace('#(?:[\x00-\x1F\x7F]+|(?:\xC2[\x80-\x9F])+)#', '', $content);

	return $content;
}

/**
* Factory class to return correct object
* @package phpBB3
*/
class phpbb_feed_factory
{
	/**
	* Return correct object for specified mode
	*
	* @param string	$mode		The feeds mode.
	* @param int	$forum_id	Forum id specified by the script if forum feed provided.
	* @param int	$topic_id	Topic id specified by the script if topic feed provided.
	*
	* @return object	Returns correct feeds object for specified mode.
	*/
	function init($mode, $forum_id, $topic_id)
	{
		global $config;

		switch ($mode)
		{
			case 'forums':
				if (!$config['feed_overall_forums'])
				{
					return false;
				}

				return new phpbb_feed_forums();
			break;

			case 'topics':
				if (!$config['feed_overall_topics'])
				{
					return false;
				}

				return new phpbb_feed_topics();
			break;

			case 'news':
				global $db;

				// Get at least one news forum
				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_NEWS, '<> 0');
				$result = $db->sql_query_limit($sql, 1, 0, 600);
				$s_feed_news = (int) $db->sql_fetchfield('forum_id');
				$db->sql_freeresult($result);

				if (!$s_feed_news)
				{
					return false;
				}

				return new phpbb_feed_news();
			break;

			default:
				// Forum and/or topic specified?
				if ($topic_id && !$config['feed_topic'])
				{
					return false;
				}

				if ($forum_id && !$topic_id && !$config['feed_forum'])
				{
					return false;
				}

				return new phpbb_feed($forum_id, $topic_id);
			break;
		}
	}
}

/**
* Base/default Feed class if no mode is specified.
* This can be the overall site feed or a forum/topic feed.
* @package phpBB3
*/
class phpbb_feed
{
	/**
	* Forum id specified for forum feed.
	*/
	var $forum_id = 0;

	/**
	* Topic id specified for topic feed.
	*/
	var $topic_id = 0;

	/**
	* SQL Query to be executed to get feed items
	*/
	var $sql;

	/**
	* Keys specified for retrieval of title, content, etc.
	*/
	var $keys = array();

	/**
	* An array of excluded forum ids.
	*/
	var $excluded_forums_ary = NULL;

	/**
	* Number of items to fetch
	*/
	var $num_items;

	/**
	* boolean to determine if items array is filled or not
	*/
	var $items_filled = false;

	/**
	* array holding items
	*/
	var $items = array();

	/**
	* Default setting for last x days
	*/
	var $sort_days = 30;

	/**
	* Default cache time of entries in seconds
	*/
	var $cache_time = 0;

	/**
	* Separator for title elements to separate items (for example forum / topic)
	*/
	var $separator = "\xE2\x80\xA2"; // &bull;

	/**
	* Constructor. Set standard keys.
	*/
	function phpbb_feed($forum_id = 0, $topic_id = 0)
	{
		global $config;

		$this->forum_id = $forum_id;
		$this->topic_id = $topic_id;

		$this->sql = array();

		// Set some values for pagination
		$this->num_items = (int) $config['feed_limit'];
		$this->set_keys();
	}

	function set_keys()
	{
		// Set keys for items...
		$this->set('title',		'post_subject');
		$this->set('title2',	'topic_title');
		$this->set('author_id',	'user_id');
		$this->set('creator',	'username');
		$this->set('text',		'post_text');
		$this->set('bitfield',	'bbcode_bitfield');
		$this->set('bbcode_uid','bbcode_uid');
		$this->set('date',		'post_time');

		$this->set('enable_bbcode',		'enable_bbcode');
		$this->set('enable_smilies',	'enable_smilies');
		$this->set('enable_magic_url',	'enable_magic_url');
	}

	function open()
	{
		if (!$this->forum_id && !$this->topic_id)
		{
			return;
		}
		else if ($this->forum_id && !$this->topic_id)
		{
			global $db, $user, $global_vars;

			$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $this->forum_id;
			$result = $db->sql_query($sql);

			$global_vars['FEED_MODE'] = $user->lang['FORUM'] . ': ' . $db->sql_fetchfield('forum_name');

			$db->sql_freeresult($result);
		}
		else if ($this->topic_id)
		{
			global $db, $user, $global_vars;

			$sql = 'SELECT topic_title
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $this->topic_id;
			$result = $db->sql_query($sql);

			$global_vars['FEED_MODE'] = $user->lang['TOPIC'] . ': ' . $db->sql_fetchfield('topic_title');

			$db->sql_freeresult($result);
		}
	}

	function close()
	{
		if (!empty($this->result))
		{
			global $db;

			$db->sql_freeresult($this->result);
		}
	}

	/**
	* Set key
	*/
	function set($key, $value)
	{
		$this->keys[$key] = $value;
	}

	/**
	* Get key
	*/
	function get($key)
	{
		return (isset($this->keys[$key])) ? $this->keys[$key] : NULL;
	}

	/**
	* Return array of excluded forums
	*/
	function excluded_forums()
	{
		if ($this->excluded_forums_ary !== NULL)
		{
			return $this->excluded_forums_ary;
		}

		global $auth, $db, $config, $phpbb_root_path, $phpEx, $user;

		// Which forums should not be searched ?
		$exclude_forums = array();

		$sql = 'SELECT forum_id
			FROM ' . FORUMS_TABLE . '
			WHERE ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_EXCLUDE, '<> 0');
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$exclude_forums[] = (int) $row['forum_id'];
		}
		$db->sql_freeresult($result);

		// Exclude forums the user is not able to read
		$this->excluded_forums_ary = array_keys($auth->acl_getf('!f_read', true));
		$this->excluded_forums_ary = (sizeof($exclude_forums)) ? array_merge($exclude_forums, $this->excluded_forums_ary) : $this->excluded_forums_ary;

		$not_in_fid = (sizeof($this->excluded_forums_ary)) ? 'WHERE (' . $db->sql_in_set('f.forum_id', $this->excluded_forums_ary, true) . ' AND ' . $db->sql_in_set('f.parent_id', $this->excluded_forums_ary, true) . ") OR (f.forum_password <> '' AND fa.user_id <> " . (int) $user->data['user_id'] . ')' : '';

		$sql = 'SELECT f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.right_id, f.forum_password, fa.user_id
			FROM ' . FORUMS_TABLE . ' f
			LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa ON (fa.forum_id = f.forum_id
				AND fa.session_id = '" . $db->sql_escape($user->session_id) . "')
			$not_in_fid
			ORDER BY f.left_id";
		$result = $db->sql_query($sql);

		$right_id = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			// Exclude passworded forum completely
			if ($row['forum_password'] && $row['user_id'] != $user->data['user_id'])
			{
				$this->excluded_forums_ary[] = (int) $row['forum_id'];
				continue;
			}

			if ($row['right_id'] > $right_id)
			{
				$right_id = (int) $row['right_id'];
			}
			else if ($row['right_id'] < $right_id)
			{
				continue;
			}
		}
		$db->sql_freeresult($result);

		return $this->excluded_forums_ary;
	}

	/**
	* Get SQL query for fetching items
	*/
	function get_sql()
	{
		global $db;

		$post_ids = array();

		// Search for topics in last X days
		$last_post_time_sql = ($this->sort_days) ? ' AND t.topic_last_post_time > ' . (time() - ($this->sort_days * 24 * 3600)) : '';

		// Fetch latest post, grouped by topic...
		if (!$this->forum_id && !$this->topic_id)
		{
			// First of all, the post ids...
			$not_in_fid = (sizeof($this->excluded_forums())) ? ' AND ' . $db->sql_in_set('t.forum_id', $this->excluded_forums(), true) : '';

			$sql = 'SELECT t.topic_last_post_id
				FROM ' . TOPICS_TABLE . ' t
				WHERE t.topic_approved = 1
					AND t.topic_moved_id = 0' .
					$not_in_fid .
					$last_post_time_sql . '
				ORDER BY t.topic_last_post_time DESC';
			$result = $db->sql_query_limit($sql, $this->num_items);

			while ($row = $db->sql_fetchrow($result))
			{
				$post_ids[] = (int) $row['topic_last_post_id'];
			}
			$db->sql_freeresult($result);
		}
		// Fetch latest posts from forum
		else if (!$this->topic_id && $this->forum_id)
		{
			// Make sure the forum is not listed within the forbidden ones. ;)
			if (in_array($this->forum_id, $this->excluded_forums()))
			{
				return false;
			}

			// Determine which forums to fetch
			$not_in_fid = (sizeof($this->excluded_forums())) ? ' AND ' . $db->sql_in_set('f1.forum_id', $this->excluded_forums(), true) : '';

			// Determine forum childs...
			$sql = 'SELECT f2.forum_id
				FROM ' . FORUMS_TABLE . ' f1, ' . FORUMS_TABLE . ' f2
				WHERE f1.forum_id = ' . $this->forum_id . '
					AND (f2.left_id BETWEEN f1.left_id AND f1.right_id' . $not_in_fid . ')';
			$result = $db->sql_query($sql);

			$forum_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$forum_ids[] = (int) $row['forum_id'];
			}
			$db->sql_freeresult($result);

			// Now select from forums...
			$sql = 'SELECT t.topic_last_post_id
				FROM ' . TOPICS_TABLE . ' t
				WHERE ' . $db->sql_in_set('t.forum_id', $forum_ids) . '
					AND t.topic_approved = 1
					AND t.topic_moved_id = 0' .
					$last_post_time_sql . '
				ORDER BY t.topic_last_post_time DESC';
			$result = $db->sql_query_limit($sql, $this->num_items);

			while ($row = $db->sql_fetchrow($result))
			{
				$post_ids[] = (int) $row['topic_last_post_id'];
			}
			$db->sql_freeresult($result);
		}
		// Fetch last posts from specified topic...
		else if ($this->topic_id)
		{
			// First of all, determine the forum...
			$sql = 'SELECT forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $this->topic_id;
			$result = $db->sql_query_limit($sql, 1);
			$this->forum_id = (int) $db->sql_fetchfield('forum_id');
			$db->sql_freeresult($result);

			// non-global announcement
			if ($this->forum_id && in_array($this->forum_id, $this->excluded_forums()))
			{
				return false;
			}

			$sql = 'SELECT post_id
				FROM ' . POSTS_TABLE . '
				WHERE topic_id = ' . $this->topic_id . '
					AND post_approved = 1
				ORDER BY post_time DESC';
			$result = $db->sql_query_limit($sql, $this->num_items);

			while ($row = $db->sql_fetchrow($result))
			{
				$post_ids[] = (int) $row['post_id'];
			}
			$db->sql_freeresult($result);
		}

		if (!sizeof($post_ids))
		{
			return false;
		}

		// Now build sql query for obtaining items
		$this->sql = array(
			'SELECT'	=>	'f.forum_id, f.forum_name, f.forum_desc_options, ' .
							't.topic_last_post_time, t.topic_id, t.topic_title, t.topic_time, t.topic_replies, t.topic_views, ' .
							'p.post_id, p.post_time, p.post_subject, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, ' .
							'u.username, u.user_id, u.user_email, u.user_colour',
			'FROM'		=> array(
				POSTS_TABLE		=> 'p',
				TOPICS_TABLE	=> 't',
				FORUMS_TABLE	=> 'f',
				USERS_TABLE		=> 'u',
			),
			'WHERE'		=> $db->sql_in_set('p.post_id', $post_ids) . '
								AND f.forum_id = p.forum_id
								AND t.topic_id = p.topic_id
								AND u.user_id = p.poster_id',
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}

	function get_item()
	{
		global $db, $cache;

		// Disable cache if it is not a guest or a bot but a registered user
		if ($this->cache_time)
		{
			global $user;

			// We check this here because we call get_item() quite often
			if (!empty($user) && $user->data['is_registered'])
			{
				$this->cache_time = 0;
			}
		}

		if (!$this->cache_time)
		{
			if (empty($this->result))
			{
				if (!$this->get_sql())
				{
					return false;
				}

				// Query database
				$sql = $db->sql_build_query('SELECT', $this->sql);
				$this->result = $db->sql_query_limit($sql, $this->num_items);
			}

			return $db->sql_fetchrow($this->result);
		}
		else
		{
			if (empty($this->items_filled))
			{
				// Try to load result set...
				$cache_filename = substr(get_class($this), strlen('phpbb_'));

				if (($this->items = $cache->get('_' . $cache_filename)) === false)
				{
					$this->items = array();

					if ($this->get_sql())
					{
						// Query database
						$sql = $db->sql_build_query('SELECT', $this->sql);
						$result = $db->sql_query_limit($sql, $this->num_items);

						while ($row = $db->sql_fetchrow($result))
						{
							$this->items[] = $row;
						}
						$db->sql_freeresult($result);
					}

					$cache->put('_' . $cache_filename, $this->items, $this->cache_time);
				}

				$this->items_filled = true;
			}

			$row = array_shift($this->items);
			return (!$row) ? false : $row;
		}
	}

	function adjust_item(&$item_row, &$row)
	{
		global $phpEx, $config;

		$item_row['title'] = (!$this->topic_id) ? $row['forum_name'] . ' ' . $this->separator . ' ' . $item_row['title'] : $item_row['title'];
		$item_row['link'] = feed_append_sid('/viewtopic.' . $phpEx, "t={$row['topic_id']}&amp;p={$row['post_id']}#p{$row['post_id']}");

		if ($config['feed_item_statistics'])
		{
			global $user;

			$user_link = '<a href="' . feed_append_sid('/memberlist.' . $phpEx, 'mode=viewprofile&amp;u=' . $row['user_id']) . '">' . $row['username'] . '</a>';

			$time = ($this->topic_id) ? $row['post_time'] : $row['topic_time'];

			$item_row['statistics'] = $user->lang['POSTED'] . ' ' . $user->lang['POST_BY_AUTHOR'] . ' ' . $user_link . ' - ' . $user->format_date($time). ' - ' . $user->lang['REPLIES'] . ' ' . $row['topic_replies'] . ' - ' . $user->lang['VIEWS'] . ' ' . $row['topic_views'];
		}
	}
}

class phpbb_feed_forums extends phpbb_feed
{
	function set_keys()
	{
		global $config;

		$this->set('title',		'forum_name');
		$this->set('text',		'forum_desc');
		$this->set('bitfield',	'forum_desc_bitfield');
		$this->set('bbcode_uid','forum_desc_uid');
		$this->set('date',		'forum_last_post_time');
		$this->set('options',	'forum_desc_options');

		$this->num_items = (int) $config['feed_overall_forums_limit'];
	}

	function open()
	{
		global $user, $global_vars;

		$global_vars['FEED_MODE'] = $user->lang['FORUMS'];
	}

	function get_sql()
	{
		global $db;

		$not_in_fid = (sizeof($this->excluded_forums())) ? ' AND ' . $db->sql_in_set('f.forum_id', $this->excluded_forums(), true) : '';

		// Build SQL Query
		$this->sql = array(
			'SELECT'	=> 'f.*',
			'FROM'		=> array(FORUMS_TABLE => 'f'),
			'WHERE'		=> 'f.forum_type = ' . FORUM_POST . '
								AND (f.forum_last_post_id > 0' . $not_in_fid . ')',
			'ORDER_BY'	=> 'f.left_id',
		);

		return true;
	}

	function adjust_item(&$item_row, &$row)
	{
		global $phpEx, $config;

		$item_row['link'] = feed_append_sid('/viewforum.' . $phpEx, 'f=' . $row['forum_id']);

		if ($config['feed_item_statistics'])
		{
			global $user;

			$item_row['statistics'] = sprintf($user->lang['TOTAL_TOPICS_OTHER'], $row['forum_topics']) . ' - ' . sprintf($user->lang['TOTAL_POSTS_OTHER'], $row['forum_posts']);
		}
	}
}

class phpbb_feed_news extends phpbb_feed
{
	function set_keys()
	{
		global $config;

		$this->set('title',		'topic_title');
		$this->set('title2',	'forum_name');
		$this->set('author_id',	'topic_poster');
		$this->set('creator',	'topic_first_poster_name');
		$this->set('text',		'post_text');
		$this->set('bitfield',	'bbcode_bitfield');
		$this->set('bbcode_uid','bbcode_uid');
		$this->set('date',		'topic_time');

		$this->set('enable_bbcode',		'enable_bbcode');
		$this->set('enable_smilies',	'enable_smilies');
		$this->set('enable_magic_url',	'enable_magic_url');

		$this->num_items = (int) $config['feed_overall_forums_limit'];
	}

	function open()
	{
		global $user, $global_vars;

		$global_vars['FEED_MODE'] = $user->lang['FEED_NEWS'];
	}

	function get_sql()
	{
		global $db, $config;

		// Get news forums...
		$sql = 'SELECT forum_id
			FROM ' . FORUMS_TABLE . '
			WHERE ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_NEWS, '<> 0');
		$result = $db->sql_query($sql);

		$in_fid_ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$in_fid_ary[] = (int) $row['forum_id'];
		}
		$db->sql_freeresult($result);

		if (!sizeof($in_fid_ary))
		{
			return false;
		}

		// Build SQL Query
		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.forum_password, f.forum_name, f.forum_topics, f.forum_posts, f.parent_id, f.left_id, f.right_id,
							t.topic_id, t.topic_title, t.topic_poster, t.topic_first_poster_name, t.topic_replies, t.topic_views, t.topic_time,
							p.post_id, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url,
							u.username, u.user_id, u.user_email, u.user_colour',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't',
				FORUMS_TABLE	=> 'f',
				POSTS_TABLE		=> 'p',
				USERS_TABLE		=> 'u',
			),
			'WHERE'		=> $db->sql_in_set('t.forum_id', $in_fid_ary) . '
							AND f.forum_id = t.forum_id
							AND p.post_id = t.topic_first_post_id
							AND t.topic_poster = u.user_id
							AND t.topic_moved_id = 0',
			'ORDER_BY'	=> 't.topic_time DESC',
		);

		return true;
	}

	function adjust_item(&$item_row, &$row)
	{
		global $phpEx, $config;

		$item_row['link'] = feed_append_sid('/viewtopic.' . $phpEx, 't=' . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '#p' . $row['post_id']);

		if ($config['feed_item_statistics'])
		{
			global $user;

			$user_link = '<a href="' . feed_append_sid('/memberlist.' . $phpEx, 'mode=viewprofile&amp;u=' . $row[$this->get('author_id')]) . '">' . $row[$this->get('creator')] . '</a>';

			$item_row['statistics'] = $user->lang['POSTED'] . ' ' . $user->lang['POST_BY_AUTHOR'] . ' ' . $user_link . ' - ' . $user->format_date($row['topic_time']). ' - ' . $user->lang['REPLIES'] . ' ' . $row['topic_replies'] . ' - ' . $user->lang['VIEWS'] . ' ' . $row['topic_views'];
		}
	}
}

class phpbb_feed_topics extends phpbb_feed
{
	function set_keys()
	{
		global $config;

		$this->set('title',		'topic_title');
		$this->set('title2',	'forum_name');
		$this->set('author_id',	'topic_poster');
		$this->set('creator',	'topic_first_poster_name');
		$this->set('text',		'post_text');
		$this->set('bitfield',	'bbcode_bitfield');
		$this->set('bbcode_uid','bbcode_uid');
		$this->set('date',		'topic_time');

		$this->set('enable_bbcode',		'enable_bbcode');
		$this->set('enable_smilies',	'enable_smilies');
		$this->set('enable_magic_url',	'enable_magic_url');

		$this->num_items = (int) $config['feed_overall_topics_limit'];
	}

	function open()
	{
		global $user, $global_vars;

		$global_vars['FEED_MODE'] = $user->lang['TOPICS'];
	}

	function get_sql()
	{
		global $db, $config;

		$post_ids = array();
		$not_in_fid = (sizeof($this->excluded_forums())) ? ' AND ' . $db->sql_in_set('t.forum_id', $this->excluded_forums(), true) : '';

		// Search for topics in last x days
		$last_post_time_sql = ($this->sort_days) ? ' AND t.topic_last_post_time > ' . (time() - ($this->sort_days * 24 * 3600)) : '';

		// Last x topics from all forums, with first post from topic...
		$sql = 'SELECT t.topic_first_post_id
			FROM ' . TOPICS_TABLE . ' t
			WHERE t.topic_approved = 1
				AND t.topic_moved_id = 0' .
				$not_in_fid .
				$last_post_time_sql . '
			ORDER BY t.topic_last_post_time DESC';
		$result = $db->sql_query_limit($sql, $this->num_items);

		while ($row = $db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['topic_first_post_id'];
		}
		$db->sql_freeresult($result);

		if (!sizeof($post_ids))
		{
			return false;
		}

		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.forum_password, f.forum_name, f.forum_topics, f.forum_posts, f.parent_id, f.left_id, f.right_id,
							t.topic_id, t.topic_title, t.topic_poster, t.topic_first_poster_name, t.topic_replies, t.topic_views, t.topic_time,
							p.post_id, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url,
							u.username, u.user_id, u.user_email, u.user_colour',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't',
				FORUMS_TABLE	=> 'f',
				POSTS_TABLE		=> 'p',
				USERS_TABLE		=> 'u',
			),
			'WHERE'		=> $db->sql_in_set('p.post_id', $post_ids) . '
								AND f.forum_id = p.forum_id
								AND t.topic_id = p.topic_id
								AND u.user_id = p.poster_id',
			'ORDER_BY'	=> 't.topic_last_post_time DESC',
		);

		return true;
	}

	function adjust_item(&$item_row, &$row)
	{
		global $phpEx, $config;

		$item_row['link'] = feed_append_sid('/viewtopic.' . $phpEx, 't=' . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '#p' . $row['post_id']);

		if ($config['feed_item_statistics'])
		{
			global $user;

			$user_link = '<a href="' . feed_append_sid('/memberlist.' . $phpEx, 'mode=viewprofile&amp;u=' . $row[$this->get('author_id')]) . '">' . $row[$this->get('creator')] . '</a>';

			$item_row['statistics'] = $user->lang['POSTED'] . ' ' . $user->lang['POST_BY_AUTHOR'] . ' ' . $user_link . ' - ' . $user->format_date($row['topic_time']). ' - ' . $user->lang['REPLIES'] . ' ' . $row['topic_replies'] . ' - ' . $user->lang['VIEWS'] . ' ' . $row['topic_views'];
		}
	}
}


?>