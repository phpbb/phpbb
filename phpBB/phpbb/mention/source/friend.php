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

class friend extends base_user
{
	/** @var  \phpbb\user */
	protected $user;

	/**
	 * Set the user service used to retrieve current user ID
	 *
	 * @param \phpbb\user $user
	 */
	public function set_user(\phpbb\user $user): void
	{
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function query(string $keyword, int $topic_id): string
	{
		/*
		 * For optimization purposes all friends are returned regardless of the keyword
		 * Names filtering is done on the frontend
		 * Results will be cached on a per-user basis
		 */
		return $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'u.username_clean, u.user_id',
			'FROM'		=> [
				USERS_TABLE => 'u',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [ZEBRA_TABLE => 'z'],
					'ON'	=> 'u.user_id = z.zebra_id'
				]
			],
			'WHERE'		=> 'z.friend = 1 AND z.user_id = ' . (int) $this->user->data['user_id'] . '
				AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) . '
				AND u.username_clean ' . $this->db->sql_like_expression($keyword . $this->db->get_any_char()),
			'ORDER_BY'	=> 'u.user_lastvisit DESC'
		]);
	}
}
