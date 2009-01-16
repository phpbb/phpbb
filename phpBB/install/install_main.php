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

if ( !defined('IN_INSTALL') )
{
	// Someone has tried to access the file direct. This is not a good idea, so exit
	exit;
}

if (!empty($setmodules))
{
	$module[] = array(
		'module_type'		=> 'install',
		'module_title'		=> 'OVERVIEW',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen(PHP_EXT)-1),
		'module_order'		=> 0,
		'module_subs'		=> array('INTRO', 'LICENSE', 'SUPPORT'),
		'module_stages'		=> '',
		'module_reqs'		=> ''
	);
}

/**
* Main Tab - Installation
* @package install
*/
class install_main extends module
{
	function install_main(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		switch ($sub)
		{
			case 'intro' :
				$title = phpbb::$user->lang['SUB_INTRO'];
				$body = phpbb::$user->lang['OVERVIEW_BODY'];
			break;

			case 'license' :
				$title = phpbb::$user->lang['GPL'];
				$body = implode("<br />\n", file('../docs/COPYING'));
			break;

			case 'support' :
				$title = phpbb::$user->lang['SUB_SUPPORT'];
				$body = phpbb::$user->lang['SUPPORT_BODY'];
			break;
		}

		$this->tpl_name = 'install/main';
		$this->page_title = $title;

		phpbb::$template->assign_vars(array(
			'TITLE'		=> $title,
			'BODY'		=> $body,

			'S_LANG_SELECT'	=> '<select id="language" name="language">' . $this->p_master->inst_language_select(phpbb::$user->lang_name) . '</select>',
		));
	}
}

?>