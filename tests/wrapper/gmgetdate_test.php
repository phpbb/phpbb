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
	public function test_gmgetdate()
	{
		$this->run_gmgetdate_assertion();
		$this->run_test_with_timezone('UTC');
		$this->run_test_with_timezone('Europe/Berlin');
		$this->run_test_with_timezone('America/Los_Angeles');
		$this->run_test_with_timezone('Antarctica/South_Pole');
	}

	protected function run_test_with_timezone($timezone_identifier)
	{
		$current_timezone = date_default_timezone_get();

		date_default_timezone_set($timezone_identifier);
		$this->run_gmgetdate_assertion();
		date_default_timezone_set($current_timezone);
	}

	protected function run_gmgetdate_assertion()
	{
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
	}
}
