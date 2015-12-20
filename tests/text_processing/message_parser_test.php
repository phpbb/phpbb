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

require_once __DIR__ . '/../../phpBB/includes/bbcode.php';
require_once __DIR__ . '/../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../phpBB/includes/functions_content.php';
require_once __DIR__ . '/../../phpBB/includes/message_parser.php';
require_once __DIR__ . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_text_processing_message_parser_test extends phpbb_test_case
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		// Set up an intercepting proxy for getimagesize() calls
		stream_wrapper_unregister('http');
		stream_wrapper_register('http', __CLASS__ . '_proxy');
	}

	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
		stream_wrapper_restore('http');
	}

	protected function prepare_s9e_services($setup = null)
	{
		global $config, $phpbb_container, $user;

		$config = new \phpbb\config\config(array('max_poll_options' => 999));

		$map = array(
			array('MAX_FLASH_HEIGHT_EXCEEDED', 123, 'Your flash files may only be up to 123 pixels high.'),
			array('MAX_FLASH_WIDTH_EXCEEDED', 456, 'Your flash files may only be up to 456 pixels wide.'),
			array('MAX_FONT_SIZE_EXCEEDED', 120, 'You may only use fonts up to size 120.'),
			array('MAX_FONT_SIZE_EXCEEDED', 200, 'You may only use fonts up to size 200.'),
			array('MAX_IMG_HEIGHT_EXCEEDED', 12, 'Your images may only be up to 12 pixels high.'),
			array('MAX_IMG_WIDTH_EXCEEDED', 34, 'Your images may only be up to 34 pixels wide.'),
			array('TOO_MANY_SMILIES', 3, 'Your message contains too many smilies. The maximum number of smilies allowed is 3.'),
			array('TOO_MANY_URLS', 2, 'Your message contains too many URLs. The maximum number of URLs allowed is 2.'),
			array('UNAUTHORISED_BBCODE', '[flash]', 'You cannot use certain BBCodes: [flash].'),
			array('UNAUTHORISED_BBCODE', '[img]', 'You cannot use certain BBCodes: [img].'),
			array('UNAUTHORISED_BBCODE', '[quote]', 'You cannot use certain BBCodes: [quote].'),
			array('UNAUTHORISED_BBCODE', '[url]', 'You cannot use certain BBCodes: [url].'),
			array('UNABLE_GET_IMAGE_SIZE', 'It was not possible to determine the dimensions of the image.'),
		);

		$user = $this->getMockBuilder('phpbb\\user')->disableOriginalConstructor()->getMock();
		$user->expects($this->any())
		     ->method('lang')
		     ->will($this->returnValueMap($map));

		$user->data = array(
			'is_bot' => false,
			'is_registered' => true,
			'user_id' => 2,
		);
		$user->style = array('style_id' => 1);

		$user->lang = array(
			'NO_POLL_TITLE' => 'You have to enter a poll title.',
			'POLL_TITLE_TOO_LONG' => 'The poll title must contain fewer than 100 characters.',
			'POLL_TITLE_COMP_TOO_LONG' => 'The parsed size of your poll title is too large, consider removing BBCodes or smilies.',
			'TOO_FEW_POLL_OPTIONS' => 'You must enter at least two poll options.',
			'TOO_MANY_POLL_OPTIONS' => 'You have tried to enter too many poll options.',
			'TOO_MANY_USER_OPTIONS' => 'You cannot specify more options per user than existing poll options.',
		);

		$phpbb_container = new phpbb_mock_container_builder;
		$phpbb_container->set('user', $user);
		$phpbb_container->set('config', $config);

		if (isset($setup))
		{
			$setup($phpbb_container, $this);
		}

		$this->get_test_case_helpers()->set_s9e_services($phpbb_container);
	}

	/**
	* @dataProvider get_test_polls
	*/
	public function test_parse_poll($poll, $expected, $warn_msg = array())
	{
		$this->prepare_s9e_services();

		$message_parser = new parse_message('Me[i]s[/i]sage');

		// Add some default values
		$poll += array(
			'poll_length'		=> 123,
			'poll_start'		=> 123,
			'poll_last_vote'	=> 123,
			'poll_vote_change'	=> true,
			'enable_bbcode'		=> true,
			'enable_urls'		=> true,
			'enable_smilies'	=> true,
			'img_status'		=> true
		);

		$message_parser->parse_poll($poll);
		$this->assertSame($expected, array_intersect_key($poll, $expected));

		$this->assertSame(
			'<r>Me<I><s>[i]</s>s<e>[/i]</e></I>sage</r>',
			$message_parser->parse(true, true, true, true, true, true, true, false)
		);

		$this->assertSame($warn_msg, $message_parser->warn_msg);
	}

	public function get_test_polls()
	{
		return array(
			array(
				array(
					'poll_title' => 'foo [b]bar[/b] baz',
					'poll_option_text' => "[i]foo[/i]\nbar\n[i]baz[/i]",
					'poll_max_options'	=> 3,
					'poll_options_size' => 3
				),
				array(
					'poll_title' => '<r>foo <B><s>[b]</s>bar<e>[/b]</e></B> baz</r>',
					'poll_option_text' => "<r><I><s>[i]</s>foo<e>[/i]</e></I></r>\n<t>bar</t>\n<r><I><s>[i]</s>baz<e>[/i]</e></I></r>",
					'poll_options' => array(
						'<r><I><s>[i]</s>foo<e>[/i]</e></I></r>',
						'<t>bar</t>',
						'<r><I><s>[i]</s>baz<e>[/i]</e></I></r>'
					)
				)
			),
			array(
				array(
					'poll_title' => 'xxx',
					'poll_option_text' => "[quote]quote[/quote]\n:)",
					'poll_max_options'	=> 2,
					'poll_options_size' => 2
				),
				array(
					'poll_title' => '<t>xxx</t>',
					'poll_option_text' => "<t>[quote]quote[/quote]</t>\n<r><E>:)</E></r>",
					'poll_options' => array(
						'<t>[quote]quote[/quote]</t>',
						'<r><E>:)</E></r>'
					)
				),
				array('You cannot use certain BBCodes: [quote].')
			),
			array(
				array(
					'poll_title' => 'xxx',
					'poll_option_text' => "[flash=12,34]http://example.org/x.swf[/flash]\n:)",
					'poll_max_options'	=> 2,
					'poll_options_size' => 2
				),
				array(
					'poll_title' => '<t>xxx</t>',
					'poll_option_text' => "<t>[flash=12,34]http://example.org/x.swf[/flash]</t>\n<r><E>:)</E></r>",
					'poll_options' => array(
						'<t>[flash=12,34]http://example.org/x.swf[/flash]</t>',
						'<r><E>:)</E></r>'
					)
				),
				array('You cannot use certain BBCodes: [flash].')
			),
			array(
				array(
					'poll_title' => 'xxx',
					'poll_option_text' => "[b]x\ny[/b]",
					'poll_max_options'	=> 2,
					'poll_options_size' => 2
				),
				array(
					'poll_title' => '<t>xxx</t>',
					'poll_option_text' => "<r><B><s>[b]</s>x</B></r>\n<t>y[/b]</t>",
					'poll_options' => array(
						'<r><B><s>[b]</s>x</B></r>',
						'<t>y[/b]</t>',
					)
				)
			),
		);
	}

	/**
	* @dataProvider get_test_cases
	*/
	public function test_options($original, $expected, array $args, $setup = null, $warn_msg = array())
	{
		$this->prepare_s9e_services($setup);

		$message_parser = new parse_message($original);
		call_user_func_array(array($message_parser, 'parse'), $args);

		$this->assertSame($expected, $message_parser->message);
		$this->assertSame($warn_msg, $message_parser->warn_msg);
	}

	public function get_test_cases()
	{
		return array(
			array(
				'[b]bold[/b]',
				'<r><B><s>[b]</s>bold<e>[/b]</e></B></r>',
				array(true, true, true, true, true, true, true)
			),
			array(
				'[b]bold[/b]',
				'<t>[b]bold[/b]</t>',
				array(false, true, true, true, true, true, true)
			),
			array(
				'http://example.org',
				'<r><URL url="http://example.org">http://example.org</URL></r>',
				array(true, true, true, true, true, true, true)
			),
			array(
				'http://example.org',
				'<t>http://example.org</t>',
				array(true, false, true, true, true, true, true)
			),
			array(
				':)',
				'<r><E>:)</E></r>',
				array(true, true, true, true, true, true, true)
			),
			array(
				':)',
				'<t>:)</t>',
				array(true, true, false, true, true, true, true)
			),
			array(
				'[url=http://example.org][img]http://example.org/img.png[/img][/url]',
				'<r><URL url="http://example.org"><s>[url=http://example.org]</s><IMG src="http://example.org/img.png"><s>[img]</s>http://example.org/img.png<e>[/img]</e></IMG><e>[/url]</e></URL></r>',
				array(true, true, true, true, true, true, true)
			),
			array(
				'[url=http://example.org][img]http://example.org/img.png[/img][/url]',
				'<r><URL url="http://example.org"><s>[url=http://example.org]</s>[img]http://example.org/img.png[/img]<e>[/url]</e></URL></r>',
				array(true, true, true, false, true, true, true),
				null,
				array('You cannot use certain BBCodes: [img].')
			),
			array(
				'[flash=12,34]http://example.org/foo.swf[/flash]',
				'<r><FLASH height="34" url="http://example.org/foo.swf" width="12"><s>[flash=12,34]</s><URL url="http://example.org/foo.swf">http://example.org/foo.swf</URL><e>[/flash]</e></FLASH></r>',
				array(true, true, true, true, true, true, true)
			),
			array(
				'[flash=12,34]http://example.org/foo.swf[/flash]',
				'<r>[flash=12,34]<URL url="http://example.org/foo.swf">http://example.org/foo.swf</URL>[/flash]</r>',
				array(true, true, true, true, false, true, true),
				null,
				array('You cannot use certain BBCodes: [flash].')
			),
			array(
				'[quote="foo"]bar :)[/quote]',
				'<r><QUOTE author="foo"><s>[quote="foo"]</s>bar <E>:)</E><e>[/quote]</e></QUOTE></r>',
				array(true, true, true, true, true, true, true)
			),
			array(
				'[quote="foo"]bar :)[/quote]',
				'<r>[quote="foo"]bar <E>:)</E>[/quote]</r>',
				array(true, true, true, true, true, false, true),
				null,
				array('You cannot use certain BBCodes: [quote].')
			),
			array(
				'[url=http://example.org][img]http://example.org/img.png[/img][/url]',
				'<r><URL url="http://example.org"><s>[url=http://example.org]</s><IMG src="http://example.org/img.png"><s>[img]</s>http://example.org/img.png<e>[/img]</e></IMG><e>[/url]</e></URL></r>',
				array(true, true, true, true, true, true, true)
			),
			array(
				'[url=http://example.org][img]http://example.org/img.png[/img][/url]',
				'<r>[url=http://example.org]<IMG src="http://example.org/img.png"><s>[img]</s>http://example.org/img.png<e>[/img]</e></IMG>[/url]</r>',
				array(true, true, true, true, true, true, false),
				null,
				array('You cannot use certain BBCodes: [url].')
			),
			array(
				'[size=200]200[/size]',
				'<r><SIZE size="200"><s>[size=200]</s>200<e>[/size]</e></SIZE></r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_font_size', 200);
				}
			),
			array(
				'[size=200]200[/size]',
				'<r><SIZE size="200"><s>[size=200]</s>200<e>[/size]</e></SIZE></r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_font_size', 0);
				}
			),
			array(
				'[size=2000]2000[/size]',
				'<t>[size=2000]2000[/size]</t>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_font_size', 200);
				},
				array('You may only use fonts up to size 200.')
			),
			array(
				'[size=0]0[/size]',
				'<t>[size=0]0[/size]</t>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_font_size', 200);
				}
			),
			array(
				'[size=200]200[/size]',
				'<r><SIZE size="200"><s>[size=200]</s>200<e>[/size]</e></SIZE></r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_sig_font_size', 200);
				}
			),
			array(
				'[size=200]200[/size]',
				'<t>[size=200]200[/size]</t>',
				array(true, true, true, true, true, true, true, true, 'sig'),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_sig_font_size', 120);
				},
				array('You may only use fonts up to size 120.')
			),
			array(
				'[img]http://example.org/100x100.png[/img]',
				'<r>[img]<URL url="http://example.org/100x100.png">http://example.org/100x100.png</URL>[/img]</r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_img_height', 12);
				},
				array('Your images may only be up to 12 pixels high.')
			),
			array(
				'[img]http://example.org/100x100.png[/img]',
				'<r>[img]<URL url="http://example.org/100x100.png">http://example.org/100x100.png</URL>[/img]</r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_img_width', 34);
				},
				array('Your images may only be up to 34 pixels wide.')
			),
			array(
				'[img]http://example.org/100x100.png[/img]',
				'<r><IMG src="http://example.org/100x100.png"><s>[img]</s><URL url="http://example.org/100x100.png">http://example.org/100x100.png</URL><e>[/img]</e></IMG></r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_img_height', 0);
					$phpbb_container->get('config')->set('max_post_img_width', 0);
				}
			),
			array(
				'[img]http://example.org/100x100.png[/img]',
				'<r><IMG src="http://example.org/100x100.png"><s>[img]</s><URL url="http://example.org/100x100.png">http://example.org/100x100.png</URL><e>[/img]</e></IMG></r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_img_height', 100);
					$phpbb_container->get('config')->set('max_post_img_width', 100);
				}
			),
			array(
				'[img]http://example.org/100x100.png[/img]',
				'<r><IMG src="http://example.org/100x100.png"><s>[img]</s><URL url="http://example.org/100x100.png">http://example.org/100x100.png</URL><e>[/img]</e></IMG></r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_sig_img_height', 12);
					$phpbb_container->get('config')->set('max_sig_img_width', 34);
				}
			),
			array(
				'[img]http://example.org/404.png[/img]',
				'<r>[img]<URL url="http://example.org/404.png">http://example.org/404.png</URL>[/img]</r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_img_height', 12);
				},
				array('It was not possible to determine the dimensions of the image.')
			),
			array(
				'[flash=999,999]http://example.org/foo.swf[/flash]',
				'<r>[flash=999,999]<URL url="http://example.org/foo.swf">http://example.org/foo.swf</URL>[/flash]</r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_img_height', 123);
				},
				array('Your flash files may only be up to 123 pixels high.')
			),
			array(
				'[flash=999,999]http://example.org/foo.swf[/flash]',
				'<r>[flash=999,999]<URL url="http://example.org/foo.swf">http://example.org/foo.swf</URL>[/flash]</r>',
				array(true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_img_width', 456);
				},
				array('Your flash files may only be up to 456 pixels wide.')
			),
			array(
				':) :) :)',
				'<r><E>:)</E> <E>:)</E> <E>:)</E></r>',
				array(true, true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_smilies', 3);
				}
			),
			array(
				':) :) :) :)',
				'<r><E>:)</E> <E>:)</E> <E>:)</E> :)</r>',
				array(true, true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_smilies', 3);
				},
				array('Your message contains too many smilies. The maximum number of smilies allowed is 3.')
			),
			array(
				':) :) :) :)',
				'<r><E>:)</E> <E>:)</E> <E>:)</E> <E>:)</E></r>',
				array(true, true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_smilies', 0);
				}
			),
			array(
				':) :) :) :)',
				'<r><E>:)</E> <E>:)</E> <E>:)</E> <E>:)</E></r>',
				array(true, true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_sig_smilies', 3);
				}
			),
			array(
				':) :) :) :)',
				'<r><E>:)</E> <E>:)</E> <E>:)</E> :)</r>',
				array(true, true, true, true, true, true, true, true, 'sig'),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_sig_smilies', 3);
				},
				array('Your message contains too many smilies. The maximum number of smilies allowed is 3.')
			),
			array(
				'http://example.org http://example.org http://example.org',
				'<r><URL url="http://example.org">http://example.org</URL> <URL url="http://example.org">http://example.org</URL> http://example.org</r>',
				array(true, true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_urls', 2);
				},
				array('Your message contains too many URLs. The maximum number of URLs allowed is 2.')
			),
			array(
				'http://example.org http://example.org http://example.org',
				'<r><URL url="http://example.org">http://example.org</URL> <URL url="http://example.org">http://example.org</URL> <URL url="http://example.org">http://example.org</URL></r>',
				array(true, true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_post_urls', 0);
				}
			),
			array(
				'http://example.org http://example.org http://example.org',
				'<r><URL url="http://example.org">http://example.org</URL> <URL url="http://example.org">http://example.org</URL> <URL url="http://example.org">http://example.org</URL></r>',
				array(true, true, true, true, true, true, true, true),
				function ($phpbb_container)
				{
					$phpbb_container->get('config')->set('max_sig_urls', 2);
				}
			),
		);
	}
}

class phpbb_text_processing_message_parser_test_proxy
{
	protected $response;

	public function stream_open($url)
	{
		if (strpos($url, '100x100'))
		{
			// Return a 100 x 100 PNG image
			$this->response = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAGQAAABkAQAAAABYmaj5AAAAE0lEQVR4AWOgKxgFo2AUjIJRAAAFeAABHs0ozQAAAABJRU5ErkJggg==');
		}
		else
		{
			$this->response = '404 not found';
		}

		return true;
	}

	public function stream_stat()
	{
		return false;
	}

	public function stream_read($len)
	{
		$chunk = substr($this->response, 0, $len);
		$this->response = substr($this->response, $len);

		return $chunk;
	}

	public function stream_eof()
	{
		return ($this->response === false);
	}
}
