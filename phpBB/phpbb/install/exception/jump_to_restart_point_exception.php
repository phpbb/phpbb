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

namespace phpbb\install\exception;

class jump_to_restart_point_exception extends installer_exception
{
	/**
	 * @var string
	 */
	protected $restart_point_name;

	/**
	 * Constructor
	 *
	 * @param string $restart_point_name
	 */
	public function __construct($restart_point_name)
	{
		$this->restart_point_name = $restart_point_name;

		parent::__construct();
	}

	/**
	 * Returns the restart point's name
	 *
	 * @return string
	 */
	public function get_restart_point_name()
	{
		return $this->restart_point_name;
	}
}
