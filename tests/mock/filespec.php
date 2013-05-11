<?php
/**
 *
 * @package testing
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * Mock filespec class with some basic values to help with testing the
 * fileupload class
 */
class phpbb_mock_filespec
{
	public $filesize;
	public $realname;
	public $extension;
	public $width;
	public $height;
	public $error = array();

	public function check_content($disallowed_content)
	{
		return true;
	}

	public function get($property)
	{
		return $this->$property;
	}
}
