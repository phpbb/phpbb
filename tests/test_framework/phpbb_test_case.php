<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_test_case extends PHPUnit_Framework_TestCase
{
	protected $test_case_helpers;

	public function init_test_case_helpers()
	{
		if (!$this->test_case_helpers)
		{
			$this->test_case_helpers = new phpbb_test_case_helpers($this);
		}
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$this->init_test_case_helpers();
		$this->test_case_helpers->setExpectedTriggerError($errno, $message);
	}
}
