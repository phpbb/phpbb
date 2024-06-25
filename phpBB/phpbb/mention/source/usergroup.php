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

class usergroup extends base_group
{
	/**
	 * {@inheritdoc}
	 */
	protected function query(string $keyword, int $topic_id): string
	{
		return $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'g.group_id',
			'FROM'	=> [
				GROUPS_TABLE => 'g',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [USER_GROUP_TABLE => 'ug'],
					'ON'	=> 'g.group_id = ug.group_id'
				]
			],
			'WHERE'		=> 'ug.user_pending = 0 AND ug.user_id = ' . (int) $this->user->data['user_id'],
			'ORDER_BY'	=> 'g.group_name',
		]);
	}
}
