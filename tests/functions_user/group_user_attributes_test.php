<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_functions_user_group_user_attributes_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/group_user_attributes.xml');
	}

	public function group_user_attributes_data()
	{
		return array(
			array(
				'Setting new default group without settings for user with no settings - no change',
				1,
				2,
				array(
					'group_avatar'	=> '',
					'group_avatar_type'		=> 0,
					'group_avatar_height'	=> 0,
					'group_avatar_width'	=> 0,
					'group_rank'	=> 0,
				),
				array(
					'user_avatar'	=> '',
					'user_rank'		=> 0,
				),
			),
			array(
				'Setting new default group without settings for user with default settings - user settings overwritten',
				2,
				2,
				array(
					'group_avatar'	=> '',
					'group_avatar_type'		=> 0,
					'group_avatar_height'	=> 0,
					'group_avatar_width'	=> 0,
					'group_rank'	=> 0,
				),
				array(
					'user_avatar'	=> '',
					'user_rank'		=> 0,
				),
			),
			array(
				'Setting new default group without settings for user with custom settings - no change',
				3,
				2,
				array(
					'group_avatar'	=> '',
					'group_avatar_type'		=> 0,
					'group_avatar_height'	=> 0,
					'group_avatar_width'	=> 0,
					'group_rank'	=> 0,
				),
				array(
					'user_avatar'	=> 'custom',
					'user_rank'		=> 2,
				),
			),
			array(
				'Setting new default group with settings for user with no settings - user settings overwritten',
				1,
				3,
				array(
					'group_avatar'	=> 'default2',
					'group_avatar_type'		=> 1,
					'group_avatar_height'	=> 1,
					'group_avatar_width'	=> 1,
					'group_rank'	=> 3,
				),
				array(
					'user_avatar'	=> 'default2',
					'user_rank'		=> 3,
				),
			),
			array(
				'Setting new default group with settings for user with default settings - user settings overwritten',
				2,
				3,
				array(
					'group_avatar'	=> 'default2',
					'group_avatar_type'		=> 1,
					'group_avatar_height'	=> 1,
					'group_avatar_width'	=> 1,
					'group_rank'	=> 3,
				),
				array(
					'user_avatar'	=> 'default2',
					'user_rank'		=> 3,
				),
			),
			array(
				'Setting new default group with settings for user with custom settings - no change',
				3,
				3,
				array(
					'group_avatar'	=> 'default2',
					'group_avatar_type'		=> 1,
					'group_avatar_height'	=> 1,
					'group_avatar_width'	=> 1,
					'group_rank'	=> 3,
				),
				array(
					'user_avatar'	=> 'custom',
					'user_rank'		=> 2,
				),
			),
		);
	}

	/**
	* @dataProvider group_user_attributes_data
	*/
	public function test_group_user_attributes($description, $user_id, $group_id, $group_row, $expected)
	{
		global $auth, $cache, $db, $phpbb_dispatcher, $user, $phpbb_container;

		$user->ip = '';
		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$auth = $this->getMock('phpbb_auth');
		$auth->expects($this->any())
			->method('acl_clear_prefetch');
		$cache_driver = new phpbb_cache_driver_null();
		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container
			->expects($this->any())
			->method('get')
			->with('cache.driver')
			->will($this->returnValue($cache_driver));

		group_user_attributes('default', $group_id, array($user_id), false, 'group_name', $group_row);

		$sql = 'SELECT user_avatar, user_rank
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . $user_id;
		$result = $db->sql_query($sql);

		$this->assertEquals(array($expected), $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}
}
