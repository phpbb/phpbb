<?php

/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace foo\bar\acp;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		$this->tpl_name = 'foobar';
		$this->page_title = 'Bertie';
	}
}
