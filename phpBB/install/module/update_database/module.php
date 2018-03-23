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

namespace phpbb\install\module\update_database;

class module extends \phpbb\install\module_base
{
	/**
	 * {@inheritdoc}
	 */
	public function get_navigation_stage_path()
	{
		return array('update', 0, 'update_database');
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_step_count()
	{
		return 0;
	}
}
