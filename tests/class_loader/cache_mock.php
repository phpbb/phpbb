<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_cache_mock
{
	private $variables = array();

	function get($var_name)
	{
		if (isset($this->variables[$var_name]))
		{
			return $this->variables[$var_name];
		}

		return false;
	}

	function put($var_name, $value)
	{
		$this->variables[$var_name] = $value;
	}
}
