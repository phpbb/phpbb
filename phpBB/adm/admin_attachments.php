<?php
/***************************************************************************
 *                           admin_attachments.php
 *                            -------------------
 *   begin                : Sunday, Apr 20, 2003
 *   copyright            : (C) 2003 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['POST']['ATTACHMENTS'] = ($auth->acl_get('a_attach')) ? $filename . $SID . '&amp;mode=manage' : '';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

if (!$auth->acl_get('a_attach'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

$mode = (isset($_REQUEST['mode'])) ? trim(htmlspecialchars($_REQUEST['mode'])) : '';

$config_sizes = array('max_filesize' => 'size', 'attachment_quota' => 'quota_size', 'max_filesize_pm' => 'pm_size');

foreach ($config_sizes as $cfg_key => $var)
{
	$$var = (isset($_REQUEST[$var])) ? trim(htmlspecialchars($_REQUEST[$var])) : '';
}

$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$search_imagick = (isset($_POST['search_imagick'])) ? TRUE : FALSE;

$error = $notify = array();

// Pull all config data
$sql = 'SELECT *
	FROM ' . CONFIG_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$config_name = $row['config_name'];
	$config_value = $row['config_value'];

	$default_config[$config_name] = $config_value;
	$new[$config_name] = (isset($_POST[$config_name])) ? $_POST[$config_name] : $default_config[$config_name];

	foreach ($config_sizes as $cfg_key => $var)
	{
		if (empty($$var) && !$submit && $config_name == $cfg_key)
		{
			$$var = (intval($default_config[$config_name]) >= 1048576) ? 'mb' : ((intval($default_config[$config_name]) >= 1024) ? 'kb' : 'b');
		}

		if (!$submit && $config_name == $cfg_key)
		{
			$new[$config_name] = ($new[$config_name] >= 1048576) ? round($new[$config_name] / 1048576 * 100) / 100 : (($new[$config_name] >= 1024) ? round($new[$config_name] / 1024 * 100) / 100 : $new[$config_name]);
		}

		if ($submit && $mode == 'manage' && $config_name == $cfg_key)
		{
			$old = $new[$config_name];
			$new[$config_name] = ($$var == 'kb') ? round($new[$config_name] * 1024) : (($$var == 'mb') ? round($new[$config_name] * 1048576) : $new[$config_name]);
		}
	} 

	if ($submit && ($mode == 'manage' || $mode == 'cats'))
	{
		// Update Extension Group Filesizes
		if ($config_name == 'max_filesize')
		{
			$old_size = intval($default_config[$config_name]);
			$new_size = intval($new[$config_name]);

			if ($old_size != $new_size)
			{
				// See, if we have a similar value of old_size in Extension Groups. If so, update these values.
				$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . "
					SET max_filesize = $new_size
					WHERE max_filesize = $old_size";
				$db->sql_query($sql);
			}
		}

		set_config($config_name, str_replace('\\\\', '\\', addslashes($new[$config_name])));
	
		if (in_array($config_name, array('max_filesize', 'attachment_quota', 'max_filesize_pm')))
		{
			$new[$config_name] = $old;
		}
	}
}

if ($submit && ($mode == 'manage' || $mode == 'cats'))
{
	add_log('admin', 'LOG_ATTACH_CONFIG');
	$notify[] = $user->lang['ATTACH_CONFIG_UPDATED'];
}

// Adjust the Upload Directory - relative or absolute, this is the question here.
$upload_dir = ($new['upload_dir'][0] == '/' || ($new['upload_dir'][0] != '/' && $new['upload_dir'][1] == ':')) ? $new['upload_dir'] : $phpbb_root_path . $new['upload_dir'];

switch ($mode)
{
	case 'manage':
		$l_title = 'ATTACHMENT_CONFIG';
		break;

	case 'cats':
		$l_title = 'MANAGE_CATEGORIES';
		break;
	
	case 'extensions':
		$l_title = 'MANAGE_EXTENSIONS';
		break;

	case 'ext_groups':
		$l_title = 'EXTENSION_GROUPS_TITLE';
}

// Temporary Language Variables

adm_page_header($user->lang[$l_title]);

// Search Imagick
if ($search_imagick)
{
	$imagick = '';
	
	$exe = ((defined('PHP_OS')) && (preg_match('#win#i', PHP_OS))) ? '.exe' : '';

	if (empty($_ENV['MAGICK_HOME']))
	{
		$locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', '/usr/bin/imagemagick/');

		foreach ($locations as $location)
		{
			if (file_exists($location . 'convert' . $exe) && is_executable($location . 'convert' . $exe))
			{
				$imagick = str_replace('\\', '/', $location);
				continue;
			}
		}
	}
	else
	{
		$imagick = str_replace('\\', '/', $_ENV['MAGICK_HOME']);
	}

	$new['img_imagick'] = $imagick;
}

// Check Settings
if ($submit && $mode == 'manage')
{
	test_upload($error, $upload_dir, false);
}

if ($submit && $mode == 'cats')
{
	test_upload($error, $upload_dir . '/thumbs', true);
}

if ($submit && $mode == 'extensions')
{
	// Change Extensions ?
	$extension_change_list	= (isset($_POST['extension_change_list'])) ? array_map('intval', $_POST['extension_change_list']) : array();
	$extension_explain_list	= (isset($_POST['extension_explain_list'])) ? array_map('trim', $_POST['extension_explain_list']) : array();
	$group_select_list		= (isset($_POST['group_select'])) ? array_map('intval', $_POST['group_select']) : array();

	// Generate correct Change List
	$extensions = array();

	for ($i = 0; $i < count($extension_change_list); $i++)
	{
		$extensions[$extension_change_list[$i]]['comment'] = stripslashes(htmlspecialchars($extension_explain_list[$i]));
		$extensions[$extension_change_list[$i]]['group_id'] = $group_select_list[$i];
	}

	$sql = 'SELECT *
		FROM ' . EXTENSIONS_TABLE . '
		ORDER BY extension_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['comment'] != $extensions[$row['extension_id']]['comment'] || $row['group_id'] != $extensions[$row['extension_id']]['group_id'])
		{
			$sql = "UPDATE " . EXTENSIONS_TABLE . " 
				SET comment = '" . $extensions[$row['extension_id']]['comment'] . "', group_id = " . $extensions[$row['extension_id']]['group_id'] . "
				WHERE extension_id = " . $row['extension_id'];
			$db->sql_query($sql);				
			add_log('admin', 'LOG_ATTACH_EXT_UPDATE', $row['extension']);
		}
	}
	$db->sql_freeresult($result);

	// Delete Extension ?
	$extension_id_list = (isset($_POST['extension_id_list'])) ? array_map('intval', $_POST['extension_id_list']) : array();

	if (count($extension_id_list))
	{
		$sql = "DELETE 
			FROM " . EXTENSIONS_TABLE . "
			WHERE extension_id IN (" . implode(', ', $extension_id_list) . ")";
		$db->sql_query($sql);

		$sql = "SELECT extension 
			FROM " . EXTENSIONS_TABLE . "
			WHERE extension_id IN (" . implode(', ', $extension_id_list) . ")";
		$result = $db->sql_query($sql);
		
		$extension_list = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$extension_list .= ($extension_list == '') ? $row['extension'] : ', ' . $row['extension'];
		}
		$db->sql_freeresult($result);

		add_log('admin', 'LOG_ATTACH_EXT_DEL', $extension_list);
	}
		
	// Add Extension ?
	$add_extension			= (isset($_POST['add_extension'])) ? strtolower(trim(strip_tags($_POST['add_extension']))) : '';
	$add_extension_explain	= (isset($_POST['add_extension_explain'])) ?  trim(strip_tags($_POST['add_extension_explain'])) : '';
	$add_extension_group	= (isset($_POST['add_group_select'])) ?  intval($_POST['add_group_select']) : '';
	$add					= (isset($_POST['add_extension_check'])) ? TRUE : FALSE;

	if ($add_extension != '' && $add)
	{
		if (!count($error))
		{
			$sql = 'SELECT extension_id
				FROM ' . EXTENSIONS_TABLE . "
				WHERE extension = '$add_extension'";
			$result = $db->sql_query($sql);
			
			if ($row = $db->sql_fetchrow($result))
			{
				$error[] = sprintf($user->lang['EXTENSION_EXIST'], $add_extension);
			}
			$db->sql_freeresult($result);

			if (!count($error))
			{
				$sql = 'INSERT INTO ' . EXTENSIONS_TABLE . " (group_id, extension, comment) 
					VALUES ($add_extension_group, '" . $add_extension . "', '" . $add_extension_explain . "')";
				$db->sql_query($sql);
				add_log('admin', 'LOG_ATTACH_EXT_ADD', $add_extension);
			}
		}
	}

	if (!count($error))
	{
		$notify[] = $user->lang['EXTENSIONS_UPDATED'];
	}
}

if ($submit && $mode == 'ext_groups')
{
	// Change Extension Groups ?
	$group_change_list		= (isset($_POST['group_change_list'])) ? array_map('intval', $_POST['group_change_list']) : array();
	$extension_group_list	= (isset($_POST['extension_group_list'])) ? $_POST['extension_group_list'] : array();
	$group_allowed_list		= (isset($_POST['allowed_list'])) ? array_map('intval', $_POST['allowed_list']) : array();
	$download_mode_list		= (isset($_POST['download_mode_list'])) ? $_POST['download_mode_list'] : array();
	$category_list			= (isset($_POST['category_list'])) ? array_map('intval', $_POST['category_list']) : array();
	$upload_icon_list		= (isset($_POST['upload_icon_list'])) ? $_POST['upload_icon_list'] : array();
	$filesize_list			= (isset($_POST['max_filesize_list'])) ? $_POST['max_filesize_list'] : array();
	$size_select_list		= (isset($_POST['size_select_list'])) ? $_POST['size_select_list'] : array();

	$allowed_list = array();

	for ($i = 0; $i < count($group_allowed_list); $i++)
	{
		for ($j = 0; $j < count($group_change_list); $j++)
		{
			if ($group_allowed_list[$i] == $group_change_list[$j])
			{
				$allowed_list[$j] = '1';
			}
		}
	}

	for ($i = 0; $i < count($group_change_list); $i++)
	{
		$allowed = (isset($allowed_list[$i])) ? 1 : 0;
		
		$filesize_list[$i] = ($size_select_list[$i] == 'kb') ? round($filesize_list[$i] * 1024) : (($size_select_list[$i] == 'mb') ? round($filesize_list[$i] * 1048576) : $filesize_list[$i]);

		$group_sql = array(
			'group_name'	=> trim(htmlspecialchars($extension_group_list[$i])),
			'cat_id'		=> $category_list[$i],
			'allow_group'	=> $allowed,
			'download_mode'	=> $download_mode_list[$i],
			'upload_icon'	=> ($upload_icon_list[$i] == 'no_image') ? '' : $upload_icon_list[$i],
			'max_filesize'	=> $filesize_list[$i]
		);
		
		$sql = "UPDATE " . EXTENSION_GROUPS_TABLE . " 
			SET " . $db->sql_build_array('UPDATE', $group_sql) . " 
			WHERE group_id = " . $group_change_list[$i];
		$db->sql_query($sql);
	}
	
	// Delete Extension Groups
	$group_id_list = (isset($_POST['group_id_list'])) ? array_map('intval', $_POST['group_id_list']) : array();

	if (count($group_id_list))
	{
		$l_group_list = '';

		$sql = 'SELECT group_name 
			FROM ' . EXTENSION_GROUPS_TABLE . '
			WHERE group_id IN (' . implode(', ', $group_id_list) . ')';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$l_group_list .= (($l_group_list != '') ? ', ' : '') . $row['group_name'];
		}
		$db->sql_freeresult($result);
		
		$sql = 'DELETE 
			FROM ' . EXTENSION_GROUPS_TABLE . ' 
			WHERE group_id IN (' . implode(', ', $group_id_list) . ')';
		$db->sql_query($sql);

		// Set corresponding Extensions to a pending Group
		$sql = "UPDATE " . EXTENSIONS_TABLE . "
			SET group_id = 0
			WHERE group_id IN (" . implode(', ', $group_id_list) . ")";
		$db->sql_query($sql);
	
		add_log('admin', 'LOG_ATTACH_EXTGROUP_DEL', $l_group_list);
	}
		
	// Add Extensions Group ?
	$extension_group	= (isset($_POST['add_extension_group'])) ?  trim(strip_tags($_POST['add_extension_group'])) : '';
	$download_mode		= (isset($_POST['add_download_mode'])) ?  $_POST['add_download_mode'] : '';
	$cat_id				= (isset($_POST['add_category'])) ?  $_POST['add_category'] : '';
	$upload_icon		= (isset($_POST['add_upload_icon'])) ?  $_POST['add_upload_icon'] : '';
	$filesize			= (isset($_POST['add_max_filesize'])) ?  $_POST['add_max_filesize'] : '';
	$size_select		= (isset($_POST['add_size_select'])) ?  $_POST['add_size_select'] : '';
	$is_allowed			= (isset($_POST['add_allowed'])) ? 1 : 0;
	$add				= (isset($_POST['add_extension_group_check'])) ? TRUE : FALSE;

	if ($extension_group != '' && $add)
	{
		// check Extension Group
		$sql = 'SELECT group_name 
			FROM ' . EXTENSION_GROUPS_TABLE;
		$result = $db->sql_query_limit($sql, 1);
			
		if ($row = $db->sql_fetchrow($result))
		{
			if ($row['group_name'] == $extension_group)
			{
				$error[] = sprintf($user->lang['EXTENSION_GROUP_EXIST'], $extension_group);
			}
		}
		$db->sql_freeresult($result);
		
		if (!count($error))
		{
			$filesize = ($size_select == 'kb') ? round($filesize * 1024) : (($size_select == 'mb') ? round($filesize * 1048576) : $filesize);
		
			$group_sql = array(
				'group_name'	=> $extension_group,
				'cat_id'		=> $cat_id,
				'allow_group'	=> $is_allowed,
				'download_mode'	=> $download_mode,
				'upload_icon'	=> ($upload_icon == 'no_image') ? '' : $upload_icon,
				'max_filesize'	=> $filesize
			);
			
			$sql = 'INSERT INTO ' . EXTENSION_GROUPS_TABLE . ' ' . 
				$db->sql_build_array('INSERT', $group_sql);
			$db->sql_query($sql);
			
			add_log('admin', 'LOG_ATTACH_EXTGROUP_ADD', $extension_group);
		}
	}

	$sql = 'SELECT e.extension, g.*
		FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
		WHERE e.group_id = g.group_id
			AND g.allow_group = 1';
	$result = $db->sql_query($sql);

	$extensions = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$extension = $row['extension'];

		$extensions['_allowed_'][]				= $extension;
		$extensions[$extension]['display_cat']	= intval($row['cat_id']);
		$extensions[$extension]['download_mode']= intval($row['download_mode']);
		$extensions[$extension]['upload_icon']	= trim($row['upload_icon']);
		$extensions[$extension]['max_filesize']	= intval($row['max_filesize']);
	}
	$db->sql_freeresult($result);

	$cache->destroy('extensions');
	$cache->put('extensions', $extensions);

	if (!count($error))
	{
		$notify[] = $user->lang['EXTENSION_GROUPS_UPDATED'];
	}
}

?>

<h1><?php echo $user->lang[$l_title]; ?></h1>

<p><?php echo $user->lang[$l_title . '_EXPLAIN']; ?></p>

<?php 
if (count($error))
{
?>

<h2 style="color:red"><?php echo $user->lang['WARNING']; ?></h2>

<p><?php echo implode('<br />', $error); ?></p>

<?php
}

if (count($notify))
{
?>

<h2 style="color:green"><?php echo $user->lang['NOTIFY']; ?></h2>

<p><?php echo implode('<br />', $notify); ?></p>

<?php
}

$modes = array('manage', 'cats', 'extensions', 'ext_groups');

$select_size_mode = size_select('size', $size);
$select_quota_size_mode = size_select('quota_size', $quota_size);
$select_pm_size_mode = size_select('pm_size', $pm_size);

?>
<form action="admin_attachments.<?php echo $phpEx . $SID . "&amp;mode=$mode"; ?>" method="post">
	<table cellspacing="1" cellpadding="0" border="0" align="center" width="99%">
	<tr>
		<td align="right"> &nbsp;&nbsp; 
<?php
	for ($i = 0; $i < count($modes); $i++)
	{
		if ($i != 0)
		{
			?> | <?php
		}

		if ($mode != $modes[$i])
		{
			?><a href="admin_attachments.<?php echo $phpEx . $SID . '&amp;mode=' . $modes[$i]; ?>"><?php
		}
		
		echo $user->lang['ATTACH_' . strtoupper($modes[$i]) . '_URL'];
		
		if ($mode != $modes[$i])
		{
			?></a><?php
		}
	}
?>		</td>
	</tr>
	</table>
<?php

if ($mode == 'manage')
{

	$yes_no_switches = array('disable_mod', 'allow_pm_attach', 'display_order');

	for ($i = 0; $i < count($yes_no_switches); $i++)
	{
		eval("\$" . $yes_no_switches[$i] . "_yes = ( \$new['" . $yes_no_switches[$i] . "']) ? 'checked=\"checked\"' : '';");
		eval("\$" . $yes_no_switches[$i] . "_no = ( !\$new['" . $yes_no_switches[$i] . "']) ? 'checked=\"checked\"' : '';");
	}

?>
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr>
	  <th align="center" colspan="2"><?php echo $user->lang['ATTACHMENT_SETTINGS']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['UPLOAD_DIR']; ?>:<br /><span class="gensmall"><?php echo $user->lang['UPLOAD_DIR_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="upload_dir" class="post" value="<?php echo $new['upload_dir'] ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['DISPLAY_ORDER']; ?>:<br /><span class="gensmall"><?php echo $user->lang['DISPLAY_ORDER_EXPLAIN']; ?></span></td>
		<td class="row2">
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td><input type="radio" name="display_order" value="0" <?php echo $display_order_no; ?> /> <?php echo $user->lang['DESCENDING']; ?></td>
            </tr>
            <tr>
                 <td><input type="radio" name="display_order" value="1" <?php echo $display_order_yes; ?> /> <?php echo $user->lang['ASCENDING']; ?></td>
            </tr>
		</table></td>
	</tr>
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['ATTACH_MAX_FILESIZE']; ?>:<br /><span class="gensmall"><?php echo $user->lang['ATTACH_MAX_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="max_filesize" class="post" value="<?php echo $new['max_filesize']; ?>" /> <?php echo $select_size_mode; ?></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['ATTACH_QUOTA']; ?>:<br /><span class="gensmall"><?php echo $user->lang['ATTACH_QUOTA_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="attachment_quota" class="post" value="<?php echo $new['attachment_quota']; ?>" /> <?php echo $select_quota_size_mode; ?></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['ATTACH_MAX_PM_FILESIZE']; ?>:<br /><span class="gensmall"><?php echo $user->lang['ATTACH_MAX_PM_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="max_filesize_pm" class="post" value="<?php echo $new['max_filesize_pm']; ?>" /> <?php echo $select_pm_size_mode; ?></td>
	</tr>
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['MAX_ATTACHMENTS'] ?>:<br /><span class="gensmall"><?php echo $user->lang['MAX_ATTACHMENTS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="3" name="max_attachments" class="post" value="<?php echo $new['max_attachments']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['MAX_ATTACHMENTS_PM'] ?>:<br /><span class="gensmall"><?php echo $user->lang['MAX_ATTACHMENTS_PM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="3" name="max_attachments_pm" class="post" value="<?php echo $new['max_attachments_pm']; ?>" /></td>
	</tr>
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['PM_ATTACH']; ?>:<br /><span class="gensmall"><?php echo $user->lang['PM_ATTACH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="allow_pm_attach" value="1" <?php echo $allow_pm_attach_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_pm_attach" value="0" <?php echo $allow_pm_attach_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
</table>
<?php
}

if ($mode == 'cats')
{
	$sql = 'SELECT group_name, cat_id
		FROM ' . EXTENSION_GROUPS_TABLE . '
		WHERE cat_id > 0
		ORDER BY cat_id';
	$result = $db->sql_query($sql);

	$s_assigned_groups = array();

	while ($row = $db->sql_fetchrow($result))
	{
		$s_assigned_groups[$row['cat_id']][] = $row['group_name'];
	}
	
	$display_inlined_yes = ($new['img_display_inlined']) ? 'checked="checked"' : '';
	$display_inlined_no = (!$new['img_display_inlined']) ? 'checked="checked"' : '';

	$create_thumbnail_yes = ($new['img_create_thumbnail']) ? 'checked="checked"' : '';
	$create_thumbnail_no = (!$new['img_create_thumbnail']) ? 'checked="checked"' : '';

?>
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr>
	  <th align="center" colspan="2"><?php echo $user->lang['SETTINGS_CAT_IMAGES']; ?></th>
	</tr>
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	<tr>
	  <th align="center" colspan="2"><?php echo $user->lang['ASSIGNED_GROUP']; ?>: <?php echo ( (count($s_assigned_groups[IMAGE_CAT])) ? implode(', ', $s_assigned_groups[IMAGE_CAT]) : $user->lang['NONE']); ?></th>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['DISPLAY_INLINED']; ?>:<br /><span class="gensmall"><?php echo $user->lang['DISPLAY_INLINED_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="img_display_inlined" value="1" <?php echo $display_inlined_yes ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="img_display_inlined" value="0" <?php echo $display_inlined_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php
	
	// Check Thumbnail Support
	if ($new['img_imagick'] == '' && !count(get_supported_image_types()))
	{
		$new['img_create_thumbnail'] = '0';
	}
	else
	{
?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['CREATE_THUMBNAIL']; ?>:<br /><span class="gensmall"><?php echo $user->lang['CREATE_THUMBNAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="img_create_thumbnail" value="1" <?php echo $create_thumbnail_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="img_create_thumbnail" value="0" <?php echo $create_thumbnail_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['MIN_THUMB_FILESIZE']; ?>:<br /><span class="gensmall"><?php echo $user->lang['MIN_THUMB_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="7" maxlength="15" name="img_min_thumb_filesize" value="<?php echo $new['img_min_thumb_filesize']; ?>" class="post" /> <?php echo $user->lang['BYTES']; ?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['IMAGICK_PATH']; ?>:<br /><span class="gensmall"><?php echo $user->lang['IMAGICK_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="200" name="img_imagick" value="<?php echo $new['img_imagick']; ?>" class="post" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['MAX_IMAGE_SIZE']; ?>:<br /><span class="gensmall"><?php echo $user->lang['MAX_IMAGE_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_max_width" value="<?php echo $new['img_max_width']; ?>" class="post" /> x <input type="text" size="3" maxlength="4" name="img_max_height" value="<?php echo $new['img_max_height']; ?>" class="post" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['IMAGE_LINK_SIZE']; ?>:<br /><span class="gensmall"><?php echo $user->lang['IMAGE_LINK_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_link_width" value="<?php echo $new['img_link_width']; ?>" class="post" /> x <input type="text" size="3" maxlength="4" name="img_link_height" value="<?php echo $new['img_link_height']; ?>" class="post" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="submit" name="search_imagick" value="<?php echo $user->lang['SEARCH_IMAGICK']; ?>" class="btnlite" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
</table>

<?php
}

if ($mode == 'ext_groups')
{
//	SELFNOTE: DO NOT FORGET TO TALK ABOUT IT!
//	$img_path = $config['upload_icons_path'];
	$img_path = 'images/upload_icons';

	$imglist = filelist($phpbb_root_path . $img_path, '');

	$filename_list = '';
	foreach ($imglist as $path => $img_ary)
	{
		foreach ($img_ary as $img)
		{
			$img = substr($path, 1) . (($path != '') ? '/' : '') . $img; 
			$filename_list .= '<option value="' . htmlspecialchars($img) . '">' . $img . '</option>';
		}
	}
	
	$size = isset($_REQUEST['size']) ? intval($_REQUEST['size']) : 0;
	
	if (!$size && !$submit)
	{
		$max_add_filesize = intval($config['max_filesize']);
		$size = ($max_add_filesize >= 1048576) ? 'mb' : (($max_add_filesize >= 1024) ? 'kb' : 'b');
	}

	$max_add_filesize = ($max_add_filesize >= 1048576) ? round($max_add_filesize / 1048576 * 100) / 100 : (($max_add_filesize >= 1024) ? round($max_add_filesize / 1024 * 100) / 100 : $max_add_filesize);

	$viewgroup = (!empty($_REQUEST['g'])) ? $_REQUEST['g'] : -1;
?>
	
	<script language="javascript" type="text/javascript" defer="defer">
	<!--

	function update_add_image(newimage)
	{
		if (newimage == 'no_image')
		{
			document.add_image.src = '<?php echo $phpbb_root_path ?>images/spacer.gif';
		}
		else
		{
			document.add_image.src = "<?php echo $phpbb_root_path . $img_path ?>/" + newimage;
		}
	}

	function update_image(newimage, index)
	{
		if (newimage == 'no_image')
		{
			eval('document.image_' + index + '.src = "<?php echo $phpbb_root_path ?>images/spacer.gif";');
		}
		else
		{
			eval('document.image_' + index + '.src = "<?php echo $phpbb_root_path . $img_path ?>/" + newimage;');
		}
	}

	//-->
	</script>

	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr>
	  <th align="center" colspan="7"><?php echo $user->lang['EXTENSION_GROUPS_TITLE']; ?></th>
	</tr>
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	<tr> 
	  <th>&nbsp;<?php echo $user->lang['EXTENSION_GROUP']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['SPECIAL_CATEGORY']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['ALLOWED']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['DOWNLOAD_MODE']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['UPLOAD_ICON']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['MAX_EXTGROUP_FILESIZE']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['ADD']; ?>&nbsp;</th>
	</tr>
	<tr>
		<td class="row1" align="center" valign="middle">
			<table width="100%" align="center" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="row1" align="center" valign="middle" width="10%" wrap="nowrap">&nbsp;</td>
				<td class="row1" align="left" valign="middle"><input type="text" size="20" maxlength="100" name="add_extension_group" class="post" value="<?php echo ((isset($submit)) ? $extension_group : '') ?>" /></td>
			</tr>
			</table>
		</td>
		<td class="row1" align="center" valign="middle"><?php echo category_select('add_category'); ?></td>
		<td class="row1" align="center" valign="middle"><input type="checkbox" name="add_allowed" /></td>
		<td class="row1" align="center" valign="middle"><?php echo download_select('add_download_mode'); ?></td>
		<td class="row1" align="center" valign="middle"><select name="add_upload_icon" onChange="update_add_image(this.options[selectedIndex].value);"><option value="no_image" selected="selected"><?php echo $user->lang['NO_IMAGE']; ?></option><?php echo $filename_list ?></select> &nbsp; <img src="<?php echo $phpbb_root_path . 'images/spacer.gif' ?>"  name="add_image" border="0" alt="" title="" /> &nbsp;</td>
		<td class="row1" align="center" valign="middle"><input type="text" size="3" maxlength="15" name="add_max_filesize" class="post" value="<?php echo $max_add_filesize; ?>" /> <?php echo size_select('add_size_select', $size); ?></td>
		<td class="row1" align="center" valign="middle"><input type="checkbox" name="add_extension_group_check" /></td>
	</tr>
	<tr align="right">
		<td class="cat" colspan="7"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" /></td>
	</tr>
	<tr> 
	  <th>&nbsp;<?php echo $user->lang['EXTENSION_GROUP']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['SPECIAL_CATEGORY']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['ALLOWED']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['DOWNLOAD_MODE']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['UPLOAD_ICON']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['MAX_EXTGROUP_FILESIZE']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['DELETE']; ?>&nbsp;</th>
	</tr>
<?

	$sql = 'SELECT * 
		FROM ' . EXTENSION_GROUPS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		// Format the filesize
		if ($row['max_filesize'] == 0)
		{
			$row['max_filesize'] = intval($config['max_filesize']);
		}

		$size_format = ($row['max_filesize'] >= 1048576) ? 'mb' : (($row['max_filesize'] >= 1024) ? 'kb' : 'b');

		$row['max_filesize'] = ($row['max_filesize'] >= 1048576) ? round($row['max_filesize'] / 1048576 * 100) / 100 : (($row['max_filesize'] >= 1024) ? round($row['max_filesize'] / 1024 * 100) / 100 : $row['max_filesize']);

		$s_allowed = ($row['allow_group'] == 1) ? 'checked="checked"' : '';
		$edit_img = ($row['upload_icon'] != '') ? $row['upload_icon'] : '';

		$filename_list = '';
		$no_image_select = false;
		foreach ($imglist as $img)
		{
			$img = substr($img['path'], 1) . (($img['path'] != '') ? '/' : '') . $img['file']; 

			if ($edit_img == '')
			{
				$no_image_select = true;
				$selected = '';
			}
			else
			{
				$selected = ($edit_img == $img) ? ' selected="selected"' : '';
			}

			$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>';
		}
?>
	<tr> 
		<input type="hidden" name="group_change_list[]" value="<?php echo $row['group_id']; ?>" />
		<td class="row1" align="center" valign="middle">
			<table width="100%" align="center" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="row1" align="center" valign="middle" width="10%" wrap="nowrap"><b><a href="<?php echo (($viewgroup == $row['group_id']) ? "admin_attachments.$phpEx$SID&mode=ext_groups" : "admin_attachments.$phpEx$SID&mode=ext_groups&g=" . $row['group_id']); ?>" class="gen"><?php echo (($viewgroup == $row['group_id']) ? '-' : '+'); ?></a></span></b></td>
				<td class="row1" align="left" valign="middle"><input type="text" size="20" maxlength="100" name="extension_group_list[]" class="post" value="<?php echo $row['group_name']; ?>" /></td>
			</tr>
			</table>
		</td>
		<td class="row2" align="center" valign="middle"><?php echo category_select('category_list[]', $row['group_id']); ?></td>
		<td class="row1" align="center" valign="middle"><input type="checkbox" name="allowed_list[]" value="<?php echo $row['group_id']; ?>" <?php echo $s_allowed; ?> /></td>
		<td class="row2" align="center" valign="middle"><?php echo download_select('download_mode_list[]', $row['group_id']); ?></td>
		<td class="row1" align="center" valign="middle"><select name="upload_icon_list[]" onChange="update_image(this.options[selectedIndex].value, <?php echo $row['group_id']; ?>);"><option value="no_image"<?php echo (($no_image_select) ? ' selected="selected"' : ''); ?>><?php echo $user->lang['NO_IMAGE']; ?></option><?php echo $filename_list ?></select> &nbsp; <img src="<?php echo (($no_image_select) ? $phpbb_root_path . 'images/spacer.gif' : $phpbb_root_path . $img_path . '/' . $edit_img) ?>" name="image_<?php echo $row['group_id']; ?>" border="0" alt="" title="" /> &nbsp;</td>
		<td class="row2" align="center" valign="middle"><input type="text" size="3" maxlength="15" name="max_filesize_list[]" class="post" value="<?php echo $row['max_filesize']; ?>" /> <?php echo size_select('size_select_list[]', $size_format); ?></td>
		<td class="row2" align="center" valign="middle"><input type="checkbox" name="group_id_list[]" value="<?php echo $row['group_id']; ?>" /></td>
	</tr>
<?

		if ($viewgroup != -1 && $viewgroup == $row['group_id'])
		{
			$sql = 'SELECT comment, extension 
				FROM ' . EXTENSIONS_TABLE . '
				WHERE group_id = ' . intval($viewgroup);
			$e_result = $db->sql_query($sql);

			while ($e_row = $db->sql_fetchrow($e_result))
			{
?>
			<tr> 
				<td class="row2" align="center" valign="middle"><span class="postdetails"><?php echo $e_row['extension']; ?></span></td>
				<td class="row2" align="center" valign="middle" colspan="6"><span class="postdetails"><?php echo $e_row['comment']; ?></span></td>
			</tr>
<?
			}
		}
	}
?>
	<tr>
		<td class="cat" colspan="7" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
	</table>
<?

}

if ($mode == 'extensions')
{
?>
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr>
	  <th align="center" colspan="4"><?php echo $user->lang['MANAGE_EXTENSIONS']; ?></th>
	</tr>
	<tr>
		<td class="spacer" colspan="4" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	<tr> 
	  <th>&nbsp;<?php echo $user->lang['COMMENT']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['EXTENSION']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['EXTENSION_GROUP']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['ADD_EXTENSION']; ?>&nbsp;</th>
	</tr>
	<tr>
	  <td class="row1" align="center" valign="middle"><input type="text" size="30" maxlength="100" name="add_extension_explain" class="post" value="<?php echo $add_extension_explain; ?>" /></td>
	  <td class="row2" align="center" valign="middle"><input type="text" size="20" maxlength="100" name="add_extension" class="post" value="<?php echo $add_extension; ?>" /></td>
	  <td class="row1" align="center" valign="middle"><?php echo (($submit) ? group_select('add_group_select', $add_extension_group) : group_select('add_group_select')) ?></td>
	  <td class="row2" align="center" valign="middle"><input type="checkbox" name="add_extension_check" /></td>
	</tr>
	<tr align="right">
		<td class="cat" colspan="4"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" /></td>
	</tr>
	<tr> 
	  <th>&nbsp;<?php echo $user->lang['COMMENT']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['EXTENSION']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['EXTENSION_GROUP']; ?>&nbsp;</th>
	  <th>&nbsp;<?php echo $user->lang['DELETE']; ?>&nbsp;</th>
	</tr>

<?
	$sql = 'SELECT * 
		FROM ' . EXTENSIONS_TABLE . ' 
		ORDER BY group_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
?>
	<tr> 
	  <input type="hidden" name="extension_change_list[]" value="<?php echo $row['extension_id']; ?>" />
	  <td class="row1" align="center" valign="middle"><input type="text" size="30" maxlength="100" name="extension_explain_list[]" class="post" value="<?php echo $row['comment']; ?>" /></td>
	  <td class="row2" align="center" valign="middle"><b class="gen"><?php echo $row['extension']; ?></b></td>
	  <td class="row1" align="center" valign="middle"><?php echo group_select('group_select[]', $row['group_id']); ?></td>
	  <td class="row2" align="center" valign="middle"><input type="checkbox" name="extension_id_list[]" value="<?php echo $row['extension_id']; ?>" /></td>
	</tr>
<?
	}
?>
	<tr>
		<td class="cat" colspan="4" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
	</table>
<?
}
?>

</form>

<br clear="all" />

<?php

adm_page_footer();

// Test Settings
function test_upload(&$error, $upload_dir, $create_directory = false)
{
	global $user;

	// Does the target directory exist, is it a directory and writeable.
	if ($create_directory)
	{
		if (!file_exists($upload_dir))
		{
			@mkdir($upload_dir, 0755);
			@chmod($upload_dir, 0777);
		}
	}
		
	if (!file_exists($upload_dir))
	{
		$error[] = sprintf($user->lang['DIRECTORY_DOES_NOT_EXIST'], $upload_dir);
	}
	
	if (!count($error) && !is_dir($upload_dir))
	{
		$error[] = sprintf($user->lang['DIRECTORY_IS_NOT_A_DIR'], $upload_dir);
	}
	
	if (!count($error))
	{
		if (!($fp = @fopen($upload_dir . '/0_000000.000', 'w')))
		{
			$error[] = sprintf($user->lang['DIRECTORY_NOT_WRITEABLE'], $new['upload_dir']);
		}
		else
		{
			@fclose($fp);
			@unlink($upload_dir . '/0_000000.000');
		}
	}
}

// Generate select form
function size_select($select_name, $size_compare)
{
	global $user;

	$size_types_text = array($user->lang['BYTES'], $user->lang['KB'], $user->lang['MB']);
	$size_types = array('b', 'kb', 'mb');

	$select_field = '<select name="' . $select_name . '">';

	for ($i = 0; $i < count($size_types_text); $i++)
	{
		$selected = ($size_compare == $size_types[$i]) ? ' selected="selected"' : '';
		$select_field .= '<option value="' . $size_types[$i] . '"' . $selected . '>' . $size_types_text[$i] . '</option>';
	}
	
	$select_field .= '</select>';

	return ($select_field);
}

// Build Select for category items
function category_select($select_name, $group_id = -1)
{
	global $db, $user;

	$types = array(
		NONE_CAT => $user->lang['NONE'],
		IMAGE_CAT => $user->lang['CAT_IMAGES'],
		WM_CAT => $user->lang['CAT_WM_FILES'],
		RM_CAT => $user->lang['CAT_RM_FILES']
	);
	
	if ($group_id != -1)
	{
		$sql = 'SELECT cat_id
			FROM ' . EXTENSION_GROUPS_TABLE . '
			WHERE group_id = ' . intval($group_id);
		$result = $db->sql_query($sql);
		
		$cat_type = (!($row = $db->sql_fetchrow($result))) ? NONE_CAT : $row['cat_id'];

		$db->sql_freeresult($result);
	}
	else
	{
		$cat_type = NONE_CAT;
	}
	
	$group_select = '<select name="' . $select_name . '">';

	foreach ($types as $type => $mode)
	{
		$selected = ($type == $cat_type) ? ' selected="selected"' : '';
		$group_select .= '<option value="' . $type . '"' . $selected . '>' . $mode . '</option>';
	}

	$group_select .= '</select>';

	return($group_select);
}

// Extension group select
function group_select($select_name, $default_group = -1)
{
	global $db, $user;
		
	$group_select = '<select name="' . $select_name . '">';

	$sql = 'SELECT group_id, group_name
		FROM ' . EXTENSION_GROUPS_TABLE . '
		ORDER BY group_name';
	$result = $db->sql_query($sql);

	$group_name = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$group_name[] = $row;
	}
	$db->sql_freeresult($result);

	$row['group_id'] = 0;
	$row['group_name'] = $user->lang['NOT_ASSIGNED'];
	$group_name[] = $row;
	
	for ($i = 0; $i < count($group_name); $i++)
	{
		if ($default_group == -1)
		{
			$selected = ($i == 0) ? ' selected="selected"' : '';
		}
		else
		{
			$selected = ($group_name[$i]['group_id'] == $default_group) ? ' selected="selected"' : '';
		}

		$group_select .= '<option value="' . $group_name[$i]['group_id'] . '"' . $selected . '>' . $group_name[$i]['group_name'] . '</option>';
	}

	$group_select .= '</select>';

	return $group_select;
}

// Build select for download modes
function download_select($select_name, $group_id = -1)
{
	global $db, $user;
		
	$types = array(
		INLINE_LINK => $user->lang['MODE_INLINE'],
		PHYSICAL_LINK => $user->lang['MODE_PHYSICAL']
	);
	
	if ($group_id != -1)
	{
		$sql = "SELECT download_mode
			FROM " . EXTENSION_GROUPS_TABLE . "
			WHERE group_id = " . intval($group_id);
		$result = $db->sql_query($sql);
		
		$download_mode = (!($row = $db->sql_fetchrow($result))) ? INLINE_LINK : $row['download_mode'];

		$db->sql_freeresult($result);
	}
	else
	{
		$download_mode = INLINE_LINK;
	}

	$group_select = '<select name="' . $select_name . '">';

	foreach ($types as $type => $mode)
	{
		$selected = ($type == $download_mode) ? ' selected="selected"' : '';
		$group_select .= '<option value="' . $type . '"' . $selected . '>' . $mode . '</option>';
	}

	$group_select .= '</select>';

	return($group_select);
}

// Get supported Image types
function get_supported_image_types()
{
	$types = array();

	if (@extension_loaded('gd'))
	{
		if (@function_exists('imagegif'))
		{
			$types[] = '1';
		}
		if (@function_exists('imagejpeg'))
		{
			$types[] = '2';
		}
		if (@function_exists('imagepng'))
		{
			$types[] = '3';
		}
    }

	return ($types);
}

?>