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

class search_backend_mock_not_available extends search_backend_mock
{
	public function get_name(): string
	{
		return 'Mock unavailable search backend';
	}

	public function is_available(): bool
	{
		return false;
	}
}
