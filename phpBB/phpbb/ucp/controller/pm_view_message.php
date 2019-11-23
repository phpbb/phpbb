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

class pm_view_message
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\profilefields\manager */
	protected $pf_manager;

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

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth				Auth object
	 * @param \phpbb\config\config				$config				Config object
	 * @param \phpbb\db\driver\driver_interface	$db					Database object
	 * @param \phpbb\event\dispatcher			$dispatcher			Event dispatcher object
	 * @param \phpbb\group\helper				$group_helper		Group helper object
	 * @param \phpbb\controller\helper			$helper				Controller helper object
	 * @param \phpbb\language\language			$language			Language object
	 * @param \phpbb\profilefields\manager		$pf_manager			Profile field manager object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\template\template			$template			Template object
	 * @param \phpbb\user						$user				User object
	 * @param string							$root_path			phpBB root path
	 * @param string							$php_ext			php File extensions
	 * @param array								$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\group\helper $group_helper,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\profilefields\manager $pf_manager,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->group_helper	= $group_helper;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->pf_manager	= $pf_manager;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	/**
	 * View private message
	 */
	function view_message($folder_id, $msg_id, $folder, $message_row)
	{
		$this->language->add_lang(['viewtopic', 'memberlist']);

		$msg_id		= (int) $msg_id;
		$folder_id	= (int) $folder_id;
		$author_id	= (int) $message_row['author_id'];
		$view		= $this->request->variable('view', '');

		// Not able to view message, it was deleted by the sender
		if ($message_row['pm_deleted'])
		{
			$meta_info = $this->helper->route('ucp_pm_view', ['folder' => $folder_id]);
			$message = $this->language->lang('NO_AUTH_READ_REMOVED_MESSAGE');

			$message .= '<br /><br />' . $this->language->lang('RETURN_FOLDER', '<a href="' . $meta_info . '">', '</a>');
			send_status_line(403, 'Forbidden');
			trigger_error($message);
		}

		// Do not allow hold messages to be seen
		if ($folder_id == PRIVMSGS_HOLD_BOX)
		{
			trigger_error('NO_AUTH_READ_HOLD_MESSAGE');
		}

		// Load the custom profile fields
		if ($this->config['load_cpf_pm'])
		{
			$profile_fields = $this->pf_manager->grab_profile_fields_data($author_id);
		}

		// Assign TO/BCC Addresses to template
		write_pm_addresses(['to' => $message_row['to_address'], 'bcc' => $message_row['bcc_address']], $author_id);

		$user_info = $this->get_user_information($author_id, $message_row);

		// Parse the message and subject
		$parse_flags = ($message_row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$message = generate_text_for_display($message_row['message_text'], $message_row['bbcode_uid'], $message_row['bbcode_bitfield'], $parse_flags, true);

		// Replace naughty words such as farty pants
		$message_row['message_subject'] = censor_text($message_row['message_subject']);

		// Editing information
		if ($message_row['message_edit_count'] && $this->config['display_last_edited'])
		{
			if (!$message_row['message_edit_user'])
			{
				$display_username = get_username_string('full', $author_id, $user_info['username'], $user_info['user_colour']);
			}
			else
			{
				$edit_user_info = $this->get_user_information($message_row['message_edit_user'], false);
				$display_username = get_username_string('full', $message_row['message_edit_user'], $edit_user_info['username'], $edit_user_info['user_colour']);
			}
			$l_edited_by = '<br /><br />' . $this->language->lang('EDITED_TIMES_TOTAL', (int) $message_row['message_edit_count'], $display_username, $this->user->format_date($message_row['message_edit_time'], false, true));
		}
		else
		{
			$l_edited_by = '';
		}

		// Pull attachment data
		$display_notice = false;
		$attachments = [];

		if ($message_row['message_attachment'] && $this->config['allow_pm_attach'])
		{
			if ($this->auth->acl_get('u_pm_download'))
			{
				$sql = 'SELECT *
				FROM ' . $this->tables['attachments'] . "
				WHERE post_msg_id = $msg_id
					AND in_message = 1
				ORDER BY filetime DESC, post_msg_id ASC";
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$attachments[] = $row;
				}
				$this->db->sql_freeresult($result);

				// No attachments exist, but message table thinks they do so go ahead and reset attach flags
				if (!count($attachments))
				{
					$sql = 'UPDATE ' . $this->tables['privmsgs'] . "
					SET message_attachment = 0
					WHERE msg_id = $msg_id";
					$this->db->sql_query($sql);
				}
			}
			else
			{
				$display_notice = true;
			}
		}

		// Assign inline attachments
		if (!empty($attachments))
		{
			$update_count = [];
			parse_attachments(false, $message, $attachments, $update_count);

			// Update the attachment download counts
			if (count($update_count))
			{
				$sql = 'UPDATE ' . $this->tables['attachments'] . '
				SET download_count = download_count + 1
				WHERE ' . $this->db->sql_in_set('attach_id', array_unique($update_count));
				$this->db->sql_query($sql);
			}
		}

		$user_info['sig'] = '';

		$signature = ($message_row['enable_sig'] && $this->config['allow_sig'] && $this->auth->acl_get('u_sig') && $this->user->optionget('viewsigs')) ? $user_info['user_sig'] : '';

		// End signature parsing, only if needed
		if ($signature)
		{
			$parse_flags = ($user_info['user_sig_bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			$signature = generate_text_for_display($signature, $user_info['user_sig_bbcode_uid'], $user_info['user_sig_bbcode_bitfield'], $parse_flags, true);
		}

		// Number of "to" recipients
		$num_recipients = (int) preg_match_all('/:?(u|g)_([0-9]+):?/', $message_row['to_address'], $match);

		$bbcode_status	= ($this->config['allow_bbcode'] && $this->config['auth_bbcode_pm'] && $this->auth->acl_get('u_pm_bbcode')) ? true : false;

		// Get the profile fields template data
		$cp_row = [];
		if ($this->config['load_cpf_pm'] && isset($profile_fields[$author_id]))
		{
			// Filter the fields we don't want to show
			foreach ($profile_fields[$author_id] as $used_ident => $profile_field)
			{
				if (!$profile_field['data']['field_show_on_pm'])
				{
					unset($profile_fields[$author_id][$used_ident]);
				}
			}

			if (isset($profile_fields[$author_id]))
			{
				$cp_row = $this->pf_manager->generate_profile_fields_template_data($profile_fields[$author_id]);
			}
		}

		$u_pm = $u_jabber = '';

		if ($this->config['allow_privmsg'] && $this->auth->acl_get('u_sendpm') && ($user_info['user_allow_pm'] || $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_')))
		{
			$u_pm = $this->helper->route('ucp_pm_compose', ['u' => $author_id]);
		}

		if ($this->config['jab_enable'] && $user_info['user_jabber'] && $this->auth->acl_get('u_sendim'))
		{
			$u_jabber = append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=contact&amp;action=jabber&amp;u=' . $author_id);
		}

		$msg_data = [
			'MESSAGE_AUTHOR_FULL'		=> get_username_string('full', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
			'MESSAGE_AUTHOR_COLOUR'		=> get_username_string('colour', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
			'MESSAGE_AUTHOR'			=> get_username_string('username', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
			'U_MESSAGE_AUTHOR'			=> get_username_string('profile', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),

			'RANK_TITLE'		=> $user_info['rank_title'],
			'RANK_IMG'			=> $user_info['rank_image'],
			'AUTHOR_AVATAR'		=> (isset($user_info['avatar'])) ? $user_info['avatar'] : '',
			'AUTHOR_JOINED'		=> $this->user->format_date($user_info['user_regdate']),
			'AUTHOR_POSTS'		=> (int) $user_info['user_posts'],
			'U_AUTHOR_POSTS'	=> ($this->config['load_search'] && $this->auth->acl_get('u_search')) ? append_sid("{$this->root_path}search.$this->php_ext", "author_id=$author_id&amp;sr=posts") : '',
			'CONTACT_USER'		=> $this->language->lang('CONTACT_USER', get_username_string('username', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username'])),

			'ONLINE_IMG'		=> (!$this->config['load_onlinetrack']) ? '' : ((isset($user_info['online']) && $user_info['online']) ? $this->user->img('icon_user_online', $this->language->lang('ONLINE')) : $this->user->img('icon_user_offline', $this->language->lang('OFFLINE'))),
			'S_ONLINE'			=> (!$this->config['load_onlinetrack']) ? false : ((isset($user_info['online']) && $user_info['online']) ? true : false),
			'DELETE_IMG'		=> $this->user->img('icon_post_delete', $this->language->lang('DELETE_MESSAGE')),
			'INFO_IMG'			=> $this->user->img('icon_post_info', $this->language->lang('VIEW_PM_INFO')),
			'PROFILE_IMG'		=> $this->user->img('icon_user_profile', $this->language->lang('READ_PROFILE')),
			'EMAIL_IMG'			=> $this->user->img('icon_contact_email', $this->language->lang('SEND_EMAIL')),
			'QUOTE_IMG'			=> $this->user->img('icon_post_quote', $this->language->lang('POST_QUOTE_PM')),
			'REPLY_IMG'			=> $this->user->img('button_pm_reply', $this->language->lang('POST_REPLY_PM')),
			'REPORT_IMG'		=> $this->user->img('icon_post_report', 'REPORT_PM'),
			'EDIT_IMG'			=> $this->user->img('icon_post_edit', $this->language->lang('POST_EDIT_PM')),
			'MINI_POST_IMG'		=> $this->user->img('icon_post_target', $this->language->lang('PM')),

			'SENT_DATE'			=> ($view == 'print') ? $this->user->format_date($message_row['message_time'], false, true) : $this->user->format_date($message_row['message_time']),
			'SUBJECT'			=> $message_row['message_subject'],
			'MESSAGE'			=> $message,
			'SIGNATURE'			=> ($message_row['enable_sig']) ? $signature : '',
			'EDITED_MESSAGE'	=> $l_edited_by,
			'MESSAGE_ID'		=> $message_row['msg_id'],

			'U_PM'				=>	$u_pm,
			'U_JABBER'			=>	$u_jabber,

			'U_DELETE'			=> ($this->auth->acl_get('u_pm_delete')) ? $this->helper->route('ucp_pm_compose', ['action' => 'delete', 'f' => $folder_id, 'p' => $message_row['msg_id']]) : '',
			'U_EMAIL'			=> $user_info['email'],
			'U_REPORT'			=> ($this->config['allow_pm_report']) ? $this->helper->route('phpbb_report_pm_controller', ['id' => $message_row['msg_id']]) : '',
			'U_QUOTE'			=> ($this->auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? $this->helper->route('ucp_pm_compose', ['action' => 'quote', 'f' => $folder_id, 'p' => $message_row['msg_id']]) : '',
			'U_EDIT'			=> (($message_row['message_time'] > time() - ($this->config['pm_edit_time'] * 60) || !$this->config['pm_edit_time']) && $folder_id == PRIVMSGS_OUTBOX && $this->auth->acl_get('u_pm_edit')) ? $this->helper->route('ucp_pm_compose', ['action' => 'edit', 'f' => $folder_id, 'p' => $message_row['msg_id']]) : '',
			'U_POST_REPLY_PM'	=> ($this->auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? $this->helper->route('ucp_pm_compose', ['action' => 'reply', 'f' => $folder_id, 'p' => $message_row['msg_id']]) : '',
			'U_POST_REPLY_ALL'	=> ($this->auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? $this->helper->route('ucp_pm_compose', ['action' => 'quote', 'reply_to_all' => true, 'f' => $folder_id, 'p' => $message_row['msg_id']]) : '',
			'U_PREVIOUS_PM'		=> $this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'p' => $message_row['msg_id'], 'view' => 'previous']),
			'U_NEXT_PM'			=> $this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'p' => $message_row['msg_id'], 'view' => 'next']),

			'U_PM_ACTION'		=> $this->helper->route('ucp_pm_compose', ['f' => $folder_id, 'p' => $message_row['msg_id']]),

			'S_HAS_ATTACHMENTS'	=> (count($attachments)) ? true : false,
			'S_DISPLAY_NOTICE'	=> $display_notice && $message_row['message_attachment'],
			'S_AUTHOR_DELETED'	=> ($author_id == ANONYMOUS) ? true : false,
			'S_SPECIAL_FOLDER'	=> in_array($folder_id, [PRIVMSGS_NO_BOX, PRIVMSGS_OUTBOX]),
			'S_PM_RECIPIENTS'	=> $num_recipients,
			'S_BBCODE_ALLOWED'	=> ($bbcode_status) ? 1 : 0,
			'S_CUSTOM_FIELDS'	=> (!empty($cp_row['row'])) ? true : false,

			'U_PRINT_PM'		=> ($this->config['print_pm'] && $this->auth->acl_get('u_pm_printpm')) ?
				$this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'p' => $message_row['msg_id'], 'view' => 'print']) : '',
			'U_FORWARD_PM'		=> ($this->config['forward_pm'] && $this->auth->acl_get('u_sendpm') && $this->auth->acl_get('u_pm_forward')) ?
				$this->helper->route('ucp_pm_compose', ['action' => 'forward', 'f' => $folder_id, 'p' => $message_row['msg_id']]) : '',
		];

		/**
		 * Modify pm and sender data before it is assigned to the template
		 *
		 * @event core.ucp_pm_view_messsage
		 * @var mixed	id			Active module category (can be int or string)
		 * @var string	mode		Active module
		 * @var int		folder_id	ID of the folder the message is in
		 * @var int		msg_id		ID of the private message
		 * @var array	folder		Array with data of user's message folders
		 * @var array	message_row	Array with message data
		 * @var array	cp_row		Array with senders custom profile field data
		 * @var array	msg_data	Template array with message data
		 * @var array	user_info	User data of the sender
		 * @since 3.1.0-a1
		 * @changed 3.1.6-RC1		Added user_info into event
		 * @changed 3.2.2-RC1		Deprecated
		 * @deprecated 4.0.0			Event name is misspelled and is replaced with new event with correct name
		 */
		$vars = [
			'id',
			'mode',
			'folder_id',
			'msg_id',
			'folder',
			'message_row',
			'cp_row',
			'msg_data',
			'user_info',
		];
		extract($this->dispatcher->trigger_event('core.ucp_pm_view_messsage', compact($vars)));

		/**
		 * Modify pm and sender data before it is assigned to the template
		 *
		 * @event core.ucp_pm_view_message
		 * @var mixed	id			Active module category (can be int or string)
		 * @var string	mode		Active module
		 * @var int		folder_id	ID of the folder the message is in
		 * @var int		msg_id		ID of the private message
		 * @var array	folder		Array with data of user's message folders
		 * @var array	message_row	Array with message data
		 * @var array	cp_row		Array with senders custom profile field data
		 * @var array	msg_data	Template array with message data
		 * @var array	user_info	User data of the sender
		 * @var array	attachments	Attachments data
		 * @since 3.2.2-RC1
		 * @changed 3.2.5-RC1 Added attachments
		 */
		$vars = [
			'id',
			'mode',
			'folder_id',
			'msg_id',
			'folder',
			'message_row',
			'cp_row',
			'msg_data',
			'user_info',
			'attachments',
		];
		extract($this->dispatcher->trigger_event('core.ucp_pm_view_message', compact($vars)));

		$this->template->assign_vars($msg_data);

		$contact_fields = [
			[
				'ID'		=> 'pm',
				'NAME'		=> $this->language->lang('SEND_PRIVATE_MESSAGE'),
				'U_CONTACT' => $u_pm,
			],
			[
				'ID'		=> 'email',
				'NAME'		=> $this->language->lang('SEND_EMAIL'),
				'U_CONTACT'	=> $user_info['email'],
			],
			[
				'ID'		=> 'jabber',
				'NAME'		=> $this->language->lang('JABBER'),
				'U_CONTACT'	=> $u_jabber,
			],
		];

		foreach ($contact_fields as $field)
		{
			if ($field['U_CONTACT'])
			{
				$this->template->assign_block_vars('contact', $field);
			}
		}

		// Display the custom profile fields
		if (!empty($cp_row['row']))
		{
			$this->template->assign_vars($cp_row['row']);

			foreach ($cp_row['blockrow'] as $cp_block_row)
			{
				$this->template->assign_block_vars('custom_fields', $cp_block_row);

				if ($cp_block_row['S_PROFILE_CONTACT'])
				{
					$this->template->assign_block_vars('contact', [
						'ID'		=> $cp_block_row['PROFILE_FIELD_IDENT'],
						'NAME'		=> $cp_block_row['PROFILE_FIELD_NAME'],
						'U_CONTACT'	=> $cp_block_row['PROFILE_FIELD_CONTACT'],
					]);
				}
			}
		}

		// Display not already displayed Attachments for this post, we already parsed them. ;)
		if (isset($attachments) && count($attachments))
		{
			foreach ($attachments as $attachment)
			{
				$this->template->assign_block_vars('attachment', ['DISPLAY_ATTACHMENT' => $attachment]);
			}
		}

		if (!$this->request->is_set('view') || $this->request->variable('view', '') != 'print')
		{
			// Message History
			if (message_history($msg_id, $this->user->data['user_id'], $message_row, $folder))
			{
				$this->template->assign_var('S_DISPLAY_HISTORY', true);
			}
		}
	}

	/**
	 * Get user information (only for message display)
	 */
	protected function get_user_information($user_id, $user_row)
	{
		if (!$user_id)
		{
			return [];
		}

		if (empty($user_row))
		{
			$sql = 'SELECT *
			FROM ' . $this->tables['users'] . '
			WHERE user_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
		}

		// Some standard values
		$user_row['online'] = false;
		$user_row['rank_title'] = $user_row['rank_image'] = $user_row['rank_image_src'] = $user_row['email'] = '';

		// Generate online information for user
		if ($this->config['load_onlinetrack'])
		{
			$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
			FROM ' . $this->tables['sessions'] . "
			WHERE session_user_id = $user_id
			GROUP BY session_user_id";
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$update_time = $this->config['load_online_time'] * 60;
			if ($row)
			{
				$user_row['online'] = (time() - $update_time < $row['online_time'] && ($row['viewonline'] || $this->auth->acl_get('u_viewonline'))) ? true : false;
			}
		}

		$user_row['avatar'] = ($this->user->optionget('viewavatars')) ? phpbb_get_user_avatar($user_row) : '';

		if (!function_exists('phpbb_get_user_rank'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}

		$user_rank_data = phpbb_get_user_rank($user_row, $user_row['user_posts']);
		$user_row['rank_title'] = $user_rank_data['title'];
		$user_row['rank_image'] = $user_rank_data['img'];
		$user_row['rank_image_src'] = $user_rank_data['img_src'];

		if ((!empty($user_row['user_allow_viewemail']) && $this->auth->acl_get('u_sendemail')) || $this->auth->acl_get('a_email'))
		{
			$user_row['email'] = ($this->config['board_email_form'] && $this->config['email_enable']) ? append_sid("{$this->root_path}memberlist.$this->php_ext", "mode=email&amp;u=$user_id") : ((($this->config['board_hide_emails'] && !$this->auth->acl_get('a_email')) || empty($user_row['user_email'])) ? '' : 'mailto:' . $user_row['user_email']);
		}

		return $user_row;
	}
}
