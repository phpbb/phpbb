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

class user extends base_user
{
	/**
	 * {@inheritdoc}
	 */
	protected function query($keyword, $topic_id)
	{
		// TODO: think about caching ALL users: 1m users results to ~40MB file
		$query = $this->db->sql_build_query('SELECT', [
			'SELECT'    => 'u.username_clean, u.username, u.user_id',
			'FROM'      => [
				USERS_TABLE => 'u',
			],
			'WHERE'     => $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER])/* . '
				AND u.username_clean ' . $this->db->sql_like_expression($keyword . $this->db->get_any_char())*/,
			'ORDER_BY'  => 'u.user_lastvisit DESC'
		]);
		return $query;
	}
}
