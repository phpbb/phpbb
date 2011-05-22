<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_functions_output_buffering_test extends phpbb_test_case
{
	public function test_when_disabled()
	{
		$result = $this->check(array('output_buffering' => 'off'));
		$this->assertEquals('false', $result);
	}

	public function test_when_disabled_with_case()
	{
		$result = $this->check(array('output_buffering' => 'Off'));
		$this->assertEquals('false', $result);
	}

	public function test_when_enabled()
	{
		$result = $this->check(array('output_buffering' => 'on'));
		$this->assertEquals('true', $result);
	}

	public function test_when_enabled_with_case()
	{
		$result = $this->check(array('output_buffering' => 'On'));
		$this->assertEquals('true', $result);
	}

	public function test_when_enabled_with_value()
	{
		$result = $this->check(array('output_buffering' => '4096'));
		$this->assertEquals('true', $result);
	}

	private function check($settings)
	{
		$php = $_SERVER['_'];
		$cmd = escapeshellcmd($php);
		$include_path = ini_get('include_path');
		$cmd .= ' -d include_path=' . escapeshellarg($include_path);
		foreach ($settings as $key => $value)
		{
			$cmd .= ' -d ' . escapeshellarg($key) . '=' . escapeshellarg($value);
		}
		$cmd .= ' ' . escapeshellarg(dirname(__FILE__) . '/output_buffering_check.php');
		$output = `$cmd`;
		return $output;
	}
}
