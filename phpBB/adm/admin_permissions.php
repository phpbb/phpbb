<?php
/***************************************************************************
 *                           admin_permissions.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
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
	$module['FORUM']['PERMISSIONS'] = ($auth->acl_get('a_auth')) ? $filename . $SID . '&amp;mode=forum' : '';
	$module['FORUM']['MODERATORS'] = ($auth->acl_get('a_authmods')) ? $filename . $SID . '&amp;mode=mod' : '';
	$module['FORUM']['SUPER_MODERATORS'] = ($auth->acl_get('a_authmods')) ? $filename . $SID . '&amp;mode=supermod' : '';
	$module['FORUM']['ADMINISTRATORS'] = ($auth->acl_get('a_authadmins')) ? $filename . $SID . '&amp;mode=admin' : '';
	$module['USER']['PERMISSIONS'] = ($auth->acl_get('a_authusers')) ? $filename . $SID . '&amp;mode=user' : '';
	$module['GROUP']['PERMISSIONS'] = ($auth->acl_get('a_authgroups')) ? $filename . $SID . '&amp;mode=group' : '';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);


// Grab and set some basic parameters
//
// 'mode' determines what we're altering; administrators, users, deps, etc.
// 'type' is used primarily for deps and contains the original 'mode'
$mode	= (isset($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';
$type	= (isset($_REQUEST['type'])) ? htmlspecialchars($_REQUEST['type']) : '';
$action = (isset($_REQUEST['action'])) ? htmlspecialchars($_REQUEST['action']) : '';

// Submitted setting data
//
// 'auth_settings' contains the submitted option settings assigned to options, should be an 
//   associative array
// 'auth_setting' contains the value of the submitted 'auth_option', an integer value used
//   mainly by deps mode
// 'auth_option' contains a single auth_option string, used mainly by deps mode
$auth_settings	= (isset($_POST['settings'])) ? $_POST['settings'] : array();
$auth_option	= (isset($_REQUEST['option'])) ? htmlspecialchars($_REQUEST['option']) : '';
$auth_setting	= (isset($_REQUEST['setting'])) ? intval($_REQUEST['setting']) : '';

// Forum, User or Group information
//
// 'ug_type' is either user or groups used mainly for forum/admin/mod permissions
// 'ug_data' contains the list of usernames, user_id's or group_ids for the 'ug_type'
$ug_type = (isset($_REQUEST['ug_type'])) ? htmlspecialchars($_REQUEST['ug_type']) : '';
$ug_data = (isset($_POST['ug_data'])) ? $_POST['ug_data'] : '';






// Define some vars
$forum_id = 0;
$forum_sql = '';
if (isset($_REQUEST['f']))
{
	$forum_id = intval($_REQUEST['f']);
	$forum_sql = " WHERE forum_id = $forum_id";
}


$username = (isset($_REQUEST['username'])) ? $_REQUEST['username'] : '';
$group_id = (isset($_REQUEST['g'])) ? intval($_REQUEST['g']) : '';











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
if ($mode == 'deps')
{
	switch ($type)
	{
		case 'mod':
		case 'supermod':
			$which_acl = 'a_authmods';
			$sql_option_mode = 'm';
			break;

		case 'admin':
			$which_acl = 'a_authadmins';
			$sql_option_mode = 'a';
			break;
	}

	// Permission check
	if (!$auth->acl_get($which_acl))
	{
		trigger_error($user->lang['NO_ADMIN']);
	}
}











//
//
// OUTPUT PAGE
//
//
page_header($l_title);








		$auth_options = $auth_settings = array();

		// Grab the list of options ... if we're in deps
		// mode we want all options, else we skip the master
		// options
		$sql_founder = ($user->data['user_founder']) ? ' AND founder_only <> 1' : '';
		$sql_limit_option = ($mode == 'deps') ? '' : "AND auth_option <> '" . $sql_option_mode . "_'";
		$sql = "SELECT auth_option_id, auth_option
			FROM " . ACL_OPTIONS_TABLE . "
			WHERE auth_option LIKE '" . $sql_option_mode . "_%' 
				$sql_limit_option 
				$sql_founder";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$auth_options[] = $row;
		}
		$db->sql_freeresult($result);

		unset($sql_limit_option);

		// Now we'll build a list of preset options ...
		$preset_options = $preset_js = $preset_update_options = '';
		$holding = array();

		// Do we have a parent forum? If so offer option to inherit from that
		if ($forum_info['parent_id'] != 0)
		{
			switch ($ug_type)
			{
				case 'group':
					$sql = "SELECT o.auth_option, a.auth_setting FROM " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE o.auth_option LIKE '" . $sql_option_mode . "_%' AND a.auth_option_id = o.auth_option_id AND a.forum_id = " . $forum_info['parent_id'] . " AND a.group_id IN ($where_sql)";
					break;

				case 'user':
					$sql = "SELECT o.auth_option, a.auth_setting FROM " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE o.auth_option LIKE '" . $sql_option_mode . "_%' AND a.auth_option_id = o.auth_option_id AND a.forum_id = " . $forum_info['parent_id'] . " AND a.user_id IN ($where_sql)";
					break;
			}
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					switch ($row['auth_setting'])
					{
						case ACL_ALLOW:
							$holding['allow'] .= $row['auth_option'] . ', ';
							break;

						case ACL_DENY:
							$holding['deny'] .= $row['auth_option'] . ', ';
							break;

						case ACL_INHERIT:
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
			WHERE preset_type = '$sql_option_mode' 
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

?>

<script language="Javascript" type="text/javascript">
<!--

	var presets = new Array();
<?php

	echo $preset_js;

?>

	function preset_obj(allow, deny, inherit)
	{
		this.allow = allow;
		this.deny = deny;
		this.inherit = inherit;
	}

	function use_preset(option)
	{
		if (option)
		{
			document.acl.set.selectedIndex = 0;
			var expr = new RegExp(/\d+/);
			for (i = 0; i < document.acl.length; i++)
			{
				var elem = document.acl.elements[i];
				if (elem.name.indexOf('option') == 0)
				{
					switch (option)
					{
						case 'all_yes':
							if (elem.value == <?php echo ACL_ALLOW; ?>)
								elem.checked = true;
							break;
						case 'all_no':
							if (elem.value == <?php echo ACL_DENY; ?>)
								elem.checked = true;
							break;
						case 'all_unset':
							if (elem.value == <?php echo ACL_INHERIT; ?>)
								elem.checked = true;
							break;
						default:
						    option_name = elem.name.substr(7, elem.name.length - 8);

							if (presets[option].allow.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_ALLOW; ?>)
								elem.checked = true;
							else if (presets[option].deny.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_DENY; ?>)
								elem.checked = true;
							else if (presets[option].inherit.indexOf(option_name + ',') != -1 && elem.value == <?php echo ACL_INHERIT; ?>)
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

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain; ?></p>

<form method="post" name="acl" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table cellspacing="2" cellpadding="0" border="0" align="center">
<?php

		// The above query grabs the list of options for the required mode ... 
		// however for the deps system we need to grab the set of options for 
		// which dependencies are to be set
		if ($mode == 'deps')
		{
			// Turn auth_options array above into the dep_auth_options list
			$dep_auth_options = $dep_auth_values = $dep_auth_forums = '';
			foreach ($auth_options as $option)
			{
				$dep_auth_options .= '<option value="' . $option['auth_option'] . '"' . (($option['auth_option'] == $auth_option) ? ' selected="selected"' : '') . '>' . ((!empty($user->lang['acl_' . $option['auth_option']])) ? $user->lang['acl_' . $option['auth_option']] : (($option['auth_option'] == $sql_option_mode . '_') ? 'Any option' : ucfirst(preg_replace('#.*?_#', '', $option['auth_option'])))) . '</option>';
			}
			unset($auth_options);
			unset($option);

			// Define the Yes, No, Unset selections
			$values = array(ACL_DENY => $user->lang['NO'], ACL_ALLOW => $user->lang['YES'], ACL_INHERIT => $user->lang['UNSET']);
			foreach ($values as $value => $option)
			{
				$dep_auth_values .= '<option value="' . $value . '"' . (($value === $auth_setting) ? ' selected="selected"' : '') . '>' . $option . '</option>';
			}
			unset($values);
			unset($option);

			$dep_auth_forums = make_forum_select($forum_id, false, false);

			// We've grabbed the list of options for this mode now we need to
			// grab the list of options we can set dependencies for
			switch ($sql_option_mode)
			{
				case 'a':
					$sql_auth_option = "(auth_option LIKE 'a_%' AND auth_option <> 'a_') OR (auth_option LIKE 'm_%' AND auth_option <> 'm_')";
					break;
				case 'm':
					$sql_auth_option = "auth_option LIKE 'm_%' AND auth_option <> 'm_'";
					break;
			}

			$founder_sql = ($user->data['user_founder']) ? ' AND founder_only <> 1' : '';
			$sql = "SELECT auth_option
				FROM " . ACL_OPTIONS_TABLE . "
				WHERE $sql_auth_option 
					$founder_sql";
			$result = $db->sql_query($sql);

			$auth_options = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$auth_options[] = $row;
			}
			$db->sql_freeresult($result);

?>
	<tr>
		<td align="right"><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<th colspan="2">&nbsp;</th>
			</tr>
			<tr>
				<td class="row1" width="150">Changing option:</td>
				<td class="row2"><select name="dep_option"><?php echo $dep_auth_options; ?></select></td>
			</tr>
			<tr>
				<td class="row1" width="150">To value:</td>
				<td class="row2"><select name="dep_value"><option value="-1"<?php

			echo ($dep_value == -1) ? ' selected="selected"' : '';
	
?>>Choose value</option><?php echo $dep_auth_values; ?></select></td>
			</tr>
			<tr>
				<td class="row1" width="150">Will set options in: <br /><span class="gensmall"></span></td>
				<td class="row2"><select name="f[]" multiple="4" onchange="this.form.submit"><option class="sep" value="0"<?php 
					
			echo (in_array(0, $dep_forum_id)) ? ' selected="selected"' : ''; 
				
?>>Current forums</option><?php 
			
			if ($dep_type == 'mod')
			{
			
?><option class="sep" value="-2">Affected forum</option><?php 
	
			}
		
			echo $dep_auth_forums; 
		
?></select></td>
			</tr>
		</table></td>
	</tr>
<?php

			unset($dep_auth_options);
			unset($dep_auth_values);
			unset($dep_forum_options);
			
		}
		// End deps output

		// This is the main listing of options

?>
	<tr>
		<td align="right"><?php echo $user->lang['PRESETS']; ?>: <select name="set" onchange="use_preset(this.options[this.selectedIndex].value);"><option class="sep"><?php echo $user->lang['SELECT'] . ' -&gt;'; ?></option><option value="all_yes"><?php echo $user->lang['ALL_YES']; ?></option><option value="all_no"><?php echo $user->lang['ALL_NO']; ?></option><option value="all_unset"><?php echo $user->lang['ALL_UNSET']; ?></option><?php 

		echo ($preset_options) ? '<option class="sep">' . $user->lang['USER_PRESETS'] . ' -&gt;' . '</option>' . $preset_options : ''; 

?></select></td>
		</tr>
		<tr>
			<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
				<tr>
					<th>&nbsp;<?php echo $user->lang['OPTION']; ?>&nbsp;</th>
					<th width="50">&nbsp;<?php echo $user->lang['YES']; ?>&nbsp;</th>
					<th width="50">&nbsp;<?php echo $user->lang['NO']; ?>&nbsp;</th>
					<th width="50">&nbsp;<?php echo $user->lang['UNSET']; ?>&nbsp;</th>
				</tr>
<?php

		for($i = 0; $i < sizeof($auth_options); $i++)
		{
			$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

			$l_auth_option = (!empty($user->lang['acl_' . $auth_options[$i]['auth_option']])) ? $user->lang['acl_' . $auth_options[$i]['auth_option']] : ucfirst(preg_replace('#.*?_#', '', $auth_options[$i]['auth_option']));


			
			$selected_yes = $selected_no = $selected_unset = '';
			if (!empty($_POST['presetsave']) || !empty($_POST['presetdel']))
			{
				$selected_yes = ($_POST['option'][$auth_settings[$i]['auth_option']] == ACL_ALLOW) ? ' checked="checked"' : '';
				$selected_no = ($_POST['option'][$auth_settings[$i]['auth_option']] == ACL_DENY) ? ' checked="checked"' : '';
				$selected_unset = ($_POST['option'][$auth_settings[$i]['auth_option']] == ACL_INHERIT) ? ' checked="checked"' : '';
			}
			else
			{
				$selected_yes = (isset($auth_settings[$auth_options[$i]['auth_option']]) && $auth_settings[$auth_options[$i]['auth_option']] == ACL_ALLOW) ? ' checked="checked"' : '';
				$selected_no = (isset($auth_settings[$auth_options[$i]['auth_option']]) && $auth_settings[$auth_options[$i]['auth_option']] == ACL_DENY) ? ' checked="checked"' : '';
				$selected_unset = (!isset($auth_settings[$auth_options[$i]['auth_option']]) || $auth_settings[$auth_options[$i]['auth_option']] == ACL_INHERIT) ? ' checked="checked"' : '';
			}


			// Output dependency links?
			$dep_x_yes = $dep_x_no = $dep_x_unset = '';
			if ($mode != 'deps')
			{
				$dep_x_open = ' <a class="gensmall" style="vertical-align:top" href="javascript:open_win(\'' . "admin_permissions.$phpEx$SID&amp;mode=deps&amp;type=$mode&amp;option=" . $auth_options[$i]['auth_option'] . "&amp;setting=";
				$dep_x_close = '\', 500, 500)" title="Set Dependency">X</a>';

				$dep_x_yes = $dep_x_open . ACL_ALLOW . $dep_x_close;
				$dep_x_no = $dep_x_open . ACL_DENY . $dep_x_close;
				$dep_x_unset = $dep_x_open . ACL_INHERIT . $dep_x_close;
			}

?>
			<tr>
				<td class="<?php echo $row_class; ?>" nowrap="nowrap"><?php echo $l_auth_option; ?>&nbsp;</td>

				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="option[<?php echo $auth_options[$i]['auth_option']; ?>]" value="<?php echo ACL_ALLOW; ?>"<?php echo $selected_yes; ?> /><?php echo $dep_x_yes; ?></td>

				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="option[<?php echo $auth_options[$i]['auth_option']; ?>]" value="<?php echo ACL_DENY; ?>"<?php echo $selected_no; ?> /><?php echo $dep_x_no; ?></td>

				<td class="<?php echo $row_class; ?>" align="center"><input type="radio" name="option[<?php echo $auth_options[$i]['auth_option']; ?>]" value="<?php echo ACL_INHERIT; ?>"<?php echo $selected_unset; ?> /><?php echo $dep_x_unset; ?></td>
			</tr>
<?php

		}

		// Subforum inheritance
		if (($sql_option_mode == 'f' || $sql_option_mode == 'm') && $mode != 'deps')
		{
			$children = get_forum_branch($forum_id, 'children', 'descending', false);

			if (!empty($children))
			{
?>
			<tr>
				<th colspan="4"><?php echo $user->lang['ACL_SUBFORUMS']; ?></th>
			</tr>
			<tr>
				<td class="row1" colspan="4"><table width="100%" cellspacing="1" cellpadding="0" border="0">
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
		if ($auth->acl_gets('a_events', 'a_cron') && $mode != 'deps')
		{
			$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
			<!-- tr>
				<th colspan="4"><?php echo $user->lang['RUN_HOW']; ?></th>
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
				<td class="cat" colspan="4" align="center"><input class="mainoption" type="submit" name="update" value="<?php echo $user->lang['UPDATE']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="cancel" value="<?php echo $user->lang['CANCEL']; ?>" /><input type="hidden" name="f" value="<?php echo $forum_id; ?>" /><input type="hidden" name="type" value="<?php echo $_POST['type']; ?>" /><?php echo $ug_hidden; ?></td>
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
				<td class="cat" colspan="4" align="center"><input class="liteoption" type="submit" name="presetsave" value="<?php echo $user->lang['SAVE']; ?>" /> &nbsp;<input class="liteoption" type="submit" name="presetdel" value="<?php echo $user->lang['DELETE']; ?>" /><input type="hidden" name="advanced" value="true" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php


page_footer();








//
//
// Add/Remove/Alter user/group settings
//
//

?>

<p><?php echo $l_title_explain; ?></p>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td align="center"><h1><?php echo $user->lang['USERS']; ?></h1></td>
		<td align="center"><h1><?php echo $user->lang['GROUPS']; ?></h1></td>
	</tr>
	<tr>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

	$sql = "SELECT DISTINCT u.user_id, u.username
		FROM " . USERS_TABLE . " u, " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o
		WHERE o.auth_option LIKE '" . $sql_option_mode . "_%'
			AND a.auth_option_id = o.auth_option_id
			$forum_sql
			AND u.user_id = a.user_id
		ORDER BY u.username, u.user_regdate ASC";
	$result = $db->sql_query($sql);

	$users = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$users .= '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
	}
	$db->sql_freeresult($result);

?>
			<tr>
				<th><?php echo $user->lang['MANAGE_USERS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><select style="width:280px" name="entries[]" multiple="multiple" size="5"><?php echo $users; ?></select></td>
			</tr>
			<tr>
				<td class="cat" align="center"><input class="liteoption" type="submit" name="delete" value="<?php echo $user->lang['DELETE']; ?>" /> &nbsp; <input class="liteoption" type="submit" name="advanced" value="<?php echo $user->lang['SET_OPTIONS']; ?>" /><input type="hidden" name="type" value="user" /><input type="hidden" name="f" value="<?php echo $forum_id; ?>" /><input type="hidden" name="option" value="<?php echo $sql_option_mode; ?>" /></td>
			</tr>
		</table></form></td>

		<td align="center"><form method="post" name="admingroups" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

	$sql = "SELECT DISTINCT g.group_id, g.group_name
		FROM " . GROUPS_TABLE . " g, " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o
		WHERE o.auth_option LIKE '" . $sql_option_mode . "_%'
			$forum_sql
			AND a.auth_option_id = o.auth_option_id
			AND g.group_id = a.group_id
		ORDER BY g.group_type DESC, g.group_name ASC";
	$result = $db->sql_query($sql);

	$groups = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$groups .= '<option value="' . $row['group_id'] . '">' . ((!empty($user->lang['G_' . $row['group_name']])) ? '* ' . $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	$db->sql_freeresult($result);

	$sql = "SELECT group_id, group_name
		FROM " . GROUPS_TABLE . "
		ORDER BY group_type DESC, group_name";
	$result = $db->sql_query($sql);

	$group_list = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$group_list .= '<option value="' . $row['group_id'] . '">' . ((!empty($user->lang['G_' . $row['group_name']])) ? '* ' . $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	$db->sql_freeresult($result);

?>
		<tr>
			<th><?php echo $user->lang['MANAGE_GROUPS']; ?></th>
		</tr>
		<tr>
			<td class="row1" align="center"><select style="width:280px" name="entries[]" multiple="multiple" size="5"><?php echo $groups; ?></select></td>
		</tr>
		<tr>
			<td class="cat" align="center"><input class="liteoption" type="submit" name="delete" value="<?php echo $user->lang['DELETE']; ?>" /> &nbsp; <input class="liteoption" type="submit" name="advanced" value="<?php echo $user->lang['SET_OPTIONS']; ?>" /><input type="hidden" name="type" value="group" /><input type="hidden" name="f" value="<?php echo $forum_id; ?>" /><input type="hidden" name="option" value="<?php echo $sql_option_mode; ?>" /></td>
		</tr>
	</table></form></td>

	</tr>
	<tr>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['ADD_USERS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><textarea cols="40" rows="4" name="entries"></textarea></td>
			</tr>
			<tr>
				<td class="cat" align="center"> <input type="submit" name="add" value="<?php echo $user->lang['SUBMIT']; ?>" class="mainoption" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="liteoption" />&nbsp; <input type="submit" name="usersubmit" value="<?php echo $user->lang['FIND_USERNAME']; ?>" class="liteoption" onclick="window.open('<?php echo "../memberlist.$phpEx$SID"; ?>&amp;mode=searchuser&amp;form=2&amp;field=entries', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;" /><input type="hidden" name="type" value="user" /><input type="hidden" name="advanced" value="1" /><input type="hidden" name="new" value="1" /><input type="hidden" name="f" value="<?php echo $forum_id; ?>" /></td>
			</tr>
		</table></form></td>

		<td><form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table width="90%" class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['ADD_GROUPS']; ?></th>
			</tr>
			<tr>
				<td class="row1" align="center"><select name="entries[]" multiple="multiple" size="4"><?php echo $group_list; ?></select></td>
			</tr>
			<tr>
				<td class="cat" align="center"> <input type="submit" name="add" value="<?php echo $user->lang['SUBMIT']; ?>" class="mainoption" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="liteoption" /><input type="hidden" name="type" value="group" /><input type="hidden" name="advanced" value="1" /><input type="hidden" name="new" value="1" /><input type="hidden" name="f" value="<?php echo $forum_id; ?>" /></td>
			</tr>
		</table></form></td>
	</tr>
</table>

<?php

	page_footer();







//
//
// Select a forum, user or group
//
//




?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain ?></p>

<form method="post" action="<?php echo "admin_permissions.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

// Mode specific markup
switch ($mode)
{
	case 'forums':
	case 'moderators':

?>
	<tr>
		<th align="center"><?php echo $user->lang['LOOK_UP_FORUM']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center">&nbsp;<select name="f"><?php echo make_forum_select(false, false, false); ?></select> &nbsp;<input type="submit" value="<?php echo $user->lang['LOOK_UP_FORUM']; ?>" class="mainoption" />&nbsp;</td>
	</tr>
<?php
		
		break;

	case 'users':
?>
	<tr>
		<th align="center"><?php echo $user->lang['Select_a_User']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><input type="text" class="post" name="username" maxlength="50" size="20" /> <input type="submit" name="submituser" value="<?php echo $user->lang['Look_up_user']; ?>" class="mainoption" /> <input type="submit" name="usersubmit" value="<?php echo $user->lang['FIND_USERNAME']; ?>" class="liteoption" onClick="window.open('<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username"; ?>', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;" /><input type="hidden" name="type" value="user" /></td>
	</tr>
<?php
		break;

	case 'groups':
		// Generate list of groups
		$sql = "SELECT group_id, group_name    
			FROM " . GROUPS_TABLE . " 
			ORDER BY group_type DESC";
		$result = $db->sql_query($sql);

		$group_options = '';
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$group_options .= (($group_options != '') ? ', ' : '') . '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

?>
	<tr>
		<th align="center"><?php echo $user->lang['SELECT_A_GROUP']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center">&nbsp;<select name="g"><?php echo $group_options; ?></select> &nbsp;<input type="submit" value="<?php echo $user->lang['LOOK_UP_GROUP']; ?>" class="mainoption" /><input type="hidden" name="type" value="group" />&nbsp;</td>
	</tr>
<?php
		break;

}

?>
</table></form>

<?php

page_footer();

?>