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

namespace phpbb\ucp\controller;

class zebra
{
	var $u_action;

	public function main($id, $mode)
	{

		$submit	= ($this->request->is_set_post('submit') || isset($_GET['add']) || isset($_GET['remove'])) ? true : false;
		$s_hidden_fields = '';

		$l_mode = strtoupper($mode);

		if ($submit)
		{
			$data = $error = [];
			$updated = false;

			$var_ary = [
				'usernames'	=> [0],
				'add'		=> '',
			];

			foreach ($var_ary as $var => $default)
			{
				$data[$var] = $this->request->variable($var, $default, true);
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
						 * @var string	mode		Zebra type: friends|foes
						 * @var array	user_ids	User ids we remove
						 * @since 3.1.0-a1
						 */
						$vars = ['mode', 'user_ids'];
						extract($this->dispatcher->trigger_event('core.ucp_remove_zebra', compact($vars)));

						$sql = 'DELETE FROM ' . $this->tables['zebra'] . '
							WHERE user_id = ' . $this->user->data['user_id'] . '
								AND ' . $this->db->sql_in_set('zebra_id', $user_ids);
						$this->db->sql_query($sql);

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
							FROM ' . $this->tables['zebra'] . ' z, ' . $this->tables['users'] . ' u
							WHERE z.user_id = ' . $this->user->data['user_id'] . '
								AND u.user_id = z.zebra_id';
						$result = $this->db->sql_query($sql);

						$friends = $foes = [];
						while ($row = $this->db->sql_fetchrow($result))
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
						$this->db->sql_freeresult($result);

						// remove friends from the username array
						$n = count($data['add']);
						$data['add'] = array_diff($data['add'], $friends);

						if (count($data['add']) < $n && $mode == 'foes')
						{
							$error[] = $this->language->lang('NOT_ADDED_FOES_FRIENDS');
						}

						// remove foes from the username array
						$n = count($data['add']);
						$data['add'] = array_diff($data['add'], $foes);

						if (count($data['add']) < $n && $mode == 'friends')
						{
							$error[] = $this->language->lang('NOT_ADDED_FRIENDS_FOES');
						}

						// remove the user himself from the username array
						$n = count($data['add']);
						$data['add'] = array_diff($data['add'], [utf8_clean_string($this->user->data['username'])]);

						if (count($data['add']) < $n)
						{
							$error[] = $this->language->lang('NOT_ADDED_' . $l_mode . '_SELF');
						}

						unset($friends, $foes, $n);

						if (count($data['add']))
						{
							$sql = 'SELECT user_id, user_type
								FROM ' . $this->tables['users'] . '
								WHERE ' . $this->db->sql_in_set('username_clean', $data['add']) . '
									AND user_type <> ' . USER_INACTIVE;
							$result = $this->db->sql_query($sql);

							$user_id_ary = [];
							while ($row = $this->db->sql_fetchrow($result))
							{
								if ($row['user_id'] != ANONYMOUS && $row['user_type'] != USER_IGNORE)
								{
									$user_id_ary[] = $row['user_id'];
								}
								else if ($row['user_id'] != ANONYMOUS)
								{
									$error[] = $this->language->lang('NOT_ADDED_' . $l_mode . '_BOTS');
								}
								else
								{
									$error[] = $this->language->lang('NOT_ADDED_' . $l_mode . '_ANONYMOUS');
								}
							}
							$this->db->sql_freeresult($result);

							if (count($user_id_ary))
							{
								// Remove users from foe list if they are admins or moderators
								if ($mode == 'foes')
								{
									$perms = [];
									foreach ($this->auth->acl_get_list($user_id_ary, ['a_', 'm_']) as $forum_id => $forum_ary)
									{
										foreach ($forum_ary as $auth_option => $user_ary)
										{
											$perms = array_merge($perms, $user_ary);
										}
									}

									$perms = array_unique($perms);

									if (count($perms))
									{
										$error[] = $this->language->lang('NOT_ADDED_FOES_MOD_ADMIN');
									}

									// This may not be right ... it may yield true when perms equate to deny
									$user_id_ary = array_diff($user_id_ary, $perms);
									unset($perms);
								}

								if (count($user_id_ary))
								{
									$sql_mode = ($mode == 'friends') ? 'friend' : 'foe';

									$sql_ary = [];
									foreach ($user_id_ary as $zebra_id)
									{
										$sql_ary[] = [
											'user_id'		=> (int) $this->user->data['user_id'],
											'zebra_id'		=> (int) $zebra_id,
											$sql_mode		=> 1
										];
									}

									/**
									 * Add users to friends/foes
									 *
									 * @event core.ucp_add_zebra
									 * @var string	mode		Zebra type:
									 *							friends|foes
									 * @var array	sql_ary		Array of
									 *							entries we add
									 * @since 3.1.0-a1
									 */
									$vars = ['mode', 'sql_ary'];
									extract($this->dispatcher->trigger_event('core.ucp_add_zebra', compact($vars)));

									$this->db->sql_multi_insert($this->tables['zebra'], $sql_ary);

									$updated = true;
								}
								unset($user_id_ary);
							}
							else if (!count($error))
							{
								$error[] = $this->language->lang('USER_NOT_FOUND_OR_INACTIVE');
							}
						}
					}

					if ($this->request->is_ajax())
					{
						$message = ($updated) ? $this->language->lang($l_mode . '_UPDATED') : implode('<br />', $error);

						$json_response = new \phpbb\json_response;
						$json_response->send([
							'success' => $updated,

							'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
							'MESSAGE_TEXT'	=> $message,
							'REFRESH_DATA'	=> [
								'time'	=> 3,
								'url'		=> $this->u_action
							]
						]);
					}
					else if ($updated)
					{
						meta_refresh(3, $this->u_action);
						$message = $this->language->lang($l_mode . '_UPDATED') . '<br />' . implode('<br />', $error) . ((count($error)) ? '<br />' : '') . '<br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}
					else
					{
						$this->template->assign_var('ERROR', implode('<br />', $error));
					}
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'mode'		=> $mode,
						'submit'	=> true,
						'usernames'	=> $data['usernames'],
						'add'		=> $data['add']])
					);
				}
			}
		}

		$sql_and = ($mode == 'friends') ? 'z.friend = 1' : 'z.foe = 1';
		$sql = 'SELECT z.*, u.username, u.username_clean
			FROM ' . $this->tables['zebra'] . ' z, ' . $this->tables['users'] . ' u
			WHERE z.user_id = ' . $this->user->data['user_id'] . "
				AND $sql_and
				AND u.user_id = z.zebra_id
			ORDER BY u.username_clean ASC";
		$result = $this->db->sql_query($sql);

		$s_username_options = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_username_options .= '<option value="' . $row['zebra_id'] . '">' . $row['username'] . '</option>';
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'L_TITLE'			=> $this->language->lang('UCP_ZEBRA_' . $l_mode),

			'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=ucp&amp;field=add'),

			'S_USERNAME_OPTIONS'	=> $s_username_options,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> $this->u_action]
		);

		$this->tpl_name = 'ucp_zebra_' . $mode;
		$this->page_title = 'UCP_ZEBRA_' . $l_mode;
	}
}
