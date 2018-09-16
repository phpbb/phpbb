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

namespace phpbb\privatemessage;

use Symfony\Component\HttpFoundation\RedirectResponse;

class compose
{
	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\request\request
	 */
	protected $request;

	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\group\helper
	 */
	protected $group_helper;

	/**
	 * @var \phpbb\textformatter\s9e\utils
	 */
	protected $text_formatter_utils;

	/**
	 * @var \phpbb\plupload\plupload
	 */
	protected $plupload;

	/**
	 * @var \phpbb\event\dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var string
	 */
	protected $root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\auth\auth $auth, \phpbb\group\helper $group_helper, \phpbb\textformatter\s9e\utils $text_formatter_utils, \phpbb\plupload\plupload $plupload, \phpbb\event\dispatcher $dispatcher, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->request = $request;
		$this->auth = $auth;
		$this->group_helper = $group_helper;
		$this->text_formatter_utils = $text_formatter_utils;
		$this->plupload = $plupload;
		$this->dispatcher = $dispatcher;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function handle()
	{
		if (!$this->user->data['is_registered'])
		{
			return $this->helper->error('NO_MESSAGE', 401);
		}

		if (!$this->config['allow_privmsg'])
		{
			return $this->helper->error('PM_DISABLED', 403);
		}

		$this->language->add_lang(array('privatemessage', 'ucp', 'posting'));








		$action = $this->request->variable('action', '');
		if (!$action)
		{
			$action = 'post';
		}
		add_form_key('ucp_pm_compose');

		// Grab only parameters needed here
		$to_user_id = $this->request->variable('u', 0);
		$to_group_id = $this->request->variable('g', 0);
		$msg_id = $this->request->variable('p', 0);
		$draft_id = $this->request->variable('d', 0);
		$lastclick = $this->request->variable('lastclick', 0);

		// Reply to all triggered (quote/reply)
		$reply_to_all	= $this->request->variable('reply_to_all', 0);

		$address_list	= $this->request->variable('address_list', array('' => array(0 => '')));

		$preview = $this->request->is_set_post('preview');
		$save = $this->request->is_set_post('save');
		$load = $this->request->is_set_post('load');
		$cancel = $this->request->is_set_post('cancel') && !$save;
		$delete = $this->request->is_set_post('delete');

		$remove_u = $this->request->is_set('remove_u');
		$remove_g = $this->request->is_set('remove_g');
		$add_to = $this->request->is_set('add_to');
		$add_bcc = $this->request->is_set('add_bcc');

		$refresh = $this->request->is_set_post('add_file') || $this->request->is_set_post('delete_file') || $save || $load || $remove_u || $remove_g || $add_to || $add_bcc;
		$submit = $this->request->is_set_post('post') && !$refresh && !$preview;

		$action = ($delete && !$preview && !$refresh && $submit) ? 'delete' : $action;
		$select_single = ($this->config['allow_mass_pm'] && $this->auth->acl_get('u_masspm')) ? false : true;

		$error = array();
		$current_time = time();

		// Was cancel pressed? If so then redirect to the appropriate page
		if ($cancel || ($current_time - $lastclick < 2 && $submit))
		{
			if ($msg_id)
			{
				redirect($controller_helper->route('phpbb_privatemessage_conversation', array('id' => $msg_id)));
			}
			redirect($controller_helper->route('phpbb_privatemessage_index'));
		}

		/**
		* Modify the default vars before composing a PM
		*
		* @event core.ucp_pm_compose_modify_data
		* @var	int		msg_id					post_id in the page request
		* @var	int		to_user_id				The id of whom the message is to
		* @var	int		to_group_id				The id of the group the message is to
		* @var	bool	submit					Whether the form has been submitted
		* @var	bool	preview					Whether the user is previewing the PM or not
		* @var	string	action					One of: post, reply, quote, forward, quotepost, edit, delete, smilies
		* @var	bool	delete					Whether the user is deleting the PM
		* @var	int		reply_to_all			Value of reply_to_all request variable.
		* @since 3.1.4-RC1
		*/
		$vars = array(
			'msg_id',
			'to_user_id',
			'to_group_id',
			'submit',
			'preview',
			'action',
			'delete',
			'reply_to_all',
		);
		extract($this->dispatcher->trigger_event('core.ucp_pm_compose_modify_data', compact($vars)));

		// Output PM_TO box if message composing
		if (in_array($action, array('post', 'forward', 'quotepost')))
		{
			// Add groups to PM box
			if ($this->config['allow_mass_pm'] && $this->auth->acl_get('u_masspm_group'))
			{
				$sql = 'SELECT g.group_id, g.group_name, g.group_type
					FROM ' . GROUPS_TABLE . ' g';
				
				if (!$this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
				{
					$sql .= ' LEFT JOIN ' . USER_GROUP_TABLE . ' ug
						ON (
							g.group_id = ug.group_id
							AND ug.user_id = ' . $this->user->data['user_id'] . '
							AND ug.user_pending = 0
						)
						WHERE (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $this->user->data['user_id'] . ')';
				}

				$sql .= ($this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? ' WHERE ' : ' AND ';

				$sql .= 'g.group_receive_pm = 1
					ORDER BY g.group_type DESC, g.group_name ASC';
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('group_options', array(
						'CLASS'	=> $row['group_type'] == GROUP_SPECIAL ? 'sep' : '',
						'VALUE'	=> $row['group_id'],
						'TEXT'	=> $this->group_helper->get_name($row['group_name']),
					));
				}
				$this->db->sql_freeresult($result);
			}

			$this->template->assign_vars(array(
				'S_SHOW_PM_BOX'		=> true,
				'S_ALLOW_MASS_PM'	=> $this->config['allow_mass_pm'] && $this->auth->acl_get('u_masspm'),
				'S_GROUP_OPTIONS'	=> $this->config['allow_mass_pm'] && $this->auth->acl_get('u_masspm_group'),
				'U_FIND_USERNAME'	=> append_sid($this->root_path . 'memberlist.' . $this->php_ext, "mode=searchuser&amp;form=postform&amp;field=username_list&amp;select_single=" . (int) $select_single),
			));
		}

		$sql = '';

		// What is all this following SQL for? Well, we need to know
		// some basic information in all cases before we do anything.
		switch ($action)
		{
			case 'post':
				if (!$this->auth->acl_get('u_sendpm'))
				{
					return $this->helper->error('NO_AUTH_SEND_MESSAGE', 403);
				}
			break;

			case 'reply':
			case 'quote':
			case 'forward':
			case 'quotepost':
				if (!$msg_id)
				{
					return $this->helper->error('NO_MESSAGE', 404);
				}

				if (!$this->auth->acl_get('u_sendpm'))
				{
					return $this->helper->error('NO_AUTH_SEND_MESSAGE', 403);
				}

				if ($action == 'quotepost')
				{
					$sql = 'SELECT p.post_id as msg_id, p.forum_id, p.post_text as message_text, p.poster_id as author_id, p.post_time as message_time, p.bbcode_bitfield, p.bbcode_uid, p.enable_sig, p.enable_smilies, p.enable_magic_url, t.topic_title as message_subject, u.username as quote_username
						FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . " u
						WHERE p.post_id = $msg_id
							AND t.topic_id = p.topic_id
							AND u.user_id = p.poster_id";
				}
				else
				{
					$sql = 'SELECT t.folder_id, p.*, u.username as quote_username
						FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . ' u
						WHERE t.user_id = ' . $this->user->data['user_id'] . "
							AND p.author_id = u.user_id
							AND t.msg_id = p.msg_id
							AND p.msg_id = $msg_id";
				}
			break;

			case 'edit':
				if (!$msg_id)
				{
					return $this->helper->error('NO_MESSAGE', 404);
				}

				// check for outbox (not read) status, we do not allow editing if one user already having the message
				$sql = 'SELECT p.*, t.folder_id
					FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p
					WHERE t.user_id = ' . $this->user->data['user_id'] . '
						AND t.folder_id = ' . PRIVMSGS_OUTBOX . "
						AND t.msg_id = $msg_id
						AND t.msg_id = p.msg_id";
			break;

			case 'delete':
				if (!$this->auth->acl_get('u_pm_delete'))
				{
					return $this->helper->error('NO_AUTH_DELETE_MESSAGE', 403);
				}

				if (!$msg_id)
				{
					return $this->helper->error('NO_MESSAGE', 404);
				}

				$sql = 'SELECT msg_id, pm_unread, pm_new, author_id, folder_id
					FROM ' . PRIVMSGS_TO_TABLE . '
					WHERE user_id = ' . $this->user->data['user_id'] . "
						AND msg_id = $msg_id";
			break;

			default:
				return $this->helper->message('NO_ACTION_MODE');
			break;
		}

		if ($action == 'forward' && (!$this->config['forward_pm'] || !$this->auth->acl_get('u_pm_forward')))
		{
			return $this->helper->error('NO_AUTH_FORWARD_MESSAGE', 403);
		}

		if ($action == 'edit' && !$this->auth->acl_get('u_pm_edit'))
		{
			return $this->helper->error('NO_AUTH_EDIT_MESSAGE', 403);
		}

		if ($sql)
		{
			/**
			* Alter sql query to get message for user to write the PM
			*
			* @event core.ucp_pm_compose_compose_pm_basic_info_query_before
			* @var	string	sql						String with the query to be executed
			* @var	int		msg_id					topic_id in the page request
			* @var	int		to_user_id				The id of whom the message is to
			* @var	int		to_group_id				The id of the group whom the message is to
			* @var	bool	submit					Whether the user is sending the PM or not
			* @var	bool	preview					Whether the user is previewing the PM or not
			* @var	string	action					One of: post, reply, quote, forward, quotepost, edit, delete, smilies
			* @var	bool	delete					Whether the user is deleting the PM
			* @var	int		reply_to_all			Value of reply_to_all request variable.
			* @since 3.1.0-RC5
			* @changed 3.2.0-a1 Removed undefined variables
			*/
			$vars = array(
				'sql',
				'msg_id',
				'to_user_id',
				'to_group_id',
				'submit',
				'preview',
				'action',
				'delete',
				'reply_to_all',
			);
			extract($this->dispatcher->trigger_event('core.ucp_pm_compose_compose_pm_basic_info_query_before', compact($vars)));

			$result = $this->db->sql_query($sql);
			$post = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$post)
			{
				// If editing it could be the recipient already read the message...
				if ($action == 'edit')
				{
					$sql = 'SELECT p.*, t.folder_id
						FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p
						WHERE t.user_id = ' . $this->user->data['user_id'] . "
							AND t.msg_id = $msg_id
							AND t.msg_id = p.msg_id";
					$result = $this->db->sql_query($sql);
					$post = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($post)
					{
						return $this->helper->error('NO_EDIT_READ_MESSAGE', 403);
					}
				}

				return $this->helper->error('NO_MESSAGE', 404);
			}

			if ($action == 'quotepost')
			{
				if (($post['forum_id'] && !$this->auth->acl_get('f_read', $post['forum_id'])) || (!$post['forum_id'] && !$this->auth->acl_getf_global('f_read')))
				{
					return $this->helper->error('NOT_AUTHORISED', 403);
				}

				/**
				* Get the result of querying for the post to be quoted in the pm message
				*
				* @event core.ucp_pm_compose_quotepost_query_after
				* @var	string	sql					The original SQL used in the query
				* @var	array	post				Associative array with the data of the quoted post
				* @var	array	msg_id				The post_id that was searched to get the message for quoting
				* @var	int		to_user_id			Users the message is sent to
				* @var	int		to_group_id			Groups the message is sent to
				* @var	bool	submit				Whether the user is sending the PM or not
				* @var	bool	preview				Whether the user is previewing the PM or not
				* @var	string	action				One of: post, reply, quote, forward, quotepost, edit, delete, smilies
				* @var	bool	delete				If deleting message
				* @var	int		reply_to_all		Value of reply_to_all request variable.
				* @since 3.1.0-RC5
				* @changed 3.2.0-a1 Removed undefined variables
				*/
				$vars = array(
					'sql',
					'post',
					'msg_id',
					'to_user_id',
					'to_group_id',
					'submit',
					'preview',
					'action',
					'delete',
					'reply_to_all',
				);
				extract($this->dispatcher->trigger_event('core.ucp_pm_compose_quotepost_query_after', compact($vars)));

				// Passworded forum?
				if ($post['forum_id'])
				{
					$sql = 'SELECT forum_id, forum_name, forum_password
						FROM ' . FORUMS_TABLE . '
						WHERE forum_id = ' . (int) $post['forum_id'];
					$result = $this->db->sql_query($sql);
					$forum_data = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!empty($forum_data['forum_password']))
					{
						login_forum_box($forum_data);
					}
				}
			}

			$msg_id = (int) $post['msg_id'];
			$folder_id = (isset($post['folder_id'])) ? $post['folder_id'] : 0;
			$message_text = (isset($post['message_text'])) ? $post['message_text'] : '';

			if ((!$post['author_id'] || ($post['author_id'] == ANONYMOUS && $action != 'delete')) && $msg_id)
			{
				return $this->helper->error('NO_AUTHOR', 404);
			}

			if ($action == 'quotepost')
			{
				if (!function_exists('decode_message'))
				{
					include($this->root_path . 'includes/functions_content.' . $this->php_ext);
				}

				// Decode text for message display
				\decode_message($message_text, $post['bbcode_uid']);
			}

			if ($action != 'delete')
			{
				$enable_urls = 1;
				$enable_sig = false;

				$message_attachment = (isset($post['message_attachment'])) ? $post['message_attachment'] : 0;
				$message_subject = $post['message_subject'];
				$message_time = $post['message_time'];
				$bbcode_uid = $post['bbcode_uid'];

				$quote_username = (isset($post['quote_username'])) ? $post['quote_username'] : '';
				$icon_id = 0;

				if (($action == 'reply' || $action == 'quote' || $action == 'quotepost') && !count($address_list) && !$refresh && !$submit && !$preview)
				{
					// Add the original author as the recipient if quoting a post or only replying and not having checked "reply to all"
					if ($action == 'quotepost' || !$reply_to_all)
					{
						$address_list = array('u' => array($post['author_id'] => 'to'));
					}
					else
					{
						// We try to include every previously listed member from the TO Header - Reply to all
						$address_list = $this->rebuild_header(array('to' => $post['to_address']));

						// Add the author (if he is already listed then this is no shame (it will be overwritten))
						$address_list['u'][$post['author_id']] = 'to';

						// Now, make sure the user itself is not listed. ;)
						if (isset($address_list['u'][$user->data['user_id']]))
						{
							unset($address_list['u'][$user->data['user_id']]);
						}
					}
				}
				else if ($action == 'edit' && !count($address_list) && !$refresh && !$submit && !$preview)
				{
					// Rebuild TO and BCC Header
					$address_list = $this->rebuild_header(array('to' => $post['to_address'], 'bcc' => $post['bcc_address']));
				}

				if ($action == 'quotepost')
				{
					$check_value = 0;
				}
				else
				{
					$check_value = ((1+1) << 8) + ((1+1) << 4) + ((1+1) << 2) + ((0+1) << 1);
				}
			}
		}
		else
		{
			$message_attachment = 0;
			$message_text = $message_subject = '';

			/**
			* Predefine message text and subject
			*
			* @event core.ucp_pm_compose_predefined_message
			* @var	string	message_text	Message text
			* @var	string	message_subject	Messate subject
			* @since 3.1.11-RC1
			*/
			$vars = array('message_text', 'message_subject');
			extract($this->dispatcher->trigger_event('core.ucp_pm_compose_predefined_message', compact($vars)));

			if ($to_user_id && $to_user_id != ANONYMOUS && $action == 'post')
			{
				$address_list['u'][$to_user_id] = 'to';
			}
			else if ($to_group_id && $action == 'post')
			{
				$address_list['g'][$to_group_id] = 'to';
			}
			$check_value = 0;
		}

		if (($to_group_id || isset($address_list['g'])) && (!$this->config['allow_mass_pm'] || !$this->auth->acl_get('u_masspm_group')))
		{
			return $this->helper->error('NO_AUTH_GROUP_MESSAGE', 403);
		}

		if ($action == 'edit' && !$refresh && !$preview && !$submit)
		{
			if (!($message_time > time() - ($this->config['pm_edit_time'] * 60) || !$this->config['pm_edit_time']))
			{
				return $this->helper->error('CANNOT_EDIT_MESSAGE_TIME', 403);
			}
		}

		if ($action == 'post')
		{
			$this->template->assign_var('S_NEW_MESSAGE', true);
		}

		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}

		$message_parser = new \parse_message();
		$message_parser->set_plupload($this->plupload);

		$message_parser->message = ($action == 'reply') ? '' : $message_text;
		unset($message_text);

		$s_action = $this->helper->route('phpbb_privatemessage_compose', array('p' => $msg_id));

		// Delete triggered ?
		if ($action == 'delete')
		{
			// Do we need to confirm ?
			if (\confirm_box(true))
			{
				// TODO: fetch root_msg_id here and redirect to the same conversation.
				// note: maybe also make sure that when we delete root msg, whole conversation is deleted
				delete_pm($this->user->data['user_id'], $msg_id);

				return new RedirectResponse($this->helper->route('phpbb_privatemessage_index'));
			}
			else
			{
				$s_hidden_fields = array(
					'p'			=> $msg_id,
					'action'	=> 'delete'
				);

				\confirm_box(false, 'DELETE_MESSAGE', \build_hidden_fields($s_hidden_fields));
			}

			return new RedirectResponse($this->helper->route('phpbb_privatemessage_conversation', array('id' => $msg_id)));
		}

		// Get maximum number of allowed recipients
		$max_recipients = $this->get_max_setting_from_group('max_recipients');

		// If it is 0, there is no limit set and we use the maximum value within the config.
		$max_recipients = (!$max_recipients) ? $this->config['pm_max_recipients'] : $max_recipients;

		// If this is a quote/reply "to all"... we may increase the max_recpients to the number of original recipients
		if (($action == 'reply' || $action == 'quote') && $max_recipients && $reply_to_all)
		{
			// We try to include every previously listed member from the TO Header
			$list = $this->rebuild_header(array('to' => $post['to_address']));

			// Can be an empty array too ;)
			$list = (!empty($list['u'])) ? $list['u'] : array();
			$list[$post['author_id']] = 'to';

			if (isset($list[$this->user->data['user_id']]))
			{
				unset($list[$this->user->data['user_id']]);
			}

			$max_recipients = ($max_recipients < count($list)) ? count($list) : $max_recipients;

			unset($list);
		}

		// Handle User/Group adding/removing
		$this->handle_message_list_actions($address_list, $error, $remove_u, $remove_g, $add_to, $add_bcc, $refresh, $submit, $preview);

		// Check mass pm to group permission
		if ((!$this->config['allow_mass_pm'] || !$this->auth->acl_get('u_masspm_group')) && !empty($address_list['g']))
		{
			$address_list = array();
			$error[] = $this->language->lang('NO_AUTH_GROUP_MESSAGE');
		}

		// Check mass pm to users permission
		if ((!$this->config['allow_mass_pm'] || !$this->auth->acl_get('u_masspm')) && $this->num_recipients($address_list) > 1)
		{
			$address_list = $this->get_recipients($address_list, 1);
			$error[] = $this->language->lang('TOO_MANY_RECIPIENTS', 1);
		}

		// Check for too many recipients
		if (!empty($address_list['u']) && $max_recipients && count($address_list['u']) > $max_recipients)
		{
			$address_list = $this->get_recipients($address_list, $max_recipients);
			$error[] = $this->language->lang('TOO_MANY_RECIPIENTS', $max_recipients);
		}

		// Always check if the submitted attachment data is valid and belongs to the user.
		// Further down (especially in submit_post()) we do not check this again.
		$message_parser->get_submitted_attachment_data();

		if ($message_attachment && !$submit && !$refresh && !$preview && $action == 'edit')
		{
			// Do not change to SELECT *
			$sql = 'SELECT attach_id, is_orphan, attach_comment, real_filename, filesize
				FROM ' . ATTACHMENTS_TABLE . "
				WHERE post_msg_id = $msg_id
					AND in_message = 1
					AND is_orphan = 0
				ORDER BY filetime DESC";
			$result = $this->db->sql_query($sql);
			$message_parser->attachment_data = array_merge($message_parser->attachment_data, $this->db->sql_fetchrowset($result));
			$this->db->sql_freeresult($result);
		}

		if (!in_array($action, array('quote', 'edit', 'delete', 'forward')))
		{
			$enable_sig		= false;
			$enable_smilies	= ($this->config['allow_smilies'] && $this->auth->acl_get('u_pm_smilies') && $this->user->optionget('smilies'));
			$enable_bbcode	= ($this->config['allow_bbcode'] && $this->auth->acl_get('u_pm_bbcode') && $this->user->optionget('bbcode'));
			$enable_urls	= true;
		}

		$drafts = false;

		// User own some drafts?
		if ($this->auth->acl_get('u_savedrafts') && $action != 'delete')
		{
			$sql = 'SELECT draft_id
				FROM ' . DRAFTS_TABLE . '
				WHERE forum_id = 0
					AND topic_id = 0
					AND user_id = ' . $this->user->data['user_id'] .
					(($draft_id) ? " AND draft_id <> $draft_id" : '');
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$drafts = true;
			}
		}

		if ($action == 'edit')
		{
			$message_parser->bbcode_uid = $bbcode_uid;
		}

		$bbcode_status	= ($this->config['allow_bbcode'] && $this->config['auth_bbcode_pm'] && $this->auth->acl_get('u_pm_bbcode')) ? true : false;
		$smilies_status	= ($this->config['allow_smilies'] && $this->auth->acl_get('u_pm_smilies')) ? true : false;
		$img_status		= ($this->config['auth_img_pm'] && $this->auth->acl_get('u_pm_img')) ? true : false;
		$flash_status	= false;
		$url_status		= ($this->config['allow_post_links']) ? true : false;

		// Save Draft
		if ($save && $this->auth->acl_get('u_savedrafts'))
		{
			$subject = $this->request->variable('subject', '', true);
			$subject = (!$subject && $action != 'post') ? $this->language->lang('NEW_MESSAGE') : $subject;
			$message = $this->request->variable('message', '', true);

			if ($subject && $message)
			{
				if (\confirm_box(true))
				{
					$sql = 'INSERT INTO ' . DRAFTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
						'user_id'		=> $this->user->data['user_id'],
						'topic_id'		=> 0,
						'forum_id'		=> 0,
						'save_time'		=> $current_time,
						'draft_subject'	=> $subject,
						'draft_message'	=> $message
						)
					);
					$this->db->sql_query($sql);
	
					$redirect_url = $this->helper->route('phpbb_privatemessage_index');
					return $this->helper->message($this->language->lang('DRAFT_SAVED') . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $redirect_url . '">', '</a>'));
				}
				else
				{
					$s_hidden_fields = \build_hidden_fields(array(
						'mode'		=> $mode,
						'action'	=> $action,
						'save'		=> true,
						'subject'	=> $subject,
						'message'	=> $message,
						'u'			=> $to_user_id,
						'g'			=> $to_group_id,
						'p'			=> $msg_id)
					);
					$s_hidden_fields .= $this->build_address_field($address_list);

					\confirm_box(false, 'SAVE_DRAFT', $s_hidden_fields);
				}
			}
			else
			{
				if (\utf8_clean_string($subject) === '')
				{
					$error[] = $this->language->lang('EMPTY_MESSAGE_SUBJECT');
				}

				if (\utf8_clean_string($message) === '')
				{
					$error[] = $this->language->lang('TOO_FEW_CHARS');
				}
			}

			unset($subject, $message);
		}

		// Load Draft
		if ($draft_id && $this->auth->acl_get('u_savedrafts'))
		{
			$sql = 'SELECT draft_subject, draft_message
				FROM ' . DRAFTS_TABLE . "
				WHERE draft_id = $draft_id
					AND topic_id = 0
					AND forum_id = 0
					AND user_id = " . $this->user->data['user_id'];
			$result = $this->db->sql_query_limit($sql, 1);

			if ($row = $this->db->sql_fetchrow($result))
			{
				$message_parser->message = $row['draft_message'];
				$message_subject = $row['draft_subject'];

				$this->template->assign_var('S_DRAFT_LOADED', true);
			}
			else
			{
				$draft_id = 0;
			}
			$this->db->sql_freeresult($result);
		}

		// Load Drafts
		if ($load && $drafts)
		{
			if (!function_exists('load_drafts'))
			{
				include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
			}

			\load_drafts(0, 0, $id, $action, $msg_id);
		}

		if ($submit || $preview || $refresh)
		{
			if (($submit || $preview) && !\check_form_key('ucp_pm_compose'))
			{
				$error[] = $this->language->lang('FORM_INVALID');
			}
			$subject = $this->request->variable('subject', '', true);
			$message_parser->message = $this->request->variable('message', '', true);

			$enable_bbcode 		= (!$bbcode_status) ? false : true;
			$enable_smilies		= (!$smilies_status) ? false : true;
			$enable_urls 		= 1;
			$enable_sig			= false;

			/**
			* Modify private message
			*
			* @event core.ucp_pm_compose_modify_parse_before
			* @var	bool	enable_bbcode		Whether or not bbcode is enabled
			* @var	bool	enable_smilies		Whether or not smilies are enabled
			* @var	bool	enable_urls			Whether or not urls are enabled
			* @var	bool	enable_sig			Whether or not signature is enabled
			* @var	string	subject				PM subject text
			* @var	object	message_parser		The message parser object
			* @var	bool	submit				Whether or not the form has been sumitted
			* @var	bool	preview				Whether or not the signature is being previewed
			* @var	array	error				Any error strings
			* @since 3.1.10-RC1
			*/
			$vars = array(
				'enable_bbcode',
				'enable_smilies',
				'enable_urls',
				'enable_sig',
				'subject',
				'message_parser',
				'submit',
				'preview',
				'error',
			);
			extract($this->dispatcher->trigger_event('core.ucp_pm_compose_modify_parse_before', compact($vars)));

			// Parse Attachments - before checksum is calculated
			$message_parser->parse_attachments('fileupload', $action, 0, $submit, $preview, $refresh, true);

			if (count($message_parser->warn_msg) && !($remove_u || $remove_g || $add_to || $add_bcc))
			{
				$error[] = implode('<br />', $message_parser->warn_msg);
				$message_parser->warn_msg = array();
			}

			// Parse message
			$message_parser->parse($enable_bbcode, ($this->config['allow_post_links']) ? $enable_urls : false, $enable_smilies, $img_status, $flash_status, true, $this->config['allow_post_links']);

			// On a refresh we do not care about message parsing errors
			if (count($message_parser->warn_msg) && !$refresh)
			{
				$error[] = implode('<br />', $message_parser->warn_msg);
			}

			if ($action != 'edit' && !$preview && !$refresh && $this->config['flood_interval'] && !$this->auth->acl_get('u_ignoreflood'))
			{
				// Flood check
				$last_post_time = $this->user->data['user_lastpost_time'];

				if ($last_post_time)
				{
					if ($last_post_time && ($current_time - $last_post_time) < intval($this->config['flood_interval']))
					{
						$error[] = $this->language->lang('FLOOD_ERROR');
					}
				}
			}

			// Subject defined
			if ($submit)
			{
				if ($action != 'reply' && \utf8_clean_string($subject) === '')
				{
					$error[] = $this->language->lang('EMPTY_MESSAGE_SUBJECT');
				}

				if (!count($address_list))
				{
					// TODO: inspect this, address_list should be rebuit only when replying
					$address_list = $this->rebuild_header(array('to' => $post['to_address']));
					//$error[] = $user->lang['NO_RECIPIENT'];
				}
			}

			// Store message, sync counters
			if (!count($error) && $submit)
			{
				$pm_data = array(
					'msg_id'				=> (int) $msg_id,
					'from_user_id'			=> $this->user->data['user_id'],
					'from_user_ip'			=> $this->user->ip,
					'from_username'			=> $this->user->data['username'],
					'reply_from_root_level'	=> (isset($post['root_level'])) ? (int) $post['root_level'] : 0,
					'reply_from_msg_id'		=> (int) $msg_id,
					'icon_id'				=> (int) $icon_id,
					'enable_sig'			=> (bool) $enable_sig,
					'enable_bbcode'			=> (bool) $enable_bbcode,
					'enable_smilies'		=> (bool) $enable_smilies,
					'enable_urls'			=> (bool) $enable_urls,
					'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
					'bbcode_uid'			=> $message_parser->bbcode_uid,
					'message'				=> $message_parser->message,
					'attachment_data'		=> $message_parser->attachment_data,
					'filename_data'			=> $message_parser->filename_data,
					'address_list'			=> $address_list
				);

				if (!function_exists('submit_pm'))
				{
					includes($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
				}

				// ((!$message_subject) ? $subject : $message_subject)
				$msg_id = \submit_pm($action, $subject, $pm_data);

				$return_message_url = $this->helper->route('phpbb_privatemessage_conversation', array('id' => $msg_id, '#' => 'pm-msg-' . $msg_id));

				$save_message = ($action === 'edit') ? $this->language->lang('MESSAGE_EDITED') : $this->language->lang('MESSAGE_STORED');
				$message = $save_message . '<br /><br />' . $this->language->lang('VIEW_PRIVATE_MESSAGE', '<a href="' . $return_message_url . '">', '</a>');

				return $this->helper->message($return_message_url);
			}

			$message_subject = $subject;
		}

		if (!function_exists('parse_attachments'))
		{
			include($this->root_path . 'includes/functions_content.' . $this->php_ext);
		}

		// Preview
		if (!count($error) && $preview)
		{
			$preview_message = $message_parser->format_display($enable_bbcode, $enable_urls, $enable_smilies, false);

			$preview_signature = '';
			$preview_signature_uid = $this->user->data['user_sig_bbcode_uid'];
			$preview_signature_bitfield = $this->user->data['user_sig_bbcode_bitfield'];

			// Attachment Preview
			if (count($message_parser->attachment_data))
			{
				$this->template->assign_var('S_HAS_ATTACHMENTS', true);

				$update_count = array();
				$attachment_data = $message_parser->attachment_data;

				\parse_attachments(false, $preview_message, $attachment_data, $update_count, true);

				foreach ($attachment_data as $i => $attachment)
				{
					$this->template->assign_block_vars('attachment', array(
						'DISPLAY_ATTACHMENT'	=> $attachment)
					);
				}
				unset($attachment_data);
			}

			$preview_subject = \censor_text($subject);

			if (!count($error))
			{
				$this->template->assign_vars(array(
					'PREVIEW_SUBJECT'		=> $preview_subject,
					'PREVIEW_MESSAGE'		=> $preview_message,
					'PREVIEW_SIGNATURE'		=> $preview_signature,

					'S_DISPLAY_PREVIEW'		=> true)
				);
			}
			unset($message_text);
		}

		// Decode text for message display
		$bbcode_uid = (($action == 'quote' || $action == 'forward') && !$preview && !$refresh && (!count($error) || (count($error) && !$submit))) ? $bbcode_uid : $message_parser->bbcode_uid;

		$message_parser->decode_message($bbcode_uid);

		if (($action == 'quote' || $action == 'quotepost') && !$preview && !$refresh && !$submit)
		{
			if ($action == 'quotepost')
			{
				$post_id = $this->request->variable('p', 0);
				if ($this->config['allow_post_links'])
				{
					$message_link = "[url=" . generate_board_url() . "/viewtopic.{$this->php_ext}?p={$post_id}#p{$post_id}]{$this->language->lang('SUBJECT')}{$this->language->lang('COLON')} {$message_subject}[/url]\n\n";
				}
				else
				{
					$message_link = $this->language->lang('SUBJECT') . $this->language->lang('COLON') . ' ' . $message_subject . " (" . generate_board_url() . "/viewtopic.{$this->php_ext}?p={$post_id}#p{$post_id})\n\n";
				}
			}
			else
			{
				$message_link = '';
			}
			$quote_attributes = array(
				'author'  => $quote_username,
				'time'    => $post['message_time'],
				'user_id' => $post['author_id'],
			);
			if ($action === 'quotepost')
			{
				$quote_attributes['post_id'] = $post['msg_id'];
			}
			$quote_text = $this->text_formatter_utils->generate_quote(
				\censor_text($message_parser->message),
				$quote_attributes
			);
			$message_parser->message = $message_link . $quote_text . "\n\n";
		}

		if (($action == 'reply' || $action == 'quote' || $action == 'quotepost') && !$preview && !$refresh)
		{
			$message_subject = ((!preg_match('/^Re:/', $message_subject)) ? 'Re: ' : '') . \censor_text($message_subject);
		}

		if ($action == 'forward' && !$preview && !$refresh && !$submit)
		{
			$fwd_to_field = $this->write_pm_addresses(array('to' => $post['to_address']), 0, true);

			if ($this->config['allow_post_links'])
			{
				$quote_username_text = '[url=' . \generate_board_url() . "/memberlist.{$this->php_ext}?mode=viewprofile&amp;u={$post['author_id']}]{$quote_username}[/url]";
			}
			else
			{
				$quote_username_text = $quote_username . ' (' . \generate_board_url() . "/memberlist.{$this->php_ext}?mode=viewprofile&amp;u={$post['author_id']})";
			}

			$forward_text = array();
			$forward_text[] = $this->language->lang('FWD_ORIGINAL_MESSAGE');
			$forward_text[] = $this->language->lang('FWD_SUBJECT', \censor_text($message_subject));
			$forward_text[] = $this->language->lang('FWD_DATE', $this->user->format_date($message_time, false, true));
			$forward_text[] = $this->language->lang('FWD_FROM', $quote_username_text);
			$forward_text[] = $this->language->lang('FWD_TO', implode($this->language->lang('COMMA_SEPARATOR'), $fwd_to_field['to']));

			$quote_text = $this->text_formatter_utils->generate_quote(
				\censor_text($message_parser->message),
				array('author' => $quote_username)
			);
			$message_parser->message = implode("\n", $forward_text) . "\n\n" . $quote_text;
			$message_subject = ((!preg_match('/^Fwd:/', $message_subject)) ? 'Fwd: ' : '') . \censor_text($message_subject);
		}

		$attachment_data = $message_parser->attachment_data;
		$filename_data = $message_parser->filename_data;
		$message_text = $message_parser->message;

		// MAIN PM PAGE BEGINS HERE

		// Generate inline attachment select box
		if (!function_exists('posting_gen_inline_attachments'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
		\posting_gen_inline_attachments($attachment_data);

		// Build address list for display
		// array('u' => array($author_id => 'to'));
		if (count($address_list))
		{
			// Get Usernames and Group Names
			$result = array();
			if (!empty($address_list['u']))
			{
				$sql = 'SELECT user_id as id, username as name, user_colour as colour
					FROM ' . USERS_TABLE . '
					WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', array_keys($address_list['u']))) . '
					ORDER BY username_clean ASC';
				$result['u'] = $this->db->sql_query($sql);
			}

			if (!empty($address_list['g']))
			{
				$sql = 'SELECT g.group_id AS id, g.group_name AS name, g.group_colour AS colour, g.group_type
					FROM ' . GROUPS_TABLE . ' g';

				if (!$this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
				{
					$sql .= ' LEFT JOIN ' . USER_GROUP_TABLE . ' ug
						ON (
							g.group_id = ug.group_id
							AND ug.user_id = ' . $this->user->data['user_id'] . '
							AND ug.user_pending = 0
						)
						WHERE (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $this->user->data['user_id'] . ')';
				}

				$sql .= ($this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? ' WHERE ' : ' AND ';

				$sql .= 'g.group_receive_pm = 1
					AND ' . $this->db->sql_in_set('g.group_id', array_map('intval', array_keys($address_list['g']))) . '
					ORDER BY g.group_name ASC';

				$result['g'] = $this->db->sql_query($sql);
			}

			$u = $g = array();
			$_types = array('u', 'g');
			foreach ($_types as $type)
			{
				if (isset($result[$type]) && $result[$type])
				{
					while ($row = $this->db->sql_fetchrow($result[$type]))
					{
						if ($type == 'g')
						{
							$row['name'] = $this->group_helper->get_name($row['name']);
						}

						${$type}[$row['id']] = array('name' => $row['name'], 'colour' => $row['colour']);
					}
					$this->db->sql_freeresult($result[$type]);
				}
			}

			// Now Build the address list
			foreach ($address_list as $type => $adr_ary)
			{
				foreach ($adr_ary as $id => $field)
				{
					if (!isset(${$type}[$id]))
					{
						unset($address_list[$type][$id]);
						continue;
					}

					$field = ($field == 'to') ? 'to' : 'bcc';
					$type = ($type == 'u') ? 'u' : 'g';
					$id = (int) $id;

					$tpl_ary = array(
						'IS_GROUP'	=> ($type == 'g') ? true : false,
						'IS_USER'	=> ($type == 'u') ? true : false,
						'UG_ID'		=> $id,
						'NAME'		=> ${$type}[$id]['name'],
						'COLOUR'	=> (${$type}[$id]['colour']) ? '#' . ${$type}[$id]['colour'] : '',
						'TYPE'		=> $type,
					);

					if ($type == 'u')
					{
						$tpl_ary = array_merge($tpl_ary, array(
							'U_VIEW'		=> \get_username_string('profile', $id, ${$type}[$id]['name'], ${$type}[$id]['colour']),
							'NAME_FULL'		=> \get_username_string('full', $id, ${$type}[$id]['name'], ${$type}[$id]['colour']),
						));
					}
					else
					{
						$tpl_ary = array_merge($tpl_ary, array(
							'U_VIEW'		=> append_sid("{$this->root_path}memberlist.{$this->php_ext}", 'mode=group&amp;g=' . $id),
						));
					}

					$this->template->assign_block_vars($field . '_recipient', $tpl_ary);
				}
			}
		}

		// Build hidden address list
		$s_hidden_address_field = $this->build_address_field($address_list);

		$bbcode_checked		= (isset($enable_bbcode)) ? !$enable_bbcode : (($this->config['allow_bbcode'] && $this->auth->acl_get('u_pm_bbcode')) ? !$this->user->optionget('bbcode') : 1);
		$smilies_checked	= (isset($enable_smilies)) ? !$enable_smilies : (($this->config['allow_smilies'] && $this->auth->acl_get('u_pm_smilies')) ? !$this->user->optionget('smilies') : 1);
		$urls_checked		= (isset($enable_urls)) ? !$enable_urls : 0;
		$sig_checked		= $enable_sig;

		switch ($action)
		{
			case 'post':
				$page_title = $this->language->lang('POST_NEW_PM');
			break;
			
			case 'quote':
				$page_title = $this->language->lang('POST_QUOTE_PM');
			break;

			case 'quotepost':
				$page_title = $this->language->lang('POST_PM_POST');
			break;

			case 'reply':
				$page_title = $this->language->lang('POST_REPLY_PM');
			break;

			case 'edit':
				$page_title = $this->language->lang('POST_EDIT_PM');
			break;

			case 'forward':
				$page_title = $this->language->lang('POST_FORWARD_PM');
			break;

			default:
				return $this->helper->message('NO_ACTION_MODE');
			break;
		}

		$s_hidden_fields = '<input type="hidden" name="lastclick" value="' . $current_time . '" />';
		$s_hidden_fields .= (isset($check_value)) ? '<input type="hidden" name="status_switch" value="' . $check_value . '" />' : '';
		$s_hidden_fields .= ($draft_id || isset($_REQUEST['draft_loaded'])) ? '<input type="hidden" name="draft_loaded" value="' . ((isset($_REQUEST['draft_loaded'])) ? $this->request->variable('draft_loaded', 0) : $draft_id) . '" />' : '';

		$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || !$this->config['allow_pm_attach'] || !$this->auth->acl_get('u_pm_attach')) ? '' : ' enctype="multipart/form-data"';

		// Start assigning vars for main posting page ...
		$this->template->assign_vars(array(
			'L_POST_A'					=> $page_title,
			'L_ICON'					=> $this->user->lang['PM_ICON'],
			'L_MESSAGE_BODY_EXPLAIN'	=> $this->language->lang('MESSAGE_BODY_EXPLAIN', (int) $this->config['max_post_chars']),

			'SUBJECT'				=> (isset($message_subject)) ? $message_subject : '',
			'MESSAGE'				=> $message_text,
			'BBCODE_STATUS'			=> $this->language->lang(($bbcode_status ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '<a href="' . $this->helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
			'IMG_STATUS'			=> ($img_status) ? $this->language->lang('IMAGES_ARE_ON') : $this->language->lang('IMAGES_ARE_OFF'),
			'FLASH_STATUS'			=> ($flash_status) ? $this->language->lang('FLASH_IS_ON') : $this->language->lang('FLASH_IS_OFF'),
			'SMILIES_STATUS'		=> ($smilies_status) ? $this->language->lang('SMILIES_ARE_ON') : $this->language->lang('SMILIES_ARE_OFF'),
			'URL_STATUS'			=> ($url_status) ? $this->language->lang('URL_IS_ON') : $this->language->lang('URL_IS_OFF'),
			'MAX_FONT_SIZE'			=> (int) $this->config['max_post_font_size'],
			'MINI_POST_IMG'			=> $this->user->img('icon_post_target', $this->language->lang('PM')),
			'ERROR'					=> (count($error)) ? implode('<br />', $error) : '',
			'MAX_RECIPIENTS'		=> ($this->config['allow_mass_pm'] && ($this->auth->acl_get('u_masspm') || $this->auth->acl_get('u_masspm_group'))) ? $max_recipients : 0,

			'S_COMPOSE_PM'			=> true,
			'S_EDIT_POST'			=> ($action == 'edit'),
			'S_BBCODE_ALLOWED'		=> ($bbcode_status) ? 1 : 0,
			'S_BBCODE_CHECKED'		=> ($bbcode_checked) ? ' checked="checked"' : '',
			'S_SMILIES_ALLOWED'		=> $smilies_status,
			'S_SMILIES_CHECKED'		=> ($smilies_checked) ? ' checked="checked"' : '',
			'S_SIG_ALLOWED'			=> false,
			'S_SIGNATURE_CHECKED'	=> ($sig_checked) ? ' checked="checked"' : '',
			'S_LINKS_ALLOWED'		=> $url_status,
			'S_MAGIC_URL_CHECKED'	=> ($urls_checked) ? ' checked="checked"' : '',
			'S_SAVE_ALLOWED'		=> ($this->auth->acl_get('u_savedrafts') && $action != 'edit') ? true : false,
			'S_HAS_DRAFTS'			=> ($this->auth->acl_get('u_savedrafts') && $drafts),
			'S_FORM_ENCTYPE'		=> $form_enctype,
			'S_ATTACH_DATA'			=> \json_encode($message_parser->attachment_data),

			'S_BBCODE_IMG'			=> $img_status,
			'S_BBCODE_FLASH'		=> $flash_status,
			'S_BBCODE_QUOTE'		=> true,
			'S_BBCODE_URL'			=> $url_status,

			'S_POST_ACTION'				=> $s_action,
			'S_HIDDEN_ADDRESS_FIELD'	=> $s_hidden_address_field,
			'S_HIDDEN_FIELDS'			=> $s_hidden_fields,

			'S_CLOSE_PROGRESS_WINDOW'	=> isset($_POST['add_file']),
			'U_PROGRESS_BAR'			=> append_sid("{$this->root_path}posting.{$this->php_ext}", 'f=0&amp;mode=popup'),
			'UA_PROGRESS_BAR'			=> addslashes(append_sid("{$this->root_path}posting.{$this->php_ext}", 'f=0&amp;mode=popup')),
		));

		if (!function_exists('display_custom_bbcodes'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
		// Build custom bbcodes array
		\display_custom_bbcodes();

		// Show attachment box for adding attachments if true
		$allowed = ($this->auth->acl_get('u_pm_attach') && $this->config['allow_pm_attach'] && $form_enctype);

		if ($allowed)
		{
			$max_files = ($this->auth->acl_gets('a_', 'm_')) ? 0 : (int) $this->config['max_attachments_pm'];

			global $cache; // TODO: find out what it is and inject it using DI
			$this->plupload->configure($cache, $this->template, $s_action, false, $max_files);
		}

		if (!function_exists('posting_gen_attachment_entry'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
		// Attachment entry
		\posting_gen_attachment_entry($attachment_data, $filename_data, $allowed);

		return $this->helper->render('posting_pm_layout.html', '');
	}

	public function rebuild_header($check_ary)
	{
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
	
			$_types = array('u', 'g');
			foreach ($_types as $type)
			{
				if (count(${$type}))
				{
					foreach (${$type} as $id)
					{
						$address[$type][$id] = $check_type;
					}
				}
			}
		}
	
		return $address;
	}

	public function get_max_setting_from_group($setting)
	{
		if ($setting !== 'max_recipients' && $setting !== 'message_limit')
		{
			throw new \InvalidArgumentException('Setting "' . $setting . '" is not supported');
		}
		// Get maximum number of allowed recipients
		$sql = 'SELECT MAX(g.group_' . $setting . ') as max_setting
			FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
			WHERE ug.user_id = ' . (int) $this->user->data['user_id'] . '
				AND ug.user_pending = 0
				AND ug.group_id = g.group_id';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$max_setting = (int) $row['max_setting'];
		return $max_setting;
	}

	public function handle_message_list_actions(&$address_list, &$error, $remove_u, $remove_g, $add_to, $add_bcc, &$refresh, &$submit, &$preview)
	{
		// Delete User [TO/BCC]
		if ($remove_u && $this->request->variable('remove_u', array(0 => '')))
		{
			$remove_user_id = array_keys($this->request->variable('remove_u', array(0 => '')));
	
			if (isset($remove_user_id[0]))
			{
				unset($address_list['u'][(int) $remove_user_id[0]]);
			}
		}
	
		// Delete Group [TO/BCC]
		if ($remove_g && $this->request->variable('remove_g', array(0 => '')))
		{
			$remove_group_id = array_keys($this->request->variable('remove_g', array(0 => '')));
	
			if (isset($remove_group_id[0]))
			{
				unset($address_list['g'][(int) $remove_group_id[0]]);
			}
		}
	
		// Add Selected Groups
		$group_list = $this->request->variable('group_list', array(0));
	
		// Build usernames to add
		$usernames = $this->request->variable('username', '', true);
		$usernames = (empty($usernames)) ? array() : array($usernames);
	
		$username_list = $this->request->variable('username_list', '', true);
		if ($username_list)
		{
			$usernames = array_merge($usernames, explode("\n", $username_list));
		}
	
		// If add to or add bcc not pressed, users could still have usernames listed they want to add...
		if (!$add_to && !$add_bcc && (count($group_list) || count($usernames)))
		{
			$add_to = true;
	
			$refresh = true;
			$submit = false;
	
			// Preview is only true if there was also a message entered
			if ($this->request->variable('message', ''))
			{
				$preview = true;
			}
		}
	
		// Add User/Group [TO]
		if ($add_to || $add_bcc)
		{
			$type = ($add_to) ? 'to' : 'bcc';
	
			if (count($group_list))
			{
				foreach ($group_list as $group_id)
				{
					$address_list['g'][$group_id] = $type;
				}
			}
	
			// User ID's to add...
			$user_id_ary = array();
	
			// Reveal the correct user_ids
			if (count($usernames))
			{
				$user_id_ary = array();
				if (!function_exists('user_get_id_name'))
				{
					include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
				}

				\user_get_id_name($user_id_ary, $usernames, array(USER_NORMAL, USER_FOUNDER, USER_INACTIVE));
	
				// If there are users not existing, we will at least print a notice...
				if (!count($user_id_ary))
				{
					$error[] = $this->language->lang('PM_NO_USERS');
				}
			}
	
			// Add Friends if specified
			$friend_list = array_keys($this->request->variable('add_' . $type, array(0)));
			$user_id_ary = array_merge($user_id_ary, $friend_list);
	
			foreach ($user_id_ary as $user_id)
			{
				if ($user_id == ANONYMOUS)
				{
					continue;
				}
	
				$address_list['u'][$user_id] = $type;
			}
		}
	
		// Check for disallowed recipients
		if (!empty($address_list['u']))
		{
			$can_ignore_allow_pm = $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_');
	
			// Administrator deactivated users check and we need to check their
			//		PM status (do they want to receive PM's?)
			// 		Only check PM status if not a moderator or admin, since they
			//		are allowed to override this user setting
			$sql = 'SELECT user_id, user_allow_pm
				FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', array_keys($address_list['u'])) . '
					AND (
							(user_type = ' . USER_INACTIVE . '
							AND user_inactive_reason = ' . INACTIVE_MANUAL . ')
							' . ($can_ignore_allow_pm ? '' : ' OR user_allow_pm = 0') . '
						)';
	
			$result = $this->db->sql_query($sql);
	
			$removed_no_pm = $removed_no_permission = false;
			while ($row = $this->db->sql_fetchrow($result))
			{
				if (!$can_ignore_allow_pm && !$row['user_allow_pm'])
				{
					$removed_no_pm = true;
				}
				else
				{
					$removed_no_permission = true;
				}
	
				unset($address_list['u'][$row['user_id']]);
			}
			$this->db->sql_freeresult($result);
	
			// print a notice about users not being added who do not want to receive pms
			if ($removed_no_pm)
			{
				$error[] = $this->language->lang('PM_USERS_REMOVED_NO_PM');
			}
	
			// print a notice about users not being added who do not have permission to receive PMs
			if ($removed_no_permission)
			{
				$error[] = $this->language->lang('PM_USERS_REMOVED_NO_PERMISSION');
			}
	
			if (!count(array_keys($address_list['u'])))
			{
				return;
			}
	
			// Check if users have permission to read PMs
			$can_read = $this->auth->acl_get_list(array_keys($address_list['u']), 'u_readpm');
			$can_read = (empty($can_read) || !isset($can_read[0]['u_readpm'])) ? array() : $can_read[0]['u_readpm'];
			$cannot_read_list = array_diff(array_keys($address_list['u']), $can_read);
			if (!empty($cannot_read_list))
			{
				foreach ($cannot_read_list as $cannot_read)
				{
					unset($address_list['u'][$cannot_read]);
				}
	
				$error[] = $this->language->lang('PM_USERS_REMOVED_NO_PERMISSION');
			}
	
			// Check if users are banned
			if (!function_exists('phpbb_get_banned_user_ids'))
			{
				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			}

			$banned_user_list = \phpbb_get_banned_user_ids(array_keys($address_list['u']), false);
			if (!empty($banned_user_list))
			{
				foreach ($banned_user_list as $banned_user)
				{
					unset($address_list['u'][$banned_user]);
				}
	
				$error[] = $this->language->lang('PM_USERS_REMOVED_NO_PERMISSION');
			}
		}
	}

	public function num_recipients($address_list)
	{
		$num_recipients = 0;
	
		foreach ($address_list as $field => $adr_ary)
		{
			$num_recipients += count($adr_ary);
		}
	
		return $num_recipients;
	}

	public function get_recipients($address_list, $num_recipients = 1)
	{
		$recipient = array();
	
		$count = 0;
		foreach ($address_list as $field => $adr_ary)
		{
			foreach ($adr_ary as $id => $type)
			{
				if ($count >= $num_recipients)
				{
					break 2;
				}
				$recipient[$field][$id] = $type;
				$count++;
			}
		}
	
		return $recipient;
	}

	public function write_pm_addresses($check_ary, $author_id, $plaintext = false)
	{
		$addresses = array();
		foreach ($check_ary as $check_type => $address_field)
		{
			if (!is_array($address_field))
			{
				// Split Addresses into users and groups
				preg_match_all('/:?(u|g)_([0-9]+):?/', $address_field, $match);
				$u = $g = array();
				foreach ($match[1] as $id => $type)
				{
					${$type}[] = (int) $match[2][$id];
				}
			}
			else
			{
				$u = $address_field['u'];
				$g = $address_field['g'];
			}
			$address = array();
			if (count($u))
			{
				$sql = 'SELECT user_id, username, user_colour
					FROM ' . USERS_TABLE . '
					WHERE ' . $this->db->sql_in_set('user_id', $u);
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					if ($check_type == 'to' || $author_id == $this->user->data['user_id'] || $row['user_id'] == $this->user->data['user_id'])
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
				$this->db->sql_freeresult($result);
			}
			if (count($g))
			{
				if ($plaintext)
				{
					$sql = 'SELECT group_name, group_type
						FROM ' . GROUPS_TABLE . '
							WHERE ' . $this->db->sql_in_set('group_id', $g);
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						if ($check_type == 'to' || $author_id == $this->user->data['user_id'] || $row['user_id'] == $this->user->data['user_id'])
						{
							$address[] = $this->group_helper->get_name($row['group_name']);
						}
					}
					$this->db->sql_freeresult($result);
				}
				else
				{
					$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type, ug.user_id
						FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
							WHERE ' . $this->db->sql_in_set('g.group_id', $g) . '
							AND g.group_id = ug.group_id
							AND ug.user_pending = 0';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						if (!isset($address['group'][$row['group_id']]))
						{
							if ($check_type == 'to' || $author_id == $this->user->data['user_id'] || $row['user_id'] == $this->user->data['user_id'])
							{
								$row['group_name'] = $this->group_helper->get_name($row['group_name']);
								$address['group'][$row['group_id']] = array('name' => $row['group_name'], 'colour' => $row['group_colour']);
							}
						}
						if (isset($address['user'][$row['user_id']]))
						{
							$address['user'][$row['user_id']]['in_group'] = $row['group_id'];
						}
					}
					$this->db->sql_freeresult($result);
				}
			}
			if (count($address) && !$plaintext)
			{
				$this->template->assign_var('S_' . strtoupper($check_type) . '_RECIPIENT', true);
				foreach ($address as $type => $adr_ary)
				{
					foreach ($adr_ary as $id => $row)
					{
						$tpl_ary = array(
							'IS_GROUP'	=> ($type == 'group') ? true : false,
							'IS_USER'	=> ($type == 'user') ? true : false,
							'UG_ID'		=> $id,
							'NAME'		=> $row['name'],
							'COLOUR'	=> ($row['colour']) ? '#' . $row['colour'] : '',
							'TYPE'		=> $type,
						);
						if ($type == 'user')
						{
							$tpl_ary = array_merge($tpl_ary, array(
								'U_VIEW'		=> \get_username_string('profile', $id, $row['name'], $row['colour']),
								'NAME_FULL'		=> \get_username_string('full', $id, $row['name'], $row['colour']),
							));
						}
						else
						{
							$tpl_ary = array_merge($tpl_ary, array(
								'U_VIEW'		=> append_sid("{$this->root_path}memberlist.{$this->php_ext}", 'mode=group&amp;g=' . $id),
							));
						}
						$this->template->assign_block_vars($check_type . '_recipient', $tpl_ary);
					}
				}
			}
			$addresses[$check_type] = $address;
		}
		return $addresses;
	}

	public function build_address_field($address_list)
	{
		$s_hidden_address_field = '';
		foreach ($address_list as $type => $adr_ary)
		{
			foreach ($adr_ary as $id => $field)
			{
				$s_hidden_address_field .= '<input type="hidden" name="address_list[' . (($type == 'u') ? 'u' : 'g') . '][' . (int) $id . ']" value="' . (($field == 'to') ? 'to' : 'bcc') . '" />';
			}
		}
		return $s_hidden_address_field;
	}
}
