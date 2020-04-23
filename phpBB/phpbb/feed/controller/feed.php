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

namespace phpbb\feed\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use \phpbb\event\dispatcher_interface;
use phpbb\exception\http_exception;
use phpbb\feed\feed_interface;
use phpbb\feed\exception\feed_unavailable_exception;
use phpbb\feed\exception\unauthorized_exception;
use phpbb\feed\helper as feed_helper;
use phpbb\controller\helper as controller_helper;
use phpbb\symfony_request;
use phpbb\user;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class feed
{
	/**
	 * @var \Twig_Environment
	 */
	protected $template;

	/**
	 * @var symfony_request
	 */
	protected $request;

	/**
	 * @var controller_helper
	 */
	protected $controller_helper;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var feed_helper
	 */
	protected $feed_helper;

	/**
	 * @var user
	 */
	protected $user;

	/**
	 * @var auth
	 */
	protected $auth;

	/**
	 * @var dispatcher_interface
	 */
	protected $phpbb_dispatcher;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \Twig_Environment $twig
	 * @param symfony_request $request
	 * @param controller_helper $controller_helper
	 * @param config $config
	 * @param driver_interface $db
	 * @param ContainerInterface $container
	 * @param feed_helper $feed_helper
	 * @param user $user
	 * @param auth $auth
	 * @param dispatcher_interface $phpbb_dispatcher
	 * @param string $php_ext
	 */
	public function __construct(\Twig_Environment $twig, symfony_request $request, controller_helper $controller_helper, config $config, driver_interface $db, ContainerInterface $container, feed_helper $feed_helper, user $user, auth $auth, dispatcher_interface $phpbb_dispatcher, $php_ext)
	{
		$this->request = $request;
		$this->controller_helper = $controller_helper;
		$this->config = $config;
		$this->db = $db;
		$this->container = $container;
		$this->feed_helper = $feed_helper;
		$this->user = $user;
		$this->auth = $auth;
		$this->php_ext = $php_ext;
		$this->template = $twig;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
	}

	/**
	 * Controller for /feed/forums route
	 *
	 * @return Response
	 *
	 * @throws http_exception when the feed is disabled
	 */
	public function forums()
	{
		if (!$this->config['feed_overall_forums'])
		{
			$this->send_unavailable();
		}

		return $this->send_feed($this->container->get('feed.forums'));
	}

	/**
	 * Controller for /feed/news route
	 *
	 * @return Response
	 *
	 * @throws http_exception when the feed is disabled
	 */
	public function news()
	{
		// Get at least one news forum
		$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE ' . $this->db->sql_bit_and('forum_options', FORUM_OPTION_FEED_NEWS, '<> 0');
		$result = $this->db->sql_query_limit($sql, 1, 0, 600);
		$s_feed_news = (int) $this->db->sql_fetchfield('forum_id');
		$this->db->sql_freeresult($result);

		if (!$s_feed_news)
		{
			$this->send_unavailable();
		}

		return $this->send_feed($this->container->get('feed.news'));
	}

	/**
	 * Controller for /feed/topics route
	 *
	 * @return Response
	 *
	 * @throws http_exception when the feed is disabled
	 */
	public function topics()
	{
		if (!$this->config['feed_topics_new'])
		{
			$this->send_unavailable();
		}

		return $this->send_feed($this->container->get('feed.topics'));
	}

	/**
	 * Controller for /feed/topics_new route
	 *
	 * @return Response
	 *
	 * @throws http_exception when the feed is disabled
	 */
	public function topics_new()
	{
		return $this->topics();
	}

	/**
	 * Controller for /feed/topics_active route
	 *
	 * @return Response
	 *
	 * @throws http_exception when the feed is disabled
	 */
	public function topics_active()
	{
		if (!$this->config['feed_topics_active'])
		{
			$this->send_unavailable();
		}

		return $this->send_feed($this->container->get('feed.topics_active'));
	}

	/**
	 * Controller for /feed/forum/{forum_id} route
	 *
	 * @param int $forum_id
	 *
	 * @return Response
	 *
	 * @throws http_exception when the feed is disabled
	 */
	public function forum($forum_id)
	{
		if (!$this->config['feed_forum'])
		{
			$this->send_unavailable();
		}

		return $this->send_feed($this->container->get('feed.forum')->set_forum_id($forum_id));
	}

	/**
	 * Controller for /feed/topic/{topic_id} route
	 *
	 * @param int $topic_id
	 *
	 * @return Response
	 *
	 * @throws http_exception when the feed is disabled
	 */
	public function topic($topic_id)
	{
		if (!$this->config['feed_topic'])
		{
			$this->send_unavailable();
		}

		return $this->send_feed($this->container->get('feed.topic')->set_topic_id($topic_id));
	}

	/**
	 * Controller for /feed/{mode] route
	 *
	 * @return Response
	 *
	 * @throws http_exception when the feed is disabled
	 */
	public function overall()
	{
		if (!$this->config['feed_overall'])
		{
			$this->send_unavailable();
		}

		return $this->send_feed($this->container->get('feed.overall'));
	}

	/**
	 * Display a given feed
	 *
	 * @param feed_interface $feed
	 *
	 * @return Response
	 */
	protected function send_feed(feed_interface $feed)
	{
		try
		{
			return $this->send_feed_do($feed);
		}
		catch (feed_unavailable_exception $e)
		{
			throw new http_exception(Response::HTTP_NOT_FOUND, $e->getMessage(), $e->get_parameters(), $e);
		}
		catch (unauthorized_exception $e)
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, $e->getMessage(), $e->get_parameters(), $e);
		}
	}

	/**
	 * Really send the feed
	 *
	 * @param feed_interface $feed
	 *
	 * @return Response
	 *
	 * @throw exception\feed_exception
	 */
	protected function send_feed_do(feed_interface $feed)
	{
		$feed_updated_time = 0;
		$item_vars = array();

		$board_url = $this->feed_helper->get_board_url();

		// Open Feed
		$feed->open();

		// Iterate through items
		while ($row = $feed->get_item())
		{
			/**
			 * Event to modify the feed row
			 *
			 * @event core.feed_modify_feed_row
			 * @var	int		forum_id	Forum ID
			 * @var	string	mode		Feeds mode (forums|topics|topics_new|topics_active|news)
			 * @var	array	row			Array with feed data
			 * @var	int		topic_id	Topic ID
			 *
			 * @since 3.1.10-RC1
			 */
			$vars = array('forum_id', 'mode', 'row', 'topic_id');
			extract($this->phpbb_dispatcher->trigger_event('core.feed_modify_feed_row', compact($vars)));

			// BBCode options to correctly disable urls, smilies, bbcode...
			if ($feed->get('options') === null)
			{
				// Allow all combinations
				$options = 7;

				if ($feed->get('enable_bbcode') !== null && $feed->get('enable_smilies') !== null && $feed->get('enable_magic_url') !== null)
				{
					$options = (($row[$feed->get('enable_bbcode')]) ? OPTION_FLAG_BBCODE : 0) + (($row[$feed->get('enable_smilies')]) ? OPTION_FLAG_SMILIES : 0) + (($row[$feed->get('enable_magic_url')]) ? OPTION_FLAG_LINKS : 0);
				}
			}
			else
			{
				$options = $row[$feed->get('options')];
			}

			$title = (isset($row[$feed->get('title')]) && $row[$feed->get('title')] !== '') ? $row[$feed->get('title')] : ((isset($row[$feed->get('title2')])) ? $row[$feed->get('title2')] : '');

			$published = ($feed->get('published') !== null) ? (int) $row[$feed->get('published')] : 0;
			$updated = ($feed->get('updated') !== null) ? (int) $row[$feed->get('updated')] : 0;

			$display_attachments = ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']) && isset($row['post_attachment']) && $row['post_attachment']) ? true : false;

			$item_row = array(
				'author'		=> ($feed->get('creator') !== null) ? $row[$feed->get('creator')] : '',
				'published'		=> ($published > 0) ? $this->feed_helper->format_date($published) : '',
				'updated'		=> ($updated > 0) ? $this->feed_helper->format_date($updated) : '',
				'link'			=> '',
				'title'			=> censor_text($title),
				'category'		=> ($this->config['feed_item_statistics'] && !empty($row['forum_id'])) ? $board_url . '/viewforum.' . $this->php_ext . '?f=' . $row['forum_id'] : '',
				'category_name'	=> ($this->config['feed_item_statistics'] && isset($row['forum_name'])) ? $row['forum_name'] : '',
				'description'	=> censor_text($this->feed_helper->generate_content($row[$feed->get('text')], $row[$feed->get('bbcode_uid')], $row[$feed->get('bitfield')], $options, $row['forum_id'], ($display_attachments ? $feed->get_attachments($row['post_id']) : array()))),
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

		$feed->close();

		$content = $this->template->render('feed.xml.twig', array(
			// Some default assignments
			// FEED_IMAGE is not used (atom)
			'FEED_IMAGE'			=> '',
			'SELF_LINK'				=> $this->controller_helper->route($this->request->attributes->get('_route'), $this->request->attributes->get('_route_params'), true, '', UrlGeneratorInterface::ABSOLUTE_URL),
			'FEED_LINK'				=> $board_url . '/index.' . $this->php_ext,
			'FEED_TITLE'			=> $this->config['sitename'],
			'FEED_SUBTITLE'			=> $this->config['site_desc'],
			'FEED_UPDATED'			=> $this->feed_helper->format_date($feed_updated_time),
			'FEED_LANG'				=> $this->user->lang['USER_LANG'],
			'FEED_AUTHOR'			=> $this->config['sitename'],

			// Feed entries
			'FEED_ROWS'				=> $item_vars,
		));

		$response = new Response($content);
		$response->headers->set('Content-Type', 'application/atom+xml; charset=UTF-8');
		$response->setLastModified(new \DateTime('@' . $feed_updated_time));

		if (!empty($this->user->data['is_bot']))
		{
			// Let reverse proxies know we detected a bot.
			$response->headers->set('X-PHPBB-IS-BOT', 'yes');
		}

		return $response;
	}

	/**
	 * Throw and exception saying that the feed isn't available
	 *
	 * @throw http_exception
	 */
	protected function send_unavailable()
	{
		throw new http_exception(404, 'FEATURE_NOT_AVAILABLE');
	}
}
