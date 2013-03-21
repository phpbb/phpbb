<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_extension_styles_finder_test extends phpbb_test_case
{
	protected $extension_manager;
	protected $finder;
	protected $local_root_path;

	public function setUp()
	{
		global $phpbb_root_path;
		$this->local_root_path = $phpbb_root_path . '../tests/extension/';

		$this->extension_manager = new phpbb_mock_extension_manager(
			$this->local_root_path,
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

	public function test_style_with_rootpath()
	{
		$dirs = $this->finder
			->directory('/' . $this->local_root_path . 'styles/prosilver/template')
			->extension_suffix('.html')
			->get_files();

		sort($dirs);
		$this->assertEquals(array(
			$this->local_root_path . 'ext/foo/styles/prosilver/template/index_body.html',
		), $dirs);
	}

	public function test_style_without_rootpath()
	{
		$dirs = $this->finder
			->directory('/styles/prosilver/template')
			->extension_suffix('.html')
			->get_files();

		sort($dirs);
		$this->assertEquals(array(
			$this->local_root_path . 'ext/foo/styles/prosilver/template/index_body.html',
		), $dirs);
	}
}
