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
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\exception\http_exception;
use phpbb\group\helper as group_helper;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;

class team
{
	/**
	 * @var auth
	 */
	protected $auth;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var group_helper
	 */
	protected $group_helper;

	/**
	 * @var helper
	 */
	protected $helper;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * @var user
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var string
	 */
	protected $forums_table;

	/**
	 * @var string
	 */
	protected $groups_table;

	/**
	 * @var string
	 */
	protected $teampage_table;

	/**
	 * @var string
	 */
	protected $user_group_table;

	/**
	 * @var string
	 */
	protected $users_table;


	/**
	 * Constructor
	 *
	 * @param auth             $auth           Authentication service
	 * @param config           $config         Configuration
	 * @param driver_interface $db             Database driver
	 * @param dispatcher       $dispatcher     Event dispatcher
	 * @param group_helper     $group_helper   Group helper
	 * @param helper           $helper         Controller helper
	 * @param language         $language       Language service
	 * @param template         $template       Template service
	 * @param user             $user           User object
	 * @param string           $phpbb_root_path Path to phpBB root
	 * @param string           $phpEx          PHP file extension
	 * @param string           $forums_table   Table name for forums
	 * @param string           $groups_table   Table name for groups
	 * @param string           $teampage_table Table name for teampage
	 * @param string           $user_group_table Table name for user_group
	 * @param string           $users_table    Table name for users
	 */
	public function __construct(auth $auth, config $config, driver_interface $db, dispatcher $dispatcher, group_helper $group_helper, helper $helper, language $language, template $template, user $user, string $phpbb_root_path, string $phpEx, string $forums_table, string $groups_table, string $teampage_table, string $user_group_table, string $users_table)
	{
		$this->auth				= $auth;
		$this->config			= $config;
		$this->db				= $db;
		$this->dispatcher		= $dispatcher;
		$this->group_helper		= $group_helper;
		$this->helper			= $helper;
		$this->language			= $language;
		$this->template			= $template;
		$this->user				= $user;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $phpEx;
		$this->forums_table	= $forums_table;
		$this->groups_table	= $groups_table;
		$this->teampage_table	= $teampage_table;
		$this->user_group_table	= $user_group_table;
		$this->users_table	= $users_table;
	}

	/**
	 * Controller for /team route
	 *
	 * @return Response a Symfony response object
	 */
	public function handle() : Response
	{
		// Display a listing of board admins, moderators
		if (!function_exists('user_get_id_name'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}
		if (!function_exists('phpbb_get_user_rank'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
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

			login_box('', $this->language->lang('LOGIN_EXPLAIN_TEAM'));
		}

		$sql = 'SELECT *
			FROM ' . $this->teampage_table . '
			ORDER BY teampage_position ASC';
		$result = $this->db->sql_query($sql, 3600);
		$teampage_data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$sql_ary = [
			'SELECT'	=> 'g.group_id, g.group_name, g.group_colour, g.group_type, ug.user_id as ug_user_id, t.teampage_id',

			'FROM'		=> [$this->groups_table => 'g'],

			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [$this->teampage_table => 't'],
					'ON'	=> 't.group_id = g.group_id',
				],
				[
					'FROM'	=> [$this->user_group_table => 'ug'],
					'ON'	=> 'ug.group_id = g.group_id AND ug.user_pending = 0 AND ug.user_id = ' . (int) $this->user->data['user_id'],
				],
			],
		];

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_ary));

		$group_ids = $groups_ary = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['group_type'] == GROUP_HIDDEN && !$this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel') && $row['ug_user_id'] != $this->user->data['user_id'])
			{
				$row['group_name'] = $this->language->lang('GROUP_UNDISCLOSED');
				$row['u_group'] = '';
			}
			else
			{
				$row['group_name'] = $this->group_helper->get_name($row['group_name']);
				$row['u_group'] = append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=group&amp;g=' . $row['group_id']);
			}

			if ($row['teampage_id'])
			{
				// Only put groups into the array we want to display.
				// We are fetching all groups, to ensure we got all data for default groups.
				$group_ids[] = (int) $row['group_id'];
			}
			$groups_ary[(int) $row['group_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		$sql_ary = [
			'SELECT'	=> 'u.user_id, u.group_id as default_group, u.username, u.username_clean, u.user_colour, u.user_type, u.user_rank, u.user_posts, u.user_allow_pm, g.group_id',

			'FROM'		=> [
				$this->user_group_table => 'ug',
			],

			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [$this->users_table => 'u'],
					'ON'	=> 'ug.user_id = u.user_id',
				],
				[
					'FROM'	=> [$this->groups_table => 'g'],
					'ON'	=> 'ug.group_id = g.group_id',
				],
			],

			'WHERE'		=> $this->db->sql_in_set('g.group_id', $group_ids, false, true) . ' AND ug.user_pending = 0',

			'ORDER_BY'	=> 'u.username_clean ASC',
		];

		/**
		 * Modify the query used to get the users for the team page
		 *
		 * @event core.memberlist_team_modify_query
		 * @var array	sql_ary			Array containing the query
		 * @var array	group_ids		Array of group ids
		 * @var array	teampage_data	The teampage data
		 * @since 3.1.3-RC1
		 */
		$vars = ['sql_ary', 'group_ids', 'teampage_data'];
		extract($this->dispatcher->trigger_event('core.memberlist_team_modify_query', compact($vars)));

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_ary));

		$user_ary = $user_ids = $group_users = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['forums'] = '';
			$row['forums_ary'] = [];
			$user_ary[(int) $row['user_id']] = $row;
			$user_ids[] = (int) $row['user_id'];
			$group_users[(int) $row['group_id']][] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$user_ids = array_unique($user_ids);

		if (!empty($user_ids) && $this->config['teampage_forums'])
		{
			$this->template->assign_var('S_DISPLAY_MODERATOR_FORUMS', true);
			// Get all moderators
			$perm_ary = $this->auth->acl_get_list($user_ids, ['m_'], false);

			foreach ($perm_ary as $forum_id => $forum_ary)
			{
				foreach ($forum_ary as $id_ary)
				{
					foreach ($id_ary as $id)
					{
						if (!$forum_id)
						{
							$user_ary[$id]['forums'] = $this->language->lang('ALL_FORUMS');
						}
						else
						{
							$user_ary[$id]['forums_ary'][] = $forum_id;
						}
					}
				}
			}

			$sql = 'SELECT forum_id, forum_name
				FROM ' . $this->forums_table;
			$result = $this->db->sql_query($sql);

			$forums = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$forums[$row['forum_id']] = $row['forum_name'];
			}
			$this->db->sql_freeresult($result);

			foreach ($user_ary as $user_id => $user_data)
			{
				if (!$user_data['forums'])
				{
					foreach ($user_data['forums_ary'] as $forum_id)
					{
						$user_ary[$user_id]['forums_options'] = true;
						if (isset($forums[$forum_id]))
						{
							if ($this->auth->acl_get('f_list', $forum_id))
							{
								$user_ary[$user_id]['forums'] .= '<option value="">' . $forums[$forum_id] . '</option>';
							}
						}
					}
				}
			}
		}

		foreach ($teampage_data as $team_data)
		{
			// If this team entry has no group, it's a category
			if (!$team_data['group_id'])
			{
				$this->template->assign_block_vars('group', [
					'GROUP_NAME'  => $team_data['teampage_name'],
				]);

				continue;
			}

			$group_data = $groups_ary[(int) $team_data['group_id']];
			$group_id = (int) $team_data['group_id'];

			if (!$team_data['teampage_parent'])
			{
				// If the group does not have a parent category, we display the groupname as category
				$this->template->assign_block_vars('group', [
					'GROUP_NAME'	=> $group_data['group_name'],
					'GROUP_COLOR'	=> $group_data['group_colour'],
					'U_GROUP'		=> $group_data['u_group'],
				]);
			}

			// Display group members.
			if (!empty($group_users[$group_id]))
			{
				foreach ($group_users[$group_id] as $user_id)
				{
					if (isset($user_ary[$user_id]))
					{
						$row = $user_ary[$user_id];
						if ($this->config['teampage_memberships'] == 1 && ($group_id != $groups_ary[$row['default_group']]['group_id']) && $groups_ary[$row['default_group']]['teampage_id'])
						{
							// Display users in their primary group, instead of the first group, when it is displayed on the teampage.
							continue;
						}

						$user_rank_data = phpbb_get_user_rank($row, (($row['user_id'] == ANONYMOUS) ? false : $row['user_posts']));

						$template_vars = [
							'USER_ID'		=> $row['user_id'],
							'FORUMS'		=> $row['forums'],
							'FORUM_OPTIONS'	=> (isset($row['forums_options'])) ? true : false,
							'RANK_TITLE'	=> $user_rank_data['title'],

							'GROUP_NAME'	=> $groups_ary[$row['default_group']]['group_name'],
							'GROUP_COLOR'	=> $groups_ary[$row['default_group']]['group_colour'],
							'U_GROUP'		=> $groups_ary[$row['default_group']]['u_group'],

							'RANK_IMG'		=> $user_rank_data['img'],
							'RANK_IMG_SRC'	=> $user_rank_data['img_src'],

							'S_INACTIVE'	=> $row['user_type'] == USER_INACTIVE,

							'U_PM'			=> ($this->config['allow_privmsg'] && $this->auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_'))) ? append_sid("{$this->phpbb_root_path}ucp.{$this->php_ext}", 'i=pm&amp;mode=compose&amp;u=' . $row['user_id']) : '',

							'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
							'USERNAME'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
							'USER_COLOR'		=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
							'U_VIEW_PROFILE'	=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),
						];

						/**
						 * Modify the template vars for displaying the user in the groups on the teampage
						 *
						 * @event core.memberlist_team_modify_template_vars
						 * @var array	template_vars		Array containing the query
						 * @var array	row					Array containing the action user row
						 * @var array	groups_ary			Array of groups with all users that should be displayed
						 * @since 3.1.3-RC1
						 */
						$vars = ['template_vars', 'row', 'groups_ary'];
						extract($this->dispatcher->trigger_event('core.memberlist_team_modify_template_vars', compact($vars)));

						$this->template->assign_block_vars('group.user', $template_vars);

						if ($this->config['teampage_memberships'] != 2)
						{
							unset($user_ary[$user_id]);
						}
					}
				}
			}
		}

		$this->template->assign_vars([
			'PM_IMG' => $this->user->img('icon_contact_pm', $this->language->lang('SEND_PRIVATE_MESSAGE')),
		]);

		// Breadcrumbs
		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME' => $this->language->lang('THE_TEAM'),
			'U_BREADCRUMB' => $this->helper->route('phpbb_members_team'),
		]);

		make_jumpbox(append_sid("{$this->phpbb_root_path}viewforum.{$this->php_ext}"));

		// Render
		return $this->helper->render('memberlist_team.html', $this->language->lang('THE_TEAM'));
	}

}
