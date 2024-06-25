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

require_once __DIR__ . '/../../test_framework/phpbb_database_test_case.php';

class phpbb_textformatter_s9e_factory_test extends phpbb_database_test_case
{
	/**
	 * @var phpbb_mock_cache
	 */
	private $cache;

	/**
	 * @var phpbb_mock_event_dispatcher
	 */
	private $dispatcher;

	protected function setUp(): void
	{
		$this->cache = new phpbb_mock_cache;
		$this->dispatcher = new phpbb_mock_event_dispatcher;
		parent::setUp();
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/factory.xml');
	}

	public function get_cache_dir()
	{
		return __DIR__ . '/../../tmp/';
	}

	public function get_factory($styles_path = null)
	{
		global $config, $phpbb_root_path, $request, $symfony_request, $user;

		if (!isset($styles_path))
		{
			$styles_path = $phpbb_root_path . 'styles/';
		}

		$this->cache = new phpbb_mock_cache;
		$dal = new \phpbb\textformatter\data_access(
			$this->new_dbal(),
			'phpbb_bbcodes',
			'phpbb_smilies',
			'phpbb_styles',
			'phpbb_words',
			$styles_path
		);
		$factory = new \phpbb\textformatter\s9e\factory(
			$dal,
			$this->cache,
			$this->dispatcher,
			new \phpbb\config\config(array('allowed_schemes_links' => 'http,https,ftp')),
			new \phpbb\textformatter\s9e\link_helper,
			$this->getMockBuilder('phpbb\\log\\log_interface')->getMock(),
			$this->get_cache_dir(),
			'_foo_parser',
			'_foo_renderer'
		);

		// Global objects required by generate_board_url()
		$config = new \phpbb\config\config(array(
			'script_path'           => '/phpbb',
			'server_name'           => 'localhost',
			'server_port'           => 80,
			'server_protocol'       => 'http://',
		));
		$request = new phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request($request);
		$user = new phpbb_mock_user;

		return $factory;
	}

	public function run_configurator_assertions($configurator)
	{
		$this->assertInstanceOf('s9e\\TextFormatter\\Configurator', $configurator);

		$this->assertTrue(isset($configurator->plugins['Autoemail']));
		$this->assertTrue(isset($configurator->plugins['Autolink']));

		$this->assertTrue(isset($configurator->BBCodes['B']));
		$this->assertTrue(isset($configurator->BBCodes['CODE']));
		$this->assertTrue(isset($configurator->BBCodes['COLOR']));
		$this->assertTrue(isset($configurator->BBCodes['EMAIL']));
		$this->assertTrue(isset($configurator->BBCodes['I']));
		$this->assertTrue(isset($configurator->BBCodes['IMG']));
		$this->assertTrue(isset($configurator->BBCodes['LIST']));
		$this->assertTrue(isset($configurator->BBCodes['*']));
		$this->assertTrue(isset($configurator->BBCodes['QUOTE']));
		$this->assertTrue(isset($configurator->BBCodes['SIZE']));
		$this->assertTrue(isset($configurator->BBCodes['U']));
		$this->assertTrue(isset($configurator->BBCodes['URL']));

		// This custom BBCode should be set
		$this->assertTrue(isset($configurator->BBCodes['CUSTOM']));

		$this->assertTrue(isset($configurator->Emoticons[':D']));
	}

	public function test_get_configurator()
	{
		$configurator = $this->get_factory()->get_configurator();
		$this->run_configurator_assertions($configurator);

		// Test with twigified bbcode.html
		$configurator = $this->get_factory(__DIR__ . '/fixtures/styles/')->get_configurator();
		$this->run_configurator_assertions($configurator);

	}

	public function test_regenerate()
	{
		extract($this->get_factory()->regenerate());

		$this->assertInstanceOf('s9e\\TextFormatter\\Parser', $parser);
		$this->assertInstanceOf('s9e\\TextFormatter\\Renderer', $renderer);

		$renderer_data = $this->cache->get('_foo_renderer');
		$this->assertEquals($parser, $this->cache->get('_foo_parser'), 'The parser was not cached');
		$this->assertEquals(get_class($renderer), $renderer_data['class']);
		$this->assertInstanceOf('s9e\\TextFormatter\\Plugins\\Censor\\Helper', $renderer_data['censor']);

		$file = $this->get_cache_dir() . get_class($renderer) . '.php';
		$this->assertFileExists($file);
		unlink($file);
	}

	public function test_tidy()
	{
		$factory = $this->get_factory();

		// Create a fake "old" cache file
		$old_file = $this->get_cache_dir() . 's9e_foo.php';
		touch($old_file);

		// Create a current renderer
		extract($factory->regenerate());
		$new_file = $this->get_cache_dir() . get_class($renderer) . '.php';

		// Tidy the cache
		$factory->tidy();

		$this->assertFileExists($new_file, 'The current renderer has been deleted');
		$this->assertFileDoesNotExist($old_file, 'The old renderer has not been deleted');

		unlink($new_file);
	}

	public function test_local_url()
	{
		global $config, $user, $request, $symfony_request;
		$config = new \phpbb\config\config(array(
			'force_server_vars' => true,
			'server_protocol' => 'http://',
			'server_name' => 'path',
			'server_port' => 80,
			'script_path' => '/to',
			'cookie_secure' => false
		));
		$user = new phpbb_mock_user;
		$request = new phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request($request);

		$fixture = __DIR__ . '/fixtures/local_url.xml';
		$renderer = $this->get_test_case_helpers()->set_s9e_services(null, $fixture)->get('text_formatter.renderer');

		$this->assertSame(
			'<a href="http://path/to/foo">http://path/to/foo</a>',
			$renderer->render('<r><LOCAL content="foo"><s>[local]</s>foo<e>[/local]</e></LOCAL></r>')
		);
	}

	public function test_smilies_special_chars()
	{
		// Use a smiley that contains every special chars in every field
		$fixture = __DIR__ . '/fixtures/smilies_special_chars.xml';
		$renderer = $this->get_test_case_helpers()->set_s9e_services(null, $fixture)->get('text_formatter.renderer');

		$this->assertSame(
			'<img class="smilies" src="phpBB/images/smilies/%22%27%3C&amp;%3E.png" width="15" height="17" alt="&quot;\'&lt;&amp;&gt;" title="&quot;\'&lt;&amp;&gt;">',
			$renderer->render('<r><E>"\'&lt;&amp;&gt;</E></r>')
		);
	}

	public function test_duplicate_smilies()
	{
		$fixture = __DIR__ . '/fixtures/smilies_duplicate.xml';
		$parser = $this->get_test_case_helpers()->set_s9e_services(null, $fixture)->get('text_formatter.parser');

		$this->assertSame(
			'<r><E>:)</E></r>',
			$parser->parse(':)')
		);
	}

	/**
	* @testdox {INTTEXT} is supported in custom BBCodes
	*/
	public function test_inttext_token()
	{
		$fixture = __DIR__ . '/fixtures/inttext_token.xml';
		$container = $this->get_test_case_helpers()->set_s9e_services(null, $fixture);
		$parser = $container->get('text_formatter.parser');
		$renderer = $container->get('text_formatter.renderer');

		$original = '[spoiler=ɎɆS]text[/spoiler]';
		$expected = '<div class="spoiler"><div class="title">ɎɆS</div><div class="content">text</div></div>';
		$this->assertSame($expected, $renderer->render($parser->parse($original)));

		$original = '[spoiler=N:O:P:E]text[/spoiler]';
		$expected = $original;
		$this->assertSame($expected, $renderer->render($parser->parse($original)));
	}

	/**
	* @testdox Preserves comments in custom BBCodes
	*/
	public function test_preserve_comments()
	{
		$fixture = __DIR__ . '/fixtures/preserve_comments.xml';
		$container = $this->get_test_case_helpers()->set_s9e_services(null, $fixture);
		$parser = $container->get('text_formatter.parser');
		$renderer = $container->get('text_formatter.renderer');

		$original = '[X]';
		$expected = '<!-- comment -->';
		$this->assertSame($expected, $renderer->render($parser->parse($original)));
	}

	/**
	* @testdox Accepts unsafe custom BBCodes
	*/
	public function test_unsafe_bbcode()
	{
		$fixture = __DIR__ . '/fixtures/unsafe_bbcode.xml';
		$container = $this->get_test_case_helpers()->set_s9e_services(null, $fixture);
		$parser = $container->get('text_formatter.parser');
		$renderer = $container->get('text_formatter.renderer');

		$original = '[xss=javascript:alert(1)]text[/xss]';
		$expected = '<a href="javascript:alert(1)">text</a>';
		$this->assertSame($expected, $renderer->render($parser->parse($original)));
	}

	/**
	* @testdox Accepts unsafe default BBCodes
	*/
	public function test_unsafe_default_bbcodes()
	{
		$fixture   = __DIR__ . '/fixtures/unsafe_default_bbcodes.xml';
		$style_dir = __DIR__ . '/fixtures/styles/';
		$container = $this->get_test_case_helpers()->set_s9e_services(null, $fixture, $style_dir);
		$parser    = $container->get('text_formatter.parser');
		$renderer  = $container->get('text_formatter.renderer');

		$original = '[b]alert(1)[/b]';
		$expected = '<script>alert(1)</script>';
		$this->assertSame($expected, $renderer->render($parser->parse($original)));
	}

	/**
	* @testdox Logs malformed BBCodes
	*/
	public function test_malformed_bbcodes()
	{
		$log = $this->getMockBuilder('phpbb\\log\\log_interface')->getMock();
		$log->expects($this->once())
			->method('add')
			->with('critical', ANONYMOUS, '', 'LOG_BBCODE_CONFIGURATION_ERROR', false, ['[x !x]{TEXT}[/x]', 'Cannot interpret the BBCode definition']);

		$container = new phpbb_mock_container_builder;
		$container->set('log', $log);

		$fixture   = __DIR__ . '/fixtures/malformed_bbcode.xml';
		$this->get_test_case_helpers()->set_s9e_services($container, $fixture);
	}

	/**
	* @testdox get_configurator() triggers events before and after configuration
	*/
	public function test_configure_events()
	{
		$this->dispatcher = $this->createMock('phpbb\\event\\dispatcher_interface');
		$this->dispatcher
			->expects($this->exactly(2))
			->method('trigger_event')
			->withConsecutive(
				['core.text_formatter_s9e_configure_before', $this->callback(array($this, 'configure_event_callback'))],
				['core.text_formatter_s9e_configure_after', $this->callback(array($this, 'configure_event_callback'))]
			)
			->will($this->returnArgument(1));
		$this->get_factory()->get_configurator();
	}

	public function configure_event_callback($vars)
	{
		return isset($vars['configurator']) && $vars['configurator'] instanceof \s9e\TextFormatter\Configurator;
	}
}
