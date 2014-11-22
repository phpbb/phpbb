<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

function before_assert_phpbb3_3981($vars)
{
	if (!function_exists('idn_to_ascii'))
	{
		extract($vars);
		$test->markTestSkipped('International URLs need idn_to_ascii()');
	}
}
