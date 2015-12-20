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

		$factory = $this->getMockBuilder('phpbb\\textformatter\\s9e\\factory')
		                ->disableOriginalConstructor()
		                ->getMock();
		$factory->expects($this->never())->method('regenerate');

		$parser = new \phpbb\textformatter\s9e\parser(
			$cache,
			'_foo_parser',
			$factory,
			new phpbb_mock_event_dispatcher
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

		$factory = $this->getMockBuilder('phpbb\\textformatter\\s9e\\factory')
		                ->disableOriginalConstructor()
		                ->getMock();
		$factory->expects($this->never())->method('regenerate');

		$parser = new \phpbb\textformatter\s9e\parser(
			$cache,
			'_foo_parser',
			$factory,
			new phpbb_mock_event_dispatcher
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

		$factory = $this->getMockBuilder('phpbb\\textformatter\\s9e\\factory')
		                ->disableOriginalConstructor()
		                ->getMock();
		$factory->expects($this->once())
		        ->method('regenerate')
		        ->will($this->returnValue(array('parser' => $mock)));

		$parser = new \phpbb\textformatter\s9e\parser(
			new phpbb_mock_cache,
			'_foo_parser',
			$factory,
			new phpbb_mock_event_dispatcher
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

		$factory = $this->getMockBuilder('phpbb\\textformatter\\s9e\\factory')
		                ->disableOriginalConstructor()
		                ->getMock();

		$parser = new \phpbb\textformatter\s9e\parser(
			$cache,
			'_foo_parser',
			$factory,
			new phpbb_mock_event_dispatcher
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
				'enable_magic_url', null,
				'enablePlugin',     array('Autoemail', 'Autolink')
			),
			array(
				'enable_smilies', null,
				'enablePlugin',   'Emoticons'
			)
		);
	}

	/**
	* @testdox The constructor triggers a core.text_formatter_s9e_parser_setup event
	*/
	public function test_setup_event()
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$dispatcher = $this->getMock('phpbb\\event\\dispatcher_interface');
		$dispatcher
			->expects($this->once())
			->method('trigger_event')
			->with(
				'core.text_formatter_s9e_parser_setup',
				$this->callback(array($this, 'setup_event_callback'))
			)
			->will($this->returnArgument(1));

		new \phpbb\textformatter\s9e\parser(
			$container->get('cache.driver'),
			'_foo_parser',
			$container->get('text_formatter.s9e.factory'),
			$dispatcher
		);
	}

	public function setup_event_callback($vars)
	{
		return isset($vars['parser'])
			&& $vars['parser'] instanceof \phpbb\textformatter\s9e\parser;
	}

	/**
	* @testdox parse() triggers a core.text_formatter_s9e_parse_before and core.text_formatter_s9e_parse_after events
	*/
	public function test_parse_event()
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
				'core.text_formatter_s9e_parse_before',
				$this->callback(array($this, 'parse_before_event_callback'))
			)
			->will($this->returnArgument(1));
		$dispatcher
			->expects($this->at(2))
			->method('trigger_event')
			->with(
				'core.text_formatter_s9e_parse_after',
				$this->callback(array($this, 'parse_after_event_callback'))
			)
			->will($this->returnArgument(1));

		$parser = new \phpbb\textformatter\s9e\parser(
			$container->get('cache.driver'),
			'_foo_parser',
			$container->get('text_formatter.s9e.factory'),
			$dispatcher
		);
		$parser->parse('...');
	}

	public function parse_before_event_callback($vars)
	{
		return isset($vars['parser'])
			&& $vars['parser'] instanceof \phpbb\textformatter\s9e\parser
			&& isset($vars['text'])
			&& $vars['text'] === '...';
	}

	public function parse_after_event_callback($vars)
	{
		return isset($vars['parser'])
			&& $vars['parser'] instanceof \phpbb\textformatter\s9e\parser
			&& isset($vars['xml'])
			&& $vars['xml'] === '<t>...</t>';
	}

	public function test_get_parser()
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$parser = $container->get('text_formatter.parser');
		$this->assertInstanceOf('s9e\\TextFormatter\\Parser', $parser->get_parser());
	}
}
