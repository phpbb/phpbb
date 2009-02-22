<?php
/**
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* ucp_zebra
* @package ucp
*/
class ucp_zebra
{
	var $u_action;

	function main($id, $mode)
	{
		$submit = phpbb_request::is_set_post('submit') || phpbb_request::is_set('add', phpbb_request::GET) || phpbb_request::is_set('remove', phpbb_request::GET);
		$s_hidden_fields = '';

		$l_mode = strtoupper($mode);

		if ($submit)
		{
			$data = $error = array();
			$updated = false;

			$var_ary = array(
				'usernames'	=> array(0),
				'add'		=> '',
			);

			foreach ($var_ary as $var => $default)
			{
				$data[$var] = request_var($var, $default, true);
			}

			if (!empty($data['add']) || sizeof($data['usernames']))
			{
				if (confirm_box(true))
				{
					if ($data['add'])
					{
						$data['add'] = array_map('trim', array_map('utf8_clean_string', explode("\n", $data['add'])));

						// Do these name/s exist on a list already? If so, ignore ... we could be
						// 'nice' and automatically handle names added to one list present on
						// the other (by removing the existing one) ... but I have a feeling this
						// may lead to complaints
						$sql = 'SELECT z.*, u.username, u.username_clean
							FROM ' . ZEBRA_TABLE . ' z, ' . USERS_TABLE . ' u
							WHERE z.user_id = ' . phpbb::$user->data['user_id'] . '
								AND u.user_id = z.zebra_id';
						$result = phpbb::$db->sql_query($sql);

						$friends = $foes = array();
						while ($row = phpbb::$db->sql_fetchrow($result))
						{
							if ($row['friend'])
							{
								$friends[] = utf8_clean_string($row['username']);
							}
							else
							{
								$foes[] = utf8_clean_string($row['username']);
							}
						}
						phpbb::$db->sql_freeresult($result);

						// remove friends from the username array
						$n = sizeof($data['add']);
						$data['add'] = array_diff($data['add'], $friends);

						if (sizeof($data['add']) < $n && $mode == 'foes')
						{
							$error[] = phpbb::$user->lang['NOT_ADDED_FOES_FRIENDS'];
						}

						// remove foes from the username array
						$n = sizeof($data['add']);
						$data['add'] = array_diff($data['add'], $foes);

						if (sizeof($data['add']) < $n && $mode == 'friends')
						{
							$error[] = phpbb::$user->lang['NOT_ADDED_FRIENDS_FOES'];
						}

						// remove the user himself from the username array
						$n = sizeof($data['add']);
						$data['add'] = array_diff($data['add'], array(utf8_clean_string(phpbb::$user->data['username'])));

						if (sizeof($data['add']) < $n)
						{
							$error[] = phpbb::$user->lang['NOT_ADDED_' . $l_mode . '_SELF'];
						}

						unset($friends, $foes, $n);

						if (sizeof($data['add']))
						{
							$sql = 'SELECT user_id, user_type
								FROM ' . USERS_TABLE . '
								WHERE ' . phpbb::$db->sql_in_set('username_clean', $data['add']) . '
									AND user_type <> ' . phpbb::USER_INACTIVE;
							$result = phpbb::$db->sql_query($sql);

							$user_id_ary = array();
							while ($row = phpbb::$db->sql_fetchrow($result))
							{
								if ($row['user_id'] != ANONYMOUS && $row['user_type'] != phpbb::USER_IGNORE)
								{
									$user_id_ary[] = $row['user_id'];
								}
								else
								{
									$error[] = phpbb::$user->lang['NOT_ADDED_' . $l_mode . '_ANONYMOUS'];
								}
							}
							phpbb::$db->sql_freeresult($result);

							if (sizeof($user_id_ary))
							{
								// Remove users from foe list if they are admins or moderators
								if ($mode == 'foes')
								{
									$perms = array();
									foreach (phpbb::$acl->acl_get_list($user_id_ary, array('a_', 'm_')) as $forum_id => $forum_ary)
									{
										foreach ($forum_ary as $auth_option => $user_ary)
										{
											$perms = array_merge($perms, $user_ary);
										}
									}

									$perms = array_unique($perms);

									if (sizeof($perms))
									{
										$error[] = phpbb::$user->lang['NOT_ADDED_FOES_MOD_ADMIN'];
									}

									// This may not be right ... it may yield true when perms equate to deny
									$user_id_ary = array_diff($user_id_ary, $perms);
									unset($perms);
								}

								if (sizeof($user_id_ary))
								{
									$sql_mode = ($mode == 'friends') ? 'friend' : 'foe';

									$sql_ary = array();
									foreach ($user_id_ary as $zebra_id)
									{
										$sql_ary[] = array(
											'user_id'		=> (int) phpbb::$user->data['user_id'],
											'zebra_id'		=> (int) $zebra_id,
											$sql_mode		=> 1
										);
									}

									phpbb::$db->sql_multi_insert(ZEBRA_TABLE, $sql_ary);

									$updated = true;
								}
								unset($user_id_ary);
							}
							else if (!sizeof($error))
							{
								$error[] = phpbb::$user->lang['USER_NOT_FOUND_OR_INACTIVE'];
							}
						}
					}
					else if (sizeof($data['usernames']))
					{
						// Force integer values
						$data['usernames'] = array_map('intval', $data['usernames']);

						$sql = 'DELETE FROM ' . ZEBRA_TABLE . '
							WHERE user_id = ' . phpbb::$user->data['user_id'] . '
								AND ' . phpbb::$db->sql_in_set('zebra_id', $data['usernames']);
						phpbb::$db->sql_query($sql);

						$updated = true;
					}

					if ($updated)
					{
						meta_refresh(3, $this->u_action);
						$message = phpbb::$user->lang[$l_mode . '_UPDATED'] . '<br />' . implode('<br />', $error) . ((sizeof($error)) ? '<br />' : '') . '<br />' . sprintf(phpbb::$user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}
					else
					{
						phpbb::$template->assign_var('ERROR', implode('<br />', $error));
					}
				}
				else
				{
					confirm_box(false, phpbb::$user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
						'mode'		=> $mode,
						'submit'	=> true,
						'usernames'	=> $data['usernames'],
						'add'		=> $data['add']))
					);
				}
			}
		}

		$sql_and = ($mode == 'friends') ? 'z.friend = 1' : 'z.foe = 1';
		$sql = 'SELECT z.*, u.username, u.username_clean
			FROM ' . ZEBRA_TABLE . ' z, ' . USERS_TABLE . ' u
			WHERE z.user_id = ' . phpbb::$user->data['user_id'] . "
				AND $sql_and
				AND u.user_id = z.zebra_id
			ORDER BY u.username_clean ASC";
		$result = phpbb::$db->sql_query($sql);

		$s_username_options = '';
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$s_username_options .= '<option value="' . $row['zebra_id'] . '">' . $row['username'] . '</option>';
		}
		phpbb::$db->sql_freeresult($result);

		phpbb::$template->assign_vars(array(
			'L_TITLE'			=> phpbb::$user->lang['UCP_ZEBRA_' . $l_mode],

			'U_FIND_USERNAME'	=> append_sid('memberlist', 'mode=searchuser&amp;form=ucp&amp;field=add'),

			'S_USERNAME_OPTIONS'	=> $s_username_options,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> $this->u_action,
		));

		$this->tpl_name = 'ucp_zebra_' . $mode;
		$this->page_title = 'UCP_ZEBRA_' . $l_mode;
	}
}

?>