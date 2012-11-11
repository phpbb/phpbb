<?php
/**
 *
 * @package phpbb
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Basic class to facilitate extension => mimetype mapping
 *
 * @package phpbb
 */
class phpbb_mimetype_extension_map
{
	/**
	 * An array of extension => mime-type mappings
	 * @var array
	 */
	protected $map = array(
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png' => 'image/png',
		'gif' => 'image/gif',
		'psd' => 'image/vnd.adobe.photoshop',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'bmp' => 'image/x-bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'svg' => 'image/svg+xml',
	);

	/**
	 * Guesses an appropriate mime-type based on a file extension
	 *
	 * @param string $extension The file extension
	 *
	 * @return string The mime-type
	 */
	public function get_mimetype($extension)
	{
		$extension = strtolower($extension);
		if (!isset($this->map[$extension]))
		{
			return 'application/octet-stream';
		}

		return $this->map[$extension];
	}
}
