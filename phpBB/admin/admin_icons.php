<?php
/***************************************************************************
*                               admin_icons.php
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

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_icons'))
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['POST']['SMILE'] = $filename . $SID . '&amp;mode=emoticons';
	$module['POST']['ICONS'] = $filename . $SID . '&amp;mode=icons';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Do we have general permissions?
if (!$auth->acl_get('a_icons'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Grab some basic parameters
$mode = (!empty($_REQUEST['mode'])) ? $_REQUEST['mode'] : '';
$action = (!empty($_REQUEST['action'])) ? $_REQUEST['action'] : ((isset($_POST['add'])) ? 'add' : '');
$id = (isset($_GET['id'])) ? intval($_GET['id']) : false;

// What are we working on?
switch ($mode)
{
	case 'emoticons':
		$table = SMILIES_TABLE;
		$lang = 'SMILE';
		$fields = 'smile';
		$img_path = $config['smilies_path'];
		break;

	case 'icons':
		$table = ICONS_TABLE;
		$lang = 'ICONS';
		$fields = 'icons';
		$img_path = $config['icons_path'];
		break;
}

// Clear some arrays
$_images = $_paks = array();



// Grab file list of paks and images
if ($action == 'edit' || $action == 'add' || $action == 'import')
{
	$dir = @opendir($phpbb_root_path . $img_path);
	while ($file = @readdir($dir))
	{
		if (is_file($phpbb_root_path . $img_path . '/' . $file))
		{
			$img_size = @getimagesize($phpbb_root_path . $img_path . '/' . $file);

			if (preg_match('#\.(gif|png|jpg)$#i', $file) || (!empty($img_size[0]) && !empty($img_size[1])))
			{
				$_images[] = $file;
			}
			elseif (preg_match('#\.pak$#i', $file))
			{
				$_paks[] = $file;
			}
		}
	}
	@closedir($dir);
}


// What shall we do today? Oops, I believe that's trademarked ...
switch ($action)
{
	case 'delete':

		$db->sql_query('DELETE FROM ' . $table . ' 
			WHERE ' . $fields . '_id = ' . intval($_GET['id']));

		switch ($mode)
		{
			case 'emoticons':
				break;

			case 'icons':
				// Reset appropriate icon_ids
				$db->sql_query('UPDATE ' . TOPICS_TABLE . ' 
					SET icon_id = 0 
					WHERE icon_id = ' . intval($_GET['id']));
				$db->sql_query('UPDATE ' . POSTS_TABLE . ' 
					SET icon_id = 0 
					WHERE icon_id = ' . intval($_GET['id']));
				break;
		}

		trigger_error($user->lang[$lang . '_DELETED']);
		break;

	case 'edit':
	case 'add':

		$order_list = '';
		$existing_imgs = array();
		$result = $db->sql_query('SELECT * 
			FROM ' . $table . ' 
			ORDER BY ' . $fields . '_order DESC');
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$existing_imgs[] = $row[$fields . '_url'];

				if ($row[$fields . '_id'] == $id)
				{
					$after = TRUE;
					$data = $row;
				}
				else
				{
					$selected = '';
					if (!empty($after))
					{
						$selected = ' selected="selected"';
						$after = FALSE;
					}

					$after_txt = ($mode == 'emoticons') ? $row['code'] : $row['icons_url'];
					$order_list = '<option value="' . ($row[$fields . '_order']) . '"' . $selected . '>' . sprintf($user->lang['AFTER_' . $lang], ' -&gt; ' . htmlspecialchars($after_txt)) . '</option>' . $order_list;
				}
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		$order_list = '<option value="1"' . ((!isset($after)) ? ' selected="selected"' : '') . '>' . $user->lang['FIRST'] . '</option>' . $order_list;
		
		$imglist = filelist($phpbb_root_path . $img_path, '');

		$filename_list = '';
		foreach ($imglist as $img)
		{
			$img = substr($img['path'], 1) . (($img['path'] != '') ? '/' : '') . $img['file']; 

			if (!in_array($img, $existing_imgs))
			{
				if ((isset($data) && $img == $data[$fields . '_url']) || 
					(!isset($data) && !isset($edit_img)))
				{
					$selected = ' selected="selected"';
					$edit_img = $img;
				}
				else
				{
					$selected = '';
				}

				$filename_list .= '<option value="' . $img . '"' . htmlspecialchars($img) . $selected . '>' . $img . '</option>';
			}
		}
		unset($existing_imgs);
		unset($imglist);
	
		page_header($user->lang[$lang]);

?>

<h1><?php echo $user->lang[$lang]; ?></h1>

<p><?php echo $user->lang[$lang .'_EXPLAIN']; ?></p>

<script language="javascript" type="text/javascript" defer="defer">
<!--

function update_image(newimage)
{
	document.image.src = "<?php echo $phpbb_root_path . $img_path ?>/" + newimage;
}

function update_image_dimensions()
{
	if (document.image.height)
	{
		document.forms[0].height.value = document.image.height;
		document.forms[0].width.value = document.image.width;
	}
}

//-->
</script>

<form method="post" action="admin_icons.<?php echo $phpEx . $SID . "&amp;mode=$mode&amp;action=" . (($action == 'add') ? 'create' : 'modify'); ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th class="th" colspan="2"><?php echo $user->lang[$lang . '_CONFIG'] ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang[$lang . '_URL'] ?></td>
		<td class="row1"><select name="img" onChange="update_image(this.options[selectedIndex].value);"><?php echo $filename_list ?></select> &nbsp; <img src="<?php echo $phpbb_root_path . $img_path . '/' . $edit_img ?>"  name="image" border="0" alt="" title="" onload="update_image_dimensions()" /> &nbsp;</td>
	</tr>
<?php

	if ($mode == 'emoticons')
	{

?>
	<tr>
		<td class="row2"><?php echo $user->lang[$lang . '_CODE'] ?></td>
		<td class="row2"><input type="text" name="code" value="<?php echo (!empty($data['code'])) ? $data['code'] : '' ?>" /></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang[$lang . '_EMOTION'] ?></td>
		<td class="row2"><input type="text" name="emotion" value="<?php echo (!empty($data['emoticon'])) ? $data['emoticon'] : '' ?>" /></td>
	</tr>
<?php

	}

?>
	<tr>
		<td class="row1"><?php echo $user->lang[$lang . '_WIDTH'] ?></td>
		<td class="row1"><input type="text" size="3" name="width" value="<?php echo (!empty($data[$fields .'_width'])) ? $data[$fields .'_width'] : '' ?>" /></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang[$lang . '_HEIGHT'] ?></td>
		<td class="row2"><input type="text" size="3" name="height" value="<?php echo (!empty($data[$fields .'_height'])) ? $data[$fields .'_height'] : '' ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['DISPLAY_ON_POSTING'] ?></td>
		<td class="row1"><input type="checkbox" name="display_on_posting" <?php echo (!empty($data['display_on_posting']) || !isset($data)) ? ' checked="checked"' : '' ?>/></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang[$lang . '_ORDER'] ?></td>
		<td class="row2"><select name="order"><?php echo $order_list ?></select></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><?php 
			
	if (!empty($data))
	{

?><input type="hidden" name="id" value="<?php echo $data[$fields . '_id'] ?>" /><?php
	
	}
	
?><input class="mainoption" type="submit" value="<?php echo $user->lang['SUBMIT'] ?>" /></td>
	</tr>
</table></form>
<?php

		page_footer();
		break;

	case 'create':
	case 'modify':

		$image_id = intval($_POST['id']);
		$img = stripslashes($_POST['img']);
		$image_order = intval($_POST['order']);
		$image_width = intval($_POST['width']);
		$image_height = intval($_POST['height']);

		if ($image_width == 0 || $image_height == 0)
		{
			$img_size = @getimagesize($phpbb_root_path . $img_path . '/' . $img);
			$smile_width = $img_size[0];
			$smile_height = $img_size[1];
		}

		$img_sql = array(
			$fields . '_url'	=>	$img,
			$fields . '_width'	=>	$image_width,
			$fields . '_height'	=>	$image_height,
			$fields . '_order'	=>	$image_order,
			'display_on_posting'=>	(!empty($_POST['display_on_posting'])) ? 1 : 0
		);
		if ($mode == 'emoticons')
		{
			$img_sql = array_merge($sql, array(
				'emoticon'	=>	stripslashes($_POST['emotion']),
				'code'		=>	htmlspecialchars(stripslashes($_POST['code']))
			));
		}

		if ($action == 'modify')
		{
			$result = $db->sql_query('SELECT ' . $fields . '_order 
				FROM ' . $table . ' 
				WHERE ' . $fields . "_id = $image_id");
			$order_old = $db->sql_fetchfield($fields . '_order', 0, $result);

			if ($order_old == $smile_order)
			{
				$no_update = TRUE;
			}

			if ($order_old > $smile_order)
			{
				$sign = '+';
				$where = $fields . "_order >= $image_order AND " . $fields . "_order < $order_old";
			}
			else
			{
				$sign = '-';
				$where = $fields . "_order > $order_old AND " . $fields . "_order < $image_order";
				$sql[$fields . '_order'] = $smile_order - 1;
			}
		}
		else
		{
			$sign = '+';
			$where = $fields . "_order > $image_order";
		}

		if (empty($no_update))
		{
			$sql = 'UPDATE ' . $table . '
				SET ' . $fields . '_order = ' . $fields . "_order $sign 1
				WHERE $where";
			$db->sql_query($sql);
		}

		if ($action == 'modify')
		{
			$db->sql_query('UPDATE ' . $table . ' 
				SET ' . $db->sql_build_array('UPDATE', $img_sql) . " 
				WHERE " . $fields . "_id = $image_id");
			$cache->destroy('icons');

			trigger_error($user->lang[$lang . '_EDITED']);
		}
		else
		{
			$db->sql_query('INSERT INTO ' . $table . ' ' . $db->sql_build_array('INSERT', $img_sql));
			$cache->destroy('icons');

			trigger_error($user->lang[$lang . '_ADDED']);
		}
		break;

	case 'import':

		if (!empty($_POST['pak']))
		{
			$order = 0;

			// The user has already selected a smilies_pak file
			if ($_POST['current'] == 'delete')
			{
				$db->sql_query('TRUNCATE ' . $table);

				switch ($mode)
				{
					case 'emoticons':
						break;

					case 'icons':
						// Reset all icon_ids
						$db->sql_query('UPDATE ' . TOPICS_TABLE . ' 
							SET icon_id = 0');
						$db->sql_query('UPDATE ' . POSTS_TABLE . ' 
							SET icon_id = 0');
						break;
				}
			}
			else 
			{
				$cur_img = array();

				$field_sql = ($mode == 'emoticons') ? 'code' : 'icons_url';
				$result = $db->sql_query('SELECT ' . $field_sql . ' 
					FROM ' . $table);
				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						++$order;
						$cur_img[$row[$field_sql]] = 1;
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);
			}

			if (!($pak_ary = @file($phpbb_root_path . $img_path . '/'. stripslashes($_POST['pak']))))
			{
				trigger_error('Could not read smiley pak file', E_USER_ERROR);
			}

			foreach ($pak_ary as $pak_entry)
			{
				$data = array();
				if (preg_match_all("#'(.*?)', #", $pak_entry, $data))
				{
					if ((sizeof($data[1]) == 5 && $mode == 'icons') || 
						(sizeof($data[1]) != 5 && $mode == 'emoticons'))
					{
						trigger_error($user->lang['WRONG_PAK_TYPE']);
					}

					$img = stripslashes($data[1][0]);
					$width = stripslashes($data[1][1]);
					$height = stripslashes($data[1][2]);
					if (isset($data[1][3]) && isset($data[1][4]))
					{
						$emotion = stripslashes($data[1][3]);
						$code = htmlentities(stripslashes($data[1][4]));
					}

					if ($_POST['current'] == 'replace' && 
						(($mode == 'emoticons' && !empty($cur_img[$code])) || 
						($mode == 'icons' && !empty($cur_img[$img]))))
					{
						$replace_sql = ($mode == 'emoticons') ? $code : $img;
						$sql = array(
							$fields . '_url'	=>	$img,
							$fields . '_height'	=>	intval($height),
							$fields . '_width'	=>	intval($width),
						);
						if ($mode == 'emoticons')
						{
							$sql = array_merge($sql, array(
								'emoticon'	=>	$emotion
							));
						}

						$db->sql_query("UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql) . " 
							WHERE $field_sql = '" . $db->sql_escape($replace_sql) . "'");
					}
					else
					{
						++$order;

						$sql = array(
							$fields . '_url'	=>	$img,
							$fields . '_height'	=>	intval($height),
							$fields . '_width'	=>	intval($width),
							$fields . '_order'	=>	intval($order),
						);
						if ($mode == 'emoticons')
						{
							$sql = array_merge($sql, array(
								'code'		=>	$code,
								'emoticon'	=>	$emotion
							));
						}
						$db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $sql));
					}

				}
			}
			trigger_error($user->lang[$lang . '_IMPORT_SUCCESS']);
		}
		else
		{
			$paklist = filelist($phpbb_root_path . $img_path, '', 'pak');

			$pak_options = '';
			if (count($paklist))
			{
				foreach ($paklist as $pak)
				{
					$pak = substr($pak['path'], 1) . (($pak['path'] != '') ? '/' : '') . $pak['file'];

					$pak_options .= '<option>' . htmlspecialchars($pak) . '</option>';
				}
			}

			page_header($user->lang[$lang]);

?>
<h1><?php echo $user->lang[$lang] ?></h1>

<p><?php echo $user->lang[$lang .'_EXPLAIN'] ?></p>

<form method="post" action="admin_icons.<?php echo $phpEx . $SID . '&amp;mode=' . $mode . '&amp;action=import'; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang[$lang . '_IMPORT'] ?></th>
	</tr>
<?php

			if ($pak_options == '')
			{

?>
	<tr>
		<td class="row1" colspan="2"><?php echo $user->lang['NO_' . $lang . '_PAK']; ?></td>
	</tr>
<?php

			}
			else
			{

?>
	<tr>
		<td class="row2"><?php echo $user->lang['SELECT_PACKAGE'] ?></td>
		<td class="row1"><select name="pak"><?php echo $pak_options ?></select></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['CURRENT_' . $lang] ?><br /><span class="gensmall"><?php echo $user->lang['CURRENT_' . $lang . '_EXPLAIN'] ?></span></td>
		<td class="row1"><input type="radio" name="current" value="keep" checked="checked" /> <?php echo $user->lang['KEEP_ALL'] ?>&nbsp; &nbsp;<input type="radio" name="current" value="replace" /> <?php echo $user->lang['REPLACE_MATCHES'] ?>&nbsp; &nbsp;<input type="radio" name="current" value="delete" /> <?php echo $user->lang['DELETE_ALL'] ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" name="import" type="submit" value="<?php echo $user->lang['IMPORT_' . $lang] ?>" /></td>
	</tr>
<?php

			}

?>
</table></form>
<?php
			page_footer();

		}
		break;

	case 'export':

		page_header($user->lang['EXPORT_' . $lang]);
		trigger_error(sprintf($user->lang['EXPORT_' . $lang . '_EXPLAIN'], '<a href="admin_icons.' . $phpEx . $SID . '&amp;mode=' . $mode . '&amp;action=send">', '</a>'));
		break;

	case 'send':

		$result = $db->sql_query('SELECT * 
			FROM ' . $table . " 
			ORDER BY {$fields}_order");
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$pak .= "'" . addslashes($row[$fields . '_url']) . "', ";
				$pak .= "'" . addslashes($row[$fields . '_height']) . "', ";
				$pak .= "'" . addslashes($row[$fields . '_width']) . "', ";
				if ($mode == 'emoticons')
				{
					$pak .= "'" . addslashes($row['emoticon']) . "', ";
					$pak .= "'" . addslashes($row['code']) . "', ";
				}
				$pak .= "\n";
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		if ($pak != '')
		{
			$db->sql_close();

			header('Content-Type: text/x-delimtext; name="' . $fields . '.pak"');
			header('Content-disposition: attachment; filename=' . $fields . '.pak"');
			echo $pak;
			exit;
		}
		else
		{
			trigger_error($user->lang['NO_' . $fields . '_EXPORT']);
		}
		break;

	case 'move_up':
	case 'move_down':
		$image_order = intval($_GET['order']);
		$order_total = $image_order * 2 + (($action == 'move_up') ? -1 : 1);

		$sql = 'UPDATE ' . $table . '
			SET ' . $fields . "_order = $order_total - " . $fields . '_order
			WHERE ' . $fields . "_order IN ($image_order, " . (($action == 'move_up') ? $image_order - 1 : $image_order + 1) . ')';
		$db->sql_query($sql);

		$cache->destroy('icons');

		// No break; here, display the smilies admin back

	default:

		// By default, check that smile_order is valid and fix it if necessary
		$result = $db->sql_query('SELECT * FROM ' . $table . ' ORDER BY ' . $fields . '_order');
		if ($row = $db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				++$order;
				if ($row[$fields . '_order'] != $order)
				{
					$db->sql_query('UPDATE ' . $table . '
						SET ' . $fields . '_order = ' . $order . ' 
						WHERE ' . $fields . '_id = ' . $row[$fields . '_id']);
				}
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		// Output the page
		page_header($user->lang[$lang]);

?>

<h1><?php echo $user->lang[$lang]; ?></h1>

<p><?php echo $user->lang[$lang .'_EXPLAIN']; ?></p>

<form method="post" action="admin_icons.<?php echo $phpEx . $SID . '&amp;mode=' . $mode ?>"><table cellspacing="1" cellpadding="0" border="0" align="center">
	<tr>
		<td align="right"> &nbsp;&nbsp; <a href="admin_icons.<?php echo $phpEx . $SID . '&amp;mode=' . $mode . '&amp;action=import'; ?>"><?php echo $user->lang['IMPORT_' . $lang]; ?></a> | <a href="admin_icons.<?php echo $phpEx . $SID . '&amp;mode=' . $mode . '&amp;action=export'; ?>"><?php echo $user->lang['EXPORT_' . $lang]; ?></a></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang[$lang]; ?></th>
<?php

	if ($mode == 'emoticons')
	{

?>
				<th><?php echo $user->lang['CODE']; ?></th>
				<th><?php echo $user->lang['EMOTION']; ?></th>
<?php

	}

?>
				<th><?php echo $user->lang['ACTION']; ?></th>
				<th><?php echo $user->lang['REORDER']; ?></th>
			</tr>
<?php

		$spacer = FALSE;

		$sql = 'SELECT * 
			FROM ' . $table . ' 
			ORDER BY display_on_posting DESC, ' . $fields . '_order ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!$spacer && !$row['display_on_posting'])
			{
				$spacer = TRUE;
?>
			<tr>
				<td class="row3" colspan="<?php echo ($mode == 'emoticons') ? 5 : 3; ?>" align="center"><?php echo $user->lang[$lang . '_NOT_DISPLAYED'] ?></td>
			</tr>
<?php
			}

			$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

			$alt_text = ($mode == 'emoticon') ? htmlspecialchars($row['code']) : '';

?>
	<tr>
				<td class="<?php echo $row_class; ?>" align="center"><img src="<?php echo './../' . $img_path . '/' . $row[$fields . '_url']; ?>" width="<?php echo $row[$fields . '_width']; ?>" height="<?php echo $row[$fields . '_height']; ?>" alt="<?php echo $alt_text; ?>" title="<?php echo $alt_text; ?>" /></td>
<?php

	if ($mode == 'emoticons')
	{

?>
				<td class="<?php echo $row_class; ?>" align="center"><?php echo htmlspecialchars($row['code']); ?></td>
				<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['emoticon']; ?></td>
<?php

	}

?>
				<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_icons.$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;id=" . $row[$fields . '_id']; ?>"><?php echo $user->lang['EDIT']; ?></a> | <a href="<?php echo "admin_icons.$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;id=" . $row[$fields . '_id']; ?>"><?php echo $user->lang['DELETE']; ?></a></td>
				<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_icons.$phpEx$SID&amp;mode=$mode&amp;action=move_up&amp;order=" . $row[$fields . '_order']; ?>"><?php echo $user->lang['MOVE_UP']; ?></a> <br /> <a href="<?php echo "admin_icons.$phpEx$SID&amp;mode=$mode&amp;action=move_down&amp;order=" . $row[$fields . '_order']; ?>"><?php echo $user->lang['MOVE_DOWN']; ?></a></td>
			</tr>
<?php

		}
		$db->sql_freeresult($result);

?>
			<tr>
				<td class="cat" colspan="<?php echo ($mode == 'emoticons') ? 5 : 3; ?>" align="center"><input type="submit" name="add" value="<?php echo $user->lang['ADD_' . $lang]; ?>" class="mainoption" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

		page_footer();

		break;
}

// ---------
// FUNCTIONS
//
function filelist($rootdir, $dir = '', $type = 'gif|jpg|png')
{ 
	static $images = array();

	$dh = opendir($rootdir . $dir);

	while ($fname = readdir($dh))
	{
		if (is_file($rootdir . $dir . '/' . $fname) && 
			preg_match('#\.' . $type . '$#i', $fname) &&  
			filesize($rootdir . $dir . '/' . $fname))
		{
			$images[] = array('path' => $dir, 'file' => $fname);
		}
		else if ($fname != '.' && $fname != '..' && 
			!is_file($rootdir . $dir . '/' . $fname) && 
			!is_link($rootdir . $dir . '/' . $fname))
		{
			filelist($rootdir, $dir . '/'. $fname, $type);
		}
	}
	
	closedir($dh);

	return $images;
}
//
// FUNCTIONS
// ---------

?>