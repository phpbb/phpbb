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
	if (!$auth->acl_get('a_server'))
	{
		return;
	}

	$module['USER']['BOTS'] = basename(__FILE__) . $SID;

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Do we have permission?
if (!$auth->acl_get('a_server'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Set various vars
$submit = (isset($_POST['submit'])) ? true : false;
$action = request_var('action', '');
$mark	= request_var('mark', 0);
$id		= request_var('id', 0);

if (isset($_POST['add']))
{
	$action = 'add';
}

$error = array();

// User wants to do something, how inconsiderate of them!
switch ($action)
{
	case 'activate':
		if ($id || $mark)
		{
			$id = ($id) ? " = $id" : ' IN (' . implode(', ', $mark) . ')';
			$sql = 'UPDATE ' . BOTS_TABLE . " 
				SET bot_active = 1
				WHERE bot_id $id";
			$db->sql_query($sql);
		}

		$cache->destroy('bots');
		break;

	case 'deactivate':
		if ($id || $mark)
		{
			$id = ($id) ? " = $id" : ' IN (' . implode(', ', $mark) . ')';
			$sql = 'UPDATE ' . BOTS_TABLE . " 
				SET bot_active = 0
				WHERE bot_id $id";
			$db->sql_query($sql);
		}

		$cache->destroy('bots');
		break;

	case 'delete':
		if ($id || $mark)
		{
			// We need to delete the relevant user, usergroup and bot entries ...
			$id = ($id) ? " = $id" : ' IN (' . implode(', ', $mark) . ')';
			$sql = 'SELECT bot_name, user_id 
				FROM ' . BOTS_TABLE . " 
				WHERE bot_id $id";
			$result = $db->sql_query($sql);

			$user_id_ary = $bot_name_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$user_id_ary[] = (int) $row['user_id'];
				$bot_name_ary[] = $row['bot_name'];
			}
			$db->sql_freeresult($result);

			$db->sql_transaction();

			$sql = 'DELETE FROM ' . BOTS_TABLE . " 
				WHERE bot_id $id";
			$db->sql_query($sql);

			foreach (array(USERS_TABLE, USER_GROUP_TABLE) as $table)
			{
				$sql = "DELETE FROM $table
					WHERE user_id IN (" . implode(', ', $user_id_ary) . ')';
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');

			$cache->destroy('bots');

			add_log('admin', 'LOG_BOT_DELETE', implode(', ', $bot_name_ary));
			trigger_error($user->lang['BOT_DELETED']);
		}
		break;

	case 'edit':
	case 'add':
		$bot_name	= request_var('bot_name', '');
		$bot_agent	= request_var('bot_agent', '');
		$bot_ip		= request_var('bot_ip', '');
		$bot_active = request_var('bot_active', true);
		$bot_lang	= request_var('bot_lang', $config['default_lang']);
		$bot_style	= request_var('bot_style' , $config['default_style']);

		if ($submit)
		{
			if (!$bot_agent && !$bot_ip)
			{
				$error[] = $user->lang['ERR_BOT_NO_MATCHES'];
			}
	
			if ($bot_ip && !preg_match('#^[\d\.,:]+$#', $bot_ip))
			{
				if (!$ip_list = gethostbynamel($bot_ip))
				{
					$error[] = $user->lang['ERR_BOT_NO_IP'];
				}
				else
				{
					$bot_ip = implode(',', $ip_list);
				}
			}
			$bot_ip = str_replace(' ', '', $bot_ip);

			if (!sizeof($error))
			{
				$db->sql_transaction();

				// New bot? Create a new user and group entry
				if ($action == 'add')
				{
					$sql = 'SELECT group_id, group_colour 
						FROM ' . GROUPS_TABLE . " 
						WHERE group_name = 'BOTS' 
							AND group_type = " . GROUP_SPECIAL;
					$result = $db->sql_query($sql);

					if (!extract($db->sql_fetchrow($result)))
					{
						trigger_error($user->lang['NO_GROUP']);
					}
					$db->sql_freeresult($result);

					$sql = 'INSERT INTO ' . USERS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
						'group_id'		=> (int) $group_id, 
						'username'		=> (string) $bot_name, 
						'user_type'		=> (int) USER_IGNORE, 
						'user_colour'	=> (string) $group_colour,
						'user_lang'		=> (string) $bot_lang, 
						'user_style'	=> (int) $bot_style,
						'user_options'	=> 0)
					);
					$db->sql_query($sql);

					$user_id = $db->sql_nextid();

					// Add to Bots usergroup
					$sql = 'INSERT INTO ' . USER_GROUP_TABLE . ' ' . $db->sql_build_array('INSERT', array(
						'user_id'	=> $user_id, 
						'group_id'	=> $group_id)
					);
					$db->sql_query($sql);

					$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
						'user_id'		=> (int) $user_id,
						'bot_name'		=> (string) $bot_name, 
						'bot_active'	=> (int) $bot_active, 
						'bot_agent'		=> (string) $bot_agent,
						'bot_ip'		=> (string) $bot_ip,)
					);

					$log = 'ADDED';
				}
				else
				{
					$sql = 'SELECT user_id 
						FROM ' . BOTS_TABLE . " 
						WHERE bot_id = $id";
					$result = $db->sql_query($sql);

					if (!extract($db->sql_fetchrow($result)))
					{
						trigger_error($user->lang['NO_BOT']);
					}

					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
						'user_style'	=> (int) $bot_style,
						'user_lang'		=> (string) $bot_lang,)
					) . " WHERE user_id = $user_id";
					$db->sql_query($sql);

					$sql = 'UPDATE ' . BOTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
						'bot_name'		=> (string) $bot_name, 
						'bot_active'	=> (int) $bot_active, 
						'bot_agent'		=> (string) $bot_agent,
						'bot_ip'		=> (string) $bot_ip,)
					) . " WHERE bot_id = $id";

					$log = 'UPDATED';
				}
				$db->sql_query($sql);

				$db->sql_transaction('commit');

				$cache->destroy('bots');
		
				add_log('admin', 'LOG_BOT_' . $log, $bot_name);
				trigger_error($user->lang['BOT_' . $log]);
			}
		}
		else if ($id)
		{
			$sql = 'SELECT b.*, u.user_lang, u.user_style 
				FROM ' . BOTS_TABLE . ' b, ' . USERS_TABLE . " u
				WHERE b.bot_id = $id
					AND u.user_id = b.user_id";
			$result = $db->sql_query($sql);

			if (!extract($db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_BOT']);
			}
			$db->sql_freeresult($result);

			$bot_lang = $user_lang;
			$bot_style = $user_style;
		}

		$s_active_options = '';
		foreach (array('0' => 'NO', '1' => 'YES') as $value => $lang)
		{
			$selected = ($bot_active == $value) ? ' selected="selected"' : '';
			$s_active_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		$style_select = style_select($bot_style, true);
		$lang_select = language_select($bot_lang);

		$l_title = ($action == 'edit') ? 'EDIT' : 'ADD';

		// Output relevant page
		adm_page_header($user->lang['BOT_' . $l_title]);

?>

<h1><?php echo $user->lang['BOT_' . $l_title]; ?></h1>

<p><?php echo $user->lang['BOT_EDIT_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_bots.$phpEx$SID&amp;action=$action&amp;id=$id"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['BOT_' . $l_title]; ?></th>
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

?>
	<tr>
		<td class="row1" width="40%"><b class="genmed"><?php echo $user->lang['BOT_NAME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOT_NAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="bot_name" size="30" maxlength="255" value="<?php echo $bot_name; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b class="genmed"><?php echo $user->lang['BOT_STYLE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOT_STYLE_EXPLAIN']; ?></span></td>
		<td class="row2"><select name="bot_style"><?php echo $style_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b class="genmed"><?php echo $user->lang['BOT_LANG']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOT_LANG_EXPLAIN']; ?></span></td>
		<td class="row2"><select name="bot_lang"><?php echo $lang_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b class="genmed"><?php echo $user->lang['BOT_ACTIVE']; ?>: </b></td>
		<td class="row2"><select name="bot_active"><?php echo $s_active_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b class="genmed"><?php echo $user->lang['BOT_AGENT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOT_AGENT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="bot_agent" size="30" maxlength="255" value="<?php echo $bot_agent; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b class="genmed"><?php echo $user->lang['BOT_IP']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOT_IP_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="bot_ip" size="30" maxlength="255" value="<?php echo $bot_ip; ?>" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<?php

		adm_page_footer();

		break;
}

// Output relevant page
adm_page_header($user->lang['BOTS']);

?>

<h1><?php echo $user->lang['BOTS']; ?></h1>

<p><?php echo $user->lang['BOTS_EXPLAIN']; ?></p>

<form method="post" action="<?php "admin_bots.$phpEx$SID"; ?>"><table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th nowrap="nowrap"><?php echo $user->lang['BOT_NAME']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['BOT_LAST_VISIT']; ?></th>
		<th colspan="3" nowrap="nowrap"><?php echo $user->lang['OPTIONS']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php

$s_options = '';
foreach (array('activate' => 'BOT_ACTIVATE', 'deactivate' => 'BOT_DEACTIVATE', 'delete' => 'DELETE') as $value => $lang)
{
	$s_options .= '<option value="' . $value . '">' . $user->lang[$lang] . '</option>';
}

$sql = 'SELECT b.bot_id, b.bot_name, b.bot_active, u.user_lastvisit 
	FROM ' . BOTS_TABLE . ' b, ' . USERS_TABLE . ' u
	WHERE u.user_id = b.user_id
	ORDER BY u.user_lastvisit DESC';
$result = $db->sql_query($sql);

$row_class = '';
while ($row = $db->sql_fetchrow($result))
{
	$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

	$active_lang = (!$row['bot_active']) ? 'BOT_ACTIVATE' : 'BOT_DEACTIVATE';
	$active_value = (!$row['bot_active']) ? 'activate' : 'deactivate';
	$id = $row['bot_id'];

?>	
	<tr>
		<td class="<?php echo $row_class; ?>" width="50%"><?php echo $row['bot_name']; ?></td>
		<td class="<?php echo $row_class; ?>" width="15%" align="center" nowrap="nowrap">&nbsp;<?php echo ($row['user_lastvisit']) ?  $user->format_date($row['user_lastvisit']) : $user->lang['BOT_NEVER']; ?>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" width="1%"align="center">&nbsp;<a href="<?php echo "admin_bots.$phpEx$SID&amp;id=$id&amp;action=$active_value"; ?>"><?php echo $user->lang[$active_lang]; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" width="1%" align="center">&nbsp;<a href="<?php echo "admin_bots.$phpEx$SID&amp;id=$id&amp;action=edit"; ?>"><?php echo $user->lang['EDIT']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" width="1%" align="center">&nbsp;<a href="<?php echo "admin_bots.$phpEx$SID&amp;id=$id&amp;action=delete"; ?>"><?php echo $user->lang['DELETE']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" width="1%" align="center"><input type="checkbox" name="mark[]" value="<?php echo $id; ?>" /></td>
	</tr>
<?php

}
$db->sql_freeresult($result);

?>
	<tr>
		<td class="cat" colspan="6"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td><input class="btnlite" type="submit" name="add" value="<?php echo $user->lang['BOT_ADD']; ?>" /></td>
				<td align="right"><select name="action"><?php echo $s_options; ?></select> <input class="btnlite" type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>
<?php

adm_page_footer();

?>