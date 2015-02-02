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
class phpbb_functional_acp_attachments_test extends phpbb_functional_test_case
{
	public function data_imagick_path()
	{
		return array(
			array('/usr/bin', 'Configuration updated successfully'),
			array('/usr/bin/', 'Configuration updated successfully'),
			array('/usr/nope', 'The entered path “/usr/nope” does not exist.'),
			array('mkdir /usr/test', 'The entered path “mkdir /usr/test” does not exist.'),
		);
	}

	/**
	 * @dataProvider data_imagick_path
	 */
	public function test_imagick_path($imagick_path, $expected)
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=attachments&mode=attach&sid=' . $this->sid);

		$form = $crawler->selectButton('Submit')->form(array('config[img_imagick]'	=> $imagick_path));

		$crawler = self::submit($form);
		$this->assertContains($expected, $crawler->text());
	}
}
