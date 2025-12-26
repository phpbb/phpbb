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

namespace phpbb\members\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\event\dispatcher;
use phpbb\exception\http_exception;
use phpbb\group\helper as group_helper;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\members\viewonline_helper;
use phpbb\pagination;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;

class online
{
	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var group_helper */
	protected $group_helper;

	/** @var viewonline_helper */
	protected $viewonline_helper;

	/** @var helper */
	protected $helper;

	/** @var language */
	protected $language;

	/** @var pagination */
	protected $pagination;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ex;

	/**
	 * online constructor.
	 * @param auth $auth
	 * @param config $config
	 * @param dispatcher $dispatcher
	 * @param group_helper $group_helper
	 * @param viewonline_helper $viewonline_helper
	 * @param helper $helper
	 * @param language $language
	 * @param pagination $pagination
	 * @param request $request
	 * @param template $template
	 * @param user $user
	 * @param string $phpbb_root_path
	 * @param string $php_ex
	 */
	public function __construct(auth $auth, config $config, dispatcher $dispatcher, group_helper $group_helper, viewonline_helper $viewonline_helper, helper $helper, language $language, pagination $pagination, request $request, template $template, user $user, string $phpbb_root_path, string $php_ex)
	{
		$this->auth					= $auth;
		$this->config				= $config;
		$this->dispatcher			= $dispatcher;
		$this->group_helper			= $group_helper;
		$this->viewonline_helper	= $viewonline_helper;
		$this->helper				= $helper;
		$this->language				= $language;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->php_ex				= $php_ex;
	}

	/**
	 * Controller for /online route
	 *
	 * @return Response a Symfony response object
	 */
	public function handle(): Response
	{
		// Display a listing of board admins, moderators
		if (!function_exists('display_user_activity'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ex);
		}

		// Load language strings
		$this->language->add_lang('memberlist');

		// Can this user view profiles/memberlist?
		if (!$this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
		{
			if ($this->user->data['user_id'] != ANONYMOUS)
			{
				throw new http_exception(403, 'NO_VIEW_USERS');
			}

			login_box('', $this->language->lang('LOGIN_EXPLAIN_VIEWONLINE'));
		}

		// Get and set some variables
		$mode			= $this->request->variable('mode', '');
		$session_id		= $this->request->variable('s', '');
		$start			= $this->request->variable('start', 0);
		$sort_key		= $this->request->variable('sk', 'b');
		$sort_dir		= $this->request->variable('sd', 'd');
		$show_guests	= ($this->config['load_online_guests']) ? $this->request->variable('sg', 0) : 0;

		$sort_key_text = ['a' => $this->language->lang('SORT_USERNAME'), 'b' => $this->language->lang('SORT_JOINED'), 'c' => $this->language->lang('SORT_LOCATION')];
		$sort_key_sql = ['a' => 'u.username_clean', 'b' => 's.session_time', 'c' => 's.session_page'];

		// Sorting and order
		if (!isset($sort_key_text[$sort_key]))
		{
			$sort_key = 'b';
		}

		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

		$this->user->update_session_infos();

		// Get number of online guests (if we do not display them)
		$guest_counter = (!$show_guests) ? $this->viewonline_helper->get_number_guests() : 0;

		// Get user list (moved into viewonline_helper)
		$session_data_rowset = $this->viewonline_helper->get_session_data_rowset($show_guests, $order_by, $guest_counter);

		$prev_id = $prev_ip = $user_list = [];
		$logged_visible_online = $logged_hidden_online = $counter = 0;

		// Get forum IDs for session pages which have only 't' parameter
		$this->viewonline_helper->get_forum_ids($session_data_rowset);

		foreach ($session_data_rowset as $row)
		{
			if ($row['user_id'] != ANONYMOUS && !isset($prev_id[$row['user_id']]))
			{
				$view_online = $s_user_hidden = false;
				$user_colour = ($row['user_colour']) ? ' style="color:#' . $row['user_colour'] . '" class="username-coloured"' : '';

				$username_full = ($row['user_type'] != USER_IGNORE) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : '<span' . $user_colour . '>' . $row['username'] . '</span>';

				if (!$row['session_viewonline'])
				{
					$view_online = ($this->auth->acl_get('u_viewonline') || $row['user_id'] === $this->user->data['user_id']) ? true : false;
					$logged_hidden_online++;

					$username_full = '<em>' . $username_full . '</em>';
					$s_user_hidden = true;
				}
				else
				{
					$view_online = true;
					$logged_visible_online++;
				}

				$prev_id[$row['user_id']] = 1;

				if ($view_online)
				{
					$counter++;
				}

				if (!$view_online || $counter > $start + $this->config['topics_per_page'] || $counter <= $start)
				{
					continue;
				}
			}
			else if ($show_guests && $row['user_id'] == ANONYMOUS && !isset($prev_ip[$row['session_ip']]))
			{
				$prev_ip[$row['session_ip']] = 1;
				$guest_counter++;
				$counter++;

				if ($counter > $start + $this->config['topics_per_page'] || $counter <= $start)
				{
					continue;
				}

				$s_user_hidden = false;
				$username_full = get_username_string('full', $row['user_id'], $this->language->lang('GUEST'));
			}
			else
			{
				continue;
			}

			list($location, $location_url) = $this->viewonline_helper->get_location($row['session_page'], $row['session_forum_id']);

			$session_page = parse_url($row['session_page'], PHP_URL_PATH);
			$session_page = preg_replace('/^\/index\.php\//', '/', $session_page);
			$on_page = $this->viewonline_helper->get_user_page($session_page);

			$forum_data = $this->viewonline_helper->get_forum_data();

			/**
			* Overwrite the location's name and URL, which are displayed in the list
			*
			* @event core.viewonline_overwrite_location
			* @var	array	on_page			File name and query string
			* @var	array	row				Array with the users sql row
			* @var	string	location		Page name to displayed in the list
			* @var	string	location_url	Page url to displayed in the list
			* @var	array	forum_data		Array with forum data
			* @since 3.1.0-a1
			* @changed 3.1.0-a2 Added var forum_data
			*/
			$vars = ['on_page', 'row', 'location', 'location_url', 'forum_data'];
			extract($this->dispatcher->trigger_event('core.viewonline_overwrite_location', compact($vars)));

			$template_row = [
				'USERNAME' 			=> $row['username'],
				'USERNAME_COLOUR'	=> $row['user_colour'],
				'USERNAME_FULL'		=> $username_full,
				'LASTUPDATE'		=> $this->user->format_date($row['session_time']),
				'FORUM_LOCATION'	=> $location,
				'USER_IP'			=> ($this->auth->acl_get('a_')) ? (($mode == 'lookup' && $session_id == $row['session_id']) ? gethostbyaddr($row['session_ip']) : $row['session_ip']) : '',
				'USER_BROWSER'		=> ($this->auth->acl_get('a_user')) ? $row['session_browser'] : '',

				'U_USER_PROFILE'	=> ($row['user_type'] != USER_IGNORE) ? get_username_string('profile', $row['user_id'], '') : '',
				'U_USER_IP'			=> append_sid($this->phpbb_root_path . "viewonline." . $this->php_ex, 'mode=lookup' . (($mode != 'lookup' || $row['session_id'] != $session_id) ? '&amp;s=' . $row['session_id'] : '') . "&amp;sg=$show_guests&amp;start=$start&amp;sk=$sort_key&amp;sd=$sort_dir"),
				'U_WHOIS'			=> $this->helper->route('phpbb_members_online_whois', ['session_id' => $row['session_id']]),
				'U_FORUM_LOCATION'	=> $location_url,

				'S_USER_HIDDEN'		=> $s_user_hidden,
				'S_GUEST'			=> ($row['user_id'] == ANONYMOUS) ? true : false,
				'S_USER_TYPE'		=> $row['user_type'],
			];

			/**
			* Modify viewonline template data before it is displayed in the list
			*
			* @event core.viewonline_modify_user_row
			* @var	array	on_page			File name and query string
			* @var	array	row				Array with the users sql row
			* @var	array	forum_data		Array with forum data
			* @var	array	template_row	Array with template variables for the user row
			* @since 3.1.0-RC4
			*/
			$vars = ['on_page', 'row', 'forum_data', 'template_row'];
			extract($this->dispatcher->trigger_event('core.viewonline_modify_user_row', compact($vars)));

			$this->template->assign_block_vars('user_row', $template_row);
		}

		$this->group_helper->display_legend();

		// Refreshing the page every 60 seconds...
		meta_refresh(60, $this->helper->route('phpbb_members_online', ['sg' => $show_guests, 'sk' => $sort_key, 'sd' => $sort_dir, 'start' => $start]));

		$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $counter);
		$base_url = $this->helper->route('phpbb_members_online', ['sg' => $show_guests, 'sk' => $sort_key, 'sd' => $sort_dir]);
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $counter, $this->config['topics_per_page'], $start);

		// Send data to template
		$this->template->assign_vars([
			'TOTAL_REGISTERED_USERS_ONLINE'	=> $this->language->lang('REG_USERS_ONLINE', (int) $logged_visible_online, $this->language->lang('HIDDEN_USERS_ONLINE', (int) $logged_hidden_online)),
			'TOTAL_GUEST_USERS_ONLINE'		=> $this->language->lang('GUEST_USERS_ONLINE', (int) $guest_counter),

			'U_SORT_USERNAME'		=> $this->helper->route('phpbb_members_online', ['sg' => (int) $show_guests, 'sk' => 'a', 'sd' => (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a')]),
			'U_SORT_UPDATED'		=> $this->helper->route('phpbb_members_online', ['sg' => (int) $show_guests, 'sk' => 'b', 'sd' => (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a')]),
			'U_SORT_LOCATION'		=> $this->helper->route('phpbb_members_online', ['sg' => (int) $show_guests, 'sk' => 'c', 'sd' => (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a')]),

			'U_SWITCH_GUEST_DISPLAY'	=> $this->helper->route('phpbb_members_online', ['sg' => (int) !$show_guests]),
			'L_SWITCH_GUEST_DISPLAY'	=> ($show_guests) ? $this->language->lang('HIDE_GUESTS') : $this->language->lang('DISPLAY_GUESTS'),
			'S_SWITCH_GUEST_DISPLAY'	=> ($this->config['load_online_guests']) ? true : false,
			'S_VIEWONLINE'				=> true,
		]);

		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME'	=> $this->language->lang('WHO_IS_ONLINE'),
			'U_BREADCRUMB'		=> $this->helper->route('phpbb_members_online'),
		]);

		make_jumpbox(append_sid($this->phpbb_root_path . "viewforum." . $this->php_ex));

		// Render
		return $this->helper->render('viewonline_body.html', $this->language->lang('WHO_IS_ONLINE'));
	}

}
