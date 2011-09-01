<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/ini_reader.php';

class phpbb_mock_ini_reader extends phpbb_ini_reader
{
	function get($varname)
	{
		return $varname;
	}
}
