<?php

/***************************************************************************
 *                               xs_import.php
 *                               -------------
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

$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_import.'.$phpEx) . '">' . $lang['xs_import_styles'] . '</a>'));

$lang['xs_import_back'] = str_replace('{URL}', append_sid('xs_import.'.$phpEx), $lang['xs_import_back']);

$return_url = isset($_POST['return']) ? stripslashes($_POST['return']) : (isset($_GET['return']) ? stripslashes($_GET['return']) : '');
$return = $return_url ? '&return=' . urlencode($return_url) : '';
if($return)
{
	$lang['xs_import_back'] = str_replace('{URL}', $return_url, $lang['xs_import_back_download']);
	$_POST['return'] = $_GET['return'] = addslashes($return_url);
}

//
// Check required functions
//
if(!@function_exists('gzcompress'))
{
	xs_error($lang['xs_import_nogzip']);
}

$tpl_filename = $template->make_filename('_xs_test.tpl');
$cache_filename = $template->make_filename_cache($tpl_filename);
$str = '';
if(!xs_check_cache($cache_filename))
{
	xs_error(str_replace(array('{URL1}', '{URL2}'), array(append_sid('xs_chmod.'.$phpEx), append_sid('xs_import.'.$phpEx)), $lang['xs_import_nowrite_cache']));
}

//
// include all functions
//
include_once('xs_include_import.'.$phpEx);

// remove timeout
@set_time_limit(XS_MAX_TIMEOUT);

//
// check if need to download style
//
if(!empty($_GET['get_remote']))
{
	$_POST['action'] = 'web';
	$_POST['source'] = $_GET['get_remote'];
}

//
// delete style
//
if(isset($_GET['del']) && !defined('DEMO_MODE'))
{
	$str = xs_tpl_name($_GET['del']);
	@unlink(XS_TEMP_DIR.$str);
}

//
// import style
//
if(isset($_GET['import']) || isset($_POST['import']))
{
	$list_only = isset($_GET['list']) ? true : false;
	$get_file = isset($_GET['get_file']) ? stripslashes($_GET['get_file']) : '';
	$filename = isset($_POST['import']) ? $_POST['import'] : $_GET['import'];
	$filename = xs_tpl_name($filename);
	$write_local = false;
	if(!$list_only)
	{
		if(defined('DEMO_MODE'))
		{
			xs_error($lang['xs_permission_denied'] . '<br /><br />' . $lang['xs_import_back']);
		}
		$params = array('import' => $filename);
		$total = intval($_POST['total']);
		$params['total'] = $total;
		$params['import_default'] = isset($_POST['import_default']) && strlen($_POST['import_default']) ? intval($_POST['import_default']) : -1;
		for($i=0; $i<$total; $i++)
		{
			$install = empty($_POST['import_install_'.$i]) ? 0 : 1;
			$default = $install ? ($params['import_default'] == $i ? 1 : 0) : 0;
			$params['import_install_'.$i] = $install;
		}
		if($return_url)
		{
			$params['return'] = $return_url;
		}
		if(!get_ftp_config(append_sid('xs_import.'.$phpEx), $params, true))
		{
			xs_exit();
		}
		xs_ftp_connect(append_sid('xs_import.'.$phpEx), $params, true);
		if($ftp === XS_FTP_LOCAL)
		{
			$write_local = true;
			$write_local_dir = '../templates/';
		}
	}
	include('xs_include_import2.' . $phpEx);	
}

//
// Download from web
//
if(isset($_GET['get_web']))
{
	$_POST['action'] = 'web';
	$_POST['source'] = $_GET['get_web'];
}
if(isset($_POST['action']) && $_POST['action'] === 'web' && !defined('DEMO_MODE'))
{
	$src = stripslashes($_POST['source']);
	$dst = generate_style_name('web');
	$str = @implode('', @file($src));
	if(empty($str))
	{
		xs_error(str_replace('{URL}', $src, $lang['xs_import_nodownload']) . '<br /><br />' . $lang['xs_import_back']);
	}
	$header = xs_get_style_header('', substr($str, 0, 10240));
	if($header === false)
	{
		xs_error($lang['xs_style_header_error_reason'] . $xs_header_error . '<br /><br />' . $lang['xs_import_back']);
	}
	if($header['filesize'] != strlen($str))
	{
		xs_error($lang['xs_style_header_error_incomplete2'] . '<br /><br />' . $lang['xs_import_back']);
	}
	$f = @fopen(XS_TEMP_DIR . $dst, 'wb');
	if(!$f)
	{
		xs_error(str_replace('{FILE}', $dst, $lang['xs_error_cannot_create_tmp']) . '<br /><br />' . $lang['xs_import_back']);
	}
	fwrite($f, $str);
	fclose($f);
	xs_message($lang['Information'], str_replace('{URL}', append_sid('xs_import.'.$phpEx.'?importstyle=' . urlencode($dst) . $return), $lang['xs_import_uploaded2']) . '<br /><br />' . $lang['xs_import_back']);
}

//
// Copy from file
//
if(isset($_POST['action']) && $_POST['action'] === 'copy' && !defined('DEMO_MODE'))
{
	$src = stripslashes($_POST['source']);
	$dst = generate_style_name('copy');
	$str = @implode('', @file($src));
	if(empty($str))
	{
		xs_error(str_replace('{URL}', $src, $lang['xs_import_nodownload2']) . '<br /><br />' . $lang['xs_import_back']);
	}
	if(substr($str, 0, strlen(STYLE_HEADER_START)) !== STYLE_HEADER_START)
	{
		xs_error($lang['xs_style_header_error_invalid2'] . '<br /><br />' . $lang['xs_import_back']);
	}
	$header = xs_get_style_header('', substr($str, 0, 10240));
	if($header === false)
	{
		xs_error($lang['xs_style_header_error_reason'] . $xs_header_error . '<br /><br />' . $lang['xs_import_back']);
	}
	if($header['filesize'] != strlen($str))
	{
		xs_error($lang['xs_style_header_error_incomplete2'] . '<br /><br />' . $lang['xs_import_back']);
	}
	$f = @fopen(XS_TEMP_DIR . $dst, 'wb');
	if(!$f)
	{
		xs_error(str_replace('{FILE}', $dst, $lang['xs_error_cannot_create_tmp']) . $lang['xs_import_back']);
	}
	fwrite($f, $str);
	fclose($f);
	xs_message($lang['Information'], str_replace('{URL}', append_sid('xs_import.'.$phpEx.'?importstyle=' . urlencode($dst)), $lang['xs_import_uploaded3']) . '<br /><br />' . $lang['xs_import_back']);
}


//
// Upload
//
if(isset($_POST['action']) && $_POST['action'] === 'upload' && !defined('DEMO_MODE'))
{
	if(empty($_POST['source']['tmp_name']) || !@file_exists($_POST['source']['tmp_name']))
	{
		xs_error($lang['xs_import_nodownload3'] . '<br /><br />' . $lang['xs_import_back']);
	}
	$src = $_POST['source']['tmp_name'];
	$dst = generate_style_name('upload');
	$str = @implode('', @file($src));
	if(empty($str))
	{
		xs_error(str_replace('{URL}', $src, $lang['xs_import_nodownload2']) . '<br /><br />' . $lang['xs_import_back']);
	}
	if(substr($str, 0, strlen(STYLE_HEADER_START)) !== STYLE_HEADER_START)
	{
		xs_error($lang['xs_style_header_error_invalid2'] . '<br /><br />' . $lang['xs_import_back']);
	}
	$header = xs_get_style_header('', substr($str, 0, 10240));
	if($header === false)
	{
		xs_error($lang['xs_style_header_error_reason'] . $xs_header_error . '<br /><br />' . $lang['xs_import_back']);
	}
	if($header['filesize'] != strlen($str))
	{
		xs_error($lang['xs_style_header_error_incomplete2'] . '<br /><br />' . $lang['xs_import_back']);
	}
	$f = @fopen(XS_TEMP_DIR . $dst, 'wb');
	if(!$f)
	{
		xs_error(str_replace('{FILE}', $dst, $lang['xs_error_cannot_create_tmp']) . '<br /><br />' . $lang['xs_import_back']);
	}
	fwrite($f, $str);
	fclose($f);
	xs_message($lang['Information'], str_replace('{URL}', append_sid('xs_import.'.$phpEx.'?importstyle=' . urlencode($dst)), $lang['xs_import_uploaded4']) . '<br /><br />' . $lang['xs_import_back']);
}


//
// Show import page
//
if(!empty($_GET['importstyle']))
{
	$file = xs_tpl_name($_GET['importstyle']);
	$header = xs_get_style_header(XS_TEMP_DIR.$file);
	if($header === false)
	{
		xs_error($lang['xs_style_header_error_reason'] . $xs_header_error . '<br /><br />' . $lang['xs_import_back']);
	}
	if(@filesize(XS_TEMP_DIR.$file) != $header['filesize'])
	{
		xs_error($lang['xs_style_header_error_incomplete2'] . '<br /><br />' . $lang['xs_import_back']);
	}
	$template->set_filenames(array('import' => XS_TPL_PATH . 'import2.tpl'));
	$template->assign_vars(array(
		'FORM_ACTION'			=> append_sid('xs_import.'.$phpEx),
		'S_RETURN'				=> $return_url ? '<input type="hidden" name="return" value="' . htmlspecialchars($return_url) . '" />' : '',
		'IMPORT_FILENAME'		=> htmlspecialchars($file),
		'STYLE_TEMPLATE'		=> htmlspecialchars($header['template']),
		'STYLE_FILENAME'		=> htmlspecialchars($file),
		'STYLE_COMMENT'			=> htmlspecialchars($header['comment']),
		'DATE'					=> create_date($board_config['default_dateformat'], $header['date'], $board_config['board_timezone']),
		'STYLE_SIZE'			=> $header['filesize'],
		'STYLE_NAME'			=> htmlspecialchars($header['styles'][0]),
		'TOTAL'					=> count($header['styles']),
		'L_XS_IMPORT_TPL'		=> str_replace('{TPL}', htmlspecialchars($header['template']), $lang['xs_import_tpl'])
		));
	if(count($header['styles']) > 1)
	{
		$template->assign_block_vars('switch_select_style', array());
		for($i=0; $i<count($header['styles']); $i++)
		{
			$template->assign_block_vars('switch_select_style.style', array(
				'NUM'		=> $i,
				'NAME'		=> htmlspecialchars($header['styles'][$i]),
				));
		}
	}
	else
	{
		$template->assign_block_vars('switch_select_nostyle', array());
	}
	$template->pparse('import');
	xs_exit();
}


$template->set_filenames(array('body' => XS_TPL_PATH . 'import.tpl'));
//
// Get list of available styles
//
$dir = @opendir(XS_TEMP_DIR);
$files = array();
if($dir)
{
	while(($file = readdir($dir)) !== false)
	{
		if(substr($file, strlen($file) - strlen(STYLE_EXTENSION)) === STYLE_EXTENSION)
		{
			$items = xs_get_style_header(XS_TEMP_DIR.$file);
			if(is_array($items))
			{
				$items['file'] = $file;
				$items['file2'] = substr($file, 0, strlen($file) - strlen(STYLE_EXTENSION));
				if(@filesize(XS_TEMP_DIR.$file) != $items['filesize'])
				{
					$items['error'] = $lang['xs_import_incomplete_file'];
				}
				$files[] = $items;
			}
			else
			{
				$items = array(
					'filename'	=> XS_TEMP_DIR.$file,
					'filesize'	=> @filesize(XS_TEMP_DIR.$file),
					'date'		=> filemtime(XS_TEMP_DIR.$file),
					'file'		=> $file,
					'file2'		=> substr($file, 0, strlen($file) - strlen(STYLE_EXTENSION)),
					'error'		=> $lang['xs_import_invalid_file'],
					'template'	=> '-',
					'styles'	=> array('-'),
					'comment'	=> '',
					'offset'	=> 0
					);
				$files[] = $items;
			}
		}
	}
	closedir($dir);
}

if(count($files))
{

	for($i=0; $i<count($files); $i++)
	{
		$item = $files[$i];
		$row_class = $xs_row_class[$i % 2];
		$template->assign_block_vars('styles', array(
			'ROW_CLASS'		=> $row_class,
			'FILE'			=> htmlspecialchars($item['file']),
			'FILE2'			=> htmlspecialchars($item['file2']),
			'FILENAME'		=> htmlspecialchars($item['filename']),
			'TEMPLATE'		=> htmlspecialchars($item['template']),
			'DATE'			=> create_date($board_config['default_dateformat'], $item['date'], $board_config['board_timezone']),
			'COMMENT'		=> htmlspecialchars($item['comment']),
			'U_DELETE'		=> append_sid('xs_import.' . $phpEx . '?del=' . urlencode($item['file'])),
			'U_IMPORT'		=> append_sid('xs_import.' . $phpEx . '?importstyle=' . urlencode($item['file'])),
			'U_DOWNLOAD'	=> append_sid('xs_download.' . $phpEx),
			'U_LIST'		=> append_sid('xs_import.' . $phpEx . '?list=1&import=' . urlencode($item['file'])),
			));
		if(empty($item['error']))
		{
			for($j=0; $j<count($item['styles']); $j++)
			{
				$template->assign_block_vars('styles.list', array(
					'STYLE'		=> $item['styles'][$j]
					));
			}
			$template->assign_block_vars('styles.valid', array());
		}
		else
		{
			$template->assign_block_vars('styles.error', array('ERROR' => htmlspecialchars($item['error'])));
		}
	}
}
else
{
	$template->assign_block_vars('nostyles', array());
}
$template->assign_vars(array(
	'U_SCRIPT'	=> append_sid('xs_import.'.$phpEx),
	));

$template->pparse('body');
xs_exit();

?>