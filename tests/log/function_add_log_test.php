<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_log_function_add_log_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/empty_log.xml');
	}

	public static function test_add_log_function_critical_data()
	{
		return array(
			array(
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
				),
				2, 'critical', 'LOG_NO_ADDITIONAL',
			),
			array(
				array(
					array(
						'user_id'		=> 2,
						'log_type'		=> LOG_CRITICAL,
						'log_operation'	=> 'LOG_ONE_ADDITIONAL',
						'log_data'		=> 'a:1:{i:0;s:9:"argument1";}',
						'reportee_id'	=> 0,
						'forum_id'		=> 0,
						'topic_id'		=> 0,
					),
				),
				2, 'critical', 'LOG_ONE_ADDITIONAL', 'argument1',
			),
			array(
				array(
					array(
						'user_id'		=> ANONYMOUS,
						'log_type'		=> LOG_ADMIN,
						'log_operation'	=> 'LOG_TWO_ADDITIONAL',
						'log_data'		=> 'a:2:{i:0;s:9:"argument1";i:1;s:9:"argument2";}',
						'reportee_id'	=> 0,
						'forum_id'		=> 0,
						'topic_id'		=> 0,
					),
				),
				false, 'admin', 'LOG_TWO_ADDITIONAL', 'argument1', 'argument2',
			),
			array(
				array(
					array(
						'user_id'		=> ANONYMOUS,
						'log_type'		=> LOG_USERS,
						'log_operation'	=> 'LOG_USERS_ADDITIONAL',
						'log_data'		=> 'a:1:{i:0;s:9:"argument2";}',
						'reportee_id'	=> 2,
						'forum_id'		=> 0,
						'topic_id'		=> 0,
					),
				),
				false, 'user', 2, 'LOG_USERS_ADDITIONAL', 'argument2',
			),
			array(
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
				),
				false, 'mod', 12, 34, 'LOG_MOD_TOPIC_AND_FORUM',
			),
			array(
				array(
					array(
						'user_id'		=> ANONYMOUS,
						'log_type'		=> LOG_MOD,
						'log_operation'	=> 'LOG_MOD_ADDITIONAL',
						'log_data'		=> 'a:1:{i:0;s:9:"argument3";}',
						'reportee_id'	=> 0,
						'forum_id'		=> 56,
						'topic_id'		=> 78,
					),
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
	* @dataProvider test_add_log_function_critical_data
	*/
	public function test_add_log_function_critical($expected, $user_id, $mode, $required1, $additional1 = null, $additional2 = null, $additional3 = null)
	{
		global $db, $user;

		$db = $this->new_dbal();

		$user->ip = 'user_ip';
		if ($user_id)
		{
			$user->data['user_id'] = $user_id;
		}

		if ($additional3 != null)
		{
			add_log($mode, $required1, $additional1, $additional2, $additional3);
		}
		else if ($additional2 != null)
		{
			add_log($mode, $required1, $additional1, $additional2);
		}
		else if ($additional1 != null)
		{
			add_log($mode, $required1, $additional1);
		}
		else
		{
			add_log($mode, $required1);
		}

		$result = $db->sql_query('SELECT user_id, log_type, log_operation, log_data, reportee_id, forum_id, topic_id
			FROM ' . LOG_TABLE);

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}
