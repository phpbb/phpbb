<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
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
				'style'		=> array('title' => 'ACP_STYLES', 'auth' => 'acl_a_styles'),
				'template'	=> array('title' => 'ACP_TEMPLATES', 'auth' => 'acl_a_styles'),
				'theme'		=> array('title' => 'ACP_THEMES', 'auth' => 'acl_a_styles'),
				'imageset'	=> array('title' => 'ACP_IMAGESETS', 'auth' => 'acl_a_styles'),
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

?>