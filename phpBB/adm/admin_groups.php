<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_groups.php
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001,2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// TODO
// Avatar gallery ...
// Mass user pref setting via group membership

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_group'))
	{
		return;
	}

	$module['USER']['GROUP_MANAGE'] = basename(__FILE__) . "$SID&amp;mode=manage";

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.'.$phpEx);

// Do we have general permissions?
if (!$auth->acl_get('a_group'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Check and set some common vars
$mode		= request_var('mode', '');
$action		= (isset($_POST['add'])) ? 'add' : ((isset($_POST['addusers'])) ? 'addusers' : request_var('action', ''));
$group_id	= request_var('g', 0);
$mark_ary	= request_var('mark', 0);
$name_ary	= request_var('usernames', '');
$leader		= request_var('leader', 0);
$default	= request_var('default', 0);
$start		= request_var('start', 0);
$update		= (isset($_POST['update'])) ? true : false;
$confirm	= (isset($_POST['confirm'])) ? true : false;
$cancel		= (isset($_POST['cancel'])) ? true : false;

// Clear some vars
$can_upload = (file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && $file_uploads) ? true : false;

$group_type = $group_name = $group_desc = $group_colour = $group_rank = $group_avatar = false;

// Grab basic data for group, if group_id is set and exists
if ($group_id)
{
	$sql = 'SELECT * 
		FROM ' . GROUPS_TABLE . " 
		WHERE group_id = $group_id";
	$result = $db->sql_query($sql);

	if (!extract($db->sql_fetchrow($result)))
	{
		trigger_error($user->lang['NO_GROUP']);
	}
	$db->sql_freeresult($result);
}

switch ($mode)
{
	case 'manage':
		// Page header
		adm_page_header($user->lang['MANAGE']);

		// Common javascript
?>

<script language="Javascript" type="text/javascript">
<!--
function marklist(match, status)
{
	len = eval('document.' + match + '.length');
	for (i = 0; i < len; i++)
	{
		eval('document.' + match + '.elements[i].checked = ' + status);
	}
}

//-->
</script>

<?php

		// Which page?
		switch ($action)
		{
			case 'approve':
			case 'demote':
			case 'promote':
				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP']);
				}

				group_user_attributes($action, $group_id, $mark_ary, false, $group_name);

				switch ($action)
				{
					case 'demote':
						$message = 'GROUP_MODS_DEMOTED';
						break;
					case 'promote':
						$message = 'GROUP_MODS_PROMOTED';
						break;
					case 'approve':
						$message = 'USERS_APPROVED';
						break;
				}
				trigger_error($user->lang[$message]);
				break;

			case 'default':
				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP']);
				}

				if (!$mark_ary)
				{
					$start = 0;
					do
					{
						$sql = 'SELECT user_id 
							FROM ' . USER_GROUP_TABLE . "
							WHERE group_id = $group_id 
							ORDER BY user_id 
							LIMIT $start, 200";
						$result = $db->sql_query($sql);

						$mark_ary = array();
						if ($row = $db->sql_fetchrow($result))
						{
							do
							{
								$mark_ary[] = $row['user_id'];
							}
							while ($row = $db->sql_fetchrow($result));

							group_user_attributes('default', $group_id, $mark_ary, false, $group_name, $group_colour, $group_rank, $group_avatar, $group_avatar_type, $group_avatar_width, $group_avatar_height);

							$start = (sizeof($user_id_ary) < 200) ? 0 : $start + 200;
						}
						else
						{
							$start = 0;
						}
						$db->sql_freeresult($result);
					}
					while ($start);
				}
				else
				{
					group_user_attributes('default', $group_id, $mark_ary, false, $group_name, $group_colour, $group_rank, $group_avatar, $group_avatar_type, $group_avatar_width, $group_avatar_height);
				}

				trigger_error($user->lang['GROUP_DEFS_UPDATED']);
				break;

			case 'deleteusers':
			case 'delete':
				if (!$cancel && !$confirm)
				{
					adm_page_confirm($user->lang['CONFIRM'], $user->lang['CONFIRM_OPERATION']);
				}
				else
				{
					if (!$group_id)
					{
						trigger_error($user->lang['NO_GROUP']);
					}

					switch ($action)
					{
						case 'delete':
							$error = group_delete($group_id, $group_name);
							break;

						case 'deleteusers':
							$error = group_user_del($group_id, $mark_ary, false, $group_name);
							break;
					}

					if ($error)
					{
						trigger_error($user->lang[$error]);
					}

					$message = ($action == 'delete') ? 'GROUP_DELETED' : 'GROUP_USERS_REMOVE';
					trigger_error($user->lang[$message]);
				}
				break;

			case 'addusers':
				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP']);
				}

				if (!$name_ary)
				{
					trigger_error($user->lang['NO_USERS']);
				}

				$name_ary = array_unique(explode("\n", $name_ary));

				// Add user/s to group
				if ($error = group_user_add($group_id, false, $name_ary, $group_name, $default, $leader, $group_colour, $group_rank, $group_avatar, $group_avatar_type, $group_avatar_width, $group_avatar_height))
				{
					trigger_error($user->lang[$error]);
				}

				$message = ($action == 'addleaders') ? 'GROUP_MODS_ADDED' : 'GROUP_USERS_ADDED';
				trigger_error($user->lang[$message]);
				break;

			case 'edit':
			case 'add':

				if ($action == 'edit' && !$group_id)
				{
					trigger_error($user->lang['NO_GROUP']);
				}

				$name	= request_var('group_name', '');
				$desc	= request_var('group_description', '');
				$type	= request_var('group_type', 0);

				$colour	= request_var('group_colour', '');
				$rank	= request_var('group_rank', 0);

				$data['uploadurl']	= request_var('uploadurl', '');
				$data['remotelink'] = request_var('remotelink', '');
				$delete				= request_var('delete', '');

				if (!empty($_FILES['uploadfile']['tmp_name']) || $data['uploadurl'] || $data['remotelink'])
				{
					$data['width']		= request_var('width', '');
					$data['height']		= request_var('height', '');

					// Avatar stuff
					$var_ary = array(
						'uploadurl'		=> array('string', true, 5, 255), 
						'remotelink'	=> array('string', true, 5, 255), 
						'width'			=> array('string', true, 1, 3), 
						'height'		=> array('string', true, 1, 3), 
					);

					if (!($error = validate_data($data, $var_ary)))
					{
						$data['user_id'] = "g$group_id";

						if ((!empty($_FILES['uploadfile']['tmp_name']) || $data['uploadurl']) && $can_upload)
						{
							list($avatar_type, $avatar, $avatar_width, $avatar_height) = avatar_upload($data, $error);
						}
						else if ($data['remotelink'])
						{
							list($avatar_type, $avatar, $avatar_width, $avatar_height) = avatar_remote($data, $error);
						}
					}
				}
				else if ($delete)
				{
					$avatar = '';
					$avatar_type = $avatar_width = $avatar_height = 0;
				}

				// Did we submit?
				if ($update)
				{
					if (($avatar && $group_avatar != $avatar) || $delete)
					{
						avatar_delete($group_avatar);
					}

					// Only set the rank, colour, etc. if it's changed or if we're adding a new
					// group. This prevents existing group members being updated if no changes 
					// were made.
					foreach (array('name', 'desc', 'type', 'rank', 'colour', 'avatar', 'avatar_type', 'avatar_width', 'avatar_height') as $test)
					{
						${'group_' . $test} = ($action == 'add' || (isset($$test) && $$test != ${'group_' . $test})) ? $$test : false;
					}

					if (!($error = group_create($group_id, $group_type, $group_name, $group_description, $group_colour, $group_rank, $group_avatar, $group_avatar_type, $group_avatar_width, $group_avatar_height)))
					{
						$message = ($action == 'edit') ? 'GROUP_UPDATED' : 'GROUP_CREATED';
						trigger_error($message);
					}
				}
				else if (!$group_id)
				{
					$group_name = request_var('group_name', '');
					$group_description = $group_colour = $group_avatar = '';
					$group_type = GROUP_FREE;
				}

?>

<h1><?php echo $user->lang['MANAGE']; ?></h1>

<p><?php echo $user->lang['GROUP_EDIT_EXPLAIN']; ?></p>

<?php 

				$sql = 'SELECT * 
					FROM ' . RANKS_TABLE . '
					WHERE rank_special = 1
					ORDER BY rank_title';
				$result = $db->sql_query($sql);

				$rank_options = '<option value="-1"' . ((empty($group_rank)) ? 'selected="selected" ' : '') . '>' . $user->lang['USER_DEFAULT'] . '</option>';
				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$selected = (!empty($group_rank) && $row['rank_id'] == $group_rank) ? ' selected="selected"' : '';
						$rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);

				$type_free		= ($group_type == GROUP_FREE) ? ' checked="checked"' : '';
				$type_open		= ($group_type == GROUP_OPEN) ? ' checked="checked"' : '';
				$type_closed	= ($group_type == GROUP_CLOSED) ? ' checked="checked"' : '';
				$type_hidden	= ($group_type == GROUP_HIDDEN) ? ' checked="checked"' : '';

				if ($group_avatar)
				{
					switch ($group_avatar_type)
					{
						case AVATAR_UPLOAD:
							$avatar_img = $phpbb_root_path . $config['avatar_path'] . '/';
							break;
						case AVATAR_GALLERY:
							$avatar_img = $phpbb_root_path . $config['avatar_gallery_path'] . '/';
							break;
					}
					$avatar_img .= $group_avatar;

					$avatar_img = '<img src="' . $avatar_img . '" width="' . $group_avatar_width . '" height="' . $group_avatar_height . '" border="0" alt="" />';
				}
				else
				{
					$avatar_img = '<img src="images/no_avatar.gif" alt="" />';
				}

?>

<script language="javascript" type="text/javascript">
<!--

function swatch()
{
	window.open('./swatch.<?php echo $phpEx; ?>?form=settings&amp;name=group_colour', '_swatch', 'HEIGHT=115,resizable=yes,scrollbars=no,WIDTH=636');
	return false;
}

//-->
</script>

<form name="settings" method="post" action="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;g=$group_id"; ?>"<?php echo ($can_upload) ? ' enctype="multipart/form-data"' : ''; ?>><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_DETAILS']; ?></th>
	</tr>
<?php

				if (sizeof($error))
				{

?>
	<tr>
		<td class="row1" colspan="2" align="center"><span style="color:red"><?php echo implode('<br />', $error); ?></span></td>
	</tr>
<?php

				}

?>
	<tr>
		<td class="row2" width="40%"><b><?php echo $user->lang['GROUP_NAME']; ?>:</b></td>
		<td class="row1"><?php 
	
				if ($group_type != GROUP_SPECIAL)
				{
		
?><input class="post" type="text" name="group_name" value="<?php echo (!empty($group_name)) ? $group_name : ''; ?>" size="40" maxlength="40" /><?php
			
				}
				else
				{
				
?><b><?php echo ($group_type == GROUP_SPECIAL) ? $user->lang['G_' . $group_name] : $group_name; ?></b><?php
	
				}
	
?></td>
	</tr>
	<tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_DESC']; ?>:</b></td>
		<td class="row1"><input class="post" type="text" name="group_description" value="<?php echo (!empty($group_description)) ? $group_description : ''; ?>" size="40" maxlength="255" /></td>
	</tr>
<?php

				if ($group_type != GROUP_SPECIAL)
				{

?>
	<tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_TYPE']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['GROUP_TYPE_EXPLAIN']; ?></span></td>
		<td class="row1" nowrap="nowrap"><input type="radio" name="group_type" value="<?php echo GROUP_FREE . '"' . $type_free; ?> /> <?php echo $user->lang['GROUP_OPEN']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_OPEN . '"' . $type_open; ?> /> <?php echo $user->lang['GROUP_REQUEST']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_CLOSED . '"' . $type_closed; ?> /> <?php echo $user->lang['GROUP_CLOSED']; ?> &nbsp; <input type="radio" name="group_type" value="<?php echo GROUP_HIDDEN . '"' . $type_hidden; ?>" /> <?php echo $user->lang['GROUP_HIDDEN']; ?></td>
	</tr>
<?php

				}

?>
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_SETTINGS_SAVE']; ?></th>
	</tr>
	<tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_COLOR']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['GROUP_COLOR_EXPLAIN']; ?></span></td>
		<td class="row1" nowrap="nowrap"><input class="post" type="text" name="group_colour" value="<?php echo (!empty($group_colour)) ? $group_colour : ''; ?>" size="6" maxlength="6" /> &nbsp; [ <a href="<?php echo "swatch.$phpEx"; ?>" onclick="swatch();return false" target="_swatch"><?php echo $user->lang['COLOUR_SWATCH']; ?></a> ]</td>
	</tr>
	<tr>
		<td class="row2"><b><?php echo $user->lang['GROUP_RANK']; ?>:</b></td>
		<td class="row1"><select name="group_rank"><?php echo $rank_options; ?></select></td>
	</tr>
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_AVATAR']; ?></th>
	</tr>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['CURRENT_IMAGE']; ?>: </b><br /><span class="gensmall"><?php echo sprintf($user->lang['AVATAR_EXPLAIN'], $config['avatar_max_width'], $config['avatar_max_height'], round($config['avatar_filesize'] / 1024)); ?></span></td>
		<td class="row1" align="center"><br /><?php echo $avatar_img; ?><br /><br /><input type="checkbox" name="delete" />&nbsp;<span class="gensmall"><?php echo $user->lang['DELETE_AVATAR']; ?></span></td>
	</tr>
<?php

			// Can we upload?
			if ($can_upload)
			{

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['UPLOAD_AVATAR_FILE']; ?>: </b></td>
		<td class="row1"><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $config['avatar_max_filesize']; ?>" /><input class="post" type="file" name="uploadfile" /></td>
	</tr>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['UPLOAD_AVATAR_URL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['UPLOAD_AVATAR_URL_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="uploadurl" size="40" value="<?php echo $avatar_url; ?>" /></td>
	</tr>
<?php

			}

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['LINK_REMOTE_AVATAR']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LINK_REMOTE_AVATAR_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="remotelink" size="40" value="<?php echo $avatar_url; ?>" /></td>
	</tr>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['LINK_REMOTE_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LINK_REMOTE_SIZE_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="width" size="3" value="<?php echo $group_avatar_width; ?>" /> <span class="gen">px X </span> <input class="post" type="text" name="height" size="3" value="<?php echo $group_avatar_height; ?>" /> <span class="gen">px</span></td>
	</tr>
<?php

			// Do we have a gallery?
			if ($config['null'] && !$display_gallery)
			{

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['AVATAR_GALLERY']; ?>: </b></td>
		<td class="row1"><input class="btnlite" type="submit" name="displaygallery" value="<?php echo $user->lang['DISPLAY_GALLERY']; ?>" /></td>
	</tr>
<?php
			}

			// Do we want to display it?
			if ($config['null'] && $display_gallery)
			{

?>
	<tr> 
		<th colspan="2"><?php echo $user->lang['AVATAR_GALLERY']; ?></th>
	</tr>
	<tr> 
		<td class="cat" colspan="2" align="center" valign="middle"><span class="genmed"><?php echo $user->lang['AVATAR_CATEGORY']; ?>: </span><select name="avatarcat">{S_CAT_OPTIONS}</select>&nbsp; <span class="genmed"><?php echo $user->lang['AVATAR_PAGE']; ?>: </span><select name="avatarpage">{S_PAGE_OPTIONS}</select>&nbsp;<input class="btnlite" type="submit" value="<?php echo $user->lang['GO']; ?>" name="avatargallery" /></td>
	</tr>
	<tr> 
		<td class="row1" colspan="2" align="center"><table cellspacing="1" cellpadding="4" border="0">
		
			<!-- BEGIN avatar_row -->
			<tr> 
				<!-- BEGIN avatar_column -->
				<td class="row1" align="center"><img src="{avatar_row.avatar_column.AVATAR_IMAGE}" alt="{avatar_row.avatar_column.AVATAR_NAME}" title="{avatar_row.avatar_column.AVATAR_NAME}" /></td>
				<!-- END avatar_column -->
			</tr>
			<tr>
				<!-- BEGIN avatar_option_column -->
				<td class="row2" align="center"><input type="radio" name="avatarselect" value="{avatar_row.avatar_option_column.S_OPTIONS_AVATAR}" /></td>
				<!-- END avatar_option_column -->
			</tr>
			<!-- END avatar_row -->

		</table></td>
	</tr>
<?php

			}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>
<?php

				adm_page_footer();
				break;
		}

		if ($mode == 'list' || $group_id)
		{
			if (!$group_id)
			{
				trigger_error($user->lang['NO_GROUP']);
			}

?>

<h1><?php echo $user->lang['GROUP_MEMBERS']; ?></h1>

<p><?php echo $user->lang['GROUP_MEMBERS_EXPLAIN']; ?></p>

<form name="list" method="post" action="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode&amp;g=$group_id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="55%"><?php echo $user->lang['USERNAME']; ?></th>
		<th width="3%" nowrap="nowrap"><?php echo $user->lang['DEFAULT']; ?></th>
		<th width="20%"><?php echo $user->lang['JOINED']; ?></th>
		<th width="20%"><?php echo $user->lang['POSTS']; ?></th>
		<th width="2%"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php

				// Total number of group leaders
				$sql = 'SELECT COUNT(user_id) AS total_leaders 
					FROM ' . USER_GROUP_TABLE . " 
					WHERE group_id = $group_id 
						AND group_leader = 1";
				$result = $db->sql_query($sql);

				$total_leaders = ($row = $db->sql_fetchrow($result)) ? $row['total_leaders'] : 0;
				$db->sql_freeresult($result);

				// Total number of group members (non-leaders)
				$sql = 'SELECT COUNT(user_id) AS total_members 
					FROM ' . USER_GROUP_TABLE . " 
					WHERE group_id = $group_id 
						AND group_leader <> 1";
				$result = $db->sql_query($sql);

				$total_members = ($row = $db->sql_fetchrow($result)) ? $row['total_members'] : 0;
				$db->sql_freeresult($result);

				// Grab the members
				$sql = 'SELECT u.user_id, u.username, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending 
					FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug 
					WHERE ug.group_id = $group_id 
						AND u.user_id = ug.user_id 
					ORDER BY ug.group_leader DESC, ug.user_pending DESC, u.username 
					LIMIT $start, " . $config['topics_per_page'];
				$result = $db->sql_query($sql);

				$leader = $member = 0;
				$group_data = array();
				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$type = ($row['group_leader']) ? 'leader' : 'member';

						$group_data[$type][$$type]['user_id'] = $row['user_id'];
						$group_data[$type][$$type]['group_id'] = $row['group_id'];
						$group_data[$type][$$type]['username'] = $row['username'];
						$group_data[$type][$$type]['user_regdate'] = $row['user_regdate'];
						$group_data[$type][$$type]['user_posts'] = $row['user_posts'];
						$group_data[$type][$$type]['user_pending'] = ($row['user_pending']) ? 1 : 0;

						$$type++;
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);

				if ($group_type != GROUP_SPECIAL)
				{

?>
	<tr>
		<td class="row3" colspan="5"><b><?php echo $user->lang['GROUP_LEAD']; ?></b></td>
	</tr>
<?php

					if (sizeof($group_data['leader']))
					{
						foreach ($group_data['leader'] as $row)
						{
							$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><a href="<?php echo "admin_users.$phpEx$SID&amp;mode=edit&amp;u=" . $row['user_id']; ?>"><?php echo $row['username']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo ($row['group_id'] == $group_id) ? $user->lang['YES'] : $user->lang['NO']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']); ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['user_posts']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input class="checkbox" type="checkbox" name="mark[]" value="<?php echo $row['user_id']; ?>" /></td>
	</tr>
<?php	

						}
					}
					else
					{

?>
	<tr>
		<td class="row1" colspan="5" align="center"><?php echo $user->lang['GROUPS_NO_MODS']; ?></td>
	</tr>
<?php

					}
				}

?>
	<tr>
		<td class="row3" colspan="5"><b><?php echo $user->lang['GROUP_APPROVED']; ?></b></td>
	</tr>
<?php
				if (sizeof($group_data['member']))
				{
					$pending = $group_data['member'][0]['user_pending'];

					foreach ($group_data['member'] as $row)
					{
						if ($pending)
						{

?>
	<tr>
		<td class="row3" colspan="5"><b><?php echo $user->lang['GROUP_PENDING']; ?></b></td>
	</tr>
<?php

						}

						$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><a href="<?php echo "admin_users.$phpEx$SID&amp;mode=edit&amp;u=" . $row['user_id']; ?>"><?php echo $row['username']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo ($row['group_id'] == $group_id) ? $user->lang['YES'] : $user->lang['NO']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo ($row['user_regdate']) ? $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']) : '-'; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['user_posts']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><input class="checkbox" type="checkbox" name="mark[]" value="<?php echo $row['user_id']; ?>" /></td>
	</tr>
<?php

					}
				}
				else
				{

?>
	<tr>
		<td class="row1" colspan="5" align="center"><?php echo $user->lang['GROUPS_NO_MEMBERS']; ?></td>
	</tr>
<?php

				}

?>
	<tr>
		<td class="cat" colspan="5" align="right"><select name="action"><option class="sep" value=""><?php echo $user->lang['SELECT_OPTION']; ?></option><?php

				foreach(array('default' => 'DEFAULT', 'approve' => 'APPROVE', 'demote' => 'DEMOTE', 'promote' => 'PROMOTE', 'deleteusers' => 'DELETE') as $option => $lang)
				{
					echo '<option value="' . $option . '">' . $user->lang['GROUP_' . $lang] . '</option>';
				}

?></select> <input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;</td>
	</tr>
</table>

<table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td valign="top"><?php echo on_page($total_members, $config['topics_per_page'], $start); ?></td>
		<td align="right"><b><span class="gensmall"><a href="javascript:marklist('list', true);" class="gensmall"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('list', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></span></b>&nbsp;<br /><span class="nav"><?php echo generate_pagination("admin_groups.$phpEx$SID&amp;action=list&amp;mode=member&amp;g=$group_id", $total_members, $config['topics_per_page'], $start); ?></span></td>
	</tr>
</table>


<h1><?php echo $user->lang['ADD_USERS']; ?></h1>

<p><?php echo $user->lang['ADD_USERS_EXPLAIN']; ?></p>

<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['ADD_USERS']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['USER_GROUP_LEADER']; ?>:</b></span></td>
		<td class="row2"><input type="radio" name="leader" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="leader" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['USER_GROUP_DEFAULT']; ?>:</b> <br /><span class="gensmall"><?php echo $user->lang['USER_GROUP_DEFAULT_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="default" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="default" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['USERNAME']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['USERNAMES_EXPLAIN']; ?><br />[ <a href="<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=mod&amp;field=usernames"; ?>"><?php echo $user->lang['FIND_USERNAME']; ?></a> ]</span></td>
		<td class="row2"><textarea name="usernames" cols="40" rows="5"></textarea></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="addusers" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table>

</form>

<?php

			adm_page_footer();
		}


?>

<h1><?php echo $user->lang['MANAGE']; ?></h1>

<p><?php echo $user->lang['GROUP_MANAGE_EXPLAIN']; ?></p>

<h1><?php echo $user->lang['USER_DEF_GROUPS']; ?></h1>

<p><?php echo $user->lang['USER_DEF_GROUPS_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="95%"><?php echo $user->lang['MANAGE']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['TOTAL_MEMBERS']; ?></th>
		<th colspan="3"><?php echo $user->lang['OPTIONS']; ?></th>
	</tr>
<?php

				$sql = 'SELECT g.group_id, g.group_name, g.group_type, COUNT(ug.user_id) AS total_members 
					FROM (' . GROUPS_TABLE . ' g
					LEFT JOIN ' . USER_GROUP_TABLE . ' ug USING (group_id)) 
					GROUP BY g.group_id 
					ORDER BY g.group_type ASC, g.group_name';
				$result = $db->sql_query($sql);

				$special = $normal = 0;
				$group_ary = array();
				while ($row = $db->sql_fetchrow($result) )
				{
					$type = ($row['group_type'] == GROUP_SPECIAL) ? 'special' : 'normal';

					$group_ary[$type][$$type]['group_id'] = $row['group_id'];
					$group_ary[$type][$$type]['group_name'] = $row['group_name'];
					$group_ary[$type][$$type]['group_type'] = $row['group_type'];
					$group_ary[$type][$$type]['total_members'] = $row['total_members'];

					$$type++;
				}
				$db->sql_freeresult($result);

				$special_toggle = false;
				foreach ($group_ary as $type => $row_ary)
				{
					if ($type == 'special')
					{

?>
	<tr>
		<td class="cat" colspan="5" align="right"><?php echo $user->lang['CREATE_GROUP']; ?>: <input class="post" type="text" name="group_name" maxlength="30" /> <input class="btnmain" type="submit" name="add" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table>

<h1><?php echo $user->lang['SPECIAL_GROUPS']; ?></h1>

<p><?php echo $user->lang['SPECIAL_GROUPS_EXPLAIN']; ?></p>

<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="95%"><?php echo $user->lang['MANAGE']; ?></th>
		<th><?php echo $user->lang['TOTAL_MEMBERS']; ?></th>
		<th colspan="3"><?php echo $user->lang['OPTIONS']; ?></th>
	</tr>
<?php

					}

					foreach ($row_ary as $row)
					{
						$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

						$group_id = $row['group_id'];
						$group_name = (!empty($user->lang['G_' . $row['group_name']]))? $user->lang['G_' . $row['group_name']] : $row['group_name'];

?>
	<tr>
		<td width="95%" class="<?php echo $row_class; ?>"><a href="admin_groups.<?php echo "$phpEx$SID&amp;mode=$mode&amp;action=list&amp;g=$group_id"; ?>"><?php echo $group_name;?></a></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<?php echo $row['total_members']; ?>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode&amp;action=default&amp;g=$group_id"; ?>">Default<?php echo $user->lang['']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_groups.$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;g=$group_id"; ?>"><?php echo $user->lang['EDIT']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<?php 
	
						echo ($row['group_type'] != GROUP_SPECIAL) ? "<a href=\"admin_groups.$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;g=$group_id\">" . $user->lang['DELETE'] . '</a>' : $user->lang['DELETE'];

?>&nbsp;</td>
	</tr>
<?php

					}
				}

?>
	<tr>
		<td class="cat" colspan="5">&nbsp;</td>
	</tr>
</table></form>

<?php

		adm_page_footer();
		break;

	// Setting groupwide preferences
	case 'prefs':
		adm_page_header($user->lang['GROUP_PREFS']);

		if ($update)
		{
			$user_lang	= request_var('lang', '');
			$user_tz	= request_var('tz', 0.0);
			$user_dst	= request_var('dst', 0);
		}
		else
		{
		}

?>
<h1><?php echo $user->lang['GROUP_SETTINGS']; ?></h1>

<p><?php echo $user->lang['GROUP_SETTINGS_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_groups.$phpEx$SID&amp;action=edit&amp;g=$group_id"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['GROUP_SETTINGS']; ?></th>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_LANG']; ?>:</td>
		<td class="row1"><select name="user_lang"><?php echo '<option value="-1" selected="selected">' . $user->lang['USER_DEFAULT'] . '</option>' . language_select(); ?></select></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_TIMEZONE']; ?>:</td>
		<td class="row1"><select name="user_tz"><?php echo '<option value="-14" selected="selected">' . $user->lang['USER_DEFAULT'] . '</option>' . tz_select(); ?></select></td>
	</tr>
	<tr>
		<td class="row2"><?php echo $user->lang['GROUP_DST']; ?>:</td>
		<td class="row1" nowrap="nowrap"><input type="radio" name="user_dst" value="0" /> <?php echo $user->lang['DISABLED']; ?> &nbsp; <input type="radio" name="user_dst" value="1" /> <?php echo $user->lang['ENABLED']; ?> &nbsp; <input type="radio" name="user_dst" value="-1" checked="checked" /> <?php echo $user->lang['USER_DEFAULT']; ?></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="submitprefs" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<?php

		adm_page_footer();
		break;

	default:
		trigger_error($user->lang['NO_MODE']);
}

exit;

?>