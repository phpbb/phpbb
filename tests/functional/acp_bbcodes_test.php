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
class phpbb_functional_acp_bbcodes_test extends phpbb_functional_test_case
{
	public function test_htmlspecialchars()
	{
		$this->login();
		$this->admin_login();

		// Create the BBCode
		$crawler = self::request('GET', 'adm/index.php?i=acp_bbcodes&sid=' . $this->sid . '&mode=bbcodes&action=add');
		$form = $crawler->selectButton('Submit')->form(array(
			'bbcode_match' => '[mod="{TEXT1}"]{TEXT2}[/mod]',
			'bbcode_tpl'   => '<div>{TEXT1}</div><div>{TEXT2}</div>'
		));
		self::submit($form);

		// Test it in the "new topic" preview
		$crawler = self::request('GET', 'posting.php?mode=post&f=2&sid=' . $this->sid);
		$form = $crawler->selectButton('Preview')->form(array(
			'subject' => 'subject',
			'message' => '[mod=a]b[/mod][mod="c"]d[/mod]'
		));
		$crawler = self::submit($form);

		$html = $crawler->filter('#preview')->html();
		$this->assertStringContainsString('<div>a</div>', $html);
		$this->assertStringContainsString('<div>b</div>', $html);
		$this->assertStringContainsString('<div>c</div>', $html);
		$this->assertStringContainsString('<div>d</div>', $html);
	}

	/**
	* @dataProvider get_bbcode_error_tests
	*/
	public function test_bbcode_error($match, $tpl, $error)
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_bbcodes&sid=' . $this->sid . '&mode=bbcodes&action=add');
		$form = $crawler->selectButton('Submit')->form([
			'bbcode_match' => $match,
			'bbcode_tpl'   => $tpl
		]);
		$crawler = self::submit($form);

		$text = $crawler->filter('.errorbox')->text();
		$this->assertStringContainsString($error, $text);
	}

	public function get_bbcode_error_tests()
	{
		return [
			[
				'XXX',
				'',
				'BBCode is constructed in an invalid form'
			],
			[
				'[x]{TEXT}[/x]',
				'<xsl:invalid',
				'template is invalid'
			],
			[
				'[x]{TEXT}[/x]',
				'<script>{TEXT}</script>',
				'unsafe'
			],
		];
	}
}
