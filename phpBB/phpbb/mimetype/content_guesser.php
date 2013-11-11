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
		return true;
	}

	/**
	* @inheritdoc
	*/
	public function guess($file, $file_name = '')
	{
		$mimetype = null;
		if (function_exists('mime_content_type'))
		{
			$mimetype = mime_content_type($file);
		}

		return $mimetype;
	}
}
