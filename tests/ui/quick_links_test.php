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

/**
* @group ui
*/
class quick_links_test extends phpbb_ui_test_case
{

	public function test_quick_links()
	{
		$this->visit('index.php');
		$this->assertEmpty(self::find_element('className', 'dropdown')->getText());
		self::find_element('className', 'dropdown-toggle')->click();
		$this->assertNotNull(self::find_element('className', 'dropdown')->getText());
	}
}
