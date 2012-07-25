<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_extension_metadata_manager_test extends phpbb_database_test_case
{
	protected $class_loader;
	protected $extension_manager;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/extensions.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->extension_manager = new phpbb_extension_manager(
			$this->new_dbal(),
			new phpbb_config(array()),
			'phpbb_ext',
			dirname(__FILE__) . '/',
			'.php',
			new phpbb_mock_cache
		);
	}

	public function test_bar()
	{
		$phpbb_extension_metadata_manager = new phpbb_extension_metadata_manager(
			'bar',
			$this->new_dbal(),
			new phpbb_config(array()),
			$this->extension_manager,
			dirname(__FILE__) . '/',
			'.php',
			new phpbb_template(
				dirname(__FILE__) . '/',
				'.php',
				new phpbb_config(array()),
				new phpbb_user(),
				new phpbb_style_resource_locator()
			),
			new phpbb_mock_cache
		);

		//$this->assertEquals(array('bar', 'foo', 'vendor/moo'), array_keys($this->extension_manager->all_available()));
	}
}