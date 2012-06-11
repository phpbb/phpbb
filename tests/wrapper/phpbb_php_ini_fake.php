<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_php_ini_fake extends phpbb_php_ini
{
	function get($varname)
	{
		return $varname;
	}
}
