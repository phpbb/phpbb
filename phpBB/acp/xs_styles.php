<?php

/***************************************************************************
 *                               xs_styles.php
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

$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_styles.'.$phpEx) . '">' . $lang['xs_default_style'] . '</a>'));

//
// set new default style
//
if(!empty($_GET['setdefault']) && !defined('DEMO_MODE'))
{
	$board_config['default_style'] = intval($_GET['setdefault']);
	$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='" . $board_config['default_style'] . "' WHERE config_name='default_style'";
	if(defined('XS_MODS_ADMIN_TEMPLATES'))
	{
		$sql = str_replace(' WHERE config_name', ', theme_public=\'1\' WHERE config_name', $sql);
	}
	$db->sql_query($sql);
	if(defined('XS_MODS_CATEGORY_HIERARCHY210'))
	{
		// recache config table
		if ( !empty($config) )
		{
			$config->read(true);
		}
	}
}

//
// change "override" variable
//
if(isset($_GET['setoverride']) && !defined('DEMO_MODE'))
{
	$board_config['override_user_style'] = intval($_GET['setoverride']);
	$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='" . $board_config['override_user_style'] . "' WHERE config_name='override_user_style'";
	$db->sql_query($sql);
	// recache config table
	if(defined('XS_MODS_CATEGORY_HIERARCHY210') && !empty($config))
	{
		$config->read(true);
	}
}

//
// move all users to some style
//
if(!empty($_GET['moveusers']) && !defined('DEMO_MODE'))
{
	$id = intval($_GET['moveusers']);
	$sql = "UPDATE " . USERS_TABLE . " SET user_style='" . $id . "' WHERE user_id > 0";
	$db->sql_query($sql);
}

//
// move all users from some style
//
if(!empty($_GET['moveaway']) && !defined('DEMO_MODE'))
{
	$id = intval($_GET['moveaway']);
	$id2 = intval($_GET['movestyle']);
	if($id2)
	{
		$sql = "UPDATE " . USERS_TABLE . " SET user_style='" . $id2 . "' WHERE user_style = " . $id;
	}
	else
	{
		$sql = "UPDATE " . USERS_TABLE . " SET user_style = NULL WHERE user_style = " . $id;
	}
	$db->sql_query($sql);
}

//
// set admin-only style (Admin Templates mod)
//
if(!empty($_GET['setadmin']) && !defined('DEMO_MODE'))
{
	$id = intval($_GET['setadmin']);
	$setadmin = empty($_GET['admin']) ? 0 : 1;
	$sql = "UPDATE " . THEMES_TABLE . " SET theme_public='{$setadmin}' WHERE themes_id='{$id}'";
	$db->sql_query($sql);
	if(defined('XS_MODS_CATEGORY_HIERARCHY210'))
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
}

//
// get list of installed styles
//
$sql = 'SELECT themes_id, template_name, style_name FROM ' . THEMES_TABLE . ' ORDER BY template_name';
if(defined('XS_MODS_ADMIN_TEMPLATES'))
{
	$sql = str_replace(', style_name', ', style_name, theme_public', $sql);
}
if(!$result = $db->sql_query($sql))
{
	xs_error($lang['xs_no_style_info'], __LINE__, __FILE__);
}
$style_rowset = $db->sql_fetchrowset($result);

$style_override = $board_config['override_user_style'];
$style_default = $board_config['default_style'];
$num_users = 0;
$style_ids = array();

for($i=0; $i<count($style_rowset); $i++)
{
	$id = $style_rowset[$i]['themes_id'];
	$style_ids[] = $id;
	$sql = 'SELECT count(user_id) as total FROM ' . USERS_TABLE . ' WHERE user_style = ' . $id;
	$result = $db->sql_query($sql);
	if(!$result)
	{
		$total = 0;
	}
	else
	{
		$total = $db->sql_fetchrow($result);
		$total = $total['total'];
		$num_users += $total;
	}

	$row_class = $xs_row_class[$i % 2];
	$template->assign_block_vars('styles', array(
		'ROW_CLASS'			=> $row_class,
		'STYLE'				=> $style_rowset[$i]['style_name'],
		'TEMPLATE'			=> $style_rowset[$i]['template_name'],
		'ID'				=> $id,
		'TOTAL'				=> $total,
		'U_TOTAL'			=> append_sid('xs_styles.' . $phpEx . '?list=' . $id),
		'U_DEFAULT'			=> append_sid('xs_styles.' . $phpEx . '?setdefault=' . $id),
		'U_OVERRIDE'		=> append_sid('xs_styles.' . $phpEx . '?setoverride=' . ($style_override ? '0' : '1')),
		'U_SWITCHALL'		=> append_sid('xs_styles.' . $phpEx . '?moveusers=' . $id),
		)
	);
	if($total > 0)
	{
		$template->assign_block_vars('styles.users', array());
	}
	if($id == $style_default)
	{
		$template->assign_block_vars('styles.default', array());
		if($style_override)
		{
			$template->assign_block_vars('styles.default.override', array());
		}
		else
		{
			$template->assign_block_vars('styles.default.nooverride', array());
		}
	}
	else
	{
		$template->assign_block_vars('styles.nodefault', array());
		if(defined('XS_MODS_ADMIN_TEMPLATES'))
		{
			if($style_rowset[$i]['theme_public'])
			{
				$template->assign_block_vars('styles.nodefault.admin_only', array(
					'U_CHANGE'	=> append_sid('xs_styles.'.$phpEx.'?setadmin='.$id.'&admin=0')
				));
			}
			else
			{
				$template->assign_block_vars('styles.nodefault.public', array(
					'U_CHANGE'	=> append_sid('xs_styles.'.$phpEx.'?setadmin='.$id.'&admin=1')
				));
			}
		}
	}
	if($total)
	{
		$template->assign_block_vars('styles.total', array());
	}
	else
	{
		$template->assign_block_vars('styles.none', array());
	}
}

// get number of users using default style
$sql = 'SELECT count(user_id) as total FROM ' . USERS_TABLE . ' WHERE user_style = NULL';
$result = $db->sql_query($sql);
if($result)
{
	$total = $db->sql_fetchrow($result);
	$num_default = $total['total'];
	$num_users += $num_default;
}

// get number of users
$sql = 'SELECT count(user_id) as total FROM ' . USERS_TABLE;
$result = $db->sql_query($sql);
if(!$result)
{
	$total_users = 0;
}
else
{
	$total = $db->sql_fetchrow($result);
	$total_users = $total['total'];
}

$template->assign_vars(array(
	'U_SCRIPT'		=> 'xs_styles.' . $phpEx,
	'NUM_DEFAULT'	=> $num_default
	)
);

if($total_users > $num_users)
{
	// fix problem
	$sql = 'UPDATE ' . USERS_TABLE . ' SET user_style = NULL WHERE user_style NOT IN (' . implode(', ', $style_ids) . ')';
	$db->sql_query($sql);
}

//
// get list of users
//
if(isset($_GET['list']))
{
	$id = intval($_GET['list']);
	$template->assign_block_vars('list_users', array());
	$sql = "SELECT user_id, username FROM " . USERS_TABLE . " WHERE user_style='{$id}' ORDER BY username ASC";
	if(!$result = $db->sql_query($sql))
	{
		xs_error('Could not get users list!', __LINE__, __FILE__);
	}
	$rowset = $db->sql_fetchrowset($result);
	for($i=0; $i<count($rowset); $i++)
	{
		$template->assign_block_vars('list_users.user', array(
			'NUM'		=> $i + 1,
			'ID'		=> $rowset[$i]['user_id'],
			'NAME'		=> htmlspecialchars($rowset[$i]['username']),
			)
		);
	}
}

$template->set_filenames(array('body' => XS_TPL_PATH . 'styles.tpl'));
$template->pparse('body');
xs_exit();

?>