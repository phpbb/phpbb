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
class functional_permission_roles_test extends phpbb_functional_test_case
{
	public function data_permission_roles()
	{
		return array(
			array(
				array(0, 14),
				array(17, 17),
				array(
					'role[5][1]'	=> 14,
				)
			),
			array(
				array(14, 14),
				array(17, 17),
				array(
					'role[5][1]'	=> 0,
				)
			),
			array(
				array(0, 14),
				array(17, 17)
			),
		);
	}
	/**
	 * @dataProvider data_permission_roles
	 */
	public function test_permission_roles($admin_roles, $guest_roles, $set_values = array())
	{
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/permissions');
		$crawler = self::request('GET', 'adm/index.php?i=acp_permissions&mode=setting_forum_local&sid=' . $this->sid);

		// Select forums
		$form = $crawler->filter('form[id=select_victim]')->form();
		$form['forum_id']->setValue(array(1,2));
		$crawler = self::$client->submit($form);

		// Select administrators and guests
		$groups_form = $crawler->filter('form[id=groups]')->form();
		$groups_form['group_id']->setValue(array(1,5));

		$crawler = self::submit($groups_form);
		$form = $crawler->filter('form')->form();
		$values = $form->getValues();

		// Check default settings
		$this->assertEquals($admin_roles[0], $values['role[5][1]']);
		$this->assertEquals($admin_roles[1], $values['role[5][2]']);
		$this->assertEquals($guest_roles[0], $values['role[1][1]']);
		$this->assertEquals($guest_roles[1], $values['role[1][2]']);

		// Set admin to full access on category
		foreach ($set_values as $key => $value)
		{
			$form[$key]->setValue($value);
		}

		$form_values = $form->getValues();
		$form_values['action[apply_all_permissions]'] = true;
		$crawler = self::request('POST', 'adm/index.php?i=acp_permissions&mode=setting_forum_local&sid=' . $this->sid, $form_values);
		$this->assertContainsLang('AUTH_UPDATED', $crawler->text());

		$this->logout();
	}
}
