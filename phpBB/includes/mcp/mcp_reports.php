<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mcp_reports.php
// STARTED   : Fri Nov 26, 2004
// COPYRIGHT : © 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------


// TODO: Would be nice if a moderator could 'checkout' a topic with reports so
// other moderators know that this topic is already being handled.

/**
* @package module_install
*/
class mcp_reports_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_reports',
			'title'		=> 'MCP_REPORTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'					=> array('title' => 'MCP_REPORTS_FRONT', 'auth' => 'acl_m_'),
				'reports_yours'			=> array('title' => 'MCP_REPORTS_YOURS', 'auth' => 'acl_m_'),
				'reports_new'			=> array('title' => 'MCP_REPORTS_NEW', 'auth' => 'acl_m_'),
				'reports_topics'		=> array('title' => 'MCP_REPORTS_TOPICS', 'auth' => 'acl_m_'),
				'reports_view_topic'	=> array('title' => 'MCP_REPORTS_VIEW_TOPIC', 'auth' => 'acl_m_'),
				'reports_view'			=> array('title' => 'MCP_REPORTS_VIEW', 'auth' => 'acl_m_')
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


/**
* @package mcp
* mcp_report
* Handle reports about users or posts sent in by users
*/
class mcp_reports
{

	var $p_master;
	
	function mcp_main(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $SID;

		$action = request_var('action', array('' => ''));

		switch ($mode)
		{
			case 'reports_yours':
				$this->mcp_reports_list($id, $mode);
				break;
			case 'reports_new':
				$this->mcp_reports_list($id, $mode);
				break;
			case 'reports_topics':
				$this->mcp_reports_list($id, $mode);
				break;
			case 'reports_view_topic':
				// View reports for one topic
				$this->mcp_reports_list($id, $mode);
				//$this->tpl_name = 'mcp_report_topic';
				break;
			case 'reports_view':
				// View one report (not attached to a topic)
				$this->mcp_report_view($id, $mode);
				break;
			default:
				// Main page with an overview
				$this->mcp_reports_list($id, $mode);
				break;
		}
	}

	// Overview of available reports
	function mcp_reports_list($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $SID;
	
		$forum_id = request_var('f', 0);
		$topic_id = request_var('t', 0);
		$start = request_var('start', 0);
	
		// Show report details for a specific topic if a topic has been selected.
		if($topic_id != 0)
		{
			return $this->mcp_report_view('topic', $topic_id);
		}
	
		$forum_info = array();
	
		$forum_list_report = get_forum_list('m_', false, true);
	
		// Show all reports that this user is allowed to view or only the
		// reports in one specific forum?
		if ($mode == 'overview' || !$forum_id)
		{
			$forum_list = array();
			foreach ($forum_list_report as $row)
			{
				$forum_list[] = $row['forum_id'];
			}
			
			if (!$forum_list = implode(', ', $forum_list))
			{
				trigger_error('NOT_MODERATOR');
			}
	
			$sql = 'SELECT SUM(forum_topics) as sum_forum_topics 
				FROM ' . FORUMS_TABLE . "
				WHERE forum_id IN ($forum_list)";
			$result = $db->sql_query($sql);
			$forum_info['forum_topics'] = (int) $db->sql_fetchfield('sum_forum_topics', 0, $result);
			$db->sql_freeresult($result);
		}
		else
		{
			$forum_info = get_forum_data(array($forum_id), 'm_approve');
	
			if (!sizeof($forum_info))
			{
				trigger_error('NOT_MODERATOR');
			}
	
			$forum_info = $forum_info[$forum_id];
			$forum_list = $forum_id;
		}
	
		$forum_options = '<option value="0"' . (($forum_id == 0) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_FORUMS'] . '</option>';
		foreach ($forum_list_report as $row)
		{
			$forum_options .= '<option value="' . $row['forum_id'] . '"' . (($forum_id == $row['forum_id']) ? ' selected="selected"' : '') . '>' . $row['forum_name'] . '</option>';
		}
	
		// Note: this query needs to be made compatible with non-MySQL DBMs
		mcp_sorting('reports', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id);
		$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
		$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';
		$sql = 'SELECT count(*) as report_count, r.*, p.post_id, p.post_subject, u.username, t.topic_id, t.topic_title, f.forum_id, f.forum_name
			FROM ' . REPORTS_TABLE . ' r, ' . REASONS_TABLE . ' rr,' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
			LEFT JOIN ' . FORUMS_TABLE . ' f ON f.forum_id = p.forum_id
			WHERE 
				r.report_status = 0
				AND r.post_id = p.post_id
				AND r.reason_id = rr.reason_id
				AND p.topic_id = t.topic_id
				AND r.user_id = u.user_id
				AND t.topic_reported = 1
				AND p.forum_id IN (' . (is_array($forum_list) ? implode(', ', $forum_list) : $forum_list) . ")
			GROUP BY topic_id
			ORDER BY $sort_order_sql";
		$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);
	
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['poster_id'] == ANONYMOUS)
			{
				$poster = (!empty($row['post_username'])) ? $row['post_username'] : $user->lang['GUEST'];
			}
			else
			{
				$poster = $row['username'];
			}
	
			$s_checkbox = '<input type="checkbox" name="post_id_list[]" value="' . $row['post_id'] . '" />';
			$template->assign_block_vars('report', array(
				'U_FORUM'	=> "mcp.$phpEx$SID&amp;i=report&amp;mode=&amp;f={$row['forum_id']}",
				// Q: Why accessing the topic by a post_id instead of its topic_id?
				// A: To prevent the post from being hidden because of wrong encoding or different charset
				'U_REPORT_TOPIC'	=> "mcp.$phpEx$SID&amp;i=report&amp;mode=report_view_topic&amp;t={$row['topic_id']}",
				'U_VIEW_DETAILS'=> "mcp.$phpEx$SID&amp;i=queue&amp;start=$start&amp;mode=approve_details&amp;f={$forum_id}&amp;p={$row['post_id']}",
				'U_VIEWPROFILE'	=> ($row['poster_id'] != ANONYMOUS) ? "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['poster_id']}" : '',
	
				'REPORT_COUNT'	=> $row['report_count'],
				'FORUM_NAME'	=> $row['forum_name'],
				'TOPIC_TITLE'	=> $row['topic_title'],
				'POSTER'		=> $poster,
				'REPORT_TIME'	=> $user->format_date($row['report_time']),
				'S_CHECKBOX'	=> $s_checkbox)
			);
		}
		$db->sql_freeresult($result);
	
		// Now display the page
		$template->assign_vars(array(
			'L_DISPLAY_ITEMS'		=> ($mode == 'unapproved_posts') ? $user->lang['DISPLAY_POSTS'] : $user->lang['DISPLAY_TOPICS'],
			'S_FORUM_OPTIONS'		=> $forum_options)
		);
		$this->tpl_name = 'mcp_reports_front';
	}
	
	// View the reports for one topic or view one topic.
	function mcp_report_view($id, $mode)
	{
		global $phpbb_root_path, $config, $db, $phpEx;
		global $user, $template, $auth;

		//$this->tpl_name = 'mcp_reports';
		if(!isset($_POST['feedback_submit']))
		{
			// Show the reports.
			$topic_id = request_var('t', 0);
			if ($topic_id == 0)
			{
				trigger_error('NO_TOPIC_SELECTED');
			}
	
			$topic_info = get_topic_data($topic_id, 'm_');
			$topic_info = $topic_info[$topic_id];

			$sql = "SELECT
					r.report_id, r.report_time, r.report_text, r.report_status,
					r.bbcode_uid as r_bbcode_uid, r.bbcode_bitfield as r_bbcode_bitfield,
					rre.reply_id, rre.reply_text, ure_from.username as reply_from_username, 
					ure_to.username as reply_to_username,
					p.post_id, p.topic_id, p.forum_id, p.post_time, p.post_subject, p.post_text,
					p.bbcode_uid as p_bbcode_uid, p.bbcode_bitfield as p_bbcode_bitfield,
					u1.user_id as reporter_user_id, u1.username as reporter_username,
					u2.user_id as poster_user_id, u2.username as poster_username
				FROM " . 
					REPORTS_TABLE . " r
					LEFT JOIN " . REPORTS_REPLIES_TABLE . " rre USING (report_id)
					LEFT JOIN " . USERS_TABLE . " ure_from ON (rre.from_user_id = ure_from.user_id)
					LEFT JOIN " . USERS_TABLE . " ure_to ON (rre.to_user_id = ure_to.user_id), " . 
					POSTS_TABLE . " p, " . 
					USERS_TABLE . " u1, " . 
					USERS_TABLE . " u2
				WHERE
					r.user_id = u1.user_id
					AND r.post_id = p.post_id
					AND p.poster_id = u2.user_id
					AND p.topic_id = $topic_id
				ORDER BY post_id DESC, report_id DESC, reply_id ASC";
			$result = $db->sql_query($sql);
	
			include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx);
			$old_post_id = 0;
			$old_report_id = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if($old_report_id != $row['report_id'])
				{
					if($old_post_id != $row['post_id'])
					{
						// Process message, leave it uncensored
						$message = $row['post_text'];
						if ($row['p_bbcode_bitfield'])
						{
							$bbcode = new bbcode($row['p_bbcode_bitfield']);
							$bbcode->bbcode_second_pass($message, $row['p_bbcode_uid'], $row['p_bbcode_bitfield']);
						}
						$message = smiley_text($message);
		
						$template->assign_block_vars('postrow', array(
								'POST_ID'	=>	$row['post_id'],
								'POST_SUBJECT'	=>	$row['post_subject'],
								'POSTER_USER_ID'	=>	$row['poster_user_id'],
								'POSTER_NAME' => $row['poster_username'],
								'U_POSTER_PROFILE'	=> ($row['poster_id'] != ANONYMOUS) ? "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['poster_id']}" : '',
								'POST_DATE' => $user->format_date($row['post_time']),
								'POST_PREVIEW' => $message,
								'U_APPROVE_ACTION'		=> "{$phpbb_root_path}mcp.$phpEx$SID&amp;i=report&amp;mode=topic",
								'U_EDIT'	=> ($auth->acl_get('m_edit', $row['forum_id'])) ? "{$phpbb_root_path}posting.$phpEx$SID&amp;mode=edit&amp;f={$row['forum_id']}&amp;p={$row['post_id']}" : '',
								'U_VIEW'	=> "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f={$row['forum_id']}&amp;p={$row['post_id']}#{$row['post_id']}"
								)
						);	
					} // Start post row.
					$old_post_id = $row['post_id'];
	
					// Process message, leave it uncensored
					$message = $row['report_text'];
					if ($row['r_bbcode_bitfield'])
					{
						$bbcode = new bbcode($row['r_bbcode_bitfield']);
						$bbcode->bbcode_second_pass($message, $row['r_bbcode_uid'], $row['r_bbcode_bitfield']);
					}
					$message = smiley_text($message);
	
					$template->assign_block_vars('postrow.reportrow', array(
						'REPORT_ID'	=>	$row['report_id'],
						'REPORT_TIME'	=> $user->format_date($row['report_time']),
						'REPORT_TEXT'	=> $message,
						'REPORT_STATUS'	=> $row['report_status'],
						'U_REPORTERPROFILE'	=> ($row['poster_id'] != ANONYMOUS) ? "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['reporter_user_id']}" : '',
						'REPORTER_USER_ID'	=>	$row['reporter_user_id'],
						'REPORTER_USERNAME' => $row['reporter_username'],
						'POSTER_USER_ID'	=>	$row['poster_user_id'],
						'POSTER_USERNAME' => $row['poster_username'],
						)
					);
				}
				$old_report_id = $row['report_id'];

				if($row['reply_text'] != '')
				{
					$template->assign_block_vars('postrow.reportrow.replyrow', array(
						'REPLY_ID'	=> $row['reply_id'],
						'REPLY_FROMUSERNAME'	=> $row['reply_from_username'],
						'REPLY_TOUSERNAME'		=> $row['reply_to_username'],
						'REPLY_TEXT'			=> $row['reply_text']
						));
				}

			}
			$db->sql_freeresult($result);
	
			// Set some vars
			$poster = ($post_info['user_colour']) ? '<span style="color:#' . $post_info['user_colour'] . '">' . $post_info['username'] . '</span>' : $post_info['username'];
	
			$template->assign_vars(array(
				'TOPIC_TITLE'			=> $topic_info['topic_title'],
				'U_TOPIC'				=> "viewtopic.$phpEx$SID&amp;t={$topic_info['topic_id']}",
				'U_FEEDBACK_ACTION'		=> $_SERVER['REQUEST_URI']
				)
			);
	
			$this->tpl_name = 'mcp_reports_topic';
			//$this->display($user->lang['MCP_QUEUE'], 'mcp_topicreports.html');
		} // No submit
		else
		{
			// Send feedback and close selected reports
			$selected_reports = request_var('sendfeedback', array('0'=>'0'));
			$report_close = request_var('feedback_close', '');
			$report_feedback = request_var('feedback_text', '');
			
			$post_ids = array();
			foreach($selected_reports as $key => $value)
			{
				$report_ids[] = $value;
			}
			
			if(count($report_ids) == 0)
			{
				// TODO: i18n
				trigger_error('No reports selected.');
			}
			
			$sql = "SELECT
					r.report_id, r.user_id, r.reason_id, r.post_id, r.report_text,
					u.username, u.user_email, u.user_jabber, u.user_lang, u.user_notify_type, u.user_options,
					rr.reason_name, rr.reason_description,
					p.post_id, p.post_subject,
					t.topic_title, t.forum_id
				FROM " . REPORTS_TABLE . " r, " .
				REASONS_TABLE . " rr, " .
				USERS_TABLE . " u, " .
				POSTS_TABLE . " p, " .
				TOPICS_TABLE . " t
				WHERE
					r.reason_id = rr.reason_id
					AND r.user_id = u.user_id
					AND r.post_id = p.post_id
					AND p.topic_id = t.topic_id
					AND report_id IN (" . implode(', ', $report_ids) . ")";
			$result = $db->sql_query($sql);
			
			while($row = $db->sql_fetchrow($result))
			{
				$feedback_data[$row['user_id']] = array(
					'name'	=> $row['username'],
					'email' => $row['user_email'],
					'jabber'=> $row['user_jabber'],
					'lang'	=> $row['user_lang'],
					'notify_type'	=> $row['user_notify_type'],
					'pm'	=> $user->optionget('report_pm_notify', $row['user_options']),
					'report_id'	=> $row['report_id'],
					'reporter'	=> $row['username'],
					'moderator'	=>	$user->data['username'],
					'moderator_id'	=>	$user->data['user_id'],
					'reason'	=> $row['reason_desc'],
					'report_feedback' => $report_feedback,
					'text'		=> $row['report_text'],
					'subject'	=> $row['topic_title'],
					'view_post'	=> "viewtopic.$phpEx?f={$row['forum_id']}&amp;t={$row['topic_id']}&amp;p={$row['post_id']}&amp;#{$row['post_id']}"
	
				);
	
				$reported_posts[$row['post_id']]++;
				
			}
			
			// Only send feedback if there is feedback to send
			if($report_feedback != '')
			{
				$this->report_feedback($feedback_data);
			}
	
			// See if we need to close the report, update notifications in viewforum/topic
	
			// Start transaction
			$db->sql_transaction('begin');
			
			if ($report_close)
			{
				// Close the reports
				$sql = 'UPDATE ' . REPORTS_TABLE . ' 
					SET report_status = ' . REPORT_CLOSED . '
					WHERE report_id IN (' . implode(', ', $report_ids) . ')';
				$db->sql_query($sql);
			}
	
			// TODO: Should we remove the notification in viewforum/topic after feedback has been added to a report? Or should we introduce a 'report in progress' icon for viewforum/topic?
			// Figure out what posts are without open reports after this update.
			$sql = 'SELECT r.post_id, p.topic_id, sum(IF(report_status=1, 0, 1)) as open_reports
				FROM ' . REPORTS_TABLE . ' r
				LEFT JOIN ' . POSTS_TABLE . ' p USING (post_id)
				WHERE r.post_id IN (' . implode(', ', array_keys($reported_posts)) . ')
				GROUP BY r.post_id
				HAVING open_reports = 0';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{
				$resolved_posts[] = $row['post_id'];
				$resolved_topics[] = $row['topic_id'];
			}
	
			// Mark those posts as resolved
			if(!empty($resolved_posts))
			{
				$sql = 'UPDATE ' . POSTS_TABLE . ' SET
					post_reported = 0
					WHERE post_id IN (' . implode(', ', $resolved_posts) . ')';
				$db->sql_query($sql);
				
				// Mark topic as resolved because a moderator has taken a look at it.
				$sql = 'UPDATE ' . TOPICS_TABLE . ' SET
					topic_reported = 0
					WHERE topic_id IN (' . implode(', ', $resolved_topics) . ')';
				$db->sql_query($sql);
			}
			
			// End transaction
			$db->sql_transaction('commit');
	
			// Say something nice to the moderator
			trigger_error('These reports have been closed. Thank you :)<br /> <a href="'.$_SERVER['REQUEST_URI'].'">return</a>');
			
	
		} // The form was submitted
	}
	
	// ----------------------------------------------------------------------------
	
	// Reply to reports and notify reporters of this event.
	function report_feedback($data)
	{
		global $config, $phpbb_root_path, $phpEx;
		global $user, $db;

		
		foreach ($data as $user_id => $reply_row)
		{
			// TODO: Include a subject?
			$sql_data = array(
				'report_id'		=> $reply_row['report_id'],
				'reply_time'	=> time(),
				'reply_text'	=> $reply_row['report_feedback'],
				'from_user_id'	=> $reply_row['moderator_id'],
				'to_user_id'	=> $user_id
			);
			$db->sql_query('INSERT INTO ' . REPORTS_REPLIES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data));
		}
		
		// Notify the recipient of this reply
		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		$messenger = new messenger();

		$email_sig = str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']);
		$email_template = 'report_feedback';

		foreach ($data as $user_id => $notify_row)
		{
			// Send notification by email
			if (!$notify_row['pm'])
			{
				$messenger->to($notify_row['email'], $notify_row['name']);
				$messenger->im($notify_row['jabber'], $notify_row['name']);
				$messenger->replyto($config['board_email']);

				$messenger->template($email_template, $notify_row['lang']);

				$messenger->assign_vars(array(
					'EMAIL_SIG'		=> $email_sig,
					'SITENAME'		=> $config['sitename'],
					'USERNAME'		=> $notify_row['name'],
					'SUBJECT'		=> $notify_row['subject'],
					'REPORTER'		=> $notify_row['reporter'],
					'MODERATOR'		=> $notify_row['moderator'],
	
					'REPORT_REASON'	=> $notify_row['reason'],
					'REPORT_TEXT'	=> $notify_row['text'],
					'REPORT_FEEDBACK'	=> $notify_row['report_feedback'],
	
					'U_VIEW_POST'	=> generate_board_url() . '/' . $notify_row['view_post'])
				);

				$messenger->send($notify_row['notify_type']);
				$messenger->reset();
				//print "mail to " . $notify_row['email'] . "({$notify_row['notify_type']})";

				if ($messenger->queue)
				{
					$messenger->queue->save();
				}
			}
			else
			{
				// Use messenger for getting the correct message, we use the email template
				$messenger->template($email_template, $notify_row['lang']);

				$messenger->assign_vars(array(
					'EMAIL_SIG'		=> $email_sig,
					'SITENAME'		=> $config['sitename'],
					'USERNAME'		=> $notify_row['name'],
					'SUBJECT'		=> $notify_row['subject'],
					'REPORTER'		=> $notify_row['reporter'],
					'MODERATOR'		=> $notify_row['moderator'],
	
					'REPORT_REASON'	=> $notify_row['reason'],
					'REPORT_TEXT'	=> $notify_row['text'],
					'REPORT_FEEDBACK'	=> $notify_row['report_feedback'],
	
					'U_VIEW_POST'	=> generate_board_url() . '/' . $notify_row['view_post'])
				);

				// Parse message, don't send it.
				$messenger->send(false, true);

				// do not put in moderators outbox
				$pm_data =  array(
					'address_list'		=> array('u' => array($user_id => 'to')),
					'from_user_id'		=> $user->data['user_id'],
					'from_user_ip'		=> $user->data['user_ip'],
					'from_username'		=> $user->data['username'],
					'icon_id'			=> 0,
					'enable_bbcode' 	=> 0,
					'enable_html' 		=> 0,
					'enable_smilies' 	=> 0,
					'enable_urls' 		=> 1,
					'enable_sig' 		=> 0,
					'message_md5'		=> md5($messenger->msg),
					'bbcode_bitfield'	=> 0,
					'bbcode_uid'		=> 0,
					'attachment_data'	=> array(),
					'filename_data'		=> array(),
					'message'			=> $messenger->msg				
					);

				//function submit_pm($mode, $subject, &$data, $update_message, $put_in_outbox)
				submit_pm('post', $notify_row['subject'], $pm_data, true, false);

				// Break the sending process...
				$messenger->reset();

				//print "PM to " . $notify_row['name'];
			}

			// Add the feedback to the report
	      $message = $notify_row['report_feedback'];
		}
		unset($messenger);
	}
}
?>