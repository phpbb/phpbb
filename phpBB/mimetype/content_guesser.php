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

class content_guesser extends guesser_base
{
	/**
	* {@inheritdoc}
	*/
	public function is_supported()
	{
		return function_exists('mime_content_type') && is_callable('mime_content_type');
	}

	/**
	* {@inheritdoc}
	*/
	public function guess($file, $file_name = '')
	{
		return mime_content_type($file);
	}
}
