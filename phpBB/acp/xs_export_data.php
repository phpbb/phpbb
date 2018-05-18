<?php

/***************************************************************************
 *                             xs_export_data.php
 *                             ------------------
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

$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_export_data.'.$phpEx) . '">' . $lang['xs_edit_styles_data'] . '</a>'));

$lang['xs_export_data_back'] = str_replace('{URL}', append_sid('xs_export_data.'.$phpEx), $lang['xs_export_data_back']);

//
// export style
//
if(isset($_GET['export']))
{
	$export = str_replace(array('\\', '/'), array('',''), stripslashes($_GET['export']));
	// get list of themes for style
	$sql = "SELECT themes_id, style_name FROM " . THEMES_TABLE . " WHERE template_name = '$export' ORDER BY style_name ASC";
	if(!$result = $db->sql_query($sql))
	{
		xs_error($lang['xs_no_theme_data'] . '<br /><br />' . $lang['xs_export_data_back']);
	}
	$theme_rowset = $db->sql_fetchrowset($result);
	if(count($theme_rowset) == 0)
	{
		xs_error($lang['xs_no_themes'] . '<br /><br />' . $lang['xs_export_data_back']);
	}
	if(count($theme_rowset) == 1)
	{
		$_POST['export'] = $_GET['export'];
		$_POST['export_total'] = '1';
		$_POST['export_id_0'] = $theme_rowset[0]['themes_id'];
		$_POST['export_check_0'] = 'checked';
	}
	else
	{
		$template->set_filenames(array('body' => XS_TPL_PATH . 'export_data2.tpl'));
		$template->assign_vars(array(
			'TOTAL'		=> count($theme_rowset),
			'EXPORT'	=> htmlspecialchars($export),
			'U_ACTION'	=> append_sid("xs_export_data.{$phpEx}")
			)
		);
		for($i=0; $i<count($theme_rowset); $i++)
		{
			$row_class = $xs_row_class[$i % 2];
			$template->assign_block_vars('styles', array(
				'ROW_CLASS'		=> $row_class,
				'NUM'			=> $i,
				'ID'			=> $theme_rowset[$i]['themes_id'],
				'STYLE'			=> htmlspecialchars($theme_rowset[$i]['style_name'])
				)
			);
		}
		$template->pparse('body');
		xs_exit();
	}
}

if(!empty($_POST['export']) && !defined('DEMO_MODE'))
{
	$export = xs_tpl_name($_POST['export']);
	// get ftp configuration
	$params = array('export' => $export);
	$total = intval($_POST['export_total']);
	$count = 0;
	for($i=0; $i<$total; $i++)
	{
		if(!empty($_POST['export_check_'.$i]))
		{
			$params['export_id_'.$count] = intval($_POST['export_id_'.$i]);
			$params['export_check_'.$count] = 'checked';
			$count ++;
		}
	}
	$params['export_total'] = $count;
	if(!$count)
	{
		xs_error($lang['xs_export_noselect_themes'] . '<br /><br />' . $lang['xs_export_data_back']);
	}
	$write_local = false;
	if(!get_ftp_config(append_sid('xs_export_data.'.$phpEx), $params, true))
	{
		xs_exit();
	}
	xs_ftp_connect(append_sid('xs_export_data.'.$phpEx), $params, true);
	if($ftp === XS_FTP_LOCAL)
	{
		$write_local = true;
		$local_filename = '../templates/'. $export . '/theme_info.cfg';
	}
	else
	{
		$local_filename = XS_TEMP_DIR . 'export_' . time() . '.tmp';
	}
	// get all themes for style
	$export_list = array();
	for($i=0; $i<$total; $i++)
	{
		if(!empty($_POST['export_check_'.$i]))
		{
			$export_list[] = intval($_POST['export_id_'.$i]);
		}
	}
	$sql = "SELECT * FROM " . THEMES_TABLE . " WHERE themes_id IN (" . implode(', ', $export_list) . ") ORDER BY style_name ASC";
	if(!$result = $db->sql_query($sql))
	{
		xs_error($lang['xs_no_style_info'] . '<br /><br />' . $lang['xs_export_data_back'], __LINE__, __FILE__);
	}
	$style_rowset = $db->sql_fetchrowset($result);
	if(!count($style_rowset))
	{
		xs_error($lang['xs_no_style_info'] . '<br /><br />' . $lang['xs_export_data_back'], __LINE__, __FILE__);
	}
	$data = xs_generate_themeinfo($style_rowset, $export, $export, 0);
	$f = @fopen($local_filename, 'wb');
	if(!$f)
	{
		xs_error(str_replace('{FILE}', $local_filename, $lang['xs_error_cannot_create_file']) . '<br /><br />' . $lang['xs_export_data_back']);
	}
	fwrite($f, $data);
	fclose($f);
	if($write_local)
	{
		xs_message($lang['Information'], $lang['xs_export_data_saved'] . '<br /><br />' . $lang['xs_export_data_back']);
	}
	// generate ftp actions
	$actions = array();
	// chdir to template directory
	$actions[] = array(
			'command'	=> 'chdir',
			'dir'		=> 'templates'
		);
	$actions[] = array(
			'command'	=> 'chdir',
			'dir'		=> $export
		);
	$actions[] = array(
			'command'	=> 'upload',
			'local'		=> $local_filename,
			'remote'	=> 'templates/' . $export . '/theme_info.cfg'
			);
	$ftp_log = array();
	$ftp_error = '';
	$res = ftp_myexec($actions);
/*	echo "<!--\n\n";
	echo "\$actions dump:\n\n";
	print_r($actions);
	echo "\n\n\$ftp_log dump:\n\n";
	print_r($ftp_log);
	echo "\n\n -->"; */
	@unlink($local_filename);
	if($res)
	{
		xs_message($lang['Information'], $lang['xs_export_data_saved'] . '<br /><br />' . $lang['xs_export_data_back']);
	}
	xs_error($ftp_error . '<br /><br />' . $lang['xs_export_data_back']);
}




$template->set_filenames(array('body' => XS_TPL_PATH . 'export_data.tpl'));
//
// get list of installed styles
//
$sql = 'SELECT themes_id, template_name, style_name FROM ' . THEMES_TABLE . ' ORDER BY template_name';
if(!$result = $db->sql_query($sql))
{
	xs_error($lang['xs_no_style_info'], __LINE__, __FILE__);
}
$style_rowset = $db->sql_fetchrowset($result);

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
					'U_EXPORT'	=> "xs_export_data.{$phpEx}?export={$str2}&sid={$userdata['session_id']}",
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
			'U_EXPORT'	=> "xs_export_data.{$phpEx}?export={$str2}&sid={$userdata['session_id']}",
		)
	);
}

$template->pparse('body');
xs_exit();

?>