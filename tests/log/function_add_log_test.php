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

class phpbb_log_function_add_log_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/empty_log.xml');
	}

	public static function add_log_function_data()
	{
		return array(
			/**
			* Case documentation
			array(
				// Row that is in the database afterwards
				array(
					'user_id'		=> ANONYMOUS,
					'log_type'		=> LOG_MOD,
					'log_operation'	=> 'LOG_MOD_ADDITIONAL',
					// log_data will be serialized
					'log_data'		=> array(
						'argument3',
					),
					'reportee_id'	=> 0,
					'forum_id'		=> 56,
					'topic_id'		=> 78,
				),
				// user_id		Can also be false, then ANONYMOUS is used
				false,
				// log_mode		Used to determine the log_type
				'mod',
				// Followed by some additional arguments
				// forum_id, topic_id and reportee_id are specified before log_operation
				// The rest is specified afterwards.
				56,
				78,
				'LOG_MOD_ADDITIONAL', // log_operation
				'argument3',
			),
			*/
			array(
				array(
					'user_id'		=> 2,
					'log_type'		=> LOG_CRITICAL,
					'log_operation'	=> 'LOG_NO_ADDITIONAL',
					'log_data'		=> '',
					'reportee_id'	=> 0,
					'forum_id'		=> 0,
					'topic_id'		=> 0,
				),
				2, 'critical', 'LOG_NO_ADDITIONAL',
			),
			array(
				array(
					'user_id'		=> 2,
					'log_type'		=> LOG_CRITICAL,
					'log_operation'	=> 'LOG_ONE_ADDITIONAL',
					'log_data'		=> array(
						'argument1',
					),
					'reportee_id'	=> 0,
					'forum_id'		=> 0,
					'topic_id'		=> 0,
				),
				2, 'critical', 'LOG_ONE_ADDITIONAL', 'argument1',
			),
			array(
				array(
					'user_id'		=> ANONYMOUS,
					'log_type'		=> LOG_ADMIN,
					'log_operation'	=> 'LOG_TWO_ADDITIONAL',
					'log_data'		=> array(
						'argument1',
						'argument2',
					),
					'reportee_id'	=> 0,
					'forum_id'		=> 0,
					'topic_id'		=> 0,
				),
				false, 'admin', 'LOG_TWO_ADDITIONAL', 'argument1', 'argument2',
			),
			array(
				array(
					'user_id'		=> ANONYMOUS,
					'log_type'		=> LOG_USERS,
					'log_operation'	=> 'LOG_USERS_ADDITIONAL',
					'log_data'		=> array(
						'argument2',
					),
					'reportee_id'	=> 2,
					'forum_id'		=> 0,
					'topic_id'		=> 0,
				),
				false, 'user', 2, 'LOG_USERS_ADDITIONAL', 'argument2',
			),
			array(
				array(
					'user_id'		=> ANONYMOUS,
					'log_type'		=> LOG_MOD,
					'log_operation'	=> 'LOG_MOD_TOPIC_AND_FORUM',
					'log_data'		=> '',
					'reportee_id'	=> 0,
					'forum_id'		=> 12,
					'topic_id'		=> 34,
				),
				false, 'mod', 12, 34, 'LOG_MOD_TOPIC_AND_FORUM',
			),
			array(
				array(
					'user_id'		=> ANONYMOUS,
					'log_type'		=> LOG_MOD,
					'log_operation'	=> 'LOG_MOD_ADDITIONAL',
					'log_data'		=> array(
						'argument3',
					),
					'reportee_id'	=> 0,
					'forum_id'		=> 56,
					'topic_id'		=> 78,
				),
				false, 'mod', 56, 78, 'LOG_MOD_ADDITIONAL', 'argument3',
			),
			array(
				array(
				),
				false, 'mode_does_not_exist', 'LOG_MOD_ADDITIONAL', 'argument1',
			),
		);
	}

	/**
	* @dataProvider add_log_function_data
	*/
	public function test_add_log_function($expected, $user_id, $mode, $required1, $additional1 = null, $additional2 = null, $additional3 = null)
	{
		global $db, $cache, $user, $phpbb_log, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		if ($expected)
		{
			// Serialize the log data if we have some
			if (is_array($expected['log_data']))
			{
				$expected['log_data'] = serialize($expected['log_data']);
			}
			$expected = array($expected);
		}

		$db = $this->new_dbal();
		$cache = new phpbb_mock_cache;
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = $this->createMock('\phpbb\user');
		$auth = $this->createMock('\phpbb\auth\auth');

		$phpbb_log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		$user->ip = 'user_ip';
		if ($user_id)
		{
			$user->data['user_id'] = $user_id;
		}

		if ($additional3 != null)
		{
			$additional_data = [
				'forum_id'	=> $required1,
				'topic_id'	=> $additional1,
				$additional3,
			];
			$phpbb_log->add($mode, $user_id, '', $additional2, false, $additional_data);
		}
		else if ($additional2 != null)
		{
			if ($mode == 'user')
			{
				$additional_data = [
					'reportee_id'	=> $required1,
					$additional2,
				];
				$log_operation = $additional1;
			}
			else if ($mode == 'mod')
			{
				$additional_data = [
					'forum_id'	=> $required1,
					'topic_id'	=> $additional1,
				];
				$log_operation = $additional2;
			}
			else
			{
				$log_operation = $required1;
				$additional_data = [
					$additional1,
					$additional2,
				];
			}
			$phpbb_log->add($mode, $user_id, '', $log_operation, false, $additional_data);
		}
		else if ($additional1 != null)
		{
			if ($mode == 'user')
			{
				$additional_data = [
					'reportee_id'	=> $required1,
				];
				$log_operation = $additional1;
			}
			else
			{
				$log_operation = $required1;
				$additional_data = [
					$additional1,
				];
			}

			$phpbb_log->add($mode, $user_id, '', $log_operation, false, $additional_data);
		}
		else
		{
			$phpbb_log->add($mode, $user_id, '', $required1);
		}

		$result = $db->sql_query('SELECT user_id, log_type, log_operation, log_data, reportee_id, forum_id, topic_id
			FROM ' . LOG_TABLE);

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}
