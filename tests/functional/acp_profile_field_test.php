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
class phpbb_functional_acp_profile_field_test extends phpbb_functional_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/profile');
	}

	public function data_add_profile_field()
	{
		return array(
			array('profilefields.type.bool',
				array(
					'field_ident'		=> 'bool',
					'lang_name'			=> 'bool',
					'lang_options[0]'	=> 'foo',
					'lang_options[1]'	=> 'bar',
				),
			),
			array('profilefields.type.dropdown',
				array(
					'field_ident'	=> 'dropdown',
					'lang_name'		=> 'dropdown',
					'lang_options'	=> "foo\nbar\nbar\nfoo",
				),
			),
		);
	}

	/**
	 * @dataProvider data_add_profile_field
	 */
	public function test_add_profile_field($type, $page1_settings)
	{
		// Custom profile fields page
		$crawler = self::request('GET', 'adm/index.php?i=acp_profile&mode=profile&sid=' . $this->sid);
		// these language strings are html
		$form = $crawler->selectButton('Create new field')->form(array(
			'field_type'	=> $type,
		));
		$crawler = self::submit($form);

		// Fill form for profile field options
		$form = $crawler->selectButton('Profile type specific options')->form($page1_settings);
		$crawler = self::submit($form);

		// Fill form for profile field specific options
		$form = $crawler->selectButton('Save')->form();
		$crawler= self::submit($form);

		$this->assertContainsLang('ADDED_PROFILE_FIELD', $crawler->text());
	}
}
