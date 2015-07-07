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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_dbal_migrator_tool_permission_test extends phpbb_database_test_case
{
	public $group_ids = array(
		'REGISTERED' => 2,
		'GLOBAL_MODERATORS' => 4,
		'ADMINISTRATORS' => 5,
	);

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/migrator_permission.xml');
	}

	public function setup()
	{
		// Global $db and $cache are needed in acp/auth.php constructor
		global $phpbb_root_path, $phpEx, $db, $cache;

		parent::setup();

		$db = $this->db = $this->new_dbal();
		$cache = $this->cache = new \phpbb\cache\service(new \phpbb\cache\driver\dummy(), new \phpbb\config\config(array()), $this->db, $phpbb_root_path, $phpEx);
		$this->auth = new \phpbb\auth\auth();

		$this->tool = new \phpbb\db\migration\tool\permission($this->db, $this->cache, $this->auth, $phpbb_root_path, $phpEx);
	}

	public function exists_data()
	{
		return array(
			array(
				'global',
				true,
				true,
			),
			array(
				'local',
				false,
				true,
			),
			array(
				'both',
				true,
				true,
			),
			array(
				'both',
				false,
				true,
			),
			array(
				'does_not_exist',
				true,
				false,
			),
		);
	}

	/**
	* @dataProvider exists_data
	*/
	public function test_exists($auth_option, $global, $expected)
	{
		$this->assertEquals($expected, $this->tool->exists($auth_option, $global));
	}

	public function test_add()
	{
		try
		{
			$this->tool->add('new', true);
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('new', true));
		$this->assertEquals(false, $this->tool->exists('new', false));

		try
		{
			$this->tool->add('new', false);
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('new', false));

		// Should fail (duplicate)
		try
		{
			$this->tool->add('new', true);
			$this->fail('Did not throw exception on duplicate');
		}
		catch (Exception $e) {}
	}

	public function test_remove()
	{
		try
		{
			$this->tool->remove('global', true);
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(false, $this->tool->exists('global', true));

		try
		{
			$this->tool->remove('both', false);
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(false, $this->tool->exists('both', false));

		// Should fail (does not exist)
		try
		{
			$this->tool->remove('new', true);
			$this->fail('Did not throw exception on duplicate');
		}
		catch (Exception $e) {}
	}

	public function test_reverse()
	{
		try
		{
			$this->tool->reverse('remove', 'global_test', true);
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertTrue($this->tool->exists('global_test', true));

		try
		{
			$this->tool->reverse('add', 'global_test', true);
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertFalse($this->tool->exists('global_test', true));
	}

	public function test_permission_set_data()
	{
		return array(
			array(
				'ADMINISTRATORS',
				'a_test',
				'group',
				true,
			),
			array(
				'GLOBAL_MODERATORS',
				'm_test',
				'group',
				true,
			),
			array(
				'REGISTERED',
				'u_test',
				'group',
				true,
			),
		);
	}

	/**
	* @dataProvider test_permission_set_data
	*/
	public function test_permission_set($group_name, $auth_option, $type, $has_permission)
	{
		$this->tool->permission_set($group_name, $auth_option, $type, $has_permission);
		$administrators_perm = $this->auth->acl_group_raw_data($this->group_ids['ADMINISTRATORS'], $auth_option);
		$global_moderators_perm = $this->auth->acl_group_raw_data($this->group_ids['GLOBAL_MODERATORS'], $auth_option);
		$registered_users_perm = $this->auth->acl_group_raw_data($this->group_ids['REGISTERED'], $auth_option);

		switch($group_name)
		{
			case 'GLOBAL_MODERATORS':
				$this->assertEquals(false, empty($administrators_perm), 'm_test is not empty for Administrators');
				$this->assertEquals(false, empty($global_moderators_perm), 'm_test is not empty for Global moderators');
				$this->assertEquals(true, empty($registered_users_perm), 'm_test empty for Registered users');
			break;

			case 'ADMINISTRATORS':
				$this->assertEquals(false, empty($administrators_perm), 'a_test is not empty for Administrators');
				$this->assertEquals(true, empty($global_moderators_perm), 'a_test is empty for Global moderators');
				$this->assertEquals(true, empty($registered_users_perm), 'a_test is empty for Registered users');
			break;

			case 'REGISTERED':
				$this->assertEquals(false, empty($administrators_perm), 'u_test is not empty for Administrators');
				$this->assertEquals(false, empty($global_moderators_perm), 'u_test is not empty for Global moderators');
				$this->assertEquals(false, empty($registered_users_perm), 'u_test is not empty for Registered users');
			break;
		}
	}
}
