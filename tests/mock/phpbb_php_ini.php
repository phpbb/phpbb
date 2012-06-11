<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_mock_phpbb_php_ini extends phpbb_php_ini
{
	function get($varname)
	{
		return $varname;
	}
}
