<?php
/**
 *
 * @package testing
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * Mock fileupload class with some basic values to help with testing the
 * filespec class
 */
class phpbb_mock_fileupload
{
	public $max_filesize = 100;
	public $error_prefix = '';

	public function valid_dimensions($filespec)
	{
		return true;
	}

	/**
	 * Copied verbatim from phpBB/includes/functions_upload.php's fileupload
	 * class to ensure the correct behaviour of filespec::move_file.
	 *
	 * Maps file extensions to the constant in second index of the array
	 * returned by getimagesize()
	 */
	public function image_types()
	{
		return array(
			IMAGETYPE_GIF		=> array('gif'),
			IMAGETYPE_JPEG		=> array('jpg', 'jpeg'),
			IMAGETYPE_PNG		=> array('png'),
			IMAGETYPE_SWF		=> array('swf'),
			IMAGETYPE_PSD		=> array('psd'),
			IMAGETYPE_BMP		=> array('bmp'),
			IMAGETYPE_TIFF_II	=> array('tif', 'tiff'),
			IMAGETYPE_TIFF_MM	=> array('tif', 'tiff'),
			IMAGETYPE_JPC		=> array('jpg', 'jpeg'),
			IMAGETYPE_JP2		=> array('jpg', 'jpeg'),
			IMAGETYPE_JPX		=> array('jpg', 'jpeg'),
			IMAGETYPE_JB2		=> array('jpg', 'jpeg'),
			IMAGETYPE_SWC		=> array('swc'),
			IMAGETYPE_IFF		=> array('iff'),
			IMAGETYPE_WBMP		=> array('wbmp'),
			IMAGETYPE_XBM		=> array('xbm'),
		);
	}
}
