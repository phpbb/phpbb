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

function before_assert_phpbb3_3981($vars)
{
	if (!function_exists('idn_to_ascii'))
	{
		extract($vars);
		$test->markTestSkipped('International URLs need idn_to_ascii()');
	}
}
