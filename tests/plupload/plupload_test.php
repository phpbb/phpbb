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
				'',
			),
			array(
				130,
				150,
				'resize: {width: 130, height: 150, quality: 100},'
			),
		);
	}

	/**
	* @dataProvider generate_resize_string_data
	*/
	public function test_generate_resize_string($config_width, $config_height, $expected)
	{
		global $phpbb_root_path, $phpEx;

		$lang = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		$config = new \phpbb\config\config(array(
			'img_max_width'		=> $config_width,
			'img_max_height'	=> $config_height,
			'upload_path'		=> 'files',
		));
		$plupload = new \phpbb\plupload\plupload(
			'',
			$config,
			new phpbb_mock_request,
			new \phpbb\user($lang, '\phpbb\datetime'),
			new \phpbb\php\ini,
			new \phpbb\mimetype\guesser(array(new \phpbb\mimetype\extension_guesser))
		);

		$this->assertEquals($expected, $plupload->generate_resize_string());
	}
}
