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

namespace phpbb\passwords\driver;

abstract class base implements driver_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\passwords\driver\helper */
	protected $helper;

	/** @var driver name */
	protected $name;

	/**
	* Constructor of passwords driver object
	*
	* @param \phpbb\config\config $config phpBB config
	* @param \phpbb\passwords\driver\helper $helper Password driver helper
	*/
	public function __construct(\phpbb\config\config $config, helper $helper)
	{
		$this->config = $config;
		$this->helper = $helper;
	}

	/**
	* {@inheritdoc}
	*/
	public function is_supported()
	{
		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function is_legacy()
	{
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_settings_only($hash, $full = false)
	{
		return false;
	}
}
