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

namespace phpbb\mimetype;

abstract class guesser_base implements guesser_interface
{
	/**
	* @var int Guesser Priority
	*/
	protected $priority;

	/**
	* {@inheritdoc}
	*/
	public function get_priority()
	{
		return $this->priority;
	}

	/**
	* {@inheritdoc}
	*/
	public function set_priority($priority)
	{
		$this->priority = $priority;
	}
}
