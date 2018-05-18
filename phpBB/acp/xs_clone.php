<?php

/***************************************************************************
 *                               xs_clone.php
 *                               ------------
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

$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_clone.'.$phpEx) . '">' . $lang['xs_clone_styles'] . '</a>'));

$lang['xs_clone_back'] = str_replace('{URL}', append_sid('xs_clone.'.$phpEx), $lang['xs_clone_back']);

//
// Check required functions
//
if(!@function_exists('gzcompress'))
{
	xs_error($lang['xs_import_nogzip']);
}

//
// clone style
//
if(!empty($_POST['clone_style']) && !defined('DEMO_MODE'))
{
	$style = intval($_POST['clone_style']);
	$new_name = stripslashes($_POST['clone_name']);
	// get theme data
	$sql = "SELECT * FROM " . THEMES_TABLE . " WHERE themes_id='{$style}'";
	if(!$result = $db->sql_query($sql))
	{
		xs_error($lang['xs_no_style_info'] . '<br /><br />' . $lang['xs_clone_back'], __LINE__, __FILE__);
	}
	$theme = $db->sql_fetchrow($result);
	if(empty($theme['themes_id']))
	{
		xs_error($lang['xs_no_themes'] . '<br /><br />' . $lang['xs_clone_back']);
	}
	if($theme['style_name'] === stripslashes($new_name))
	{
		xs_error($lang['xs_clone_taken'] . '<br /><br />' . $lang['xs_clone_back']);
	}
	// check for clone
	$sql = "SELECT themes_id FROM " . THEMES_TABLE . " WHERE style_name = '" . xs_sql($new_name) . "'";
	if(!$result = $db->sql_query($sql))
	{
		xs_error($lang['xs_no_theme_data'] . '<br /><br />' . $lang['xs_clone_back'], __LINE__, __FILE__);
	}
	$row = $db->sql_fetchrow($result);
	if(!empty($row['themes_id']))
	{
		xs_error($lang['xs_clone_taken'] . '<br /><br />' . $lang['xs_clone_back']);
	}
	// clone it
	$vars = array('style_name');
	$values = array(xs_sql($new_name));
	foreach($theme as $var => $value)
	{
		if(!is_integer($var) && $var !== 'style_name' && $var !== 'themes_id')
		{
			$vars[] = $var;
			$values[] = xs_sql($value);
		}
	}
	$sql = "INSERT INTO " . THEMES_TABLE . " (" . implode(', ', $vars) . ") VALUES ('" . implode("','", $values) . "')";
	if(!$result = $db->sql_query($sql))
	{
		xs_error($lang['xs_error_new_row'] . '<br /><br />' . $lang['xs_clone_back'], __LINE__, __FILE__);
	}
	// recache themes
	if(defined('XS_MODS_CATEGORY_HIERARCHY210'))
	{
		if ( empty($themes) )
		{
			$themes = new themes();
		}
		if ( !empty($themes) )
		{
			$themes->read(true);
		}
	}
	xs_message($lang['Information'], $lang['xs_theme_cloned'] . '<br /><br />' . $lang['xs_clone_back']);
}

//
// clone template
//
if(!empty($_POST['clone_tpl']) && !defined('DEMO_MODE'))
{
	$old_name = xs_tpl_name($_POST['clone_tpl']);
	$new_name = xs_tpl_name($_POST['clone_style_name']);
	if(empty($new_name) || $new_name === $old_name)
	{
		xs_error($lang['xs_invalid_style_name'] . '<br /><br />' . $lang['xs_clone_back']);
	}
	// check if template exists
	if(@file_exists('../templates/'.$new_name))
	{
		xs_error($lang['xs_clone_style_exists'] . '<br /><br />' . $lang['xs_clone_back']);
	}
	// check variables
	$total = intval($_POST['total']);
	$vars = array('clone_tpl', 'clone_style_name', 'total');
	$count = 0;
	$list = array();
	for($i=0; $i<$total; $i++)
	{
		$vars[] = 'clone_style_id_'.$i;
		$vars[] = 'clone_style_'.$i;
		$vars[] = 'clone_style_name_'.$i;
		if(!empty($_POST['clone_style_'.$i]) && !empty($_POST['clone_style_name_'.$i]))
		{
			// prepare for export
			$list[] = intval($_POST['clone_style_id_'.$i]);
			$_POST['export_style_'.$i] = $_POST['clone_style_'.$i];
			$_POST['export_style_id_'.$i] = $_POST['clone_style_id_'.$i];
			$_POST['export_style_name_'.$i] = $_POST['clone_style_name_'.$i];
			// prepare for import
			$_POST['import_install_'.$count] = '1';
			$count ++;
		}
	}
	if(!$count)
	{
		xs_error($lang['xs_clone_no_select'] . '<br /><br />' . $lang['xs_clone_back']);
	}
	$request = array();
	for($i=0; $i<count($vars); $i++)
	{
		$request[$vars[$i]] = stripslashes($_POST[$vars[$i]]);
	}
	// get ftp configuration
	$write_local = false;
	if(!get_ftp_config(append_sid('xs_clone.'.$phpEx), $request, true))
	{
		xs_exit();
	}
	xs_ftp_connect(append_sid('xs_clone.'.$phpEx), $request, true);
	if($ftp === XS_FTP_LOCAL)
	{
		$write_local = true;
		$write_local_dir = '../templates/';
	}
	// prepare variables for export
	$export = $old_name;
	$exportas = $new_name;
	// Generate theme_info.cfg
	$sql = "SELECT * FROM " . THEMES_TABLE . " WHERE template_name = '$export' AND themes_id IN (" . implode(', ', $list) . ")";
	if(!$result = $db->sql_query($sql))
	{
		xs_error($lang['xs_no_theme_data'] . $lang['xs_clone_back']);
	}
	$theme_rowset = $db->sql_fetchrowset($result);
	if(count($theme_rowset) == 0)
	{
		xs_error($lang['xs_no_themes']  . '<br /><br />' . $lang['xs_clone_back']);
	}
	$theme_data = xs_generate_themeinfo($theme_rowset, $export, $exportas, $total);
	// prepare to pack	
	$pack_error = '';
	$pack_list = array();
	$pack_replace = array('./theme_info.cfg' => $theme_data);
	// pack style
	for($i = 0; $i < count($theme_rowset); $i++)
	{
		$id = $theme_rowset[$i]['themes_id'];
		$theme_name = $theme_rowset[$i]['style_name'];
		for($j=0; $j<$total; $j++)
		{
			if(!empty($_POST['export_style_name_'.$j]) && $_POST['export_style_id_'.$j] == $id)
			{
				$theme_name = stripslashes($_POST['export_style_name_'.$j]);
			}
		}
		$theme_rowset[$i]['style_name'] = $theme_name;
	}
	$data = pack_style($export, $exportas, $theme_rowset, '');
	// check errors
	if($pack_error)
	{
		xs_error(str_replace('{TPL}', $export, $lang['xs_export_error']) . $pack_error  . '<br /><br />' . $lang['xs_clone_back']);
	}
	if(!$data)
	{
		xs_error(str_replace('{TPL}', $export, $lang['xs_export_error2']) . '<br /><br />' . $lang['xs_clone_back']);
	}
	// save as file
	$filename = 'clone_' . time() . '.tmp';
	$tmp_filename = XS_TEMP_DIR . $filename;
	$f = @fopen($tmp_filename, 'wb');
	if(!$f)
	{
		xs_error(str_replace('{FILE}', $tpl_filename, $lang['xs_error_cannot_create_tmp']) . '<br /><br />' . $lang['xs_clone_back']);
	}
	fwrite($f, $data);
	fclose($f);
	// prepare import variables
	$total = $count;
	$_POST['total'] = $count;
	$list_only = false;
	$get_file = '';
	define('XS_CLONING', true);
	$lang['xs_import_back'] = $lang['xs_clone_back'];
	include('xs_include_import.' . $phpEx);
	include('xs_include_import2.' . $phpEx);	
}


//
// clone style menu
//
if(!empty($_GET['clone']))
{
	$style = stripslashes($_GET['clone']);
	$sql = "SELECT themes_id, style_name FROM " . THEMES_TABLE . " WHERE template_name = '" . xs_sql($style) . "' ORDER BY style_name ASC";
	if(!$result = $db->sql_query($sql))
	{
		xs_error($lang['xs_no_theme_data'] . '<br /><br />' . $lang['xs_clone_back'], __LINE__, __FILE__);
	}
	$theme_rowset = $db->sql_fetchrowset($result);
	if(count($theme_rowset) == 0)
	{
		xs_error($lang['xs_no_themes'] . '<br /><br />' . $lang['xs_clone_back']);
	}
	$template->set_filenames(array('body' => XS_TPL_PATH . 'clone2.tpl'));
	// clone template
	$template->assign_vars(array(
			'FORM_ACTION'		=> append_sid('xs_clone.'.$phpEx),
			'CLONE_TEMPLATE'	=> htmlspecialchars($style),
			'STYLE_ID'			=> $theme_rowset[0]['themes_id'],
			'STYLE_NAME'		=> htmlspecialchars($theme_rowset[0]['style_name']),
			'TOTAL'				=> count($theme_rowset),
			'L_CLONE_STYLE3'	=> str_replace('{STYLE}', htmlspecialchars($style), $lang['xs_clone_style3'])
			));
	// clone styles
	for($i=0; $i<count($theme_rowset); $i++)
	{
		$template->assign_block_vars('styles', array(
			'ID'		=> $theme_rowset[$i]['themes_id'],
			'TPL'		=> htmlspecialchars($theme_rowset[$i]['template_name']),
			'STYLE'		=> htmlspecialchars($theme_rowset[$i]['style_name']),
			'L_CLONE'	=> str_replace('{STYLE}', htmlspecialchars($theme_rowset[$i]['style_name']), $lang['xs_clone_style2'])
			));
	}
	if(count($theme_rowset) == 1)
	{
		$template->assign_block_vars('switch_select_nostyle', array());
		if($theme_rowset[0]['style_name'] === $style)
		{
			$template->assign_block_vars('switch_onchange', array());
		}
	}
	else
	{
		$template->assign_block_vars('switch_select_style', array());
		for($i=0; $i<count($theme_rowset); $i++)
		{
			$template->assign_block_vars('switch_select_style.style', array(
				'NUM'		=> $i,
				'ID'		=> $theme_rowset[$i]['themes_id'],
				'NAME'		=> htmlspecialchars($theme_rowset[$i]['style_name'])
				));
		}
	}
	$template->pparse('body');
	xs_exit();
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
					'U_CLONE'	=> "xs_clone.{$phpEx}?clone={$str2}&sid={$userdata['session_id']}",
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
			'U_CLONE'	=> "xs_clone.{$phpEx}?clone={$str2}&sid={$userdata['session_id']}",
		)
	);
}

$template->set_filenames(array('body' => XS_TPL_PATH . 'clone.tpl'));
$template->pparse('body');
xs_exit();

?>