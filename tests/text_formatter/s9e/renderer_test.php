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

class phpbb_textformatter_s9e_renderer_test extends phpbb_test_case
{
	public function get_cache_dir()
	{
		return __DIR__ . '/../../tmp/';
	}

	public function test_load_from_cache()
	{
		// Save a fake renderer class in the cache dir
		file_put_contents(
			$this->get_cache_dir() . 'renderer_foo.php',
			'<?php class renderer_foo { public function setParameter() {} }'
		);

		$cache = $this->getMock('phpbb_mock_cache');
		$cache->expects($this->once())
		      ->method('get')
		      ->with('_foo_renderer')
		      ->will($this->returnValue(array('class' => 'renderer_foo')));

		$factory = $this->getMockBuilder('phpbb\\textformatter\\s9e\\factory')
		                ->disableOriginalConstructor()
		                ->getMock();
		$factory->expects($this->never())->method('regenerate');

		$renderer = new \phpbb\textformatter\s9e\renderer(
			$cache,
			$this->get_cache_dir(),
			'_foo_renderer',
			$factory,
			new phpbb_mock_event_dispatcher
		);
	}

	public function test_regenerate_on_cache_miss()
	{
		$mock = $this->getMockForAbstractClass('s9e\\TextFormatter\\Renderer');

		$cache = $this->getMock('phpbb_mock_cache');
		$cache->expects($this->once())
		      ->method('get')
		      ->with('_foo_renderer')
		      ->will($this->returnValue(false));

		$factory = $this->getMockBuilder('phpbb\\textformatter\\s9e\\factory')
		                ->disableOriginalConstructor()
		                ->getMock();
		$factory->expects($this->once())
		        ->method('regenerate')
		        ->will($this->returnValue(array('parser' => $mock)));

		$renderer = new \phpbb\textformatter\s9e\renderer(
			$cache,
			$this->get_cache_dir(),
			'_foo_renderer',
			$factory,
			new phpbb_mock_event_dispatcher
		);
	}

	/**
	* @dataProvider get_options_cases
	*/
	public function test_options($original, $expected, $calls)
	{
		$container = new phpbb_mock_container_builder;
		$this->get_test_case_helpers()->set_s9e_services($container);

		$renderer = $container->get('text_formatter.renderer');

		foreach ($calls as $method => $arg)
		{
			$renderer->$method($arg);
		}

		$this->assertSame($expected, $renderer->render($original));
	}

	public function get_options_cases()
	{
		return array(
			array(
				'<t>apple</t>',
				'banana',
				array('set_viewcensors' => true)
			),
			array(
				'<t>apple</t>',
				'apple',
				array('set_viewcensors' => false)
			),
			array(
				'<r><FLASH height="456" url="http://example.org/foo.swf" width="123"><s>[flash=123,456]</s><URL url="http://example.org/foo.swf">http://example.org/foo.swf</URL><e>[/flash]</e></FLASH></r>',
				'<object classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=5,0,0,0" width="123" height="456"><param name="movie" value="http://example.org/foo.swf"><param name="play" value="false"><param name="loop" value="false"><param name="quality" value="high"><param name="allowScriptAccess" value="never"><param name="allowNetworking" value="internal"><embed src="http://example.org/foo.swf" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" width="123" height="456" play="false" loop="false" quality="high" allowscriptaccess="never" allownetworking="internal"></object>',
				array('set_viewflash' => true)
			),
			array(
				'<r><IMG src="http://example.org/foo.png"><s>[img]</s>http://example.org/foo.png<e>[/img]</e></IMG></r>',
				'<img src="http://example.org/foo.png" alt="Image">',
				array('set_viewimg' => true)
			),
			array(
				'<r><E>:)</E></r>',
				'<img class="smilies" src="phpBB/images/smilies/icon_e_smile.gif" alt=":)" title="Smile">',
				array('set_viewsmilies' => true)
			),
			array(
				'<r><E>:)</E></r>',
				':)',
				array('set_viewsmilies' => false)
			),
		);
	}

	/**
	* @dataProvider get_default_options_cases
	*/
	public function test_default_options($original, $expected, $setup = null)
	{
		$container = new phpbb_mock_container_builder;

		if (isset($setup))
		{
			$setup($container, $this);
		}

		$this->get_test_case_helpers()->set_s9e_services($container);

		$this->assertSame($expected, $container->get('text_formatter.renderer')->render($original));
	}

	public function get_default_options_cases()
	{
		return array(
			array(
				'<t>apple</t>',
				'banana'
			),
			array(
				'<t>apple</t>',
				'banana',
				function ($phpbb_container)
				{
					global $phpbb_root_path, $phpEx;

					$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
					$lang = new \phpbb\language\language($lang_loader);
					$user = new \phpbb\user($lang, '\phpbb\datetime');
					$user->optionset('viewcensors', false);

					$phpbb_container->set('user', $user);
				}
			),
			array(
				'<t>apple</t>',
				'banana',
				function ($phpbb_container)
				{
					global $phpbb_root_path, $phpEx;

					$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
					$lang = new \phpbb\language\language($lang_loader);
					$user = new \phpbb\user($lang, '\phpbb\datetime');
					$user->optionset('viewcensors', false);

					$config = new \phpbb\config\config(array('allow_nocensors' => true));

					$phpbb_container->set('user', $user);
					$phpbb_container->set('config', $config);
				}
			),
			array(
				'<t>apple</t>',
				'apple',
				function ($phpbb_container, $test)
				{
					global $phpbb_root_path, $phpEx;

					$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
					$lang = new \phpbb\language\language($lang_loader);
					$user = new \phpbb\user($lang, '\phpbb\datetime');
					$user->optionset('viewcensors', false);

					$config = new \phpbb\config\config(array('allow_nocensors' => true));

					$auth = $test->getMock('phpbb\\auth\\auth');
					$auth->expects($test->any())
					     ->method('acl_get')
					     ->with('u_chgcensors')
					     ->will($test->returnValue(true));

					$phpbb_container->set('user', $user);
					$phpbb_container->set('config', $config);
					$phpbb_container->set('auth', $auth);
				}
			),
			array(
				'<r><FLASH url="http://localhost/foo.swf" width="123" height="456"><s>[flash=123,456]</s>http://localhost/foo.swf<e>[/flash]</e></FLASH></r>',
				'<object classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=5,0,0,0" width="123" height="456"><param name="movie" value="http://localhost/foo.swf"><param name="play" value="false"><param name="loop" value="false"><param name="quality" value="high"><param name="allowScriptAccess" value="never"><param name="allowNetworking" value="internal"><embed src="http://localhost/foo.swf" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" width="123" height="456" play="false" loop="false" quality="high" allowscriptaccess="never" allownetworking="internal"></object>'
			),
			array(
				'<r><FLASH url="http://localhost/foo.swf" width="123" height="456"><s>[flash=123,456]</s>http://localhost/foo.swf<e>[/flash]</e></FLASH></r>',
				'http://localhost/foo.swf',
				function ($phpbb_container)
				{
					global $phpbb_root_path, $phpEx;

					$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
					$lang = new \phpbb\language\language($lang_loader);
					$user = new \phpbb\user($lang, '\phpbb\datetime');
					$user->optionset('viewflash', false);

					$phpbb_container->set('user', $user);
				}
			),
			array(
				'<r><IMG src="http://localhost/mrgreen.gif"><s>[img]</s><URL url="http://localhost/mrgreen.gif">http://localhost/mrgreen.gif</URL><e>[/img]</e></IMG></r>',
				'<img src="http://localhost/mrgreen.gif" alt="Image">'
			),
			array(
				'<r><IMG src="http://localhost/mrgreen.gif"><s>[img]</s><URL url="http://localhost/mrgreen.gif">http://localhost/mrgreen.gif</URL><e>[/img]</e></IMG></r>',
				'<a href="http://localhost/mrgreen.gif" class="postlink">http://localhost/mrgreen.gif</a>',
				function ($phpbb_container)
				{
					global $phpbb_root_path, $phpEx;

					$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
					$lang = new \phpbb\language\language($lang_loader);
					$user = new \phpbb\user($lang, '\phpbb\datetime');
					$user->optionset('viewimg', false);

					$phpbb_container->set('user', $user);
				}
			),
			array(
				'<r><E>:)</E></r>',
				'<img class="smilies" src="phpBB/images/smilies/icon_e_smile.gif" alt=":)" title="Smile">'
			),
			array(
				'<r><E>:)</E></r>',
				':)',
				function ($phpbb_container)
				{
					global $phpbb_root_path, $phpEx;

					$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
					$lang = new \phpbb\language\language($lang_loader);
					$user = new \phpbb\user($lang, '\phpbb\datetime');
					$user->optionset('smilies', false);

					$phpbb_container->set('user', $user);
				}
			),
		);
	}

	public function test_default_lang()
	{
		global $phpbb_container;
		$this->get_test_case_helpers()->set_s9e_services($phpbb_container, __DIR__ . '/fixtures/default_lang.xml');

		$renderer = $phpbb_container->get('text_formatter.renderer');

		$this->assertSame('FOO_BAR', $renderer->render('<r><FOO/></r>'));
	}

	/**
	* @dataProvider get_option_names
	*/
	public function test_get_option($option_name)
	{
		global $phpbb_container;
		$this->get_test_case_helpers()->set_s9e_services();

		$renderer = $phpbb_container->get('text_formatter.renderer');

		$renderer->{'set_' . $option_name}(false);
		$this->assertFalse($renderer->{'get_' . $option_name}());
		$renderer->{'set_' . $option_name}(true);
		$this->assertTrue($renderer->{'get_' . $option_name}());
	}

	public function get_option_names()
	{
		return array(
			array('viewcensors'),
			array('viewflash'),
			array('viewimg'),
			array('viewsmilies')
		);
	}

	public function test_styles()
	{
		global $phpbb_container;

		$tests = array(
			1 => '<strong>bold</strong>',
			2 => '<b>bold</b>'
		);

		global $phpbb_root_path, $phpEx;

		foreach ($tests as $style_id => $expected)
		{
			$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
			$lang = new \phpbb\language\language($lang_loader);
			$user = new \phpbb\user($lang, '\phpbb\datetime');
			$user->style = array('style_id' => $style_id);

			$phpbb_container = new phpbb_mock_container_builder;
			$phpbb_container->set('user', $user);

			$this->get_test_case_helpers()->set_s9e_services($phpbb_container, __DIR__ . '/fixtures/styles.xml', __DIR__ . '/fixtures/styles/');

			$renderer = $phpbb_container->get('text_formatter.renderer');
			$this->assertSame(
				$expected,
				$renderer->render('<r><B><s>[b]</s>bold<e>[/b]</e></B></r>')
			);
		}
	}

	public function test_style_inheritance1()
	{
		global $phpbb_container, $phpbb_root_path, $phpEx;

		// Style 3 inherits from 2 which inherits from 1. Only style 1 has a bbcode.html
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->style = array('style_id' => 3);

		$phpbb_container = new phpbb_mock_container_builder;
		$phpbb_container->set('user', $user);

		$this->get_test_case_helpers()->set_s9e_services($phpbb_container, __DIR__ . '/fixtures/style_inheritance.xml', __DIR__ . '/fixtures/styles/');

		$renderer = $phpbb_container->get('text_formatter.renderer');
		$this->assertSame(
			'<strong>bold</strong>',
			$renderer->render('<r><B><s>[b]</s>bold<e>[/b]</e></B></r>')
		);
	}

	public function test_style_inheritance2()
	{
		global $phpbb_container, $phpbb_root_path, $phpEx;

		// Style 5 inherits from 4, but both have a bbcode.html
		$tests = array(
			4 => '<b>bold</b>',
			5 => '<b class="barplus">bold</b>'
		);

		foreach ($tests as $style_id => $expected)
		{
			$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
			$lang = new \phpbb\language\language($lang_loader);
			$user = new \phpbb\user($lang, '\phpbb\datetime');
			$user->style = array('style_id' => $style_id);

			$phpbb_container = new phpbb_mock_container_builder;
			$phpbb_container->set('user', $user);

			$this->get_test_case_helpers()->set_s9e_services($phpbb_container, __DIR__ . '/fixtures/style_inheritance.xml', __DIR__ . '/fixtures/styles/');

			$renderer = $phpbb_container->get('text_formatter.renderer');
			$this->assertSame(
				$expected,
				$renderer->render('<r><B><s>[b]</s>bold<e>[/b]</e></B></r>')
			);
		}
	}

	/**
	* @testdox The constructor triggers a core.text_formatter_s9e_renderer_setup event
	*/
	public function test_setup_event()
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$dispatcher = $this->getMock('phpbb\\event\\dispatcher_interface');
		$dispatcher
			->expects($this->once())
			->method('trigger_event')
			->with(
				'core.text_formatter_s9e_renderer_setup',
				$this->callback(array($this, 'setup_event_callback'))
			)
			->will($this->returnArgument(1));

		new \phpbb\textformatter\s9e\renderer(
			$container->get('cache.driver'),
			$container->getParameter('cache.dir'),
			'_foo_renderer',
			$container->get('text_formatter.s9e.factory'),
			$dispatcher
		);
	}

	public function setup_event_callback($vars)
	{
		return isset($vars['renderer'])
			&& $vars['renderer'] instanceof \phpbb\textformatter\s9e\renderer;
	}

	/**
	* @testdox render() triggers a core.text_formatter_s9e_render_before and core.text_formatter_s9e_render_after events
	*/
	public function test_render_event()
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$dispatcher = $this->getMock('phpbb\\event\\dispatcher_interface');
		$dispatcher
			->expects($this->any())
			->method('trigger_event')
			->will($this->returnArgument(1));
		$dispatcher
			->expects($this->at(1))
			->method('trigger_event')
			->with(
				'core.text_formatter_s9e_render_before',
				$this->callback(array($this, 'render_before_event_callback'))
			)
			->will($this->returnArgument(1));
		$dispatcher
			->expects($this->at(2))
			->method('trigger_event')
			->with(
				'core.text_formatter_s9e_render_after',
				$this->callback(array($this, 'render_after_event_callback'))
			)
			->will($this->returnArgument(1));

		$renderer = new \phpbb\textformatter\s9e\renderer(
			$container->get('cache.driver'),
			$container->getParameter('cache.dir'),
			'_foo_renderer',
			$container->get('text_formatter.s9e.factory'),
			$dispatcher
		);
		$renderer->render('<t>...</t>');
	}

	public function render_before_event_callback($vars)
	{
		return isset($vars['renderer'])
			&& $vars['renderer'] instanceof \phpbb\textformatter\s9e\renderer
			&& isset($vars['xml'])
			&& $vars['xml'] === '<t>...</t>';
	}

	public function render_after_event_callback($vars)
	{
		return isset($vars['html'])
			&& $vars['html'] === '...'
			&& isset($vars['renderer'])
			&& $vars['renderer'] instanceof \phpbb\textformatter\s9e\renderer;
	}

	public function test_get_renderer()
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$renderer = $container->get('text_formatter.renderer');
		$this->assertInstanceOf('s9e\\TextFormatter\\Renderer', $renderer->get_renderer());
	}
}
