<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_test_case_helpers
{
	protected $expectedTriggerError = false;

	protected $test_case;

	public function __construct($test_case)
	{
		$this->test_case = $test_case;
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$exceptionName = '';
		switch ($errno)
		{
			case E_NOTICE:
			case E_STRICT:
				PHPUnit_Framework_Error_Notice::$enabled = true;
				$exceptionName = 'PHPUnit_Framework_Error_Notice';
			break;

			case E_WARNING:
				PHPUnit_Framework_Error_Warning::$enabled = true;
				$exceptionName = 'PHPUnit_Framework_Error_Warning';
			break;

			default:
				$exceptionName = 'PHPUnit_Framework_Error';
			break;
		}
		$this->expectedTriggerError = true;
		$this->test_case->setExpectedException($exceptionName, (string) $message, $errno);
	}

	public function makedirs($path)
	{
		mkdir($path, 0777, true);
	}
}
