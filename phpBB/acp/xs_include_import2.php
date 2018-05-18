<?php

/***************************************************************************
 *                           xs_include_import2.php
 *                           ----------------------
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

if (!defined('IN_PHPBB') || !defined('IN_XS'))
{
	die("Hacking attempt");
}

/*

Import style.

$filename			= style filename. it should be in temporary directory.
$write_local		= false if style should be uploaded via ftp, true if written directory to disk
$write_local_dir	= directory where to write. only if $write_local = true.
$list_only			= true if only list files
$get_file			= filename to get. empty if do not return any files

$_POST['total']				= total number of themes
$_POST['import_install_0']		= non-empty if install theme
$_POST['import_default']		= number of default style or -1 or empty

*/

if(empty($list_only))
{
	$list_only = false;
}

$lang['xs_import_back'] = str_replace('{URL}', append_sid('xs_import.'.$phpEx), $lang['xs_import_back']);

// list of text types. only last 4 characters of filename
$text_types = array('.tpl', '.htm', 'html', '.txt', '.css', '.cfg', '.php', '.xml');
// list of image types. if you add type make sure you add content-type header in code below
$img_types = array('.gif', '.jpg', '.jpe', 'jpeg', '.png');

$file = XS_TEMP_DIR . xs_fix_dir($filename);
$header = xs_get_style_header($file);
if($header === false)
{
	if(defined('XS_CLONING'))
	{
		@unlink($tmp_filename);
	}
	xs_error($lang['xs_style_header_error_reason'] . $xs_header_error . '<br /><br />' . $lang['xs_import_back']);
}
if($header['filesize'] != filesize($file))
{
	if(defined('XS_CLONING'))
	{
		@unlink($tmp_filename);
	}
	xs_error($lang['xs_style_header_error_incomplete'] . '<br /><br />' . $lang['xs_import_back']);
}
$f = @fopen($file, 'rb');
if(!$f)
{
	if(defined('XS_CLONING'))
	{
		@unlink($tmp_filename);
	}
	xs_error($lang['xs_error_cannot_open'] . '<br /><br />' . $lang['xs_import_back']);
}
if($write_local)
{
	$write_local_dir .= $header['template'] . '/';
}
fseek($f, $header['offset'], 0);
$str = fread($f, filesize($file) - $header['offset']);
fclose($f);
$str = @gzuncompress($str);
if($str === false || !strlen($str))
{
	if(defined('XS_CLONING'))
	{
		@unlink($tmp_filename);
	}
	xs_error($lang['xs_error_decompress_style'] . '<br /><br />' . $lang['xs_import_back']);
}
//
// unpack tar file
//
$pos = 0;
$tmp_name = XS_TEMP_DIR . 'file_' . $filename . '_%02d.tmp';
$tmp_count = 0;
$files = array();	// complete list of files
$list_data = array();	// result for list
$dirs = array();	// complete list of directories
$items = array();	// data
while($pos < strlen($str))
{
	$data = unpack(TAR_HEADER_UNPACK, substr($str, $pos, 512));
	$pos += 512;
	$data['filename'] = trim($data['prefix']) . trim($data['filename']);
	if(substr($data['filename'], 0, 2) === './')
	{
		$data['filename'] = substr($data['filename'], 2);
	}
	if($write_local)
	{
		$save_filename = $write_local_dir . $data['filename'];
	}
	else
	{
		$pos1 = strrpos($data['filename'], '/');
		if($pos1)
		{
			$data['dir'] = substr($data['filename'], 0, $pos1);
			$data['file'] = substr($data['filename'], $pos1 + 1);
		}
		else
		{
			$data['dir'] = '';
			$data['file'] = $data['filename'];
		}
	}
	$data['size'] = octdec(trim($data['size']));
	$data['mtime'] = octdec(trim($data['mtime']));
	$data['typeflag'] = octdec(trim($data['typeflag']));
	if($data['typeflag'] === '5')
	{
		$data['size'] = 0;
		if($write_local)
		{
			xs_create_dir($save_filename);
		}
	}
	$data['offset'] = $pos;
	$contents = $data['size'] > 0 ? substr($str, $pos, $data['size']) : '';
	$data['tmp'] = '';
	// adding to list
	$is_file = true;
	if(intval($data['typeflag']) == 5)
	{
		$is_file = false;
		if($data['filename'])
		{
			$dirs[] = $data['filename'];
		}
	}
	else
	{
		if($data['filename'])
		{
			if(!$list_only)
			{
				if($write_local)
				{
					$res = xs_write_file($save_filename, $contents);
					if(!$res)
					{
						if(defined('XS_CLONING'))
						{
							@unlink($tmp_filename);
						}
						xs_error(str_replace('{FILE}', $save_filename, $lang['xs_error_cannot_create_file']) . '<br /><br />' . $lang['xs_import_back']);
					}
				}
				else
				{
					// write to temporary file
					$tmp_count ++;				
					$data['tmp'] = sprintf($tmp_name, $tmp_count);
					$f = @fopen($data['tmp'], 'wb');
					if(!$f)
					{
						if(defined('XS_CLONING'))
						{
							@unlink($tmp_filename);
						}
						xs_error(str_replace('{FILE}', $data['tmp'], $lang['xs_error_cannot_create_tmp']) . '<br /><br />' . $lang['xs_import_back']);
					}
					fwrite($f, $contents);
					fclose($f);
				}
			}
			elseif(!empty($get_file) && $get_file === $data['filename'])
			{
				// show contents of file
				$f = $data['filename'];
				$ext = strtolower(substr($f, strlen($f) - 4));
				if(empty($_GET['get_content']) && xs_in_array($ext, $text_types))
				{
					// show as text
					$str = '<div align="left">' . $lang['xs_import_list_contents'] . $f . ' [<a href="' . append_sid('xs_import.' . $phpEx . '?list=1&import=' . urlencode($filename) . '&get_file=' . urlencode($f) . '&get_content=1') . '">' . $lang['xs_import_download_lc'] . '</a>]<br /><br />';
					$str .= '<textarea cols="120" rows="30" style="width: 100%">' . htmlspecialchars($contents) . '</textarea>';
					$str .= '</div>';
					xs_message($lang['Information'], $str);
				}
				else
				{
					$do_download = false;
					$content_type = '';
					if(empty($_GET['get_content']))
					{
						if($ext === '.gif')
						{
							$content_type = 'image/gif';
						}
						elseif($ext === '.jpg' || $ext === '.jpe' || $ext === 'jpeg')
						{
							$content_type = 'image/jpeg';
						}
						elseif($ext === '.png')
						{
							$content_type = 'image/png';
						}
						else
						{
							$do_download = true;
						}
					}
					else
					{
						$do_download = true;
					}
					xs_download_file($do_download ? basename($f) : '', $contents, $content_type);
					xs_exit();
				}
			}
			else
			{
				$list_data[$data['filename']] = $data;
			}
			$files[] = $data['filename'];
		}
	}
	if(empty($data['filename']) && $is_file)
	{
		$pos = strlen($str);
	}
	else
	{
		$pos += floor(($data['size'] + 511) / 512) * 512;
		if($is_file)
		{
			$items[] = $data;
		}
	}
}
if($list_only)
{
	// show list of files. used for debug.
	$str = '<div align="left">';
	// main data
	$str .= $lang['xs_import_list_filename'] . $header['filename'] . '<br />';
	$str .= $lang['xs_import_list_template'] . $header['template'] . '<br />';
	$str .= $lang['xs_import_list_comment'] . $header['comment'] . '<br />';
	$str .= $lang['xs_import_list_styles'] . implode(', ', $header['styles']) . '<br />';
	ksort($list_data);
	$str .= '<br />' . str_replace('{NUM}', count($list_data), $lang['xs_import_list_files']) . '<br />';
	$str .= '<table border="0" cellspacing="0" cellpadding="1" align="left">';
	foreach($list_data as $var => $value)
	{
		$str .= '<tr><td>' . htmlspecialchars($value['filename']) . '</td><td>';
		if($value['size'] > 0)
		{
			$ext = strtolower(substr($var, strlen($var) - 4));
			if(xs_in_array($ext, $text_types) || xs_in_array($ext, $img_types))
			{
				$str .= '[<a href="' . append_sid('xs_import.' . $phpEx . '?list=1&import=' . urlencode($filename) . '&get_file=' . urlencode($var)) . '">' . $lang['xs_import_view_lc'] . '</a>] ';
			}
			$str .= '[<a href="' . append_sid('xs_import.' . $phpEx . '?list=1&import=' . urlencode($filename) . '&get_file=' . urlencode($var)) . '&get_content=1">' . $lang['xs_import_download_lc'] . '</a>] ';
		}
		$str .= str_replace('{NUM}', $value['size'], $lang['xs_import_file_size']) . '</td></tr>';
	}
	$str .= '</table>';
	$str .= '</div>';
	xs_message($lang['Information'], $str);
}
$str = '';
if(!$write_local)
{
	//
	// Generate actions list
	//
	$actions = array();
	// chdir to template directory
	$actions[] = array(
			'command'	=> 'chdir',
			'dir'		=> 'templates'
		);
	// create directory with template name
	$actions[] = array(
			'command'	=> 'mkdir',
			'dir'		=> $header['template'],
			'ignore'	=> true
		);
	// change directory
	$actions[] = array(
			'command'	=> 'chdir',
			'dir'		=> $header['template']
		);
	// create all directories and upload all files
	$actions[] = array(
			'command'	=> 'exec',
			'list'		=> generate_actions_dirs()
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
	// remove temporary files
	for($i=0; $i<count($items); $i++)
	{
		if(!empty($items[$i]['tmp']))
		{
			@unlink($items[$i]['tmp']);
		}
	}
	if(!$res)
	{
		if(defined('XS_CLONING'))
		{
			@unlink($tmp_filename);
		}
		xs_error($ftp_error . '<br /><br />' . $lang['xs_import_back']);
	}
}

//
// Check if we need to install style
//
$total = intval($_POST['total']);
$default = isset($_POST['import_default']) && strlen($_POST['import_default']) ? intval($_POST['import_default']) : -1;
$install = array();
$default_name = '';
for($i=0; $i<$total; $i++)
{
	$tmp = empty($_POST['import_install_'.$i]) ? 0 : 1;
	if($tmp)
	{
		$set_default = $default == $i ? 1 : 0;
		$tmp_name = $header['styles'][$i];
		if($tmp_name)
		{
			$install[] = $tmp_name;
			if($set_default)
			{
				$default_name = $tmp_name;
			}
		}
	}
}
if(!count($install))
{
	if(defined('XS_CLONING'))
	{
		@unlink($tmp_filename);
	}
	xs_message($lang['Information'], $lang['xs_import_uploaded'] . '<br /><br />' . $lang['xs_import_back']);
}
//
// Get list of installed styles
//
$tpl = $header['template'];
$sql = "SELECT themes_id, style_name FROM " . THEMES_TABLE . " WHERE template_name='" . xs_sql($tpl) . "'";
if(!$result = $db->sql_query($sql))
{
	if(defined('XS_CLONING'))
	{
		@unlink($tmp_filename);
	}
	xs_error($lang['xs_import_notinstall'] . '<br /><br />' . $lang['xs_import_back']);
}
$style_rowset = $db->sql_fetchrowset($result);
// run theme_info.cfg
$data = xs_get_themeinfo($tpl);
if(!@count($data))
{
	if(defined('XS_CLONING'))
	{
		@unlink($tmp_filename);
	}
	xs_error($lang['xs_import_notinstall2'] . '<br /><br />' . $lang['xs_import_back']);
}
// install styles
$default_id = 0;
for($i=0; $i<count($install); $i++)
{
	$style_name = $install[$i];
	$style_data = false;
	// find entry in theme_info.cfg
	for($j=0; $j<count($data); $j++)
	{
		if($data[$j]['style_name'] === $style_name)
		{
			$style_data = $data[$j];
		}
	}
	// check if already installed
	$installed = 0;
	for($j=0; $j<count($style_rowset); $j++)
	{
		if($style_rowset[$j]['style_name'] === $style_name)
		{
			$installed = $style_rowset[$j]['themes_id'];
		}
	}
	// install/update
	if(empty($style_data['style_name']) || empty($style_data['template_name']))
	{
		if(defined('XS_CLONING'))
		{
			@unlink($tmp_filename);
		}
		xs_error(str_replace('{STYLE}', $style_name, $lang['xs_import_notinstall3']) . '<br /><br />' . $lang['xs_import_back']);
	}
	if($installed)
	{
		// update
		$sql = '';
		foreach($style_data as $var => $value)
		{
			if($sql)
			{
				$sql .= ', ';
			}
			$sql .= xs_sql($var) . " = '" . xs_sql($value) . "'";
		}
		$sql = "UPDATE " . THEMES_TABLE . " SET " . $sql . " WHERE themes_id = '{$installed}'";
	}
	else
	{
		// install
		$sql = "SELECT MAX(themes_id) AS total FROM " . THEMES_TABLE;
		if ( !($result = $db->sql_query($sql)) )
		{
			if(defined('XS_CLONING'))
			{
				@unlink($tmp_filename);
			}
			xs_error($lang['xs_import_notinstall4'] . '<br /><br />' . $lang['xs_import_back']);
		}
		if ( !($row = $db->sql_fetchrow($result)) )
		{
			if(defined('XS_CLONING'))
			{
				@unlink($tmp_filename);
			}
			xs_error($lang['xs_import_notinstall4'] . '<br /><br />' . $lang['xs_import_back']);
		}
		$installed = $row['total'] + 1;
		$style_data['themes_id'] = $installed;
		$sql1 = $sql2 = '';
		foreach($style_data as $var => $value)
		{
			if($sql1)
			{
				$sql1 .= ', ';
				$sql2 .= ', ';
			}
			$sql1 .= xs_sql($var);
			$sql2 .= "'" . xs_sql($value) . "'";
		}
		$sql = "INSERT INTO " . THEMES_TABLE . " (" . $sql1 . ") VALUES (" . $sql2 . ")";
	}
	if ( !($result = $db->sql_query($sql)) )
	{
		if(defined('XS_CLONING'))
		{
			@unlink($tmp_filename);
		}
		xs_error($lang['xs_import_notinstall5'] . '<br /><br />' . $lang['xs_import_back']);
	}
	if($default_name === $style_name)
	{
		$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='{$installed}' WHERE config_name='default_style'";
		$board_config['default_style'] = $installed;
		$db->sql_query($sql);
	}
}
if(defined('XS_CLONING'))
{
	@unlink($tmp_filename);
}
if(count($install) && defined('XS_MODS_CATEGORY_HIERARCHY210'))
{
	// recache themes table
	if ( empty($themes) )
	{
		$themes = new themes();
	}
	if ( !empty($themes) )
	{
		$themes->read(true);
	}
}
if(count($install) && defined('XS_MODS_CATEGORY_HIERARCHY'))
{
	cache_themes();
}
xs_message($lang['Information'], $lang['xs_import_installed'] . '<br /><br />' . $lang['xs_import_back']);

?>