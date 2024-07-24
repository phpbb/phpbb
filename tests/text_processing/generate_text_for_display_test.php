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

class phpbb_text_processing_generate_text_for_display_test extends phpbb_test_case
{
	protected function setUp(): void
	{
		global $cache, $user, $phpbb_dispatcher;

		parent::setUp();

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$config = new \phpbb\config\config(array());
	}

	/**
	* @dataProvider get_legacy_tests
	*/
	public function test_legacy($original, $expected, $uid = '', $bitfield = '', $flags = 0, $censor_text = true)
	{
		global $auth, $cache, $config, $user;

		global $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->data['user_options'] = 230271;
		$user->optionset('viewcensors', true);
		$user->optionset('viewimg', true);
		$user->optionset('viewsmilies', true);

		$actual = generate_text_for_display($original, $uid, $bitfield, $flags, $censor_text);

		$this->assertSame($expected, $actual);
	}

	public function get_legacy_tests()
	{
		return array(
			array(
				'',
				''
			),
			array(
				'0',
				'0'
			),
		);
	}

	public function test_censor_is_restored()
	{
		global $auth, $user, $config, $phpbb_container;

		$phpbb_container = new phpbb_mock_container_builder;

		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->data['user_options'] = 230271;
		// Do not ignore word censoring by user (switch censoring on in UCP)
		$user->optionset('viewcensors', true);

		$config = new \phpbb\config\config(array('allow_nocensors' => true));

		$auth = $this->createMock('phpbb\\auth\\auth');
		$auth->expects($this->any())
			 ->method('acl_get')
			 ->with('u_chgcensors')
			 ->will($this->returnValue(true));

		$phpbb_container->set('user', $user);
		$phpbb_container->set('config', $config);
		$phpbb_container->set('auth', $auth);

		$this->get_test_case_helpers()->set_s9e_services($phpbb_container);
		$renderer = $phpbb_container->get('text_formatter.renderer');

		$original = '<r><CENSOR with="banana">apple</CENSOR></r>';

		$renderer->set_viewcensors(false);
		$this->assertSame('apple', $renderer->render($original));
		$renderer->set_viewcensors(true);
		$this->assertSame('banana', $renderer->render($original));
		$this->assertSame('apple', generate_text_for_display($original, '', '', 0, false));
		$this->assertSame('banana', $renderer->render($original), 'The original setting was not restored');

		$renderer->set_viewcensors(false);
		$this->assertSame('apple', $renderer->render($original));
		$this->assertSame('banana', generate_text_for_display($original, '', '', 0, true));
		$this->assertSame('apple', $renderer->render($original), 'The original setting was not restored');

		// Test user option switch to ignore censoring
		$renderer->set_viewcensors(true);
		// 1st: censoring is still on in UCP
		$this->assertSame('banana', generate_text_for_display($original, '', '', 0, true));
		// 2nd: switch censoring off in UCP
		$user->optionset('viewcensors', false);
		$this->assertSame('apple', generate_text_for_display($original, '', '', 0, true));
	}

	/**
	* @dataProvider get_text_formatter_tests
	*/
	public function test_text_formatter($original, $expected, $censor_text = true, $setup = null)
	{
		global $auth, $user, $config, $phpbb_container;

		$phpbb_container = new phpbb_mock_container_builder;

		if (isset($setup))
		{
			$setup($phpbb_container, $this);
		}

		$this->get_test_case_helpers()->set_s9e_services($phpbb_container);

		$this->assertSame($expected, generate_text_for_display($original, '', '', 0, $censor_text));
	}

	public function get_text_formatter_tests()
	{
		return array(
			array(
				'<t>Plain text</t>',
				'Plain text'
			),
			array(
				'<r>Hello <URL url="http://example.org"><s>[url=http://example.org]</s>world<e>[/url]</e></URL></r>',
				'Hello <a href="http://example.org" class="postlink">world</a>'
			),
			array(
				'<t>&amp;&lt;&gt;"\'</t>',
				'&amp;&lt;&gt;"\''
			),
			array(
				'<r><CENSOR with="banana">apple</CENSOR></r>',
				'banana',
				true
			),
			array(
				'<r><CENSOR with="banana">apple</CENSOR></r>',
				'apple',
				false
			),
			array(
				'<r><IMG src="http://localhost/mrgreen.gif"><s>[img]</s><URL url="http://localhost/mrgreen.gif">http://localhost/mrgreen.gif</URL><e>[/img]</e></IMG></r>',
				'<img src="http://localhost/mrgreen.gif" class="postimage" alt="Image">'
			),
			array(
				'<r><IMG src="http://localhost/mrgreen.gif"><s>[img]</s><URL url="http://localhost/mrgreen.gif">http://localhost/mrgreen.gif</URL><e>[/img]</e></IMG></r>',
				'<a href="http://localhost/mrgreen.gif" class="postlink">http://localhost/mrgreen.gif</a>',
				true,
				function ($phpbb_container)
				{
					global $phpbb_root_path, $phpEx;

					$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
					$lang = new \phpbb\language\language($lang_loader);
					$user = new \phpbb\user($lang, '\phpbb\datetime');
					$user->data['user_options'] = 230271;
					$user->optionset('viewimg', false);

					$phpbb_container->set('user', $user);
				}
			),
			array(
				'<r><E>:)</E></r>',
				'<img class="smilies" src="phpBB/images/smilies/icon_e_smile.gif" width="15" height="17" alt=":)" title="Smile">'
			),
			array(
				'<r><E>:)</E></r>',
				':)',
				true,
				function ($phpbb_container)
				{
					global $phpbb_root_path, $phpEx;

					$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
					$lang = new \phpbb\language\language($lang_loader);
					$user = new \phpbb\user($lang, '\phpbb\datetime');
					$user->data['user_options'] = 230271;
					$user->optionset('viewsmilies', false);

					$phpbb_container->set('user', $user);
				}
			),
		);
	}
}
