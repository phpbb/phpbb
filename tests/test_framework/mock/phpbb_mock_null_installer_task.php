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

class phpbb_mock_null_installer_task extends \phpbb\install\task_base
{
	public function run()
	{

	}

	static public function get_step_count()
	{
		return 0;
	}

	public function get_task_lang_name()
	{
		return '';
	}
}
