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
class phpbb_functional_jumpbox_test extends phpbb_functional_test_case
{
	public function test_jumpbox()
	{
		$this->login();

		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$form = $crawler->filter('#quickmodform')->selectButton($this->lang('GO'))->form(array(
			'action'	=> 'merge_topic',
		));

		$crawler = self::submit($form);
		$this->assertContains($this->lang('FORUM') . ': Your first forum', $crawler->filter('#cp-main h2')->text());
		$form = $crawler->filter('#jumpbox')->selectButton($this->lang('GO'))->form(array(
			'f'	=> 1,
		));

		$crawler = self::submit($form);
		$this->assertContains($this->lang('FORUM') . ': Your first category', $crawler->filter('#cp-main h2')->text());
	}
}
