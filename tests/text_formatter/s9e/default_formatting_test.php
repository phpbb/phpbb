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
require_once __DIR__ . '/../../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../../phpBB/includes/functions_content.php';

class phpbb_textformatter_s9e_default_formatting_test extends phpbb_test_case
{
	public function test_bbcode_code_lang_is_saved()
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$parser    = $container->get('text_formatter.parser');

		$original = '[code]...[/code][code=php]...[/code]';
		$expected = '<r><CODE><s>[code]</s>...<e>[/code]</e></CODE><CODE lang="php"><s>[code=php]</s>...<e>[/code]</e></CODE></r>';

		$this->assertXmlStringEqualsXmlString($expected, $parser->parse($original));
	}

	/**
	* @dataProvider get_default_formatting_tests
	*/
	public function test_default_formatting($original, $expected, $setup = null)
	{
		$fixture   = __DIR__ . '/fixtures/default_formatting.xml';
		$container = $this->get_test_case_helpers()->set_s9e_services(null, $fixture);

		$parser   = $container->get('text_formatter.parser');
		$renderer = $container->get('text_formatter.renderer');

		if (isset($setup))
		{
			call_user_func($setup, $container);
		}

		$parsed_text = $parser->parse($original);

		$this->assertSame($expected, $renderer->render($parsed_text));
	}

	public function get_default_formatting_tests()
	{
		return array(
			array(
				'[b]bold[/b]',
				'<span style="font-weight: bold">bold</span>'
			),
			array(
				'[u]underlined[/u]',
				'<span style="text-decoration: underline">underlined</span>'
			),
			array(
				'[i]italic[/i]',
				'<span style="font-style: italic">italic</span>'
			),
			array(
				'[color=#FF0000]colored[/color]',
				'<span style="color: #FF0000">colored</span>'
			),
			array(
				'[color=red]colored[/color]',
				'<span style="color: red">colored</span>'
			),
			array(
				'[size=75]smaller[/size]',
				'<span style="font-size: 75%; line-height: normal">smaller</span>'
			),
			array(
				'[quote]quoted[/quote]',
				'<blockquote class="uncited"><div>quoted</div></blockquote>'
			),
			array(
				'[quote="username"]quoted[/quote]',
				'<blockquote><div><cite>username wrote:</cite>quoted</div></blockquote>'
			),
			array(
				'[code]unparsed code[/code]',
				'<div class="codebox"><p>CODE: <a href="#" onclick="selectCode(this); return false;">Select all</a></p><pre><code>unparsed code</code></pre></div>'
			),
			array(
				'[list]no item[/list]',
				'<ul>no item</ul>'
			),
			array(
				'[*]unparsed',
				'[*]unparsed'
			),
			array(
				'[list][*]item[/list]',
				'<ul><li>item</li></ul>'
			),
			array(
				'[list][*]item[/*][/list]',
				'<ul><li>item</li></ul>'
			),
			array(
				'[list=1][*]item[/list]',
				'<ol style="list-style-type: decimal"><li>item</li></ol>'
			),
			array(
				'[list=a][*]item[/list]',
				'<ol style="list-style-type: lower-alpha"><li>item</li></ol>'
			),
			array(
				'[list=i][*]item[/list]',
				'<ol style="list-style-type: lower-roman"><li>item</li></ol>'
			),
			array(
				'[list=I][*]item[/list]',
				'<ol style="list-style-type: upper-roman"><li>item</li></ol>'
			),
			array(
				'[list=disc][*]item[/list]',
				'<ul style="list-style-type: disc"><li>item</li></ul>'
			),
			array(
				'[list=circle][*]item[/list]',
				'<ul style="list-style-type: circle"><li>item</li></ul>'
			),
			array(
				'[list=square][*]item[/list]',
				'<ul style="list-style-type: square"><li>item</li></ul>'
			),
			array(
				'[img]https://area51.phpbb.com/images/area51.png[/img]',
				'<img src="https://area51.phpbb.com/images/area51.png" alt="Image">'
			),
			array(
				'[url]https://area51.phpbb.com/[/url]',
				'<a href="https://area51.phpbb.com/" class="postlink">https://area51.phpbb.com/</a>'
			),
			array(
				'[url=https://area51.phpbb.com/]Area51[/url]',
				'<a href="https://area51.phpbb.com/" class="postlink">Area51</a>'
			),
			array(
				'[email]bbcode-test@phpbb.com[/email]',
				'<a href="mailto:bbcode-test@phpbb.com">bbcode-test@phpbb.com</a>'
			),
			array(
				'[email=bbcode-test@phpbb.com]Email[/email]',
				'<a href="mailto:bbcode-test@phpbb.com">Email</a>'
			),
			array(
				'[attachment=0]filename[/attachment]',
				'<div class="inline-attachment"><!-- ia0 -->filename<!-- ia0 --></div>'
			),
			array(
				// PHPBB3-1401 - correct: parsed
				'[quote="[test]test"]test [ test[/quote]',
				'<blockquote><div><cite>[test]test wrote:</cite>test [ test</div></blockquote>'
			),
			array(
				// PHPBB3-6117 - correct: parsed
				'[quote]test[/quote] test ] and [ test [quote]test[/quote]',
				'<blockquote class="uncited"><div>test</div></blockquote> test ] and [ test <blockquote class="uncited"><div>test</div></blockquote>'
			),
			array(
				// PHPBB3-6200 - correct: parsed
				'[quote="["]test[/quote]',
				'<blockquote><div><cite>[ wrote:</cite>test</div></blockquote>'
			),
			array(
				// PHPBB3-9364 - quoted: "test[/[/b]quote] test" / non-quoted: "[/quote] test" - also failed if layout distorted
				'[quote]test[/[/b]quote] test [/quote][/quote] test',
				'<blockquote class="uncited"><div>test[/[/b]quote] test </div></blockquote>[/quote] test'
			),
			array(
				// PHPBB3-8096 - first quote tag parsed, second quote tag unparsed
				'[quote="a"]a[/quote][quote="a]a[/quote]',
				'<blockquote><div><cite>a wrote:</cite>a</div></blockquote>[quote="a]a[/quote]'
			),
			array(
				// Allow textual bbcodes in textual bbcodes
				'[b]bold [i]bold + italic[/i][/b]',
				'<span style="font-weight: bold">bold <span style="font-style: italic">bold + italic</span></span>'
			),
			array(
				// Allow textual bbcodes in url with description
				'[url=https://area51.phpbb.com/]Area51 [i]italic[/i][/url]',
				'<a href="https://area51.phpbb.com/" class="postlink">Area51 <span style="font-style: italic">italic</span></a>'
			),
			array(
				// Allow url with description in textual bbcodes
				'[i]italic [url=https://area51.phpbb.com/]Area51[/url][/i]',
				'<span style="font-style: italic">italic <a href="https://area51.phpbb.com/" class="postlink">Area51</a></span>'
			),
			array(
				// Do not parse textual bbcodes in code
				'[code]unparsed code [b]bold [i]bold + italic[/i][/b][/code]',
				'<div class="codebox"><p>CODE: <a href="#" onclick="selectCode(this); return false;">Select all</a></p><pre><code>unparsed code [b]bold [i]bold + italic[/i][/b]</code></pre></div>'
			),
			array(
				// Do not parse quote bbcodes in code
				'[code]unparsed code [quote="username"]quoted[/quote][/code]',
				'<div class="codebox"><p>CODE: <a href="#" onclick="selectCode(this); return false;">Select all</a></p><pre><code>unparsed code [quote="username"]quoted[/quote]</code></pre></div>'
			),
			array(
				// Textual bbcode nesting into textual bbcode
				'[b]bold [i]bold + italic[/b] italic[/i]',
				'<span style="font-weight: bold">bold <span style="font-style: italic">bold + italic</span></span><span style="font-style: italic"> italic</span>'
			),
			array(
				"[code]\tline1\n  line2[/code]",
				'<div class="codebox"><p>CODE: <a href="#" onclick="selectCode(this); return false;">Select all</a></p><pre><code>' . "\tline1\n  line2</code></pre></div>"
			),
			array(
				"[code]\n\tline1\n  line2[/code]",
				'<div class="codebox"><p>CODE: <a href="#" onclick="selectCode(this); return false;">Select all</a></p><pre><code>' . "\tline1\n  line2</code></pre></div>"
			),
			array(
				'... http://example.org ...',
				'... <a href="http://example.org" class="postlink">http://example.org</a> ...'
			),
			array(
				'... www.example.org ...',
				'... <a href="http://www.example.org" class="postlink">www.example.org</a> ...'
			),
			array(
				'[quote="[url=http://example.org]xxx[/url]"]...[/quote]',
				'<blockquote><div><cite><a href="http://example.org" class="postlink">xxx</a> wrote:</cite>...</div></blockquote>'
			),
			array(
				'[quote="[url]http://example.org[/url]"]...[/quote]',
				'<blockquote><div><cite><a href="http://example.org" class="postlink">http://example.org</a> wrote:</cite>...</div></blockquote>'
			),
			array(
				'[quote=http://example.org]...[/quote]',
				'<blockquote><div><cite><a href="http://example.org" class="postlink">http://example.org</a> wrote:</cite>...</div></blockquote>'
			),
			array(
				"[quote]\nThis is a long quote that is definitely going to exceed 80 characters\n[/quote]\n\nFollowed by a reply",
				"<blockquote class=\"uncited\"><div>\nThis is a long quote that is definitely going to exceed 80 characters\n</div></blockquote>\n\nFollowed by a reply"
			),
			array(
				'[quote=Username post_id=123]...[/quote]',
				'<blockquote><div><cite>Username wrote: <a href="phpBB/viewtopic.php?p=123#p123" data-post-id="123" onclick="if(document.getElementById(hash.substr(1)))href=hash">↑</a></cite>...</div></blockquote>'
			),
			array(
				// Users are not allowed to submit their own URL for the post
				'[quote="Username" post_url="http://fake.example.org"]...[/quote]',
				'<blockquote><div><cite>Username wrote:</cite>...</div></blockquote>'
			),
			array(
				'[quote=Username time=58705871]...[/quote]',
				'<blockquote><div><cite>Username wrote:<div class="responsive-hide">1971-11-11 11:11:11</div></cite>...</div></blockquote>'
			),
			array(
				'[quote=Username user_id=123]...[/quote]',
				'<blockquote><div><cite><a href="phpBB/memberlist.php?mode=viewprofile&amp;u=123">Username</a> wrote:</cite>...</div></blockquote>'
			),
			array(
				// Users are not allowed to submit their own URL for the profile
				'[quote=Username profile_url=http://fake.example.org]...[/quote]',
				'<blockquote><div><cite>Username wrote:</cite>...</div></blockquote>'
			),
			array(
				// From phpbb_textformatter_s9e_utils_test::test_generate_quote()
				'[quote=\'[quote="foo"]\']...[/quote]',
				'<blockquote><div><cite>[quote="foo"] wrote:</cite>...</div></blockquote>'
			),
			array(
				"Emoji: \xF0\x9F\x98\x80",
				'Emoji: <img alt="' . "\xF0\x9F\x98\x80" . '" class="smilies" draggable="false" width="18" height="18" src="//twemoji.maxcdn.com/36x36/1f600.png">'
			),
			array(
				"Emoji: \xF0\x9F\x98\x80",
				"Emoji: \xF0\x9F\x98\x80",
				function ($container)
				{
					$container->get('text_formatter.renderer')->set_viewsmilies(false);
				}
			),
		);
	}
}
