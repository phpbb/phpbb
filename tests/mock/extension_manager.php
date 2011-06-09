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
	public function __construct($extensions = array())
	{
		$this->extensions = $extensions;
	}
}
