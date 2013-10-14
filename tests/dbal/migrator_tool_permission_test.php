<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_dbal_migrator_tool_permission_test extends phpbb_database_test_case
{
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
		$cache = $this->cache = new \phpbb\cache\service(new \phpbb\cache\driver\null(), new \phpbb\config\config(array()), $this->db, $phpbb_root_path, $phpEx);
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
}
