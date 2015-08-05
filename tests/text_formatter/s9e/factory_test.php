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
require_once __DIR__ . '/../../test_framework/phpbb_database_test_case.php';

class phpbb_textformatter_s9e_factory_test extends phpbb_database_test_case
{
	public function setUp()
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

	public function get_factory()
	{
		global $phpbb_root_path;
		$this->cache = new phpbb_mock_cache;
		$dal = new \phpbb\textformatter\data_access(
			$this->new_dbal(),
			'phpbb_bbcodes',
			'phpbb_smilies',
			'phpbb_styles',
			'phpbb_words',
			$phpbb_root_path . 'styles/'
		);
		$factory = new \phpbb\textformatter\s9e\factory(
			$dal,
			$this->cache,
			$this->dispatcher,
			new \phpbb\config\config(array('allowed_schemes_links' => 'http,https,ftp')),
			$this->get_cache_dir(),
			'_foo_parser',
			'_foo_renderer'
		);

		return $factory;
	}

	public function test_get_configurator()
	{
		$configurator = $this->get_factory()->get_configurator();

		$this->assertInstanceOf('s9e\\TextFormatter\\Configurator', $configurator);

		$this->assertTrue(isset($configurator->plugins['Autoemail']));
		$this->assertTrue(isset($configurator->plugins['Autolink']));

		$this->assertTrue(isset($configurator->BBCodes['B']));
		$this->assertTrue(isset($configurator->BBCodes['CODE']));
		$this->assertTrue(isset($configurator->BBCodes['COLOR']));
		$this->assertTrue(isset($configurator->BBCodes['EMAIL']));
		$this->assertTrue(isset($configurator->BBCodes['FLASH']));
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
		$this->assertFileNotExists($old_file, 'The old renderer has not been deleted');

		unlink($new_file);
	}

	public function test_local_url()
	{
		global $config, $user, $request;
		$config = array(
			'force_server_vars' => true,
			'server_protocol' => 'http://',
			'server_name' => 'path',
			'server_port' => 80,
			'script_path' => '/to',
			'cookie_secure' => false
		);
		$user = new phpbb_mock_user;
		$request = new phpbb_mock_request;

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
			'<img class="smilies" src="phpBB/images/smilies/%22%27%3C&amp;%3E.png" alt="&quot;\'&lt;&amp;&gt;" title="&quot;\'&lt;&amp;&gt;">',
			$renderer->render('<r><E>"\'&lt;&amp;&gt;</E></r>')
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
	* @testdox get_configurator() triggers events before and after configuration
	*/
	public function test_configure_events()
	{
		$this->dispatcher = $this->getMock('phpbb\\event\\dispatcher_interface');
		$this->dispatcher
			->expects($this->at(0))
			->method('trigger_event')
			->with(
				'core.text_formatter_s9e_configure_before',
				$this->callback(array($this, 'configure_event_callback'))
			)
			->will($this->returnArgument(1));
		$this->dispatcher
			->expects($this->at(1))
			->method('trigger_event')
			->with(
				'core.text_formatter_s9e_configure_after',
				$this->callback(array($this, 'configure_event_callback'))
			)
			->will($this->returnArgument(1));

		$this->get_factory()->get_configurator();
	}

	public function configure_event_callback($vars)
	{
		return isset($vars['configurator']) && $vars['configurator'] instanceof \s9e\TextFormatter\Configurator;
	}
}
