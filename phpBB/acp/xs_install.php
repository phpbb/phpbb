<?php

/***************************************************************************
 *                              xs_install.php
 *                              --------------
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

$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_install.'.$phpEx) . '">' . $lang['xs_install_styles'] . '</a>'));

$lang['xs_install_back'] = str_replace('{URL}', append_sid('xs_install.'.$phpEx), $lang['xs_install_back']);
$lang['xs_goto_default'] = str_replace('{URL}', append_sid('xs_styles.'.$phpEx), $lang['xs_goto_default']);

// remove timeout. useful for forum with 100+ styles
@set_time_limit(XS_MAX_TIMEOUT);

// install style
if(!empty($_GET['style']) && !defined('DEMO_MODE'))
{
	$style = stripslashes($_GET['style']);
	$num = intval($_GET['num']);
	$res = xs_install_style($style, $num);
	if(defined('REFRESH_NAVBAR'))
	{
		$template->assign_block_vars('left_refresh', array(
				'ACTION'	=> append_sid('index.' . $phpEx . '?pane=left')
			));
	}
	if($res)
	{
		if(defined('XS_MODS_CATEGORY_HIERARCHY'))
		{
			cache_themes();
		}
		xs_message($lang['Information'], $lang['xs_install_installed'] . '<br /><br />' . $lang['xs_install_back'] . '<br /><br />' . $lang['xs_goto_default']);
	}
	xs_error($lang['xs_install_error'] . '<br /><br />' . $lang['xs_install_back']);
}

// install styles
if(!empty($_POST['total']) && !defined('DEMO_MODE'))
{
	$tpl = array();
	$num = array();
	$total = intval($_POST['total']);
	for($i=0; $i<$total; $i++)
	{
		if(!empty($_POST['install_'.$i]))
		{
			$tpl[] = stripslashes($_POST['install_'.$i.'_style']);
			$num[] = intval($_POST['install_'.$i.'_num']);
		}
	}
	if(count($tpl))
	{
		for($i=0; $i<count($tpl); $i++)
		{
			xs_install_style($tpl[$i], $num[$i]);
		}
		if(defined('REFRESH_NAVBAR'))
		{
			$template->assign_block_vars('left_refresh', array(
					'ACTION'	=> append_sid('index.' . $phpEx . '?pane=left')
				));
		}
		if(defined('XS_MODS_CATEGORY_HIERARCHY'))
		{
			cache_themes();
		}
		xs_message($lang['Information'], $lang['xs_install_installed'] . '<br /><br />' . $lang['xs_install_back'] . '<br /><br />' . $lang['xs_goto_default']);
	}
}


// get all installed styles
$sql = 'SELECT themes_id, template_name, style_name FROM ' . THEMES_TABLE . ' ORDER BY template_name';
if(!$result = $db->sql_query($sql))
{
	xs_error($lang['xs_no_style_info'], __LINE__, __FILE__);
}
$style_rowset = $db->sql_fetchrowset($result);

// find all styles to install
$res = @opendir('../templates/');
$styles = array();
while(($file = readdir($res)) !== false)
{
	if($file !== '.' && $file !== '..' && @file_exists('../templates/'.$file.'/theme_info.cfg') && @file_exists('../templates/'.$file.'/'.$file.'.cfg'))
	{
		$arr = xs_get_themeinfo($file);
		for($i=0; $i<count($arr); $i++)
		{
			if(isset($arr[$i]['template_name']) && $arr[$i]['template_name'] === $file)
			{
				$arr[$i]['num'] = $i;
				$style = $arr[$i]['style_name'];
				$found = false;
				for($j=0; $j<count($style_rowset); $j++)
				{
					if($style_rowset[$j]['style_name'] == $style)
					{
						$found = true;
					}
				}
				if(!$found)
				{
					$styles[$arr[$i]['style_name']] = $arr[$i];
				}
			}
		}
	}
}
closedir($res);

if(!count($styles))
{
	xs_message($lang['Information'], $lang['xs_install_none'] . '<br /><br />' . $lang['xs_goto_default']);
}

ksort($styles);

$j = 0;
foreach($styles as $var => $value)
{
	$row_class = $xs_row_class[$j % 2];
	$template->assign_block_vars('styles', array(
			'ROW_CLASS'	=> $row_class,
			'STYLE'		=> htmlspecialchars($value['template_name']),
			'THEME'		=> htmlspecialchars($value['style_name']),
			'U_INSTALL'	=> append_sid('xs_install.'.$phpEx.'?style='.urlencode($value['template_name']).'&num='.$value['num']),
			'CB_NAME'	=> 'install_'.$j,
			'NUM'		=> $value['num'],
		)
	);
	$j++;
}

$template->assign_vars(array(
	'U_INSTALL'		=> append_sid('xs_install.'.$phpEx),
	'TOTAL'			=> count($styles)
	));

$template->set_filenames(array('body' => XS_TPL_PATH . 'install.tpl'));
$template->pparse('body');
xs_exit();

?>