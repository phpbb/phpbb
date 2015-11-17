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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_posting.php';

class phpbb_attachment_thumbnail_test extends \phpbb_test_case
{
	/** @var \phpbb\attachment\thumbnail */
	protected $thumbnail;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \bantu\IniGetWrapper\IniGetWrapper */
	protected $php_ini;

	/** @var \FastImageSize\FastImageSize */
	protected $image_size;

	public function setUp()
	{
		global $phpbb_root_path;

		parent::setUp();

		$this->config = new \phpbb\config\config(array());
		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->php_ini = new \bantu\IniGetWrapper\IniGetWrapper();
		$this->image_size = new \FastImageSize\FastImageSize();
		$this->phpbb_root_path = $phpbb_root_path;

		$this->get_thumbnail();
	}

	private function get_thumbnail()
	{
		$this->thumbnail = new \phpbb\attachment\thumbnail(
			$this->config,
			$this->filesystem,
			$this->php_ini,
			$this->image_size
		);
	}

	public function data_get_size()
	{
		return array(
			array('foobar', 'meh', 'image/png', false),
			array('../tests/upload/fixture/png', 'meh', 'image/png', false,
				array('img_min_thumb_filesize' => 500)
			),
			array('../tests/upload/fixture/png', 'meh', 'image/png', false,
				array('img_min_thumb_filesize' => 0),
				true,
				false
			),
			array('../tests/upload/fixture/png', 'meh', 'image/png', false,
				array(
					'img_min_thumb_filesize'	=> 0,
					'img_max_thumb_width'		=> 200,
					'img_max_thumb_height'		=> 200,
				),
				true,
				array(
					'width'		=> 2,
					'height'	=> 2,
					'type'		=> IMAGETYPE_PNG,
				)
			),
			array('../tests/upload/fixture/png', 'meh', 'image/png', true,
				array(
					'img_min_thumb_filesize'	=> 0,
					'img_max_thumb_width'		=> 200,
					'img_max_thumb_height'		=> 200,
				),
				true,
				array(
					'width'		=> 500,
					'height'	=> 500,
					'type'		=> IMAGETYPE_PNG,
				)
			),
		);
	}

	/**
	 * @dataProvider data_get_size
	 */
	public function test_set_size($source, $destination, $type, $expected, $config_data = array(), $mock_image_size = false, $mock_image_size_return = false)
	{
		foreach ($config_data as $key => $value)
		{
			$this->config->set($key, $value);
		}

		if ($mock_image_size)
		{
			$this->image_size = $this->getMock('\FastImageSize\FastImageSize', array('getImageSize'));
			$this->image_size->expects($this->any())
				->method('getImageSize')
				->with($this->anything())
				->willReturn($mock_image_size_return);
		}

		$this->get_thumbnail();

		$this->assertSame($expected, $this->thumbnail->create($this->phpbb_root_path . $source, $destination, $type));

		$this->image_size = new \FastImageSize\FastImageSize();
	}
}
