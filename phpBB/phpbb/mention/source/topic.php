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

class topic extends base_user
{
	/**
	 * {@inheritdoc}
	 */
	protected function query($keyword, $topic_id)
	{
		/*
		 * For optimization purposes all users are returned regardless of the keyword
		 * Names filtering is done on the frontend
		 * Results will be cached on a per-topic basis
		 */
		$query = $this->db->sql_build_query('SELECT', [
			'SELECT'    => 'u.username, u.user_id',
			'FROM'      => [
				USERS_TABLE => 'u',
			],
			'LEFT_JOIN' => [
				[
					'FROM' => [POSTS_TABLE => 'p'],
					'ON'   => 'u.user_id = p.poster_id'
				]
			],
			'WHERE'     => 'p.topic_id = ' . $topic_id . '
				AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]),
			'ORDER_BY'  => 'p.post_time DESC'
		]);
		return $query;
	}
}
