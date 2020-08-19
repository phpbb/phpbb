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

class reverse_update_data_test extends phpbb_test_case
{
	/** @var \phpbb\db\migration\helper */
	protected $helper;

	protected function setUp(): void
	{
		parent::setUp();

		$this->helper = new \phpbb\db\migration\helper();
	}

	public function update_data_provider()
	{
		return array(
			array(
				array(
					array('config.add', array('foobar', 1)),
					array('if', array(
						(false === true),
						array('permission.add', array('some_data')),
					)),
					array('config.remove', array('foobar')),
					array('custom', array(array($this, 'foo_bar'))),
					array('tool.method', array('test_data')),
				),
				array(
					array('tool.reverse', array('method', 'test_data')),
					array('config.reverse', array('remove', 'foobar')),
					array('config.reverse', array('add', 'foobar', 1)),
				),
			),
		);
	}

	/**
	 * @dataProvider update_data_provider
	 */
	public function test_get_schema_steps($data_changes, $expected)
	{
		$this->assertEquals($expected, $this->helper->reverse_update_data($data_changes));
	}
}
