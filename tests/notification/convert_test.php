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
require_once dirname(__FILE__) . '/../mock/sql_insert_buffer.php';

class phpbb_notification_convert_test extends phpbb_database_test_case
{
	protected $notifications, $db, $container, $user, $config, $auth, $cache;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/convert.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$factory = new \phpbb\db\tools\factory();

		$this->migration = new \phpbb\db\migration\data\v310\notification_options_reconvert(
			new \phpbb\config\config(array()),
			$this->db,
			$factory->get($this->db),
			$phpbb_root_path,
			$phpEx,
			'phpbb_'
		);
	}

	public function test_convert()
	{
		$buffer = new phpbb_mock_sql_insert_buffer($this->db, 'phpbb_user_notifications');
		$this->migration->perform_conversion($buffer, 0);

		$expected = array_merge(
			$this->create_expected('post', 1, 'email'),
			$this->create_expected('topic', 1, 'email'),

			$this->create_expected('post', 2, 'email'),
			$this->create_expected('topic', 2, 'email'),
			$this->create_expected('pm', 2, 'email'),

			$this->create_expected('post', 3, 'jabber'),
			$this->create_expected('topic', 3, 'jabber'),

			$this->create_expected('post', 4, 'jabber'),
			$this->create_expected('topic', 4, 'jabber'),
			$this->create_expected('pm', 4, 'jabber'),

			$this->create_expected('post', 5, 'both'),
			$this->create_expected('topic', 5, 'both'),

			$this->create_expected('post', 6, 'both'),
			$this->create_expected('topic', 6, 'both'),
			$this->create_expected('pm', 6, 'both')
		);

		$this->assertEquals($expected, $buffer->get_buffer());
	}

	protected function create_expected($type, $user_id, $method = '')
	{
		$return = array();

		if ($method !== '')
		{
			$return[] = array(
				'item_type'		=> $type,
				'item_id'		=> 0,
				'user_id'		=> $user_id,
				'method'		=> '',
				'notify'		=> 1,
			);
		}

		if ($method === 'email' || $method === 'both')
		{
			$return[] = array(
				'item_type'		=> $type,
				'item_id'		=> 0,
				'user_id'		=> $user_id,
				'method'		=> 'email',
				'notify'		=> 1,
			);
		}

		if ($method === 'jabber' || $method === 'both')
		{
			$return[] = array(
				'item_type'		=> $type,
				'item_id'		=> 0,
				'user_id'		=> $user_id,
				'method'		=> 'jabber',
				'notify'		=> 1,
			);
		}

		return $return;
	}
}
