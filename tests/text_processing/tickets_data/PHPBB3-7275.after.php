<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

function after_assert_phpbb3_7275($vars)
{
	extract($vars);
	decode_message($parsed_text);
	$test->assertSame($original, $parsed_text);
}
