<?php
/** 
*
* @package install
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/

if (!empty($setmodules))
{
	$module[] = array(
		'module_type' => 'install',
		'module_title' => 'OVERVIEW',
		'module_filename' => substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order' => 0,
		'module_subs' => array('INTRO', 'LICENSE', 'SUPPORT'),
		'module_stages' => '',
		'module_reqs' => ''
	);
	return;
}

class install_main extends module
{
	function install_main(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $lang, $template;

		switch ($sub)
		{
			case 'intro' :
				$title = $lang['SUB_INTRO'];
				$body = $lang['OVERVIEW_BODY'];
			break;
			case 'license' :
				$title = $lang['GPL'];
				$body = implode("<br/>\n", file('../docs/COPYING'));
			break;
			case 'support' :
				$title = $lang['SUB_SUPPORT'];
				$body = $lang['SUPPORT_BODY'];
			break;
		}

		$this->tpl_name = 'install_main';
		$this->page_title = $title;

		$template->assign_vars(array(
			'TITLE'		=> $title,
			'BODY'		=> $body,
		));
	}
}
?>