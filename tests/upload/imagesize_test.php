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
			array('png', 'image/png', array('width' => 1, 'height' => 1)),
			array('gif', 'image/png', false),
			array('png', '', false),
			array('gif', 'image/gif', array('width' => 1, 'height' => 1)),
			array('jpg', 'image/gif', false),
			array('gif', '', false),
			array('jpg', 'image/jpg', array('width' => 1, 'height' => 1)),
			array('jpg', 'image/jpeg', array('width' => 1, 'height' => 1)),
			array('png', 'image/jpg', false),
			array('jpg', '', false),
			array('psd', 'image/psd', array('width' => 2, 'height' => 1)),
			array('psd', 'image/photoshop', array('width' => 2, 'height' => 1)),
			array('jpg', 'image/psd', false),
			array('psd', '', false),
			array('bmp', 'image/bmp', array('width' => 2, 'height' => 1)),
			array('png', 'image/bmp', false),
			array('bmp', '', false),
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
