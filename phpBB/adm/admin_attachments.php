<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_attachments.php
// STARTED   : Sun Apr 20, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['GENERAL']['ATTACHMENT_SETTINGS'] = ($auth->acl_get('a_attach')) ? "$filename$SID&amp;mode=attach" : '';
	$module['POST']['ATTACHMENTS'] = ($auth->acl_get('a_attach')) ? "$filename$SID&amp;mode=ext_groups" : '';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

$user->add_lang(array('posting', 'viewtopic'));

if (!$auth->acl_get('a_attach'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

$mode = request_var('mode', '');
$submit = (isset($_POST['submit'])) ? true : false;

$error = $notify = array();

switch ($mode)
{
	case 'attach':
		$l_title = 'ATTACHMENT_SETTINGS';
		break;

	case 'extensions':
		$l_title = 'MANAGE_EXTENSIONS';
		break;

	case 'ext_groups':
		$l_title = 'EXTENSION_GROUPS_TITLE';
		break;
	
	case 'orphan':
		$l_title = 'ORPHAN_ATTACHMENTS';
		break;

	default:
		trigger_error('NO_MODE');
}

if ($mode == 'attach')
{

	$config_sizes = array('max_filesize' => 'size', 'attachment_quota' => 'quota_size', 'max_filesize_pm' => 'pm_size');
	foreach ($config_sizes as $cfg_key => $var)
	{
		$$var = request_var($var, '');
	}

	// Pull all config data
	$sql = 'SELECT *
		FROM ' . CONFIG_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$config_name = $row['config_name'];
		$config_value = $row['config_value'];

		$default_config[$config_name] = $config_value;
		$new[$config_name] = request_var($config_name, $default_config[$config_name]);

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

			if ($submit && $config_name == $cfg_key)
			{
				$old = $new[$config_name];
				$new[$config_name] = ($$var == 'kb') ? round($new[$config_name] * 1024) : (($$var == 'mb') ? round($new[$config_name] * 1048576) : $new[$config_name]);
			}
		} 

		if ($submit)
		{
			// Update Extension Group Filesizes
			if ($config_name == 'max_filesize')
			{
				$old_size = (int) $default_config[$config_name];
				$new_size = (int) $new[$config_name];

				if ($old_size != $new_size)
				{
					// check for similar value of old_size in Extension Groups. If so, update these values.
					$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . "
						SET max_filesize = $new_size
						WHERE max_filesize = $old_size";
					$db->sql_query($sql);
				}
			}

			set_config($config_name, $new[$config_name]);
	
			if (in_array($config_name, array('max_filesize', 'attachment_quota', 'max_filesize_pm')))
			{
				$new[$config_name] = $old;
			}
		}
	}

	perform_site_list();

	if ($submit)
	{
		add_log('admin', 'LOG_' . strtoupper($mode) . '_CONFIG');

		// Check Settings
		test_upload($error, $new['upload_dir'], false);

		if (!sizeof($error))
		{
			trigger_error($user->lang['CONFIG_UPDATED']);
		}
	}
}

adm_page_header($user->lang[$l_title]);


if ($submit && $mode == 'extensions')
{
	// Change Extensions ?
	$extension_change_list	= (isset($_POST['extension_change_list'])) ? array_map('intval', $_POST['extension_change_list']) : array();
	$group_select_list		= (isset($_POST['group_select'])) ? array_map('intval', $_POST['group_select']) : array();

	// Generate correct Change List
	$extensions = array();

	for ($i = 0; $i < count($extension_change_list); $i++)
	{
		$extensions[$extension_change_list[$i]]['group_id'] = $group_select_list[$i];
	}

	$sql = 'SELECT *
		FROM ' . EXTENSIONS_TABLE . '
		ORDER BY extension_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['group_id'] != $extensions[$row['extension_id']]['group_id'])
		{
			$sql = 'UPDATE ' . EXTENSIONS_TABLE . ' 
				SET group_id = ' . (int) $extensions[$row['extension_id']]['group_id'] . '
				WHERE extension_id = ' . $row['extension_id'];
			$db->sql_query($sql);	
			add_log('admin', 'LOG_ATTACH_EXT_UPDATE', $row['extension']);
		}
	}
	$db->sql_freeresult($result);

	// Delete Extension ?
	$extension_id_list = (isset($_POST['extension_id_list'])) ? array_map('intval', $_POST['extension_id_list']) : array();

	if (sizeof($extension_id_list))
	{
		$sql = 'SELECT extension 
			FROM ' . EXTENSIONS_TABLE . '
			WHERE extension_id IN (' . implode(', ', $extension_id_list) . ')';
		$result = $db->sql_query($sql);
		
		$extension_list = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$extension_list .= ($extension_list == '') ? $row['extension'] : ', ' . $row['extension'];
		}
		$db->sql_freeresult($result);

		$sql = 'DELETE 
			FROM ' . EXTENSIONS_TABLE . '
			WHERE extension_id IN (' . implode(', ', $extension_id_list) . ')';
		$db->sql_query($sql);

		add_log('admin', 'LOG_ATTACH_EXT_DEL', $extension_list);
	}
		
	// Add Extension ?
	$add_extension			= strtolower(request_var('add_extension', ''));
	$add_extension_group	= request_var('add_group_select', 0);
	$add					= (isset($_POST['add_extension_check'])) ? true : false;

	if ($add_extension != '' && $add)
	{
		if (!sizeof($error))
		{
			$sql = 'SELECT extension_id
				FROM ' . EXTENSIONS_TABLE . "
				WHERE extension = '" . $db->sql_escape($add_extension) . "'";
			$result = $db->sql_query($sql);
			
			if ($row = $db->sql_fetchrow($result))
			{
				$error[] = sprintf($user->lang['EXTENSION_EXIST'], $add_extension);
			}
			$db->sql_freeresult($result);

			if (!sizeof($error))
			{
				$sql_ary = array(
					'group_id'	=>	$add_extension_group,
					'extension'	=>	$add_extension
				);
				
				$db->sql_query('INSERT INTO ' . EXTENSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				add_log('admin', 'LOG_ATTACH_EXT_ADD', $add_extension);
			}
		}
	}

	if (!sizeof($error))
	{
		$notify[] = $user->lang['EXTENSIONS_UPDATED'];
	}
}

if ($submit && $mode == 'ext_groups')
{
	// Change Extension Groups ?
	$group_change_list		= (isset($_POST['group_change_list'])) ? array_map('intval', $_POST['group_change_list']) : array();
	$extension_group_list	= request_var('extension_group_list', '');
	$group_allowed_list		= (isset($_POST['allowed_list'])) ? array_flip(array_map('intval', $_POST['allowed_list'])) : array();
	$download_mode_list		= (isset($_POST['download_mode_list'])) ? array_map('intval', $_POST['download_mode_list']) : array();
	$category_list			= (isset($_POST['category_list'])) ? array_map('intval', $_POST['category_list']) : array();
	$upload_icon_list		= request_var('upload_icon_list', '');
	$filesize_list			= (isset($_POST['max_filesize_list'])) ? array_map('intval', $_POST['max_filesize_list']) : array();
	$size_select_list		= request_var('size_select_list', 'b');

	foreach ($group_change_list as $group_id => $var)
	{
		$filesize_list[$group_id] = ($size_select_list[$group_id] == 'kb') ? round($filesize_list[$group_id] * 1024) : (($size_select_list[$group_id] == 'mb') ? round($filesize_list[$group_id] * 1048576) : $filesize_list[$group_id]);

		$group_sql = array(
			'group_name'	=> $extension_group_list[$group_id],
			'cat_id'		=> $category_list[$group_id],
			'allow_group'	=> (isset($group_allowed_list[$group_id])) ? 1 : 0,
			'download_mode'	=> $download_mode_list[$group_id],
			'upload_icon'	=> ($upload_icon_list[$group_id] == 'no_image') ? '' : $upload_icon_list[$group_id],
			'max_filesize'	=> $filesize_list[$group_id]
		);
		
		$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $group_sql) . " 
			WHERE group_id = $group_id";
		$db->sql_query($sql);
	}
	
	// Delete Extension Groups
	$group_delete_list = (isset($_POST['group_delete_list'])) ? array_keys(array_map('intval', $_POST['group_delete_list'])) : array();

	if (sizeof($group_delete_list))
	{
		$sql = 'SELECT group_name 
			FROM ' . EXTENSION_GROUPS_TABLE . '
			WHERE group_id IN (' . implode(', ', $group_delete_list) . ')';
		$result = $db->sql_query($sql);

		$l_group_list = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$l_group_list[] = $row['group_name'];
		}
		$db->sql_freeresult($result);
		
		$sql = 'DELETE 
			FROM ' . EXTENSION_GROUPS_TABLE . ' 
			WHERE group_id IN (' . implode(', ', $group_delete_list) . ')';
		$db->sql_query($sql);

		// Set corresponding Extensions to a pending Group
		$sql ='"UPDATE ' . EXTENSIONS_TABLE . '
			SET group_id = 0
			WHERE group_id IN (' . implode(', ', $group_delete_list) . ')';
		$db->sql_query($sql);
	
		add_log('admin', 'LOG_ATTACH_EXTGROUP_DEL', implode(', ', $l_group_list));
	}
		
	// Add Extensions Group ?
	$extension_group	= request_var('add_extension_group', '');
	$download_mode		= request_var('add_download_mode', INLINE_LINK);
	$cat_id				= request_var('add_category', 0);
	$upload_icon		= request_var('add_upload_icon', '');
	$filesize			= request_var('add_max_filesize', 0);
	$size_select		= request_var('add_size_select', 'b');
	$is_allowed			= (isset($_POST['add_allowed'])) ? true : false;
	$add				= (isset($_POST['add_extension_group_check'])) ? true : false;

	if ($extension_group != '' && $add)
	{
		// check Extension Group
		$sql = 'SELECT group_name 
			FROM ' . EXTENSION_GROUPS_TABLE . "
			WHERE group_name = '" . $db->sql_escape($extension_group) . "'";
		$result = $db->sql_query_limit($sql, 1);
			
		if ($row = $db->sql_fetchrow($result))
		{
			if ($row['group_name'] == $extension_group)
			{
				$error[] = sprintf($user->lang['EXTENSION_GROUP_EXIST'], $extension_group);
			}
		}
		$db->sql_freeresult($result);
		
		if (!sizeof($error))
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
		$extensions[$extension]['display_cat']	= (int) $row['cat_id'];
		$extensions[$extension]['download_mode']= (int) $row['download_mode'];
		$extensions[$extension]['upload_icon']	= (string) $row['upload_icon'];
		$extensions[$extension]['max_filesize']	= (int) $row['max_filesize'];
	}
	$db->sql_freeresult($result);

	$cache->destroy('extensions');
	$cache->put('extensions', $extensions);

	if (!sizeof($error))
	{
		$notify[] = $user->lang['EXTENSION_GROUPS_UPDATED'];
	}
}

?>

<h1><?php echo $user->lang[$l_title]; ?></h1>

<p><?php echo $user->lang[$l_title . '_EXPLAIN']; ?></p>

<?php

if ($submit && $mode == 'orphan')
{
	$delete_files = array_keys(request_var('delete', ''));
	$add_files = array_keys(request_var('add', ''));
	$post_ids = request_var('post_id', 0);

	foreach ($delete_files as $delete)
	{
		phpbb_unlink($config['upload_dir'] . '/' . $delete);
		phpbb_unlink($config['upload_dir'] . '/thumb_' . $delete);
	}

	if (sizeof($delete_files))
	{
		add_log('admin', sprintf($user->lang['LOG_ATTACH_ORPHAN_DEL'], implode(', ', $delete_files)));
		$notify[] = sprintf($user->lang['LOG_ATTACH_ORPHAN_DEL'], implode(', ', $delete_files));
	}

	$upload_list = array();
	foreach ($add_files as $file)
	{
		if (!in_array($file, $delete_files) && $post_ids[$file])
		{
			$upload_list[$post_ids[$file]] = $file;
		}
	}
	unset($add_files);

	if (sizeof($upload_list))
	{
?>
	<h2><?php echo $user->lang['UPLOADING_FILES']; ?></h2>
<?php
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		$message_parser = new parse_message();

		$sql = 'SELECT forum_id, forum_name
			FROM ' . FORUMS_TABLE;
		$result = $db->sql_query($sql);
		
		$forum_names = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_names[$row['forum_id']] = $row['forum_name'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT forum_id, topic_id, post_id 
			FROM ' . POSTS_TABLE . '
			WHERE post_id IN (' . implode(', ', array_keys($upload_list)) . ')';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			echo sprintf($user->lang['UPLOADING_FILE_TO'], $upload_list[$row['post_id']], $row['post_id']) . '<br />';
			if (!$auth->acl_gets('f_attach', 'u_attach', $row['forum_id']))
			{
				echo '<span style="color:red">' . sprintf($user->lang['UPLOAD_DENIED_FORUM'], $forum_names[$row['forum_id']]) . '</span><br /><br />';
			}
			else
			{
				upload_file($row['post_id'], $row['topic_id'], $row['forum_id'], $config['upload_dir'], $upload_list[$row['post_id']]);
			}
		}
	}
}

 
if (sizeof($error))
{
?>

<h2 style="color:red"><?php echo $user->lang['WARNING']; ?></h2>

<p><?php echo implode('<br />', $error); ?></p>

<?php
}

if (sizeof($notify))
{
?>

<h2 style="color:green"><?php echo $user->lang['NOTIFY']; ?></h2>

<p><?php echo implode('<br />', $notify); ?></p>

<?php
}

$modes = array('extensions', 'ext_groups', 'orphan');

$s_select_mode = '<select name="mode">';
foreach ($modes as $_mode)
{
	$s_select_mode .= '<option value="' . $_mode . '"' . (($mode == $_mode) ? ' selected="selected"' : '') . '>' . $user->lang['ATTACH_' . strtoupper($_mode) . '_URL'] . '</option>';
}
$s_select_mode .= '</select>';
?>
<form name="attachments" method="post" action="admin_attachments.<?php echo "$phpEx$SID&amp;mode=$mode"; ?>">
<?php
if ($mode != 'attach')
{
?>
	<table cellspacing="1" cellpadding="0" border="0" align="center" width="99%">
	<tr>
		<td align="right"><?php echo $s_select_mode; ?> &nbsp; <input type="submit" name="select_mode" class="btnlite" value="<?php echo $user->lang['SELECT']; ?>" /></td>
	</tr>
	</table>
<?php
}

// Attachment Settings
if ($mode == 'attach')
{
	$action = request_var('action', '');

	if ($action == 'imgmagick')
	{
		$new['img_imagick'] = search_imagemagick();
	}

	$select_size_mode = size_select('size', $size);
	$select_quota_size_mode = size_select('quota_size', $quota_size);
	$select_pm_size_mode = size_select('pm_size', $pm_size);

	$display_order_yes = ($new['display_order']) ? 'checked="checked"' : '';
	$display_order_no = (!$new['display_order']) ? 'checked="checked"' : '';

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
	$db->sql_freeresult($result);

	$display_inlined_yes = ($new['img_display_inlined']) ? 'checked="checked"' : '';
	$display_inlined_no = (!$new['img_display_inlined']) ? 'checked="checked"' : '';

	$create_thumbnail_yes = ($new['img_create_thumbnail']) ? 'checked="checked"' : '';
	$create_thumbnail_no = (!$new['img_create_thumbnail']) ? 'checked="checked"' : '';

	$secure_downloads_yes = ($new['secure_downloads']) ? 'checked="checked"' : '';
	$secure_downloads_no = (!$new['secure_downloads']) ? 'checked="checked"' : '';

	$secure_allow_deny_yes = ($new['secure_allow_deny']) ? 'checked="checked"' : '';
	$secure_allow_deny_no = (!$new['secure_allow_deny']) ? 'checked="checked"' : '';
	
	$secure_allow_empty_referer_yes = ($new['secure_allow_empty_referer']) ? 'checked="checked"' : '';
	$secure_allow_empty_referer_no = (!$new['secure_allow_empty_referer']) ? 'checked="checked"' : '';

?>

	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr>
		<th colspan="2"><?php echo $user->lang[$l_title]; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['UPLOAD_DIR']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['UPLOAD_DIR_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="upload_dir" class="post" value="<?php echo $new['upload_dir'] ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DISPLAY_ORDER']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DISPLAY_ORDER_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="display_order" value="0" <?php echo $display_order_no; ?> /> <?php echo $user->lang['DESCENDING']; ?> &nbsp; <input type="radio" name="display_order" value="1" <?php echo $display_order_yes; ?> /> <?php echo $user->lang['ASCENDING']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ATTACH_QUOTA']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ATTACH_QUOTA_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="attachment_quota" class="post" value="<?php echo $new['attachment_quota']; ?>" /> <?php echo $select_quota_size_mode; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ATTACH_MAX_FILESIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ATTACH_MAX_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="max_filesize" class="post" value="<?php echo $new['max_filesize']; ?>" /> <?php echo $select_size_mode; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ATTACH_MAX_PM_FILESIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ATTACH_MAX_PM_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="max_filesize_pm" class="post" value="<?php echo $new['max_filesize_pm']; ?>" /> <?php echo $select_pm_size_mode; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_ATTACHMENTS'] ?>: </b></td>
		<td class="row2"><input type="text" size="3" maxlength="3" name="max_attachments" class="post" value="<?php echo $new['max_attachments']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_ATTACHMENTS_PM'] ?>: </b></td>
		<td class="row2"><input type="text" size="3" maxlength="3" name="max_attachments_pm" class="post" value="<?php echo $new['max_attachments_pm']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SECURE_DOWNLOADS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SECURE_DOWNLOADS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="secure_downloads" value="1" <?php echo $secure_downloads_yes ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="secure_downloads" value="0" <?php echo $secure_downloads_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SECURE_ALLOW_DENY']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SECURE_ALLOW_DENY_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="secure_allow_deny" value="1" <?php echo $secure_allow_deny_yes ?> /> <?php echo $user->lang['ORDER_ALLOW_DENY']; ?>&nbsp;&nbsp;<input type="radio" name="secure_allow_deny" value="0" <?php echo $secure_allow_deny_no ?> /> <?php echo $user->lang['ORDER_DENY_ALLOW']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SECURE_EMPTY_REFERER']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SECURE_EMPTY_REFERER_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="secure_allow_empty_referer" value="1" <?php echo $secure_allow_empty_referer_yes ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="secure_allow_empty_referer" value="0" <?php echo $secure_allow_empty_referer_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
	  <th align="center" colspan="2"><?php echo $user->lang['SETTINGS_CAT_IMAGES']; ?></th>
	</tr>
	<tr>
	  <td class="row3" colspan="2" align="center"><?php echo $user->lang['ASSIGNED_GROUP']; ?>: <?php echo ( (count($s_assigned_groups[IMAGE_CAT])) ? implode(', ', $s_assigned_groups[IMAGE_CAT]) : $user->lang['NONE']); ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DISPLAY_INLINED']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DISPLAY_INLINED_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="img_display_inlined" value="1" <?php echo $display_inlined_yes ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="img_display_inlined" value="0" <?php echo $display_inlined_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php
	
	// Check Thumbnail Support
	if (!$new['img_imagick'] && !count(get_supported_image_types()))
	{
		$new['img_create_thumbnail'] = '0';
	}
	else
	{

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['CREATE_THUMBNAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['CREATE_THUMBNAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="img_create_thumbnail" value="1" <?php echo $create_thumbnail_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="img_create_thumbnail" value="0" <?php echo $create_thumbnail_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MIN_THUMB_FILESIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MIN_THUMB_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="7" maxlength="15" name="img_min_thumb_filesize" value="<?php echo $new['img_min_thumb_filesize']; ?>" class="post" /> <?php echo $user->lang['BYTES']; ?></td>
	</tr>
<?php

	}

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['IMAGICK_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['IMAGICK_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="200" name="img_imagick" value="<?php echo $new['img_imagick']; ?>" class="post" />&nbsp;&nbsp;<span class="gensmall">[ <a href="<?php echo "admin_attachments.$phpEx$SID&amp;mode=$mode&amp;action=imgmagick"; ?>"><?php echo $user->lang['SEARCH_IMAGICK']; ?></a> ]</span></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_IMAGE_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_IMAGE_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_max_width" value="<?php echo $new['img_max_width']; ?>" class="post" /> px X <input type="text" size="3" maxlength="4" name="img_max_height" value="<?php echo $new['img_max_height']; ?>" class="post" /> px</td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['IMAGE_LINK_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['IMAGE_LINK_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_link_width" value="<?php echo $new['img_link_width']; ?>" class="post" /> px X <input type="text" size="3" maxlength="4" name="img_link_height" value="<?php echo $new['img_link_height']; ?>" class="post" /> px</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
	</table>
<?php
	// Secure Download Options - Same procedure as with banning
	if ($new['secure_downloads'])
	{
		$allow_deny = ($new['secure_allow_deny']) ? 'ALLOWED' : 'DISALLOWED';
		
		$sql = 'SELECT *
			FROM ' . SITELIST_TABLE;
		$result = $db->sql_query($sql);

		$defined_ips = '';
		$ips = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$value = ($row['site_ip']) ? $row['site_ip'] : $row['site_hostname'];
			if ($value)
			{
				$defined_ips .=  '<option' . (($row['ip_exclude']) ? ' class="sep"' : '') . ' value="' . $row['site_id'] . '">' . $value . '</option>';
					$ips[$row['site_id']] = $value;
			}
		}
		$db->sql_freeresult($result);
?>
	<br />
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
		<tr>
			<th colspan="2"><?php echo $user->lang['DEFINE_' . $allow_deny . '_IPS']; ?></th>
		</tr>
		<tr>
			<td colspan="2" class="row3"><?php echo $user->lang['DOWNLOAD_ADD_IPS_EXPLAIN']; ?></td>
		<tr>
			<td class="row1" width="45%"><b><?php echo $user->lang['IP_HOSTNAME']; ?>: </b></td>
			<td class="row2"><textarea cols="40" rows="3" name="ips"></textarea></td>
		</tr>
		<tr>
			<td class="row1" width="45%"><b><?php echo $user->lang['EXCLUDE_FROM_' . $allow_deny . '_IP']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['EXCLUDE_ENTERED_IP']; ?></span></td>
			<td class="row2"><input type="radio" name="ipexclude" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="ipexclude" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
		</tr>
		<tr>
			<td class="cat" colspan="2" align="center"> <input type="submit" name="securesubmit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" />&nbsp; </td>
		</tr>
		<tr>
			<th colspan="2"><?php echo $user->lang['REMOVE_' . $allow_deny . '_IPS']; ?></th>
		</tr>
<?php

		if ($defined_ips != '')
		{

?>
		<tr>
			<td colspan="2" class="row3"><?php echo $user->lang['DOWNLOAD_REMOVE_IPS_EXPLAIN']; ?></td>
		<tr>
		<tr>
			<td class="row1" width="45%"><?php echo $user->lang['IP_HOSTNAME']; ?>: <br /></td>
			<td class="row2"> <select name="unip[]" multiple="multiple" size="10"><?php echo $defined_ips; ?></select></td>
		</tr>
		<tr>
			<td class="cat" colspan="2" align="center"><input type="submit" name="unsecuresubmit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
		</tr>
<?php

		}
		else
		{

?>
		<tr>
			<td class="row1" colspan="2" align="center"><?php echo $user->lang['NO_IPS_DEFINED']; ?></td>
		</tr>
<?php
		}
	}
?>
	</table>
<?php
}

// Extension Groups
if ($mode == 'ext_groups')
{
	$img_path = $config['upload_icons_path'];

	$imglist = filelist($phpbb_root_path . $img_path);
	$imglist = array_values($imglist);
	$imglist = $imglist[0];

	$filename_list = '';
	foreach ($imglist as $key => $img)
	{
		$filename_list .= '<option value="' . $img . '">' . htmlspecialchars($img) . '</option>';
	}
	
	$size = request_var('size', 0);
	
	if (!$size && !$submit)
	{
		$max_add_filesize = intval($config['max_filesize']);
		$size = ($max_add_filesize >= 1048576) ? 'mb' : (($max_add_filesize >= 1024) ? 'kb' : 'b');
	}

	$max_add_filesize = ($max_add_filesize >= 1048576) ? round($max_add_filesize / 1048576 * 100) / 100 : (($max_add_filesize >= 1024) ? round($max_add_filesize / 1024 * 100) / 100 : $max_add_filesize);

	$viewgroup = request_var('g', 0);
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
		<td class="row1" align="center" valign="middle">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td align="center"><select name="add_upload_icon" onChange="update_add_image(this.options[selectedIndex].value);"><option value="no_image" selected="selected"><?php echo $user->lang['NO_IMAGE']; ?></option><?php echo $filename_list ?></select></td>
				<td width="50" align="center" valign="middle">&nbsp;<img src="<?php echo $phpbb_root_path . 'images/spacer.gif' ?>"  name="add_image" border="0" alt="" title="" />&nbsp;</td>
			</tr>
			</table>
		</td>
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
			$row['max_filesize'] = (int) $config['max_filesize'];
		}

		$size_format = ($row['max_filesize'] >= 1048576) ? 'mb' : (($row['max_filesize'] >= 1024) ? 'kb' : 'b');

		$row['max_filesize'] = ($row['max_filesize'] >= 1048576) ? round($row['max_filesize'] / 1048576 * 100) / 100 : (($row['max_filesize'] >= 1024) ? round($row['max_filesize'] / 1024 * 100) / 100 : $row['max_filesize']);

		$s_allowed = ($row['allow_group'] == 1) ? 'checked="checked"' : '';
		$edit_img = ($row['upload_icon'] != '') ? $row['upload_icon'] : '';

		$filename_list = '';
		$no_image_select = false;
		foreach ($imglist as $key => $img)
		{
			if ($edit_img == '')
			{
				$no_image_select = true;
				$selected = '';
			}
			else
			{
				$selected = ($edit_img == $img) ? ' selected="selected"' : '';
			}

			$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . htmlspecialchars($img) . '</option>';
		}
?>
	<tr> 
		<input type="hidden" name="group_change_list[<?php echo $row['group_id']; ?>]" value="1" />
		<td class="row1" align="center" valign="middle">
			<table width="100%" align="center" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="row1" align="center" valign="middle" width="10%" wrap="nowrap"><b><a href="<?php echo (($viewgroup == $row['group_id']) ? "admin_attachments.$phpEx$SID&mode=ext_groups" : "admin_attachments.$phpEx$SID&mode=ext_groups&g=" . $row['group_id']); ?>" class="gen"><?php echo (($viewgroup == $row['group_id']) ? '-' : '+'); ?></a></span></b></td>
				<td class="row1" align="left" valign="middle"><input type="text" size="20" maxlength="100" name="extension_group_list[<?php echo $row['group_id']; ?>]" class="post" value="<?php echo $row['group_name']; ?>" /></td>
			</tr>
			</table>
		</td>
		<td class="row2" align="center" valign="middle"><?php echo category_select('category_list[' . $row['group_id'] . ']', $row['group_id']); ?></td>
		<td class="row1" align="center" valign="middle"><input type="checkbox" name="allowed_list[<?php echo $row['group_id']; ?>]" value="<?php echo $row['group_id']; ?>" <?php echo $s_allowed; ?> /></td>
		<td class="row2" align="center" valign="middle"><?php echo download_select('download_mode_list[' . $row['group_id'] . ']', $row['group_id']); ?></td>
		<td class="row1" align="center" valign="middle">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td align="center"><select name="upload_icon_list[<?php echo $row['group_id']; ?>]" onChange="update_image(this.options[selectedIndex].value, <?php echo $row['group_id']; ?>);"><option value="no_image"<?php echo (($no_image_select) ? ' selected="selected"' : ''); ?>><?php echo $user->lang['NO_IMAGE']; ?></option><?php echo $filename_list ?></select></td>
				<td width="50" align="center" valign="middle">&nbsp;<img src="<?php echo (($no_image_select) ? $phpbb_root_path . 'images/spacer.gif' : $phpbb_root_path . $img_path . '/' . $edit_img) ?>" name="image_<?php echo $row['group_id']; ?>" border="0" alt="" title="" />&nbsp;</td>
			</tr>
			</table>
		</td>
		<td class="row2" align="center" valign="middle"><input type="text" size="3" maxlength="15" name="max_filesize_list[<?php echo $row['group_id']; ?>]" class="post" value="<?php echo $row['max_filesize']; ?>" /> <?php echo size_select('size_select_list[' . $row['group_id'] . ']', $size_format); ?></td>
		<td class="row2" align="center" valign="middle"><input type="checkbox" name="group_delete_list[<?php echo $row['group_id']; ?>]" value="1" /></td>
	</tr>
<?php

		if ($viewgroup && $viewgroup == $row['group_id'])
		{
			$sql = 'SELECT extension 
				FROM ' . EXTENSIONS_TABLE . "
				WHERE group_id = $viewgroup";
			$e_result = $db->sql_query($sql);

			$assigned_extensions = array();
			while ($e_row = $db->sql_fetchrow($e_result))
			{
				$assigned_extensions[] = strtoupper($e_row['extension']);
			}
			$db->sql_freeresult($e_result);

?>
			<tr> 
				<td class="row2" colspan="7">&nbsp;<?php echo $user->lang['ASSIGNED_EXTENSIONS'] . ' &#187; ' . ((sizeof($assigned_extensions)) ? implode(', ', $assigned_extensions) : $user->lang['NONE']); ?></td>
			</tr>
<?php
		}
	}
?>
	<tr>
		<td class="cat" colspan="7" align="right"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
	</table>
<?

}

// Extensions
if ($mode == 'extensions')
{
?>
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr> 
		<th>&nbsp;<?php echo $user->lang['EXTENSION']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $user->lang['EXTENSION_GROUP']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $user->lang['ADD_EXTENSION']; ?>&nbsp;</th>
	</tr>
	<tr>
		<td class="row1" align="center" valign="middle"><input type="text" size="20" maxlength="100" name="add_extension" class="post" value="<?php echo $add_extension; ?>" /></td>
		<td class="row2" align="center" valign="middle"><?php echo (($submit) ? group_select('add_group_select', $add_extension_group) : group_select('add_group_select')) ?></td>
		<td class="row1" align="center" valign="middle"><input type="checkbox" name="add_extension_check" /></td>
	</tr>
	<tr align="right">
		<td class="cat" colspan="3"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" /></td>
	</tr>
	<tr> 
		<th>&nbsp;<?php echo $user->lang['EXTENSION']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $user->lang['EXTENSION_GROUP']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $user->lang['DELETE']; ?>&nbsp;</th>
	</tr>

<?php
	$sql = 'SELECT * 
		FROM ' . EXTENSIONS_TABLE . ' 
		ORDER BY group_id';
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$old_group_id = $row['group_id'];
		do
		{
			$current_group_id = $row['group_id'];
			if ($old_group_id != $current_group_id)
			{
?>
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
<?php
				$old_group_id = $current_group_id;
			}
?>
	<tr> 
		<input type="hidden" name="extension_change_list[]" value="<?php echo $row['extension_id']; ?>" />
		<td class="row1" align="center" valign="middle"><b class="gen"><?php echo $row['extension']; ?></b></td>
		<td class="row2" align="center" valign="middle"><?php echo group_select('group_select[]', $row['group_id']); ?></td>
		<td class="row1" align="center" valign="middle"><input type="checkbox" name="extension_id_list[]" value="<?php echo $row['extension_id']; ?>" /></td>
	</tr>
<?
		}
		while ($row = $db->sql_fetchrow($result));
	}
?>
	<tr>
		<td class="cat" colspan="3" align="right"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
	</table>
<?
}

// Orphan Attachments
if ($mode == 'orphan')
{
	$attach_filelist = array();

	$dir = @opendir($config['upload_dir']);
	while ($file = @readdir($dir))
	{
		if (is_file($config['upload_dir'] . '/' . $file) && filesize($config['upload_dir'] . '/' . $file) && $file{0} != '.' && $file != 'index.htm' && !preg_match('#^thumb\_#', $file))
		{
			$attach_filelist[$file] = $file;
		}
	}
	@closedir($dir);

?>

<script language="Javascript" type="text/javascript">
<!--
function marklist(match, name, status)
{
	len = eval('document.' + match + '.length');
	object = eval('document.' + match);
	for (i = 0; i < len; i++)
	{
		result = eval('object.elements[' + i + '].name.search(/' + name + '.+/)');
		if (result != -1)
			object.elements[i].checked = status;
	}
}
//-->
</script>

<?php
	$sql = 'SELECT physical_filename 
		FROM ' . ATTACHMENTS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		unset($attach_filelist[$row['physical_filename']]);
	}
	$db->sql_freeresult($result);

?>

	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr> 
		<th>&nbsp;<?php echo $user->lang['FILENAME']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $user->lang['FILESIZE']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $user->lang['ATTACH_POST_ID']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $user->lang['ATTACH_TO_POST']; ?>&nbsp;</th>
		<th>&nbsp;<?php echo $user->lang['DELETE']; ?>&nbsp;</th>
	</tr>

<?php
	$i = 0;
	foreach ($attach_filelist as $file)
	{
		$row_class = (++$i % 2 == 0) ? 'row2' : 'row1';
		$filesize = @filesize($config['upload_dir'] . '/' . $file);
		$size_lang = ($filesize >= 1048576) ? $user->lang['MB'] : ( ($filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );
		$filesize = ($filesize >= 1048576) ? round((round($filesize / 1048576 * 100) / 100), 2) : (($filesize >= 1024) ? round((round($filesize / 1024 * 100) / 100), 2) : $filesize);
?>
		<tr>
			<td class="<?php echo $row_class; ?>"><a href="<?php echo $config['upload_dir'] . '/' . $file; ?>" class="gen" target="file"><?php echo $file; ?></a></td>
			<td class="<?php echo $row_class; ?>"><?php echo $filesize . ' ' . $size_lang; ?></td>
			<td class="<?php echo $row_class; ?>"><b class="gen">ID: </b><input type="text" name="post_id[<?php echo $file; ?>]" class="post" size="7" maxlength="10" value="<?php echo (!empty($post_ids[$file])) ? $post_ids[$file] : ''; ?>" /></td>
			<td class="<?php echo $row_class; ?>"><input type="checkbox" name="add[<?php echo $file; ?>]" /></td>
			<td class="<?php echo $row_class; ?>"><input type="checkbox" name="delete[<?php echo $file; ?>]" /></td>
		</tr>
<?php
	}

?>
	<tr>
		<td class="cat" colspan="3"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
		<td class="cat" align="left"><b><span class="gensmall"><a href="javascript:marklist('attachments', 'add', true);" class="gensmall"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('attachments', 'add', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></span></b></td>
		<td class="cat" align="left"><b><span class="gensmall"><a href="javascript:marklist('attachments', 'delete', true);" class="gensmall"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('attachments', 'delete', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></span></b></td>
	</tr>
</table>
<?php
}

?>

</form>

<br clear="all" />

<?php

adm_page_footer();

// Build Select for category items
function category_select($select_name, $group_id = FALSE)
{
	global $db, $user;

	$types = array(
		NONE_CAT => $user->lang['NONE'],
		IMAGE_CAT => $user->lang['CAT_IMAGES'],
		WM_CAT => $user->lang['CAT_WM_FILES'],
		RM_CAT => $user->lang['CAT_RM_FILES']
	);
	
	if ($group_id)
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
function group_select($select_name, $default_group = '-1')
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
		if ($default_group == '-1')
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
function download_select($select_name, $group_id = FALSE)
{
	global $db, $user;
		
	$types = array(
		INLINE_LINK => $user->lang['MODE_INLINE'],
		PHYSICAL_LINK => $user->lang['MODE_PHYSICAL']
	);
	
	if ($group_id)
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

// Upload already uploaded file... huh? are you kidding?
function upload_file($post_id, $topic_id, $forum_id, $upload_dir, $filename)
{
	global $message_parser, $db, $user;

	$message_parser->attachment_data = array();

	$message_parser->filename_data['filecomment'] = '';
	$message_parser->filename_data['filename'] = $upload_dir . '/' . $filename;

	$filedata = upload_attachment($filename, true, $upload_dir . '/' . $filename);

	if ($filedata['post_attach'] && !sizeof($filedata['error']))
	{
		$message_parser->attachment_data = array(
			'post_id'			=> $post_id,
			'poster_id'			=> $user->data['user_id'],
			'topic_id'			=> $topic_id,
			'physical_filename'	=> $filedata['destination_filename'],
			'real_filename'		=> $filedata['filename'],
			'comment'			=> $message_parser->filename_data['filecomment'],
			'extension'			=> $filedata['extension'],
			'mimetype'			=> $filedata['mimetype'],
			'filesize'			=> $filedata['filesize'],
			'filetime'			=> $filedata['filetime'],
			'thumbnail'			=> $filedata['thumbnail']
		);

		$message_parser->filename_data['filecomment'] = '';
		$filedata['post_attach'] = FALSE;

		// Submit Attachment
		$attach_sql = $message_parser->attachment_data;

		$db->sql_transaction();

		$sql = 'INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $attach_sql);
		$db->sql_query($sql);

		$sql = 'UPDATE ' . POSTS_TABLE . "
			SET post_attachment = 1
			WHERE post_id = $post_id";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . "
			SET topic_attachment = 1
			WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		$db->sql_transaction('commit');

		add_log('admin', sprintf($user->lang['LOG_ATTACH_FILEUPLOAD'], $post_id, $filename));
		echo '<span style="color:green">' . $user->lang['SUCCESSFULLY_UPLOADED'] . '</span><br /><br />';
	}
	else if (sizeof($filedata['error']))
	{
		echo '<span style="color:red">' . sprintf($user->lang['ADMIN_UPLOAD_ERROR'], implode("<br />\t", $filedata['error'])) . '</span><br /><br />';
	}
}

// Search Imagick
function search_imagemagick()
{
	$imagick = '';
	
	$exe = ((defined('PHP_OS')) && (preg_match('#win#i', PHP_OS))) ? '.exe' : '';

	if (empty($_ENV['MAGICK_HOME']))
	{
		$locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', '/usr/bin/imagemagick/');

		foreach ($locations as $location)
		{
			if (file_exists($location . 'convert' . $exe) && @is_readable($location . 'convert' . $exe) && @filesize($location . 'convert' . $exe) > 80000)
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

	return $imagick;
}

// Test Settings
function test_upload(&$error, $upload_dir, $create_directory = false)
{
	global $user, $phpbb_root_path;

	// Adjust the Upload Directory. Relative or absolute, this is the question here.
	$real_upload_dir = $upload_dir;
	$upload_dir = ($upload_dir{0} == '/' || ($upload_dir{0} != '/' && $upload_dir{1} == ':')) ? $upload_dir : $phpbb_root_path . $upload_dir;

	// Does the target directory exist, is it a directory and writeable.
	if ($create_directory)
	{
		if (!file_exists($upload_dir))
		{
			@mkdir($upload_dir, 0777);
			@chmod($upload_dir, 0777);
		}
	}

	if (!file_exists($upload_dir))
	{
		$error[] = sprintf($user->lang['NO_UPLOAD_DIR'], $real_upload_dir);
		return;
	}
	
	if (!is_dir($upload_dir))
	{
		$error[] = sprintf($user->lang['UPLOAD_NOT_DIR'], $real_upload_dir);
		return;
	}
	
	if (!is_writable($upload_dir))
	{
		$error[] = sprintf($user->lang['NO_WRITE_UPLOAD'], $real_upload_dir);
		return;
	}
}

function perform_site_list()
{
	global $db, $user;

	if (isset($_REQUEST['securesubmit']))
	{
		// Grab the list of entries
		$ips = request_var('ips', '');
		$ip_list = array_unique(explode("\n", $ips));
		$ip_list_log = implode(', ', $ip_list);

		$ip_exclude = (!empty($_POST['ipexclude'])) ? 1 : 0;

		$iplist = array();
		$hostlist = array();

		foreach ($ip_list as $item)
		{
			if (preg_match('#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})[ ]*\-[ ]*([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#', trim($item), $ip_range_explode))
			{
				// Don't ask about all this, just don't ask ... !
				$ip_1_counter = $ip_range_explode[1];
				$ip_1_end = $ip_range_explode[5];

				while ($ip_1_counter <= $ip_1_end)
				{
					$ip_2_counter = ($ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[2] : 0;
					$ip_2_end = ($ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[6];

					if($ip_2_counter == 0 && $ip_2_end == 254)
					{
						$ip_2_counter = 256;
						$ip_2_fragment = 256;

						$iplist[] = "'$ip_1_counter.*'";
					}

					while ($ip_2_counter <= $ip_2_end)
					{
						$ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
						$ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

						if ($ip_3_counter == 0 && $ip_3_end == 254)
						{
							$ip_3_counter = 256;
							$ip_3_fragment = 256;

							$iplist[] = "'$ip_1_counter.$ip_2_counter.*'";
						}

						while ($ip_3_counter <= $ip_3_end)
						{
							$ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
							$ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

							if ($ip_4_counter == 0 && $ip_4_end == 254)
							{
								$ip_4_counter = 256;
								$ip_4_fragment = 256;

								$iplist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.*'";
							}

							while ($ip_4_counter <= $ip_4_end)
							{
								$iplist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter'";
								$ip_4_counter++;
							}
							$ip_3_counter++;
						}
						$ip_2_counter++;
					}
					$ip_1_counter++;
				}
			}
			else if (preg_match('#^([0-9]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})$#', trim($item)) || preg_match('#^[a-f0-9:]+\*?$#i', trim($item)))
			{
				$iplist[] = "'" . trim($item) . "'";
			}
			else if (preg_match('#^([\w\-_]\.?){2,}$#is', trim($item)))
			{
				$hostlist[] = "'" . trim($item) . "'";
			}
			else if (preg_match("#^([a-z0-9\-\*\._/]+?)$#is", trim($item)))
			{
				$hostlist[] = "'" . trim($item) . "'";
			}
		}

		$sql = 'SELECT site_ip, site_hostname
			FROM ' . SITELIST_TABLE . "
			WHERE ip_exclude = $ip_exclude";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$iplist_tmp = array();
			$hostlist_tmp = array();
			do
			{
				if ($row['site_ip'])
				{
					$iplist_tmp[] = "'" . $row['site_ip'] . "'";
				}
				else if ($row['site_hostname'])
				{
					$hostlist_tmp[] = "'" . $row['site_hostname'] . "'";
				}
				break;
			}
			while ($row = $db->sql_fetchrow($result));

			$iplist = array_unique(array_diff($iplist, $iplist_tmp));
			$hostlist = array_unique(array_diff($hostlist, $hostlist_tmp));
			unset($iplist_tmp);
			unset($hostlist_tmp);
		}

		if (sizeof($iplist))
		{
			foreach ($iplist as $ip_entry)
			{
				$sql = 'INSERT INTO ' . SITELIST_TABLE . " (site_ip, ip_exclude)
					VALUES ($ip_entry, $ip_exclude)";
				$db->sql_query($sql);
			}
		}

		if (sizeof($hostlist))
		{
			foreach ($hostlist as $host_entry)
			{
				$sql = 'INSERT INTO ' . SITELIST_TABLE . " (site_hostname, ip_exclude)
					VALUES ($host_entry, $ip_exclude)";
				$db->sql_query($sql);
			}
		}
		
		if (!empty($ip_list_log))
		{
			// Update log
			$log_entry = ($ip_exclude) ? 'LOG_DOWNLOAD_EXCLUDE_IP' : 'LOG_DOWNLOAD_IP';
			add_log('admin', $log_entry, $ip_list_log);
		}

		trigger_error($user->lang['SECURE_DOWNLOAD_UPDATE_SUCESSFUL']);
	}
	else if (isset($_POST['unsecuresubmit']))
	{
		$unip_sql = implode(', ', array_map('intval', $_POST['unip']));

		if ($unip_sql != '')
		{
			$l_unip_list = '';
		
			// Grab details of ips for logging information later
			$sql = 'SELECT site_ip, site_hostname
				FROM ' . SITELIST_TABLE . "
				WHERE site_id IN ($unip_sql)";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$l_unip_list .= (($l_unip_list != '') ? ', ' : '') . (($row['site_ip']) ? $row['site_ip'] : $row['site_hostname']);
			}

			$sql = 'DELETE FROM ' . SITELIST_TABLE . "
				WHERE site_id IN ($unip_sql)";
			$db->sql_query($sql);

			add_log('admin', 'LOG_DOWNLOAD_REMOVE_IP', $l_unip_list);
		}

		trigger_error($user->lang['SECURE_DOWNLOAD_UPDATE_SUCESSFUL']);
	}
}

?>