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

function test_upload(&$error, &$error_msg, $upload_dir, $ftp_path, $ftp_upload_allowed, $create_directory = false)
{
	global $user;

	$error = FALSE;

	// Does the target directory exist, is it a directory and writeable. (only test if ftp upload is disabled)
	if (!$ftp_upload_allowed)
	{
		if ($create_directory)
		{
			if (!@file_exists($upload_dir))
			{
				@mkdir($upload_dir, 0755);
				@chmod($upload_dir, 0777);
			}
		}
		
		if (!@file_exists($upload_dir))
		{
			$error = TRUE;
			$error_msg = sprintf($user->lang['DIRECTORY_DOES_NOT_EXIST'], $new['upload_dir']) . '<br />';
		}
	
		if (!$error && !is_dir($upload_dir))
		{
			$error = TRUE;
			$error_msg = sprintf($user->lang['DIRECTORY_IS_NOT_A_DIR'], $new['upload_dir']) . '<br />';
		}
	
		if (!$error)
		{
			if ( !($fp = @fopen($upload_dir . '/0_000000.000', 'w')) )
			{
				$error = TRUE;
				$error_msg = sprintf($user->lang['DIRECTORY_NOT_WRITEABLE'], $new['upload_dir']) . '<br />';
			}
			else
			{
				@fclose($fp);
				@unlink($upload_dir . '/0_000000.000');
			}
		}
	}
	else
	{
		// Check FTP Settings
		$server = ( empty($new['ftp_server']) ) ? 'localhost' : $new['ftp_server'];
		$conn_id = @ftp_connect($server);

		if (!$conn_id)
		{
			$error = TRUE;
			$error_msg = sprintf($user->lang['FTP_ERROR_CONNECT'], $server) . '<br />';
		}

		$login_result = @ftp_login($conn_id, $new['ftp_user'], $new['ftp_pass']);

		if (!$login_result && !$error)
		{
			$error = TRUE;
			$error_msg = sprintf($user->lang['FTP_ERROR_LOGIN'], $new['ftp_user']) . '<br />';
		}
		
		if (!@ftp_pasv($conn_id, intval($new['ftp_pasv_mode'])))
		{
			$error = TRUE;
			$error_msg = $user->lang['FTP_ERROR_PASV_MODE'];
		}

		if (!$error)
		{
			// Check Upload
			$tmpfname = @tempnam('/tmp', 't0000');
			@unlink($tmpfname); // unlink for safety on php4.0.3+
			$fp = @fopen($tmpfname, 'w');
			@fwrite($fp, 'test');
			@fclose($fp);

			if ($create_directory)
			{
				$result = @ftp_chdir($conn_id, $ftp_path);
			
				if (!$result)
				{
					@ftp_mkdir($conn_id, $ftp_path);
				}
			}

			$result = @ftp_chdir($conn_id, $ftp_path);

			if (!$result)
			{
				$error = TRUE;
				$error_msg = sprintf($user->lang['FTP_ERROR_PATH'], $ftp_path) . '<br />';
			}
			else
			{
				$res = @ftp_put($conn_id, 't0000', $tmpfname, FTP_ASCII);
			
				if (!$res)
				{
					$error = TRUE;
					$error_msg = sprintf($user->lang['FTP_ERROR_UPLOAD'], $ftp_path) . '<br />';
				}
				else
				{
					$res = @ftp_delete($conn_id, 't0000');

					if (!$res)
					{
						$error = TRUE;
						$error_msg = sprintf($user->lang['FTP_ERROR_DELETE'], $ftp_path) . '<br />';
					}
				}
			}

			@ftp_quit($conn_id);
			@unlink($tmpfname);
		}
	}
}

$mode = (isset($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';

$config_sizes = array('max_filesize' => 'size', 'attachment_quota' => 'quota_size', 'max_filesize_pm' => 'pm_size');

foreach ($config_sizes as $cfg_key => $var)
{
	$$var = (isset($_REQUEST[$var])) ? htmlspecialchars($_REQUEST[$var]) : '';
}

$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$search_imagick = (isset($_POST['search_imagick'])) ? TRUE : FALSE;

$error = $notify = false;
$error_msg = $notify_msg = '';

// Pull all config data
$sql = "SELECT *
	FROM " . CONFIG_TABLE;
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
			if ($new[$config_name] >= 1048576)
			{
				$new[$config_name] = round($new[$config_name] / 1048576 * 100) / 100;
			}
			else if($new[$config_name] >= 1024)
			{
				$new[$config_name] = round($new[$config_name] / 1024 * 100) / 100;
			}
		}

		if ($submit && $mode == 'manage' && $config_name == $cfg_key)
		{
			$old = $new[$config_name];
			$new[$config_name] = ($$var == 'kb') ? round($new[$config_name] * 1024) : (($$var == 'mb') ? round($new[$config_name] * 1048576) : $new[$config_name]);
		}
	} 

	if ($submit && $mode == 'manage')
	{
		// Update Extension Group Filesizes
		if ($config_name == 'max_filesize')
		{
			$old_size = intval($default_config[$config_name]);
			$new_size = intval($new[$config_name]);

			if ($old_size != $new_size)
			{
				// See, if we have a similar value of old_size in Extension Groups. If so, update these values.
				$sql = "UPDATE " . EXTENSION_GROUPS_TABLE . "
					SET max_filesize = " . $new_size . "
					WHERE max_filesize = " . $old_size;
				$db->sql_query($sql);
			}
		}

		set_config($config_name, stripslashes($new[$config_name]));
	
		if (in_array($config_name, array('max_filesize', 'attachment_quota', 'max_filesize_pm')))
		{
			$new[$config_name] = $old;
		}
	}
}

if ($submit && $mode == 'manage')
{
	add_log('admin', 'LOG_SETTING_CONFIG');
	$notify = TRUE;
	$notify_msg = $user->lang['Config_updated'];
}

// Adjust the Upload Directory
if (!$new['allow_ftp_upload'])
{
	if ( ($new['upload_dir'][0] == '/') || ( ($new['upload_dir'][0] != '/') && ($new['upload_dir'][1] == ':') ) )
	{
		$upload_dir = $new['upload_dir'];
	}
	else
	{
		$upload_dir = $phpbb_root_path . $new['upload_dir'];
	}
}
else
{
	$upload_dir = $new['download_path'];
}

switch ($mode)
{
	case 'manage':
		$l_title = 'ATTACHMENT_CONFIG';
		break;
}

// Temporary Language Variables

page_header($user->lang[$l_title]);

// Search Imagick
if ($search_imagick)
{
	$imagick = '';
	
	if (eregi('convert', $imagick)) 
	{
		continue;
	} 
	else if ($imagick != 'none') 
	{
		if (!eregi('WIN', PHP_OS)) 
		{
			$retval = @exec('whereis convert');
			$paths = explode(' ', $retval);

			if (is_array($paths)) 
			{
				foreach($paths as $path)
				{
					if (basename($path) == 'convert') 
					{
						$imagick = $path;
					}
				}
			}
		}
		else if (eregi('WIN', PHP_OS))
		{
			$path = 'c:/imagemagick/convert.exe';

			if (@file_exists($path))
			{
				$imagick = $path;
			}
		}
	} 

	$new['img_imagick'] = (@file_exists(trim($imagick))) ? trim($imagick) : '';
}

// Check Settings
if ($submit && $mode == 'manage')
{
	$upload_dir = ( ($new['upload_dir'][0] == '/') || ($new['upload_dir'][0] != '/' && $new['upload_dir'][1] == ':') ) ? $new['upload_dir'] : $phpbb_root_path . $new['upload_dir'];

	test_upload($error, $error_msg, $upload_dir, $new['ftp_path'], $new['allow_ftp_upload'], false);
}


if ($submit && $mode == 'cats')
{
	$upload_dir = ( ($new['upload_dir'][0] == '/') || ($new['upload_dir'][0] != '/' && $new['upload_dir'][1] == ':') ) ? $new['upload_dir'] . '/thumbs' : $phpbb_root_path . $new['upload_dir'] . '/thumbs';
	test_upload($error, $error_msg, $upload_dir, $new['ftp_path'] . '/thumbs', $new['allow_ftp_upload'], true);
}

?>

<h1><?php echo $user->lang[$l_title]; ?></h1>

<p><?php echo $user->lang[$l_title . '_EXPLAIN']; ?></p>

<?php 
if ($error)
{
?>

<h2 style="color:red"><?php echo $user->lang['WARNING']; ?></h2>

<p><?php echo $error_msg; ?></p>

<?php
}
else if ($notify)
{
?>

<h2 style="color:green"><?php echo $user->lang['NOTIFY']; ?></h2>

<p><?php echo $notify_msg; ?></p>

<?php
}

$modes = array('manage', 'cats', 'extensions');

$select_size_mode = size_select('size', $size);
$select_quota_size_mode = size_select('quota_size', $quota_size);
$select_pm_size_mode = size_select('pm_size', $pm_size);

?>
<form action="admin_attachments.<?php echo $phpEx . $SID . "&amp;mode=$mode"; ?>" method="post">
	<table cellspacing="1" cellpadding="0" border="0" align="center" width="100%">
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

	$yes_no_switches = array('disable_mod', 'allow_pm_attach', 'allow_ftp_upload', 'display_order', 'ftp_pasv_mode');

	for ($i = 0; $i < count($yes_no_switches); $i++)
	{
		eval("\$" . $yes_no_switches[$i] . "_yes = ( \$new['" . $yes_no_switches[$i] . "']) ? 'checked=\"checked\"' : '';");
		eval("\$" . $yes_no_switches[$i] . "_no = ( !\$new['" . $yes_no_switches[$i] . "']) ? 'checked=\"checked\"' : '';");
	}

?>
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
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
<?php
	if (!function_exists('ftp_connect'))
	{
?>

	<input type="hidden" name="allow_ftp_upload" value="0" />
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	<tr>
	  <td class="row1" colspan="2" align="center"><span class="gen"><?php echo $user->lang['NO_FTP_EXTENSIONS_INSTALLED']; ?></span></td>
	</tr>

<?php
	}
	else
	{
?>

	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['FTP_UPLOAD']; ?>:<br /><span class="gensmall"><?php echo $user->lang['FTP_UPLOAD_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="allow_ftp_upload" value="1" <?php echo $allow_ftp_upload_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_ftp_upload" value="0" <?php echo $allow_ftp_upload_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="spacer" colspan="2" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['FTP_SERVER']; ?>:<br /><span class="gensmall"><?php echo $user->lang['FTP_SERVER_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="100" name="ftp_server" class="post" value="<?php echo $new['ftp_server']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['ATTACH_FTP_PATH']; ?>:<br /><span class="gensmall"><?php echo $user->lang['ATTACH_FTP_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="100" name="ftp_path" class="post" value="<?php echo $new['ftp_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['FTP_DOWNLOAD_PATH']; ?>:<br /><span class="gensmall"><?php echo $user->lang['FTP_DOWNLOAD_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="100" name="download_path" class="post" value="<?php echo $new['download_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['FTP_PASSIVE_MODE']; ?>:<br /><span class="gensmall"><?php echo $user->lang['FTP_PASSIVE_MODE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="ftp_pasv_mode" value="1" <?php echo $ftp_pasv_mode_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="ftp_pasv_mode" value="0" <?php echo $ftp_pasv_mode_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['FTP_USER']; ?>:</td>
		<td class="row2"><input type="text" size="20" maxlength="100" name="ftp_user" class="post" value="<?php echo $new['ftp_user']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['FTP_PASS']; ?>:</td>
		<td class="row2"><input type="password" size="10" maxlength="20" name="ftp_pass" class="post" value="<?php echo $new['ftp_path']; ?>" /></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="liteoption" /></td>
	</tr>
</table></form>

<br clear="all" />

<?php
}

page_footer();

?>