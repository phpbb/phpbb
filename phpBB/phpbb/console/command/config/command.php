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
namespace phpbb\console\command\config;

abstract class command extends \phpbb\console\command\command
{
	/** @var \phpbb\config\config */
	protected $config;

	function __construct(\phpbb\user $user, \phpbb\config\config $config)
	{
		$this->config = $config;

		parent::__construct($user);
	}
}
