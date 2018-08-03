<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\mention\source;

class team extends base_user
{
	/**
	 * {@inheritdoc}
	 */
	protected function query($keyword, $topic_id)
	{
		/*
		 * Select unique names of team members: each name should be selected only once
		 * regardless of the number of groups the certain user is a member of
		 *
		 * For optimization purposes all team members are returned regardless of the keyword
		 * Names filtering is done on the frontend
		 * Results will be cached in a single file
		 */
		$query = $this->db->sql_build_query('SELECT_DISTINCT', [
			'SELECT'    => 'u.username_clean, u.username, u.user_id',
			'FROM'      => [
				USERS_TABLE => 'u',
				USER_GROUP_TABLE => 'ug',
				TEAMPAGE_TABLE => 't',
			],
			'WHERE'     => 'ug.group_id = t.group_id AND ug.user_id = u.user_id AND ug.user_pending = 0
				AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]),
			'ORDER_BY'  => 'u.username'
		]);
		return $query;
	}
}
