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

class get_schema_steps_test extends phpbb_test_case
{
	public function setUp()
	{
		parent::setUp();

		$this->helper = new \phpbb\db\migration\helper();
	}

	public function schema_provider()
	{
		return array(
			array(
				array(
					'add_tables'	=> array(
						'foo'	=> array(
							'COLUMNS'			=> array(
								'foobar'			=> array('BOOL', 0),
								'foobar2'			=> array('BOOL', 0),
							),
							'PRIMARY_KEY'		=> array('foobar'),
						),
						'bar'	=> array(
							'COLUMNS'			=> array(
								'barfoo'			=> array('BOOL', 0),
								'barfoor2'			=> array('BOOL', 0),
							),
							'PRIMARY_KEY'		=> array('barfoo'),
						),
					),
					'drop_tables'	=> array('table1', 'table2', 'table3'),
					'add_index'	=> array(
						'table1'	=> array(
							'index1'	=> 'column1',
							'index2'	=> 'column2',
						),
						'table2'	=> array(
							'index1'	=> 'column1',
							'index2'	=> 'column2',
						),
					),
					'add_columns'	=> array(
						'table1'	=> array(
							'column1'	=> array('foo'),
							'column2'	=> array('bar'),
						),
					),
					'change_columns'	=> array(
						'table1'	=> array(
							'column1'	=> array('foo'),
							'column2'	=> array('bar'),
						),
					),
					'drop_columns'	=> array(
						'table1'	=> array(
							'column1',
							'column2',
						),
					),
					'add_unique_index'	=> array(
						'table1'	=> array(
							'index1'	=> 'column1',
							'index2'	=> 'column2',
						),
					),
					'drop_keys'	=> array(
						'table1'	=> array(
							'column1',
							'column2',
						),
					),
					'add_primary_keys'	=> array(
						'table1' => array('foo'),
						'table2' => array('bar'),
						'table3' => array('foobar'),
					),
				),
				array(
					array('dbtools.perform_schema_changes', array(array('drop_tables'	=> array('table1')))),
					array('dbtools.perform_schema_changes', array(array('drop_tables'	=> array('table2')))),
					array('dbtools.perform_schema_changes', array(array('drop_tables'	=> array('table3')))),
					array('dbtools.perform_schema_changes', array(array('add_tables'	=> array(
						'foo'	=> array(
							'COLUMNS'			=> array(
								'foobar'			=> array('BOOL', 0),
								'foobar2'			=> array('BOOL', 0),
							),
							'PRIMARY_KEY'		=> array('foobar'),
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_tables'	=> array(
						'bar'	=> array(
							'COLUMNS'			=> array(
								'barfoo'			=> array('BOOL', 0),
								'barfoor2'			=> array('BOOL', 0),
							),
							'PRIMARY_KEY'		=> array('barfoo'),
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('change_columns'	=> array(
						'table1'	=> array(
							'column1'	=> array('foo'),
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('change_columns'	=> array(
						'table1'	=> array(
							'column2'	=> array('bar'),
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_columns'	=> array(
						'table1'	=> array(
							'column1'	=> array('foo'),
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_columns'	=> array(
						'table1'	=> array(
							'column2'	=> array('bar'),
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('drop_keys'	=> array(
						'table1'	=> array(
							0 => 'column1',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('drop_keys'	=> array(
						'table1'	=> array(
							1 => 'column2',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('drop_columns'	=> array(
						'table1'	=> array(
							0 => 'column1',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('drop_columns'	=> array(
						'table1'	=> array(
							1 => 'column2',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_primary_keys'	=> array(
						'table1' => array('foo'),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_primary_keys'	=> array(
						'table2' => array('bar'),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_primary_keys'	=> array(
						'table3' => array('foobar'),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_unique_index'	=> array(
						'table1'	=> array(
							'index1'	=> 'column1',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_unique_index'	=> array(
						'table1'	=> array(
							'index2'	=> 'column2',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_index'	=> array(
						'table1'	=> array(
							'index1'	=> 'column1',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_index'	=> array(
						'table1'	=> array(
							'index2'	=> 'column2',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_index'	=> array(
						'table2'	=> array(
							'index1'	=> 'column1',
						),
					)))),
					array('dbtools.perform_schema_changes', array(array('add_index'	=> array(
						'table2'	=> array(
							'index2'	=> 'column2',
						),
					)))),
				),
			),
		);
	}

	/**
	 * @dataProvider schema_provider
	 */
	public function test_get_schema_steps($schema_changes, $expected)
	{
		$this->assertEquals($expected, $this->helper->get_schema_steps($schema_changes));
	}
}
