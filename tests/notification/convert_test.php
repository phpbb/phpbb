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
require_once __DIR__ . '/../mock/sql_insert_buffer.php';

class phpbb_notification_convert_test extends phpbb_database_test_case
{
	protected $db;
	protected $doctrine_db;
	protected $migration;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/convert.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$this->doctrine_db = $this->new_doctrine_dbal();
		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($this->doctrine_db);
		$core_tables = self::get_core_tables();

		// Add user_notify_type column for testing this migration and set type
		$db_tools->sql_column_add($core_tables['users'], 'user_notify_type', ['TINT:4', 0]);
		$user_notify_type_map = [
			1 => 0,
			2 => 0,
			3 => 1,
			4 => 1,
			5 => 2,
			6 => 2,
		];

		foreach ($user_notify_type_map as $user_id => $notify_type)
		{
			$this->db->sql_query('UPDATE ' . $core_tables['users'] . ' SET user_notify_type = ' . (int) $notify_type . ' WHERE user_id = ' . (int) $user_id);
		}

		$this->migration = new \phpbb\db\migration\data\v310\notification_options_reconvert(
			new \phpbb\config\config(array()),
			$this->db,
			$db_tools,
			$phpbb_root_path,
			$phpEx,
			'phpbb_',
			$core_tables
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
