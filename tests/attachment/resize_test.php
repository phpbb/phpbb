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

class phpbb_attachment_resize_test extends \phpbb_test_case
{
	/** @var \phpbb\attachment\resize */
	protected $resize;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\attachment\image_helper */
	protected $image_helper;

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \bantu\IniGetWrapper\IniGetWrapper */
	protected $php_ini;

	/** @var \FastImageSize\FastImageSize */
	protected $image_size;

	public function setUp()
	{
		global $phpbb_root_path;

		if (!@extension_loaded('gd'))
		{
			$this->markTestSkipped('Attachment resize tests require gd extension.');
		}

		parent::setUp();

		$this->config = new \phpbb\config\config(array());
		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->php_ini = new \bantu\IniGetWrapper\IniGetWrapper();
		$this->image_size = new \FastImageSize\FastImageSize();
		$this->phpbb_root_path = $phpbb_root_path;
		$this->image_helper = new \phpbb\attachment\image_helper();

		$this->get_resize();
	}

	private function get_resize()
	{
		$this->resize = new \phpbb\attachment\resize(
			$this->filesystem,
			$this->image_helper,
			$this->php_ini,
			$this->image_size
		);
		$this->resize
			->set_imagick_path($this->config['img_imagick'])
			->set_min_file_size($this->config['img_min_thumb_filesize'])
			->set_target_size(
				$this->config['img_max_thumb_width'],
				$this->config['imag_max_thumb_height']
			);
	}

	public function data_get_size()
	{
		return array(
			array('foobar', '../tests/upload/fixture/meh', 'image/png', false),
			array('../tests/upload/fixture/png', '../tests/upload/fixture/meh', 'image/png', false,
				array('img_min_thumb_filesize' => 500)
			),
			array('../tests/upload/fixture/png', '../tests/upload/fixture/meh', 'image/png', false,
				array('img_min_thumb_filesize' => 0),
				true,
				false
			),
			array('../tests/upload/fixture/png', '../tests/upload/fixture/meh', 'image/png', false,
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
			array('../tests/upload/fixture/png', '../tests/upload/fixture/meh', 'image/png', true,
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

		$this->get_resize();

		$this->assertSame($expected, $this->resize->create($this->phpbb_root_path . $source, $this->phpbb_root_path . $destination, $type));

		$this->image_size = new \FastImageSize\FastImageSize();

		if (file_exists($this->phpbb_root_path . $destination))
		{
			unlink($this->phpbb_root_path . $destination);
		}
	}

	public function test_create_fail()
	{
		$this->config->set('img_min_thumb_filesize', 0);
		$this->config->set('img_max_thumb_width', 200);
		$this->config->set('img_max_thumb_height', 200);

		$this->image_size = $this->getMock('\FastImageSize\FastImageSize', array('getImageSize'));
		$this->image_size->expects($this->any())
			->method('getImageSize')
			->with($this->anything())
			->willReturn(array(
				'width'		=> 500,
				'height'	=> 500,
				'type'		=> IMAGETYPE_PNG,
			));
		$image_helper = $this->getMock('\phpbb\attachment\image_helper', array('get_supported_image_types'));
		$image_helper->expects($this->any())
		->method('get_supported_image_types')
		->with($this->anything())
		->willReturn(array('gd' => false));

		$resize_mock = new \phpbb\attachment\resize($this->filesystem, $image_helper, $this->php_ini, $this->image_size);

		$this->assertSame(false, $resize_mock->create($this->phpbb_root_path . '../tests/upload/fixture/png', $this->phpbb_root_path . '../tests/upload/fixture/meh', 'image/png'));

		/** @var \phpbb\attachment\resize $thumbnail_mock */
		$resize_mock = $this->getMock(
			'\phpbb\attachment\resize',
			array('create_gd'),
			array(
				$this->filesystem,
				$this->image_helper,
				$this->php_ini,
				$this->image_size
			)
		);

		// Pretend create_gd fails creating the file but returns true
		$resize_mock->expects($this->any())
			->method('create_gd')
			->with()
			->willReturn(true);

		$this->assertSame(false, $resize_mock->create($this->phpbb_root_path . '../tests/upload/fixture/png', $this->phpbb_root_path . '../tests/upload/fixture/meh', 'image/png'));

		$this->image_size = new \FastImageSize\FastImageSize();
	}

	public function data_get_supported_image_types()
	{
		return array(
			array(-1, array(
				'gd'		=> false,
				'format'	=> 0,
				'version'	=> (function_exists('imagecreatetruecolor')) ? 2 : 1,
			)),
			array(false, array(
				'gd'		=> true,
				'format'	=> array(
					IMG_GIF,
					IMG_JPG,
					IMG_PNG,
					IMG_WBMP,
				),
				'version'	=> (function_exists('imagecreatetruecolor')) ? 2 : 1,
			)),
			array(IMAGETYPE_GIF, array(
				'gd'		=> true,
				'format'	=> IMG_GIF,
				'version'	=> (function_exists('imagecreatetruecolor')) ? 2 : 1,
			)),
			array(IMAGETYPE_JPEG, array(
				'gd'		=> true,
				'format'	=> IMG_JPEG,
				'version'	=> (function_exists('imagecreatetruecolor')) ? 2 : 1,
			)),
			array(IMAGETYPE_WBMP, array(
				'gd'		=> true,
				'format'	=> IMG_WBMP,
				'version'	=> (function_exists('imagecreatetruecolor')) ? 2 : 1,
			)),
		);
	}

	/**
	 * @dataProvider data_get_supported_image_types
	 */
	public function test_get_supported_image_types($input, $expected)
	{
		$this->assertSame($expected, $this->image_helper->get_supported_image_types($input));
	}

	public function data_create_gd()
	{
		return array(
			array('image/iff', IMAGETYPE_IFF, 'png', false),
			array('image/gif', IMAGETYPE_GIF, 'png', false),
			array('image/gif', IMAGETYPE_GIF, 'gif', true),
			array('image/jpg', IMAGETYPE_JPEG, 'jpg', true),
			array('image/wbmp', IMAGETYPE_WBMP, 'wbmp', true),
		);
	}

	/**
	 * @dataProvider data_create_gd
	 */
	public function test_create_thumbnail($mimetype, $type, $source, $expected)
	{
		$this->image_size = $this->getMock('\FastImageSize\FastImageSize', array('getImageSize'));
		$this->image_size->expects($this->any())
			->method('getImageSize')
			->with($this->anything())
			->willReturn(array(
				'width'		=> 500,
				'height'	=> 500,
				'type'		=> $type,
			));
		$this->config->set('img_min_thumb_filesize', 0);
		$this->config->set('img_max_thumb_width', 200);
		$this->config->set('img_max_thumb_height', 200);

		$this->get_resize();
		$thumbnail_class = new \phpbb\attachment\thumbnail($this->config, $this->resize);

		$this->assertEquals($expected, $thumbnail_class->create($this->phpbb_root_path . '../tests/upload/fixture/' . $source, $this->phpbb_root_path . '../tests/upload/fixture/meh', $mimetype));

		$this->image_size = new \FastImageSize\FastImageSize();
	}

	/**
	 * @dataProvider data_create_gd
	 */
	public function test_create_gd($mimetype, $type, $source, $expected)
	{
		$this->image_size = $this->getMock('\FastImageSize\FastImageSize', array('getImageSize'));
		$this->image_size->expects($this->any())
			->method('getImageSize')
			->with($this->anything())
			->willReturn(array(
				'width'		=> 500,
				'height'	=> 500,
				'type'		=> $type,
			));
		$this->config->set('img_min_thumb_filesize', 0);
		$this->config->set('img_max_thumb_width', 200);
		$this->config->set('img_max_thumb_height', 200);

		$this->get_resize();

		$this->assertEquals($expected, $this->resize->create($this->phpbb_root_path . '../tests/upload/fixture/' . $source, $this->phpbb_root_path . '../tests/upload/fixture/meh', $mimetype));

		$this->image_size = new \FastImageSize\FastImageSize();
	}

	public function data_create_imagick()
	{
		return array(
			array(true, '/usr/bin'),
			array(true, '/usr/bin/'),
			// will still work as GD exists
			array(true, '/usr/does/not/have/this/dir/that/should/not/exist'),
		);
	}

	/**
	 * @dataProvider data_create_imagick
	 */
	public function test_create_imagick($expected, $imagick_path)
	{
		if (!file_exists('/usr/bin/convert'))
		{
			$this->markTestSkipped('Unable to test imagick if its location is unknown');
		}

		$this->image_size = $this->getMock('\FastImageSize\FastImageSize', array('getImageSize'));
		$this->image_size->expects($this->any())
			->method('getImageSize')
			->with($this->anything())
			->willReturn(array(
				'width'		=> 500,
				'height'	=> 500,
				'type'		=> IMAGETYPE_PNG,
			));
		$this->config->set('img_imagick', $imagick_path);
		$this->config->set('img_max_thumb_width', 200);

		$this->get_resize();

		$this->assertEquals($expected, $this->resize->create($this->phpbb_root_path . '../tests/upload/fixture/png', $this->phpbb_root_path . '../tests/upload/fixture/meh', 'image/png'));

		$this->image_size = new \FastImageSize\FastImageSize();
	}

	public function test_create_thumbnail_imagick()
	{
		$this->image_size = $this->getMock('\FastImageSize\FastImageSize', array('getImageSize'));
		$this->image_size->expects($this->any())
			->method('getImageSize')
			->with($this->anything())
			->willReturn(array(
				'width'		=> 500,
				'height'	=> 500,
				'type'		=> IMAGETYPE_PNG,
			));
		$this->config->set('img_min_thumb_filesize', 0);
		$this->config->set('img_max_thumb_width', 200);
		$this->config->set('img_max_thumb_height', 200);
		$this->config->set('img_imagick', '/usr/bin');

		$this->get_resize();
		$thumbnail_class = new \phpbb\attachment\thumbnail($this->config, $this->resize);

		$this->assertEquals(true, $thumbnail_class->create($this->phpbb_root_path . '../tests/upload/fixture/png', $this->phpbb_root_path . '../tests/upload/fixture/meh', 'image/png'));

		$this->image_size = new \FastImageSize\FastImageSize();

		if (file_exists($this->phpbb_root_path . '../tests/upload/fixture/meh'))
		{
			unlink($this->phpbb_root_path . '../tests/upload/fixture/meh');
		}
	}

	public function data_get_img_size_format()
	{
		return array(
			array(200, 100, 50, 20, array(
				40.0,
				20.0,
			)),
			array(200, 100, 50, '', array(
				50.0,
				25.0,
			)),
			array(200, 100, 50, 40, array(
				50.0,
				25.0,
			)),
			array(200, 100, 50, 25, array(
				50.0,
				25.0,
			)),
		);
	}

	/**
	 * @dataProvider data_get_img_size_format
	 */
	public function test_get_img_size_format($width, $height, $target_width, $target_height, $expected)
	{
		$this->assertSame($expected, $this->image_helper->get_img_size_format($width, $height, $target_width, $target_height));
	}
}
