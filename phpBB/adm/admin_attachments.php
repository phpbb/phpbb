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
	$module['POST']['ATTACHMENTS'] = ($auth->acl_get('a_attach')) ? "$filename$SID&amp;mode=ext_groups" : '';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

if (!$auth->acl_get('a_attach'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

$mode = request_var('mode', '');
$submit = (isset($_POST['submit'])) ? true : false;

$error = $notify = array();

// Adjust the Upload Directory. Relative or absolute, this is the question here.
$upload_dir = ($config['upload_dir'][0] == '/' || ($config['upload_dir'][0] != '/' && $config['upload_dir'][1] == ':')) ? $config['upload_dir'] : $phpbb_root_path . $config['upload_dir'];

switch ($mode)
{
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
		phpbb_unlink($upload_dir . '/' . $delete);
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
		$message_parser = new parse_message(0);

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
				upload_file($row['post_id'], $row['topic_id'], $row['forum_id'], $upload_dir, $upload_list[$row['post_id']]);
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
	<table cellspacing="1" cellpadding="0" border="0" align="center" width="99%">
	<tr>
		<td align="right"><?php echo $s_select_mode; ?> &nbsp; <input type="submit" name="select_mode" class="btnlite" value="<?php echo $user->lang['SELECT']; ?>" /></td>
	</tr>
	</table>
<?php

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
			$row['max_filesize'] = intval($config['max_filesize']);
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

			$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>';
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
			$sql = 'SELECT comment, extension 
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
		<td class="cat" colspan="7" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
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
		<th align="center" colspan="3"><?php echo $user->lang['MANAGE_EXTENSIONS']; ?></th>
	</tr>
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
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
		<td class="cat" colspan="3" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
	</table>
<?
}

// Orphan Attachments
if ($mode == 'orphan')
{
	$attach_filelist = array();

	$dir = @opendir($upload_dir);
	while ($file = @readdir($dir))
	{
		if (is_file($upload_dir . '/' . $file) && filesize($upload_dir . '/' . $file) && $file != '.htaccess')
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
		<th align="center" colspan="5">Orphan Attachments</th>
	</tr>
	<tr>
		<td class="spacer" colspan="5" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
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
		$filesize = @filesize($upload_dir . '/' . $file);
		$size_lang = ($filesize >= 1048576) ? $user->lang['MB'] : ( ($filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );
		$filesize = ($filesize >= 1048576) ? round((round($filesize / 1048576 * 100) / 100), 2) : (($filesize >= 1024) ? round((round($filesize / 1024 * 100) / 100), 2) : $filesize);
?>
<tr>
	<td class="<?php echo $row_class; ?>"><a href="<?php echo $upload_dir . '/' . $file; ?>" class="gen" target="file"><?php echo $file; ?></a></td>
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

?>