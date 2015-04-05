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

function after_assert_phpbb3_7275($vars)
{
	extract($vars);
	decode_message($parsed_text);
	$test->assertSame($original, $parsed_text);
}
