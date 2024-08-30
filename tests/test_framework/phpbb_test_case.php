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

use PHPUnit\Framework\TestCase;

class phpbb_test_case extends TestCase
{
	protected $test_case_helpers;

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->backupStaticAttributesExcludeList += [
			'SebastianBergmann\CodeCoverage\CodeCoverage' => ['instance'],
			'SebastianBergmann\CodeCoverage\Filter' => ['instance'],
			'SebastianBergmann\CodeCoverage\Util' => ['ignoredLines', 'templateMethods'],
			'SebastianBergmann\Timer\Timer' => ['startTimes'],
			'PHP_Token_Stream' => ['customTokens'],
			'PHP_Token_Stream_CachingFactory' => ['cache'],

			'phpbb_database_test_case' => ['already_connected', 'last_post_timestamp'],
		];

		set_error_handler([$this, 'trigger_error_callback']);
	}

	public function get_test_case_helpers()
	{
		if (!$this->test_case_helpers)
		{
			$this->test_case_helpers = new phpbb_test_case_helpers($this);
		}

		return $this->test_case_helpers;
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$this->get_test_case_helpers()->setExpectedTriggerError($errno, $message);
	}

	/**
	 * Passing E_USER_ERROR to trigger_error() is deprecated as of PHP 8.4, so it causes E_DEPRECATED
	 * Use trigger_error() callback function to workaround this by handling E_USER_ERROR and suppressing E_DEPRECATED
	 * "Passing E_USER_ERROR to trigger_error() is deprecated since 8.4, throw an exception or call exit with a string message instead"
	 * 
	 */
	public function trigger_error_callback($errno, $errstr, $errfile, $errline)
	{
		// $errstr may need to be escaped
		$errstr = htmlspecialchars($errstr);

		switch ($errno) {
			case E_USER_ERROR:
				echo $errstr;
				exit();
			break;

			case E_DEPRECATED:
				return true;
			break;

			default:
				return false;
			break;
		}
	}
}
