<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
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
