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
class ui_permission_roles_test extends phpbb_ui_test_case
{

	public function test_permission_roles()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/permissions');
		$this->visit('adm/index.php?i=acp_permissions&mode=setting_forum_local&sid=' . $this->sid);

		// Select forums
		$elements = $this->find_element('cssSelector', 'select#forum')
			->findElements(\Facebook\WebDriver\WebDriverBy::tagName('option'));

		foreach ($elements as $element)
		{
			$element->click();
		}
		$this->find_element('cssSelector', 'form#select_victim')
			->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('input[type=submit]'))
			->click();

		// Select administrators and guests
		$groups_form = $this->find_element('cssSelector', 'form#groups');
		$elements = $groups_form
			->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('select'))
			->findElements(\Facebook\WebDriver\WebDriverBy::tagName('option'));

		foreach ($elements as $element)
		{
			if ($element->getText() === 'Administrators' || $element->getText() === 'Guests')
			{
				$element->click();
			}
		}
		$groups_form->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('input[name=submit_edit_options]'))->click();

		$first_fieldset = $this->find_element('cssSelector', '#perm11');
		$this->assertEquals('none', $first_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('div.dropdown'))->getCSSValue('display'));
		$first_fieldset
			->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('span.dropdown-toggle'))
			->click();
		$this->assertEquals('block', $first_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('div.dropdown'))->getCSSValue('display'));
		$lis = $first_fieldset
			->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector('ul > li'));

		foreach ($lis as $li)
		{
			if ($li->getAttribute('data-id') == 18)
			{
				$li->click();

				break;
			}
		}
		$this->assertEquals('none', $first_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('div.dropdown'))->getCSSValue('display'));
		$this->assertEquals(18, $first_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('input[type=hidden]'))->getAttribute('value'));
		$this->assertEquals($this->lang('ROLE_FORUM_LIMITED'), $first_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('span.dropdown-toggle'))->getText());

		// Check that admin settings didn't get changed
		$second_fieldset = $this->find_element('cssSelector', '#perm10');
		$this->assertEquals('none', $second_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('div.dropdown'))->getCSSValue('display'));
		// Full access = 14
		$this->assertEquals(14, $second_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('input[type=hidden]'))->getAttribute('value'));
		$this->assertEquals($this->lang('ROLE_FORUM_FULL'), $second_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('span.dropdown-toggle'))->getText());

		// Check that category settings were not modified
		$category_fieldset = $this->find_element('cssSelector', '#perm00');
		$this->assertEquals('none', $category_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('div.dropdown'))->getCSSValue('display'));
		// No settings
		$this->assertEquals('', $category_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('input[type=hidden]'))->getAttribute('value'));
		$this->assertEquals($this->lang('NO_ROLE_ASSIGNED'), $category_fieldset->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('span.dropdown-toggle'))->getText());
	}
}
