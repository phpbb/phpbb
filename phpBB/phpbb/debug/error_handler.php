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

namespace phpbb\debug;

use Symfony\Component\Debug\ErrorHandler;

class error_handler extends ErrorHandler
{
	public function handleError($type, $message, $file, $line)
	{
		if ($type === E_USER_WARNING || $type === E_USER_NOTICE)
		{
			$handler = defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler';

			$handler($type, $message, $file, $line);
		}

		return parent::handleError($type, $message, $file, $line);
	}
}
