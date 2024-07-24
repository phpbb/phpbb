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

require_once __DIR__ . '/../../phpBB/includes/functions_privmsgs.php';

class phpbb_privmsgs_delete_user_pms_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/delete_user_pms.xml');
	}

	static public function delete_user_pms_data()
	{
		return array(
		//	array(
		//		(user we delete),
		//		array(remaining privmsgs ids),
		//		array(remaining privmsgs_to),
		//	),
			array(
				2,
				array(
					array('msg_id' => 1),
				),
				array(
					array('msg_id' => 1, 'user_id' => 3),
				),
			),
			array(
				3,
				array(
					array('msg_id' => 1),
					array('msg_id' => 2),
					array('msg_id' => 3),
					array('msg_id' => 5),
				),
				array(
					array('msg_id' => 1, 'user_id' => 2),
					array('msg_id' => 1, 'user_id' => 4),
					array('msg_id' => 2, 'user_id' => 2),
					array('msg_id' => 2, 'user_id' => 4),
					array('msg_id' => 3, 'user_id' => 2),
					array('msg_id' => 5, 'user_id' => 2),
					array('msg_id' => 5, 'user_id' => 4),
				),
			),
			array(
				5,
				array(
					array('msg_id' => 1),
					array('msg_id' => 2),
					array('msg_id' => 3),
					array('msg_id' => 4),
					array('msg_id' => 5),
				),
				array(
					array('msg_id' => 1, 'user_id' => 2),
					array('msg_id' => 1, 'user_id' => 3),
					array('msg_id' => 1, 'user_id' => 4),
					array('msg_id' => 2, 'user_id' => 2),
					array('msg_id' => 2, 'user_id' => 4),
					array('msg_id' => 3, 'user_id' => 2),
					array('msg_id' => 4, 'user_id' => 3),
					array('msg_id' => 5, 'user_id' => 2),
					array('msg_id' => 5, 'user_id' => 3),
					array('msg_id' => 5, 'user_id' => 4),
				),
			),
		);
	}

	/**
	* @dataProvider delete_user_pms_data
	*/
	public function test_delete_user_pms($delete_user, $remaining_privmsgs, $remaining_privmsgs_to)
	{
		global $db, $phpbb_container;

		$db = $this->new_dbal();

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('notification_manager', new phpbb_mock_notification_manager());

		$storage =  $this->createMock('\phpbb\storage\storage');

		// Works as a workaround for tests
		$phpbb_container->set('attachment.manager', new \phpbb\attachment\delete(new \phpbb\config\config(array()), $db, new \phpbb_mock_event_dispatcher(), new \phpbb\attachment\resync($db), $storage));

		phpbb_delete_users_pms([$delete_user]);

		$sql = 'SELECT msg_id
			FROM ' . PRIVMSGS_TABLE;
		$result = $db->sql_query($sql);

		$this->assertEquals($remaining_privmsgs, $db->sql_fetchrowset($result));

		$sql = 'SELECT msg_id, user_id
			FROM ' . PRIVMSGS_TO_TABLE;
		$result = $db->sql_query($sql);

		$this->assertEquals($remaining_privmsgs_to, $db->sql_fetchrowset($result));
	}
}
