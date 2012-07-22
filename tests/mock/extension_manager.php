<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_mock_extension_manager extends phpbb_extension_manager
{
	public function __construct($phpbb_root_path, $extensions = array())
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = '.php';
		$this->extensions = $extensions;
	}
}
