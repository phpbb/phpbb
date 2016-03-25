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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_acp.php';

class phpbb_functions_insert_config_array_test extends phpbb_test_case
{
	public function config_display_vars()
	{
		return array(
			'legend1'		=> '',
			'acp_config_1'	=> array(),
			'acp_config_2'	=> array(),
			'acp_config_3'	=> array(),
			'acp_config_4'	=> array(),
			'acp_config_5'	=> array(),
		);
	}

	public function insert_config_array_data()
	{
		return array(
			array( // Add a new config after 1st array item
				array('new_config_1' => array()),
				array('after' => 'legend1'),
				array(
					'legend1'		=> '',
					'new_config_1'	=> array(),
					'acp_config_1'	=> array(),
					'acp_config_2'	=> array(),
					'acp_config_3'	=> array(),
					'acp_config_4'	=> array(),
					'acp_config_5'	=> array(),
				),
			),
			array( // Add a new config after last array item
				array('new_config_1' => array()),
				array('after' => 'acp_config_5'),
				array(
					'legend1'		=> '',
					'acp_config_1'	=> array(),
					'acp_config_2'	=> array(),
					'acp_config_3'	=> array(),
					'acp_config_4'	=> array(),
					'acp_config_5'	=> array(),
					'new_config_1'	=> array(),
				),
			),
			array( // Add a new config before 2nd array item
				array('new_config_1' => array()),
				array('before' => 'acp_config_1'),
				array(
					'legend1'		=> '',
					'new_config_1'	=> array(),
					'acp_config_1'	=> array(),
					'acp_config_2'	=> array(),
					'acp_config_3'	=> array(),
					'acp_config_4'	=> array(),
					'acp_config_5'	=> array(),
				),
			),
			array( // Add a new config before last config item
				array('new_config_1' => array()),
				array('before' => 'acp_config_5'),
				array(
					'legend1'		=> '',
					'acp_config_1'	=> array(),
					'acp_config_2'	=> array(),
					'acp_config_3'	=> array(),
					'acp_config_4'	=> array(),
					'new_config_1'	=> array(),
					'acp_config_5'	=> array(),
				),
			),
			array( // When an array key does not exist
				array('new_config_1' => array()),
				array('after' => 'foobar'),
				array(
					'legend1'		=> '',
					'acp_config_1'	=> array(),
					'acp_config_2'	=> array(),
					'acp_config_3'	=> array(),
					'acp_config_4'	=> array(),
					'acp_config_5'	=> array(),
				),
			),
			array( // When after|before is not used correctly (defaults to after)
				array('new_config_1' => array()),
				array('foobar' => 'acp_config_1'),
				array(
					'legend1'		=> '',
					'acp_config_1'	=> array(),
					'new_config_1'	=> array(),
					'acp_config_2'	=> array(),
					'acp_config_3'	=> array(),
					'acp_config_4'	=> array(),
					'acp_config_5'	=> array(),
				),
			),
			array( // Add a new config set after the last array item
				array(
					'legend2' => array(),
					'new_config_1' => array(),
					'new_config_2' => array(),
					'new_config_3' => array(),
				),
				array('after' => 'acp_config_5'),
				array(
					'legend1'		=> '',
					'acp_config_1'	=> array(),
					'acp_config_2'	=> array(),
					'acp_config_3'	=> array(),
					'acp_config_4'	=> array(),
					'acp_config_5'	=> array(),
					'legend2' => array(),
					'new_config_1' => array(),
					'new_config_2' => array(),
					'new_config_3' => array(),
				),
			),
		);
	}

	/**
	* @dataProvider insert_config_array_data
	*/
	public function test_insert_config_array($new_config, $position, $expected)
	{
		$config_array = $this->config_display_vars();
		$new_config_array = phpbb_insert_config_array($config_array, $new_config, $position);

		$this->assertSame($expected, $new_config_array);
	}
}
