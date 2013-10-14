<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class mimetype_guesser implements MimeTypeGuesserInterface
{
	/**
	* Guesses the mime type of the file with the given path.
	*
	* @param string	$path	The path to the file
	*
	* @return string	The mime type or NULL, if none could be guessed
	*
	* @throws FileNotFoundException	If the file does not exist
	* @throws AccessDeniedException	If the file could not be read
	*/
	public function guess($path)
	{
		$mimetype = '';

		if (!is_file($path))
		{
			throw new FileNotFoundException($path);
		}

		if (!is_readable($path))
		{
			throw new AccessDeniedException($path);
		}

		if (function_exists('mime_content_type'))
		{
			$mimetype = mime_content_type($path);
		}

		// Some browsers choke on a mimetype of application/octet-stream
		if (!$mimetype || $mimetype == 'application/octet-stream')
		{
			$mimetype = 'application/octetstream';
		}

		return $mimetype;
	}
}
