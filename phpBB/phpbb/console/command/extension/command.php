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
namespace phpbb\console\command\extension;

abstract class command extends \phpbb\console\command\command
{
	/** @var \phpbb\extension\manager */
	protected $manager;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\user */
	protected $user;

	/**
	* Construct method
	*
	* @param \phpbb\extension\manager $manager Manager object
	* @param \phpbb\log\log $log Log table
	* @param \phpbb\user $user User object
	*/
	public function __construct(\phpbb\extension\manager $manager, \phpbb\log\log $log, \phpbb\user $user)
	{
		$this->manager = $manager;
		$this->log = $log;
		$this->user = $user;

		parent::__construct();
	}
}
