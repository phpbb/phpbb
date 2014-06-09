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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_install.php';

class phpbb_functions_install_ignore_new_file_on_update_test extends phpbb_test_case
{
	static public function ignore_new_file_on_update_data()
	{
		return array(
			array('willneverexist.php', false),
			array('includes/dirwillneverexist/newfile.php', false),

			array('language/en/email/short/bookmark.txt', false),
			array('language/languagewillneverexist/email/short/bookmark.txt', true),

			array('styles/prosilver/template/bbcode.html', false),
			array('styles/stylewillneverexist/template/bbcode.html', true),

			array('styles/prosilver/theme/en/icon_user_online.gif', false),
			array('styles/prosilver/theme/languagewillneverexist/icon_user_online.gif', true),

			array('styles/prosilver/theme/imageset.css', false),
		);
	}

	/**
	* @dataProvider ignore_new_file_on_update_data
	*/
	public function test_ignore_new_file_on_update($file, $expected)
	{
		global $phpbb_root_path;
		$this->assertEquals($expected, phpbb_ignore_new_file_on_update($phpbb_root_path, $file));
	}
}
