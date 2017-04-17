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
namespace phpbb\console\command\style;

abstract class command extends \phpbb\console\command\command
{
	/** @var \phpbb\style\manager */
	protected $manager;

	/** @var \phpbb\language\language */
	private $language;

	/** @var \phpbb\log\log */
	protected $log;

	public function __construct(\phpbb\user $user, \phpbb\style\manager $manager, \phpbb\language\language $language, \phpbb\log\log $log)
	{
		$this->manager = $manager;
		$this->log = $log;
		$this->language = $language;

		$this->language->add_lang(array('acp/styles'));

		parent::__construct($user);
	}
}
