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

class module_not_found_exception extends installer_exception
{
	/**
	 * @var string
	 */
	private $module_service_name;

	/**
	 * Constructor
	 *
	 * @param string	$module_service_name	The name of the missing installer module
	 */
	public function __construct($module_service_name)
	{
		$this->module_service_name = $module_service_name;
	}

	/**
	 * Returns the missing installer module's service name
	 *
	 * @return string
	 */
	public function get_module_service_name()
	{
		return $this->module_service_name;
	}
}
