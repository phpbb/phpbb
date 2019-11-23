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

namespace phpbb\cp;

interface constructor_interface
{
	/**
	 * Set up everything needed for a control panel.
	 * This checks authentication and handles all other miscellaneous things.
	 *
	 * @see \phpbb\acp\helper\constructor::setup()
	 * @see \phpbb\mcp\helper\constructor::setup()
	 * @see \phpbb\ucp\helper\constructor::setup()
	 *
	 * @throws \phpbb\exception\exception_interface
	 * @return void
	 */
	public function setup();
}
