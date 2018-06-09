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

class group extends base_group
{
	/**
	 * {@inheritdoc}
	 */
	protected function query($keyword, $topic_id)
	{
		$query = $this->db->sql_build_query('SELECT', [
			'SELECT'    => 'g.group_id',
			'FROM'      => [
				GROUPS_TABLE => 'g',
			],
		]);
		return $query;
	}
}
