<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : functions_privmsgs.php
// STARTED   : Sun Apr 18, 2004
// COPYRIGHT : © 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// Define Rule processing schema
// NOTE: might change

/*
	Ability to simply add own rules by doing three things:
		1) Add an appropiate constant
		2) Add a new check array to the global_privmsgs_rules variable and the condition array (if one is required)
		3) Add a new language variable to ucp.php
		
		The user is then able to select the new rule. It will be checked against and handled as specified.
		To add new actions (yes, checks can be added here too) to the rule management, the core code has to be modified.
*/

define('RULE_IS_LIKE', 1); // Is Like
define('RULE_IS_NOT_LIKE', 2); // Is Not Like
define('RULE_IS', 3); // Is
define('RULE_IS_NOT', 4); // Is Not
define('RULE_BEGINS_WITH', 5); // Begins with
define('RULE_ENDS_WITH', 6); // Ends with
define('RULE_IS_FRIEND', 7); // Is Friend
define('RULE_IS_FOE', 8); // Is Foe
define('RULE_IS_USER', 9); // Is User
define('RULE_IS_GROUP', 10); // Is In Usergroup
define('RULE_ANSWERED', 11); // Answered
define('RULE_FORWARDED', 12); // Forwarded
define('RULE_REPORTED', 13); // Reported
define('RULE_TO_GROUP', 14); // Usergroup
define('RULE_TO_ME', 15); // Me

define('ACTION_PLACE_INTO_FOLDER', 1);
define('ACTION_MARK_AS_READ', 2);
define('ACTION_MARK_AS_IMPORTANT', 3);
define('ACTION_DELETE_MESSAGE', 4);

define('CHECK_SUBJECT', 1);
define('CHECK_SENDER', 2);
define('CHECK_MESSAGE', 3);
define('CHECK_STATUS', 4);
define('CHECK_TO', 5);

$global_privmsgs_rules = array(
	CHECK_SUBJECT	=> array(
		RULE_IS_LIKE		=> array('check0' => 'message_subject', 'function' => 'preg_match("/" . preg_quote({STRING}) . "/i", {CHECK0})'),
		RULE_IS_NOT_LIKE	=> array('check0' => 'message_subject', 'function' => '!(preg_match("/" . preg_quote({STRING}) . "/i", {CHECK0}))'),
		RULE_IS				=> array('check0' => 'message_subject', 'function' => '{CHECK0} == {STRING}'),
		RULE_IS_NOT			=> array('check0' => 'message_subject', 'function' => '{CHECK0} != {STRING}'),
		RULE_BEGINS_WITH	=> array('check0' => 'message_subject', 'function' => 'preg_match("/^" . preg_quote({STRING}) . "/i", {CHECK0})'),
		RULE_ENDS_WITH		=> array('check0' => 'message_subject', 'function' => 'preg_match("/" . preg_quote({STRING}) . "$/i", {CHECK0})')),

	CHECK_SENDER	=> array(
		RULE_IS_LIKE		=> array('check0' => 'username', 'function' => 'preg_match("/" . preg_quote({STRING}) . "/i", {CHECK0})'),
		RULE_IS_NOT_LIKE	=> array('check0' => 'username', 'function' => '!(preg_match("/" . preg_quote({STRING}) . "/i", {CHECK0}))'),
		RULE_IS				=> array('check0' => 'username', 'function' => '{CHECK0} == {STRING}'),
		RULE_IS_NOT			=> array('check0' => 'username', 'function' => '{CHECK0} != {STRING}'),
		RULE_BEGINS_WITH	=> array('check0' => 'username', 'function' => 'preg_match("/^" . preg_quote({STRING}) . "/i", {CHECK0})'),
		RULE_ENDS_WITH		=> array('check0' => 'username', 'function' => 'preg_match("/" . preg_quote({STRING}) . "$/i", {CHECK0})'),
		RULE_IS_FRIEND		=> array('check0' => 'friend', 'function' => '{CHECK0} == 1'),
		RULE_IS_FOE			=> array('check0' => 'foe', 'function' => '{CHECK0} == 1'),
		RULE_IS_USER		=> array('check0' => 'author_id', 'function' => '{CHECK0} == {USER_ID}'),
		RULE_IS_GROUP		=> array('check0' => 'author_in_group', 'function' => 'in_array({GROUP_ID}, {CHECK0})')),
		
	CHECK_MESSAGE	=> array(
		RULE_IS_LIKE		=> array('check0' => 'message_text', 'function' => 'preg_match("/" . preg_quote({STRING}) . "/i", {CHECK0})'),
		RULE_IS_NOT_LIKE	=> array('check0' => 'message_text', 'function' => '!(preg_match("/" . preg_quote({STRING}) . "/i", {CHECK0}))'),
		RULE_IS				=> array('check0' => 'message_text', 'function' => '{CHECK0} == {STRING}'),
		RULE_IS_NOT			=> array('check0' => 'message_text', 'function' => '{CHECK0} != {STRING}')),
		
	CHECK_STATUS	=> array(
		RULE_ANSWERED		=> array('check0' => 'replied', 'function' => '{CHECK0} == 1'),
		RULE_FORWARDED		=> array('check0' => 'forwarded', 'function' => '{CHECK0} == 1'),
		RULE_REPORTED		=> array('check0' => 'message_reported', 'function' => '{CHECK0} == 1')),
		
	CHECK_TO		=> array(
		RULE_TO_GROUP		=> array('check0' => 'to', 'check1' => 'bcc', 'check2' => 'user_in_group', 'function' => 'in_array("g_" . {CHECK2}, {CHECK0}) || in_array("g_" . {CHECK2}, {CHECK1})'),
		RULE_TO_ME			=> array('check0' => 'to', 'check1' => 'bcc', 'function' => 'in_array("u_" . $user_id, {CHECK0}) || in_array("u_" . $user_id, {CHECK1})'))
);

// This is for defining which condition fields to show for which Rule
$global_rule_conditions = array(
	RULE_IS_LIKE		=> 'text',
	RULE_IS_NOT_LIKE	=> 'text',
	RULE_IS				=> 'text',
	RULE_IS_NOT			=> 'text',
	RULE_BEGINS_WITH	=> 'text',
	RULE_ENDS_WITH		=> 'text',
	RULE_IS_USER		=> 'user',
	RULE_IS_GROUP		=> 'group'
);

// Get number of unread messages from all folder
function get_unread_pm($user_id)
{
	global $db;

	$unread_pm = array();

	$sql = 'SELECT folder_id, SUM(unread) as sum_unread
		FROM ' . PRIVMSGS_TO_TABLE . "
		WHERE user_id = $user_id
			AND folder_id <> " . PRIVMSGS_OUTBOX . '
		GROUP BY folder_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['sum_unread'])
		{
			$unread_pm[$row['folder_id']] = $row['sum_unread'];
		}
	}
	$db->sql_freeresult($result);

	return $unread_pm;
}

// Get all folder
function get_folder($user_id, &$folder)
{
	global $db, $user;

	if (!is_array($folder))
	{
		$folder = array();
	}

	$sql = 'SELECT folder_id, COUNT(msg_id) as num_messages
		FROM ' . PRIVMSGS_TO_TABLE . "
		WHERE user_id = $user_id
			AND folder_id <> " . PRIVMSGS_NO_BOX . '
		GROUP BY folder_id';
	$result = $db->sql_query($sql);

	$num_messages = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$num_messages[(int) $row['folder_id']] = $row['num_messages'];
	}
	$db->sql_freeresult($result);

	if (!isset($num_messages[PRIVMSGS_OUTBOX]))
	{
		$num_messages[PRIVMSGS_OUTBOX] = 0;
	}

	$folder[PRIVMSGS_INBOX] = array('folder_name' => $user->lang['PM_INBOX'], 'num_messages' => (int) $num_messages[PRIVMSGS_INBOX]);

	$sql = 'SELECT folder_id, folder_name, pm_count
		FROM ' . PRIVMSGS_FOLDER_TABLE . "
			WHERE user_id = $user_id";
	$result = $db->sql_query($sql);
			
	while ($row = $db->sql_fetchrow($result))
	{
		$folder[$row['folder_id']] = array('folder_name' => $row['folder_name'], 'num_messages' => $row['pm_count']);
	}
	$db->sql_freeresult($result);

	$folder[PRIVMSGS_OUTBOX] = array('folder_name' => $user->lang['PM_OUTBOX'], 'num_messages' => (int) $num_messages[PRIVMSGS_OUTBOX]);
	$folder[PRIVMSGS_SENTBOX] = array('folder_name' => $user->lang['PM_SENTBOX'], 'num_messages' => (int) $num_messages[PRIVMSGS_SENTBOX]);

	return;
}

// Delete Messages From Sentbox - we are doing this here because this saves us a bunch of checks and queries
function clean_sentbox($num_sentbox_messages)
{
	global $db, $user, $config;

	$message_limit = (!$user->data['group_message_limit']) ? $config['pm_max_msgs'] : $user->data['group_message_limit'];

	// Check Message Limit - 
	if ($message_limit && $num_sentbox_messages > $message_limit)
	{
		// Delete old messages
		$sql = 'SELECT t.msg_id
			FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p
			WHERE t.msg_id = p.msg_id
				AND t.user_id = ' . $user->data['user_id'] . '
				AND t.folder_id = ' . PRIVMSGS_SENTBOX . '
			ORDER BY p.message_time ASC';
		$result = $db->sql_query_limit($sql, ($num_sentbox_messages - $message_limit));

		$delete_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$delete_ids[] = $row['msg_id'];
		}
		$db->sql_freeresult($result);
		delete_pm($user->data['user_id'], $delete_ids, PRIVMSGS_SENTBOX);
	}
}

// Check Rule against Message Informations
function check_rule(&$rules, &$rule_row, &$message_row, $user_id)
{
	global $user, $config;

	if (!isset($rules[$rule_row['rule_check']][$rule_row['rule_connection']]))
	{
		return false;
	}

	$check_ary = $rules[$rule_row['rule_check']][$rule_row['rule_connection']];

	// Replace Check Literals
	$evaluate = $check_ary['function'];
	$evaluate = preg_replace('/{(CHECK[0-9])}/', '$message_row[$check_ary[strtolower("\1")]]', $evaluate);

	// Replace Rule Literals
	$evaluate = preg_replace('/{(STRING|USER_ID|GROUP_ID)}/', '$rule_row["rule_" . strtolower("\1")]', $evaluate);

	// Eval Statement
	$result = false;
	eval('$result = (' . $evaluate . ') ? true : false;');
		
	if (!$result)
	{
		return false;
	}

	switch ($rule_row['rule_action'])
	{
		case ACTION_PLACE_INTO_FOLDER:
			return array('action' => $rule_row['rule_action'], 'folder_id' => $rule_row['rule_folder_id']);
			break;
		case ACTION_MARK_AS_READ:
		case ACTION_MARK_AS_IMPORTANT:
		case ACTION_DELETE_MESSAGE:
			return array('action' => $rule_row['rule_action'], 'unread' => $row['unread'], 'important' => $row['important']);
			break;
		default:
			return false;
	}

	return false;
}

// Place new messages into appropiate folder
function place_pm_into_folder(&$global_privmsgs_rules, $release = false)
{
	global $db, $user, $config;

	if (!$user->data['user_new_privmsg'])
	{
		return;
	}

	$user_new_privmsg = (int) $user->data['user_new_privmsg'];
	$user_message_rules = (int) $user->data['user_message_rules'];
	$user_id = (int) $user->data['user_id'];

	$user_rules = $zebra = array();
	if ($user_message_rules)
	{
		$sql = 'SELECT * 
			FROM ' . PRIVMSGS_RULES_TABLE . "
			WHERE user_id = $user_id";
		$result = $db->sql_query($sql);

		$user_rules = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		if (sizeof($user_rules))
		{
			$sql = 'SELECT zebra_id, friend, foe
				FROM ' . ZEBRA_TABLE . "
				WHERE user_id = $user_id";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$zebra[$row['zebra_id']] = $row;
			}
			$db->sql_freeresult($result);
		}
	}

	if ($release)
	{
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . ' 
			SET folder_id = ' . PRIVMSGS_NO_BOX . '
			WHERE folder_id = ' . PRIVMSGS_HOLD_BOX . "
				AND user_id = $user_id";
		$db->sql_query($sql);
	}

	// Get those messages not yet placed into any box
	// NOTE: Expand Group Information to all groups the user/author is in? 
	$sql = 'SELECT t.*, p.*, u.username, u.group_id as author_in_group
		FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . " u
		WHERE t.user_id = $user_id
			AND p.author_id = u.user_id
			AND t.folder_id = " . PRIVMSGS_NO_BOX . '
			AND t.msg_id = p.msg_id';
	$result = $db->sql_query($sql);

	$action_ary = $move_into_folder = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$row['to']		= explode(':', $row['to_address']);
		$row['bcc']		= explode(':', $row['bcc_address']);
		$row['friend']	= (isset($zebra[$row['author_id']])) ? $zebra[$row['author_id']]['friend'] : 0;
		$row['foe']		= (isset($zebra[$row['author_id']])) ? $zebra[$row['author_id']]['foe'] : 0;
		$row['user_in_group'] = $user->data['group_id'];
						
		// Check Rule - this should be very quick since we have all informations we need
		$is_match = false;
		foreach ($user_rules as $rule_row)
		{
			if (($action = check_rule($global_privmsgs_rules, $rule_row, $row, $user_id)) !== false)
			{
				$is_match = true;
				$action_ary[$row['msg_id']][] = $action;
			}
		}
	
		if (!$is_match)
		{
			$action_ary[$row['msg_id']][] = array('action' => false);
			$move_into_folder[PRIVMSGS_INBOX][] = $row['msg_id'];
		}
	}
	$db->sql_freeresult($result);

	// We place actions into arrays, to save queries.
	$num_new = $num_unread = 0;
	$sql = $unread_ids = $delete_ids = $important_ids = array();

	foreach ($action_ary as $msg_id => $msg_ary)
	{
		// It is allowed to execute actions more than once, except placing messages into folder
		$folder_action = false;

		foreach ($msg_ary as $pos => $rule_ary)
		{
			if ($folder_action && $rule_ary['action'] == ACTION_PLACE_INTO_FOLDER)
			{
				continue;
			}
	
			switch ($rule_ary['action'])
			{
				case ACTION_PLACE_INTO_FOLDER:
					$folder_action = true;
					$_folder_id = (int) $rule_ary['folder_id'];
					$move_into_folder[$_folder_id][] = $msg_id;
					$num_new++;
					break;

				case ACTION_MARK_AS_READ:
					if ($rule_ary['unread'])
					{
						$unread_ids[] = $msg_id;
					}
					$move_into_folder[PRIVMSGS_INBOX][] = $msg_id;
					break;

				case ACTION_DELETE_MESSAGE:
					$delete_ids[] = $msg_id;
					break;

				case ACTION_MARK_AS_IMPORTANT:
					if (!$rule_ary['important'])
					{
						$important_ids[] = $msg_id;
					}
					$move_into_folder[PRIVMSGS_INBOX][] = $msg_id;
					break;

				default:
			}
		}
	}

	$num_new += sizeof(array_unique($delete_ids));
	$num_unread += sizeof(array_unique($delete_ids));
	$num_unread += sizeof(array_unique($unread_ids));

	// Do not change the order of processing
	// The number of queries needed to be executed here highly depends on the defined rules and are
	// only gone through if new messages arrive.
	$num_not_moved = 0;

	// Delete messages
	if (sizeof($delete_ids))
	{
		delete_pm($user_id, $delete_ids, PRIVMSGS_NO_BOX);
	}

	// Set messages to Unread
	if (sizeof($unread_ids))
	{
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . ' 
			SET unread = 0
			WHERE msg_id IN (' . implode(', ', $unread_ids) . ")
				AND user_id = $user_id
				AND folder_id = " . PRIVMSGS_NO_BOX;
		$db->sql_query($sql);
	}

	// mark messages as important
	if (sizeof($important_ids))
	{
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
			SET marked = !marked
			WHERE folder_id = ' . PRIVMSGS_NO_BOX . "
				AND user_id = $user_id
				AND msg_id IN (" . implode(', ', $important_ids) . ')';
		$db->sql_query($sql);
	}

	// Move into folder
	$folder = array();

	if (sizeof($move_into_folder))
	{
		// Determine Full Folder Action - we need the move to folder id later eventually
		$full_folder_action = ($user->data['user_full_folder'] == FULL_FOLDER_NONE) ? ($config['full_folder_action'] - (FULL_FOLDER_NONE*(-1))) : $user->data['user_full_folder'];

		$sql = 'SELECT folder_id, pm_count 
			FROM ' . PRIVMSGS_FOLDER_TABLE . '
			WHERE folder_id IN (' . implode(', ', array_keys($move_into_folder)) . (($full_folder_action >= 0) ? ', ' . $full_folder_action : '') . ")
				AND user_id = $user_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$folder[(int) $row['folder_id']] = (int) $row['pm_count'];
		}
		$db->sql_freeresult($result);

		if (in_array(PRIVMSGS_INBOX, array_keys($move_into_folder)))
		{
			$sql = 'SELECT folder_id, COUNT(msg_id) as num_messages
				FROM ' . PRIVMSGS_TO_TABLE . "
				WHERE user_id = $user_id
					AND folder_id = " . PRIVMSGS_INBOX . "
				GROUP BY folder_id";
			$result = $db->sql_query_limit($sql, 1);
			
			$folder[PRIVMSGS_INBOX] = (int) $db->sql_fetchfield('num_messages', 0, $result);
			$db->sql_freeresult($result);			
		}
	}

	// Here we have ideally only one folder to move into
	foreach ($move_into_folder as $folder_id => $msg_ary)
	{
		$message_limit = (!$user->data['group_message_limit']) ? $config['pm_max_msgs'] : $user->data['group_message_limit'];
		$dest_folder = $folder_id;
		$full_folder_action = FULL_FOLDER_NONE;

		// Check Message Limit - we calculate with the complete array, most of the time it is one message
		// But we are making sure that the other way around works too (more messages in queue than allowed to be stored)
		if ($message_limit && $folder[$folder_id] && ($folder[$folder_id] + sizeof($msg_ary)) > $message_limit)
		{
			$full_folder_action = ($user->data['user_full_folder'] == FULL_FOLDER_NONE) ? ($config['full_folder_action'] - (FULL_FOLDER_NONE*(-1))) : $user->data['user_full_folder'];

			// If destination folder itself is full...
			if ($full_folder_action >= 0 && ($folder[$full_folder_action] + sizeof($msg_ary)) > $message_limit)
			{
				$full_folder_action = $config['full_folder_action'] - (FULL_FOLDER_NONE*(-1));
			}

			// If Full Folder Action is to move to another folder, we simply adjust the destination folder
			if ($full_folder_action >= 0)
			{
				$dest_folder = $full_folder_action;
			}
			else if ($full_folder_action == FULL_FOLDER_DELETE)
			{
				// Delete some messages ;)
				$sql = 'SELECT t.msg_id
					FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . " p
					WHERE t.msg_id = p.msg_id
						AND t.user_id = $user_id
						AND t.folder_id = $dest_folder
					ORDER BY p.message_time ASC";
				$result = $db->sql_query_limit($sql, (($folder[$dest_folder] + sizeof($msg_ary)) - $message_limit));

				$delete_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$delete_ids[] = $row['msg_id'];
				}
				$db->sql_freeresult($result);
				delete_pm($user_id, $delete_ids, $dest_folder);
			}
		}
		
		if ($full_folder_action < 0)
		{
			$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . " 
				SET folder_id = $dest_folder, new = 0
				WHERE folder_id = " . PRIVMSGS_NO_BOX . "
					AND user_id = $user_id
					AND new = 1
					AND msg_id IN (" . implode(', ', $msg_ary) . ')';
			$db->sql_query($sql);

			if ($dest_folder != PRIVMSGS_INBOX)
			{
				$sql = 'UPDATE ' . PRIVMSGS_FOLDER_TABLE . '
					SET pm_count = pm_count + ' . (int) $db->sql_affectedrows() . "
					WHERE folder_id = $dest_folder
						AND user_id = $user_id";
				$db->sql_query($sql);
			}
			else
			{
				$num_new += $db->sql_affectedrows();
			}
		}
		else
		{
			$num_not_moved += sizeof($msg_ary);
			$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . ' 
				SET folder_id = ' . PRIVMSGS_HOLD_BOX . '
				WHERE folder_id = ' . PRIVMSGS_NO_BOX . "
					AND user_id = $user_id
					AND msg_id IN (" . implode(', ', $msg_ary) . ')';
			$db->sql_query($sql);
		}
	}

	if (sizeof($action_ary))
	{
		// Move from OUTBOX to SENTBOX
		// We are not checking any full folder status here... SENTBOX is a special treatment (old messages get deleted)
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . ' 
			SET folder_id = ' . PRIVMSGS_SENTBOX . '
			WHERE folder_id = ' . PRIVMSGS_OUTBOX . '
				AND msg_id IN (' . implode(', ', array_keys($action_ary)) . ')';
		$db->sql_query($sql);
	}

	// Update unread and new status field
	if ($num_unread || $num_new)
	{
		$set_sql = ($num_unread) ? 'user_unread_privmsg = user_unread_privmsg - ' . $num_unread : '';
		if ($num_new)
		{
			$set_sql .= ($set_sql != '') ? ', ' : '';
			$set_sql .= 'user_new_privmsg = user_new_privmsg - ' . $num_new;
		}
		
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET $set_sql WHERE user_id = $user_id");
		$user->data['user_new_privmsg'] -= $num_new;
		$user->data['user_unread_privmsg'] -= $num_unread;
	}

	return $num_not_moved;
}

// Move PM from one to another folder
function move_pm($user_id, $message_limit, $move_msg_ids, $dest_folder, $cur_folder_id)
{
	global $db, $user;
	global $phpbb_root_path, $phpEx, $SID;

	$num_moved = 0;

	if (!is_array($move_msg_ids))
	{
		$move_msg_ids = array($move_msg_ids);
	}

	if (sizeof($move_msg_ids) && !in_array($dest_folder, array(PRIVMSGS_NO_BOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX)) && 
		!in_array($cur_folder_id, array(PRIVMSGS_NO_BOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX)) && $cur_folder_id != $dest_folder)
	{
		// We have to check the destination folder ;)
		if ($dest_folder != PRIVMSGS_INBOX)
		{
			$sql = 'SELECT folder_id, folder_name, pm_count
				FROM ' . PRIVMSGS_FOLDER_TABLE . "
				WHERE folder_id = $dest_folder
					AND user_id = $user_id";
			$result = $db->sql_query($sql);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error('NOT_AUTHORIZED');
			}
			$db->sql_freeresult($result);

			if ($row['pm_count'] + sizeof($move_msg_ids) > $message_limit)
			{
				$message = sprintf($user->lang['NOT_ENOUGH_SPACE_FOLDER'], $row['folder_name']) . '<br /><br />';
				$message .= sprintf($user->lang['CLICK_RETURN_FOLDER'], "<a href=\"{$phpbb_root_path}ucp.$phpEx$SID&amp;i=pm&amp;folder={$row['folder_id']}\">", '</a>', $row['folder_name']);
				trigger_error($message);
			}
		}
		else
		{
			$sql = 'SELECT COUNT(msg_id) as num_messages
				FROM ' . PRIVMSGS_TO_TABLE . '
				WHERE folder_id = ' . PRIVMSGS_INBOX . "
					AND user_id = $user_id";
			$result = $db->sql_query($sql);
			if ($db->sql_fetchfield('num_messages', 0, $result) + sizeof($move_msg_ids) > $message_limit)
			{
				$message = sprintf($user->lang['NOT_ENOUGH_SPACE_FOLDER'], $user->lang['PM_INBOX']) . '<br /><br />';
				$message .= sprintf($user->lang['CLICK_RETURN_FOLDER'], "<a href=\"{$phpbb_root_path}ucp.$phpEx$SID&amp;i=pm&amp;folder=inbox\">", '</a>', $user->lang['PM_INBOX']);
				trigger_error($message);
			}
		}

		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . "
			SET folder_id = $dest_folder
			WHERE folder_id = $cur_folder_id
				AND user_id = $user_id
				AND msg_id IN (" . implode(', ', $move_msg_ids) . ')';
		$db->sql_query($sql);
		$num_moved = $db->sql_affectedrows();

		// Update pm counts
		if ($num_moved)
		{
			if (!in_array($cur_folder_id, array(PRIVMSGS_INBOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX)))
			{
				$sql = 'UPDATE ' . PRIVMSGS_FOLDER_TABLE . "
					SET pm_count = pm_count - $num_moved
					WHERE folder_id = $cur_folder_id
						AND user_id = $user_id";
				$db->sql_query($sql);
			}

			if ($dest_folder != PRIVMSGS_INBOX)
			{
				$sql = 'UPDATE ' . PRIVMSGS_FOLDER_TABLE . "
					SET pm_count = pm_count + $num_moved
					WHERE folder_id = $dest_folder
						AND user_id = $user_id";
				$db->sql_query($sql);
			}
		}
	}

	return $num_moved;
}

// Update unread message status
function update_unread_status($unread, $msg_id, $user_id, $folder_id)
{
	if (!$unread)
	{
		return;
	}

	global $db;

	$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . " 
		SET unread = 0
		WHERE msg_id = $msg_id
			AND user_id = $user_id
			AND folder_id = $folder_id";
	$db->sql_query($sql);

	$sql = 'UPDATE ' . USERS_TABLE . " 
		SET user_unread_privmsg = user_unread_privmsg - 1
		WHERE user_id = $user_id";
	$db->sql_query($sql);
}

// Handle all actions possible with marked messages
function handle_mark_actions($user_id, $mark_action)
{
	global $db, $user, $_POST, $phpbb_root_path, $SID, $phpEx;

	$msg_ids		= (isset($_POST['marked_msg_id'])) ? array_map('intval', $_POST['marked_msg_id']) : array();
	$cur_folder_id	= request_var('cur_folder_id', PRIVMSGS_NO_BOX);
	$confirm		= (isset($_POST['confirm'])) ? true : false;

	if (!sizeof($msg_ids))
	{
		return;
	}

	switch ($mark_action)
	{
		case 'mark_important':

			$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . "
				SET marked = !marked
				WHERE folder_id = $cur_folder_id
					AND user_id = $user_id
					AND msg_id IN (" . implode(', ', $msg_ids) . ')';
			$db->sql_query($sql);

			break;

		case 'delete_marked':
			$hidden_fields = array('cur_folder_id' => $cur_folder_id, 'mark_option' => 'delete_marked', 'submit_mark' => true);
			$hidden_fields['marked_msg_id'] = $msg_ids;
			$s_hidden_fields = '';

			foreach ($hidden_fields as $key => $var)
			{
				if (is_array($var))
				{
					foreach ($var as $_key => $_var)
					{
						$s_hidden_fields .= '<input type="hidden" name="' . $key . '[' . $_key . ']" value="' . $_var . '" />';
					}
				}
				else
				{
					$s_hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . $var . '" />';
				}
			}
			unset($hidden_fields);
			
			if (confirm_box(true))
			{
				delete_pm($user_id, $msg_ids, $cur_folder_id);
				// TODO: meta blabla
				trigger_error('MESSAGE_DELETED');
			}
			else
			{
				confirm_box(false, 'DELETE_MARKED_PM', $s_hidden_fields);
			}

			break;

		case 'export_as_xml':
		case 'export_as_csv':
		case 'export_as_txt':
			$export_as = str_replace('export_as_', '', $mark_action);
			break;

		default:
			return false;
	}

	return true;
}

// Delete PM(s)
function delete_pm($user_id, $msg_ids, $folder_id)
{
	global $db;

	$user_id	= (int) $user_id;
	$folder_id	= (int) $folder_id;

	if (!$user_id)
	{
		return false;
	}

	if (!is_array($msg_ids))
	{
		if (!$msg_ids)
		{
			return false;
		}
		$msg_ids = array($msg_ids);
	}

	if (!sizeof($msg_ids))
	{
		return false;
	}

	// Get PM Informations for later deleting
	$sql = 'SELECT msg_id, unread, new
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE msg_id IN (' . implode(', ', array_map('intval', $msg_ids)) . ")
			AND folder_id = $folder_id
			AND user_id = $user_id";
	$result = $db->sql_query($sql);

	$delete_rows = array();
	$num_unread = $num_new = $num_deleted = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		$num_unread += (int) $row['unread'];
		$num_new += (int) $row['new'];

		$delete_rows[$row['msg_id']] = 1;
	}
	$db->sql_freeresult($result);
	unset($msg_ids);

	if (!sizeof($delete_rows))
	{
		return false;
	}

	// if no one has read the message yet (meaning it is in users outbox)
	// then mark the message as deleted...
	if ($folder_id == PRIVMSGS_OUTBOX)
	{
		// Remove PM from Outbox
		$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . "
			WHERE user_id = $user_id AND folder_id = " . PRIVMSGS_OUTBOX . '
				AND msg_id IN (' . implode(', ', array_keys($delete_rows)) . ')';
		$db->sql_query($sql);

		// Update PM Information for safety
		$sql = 'UPDATE ' . PRIVMSGS_TABLE . " SET message_text = ''
			WHERE msg_id IN (" . implode(', ', array_keys($delete_rows)) . ')';
		$db->sql_query($sql);

		// Set delete flag for those intended to receive the PM
		// We do not remove the message actually, to retain some basic informations (sent time for example)
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
			SET deleted = 1
			WHERE msg_id IN (' . implode(', ', array_keys($delete_rows)) . ')';
		$db->sql_query($sql);

		$num_deleted = $db->sql_affectedrows();
	}
	else
	{
		// Delete Private Message Informations
		$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . "
			WHERE user_id = $user_id
				AND folder_id = $folder_id
				AND msg_id IN (" . implode(', ', array_keys($delete_rows)) . ')';
		$db->sql_query($sql);
		$num_deleted = $db->sql_affectedrows();
	}

	// if folder id is user defined folder then decrease pm_count
	if (!in_array($folder_id, array(PRIVMSGS_INBOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX, PRIVMSGS_NO_BOX)))
	{
		$sql = 'UPDATE ' . PRIVMSGS_FOLDER_TABLE . " 
			SET pm_count = pm_count - $num_deleted 
			WHERE folder_id = $folder_id";
		$db->sql_query($sql);
	}

	// Update unread and new status field
	if ($num_unread || $num_new)
	{
		$set_sql = ($num_unread) ? 'user_unread_privmsg = user_unread_privmsg - ' . $num_unread : '';
		if ($num_new)
		{
			$set_sql .= ($set_sql != '') ? ', ' : '';
			$set_sql .= 'user_new_privmsg = user_new_privmsg - ' . $num_new;
		}
		
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET $set_sql WHERE user_id = $user_id");
	}
	
	// Now we have to check which messages we can delete completely	
	$sql = 'SELECT msg_id 
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE msg_id IN (' . implode(', ', array_keys($delete_rows)) . ')';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		unset($delete_rows[$row['msg_id']]);
	}
	$db->sql_freeresult($result);

	$delete_ids = implode(', ', array_keys($delete_rows));

	if ($delete_ids)
	{
		$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
			WHERE msg_id IN (' . $delete_ids . ')';
		$db->sql_query($sql);
	}
}

// Rebuild message header
function rebuild_header($check_ary)
{
	global $db;

	$address = array();

	foreach ($check_ary as $check_type => $address_field)
	{
		// Split Addresses into users and groups
		preg_match_all('/:?(u|g)_([0-9]+):?/', $address_field, $match);

		$u = $g = array();
		foreach ($match[1] as $id => $type)
		{
			${$type}[] = (int) $match[2][$id];
		}

		foreach (array('u', 'g') as $type)
		{
			if (sizeof($$type))
			{
				foreach ($$type as $id)
				{
					$address[$type][$id] = $check_type;
				}
			}
		}
	}

	return $address;
}

// Print out/Assign recipient informations
function write_pm_addresses($check_ary, $author_id, $plaintext = false)
{
	global $db, $user, $template, $phpbb_root_path, $SID, $phpEx;

	$addresses = array();
	
	foreach ($check_ary as $check_type => $address_field)
	{
		// Split Addresses into users and groups
		preg_match_all('/:?(u|g)_([0-9]+):?/', $address_field, $match);

		$u = $g = array();
		foreach ($match[1] as $id => $type)
		{
			${$type}[] = (int) $match[2][$id];
		}

		$address = array();
		if (sizeof($u))
		{
			$sql = 'SELECT user_id, username, user_colour 
				FROM ' . USERS_TABLE . '
				WHERE user_id IN (' . implode(', ', $u) . ')';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if ($check_type == 'to' || $author_id == $user->data['user_id'] || $row['user_id'] == $user->data['user_id'])
				{
					if ($plaintext)
					{
						$address[] = $row['username'];
					}
					else
					{
						$address['user'][$row['user_id']] = array('name' => $row['username'], 'colour' => $row['user_colour']);
					}
				}
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($g))
		{
			if ($plaintext)
			{
				$sql = 'SELECT group_name
					FROM ' . GROUPS_TABLE . ' 
						WHERE group_id IN (' . implode(', ', $g) . ')';
				$result = $db->sql_query($sql);
		
				while ($row = $db->sql_fetchrow($result))
				{
					if ($check_type == 'to' || $author_id == $user->data['user_id'] || $row['user_id'] == $user->data['user_id'])
					{
						$address[] = $row['group_name'];
					}
				}
				$db->sql_freeresult($result);
			}
			else
			{
				$sql = 'SELECT g.group_id, g.group_name, g.group_colour, ug.user_id
					FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
						WHERE g.group_id IN (' . implode(', ', $g) . ')
						AND g.group_id = ug.group_id
						AND ug.user_pending = 0';
				$result = $db->sql_query($sql);
		
				while ($row = $db->sql_fetchrow($result))
				{
					if (!isset($address['group'][$row['group_id']]))
					{
						if ($check_type == 'to' || $author_id == $user->data['user_id'] || $row['user_id'] == $user->data['user_id'])
						{
							$address['group'][$row['group_id']] = array('name' => $row['group_name'], 'colour' => $row['group_colour']);
						}
					}
	
					if (isset($address['user'][$row['user_id']]))
					{
						$address['user'][$row['user_id']]['in_group'] = $row['group_id'];
					}
				}
				$db->sql_freeresult($result);
			}
		}

		if (sizeof($address) && !$plaintext)
		{
			$template->assign_var('S_' . strtoupper($check_type) . '_RECIPIENT', true);

			foreach ($address as $type => $adr_ary)
			{
				foreach ($adr_ary as $id => $row)
				{
					$template->assign_block_vars($check_type . '_recipient', array(
						'NAME'		=> $row['name'],
						'IS_GROUP'	=> ($type == 'group'),
						'IS_USER'	=> ($type == 'user'),
						'COLOUR'	=> ($row['colour']) ? $row['colour'] : '',
						'UG_ID'		=> $id,
						'U_VIEW'	=> ($type == 'user') ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $id : "{$phpbb_root_path}groupcp.$phpEx$SID&amp;g=" . $id)
					);
				}
			}
		}

		$addresses[$check_type] = $address;
	}

	return $addresses;
}

// Get folder status
function get_folder_status($folder_id, $folder)
{
	global $db, $user, $config;

	if (isset($folder[$folder_id]))
	{
		$folder = $folder[$folder_id];
	}
	else
	{
		return false;
	}
	$return = array();

	$message_limit = (!$user->data['group_message_limit']) ? $config['pm_max_msgs'] : $user->data['group_message_limit'];

	$return = array(
		'folder_name'	=> $folder['folder_name'], 
		'cur'			=> $folder['num_messages'],
		'remaining'		=> $message_limit - $folder['num_messages'],
		'max'			=> $message_limit,
		'percent'		=> ($message_limit > 0) ? round(($folder['num_messages'] / $message_limit) * 100) : 100
	);

	$return['message'] = sprintf($user->lang['FOLDER_STATUS_MSG'], $return['percent'], $return['cur'], $return['max']);

	return $return;
}

//
// COMPOSE MESSAGES
//

// Submit PM
function submit_pm($mode, $subject, &$data, $update_message, $put_in_outbox = true)
{
	global $db, $auth, $user, $config, $phpEx, $SID, $template;

	// We do not handle erasing posts here
	if ($mode == 'delete')
	{
		return;
	}

	$current_time = time();

	// Collect some basic informations about which tables and which rows to update/insert
	$sql_data = array();
	$root_level = 0;

	// Recipient Informations
	$recipients = $to = $bcc = array();

	if ($mode != 'edit')
	{
		// Build Recipient List
		// u|g => array($user_id => 'to'|'bcc')
		foreach (array('u', 'g') as $ug_type)
		{
			if (isset($data['address_list'][$ug_type]) && sizeof($data['address_list'][$ug_type]))
			{
				foreach ($data['address_list'][$ug_type] as $id => $field)
				{
					$field = ($field == 'to') ? 'to' : 'bcc';
					if ($ug_type == 'u')
					{
						$recipients[$id] = $field;
					}
					${$field}[] = $ug_type . '_' . (int) $id;
				}
			}
		}

		if (isset($data['address_list']['g']) && sizeof($data['address_list']['g']))
		{
			$sql = 'SELECT group_id, user_id
				FROM ' . USER_GROUP_TABLE . '
				WHERE group_id IN (' . implode(', ', array_keys($data['address_list']['g'])) . ')
					AND user_pending = 0';
			$result = $db->sql_query($sql);
	
			while ($row = $db->sql_fetchrow($result))
			{
				$field = ($data['address_list']['g'][$row['group_id']] == 'to') ? 'to' : 'bcc';
				$recipients[$row['user_id']] = $field;
			}
			$db->sql_freeresult($result);
		}

		if (!sizeof($recipients))
		{
			trigger_error('NO_RECIPIENT');
		}
	}

	$sql = '';
	
	switch ($mode)
	{
		case 'reply':
		case 'quote':
			$root_level = ($data['reply_from_root_level']) ? $data['reply_from_root_level'] : $data['reply_from_msg_id']; 

			// Set message_replied switch for this user
			$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
				SET replied = 1
				WHERE user_id = ' . $user->data['user_id'] . '
					AND msg_id = ' . $data['reply_from_msg_id'];

		case 'forward':
		case 'post':
			$sql_data = array(
				'root_level'		=> $root_level,
				'author_id'			=> (int) $user->data['user_id'],
				'icon_id'			=> $data['icon_id'], 
				'author_ip' 		=> $user->ip,
				'message_time'		=> $current_time,
				'enable_bbcode' 	=> $data['enable_bbcode'],
				'enable_html' 		=> $data['enable_html'],
				'enable_smilies' 	=> $data['enable_smilies'],
				'enable_magic_url' 	=> $data['enable_urls'],
				'enable_sig' 		=> $data['enable_sig'],
				'message_subject'	=> $subject,
				'message_text' 		=> $data['message'],
				'message_checksum'	=> $data['message_md5'],
				'message_encoding'	=> $user->lang['ENCODING'],
				'message_attachment'=> (isset($data['filename_data']['physical_filename']) && sizeof($data['filename_data']['physical_filename'])) ? 1 : 0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $data['bbcode_uid'],
				'to_address'		=> implode(':', $to),
				'bcc_address'		=> implode(':', $bcc)
			);
			break;

		case 'edit':
			$sql_data = array(
				'icon_id'			=> $data['icon_id'],
				'message_edit_time'	=> $current_time,
				'enable_bbcode' 	=> $data['enable_bbcode'],
				'enable_html' 		=> $data['enable_html'],
				'enable_smilies' 	=> $data['enable_smilies'],
				'enable_magic_url' 	=> $data['enable_urls'],
				'enable_sig' 		=> $data['enable_sig'],
				'message_subject'	=> $subject,
				'message_text' 		=> $data['message'],
				'message_checksum'	=> $data['message_md5'],
				'message_encoding'	=> $user->lang['ENCODING'],
				'message_attachment'=> (sizeof($data['filename_data']['physical_filename'])) ? 1 : 0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $data['bbcode_uid']
			);
			break;
	}

	if (sizeof($sql_data))
	{
		if ($mode == 'post' || $mode == 'reply' || $mode == 'quote' || $mode == 'forward')
		{
			$db->sql_query('INSERT INTO ' . PRIVMSGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data));
			$data['msg_id'] = $db->sql_nextid();
		}
		else if ($mode == 'edit')
		{
			$sql = 'UPDATE ' . PRIVMSGS_TABLE . ' 
				SET message_edit_count = message_edit_count + 1, ' . $db->sql_build_array('UPDATE', $sql_data) . ' 
				WHERE msg_id = ' . $data['msg_id'];
			$db->sql_query($sql);
		}
	}
	
	if ($mode != 'edit')
	{
		$db->sql_transaction();
	
		if ($sql)
		{
			$db->sql_query($sql);
		}
		unset($sql);

		foreach ($recipients as $user_id => $type)
		{
			$db->sql_query('INSERT INTO ' . PRIVMSGS_TO_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'msg_id'	=> $data['msg_id'],
				'user_id'	=> $user_id,
				'author_id'	=> $user->data['user_id'],
				'folder_id'	=> PRIVMSGS_NO_BOX,
				'new'		=> 1,
				'unread'	=> 1,
				'forwarded'	=> ($mode == 'forward') ? 1 : 0))
			);
		}

		$sql = 'UPDATE ' . USERS_TABLE . ' 
			SET user_new_privmsg = user_new_privmsg + 1, user_unread_privmsg = user_unread_privmsg + 1
			WHERE user_id IN (' . implode(', ', array_keys($recipients)) . ')';
		$db->sql_query($sql);

		// Put PM into outbox
		if ($put_in_outbox)
		{
			$db->sql_query('INSERT INTO ' . PRIVMSGS_TO_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'msg_id'	=> (int) $data['msg_id'],
				'user_id'	=> (int) $user->data['user_id'],
				'author_id'	=> (int) $user->data['user_id'],
				'folder_id'	=> PRIVMSGS_OUTBOX,
				'new'		=> 0,
				'unread'	=> 0,
				'forwarded'	=> ($mode == 'forward') ? 1 : 0))
			);
		}

		$db->sql_transaction('commit');
	}

	// Set user last post time
	if ($mode == 'reply' || $mode == 'quote' || $mode == 'forward' || $mode == 'post')
	{
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_lastpost_time = $current_time
			WHERE user_id = " . $user->data['user_id'];
		$db->sql_query($sql);
	}

	$db->sql_transaction();

	// Submit Attachments
	if (sizeof($data['attachment_data']) && $data['msg_id'] && in_array($mode, array('post', 'reply', 'quote', 'edit', 'forward')))
	{
		$space_taken = $files_added = 0;

		foreach ($data['attachment_data'] as $pos => $attach_row)
		{
			if ($attach_row['attach_id'])
			{
				// update entry in db if attachment already stored in db and filespace
				$sql = 'UPDATE ' . ATTACHMENTS_TABLE . " 
					SET comment = '" . $db->sql_escape($attach_row['comment']) . "' 
					WHERE attach_id = " . (int) $attach_row['attach_id'];
				$db->sql_query($sql);
			}
			else
			{
				// insert attachment into db 
				$attach_sql = array(
					'post_msg_id'		=> $data['msg_id'],
					'topic_id'			=> 0,
					'in_message'		=> 1,
					'poster_id'			=> $user->data['user_id'],
					'physical_filename'	=> basename($attach_row['physical_filename']),
					'real_filename'		=> basename($attach_row['real_filename']),
					'comment'			=> $attach_row['comment'],
					'extension'			=> $attach_row['extension'],
					'mimetype'			=> $attach_row['mimetype'],
					'filesize'			=> $attach_row['filesize'],
					'filetime'			=> $attach_row['filetime'],
					'thumbnail'			=> $attach_row['thumbnail']
				);

				$sql = 'INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . 
					$db->sql_build_array('INSERT', $attach_sql);
				$db->sql_query($sql);

				$space_taken += $attach_row['filesize'];
				$files_added++;
			}
		}
		
		if (sizeof($data['attachment_data']))
		{
			$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
				SET message_attachment = 1
				WHERE msg_id = ' . $data['msg_id'];
			$db->sql_query($sql);
		}

		if ($space_taken && $files_added)
		{
			set_config('upload_dir_size', $config['upload_dir_size'] + $space_taken, true);
			set_config('num_files', $config['num_files'] + $files_added, true);
		}
	}

	$db->sql_transaction('commit');

	// Delete draft if post was loaded...
	$draft_id = request_var('draft_loaded', 0);
	if ($draft_id)
	{
		$sql = 'DELETE FROM ' . DRAFTS_TABLE . " 
			WHERE draft_id = $draft_id 
				AND user_id = " . $user->data['user_id'];
		$db->sql_query($sql);
	}

	// Send Notifications
	if ($mode != 'edit')
	{
		pm_notification($mode, stripslashes($user->data['username']), $recipients, stripslashes($subject), stripslashes($data['message']));
	}

	return $data['msg_id'];
}

// PM Notification
function pm_notification($mode, $author, $recipients, $subject, $message)
{
	global $db, $user, $config, $phpbb_root_path, $phpEx, $auth;

	$subject = censor_text($subject);
	
	// Get banned User ID's
	$sql = 'SELECT ban_userid 
		FROM ' . BANLIST_TABLE;
	$result = $db->sql_query($sql);

	unset($recipients[ANONYMOUS], $recipients[$user->data['user_id']]);
	
	while ($row = $db->sql_fetchrow($result))
	{
		if (isset($row['ban_userid']))
		{
			unset($recipients[$row['ban_userid']]);
		}
	}
	$db->sql_freeresult($result);

	if (!sizeof($recipients))
	{
		return;
	}

	$recipient_list = implode(', ', array_keys($recipients));

	$sql = 'SELECT user_id, username, user_email, user_lang, user_notify_type, user_jabber 
		FROM ' . USERS_TABLE . "
		WHERE user_id IN ($recipient_list)";
	$result = $db->sql_query($sql);

	$msg_list_ary = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if (trim($row['user_email']))
		{
			$msg_list_ary[] = array(
				'method'	=> $row['user_notify_type'],
				'email'		=> $row['user_email'],
				'jabber'	=> $row['user_jabber'],
				'name'		=> $row['username'],
				'lang'		=> $row['user_lang']
			);
		}
	}
	$db->sql_freeresult($result);
	
	if (!sizeof($msg_list_ary))
	{
		return;
	}

	include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);
	$messenger = new messenger();

	$email_sig = str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']);

	foreach ($msg_list_ary as $pos => $addr)
	{
		$messenger->template('privmsg_notify', $addr['lang']);

		$messenger->replyto($config['board_email']);
		$messenger->to($addr['email'], $addr['name']);
		$messenger->im($addr['jabber'], $addr['name']);

		$messenger->assign_vars(array(
			'EMAIL_SIG'		=> $email_sig,
			'SITENAME'		=> $config['sitename'],
			'SUBJECT'		=> $subject,
			'AUTHOR_NAME'	=> $author,
			'USERNAME'		=> $addr['name'],

			'U_INBOX'		=> generate_board_url() . "/ucp.$phpEx?i=pm&mode=unread")
		);

		$messenger->send($addr['method']);
		$messenger->reset();
	}
	unset($msg_list_ary);

	if ($messenger->queue)
	{
		$messenger->queue->save();
	}

	unset($messenger);
}

?>