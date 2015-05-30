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
	public function data_imagick_path_linux()
	{
		return array(
			array('/usr/bin', 'Configuration updated successfully'),
			array('/usr/foobar', 'The entered path “/usr/foobar” does not exist.'),
			array('/usr/bin/which', 'The entered path “/usr/bin/which” is not a directory.'),
		);
	}

	/**
	 * @dataProvider data_imagick_path_linux
	 */
	public function test_imagick_path_linux($imagick_path, $expected)
	{
		if (strtolower(substr(PHP_OS, 0, 5)) !== 'linux')
		{
			$this->markTestSkipped('Unable to test linux specific paths on other OS.');
		}

		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=attachments&mode=attach&sid=' . $this->sid);

		$form = $crawler->selectButton('Submit')->form(array('config[img_imagick]'	=> $imagick_path));

		$crawler = self::submit($form);
		$this->assertContains($expected, $crawler->filter('#main')->text());
	}

	public function data_imagick_path_windows()
	{
		return array(
			array('C:\Windows', 'Configuration updated successfully'),
			array('C:\Windows\foobar1', 'The entered path “C:\Windows\foobar1” does not exist.'),
			array('C:\Windows\explorer.exe', 'The entered path “C:\Windows\explorer.exe” is not a directory.'),
		);
	}

	/**
	 * @dataProvider data_imagick_path_windows
	 */
	public function test_imagick_path_windows($imagick_path, $expected)
	{
		if (strtolower(substr(PHP_OS, 0, 3)) !== 'win')
		{
			$this->markTestSkipped('Unable to test windows specific paths on other OS.');
		}

		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=attachments&mode=attach&sid=' . $this->sid);

		$form = $crawler->selectButton('Submit')->form(array('config[img_imagick]'	=> $imagick_path));

		$crawler = self::submit($form);
		$this->assertContains($expected, $crawler->filter('#main')->text());
	}
}
