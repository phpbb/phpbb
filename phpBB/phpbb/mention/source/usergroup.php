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

class usergroup extends group
{
	/** @var  \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\group\helper $helper, \phpbb\user $user, $phpbb_root_path, $phpEx)
	{
		$this->user = $user;

		parent::__construct($db, $helper, $phpbb_root_path, $phpEx);
	}

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
			'LEFT_JOIN' => [
				[
					'FROM' => [USER_GROUP_TABLE => 'ug'],
					'ON'   => 'g.group_id = ug.group_id'
				]
			],
			'WHERE'     => 'ug.user_id = ' . (int) $this->user->data['user_id'],
		]);
		return $query;
	}
}
