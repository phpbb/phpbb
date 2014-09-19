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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_functions_get_preg_expression_test extends phpbb_test_case
{
	public function data_path_remove_dot_trailing_slash()
	{
		return array(
			array('./../', '$2', '/..'),
			array('/../', '$2', '/..'),
			array('', '$2', ''),
			array('./', '$2', ''),
			array('/', '$2', ''),
			array('./../../', '$2', '/../..'),
			array('/../../', '$2', '/../..'),
			array('./dir/', '$2', '/dir'),
			array('./../dir/', '$2', '/../dir'),
		);
	}

	/**
	 * @dataProvider data_path_remove_dot_trailing_slash
	 */
	public function test_path_remove_dot_trailing_slash($input, $replace, $expected)
	{
		$this->assertSame($expected, preg_replace(get_preg_expression('path_remove_dot_trailing_slash'), $replace, $input));
	}
}
