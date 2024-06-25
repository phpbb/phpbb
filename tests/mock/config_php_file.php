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

class phpbb_mock_config_php_file extends \phpbb\config_php_file {
	public function __construct()
	{
	}

	protected function load_config_file()
	{
		if (!$this->config_loaded)
		{
			$this->config_data = phpbb_test_case_helpers::get_test_config();
			$this->config_loaded = true;
		}
	}
}
