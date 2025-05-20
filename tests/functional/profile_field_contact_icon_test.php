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
class phpbb_functional_profile_field_contact_icon_test extends phpbb_functional_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/profile');
	}

	public function test_add_contact_field_icon()
	{
		// Custom profile fields page
		$crawler = self::request('GET', 'adm/index.php?i=acp_profile&mode=profile&sid=' . $this->sid);

		// Get any contact profile field, f.e. phpbb_twitter
		$twitter_field = $crawler->filter('tbody tr')
			->reduce(
				function ($node, $i) {
					$text = $node->text();
					return ((bool) strpos($text, 'phpbb_twitter'));
			});

		$twitter_edit_url = $twitter_field->filter('.actions a')->eq(2)->attr('href');

		$crawler = self::request('GET', 'adm/' . $twitter_edit_url . '&sid=' . $this->sid);

		$this->assertStringContainsString('phpbb_twitter', $crawler->text());

		$form = $crawler->selectButton('Profile type specific options')->form([
			'field_icon'		=> 'twitter',
			'field_icon_color'	=> '1da1f2',
		]);
		$crawler= self::submit($form);

		$this->assertStringContainsString('Profile type specific options', $crawler->text());

		$form = $crawler->selectButton('Save')->form();
		$crawler= self::submit($form);
		$this->assertContainsLang('CHANGED_PROFILE_FIELD', $crawler->text());

		// Ensure contact filed icon was saved correctly
		$crawler = self::request('GET', 'adm/' . $twitter_edit_url . '&sid=' . $this->sid);
		$this->assertEquals('twitter', $crawler->filter('#field_icon')->attr('value'));
		$this->assertEquals('1da1f2', $crawler->filter('#contact_field_icon_bgcolor')->attr('value'));
		$this->assertEquals(1, $crawler->filter('i.fa-twitter')->count());
		$this->assertStringContainsString('#1da1f2;', $crawler->filter('i.fa-twitter')->attr('style'));
	}
}
