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

class phpbb_extension_extension_base_test extends phpbb_test_case
{
	protected static $reflection_method_get_migration_file_list;

	/** @var phpbb_mock_extension_manager */
	protected $extension_manager;

	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		$reflection_class = new ReflectionClass('\phpbb\extension\base');
		self::$reflection_method_get_migration_file_list = $reflection_class->getMethod('get_migration_file_list');
		self::$reflection_method_get_migration_file_list->setAccessible(true);
	}

	public function setUp()
	{
		$container = new phpbb_mock_container_builder();
		$migrator = new phpbb_mock_migrator();
		$container->set('migrator', $migrator);

		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
				'vendor3/bar' => array(
					'ext_name' => 'vendor3/bar',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor3/bar/',
				),
				'vendor2/bar' => array(
					'ext_name' => 'vendor2/bar',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/bar/',
				),
			),
			$container);
	}

	public function data_test_suffix_get_classes()
	{
		return array(
			array(
				'vendor2/bar',
				array(
					'\vendor2\bar\migrations\migration',
				),
			),
		);
	}

	/**
	* @dataProvider data_test_suffix_get_classes
	*/
	public function test_suffix_get_classes($extension_name, $expected)
	{
		$extension = $this->extension_manager->get_extension($extension_name);
		$this->assertEquals($expected, self::$reflection_method_get_migration_file_list->invoke($extension));
	}
}
