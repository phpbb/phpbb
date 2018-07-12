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

namespace phpbb\storage\provider;

interface provider_interface
{
	/**
	 * Gets adapter name
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Gets adapter class
	 *
	 * @return \phpbb\storage\adapter\adapter_interface
	 */
	public function get_adapter_class();

	/**
	 * Gets adapter options
	 *
	 * @return array	Configuration keys
	 */
	public function get_options();

	/**
	 * Return true if the adapter is available
	 *
	 * @return bool
	 */
	public function is_available();
}
