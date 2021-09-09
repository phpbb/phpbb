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

namespace phpbb\files\types;

class foo extends \phpbb\files\types\upload
{
	static public $tempnam_path;
}

function tempnam($one, $two)
{
	if (empty(foo::$tempnam_path))
	{
		return \tempnam($one, $two);
	}
	else
	{
		return foo::$tempnam_path;
	}
}
