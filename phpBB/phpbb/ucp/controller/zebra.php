<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class ucp_zebra
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $phpbb_root_path, $phpEx, $request, $phpbb_dispatcher;

		$submit	= (isset($_POST['submit']) || isset($_GET['add']) || isset($_GET['remove'])) ? true : false;
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
				$data[$var] = $request->variable($var, $default, true);
			}

			if (!empty($data['add']) || count($data['usernames']))
			{
				if (confirm_box(true))
				{
					// Remove users
					if (!empty($data['usernames']))
					{
						$user_ids = $data['usernames'];

						/**
						* Remove users from friends/foes
						*
						* @event core.ucp_remove_zebra
						* @var	string	mode		Zebra type: friends|foes
						* @var	array	user_ids	User ids we remove
						* @since 3.1.0-a1
						*/
						$vars = array('mode', 'user_ids');
						extract($phpbb_dispatcher->trigger_event('core.ucp_remove_zebra', compact($vars)));

						$sql = 'DELETE FROM ' . ZEBRA_TABLE . '
							WHERE user_id = ' . $user->data['user_id'] . '
								AND ' . $db->sql_in_set('zebra_id', $user_ids);
						$db->sql_query($sql);

						$updated = true;
					}

					// Add users
					if ($data['add'])
					{
						$data['add'] = array_map('trim', array_map('utf8_clean_string', explode("\n", $data['add'])));

						// Do these name/s exist on a list already? If so, ignore ... we could be
						// 'nice' and automatically handle names added to one list present on
						// the other (by removing the existing one) ... but I have a feeling this
						// may lead to complaints
						$sql = 'SELECT z.*, u.username, u.username_clean
							FROM ' . ZEBRA_TABLE . ' z, ' . USERS_TABLE . ' u
							WHERE z.user_id = ' . $user->data['user_id'] . '
								AND u.user_id = z.zebra_id';
						$result = $db->sql_query($sql);

						$friends = $foes = array();
						while ($row = $db->sql_fetchrow($result))
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
						$db->sql_freeresult($result);

						// remove friends from the username array
						$n = count($data['add']);
						$data['add'] = array_diff($data['add'], $friends);

						if (count($data['add']) < $n && $mode == 'foes')
						{
							$error[] = $user->lang['NOT_ADDED_FOES_FRIENDS'];
						}

						// remove foes from the username array
						$n = count($data['add']);
						$data['add'] = array_diff($data['add'], $foes);

						if (count($data['add']) < $n && $mode == 'friends')
						{
							$error[] = $user->lang['NOT_ADDED_FRIENDS_FOES'];
						}

						// remove the user himself from the username array
						$n = count($data['add']);
						$data['add'] = array_diff($data['add'], array(utf8_clean_string($user->data['username'])));

						if (count($data['add']) < $n)
						{
							$error[] = $user->lang['NOT_ADDED_' . $l_mode . '_SELF'];
						}

						unset($friends, $foes, $n);

						if (count($data['add']))
						{
							$sql = 'SELECT user_id, user_type
								FROM ' . USERS_TABLE . '
								WHERE ' . $db->sql_in_set('username_clean', $data['add']) . '
									AND user_type <> ' . USER_INACTIVE;
							$result = $db->sql_query($sql);

							$user_id_ary = array();
							while ($row = $db->sql_fetchrow($result))
							{
								if ($row['user_id'] != ANONYMOUS && $row['user_type'] != USER_IGNORE)
								{
									$user_id_ary[] = $row['user_id'];
								}
								else if ($row['user_id'] != ANONYMOUS)
								{
									$error[] = $user->lang['NOT_ADDED_' . $l_mode . '_BOTS'];
								}
								else
								{
									$error[] = $user->lang['NOT_ADDED_' . $l_mode . '_ANONYMOUS'];
								}
							}
							$db->sql_freeresult($result);

							if (count($user_id_ary))
							{
								// Remove users from foe list if they are admins or moderators
								if ($mode == 'foes')
								{
									$perms = array();
									foreach ($auth->acl_get_list($user_id_ary, array('a_', 'm_')) as $forum_id => $forum_ary)
									{
										foreach ($forum_ary as $auth_option => $user_ary)
										{
											$perms = array_merge($perms, $user_ary);
										}
									}

									$perms = array_unique($perms);

									if (count($perms))
									{
										$error[] = $user->lang['NOT_ADDED_FOES_MOD_ADMIN'];
									}

									// This may not be right ... it may yield true when perms equate to deny
									$user_id_ary = array_diff($user_id_ary, $perms);
									unset($perms);
								}

								if (count($user_id_ary))
								{
									$sql_mode = ($mode == 'friends') ? 'friend' : 'foe';

									$sql_ary = array();
									foreach ($user_id_ary as $zebra_id)
									{
										$sql_ary[] = array(
											'user_id'		=> (int) $user->data['user_id'],
											'zebra_id'		=> (int) $zebra_id,
											$sql_mode		=> 1
										);
									}

									/**
									* Add users to friends/foes
									*
									* @event core.ucp_add_zebra
									* @var	string	mode		Zebra type:
									*							friends|foes
									* @var	array	sql_ary		Array of
									*							entries we add
									* @since 3.1.0-a1
									*/
									$vars = array('mode', 'sql_ary');
									extract($phpbb_dispatcher->trigger_event('core.ucp_add_zebra', compact($vars)));

									$db->sql_multi_insert(ZEBRA_TABLE, $sql_ary);

									$updated = true;
								}
								unset($user_id_ary);
							}
							else if (!count($error))
							{
								$error[] = $user->lang['USER_NOT_FOUND_OR_INACTIVE'];
							}
						}
					}

					if ($request->is_ajax())
					{
						$message = ($updated) ? $user->lang[$l_mode . '_UPDATED'] : implode('<br />', $error);

						$json_response = new \phpbb\json_response;
						$json_response->send(array(
							'success' => $updated,

							'MESSAGE_TITLE'	=> $user->lang['INFORMATION'],
							'MESSAGE_TEXT'	=> $message,
							'REFRESH_DATA'	=> array(
								'time'	=> 3,
								'url'		=> $this->u_action
							)
						));
					}
					else if ($updated)
					{
						meta_refresh(3, $this->u_action);
						$message = $user->lang[$l_mode . '_UPDATED'] . '<br />' . implode('<br />', $error) . ((count($error)) ? '<br />' : '') . '<br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}
					else
					{
						$template->assign_var('ERROR', implode('<br />', $error));
					}
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
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
			WHERE z.user_id = ' . $user->data['user_id'] . "
				AND $sql_and
				AND u.user_id = z.zebra_id
			ORDER BY u.username_clean ASC";
		$result = $db->sql_query($sql);

		$s_username_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$s_username_options .= '<option value="' . $row['zebra_id'] . '">' . $row['username'] . '</option>';
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['UCP_ZEBRA_' . $l_mode],

			'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=ucp&amp;field=add'),

			'S_USERNAME_OPTIONS'	=> $s_username_options,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> $this->u_action)
		);

		$this->tpl_name = 'ucp_zebra_' . $mode;
		$this->page_title = 'UCP_ZEBRA_' . $l_mode;
	}
}
