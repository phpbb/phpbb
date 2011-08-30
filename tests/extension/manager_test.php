<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/cache.php';
require_once dirname(__FILE__) . '/ext/bar/bar.php';
require_once dirname(__FILE__) . '/ext/moo/moo.php';

class phpbb_extension_manager_test extends phpbb_database_test_case
{
	protected $extension_manager;
	protected $class_loader;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/extensions.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->extension_manager = new phpbb_extension_manager(
			$this->new_dbal(),
			'phpbb_ext',
			dirname(__FILE__) . '/',
			'.php',
			new phpbb_mock_cache
		);
	}

	public function test_available()
	{
		$this->assertEquals(array('bar', 'foo', 'moo'), array_keys($this->extension_manager->all_available()));
	}

	public function test_enabled()
	{
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
	}

	public function test_configured()
	{
		$this->assertEquals(array('foo', 'moo'), array_keys($this->extension_manager->all_configured()));
	}

	public function test_enable()
	{
		phpbb_ext_bar::$state = 0;

		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->extension_manager->enable('bar');
		$this->assertEquals(array('bar', 'foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('bar', 'foo', 'moo'), array_keys($this->extension_manager->all_configured()));

		$this->assertEquals(4, phpbb_ext_bar::$state);
	}

	public function test_disable()
	{
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->extension_manager->disable('foo');
		$this->assertEquals(array(), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('foo', 'moo'), array_keys($this->extension_manager->all_configured()));
	}

	public function test_purge()
	{
		phpbb_ext_moo::$purged = false;

		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('foo', 'moo'), array_keys($this->extension_manager->all_configured()));
		$this->extension_manager->purge('moo');
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_configured()));

		$this->assertTrue(phpbb_ext_moo::$purged);
	}

	public function test_enabled_no_cache()
	{
		$extension_manager = new phpbb_extension_manager(
			$this->new_dbal(),
			'phpbb_ext',
			dirname(__FILE__) . '/',
			'.php'
		);

		$this->assertEquals(array('foo'), array_keys($extension_manager->all_enabled()));
	}

}
