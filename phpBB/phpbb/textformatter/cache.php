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

namespace phpbb\textformatter;

/**
* text_formatter.cache service
*
* Currently only used to signal that something that could effect the rendering has changed.
* BBCodes, smilies, censored words, templates, etc...
*
* @todo functionality should be moved to data_access
*
* @package phpBB3
*/
interface cache
{
	/**
	* Invalidate and/or regenerate this text formatter's cache(s)
	*/
	public function invalidate();

	/**
	* Tidy/prune this text formatter's cache(s)
	*/
	public function tidy();
}
