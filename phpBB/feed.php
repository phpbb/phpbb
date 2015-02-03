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
	'FEED_IMAGE'			=> ($user->img('site_logo', '', false, '', 'src')) ? $board_url . '/' . substr($user->img('site_logo', '', false, '', 'src'), strlen($phpbb_root_path)) : '',
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
if (defined('DEBUG_EXTRA') && request_var('explain', 0) && $auth->acl_get('a_'))
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

		$zone_offset = (int) $user->timezone + (int) $user->dst;

		$sign = ($zone_offset < 0) ? '-' : '+';
		$time_offset = abs($zone_offset);

		$offset_seconds	= $time_offset % 3600;
		$offset_minutes	= $offset_seconds / 60;
		$offset_hours	= ($time_offset - $offset_seconds) / 3600;

		$offset_string	= sprintf("%s%02d:%02d", $sign, $offset_hours, $offset_minutes);
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
			case 'topics_new':
				if (!$config['feed_topics_new'])
				{
					return false;
				}

				return new phpbb_feed_topics();
			break;

			case 'topics_active':
				if (!$config['feed_topics_active'])
				{
					return false;
				}

				return new phpbb_feed_topics_active();
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
				if ($topic_id && $config['feed_topic'])
				{
					return new phpbb_feed_topic($topic_id);
				}
				else if ($forum_id && $config['feed_forum'])
				{
					return new phpbb_feed_forum($forum_id);
				}
				else if ($config['feed_overall'])
				{
					return new phpbb_feed_overall();
				}

				return false;
			break;
		}
	}
}

/**
* Base class with some generic functions and settings.
*
* @package phpBB3
*/
class phpbb_feed_base
{
	/**
	* SQL Query to be executed to get feed items
	*/
	var $sql = array();

	/**
	* Keys specified for retrieval of title, content, etc.
	*/
	var $keys = array();

	/**
	* Number of items to fetch. Usually overwritten by $config['feed_something']
	*/
	var $num_items = 15;

	/**
	* Separator for title elements to separate items (for example forum / topic)
	*/
	var $separator = "\xE2\x80\xA2"; // &bull;

	/**
	* Separator for the statistics row (Posted by, post date, replies, etc.)
	*/
	var $separator_stats = "\xE2\x80\x94"; // &mdash;

	/** @var mixed Query result handle */
	var $result;

	/**
	* Constructor
	*/
	function phpbb_feed_base()
	{
		global $config;

		$this->set_keys();

		// Allow num_items to be string
		if (is_string($this->num_items))
		{
			$this->num_items = (int) $config[$this->num_items];

			// A precaution
			if (!$this->num_items)
			{
				$this->num_items = 10;
			}
		}
	}

	/**
	* Set keys.
	*/
	function set_keys()
	{
	}

	/**
	* Open feed
	*/
	function open()
	{
	}

	/**
	* Close feed
	*/
	function close()
	{
		global $db;

		if (!empty($this->result))
		{
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

	function get_readable_forums()
	{
		global $auth;
		static $forum_ids;

		if (!isset($forum_ids))
		{
			$forum_ids = array_keys($auth->acl_getf('f_read', true));
		}

		return $forum_ids;
	}

	function get_moderator_approve_forums()
	{
		global $auth;
		static $forum_ids;

		if (!isset($forum_ids))
		{
			$forum_ids = array_keys($auth->acl_getf('m_approve', true));
		}

		return $forum_ids;
	}

	function is_moderator_approve_forum($forum_id)
	{
		static $forum_ids;

		if (!isset($forum_ids))
		{
			$forum_ids = array_flip($this->get_moderator_approve_forums());
		}

		if (!$forum_id)
		{
			// Global announcement, your a moderator in any forum than it's okay.
			return (!empty($forum_ids)) ? true : false;
		}

		return (isset($forum_ids[$forum_id])) ? true : false;
	}

	function get_excluded_forums()
	{
		global $db, $cache;
		static $forum_ids;

		// Matches acp/acp_board.php
		$cache_name	= 'feed_excluded_forum_ids';

		if (!isset($forum_ids) && ($forum_ids = $cache->get('_' . $cache_name)) === false)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_EXCLUDE, '<> 0');
			$result = $db->sql_query($sql);

			$forum_ids = array();
			while ($forum_id = (int) $db->sql_fetchfield('forum_id'))
			{
				$forum_ids[$forum_id] = $forum_id;
			}
			$db->sql_freeresult($result);

			$cache->put('_' . $cache_name, $forum_ids);
		}

		return $forum_ids;
	}

	function is_excluded_forum($forum_id)
	{
		$forum_ids = $this->get_excluded_forums();

		return isset($forum_ids[$forum_id]) ? true : false;
	}

	function get_passworded_forums()
	{
		global $user;

		return $user->get_passworded_forums();
	}

	function get_item()
	{
		global $db;

		if (!isset($this->result))
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

	function user_viewprofile($row)
	{
		global $phpEx, $user;

		$author_id = (int) $row[$this->get('author_id')];

		if ($author_id == ANONYMOUS)
		{
			// Since we cannot link to a profile, we just return GUEST
			// instead of $row['username']
			return $user->lang['GUEST'];
		}

		return '<a href="' . feed_append_sid('/memberlist.' . $phpEx, 'mode=viewprofile&amp;u=' . $author_id) . '">' . $row[$this->get('creator')] . '</a>';
	}
}

/**
* Abstract class for post based feeds
*
* @package phpBB3
*/
class phpbb_feed_post_base extends phpbb_feed_base
{
	var $num_items = 'feed_limit_post';

	function set_keys()
	{
		$this->set('title',		'post_subject');
		$this->set('title2',	'topic_title');

		$this->set('author_id',	'user_id');
		$this->set('creator',	'username');
		$this->set('published',	'post_time');
		$this->set('updated',	'post_edit_time');
		$this->set('text',		'post_text');

		$this->set('bitfield',	'bbcode_bitfield');
		$this->set('bbcode_uid','bbcode_uid');

		$this->set('enable_bbcode',		'enable_bbcode');
		$this->set('enable_smilies',	'enable_smilies');
		$this->set('enable_magic_url',	'enable_magic_url');
	}

	function adjust_item(&$item_row, &$row)
	{
		global $phpEx, $config, $user;

		$item_row['link'] = feed_append_sid('/viewtopic.' . $phpEx, "t={$row['topic_id']}&amp;p={$row['post_id']}#p{$row['post_id']}");

		if ($config['feed_item_statistics'])
		{
			$item_row['statistics'] = $user->lang['POSTED'] . ' ' . $user->lang['POST_BY_AUTHOR'] . ' ' . $this->user_viewprofile($row)
				. ' ' . $this->separator_stats . ' ' . $user->format_date($row[$this->get('published')])
				. (($this->is_moderator_approve_forum($row['forum_id']) && !$row['post_approved']) ? ' ' . $this->separator_stats . ' ' . $user->lang['POST_UNAPPROVED'] : '');
		}
	}
}

/**
* Abstract class for topic based feeds
*
* @package phpBB3
*/
class phpbb_feed_topic_base extends phpbb_feed_base
{
	var $num_items = 'feed_limit_topic';

	function set_keys()
	{
		$this->set('title',		'topic_title');
		$this->set('title2',	'forum_name');

		$this->set('author_id',	'topic_poster');
		$this->set('creator',	'topic_first_poster_name');
		$this->set('published',	'post_time');
		$this->set('updated',	'post_edit_time');
		$this->set('text',		'post_text');

		$this->set('bitfield',	'bbcode_bitfield');
		$this->set('bbcode_uid','bbcode_uid');

		$this->set('enable_bbcode',		'enable_bbcode');
		$this->set('enable_smilies',	'enable_smilies');
		$this->set('enable_magic_url',	'enable_magic_url');
	}

	function adjust_item(&$item_row, &$row)
	{
		global $phpEx, $config, $user;

		$item_row['link'] = feed_append_sid('/viewtopic.' . $phpEx, 't=' . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '#p' . $row['post_id']);

		if ($config['feed_item_statistics'])
		{
			$item_row['statistics'] = $user->lang['POSTED'] . ' ' . $user->lang['POST_BY_AUTHOR'] . ' ' . $this->user_viewprofile($row)
				. ' ' . $this->separator_stats . ' ' . $user->format_date($row[$this->get('published')])
				. ' ' . $this->separator_stats . ' ' . $user->lang['REPLIES'] . ' ' . (($this->is_moderator_approve_forum($row['forum_id'])) ? $row['topic_replies_real'] : $row['topic_replies'])
				. ' ' . $this->separator_stats . ' ' . $user->lang['VIEWS'] . ' ' . $row['topic_views']
				. (($this->is_moderator_approve_forum($row['forum_id']) && ($row['topic_replies_real'] != $row['topic_replies'])) ? ' ' . $this->separator_stats . ' ' . $user->lang['POSTS_UNAPPROVED'] : '');
		}
	}
}

/**
* Board wide feed (aka overall feed)
*
* This will give you the newest {$this->num_items} posts
* from the whole board.
*
* @package phpBB3
*/
class phpbb_feed_overall extends phpbb_feed_post_base
{
	function get_sql()
	{
		global $auth, $db;

		$forum_ids = array_diff($this->get_readable_forums(), $this->get_excluded_forums(), $this->get_passworded_forums());
		if (empty($forum_ids))
		{
			return false;
		}

		// Add global forum id
		$forum_ids[] = 0;

		// m_approve forums
		$fid_m_approve = $this->get_moderator_approve_forums();
		$sql_m_approve = (!empty($fid_m_approve)) ? 'OR ' . $db->sql_in_set('forum_id', $fid_m_approve) : '';

		// Determine topics with recent activity
		$sql = 'SELECT topic_id, topic_last_post_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $forum_ids) . '
				AND topic_moved_id = 0
				AND (topic_approved = 1
					' . $sql_m_approve . ')
			ORDER BY topic_last_post_time DESC';
		$result = $db->sql_query_limit($sql, $this->num_items);

		$topic_ids = array();
		$min_post_time = 0;
		while ($row = $db->sql_fetchrow())
		{
			$topic_ids[] = (int) $row['topic_id'];

			$min_post_time = (int) $row['topic_last_post_time'];
		}
		$db->sql_freeresult($result);

		if (empty($topic_ids))
		{
			return false;
		}

		// Get the actual data
		$this->sql = array(
			'SELECT'	=>	'f.forum_id, f.forum_name, ' .
							'p.post_id, p.topic_id, p.post_time, p.post_edit_time, p.post_approved, p.post_subject, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, ' .
							'u.username, u.user_id',
			'FROM'		=> array(
				USERS_TABLE		=> 'u',
				POSTS_TABLE		=> 'p',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE	=> 'f'),
					'ON'	=> 'f.forum_id = p.forum_id',
				),
			),
			'WHERE'		=> $db->sql_in_set('p.topic_id', $topic_ids) . '
							AND (p.post_approved = 1
								' . str_replace('forum_id', 'p.forum_id', $sql_m_approve) . ')
							AND p.post_time >= ' . $min_post_time . '
							AND u.user_id = p.poster_id',
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}

	function adjust_item(&$item_row, &$row)
	{
		parent::adjust_item($item_row, $row);

		$item_row['title'] = (isset($row['forum_name']) && $row['forum_name'] !== '') ? $row['forum_name'] . ' ' . $this->separator . ' ' . $item_row['title'] : $item_row['title'];
	}
}

/**
* Forum feed
*
* This will give you the last {$this->num_items} posts made
* within a specific forum.
*
* @package phpBB3
*/
class phpbb_feed_forum extends phpbb_feed_post_base
{
	var $forum_id		= 0;
	var $forum_data		= array();

	function phpbb_feed_forum($forum_id)
	{
		parent::phpbb_feed_base();

		$this->forum_id = (int) $forum_id;
	}

	function open()
	{
		global $db, $auth;

		// Check if forum exists
		$sql = 'SELECT forum_id, forum_name, forum_password, forum_type, forum_options
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $this->forum_id;
		$result = $db->sql_query($sql);
		$this->forum_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (empty($this->forum_data))
		{
			trigger_error('NO_FORUM');
		}

		// Forum needs to be postable
		if ($this->forum_data['forum_type'] != FORUM_POST)
		{
			trigger_error('NO_FEED');
		}

		// Make sure forum is not excluded from feed
		if (phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $this->forum_data['forum_options']))
		{
			trigger_error('NO_FEED');
		}

		// Make sure we can read this forum
		if (!$auth->acl_get('f_read', $this->forum_id))
		{
			trigger_error('SORRY_AUTH_READ');
		}

		// Make sure forum is not passworded or user is authed
		if ($this->forum_data['forum_password'])
		{
			$forum_ids_passworded = $this->get_passworded_forums();

			if (isset($forum_ids_passworded[$this->forum_id]))
			{
				trigger_error('SORRY_AUTH_READ');
			}

			unset($forum_ids_passworded);
		}
	}

	function get_sql()
	{
		global $auth, $db;

		$m_approve = ($auth->acl_get('m_approve', $this->forum_id)) ? true : false;
		$forum_ids = array(0, $this->forum_id);

		// Determine topics with recent activity
		$sql = 'SELECT topic_id, topic_last_post_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $forum_ids) . '
				AND topic_moved_id = 0
				' . ((!$m_approve) ? 'AND topic_approved = 1' : '') . '
			ORDER BY topic_last_post_time DESC';
		$result = $db->sql_query_limit($sql, $this->num_items);

		$topic_ids = array();
		$min_post_time = 0;
		while ($row = $db->sql_fetchrow())
		{
			$topic_ids[] = (int) $row['topic_id'];

			$min_post_time = (int) $row['topic_last_post_time'];
		}
		$db->sql_freeresult($result);

		if (empty($topic_ids))
		{
			return false;
		}

		$this->sql = array(
			'SELECT'	=>	'p.post_id, p.topic_id, p.post_time, p.post_edit_time, p.post_approved, p.post_subject, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, ' .
							'u.username, u.user_id',
			'FROM'		=> array(
				POSTS_TABLE		=> 'p',
				USERS_TABLE		=> 'u',
			),
			'WHERE'		=> $db->sql_in_set('p.topic_id', $topic_ids) . '
							' . ((!$m_approve) ? 'AND p.post_approved = 1' : '') . '
							AND p.post_time >= ' . $min_post_time . '
							AND p.poster_id = u.user_id',
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}

	function adjust_item(&$item_row, &$row)
	{
		parent::adjust_item($item_row, $row);

		$item_row['title'] = (isset($row['forum_name']) && $row['forum_name'] !== '') ? $row['forum_name'] . ' ' . $this->separator . ' ' . $item_row['title'] : $item_row['title'];
	}

	function get_item()
	{
		return ($row = parent::get_item()) ? array_merge($this->forum_data, $row) : $row;
	}
}

/**
* Topic feed for a specific topic
*
* This will give you the last {$this->num_items} posts made within this topic.
*
* @package phpBB3
*/
class phpbb_feed_topic extends phpbb_feed_post_base
{
	var $topic_id		= 0;
	var $forum_id		= 0;
	var $topic_data		= array();

	function phpbb_feed_topic($topic_id)
	{
		parent::phpbb_feed_base();

		$this->topic_id = (int) $topic_id;
	}

	function open()
	{
		global $auth, $db, $user;

		$sql = 'SELECT f.forum_options, f.forum_password, t.topic_id, t.forum_id, t.topic_approved, t.topic_title, t.topic_time, t.topic_views, t.topic_replies, t.topic_type
			FROM ' . TOPICS_TABLE . ' t
			LEFT JOIN ' . FORUMS_TABLE . ' f
				ON (f.forum_id = t.forum_id)
			WHERE t.topic_id = ' . $this->topic_id;
		$result = $db->sql_query($sql);
		$this->topic_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (empty($this->topic_data))
		{
			trigger_error('NO_TOPIC');
		}

		if ($this->topic_data['topic_type'] == POST_GLOBAL)
		{
			// We need to find at least one postable forum where feeds are enabled,
			// that the user can read and maybe also has approve permissions.
			$in_fid_ary = $this->get_readable_forums();

			if (empty($in_fid_ary))
			{
				// User cannot read any forums
				trigger_error('SORRY_AUTH_READ');
			}

			if (!$this->topic_data['topic_approved'])
			{
				// Also require m_approve
				$in_fid_ary = array_intersect($in_fid_ary, $this->get_moderator_approve_forums());

				if (empty($in_fid_ary))
				{
					trigger_error('SORRY_AUTH_READ');
				}
			}

			// Diff excluded forums
			$in_fid_ary = array_diff($in_fid_ary, $this->get_excluded_forums());

			if (empty($in_fid_ary))
			{
				trigger_error('SORRY_AUTH_READ');
			}

			// Also exclude passworded forums
			$in_fid_ary = array_diff($in_fid_ary, $this->get_passworded_forums());

			if (empty($in_fid_ary))
			{
				trigger_error('SORRY_AUTH_READ');
			}

			$sql = 'SELECT forum_id, left_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type = ' . FORUM_POST . '
					AND ' . $db->sql_in_set('forum_id', $in_fid_ary) . '
				ORDER BY left_id ASC';
			$result = $db->sql_query_limit($sql, 1);
			$this->forum_data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (empty($this->forum_data))
			{
				// No forum found.
				trigger_error('SORRY_AUTH_READ');
			}

			unset($in_fid_ary);
		}
		else
		{
			$this->forum_id = (int) $this->topic_data['forum_id'];

			// Make sure topic is either approved or user authed
			if (!$this->topic_data['topic_approved'] && !$auth->acl_get('m_approve', $this->forum_id))
			{
				trigger_error('SORRY_AUTH_READ');
			}

			// Make sure forum is not excluded from feed
			if (phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $this->topic_data['forum_options']))
			{
				trigger_error('NO_FEED');
			}

			// Make sure we can read this forum
			if (!$auth->acl_get('f_read', $this->forum_id))
			{
				trigger_error('SORRY_AUTH_READ');
			}

			// Make sure forum is not passworded or user is authed
			if ($this->topic_data['forum_password'])
			{
				$forum_ids_passworded = $this->get_passworded_forums();

				if (isset($forum_ids_passworded[$this->forum_id]))
				{
					trigger_error('SORRY_AUTH_READ');
				}

				unset($forum_ids_passworded);
			}
		}
	}

	function get_sql()
	{
		global $auth, $db;

		$this->sql = array(
			'SELECT'	=>	'p.post_id, p.post_time, p.post_edit_time, p.post_approved, p.post_subject, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, ' .
							'u.username, u.user_id',
			'FROM'		=> array(
				POSTS_TABLE		=> 'p',
				USERS_TABLE		=> 'u',
			),
			'WHERE'		=> 'p.topic_id = ' . $this->topic_id . '
								' . ($this->forum_id && !$auth->acl_get('m_approve', $this->forum_id) ? 'AND p.post_approved = 1' : '') . '
								AND p.poster_id = u.user_id',
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}

	function get_item()
	{
		return ($row = parent::get_item()) ? array_merge($this->topic_data, $row) : $row;
	}
}

/**
* 'All Forums' feed
*
* This will give you a list of all postable forums where feeds are enabled
* including forum description, topic stats and post stats
*
* @package phpBB3
*/
class phpbb_feed_forums extends phpbb_feed_base
{
	var $num_items	= 0;

	function set_keys()
	{
		$this->set('title',		'forum_name');
		$this->set('text',		'forum_desc');
		$this->set('bitfield',	'forum_desc_bitfield');
		$this->set('bbcode_uid','forum_desc_uid');
		$this->set('updated',	'forum_last_post_time');
		$this->set('options',	'forum_desc_options');
	}

	function get_sql()
	{
		global $auth, $db;

		$in_fid_ary = array_diff($this->get_readable_forums(), $this->get_excluded_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		// Build SQL Query
		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.left_id, f.forum_name, f.forum_last_post_time,
							f.forum_desc, f.forum_desc_bitfield, f.forum_desc_uid, f.forum_desc_options,
							f.forum_topics, f.forum_posts',
			'FROM'		=> array(FORUMS_TABLE => 'f'),
			'WHERE'		=> 'f.forum_type = ' . FORUM_POST . '
							AND ' . $db->sql_in_set('f.forum_id', $in_fid_ary),
			'ORDER_BY'	=> 'f.left_id ASC',
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

			$item_row['statistics'] = sprintf($user->lang['TOTAL_TOPICS_OTHER'], $row['forum_topics'])
				. ' ' . $this->separator_stats . ' ' . sprintf($user->lang['TOTAL_POSTS_OTHER'], $row['forum_posts']);
		}
	}
}

/**
* News feed
*
* This will give you {$this->num_items} first posts
* of all topics in the selected news forums.
*
* @package phpBB3
*/
class phpbb_feed_news extends phpbb_feed_topic_base
{
	function get_news_forums()
	{
		global $db, $cache;
		static $forum_ids;

		// Matches acp/acp_board.php
		$cache_name	= 'feed_news_forum_ids';

		if (!isset($forum_ids) && ($forum_ids = $cache->get('_' . $cache_name)) === false)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_NEWS, '<> 0');
			$result = $db->sql_query($sql);

			$forum_ids = array();
			while ($forum_id = (int) $db->sql_fetchfield('forum_id'))
			{
				$forum_ids[$forum_id] = $forum_id;
			}
			$db->sql_freeresult($result);

			$cache->put('_' . $cache_name, $forum_ids);
		}

		return $forum_ids;
	}

	function get_sql()
	{
		global $auth, $config, $db;

		// Determine forum ids
		$in_fid_ary = array_intersect($this->get_news_forums(), $this->get_readable_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		$in_fid_ary = array_diff($in_fid_ary, $this->get_passworded_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		// Add global forum
		$in_fid_ary[] = 0;

		// We really have to get the post ids first!
		$sql = 'SELECT topic_first_post_id, topic_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $in_fid_ary) . '
				AND topic_moved_id = 0
				AND topic_approved = 1
			ORDER BY topic_time DESC';
		$result = $db->sql_query_limit($sql, $this->num_items);

		$post_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['topic_first_post_id'];
		}
		$db->sql_freeresult($result);

		if (empty($post_ids))
		{
			return false;
		}

		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.forum_name,
							t.topic_id, t.topic_title, t.topic_poster, t.topic_first_poster_name, t.topic_replies, t.topic_replies_real, t.topic_views, t.topic_time, t.topic_last_post_time,
							p.post_id, p.post_time, p.post_edit_time, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't',
				POSTS_TABLE		=> 'p',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE => 'f'),
					'ON'	=> 'p.forum_id = f.forum_id',
				),
			),
			'WHERE'		=> 'p.topic_id = t.topic_id
							AND ' . $db->sql_in_set('p.post_id', $post_ids),
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}
}

/**
* New Topics feed
*
* This will give you the last {$this->num_items} created topics
* including the first post.
*
* @package phpBB3
*/
class phpbb_feed_topics extends phpbb_feed_topic_base
{
	function get_sql()
	{
		global $db, $config;

		$forum_ids_read = $this->get_readable_forums();
		if (empty($forum_ids_read))
		{
			return false;
		}

		$in_fid_ary = array_diff($forum_ids_read, $this->get_excluded_forums(), $this->get_passworded_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		// Add global forum
		$in_fid_ary[] = 0;

		// We really have to get the post ids first!
		$sql = 'SELECT topic_first_post_id, topic_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $in_fid_ary) . '
				AND topic_moved_id = 0
				AND topic_approved = 1
			ORDER BY topic_time DESC';
		$result = $db->sql_query_limit($sql, $this->num_items);

		$post_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['topic_first_post_id'];
		}
		$db->sql_freeresult($result);

		if (empty($post_ids))
		{
			return false;
		}

		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.forum_name,
							t.topic_id, t.topic_title, t.topic_poster, t.topic_first_poster_name, t.topic_replies, t.topic_replies_real, t.topic_views, t.topic_time, t.topic_last_post_time,
							p.post_id, p.post_time, p.post_edit_time, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't',
				POSTS_TABLE		=> 'p',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE => 'f'),
					'ON'	=> 'p.forum_id = f.forum_id',
				),
			),
			'WHERE'		=> 'p.topic_id = t.topic_id
							AND ' . $db->sql_in_set('p.post_id', $post_ids),
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}

	function adjust_item(&$item_row, &$row)
	{
		parent::adjust_item($item_row, $row);

		$item_row['title'] = (isset($row['forum_name']) && $row['forum_name'] !== '') ? $row['forum_name'] . ' ' . $this->separator . ' ' . $item_row['title'] : $item_row['title'];
	}
}

/**
* Active Topics feed
*
* This will give you the last {$this->num_items} topics
* with replies made withing the last {$this->sort_days} days
* including the last post.
*
* @package phpBB3
*/
class phpbb_feed_topics_active extends phpbb_feed_topic_base
{
	var $sort_days = 7;

	function set_keys()
	{
		parent::set_keys();

		$this->set('author_id',	'topic_last_poster_id');
		$this->set('creator',	'topic_last_poster_name');
	}

	function get_sql()
	{
		global $db, $config;

		$forum_ids_read = $this->get_readable_forums();
		if (empty($forum_ids_read))
		{
			return false;
		}

		$in_fid_ary = array_intersect($forum_ids_read, $this->get_forum_ids());
		$in_fid_ary = array_diff($in_fid_ary, $this->get_passworded_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		// Add global forum
		$in_fid_ary[] = 0;

		// Search for topics in last X days
		$last_post_time_sql = ($this->sort_days) ? ' AND topic_last_post_time > ' . (time() - ($this->sort_days * 24 * 3600)) : '';

		// We really have to get the post ids first!
		$sql = 'SELECT topic_last_post_id, topic_last_post_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $in_fid_ary) . '
				AND topic_moved_id = 0
				AND topic_approved = 1
				' . $last_post_time_sql . '
			ORDER BY topic_last_post_time DESC';
		$result = $db->sql_query_limit($sql, $this->num_items);

		$post_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['topic_last_post_id'];
		}
		$db->sql_freeresult($result);

		if (empty($post_ids))
		{
			return false;
		}

		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.forum_name,
							t.topic_id, t.topic_title, t.topic_replies, t.topic_replies_real, t.topic_views,
							t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_post_time,
							p.post_id, p.post_time, p.post_edit_time, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't',
				POSTS_TABLE		=> 'p',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE => 'f'),
					'ON'	=> 'p.forum_id = f.forum_id',
				),
			),
			'WHERE'		=> 'p.topic_id = t.topic_id
							AND ' . $db->sql_in_set('p.post_id', $post_ids),
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}

	function get_forum_ids()
	{
		global $db, $cache;
		static $forum_ids;

		$cache_name	= 'feed_topic_active_forum_ids';

		if (!isset($forum_ids) && ($forum_ids = $cache->get('_' . $cache_name)) === false)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type = ' . FORUM_POST . '
					AND ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_EXCLUDE, '= 0') . '
					AND ' . $db->sql_bit_and('forum_flags', log(FORUM_FLAG_ACTIVE_TOPICS, 2), '<> 0');
			$result = $db->sql_query($sql);

			$forum_ids = array();
			while ($forum_id = (int) $db->sql_fetchfield('forum_id'))
			{
				$forum_ids[$forum_id] = $forum_id;
			}
			$db->sql_freeresult($result);

			$cache->put('_' . $cache_name, $forum_ids, 180);
		}

		return $forum_ids;
	}

	function adjust_item(&$item_row, &$row)
	{
		parent::adjust_item($item_row, $row);

		$item_row['title'] = (isset($row['forum_name']) && $row['forum_name'] !== '') ? $row['forum_name'] . ' ' . $this->separator . ' ' . $item_row['title'] : $item_row['title'];
	}
}


?>