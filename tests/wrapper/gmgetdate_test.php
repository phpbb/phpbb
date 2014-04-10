<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

		$this->assertEquals($expected, $actual);

		if (isset($current_timezone))
		{
			date_default_timezone_set($current_timezone);
		}
	}
}
