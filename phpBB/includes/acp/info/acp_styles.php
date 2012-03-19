<?php
/**
*
* @package acp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class acp_styles_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_styles',
			'title'		=> 'ACP_CAT_STYLES',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'style'		=> array('title' => 'ACP_STYLES', 'auth' => 'acl_a_styles', 'cat' => array('ACP_STYLE_MANAGEMENT')),
				'template'	=> array('title' => 'ACP_TEMPLATES', 'auth' => 'acl_a_styles', 'cat' => array('ACP_STYLE_COMPONENTS')),
				'theme'		=> array('title' => 'ACP_THEMES', 'auth' => 'acl_a_styles', 'cat' => array('ACP_STYLE_COMPONENTS')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
