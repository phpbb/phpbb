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

namespace phpbb\extension;

class manager_mock extends \phpbb\extension\manager
{
	public function __construct()
	{
	}

	public function all_enabled($phpbb_relative = true)
	{
		return array(
			'vendor/enabled' => __DIR__ . '/ext/vendor/enabled/',
			'vendor/enabled-2' => __DIR__ . '/ext/vendor/enabled-2/',
			'vendor/enabled-3' => __DIR__ . '/ext/vendor/enabled-3/',
			'vendor/enabled_4' => __DIR__ . '/ext/vendor/enabled_4/',
		);
	}
}
