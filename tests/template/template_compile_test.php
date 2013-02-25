<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_template_template_compile_test extends phpbb_test_case
{
	private $template_compile;
	private $template_path;

	protected function setUp()
	{
		$this->template_compile = new phpbb_template_compile(false, null, $this->style_resource_locator, '');
		$this->template_path = dirname(__FILE__) . '/templates';
	}

	public function test_in_phpbb()
	{
		$output = $this->template_compile->compile_file($this->template_path . '/trivial.html');
		$this->assertTrue(strlen($output) > 0);
		$statements = explode(';', $output);
		$first_statement = $statements[0];
		$this->assertTrue(!!preg_match('#if.*defined.*IN_PHPBB.*exit#', $first_statement));
	}
}
