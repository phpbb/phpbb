<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_bbcode_parser_test extends PHPUnit_Framework_TestCase
{
	public function string_bbcode_data()
	{
		return array(
			// Default BBCodes from in their simplest way
			array(
				'Test default bbcodes: simple bold',
				'[b]bold[/b]',
				'[b:]bold[/b:]',
			),
			array(
				'Test default bbcodes: simple underlined',
				'[u]underlined[/u]',
				'[u:]underlined[/u:]',
			),
			array(
				'Test default bbcodes: simple italic',
				'[i]italic[/i]',
				'[i:]italic[/i:]',
			),
			array(
				'Test default bbcodes: simple color rgb',
				'[color=#FF0000]colored[/color]',
				'[color=#FF0000:]colored[/color:]',
			),
			array(
				'Test default bbcodes: simple color name',
				'[color=red]colored[/color]',
				'[color=red:]colored[/color:]',
			),
			array(
				'Test default bbcodes: simple quote',
				'[quote]quoted[/quote]',
				'[quote:]quoted[/quote:]',
			),
			array(
				'Test default bbcodes: simple quote with username',
				'[quote="username"]quoted[/quote]',
				'[quote="username":]quoted[/quote:]',
			),
			array(
				'Test default bbcodes: simple code',
				'[code]unparsed code[/code]',
				'[code:]unparsed code[/code:]',
			),
			array(
				'Test default bbcodes: simple php code',
				'[code=php]unparsed code[/code]',
				'[code=php:]unparsed code[/code:]',
			),
			array(
				'Test default bbcodes: simple list',
				'[list]no item[/list]',
				'[list:]no item[/list:]',
			),
			array(
				'Test default bbcodes: simple list-item only',
				'[*]unparsed',
				'[*]unparsed',
			),
			array(
				'Test default bbcodes: simple list-item',
				'[list][*]item[/list]',
				'[list:][*:]item[/list:]',
			),
			array(
				'Test default bbcodes: simple list-item closed',
				'[list][*]item[/*][/list]',
				'[list:][*:]item[/*:][/list:]',
			),
			array(
				'Test default bbcodes: simple list-item numbered',
				'[list=1][*]item[/list]',
				'[list=1:][*:]item[/list:]',
			),
			array(
				'Test default bbcodes: simple list-item alpha',
				'[list=a][*]item[/list]',
				'[list=a:][*:]item[/list:]',
			),
			array(
				'Test default bbcodes: simple list-item roman',
				'[list=i][*]item[/list]',
				'[list=i:][*:]item[/list:]',
			),
			array(
				'Test default bbcodes: simple list-item disc',
				'[list=disc][*]item[/list]',
				'[list=disc:][*:]item[/list:]',
			),
			array(
				'Test default bbcodes: simple list-item circle',
				'[list=circle][*]item[/list]',
				'[list=circle:][*:]item[/list:]',
			),
			array(
				'Test default bbcodes: simple list-item square',
				'[list=square][*]item[/list]',
				'[list=square:][*:]item[/list:]',
			),
			array(
				'Test default bbcodes: simple img',
				'[img]https://area51.phpbb.com/images/area51.png[/img]',
				'[img:]https://area51.phpbb.com/images/area51.png[/img:]',
			),
			array(
				'Test default bbcodes: simple url',
				'[url]https://area51.phpbb.com/[/url]',
				'[url:]https://area51.phpbb.com/[/url:]',
			),
			array(
				'Test default bbcodes: simple url with description',
				'[url=https://area51.phpbb.com/]Area51[/url]',
				'[url=https://area51.phpbb.com/:]Area51[/url:]',
			),
			array(
				'Test default bbcodes: simple email',
				'[email]bbcode-test@phpbb.com[/email]',
				'[email:]bbcode-test@phpbb.com[/email:]',
			),
			array(
				'Test default bbcodes: simple email with description',
				'[email=bbcode-test@phpbb.com]Email[/email]',
				'[email=bbcode-test@phpbb.com:]Email[/email:]',
			),

			// Special cases for quote which were reported as bugs before
			array(
				'PHPBB3-1401 - correct: parsed',
				'[quote="[test]test"]test [ test[/quote]',
				'[quote="[test]test":]test [ test[/quote:]',
			),
			array(
				'PHPBB3-6117 - correct: parsed',
				'[quote]test[/quote] test ] and [ test [quote]test[/quote]',
				'[quote:]test[/quote:] test ] and [ test [quote:]test[/quote:]',
			),
			array(
				'PHPBB3-6200 - correct: parsed',
				'[quote="["]test[/quote]',
				'[quote="[":]test[/quote:]',
			),
			array(
				'PHPBB3-9364 - quoted: "test[/[/b]quote] test" / non-quoted: "[/quote] test" - also failed if layout distorted',
				'[quote]test[/[/b]quote] test [/quote][/quote] test',
				'[quote:]test[/[/b]quote] test [/quote:][/quote] test',
			),
			array(
				'PHPBB3-8096 - first quote tag parsed, second quote tag unparsed',
				'[quote="a"]a[/quote][quote="a]a[/quote]',
				'[quote="a":]a[/quote:][quote="a]a[/quote]',
			),

			// Nesting bbcodes into quote usernames
			array(
				'Allow textual BBcodes in usernames',
				'[quote="[i]test[/i]"]test[/quote]',
				'[quote="[i:]test[/i:]":]test[/quote:]',
			),
			array(
				'Allow links BBcodes in usernames',
				'[quote="[url=http://www.phpbb.com/]test[/url]"]test[/quote]',
				'[quote="[url=http://www.phpbb.com/:]test[/url:]":]test[/quote:]',
			),
			array(
				'Disallow img BBcodes in usernames - Username displayed as [img]http://www.phpbb.com/[/img]',
				'[quote="[img]http://www.phpbb.com/[/img]"]test[/quote]',
				'[quote="[img]http://www.phpbb.com/[/img]":]test[/quote:]',
			),
			array(
				'Disallow flash BBcodes in usernames - Username displayed as [flash]http://www.phpbb.com/[/flash]',
				'[quote="[flash]http://www.phpbb.com/[/flash]"]test[/quote]',
				'[quote="[flash]http://www.phpbb.com/[/flash]":]test[/quote:]',
			),
			array(
				'Disallow quote BBcodes in usernames - Username displayed as [quote]test[/quote]',
				'[quote="[quote]test[/quote]"]test[/quote]',
				'[quote="[quote]test[/quote]":]test[/quote:]',
			),
		);
	}


	/**
	* @dataProvider string_bbcode_data
	*/
	public function test_firstpass($description, $message, $expected)
	{
		$this->markTestIncomplete('New bbcode parser has not been backported from feature/ascraeus-experiment yet.');

		global $user, $request;
		$user = new phpbb_mock_user;
		$request = new phpbb_mock_request;

		$bbcode = new bbcode_firstpass();
		$bbcode->message = $message;
		$bbcode->bbcode_init(false);
		$bbcode->parse_bbcode();
		$this->assertEquals($expected, $bbcode->message);
	}
}
