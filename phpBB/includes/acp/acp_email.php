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
* @package acp
*/
class acp_email
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;

		$user->add_lang('acp/email');
		$this->tpl_name = 'acp_email';
		$this->page_title = 'ACP_MASS_EMAIL';

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		// Set some vars
		$submit = (isset($_POST['submit'])) ? true : false;
		$error = array();

		$usernames = request_var('usernames', '');
		$group_id = request_var('g', 0);

		// Do the job ...
		if ($submit)
		{
			// Error checking needs to go here ... if no subject and/or no message then skip 
			// over the send and return to the form
			$subject		= html_entity_decode(request_var('subject', '', true));
			$message		= html_entity_decode(request_var('message', '', true));
			$use_queue		= (isset($_POST['send_immediatly'])) ? false : true;
			$priority		= request_var('mail_priority_flag', MAIL_NORMAL_PRIORITY);

			if (!$subject)
			{
				$error[] = $user->lang['NO_EMAIL_SUBJECT'];
			}

			if (!$message)
			{
				$error[] = $user->lang['NO_EMAIL_MESSAGE'];
			}

			if (!sizeof($error))	
			{
				if ($usernames)
				{
					$usernames = implode(', ', preg_replace('#^[\s]*?(.*?)[\s]*?$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", explode("\n", $usernames)));

					$sql = 'SELECT username, user_email, user_jabber, user_notify_type, user_lang 
						FROM ' . USERS_TABLE . " 
						WHERE username IN ($usernames)
							AND user_allow_massemail = 1
						ORDER BY user_lang, user_notify_type"; // , SUBSTRING(user_email FROM INSTR(user_email, '@'))
				}
				else
				{
					if ($group_id)
					{
						$sql = 'SELECT u.user_email, u.username, u.user_lang, u.user_jabber, u.user_notify_type 
							FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug 
							WHERE ug.group_id = $group_id 
								AND ug.user_pending <> 1 
								AND u.user_id = ug.user_id 
								AND u.user_allow_massemail = 1
							ORDER BY u.user_lang, u.user_notify_type";
					}
					else
					{
						$sql = 'SELECT username, user_email, user_jabber, user_notify_type, user_lang 
							FROM ' . USERS_TABLE . '
							WHERE user_allow_massemail = 1
							ORDER BY user_lang, user_notify_type';
					}
				}
				$result = $db->sql_query($sql);

				if (!($row = $db->sql_fetchrow($result)))
				{
					trigger_error($user->lang['NO_USER'] . adm_back_link($u_action));
				}
				$db->sql_freeresult($result);
	
				$i = $j = 0;
				// Send with BCC, no more than 50 recipients for one mail (to not exceed the limit)
				$max_chunk_size = 50;
				$email_list = array();
				$old_lang = $row['user_lang'];
				$old_notify_type = $row['user_notify_type'];

				do
				{
					if (($row['user_notify_type'] == NOTIFY_EMAIL && $row['user_email']) ||
						($row['user_notify_type'] == NOTIFY_IM && $row['user_jabber']) ||
						($row['user_notify_type'] == NOTIFY_BOTH && $row['user_email'] && $row['user_jabber']))
					{
						if ($i == $max_chunk_size || $row['user_lang'] != $old_lang || $row['user_notify_type'] != $old_notify_type)
						{
							$i = 0;
							$j++;
							$old_lang = $row['user_lang'];
							$old_notify_type = $row['user_notify_type'];
						}

						$email_list[$j][$i]['lang']		= $row['user_lang'];
						$email_list[$j][$i]['method']	= $row['user_notify_type'];
						$email_list[$j][$i]['email']	= $row['user_email'];
						$email_list[$j][$i]['name']		= $row['username'];
						$email_list[$j][$i]['jabber']	= $row['user_jabber'];
						$i++;
					}
				} 
				while ($row = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);

				// Send the messages
				include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
				$messenger = new messenger($use_queue);

				$errored = false;

				for ($i = 0, $size = sizeof($email_list); $i < $size; $i++)
				{
					$used_lang = $email_list[$i][0]['lang'];
					$used_method = $email_list[$i][0]['method'];

					for ($j = 0, $list_size = sizeof($email_list[$i]); $j < $list_size; $j++)
					{
						$email_row = $email_list[$i][$j];

						$messenger->{((sizeof($email_list[$i]) == 1) ? 'to' : 'bcc')}($email_row['email'], $email_row['name']);
						$messenger->im($email_row['jabber'], $email_row['name']);
					}

					$messenger->template('admin_send_email', $used_lang);

					$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
					$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
					$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
					$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
			
					$messenger->subject($subject);
					$messenger->replyto($config['board_email']);
					$messenger->set_mail_priority($priority);

					$messenger->assign_vars(array(
						'SITENAME'		=> $config['sitename'],
						'CONTACT_EMAIL' => $config['board_contact'],
						'MESSAGE'		=> $message)
					);
	
					if (!($messenger->send($used_method)))
					{
						$errored = true;
					}
				}
				unset($email_list);

				$messenger->save_queue();

				if ($group_id)
				{
					$sql = 'SELECT group_name 
						FROM ' . GROUPS_TABLE . " 
						WHERE group_id = $group_id";
					$result = $db->sql_query($sql);
					$group_name = (string) $db->sql_fetchfield('group_name', 0, $result);
					$db->sql_freeresult($result);
				}
				else
				{
					// Not great but the logging routine doesn't cope well with localising
					// on the fly
					$group_name = $user->lang['ALL_USERS'];
				}

				add_log('admin', 'LOG_MASS_EMAIL', $group_name);
				$message = (!$errored) ? $user->lang['EMAIL_SENT'] : sprintf($user->lang['EMAIL_SEND_ERROR'], '<a href="' . $phpbb_admin_path . "index.$phpEx$SID&amp;i=logs&amp;mode=critical" . '">', '</a>');
				trigger_error($message . adm_back_link($u_action));
			}
		}

		// Initial selection
		$sql = 'SELECT group_id, group_type, group_name
			FROM ' . GROUPS_TABLE . ' 
			ORDER BY group_type DESC, group_name ASC';
		$result = $db->sql_query($sql);

		$select_list = '<option value="0"' . ((!$group_id) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_USERS'] . '</option>';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = ($group_id == $row['group_id']) ? ' selected="selected"' : '';
			$select_list .= '<option value = "' . $row['group_id'] . '"' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);

		$s_priority_options = '<option value="' . MAIL_LOW_PRIORITY . '">' . $user->lang['MAIL_LOW_PRIORITY'] . '</option>';
		$s_priority_options .= '<option value="' . MAIL_NORMAL_PRIORITY . '" selected="selected">' . $user->lang['MAIL_NORMAL_PRIORITY'] . '</option>';
		$s_priority_options .= '<option value="' . MAIL_HIGH_PRIORITY . '">' . $user->lang['MAIL_HIGH_PRIORITY'] . '</option>';

		$template->assign_vars(array(
			'S_WARNING'				=> (sizeof($error)) ? true : false,
			'WARNING_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
			'U_ACTION'				=> $u_action,
			'S_GROUP_OPTIONS'		=> $select_list,
			'USERNAMES'				=> $usernames,
			'U_FIND_USERNAME'		=> $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=acp_email&amp;field=usernames",
			'SUBJECT'				=> request_var('subject', ''),
			'MESSAGE'				=> request_var('message', ''),
			'S_PRIORITY_OPTIONS'	=> $s_priority_options)
		);

	}
}

/**
* @package module_install
*/
class acp_email_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_email',
			'title'		=> 'ACP_MASS_EMAIL',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'email'		=> array('title' => 'ACP_MASS_EMAIL', 'auth' => 'acl_a_email'),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}


?>