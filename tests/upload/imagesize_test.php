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
class phpbb_upload_imagesize_test extends \phpbb_test_case
{
	/** @var \phpbb\upload\imagesize */
	protected $imagesize;

	/** @var string Path to fixtures */
	protected $path;

	public function setUp()
	{
		parent::setUp();
		$this->imagesize = new \phpbb\upload\imagesize();
		$this->path = __DIR__ . '/fixture/';
	}

	public function data_get_imagesize()
	{
		return array(
			array('foobar', 'image/bmp', false),
			array('png', 'image/png', array('width' => 1, 'height' => 1, 'mime' => IMAGETYPE_PNG)),
			array('gif', 'image/png', false),
			array('png', '', false),
			array('gif', 'image/gif', array('width' => 1, 'height' => 1, 'mime' => IMAGETYPE_GIF)),
			array('jpg', 'image/gif', false),
			array('gif', '', false),
			array('jpg', 'image/jpg', array('width' => 1, 'height' => 1, 'mime' => IMAGETYPE_JPEG)),
			array('jpg', 'image/jpeg', array('width' => 1, 'height' => 1, 'mime' => IMAGETYPE_JPEG)),
			array('png', 'image/jpg', false),
			array('jpg', '', false),
			array('psd', 'image/psd', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_PSD)),
			array('psd', 'image/photoshop', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_PSD)),
			array('jpg', 'image/psd', false),
			array('psd', '', false),
			array('bmp', 'image/bmp', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_BMP)),
			array('png', 'image/bmp', false),
			array('bmp', '', false),
			array('tif', 'image/tif', array('width' => 1, 'height' => 1, 'mime' => IMAGETYPE_TIFF_II)),
			array('png', 'image/tif', false),
			array('tif', '', false),
			array('tif_compressed', 'image/tif', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_TIFF_II)),
			array('png', 'image/tiff', false),
			array('tif_compressed', '', false),
			array('tif_msb', 'image/tif', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_TIFF_MM)),
			array('tif_msb', '', false),
			array('wbmp', 'image/wbmp', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_WBMP)),
			array('wbmp', 'image/vnd.wap.wbmp', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_WBMP)),
			array('png', 'image/vnd.wap.wbmp', false),
			array('wbmp', '', false),
			array('iff', 'image/iff', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_IFF)),
			array('iff', 'image/x-iff', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_IFF)),
			array('iff_maya', 'iamge/iff', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_IFF)),
			array('png', 'image/iff', false),
			array('png', 'image/x-iff', false),
			array('iff', '', false),
			array('iff_maya', '', false),
			array('jp2', 'image/jp2', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_JPEG2000)),
			array('jp2', 'image/jpx', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_JPEG2000)),
			array('jp2', 'image/jpm', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_JPEG2000)),
			array('jpg', 'image/jp2', false),
			array('jpx', 'image/jpx', array('width' => 2, 'height' => 1, 'mime' => IMAGETYPE_JPEG2000)),
			array('jp2', '', false),
			array('jpx', '', false),
		);
	}

	/**
	 * @dataProvider data_get_imagesize
	 */
	public function test_get_imagesize($file, $mime_type, $expected)
	{
		$this->assertEquals($expected, $this->imagesize->get_imagesize($this->path . $file, $mime_type));
	}
}
