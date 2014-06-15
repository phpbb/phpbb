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

namespace phpbb\feed;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Factory class to return correct object
*/
class factory
{
	/**
	* Service container object
	* @var ContainerInterface
	*/
	protected $container;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	* Constructor
	*
	* @param ContainerInterface					$container	Container object
	* @param \phpbb\config\config				$config		Config object
	* @param \phpbb\db\driver\driver_interface	$db			Database connection
	*/
	public function __construct(ContainerInterface $container, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db)
	{
		$this->container = $container;
		$this->config = $config;
		$this->db = $db;
	}

	/**
	* Return correct object for specified mode
	*
	* @param string	$mode		The feeds mode.
	* @param int	$forum_id	Forum id specified by the script if forum feed provided.
	* @param int	$topic_id	Topic id specified by the script if topic feed provided.
	*
	* @return object	Returns correct feeds object for specified mode.
	*/
	function get_feed($mode, $forum_id, $topic_id)
	{
		switch ($mode)
		{
			case 'forums':
				if (!$this->config['feed_overall_forums'])
				{
					return false;
				}

				return $this->container->get('feed.forums');
			break;

			case 'topics':
			case 'topics_new':
				if (!$this->config['feed_topics_new'])
				{
					return false;
				}

				return $this->container->get('feed.topics');
			break;

			case 'topics_active':
				if (!$this->config['feed_topics_active'])
				{
					return false;
				}

				return $this->container->get('feed.topics_active');
			break;

			case 'news':
				// Get at least one news forum
				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE ' . $this->db->sql_bit_and('forum_options', FORUM_OPTION_FEED_NEWS, '<> 0');
				$result = $this->db->sql_query_limit($sql, 1, 0, 600);
				$s_feed_news = (int) $this->db->sql_fetchfield('forum_id');
				$this->db->sql_freeresult($result);

				if (!$s_feed_news)
				{
					return false;
				}

				return $this->container->get('feed.news');
			break;

			default:
				if ($topic_id && $this->config['feed_topic'])
				{
					return $this->container->get('feed.topic')
								->set_topic_id($topic_id);
				}
				else if ($forum_id && $this->config['feed_forum'])
				{
					return $this->container->get('feed.forum')
								->set_forum_id($forum_id);
				}
				else if ($this->config['feed_overall'])
				{
				return $this->container->get('feed.overall');
				}

				return false;
			break;
		}
	}
}
