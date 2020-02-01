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

class phpbb_plupload_test extends phpbb_test_case
{
	public function generate_resize_string_data()
	{
		return array(
			array(
				0,
				0,
				85,
				0,
				'',
			),
			array(
				130,
				150,
				85,
				1,
				'resize: {width: 130, height: 150, quality: 85, preserve_headers: false},'
			),
		);
	}

	/**
	* @dataProvider generate_resize_string_data
	*/
	public function test_generate_resize_string($config_width, $config_height, $config_quality, $config_metadata, $expected)
	{
		global $phpbb_root_path, $phpEx;

		$lang = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		$config = new \phpbb\config\config(array(
			'img_max_width'		=> $config_width,
			'img_max_height'	=> $config_height,
			'img_quality'		=> $config_quality,
			'img_strip_metadata'	=> $config_metadata,
			'upload_path'		=> 'files',
		));
		$plupload = new \phpbb\plupload\plupload(
			'',
			$config,
			new phpbb_mock_request,
			new \phpbb\user($lang, '\phpbb\datetime'),
			new \bantu\IniGetWrapper\IniGetWrapper,
			new \phpbb\mimetype\guesser(array(new \phpbb\mimetype\extension_guesser))
		);

		$this->assertEquals($expected, $plupload->generate_resize_string());
	}

	public function data_get_chunk_size()
	{
		return [
			[[
				'memory_limit' => -1,
				'upload_max_filesize' => 0,
				'post_max_size' => 0,
			], 0],
			[[
				'memory_limit' => -1,
				'upload_max_filesize' => 500,
				'post_max_size' => 400,
			], 200],
			[[
				'memory_limit' => 100,
				'upload_max_filesize' => 0,
				'post_max_size' => 300,
			], 50],
			[[
				'memory_limit' => 300,
				'upload_max_filesize' => 200,
				'post_max_size' => 0,
			], 100],
			[[
				'memory_limit' => 3000,
				'upload_max_filesize' => 800,
				'post_max_size' => 900,
			], 400],
			[[
				'memory_limit' => 2000,
				'upload_max_filesize' => 1000,
				'post_max_size' => 600,
			], 300],
			[[
				'memory_limit' => 1000,
				'upload_max_filesize' => 2000,
				'post_max_size' => 3000,
			], 500],
		];
	}

	/**
	 * @dataProvider data_get_chunk_size
	 */
	public function test_get_chunk_size($limits_ary, $expected)
	{
		global $phpbb_root_path, $phpEx;

		$lang = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$config = new \phpbb\config\config([]);

		$ini_wrapper = $this->getMockBuilder('\bantu\IniGetWrapper\IniGetWrapper')
			->setMethods(['getBytes'])
			->getMock();
		$ini_wrapper->method('getBytes')
			->will($this->returnValueMap([
				['memory_limit', $limits_ary['memory_limit']],
				['upload_max_filesize', $limits_ary['upload_max_filesize']],
				['post_max_size', $limits_ary['post_max_size']]
			]));

		$plupload = new \phpbb\plupload\plupload(
			'',
			$config,
			new phpbb_mock_request,
			new \phpbb\user($lang, '\phpbb\datetime'),
			$ini_wrapper,
			new \phpbb\mimetype\guesser(array(new \phpbb\mimetype\extension_guesser))
		);

		$this->assertEquals($expected, $plupload->get_chunk_size());
	}
}
