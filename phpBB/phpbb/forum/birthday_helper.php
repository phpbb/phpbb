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

namespace phpbb\forum;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;

class birthday_helper
{
	protected $auth;
	protected $config;
	protected $db;
	protected $dispatcher;
	protected $language;
	protected $template;
	protected $user;

	public function __construct(auth $auth, config $config, driver_interface $db, dispatcher $dispatcher, language $language, template $template, user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
	}

	public function display_birthdays()
	{
		$show_birthdays = ($this->config['load_birthdays'] && $this->config['allow_birthdays'] && $this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'));

		$birthdays = $birthday_list = array();
		if ($show_birthdays)
		{
			$time = $this->user->create_datetime();
			$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

			// Display birthdays of 29th february on 28th february in non-leap-years
			$leap_year_birthdays = '';
			if ($now['mday'] == 28 && $now['mon'] == 2 && !$time->format('L'))
			{
				$leap_year_birthdays = " OR u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', 29, 2)) . "%'";
			}

			$sql_ary = array(
				'SELECT' => 'u.user_id, u.username, u.user_colour, u.user_birthday',
				'FROM' => array(
					USERS_TABLE => 'u',
				),
				'LEFT_JOIN' => array(
					array(
						'FROM' => array(BANS_TABLE => 'b'),
						'ON' => 'u.user_id = b.ban_userid',
					),
				),
				'WHERE' => 'b.ban_id IS NULL
			AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ")
			AND (u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%' $leap_year_birthdays)",
			);

			/**
			 * Event to modify the SQL query to get birthdays data
			 *
			 * @event core.index_modify_birthdays_sql
			 * @var	array	now			The assoc array with the 'now' local timestamp data
			 * @var	array	sql_ary		The SQL array to get the birthdays data
			 * @var	object	time		The user related Datetime object
			 * @since 3.1.7-RC1
			 */
			$vars = array('now', 'sql_ary', 'time');
			extract($this->dispatcher->trigger_event('core.index_modify_birthdays_sql', compact($vars)));

			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			$rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			foreach ($rows as $row)
			{
				$birthday_username	= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
				$birthday_year		= (int) substr($row['user_birthday'], -4);
				$birthday_age		= ($birthday_year) ? max(0, $now['year'] - $birthday_year) : '';

				$birthdays[] = array(
					'USERNAME'	=> $birthday_username,
					'AGE'		=> $birthday_age,
				);

				// For 3.0 compatibility
				$birthday_list[] = $birthday_username . (($birthday_age) ? " ({$birthday_age})" : '');
			}

			/**
			 * Event to modify the birthdays list
			 *
			 * @event core.index_modify_birthdays_list
			 * @var	array	birthdays		Array with the users birthdays data
			 * @var	array	rows			Array with the birthdays SQL query result
			 * @since 3.1.7-RC1
			 */
			$vars = array('birthdays', 'rows');
			extract($this->dispatcher->trigger_event('core.index_modify_birthdays_list', compact($vars)));

			$this->template->assign_block_vars_array('birthdays', $birthdays);
		}

		$this->template->assign_vars([
			'BIRTHDAY_LIST'		=> (empty($birthday_list)) ? '' : implode($this->language->lang('COMMA_SEPARATOR'), $birthday_list),
			'S_DISPLAY_BIRTHDAY_LIST' => $show_birthdays,
		]);
	}
}
