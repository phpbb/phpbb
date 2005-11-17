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
* @package acp
*/
class acp_php_info
{
	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		if ($mode != 'info')
		{
			trigger_error('NO_MODE');
		}

		$this->tpl_name = 'acp_php_info';
		
		ob_start(); 
		phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES); 
		$phpinfo = ob_get_contents(); 
		ob_end_clean(); 

		// Get used layout
		$layout = (preg_match('#bgcolor#i', $phpinfo)) ? 'old' : 'new';

		// Here we play around a little with the PHP Info HTML to try and stylise
		// it along phpBB's lines ... hopefully without breaking anything. The idea
		// for this was nabbed from the PHP annotated manual
		preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output); 

		switch ($layout)
		{
			case 'old':
				$output = preg_replace('#<table#', '<table', $output[1][0]);
				$output = preg_replace('# bgcolor="\#(\w){6}"#', '', $output);
				$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
				$output = preg_replace('#border="0" cellpadding="3" cellspacing="1" width="600"#', 'border="0" cellspacing="1" cellpadding="4" width="95%"', $output);
				$output = preg_replace('#<tr valign="top"><td align="left">(.*?<a .*?</a>)(.*?)</td></tr>#s', '<tr class="row1"><td style="{background-color: #9999cc;}"><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td style="{background-color: #9999cc;}">\2</td><td style="{background-color: #9999cc;}">\1</td></tr></table></td></tr>', $output);
				$output = preg_replace('#<tr valign="baseline"><td[ ]{0,1}><b>(.*?)</b>#', '<tr><td class="row1" nowrap="nowrap">\1', $output);
				$output = preg_replace('#<td align="(center|left)">#', '<td class="row2">', $output);
				$output = preg_replace('#<td>#', '<td class="row2">', $output);
				$output = preg_replace('#valign="middle"#', '', $output);
				$output = preg_replace('#<tr >#', '<tr>', $output);
				$output = preg_replace('#<hr(.*?)>#', '', $output);
				$output = preg_replace('#<h1 align="center">#i', '<h1>', $output);
				$output = preg_replace('#<h2 align="center">#i', '<h2>', $output);
			break;

			case 'new':
				$output = preg_replace('#(\w),(\w)#', '\1, \2', $output[1][0]);
				$output = preg_replace('#<tr class="v"><td>(.*?<a .*?</a>)(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2"><tr><td>\2</td><td>\1</td></tr></table></td></tr>', $output);
				$output = preg_replace('#class="e"#', 'class="row1"', $output);
				$output = preg_replace('#class="v"#', 'class="row2"', $output);
				$output = preg_replace('#class="h"#', '', $output);
				$output = preg_replace('#<hr />#', '', $output);
				$output = preg_replace('#<table [^<]+>#i', '<table>', $output);
				$output = preg_replace('#<img border="0"#i', '<img', $output);
				$output = str_replace(array('<font', '</font>'), array('<span', '</span>'), $output);
				
				preg_match_all('#<div class="center">(.*)</div>#siU', $output, $output); 
				$output = $output[1][0];
			break;
		}

		$template->assign_var('PHPINFO', $output);
	}
}

/**
* @package module_install
*/
class acp_php_info_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_php_info',
			'title'		=> 'ACP_PHP_INFO',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'info'		=> array('title' => 'ACP_PHP_INFO', 'auth' => 'acl_a_server'),
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