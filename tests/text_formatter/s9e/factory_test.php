<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
require_once __DIR__ . '/../../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../../phpBB/includes/functions_content.php';
require_once __DIR__ . '/../../mock/user.php';
require_once __DIR__ . '/../../mock/cache.php';
require_once __DIR__ . '/../../test_framework/phpbb_database_test_case.php';

class phpbb_textformatter_s9e_factory_test extends phpbb_database_test_case
{
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

		// This unsafe custom BBCode will trigger an exception and should be ignored
		$this->assertFalse(isset($configurator->BBCodes['UNSAFE']));

		$this->assertTrue(isset($configurator->Emoticons[':D']));
	}

	public function test_regenerate()
	{
		extract($this->get_factory()->regenerate());

		$this->assertInstanceOf('s9e\\TextFormatter\\Parser', $parser);
		$this->assertInstanceOf('s9e\\TextFormatter\\Renderer', $renderer);

		$this->assertEquals($parser, $this->cache->get('_foo_parser'), 'The parser was not cached');
		$this->assertEquals(
			array(
				'class'    => get_class($renderer),
				'renderer' => serialize($renderer)
			),
			$this->cache->get('_foo_renderer'),
			'The renderer was not cached'
		);

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
}
