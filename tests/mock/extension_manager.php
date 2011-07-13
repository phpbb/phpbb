<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
