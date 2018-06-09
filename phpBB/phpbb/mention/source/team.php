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
		$query = $this->db->sql_build_query('SELECT', [
			'SELECT'    => 'u.username, u.user_id',
			'FROM'      => [
				USERS_TABLE => 'u',
				USER_GROUP_TABLE => 'ug',
				TEAMPAGE_TABLE => 't',
			],
			'WHERE'     => 'ug.group_id = t.group_id AND ug.user_id = u.user_id AND ug.user_pending = 0
				AND u.user_id <> ' . ANONYMOUS . '
				AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) . '
				AND u.username_clean ' . $this->db->sql_like_expression($keyword . $this->db->get_any_char()),
			'ORDER_BY'  => 'u.user_lastvisit DESC'
		]);
		return $query;
	}
}
