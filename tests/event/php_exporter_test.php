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

class phpbb_event_php_exporter_test extends phpbb_test_case
{
	/** @var \phpbb\event\php_exporter */
	protected $exporter;

	public function setUp()
	{
		parent::setUp();
		$this->exporter = new \phpbb\event\php_exporter(dirname(__FILE__) . '/fixtures/');
	}

	static public function crawl_php_file_data()
	{
		return array(
			array(
				'default.test',
				array(
					'default.dispatch'	=> array(
						'event'			=> 'default.dispatch',
						'file'			=> 'default.test',
						'arguments'		=> array(),
						'since'			=> '3.1.0-b2',
						'description'	=> 'Description',
					),
				),
			),
			array(
				'extra_description.test',
				array(
					'extra_description.dispatch'	=> array(
						'event'			=> 'extra_description.dispatch',
						'file'			=> 'extra_description.test',
						'arguments'		=> array(),
						'since'			=> '3.1.0-b2',
						'description'	=> 'Description',
					),
				),
			),
			array(
				'trigger.test',
				array(
					'core.trigger'	=> array(
						'event'			=> 'core.trigger',
						'file'			=> 'trigger.test',
						'arguments'		=> array('cp_row', 'current_row_number', 'end', 'row', 'start'),
						'since'			=> '3.1.0-a3',
						'description'	=> 'Event after the post data has been assigned to the template',
					),
				),
			),
			array(
				'trigger_wspace.test',
				array(
					'core.trigger'	=> array(
						'event'			=> 'core.trigger',
						'file'			=> 'trigger_wspace.test',
						'arguments'		=> array('cp_row', 'current_row_number', 'end', 'row', 'start'),
						'since'			=> '3.1.0-a3',
						'description'	=> 'Event after the post data has been assigned to the template',
					),
				),
			),
			array(
				'trigger_many_vars.test',
				array(
					'core.posting_modify_template_vars'	=> array(
						'event'			=> 'core.posting_modify_template_vars',
						'file'			=> 'trigger_many_vars.test',
						'arguments'		=> array(
							'cancel', 'delete', 'error', 'form_enctype', 'forum_id',
							'load', 'message_parser', 'mode', 'moderators', 'page_data',
							'page_title', 'post_data', 'post_id', 'preview', 'refresh',
							's_action', 's_hidden_fields', 's_topic_icons', 'save',
							'submit', 'topic_id',
						),
						'since'			=> '3.1.0-a1',
						'description'	=> 'This event allows you to modify template variables for the posting screen',
					),
				),
			),
			array(
				'none.test',
				array(),
			),
		);
	}

	/**
	* @dataProvider crawl_php_file_data
	*/
	public function test_crawl_php_file($file, $expected)
	{
		$this->exporter->crawl_php_file($file);
		$this->assertEquals($expected, $this->exporter->get_events());
	}

	static public function crawl_php_file_throws_data()
	{
		return array(
			array('missing_var.test', null),
			array('duplicate_event.test', 10),
		);
	}

	/**
	* @dataProvider crawl_php_file_throws_data
	*/
	public function test_crawl_php_file_throws($file, $exception_code)
	{
		$this->setExpectedException('LogicException', '', $exception_code);
		$this->exporter->crawl_php_file($file);
	}

	static public function validate_since_data()
	{
		return array(
			array('* @since 3.1.0-a1', '3.1.0-a1'),
			array('* @since 3.1.0-b3', '3.1.0-b3'),
			array('	* @since 3.1.0-b3', '3.1.0-b3'),
			array('* @since 3.1.0-RC2', '3.1.0-RC2'),
			array(' * @since 3.1.0-a1', '3.1.0-a1'),
		);
	}

	/**
	* @dataProvider validate_since_data
	*/
	public function test_validate_since($since, $expected)
	{
		$this->assertEquals($expected, $this->exporter->validate_since($since));
	}

	static public function validate_since_throws_data()
	{
		return array(
			array('* @since 3.1.0-a1 '),
			array('* @since 3.1.0-a1 bertie is cool'),
			array('bertie* @since 3.1.0-a1'),
			array('* @since 3.1-A2'),
			array('* @since 3.1.0-rc1'),
		);
	}

	/**
	* @dataProvider validate_since_throws_data
	* @expectedException LogicException
	*/
	public function test_validate_since_throws($since)
	{
		$this->exporter->validate_since($since);
	}

	static public function validate_event_data()
	{
		return array(
			array('test.event', '* @event test.event', 'test.event'),
			array('test.event2', '	* @event test.event2', 'test.event2'),
			array('test.event', ' * @event test.event', 'test.event'),
		);
	}

	/**
	* @dataProvider validate_event_data
	*/
	public function test_validate_event($event_name, $event, $expected)
	{
		$this->assertEquals($expected, $this->exporter->validate_event($event_name, $event));
	}

	static public function validate_event_throws_data()
	{
		return array(
			array('test.event', '* @event test.event bertie is cool', 2),
			array('test.event', 'bertie* @event test.event', 2),
		);
	}

	/**
	* @dataProvider validate_event_throws_data
	* @expectedException LogicException
	*/
	public function test_validate_event_throws($event_name, $event, $exception_code)
	{
		$this->setExpectedException('LogicException', '', $exception_code);
		$this->exporter->validate_event($event_name, $event);
	}

	static public function validate_vars_docblock_array_data()
	{
		return array(
			array(array('abc', 'def'), array('abc', 'def')),
		);
	}

	/**
	* @dataProvider validate_vars_docblock_array_data
	*/
	public function test_validate_vars_docblock_array($vars_array, $vars_docblock)
	{
		$this->assertNull($this->exporter->validate_vars_docblock_array($vars_array, $vars_docblock));
	}

	static public function validate_vars_docblock_array_throws_data()
	{
		return array(
			array(array('abc', 'def'), array()),
			array(array('abc', 'def'), array('abc')),
			array(array('abc', 'defg'), array('abc', 'def')),
			array(array('abc'), array('abc', 'def')),
			array(array(), array('abc', 'def')),
		);
	}

	/**
	* @dataProvider validate_vars_docblock_array_throws_data
	* @expectedException LogicException
	*/
	public function test_validate_vars_docblock_array_throws($vars_array, $vars_docblock)
	{
		$this->exporter->validate_vars_docblock_array($vars_array, $vars_docblock);
	}

	static public function get_dispatch_name_data()
	{
		return array(
			array("\$phpbb_dispatcher->dispatch('dispatch.one2');", 'dispatch.one2'),
			array("\t\$phpbb_dispatcher->dispatch('dispatch.one2.thr_ee4');", 'dispatch.one2.thr_ee4'),
			array("\$this->dispatcher->dispatch('dispatch.one2');", 'dispatch.one2'),
			array("\$phpbb_dispatcher->dispatch('dis_patch.one');", 'dis_patch.one'),
		);
	}

	/**
	* @dataProvider get_dispatch_name_data
	*/
	public function test_get_dispatch_name($event_line, $expected)
	{
		$this->exporter->set_content(array($event_line));
		$this->assertEquals($expected, $this->exporter->get_event_name(0, true));
	}

	static public function get_dispatch_name_throws_data()
	{
		return array(
			array("\$phpbb_dispatcher->dispatch();"),
			array("\$phpbb_dispatcher->dispatch('');"),
			array("\$phpbb_dispatcher->dispatch('dispatch.2one');"),
			array("\$phpbb_dispatcher->dispatch('dispatch');"),
		);
	}

	/**
	* @dataProvider get_dispatch_name_throws_data
	* @expectedException LogicException
	*/
	public function test_get_dispatch_name_throws($event_line)
	{
		$this->exporter->set_content(array($event_line));
		$this->exporter->get_event_name(0, true);
	}

	static public function get_trigger_event_name_data()
	{
		return array(
			array("extract(\$phpbb_dispatcher->trigger_event('dispatch.one2', compact(\$vars)));", 'dispatch.one2'),
			array("\textract(\$phpbb_dispatcher->trigger_event('dispatch.one2.thr_ee4', compact(\$vars)));", 'dispatch.one2.thr_ee4'),
			array("extract(\$this->dispatcher->trigger_event('dispatch.one2', compact(\$vars)));", 'dispatch.one2'),
			array("extract(\$phpbb_dispatcher->trigger_event('dis_patch.one', compact(\$vars)));", 'dis_patch.one'),
		);
	}

	/**
	* @dataProvider get_trigger_event_name_data
	*/
	public function test_get_trigger_event_name($event_line, $expected)
	{
		$this->exporter->set_content(array($event_line));
		$this->assertEquals($expected, $this->exporter->get_event_name(0, false));
	}

	static public function get_trigger_event_name_throws_data()
	{
		return array(
			array("extract(\$phpbb_dispatcher->trigger_event());"),
			array("extract(\$phpbb_dispatcher->trigger_event(''));"),
			array("extract(\$phpbb_dispatcher->trigger_event('dispatch.2one'));"),
			array("extract(\$phpbb_dispatcher->trigger_event('dispatch'));"),
			array("extract(\$phpbb_dispatcher->trigger_event('dispatch.one', \$vars));"),
			array("extract(\$phpbb_dispatcher->trigger_event('dispatch.one', compact(\$var)));"),
			array("extract(\$phpbb_dispatcher->trigger_event('dispatch.one', compact(\$array)));"),
			array("\$phpbb_dispatcher->trigger_event('dis_patch.one', compact(\$vars));", 'dis_patch.one'),
		);
	}

	/**
	* @dataProvider get_trigger_event_name_throws_data
	* @expectedException LogicException
	*/
	public function test_get_trigger_event_name_throws($event_line)
	{
		$this->exporter->set_content(array($event_line));
		$this->exporter->get_event_name(0, false);
	}

	static public function get_vars_from_array_data()
	{
		return array(
			array(
				array(
					'/**',
					'*/',
					'$vars = array(\'bertie\');',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				3,
				array('bertie'),
			),
			array(
				array(
					"\t/**",
					"\t*/",
					"\t\$vars = array('_Strange123', 'phpBB3_Test');",
					"\t\$this->dispatcher->dispatch('test');",
				),
				3,
				array('_Strange123', 'phpBB3_Test'),
			),
		);
	}

	/**
	* @dataProvider get_vars_from_array_data
	*/
	public function test_get_vars_from_array($lines, $event_line, $expected)
	{
		$this->exporter->set_current_event('', $event_line);
		$this->exporter->set_content($lines);
		$this->assertEquals($expected, $this->exporter->get_vars_from_array());
	}

	static public function get_vars_from_array_throws_data()
	{
		return array(
			array(
				array(
					'/**',
					'*/',
					'$phpbb_dispatcher->trigger_event(\'test\', compact($vars));',
				),
				2,
				1,
			),
			array(
				array(
					'/**',
					'*/',
					'$vars = $bertie;',
					'$phpbb_dispatcher->trigger_event(\'test\', compact($vars));',
				),
				3,
				1,
			),
			array(
				array(
					'/**',
					'*/',
					'$vars = array(\'$bertie\');',
					'$phpbb_dispatcher->trigger_event(\'test\', compact($vars));',
				),
				3,
				1,
			),
			array(
				array(
					'/**',
					'*/',
					'$vars = array();',
					'$phpbb_dispatcher->trigger_event(\'test\', compact($vars));',
				),
				3,
				1,
			),
			array(
				array(
					'/**',
					'*/',
					'$vars = array(\'t1\', \'t2\', \'t3\', \'t4\', \'t5\', \'t6\', \'t7\');',
					'$phpbb_dispatcher->trigger_event(\'test\', compact($vars));',
				),
				3,
				2,
			),
			array(
				array(
					'/**',
					'*/',
					'$vars = array(\'test2\', \'\');',
					'$phpbb_dispatcher->trigger_event(\'test\', compact($vars));',
				),
				3,
				3,
			),
			array(
				array(
					'/**',
					'*/',
					'$vars = array(\'bertie\'\');',
					'$phpbb_dispatcher->trigger_event(\'test\', compact($vars));',
				),
				3,
				3,
			),
			array(
				array(
					'/**',
					'*/',
					'$vars = array(\'bertie\',\'basically_valid\');',
					'$phpbb_dispatcher->trigger_event(\'test\', compact($vars));',
				),
				3,
				3,
			),
		);
	}

	/**
	* @dataProvider get_vars_from_array_throws_data
	* @expectedException LogicException
	*/
	public function test_get_vars_from_array_throws($lines, $event_line, $exception_code)
	{
		$this->setExpectedException('LogicException', '', $exception_code);

		$this->exporter->set_current_event('', $event_line);
		$this->exporter->set_content($lines);
		$this->exporter->get_vars_from_array();
	}

	static public function get_vars_from_docblock_data()
	{
		return array(
			array(
				array(
					'/**',
					'* @var	int	name1	Description',
					'* @var	array	name2	Description test',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				4,
				array('name1', 'name2'),
			),
		);
	}

	/**
	* @dataProvider get_vars_from_docblock_data
	*/
	public function test_get_vars_from_docblock($lines, $event_line, $expected)
	{
		$this->exporter->set_current_event('', $event_line);
		$this->exporter->set_content($lines);
		$this->assertEquals($expected, $this->exporter->get_vars_from_docblock());
	}

	static public function get_vars_from_docblock_throws_data()
	{
		return array(
			array(
				array(
					'$vars = array();',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				1,
				2,
			),
			array(
				array(
					'/**',
					'* @var	int	name1',
					'* @var	array	name2	Description test',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				4,
				1,
			),
			array(
				array(
					'/**',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				2,
				3,
			),
			array(
				array(
					'/**',
					'* @var	int	name1	Description',
					'* @var	array	$name2	Description',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				4,
				4,
			),
		);
	}

	/**
	* @dataProvider get_vars_from_docblock_throws_data
	* @expectedException LogicException
	*/
	public function test_get_vars_from_docblock_throws($lines, $event_line, $exception_code)
	{
		$this->setExpectedException('LogicException', '', $exception_code);

		$this->exporter->set_current_event('', $event_line);
		$this->exporter->set_content($lines);
		$this->exporter->get_vars_from_docblock();
	}

	static public function find_since_data()
	{
		return array(
			array(
				array(
					'/**',
					'* @since 3.1.0-a1',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				3,
				1,
			),
			array(
				array(
					'* @since 3.1.0-a1',
					'/**',
					'* @since 3.1.0-a1',
					'* @changed 3.1.0-a2',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				5,
				2,
			),
		);
	}

	/**
	* @dataProvider find_since_data
	*/
	public function test_find_since($lines, $event_line, $expected)
	{
		$this->exporter->set_current_event('', $event_line);
		$this->exporter->set_content($lines);
		$this->assertEquals($expected, $this->exporter->find_since());
	}

	static public function find_since_throws_data()
	{
		return array(
			array(
				array(
					'/**',
					'* @since 3.1.0-a1',
					'*/',
					'/**',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				5,
				1,
			),
			array(
				array(
					'/**',
					'* @changed 3.1.0-a1',
					'* @changed 3.1.0-a2',
					'* @changed 3.1.0-a3',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				5,
				2,
			),
			array(
				array(
					'/**',
					'* @since 3.1.0-a2',
					'* @var',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				4,
				3,
			),
			array(
				array(
					'/**',
					'* @since 3.1.0-a2',
					'* @event',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				4,
				3,
			),
		);
	}

	/**
	* @dataProvider find_since_throws_data
	* @expectedException LogicException
	*/
	public function test_find_since_throws($lines, $event_line, $exception_code)
	{
		$this->setExpectedException('LogicException', '', $exception_code);

		$this->exporter->set_current_event('', $event_line);
		$this->exporter->set_content($lines);
		$this->exporter->find_since();
	}

	static public function find_description_data()
	{
		return array(
			array(
				array(
					'/**',
					'* Hello Bertie!',
					'* @since 3.1.0-a1',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				4,
				1,
			),
			array(
				array(
					'	/**',
					'	* Hello Bertie!',
					'	*',
					'	* @since 3.1.0-a1',
					'	* @changed 3.1.0-a2',
					'	*/',
					'	$phpbb_dispatcher->dispatch(\'test\');',
				),
				6,
				1,
			),
		);
	}

	/**
	* @dataProvider find_description_data
	*/
	public function test_find_description($lines, $event_line, $expected)
	{
		$this->exporter->set_current_event('', $event_line);
		$this->exporter->set_content($lines);
		$this->assertEquals($expected, $this->exporter->find_description());
	}

	static public function find_description_throws_data()
	{
		return array(
			array(
				array(
					'$vars = array();',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				1,
				1,
			),
			array(
				array(
					'/**',
					'* @changed 3.1.0-a1',
					'* @changed 3.1.0-a2',
					'* @changed 3.1.0-a3',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				5,
				2,
			),
			array(
				array(
					'/**',
					'*',
					'* @since 3.1.0-a2',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				4,
				2,
			),
			array(
				array(
					'/**',
					'* ',
					'* @event',
					'*/',
					'$phpbb_dispatcher->dispatch(\'test\');',
				),
				4,
				2,
			),
		);
	}

	/**
	* @dataProvider find_description_throws_data
	* @expectedException LogicException
	*/
	public function test_find_description_throws($lines, $event_line, $exception_code)
	{
		$this->setExpectedException('LogicException', '', $exception_code);

		$this->exporter->set_current_event('', $event_line);
		$this->exporter->set_content($lines);
		$this->exporter->find_description();
	}
}
