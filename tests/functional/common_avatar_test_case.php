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
abstract class phpbb_functional_common_avatar_test_case extends phpbb_functional_test_case
{
	private $path;
	private $form_content;

	abstract function get_url();

	public function setUp()
	{
		parent::setUp();
		$this->path = __DIR__ . '/fixtures/files/';
		$this->login();
		$this->admin_login();
		$this->add_lang(array('acp/board', 'ucp', 'acp/users', 'acp/groups'));
		$this->set_acp_settings();
	}

	private function set_acp_settings()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
		// Check the default entries we should have
		$this->assertContainsLang('ALLOW_GRAVATAR', $crawler->text());
		$this->assertContainsLang('ALLOW_REMOTE_UPLOAD', $crawler->text());
		$this->assertContainsLang('ALLOW_AVATARS', $crawler->text());
		$this->assertContainsLang('ALLOW_LOCAL', $crawler->text());

		// Now start setting the needed settings
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['config[allow_avatar_local]']->select(1);
		$form['config[allow_avatar_gravatar]']->select(1);
		$form['config[allow_avatar_remote]']->select(1);
		$form['config[allow_avatar_remote_upload]']->select(1);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->text());
	}

	public function assert_avatar_submit($expected, $type, $data, $delete = false, $button_text = 'SUBMIT')
	{
		$crawler = self::request('GET', $this->get_url() . '&sid=' . $this->sid);

		// Test if setting a gravatar avatar properly works
		$form = $crawler->selectButton($this->lang($button_text))->form();
		$form['avatar_driver']->select($type);

		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$form[$key]->{$value[0]}($value[1]);
			}
			else
			{
				$form[$key]->setValue($value);
			}
		}

		$crawler = self::submit($form);

		if (is_array($expected))
		{
			$delete_expected = $expected[1];
			$expected = $expected[0];
		}

		try
		{
			$this->assertContainsLang($expected, $crawler->text());
		}
		catch (Exception $e)
		{
			$this->assertContains($expected, $crawler->text());
		}

		if ($delete)
		{
			$form = $crawler->selectButton('confirm')->form();
			$crawler = self::submit($form);
			$this->assertContainsLang($delete_expected, $crawler->text());
		}
	}
}
