<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_permissions.php
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['PERM']['PERMISSIONS'] = ($auth->acl_get('a_auth')) ? "$filename$SID&amp;mode=forum" : '';
	$module['PERM']['MODERATORS'] = ($auth->acl_get('a_authmods')) ? "$filename$SID&amp;mode=mod" : '';
	$module['PERM']['SUPER_MODERATORS'] = ($auth->acl_get('a_authmods')) ? "$filename$SID&amp;mode=supermod" : '';
	$module['PERM']['ADMINISTRATORS'] = ($auth->acl_get('a_authadmins')) ? "$filename$SID&amp;mode=admin" : '';
	$module['PERM']['USER_PERMS'] = ($auth->acl_get('a_authusers')) ? "$filename$SID&amp;mode=user" : '';
	$module['PERM']['GROUP_PERMS'] = ($auth->acl_get('a_authgroups')) ? "$filename$SID&amp;mode=group" : '';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);


// Grab and set some basic parameters
//
// 'mode' determines what we're altering; administrators, users, deps, etc.
// 'submit' is used to determine what we're doing ... special format
$mode		= (isset($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';
$submode	= (isset($_REQUEST['submode'])) ? htmlspecialchars($_REQUEST['submode']) : '';
$which_mode = (!empty($submode) && $submode != $mode) ? $submode : $mode;
$submit		= array_values(preg_grep('#^submit_(.*)$#i', array_keys($_REQUEST)));
$submit		= (sizeof($submit)) ? substr($submit[0], strpos($submit[0], '_') + 1) : '';


// Submitted setting data
//
// 'auth_settings' contains the submitted option settings assigned to options, should be an 
//   associative array with integer values
$auth_settings = (isset($_POST['settings'])) ? $_POST['settings'] : '';


// Forum, User or Group information
//
// 'ug_type' is either user or groups used mainly for forum/admin/mod permissions
// 'ug_data' contains the list of usernames, user_id's or group_ids for the 'ug_type'
// 'forum_id' contains the list of forums, 0 is used for "All forums", can be array or scalar
$ug_type = (isset($_REQUEST['ug_type'])) ? htmlspecialchars($_REQUEST['ug_type']) : '';
$ug_data = (isset($_POST['ug_data'])) ? $_POST['ug_data'] : '';

if (isset($_REQUEST['f']))
{
	$forum_id = (is_array($_REQUEST['f'])) ? $_REQUEST['f'] : intval($_REQUEST['f']);
}

if (!isset($forum_id[$which_mode]))
{
	$forum_id[$which_mode][] = 0;
}
$sql_forum_id = implode(', ', array_map('intval', $forum_id[$which_mode]));

// Generate list of forum id's
$s_forum_id = '';
foreach ($forum_id as $forum_submode => $forum_submode_ids)
{
	foreach ($forum_submode_ids as $submode_forum_id)
	{
		$s_forum_id .= '<input type="hidden" name="f[' . $forum_submode . '][]" value="' . $submode_forum_id . '" />';
	}
}
unset($forum_submode_ids);
unset($forum_submode);
unset($submode_forum_id);


// Instantiate a new auth admin object in readiness
$auth_admin = new auth_admin();


// What mode are we running? So we can output the correct title, explanation
// and set the sql_option_mode/acl check
switch ($mode)
{
	case 'forum':
		$l_title = $user->lang['PERMISSIONS'];
		$l_title_explain = $user->lang['PERMISSIONS_EXPLAIN'];
		$which_acl = 'a_auth';
		$sql_option_mode = 'f';
		break;

	case 'mod':
		$l_title = $user->lang['MODERATORS'];
		$l_title_explain = $user->lang['MODERATORS_EXPLAIN'];
		$which_acl = 'a_authmods';
		$sql_option_mode = 'm';
		break;

	case 'supermod':
		$l_title = $user->lang['SUPER_MODERATORS'];
		$l_title_explain = $user->lang['SUPER_MODERATORS_EXPLAIN'];
		$which_acl = 'a_authmods';
		$sql_option_mode = 'm';
		break;

	case 'admin':
		$l_title = $user->lang['ADMINISTRATORS'];
		$l_title_explain = $user->lang['ADMINISTRATORS_EXPLAIN'];
		$which_acl = 'a_authadmins';
		$sql_option_mode = 'a';
		break;

	case 'user':
		$l_title = $user->lang['USER_PERMISSIONS'];
		$l_title_explain = $user->lang['USER_PERMISSIONS_EXPLAIN'];
		$which_acl = 'a_authusers';
		$sql_option_mode = 'u';
		break;

	case 'group':
		$l_title = $user->lang['GROUP_PERMISSIONS'];
		$l_title_explain = $user->lang['GROUP_PERMISSIONS_EXPLAIN'];
		$which_acl = 'a_authgroups';
		$sql_option_mode = 'u';
		break;

	case 'deps':
		$l_title = $user->lang['DEPENDENCIES'];
		$l_title_explain = $user->lang['DEPENDENCIES_EXPLAIN'];
		$which_acl = 'a_authdeps';
		break;
}

// Permission check
if (!$auth->acl_get($which_acl))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Are we setting deps? If we are we need to re-run the mode match above for the
// relevant 'new' mode
if (!empty($submode))
{
	switch ($submode)
	{
		case 'forum':
			$l_title_explain = $user->lang['PERMISSIONS_EXPLAIN'];
			$which_acl = 'a_auth';
			$sql_option_mode = 'f';
			break;

		case 'mod':
			$l_title_explain = $user->lang['MODERATORS_EXPLAIN'];
			$which_acl = 'a_authmods';
			$sql_option_mode = 'm';
			break;

		case 'supermod':
			$l_title_explain = $user->lang['SUPER_MODERATORS_EXPLAIN'];
			$which_acl = 'a_authmods';
			$sql_option_mode = 'm';
			break;
	}

	// Permission check
	if (!$auth->acl_get($which_acl))
	{
		trigger_error($user->lang['NO_ADMIN']);
	}
}


// Does user want to update anything? Check here to find out 
// and act appropriately
switch ($submit)
{
	case 'update':

		if (sizeof($auth_settings))
		{
			// Admin wants subforums to inherit permissions ... so add these
			// forums to the list ... since inheritance is only available for
			// forum and moderator primary modes we deal with '$forum_id[$mode]'
			if (!empty($_POST['inherit']))
			{
				$forum_id[$mode] = array_merge($forum_id[$mode], array_map('intval', $_POST['inherit']));
			}

			// Update the permission set ... we loop through each auth setting array
			foreach ($auth_settings as $auth_submode => $auth_setting)
			{
				// Are any entries * ? If so we need to remove them since they
				// are options the user wishes to ignore
				if (in_array('*', $auth_setting))
				{
					$temp = array();
					foreach ($auth_setting as $option => $setting)
					{
						if ($setting != '*')
						{
							$temp[$option] = $setting;
						}
					}
					$auth_setting = $temp;
				}

				if (sizeof($auth_setting))
				{
					// Loop through all user/group ids
					foreach ($ug_data as $id)
					{
						$auth_admin->acl_set($ug_type, $forum_id[$auth_submode], intval($id), $auth_setting);
					}
				}
			}


			// Do we need to recache the moderator lists? We do if the mode
			// was mod or auth_settings['mod'] is a non-zero size array
			if ($mode == 'mod' || sizeof($auth_settings['mod']))
			{
				cache_moderators();
			}

			// Remove users who are now moderators or admins from everyones foes
			// list
			if ($mode == 'mod' || sizeof($auth_settings['mod']) || $mode == 'admin' || sizeof($auth_settings['admin']))
			{
				update_foes();
			}

			// Logging ... first grab user or groupnames ...
			$sql = ($ug_type == 'group') ? 'SELECT group_name as name, group_type FROM ' . GROUPS_TABLE . ' WHERE group_id' : 'SELECT username as name FROM ' . USERS_TABLE . ' WHERE user_id';
			$sql .=  ' IN (' . implode(', ', array_map('intval', $ug_data)) . ')';
			$result = $db->sql_query($sql);

			$l_ug_list = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$l_ug_list .= (($l_ug_list != '') ? ', ' : '') . ((isset($row['group_type']) && $row['group_type'] == GROUP_SPECIAL) ? '<span class="blue">' . $user->lang['G_' . $row['name']] . '</span>' : $row['name']);
			}
			$db->sql_freeresult($result);

			foreach (array_keys($auth_settings) as $submode)
			{
				if (!in_array(0, $forum_id[$submode]))
				{
					// Grab the forum details if non-zero forum_id
					$sql = 'SELECT forum_name  
						FROM ' . FORUMS_TABLE . "
						WHERE forum_id IN ($sql_forum_id)";
					$result = $db->sql_query($sql);

					$l_forum_list = '';
					while ($row = $db->sql_fetchrow($result))
					{
						$l_forum_list .= (($l_forum_list != '') ? ', ' : '') . $row['forum_name'];
					}
					$db->sql_freeresult($result);

					add_log('admin', 'LOG_ACL_' . strtoupper($submode) . '_ADD', $l_forum_list, $l_ug_list);
				}
				else
				{
					add_log('admin', 'LOG_ACL_' . strtoupper($submode) . '_ADD', $l_ug_list);
				}
			}
			unset($l_ug_list);
		}
		unset($auth_submode);
		unset($auth_setting);

		trigger_error($user->lang['AUTH_UPDATED']);
		break;

	case 'delete':

		$sql = "SELECT auth_option_id
			FROM " . ACL_OPTIONS_TABLE . "
			WHERE auth_option LIKE '{$sql_option_mode}_%'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$option_id_ary = array();
			do
			{
				$option_id_ary[] = $row['auth_option_id'];
			}
			while($row = $db->sql_fetchrow($result));

			foreach ($ug_data as $id)
			{
				$auth_admin->acl_delete($ug_type, $forum_id[$mode], $id, $option_id_ary);
			}
			unset($option_id_ary);
		}
		$db->sql_freeresult($result);


		// Do we need to recache the moderator lists? We do if the mode
		// was mod or auth_settings['mod'] is a non-zero size array
		if ($mode == 'mod' || (isset($auth_settings['mod']) && sizeof($auth_settings['mod'])))
		{
			cache_moderators();
		}


		// Logging ... first grab user or groupnames ...
		$sql = ($ug_type == 'group') ? 'SELECT group_name as name, group_type FROM ' . GROUPS_TABLE . ' WHERE group_id' : 'SELECT username as name FROM ' . USERS_TABLE . ' WHERE user_id';
		$sql .=  ' IN (' . implode(', ', array_map('intval', $ug_data)) . ')';
		$result = $db->sql_query($sql);

		$l_ug_list = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$l_ug_list .= (($l_ug_list != '') ? ', ' : '') . ((isset($row['group_type']) && $row['group_type'] == GROUP_SPECIAL) ? '<span class="blue">' . $user->lang['G_' . $row['name']] . '</span>' : $row['name']);
		}
		$db->sql_freeresult($result);


		// Grab the forum details if non-zero forum_id
		if (!in_array(0, $forum_id[$which_mode]))
		{
			$sql = 'SELECT forum_name  
				FROM ' . FORUMS_TABLE . "
				WHERE forum_id IN ($sql_forum_id)";
			$result = $db->sql_query($sql);

			$l_forum_list = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$l_forum_list .= (($l_forum_list != '') ? ', ' : '') . $row['forum_name'];
			}
			$db->sql_freeresult($result);

			add_log('admin', 'LOG_ACL_' . strtoupper($which_mode) . '_DEL', $l_forum_list, $l_ug_list);
		}
		else
		{
			add_log('admin', 'LOG_ACL_' . strtoupper($which_mode) . '_DEL', $l_ug_list);
		}

		trigger_error($user->lang['AUTH_UPDATED']);
		break;

	case 'presetsave':

		$holding_ary = array();
		foreach ($auth_settings as $option => $setting)
		{
			switch ($setting)
			{
				case ACL_YES:
					$holding_ary['yes'][] = $option;
					break;

				case ACL_NO:
					$holding_ary['no'][] = $option;
					break;

				case ACL_UNSET:
					$holding_ary['unset'][] = $option;
					break;
			}
		}
		unset($option);
		unset($setting);

		$sql = array(
			'preset_user_id'=> intval($user->data['user_id']),
			'preset_type'	=> $sql_option_mode,
			'preset_data'	=> $db->sql_escape(serialize($holding_ary))
		);

		if (!empty($_POST['presetname']))
		{
			$sql['preset_name'] = $db->sql_escape($_POST['presetname']);
		}
		
		if (!empty($_POST['presetname']) || $_POST['presetoption'] != -1)
		{
			$sql = ($_POST['presetoption'] == -1) ? 'INSERT INTO ' . ACL_PRESETS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql) : 'UPDATE ' . ACL_PRESETS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql) . ' WHERE preset_id =' . intval($_POST['presetoption']);
			$db->sql_query($sql);

			add_log('admin', 'LOG_ACL_PRESET_ADD', $sql['preset_name']);
		}
		break;

	case 'presetdel':
		if (!empty($_POST['presetoption']))
		{
			$sql = "SELECT preset_name 
				FROM " . ACL_PRESETS_TABLE . " 
				WHERE preset_id = " . intval($_POST['presetoption']);
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$sql = "DELETE FROM " . ACL_PRESETS_TABLE . " 
				WHERE preset_id = " . intval($_POST['presetoption']);
			$db->sql_query($sql);

			add_log('admin', 'LOG_ACL_PRESET_DEL', $row['preset_name']);
			unset($row);
		}
		break;
}
// End update


// Output page header
adm_page_header($l_title);


// First potential form ... this is for selecting forums, users
// or groups. 
if (in_array($mode, array('user', 'group', 'forum', 'mod')) && empty($submit))
{

?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain ?></p>

<form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

	// Mode specific markup
	switch ($mode)
	{
		case 'forum':
		case 'mod':

?>
	<tr>
		<th align="center"><?php echo $user->lang['LOOK_UP_FORUM']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center" valign="middle">&nbsp;<select name="f[<?php echo $mode; ?>][]" multiple="true" size="5"><?php 
	
			echo make_forum_select(false, false, false);
			
?></select>&nbsp;</td>
	</tr>
	<tr>
		<td class="cat" align="center"><input type="submit" name="submit_usergroups" value="<?php echo $user->lang['LOOK_UP_FORUM']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /><input type="hidden" name="ug_type" value="forum" /><input type="hidden" name="action" value="usergroups" /></td>
	</tr>
<?php
		
			break;

		case 'user':

?>
	<tr>
		<th align="center"><?php echo $user->lang['LOOK_UP_USER']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center">&nbsp;<textarea cols="40" rows="4" name="ug_data[]"></textarea>&nbsp;</td>
	</tr>
	<tr>
		<td class="cat" align="center"><input type="submit" name="submit_add_options" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" />&nbsp; <input type="submit" value="<?php echo $user->lang['FIND_USERNAME']; ?>" class="btnlite" onclick="window.open('<?php echo "../memberlist.$phpEx$SID"; ?>&amp;mode=searchuser&amp;form=2&amp;field=entries', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;" /><input type="hidden" name="ug_type" value="user" /></td>
	</tr>
<?php

			break;

		case 'group':
			// Generate list of groups

?>
	<tr>
		<th align="center"><?php echo $user->lang['LOOK_UP_GROUP']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center" valign="middle">&nbsp;<select name="ug_data[]" multiple="true" size="5"><?php 

			$sql = "SELECT group_id, group_name, group_type   
				FROM " . GROUPS_TABLE . " 
				ORDER BY group_type DESC";
			$result = $db->sql_query($sql);

			$group_options = '';
			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					echo '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="blue"' : '') . ' value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
				}
				while ($row = $db->sql_fetchrow($result));
			}
			$db->sql_freeresult($result);
			
?></select>&nbsp;</td>
	</tr>
	<tr>
		<td class="cat" align="center"><input type="submit" name="submit_edit_options" value="<?php echo $user->lang['LOOK_UP_GROUP']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /><input type="hidden" name="ug_type" value="group" /></td>
	</tr>
<?php

		break;

	}

?>
</table></form>

<?php

}
// End user, group or forum selection


// Second possible form, this lists the currently enabled
// users/groups for the given mode
if ((in_array($submit, array('usergroups', 'delete', 'cancel'))) || (!strstr($submit, 'options') && empty($submode) && in_array($mode, array('admin', 'supermod'))))
{

?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain; ?></p>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td align="center"><h1><?php echo $user->lang['USERS']; ?></h1></td>
		<td align="center"><h1><?php echo $user->lang['USERGROUPS']; ?></h1></td>
	</tr>
	<tr>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['MANAGE_USERS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><select style="width:280px" name="ug_data[]" multiple="multiple" size="5"><?php
			
	$sql = "SELECT DISTINCT u.user_id, u.username
		FROM " . USERS_TABLE . " u, " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o
		WHERE o.auth_option LIKE '" . $sql_option_mode . "_%'
			AND a.auth_option_id = o.auth_option_id
			AND a.forum_id IN ($sql_forum_id)
			AND u.user_id = a.user_id
		ORDER BY u.username, u.user_regdate ASC";
	$result = $db->sql_query($sql);

	$users = '';
	while ($row = $db->sql_fetchrow($result))
	{
		echo '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
	}
	$db->sql_freeresult($result);
		
?></select></td>
			</tr>
			<tr>
				<td class="cat" align="center"><input class="btnlite" type="submit" name="submit_delete" value="<?php echo $user->lang['DELETE']; ?>" /> &nbsp; <input class="btnlite" type="submit" name="submit_edit_options" value="<?php echo $user->lang['SET_OPTIONS']; ?>" /><input type="hidden" name="ug_type" value="user" /><?php echo $s_forum_id; ?></td>
			</tr>
		</table></form></td>

		<td align="center"><form method="post" name="admingroups" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
		<tr>
			<th><?php echo $user->lang['MANAGE_GROUPS']; ?></th>
		</tr>
		<tr>
			<td class="row1" align="center"><select style="width:280px" name="ug_data[]" multiple="multiple" size="5"><?php 
	
	$sql = "SELECT DISTINCT g.group_id, g.group_name, g.group_type 
		FROM " . GROUPS_TABLE . " g, " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o
		WHERE o.auth_option LIKE '" . $sql_option_mode . "_%'
			AND a.forum_id IN ($sql_forum_id)
			AND a.auth_option_id = o.auth_option_id
			AND g.group_id = a.group_id
		ORDER BY g.group_type DESC, g.group_name ASC";
	$result = $db->sql_query($sql);

	$groups = '';
	while ($row = $db->sql_fetchrow($result))
	{
		echo '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="blue"' : '') . ' value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	$db->sql_freeresult($result);

?></select></td>
		</tr>
		<tr>
			<td class="cat" align="center"><input class="btnlite" type="submit" name="submit_delete" value="<?php echo $user->lang['DELETE']; ?>" /> &nbsp; <input class="btnlite" type="submit" name="submit_edit_options" value="<?php echo $user->lang['SET_OPTIONS']; ?>" /><input type="hidden" name="ug_type" value="group" /><?php echo $s_forum_id; ?></td>
		</tr>
	</table></form></td>

	</tr>
	<tr>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['ADD_USERS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><textarea cols="40" rows="4" name="ug_data[]"></textarea></td>
			</tr>
			<tr>
				<td class="cat" align="center"> <input type="submit" name="submit_add_options" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" />&nbsp; <input type="submit" value="<?php echo $user->lang['FIND_USERNAME']; ?>" class="btnlite" onclick="window.open('<?php echo "../memberlist.$phpEx$SID"; ?>&amp;mode=searchuser&amp;form=2&amp;field=entries', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;" /><input type="hidden" name="ug_type" value="user" /><?php echo $s_forum_id; ?></td>
			</tr>
		</table></form></td>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['ADD_GROUPS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><select name="ug_data[]" multiple="multiple" size="4"><?php 
			
	$sql = "SELECT group_id, group_name, group_type 
		FROM " . GROUPS_TABLE . "
		ORDER BY group_type DESC, group_name";
	$result = $db->sql_query($sql);

	$group_list = '';
	while ($row = $db->sql_fetchrow($result))
	{
		echo '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="blue"' : '') . ' value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	$db->sql_freeresult($result);
		
?></select></td>
			</tr>
			<tr>
				<td class="cat" align="center"> <input type="submit" name="submit_add_options" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /><input type="hidden" name="ug_type" value="group" /><?php echo $s_forum_id; ?></td>
			</tr>
		</table></form></td>
	</tr>
</table>

<?php

}
// End user and group acl selections






// Third possible form, this is the major section of this script. It
// handles the entry of permission options for all situations
if (in_array($submit, array('add_options', 'edit_options', 'presetsave', 'presetdel', 'update')) || !empty($submode))
{

	// Did the user specify any users or groups?
	if (empty($ug_data))
	{
		$l_message = ($ug_type == 'user') ? 'NO_USER' : 'NO_GROUP';
		trigger_error($user->lang[$l_message]);
	}


	$forum_list = '';
	// Grab the forum details if non-zero forum_id
	if (!in_array(0, $forum_id[$which_mode]))
	{
		$sql = 'SELECT forum_id, forum_name, parent_id  
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id IN ($sql_forum_id)";
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_FORUM']);
		}

		// If we have more than one forum we want a list of all their names
		// so loop through all results. We don't need all the data though 
		// since cascading/inheritance is only applicable if a single forum
		// was selected
		$forum_data = $row;

		do
		{
			$forum_list .= (($forum_list != '') ? ', ' : '') . '<b>' . $row['forum_name'] . '</b>';
		}
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);
	}


	// Grab relevant user or group information
	$ug_ids = $l_ug_list = $ug_hidden = $l_no_error = '';
	switch ($ug_type)
	{
		case 'user':
			// If we've just come from the usergroup form then user will actually
			// be a username rather than a user_id, so act appropriately
			$l_no_error = $user->lang['NO_USER'];
			$sql = 'SELECT user_id AS id, username AS name 
				FROM ' . USERS_TABLE . ' 
				WHERE ';
			$sql .= ($submit == 'add_options') ? ' username IN (' . implode(', ', array_unique(preg_replace('#^[\s]*?(.*?)[\s]*?$#', "'\\1'", explode("\n", $ug_data[0])))) . ')' : ' user_id ' . ((is_array($ug_data)) ? 'IN (' . implode(', ', $ug_data) . ')' : '= ' . $ug_data);
			break;

		case 'group':
			$l_no_error = $user->lang['NO_GROUP'];
			$sql = 'SELECT group_id AS id, group_name AS name, group_type  
				FROM ' . GROUPS_TABLE . '
				WHERE group_id';
			$sql .= (is_array($ug_data)) ? ' IN (' . implode(', ', $ug_data) . ')' : ' = ' . $ug_data;
			break;
	}
	$result = $db->sql_query($sql);

	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error($l_no_error);
	}
	unset($l_no_error);

	// Store the user_ids and names for later use
	do 
	{
		$l_ug_list .= (($l_ug_list != '') ? ', ' : '') . ((isset($row['group_type']) && $row['group_type'] == GROUP_SPECIAL) ? '<b class="blue">' . $user->lang['G_' . $row['name']] : '<b>' . $row['name']) . '</b>';
		$ug_ids .= (($ug_ids != '') ? ', ' : '') . $row['id'];
		$ug_hidden .= '<input type="hidden" name="ug_data[]" value="' . $row['id'] . '" />';
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);


	// Grab the list of options ... if we're in deps mode we want all options, 
	// else we skip the master options
	$sql_founder = ($user->data['user_type'] == USER_FOUNDER) ? ' AND founder_only <> 1' : '';
	$sql_limit_option = ($mode == 'deps') ? '' : "AND auth_option <> '" . $sql_option_mode . "_'";
	$sql = "SELECT auth_option_id, auth_option
		FROM " . ACL_OPTIONS_TABLE . "
		WHERE auth_option LIKE '" . $sql_option_mode . "_%' 
			$sql_limit_option 
			$sql_founder";
	$result = $db->sql_query($sql);

	$auth_options = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$auth_options[] = $row;
	}
	$db->sql_freeresult($result);

	unset($sql_limit_option);


	// Now we'll build a list of preset options ...
	$preset_options = $preset_js = $preset_update_options = '';
	$holding = array();
	$holding['allow'] = $holding['deny'] = $holding['inherit'] = '';

	// Do we have a parent forum? If so offer option to inherit from that
	if ($forum_data['parent_id'] != 0)
	{
		switch ($ug_type)
		{
			case 'group':
				$sql = "SELECT o.auth_option, a.auth_setting FROM " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE o.auth_option LIKE '" . $sql_option_mode . "_%' AND a.auth_option_id = o.auth_option_id AND a.forum_id = " . $forum_data['parent_id'] . " AND a.group_id IN ($ug_ids)";
				break;

			case 'user':
				$sql = "SELECT o.auth_option, a.auth_setting FROM " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE o.auth_option LIKE '" . $sql_option_mode . "_%' AND a.auth_option_id = o.auth_option_id AND a.forum_id = " . $forum_data['parent_id'] . " AND a.user_id IN ($ug_ids)";
				break;
		}
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				switch ($row['auth_setting'])
				{
					case ACL_YES:
						$holding['allow'] .= $row['auth_option'] . ', ';
						break;

					case ACL_NO:
						$holding['deny'] .= $row['auth_option'] . ', ';
						break;

					case ACL_UNSET:
						$holding['inherit'] .= $row['auth_option'] . ', ';
						break;
				}
			}
			while ($row = $db->sql_fetchrow($result));

			$preset_options .= '<option value="preset_0">' . $user->lang['INHERIT_PARENT'] . '</option>';
			$preset_js .= "\tpresets['preset_0'] = new Array();" . "\n";
			$preset_js .= "\tpresets['preset_0'] = new preset_obj('" . $holding['allow'] . "', '" . $holding['deny'] . "', '" . $holding['inherit'] . "');\n";
		}
		$db->sql_freeresult($result);
	}

	// Look for custom presets
	$sql = "SELECT preset_id, preset_name, preset_data  
		FROM " . ACL_PRESETS_TABLE . " 
		WHERE preset_type = '" . (($mode == 'deps') ? 'f' : $sql_option_mode) . "' 
		ORDER BY preset_id ASC";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			$preset_update_options .= '<option value="' . $row['preset_id'] . '">' . $row['preset_name'] . '</option>';
			$preset_options .= '<option value="preset_' . $row['preset_id'] . '">' . $row['preset_name'] . '</option>';

			$preset_data = unserialize($row['preset_data']);
			
			foreach ($preset_data as $preset_type => $preset_type_ary)
			{
				$holding[$preset_type] = '';
				foreach ($preset_type_ary as $preset_option)
				{
					$holding[$preset_type] .= "$preset_option, ";
				}
			}

			$preset_js .= "\tpresets['preset_" . $row['preset_id'] . "'] = new Array();" . "\n";
			$preset_js .= "\tpresets['preset_" . $row['preset_id'] . "'] = new preset_obj('" . $holding['allow'] . "', '" . $holding['deny'] . "', '" . $holding['inherit'] . "');\n";
		}
		while ($row = $db->sql_fetchrow($result));
	}
	$db->sql_freeresult($result);

	unset($holding);


	// If we aren't looking @ deps then we try and grab existing sessions for
	// the given forum and user/group
	if (!is_array($auth_settings) || empty($auth_settings[$which_mode]))
	{
		if ($which_mode == $mode)
		{
			switch ($ug_type)
			{
				case 'group':
					$sql_table = ACL_GROUPS_TABLE . ' a ';
					$sql_join = 'a.group_id';
					break;

				case 'user':
					$sql_table = ACL_USERS_TABLE . ' a ';
					$sql_join = 'a.user_id';
					break;
			}
		
			$sql = "SELECT o.auth_option, MIN(a.auth_setting) AS min_auth_setting 
					FROM $sql_table, " . ACL_OPTIONS_TABLE . " o 
					WHERE o.auth_option LIKE '" . $sql_option_mode . "_%' 
						AND a.auth_option_id = o.auth_option_id 
						AND a.forum_id IN ($sql_forum_id) 
						AND $sql_join IN ($ug_ids)
					GROUP BY o.auth_option";
			$result = $db->sql_query($sql);

			$auth_settings[$which_mode] = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$auth_settings[$which_mode][$row['auth_option']] = $row['min_auth_setting'];
			}
			$db->sql_freeresult($result);
		}
		else
		{
			// We're looking at a view ... so we'll set all options to unset
			// We could be a little more clever here but the "safe side" looks
			// better right now
			$auth_settings[$which_mode] = array();
			foreach ($auth_options as $option)
			{
				$auth_settings[$which_mode][$option['auth_option']] = '*';
			}
			unset($option);
		}
	}

	$view_options = '';
	// Should we display a dropdown for views?
	if (in_array($mode, array('admin', 'supermod', 'mod')))
	{
		$view_options .= '<option value="">' . $user->lang['SELECT_VIEW'] . '</option>';
		$view_ary = array(
			'admin'		=> array('admin' => 'a_', 'forum' => 'a_auth', 'supermod' => 'a_authmods', 'mod' => 'a_authmods'),
			'supermod'	=> array('supermod' => 'a_authmods', 'mod' => 'a_authmods', 'forum' => 'a_auth'), 
			'mod'		=> array('mod' => 'a_authmods', 'forum' => 'a_auth')
		);

		foreach ($view_ary[$mode] as $which_submode => $which_acl)
		{
			if ($auth->acl_get($which_acl))
			{
				$view_options .= '<option value="' . $which_submode . '"' . (($which_submode == $which_mode) ? ' selected="selected"' : '') . '>' . $user->lang['ACL_VIEW_' . strtoupper($which_submode)] . '</option>';
			}

		}
		unset($view_ary);
	}

	$settings_hidden = '';
	// Output original settings ... needed when we jump views
	foreach ($auth_settings as $auth_submode => $auth_submode_settings)
	{
		if ($auth_submode != $which_mode)
		{
			foreach ($auth_submode_settings as $submode_option => $submode_setting)
			{
				$settings_hidden .= ($submode_setting != '*') ? '<input type="hidden" name="settings[' . $auth_submode . '][' . $submode_option . ']" value="' . $submode_setting . '" />' : '';
			}
		}
	}
	unset($auth_submode);
	unset($auth_submode_settings);
	unset($auth_submode_option);
	unset($auth_submode_setting);

?>

<script language="Javascript" type="text/javascript">
<!--

	var presets = new Array();
<?php

	echo $preset_js;

?>

	function preset_obj(yes, no, unset)
	{
		this.yes = yes;
		this.no = no;
		this.unset = unset;
	}

	function use_preset(option)
	{
		if (option)
		{
			document.acl.set.selectedIndex = 0;
			for (i = 0; i < document.acl.length; i++)
			{
				var elem = document.acl.elements[i];
				if (elem.name.indexOf('settings') == 0)
				{
					switch (option)
					{
						case 'all_yes':
							if (elem.value == <?php echo ACL_YES; ?>)
								elem.checked = true;
							break;

						case 'all_no':
							if (elem.value == <?php echo ACL_NO; ?>)
								elem.checked = true;
							break;

						case 'all_unset':
							if (elem.value == <?php echo ACL_UNSET; ?>)
								elem.checked = true;
							break;

						case 'all_ignore':
							if (elem.value == '*')
								elem.checked = true;
							break;

						default:
							option_start = elem.name.search(/\[(\w+?)\]$/);
							option_name = elem.name.substr(option_start + 1, elem.name.length - option_start - 2);

							if (presets[option].yes.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_YES; ?>)
								elem.checked = true;
							else if (presets[option].no.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_NO; ?>)
								elem.checked = true;
							else if (presets[option].unset.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_UNSET; ?>)
								elem.checked = true;
							break;
					}
				}
			}
		}
	}

	function marklist(match, status)
	{
		for (i = 0; i < document.acl.length; i++)
		{
			if (document.acl.elements[i].name.indexOf(match) == 0)
				document.acl.elements[i].checked = status;
		}
	}

	function open_win(url, width, height)
	{
		aclwin = window.open(url, '_phpbbacl', 'HEIGHT=' + height + ',resizable=yes, scrollbars=yes,WIDTH=' + width);
		if (window.focus)
			aclwin.focus();
	}
//-->
</script>

<p><?php echo $user->lang['ACL_EXPLAIN']; ?></p>

<h1><?php echo $l_title; ?></h1>

<?php

	// Do we have a list of forums? If so, output them ... but only
	// if we're looking at the primary view or mode ... submodes
	// output their own list of forums as and where applicable so this
	// is unnecessary
	if ($forum_list != '' && $which_mode == $mode)
	{
		$l_selected_forums = (sizeof($forum_id[$which_mode]) == 1) ? 'SELECTED_FORUM' : 'SELECTED_FORUMS';

		echo '<p>' . $user->lang[$l_selected_forums] . ': ' . $forum_list . '</p>';

		unset($forum_list);
		unset($l_selected_forums);
	}

	// Now output the list of users or groups ... these will always exist
	$l_selected_users = ($ug_type == 'user') ? ((sizeof($ug_data) == 1) ? 'SELECTED_USER' : 'SELECTED_USERS') : ((sizeof($ug_data) == 1) ? 'SELECTED_GROUP' : 'SELECTED_GROUPS'); 

	echo '<p>' . $user->lang[$l_selected_users] . ': ' . $l_ug_list . '</p>';

	unset($l_selected_users);
	unset($ug_data);

?>

<p><?php echo $l_title_explain; ?></p>

<?php

	if ($settings_hidden != '')
	{

?>

<h2 style="color:red"><?php echo $user->lang['WARNING']; ?></h2>

<p><?php echo $user->lang['WARNING_EXPLAIN']; ?></p>

<?php

	}

?>

<form method="post" name="acl" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode&amp;submode=$submode"; ?>"><table cellspacing="2" cellpadding="0" border="0" align="center">
<?php

	// This is the main listing of options

	// We output this for both deps and when update is requested where
	// deps exist
	if (($mode == 'admin' || $mode == 'supermod') && in_array($submode, array('forum', 'mod')))
	{

?>
	<tr>
		<td colspan="2" align="right"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<th colspan="2"><?php echo $user->lang['SELECT_FORUM']; ?></th>
			</tr>
			<tr>
				<td class="row1" width="150"><?php echo $user->lang['WILL_SET_OPTIONS']; ?>:</td>
				<td class="row2"><select name="f[<?php echo $which_mode; ?>][]" multiple="4"><?php 
		
		echo make_forum_select($forum_id[$which_mode], false, true); 
		
?></select></td>
			</tr>
		</table><br /></td>
	</tr>
<?php

	}
	// End deps output

?>
	<tr>
		<td align="left"><?php
	
	if ($view_options != '')
	{
	
?><select name="submode" onchange="if (this.options[this.selectedIndex].value != '') this.form.submit();"><?php echo $view_options; ?></select><?php
	
	}
	
?></td>
		<td align="right"><?php echo $user->lang['PRESETS']; ?>: <select name="set" onchange="use_preset(this.options[this.selectedIndex].value);"><option class="sep"><?php echo $user->lang['SELECT'] . ' -&gt;'; ?></option><option value="all_yes"><?php echo $user->lang['ALL_YES']; ?></option><option value="all_no"><?php echo $user->lang['ALL_NO']; ?></option><option value="all_unset"><?php echo $user->lang['ALL_UNSET']; ?></option><?php 

	$colspan = 4;
	if ($which_mode != $mode)
	{
		$colspan = 5;
		echo '<option value="all_ignore">' . $user->lang['ALL_IGNORE'] . '</option>';
	}

	// Output user preset options ... if any
	echo ($preset_options) ? '<option class="sep">' . $user->lang['USER_PRESETS'] . ' -&gt;' . '</option>' . $preset_options : ''; 

?></select></td>
	</tr>
	<tr>
		<td colspan="2"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th>&nbsp;<?php echo $user->lang['OPTION']; ?>&nbsp;</th>
				<th width="50">&nbsp;<?php echo $user->lang['YES']; ?>&nbsp;</th>
				<th width="50">&nbsp;<?php echo $user->lang['UNSET']; ?>&nbsp;</th>
				<th width="50">&nbsp;<?php echo $user->lang['NO']; ?>&nbsp;</th>
<?php

	if ($which_mode != $mode)
	{

?>
				<th width="50">&nbsp;<?php echo $user->lang['IGNORE']; ?>&nbsp;</th>
<?php

	}

?>
			</tr>
<?php

	$row_class = 'row2';
	for ($i = 0; $i < sizeof($auth_options); $i++)
	{
		$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

		// Try and output correct language strings, else output prettyfied auth_option
		$l_auth_option = (!empty($user->lang['acl_' . $auth_options[$i]['auth_option']])) ? $user->lang['acl_' . $auth_options[$i]['auth_option']] : ucfirst(preg_replace('#.*?_#', '', $auth_options[$i]['auth_option']));
		$s_auth_option = '[' . $which_mode . '][' . $auth_options[$i]['auth_option'] . ']';

		
		// Which option should we select?
		$selected_yes = (isset($auth_settings[$which_mode][$auth_options[$i]['auth_option']]) && $auth_settings[$which_mode][$auth_options[$i]['auth_option']] == ACL_YES) ? ' checked="checked"' : '';
		$selected_no = (isset($auth_settings[$which_mode][$auth_options[$i]['auth_option']]) && $auth_settings[$which_mode][$auth_options[$i]['auth_option']] == ACL_NO) ? ' checked="checked"' : '';
		$selected_unset = (!isset($auth_settings[$which_mode][$auth_options[$i]['auth_option']]) || $auth_settings[$which_mode][$auth_options[$i]['auth_option']] == ACL_UNSET) ? ' checked="checked"' : '';

?>
			<tr>
				<td class="<?php echo $row_class; ?>" nowrap="nowrap"><?php echo $l_auth_option; ?>&nbsp;</td>
				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="settings<?php echo $s_auth_option ;?>" value="<?php echo ACL_YES; ?>"<?php echo $selected_yes; ?> /></td>
				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="settings<?php echo $s_auth_option ;?>" value="<?php echo ACL_UNSET; ?>"<?php echo $selected_unset; ?> /></td>
				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="settings<?php echo $s_auth_option ;?>" value="<?php echo ACL_NO; ?>"<?php echo $selected_no; ?> /></td>
<?php

		if ($which_mode != $mode)
		{
			$selected_ignore = (isset($auth_settings[$which_mode][$auth_options[$i]['auth_option']]) && $auth_settings[$which_mode][$auth_options[$i]['auth_option']] == '*') ? ' checked="checked"' : '';

?>
				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="settings<?php echo $s_auth_option ;?>" value="*"<?php echo $selected_ignore; ?> /></td>
<?php

		}

?>
			</tr>
<?php

	}


	// If we're setting forum or moderator options and a single forum has
	// been selected then look to see if any subforums exist. If they do
	// give user the option of cascading permissions to them
	if (($mode == 'forum' || $mode == 'mod') && empty($submode) && sizeof($forum_id[$which_mode]) == 1)
	{
		$children = get_forum_branch($forum_id[$which_mode][0], 'children', 'descending', false);

		if (!empty($children))
		{

?>
			<tr>
				<th colspan="<?php echo $colspan; ?>"><?php echo $user->lang['ACL_SUBFORUMS']; ?></th>
			</tr>
			<tr>
				<td class="row1" colspan="<?php echo $colspan; ?>"><table width="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td class="gensmall" colspan="4" height="16" align="center"><?php echo $user->lang['ACL_SUBFORUMS_EXPLAIN']; ?></td>
					</tr>
<?php

			foreach ($children as $row)
			{

?>
					<tr>
						<td><input type="checkbox" name="inherit[]" value="<?php echo $row['forum_id']; ?>" /> <?php echo $row['forum_name']; ?></td>
					</tr>
<?php

			}

?>
					<tr>
						<td height="16" align="center"><a class="gensmall" href="javascript:marklist('inherit', true);"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('inherit', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></td>
					</tr>
				</table></td>
			</tr>
<?php

		}
	}

	// Display event/cron radio buttons
	if ($auth->acl_gets('a_events', 'a_cron') && $mode != 'deps' && $submit != 'update')
	{
		$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
			<!-- tr>
				<th colspan="<?php echo $colspan; ?>"><?php echo $user->lang['RUN_HOW']; ?></th>
			</tr>
			<tr>
				<td class="<?php echo $row_class; ?>" colspan="4" align="center"><input type="radio" name="runas" value="now" checked="checked" /> <?php echo $user->lang['RUN_AS_NOW']; ?><?php 
	
			if ($auth->acl_get('a_events'))
			{ 

?> &nbsp;<input type="radio" name="runas" value="evt" /> <?php 
	
				echo $user->lang['RUN_AS_EVT'];  
			}
			
			if ($auth->acl_get('a_cron'))
			{

?> &nbsp;<input type="radio" name="runas" value="crn" /> <?php 
	
				echo $user->lang['RUN_AS_CRN']; 
				
			}

?></td>
			</tr -->
<?php

	}

?>
			<tr>
				<td class="cat" colspan="<?php echo $colspan; ?>" align="center"><input class="btnmain" type="submit" name="submit_update" value="<?php echo $user->lang['UPDATE']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="submit" name="submit_cancel" value="<?php echo $user->lang['CANCEL']; ?>" /><input type="hidden" name="ug_type" value="<?php echo $ug_type; ?>" /><?php echo $ug_hidden; ?><?php 

	// Output forum id data
	echo $s_forum_id;

	// Output settings generated from other views
	echo $settings_hidden;
	unset($settings_hidden);
	
?></td>
			</tr>
		</table>

		<br clear="all" />

		<table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="4"><?php echo $user->lang['PRESETS']; ?></th>
			</tr>
			<tr>
				<td class="row1" colspan="4"><table width="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td colspan="2" height="16"><span class="gensmall"><?php echo $user->lang['PRESETS_EXPLAIN']; ?></span></td>
					</tr>
					<tr>
						<td nowrap="nowrap"><?php echo $user->lang['SELECT_PRESET']; ?>: </td>
						<td><select name="presetoption"><option class="sep" value="-1"><?php echo $user->lang['SELECT'] . ' -&gt;'; ?></option><?php 

	echo $preset_update_options;
			
		?></select></td>
					</tr>
					<tr>
						<td nowrap="nowrap"><?php echo $user->lang['PRESET_NAME']; ?>: </td>
						<td><input type="text" name="presetname" maxlength="25" /> </td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="cat" colspan="4" align="center"><input class="btnlite" type="submit" name="submit_presetsave" value="<?php echo $user->lang['SAVE']; ?>" /> &nbsp;<input class="btnlite" type="submit" name="submit_presetdel" value="<?php echo $user->lang['DELETE']; ?>" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

}

// Output page footer
adm_page_footer();

// ---------
// FUNCTIONS
//
function update_foes()
{
	global $db, $auth;

	$perms = array();
	foreach ($auth->acl_get_list(false, array('a_', 'm_'), false) as $forum_id => $forum_ary)
	{
		foreach ($forum_ary as $auth_option => $user_ary)
		{
			$perms += $user_ary;
		}
	}

	if (sizeof($perms))
	{
		$sql = 'DELETE FROM ' . ZEBRA_TABLE . ' 
			WHERE zebra_id IN (' . implode(', ', $perms) . ')';
		$db->sql_query($sql);
	}
	unset($perms);
}
//
// FUNCTIONS
// ---------

?>