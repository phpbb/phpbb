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

namespace phpbb\mcp\controller;

class zebra
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->lang			= $lang;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	function main($id, $mode)
	{
		$submit	= $this->request->is_set_post('submit')
				|| $this->request->is_set('add', \phpbb\request\request_interface::GET)
				|| $this->request->is_set('remove', \phpbb\request\request_interface::GET);

		$l_mode = strtoupper($mode);
		$s_hidden_fields = '';

		if ($submit)
		{
			$data = [];
			$errors = [];
			$updated = false;

			$var_ary = [
				'usernames'	=> [0],
				'add'		=> '',
			];

			foreach ($var_ary as $var => $default)
			{
				$data[$var] = $this->request->variable($var, $default, true);
			}

			if (!empty($data['add']) || !empty($data['usernames']))
			{
				if (confirm_box(true))
				{
					// Remove users
					if (!empty($data['usernames']))
					{
						$user_ids = $data['usernames'];

						/**
						 * Remove users from friends/foes.
						 *
						 * @event core.ucp_remove_zebra
						 * @var string	mode		Zebra type: friends|foes
						 * @var array	user_ids	User ids we remove
						 * @since 3.1.0-a1
						 */
						$vars = ['mode', 'user_ids'];
						extract($this->dispatcher->trigger_event('core.ucp_remove_zebra', compact($vars)));

						$sql = 'DELETE FROM ' . $this->tables['zebra'] . '
							WHERE user_id = ' . (int) $this->user->data['user_id'] . '
								AND ' . $this->db->sql_in_set('zebra_id', $user_ids);
						$this->db->sql_query($sql);

						$updated = true;
					}

					// Add users
					if ($data['add'])
					{
						$data['add'] = array_map('trim', array_map('utf8_clean_string', explode("\n", $data['add'])));

						$friends = $foes = [];

						// Do these name/s exist on a list already? If so, ignore ... we could be
						// 'nice' and automatically handle names added to one list present on
						// the other (by removing the existing one) ... but I have a feeling this
						// may lead to complaints
						$sql = 'SELECT z.*, u.username, u.username_clean
							FROM ' . $this->tables['zebra'] . ' z, 
								' . $this->tables['users'] . ' u
							WHERE z.user_id = ' . (int) $this->user->data['user_id'] . '
								AND u.user_id = z.zebra_id';
						$result = $this->db->sql_query($sql);
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

						if (count($data['add']) < $n && $mode === 'foes')
						{
							$errors[] = $this->lang->lang('NOT_ADDED_FOES_FRIENDS');
						}

						// remove foes from the username array
						$n = count($data['add']);
						$data['add'] = array_diff($data['add'], $foes);

						if (count($data['add']) < $n && $mode === 'friends')
						{
							$errors[] = $this->lang->lang('NOT_ADDED_FRIENDS_FOES');
						}

						// remove the user himself from the username array
						$n = count($data['add']);
						$data['add'] = array_diff($data['add'], [utf8_clean_string($this->user->data['username'])]);

						if (count($data['add']) < $n)
						{
							$errors[] = $this->lang->lang('NOT_ADDED_' . $l_mode . '_SELF');
						}

						unset($friends, $foes, $n);

						if (!empty($data['add']))
						{
							$user_id_ary = [];

							$sql = 'SELECT user_id, user_type
								FROM ' . $this->tables['users'] . '
								WHERE ' . $this->db->sql_in_set('username_clean', $data['add']) . '
									AND user_type <> ' . USER_INACTIVE;
							$result = $this->db->sql_query($sql);
							while ($row = $this->db->sql_fetchrow($result))
							{
								if ($row['user_id'] != ANONYMOUS && $row['user_type'] != USER_IGNORE)
								{
									$user_id_ary[] = (int) $row['user_id'];
								}
								else if ($row['user_id'] != ANONYMOUS)
								{
									$errors[] = $this->lang->lang('NOT_ADDED_' . $l_mode . '_BOTS');
								}
								else
								{
									$errors[] = $this->lang->lang('NOT_ADDED_' . $l_mode . '_ANONYMOUS');
								}
							}
							$this->db->sql_freeresult($result);

							if (!empty($user_id_ary))
							{
								// Remove users from foe list if they are admins or moderators
								if ($mode === 'foes')
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

									if (!empty($perms))
									{
										$errors[] = $this->lang->lang('NOT_ADDED_FOES_MOD_ADMIN');
									}

									// This may not be right ... it may yield true when perms equate to deny
									$user_id_ary = array_diff($user_id_ary, $perms);
									unset($perms);
								}

								if (!empty($user_id_ary))
								{
									$sql_ary = [];
									$sql_mode = ($mode === 'friends') ? 'friend' : 'foe';

									foreach ($user_id_ary as $zebra_id)
									{
										$sql_ary[] = [
											'user_id'		=> (int) $this->user->data['user_id'],
											'zebra_id'		=> (int) $zebra_id,
											$sql_mode		=> 1,
										];
									}

									/**
									* Add users to friends/foes.
									*
									* @event core.ucp_add_zebra
									* @var string	mode		Zebra type: friends|foes
									* @var array	sql_ary		Array of entries we add
									* @since 3.1.0-a1
									*/
									$vars = ['mode', 'sql_ary'];
									extract($this->dispatcher->trigger_event('core.ucp_add_zebra', compact($vars)));

									$this->db->sql_multi_insert($this->tables['zebra'], $sql_ary);

									$updated = true;
								}
								unset($user_id_ary);
							}
							else if (empty($errors))
							{
								$errors[] = $this->lang->lang('USER_NOT_FOUND_OR_INACTIVE');
							}
						}
					}

					if ($this->request->is_ajax())
					{
						$message = $updated ? $this->lang->lang($l_mode . '_UPDATED') : implode('<br />', $errors);

						$json_response = new \phpbb\json_response;
						$json_response->send([
							'success'		=> $updated,

							'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
							'MESSAGE_TEXT'	=> $message,
							'REFRESH_DATA'	=> [
								'time'	=> 3,
								'url'	=> $this->u_action,
							],
						]);
					}
					else if ($updated)
					{
						meta_refresh(3, $this->u_action);
						$message = $this->lang->lang($l_mode . '_UPDATED') . '<br />' . implode('<br />', $errors) . (!empty($errors) ? '<br />' : '') . '<br />' . $this->lang->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}
					else
					{
						$this->template->assign_var('ERROR', implode('<br />', $errors));
					}
				}
				else
				{
					confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'mode'		=> $mode,
						'submit'	=> true,
						'usernames'	=> $data['usernames'],
						'add'		=> $data['add'],
					]));
				}
			}
		}

		$s_username_options = '';

		$sql = 'SELECT z.*, u.username, u.username_clean
			FROM ' . $this->tables['zebra'] . ' z, 
				' . $this->tables['users'] . ' u
			WHERE z.user_id = ' . (int) $this->user->data['user_id'] . '
				AND ' . ($mode === 'friends' ? 'z.friend = 1' : 'z.foe = 1') . '
				AND u.user_id = z.zebra_id
			ORDER BY u.username_clean ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_username_options .= '<option value="' . $row['zebra_id'] . '">' . $row['username'] . '</option>';
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'L_TITLE'				=> $this->lang->lang('UCP_ZEBRA_' . $l_mode),

			'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=ucp&amp;field=add'),

			'S_USERNAME_OPTIONS'	=> $s_username_options,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> $this->u_action,
		]);

		$this->tpl_name = 'ucp_zebra_' . $mode;
		$this->page_title = 'UCP_ZEBRA_' . $l_mode;
	}
}
