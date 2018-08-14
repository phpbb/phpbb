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
* @group functional
*/
class phpbb_functional_forgot_username_test extends phpbb_functional_test_case
{
	public function test_forgot_username()
	{
		global $config;
		$this->add_lang('ucp');
		$crawler = self::request('GET', 'ucp.php?mode=remind_username');
		$this->assertEquals($this->lang('REMIND_USERNAME'), $crawler->filter('h2')->text());
	}
}
