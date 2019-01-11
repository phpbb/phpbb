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

namespace phpbb\forum\controller;

use phpbb\exception\http_exception;
use phpbb\forum\exception\forum_not_found_exception;
use phpbb\forum\exception\forum_password_needed_exception;
use phpbb\forum\exception\login_required_exception;
use phpbb\forum\exception\permission_denied_exception;
use phpbb\request\request_interface;

/**
 * Forum view controller.
 */
class viewforum
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\forum\forum_retriever */
	protected $forum_retriever;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\forum\render\helper */
	protected $render_helper;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\forum\visibility_helper */
	protected $visibility_helper;

	/** @var array */
	protected $parameters = [];

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\forum\forum_retriever $forum_retriever,
		\phpbb\language\language $language,
		\phpbb\forum\render\helper $render_helper,
		request_interface $request,
		\phpbb\user $user,
		\phpbb\forum\visibility_helper $visibility_helper)
	{
		$this->config = $config;
		$this->controller_helper = $helper;
		$this->dispatcher = $dispatcher;
		$this->forum_retriever = $forum_retriever;
		$this->language = $language;
		$this->render_helper = $render_helper;
		$this->request = $request;
		$this->user = $user;
		$this->visibility_helper = $visibility_helper;
	}

	/**
	 * Renders the list of topics or subforums of the specified forum.
	 *
	 * @param int		$forum_id	The ID of the forum to display.
	 * @param string	$parameters	URL parameters.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response HTTP response with the rendered page.
	 */
	public function show_forum($forum_id, $parameters)
	{
		$this->get_request_parameters($forum_id);
		return $this->render_forum($forum_id, $parameters);
	}

	/**
	 * Renders the forum.
	 *
	 * @param int		$forum_id	The ID of the forum to display.
	 * @param string	$parameters	URL parameters.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response HTTP response with the rendered page.
	 */
	protected function render_forum($forum_id, $parameters)
	{
		$forum_data = [];
		$forum_parents = [];
		$parameters = $this->parameters;
		$forum_tracking_info = $active_forum_ary = $forum_rows = $subforums = $valid_categories = $forum_ids_moderator = $moderators = [];

		// @todo: replace with param processing
		$has_page_param = false;

		try
		{
			$forum_data = $this->forum_retriever->get_forum_metadata($forum_id);
			$this->user->setup('viewforum', $forum_data['forum_style']);
			$this->visibility_helper->check($forum_data, $parameters);

			if ($this->request->is_set('e', request_interface::GET) && !$this->user->data['is_registered'])
			{
				throw new login_required_exception('LOGIN_NOTIFY_FORUM');
			}

			// Is this forum a link? ... User got here either because the
			// number of clicks is being tracked or they guessed the id
			if ($forum_data['forum_type'] == FORUM_LINK && $forum_data['forum_link'])
			{
				// Does it have click tracking enabled?
				if ($forum_data['forum_flags'] & FORUM_FLAG_LINK_TRACK)
				{
					$this->forum_retriever->update_link_count($forum_data);
				}

				// We redirect to the url. The third parameter indicates that external redirects are allowed.
				redirect($forum_data['forum_link'], false, true);
			}

			$forum_parents = $this->forum_retriever->get_forum_parents($forum_data);

			// This is mostly done for BC reasons...
			$forum_data['forum_parents'] = serialize($forum_parents);

			$forum_parents = $this->visibility_helper->filter_forum_parents($forum_parents);

			if ($forum_data['left_id'] != $forum_data['right_id'] - 1)
			{
				$rows = $this->forum_retriever->get_subforums($forum_data);
				$rows = $this->visibility_helper->filter_subforums($rows);

				list($forum_rows, $subforums, $valid_categories, $forum_ids_moderator, $forum_tracking_info) = $this->forum_retriever->get_subforum_hierarchy(
					$forum_data,
					$rows
				);

				$active_forum_ary = $this->forum_retriever->get_active_topic_array($forum_data, $rows);
				unset ($rows);
			}

			// @todo: handle this madness
			//$template->assign_var('S_HAS_SUBFORUM', false);
			if ($this->config['load_moderators'])
			{
				get_moderators($moderators, $forum_id);
			}
		}
		catch (forum_not_found_exception $e)
		{
			throw new http_exception(404, $e->getMessage());
		}
		catch (forum_password_needed_exception $e)
		{
			$password = (isset($parameters['forum_password'])) ? $parameters['forum_password'] : false;
			return $this->render_helper->render_forum_password_box($forum_data, $password);
		}
		catch (login_required_exception $e)
		{
			// @todo: login box.
		}
		catch (permission_denied_exception $e)
		{
			throw new http_exception(403, $e->getMessage());
		}

		$this->render_helper->generate_navigation($forum_data, $forum_parents);
		$this->render_helper->render_forum_rules($forum_data);
		$this->render_helper->render_subforums(
			$forum_data,
			$forum_rows,
			$subforums,
			$valid_categories,
			$forum_tracking_info,
			$moderators
		);

		// Calculate the page title.
		$page_title = $forum_data['forum_name'];
		if ($has_page_param)
		{
			// @todo: handle the page number stuff.
			$page_title = ' - ' . $this->language->lang('PAGE_TITLE_NUMBER', 1);
		}

		$start = 0;

		/**
		 * You can use this event to modify the page title of the viewforum page
		 *
		 * @event core.viewforum_modify_page_title
		 * @var	string	page_title		Title of the viewforum page.
		 * @var	array	forum_data		Array with forum data
		 * @var	int		forum_id		The forum ID
		 * @var	int		start			Start offset used to calculate the page
		 * @since 3.2.2-RC1
		 */
		$vars = array('page_title', 'forum_data', 'forum_id', 'start');
		extract($this->dispatcher->trigger_event('core.viewforum_modify_page_title', compact($vars)));

		/**
		 * This event is to perform additional actions on viewforum page
		 *
		 * @event core.viewforum_generate_page_after
		 * @var	array	forum_data	Array with the forum data
		 * @since 3.2.2-RC1
		 */
		$vars = array('forum_data');
		extract($this->dispatcher->trigger_event('core.viewforum_generate_page_after', compact($vars)));

		return $this->controller_helper->render(
			'viewforum_body.html',
			$page_title
		);
	}

	/**
	 * Process additional request parameters (GET, POST, etc) that might be set.
	 */
	protected function get_request_parameters($forum_id)
	{
		// Check for forum password.
		if ($this->request->is_set('password', request_interface::POST))
		{
			$form_name = 'forum_password_' . $forum_id;
			if (check_form_key($form_name))
			{
				$this->parameters['forum_password'] = $this->request->variable('password', '', true);
			}
		}
	}
}
