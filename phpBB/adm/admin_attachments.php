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
*/
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
		test_upload($error, $new['upload_path'], false);

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
	
	$cache->destroy('extensions');
}


if ($submit && $mode == 'ext_groups')
{
	$action = request_var('action', '');
	$group_id = request_var('g', 0);
	
	if ($action != 'add' && $action != 'edit')
	{
		trigger_error('WRONG_MODE');
	}

	if (!$group_id && $action == 'edit')
	{
		trigger_error('NO_EXT_GROUP_SPECIFIED');
	}

	if ($group_id)
	{
		$sql = 'SELECT * FROM ' . EXTENSION_GROUPS_TABLE . "
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);
		$ext_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	}
	else
	{
		$ext_row = array();
	}

	$group_name = request_var('group_name', '');
	$new_group_name = ($action == 'add') ? $group_name : (($ext_row['group_name'] != $group_name) ? $group_name : '');

	if (!$group_name)
	{
		$error[] = $user->lang['NO_EXT_GROUP_NAME'];
	}

	// Check New Group Name
	if ($new_group_name)
	{
		$sql = 'SELECT group_id 
			FROM ' . EXTENSION_GROUPS_TABLE . "
			WHERE LOWER(group_name) = '" . $db->sql_escape(strtolower($new_group_name)) . "'";
		$result = $db->sql_query($sql);
		if ($db->sql_fetchrow($result))
		{
			$error[] = sprintf($user->lang['EXTENSION_GROUP_EXIST'], $new_group_name);
		}
		$db->sql_freeresult($result);
	}

	if (!sizeof($error))
	{
		// Ok, build the update/insert array
		$upload_icon	= request_var('upload_icon', 'no_image');
		$size_select	= request_var('size_select', 'b');
		$forum_select	= request_var('forum_select', false);
		$allowed_forums	= isset($_POST['allowed_forums']) ? array_map('intval', array_values($_POST['allowed_forums'])) : array();
		$allow_in_pm	= isset($_POST['allow_in_pm']) ? true : false;
		$max_filesize	= request_var('max_filesize', 0);
		$max_filesize	= ($size_select == 'kb') ? round($max_filesize * 1024) : (($size_select == 'mb') ? round($max_filesize * 1048576) : $max_filesize);

		if ($max_filesize == $config['max_filesize'])
		{
			$max_filesize = 0;
		}	

		if (!sizeof($allowed_forums))
		{
			$forum_select = false;
		}

		$group_ary = array(
			'group_name'	=> $group_name,
			'cat_id'		=> request_var('special_category', ATTACHMENT_CATEGORY_NONE),
			'allow_group'	=> (isset($_POST['allow_group'])) ? 1 : 0,
			'download_mode'	=> request_var('download_mode', INLINE_LINK),
			'upload_icon'	=> ($upload_icon == 'no_image') ? '' : $upload_icon,
			'max_filesize'	=> $max_filesize,
			'allowed_forums'=> ($forum_select) ? serialize($allowed_forums) : '',
			'allow_in_pm'	=> ($allow_in_pm) ? 1 : 0
		);

		$sql = ($action == 'add') ? 'INSERT INTO ' . EXTENSION_GROUPS_TABLE . ' ' : 'UPDATE ' . EXTENSION_GROUPS_TABLE . ' SET ';
		$sql .= $db->sql_build_array((($action == 'add') ? 'INSERT' : 'UPDATE'), $group_ary);
		$sql .= ($action == 'edit') ? " WHERE group_id = $group_id" : '';

		$db->sql_query($sql);
		
		if ($action == 'add')
		{
			$group_id = $db->sql_nextid();
		}

		add_log('admin', 'LOG_ATTACH_EXTGROUP_' . strtoupper($action), $group_name);
	}

	$extension_list = isset($_REQUEST['extensions']) ? array_map('intval', array_values($_REQUEST['extensions'])) : array();

	if ($action == 'edit' && sizeof($extension_list))
	{
		$sql = 'UPDATE ' . EXTENSIONS_TABLE . "
			SET group_id = 0
			WHERE group_id = $group_id";
		$db->sql_query($sql);
	}

	if (sizeof($extension_list))
	{
		$sql = 'UPDATE ' . EXTENSIONS_TABLE . " 
			SET group_id = $group_id
			WHERE extension_id IN (" . implode(', ', $extension_list) . ")";
		$db->sql_query($sql);
	}

	rewrite_extensions();

	if (!sizeof($error))
	{
		$notify[] = $user->lang['SUCCESS_EXTENSION_GROUP_' . strtoupper($action)];
	}
}

?>

<h1><?php echo $user->lang[$l_title]; ?></h1>

<p><?php echo $user->lang[$l_title . '_EXPLAIN']; ?></p>

<?php

if ($submit && $mode == 'orphan')
{
	$delete_files = (isset($_POST['delete'])) ? array_keys(request_var('delete', array('' => 0))) : array();
	$add_files = (isset($_POST['add'])) ? array_keys(request_var('add', array('' => 0))) : array();
	$post_ids = request_var('post_id', 0);

	foreach ($delete_files as $delete)
	{
		phpbb_unlink($delete);
		phpbb_unlink($delete, 'thumbnail');
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
				upload_file($row['post_id'], $row['topic_id'], $row['forum_id'], $config['upload_path'], $upload_list[$row['post_id']]);
			}
		}
		unset($message_parser);
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

	// We strip eventually manual added convert program, we only want the patch
	$new['img_imagick'] = str_replace(array('convert', '.exe'), array('', ''), $new['img_imagick']);

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
		<td class="row2"><input type="text" size="25" maxlength="100" name="upload_path" class="post" value="<?php echo $new['upload_path'] ?>" /></td>
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
	  <td class="row3" colspan="2" align="center"><?php echo $user->lang['ASSIGNED_GROUP']; ?>: <?php echo ((sizeof($s_assigned_groups[ATTACHMENT_CATEGORY_IMAGE])) ? implode(', ', $s_assigned_groups[ATTACHMENT_CATEGORY_IMAGE]) : $user->lang['NONE']); ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DISPLAY_INLINED']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DISPLAY_INLINED_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="img_display_inlined" value="1" <?php echo $display_inlined_yes ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="img_display_inlined" value="0" <?php echo $display_inlined_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php
	
	$supported_types = get_supported_image_types();

	// Check Thumbnail Support
	if (!$new['img_imagick'] && (!isset($supported_types['format']) || !sizeof($supported_types['format'])))
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

	if (!$new['secure_downloads'])
	{
?>
	<br />
	<table cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
		<tr>
			<td class="row3" align="center"><?php echo $user->lang['SECURE_DOWNLOAD_NOTICE']; ?></td>
		</tr>
	</table>

<?php
	}
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
?>
	</table>
<?php
}

// Extension Groups
if ($mode == 'ext_groups')
{
	$cat_lang = array(
		ATTACHMENT_CATEGORY_NONE	=> $user->lang['NONE'],
		ATTACHMENT_CATEGORY_IMAGE	=> $user->lang['CAT_IMAGES'],
		ATTACHMENT_CATEGORY_WM		=> $user->lang['CAT_WM_FILES'],
		ATTACHMENT_CATEGORY_RM		=> $user->lang['CAT_RM_FILES']
	);


	$action = request_var('action', 'show');
	$group_id = request_var('g', 0);
	$action = (isset($_POST['add'])) ? 'add' : $action;
	$action = (($action == 'add' || $action == 'edit') && $submit && !sizeof($error)) ? 'show' : $action;

	if (isset($_POST['select_mode']))
	{
		$action = 'show';
	}

	if ($action == 'delete')
	{
		$confirm	= (isset($_POST['confirm'])) ? true : false;
		$cancel		= (isset($_POST['cancel'])) ? true : false;
		
		if (!$cancel && !$confirm)
		{
			adm_page_confirm($user->lang['CONFIRM'], $user->lang['CONFIRM_OPERATION']);
		}
		else if ($confirm && !$cancel)
		{
			$sql = 'SELECT group_name 
				FROM ' . EXTENSION_GROUPS_TABLE . "
				WHERE group_id = $group_id";
			$result = $db->sql_query($sql);
			$group_name = $db->sql_fetchfield('group_name', 0, $result);
			$db->sql_freeresult($result);

			$sql = 'DELETE 
				FROM ' . EXTENSION_GROUPS_TABLE . " 
				WHERE group_id = $group_id";
			$db->sql_query($sql);

			// Set corresponding Extensions to a pending Group
			$sql = 'UPDATE ' . EXTENSIONS_TABLE . "
				SET group_id = 0
				WHERE group_id = $group_id";
			$db->sql_query($sql);
	
			add_log('admin', 'LOG_ATTACH_EXTGROUP_DEL', $group_name);

			rewrite_extensions();

			trigger_error('EXTENSION_GROUP_DELETED');
		}
		else
		{
			$action = 'show';
		}
	}

	switch ($action)
	{
		case 'edit':
		
			if (!$group_id)
			{
				trigger_error('NO_EXTENSION_GROUP');
			}

			$sql = 'SELECT * FROM ' . EXTENSION_GROUPS_TABLE . "
				WHERE group_id = $group_id";
			$result = $db->sql_query($sql);
			extract($db->sql_fetchrow($result));
			$db->sql_freeresult($result);

			$forum_ids = (!$allowed_forums) ? array() : unserialize(trim($allowed_forums));

		case 'add':
			
			if ($action == 'add')
			{
				$group_name = request_var('group_name', '');
				$cat_id = 0;
				$allow_group = 1;
				$allow_in_pm = 1;
				$download_mode = 1;
				$upload_icon = '';
				$max_filesize = 0;
				$forum_ids = array();
			}

			$extensions = array();

			$sql = 'SELECT * FROM ' . EXTENSIONS_TABLE . "
				WHERE group_id = $group_id OR group_id = 0
				ORDER BY extension";
			$result = $db->sql_query($sql);
			$extensions = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);

			$img_path = $config['upload_icons_path'];

			$imglist = filelist($phpbb_root_path . $img_path);
			$imglist = array_values($imglist);
			$imglist = $imglist[0];

			$filename_list = '';
			foreach ($imglist as $key => $img)
			{
				$filename_list .= '<option value="' . $img . '">' . htmlspecialchars($img) . '</option>';
			}

			if ($max_filesize == 0)
			{
				$max_filesize = (int) $config['max_filesize'];
			}

			$size_format = ($max_filesize >= 1048576) ? 'mb' : (($max_filesize >= 1024) ? 'kb' : 'b');

			$max_filesize = ($max_filesize >= 1048576) ? round($max_filesize / 1048576 * 100) / 100 : (($max_filesize >= 1024) ? round($max_filesize / 1024 * 100) / 100 : $max_filesize);

			$s_allowed = ($allow_group) ? ' checked="checked"' : '';
			$s_in_pm_allowed = ($allow_in_pm) ? ' checked="checked"' : '';

			$filename_list = '';
			$no_image_select = false;
			foreach ($imglist as $key => $img)
			{
				if (!$upload_icon)
				{
					$no_image_select = true;
					$selected = '';
				}
				else
				{
					$selected = ($upload_icon == $img) ? ' selected="selected"' : '';
				}

				$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . htmlspecialchars($img) . '</option>';
			}

			// Show Edit Screen
?>
			<script language="javascript" type="text/javascript" defer="defer">
			<!--

			function update_image(newimage)
			{
				if (newimage == 'no_image')
				{
					document.image.src = "<?php echo $phpbb_root_path; ?>images/spacer.gif";
				}
				else
				{
					document.image.src = "<?php echo $phpbb_root_path . $img_path; ?>/" + newimage;
				}
			}

			function show_extensions(elem)
			{
				var str = '';

				for (i = 0; i < elem.length; i++)
				{
					var element = elem.options[i];
					if (element.selected)
					{
						if (str)
						{
							str = str + ', ';
						}

						str = str + element.innerHTML;
					}
				}

				if (document.all)
				{
					document.all.ext.innerText = str;
				}
				else if (document.getElementById('ext').textContent)
				{
					document.getElementById('ext').textContent = str;
				}
				else if (document.getElementById('ext').firstChild.nodeValue)
				{
					document.getElementById('ext').firstChild.nodeValue = str;
				}
			}

			//-->
			</script>
		
			<input type="hidden" name="action" value="<?php echo $action; ?>" />
			<input type="hidden" name="g" value="<?php echo $group_id; ?>" />

			<table class="bg" width="99%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2"><?php echo $user->lang[strtoupper($action) . '_EXTENSION_GROUP']; ?></th>
			</tr>
			<tr>
				<td class="row1" width="35%"><b><?php echo $user->lang['GROUP_NAME']; ?>: </b></td>
				<td class="row2"><input type="text" size="20" maxlength="100" name="group_name" class="post" value="<?php echo $group_name; ?>" /></td>
			</tr>
			<tr>
				<td class="row1" width="35%"><b><?php echo $user->lang['SPECIAL_CATEGORY']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SPECIAL_CATEGORY_EXPLAIN']; ?></span></td>
				<td class="row2"><?php echo category_select('special_category', $group_id); ?></td>
			</tr>
			<tr>
				<td class="row1" width="35%"><b><?php echo $user->lang['ALLOWED']; ?>: </b></td>
				<td class="row2"><input type="checkbox" name="allow_group" value="<?php echo $group_id; ?>"<?php echo $s_allowed; ?> /></td>
			</tr>
			<tr>
				<td class="row1" width="35%"><b><?php echo $user->lang['ALLOW_IN_PM']; ?>: </b></td>
				<td class="row2"><input type="checkbox" name="allow_in_pm" value="1"<?php echo $s_in_pm_allowed; ?> /></td>
			</tr>
			<tr>
				<td class="row1" width="35%"><b><?php echo $user->lang['DOWNLOAD_MODE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DOWNLOAD_MODE_EXPLAIN']; ?></span></td>
				<td class="row2"><?php echo download_select('download_mode', $group_id); ?></td>
			</tr>
			<tr>
				<td class="row1" width="35%"><b><?php echo $user->lang['UPLOAD_ICON']; ?>: </b></td>
				<td class="row2" align="left">
					<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td align="center"><select name="upload_icon" onChange="update_image(this.options[selectedIndex].value);"><option value="no_image"<?php echo (($no_image_select) ? ' selected="selected"' : ''); ?>><?php echo $user->lang['NO_IMAGE']; ?></option><?php echo $filename_list ?></select></td>
						<td width="50" align="center" valign="middle">&nbsp;<img src="<?php echo (($no_image_select) ? $phpbb_root_path . 'images/spacer.gif' : $phpbb_root_path . $img_path . '/' . $upload_icon) ?>" name="image" border="0" alt="" title="" />&nbsp;</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="row1" width="35%"><b><?php echo $user->lang['MAX_EXTGROUP_FILESIZE']; ?>: </b></td>
				<td class="row2"><input type="text" size="3" maxlength="15" name="max_filesize" class="post" value="<?php echo $max_filesize; ?>" /> <?php echo size_select('size_select', $size_format); ?></td>
			</tr>
			<tr>
				<td class="row1" width="35%" valign="top"><table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td colspan="2"><b><?php echo $user->lang['ASSIGNED_EXTENSIONS']; ?>: </b></td></tr>
					<tr><td class="row1" width="20"> &#187; &nbsp;</td>
					<td class="row1"><div id="ext" style="margin:0px; width:200px">&nbsp;<?php
							$i = 0;
							foreach ($extensions as $num => $row)
							{
								if ($row['group_id'] == $group_id && $group_id)
								{
									echo ($i) ? ', ' . $row['extension'] : $row['extension'];
									$i++;
								}
							}
					?></div></td></tr>
					<tr><td class="row1">&nbsp;</td><td class="row1"><br />[ <a href="admin_attachments.<?php echo $phpEx.$SID . '&amp;mode=extensions'; ?>"><?php echo $user->lang['GO_TO_EXTENSIONS']; ?></a> ]</td></tr></table>
				</td>
				<td class="row2"><select name="extensions[]" onChange="show_extensions(this);" multiple="true" size="8" style="width:100px">
<?php
					foreach ($extensions as $row)
					{
						echo '<option' . ((!$row['group_id']) ? ' class="blue"' : '') . ' value="' . $row['extension_id'] . '"' . (($row['group_id'] == $group_id && $group_id) ? ' selected="selected"' : '') . '>' . $row['extension'] . '</option>';
					}
?>
				</select></td>
			</tr>
			<tr>
				<td class="row1" valign="top"><b><?php echo $user->lang['ALLOWED_FORUMS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOWED_FORUMS_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="radio" name="forum_select" value="0"<?php echo (!sizeof($forum_ids)) ? ' checked="checked"' : ''; ?> />&nbsp;<?php echo $user->lang['ALLOW_ALL_FORUMS']; ?>&nbsp;&nbsp;<input type="radio" name="forum_select" value="1"<?php echo (sizeof($forum_ids)) ? ' checked="checked"' : ''; ?> />&nbsp;<?php echo $user->lang['ALLOW_SELECTED_FORUMS']; ?><br /><br />
				<select name="allowed_forums[]" multiple="true" size="8">
<?php

				$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
					FROM ' . FORUMS_TABLE . '
					ORDER BY left_id ASC';
				$result = $db->sql_query($sql);

				$right = $cat_right = $padding_inc = 0;
				$padding = $forum_list = $holding = '';
				$padding_store = array('0' => '');
				while ($row = $db->sql_fetchrow($result))
				{
					if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
					{
						// Non-postable forum with no subforums, don't display
						continue;
					}

					if (!$auth->acl_get('f_list', $row['forum_id']))
					{
						// if the user does not have permissions to list this forum skip
						continue;
					}

					if ($row['left_id'] < $right)
					{
						$padding .= '&nbsp; &nbsp;';
						$padding_store[$row['parent_id']] = $padding;
					}
					else if ($row['left_id'] > $right + 1)
					{
						$padding = $padding_store[$row['parent_id']];
					}

					$right = $row['right_id'];

					$selected = (in_array($row['forum_id'], $forum_ids)) ? ' selected="selected"' : '';

					if ($row['left_id'] > $cat_right)
					{
						$holding = '';
					}

					if ($row['right_id'] - $row['left_id'] > 1)
					{
						$cat_right = max($cat_right, $row['right_id']);

						$holding .= '<option value="' . $row['forum_id'] . '"' . (($row['forum_type'] == FORUM_POST) ? ' class="blue"' : '') . $selected . '>' . $padding . $row['forum_name'] . '</option>';
					}
					else
					{
						echo $holding . '<option value="' . $row['forum_id'] . '"' . (($row['forum_type'] == FORUM_POST) ? ' class="blue"' : '') . $selected . '>' . $padding . $row['forum_name'] . '</option>';
						$holding = '';
					}
				}
				$db->sql_freeresult($result);
				unset($padding_store);
?>
				</select></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="right"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
			</tr>
			</table>
<?php
		
			break;

		case 'deactivate':
		case 'activate':
	
			if (!$group_id)
			{
				trigger_error('NO_EXTENSION_GROUP');
			}

			$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
				SET allow_group = ' . (($action == 'activate') ? '1' : '0') . "
				WHERE group_id = $group_id";

			$db->sql_query($sql);

			rewrite_extensions();

		case 'show':
	
	$sql = 'SELECT *
		FROM ' . EXTENSION_GROUPS_TABLE . '
		ORDER BY allow_group DESC, group_name';
	$result = $db->sql_query($sql);

?>

	<table class="bg" width="99%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="60%"><?php echo $user->lang['EXTENSION_GROUP']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['SPECIAL_CATEGORY']; ?></th>
		<th colspan="3"><?php echo $user->lang['OPTIONS']; ?></th>
	</tr>

<?php

	$row_class = 'row2';

	while ($row = $db->sql_fetchrow($result))
	{
		$row_class = ($row_class == 'row1') ? 'row2' : 'row1';
		
		if ($row['allow_group'] == 0 && $act_deact == 'deactivate')
		{
?>

		<tr>
			<td class="spacer" colspan="5" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
		</tr>

<?php

		}
		
		$act_deact = ($row['allow_group']) ? 'deactivate' : 'activate';
?>

	<tr>
		<td class="<?php echo $row_class; ?>"><a href="admin_attachments.<?php echo "$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;g={$row['group_id']}"; ?>"><?php echo $row['group_name']; ?></a></td>
		<td class="<?php echo $row_class; ?>"><b><?php echo $cat_lang[$row['cat_id']]; ?></b></td>
		<td class="<?php echo $row_class; ?>"><a href="admin_attachments.<?php echo "$phpEx$SID&amp;mode=$mode&amp;action=$act_deact&amp;g={$row['group_id']}"; ?>"><?php echo $user->lang[strtoupper($act_deact)]; ?></a></td>
		<td class="<?php echo $row_class; ?>"><a href="admin_attachments.<?php echo "$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;g={$row['group_id']}"; ?>"><?php echo $user->lang['EDIT']; ?></a></td>
		<td class="<?php echo $row_class; ?>"><a href="admin_attachments.<?php echo "$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;g={$row['group_id']}"; ?>"><?php echo $user->lang['DELETE']; ?></a></td>
	</tr>

<?php
	}
	$db->sql_freeresult($result);

?>
	<td class="cat" colspan="5" align="right"><?php echo $user->lang['CREATE_GROUP']; ?>: <input class="post" type="text" name="group_name" maxlength="30" /> <input class="btnmain" type="submit" name="add" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</table>

<?php

			break;
	}

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
		<td class="row1" align="center" valign="middle"><input type="text" size="20" maxlength="100" name="add_extension" class="post" value="<?php echo (isset($add_extension)) ? $add_extension : ''; ?>" /></td>
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
		ORDER BY group_id, extension';
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

	$dir = @opendir($phpbb_root_path . $config['upload_path']);
	while ($file = @readdir($dir))
	{
		if (is_file($phpbb_root_path . $config['upload_path'] . '/' . $file) && filesize($phpbb_root_path . $config['upload_path'] . '/' . $file) && $file{0} != '.' && $file != 'index.htm' && !preg_match('#^thumb\_#', $file))
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
		$filesize = @filesize($phpbb_root_path . $config['upload_path'] . '/' . $file);
		$size_lang = ($filesize >= 1048576) ? $user->lang['MB'] : ( ($filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );
		$filesize = ($filesize >= 1048576) ? round((round($filesize / 1048576 * 100) / 100), 2) : (($filesize >= 1024) ? round((round($filesize / 1024 * 100) / 100), 2) : $filesize);
?>
		<tr>
			<td class="<?php echo $row_class; ?>"><a href="<?php echo $phpbb_root_path . $config['upload_path'] . '/' . $file; ?>" class="gen" target="file"><?php echo $file; ?></a></td>
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
function category_select($select_name, $group_id = false)
{
	global $db, $user;

	$types = array(
		ATTACHMENT_CATEGORY_NONE	=> $user->lang['NONE'],
		ATTACHMENT_CATEGORY_IMAGE	=> $user->lang['CAT_IMAGES'],
		ATTACHMENT_CATEGORY_WM		=> $user->lang['CAT_WM_FILES'],
		ATTACHMENT_CATEGORY_RM		=> $user->lang['CAT_RM_FILES']
	);
	
	if ($group_id)
	{
		$sql = 'SELECT cat_id
			FROM ' . EXTENSION_GROUPS_TABLE . '
			WHERE group_id = ' . (int) $group_id;
		$result = $db->sql_query($sql);
		
		$cat_type = (!($row = $db->sql_fetchrow($result))) ? ATTACHMENT_CATEGORY_NONE : $row['cat_id'];

		$db->sql_freeresult($result);
	}
	else
	{
		$cat_type = ATTACHMENT_CATEGORY_NONE;
	}
	
	$group_select = '<select name="' . $select_name . '">';

	foreach ($types as $type => $mode)
	{
		$selected = ($type == $cat_type) ? ' selected="selected"' : '';
		$group_select .= '<option value="' . $type . '"' . $selected . '>' . $mode . '</option>';
	}

	$group_select .= '</select>';

	return $group_select;
}

// Extension group select
function group_select($select_name, $default_group = false)
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
	
	for ($i = 0; $i < sizeof($group_name); $i++)
	{
		if ($default_group === false)
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
function download_select($select_name, $group_id = false)
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
			WHERE group_id = " . (int) $group_id;
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

	return $group_select;
}

// Upload already uploaded file... huh? are you kidding?
function upload_file($post_id, $topic_id, $forum_id, $upload_dir, $filename)
{
	global $message_parser, $db, $user, $phpbb_root_path;

	$message_parser->attachment_data = array();

	$message_parser->filename_data['filecomment'] = '';
	$message_parser->filename_data['filename'] = $phpbb_root_path . $upload_dir . '/' . basename($filename);

	$filedata = upload_attachment('local', $forum_id, true, $phpbb_root_path . $upload_dir . '/' . basename($filename));

	if ($filedata['post_attach'] && !sizeof($filedata['error']))
	{
		$message_parser->attachment_data = array(
			'post_msg_id'		=> $post_id,
			'poster_id'			=> $user->data['user_id'],
			'topic_id'			=> $topic_id,
			'in_message'		=> 0,
			'physical_filename'	=> $filedata['physical_filename'],
			'real_filename'		=> $filedata['real_filename'],
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
			if (@is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000)
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

	// Does the target directory exist, is it a directory and writeable.
	if ($create_directory)
	{
		if (!file_exists($phpbb_root_path . $upload_dir))
		{
			@mkdir($phpbb_root_path . $upload_dir, 0777);
			@chmod($phpbb_root_path . $upload_dir, 0777);
		}
	}

	if (!file_exists($phpbb_root_path . $upload_dir))
	{
		$error[] = sprintf($user->lang['NO_UPLOAD_DIR'], $upload_dir);
		return;
	}
	
	if (!is_dir($phpbb_root_path . $upload_dir))
	{
		$error[] = sprintf($user->lang['UPLOAD_NOT_DIR'], $upload_dir);
		return;
	}
	
	if (!is_writable($phpbb_root_path . $upload_dir))
	{
		$error[] = sprintf($user->lang['NO_WRITE_UPLOAD'], $upload_dir);
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

					if ($ip_2_counter == 0 && $ip_2_end == 254)
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

		trigger_error($user->lang['SECURE_DOWNLOAD_UPDATE_SUCCESS']);
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

		trigger_error($user->lang['SECURE_DOWNLOAD_UPDATE_SUCCESS']);
	}
}

// Re-Write extensions cache file
function rewrite_extensions()
{
	global $db, $cache;

	$sql = 'SELECT e.extension, g.*
		FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
		WHERE e.group_id = g.group_id
			AND g.allow_group = 1';
	$result = $db->sql_query($sql);

	$extensions = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$extension = $row['extension'];

		$extensions[$extension]['display_cat']	= (int) $row['cat_id'];
		$extensions[$extension]['download_mode']= (int) $row['download_mode'];
		$extensions[$extension]['upload_icon']	= (string) $row['upload_icon'];
		$extensions[$extension]['max_filesize']	= (int) $row['max_filesize'];

		$allowed_forums = ($row['allowed_forums']) ? unserialize(trim($row['allowed_forums'])) : array();
			
		if ($row['allow_in_pm'])
		{
			$allowed_forums = array_merge($allowed_forums, array(0));
		}
			
		// Store allowed extensions forum wise
		$extensions['_allowed_'][$extension] = (!sizeof($allowed_forums)) ? 0 : $allowed_forums;
	}
	$db->sql_freeresult($result);

	$cache->destroy('extensions');
	$cache->put('extensions', $extensions);
}

?>