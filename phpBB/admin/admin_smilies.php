<?php
/***************************************************************************
*                               admin_smilies.php
*                              -------------------
*     begin                : Thu May 31, 2001
*     copyright            : (C) 2001 The phpBB Group
*     email                : support@phpbb.com
*
*     $Id$
*
****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if ( !empty($setmodules) )
{
	if ( !$auth->acl_get('a_general') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Posts']['Emoticons'] = $filename . $SID . '&amp;type=emoticons';
	$module['Posts']['Topic_icons'] = $filename . $SID . '&amp;type=icons';

	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have general permissions?
//
if (!$auth->acl_get('a_general'))
{
	message_die(MESSAGE, $user->lang['No_admin']);
}

//
// Check to see what mode we should operate in.
//
if (isset($_POST['type']) || isset($_GET['type']))
{
	$type = (!empty($_POST['type'])) ? $_POST['type'] : $_GET['type'];
}
else
{
	$type = '';
}

if (isset($_POST['mode']) || isset($_GET['mode']))
{
	$mode = (!empty($_POST['mode'])) ? $_POST['mode'] : $_GET['mode'];
}
else
{
	$mode = '';
}

switch ($type)
{
	case 'emoticons':
		$table = SMILIES_TABLE;
		$lang = 'smilies';
		$path = $config['smilies_path'];
		break;

	case 'icons':
		$table = ICONS_TABLE;
		$lang = 'icons';
		$path = $config['icons_path'];
		break;
}

$delimiter  = '=+:';
$_images = $_paks = array();

if ($mode == 'edit' || !empty($_POST['add']) || !empty($_POST['import_pak']))
{
	$dir = @opendir($phpbb_root_path . $path);
	while ($file = @readdir($dir))
	{
		if (is_file($phpbb_root_path . $path . '/' . $file))
		{
			$img_size = @getimagesize($phpbb_root_path . $path . '/' . $file);

			if (preg_match('/\.(gif|png|jpg)$/i', $file) || (!empty($img_size[0]) && !empty($img_size[1])))
			{
				$_images[] = $file;
			}
			elseif (preg_match('/\.pak$/i', $file))
			{
				$_paks[] = $file;
			}
		}
	}
	@closedir($dir);
}

//
// Select main mode
//
if (isset($_POST['import_pak']))
{
	if (!empty($_POST['_pak']))
	{
		$smile_order = 0;
		//
		// The user has already selected a smilies_pak file.. Import it.
		//
		if (!empty($_POST['clear_current']))
		{
			$db->sql_query('DELETE FROM ' . $table);
		}
		else
		{
			$result = $db->sql_query('SELECT code FROM ' . $table);

			$smilies = array();
			while ($row = $db->sql_fetchrow($result))
			{
				++$smile_order;
				$smilies[$row['code']] = 1;
			}
		}

		$fcontents = @file($phpbb_root_path . $path . '/'. $_pak);

		if (empty($fcontents))
		{
			trigger_error('Could not read smiley pak file', E_USER_ERROR);
		}

		foreach ($fcontents as $line)
		{
			$_data = explode($delimiter, trim($line));

			$_url = $_data[0];
			$emotion = $_data[1];
			$code = htmlentities($_data[2]);

			if (!isset($_data[4]))
			{
				//
				// The size isn't specified, try to get it from the file and if it fails
				// arbitrary set it to 15 and let the user correct it later.
				//
				$size = @getimagesize($phpbb_root_path . $path . '/' . $smile_url);
				$_width = (!empty($size[0])) ? $size[0] : 15;
				$_height = (!empty($size[1])) ? $size[1] : 15;
			}
			else
			{
				$_width = $_data[3];
				$_height = $_data[4];
			}

			if (!empty($smilies[$code]))
			{
				if (!empty($_POST['replace_existing']))
				{
					$code_sql = str_replace("'", "''", str_replace('\\', '\\\\', $code));
					$sql = array(
						'smile_url'		=>	$_url,
						'smile_height'	=>	$_height,
						'smile_width'	=>	$_width,
						'emoticon'		=>	$emotion
					);
					$db->sql_query("UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql) . "WHERE code = '$code_sql'");
				}
			}
			else
			{
				++$smile_order;

				$sql = array(
					'code'			=>	$code,
					'smile_url'		=>	$_url,
					'smile_height'	=>	$_height,
					'smile_width'	=>	$_width,
					'smile_order'	=>	$_order,
					'emoticon'		=>	$emotion
				);
				$db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $sql));
			}
		}

		message_die(MESSAGE, $user->lang[$lang . '_import_success']);
	}
	else
	{
		if (!count($_paks))
		{
			$_paks_select = $user->lang['No_smilies_pak'];
		}
		else
		{
			$_paks_select = '<select name="smilies_pak">';

			foreach ($_paks as $pak)
			{
				$_paks_select .= '<option>' . htmlspecialchars($pak) . '</option>';
			}
			$_paks_select .= '</select>';
		}

		page_header($user->lang['Import_smilies']);
?>
<h1><?php echo $user->lang['Import_smilies'] ?></h1>

<p><?php echo $user->lang['Import_smilies_explain'] ?></p>

<form method="post" action="admin_smilies.<?php echo $phpEx . $SID . '&amp;type=' . $type; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['Smilies_import'] ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['Select_package'] ?></td>
		<td class="row2"><?php echo $_paks_select ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Delete_existing_smilies'] ?></td>
		<td class="row1"><input type="checkbox" name="clear_current" /></td>
	</tr>
	<tr>
		<td class="row2" colspan="2" align="center"><?php echo $user->lang['Smilies_conflicts'] ?><br />
			<table align="center" border="0"><tr><td>
			&nbsp;<input type="radio" name="replace_existing" value="1" checked="checked" /> <?php echo $user->lang['Replace_existing_smilies'] ?>&nbsp;<br />
			&nbsp;<input type="radio" name="replace_existing" value="0" /> <?php echo $user->lang['Keep_existing_smilies'] ?>&nbsp;</td></tr></table>
		</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" name="import_pak" type="submit" value="<?php echo $user->lang['Import_smilies'] ?>" /></td>
	</tr>
</table></form>
<?php

		page_footer();
	}
}
elseif (isset($_GET['export_pak']))
{
	$smilies_pak = '';

	$result = $db->sql_query('SELECT * FROM ' . $table);
	while ($row = $db->sql_fetchrow($result))
	{
		$smilies_pak .= $row['smile_url'] . $delimiter;
		$smilies_pak .= $row['emoticon'] . $delimiter;
		$smilies_pak .= $row['code'] . $delimiter;
		$smilies_pak .= $row['smile_height'] . $delimiter;
		$smilies_pak .= $row['smile_width'] . "\n";
	}
	$db->sql_close();

	header('Content-Type: text/x-delimtext; name="smilies.pak"');
	header('Content-disposition: attachment; filename=smilies.pak"');

	echo $smilies_pak;

	exit;
}
elseif (isset($_POST['export_pak']))
{
	page_header($user->lang['Export_smilies']);
	message_die(MESSAGE, sprintf($user->lang['Export_smilies_explain'], '<a href="admin_smilies.' . $phpEx . $SID . '&amp;export_pak=send">', '</a>'));
}
elseif (isset($_POST['add']))
{
	$filename_list = '';
	foreach ($_images as $smile_url)
	{
		if (!isset($default_image))
		{
			$default_image = $smile_url;
		}
		$filename_list .= '<option value="' . $smile_url . '">' . htmlspecialchars($smile_url) . '</option>';
	}

	page_header($user->lang['Add_smile']);
?>
<h1><?php echo $user->lang['Add_smile'] ?></h1>

<script language="javascript" type="text/javascript" defer="defer">
<!--
function update_smile(newimage)
{
	document.smile_image.src = "<?php echo $phpbb_root_path . $config['smilies_path'] ?>/" + newimage;
}
function update_smile_dimensions()
{
	if (document.smile_image.height)
	{
		document.forms[0].smile_height.value = document.smile_image.height;
		document.forms[0].smile_width.value = document.smile_image.width;
	}
}
//-->
</script>

<form method="post" action="admin_smilies.<?php echo $phpEx . $SID ?>&amp;mode=create">><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['smile_config'] ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['Smile_code'] ?></td>
		<td class="row2"><input type="text" name="smile_code" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Smile_url'] ?></td>
		<td class="row1"><select name="smile_url" onChange="update_smile(this.options[selectedIndex].value);"><?php echo $filename_list ?></select> &nbsp; <img name="smile_image" src="<?php echo (!empty($default_image)) ? $phpbb_root_path . $config['smilies_path'] . '/' . $default_image : '../images/spacer.gif' ?>" border="0" alt="" onLoad="update_smile_dimensions()" /> &nbsp;</td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['Smile_width'] ?></td>
		<td class="row2"><input type="text" size="4" name="smile_width" value="0" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Smile_height'] ?></td>
		<td class="row1"><input type="text" size="4" name="smile_height" value="0" /></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['Smile_emotion'] ?></td>
		<td class="row2"><input type="text" name="smile_emotion" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" type="submit" value="<?php echo $user->lang['Submit'] ?>" /></td>
	</tr>
</table></form>
<?php

	page_footer();
}

switch ($mode)
{
	case 'delete':
		$db->sql_query('DELETE FROM ' . SMILIES_TABLE . ' WHERE smilies_id = ' . intval($_GET['smile_id']));
		message_die(MESSAGE, $user->lang['Smile_deleted']);
	break;

	case 'edit':
		$smile_id = intval($_GET['smile_id']);

		$order_list = '';
		$result = $db->sql_query('SELECT * FROM ' . SMILIES_TABLE . ' ORDER BY smile_order DESC');
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['smilies_id'] == $smile_id)
			{
				$after = TRUE;
				$smile_data = $row;
			}
			else
			{
				$selected = '';
				if (!empty($after))
				{
					$selected = ' selected="selected"';
					$after = FALSE;
				}
				$order_list = '<option value="' . ($row['smile_order'] + 1) . '"' . $selected . '>' . sprintf($user->lang['After_smile'], htmlspecialchars($row['code'])) . '</option>' . $order_list;
			}
		}
		$order_list = '<option value="1"' . ((!isset($after)) ? ' selected="selected"' : '') . '>' . $user->lang['First'] . '</option>' . $order_list;

		$filename_list = '';
		foreach ($_images as $smile_url)
		{
			if ($smile_url == $smile_data['smile_url'])
			{
				$smile_selected = ' selected="selected"';
				$smile_edit_img = $smile_url;
			}
			else
			{
				$smile_selected = '';
			}

			$filename_list .= '<option value="' . $smile_url . '"' . htmlspecialchars($smile_url) . $smile_selected . '>' . $smile_url . '</option>';
		}

		page_header($user->lang['Edit_smile']);
?>
<h1><?php echo $user->lang['Edit_smile'] ?></h1>

<script language="javascript" type="text/javascript" defer="defer">
<!--
function update_smile(newimage)
{
	document.smile_image.src = "<?php echo $phpbb_root_path . $config['smilies_path'] ?>/" + newimage;
}
function update_smile_dimensions()
{
	if (document.smile_image.height)
	{
		document.forms[0].smile_height.value = document.smile_image.height;
		document.forms[0].smile_width.value = document.smile_image.width;
	}
}
//-->
</script>

<form method="post" action="admin_smilies.<?php echo $phpEx . $SID ?>&amp;mode=modify"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th class="th" colspan="2"><?php echo $user->lang['Smile_config'] ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['Smile_code'] ?></td>
		<td class="row2"><input type="text" name="smile_code" value="<?php echo $smile_data['code'] ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Smile_url'] ?></td>
		<td class="row1"><select name="smile_url" onChange="update_smile(this.options[selectedIndex].value);"><?php echo $filename_list ?></select> &nbsp; <img name="smile_image" src="<?php echo $phpbb_root_path . $config['smilies_path'] . '/' . $smile_edit_img ?>" border="0" alt="" onLoad="update_smile_dimensions()" /> &nbsp;</td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['Smile_emotion'] ?></td>
		<td class="row2"><input type="text" name="smile_emotion" value="<?php echo $smile_data['emoticon'] ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Smile_width'] ?></td>
		<td class="row1"><input type="text" size="3" name="smile_width" value="<?php echo $smile_data['smile_width'] ?>" /></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['Smile_height'] ?></td>
		<td class="row2"><input type="text" size="3" name="smile_height" value="<?php echo $smile_data['smile_height'] ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Display_on_posting'] ?></td>
		<td class="row1"><input type="checkbox" name="display_on_posting" <?php echo ($smile_data['display_on_posting']) ? ' checked="checked"' : '' ?>/></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['Smile_order'] ?></td>
		<td class="row2"><select name="smile_order"><?php echo $order_list ?></select></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="hidden" name="smile_id" value="<?php echo $smile_data['smilies_id'] ?>" /><input class="mainoption" type="submit" value="<?php echo $user->lang['Submit'] ?>" /></td>
	</tr>
</table></form>
<?php

		page_footer();
		break;

	case 'create':
	case 'modify':

		$smile_width = intval($_POST['smile_width']);
		$smile_height = intval($_POST['smile_height']);
		if ($smile_width == 0 || $smile_height == 0)
		{
			$img_size = @getimagesize($phpbb_root_path . $config['smilies_path'] . '/' . stripslashes($_POST['smile_url']));
			$smile_width = $img_size[0];
			$smile_height = $img_size[1];
		}
		$sql = array(
			'code'				=>	htmlspecialchars(stripslashes($_POST['smile_code'])),
			'smile_url'			=>	stripslashes($_POST['smile_url']),
			'smile_width'		=>	$smile_width,
			'smile_height'		=>	$smile_height,
			'smile_order'		=>	$smile_order,
			'emoticon'			=>	stripslashes($_POST['smile_emotion']),
			'display_on_posting'	=>	(!empty($_POST['display_on_posting'])) ? 1 : 0
		);

		$smile_id = $_POST['smile_id'];
		$smile_order = $_POST['smile_order'];

		if ($mode == 'modify')
		{
			$result = $db->sql_query('SELECT smile_order FROM ' . SMILIES_TABLE . " WHERE smilies_id = $smile_id");
			$order_old = $db->sql_fetchfield('smile_order', 0, $result);

			if ($order_old == $smile_order)
			{
				$no_update = TRUE;
			}
			if ($order_old > $smile_order)
			{
				$sign = '+';
				$where = "smile_order >= $smile_order AND smile_order < $order_old";
			}
			else
			{
				$sign = '-';
				$where = "smile_order > $order_old AND smile_order < $smile_order";
				$sql['smile_order'] = $smile_order - 1;
			}
		}
		else
		{
			$sign = '+';
			$where = "smile_order > $smile_order";
		}

		if (empty($no_update))
		{
			$qry = 'UPDATE ' . SMILIES_TABLE . "
					SET smile_order = smile_order $sign 1
					WHERE $where";
			$db->sql_query($qry);
		}

		if ($mode == 'modify')
		{
			$db->sql_query('UPDATE ' . SMILIES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql) . "WHERE smilies_id = $smile_id");
			message_die(MESSAGE, $user->lang['Smile_edited']);
		}
		else
		{
			$db->sql_query('INSERT INTO ' . SMILIES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql));
			message_die(MESSAGE, $user->lang['Smile_added']);
		}
	break;

	case 'move_up':
	case 'move_down':
		$smile_order = intval($_GET['smile_order']);
		$order_total = $smile_order * 2 + (($mode == 'move_up') ? -1 : 1);

		$sql = 'UPDATE ' . SMILIES_TABLE . "
				SET smile_order = $order_total - smile_order
				WHERE smile_order IN ($smile_order, " . (($mode == 'move_up') ? $smile_order - 1 : $smile_order + 1) . ')';
		$db->sql_query($sql);

		//
		// No break; here, display the smilies admin back
		//

	default:
		//
		// By default, check that smile_order is valid and fix it if necessary
		//
		$order = 0;
		$result = $db->sql_query('SELECT * FROM ' . SMILIES_TABLE . ' ORDER BY smile_order');
		while ($row = $db->sql_fetchrow($result))
		{
			++$order;
			if ($row['smile_order'] != $order)
			{
				$db->sql_query('UPDATE ' . SMILIES_TABLE . " SET smile_order = $order WHERE smilies_id = " . $row['smilies_id']);
			}
		}

		$sql = 'SELECT *
				FROM ' . SMILIES_TABLE . '
				ORDER BY display_on_posting DESC, smile_order ASC';
		$result = $db->sql_query($sql);
		page_header($user->lang['Emoticons']);
?>

<h1><?php echo $user->lang['Emoticons']; ?></h1>

<p><?php echo $user->lang['Emoticons_explain']; ?></p>

<form method="post" action="admin_smilies.<?php echo $phpEx . $SID ?>"><table cellspacing="1" cellpadding="0" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['Add_smile']; ?> |  <?php echo $user->lang['Import_smilies']; ?> | <?php echo $user->lang['Export_smilies']; ?></td>
	</tr>
	<tr>
		<td><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
<?php

	if ($type == 'emoticons')
	{

?>
				<th><?php echo $user->lang['Code']; ?></th>
				<th><?php echo $user->lang['Emotion']; ?></th>
<?php

	}

?>
				<th><?php echo $user->lang['Smile']; ?></th>
				<th colspan="2"><?php echo $user->lang['Action']; ?></th>
				<th colspan="2"><?php echo $user->lang['Reorder']; ?></th>
			</tr>
<?php

		$spacer = FALSE;
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$spacer && !$row['display_on_posting'])
			{
				$spacer = TRUE;
?>
			<tr>
				<td class="row3" colspan="<?php echo ($type == 'emoticons') ? 7 : 5; ?>" align="center"><?php echo $user->lang['Smilies_not_displayed'] ?></td>
			</tr>
<?php
			}
			$row_class = ( $row_class != 'row1' ) ? 'row1' : 'row2';
?>
	<tr>
<?php

	if ($type == 'emoticons')
	{

?>
				<td class="<?php echo $row_class; ?>" align="center"><?php echo htmlspecialchars($row['code']); ?></td>
				<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['emoticon']; ?></td>
<?php

	}

?>
				<td class="<?php echo $row_class; ?>" align="center"><img src="<?php echo './../' . $config['smilies_path'] . '/' . $row['smile_url']; ?>" width="<?php echo $row['smile_width']; ?>" height="<?php echo $row['smile_height']; ?>" alt="<?php echo htmlspecialchars($row['code']); ?>" /></td>
				<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=edit&amp;smile_id=" . $row['smilies_id']; ?>"><?php echo $user->lang['Edit']; ?></a></td>
				<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=delete&amp;smile_id=" . $row['smilies_id']; ?>"><?php echo $user->lang['Delete']; ?></a></td>
				<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=move_up&amp;smile_order=" . $row['smile_order']; ?>"><?php echo $user->lang['Up']; ?></a></td>
				<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=move_down&amp;smile_order=" . $row['smile_order']; ?>"><?php echo $user->lang['Down']; ?></a></td>
			</tr>
<?php

		}

?>
			<tr>
				<td class="cat" colspan="<?php echo ($type == 'emoticons') ? 7 : 5; ?>" align="center"><input type="submit" name="add" value="<?php echo $user->lang['Add_smile']; ?>" class="mainoption" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="import_pak" value="<?php echo $user->lang['Import_smilies']; ?>">&nbsp;&nbsp;<input class="liteoption" type="submit" name="export_pak" value="<?php echo $user->lang['Export_smilies']; ?>"></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

		page_footer();

		break;
}
?>