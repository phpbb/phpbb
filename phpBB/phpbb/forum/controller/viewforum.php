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
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\forum\data_transformer */
	protected $forum_data_transformer;

	/** @var \phpbb\forum\forum_retriever */
	protected $forum_retriever;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\forum\render\helper */
	protected $render_helper;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\forum\visibility_helper */
	protected $visibility_helper;

	/** @var array */
	protected $parameters = [];

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\content_visibility $content_visibility,
		\phpbb\controller\helper $helper,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\forum\data_transformer $data_transformer,
		\phpbb\forum\forum_retriever $forum_retriever,
		\phpbb\language\language $language,
		\phpbb\pagination $pagination,
		\phpbb\forum\render\helper $render_helper,
		request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\forum\visibility_helper $visibility_helper)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->controller_helper = $helper;
		$this->dispatcher = $dispatcher;
		$this->forum_data_transformer = $data_transformer;
		$this->forum_retriever = $forum_retriever;
		$this->language = $language;
		$this->render_helper = $render_helper;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
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

		$parameters = $this->parameters;

		// @todo: replace with param processing
		$has_page_param = false;

		try
		{
			$forum_data = $this->forum_retriever->get_forum_metadata($forum_id);
			$forum_data = $this->visibility_helper->check($forum_data, $parameters);

			$this->user->setup('viewforum', $forum_data['forum_style']);

			if ($this->request->is_set('e', request_interface::GET) &&
				!$this->user->data['is_registered'])
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

			$has_read_access = $this->auth->acl_gets('f_read', 'f_list_topics', $forum_id);
			if (!($forum_data['forum_type'] == FORUM_POST ||
				(($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) && $forum_data['forum_type'] == FORUM_CAT)) ||
				!$has_read_access)
			{
				if (!$has_read_access)
				{
					$this->template->assign_var('S_NO_READ_ACCESS', true);
				}

				return $this->render_page($forum_data);
			}

			if ($forum_data['forum_topics_per_page'])
			{
				$this->config['topics_per_page'] = $forum_data['forum_topics_per_page'];
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

		$response = $this->render_page($forum_data);

		/**
		 * This event is to perform additional actions on viewforum page
		 *
		 * @event core.viewforum_generate_page_after
		 * @var	array	forum_data	Array with the forum data
		 * @since 3.2.2-RC1
		 */
		$vars = array('forum_data');
		extract($this->dispatcher->trigger_event('core.viewforum_generate_page_after', compact($vars)));

		return $response;
	}

	/**
	 * Process additional request parameters (GET, POST, etc) that might be set.
	 *
	 * @param int $forum_id The ID of the forum currently requested.
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

	/*
	 * @todo: navigation, subforums etc...
	 */
	protected function render_page_header(array $forum_data)
	{
		$forum_id = (int) $forum_data['forum_id'];

		$forum_rows = [];
		$subforums = [];
		$valid_categories = [];
		$forum_tracking_info = [];
		$moderators = [];
		$moderator_ids = [];
		$active_forum_ary = [];

		$forum_parents = $this->forum_retriever->get_forum_parents($forum_data);

		$forum_parents = $this->visibility_helper->filter_forum_parents($forum_parents);

		if ($forum_data['left_id'] != $forum_data['right_id'] - 1)
		{
			$rows = $this->forum_retriever->get_subforums($forum_data);
			$rows = $this->visibility_helper->filter_subforums($rows);

			list($forum_rows, $subforums, $valid_categories, $forum_tracking_info) = $this->forum_data_transformer->get_subforum_hierarchy(
				$forum_data,
				$rows
			);

			$active_forum_ary = $this->forum_data_transformer->get_active_topic_array($forum_data, $rows);
			$moderator_ids = $this->forum_data_transformer->get_non_category_ids($forum_rows);
		}

		if ($this->config['load_moderators'])
		{
			$moderator_ids[] = $forum_id;
			get_moderators($moderators, $moderator_ids);
			unset($moderator_ids);
		}

		if ($this->auth->acl_get('f_list', $forum_id))
		{
			$this->render_helper->generate_navigation($forum_data, $forum_parents);
		}

		if ($this->auth->acl_get('f_read', $forum_id))
		{
			$this->render_helper->render_forum_rules($forum_data);
		}

		$this->render_helper->render_subforums(
			$forum_data,
			$forum_rows,
			$subforums,
			$valid_categories,
			$forum_tracking_info,
			$moderators
		);

		// @todo: param handling
		$route = $this->controller_helper->route(
			'phpbb_view_forum',
			[
				'forum_id' => $forum_id,
				'parameters' => ''
			]
		);

		//$this->render_helper->render_forum_data($forum_data, $active_forum_ary);

		// @todo: append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . (($start == 0) ? '' : "&amp;start=$start")),
		$this->template->assign_var('U_VIEW_FORUM', $route);
	}

	/*
	 * @todo: render function mostly to get around the fact that viewforum.php had a bunch of exit points
	 */
	protected function render_page(array $forum_data)
	{
		$forum_id = (int) $forum_data['forum_id'];
		$start = 0;

		$this->render_page_header($forum_data);

		// @todo:
//		$topics_count = $phpbb_content_visibility->get_count('forum_topics', $forum_data, $forum_id);
//		$start = $pagination->validate_start($start, $config['topics_per_page'], $topics_count);
//		$page_title = $forum_data['forum_name'] .
//			($start ? ' - ' . $user->lang('PAGE_TITLE_NUMBER', $pagination->get_on_page($config['topics_per_page'], $start)) : '');
		$page_title = $forum_data['forum_name'];

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

		return $this->controller_helper->render(
			'viewforum_body.html',
			$page_title,
			200,
			true,
			$forum_id
		);
	}
}
