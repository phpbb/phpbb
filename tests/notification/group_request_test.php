<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/base.php';

class phpbb_notification_group_request_test extends phpbb_tests_notification_base
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/group_request.xml');
	}

	protected function get_notification_types()
	{
		return array_merge(
			parent::get_notification_types(),
			array(
				'group_request',
			)
		);
	}

	public function test_notifications()
	{
		global $phpbb_root_path, $phpEx, $phpbb_dispatcher, $phpbb_log;

		include($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_content.' . $phpEx);

		set_config(false, false, false, $this->config);

		$this->container->set('groupposition.legend', new phpbb_groupposition_legend(
			$this->db,
			$this->user
		));
		$this->container->set('groupposition.teampage', new phpbb_groupposition_teampage(
			$this->db,
			$this->user,
			$this->cache->get_driver()
		));
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$phpbb_log = new phpbb_log_null();

		// Now on to the actual test

		$this->assertEquals(1, $this->notifications->get_notification_type_id('group_request'));

		$group_id = false;
		group_create($group_id, GROUP_OPEN, 'test', 'test group', array());

		// Add user 1 as group leader
		group_user_add($group_id, 2, false, false, false, true, false);

		// Add user 2 as pending
		group_user_add($group_id, 3, false, false, false, false, true);

		$notifications = $this->notifications->load_notifications(array(
			'count_unread'	=> true,
			'user_id'		=> 2,
		));

		$expected = array(
			1 => array(
				'notification_type_id'	=> 1,
				'item_id'				=> 3, // user_id of requesting join
				'item_parent_id'		=> $group_id,
				'user_id'	   			=> 2,
				'notification_read'		=> 0,
				'notification_data'	   	=> array(
					'group_name'			=> 'test',
				),
			),
		);

		$this->assertEquals(sizeof($expected), $notifications['unread_count']);

		$notifications = $notifications['notifications'];

		foreach ($expected as $notification_id => $notification_data)
		{
			$this->assertEquals($notification_id, $notifications[$notification_id]->notification_id, 'notification_id');

			foreach ($notification_data as $key => $value)
			{
				$this->assertEquals($value, $notifications[$notification_id]->$key, $key . ' ' . $notification_id);
			}
		}
	}
}
