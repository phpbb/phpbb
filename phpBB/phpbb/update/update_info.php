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

namespace phpbb\update;

use phpbb\update\update_info_provider_interface;

class update_info implements update_info_provider_interface
{
	public function get_version_url(): string
	{
		return 'version.phpbb.com';
	}

	public function get_version_path(): string
	{
		return '/phpbb';
	}

	public function get_download_url(): string
	{
		return 'https://download.phpbb.com/pub/release/';
	}
}
