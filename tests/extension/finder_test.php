<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/cache.php';
require_once dirname(__FILE__) . '/../mock/extension_manager.php';

class phpbb_extension_finder_test extends phpbb_test_case
{
	protected $extension_manager;
	protected $finder;

	public function setUp()
	{
		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'foo' => array(
					'ext_name' => 'foo',
					'ext_active' => '1',
					'ext_path' => dirname(__FILE__) . '/ext/foo/',
				),
				'bar' => array(
					'ext_name' => 'bar',
					'ext_active' => '1',
					'ext_path' => dirname(__FILE__) . '/ext/bar/',
				),
			));

		$this->finder = $this->extension_manager->get_finder();
	}

	public function test_suffix_get_classes()
	{
		$classes = $this->finder
			->default_path('includes/default/')
			->suffix('_class')
			->default_suffix('')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'phpbb_default_implementation',
				'phpbb_ext_bar_my_hidden_class',
				'phpbb_ext_foo_a_class',
				'phpbb_ext_foo_b_class',
			),
			$classes
		);
	}

	public function test_prefix_get_directories()
	{
		$dirs = $this->finder
			->directory('/type')
			->get_directories();

		sort($dirs);
		$this->assertEquals(array(
			'ext/foo/type/',
		), $dirs);
	}

	public function test_prefix_get_classes()
	{
		$classes = $this->finder
			->default_path('includes/default/')
			->prefix('hidden_')
			->default_prefix('')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'phpbb_default_implementation',
				'phpbb_ext_bar_my_hidden_class',
			),
			$classes
		);
	}

	public function test_directory_get_classes()
	{
		$classes = $this->finder
			->default_path('includes/default/')
			->directory('type')
			->default_directory('')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'phpbb_default_implementation',
				'phpbb_ext_foo_sub_type_alternative',
				'phpbb_ext_foo_type_alternative',
			),
			$classes
		);
	}

	public function test_absolute_directory_get_classes()
	{
		$classes = $this->finder
			->directory('/type/')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'phpbb_ext_foo_type_alternative',
			),
			$classes
		);
	}

	public function test_sub_directory_get_classes()
	{
		$classes = $this->finder
			->directory('/sub/type')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'phpbb_ext_foo_sub_type_alternative',
			),
			$classes
		);
	}

	public function test_get_classes_create_cache()
	{
		$cache = new phpbb_mock_cache;
		$finder = new phpbb_extension_finder($this->extension_manager, dirname(__FILE__) . '/includes/', $cache, '.php', '_custom_cache_name');
		$files = $finder->suffix('_class.php')->get_files();

		sort($files);

		$expected_files = array(
			'ext/bar/my/hidden_class.php',
			'ext/foo/a_class.php',
			'ext/foo/b_class.php',
		);

		$query = array(
			'default_path' => false,
			'default_suffix' => '_class.php',
			'default_prefix' => false,
			'default_directory' => false,
			'suffix' => '_class.php',
			'prefix' => false,
			'directory' => false,
			'is_dir' => false,
		);

		$this->assertEquals($expected_files, $files);
		$cache->checkAssociativeVar($this, '_custom_cache_name', array(
			md5(serialize($query)) => $expected_files,
		));
	}

	public function test_cached_get_files()
	{
		$query = array(
			'default_path' => 'includes/foo',
			'default_suffix' => false,
			'default_prefix' => false,
			'default_directory' => 'bar',
			'suffix' => false,
			'prefix' => false,
			'directory' => false,
			'is_dir' => false,
		);

		$finder = new phpbb_extension_finder($this->extension_manager, dirname(__FILE__) . '/includes/', new phpbb_mock_cache(array(
			'_ext_finder' => array(
				md5(serialize($query)) => array('file_name'),
			),
		)));

		$classes = $finder
			->default_path($query['default_path'])
			->default_directory($query['default_directory'])
			->get_files();

		sort($classes);
		$this->assertEquals(
			array('file_name'),
			$classes
		);
	}
}
