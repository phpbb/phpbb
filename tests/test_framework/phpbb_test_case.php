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

	protected function setUp(): void
	{
		$this->setBackupStaticPropertiesExcludeList([
			'SebastianBergmann\CodeCoverage\CodeCoverage' => ['instance'],
			'SebastianBergmann\CodeCoverage\Filter' => ['instance'],
			'SebastianBergmann\CodeCoverage\Util' => ['ignoredLines', 'templateMethods'],
			'SebastianBergmann\Timer\Timer' => ['startTimes'],
			'PHP_Token_Stream' => ['customTokens'],
			'PHP_Token_Stream_CachingFactory' => ['cache'],

			'phpbb_database_test_case' => ['already_connected', 'last_post_timestamp'],
		]);
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
}
