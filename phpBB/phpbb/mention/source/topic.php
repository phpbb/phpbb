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
	public function get_priority(array $row): int
	{
		/*
		 * Topic's open poster is probably the most mentionable user in the topic
		 * so we give him a significant priority
		 */
		return $row['user_id'] === $row['topic_poster'] ? 5 : 1;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function query(string $keyword, int $topic_id): string
	{
		/*
		 * Select poster's username together with topic author's ID
		 * that will be later used for prioritisation
		 *
		 * For optimization purposes all users are returned regardless of the keyword
		 * Names filtering is done on the frontend
		 * Results will be cached on a per-topic basis
		 */
		return $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'u.username_clean, u.user_id, t.topic_poster',
			'FROM'		=> [
				USERS_TABLE => 'u',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [POSTS_TABLE => 'p'],
					'ON'	=> 'u.user_id = p.poster_id'
				],
				[
					'FROM'	=> [TOPICS_TABLE => 't'],
					'ON'	=> 't.topic_id = p.topic_id'
				],
			],
			'WHERE'		=> 'p.topic_id = ' . (int) $topic_id . '
				AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) . '
				AND u.username_clean ' . $this->db->sql_like_expression($keyword . $this->db->get_any_char()),
			'ORDER_BY'	=> 'p.post_time DESC'
		]);
	}
}
