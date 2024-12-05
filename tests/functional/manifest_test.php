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
class phpbb_functional_manifest_test extends phpbb_functional_test_case
{
	public function test_manifest()
	{
		$url_path = preg_replace('#^(/.+)/$#', '$1', parse_url(self::$root_url, PHP_URL_PATH));

		$expected = [
			'name'			=> 'yourdomain.com',
			'short_name'	=> 'yourdomain',
			'display'		=> 'standalone',
			'orientation'	=> 'portrait',
			'dir'			=> 'ltr',
			'start_url'		=> $url_path,
			'scope'			=> $url_path,
		];

		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=settings&sid=' . $this->sid);

		$form_data = [
			'config[sitename]'		=> $expected['name'],
			'config[sitename_short]'	=> $expected['short_name'],
		];
		$form = $crawler->selectButton('submit')->form($form_data);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('CONFIG_UPDATED'), $crawler->filter('.successbox')->text());

		self::request('GET', 'app.php/manifest', [], false);
		$this->assertEquals(json_encode($expected), self::get_content());
	}
}
