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

class phpbb_dbal_migrator_tool_permission_role_test extends phpbb_database_test_case
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \includes\acp\auth\auth_admin */
	protected $auth_admin;

	/** @var \phpbb\db\migration\tool\permission */
	protected $tool;

	public $group_ids = [
		'REGISTERED' => 2,
		'GLOBAL_MODERATORS' => 4,
		'ADMINISTRATORS' => 5,
	];

	public $role_ids = [
		'ROLE_ADMIN_STANDARD' => 1,
		'ROLE_USER_FULL' => 5,
		'ROLE_MOD_FULL' => 10,
	];

	public $new_roles = [
		[
			'ROLE_ADMIN_NEW',
			'a_',
			'A new admin role',
			'a_new',
		],
		[
			'ROLE_MODERATOR_NEW',
			'm_',
			'A new mod role',
			'm_new',
		],
		[
			'ROLE_USER_NEW',
			'u_',
			'A new user role',
			'u_new',
		],
	];

	public $new_role_ids = [];

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/migrator_permission.xml');
	}

	protected function setUp(): void
	{
		// Global $db and $cache are needed in acp/auth.php constructor
		global $phpbb_root_path, $phpEx, $db, $cache;

		parent::setup();

		$db = $this->db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$cache = $this->cache = new \phpbb\cache\service(new \phpbb\cache\driver\dummy(), new \phpbb\config\config(array()), $this->db, $phpbb_dispatcher, $phpbb_root_path, $phpEx);
		$this->auth = new \phpbb\auth\auth();

		// Initialize this auth_admin instance later after adding new auth options via this->tool->add()
		if (!class_exists('auth_admin'))
		{
			include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
		}

		$this->tool = new \phpbb\db\migration\tool\permission($this->db, $this->cache, $this->auth, $phpbb_root_path, $phpEx);

		$this->new_roles_add();
	}

	public function new_roles_add()
	{
		foreach ($this->new_roles as $new_role_data)
		{
			$role_name = $new_role_data[0];
			$role_type = $new_role_data[1];
			$role_description = $new_role_data[2];
			$role_auth_option = $new_role_data[3];

			$this->tool->add($role_auth_option);
			$this->new_role_ids[$role_name] = $this->tool->role_add($role_name, $role_type, $role_description);
		}

		// Initialize external auth_admin instance here to keep acl_options array in sync with the one from the permission tool
		$this->auth_admin = new \auth_admin();
	}

	public function data_test_new_role_exists()
	{
		return [
			['ROLE_ADMIN_NEW', true],
			['ROLE_MODERATOR_NEW', true],
			['ROLE_USER_NEW', true],
		];
	}

	/**
	 * @dataProvider data_test_new_role_exists
	 */
	public function test_permission_new_role_exists($role_name, $expected)
	{
		$this->assertEquals($expected, (bool) $this->tool->role_exists($role_name));
	}

	public function data_test_permission_assign_new_roles()
	{
		return [
			[
				'group',
				0,
				'ADMINISTRATORS',
				['a_new' => true],
				'ROLE_ADMIN_NEW',
			],
			[
				'group',
				0,
				'GLOBAL_MODERATORS',
				['m_new' => true],
				'ROLE_MODERATOR_NEW',
			],
			[
				'group',
				0,
				'REGISTERED',
				['u_new' => true],
				'ROLE_USER_NEW',
			],
		];
	}

	/**
	 * @dataProvider data_test_permission_assign_new_roles
	 */
	public function test_permission_assign_new_roles($ug_type, $forum_id, $group_name, $auth, $role_name, $clear_prefetch = true)
	{
		$auth_option = key($auth);
		$group_id = (int) $this->group_ids[$group_name];
		$role_id = (int) $this->new_role_ids[$role_name];
		$expected = current($auth);

		// Set auth options for each role
		$this->tool->permission_set($role_name, $auth_option, 'role', true);

		// Assign roles to groups
		$this->auth_admin->acl_set($ug_type, $forum_id, $group_id, $auth, $role_id, $clear_prefetch);

		// Test if role based group permissions assigned correctly
		$new_perm_state = $this->auth->acl_group_raw_data($group_id, $auth_option);
		$this->assertEquals($expected, !empty($new_perm_state), "$auth_option is " . ($expected ? 'empty' : 'not empty') . " for $group_name");
	}

	/**
	 * @dataProvider data_test_permission_assign_new_roles
	 * @depends test_permission_new_role_exists
	 * @depends test_permission_assign_new_roles
	 */
	public function test_permission_new_role_remove($ug_type, $forum_id, $group_name, $auth, $role_name)
	{
		$auth_option = key($auth);
		$group_id = (int) $this->group_ids[$group_name];
		$role_id = (int) $this->new_role_ids[$role_name];

		$sql = 'SELECT agt.auth_role_id
			FROM ' . ACL_GROUPS_TABLE . ' agt, ' . ACL_ROLES_TABLE . ' art
			WHERE agt.auth_role_id = art.role_id
				AND art.role_id = ' . $role_id;

		// Set auth options for each role
		$this->tool->permission_set($role_name, $auth_option, 'role', true);

		// Assign roles to groups
		$this->auth_admin->acl_set($ug_type, $forum_id, $group_id, $auth, $role_id);

		// Check if the role is assigned to the group
		$result = $this->db->sql_query($sql);
		$this->assertEquals($role_id, $this->db->sql_fetchfield('auth_role_id'));
		$this->db->sql_freeresult($result);

		$this->tool->role_remove($role_name);
		$this->assertFalse((bool) $this->tool->role_exists($role_name));

		// Check if the role is unassigned
		$result = $this->db->sql_query($sql);
		$this->assertFalse($this->db->sql_fetchfield('auth_role_id'));
		$this->db->sql_freeresult($result);
	}

	public function test_copied_permission_set()
	{
		$sql = 'SELECT rdt.auth_setting
			FROM ' . ACL_OPTIONS_TABLE. ' ot, ' . ACL_ROLES_DATA_TABLE . ' rdt
			WHERE rdt.role_id = ' . $this->role_ids['ROLE_ADMIN_STANDARD'] . "
				AND auth_option = 'u_copied_permission'
				AND ot.auth_option_id = rdt.auth_option_id";

		// Add new local 'u_copied_permission' copied from 'u_test'
		// It should be added to the ROLE_ADMIN_STANDARD role automatically similar to 'u_test' permission
		$this->tool->add('u_copied_permission', false, 'u_test');
		$this->assertEquals(true, $this->tool->exists('u_copied_permission', false));

		// Copied permission setting should be equal to what it was copied from
		$result = $this->db->sql_query($sql);
		$this->assertEquals(0, $this->db->sql_fetchfield('auth_setting')); 
		$this->db->sql_freeresult($result);

		// Set new permission for copied auth option for the role
		$this->tool->permission_set('ROLE_ADMIN_STANDARD', 'u_copied_permission', 'role', true);

		// Copied permission setting should be updated
		$result = $this->db->sql_query($sql);
		$this->assertEquals(1, $this->db->sql_fetchfield('auth_setting'));
		$this->db->sql_freeresult($result);
	}

}
