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

class phpbb_build_hidden_fields_for_query_params_test extends phpbb_test_case
{
	public function build_hidden_fields_for_query_params_test_data()
	{
		return array(
			// get
			// post
			// exclude
			// expected
			array(
				array('foo' => 'bar'),
				array(),
				array(),
				"<input type='hidden' name=\"foo\" value=\"bar\" />",
			),
			array(
				array('foo' => 'bar', 'a' => 'b'),
				array(),
				array(),
				"<input type='hidden' name=\"foo\" value=\"bar\" /><input type='hidden' name=\"a\" value=\"b\" />",
			),
			array(
				array('a' => 'quote"', 'b' => '<less>'),
				array(),
				array(),
				"<input type='hidden' name=\"a\" value='quote\"' /><input type='hidden' name=\"b\" value=\"&lt;less&gt;\" />",
			),
			array(
				array('a' => "quotes'\""),
				array(),
				array(),
				"<input type='hidden' name=\"a\" value=\"quotes'&quot;\" />",
			),
			array(
				array('foo' => 'bar', 'a' => 'b'),
				array('a' => 'c'),
				array(),
				"<input type='hidden' name=\"foo\" value=\"bar\" />",
			),
			// strict equality check
			array(
				array('foo' => 'bar', 'a' => '0'),
				array('a' => ''),
				array(),
				"<input type='hidden' name=\"foo\" value=\"bar\" />",
			),
		);
	}

	/**
	* @dataProvider build_hidden_fields_for_query_params_test_data
	*/
	public function test_build_hidden_fields_for_query_params($get, $post, $exclude, $expected)
	{
		$request = new phpbb_mock_request($get, $post);
		$result = phpbb_build_hidden_fields_for_query_params($request, $exclude);

		$this->assertEquals($expected, $result);
	}
}
