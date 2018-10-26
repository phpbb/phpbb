<?php
/**
*
* @package profile
* @version $Id$
* @copyright (c) 2008 Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
	exit;
}

/*
if ($board_config['allow_zebra'] == false)
{
	message_die(GENERAL_MESSAGE, $lang['Not_Auth_View']);
}
*/

$zmode = 'friends';
$zmode_types = array('friends', 'foes');
$zmode = request_var('zmode', 'friends');
$zmode = check_var_value($zmode, $zmode_types);

// Forced to friends...
$zmode = 'friends';

if (isset($_POST['submit']))
{
	$data = array();
	$error = array();
	$updated = false;

	$var_ary = array(
		'usernames' => array(0),
		'add' => '',
	);

	foreach ($var_ary as $var => $default)
	{
		$data[$var] = request_var($var, $default, true);
	}

	if (!empty($data['add']) || sizeof($data['usernames']))
	{
		if ($data['add'])
		{
			$data['add'] = array_map('trim', explode("\n", $data['add']));

			// Do these name/s exist on a list already? If so, ignore ... we could be
			// 'nice' and automatically handle names added to one list present on
			// the other (by removing the existing one) ... but I have a feeling this
			// may lead to complaints
			$sql = 'SELECT z.*, u.username
				FROM ' . ZEBRA_TABLE . ' z, ' . USERS_TABLE . ' u
				WHERE z.user_id = ' . $user->data['user_id'] . '
					AND u.user_id = z.zebra_id';
			$result = $db->sql_query($sql);

			$friends = array();
			$foes = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['friend'])
				{
					$friends[] = $row['user_id'];
				}
				else
				{
					$foes[] = $row['user_id'];
				}
			}
			$db->sql_freeresult($result);

			// remove friends from the username array
			$n = sizeof($data['add']);
			$data['add'] = array_diff($data['add'], $friends);

			// remove foes from the username array
			$n = sizeof($data['add']);
			$data['add'] = array_diff($data['add'], $foes);

			// remove the user himself from the username array
			$n = sizeof($data['add']);
			$data['add'] = array_diff($data['add'], array($user->data['username']));

			unset($friends, $foes, $n);

			if (sizeof($data['add']))
			{
				$users_to_add = '';
				foreach ($data['add'] as $user_tmp)
				{
					$username_tmp = phpbb_clean_username($user_tmp);
					//$users_to_add .= (($users_to_add == '') ? '' : ', ') . "'" . $db->sql_escape($username_tmp) . "'";
					$users_to_add .= (($users_to_add == '') ? '' : ', ') . "'" . $db->sql_escape(utf8_clean_string($username_tmp)) . "'";
				}
				//$users_to_add = implode('\',\'', $data['add']);
				$sql = "SELECT user_id, user_level
					FROM " . USERS_TABLE . "
					WHERE username_clean IN (" . $users_to_add . ")
						AND user_active = 1";
				//die($sql);
				$result = $db->sql_query($sql);

				$user_id_ary = array();
				$user_id_level = array();
				while ($row = $db->sql_fetchrow($result))
				{
					if ($row['user_id'] != ANONYMOUS)
					{
						$user_id_ary[$row['user_id']] = $row['user_id'];
						$user_id_level[$row['user_id']] = $row['user_level'];
					}
				}
				$db->sql_freeresult($result);

				if (sizeof($user_id_ary))
				{
					// Remove users from foe list if they are admins or moderators
					if ($zmode == 'foes')
					{
						$perms = array();
						foreach ($user_id_ary as $user_tmp)
						{
							if ($user_id_level[$row['user_id']] > 0)
							{
								$perms[] = array_merge($perms, $user_tmp);
							}
						}
						$perms = array_unique($perms);

						// This may not be right ... it may yield true when perms equate to deny
						$user_id_ary = array_diff($user_id_ary, $perms);
						unset($perms);
					}

					if (sizeof($user_id_ary))
					{
						$friend_foe_mode = ($zmode == 'friends') ? true : false;
						user_friend_foe_add($user_id_ary, $friend_foe_mode);
						$updated = true;
					}
					unset($user_id_ary);
				}
			}
		}
		elseif (sizeof($data['usernames']))
		{
			// Force integer values
			$data['usernames'] = array_map('intval', $data['usernames']);
			$friend_foe_mode = ($zmode == 'friends') ? true : false;
			user_friend_foe_remove($data['usernames'], $friend_foe_mode);
			$updated = true;
		}

		$db->clear_cache('zebra_users_');
		if ($updated)
		{
			$redirect_url = append_sid(append_sid('profile.$phpEx' . '?mode=zebra&amp;zmode=' . $zmode));
			meta_refresh(3, $redirect_url);
			message_die(GENERAL_MESSAGE, (($zmode == 'friends') ? $lang['FRIENDS_UPDATED'] : $lang['FOES_UPDATED']));
		}
		else
		{
			message_die(GENERAL_ERROR, (($zmode == 'friends') ? $lang['FRIENDS_UPDATE_ERROR'] : $lang['FOES_UPDATE_ERROR']));
		}
	}
}

$sql_and = ($zmode == 'foes') ? 'z.foe = 1' : 'z.friend = 1';
$sql = "SELECT z.*, u.username
	FROM " . ZEBRA_TABLE . " z, " . USERS_TABLE . " u
	WHERE z.user_id = '" . $user->data['user_id'] . "'
		AND " . $sql_and . "
		AND u.user_id = z.zebra_id
	ORDER BY u.username ASC";
$result = $db->sql_query($sql);

$username_count = 0;
$s_username_options = '';
while ($row = $db->sql_fetchrow($result))
{
	$s_username_options .= '<option value="' . $row['zebra_id'] . '">' . htmlspecialchars($row['username']) . '</option>';
	$username_count++;
}
$db->sql_freeresult($result);

$link_name = $lang['UCP_ZEBRA_FRIENDS'];
$nav_server_url = create_server_url();
$breadcrumbs['address'] = $lang['Nav_Separator'] . '<a href="' . $nav_server_url . append_sid('profile.$phpEx'_MAIN) . '"' . (!empty($link_name) ? '' : ' class="nav-current"') . '>' . $lang['Profile'] . '</a>' . (!empty($link_name) ? ($lang['Nav_Separator'] . '<a class="nav-current" href="#">' . $link_name . '</a>') : '');

if ($username_count > 0)
{
	$template->assign_block_vars('friends', array());
}
else
{
	$template->assign_block_vars('no_friends', array());
}

$template->assign_vars(array(
	'L_TITLE' => $lang['UCP_ZEBRA'],
	'L_SUBMIT' => $lang['Submit'],
	'L_RESET' => $lang['Reset'],

	'L_SELECT' => $lang['Select'],
	'L_REMOVE_SELECTED' => $lang['Remove_selected'],
	'L_ADD_MEMBER' => $lang['Add_member'],

	'L_ADD_FOES' => $lang['ADD_FOES'],
	'L_ADD_FOES_EXPLAIN' => $lang['ADD_FOES_EXPLAIN'],
	'L_FOES' => $lang['FOES'],
	'L_FOES_EXPLAIN' => $lang['FOES_EXPLAIN'],
	'L_YOUR_FOES' => $lang['YOUR_FOES'],
	'L_YOUR_FOES_EXPLAIN' => $lang['YOUR_FOES_EXPLAIN'],
	'L_NO_FOES' => $lang['NO_FOES'],
	'L_ADD_FRIENDS' => $lang['ADD_FRIENDS'],
	'L_ADD_FRIENDS_EXPLAIN' => $lang['ADD_FRIENDS_EXPLAIN'],
	'L_FRIENDS' => $lang['FRIENDS'],
	'L_FRIENDS_EXPLAIN' => $lang['FRIENDS_EXPLAIN'],
	'L_YOUR_FRIENDS' => $lang['YOUR_FRIENDS'],
	'L_YOUR_FRIENDS_EXPLAIN' => $lang['YOUR_FRIENDS_EXPLAIN'],
	'L_NO_FRIENDS' => $lang['NO_FRIENDS'],

	'U_SEARCH_USER' => append_sid(CMS_PAGE_SEARCH . '?mode=searchuser'),
	'S_USERNAME_OPTIONS' => $s_username_options,
	'S_PROFILE_ACTION' => append_sid('profile.$phpEx' . '?mode=zebra&amp;zmode=' . $zmode),
	'S_HIDDEN_FIELDS' => ''
	)
);

full_page_generation('profile_friends_mng_body.tpl', $lang['UCP_ZEBRA_FRIENDS'], '', '');

?>