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

namespace phpbb\console\command;

abstract class command extends \Symfony\Component\Console\Command\Command
{
	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\user $user User instance (mostly for translation)
	*/
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
		parent::__construct();
	}
}
