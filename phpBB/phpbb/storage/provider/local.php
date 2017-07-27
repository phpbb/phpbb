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

class local implements provider_interface
{
	public function get_name()
	{
		return 'local';
	}

	public function get_class()
	{
		return get_class($this);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_adapter_class()
	{
		return \phpbb\storage\adapter\local::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_options()
	{
		return ['path' => array('lang' => 'PATH', 'validate' => 'string', 'type' => 'text:40:255', 'explain' => false)];
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available()
	{
		return true;
	}
}
