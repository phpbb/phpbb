<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp_pm.php
// STARTED   : Sat Mar 27, 2004
// COPYRIGHT : © 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// TODO:
//
// * Review of post when replying/quoting
// * Handle delete flag (user deletes PM from outbox)
// * Report PM
// * Check Permissions (compose message - to user/group)

/*
	folder=
		(int)		display folder with the id used
		(string)	can be inbox, outbox or sentbox

	Display Unread Messages - mode=unread
	Display Messages (default to inbox) - mode=view_messages
	Display single message - mode=view_messages&action=view_message&p=[msg_id] or &p=[msg_id] (short linkage)

	if the folder id with (&f=[folder_id]) is used when displaying messages, one query will be saved. If it is not used, phpBB needs to grab
	the folder id first in order to display the input boxes and folder names and such things. ;) phpBB always checks this against the database to make
	sure the user is able to view the message.

	Composing Messages (mode=compose):
		To specific user (u=[user_id])
		To specific group (g=[group_id])
		Quoting a post (action=quote&q=1&p=[post_id])
		Quoting a PM (action=quote&p=[msg_id])
		Forwarding a PM (action=forward&p=[msg_id])

*/

class ucp_pm extends module
{
	function ucp_pm($id, $mode)
	{
		global $user, $template, $phpbb_root_path, $auth, $phpEx, $db, $SID, $config;
		
		if ($user->data['user_id'] == ANONYMOUS)
		{
			trigger_error('NO_PM');
		}

		// Is PM disabled?
		if (!$config['allow_privmsg'])
		{
			trigger_error('PM_DISABLED');
		}

		$user->add_lang('posting');
		$template->assign_var('S_PRIVMSGS', true);

		$folder_specified = request_var('folder', '');
		if (!in_array($folder_specified, array('inbox', 'outbox', 'sentbox')))
		{
			$folder_specified = (int) $folder_specified;
		}
		else
		{
			$folder_specified = ($folder_specified == 'inbox') ? PRIVMSGS_INBOX : (($folder_specified == 'outbox') ? PRIVMSGS_OUTBOX : PRIVMSGS_SENTBOX);
		}

		if (!$folder_specified)
		{
			$mode = (!$mode) ? request_var('mode', 'view_messages') : $mode;
		}
		else
		{
			$mode = 'view_messages';
		}

		include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		
		$tpl_file = 'ucp_pm_' . $mode . '.html';
		switch ($mode)
		{
			// New private messages popup
			case 'popup':
			
				$l_new_message = '';
				if ($user->data['user_id'] != ANONYMOUS)
				{
					if ($user->data['user_new_privmsg'])
					{
						$l_new_message = ($user->data['user_new_privmsg'] == 1 ) ? $user->lang['YOU_NEW_PM'] : $user->lang['YOU_NEW_PMS'];
					}
					else
					{
						$l_new_message = $user->lang['YOU_NO_NEW_PM'];
					}

					$l_new_message .= '<br /><br />' . sprintf($user->lang['CLICK_VIEW_PRIVMSG'], '<a href="' . $phpbb_root_path . 'ucp.' . $phpEx . $SID . '&amp;i=pm&amp;folder=inbox" onclick="jump_to_inbox();return false;" target="_new">', '</a>');
				}
				else
				{
					$l_new_message = $user->lang['LOGIN_CHECK_PM'];
				}

				$template->assign_vars(array(
					'MESSAGE'	=> $l_new_message)
				);

				break;
			
			// Compose message
			case 'compose':
				$action = request_var('action', 'post');
	
				if (!$auth->acl_get('u_sendpm'))
				{
					trigger_error('NOT_AUTHORIZED');
				}

				include($phpbb_root_path . 'includes/ucp/ucp_pm_compose.'.$phpEx);
				compose_pm($id, $mode, $action);
			
				$tpl_file = 'posting_body.html';
				break;
			
			case 'options':
				include($phpbb_root_path . 'includes/ucp/ucp_pm_options.'.$phpEx);
				message_options($id, $mode, $global_privmsgs_rules, $global_rule_conditions);
				break;

			case 'drafts':
				include($phpbb_root_path . 'includes/ucp/ucp_main.'.$phpEx);
				$module = new ucp_main($id, $mode);
				unset($module);
				exit;
				break;

			case 'unread':
			case 'view_messages':
				if ($folder_specified)
				{
					$folder_id = $folder_specified;
					$action = 'view_folder';
				}
				else
				{
					$folder_id = request_var('f', PRIVMSGS_NO_BOX);
					$action = request_var('action', 'view_folder');
				}

				$msg_id = request_var('p', 0);
				$view	= request_var('view', '');
				
				if ($msg_id && $action == 'view_folder')
				{
					$action = 'view_message';
				}

				if (!$auth->acl_get('u_readpm'))
				{
					trigger_error('NOT_AUTHORIZED');
				}

				// First Handle Mark actions and moving messages

				// Move PM
				if (isset($_REQUEST['move_pm']))
				{
					$message_limit = (!$user->data['group_message_limit']) ? $config['pm_max_msgs'] : $user->data['group_message_limit'];

					if (move_pm($user->data['user_id'], $message_limit))
					{
						// Return to folder view if single message moved
						if ($action == 'view_message')
						{
							$msg_id		= 0;
							$folder_id	= request_var('cur_folder_id', PRIVMSGS_NO_BOX);
							$action		= 'view_folder';
						}
					}
				}

				// Message Mark Options
				if (isset($_REQUEST['submit_mark']))
				{
					handle_mark_actions($user->data['user_id'], request_var('mark_option', ''));
				}

				// If new messages arrived, place them into the appropiate folder
				$num_not_moved = 0;
				if ($user->data['user_new_privmsg'] && $action == 'view_folder')
				{
					place_pm_into_folder($global_privmsgs_rules, request_var('release', 0));
					$num_not_moved = $user->data['user_new_privmsg'];
				}
		
				if (!$msg_id && $folder_id == PRIVMSGS_NO_BOX && $mode != 'unread')
				{
					$folder_id = PRIVMSGS_INBOX;
				}
				else if ($msg_id && $folder_id == PRIVMSGS_NO_BOX)
				{
					$sql = 'SELECT folder_id
						FROM ' . PRIVMSGS_TO_TABLE . "
						WHERE msg_id = $msg_id
							AND user_id = " . $user->data['user_id'];
					$result = $db->sql_query_limit($sql, 1);
					if (!($row = $db->sql_fetchrow($result)))
					{
						trigger_error('MESSAGE_NO_LONGER_AVAILABLE');
					}					
					$folder_id = (int) $row['folder_id'];
				}
			
				$message_row = array();
				if ($mode == 'view_messages' && $action == 'view_message' && $msg_id)
				{
					// Get Message user want to see
					
					if ($view == 'next' || $view == 'previous')
					{
						$sql_condition = ($view == 'next') ? '>' : '<';
						$sql_ordering = ($view == 'next') ? 'ASC' : 'DESC';

						$sql = 'SELECT t.msg_id
							FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . PRIVMSGS_TABLE . " p2
							WHERE p2.msg_id = $msg_id
								AND t.folder_id = $folder_id
								AND t.user_id = " . $user->data['user_id'] . "
								AND t.msg_id = p.msg_id
								AND p.message_time $sql_condition p2.message_time
							ORDER BY p.message_time $sql_ordering";
						$result = $db->sql_query_limit($sql, 1);

						if (!($row = $db->sql_fetchrow($result)))
						{
							$message = ($view == 'next') ? 'NO_NEWER_PM' : 'NO_OLDER_PM';
							trigger_error($message);
						}
						else
						{
							$msg_id = $row['msg_id'];
						}
					}
	
					$sql = 'SELECT t.*, p.*, u.*
						FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . ' u
						WHERE t.user_id = ' . $user->data['user_id'] . "
							AND p.author_id = u.user_id
							AND t.folder_id = $folder_id
							AND t.msg_id = p.msg_id
							AND p.msg_id = $msg_id";
					$result = $db->sql_query_limit($sql, 1);

					if (!($message_row = $db->sql_fetchrow($result)))
					{
						trigger_error('MESSAGE_NO_LONGER_AVAILABLE');
					}

					// Update unread status
					update_unread_status($message_row['unread'], $message_row['msg_id'], $user->data['user_id'], $folder_id);
				}
			
				$unread_pm = array();
				if ($user->data['user_unread_privmsg'])
				{
					$unread_pm = get_unread_pm($user->data['user_id']);
				}
				$folder = array();
			
				if ($mode == 'unread')
				{
					$folder['unread'] = array('folder_name' => $user->lang['UNREAD_MESSAGES']);
				}
				get_folder($user->data['user_id'], $folder);

				$s_folder_options = $s_to_folder_options = '';
				foreach ($folder as $f_id => $folder_ary)
				{
					$unread = ((isset($unread_pm[$f_id]) || ($f_id == PRIVMSGS_OUTBOX && $folder_ary['num_messages'])) ? ' [' . (($f_id == PRIVMSGS_OUTBOX) ? $folder_ary['num_messages'] : $unread_pm[$f_id]) . ']' : '');
					
					$option = '<option' . ((!in_array($f_id, array(PRIVMSGS_INBOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX))) ? ' class="blue"' : '') . ' value="' . $f_id . '"' . ((($f_id == $folder_id && $mode != 'unread') || ($f_id === 'unread' && $mode == 'unread')) ? ' selected="selected"' : '') . '>' . $folder_ary['folder_name'] . $unread . '</option>';

					$s_to_folder_options .= ($f_id != PRIVMSGS_OUTBOX && $f_id != PRIVMSGS_SENTBOX) ? $option : '';
					$s_folder_options .= $option;
				}

				clean_sentbox($folder[PRIVMSGS_SENTBOX]['num_messages']);
	
				// Header for message view - folder and so on
				$folder_status = get_folder_status($folder_id, $folder);
				$url = "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id";

				$template->assign_vars(array(
					'CUR_FOLDER_ID'			=> $folder_id,
					'CUR_FOLDER_NAME'		=> $folder_status['folder_name'],
					'NUM_NOT_MOVED'			=> $num_not_moved,
					'RELEASE_MESSAGE_INFO'	=> sprintf($user->lang['RELEASE_MESSAGES'], '<a href="' . $url . '&amp;folder=' . $folder_id . '&amp;release=1">', '</a>'),
					'NOT_MOVED_MESSAGES'	=> ($num_not_moved == 1) ? $user->lang['NOT_MOVED_MESSAGE'] : sprintf($user->lang['NOT_MOVED_MESSAGES'], $num_not_moved),

					'S_FOLDER_OPTIONS'		=> $s_folder_options,
					'S_TO_FOLDER_OPTIONS'	=> $s_to_folder_options,
					'S_FOLDER_ACTION'		=> "$url&amp;mode=view_messages&amp;action=view_folder",
					'S_PM_ACTION'			=> "$url&amp;mode=$mode&amp;action=$action",
					
					'U_INBOX'				=> ($folder_id != PRIVMSGS_INBOX) ? "$url&amp;folder=inbox" : '',
					'U_OUTBOX'				=> ($folder_id != PRIVMSGS_OUTBOX) ? "$url&amp;folder=outbox" : '',
					'U_SENTBOX'				=> ($folder_id != PRIVMSGS_SENTBOX) ? "$url&amp;folder=sentbox" : '',
					'U_CREATE_FOLDER'		=> "$url&amp;mode=options",

					'FOLDER_STATUS'			=> $folder_status['message'],
					'FOLDER_MAX_MESSAGES'	=> $folder_status['max'],
					'FOLDER_CUR_MESSAGES'	=> $folder_status['cur'],
					'FOLDER_REMAINING_MESSAGES'	=> $folder_status['remaining'],
					'FOLDER_PERCENT'		=> $folder_status['percent'])
				);
			
				if ($mode == 'unread' || $action == 'view_folder')
				{
					include($phpbb_root_path . 'includes/ucp/ucp_pm_viewfolder.'.$phpEx);
					view_folder($id, $mode, $folder_id, $folder, (($mode == 'unread') ? 'unread' : 'folder'));

					$tpl_file = 'ucp_pm_viewfolder.html';
				}
				else if ($action == 'view_message')
				{
					$template->assign_vars(array(
						'S_VIEW_MESSAGE'=> true,
						'MSG_ID'		=> $msg_id)
					);
				
					include($phpbb_root_path . 'includes/ucp/ucp_pm_viewmessage.'.$phpEx);
					view_message($id, $mode, $folder_id, $msg_id, $folder, $message_row);

					$tpl_file = ($view == 'print') ? 'ucp_pm_viewmessage_print.html' : 'ucp_pm_viewmessage.html';
				}
				
				break;

			default:
				trigger_error('NOT_AUTHORIZED');
		}

		$template->assign_vars(array( 
			'L_TITLE'			=> $user->lang['UCP_PM_' . strtoupper($mode)],
			'S_UCP_ACTION'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode" . ((isset($action)) ? "&amp;action=$action" : ''))
		);

		$this->display($user->lang['UCP_PM'], $tpl_file);
	}
}

?>