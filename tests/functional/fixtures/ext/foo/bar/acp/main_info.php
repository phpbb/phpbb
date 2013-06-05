<?php

/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_ext_foo_bar_acp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'phpbb_ext_foo_bar_acp_main_module',
			'title'		=> 'ACP_FOOBAR_TITLE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'mode'		=> array('title' => 'ACP_FOOBAR_MODE', 'auth' => '', 'cat' => array('ACP_FOOBAR_TITLE')),
			),
		);
	}
}
