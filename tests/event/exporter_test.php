<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/develop/event_exporter.php';

class phpbb_event_exporter_test extends phpbb_test_case
{
	/** @var \event_exporter */
	protected $exporter;

	public function setUp()
	{
		parent::setUp();
		$this->exporter = new \event_exporter(dirname(__FILE__) . '/fixtures/');
	}

	static public function check_for_events_data()
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
				'legacy_alpha1_version.test',
				array(
					'legacy_alpha1_version.dispatch'	=> array(
						'event'			=> 'legacy_alpha1_version.dispatch',
						'file'			=> 'legacy_alpha1_version.test',
						'arguments'		=> array(),
						'since'			=> '3.1.0-a1',
						'description'	=> 'Description',
					),
				),
			),
		);
	}

	/**
	* @dataProvider check_for_events_data
	*/
	public function test_check_for_events($file, $expected)
	{
		$this->assertEquals($expected, $this->exporter->check_for_events($file));
	}

	static public function validate_since_data()
	{
		return array(
			array('* @since 3.1.0-a1', '3.1.0-a1'),
			array('* @since 3.1.0-b3', '3.1.0-b3'),
			array('	* @since 3.1.0-b3', '3.1.0-b3'),
			array('* @since 3.1-A1', '3.1.0-a1'),
		);
	}

	/**
	* @dataProvider validate_since_data
	*/
	public function test_validate_since($since, $expected)
	{
		$this->assertEquals($expected, $this->exporter->validate_since('', '', $since));
	}

	static public function validate_since_throws_data()
	{
		return array(
			array(' * @since 3.1.0-a1', 1),
			array('* @since 3.1.0-a1 ', 1),
			array('* @since 3.1.0-a1 bertie is cool', 2),
			array('bertie* @since 3.1.0-a1', 2),
			array('* @since 3.1-A2', 2),
			array('* @since 3.1-B3', 2),
		);
	}

	/**
	* @dataProvider validate_since_throws_data
	* @expectedException LogicException
	*/
	public function test_validate_since_throws($since, $exception_code)
	{
		$this->setExpectedException('LogicException', '', $exception_code);
		$this->exporter->validate_since('', '', $since);
	}

	static public function validate_event_data()
	{
		return array(
			array('test.event', '* @event test.event', 'test.event'),
			array('test.event2', '	* @event test.event2', 'test.event2'),
		);
	}

	/**
	* @dataProvider validate_event_data
	*/
	public function test_validate_event($event_name, $event, $expected)
	{
		$this->assertEquals($expected, $this->exporter->validate_event('', $event_name, $event));
	}

	static public function validate_event_throws_data()
	{
		return array(
			array('test.event', ' * @event test.event', 1),
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
		$this->exporter->validate_event('', $event_name, $event);
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
		$this->assertEquals($expected, $this->exporter->find_since('', '', $lines, $event_line));
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
		$this->exporter->find_since('', '', $lines, $event_line);
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
		$this->assertEquals($expected, $this->exporter->find_description('', '', $lines, $event_line));
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
		$this->exporter->find_description('', '', $lines, $event_line);
	}
}
