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

namespace phpbb\db\migration\data\v33x;

class remove_orphaned_roles extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v33x\v335'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'acl_remove_orphaned_roles']]],
		];
	}

	public function acl_remove_orphaned_roles()
	{
		$role_ids = [];
		$auth_role_ids = [];

		$sql = 'SELECT auth_role_id
			FROM ' . ACL_GROUPS_TABLE . '
			WHERE auth_role_id <> 0
				AND forum_id = 0';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$auth_role_ids[] = $row['auth_role_id'];
		}
		$this->db->sql_freeresult($result);

		if (count($auth_role_ids))
		{
			$sql = 'SELECT role_id
				FROM ' . ACL_ROLES_TABLE . '
				WHERE ' . $this->db->sql_in_set('role_id', $auth_role_ids);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$role_ids[] = $row['role_id'];
			}
			$this->db->sql_freeresult($result);
		}

		$non_existent_role_ids = array_diff($auth_role_ids, $role_ids);

		// Nothing to do, there are no non-existent roles assigned to groups
		if (empty($non_existent_role_ids))
		{
			return true;
		}

		// Remove assigned non-existent roles from users and groups
		$sql = 'DELETE FROM ' . ACL_USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('auth_role_id', $non_existent_role_ids);
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
			WHERE ' . $this->db->sql_in_set('auth_role_id', $non_existent_role_ids);
		$this->db->sql_query($sql);

		$auth = new \phpbb\auth\auth();
		$auth->acl_clear_prefetch();

		return true;
	}
}
