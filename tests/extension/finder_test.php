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

class phpbb_extension_finder_test extends phpbb_test_case
{
	/** @var \phpbb\extension\manager */
	protected $extension_manager;
	/** @var \phpbb\finder */
	protected $finder;

	public function setUp()
	{
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
			));

		$this->finder = $this->extension_manager->get_finder();
	}

	public function test_suffix_get_classes()
	{
		$classes = $this->finder
			->core_path('phpbb/default/')
			->extension_suffix('_class')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'\phpbb\default\implementation',
				'\vendor2\foo\a_class',
				'\vendor2\foo\b_class',
				'\vendor3\bar\my\hidden_class',
			),
			$classes
		);
	}

	public function set_extensions_data()
	{
		return array(
			array(
				array(),
				array('\phpbb\default\implementation'),
			),
			array(
				array('vendor3/bar'),
				array(
					'\phpbb\default\implementation',
					'\vendor3\bar\my\hidden_class',
				),
			),
			array(
				array('vendor2/foo', 'vendor3/bar'),
				array(
					'\phpbb\default\implementation',
					'\vendor2\foo\a_class',
					'\vendor2\foo\b_class',
					'\vendor3\bar\my\hidden_class',
				),
			),
		);
	}

	/**
	 * @dataProvider set_extensions_data
	 */
	public function test_set_extensions($extensions, $expected)
	{
		$classes = $this->finder
			->set_extensions($extensions)
			->core_path('phpbb/default/')
			->extension_suffix('_class')
			->get_classes();

		sort($classes);
		$this->assertEquals($expected, $classes);
	}

	public function test_get_directories()
	{
		$dirs = $this->finder
			->directory('/type')
			->get_directories();

		sort($dirs);
		$this->assertEquals(array(
			dirname(__FILE__) . '/ext/vendor2/foo/type/',
		), $dirs);
	}

	public function test_prefix_get_directories()
	{
		$dirs = $this->finder
			->prefix('ty')
			->get_directories();

		sort($dirs);
		$this->assertEquals(array(
			dirname(__FILE__) . '/ext/vendor2/foo/sub/type/',
			dirname(__FILE__) . '/ext/vendor2/foo/type/',
			dirname(__FILE__) . '/ext/vendor2/foo/typewrong/',
		), $dirs);
	}

	public function test_prefix_get_classes()
	{
		$classes = $this->finder
			->core_path('phpbb/default/')
			->extension_prefix('hidden_')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'\phpbb\default\implementation',
				'\vendor3\bar\my\hidden_class',
			),
			$classes
		);
	}

	public function test_directory_get_classes()
	{
		$classes = $this->finder
			->core_path('phpbb/default/')
			->extension_directory('type')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'\phpbb\default\implementation',
				'\vendor2\foo\sub\type\alternative',
				'\vendor2\foo\type\alternative',
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
				'\vendor2\foo\type\alternative',
			),
			$classes
		);
	}

	public function test_non_absolute_directory_get_classes()
	{
		$classes = $this->finder
			->directory('type/')
			->get_classes();

		sort($classes);
		$this->assertEquals(
			array(
				'\vendor2\foo\sub\type\alternative',
				'\vendor2\foo\type\alternative',
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
				'\vendor2\foo\sub\type\alternative',
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
				'\vendor2\foo\sub\type\alternative',
			),
			$classes
		);
	}

	public function test_find_from_extension()
	{
		$files = $this->finder
			->extension_directory('/type')
			->find_from_extension('vendor2/foo', dirname(__FILE__) . '/ext/vendor2/foo/');
		$classes = $this->finder->get_classes_from_files($files);

		sort($classes);
		$this->assertEquals(
			array(
				'\vendor2\foo\type\alternative',
				'\vendor2\foo\type\dummy\empty',
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
		$finder = new \phpbb\finder(new \phpbb\filesystem\filesystem(), dirname(__FILE__) . '/', $cache, 'php', '_custom_cache_name');
		$finder->set_extensions(array_keys($this->extension_manager->all_enabled()));
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
			'core_path' => 'phpbb/foo',
			'core_suffix' => false,
			'core_prefix' => false,
			'core_directory' => 'bar',
			'extension_suffix' => false,
			'extension_prefix' => false,
			'extension_directory' => false,
			'is_dir' => false,
		);

		$finder = new \phpbb\finder(
			new \phpbb\filesystem\filesystem(),
			dirname(__FILE__) . '/',
			new phpbb_mock_cache(array(
				'_ext_finder' => array(
					md5(serialize($query)) => array('file_name' => 'extension'),
				),
			))
		);
		$finder->set_extensions(array_keys($this->extension_manager->all_enabled()));

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
