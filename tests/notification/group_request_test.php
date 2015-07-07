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

require_once dirname(__FILE__) . '/base.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

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
				'notification.type.group_request',
				'notification.type.group_request_approved',
			)
		);
	}

	public function test_notifications()
	{
		global $phpbb_root_path, $phpEx, $phpbb_dispatcher, $phpbb_log;

		include_once($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);
		include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);

		$this->container->set('groupposition.legend', new \phpbb\groupposition\legend(
			$this->db,
			$this->user
		));
		$this->container->set('groupposition.teampage', new \phpbb\groupposition\teampage(
			$this->db,
			$this->user,
			$this->cache->get_driver()
		));
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$phpbb_log = new \phpbb\log\dummy();
		$this->get_test_case_helpers()->set_s9e_services();

		// Now on to the actual test

		$group_id = false;
		group_create($group_id, GROUP_OPEN, 'test', 'test group', array());

		// Add user 2 as group leader
		group_user_add($group_id, 2, false, false, false, true, false);

		// Add user 3 as pending
		group_user_add($group_id, 3, false, false, false, false, true);

		$this->assert_notifications(
			array(
				// user 3 pending notification
				array(
					'item_id'				=> 3, // user_id of requesting join
					'item_parent_id'		=> $group_id,
					'user_id'	   			=> 2,
					'notification_read'		=> 0,
					'notification_data'	   	=> array(
						'group_name'			=> 'test',
					),
				),
			),
			array(
				'user_id'		=> 2,
			)
		);

		// Approve user 3 joining the group
		group_user_attributes('approve', $group_id, array(3));

		// user 3 pending notification should have been deleted
		$this->assert_notifications(
			array(),
			array(
				'user_id'		=> 2,
			)
		);

		$this->assert_notifications(
			array(
				// user 3 approved notification
				array(
					'item_id'				=> $group_id, // user_id of requesting join
					'user_id'	   			=> 3,
					'notification_read'		=> 0,
					'notification_data'	   	=> array(
						'group_name'			=> 'test',
					),
				),
			),
			array(
				'user_id'		=> 3,
			)
		);
	}
}
