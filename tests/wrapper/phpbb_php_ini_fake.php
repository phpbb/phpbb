<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_php_ini_fake extends \phpbb\php\ini
{
	function get($varname)
	{
		return $varname;
	}
}
