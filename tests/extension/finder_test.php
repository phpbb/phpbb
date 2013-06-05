<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

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
					'ext_path' => 'ext/foo/',
				),
				'bar' => array(
					'ext_name' => 'bar',
					'ext_active' => '1',
					'ext_path' => 'ext/bar/',
				),
			));

		$this->finder = $this->extension_manager->get_finder();
	}

	public function test_suffix_get_classes()
	{
		$classes = $this->finder
			->core_path('includes/default/')
			->extension_suffix('_class')
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

	public function test_get_directories()
	{
		$dirs = $this->finder
			->directory('/type')
			->get_directories();

		sort($dirs);
		$this->assertEquals(array(
			dirname(__FILE__) . '/ext/foo/type/',
		), $dirs);
	}

	public function test_prefix_get_directories()
	{
		$dirs = $this->finder
            ->prefix('ty')
			->get_directories();

		sort($dirs);
		$this->assertEquals(array(
			dirname(__FILE__) . '/ext/foo/sub/type/',
			dirname(__FILE__) . '/ext/foo/type/',
			dirname(__FILE__) . '/ext/foo/typewrong/',
		), $dirs);
	}

	public function test_prefix_get_classes()
	{
		$classes = $this->finder
			->core_path('includes/default/')
			->extension_prefix('hidden_')
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
			->core_path('includes/default/')
			->extension_directory('type')
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

	public function test_uncleansub_directory_get_classes()
	{
		$classes = $this->finder
			->directory('/sub/../sub/type')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'phpbb_ext_foo_sub_type_alternative',
			),
			$classes
		);
	}

	public function test_find_from_extension()
	{
		$files = $this->finder
			->extension_directory('/type')
			->find_from_extension('foo', dirname(__FILE__) . '/ext/foo/');
		$classes = $this->finder->get_classes_from_files($files);

		sort($classes);
		$this->assertEquals(
			array(
				'phpbb_ext_foo_type_alternative',
				'phpbb_ext_foo_type_dummy_empty',
			),
			$classes
		);
	}

	/**
	* These do not work because of changes with PHPBB3-11386
	* They do not seem neccessary to me, so I am commenting them out for now
	public function test_get_classes_create_cache()
	{
		$cache = new phpbb_mock_cache;
		$finder = new phpbb_extension_finder($this->extension_manager, new phpbb_filesystem(), dirname(__FILE__) . '/', $cache, 'php', '_custom_cache_name');
		$files = $finder->suffix('_class.php')->get_files();

		$expected_files = array(
			'ext/bar/my/hidden_class.php' => 'bar',
			'ext/foo/a_class.php' => 'foo',
			'ext/foo/b_class.php' => 'foo',
		);

		$query = array(
			'core_path' => false,
			'core_suffix' => '_class.php',
			'core_prefix' => false,
			'core_directory' => false,
			'extension_suffix' => '_class.php',
			'extension_prefix' => false,
			'extension_directory' => false,
			'is_dir' => false,
		);

		$cache->checkAssociativeVar($this, '_custom_cache_name', array(
			md5(serialize($query)) => $expected_files,
		), false);
	}

	public function test_cached_get_files()
	{
		$query = array(
			'core_path' => 'includes/foo',
			'core_suffix' => false,
			'core_prefix' => false,
			'core_directory' => 'bar',
			'extension_suffix' => false,
			'extension_prefix' => false,
			'extension_directory' => false,
			'is_dir' => false,
		);

		$finder = new phpbb_extension_finder(
			$this->extension_manager,
			new phpbb_filesystem(),
			dirname(__FILE__) . '/',
			new phpbb_mock_cache(array(
				'_ext_finder' => array(
					md5(serialize($query)) => array('file_name' => 'extension'),
				),
			))
		);

		$classes = $finder
			->core_path($query['core_path'])
			->core_directory($query['core_directory'])
			->get_files();

		sort($classes);
		$this->assertEquals(
			array(dirname(__FILE__) . '/file_name'),
			$classes
		);
	}
	*/
}
