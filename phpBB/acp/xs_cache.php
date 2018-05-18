<?php

/***************************************************************************
 *                                xs_cache.php
 *                                ------------
 *   copyright            : (C) 2003 - 2007 Vjacheslav Trushkin
 *   support              : http://www.stsoftware.biz/forum
 *
 *   version              : 2.4.0
 *
 *   file revision        : 80
 *   project revision     : 83
 *   last modified        : 12 Mar 2007  10:28:52
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

define('IN_PHPBB', 1);
$phpbb_root_path = "./../";
$no_page_header = true;
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);

// check if mod is installed
if(empty($template->xs_version) || $template->xs_version !== 8)
{
	message_die(GENERAL_ERROR, isset($lang['xs_error_not_installed']) ? $lang['xs_error_not_installed'] : 'eXtreme Styles mod is not installed. You forgot to upload includes/template.php');
}

define('IN_XS', true);
include_once('xs_include.' . $phpEx);

$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_cache.'.$phpEx) . '">' . $lang['xs_manage_cache'] . '</a>'));

$data = '';

$skip_files = array(
	'.',
	'..',
	'.htaccess',
	'index.htm',
	'index.html',
	'index.php',
	'attach_config.php',
	);

//
// clear cache
//
if(isset($_GET['clear']) && !defined('DEMO_MODE'))
{
	@set_time_limit(XS_MAX_TIMEOUT);
	$clear = $_GET['clear'];
	if(!$clear)
	{
		// clear all cache
		$match = '';
	}
	else
	{
		$match = XS_TPL_PREFIX . $clear . XS_SEPARATOR;
	}
	$match_len = strlen($match);
	$style_len = strlen(STYLE_EXTENSION);
	$backup_len = strlen(XS_BACKUP_EXT);
	$dir = $template->cachedir;
	$res = @opendir($dir);
	if(!$res)
	{
		$data = $lang['xs_cache_nowrite'];
	}
	else
	{
		$num = 0;
		$num_error = 0;
		while(($file = readdir($res)) !== false)
		{
			$len = strlen($file);
			// delete only files that match pattern, that aren't in exclusion list and that aren't downloaded styles.
			if(substr($file, 0, $match_len) === $match && !xs_in_array($file, $skip_files))
			if(substr($file, $len - $style_len) !== STYLE_EXTENSION && substr($file, $len - $backup_len) !== XS_BACKUP_EXT)
			{
				$res2 = @unlink($dir . $file);
				if($res2)
				{
					$data .= str_replace('{FILE}', $file, $lang['xs_cache_log_deleted']) . "<br />\n";
					$num ++;
				}
				elseif(@is_file($dir . $file))
				{
					$data .= str_replace('{FILE}', $file, $lang['xs_cache_log_nodelete']) . "<br />\n";
					$num_error ++;
				}
			}
		}
		closedir($res);
		if(!$num && !$num_error)
		{
			if($clear)
			{
				$data .= str_replace('{TPL}', $clear, $lang['xs_cache_log_nothing']) . "<br />\n";
			}
			else
			{
				$data .= $lang['xs_cache_log_nothing2'] . "<br />\n";
			}
		}
		else
		{
			$data .= str_replace('{NUM}', $num, $lang['xs_cache_log_count']) . "<br />\n";
			if($num_error)
			{
				$data .= str_replace('{NUM}', $num_error, $lang['xs_cache_log_count2']) . "<br />\n";
			}
		}
	}
}


//
// compile cache
//
if(isset($_GET['compile']) && !defined('DEMO_MODE'))
{
	$tpl = $_GET['compile'];
	@set_time_limit(XS_MAX_TIMEOUT);
	$num_errors = 0;
	$num_compiled = 0;
	if($tpl)
	{
		$dir = $template->tpldir . $tpl . '/';
		compile_cache($dir, '', $tpl);
	}
	else
	{
		$res = opendir('../templates');
		while(($file = readdir($res)) !== false)
		{
			if($file !== '.' && $file !== '..' && is_dir('../templates/'.$file) && @file_exists('../templates/'.$file.'/overall_header.tpl'))
			{
				compile_cache('../templates/'.$file.'/', '', $file);
			}
		}
		closedir($res);
	}
	$data .= str_replace('{NUM}', $num_compiled, $lang['xs_cache_log_compiled']) . "<br />\n";
	$data .= str_replace('{NUM}', $num_errors, $lang['xs_cache_log_errors']) . "<br />\n";
}

function compile_cache($dir, $subdir, $tpl)
{
	global $data, $template, $num_errors, $num_compiled, $lang;
	$str = $dir . $subdir;
	$res = @opendir($dir . $subdir);
	if(!$res)
	{
		$data .= str_replace('{DIR}', $dir.$subdir, $lang['xs_cache_log_noaccess']) . "<br />\n";
		$num_errors ++;
		return;
	}
	while(($file = readdir($res)) !== false)
	{
		if(@is_dir($str . $file) && $file !== '.' && $file !== '..' && $file !== 'CVS')
		{
			compile_cache($dir, $subdir . $file . '/', $tpl);
		}
		elseif(substr($file, strlen($file) - 4) === '.tpl')
		{
			$res2 = $template->precompile($tpl, $subdir . $file);
			if($res2)
			{
				$data .= str_replace('{FILE}', $dir.$subdir.$file, $lang['xs_cache_log_compiled2']) . "<br />\n";
				$num_compiled ++;
			}
			else
			{
				$data .= str_replace('{FILE}', $dir.$subdir.$file, $lang['xs_cache_log_nocompile']) . "<br />\n";
				$num_errors ++;
			}
		}
	}
	closedir($res);
}

//
// get list of installed styles
//
$sql = 'SELECT themes_id, template_name, style_name FROM ' . THEMES_TABLE . ' ORDER BY template_name';
if(!$result = $db->sql_query($sql))
{
	xs_error($lang['xs_no_style_info'], __LINE__, __FILE__);
}
$style_rowset = $db->sql_fetchrowset($result);

$template->set_filenames(array('body' => XS_TPL_PATH . 'cache.tpl'));

$prev_id = -1;
$prev_tpl = '';
$style_names = array();
$j = 0;
for($i=0; $i<count($style_rowset); $i++)
{
	$item = $style_rowset[$i];
	if($item['template_name'] === $prev_tpl)
	{
		$style_names[] = htmlspecialchars($item['style_name']);
	}
	else
	{
		if($prev_id > 0)
		{
			$str = implode('<br />', $style_names);
			$str2 = urlencode($prev_tpl);
			$row_class = $xs_row_class[$j % 2];
			$j++;
			$template->assign_block_vars('styles', array(
					'ROW_CLASS'	=> $row_class,
					'TPL'		=> $prev_tpl,
					'STYLES'	=> $str,
					'U_CLEAR'	=> "xs_cache.{$phpEx}?clear={$str2}&sid={$userdata['session_id']}",
					'U_COMPILE'	=> "xs_cache.{$phpEx}?compile={$str2}&sid={$userdata['session_id']}",
				)
			);
		}
		$prev_id = $item['themes_id'];
		$prev_tpl = $item['template_name'];
		$style_names = array(htmlspecialchars($item['style_name']));
	}
}
if($prev_id > 0)
{
	$str = implode('<br />', $style_names);
	$str2 = urlencode($prev_tpl);
	$row_class = $xs_row_class[$j % 2];
	$j++;
	$template->assign_block_vars('styles', array(
			'ROW_CLASS'	=> $row_class,
			'TPL'		=> $prev_tpl,
			'STYLES'	=> $str,
			'U_CLEAR'	=> "xs_cache.{$phpEx}?clear={$str2}&sid={$userdata['session_id']}",
			'U_COMPILE'	=> "xs_cache.{$phpEx}?compile={$str2}&sid={$userdata['session_id']}",
		)
	);
}

$template->assign_vars(array(
	'U_CLEAR_ALL'	=> "xs_cache.{$phpEx}?clear=&sid={$userdata['session_id']}",
	'U_COMPILE_ALL'	=> "xs_cache.{$phpEx}?compile=&sid={$userdata['session_id']}",
	'RESULT'		=> '<br /><br />' . $data
	)
);

$template->pparse('body');
xs_exit();

?>