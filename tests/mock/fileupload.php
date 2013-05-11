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
}
