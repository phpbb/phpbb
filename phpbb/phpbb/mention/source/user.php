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

abstract class user
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db)
	{
		$this->db = $db;
	}

	abstract protected function query($keyword, $topic_id);

	public function get($keyword, $topic_id)
	{
		$keyword = utf8_clean_string($keyword);
		$res = $this->db->sql_query_limit($this->query($keyword, $topic_id), 5);

		$names = [];
		while ($row = $this->db->sql_fetchrow($res))
		{
			$names['u' . $row['user_id']] = $row['username'];
		}

		return $names;
	}
}
