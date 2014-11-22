<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
require_once __DIR__ . '/../../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../mock/user.php';
require_once __DIR__ . '/../../mock/cache.php';

class phpbb_textformatter_s9e_parser_test extends phpbb_test_case
{
	public function test_load_from_cache()
	{
		$mock = $this->getMockBuilder('s9e\\TextFormatter\\Parser')
		             ->disableOriginalConstructor()
		             ->getMock();

		$cache = $this->getMock('phpbb_mock_cache');
		$cache->expects($this->once())
		      ->method('get')
		      ->with('_foo_parser')
		      ->will($this->returnValue($mock));

		$parser = new \phpbb\textformatter\s9e\parser(
			$cache,
			'_foo_parser',
			$this->getMockBuilder('phpbb\\user')->disableOriginalConstructor()->getMock(),
			new phpbb_mock_container_builder
		);
	}

	public function test_use_from_cache()
	{
		$mock = $this->getMockBuilder('s9e\\TextFormatter\\Parser')
		             ->disableOriginalConstructor()
		             ->getMock();

		$mock->expects($this->once())
		     ->method('parse')
		     ->with('test')
		     ->will($this->returnValue('<t>test</t>'));

		$cache = new phpbb_mock_cache;
		$cache->put('_foo_parser', $mock);

		$parser = new \phpbb\textformatter\s9e\parser(
			$cache,
			'_foo_parser',
			$this->getMockBuilder('phpbb\\user')->disableOriginalConstructor()->getMock(),
			new phpbb_mock_container_builder
		);

		$this->assertSame('<t>test</t>', $parser->parse('test'));
	}

	public function test_regenerate_on_cache_miss()
	{
		$mock = $this->getMockBuilder('s9e\\TextFormatter\\Parser')
		             ->disableOriginalConstructor()
		             ->getMock();

		$mock->expects($this->once())
		     ->method('parse')
		     ->with('test')
		     ->will($this->returnValue('<t>test</t>'));

		$factory = $this->getMock('stdClass', array('regenerate'));
		$factory->expects($this->once())
		        ->method('regenerate')
		        ->will($this->returnValue(array($mock, false)));

		$container = new phpbb_mock_container_builder;
		$container->set('text_formatter.s9e.factory', $factory);

		$parser = new \phpbb\textformatter\s9e\parser(
			new phpbb_mock_cache,
			'_foo_parser',
			$this->getMockBuilder('phpbb\\user')->disableOriginalConstructor()->getMock(),
			$container
		);

		$this->assertSame('<t>test</t>', $parser->parse('test'));
	}

	/**
	* @dataProvider get_options_tests()
	*/
	public function test_options($adapter_method, $adapter_arg, $concrete_method, $concrete_arg)
	{
		$mock = $this->getMockBuilder('s9e\\TextFormatter\\Parser')
		             ->setMethods(array($concrete_method))
		             ->disableOriginalConstructor()
		             ->getMock();
		foreach ((array) $concrete_arg as $i => $concrete_arg)
		{
			$mock->expects($this->at($i))
			     ->method($concrete_method)
			     ->with($concrete_arg);
		}

		$cache = new phpbb_mock_cache;
		$cache->put('_foo_parser', $mock);

		$parser = new \phpbb\textformatter\s9e\parser(
			$cache,
			'_foo_parser',
			$this->getMockBuilder('phpbb\\user')->disableOriginalConstructor()->getMock(),
			new phpbb_mock_container_builder
		);

		call_user_func_array(array($parser, $adapter_method), (array) $adapter_arg);
	}

	public function get_options_tests()
	{
		return array(
			array(
				'disable_bbcode', 'url',
				'disableTag',     'URL'
			),
			array(
				'disable_bbcodes', null,
				'disablePlugin',   'BBCodes'
			),
			array(
				'disable_censor', null,
				'disablePlugin',  'Censor'
			),
			array(
				'disable_magic_url', null,
				'disablePlugin',     array('Autoemail', 'Autolink')
			),
			array(
				'disable_smilies', null,
				'disablePlugin',   'Emoticons'
			),
			array(
				'enable_bbcode', 'url',
				'enableTag',     'URL'
			),
			array(
				'enable_bbcodes', null,
				'enablePlugin',   'BBCodes'
			),
			array(
				'enable_censor', null,
				'enablePlugin',  'Censor'
			),
			array(
				'enable_magic_url', null,
				'enablePlugin',     array('Autoemail', 'Autolink')
			),
			array(
				'enable_smilies', null,
				'enablePlugin',   'Emoticons'
			)
		);
	}
}
