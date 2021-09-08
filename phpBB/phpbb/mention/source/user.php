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
	public function get_priority(array $row): int
	{
		/*
		 * Presence in array with all names for this type should not increase the priority
		 * Otherwise names will not be properly sorted because we fetch them in batches
		 * and the name from 'special' source can be absent from the array with all names
		 * and therefore it will appear lower than needed
		 */
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function query(string $keyword, int $topic_id): string
	{
		return $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'u.username_clean, u.user_id',
			'FROM'		=> [
				USERS_TABLE => 'u',
			],
			'WHERE'		=> $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) . '
				AND u.username_clean ' . $this->db->sql_like_expression($keyword . $this->db->get_any_char()),
			'ORDER_BY'	=> 'u.user_lastvisit DESC'
		]);
	}
}
