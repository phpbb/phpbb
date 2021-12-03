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

namespace phpbb\db\migration\tool;

/**
* Migration permission management tool
*/
class permission implements \phpbb\db\migration\tool\tool_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \includes\acp\auth\auth_admin */
	protected $auth_admin;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\cache\service $cache
	* @param \phpbb\auth\auth $auth
	* @param string $phpbb_root_path
	* @param string $php_ext
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\cache\service $cache, \phpbb\auth\auth $auth, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->auth = $auth;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		if (!class_exists('auth_admin'))
		{
			include($this->phpbb_root_path . 'includes/acp/auth.' . $this->php_ext);
		}
		$this->auth_admin = new \auth_admin();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_name()
	{
		return 'permission';
	}

	/**
	* Permission Exists
	*
	* Check if a permission (auth) setting exists
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting,
	* 	False for a local permission setting
	* @return bool true if it exists, false if not
	*/
	public function exists($auth_option, $global = true)
	{
		if ($global)
		{
			$type_sql = ' AND is_global = 1';
		}
		else
		{
			$type_sql = ' AND is_local = 1';
		}

		$sql = 'SELECT auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = '" . $this->db->sql_escape($auth_option) . "'"
				. $type_sql;
		$result = $this->db->sql_query($sql);

		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			return true;
		}

		return false;
	}

	/**
	* Permission Add
	*
	* Add a permission (auth) option
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting,
	* 	False for a local permission setting
	* @param int|false $copy_from If set, contains the id of the permission from which to copy the new one.
	* @return null
	*/
	public function add($auth_option, $global = true, $copy_from = false)
	{
		if ($this->exists($auth_option, $global))
		{
			return;
		}

		// We've added permissions, so set to true to notify the user.
		$this->permissions_added = true;

		// We have to add a check to see if the !$global (if global, local, and if local, global) permission already exists.  If it does, acl_add_option currently has a bug which would break the ACL system, so we are having a work-around here.
		if ($this->exists($auth_option, !$global))
		{
			$sql_ary = array(
				'is_global'	=> 1,
				'is_local'	=> 1,
			);
			$sql = 'UPDATE ' . ACL_OPTIONS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE auth_option = '" . $this->db->sql_escape($auth_option) . "'";
			$this->db->sql_query($sql);
		}
		else
		{
			if ($global)
			{
				$this->auth_admin->acl_add_option(array('global' => array($auth_option)));
			}
			else
			{
				$this->auth_admin->acl_add_option(array('local' => array($auth_option)));
			}
		}

		// The permission has been added, now we can copy it if needed
		if ($copy_from && isset($this->auth_admin->acl_options['id'][$copy_from]))
		{
			$old_id = $this->auth_admin->acl_options['id'][$copy_from];
			$new_id = $this->auth_admin->acl_options['id'][$auth_option];

			$tables = array(ACL_GROUPS_TABLE, ACL_ROLES_DATA_TABLE, ACL_USERS_TABLE);

			foreach ($tables as $table)
			{
				$sql = 'SELECT *
					FROM ' . $table . '
					WHERE auth_option_id = ' . $old_id;
				$result = $this->db->sql_query($sql);

				$sql_ary = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$row['auth_option_id'] = $new_id;
					$sql_ary[] = $row;
				}
				$this->db->sql_freeresult($result);

				if (!empty($sql_ary))
				{
					$this->db->sql_multi_insert($table, $sql_ary);
				}
			}

			$this->auth_admin->acl_clear_prefetch();
		}
	}

	/**
	* Permission Remove
	*
	* Remove a permission (auth) option
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting,
	* 	False for a local permission setting
	* @return null
	*/
	public function remove($auth_option, $global = true)
	{
		if (!$this->exists($auth_option, $global))
		{
			return;
		}

		if ($global)
		{
			$type_sql = ' AND is_global = 1';
		}
		else
		{
			$type_sql = ' AND is_local = 1';
		}
		$sql = 'SELECT auth_option_id, is_global, is_local
			FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = '" . $this->db->sql_escape($auth_option) . "'" .
				$type_sql;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$id = (int) $row['auth_option_id'];

		// If it is a local and global permission, do not remove the row! :P
		if ($row['is_global'] && $row['is_local'])
		{
			$sql = 'UPDATE ' . ACL_OPTIONS_TABLE . '
				SET ' . (($global) ? 'is_global = 0' : 'is_local = 0') . '
				WHERE auth_option_id = ' . $id;
			$this->db->sql_query($sql);
		}
		else
		{
			// Delete time
			$tables = array(ACL_GROUPS_TABLE, ACL_ROLES_DATA_TABLE, ACL_USERS_TABLE, ACL_OPTIONS_TABLE);
			foreach ($tables as $table)
			{
				$this->db->sql_query('DELETE FROM ' . $table . '
					WHERE auth_option_id = ' . $id);
			}
		}

		// Purge the auth cache
		$this->cache->destroy('_acl_options');
		$this->auth->acl_clear_prefetch();
	}

	/**
	 * Check if a permission role exists
	 *
	 * @param string $role_name The role name
	 *
	 * @return int The id of the role if it exists, 0 otherwise
	 */
	public function role_exists($role_name)
	{
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_name = '" . $this->db->sql_escape($role_name) . "'";
		$result = $this->db->sql_query($sql);
		$role_id = (int) $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);

		return $role_id;
	}

	/**
	* Add a new permission role
	*
	* @param string $role_name The new role name
	* @param string $role_type The type (u_, m_, a_)
	* @param string $role_description Description of the new role
	*
	* @return null
	*/
	public function role_add($role_name, $role_type, $role_description = '')
	{
		if ($this->role_exists($role_name))
		{
			return;
		}

		$sql = 'SELECT MAX(role_order) AS max_role_order
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_type = '" . $this->db->sql_escape($role_type) . "'";
		$this->db->sql_query($sql);
		$role_order = (int) $this->db->sql_fetchfield('max_role_order');
		$role_order = (!$role_order) ? 1 : $role_order + 1;

		$sql_ary = array(
			'role_name'			=> $role_name,
			'role_description'	=> $role_description,
			'role_type'			=> $role_type,
			'role_order'		=> $role_order,
		);

		$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		return $this->db->sql_nextid();
	}

	/**
	* Update the name on a permission role
	*
	* @param string $old_role_name The old role name
	* @param string $new_role_name The new role name
	* @return null
	* @throws \phpbb\db\migration\exception
	*/
	public function role_update($old_role_name, $new_role_name)
	{
		if (!$this->role_exists($old_role_name))
		{
			throw new \phpbb\db\migration\exception('ROLE_NOT_EXIST', $old_role_name);
		}

		$sql = 'UPDATE ' . ACL_ROLES_TABLE . "
			SET role_name = '" . $this->db->sql_escape($new_role_name) . "'
			WHERE role_name = '" . $this->db->sql_escape($old_role_name) . "'";
		$this->db->sql_query($sql);
	}

	/**
	* Remove a permission role
	*
	* @param string $role_name The role name to remove
	* @return null
	*/
	public function role_remove($role_name)
	{
		if (!($role_id = $this->role_exists($role_name)))
		{
			return;
		}

		// Get the role type
		$sql = 'SELECT role_type
			FROM ' . ACL_ROLES_TABLE . '
			WHERE role_id = ' . (int) $role_id;
		$result = $this->db->sql_query($sql);
		$role_type = $this->db->sql_fetchfield('role_type');
		$this->db->sql_freeresult($result);

		// Get complete auth array
		$sql = 'SELECT auth_option, auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option " . $this->db->sql_like_expression($role_type . $this->db->get_any_char());
		$result = $this->db->sql_query($sql);

		$auth_settings = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$auth_settings[$row['auth_option']] = ACL_NO;
		}
		$this->db->sql_freeresult($result);

		// Get the role auth settings we need to re-set...
		$sql = 'SELECT o.auth_option, r.auth_setting
			FROM ' . ACL_ROLES_DATA_TABLE . ' r, ' . ACL_OPTIONS_TABLE . ' o
			WHERE o.auth_option_id = r.auth_option_id
				AND r.role_id = ' . (int) $role_id;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$auth_settings[$row['auth_option']] = $row['auth_setting'];
		}
		$this->db->sql_freeresult($result);

		// Get role assignments
		$hold_ary = $this->auth_admin->get_role_mask($role_id);

		// Re-assign permissions
		foreach ($hold_ary as $forum_id => $forum_ary)
		{
			if (isset($forum_ary['users']))
			{
				$this->auth_admin->acl_set('user', $forum_id, $forum_ary['users'], $auth_settings, 0, false);
			}

			if (isset($forum_ary['groups']))
			{
				$this->auth_admin->acl_set('group', $forum_id, $forum_ary['groups'], $auth_settings, 0, false);
			}
		}

		// Remove role from users and groups just to be sure (happens through acl_set)
		$sql = 'DELETE FROM ' . ACL_USERS_TABLE . '
			WHERE auth_role_id = ' . $role_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
			WHERE auth_role_id = ' . $role_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . ACL_ROLES_DATA_TABLE . '
			WHERE role_id = ' . $role_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . ACL_ROLES_TABLE . '
			WHERE role_id = ' . $role_id;
		$this->db->sql_query($sql);

		$this->auth->acl_clear_prefetch();
	}

	/**
	* Permission Set
	*
	* Allows you to set permissions for a certain group/role
	*
	* @param string $name The name of the role/group
	* @param string|array $auth_option The auth_option or array of
	* 	auth_options you would like to set
	* @param string $type The type (role|group)
	* @param bool $has_permission True if you want to give them permission,
	* 	false if you want to deny them permission
	* @return null
	* @throws \phpbb\db\migration\exception
	*/
	public function permission_set($name, $auth_option, $type = 'role', $has_permission = true)
	{
		if (!is_array($auth_option))
		{
			$auth_option = array($auth_option);
		}

		$new_auth = array();
		$sql = 'SELECT auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('auth_option', $auth_option);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$new_auth[] = (int) $row['auth_option_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($new_auth))
		{
			return;
		}

		$current_auth = array();

		$type = (string) $type; // Prevent PHP bug.

		switch ($type)
		{
			case 'role':
				if (!($role_id = $this->role_exists($name)))
				{
					throw new \phpbb\db\migration\exception('ROLE_NOT_EXIST', $name);
				}

				$sql = 'SELECT auth_option_id, auth_setting
					FROM ' . ACL_ROLES_DATA_TABLE . '
					WHERE role_id = ' . $role_id;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$current_auth[$row['auth_option_id']] = $row['auth_setting'];
				}
				$this->db->sql_freeresult($result);
			break;

			case 'group':
				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = '" . $this->db->sql_escape($name) . "'";
				$this->db->sql_query($sql);
				$group_id = (int) $this->db->sql_fetchfield('group_id');

				if (!$group_id)
				{
					throw new \phpbb\db\migration\exception('GROUP_NOT_EXIST', $name);
				}

				// If the group has a role set for them we will add the requested permissions to that role.
				$sql = 'SELECT auth_role_id
					FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id . '
						AND auth_role_id <> 0
						AND forum_id = 0';
				$this->db->sql_query($sql);
				$role_id = (int) $this->db->sql_fetchfield('auth_role_id');
				if ($role_id)
				{
					$sql = 'SELECT role_name, role_type
						FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$this->db->sql_query($sql);
					$role_data = $this->db->sql_fetchrow();
					if (!$role_data)
					{
						throw new \phpbb\db\migration\exception('ROLE_ASSIGNED_NOT_EXIST', $name, $role_id);
					}

					$role_name = $role_data['role_name'];
					$role_type = $role_data['role_type'];

					// Filter new auth options to match the role type: a_ | f_ | m_ | u_
					// Set new auth options to the role only if options matching the role type were found
					$auth_option = array_filter($auth_option,
						function ($option) use ($role_type)
						{
							return strpos($option, $role_type) === 0;
						}
					);

					if (count($auth_option))
					{
						return $this->permission_set($role_name, $auth_option, 'role', $has_permission);
					}
				}

				$sql = 'SELECT auth_option_id, auth_setting
					FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$current_auth[$row['auth_option_id']] = $row['auth_setting'];
				}
				$this->db->sql_freeresult($result);
			break;
		}

		$sql_ary = array();
		switch ($type)
		{
			case 'role':
				foreach ($new_auth as $auth_option_id)
				{
					if (!isset($current_auth[$auth_option_id]))
					{
						$sql_ary[] = array(
							'role_id'			=> $role_id,
							'auth_option_id'	=> $auth_option_id,
							'auth_setting'		=> $has_permission,
						);
					}
				}

				$this->db->sql_multi_insert(ACL_ROLES_DATA_TABLE, $sql_ary);
			break;

			case 'group':
				foreach ($new_auth as $auth_option_id)
				{
					if (!isset($current_auth[$auth_option_id]))
					{
						$sql_ary[] = array(
							'group_id'			=> $group_id,
							'auth_option_id'	=> $auth_option_id,
							'auth_setting'		=> $has_permission,
						);
					}
				}

				$this->db->sql_multi_insert(ACL_GROUPS_TABLE, $sql_ary);
			break;
		}

		$this->auth->acl_clear_prefetch();
	}

	/**
	* Permission Unset
	*
	* Allows you to unset (remove) permissions for a certain group/role
	*
	* @param string $name The name of the role/group
	* @param string|array $auth_option The auth_option or array of
	* 	auth_options you would like to set
	* @param string $type The type (role|group)
	* @return null
	* @throws \phpbb\db\migration\exception
	*/
	public function permission_unset($name, $auth_option, $type = 'role')
	{
		if (!is_array($auth_option))
		{
			$auth_option = array($auth_option);
		}

		$to_remove = array();
		$sql = 'SELECT auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('auth_option', $auth_option);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$to_remove[] = (int) $row['auth_option_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($to_remove))
		{
			return;
		}

		$type = (string) $type; // Prevent PHP bug.

		switch ($type)
		{
			case 'role':
				if (!($role_id = $this->role_exists($name)))
				{
					throw new \phpbb\db\migration\exception('ROLE_NOT_EXIST', $name);
				}

				$sql = 'DELETE FROM ' . ACL_ROLES_DATA_TABLE . '
					WHERE ' . $this->db->sql_in_set('auth_option_id', $to_remove) . '
						AND role_id = ' . (int) $role_id;
				$this->db->sql_query($sql);
			break;

			case 'group':
				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = '" . $this->db->sql_escape($name) . "'";
				$this->db->sql_query($sql);
				$group_id = (int) $this->db->sql_fetchfield('group_id');

				if (!$group_id)
				{
					throw new \phpbb\db\migration\exception('GROUP_NOT_EXIST', $name);
				}

				// If the group has a role set for them we will remove the requested permissions from that role.
				$sql = 'SELECT auth_role_id
					FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id . '
						AND auth_role_id <> 0';
				$this->db->sql_query($sql);
				$role_id = (int) $this->db->sql_fetchfield('auth_role_id');
				if ($role_id)
				{
					$sql = 'SELECT role_name
						FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$this->db->sql_query($sql);
					$role_name = $this->db->sql_fetchfield('role_name');
					if (!$role_name)
					{
						throw new \phpbb\db\migration\exception('ROLE_ASSIGNED_NOT_EXIST', $name, $role_id);
					}

					return $this->permission_unset($role_name, $auth_option, 'role');
				}

				$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
					WHERE ' . $this->db->sql_in_set('auth_option_id', $to_remove);
				$this->db->sql_query($sql);
			break;
		}

		$this->auth->acl_clear_prefetch();
	}

	/**
	* {@inheritdoc}
	*/
	public function reverse()
	{
		$arguments = func_get_args();
		$original_call = array_shift($arguments);

		$call = false;
		switch ($original_call)
		{
			case 'add':
				$call = 'remove';
			break;

			case 'remove':
				$call = 'add';
			break;

			case 'permission_set':
				$call = 'permission_unset';
			break;

			case 'permission_unset':
				$call = 'permission_set';
			break;

			case 'role_add':
				$call = 'role_remove';
			break;

			case 'role_remove':
				$call = 'role_add';
			break;

			case 'role_update':
				// Set to the original value if the current value is what we compared to originally
				$arguments = array(
					$arguments[1],
					$arguments[0],
				);
			break;

			case 'reverse':
				// Reversing a reverse is just the call itself
				$call = array_shift($arguments);
			break;
		}

		if ($call)
		{
			return call_user_func_array(array(&$this, $call), $arguments);
		}
	}
}
