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
	/** @var int */
	protected $cache_ttl = 300;

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
			'SELECT'	=> 'g.group_id',
			'FROM'		=> [
				GROUPS_TABLE => 'g',
			],
			'ORDER_BY'	=> 'g.group_name',
		]);
	}
}
