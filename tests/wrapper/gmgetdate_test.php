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

class phpbb_wrapper_gmgetdate_test extends phpbb_test_case
{
	public static function phpbb_gmgetdate_data()
	{
		return array(
			array(''),
			array('UTC'),
			array('Europe/Berlin'),
			array('America/Los_Angeles'),
			array('Antarctica/South_Pole'),
		);
	}

	/**
	 * @dataProvider phpbb_gmgetdate_data
	 */
	public function test_phpbb_gmgetdate($timezone_identifier)
	{
		if ($timezone_identifier)
		{
			$current_timezone = date_default_timezone_get();
			date_default_timezone_set($timezone_identifier);
		}

		$expected = time();

		$date_array = phpbb_gmgetdate($expected);

		$actual = gmmktime(
			$date_array['hours'],
			$date_array['minutes'],
			$date_array['seconds'],
			$date_array['mon'],
			$date_array['mday'],
			$date_array['year']
		);

		// Calling second-granularity time functions twice isn't guaranteed to
		// give the same results. As long as they're in the right order, allow
		// a 1 second difference.
		$this->assertGreaterThanOrEqual(
			$expected, $actual,
			'Expected second time to be after (or equal to) the previous one'
		);
		$this->assertLessThanOrEqual(
			1,
			abs($actual - $expected),
			"Expected $actual to be within 1 second of $expected."
		);

		if (isset($current_timezone))
		{
			date_default_timezone_set($current_timezone);
		}
	}
}
