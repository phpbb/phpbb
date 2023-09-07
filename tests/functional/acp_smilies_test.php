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
class phpbb_functional_acp_smilies_test extends phpbb_functional_test_case
{
	public function test_htmlspecialchars()
	{
		$this->login();
		$this->admin_login();

		// Create the BBCode
		$crawler = self::request('GET', 'adm/index.php?i=acp_icons&sid=' . $this->sid . '&mode=smilies&action=edit&id=1');
		$form = $crawler->selectButton('Submit')->form(array(
			'code[icon_e_biggrin.gif]'    => '>:D',
			'emotion[icon_e_biggrin.gif]' => '>:D'
		));
		self::submit($form);

		// Test it in the "new topic" preview
		$crawler = self::request('GET', 'posting.php?mode=post&f=2&sid=' . $this->sid);
		$form = $crawler->selectButton('Preview')->form(array(
			'subject' => 'subject',
			'message' => '>:D'
		));
		$crawler = self::submit($form);

		$html = $crawler->filter('#preview')->html();
		$this->assertMatchesRegularExpression('(<img [^>]+ alt=">:D" title=">:D"[^>]*>)', $html);
	}
}
