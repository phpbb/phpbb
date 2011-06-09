<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/cache.php';

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

		// disable the regular class loader to replace it with one that loads
		// test extensions
		global $class_loader;
		$class_loader->unregister();

		$prefix = dirname(__FILE__) . '/';
		$this->class_loader = new phpbb_class_loader($prefix . '../../phpBB/includes/', $prefix . 'ext/');
		$this->class_loader->register();

		$this->extension_manager = new phpbb_extension_manager(
			$this->new_dbal(),
			'phpbb_ext',
			$prefix,
			'.php',
			new phpbb_mock_cache
		);
	}

	protected function tearDown()
	{
		global $class_loader;
		$class_loader->register();
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
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->extension_manager->enable('bar');
		$this->assertEquals(array('bar', 'foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('bar', 'foo', 'moo'), array_keys($this->extension_manager->all_configured()));
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
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('foo', 'moo'), array_keys($this->extension_manager->all_configured()));
		$this->extension_manager->purge('moo');
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_configured()));
	}
}
