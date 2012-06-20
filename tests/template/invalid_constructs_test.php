<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_invalid_constructs_test extends phpbb_template_template_test_case
{
	public function template_data()
	{
		return array(
			array(
				'Unknown tag',
				'invalid/unknown_tag.html',
				array(),
				array(),
				array(),
				'invalid/output/unknown_tag.html',
			),
			/*
			* Produces a parse error which is fatal, therefore
			* destroying the test suite.
			array(
				'ENDIF without IF',
				'invalid/endif_without_if.html',
				array(),
				array(),
				array(),
				'invalid/output/endif_without_if.html',
			),
			*/
		);
	}

	public function template_data_error()
	{
		return array(
			array(
				'Include a nonexistent file',
				'invalid/include_nonexistent_file.html',
				array(),
				array(),
				array(),
				E_USER_ERROR,
				'invalid/output/include_nonexistent_file.html',
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_template($description, $file, $vars, $block_vars, $destroy, $expected)
	{
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.php';

		$this->assertFileNotExists($cache_file);

		$expected = file_get_contents(dirname(__FILE__) . '/templates/' . $expected);
		// apparently the template engine does not put
		// the trailing newline into compiled templates
		$expected = trim($expected);
		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $cache_file);
	}

	/**
	* @dataProvider template_data_error
	*/
	public function test_template_error($description, $file, $vars, $block_vars, $destroy, $error, $expected)
	{
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.php';

		$this->assertFileNotExists($cache_file);

		$expected = file_get_contents(dirname(__FILE__) . '/templates/' . $expected);
		// apparently the template engine does not put
		// the trailing newline into compiled templates
		$expected = trim($expected);
		$this->setExpectedTriggerError($error, $expected);
		$this->run_template($file, $vars, $block_vars, $destroy, '', $cache_file);
	}
}
