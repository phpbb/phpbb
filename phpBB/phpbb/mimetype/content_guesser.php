<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\mimetype;

/**
* @package mimetype
*/

class content_guesser extends guesser_base
{
	/**
	* @inheritdoc
	*/
	public function is_supported()
	{
		return function_exists('mime_content_type');
	}

	/**
	* @inheritdoc
	*/
	public function guess($file, $file_name = '')
	{
		return mime_content_type($file);
	}
}
