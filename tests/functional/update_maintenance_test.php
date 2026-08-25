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

/**
 * @group functional
 */
class phpbb_functional_update_maintenance_test extends phpbb_functional_test_case
{
	public function test_notification_titles()
	{
		$this->login();

		$admin_id = $this->get_admin_id();
		$type_id = $this->get_update_maintenance_type_id();

		$this->delete_admin_notifications($type_id, $admin_id);

		$notifications = [
			['template' => 'update_maintenance', 'current_version' => '3.2.0', 'new_version' => '3.3.0', 'lang_key' => 'NOTIFICATION_UPDATE_MAINTENANCE'],
			['template' => 'update_security', 'current_version' => '3.2.0', 'new_version' => '3.3.1', 'lang_key' => 'NOTIFICATION_UPDATE_SECURITY'],
			['template' => 'update_critical', 'current_version' => '3.2.0', 'new_version' => '3.3.2', 'lang_key' => 'NOTIFICATION_UPDATE_CRITICAL'],
		];

		foreach ($notifications as $i => $data)
		{
			$this->create_notification($type_id, $admin_id, $i + 1, $data['template'], $data['current_version'], $data['new_version']);
		}

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&sid=' . $this->sid);
		$html = $crawler->filter('html')->html();

		foreach ($notifications as $data)
		{
			$expected = $this->lang($data['lang_key'], $data['current_version'], $data['new_version']);
			$this->assertStringContainsString($expected, $html);
		}
	}

	private function get_admin_id()
	{
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = 'admin'";
		$result = $this->db->sql_query($sql);
		$admin_id = (int) $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);
		$this->assertGreaterThan(0, $admin_id);

		return $admin_id;
	}

	private function get_update_maintenance_type_id()
	{
		$sql = 'SELECT notification_type_id FROM ' . NOTIFICATION_TYPES_TABLE . "
			WHERE notification_type_name = 'notification.type.update_maintenance'";
		$result = $this->db->sql_query($sql);
		$type_id = (int) $this->db->sql_fetchfield('notification_type_id');
		$this->db->sql_freeresult($result);

		if (!$type_id)
		{
			$sql_ary = [
				'notification_type_name' => 'notification.type.update_maintenance',
				'notification_type_enabled' => 1,
			];
			$sql = 'INSERT INTO ' . NOTIFICATION_TYPES_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);
			$type_id = (int) $this->db->sql_nextid();
		}

		$this->assertGreaterThan(0, $type_id);

		return $type_id;
	}

	private function delete_admin_notifications($type_id, $admin_id)
	{
		$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . '
			WHERE notification_type_id = ' . (int) $type_id . '
				AND user_id = ' . (int) $admin_id;
		$this->db->sql_query($sql);
	}

	private function create_notification($type_id, $admin_id, $item_id, $template, $current_version, $new_version)
	{
		$notification_data = serialize([
			'template' => $template,
			'current_version' => $current_version,
			'new_version' => $new_version,
			'announcement' => '',
			'download' => '',
		]);

		$sql_ary = [
			'notification_type_id' => $type_id,
			'item_id' => $item_id,
			'item_parent_id' => 0,
			'user_id' => $admin_id,
			'notification_read' => 0,
			'notification_time' => time(),
			'notification_data' => $notification_data,
		];
		$sql = 'INSERT INTO ' . NOTIFICATIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
	}
}
