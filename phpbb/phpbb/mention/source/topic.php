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

namespace phpbb\mention\method;

class topic
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var  \phpbb\request\request_interface */
	protected $request;

	/**
	* Constructor
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request)
	{
		$this->db = $db;
		$this->request = $request;
	}

	public function get($keyword, $topic_id)
	{
		$query = $this->db->sql_build_query('SELECT', [
			'SELECT' => 'u.username, u.user_id',
			'FROM' => [
				USERS_TABLE => 'u',
			],
			'LEFT_JOIN' => [
				'FROM' => [POSTS_TABLE => 'p'],
				'ON' => 'u.user_id = p.poster_id'
			],
			'WHERE' => 'p.topic_id = ' . $topic_id . ' AND u.user_id <> ' . ANONYMOUS . '
				AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]) .  '
				AND u.username_clean ' . $this->db->sql_like_expression($keyword . $this->db->get_any_char()),
			'ORDER_BY' => 'p.post_time DESC'
		]);
		$res = $this->db->sql_query_limit($query, 5);

		return $this->db->sql_fetchrowset($res);
	}
}
