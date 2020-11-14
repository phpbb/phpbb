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
	static protected $phpunit_version; 

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		self::$phpunit_version = PHPUnit\Runner\Version::id();

		$backupStaticAttributesBlacklist = [
			'SebastianBergmann\CodeCoverage\CodeCoverage' => ['instance'],
			'SebastianBergmann\CodeCoverage\Filter' => ['instance'],
			'SebastianBergmann\CodeCoverage\Util' => ['ignoredLines', 'templateMethods'],
			'SebastianBergmann\Timer\Timer' => ['startTimes'],
			'PHP_Token_Stream' => ['customTokens'],
			'PHP_Token_Stream_CachingFactory' => ['cache'],

			'phpbb_database_test_case' => ['already_connected', 'last_post_timestamp'],
		];
		$this->excludeBackupStaticAttributes($backupStaticAttributesBlacklist);
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
	 * PHPUnit deprecates several methods and properties in its recent versions
	 * Provide BC layer to be able to test in multiple environment settings
	 */
	public function excludeBackupStaticAttributes($attributes_array)
	{
		if (version_compare(self::$phpunit_version, '9.0', '>='))
		{
			$this->backupStaticAttributesExcludeList += $attributes_array;
		}
		else
		{
			$this->backupStaticAttributesBlacklist += $attributes_array;
		}
	}

	/**
	 * PHPUnit deprecates several methods and properties in its recent versions
	 * Provide BC layer to be able to test in multiple environment settings
	 */
	public static function assertRegExp(string $pattern, string $string, string $message = ''): void
	{
		if (version_compare(self::$phpunit_version, '9.0', '>='))
		{
			parent::assertMatchesRegularExpression($pattern, $string, $message);
		}
		else
		{
			parent::assertRegExp($pattern, $string, $message);
		}
	}

	/**
	 * PHPUnit deprecates several methods and properties in its recent versions
	 * Provide BC layer to be able to test in multiple environment settings
	 */
	public function expectException(string $exception): void
	{
		if (version_compare(self::$phpunit_version, '9.0', '>='))
		{
			switch ($exception) {
				case PHPUnit\Framework\Error\Deprecated::class:
					parent::expectDeprecation();
				break;

				case PHPUnit\Framework\Error\Error::class:
					parent::expectError();
				break;

				case PHPUnit\Framework\Error\Notice::class:
					parent::expectNotice();
				break;

				case PHPUnit\Framework\Error\Warning::class:
					parent::expectWarning();
				break;

				default:
					parent::expectException($exception);
				break;
			}
		}
		else
		{
			parent::expectException($exception);
		}
	}

	/**
	 * PHPUnit deprecates several methods and properties in its recent versions
	 * Provide BC layer to be able to test in multiple environment settings
	 */
	public static function assertFileNotExists(string $filename, string $message = ''): void
	{
		if (version_compare(self::$phpunit_version, '9.0', '>='))
		{
			parent::assertFileDoesNotExist($filename, $message);
		}
		else
		{
			parent::assertFileNotExists($filename, $message);
		}
	}
}
