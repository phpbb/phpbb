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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/bbcode.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/message_parser.php';

class phpbb_bbcode_parser_test extends \phpbb_test_case
{
	public function bbcode_firstpass_data()
	{
		return array(
			// Default bbcodes from in their simplest way
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
				'Test default bbcodes: simple size',
				'[size=75]smaller[/size]',
				'[size=75:]smaller[/size:]',
			),
			array(
				'Test default bbcodes: simple quote',
				'[quote]quoted[/quote]',
				'[quote:]quoted[/quote:]',
			),
			array(
				'Test default bbcodes: simple quote with username',
				'[quote=&quot;username&quot;]quoted[/quote]',
				'[quote=&quot;username&quot;:]quoted[/quote:]',
			),
			array(
				'Test default bbcodes: simple code',
				'[code]unparsed code[/code]',
				'[code:]unparsed code[/code:]',
			),
			array(
				'Test default bbcodes: simple php code',
				'[code=php]unparsed code[/code]',
				'[code=php:]<span class="syntaxdefault">unparsed&nbsp;code</span>[/code:]',
			),
			array(
				'Test default bbcodes: simple list',
				'[list]no item[/list]',
				'[list:]no item[/list:u:]',
			),
			array(
				'Test default bbcodes: simple list-item only',
				'[*]unparsed',
				'[*]unparsed',
			),
			array(
				'Test default bbcodes: simple list-item',
				'[list][*]item[/list]',
				'[list:][*:]item[/*:m:][/list:u:]',
			),
			array(
				'Test default bbcodes: simple list-item closed',
				'[list][*]item[/*][/list]',
				'[list:][*:]item[/*:][/list:u:]',
			),
			array(
				'Test default bbcodes: simple list-item numbered',
				'[list=1][*]item[/list]',
				'[list=1:][*:]item[/*:m:][/list:o:]',
			),
			array(
				'Test default bbcodes: simple list-item alpha',
				'[list=a][*]item[/list]',
				'[list=a:][*:]item[/*:m:][/list:o:]',
			),
			array(
				'Test default bbcodes: simple list-item roman',
				'[list=i][*]item[/list]',
				'[list=i:][*:]item[/*:m:][/list:o:]',
			),
			array(
				'Test default bbcodes: simple list-item disc',
				'[list=disc][*]item[/list]',
				'[list=disc:][*:]item[/*:m:][/list:u:]',
			),
			array(
				'Test default bbcodes: simple list-item circle',
				'[list=circle][*]item[/list]',
				'[list=circle:][*:]item[/*:m:][/list:u:]',
			),
			array(
				'Test default bbcodes: simple list-item square',
				'[list=square][*]item[/list]',
				'[list=square:][*:]item[/*:m:][/list:u:]',
			),
			array(
				'Test default bbcodes: simple img',
				'[img]https://area51.phpbb.com/images/area51.png[/img]',
				'[img:]https&#58;//area51&#46;phpbb&#46;com/images/area51&#46;png[/img:]',
			),
			array(
				'Test default bbcodes: simple url',
				'[url]https://area51.phpbb.com/[/url]',
				'[url:]https&#58;//area51&#46;phpbb&#46;com/[/url:]',
			),
			array(
				'Test default bbcodes: simple url with description',
				'[url=https://area51.phpbb.com/]Area51[/url]',
				'[url=https&#58;//area51&#46;phpbb&#46;com/:]Area51[/url:]',
			),
			array(
				'Test default bbcodes: simple email',
				'[email]bbcode-test@phpbb.com[/email]',
				'[email:]bbcode-test@phpbb&#46;com[/email:]',
			),
			array(
				'Test default bbcodes: simple email with description',
				'[email=bbcode-test@phpbb.com]Email[/email]',
				'[email=bbcode-test@phpbb&#46;com:]Email[/email:]',
			),
			array(
				'Test default bbcodes: simple attachment',
				'[attachment=0]filename[/attachment]',
				'[attachment=0:]<!-- ia0 -->filename<!-- ia0 -->[/attachment:]',
			),

			// Special cases for quote which were reported as bugs before
			array(
				'PHPBB3-1401 - correct: parsed',
				'[quote=&quot;&#91;test]test&quot;]test [ test[/quote]',
				'[quote=&quot;&#91;test]test&quot;:]test [ test[/quote:]',
			),
			array(
				'PHPBB3-6117 - correct: parsed',
				'[quote]test[/quote] test ] and [ test [quote]test[/quote]',
				'[quote:]test[/quote:] test ] and [ test [quote:]test[/quote:]',
			),
			array(
				'PHPBB3-6200 - correct: parsed',
				'[quote=&quot;[&quot;]test[/quote]',
				'[quote=&quot;&#91;&quot;:]test[/quote:]',
			),
			array(
				'PHPBB3-9364 - quoted: "test[/[/b]quote] test" / non-quoted: "[/quote] test" - also failed if layout distorted',
				'[quote]test[/[/b]quote] test [/quote][/quote] test',
				'[quote:]test[/[/b]quote] test [/quote:][/quote] test',
			),
			array(
				'PHPBB3-8096 - first quote tag parsed, second quote tag unparsed',
				'[quote=&quot;a&quot;]a[/quote][quote=&quot;a]a[/quote]',
				'[quote=&quot;a&quot;:]a[/quote:][quote=&quot;a]a[/quote]',
			),

			// Simple bbcodes nesting
			array(
				'Allow textual bbcodes in textual bbcodes',
				'[b]bold [i]bold + italic[/i][/b]',
				'[b:]bold [i:]bold + italic[/i:][/b:]',
			),
			array(
				'Allow textual bbcodes in url with description',
				'[url=https://area51.phpbb.com/]Area51 [i]italic[/i][/url]',
				'[url=https&#58;//area51&#46;phpbb&#46;com/:]Area51 [i:]italic[/i:][/url:]',
			),
			array(
				'Allow url with description in textual bbcodes',
				'[i]italic [url=https://area51.phpbb.com/]Area51[/url][/i]',
				'[i:]italic [url=https&#58;//area51&#46;phpbb&#46;com/:]Area51[/url:][/i:]',
			),

			// Nesting bbcodes into quote usernames
			array(
				'Allow textual bbcodes in usernames',
				'[quote=&quot;[i]test[/i]&quot;]test[/quote]',
				'[quote=&quot;[i:]test[/i:]&quot;:]test[/quote:]',
			),
			array(
				'Allow links bbcodes in usernames',
				'[quote=&quot;[url=https://area51.phpbb.com/]test[/url]&quot;]test[/quote]',
				'[quote=&quot;[url=https&#58;//area51&#46;phpbb&#46;com/:]test[/url:]&quot;:]test[/quote:]',
			),
			array(
				'Allow img bbcodes in usernames - Username displayed the image',
				'[quote=&quot;[img]https://area51.phpbb.com/images/area51.png[/img]&quot;]test[/quote]',
				'[quote=&quot;[img:]https&#58;//area51&#46;phpbb&#46;com/images/area51&#46;png[/img:]&quot;:]test[/quote:]',
			),
			array(
				'Disallow flash bbcodes in usernames - Username displayed as [flash]http://www.phpbb.com/[/flash]',
				'[quote=&quot;[flash]http://www.phpbb.com/[/flash]&quot;]test[/quote]',
				'[quote=&quot;&#91;flash]http://www.phpbb.com/&#91;/flash]&quot;:]test[/quote:]',
			),
			array(
				'Disallow quote bbcodes in usernames - Username displayed as [quote]test[/quote]',
				'[quote=&quot;[quote]test[/quote]&quot;]test[/quote]',
				'[quote=&quot;&#91;quote]test&#91;/quote]&quot;:]test[/quote:]',
			),

			// Do not parse bbcodes in code boxes
			array(
				'Do not parse textual bbcodes in code',
				'[code]unparsed code [b]bold [i]bold + italic[/i][/b][/code]',
				'[code:]unparsed code &#91;b&#93;bold &#91;i&#93;bold + italic&#91;/i&#93;&#91;/b&#93;[/code:]',
			),
			array(
				'Do not parse quote bbcodes in code',
				'[code]unparsed code [quote=&quot;username&quot;]quoted[/quote][/code]',
				'[code:]unparsed code &#91;quote=&quot;username&quot;&#93;quoted&#91;/quote&#93;[/code:]',
			),

			// New user friendly mixed nesting
			array(
				'Textual bbcode nesting into textual bbcode',
				'[b]bold [i]bold + italic[/b] italic[/i]',
				'[b:]bold [i:]bold + italic[/b:] italic[/i:]',
				'Incomplete test case: secondpass parses as [b:]bold [i:]bold + italic[/i:] italic[/b:]',
			),
		);
	}

	/**
	* @dataProvider bbcode_firstpass_data
	*/
	public function test_bbcode_firstpass($description, $message, $expected, $incomplete = false)
	{
		if ($incomplete)
		{
			$this->markTestIncomplete($incomplete);
		}

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
