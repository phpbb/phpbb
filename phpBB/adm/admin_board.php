<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_board.php
// STARTED   : Thu Jul 12, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['GENERAL']['ATTACHMENT_SETTINGS'] = ($auth->acl_get('a_attach')) ? "$filename$SID&amp;mode=attach" : '';
	$module['GENERAL']['AUTH_SETTINGS'] = ($auth->acl_get('a_server')) ? "$filename$SID&amp;mode=auth" : '';
	$module['GENERAL']['AVATAR_SETTINGS'] = ($auth->acl_get('a_board')) ? "$filename$SID&amp;mode=avatar" : '';
	$module['GENERAL']['BOARD_DEFAULTS'] = ($auth->acl_get('a_defaults')) ? "$filename$SID&amp;mode=default" : '';
	$module['GENERAL']['BOARD_SETTINGS'] = ($auth->acl_get('a_board')) ? "$filename$SID&amp;mode=setting" : '';
	$module['GENERAL']['COOKIE_SETTINGS'] = ($auth->acl_get('a_cookies')) ? "$filename$SID&amp;mode=cookie" : '';
	$module['GENERAL']['EMAIL_SETTINGS'] = ($auth->acl_get('a_server')) ? "$filename$SID&amp;mode=email" : '';
	$module['GENERAL']['LOAD_SETTINGS'] = ($auth->acl_get('a_server')) ? "$filename$SID&amp;mode=load" : '';
	$module['GENERAL']['SERVER_SETTINGS'] = ($auth->acl_get('a_server')) ? "$filename$SID&amp;mode=server" : '';
	$module['USER']['KARMA_SETTINGS'] = ($auth->acl_get('a_user')) ? "$filename$SID&amp;mode=karma" : '';
	return;
}

define('IN_PHPBB', 1);
// Load default header
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Get mode
$mode	= request_var('mode', '');
$action	= request_var('action', '');
$submit = (isset($_POST['submit'])) ? true : false;

// Check permissions/set title
switch ($mode)
{
	case 'attach':
		$l_title = 'ATTACHMENT_SETTINGS';
		$which_auth = 'a_attach';
		break;
	case 'cookie':
		$l_title = 'COOKIE_SETTINGS';
		$which_auth = 'a_cookies';
		break;
	case 'default':
		$l_title = 'BOARD_DEFAULTS';
		$which_auth = 'a_defaults';
		break;
	case 'avatar':
		$l_title = 'AVATAR_SETTINGS';
		$which_auth = 'a_board';
		break;
	case 'setting':
		$l_title = 'BOARD_SETTINGS';
		$which_auth = 'a_board';
		break;
	case 'email':
		$l_title = 'EMAIL_SETTINGS';
		$which_auth = 'a_server';
		break;
	case 'server':
		$l_title = 'SERVER_SETTINGS';
		$which_auth = 'a_server';
		break;
	case 'load':
		$l_title = 'LOAD_SETTINGS';
		$which_auth = 'a_server';
		break;
	case 'auth':
		$l_title = 'AUTH_SETTINGS';
		$which_auth = 'a_server';
		break;
	case 'karma':
		$l_title = 'KARMA_SETTINGS';
		$which_auth = 'a_user';
		break;
	default:
		return;
}

// Check permissions
if (!$auth->acl_get($which_auth))
{
	trigger_error($user->lang['NO_ADMIN']);
}

$config_sizes = array('max_filesize' => 'size', 'attachment_quota' => 'quota_size', 'max_filesize_pm' => 'pm_size');
foreach ($config_sizes as $cfg_key => $var)
{
	$$var = request_var($var, '');
}
$error = array();

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

	if ($config_name == 'bump_interval' && $submit)
	{
		$new['bump_interval'] = request_var('bump_interval', 0) . request_var('bump_type', '');
	}

	if ($mode == 'attach')
	{
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
	else
	{
		if ($submit)
		{
			set_config($config_name, $new[$config_name]);
		}
	}
}

if ($submit)
{
	add_log('admin', 'LOG_' . strtoupper($mode) . '_CONFIG');

	if ($mode == 'attach')
	{
		// Check Settings
		test_upload($error, $new['upload_dir'], false);
		test_upload($error, $new['upload_dir'] . '/thumbs', true);
	}

	if (!sizeof($error))
	{
		trigger_error($user->lang['CONFIG_UPDATED']);
	}
}

adm_page_header($user->lang[$l_title]);

?>

<h1><?php echo $user->lang[$l_title]; ?></h1>

<p><?php echo $user->lang[$l_title . '_EXPLAIN']; ?></p>

<form action="<?php echo "admin_board.$phpEx$SID&amp;mode=$mode"; ?>" method="post"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang[$l_title]; ?></th>
	</tr>
<?php

if (sizeof($error))
{

?>
	<tr>
		<td class="row3" colspan="2" align="center"><span style="color:red"><?php echo implode('<br />', $error); ?></span></td>
	</tr>
<?php

}

// Output relevant page
switch ($mode)
{
	case 'attach':

		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		
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

?>

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
		<td class="row2"><input type="text" size="20" maxlength="200" name="img_imagick" value="<?php echo $new['img_imagick']; ?>" class="post" />&nbsp;&nbsp;<span class="gensmall">[ <a href="<?php echo "admin_board.$phpEx$SID&amp;mode=$mode&amp;action=imgmagick"; ?>"><?php echo $user->lang['SEARCH_IMAGICK']; ?></a> ]</span></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_IMAGE_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_IMAGE_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_max_width" value="<?php echo $new['img_max_width']; ?>" class="post" /> px X <input type="text" size="3" maxlength="4" name="img_max_height" value="<?php echo $new['img_max_height']; ?>" class="post" /> px</td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['IMAGE_LINK_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['IMAGE_LINK_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_link_width" value="<?php echo $new['img_link_width']; ?>" class="post" /> px X <input type="text" size="3" maxlength="4" name="img_link_height" value="<?php echo $new['img_link_height']; ?>" class="post" /> px</td>
	</tr>
	
<?php

		break;

	case 'cookie':

		$cookie_secure_yes = ($new['cookie_secure']) ? 'checked="checked"' : '';
		$cookie_secure_no = (!$new['cookie_secure']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COOKIE_DOMAIN']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="cookie_domain" value="<?php echo $new['cookie_domain']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COOKIE_NAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" maxlength="16" name="cookie_name" value="<?php echo $new['cookie_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COOKIE_PATH']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="cookie_path" value="<?php echo $new['cookie_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COOKIE_SECURE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['COOKIE_SECURE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="cookie_secure" value="0"<?php echo $cookie_secure_no; ?> /><?php echo $user->lang['DISABLED']; ?>&nbsp; &nbsp;<input type="radio" name="cookie_secure" value="1"<?php echo $cookie_secure_yes; ?> /><?php echo $user->lang['ENABLED']; ?></td>
	</tr>
<?php

		break;

	case 'avatar':

		$avatars_local_yes = ($new['allow_avatar_local']) ? 'checked="checked"' : '';
		$avatars_local_no = (!$new['allow_avatar_local']) ? 'checked="checked"' : '';
		$avatars_remote_yes = ($new['allow_avatar_remote']) ? 'checked="checked"' : '';
		$avatars_remote_no = (!$new['allow_avatar_remote']) ? 'checked="checked"' : '';
		$avatars_upload_yes = ($new['allow_avatar_upload']) ? 'checked="checked"' : '';
		$avatars_upload_no = (!$new['allow_avatar_upload']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_LOCAL']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_avatar_local" value="1"<?php echo $avatars_local_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_local" value="0"<?php echo $avatars_local_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_REMOTE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_REMOTE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="allow_avatar_remote" value="1"<?php echo $avatars_remote_yes; ?>  /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_remote" value="0"<?php echo $avatars_remote_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_UPLOAD']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_avatar_upload" value="1"<?php echo $avatars_upload_yes; ?>  /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_upload" value="0"<?php echo $avatars_upload_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_FILESIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="10" name="avatar_filesize" value="<?php echo $new['avatar_filesize']; ?>" /> Bytes</td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MIN_AVATAR_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MIN_AVATAR_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="avatar_min_height" value="<?php echo $new['avatar_min_height']; ?>" /> x <input class="post" type="text" size="3" maxlength="4" name="avatar_min_width" value="<?php echo $new['avatar_min_width']; ?>"></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_AVATAR_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_AVATAR_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="avatar_max_height" value="<?php echo $new['avatar_max_height']; ?>" /> x <input class="post" type="text" size="3" maxlength="4" name="avatar_max_width" value="<?php echo $new['avatar_max_width']; ?>"></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AVATAR_STORAGE_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['AVATAR_STORAGE_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="avatar_path" value="<?php echo $new['avatar_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AVATAR_GALLERY_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['AVATAR_GALLERY_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="avatar_gallery_path" value="<?php echo $new['avatar_gallery_path']; ?>" /></td>
	</tr>
<?php

		break;

	case 'default':

		$style_select = style_select($new['default_style'], true);
		$lang_select = language_select($new['default_lang']);
		$timezone_select = tz_select($new['board_timezone']);

		$dst_yes = ($new['board_dst']) ? 'checked="checked"' : '';
		$dst_no = (!$new['board_dst']) ? 'checked="checked"' : '';

		$yes_no_switches = array('override_user_style', 'allow_topic_notify', 'allow_forum_notify', 'allow_html', 'allow_bbcode', 'allow_smilies', 'allow_sig', 'allow_nocensors', 'allow_namechange', 'allow_emailreuse', 'allow_attachments', 'allow_pm_attach');

		foreach ($yes_no_switches as $switch)
		{
			eval('$' . str_replace('allow_', '', $switch) . '_yes = ($new[\'' . $switch . "']) ? 'checked=\"checked\"' : '';");
			eval('$' . str_replace('allow_', '', $switch) . '_no = (!$new[\'' . $switch . "']) ? 'checked=\"checked\"' : '';");
		}

		$user_char_ary = array('USERNAME_CHARS_ANY' => '.*', 'USERNAME_ALPHA_ONLY' => '[/w]+', 'USERNAME_ALPHA_SPACERS' => '[\w_\+\. \-\[\]]+');
		$user_char_options = '';
		foreach ($user_char_ary as $lang => $value)
		{
			$selected = ($new['allow_name_chars'] == $value) ? ' selected="selected"' : '';
			$user_char_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DEFAULT_STYLE']; ?></td>
		<td class="row2"><select name="default_style"><?php echo $style_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['OVERRIDE_STYLE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['OVERRIDE_STYLE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="override_user_style" value="1" <?php echo $override_user_style_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="override_user_style" value="0" <?php echo $override_user_style_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DEFAULT_LANGUAGE']; ?>: </b></td>
		<td class="row2"><select name="default_lang"><?php echo $lang_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DATE_FORMAT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DATE_FORMAT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="default_dateformat" value="<?php echo $new['default_dateformat']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SYSTEM_TIMEZONE']; ?>: </b></td>
		<td class="row2"><select name="board_timezone"><?php echo $timezone_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SYSTEM_DST']; ?>: </b></td>
		<td class="row2"><input type="radio" name="board_dst" value="1" <?php echo $dst_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="board_dst" value="0" <?php echo $dst_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['CHAR_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['CHAR_LIMIT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="6" name="max_post_chars" value="<?php echo $new['max_post_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMILIES_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMILIES_LIMIT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="4" name="max_post_smilies" value="<?php echo $new['max_post_smilies']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['QUOTE_DEPTH_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['QUOTE_DEPTH_LIMIT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="4" name="max_quote_depth" value="<?php echo $new['max_quote_depth']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_TOPIC_NOTIFY']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_topic_notify" value="1" <?php echo $topic_notify_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_topic_notify" value="0" <?php echo $topic_notify_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_FORUM_NOTIFY']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_forum_notify" value="1" <?php echo $forum_notify_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_forum_notify" value="0" <?php echo $forum_notify_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_NAME_CHANGE']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_namechange" value="1" <?php echo $namechange_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_namechange" value="0" <?php echo $namechange_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['USERNAME_LENGTH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['USERNAME_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="min_name_chars" value="<?php echo $new['min_name_chars']; ?>" /> <?php echo $user->lang['MIN_CHARS']; ?>&nbsp;&nbsp;<input class="post" type="text" size="3" maxlength="3" name="max_name_chars" value="<?php echo $new['max_name_chars']; ?>" /> <?php echo $user->lang['MAX_CHARS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['USERNAME_CHARS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['USERNAME_CHARS_EXPLAIN']; ?></span></td>
		<td class="row2"><select name="allow_name_chars"><?php echo $user_char_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['PASSWORD_LENGTH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['PASSWORD_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="min_pass_chars" value="<?php echo $new['min_pass_chars']; ?>" /> <?php echo $user->lang['MIN_CHARS']; ?>&nbsp;&nbsp;<input class="post" type="text" size="3" maxlength="3" name="max_pass_chars" value="<?php echo $new['max_pass_chars']; ?>" /> <?php echo $user->lang['MAX_CHARS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_EMAIL_REUSE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_EMAIL_REUSE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="allow_emailreuse" value="1" <?php echo $emailreuse_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_emailreuse" value="0" <?php echo $emailreuse_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_ATTACHMENTS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_attachments" value="1" <?php echo $attachments_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_attachments" value="0" <?php echo $attachments_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_PM_ATTACHMENTS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_pm_attach" value="1" <?php echo $pm_attach_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_pm_attach" value="0" <?php echo $pm_attach_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_HTML']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_html" value="1" <?php echo $html_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_html" value="0" <?php echo $html_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOWED_TAGS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOWED_TAGS_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="30" maxlength="255" name="allow_html_tags" value="<?php echo $new['allow_html_tags']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_BBCODE']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_bbcode" value="1" <?php echo $bbcode_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_bbcode" value="0" <?php echo $bbcode_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_SMILIES']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_smilies" value="1" <?php echo $smile_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_smilies" value="0" <?php echo $smile_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_SIG']; ?>: </b></td>
		<td class="row2"><input type="radio" name="allow_sig" value="1" <?php echo $sig_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_sig" value="0" <?php echo $sig_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_SIG_LENGTH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_SIG_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="5" maxlength="4" name="max_sig_chars" value="<?php echo $new['max_sig_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_NO_CENSORS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_NO_CENSORS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="allow_nocensors" value="1" <?php echo $nocensors_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_nocensors" value="0" <?php echo $nocensors_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

		break;

	case 'setting':

		$disable_board_yes = ($new['board_disable']) ? 'checked="checked"' : '';
		$disable_board_no = (!$new['board_disable']) ? 'checked="checked"' : '';

		$confirm_enabled = ($new['enable_confirm']) ? 'checked="checked"' : '';
		$confirm_disabled = (!$new['enable_confirm']) ? 'checked="checked"' : '';
		
		$coppa_enable_yes = ($new['coppa_enable']) ? 'checked="checked"' : '';
		$coppa_enable_no = (!$new['coppa_enable']) ? 'checked="checked"' : '';

		$activation_none = ($new['require_activation'] == USER_ACTIVATION_NONE) ? 'checked="checked"' : '';
		$activation_user = ($new['require_activation'] == USER_ACTIVATION_SELF) ? 'checked="checked"' : '';
		$activation_admin = ($new['require_activation'] == USER_ACTIVATION_ADMIN) ? 'checked="checked"' : '';
		$activation_user_admin = ($new['require_activation'] == USER_ACTIVATION_SELF_ADMIN) ? 'checked="checked"' : '';
		$activation_disable = ($new['require_activation'] == USER_ACTIVATION_DISABLE) ? 'checked="checked"' : '';

		$privmsg_on = (!$new['privmsg_disable']) ? 'checked="checked"' : '';
		$privmsg_off = ($new['privmsg_disable']) ? 'checked="checked"' : '';

		$prune_yes = ($new['prune_enable']) ? 'checked="checked"' : '';
		$prune_no = (!$new['prune_enable']) ? 'checked="checked"' : '';

		$display_last_edited_yes = ($new['display_last_edited']) ? 'checked="checked"' : '';
		$display_last_edited_no = (!$new['display_last_edited']) ? 'checked="checked"' : '';

		$bump_type = (string) preg_replace('#^[0-9]+([m|h|d])$#', '\1', $new['bump_interval']);
		$bump_time = (int) preg_replace('#^([0-9]+)[m|h|d]$#', '\1', $new['bump_interval']);
	
		$s_bump_type = '';
		$types = array('m' => 'MINUTES', 'h' => 'HOURS', 'd' => 'DAYS');
		foreach ($types as $type => $lang)
		{
			$selected = ($type == $bump_type) ? 'selected="selected" ' : '';
			$s_bump_type .= '<option value="' . $type . '" ' . $selected . '>' . $user->lang[$lang] . '</option>';
		}
?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SITE_NAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" size="40" maxlength="255" name="sitename" value="<?php echo htmlentities($new['sitename']); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SITE_DESC']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" size="40" maxlength="255" name="site_desc" value="<?php echo htmlentities($new['site_desc']); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BOARD_DISABLE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOARD_DISABLE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="board_disable" value="1" <?php echo $disable_board_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="board_disable" value="0" <?php echo $disable_board_no; ?> /> <?php echo $user->lang['NO']; ?><br /><input class="post" type="text" name="board_disable_msg" maxlength="255" size="40" value="<?php echo $new['board_disable_msg']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ACC_ACTIVATION']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ACC_ACTIVATION_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_NONE; ?>" <?php echo $activation_none; ?> /> <?php echo $user->lang['ACC_NONE']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_SELF; ?>" <?php echo $activation_user; ?> /> <?php echo $user->lang['ACC_USER']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_ADMIN; ?>" <?php echo $activation_admin; ?> /> <?php echo $user->lang['ACC_ADMIN']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_DISABLE; ?>" <?php echo $activation_disable; ?> /> <?php echo $user->lang['ACC_DISABLE']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['VISUAL_CONFIRM']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['VISUAL_CONFIRM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="enable_confirm" value="1"<?php echo $confirm_enabled ?> /> <?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="enable_confirm" value="0" <?php echo $confirm_disabled ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	
	<tr>
		<td class="row1"><b><?php echo $user->lang['ENABLE_COPPA']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ENABLE_COPPA_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="coppa_enable" value="1" <?php echo $coppa_enable_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="coppa_enable" value="0" <?php echo $coppa_enable_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COPPA_FAX']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="coppa_fax" value="<?php echo $new['coppa_fax']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COPPA_MAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['COPPA_MAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea name="coppa_mail" rows="5" cols="40"><?php echo $new['coppa_mail']; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BOARD_PM']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOARD_PM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="privmsg_disable" value="0" <?php echo $privmsg_on; ?> /><?php echo $user->lang['ENABLED']; ?>&nbsp; &nbsp;<input type="radio" name="privmsg_disable" value="1" <?php echo $privmsg_off; ?> /><?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BOXES_MAX']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOXES_MAX_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="4" size="4" name="pm_max_boxes" value="<?php echo $new['pm_max_boxes']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BOXES_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOXES_LIMIT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="4" size="4" name="pm_max_msgs" value="<?php echo $new['pm_max_msgs']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['EDIT_TIME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['EDIT_TIME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="3" size="3" name="edit_time" value="<?php echo $new['edit_time']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DISPLAY_LAST_EDITED']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DISPLAY_LAST_EDITED_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="display_last_edited" value="1" <?php echo $display_last_edited_yes; ?> /><?php echo $user->lang['YES']; ?>&nbsp; &nbsp;<input type="radio" name="display_last_edited" value="0" <?php echo $display_last_edited_no; ?> /><?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FLOOD_INTERVAL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FLOOD_INTERVAL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="flood_interval" value="<?php echo $new['flood_interval']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BUMP_INTERVAL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BUMP_INTERVAL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="bump_interval" value="<?php echo $bump_time ?>" />&nbsp;<select name="bump_type"><?php echo $s_bump_type; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['TOPICS_PER_PAGE']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="topics_per_page" size="3" maxlength="4" value="<?php echo $new['topics_per_page']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['POSTS_PER_PAGE']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="posts_per_page" size="3" maxlength="4" value="<?php echo $new['posts_per_page']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['HOT_THRESHOLD']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="hot_threshold" size="3" maxlength="4" value="<?php echo $new['hot_threshold']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_POLL_OPTIONS']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="max_poll_options" size="4" maxlength="4" value="<?php echo $new['max_poll_options']; ?>" /></td>
	</tr>
<?php

		break;

	case 'email':

		$email_yes = ($new['email_enable']) ? 'checked="checked"' : '';
		$email_no = (!$new['email_enable']) ? 'checked="checked"' : '';

		$board_email_form_yes = ($new['board_email_form']) ? 'checked="checked"' : '';
		$board_email_form_no = (!$new['board_email_form']) ? 'checked="checked"' : '';

		$smtp_yes = ($new['smtp_delivery']) ? 'checked="checked"' : '';
		$smtp_no = (!$new['smtp_delivery']) ? 'checked="checked"' : '';

		$smtp_auth_plain = ($new['smtp_auth_method'] == 'PLAIN' || !$new['smtp_auth_method']) ? 'checked="checked"' : '';
		$smtp_auth_login = ($new['smtp_auth_method'] == 'LOGIN') ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ENABLE_EMAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ENABLE_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="email_enable" value="1" <?php echo $email_yes; ?> /> <?php echo $user->lang['ENABLED']; ?>&nbsp;&nbsp;<input type="radio" name="email_enable" value="0" <?php echo $email_no; ?> /> <?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BOARD_EMAIL_FORM']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOARD_EMAIL_FORM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="board_email_form" value="1" <?php echo $board_email_form_yes; ?> /> <?php echo $user->lang['ENABLED']; ?>&nbsp;&nbsp;<input type="radio" name="board_email_form" value="0" <?php echo $board_email_form_no; ?> /> <?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['EMAIL_PACKAGE_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['EMAIL_PACKAGE_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="5" maxlength="5" name="email_package_size" value="<?php echo $new['email_package_size']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['CONTACT_EMAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['CONTACT_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="board_contact" value="<?php echo $new['board_contact']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ADMIN_EMAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ADMIN_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="board_email" value="<?php echo $new['board_email']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['EMAIL_SIG']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['EMAIL_SIG_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea name="board_email_sig" rows="5" cols="30"><?php echo $new['board_email_sig']; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['USE_SMTP']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['USE_SMTP_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="smtp_delivery" value="1" <?php echo $smtp_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="smtp_delivery" value="0" <?php echo $smtp_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_SERVER']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="smtp_host" value="<?php echo $new['smtp_host']; ?>" size="25" maxlength="50" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_PORT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMTP_PORT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="smtp_port" value="<?php echo $new['smtp_port']; ?>" size="4" maxlength="5" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_AUTH_METHOD']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMTP_AUTH_METHOD_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="smtp_auth_method" value="PLAIN" <?php echo $smtp_auth_plain; ?> /> <?php echo $user->lang['SMTP_PLAIN']; ?>&nbsp;&nbsp;<input type="radio" name="smtp_auth_method" value="LOGIN" <?php echo $smtp_auth_login; ?> /> <?php echo $user->lang['SMTP_LOGIN']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_USERNAME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMTP_USERNAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="smtp_username" value="<?php echo $new['smtp_username']; ?>" size="25" maxlength="255" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_PASSWORD']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMTP_PASSWORD_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="password" name="smtp_password" value="<?php echo $new['smtp_password']; ?>" size="25" maxlength="255" /></td>
	</tr>
<?php

		break;

	case 'server':

		$ip_all = ($new['ip_check'] == 4) ? 'checked="checked"' : '';
		$ip_classc = ($new['ip_check'] == 3) ? 'checked="checked"' : '';
		$ip_classb = ($new['ip_check'] == 2) ? 'checked="checked"' : '';
		$ip_none = ($new['ip_check'] == 0) ? 'checked="checked"' : '';

		$browser_yes = ($new['browser_check']) ? 'checked="checked"' : '';
		$browser_no = (!$new['browser_check']) ? 'checked="checked"' : '';

		$gzip_yes = ($new['gzip_compress']) ? 'checked="checked"' : '';
		$gzip_no = (!$new['gzip_compress']) ? 'checked="checked"' : '';
?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SERVER_NAME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SERVER_NAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="255" size="40" name="server_name" value="<?php echo $new['server_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SERVER_PORT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SERVER_PORT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="5" size="5" name="server_port" value="<?php echo $new['server_port']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SCRIPT_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SCRIPT_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="script_path" value="<?php echo $new['script_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['IP_VALID']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['IP_VALID_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="ip_check" value="4" <?php echo $ip_all; ?> /> <?php echo $user->lang['ALL']; ?>&nbsp;&nbsp;<input type="radio" name="ip_check" value="3" <?php echo $ip_classc; ?> /> <?php echo $user->lang['CLASS_C']; ?>&nbsp;&nbsp;<input type="radio" name="ip_check" value="2" <?php echo $ip_classb; ?> /> <?php echo $user->lang['CLASS_B']; ?>&nbsp;&nbsp;<input type="radio" name="ip_check" value="0" <?php echo $ip_none; ?> /> <?php echo $user->lang['NONE']; ?>&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BROWSER_VALID']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BROWSER_VALID_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="browser_check" value="1" <?php echo $browser_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="browser_check" value="0" <?php echo $browser_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ENABLE_GZIP']; ?>: </b></td>
		<td class="row2"><input type="radio" name="gzip_compress" value="1" <?php echo $gzip_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="gzip_compress" value="0" <?php echo $gzip_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMILIES_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMILIES_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="smilies_path" value="<?php echo $new['smilies_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ICONS_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ICONS_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="icons_path" value="<?php echo $new['icons_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['UPLOAD_ICONS_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['UPLOAD_ICONS_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="upload_icons_path" value="<?php echo $new['upload_icons_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['RANKS_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['RANKS_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="ranks_path" value="<?php echo $new['ranks_path']; ?>" /></td>
	</tr>
<?php

		break;

	case 'load':

		$tplcompile_yes = ($new['load_tplcompile']) ? 'checked="checked"' : '';
		$tplcompile_no = (!$new['load_tplcompile']) ? 'checked="checked"' : '';
		$load_db_track_yes = ($new['load_db_track']) ? 'checked="checked"' : '';
		$load_db_track_no = (!$new['load_db_track']) ? 'checked="checked"' : '';
		$load_db_lastread_yes = ($new['load_db_lastread']) ? 'checked="checked"' : '';
		$load_db_lastread_no = (!$new['load_db_lastread']) ? 'checked="checked"' : '';
		$load_online_yes = ($new['load_online']) ? 'checked="checked"' : '';
		$load_online_no = (!$new['load_online']) ? 'checked="checked"' : '';
		$load_onlinetrack_yes = ($new['load_onlinetrack']) ? 'checked="checked"' : '';
		$load_onlinetrack_no = (!$new['load_onlinetrack']) ? 'checked="checked"' : '';
		$load_birthdays_yes = ($new['load_birthdays']) ? 'checked="checked"' : '';
		$load_birthdays_no = (!$new['load_birthdays']) ? 'checked="checked"' : '';
		$moderators_yes = ($new['load_moderators']) ? 'checked="checked"' : '';
		$moderators_no = (!$new['load_moderators']) ? 'checked="checked"' : '';
		$jumpbox_yes = ($new['load_jumpbox']) ? 'checked="checked"' : '';
		$jumpbox_no = (!$new['load_jumpbox']) ? 'checked="checked"' : '';
		$search_yes = ($new['load_search']) ? 'checked="checked"' : '';
		$search_no = (!$new['load_search']) ? 'checked="checked"' : '';
		$search_update_yes = ($new['load_search_upd']) ? 'checked="checked"' : '';
		$search_update_no = (!$new['load_search_upd']) ? 'checked="checked"' : '';
		$search_phrase_yes = ($new['load_search_phr']) ? 'checked="checked"' : '';
		$search_phrase_no = (!$new['load_search_phr']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['LIMIT_LOAD']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LIMIT_LOAD_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="4" name="limit_load" value="<?php echo $new['limit_load']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SESSION_LENGTH']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" maxlength="5" size="5" name="session_length" value="<?php echo $new['session_length']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['LIMIT_SESSIONS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LIMIT_SESSIONS_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="4" name="active_sessions" value="<?php echo $new['active_sessions']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_POST_MARKING']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_POST_MARKING_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_db_track" value="1"<?php echo $load_db_track_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_db_track" value="0" <?php echo $load_db_track_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_READ_MARKING']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_READ_MARKING_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_db_lastread" value="1"<?php echo $load_db_lastread_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_db_lastread" value="0" <?php echo $load_db_lastread_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_ONLINE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_ONLINE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_online" value="1"<?php echo $load_online_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_online" value="0" <?php echo $load_online_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_ONLINE_TRACK']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_ONLINE_TRACK_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_onlinetrack" value="1"<?php echo $load_onlinetrack_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_onlinetrack" value="0" <?php echo $load_onlinetrack_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['VIEW_ONLINE_TIME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['VIEW_ONLINE_TIME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="3" name="load_online_time" value="<?php echo $new['load_online_time']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_BIRTHDAYS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="load_birthdays" value="1"<?php echo $load_birthdays_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_birthdays" value="0" <?php echo $load_birthdays_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_MODERATORS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="load_moderators" value="1"<?php echo $moderators_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_moderators" value="0" <?php echo $moderators_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_JUMPBOX']; ?>: </b></td>
		<td class="row2"><input type="radio" name="load_jumpbox" value="1"<?php echo $jumpbox_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_jumpbox" value="0" <?php echo $jumpbox_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_SEARCH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_search" value="1"<?php echo $search_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_search" value="0" <?php echo $search_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SEARCH_INTERVAL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SEARCH_INTERVAL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="search_interval" value="<?php echo $new['search_interval']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MIN_SEARCH_CHARS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MIN_SEARCH_CHARS_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="min_search_chars" value="<?php echo $new['min_search_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_SEARCH_CHARS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_SEARCH_CHARS_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="max_search_chars" value="<?php echo $new['max_search_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_SEARCH_UPDATE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_UPDATE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_search_upd" value="1"<?php echo $search_update_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_search_upd" value="0" <?php echo $search_update_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_SEARCH_PHRASE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_PHRASE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_search_phr" value="1"<?php echo $search_phrase_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_search_phr" value="0" <?php echo $search_phrase_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['RECOMPILE_TEMPLATES']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['RECOMPILE_TEMPLATES_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_tplcompile" value="1"<?php echo $tplcompile_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_tplcompile" value="0" <?php echo $tplcompile_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

		break;

	case 'auth':

		$auth_plugins = array();

		$dp = opendir($phpbb_root_path . 'includes/auth');
		while ($file = readdir($dp))
		{
			if (preg_match('#^auth_(.*?)\.' . $phpEx . '$#', $file))
			{
				$auth_plugins[] = preg_replace('#^auth_(.*?)\.' . $phpEx . '$#', '\1', $file);
			}
		}

		sort($auth_plugins);

		$auth_select = '';
		foreach ($auth_plugins as $method)
		{
			$selected = ($config['auth_method'] == $method) ? ' selected="selected"' : '';
			$auth_select .= '<option value="' . $method . '"' . $selected . '>' . ucfirst($method) . '</option>';
		}

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTH_METHOD']; ?>: </b></td>
		<td class="row2"><select name="auth_method"><?php echo $auth_select; ?></select></td>
	</tr>
<?php

		foreach ($auth_plugins as $method)
		{
			if ($method && file_exists($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx))
			{
				include_once($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx);

				$method = 'admin_' . $method;
				if (function_exists($method))
				{
					if ($config_fields = $method($new))
					{
						// Check if we need to create config fields for this plugin
						foreach($config_fields as $field)
						{
							if (!isset($config[$field]))
							{
								set_config($field, '');
							}
						}
					}

					unset($config_fields);
				}
			}
		}

		break;

	case 'karma':

		$enable_karma_yes = ($new['enable_karma']) ? 'checked="checked"' : '';
		$enable_karma_no = (!$new['enable_karma']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['ENABLE_KARMA']; ?>: </b></td>
		<td class="row2"><input type="radio" name="enable_karma" value="1"<?php echo $enable_karma_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="enable_karma" value="0" <?php echo $enable_karma_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MIN_RATINGS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MIN_RATINGS_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="min_ratings" value="<?php echo $new['min_ratings']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['KARMA_HIST_WEIGHT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['KARMA_HIST_WEIGHT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="karma_hist_weight" value="<?php echo $new['karma_hist_weight']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['KARMA_DAY_WEIGHT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['KARMA_DAY_WEIGHT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="karma_day_weight" value="<?php echo $new['karma_30_weight']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['KARMA_REG_WEIGHT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['KARMA_REG_WEIGHT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="karma_reg_weight" value="<?php echo $new['karma_reg_weight']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['KARMA_POST_WEIGHT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['KARMA_POST_WEIGHT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="karma_post_weight" value="<?php echo $new['karma_post_weight']; ?>" /></td>
	</tr>
<?php

		break;
}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();

// Functions

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

?>