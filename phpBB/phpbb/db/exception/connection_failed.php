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

namespace phpbb\db\exception;

use Exception as BaseException;
use phpbb\exception\runtime_exception;

/**
 * Exception thrown when the specified database system is unknown.
 */
class connection_failed extends runtime_exception
{
	/**
	 * Constructor.
	 *
	 * @param BaseException|null $previous The previous exception.
	 */
	public function __construct(BaseException $previous = null)
	{
		parent::__construct('phpBB has failed to connect to the database.', [], $previous);
	}
}
