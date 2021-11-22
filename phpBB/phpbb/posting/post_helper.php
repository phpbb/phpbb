<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\posting;

use phpbb\db\driver\driver_interface;

class post_helper
{
	/**
	 * @var driver_interface
	 */
	protected $db;

	public function __construct(driver_interface $db)
	{
		$this->db = $db;
	}

	/**
	 * Get last post id
	 */
	public function get_max_post_id(): int
	{
		$sql = 'SELECT MAX(post_id) as max_post_id
			FROM '. POSTS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_post_id = (int) $this->db->sql_fetchfield('max_post_id');
		$this->db->sql_freeresult($result);

		return $max_post_id;
	}
}
