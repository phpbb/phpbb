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
	if ( !$auth->get_acl_admin('general') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['General']['Emoticons'] = $filename . $SID . '&amp;mode=emoticons';

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
if (!$auth->get_acl_admin('general'))
{
	message_die(MESSAGE, $lang['No_admin']);
}

//
// Check to see what mode we should operate in.
//
if (isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']))
{
	$mode = (!empty($HTTP_POST_VARS['mode'])) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = '';
}

$delimiter  = '=+:';
$smilies_images = $smilies_paks = array();
$click_return = '<br /><br />' . sprintf($lang['Click_return_smileadmin'], '<a href="admin_smilies.' . $phpEx . $SID . '">', '</a>');
$click_return .= '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="index.' . $phpEx . $SID . '&amp;pane=right">', '</a>');

if ($mode == 'edit' || !empty($HTTP_POST_VARS['add']) || !empty($HTTP_POST_VARS['import_pak']))
{
	$dir = @opendir($phpbb_root_path . $board_config['smilies_path']);
	while ($file = @readdir($dir))
	{
		if (is_file($phpbb_root_path . $board_config['smilies_path'] . '/' . $file))
		{
			$img_size = @getimagesize($phpbb_root_path . $board_config['smilies_path'] . '/' . $file);

			if (preg_match('/\.(gif|png|jpg)$/i', $file) || (!empty($img_size[0]) && !empty($img_size[1])))
			{
				$smilies_images[] = $file;
			}
			elseif (preg_match('/\.pak$/i', $file))
			{
				$smilies_paks[] = $file;
			}
		}
	}
	@closedir($dir);
}

//
// Select main mode
//
if (isset($HTTP_POST_VARS['import_pak']))
{
	if (!empty($HTTP_POST_VARS['smilies_pak']))
	{
		$smile_order = 0;
		//
		// The user has already selected a smilies_pak file.. Import it.
		//
		if (!empty($HTTP_POST_VARS['clear_current']))
		{
			$db->sql_query('DELETE FROM ' . SMILIES_TABLE);
		}
		else
		{
			$result = $db->sql_query('SELECT code FROM ' . SMILIES_TABLE);

			$smilies = array();
			while ($row = $db->sql_fetchrow($result))
			{
				++$smile_order;
				$smilies[$row['code']] = 1;
			}
		}

		$fcontents = @file($phpbb_root_path . $board_config['smilies_path'] . '/'. $smilies_pak);

		if (empty($fcontents))
		{
			message_die(ERROR, 'Could not read smiley pak file' . $click_return);
		}

		foreach ($fcontents as $line)
		{
			$smile_data = explode($delimiter, trim($line));

			$smile_url = $smile_data[0];
			$emotion = $smile_data[1];
			$code = htmlentities($smile_data[2]);

			if (!isset($smile_data[4]))
			{
				//
				// The size isn't specified, try to get it from the file and if it fails
				// arbitrary set it to 15 and let the user correct it later.
				//
				$size = @getimagesize($phpbb_root_path . $board_config['smilies_path'] . '/' . $smile_url);
				$smile_width = (!empty($size[0])) ? $size[0] : 15;
				$smile_height = (!empty($size[1])) ? $size[1] : 15;
			}
			else
			{
				$smile_width = $smile_data[3];
				$smile_height = $smile_data[4];
			}

			if (!empty($smilies[$code]))
			{
				if (!empty($HTTP_POST_VARS['replace_existing']))
				{
					$code_sql = str_replace("'", "''", str_replace('\\', '\\\\', $code));
					$sql = array(
						'smile_url'		=>	$smile_url,
						'smile_height'	=>	$smile_height,
						'smile_width'	=>	$smile_width,
						'emoticon'		=>	$emotion
					);
					$db->sql_query_array('UPDATE ' . SMILIES_TABLE . " SET WHERE code = '$code_sql'", $sql);
				}
			}
			else
			{
				++$smile_order;

				$sql = array(
					'code'			=>	$code,
					'smile_url'		=>	$smile_url,
					'smile_height'	=>	$smile_height,
					'smile_width'	=>	$smile_width,
					'smile_order'	=>	$smile_order,
					'emoticon'		=>	$emotion
				);
				$db->sql_query_array('INSERT INTO ' . SMILIES_TABLE, $sql);
			}
		}

		message_die(MESSAGE, $lang['Smilies_import_success'] . $click_return);
	}
	else
	{
		if (!count($smilies_paks))
		{
			$smilies_paks_select = $lang['No_smilies_pak'];
		}
		else
		{
			$smilies_paks_select = '<select name="smilies_pak">';

			foreach ($smilies_paks as $pak)
			{
				$smilies_paks_select .= '<option>' . htmlspecialchars($pak) . '</option>';
			}
			$smilies_paks_select .= '</select>';
		}

		page_header($lang['Import_smilies']);
?>
<h1><?php echo $lang['Import_smilies'] ?></h1>

<p><?php echo $lang['Import_smilies_explain'] ?></p>

<form method="post" action="admin_smilies.<?php echo $phpEx . $SID ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th class="thHead" colspan="2"><?php echo $lang['Smilies_import'] ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $lang['Select_package'] ?></td>
		<td class="row2"><?php echo $smilies_paks_select ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Delete_existing_smilies'] ?></td>
		<td class="row1"><input type="checkbox" name="clear_current" /></td>
	</tr>
	<tr>
		<td class="row2" colspan="2" align="center"><?php echo $lang['Smilies_conflicts'] ?><br />
			<table align="center" border="0"><tr><td>
			&nbsp;<input type="radio" name="replace_existing" value="1" checked="checked" /> <?php echo $lang['Replace_existing_smilies'] ?>&nbsp;<br />
			&nbsp;<input type="radio" name="replace_existing" value="0" /> <?php echo $lang['Keep_existing_smilies'] ?>&nbsp;</td></tr></table>
		</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" name="import_pak" type="submit" value="<?php echo $lang['Import_smilies'] ?>" /></td>
	</tr>
</table></form>
<?php

		page_footer();
	}
}
elseif (isset($HTTP_GET_VARS['export_pak']))
{
	$smilies_pak = '';

	$result = $db->sql_query('SELECT * FROM ' . SMILIES_TABLE);
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
elseif (isset($HTTP_POST_VARS['export_pak']))
{
	page_header($lang['Export_smilies']);
	message_die(MESSAGE, sprintf($lang['Export_smilies_explain'], '<a href="admin_smilies.' . $phpEx . $SID . '&amp;export_pak=send">', '</a>') . $click_return);
}
elseif (isset($HTTP_POST_VARS['add']))
{
	$filename_list = '';
	foreach ($smilies_images as $smile_url)
	{
		if (!isset($default_image))
		{
			$default_image = $smile_url;
		}
		$filename_list .= '<option value="' . $smile_url . '">' . htmlspecialchars($smile_url) . '</option>';
	}

	page_header($lang['Add_smile']);
?>
<h1><?php echo $lang['Add_smile'] ?></h1>

<script language="javascript" type="text/javascript" defer="defer">
<!--
function update_smile(newimage)
{
	document.smile_image.src = "<?php echo $phpbb_root_path . $board_config['smilies_path'] ?>/" + newimage;
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

<form method="post" action="admin_smilies.<?php echo $phpEx . $SID ?>&amp;mode=create"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th class="thHead" colspan="2"><?php echo $lang['smile_config'] ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $lang['Smile_code'] ?></td>
		<td class="row2"><input type="text" name="smile_code" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Smile_url'] ?></td>
		<td class="row1"><select name="smile_url" onChange="update_smile(this.options[selectedIndex].value);"><?php echo $filename_list ?></select> &nbsp; <img name="smile_image" src="<?php echo (!empty($default_image)) ? $phpbb_root_path . $board_config['smilies_path'] . '/' . $default_image : '../images/spacer.gif' ?>" border="0" alt="" onLoad="update_smile_dimensions()" /> &nbsp;</td>
	</tr>
	<tr>
		<td class="row2"><?php echo $lang['Smile_width'] ?></td>
		<td class="row2"><input type="text" size="4" name="smile_width" value="0" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Smile_height'] ?></td>
		<td class="row1"><input type="text" size="4" name="smile_height" value="0" /></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $lang['Smile_emotion'] ?></td>
		<td class="row2"><input type="text" name="smile_emotion" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" type="submit" value="<?php echo $lang['Submit'] ?>" /></td>
	</tr>
</table></form>
<?php

	page_footer();
}

switch ($mode)
{
	case 'delete':
		$db->sql_query('DELETE FROM ' . SMILIES_TABLE . ' WHERE smilies_id = ' . intval($HTTP_GET_VARS['smile_id']));
		message_die(MESSAGE, $lang['Smile_deleted'] . $click_return);
	break;

	case 'edit':
		$smile_id = intval($HTTP_GET_VARS['smile_id']);

/*
		$sql = 'SELECT *
				FROM ' . SMILIES_TABLE . "
				WHERE smilies_id = $smile_id";
		$result = $db->sql_query($sql);
		$smile_data = $db->sql_fetchrow($result);
*/
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
				$order_list = '<option value="' . ($row['smile_order'] + 1) . '"' . $selected . '>' . sprintf($lang['After_smile'], htmlspecialchars($row['code'])) . '</option>' . $order_list;
			}
		}
		$order_list = '<option value="1"' . ((!isset($after)) ? ' selected="selected"' : '') . '>' . $lang['First'] . '</option>' . $order_list;

		$filename_list = '';
		foreach ($smilies_images as $smile_url)
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

		page_header($lang['Edit_smile']);
?>
<h1><?php echo $lang['Edit_smile'] ?></h1>

<script language="javascript" type="text/javascript" defer="defer">
<!--
function update_smile(newimage)
{
	document.smile_image.src = "<?php echo $phpbb_root_path . $board_config['smilies_path'] ?>/" + newimage;
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
		<th class="th" colspan="2"><?php echo $lang['Smile_config'] ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $lang['Smile_code'] ?></td>
		<td class="row2"><input type="text" name="smile_code" value="<?php echo $smile_data['code'] ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Smile_url'] ?></td>
		<td class="row1"><select name="smile_url" onChange="update_smile(this.options[selectedIndex].value);"><?php echo $filename_list ?></select> &nbsp; <img name="smile_image" src="<?php echo $phpbb_root_path . $board_config['smilies_path'] . '/' . $smile_edit_img ?>" border="0" alt="" onLoad="update_smile_dimensions()" /> &nbsp;</td>
	</tr>
	<tr>
		<td class="row2"><?php echo $lang['Smile_emotion'] ?></td>
		<td class="row2"><input type="text" name="smile_emotion" value="<?php echo $smile_data['emoticon'] ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Smile_width'] ?></td>
		<td class="row1"><input type="text" size="3" name="smile_width" value="<?php echo $smile_data['smile_width'] ?>" /></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $lang['Smile_height'] ?></td>
		<td class="row2"><input type="text" size="3" name="smile_height" value="<?php echo $smile_data['smile_height'] ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Display_on_posting'] ?></td>
		<td class="row1"><input type="checkbox" name="smile_on_posting" <?php echo ($smile_data['smile_on_posting']) ? ' checked="checked"' : '' ?>/></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $lang['Smile_order'] ?></td>
		<td class="row2"><select name="smile_order"><?php echo $order_list ?></select></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="hidden" name="smile_id" value="<?php echo $smile_data['smilies_id'] ?>" /><input class="mainoption" type="submit" value="<?php echo $lang['Submit'] ?>" /></td>
	</tr>
</table></form>
<?php

		page_footer();
		break;

	case 'create':
	case 'modify':

		$smile_width = intval($HTTP_POST_VARS['smile_width']);
		$smile_height = intval($HTTP_POST_VARS['smile_height']);
		if ($smile_width == 0 || $smile_height == 0)
		{
			$img_size = @getimagesize($phpbb_root_path . $board_config['smilies_path'] . '/' . stripslashes($HTTP_POST_VARS['smile_url']));
			$smile_width = $img_size[0];
			$smile_height = $img_size[1];
		}
		$sql = array(
			'code'				=>	htmlspecialchars(stripslashes($HTTP_POST_VARS['smile_code'])),
			'smile_url'			=>	stripslashes($HTTP_POST_VARS['smile_url']),
			'smile_width'		=>	$smile_width,
			'smile_height'		=>	$smile_height,
			'smile_order'		=>	$smile_order,
			'emoticon'			=>	stripslashes($HTTP_POST_VARS['smile_emotion']),
			'smile_on_posting'	=>	(!empty($HTTP_POST_VARS['smile_on_posting'])) ? 1 : 0
		);

		$smile_id = $HTTP_POST_VARS['smile_id'];
		$smile_order = $HTTP_POST_VARS['smile_order'];

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
			$db->sql_query_array('UPDATE ' . SMILIES_TABLE . " SET WHERE smilies_id = $smile_id", $sql);
			message_die(MESSAGE, $lang['Smile_edited'] . $click_return);
		}
		else
		{
			$db->sql_query_array('INSERT INTO ' . SMILIES_TABLE, $sql);
			message_die(MESSAGE, $lang['Smile_added'] . $click_return);
		}
	break;

	case 'move_up':
	case 'move_down':
		$smile_order = intval($HTTP_GET_VARS['smile_order']);
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
				ORDER BY smile_on_posting DESC, smile_order ASC';
		$result = $db->sql_query($sql);
		page_header($lang['Emoticons']);
?>

<h1><?php echo $lang['Emoticons']; ?></h1>

<p><?php echo $lang['Emoticons_explain']; ?></p>

<form method="post" action="admin_smilies.<?php echo $phpEx . $SID ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $lang['Code']; ?></th>
		<th><?php echo $lang['Smile']; ?></th>
		<th><?php echo $lang['Emotion']; ?></th>
		<th colspan="2"><?php echo $lang['Action']; ?></th>
		<th colspan="2"><?php echo $lang['Reorder']; ?></th>
	</tr>
<?php

		$spacer = FALSE;
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$spacer && !$row['smile_on_posting'])
			{
				$spacer = TRUE;
?>
	<tr>
		<td class="row3" colspan="7" align="center"><?php echo $lang['Smilies_not_displayed'] ?></td>
	</tr>
<?php
			}
			$row_class = ( $row_class != 'row1' ) ? 'row1' : 'row2';
?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo htmlspecialchars($row['code']); ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><img src="<?php echo './../' . $board_config['smilies_path'] . '/' . $row['smile_url']; ?>" width="<?php echo $row['smile_width']; ?>" height="<?php echo $row['smile_height']; ?>" alt="<?php echo htmlspecialchars($row['code']); ?>" /></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['emoticon']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=edit&amp;smile_id=" . $row['smilies_id']; ?>"><?php echo $lang['Edit']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=delete&amp;smile_id=" . $row['smilies_id']; ?>"><?php echo $lang['Delete']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=move_up&amp;smile_order=" . $row['smile_order']; ?>"><?php echo $lang['Up']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=move_down&amp;smile_order=" . $row['smile_order']; ?>"><?php echo $lang['Down']; ?></a></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="cat" colspan="7" align="center"><input type="submit" name="add" value="<?php echo $lang['Add_smile']; ?>" class="mainoption" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="import_pak" value="<?php echo $lang['Import_smilies']; ?>">&nbsp;&nbsp;<input class="liteoption" type="submit" name="export_pak" value="<?php echo $lang['Export_smilies']; ?>"></td>
	</tr>
</table></form>

<?php

		page_footer();

		break;
}
?>