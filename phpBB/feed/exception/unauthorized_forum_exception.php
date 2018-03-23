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

namespace phpbb\feed\exception;

class unauthorized_forum_exception extends unauthorized_exception
{
	public function __construct($forum_id, \Exception $previous = null, $code = 0)
	{
		parent::__construct('SORRY_AUTH_READ', array($forum_id), $previous, $code);
	}
}
