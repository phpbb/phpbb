<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_extension_style_path_provider_test extends phpbb_test_case
{
	protected $relative_root_path;
	protected $root_path;

	public function setUp()
	{
		$this->relative_root_path = './';
		$this->root_path = dirname(__FILE__) . '/';
	}

	public function test_find()
	{
		$phpbb_style_path_provider = new phpbb_style_path_provider();
		$phpbb_style_path_provider->set_styles(array($this->relative_root_path . 'styles/prosilver'));
		$phpbb_style_extension_path_provider = new phpbb_style_extension_path_provider(new phpbb_mock_extension_manager(
			$this->root_path,
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
			)), $phpbb_style_path_provider, $this->relative_root_path);

		$this->assertEquals(array(
			'style' => array(
				$this->relative_root_path . 'styles/prosilver',
			),
			'bar' => array(
				$this->root_path . 'ext/bar/styles/prosilver',
			),
		), $phpbb_style_extension_path_provider->find());
	}
}
