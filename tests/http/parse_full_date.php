<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_test_parse_http_date extends phpbb_test_case
{
	/**
	* @dataProvider parse_http_date_data()
	*/
	public function test_parse_http_date($expected, $date)
	{
		// php wants user to set a default timezone before invoking any
		// time-related functions. This might produce false positives
		// in some tests if the user sets the default timezone to utc.
		// Explicitly test in utc and a local timezone.
		//
		// Unfortunately, if default timezone is not set then
		// even date_default_timezone get produces a warning,
		// which causes phpunit to fail the test. It is apparently
		// not possible to save the state of no default timezone
		// being set. Thus, the following note:
		//
		// If you are getting a test failure here due to default
		// timezone not being set, set it in your php.ini
		// (to anything, this test does not care what its value
		// actually is).
		//
		// The upside of this mess is that it should be always
		// possible to retrieve the default timezone. Note that
		// date_default_timezone_get documentation does not specify
		// what value is returned if no timezone is ever set.
		//
		// Finally, date_default_timezone_get is php 5.1+.
		if (function_exists('date_default_timezone_get'))
		{
			$saved_timezone = date_default_timezone_get();

			// test in utc
			date_default_timezone_set('UTC');
			$this->assertEquals($expected, phpbb_parse_http_date($date));

			// test in a local timezone
			date_default_timezone_set('America/New_York');
			$this->assertEquals($expected, phpbb_parse_http_date($date));

			// php has no finally thus this may never get run
			// if the above assertions fail, screwing up
			// subsequent tests. Two mitigating circumstances are:
			// 1. This test would have failed correctly.
			// 2. We should probably not be relying on the
			// default timezone being set to any particular value
			// anywhere in our code.
			date_default_timezone_set($saved_timezone);
		}
		else
		{
			// no timezone support
			$this->assertEquals($expected, phpbb_parse_http_date($date));
		}
	}

	public function parse_http_date_data()
	{
		return array(
			// rfc 822, updated by rfc 1123
			array(784111777,	'Sun, 06 Nov 1994 08:49:37 GMT'),
			// try something in Y2K
			array(1446799777,	'Fri, 06 Nov 2015 08:49:37 GMT'),
			// rfc 850, obseleted by rfc 1036
			array(784111777,	'Sunday, 06-Nov-94 08:49:37 GMT'),
			// in Y2K
			array(1446799777,	'Friday, 06-Nov-15 08:49:37 GMT'),
			// bogus input
			array(false,		'blarg'),
			// asctime is not supported
			array(false,		'Sun Nov  6 08:49:37 1994'),
		);
	}
}
