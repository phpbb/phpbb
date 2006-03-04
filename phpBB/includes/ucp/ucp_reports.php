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
* @package ucp
* ucp_reports
*/
class ucp_reports
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		switch ($mode)
		{
			case 'list':
				$this->ucp_reports_list($id, $mode);
				break;
			case 'report':
				$this->ucp_reports_report($id, $mode);
				break;
		}
	}
	
	function ucp_reports_list($id, $mode)
	{
		global $db, $user, $config, $template;
		
		$sql = "SELECT 
					r.report_id, r.report_time, r.report_status,
					p.post_id, p.poster_id,
					t.topic_id, t.topic_title,
					f.forum_id, f.forum_name,
					u.username
				FROM " .
					REPORTS_TABLE . " r
					LEFT JOIN " . POSTS_TABLE . " p USING (post_id)
					LEFT JOIN " . TOPICS_TABLE . " t USING (topic_id)
					LEFT JOIN " . FORUMS_TABLE . " f USING (forum_id)," .
					REASONS_TABLE . " re, " .
					USERS_TABLE . " u
				WHERE
					p.poster_id = u.user_id
					&& r.reason_id = re.reason_id
					&& r.user_id = " . $user->data['user_id'] . "
				ORDER BY
					report_time DESC";
		
		$start = request_var('start', 0);
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
				)
			);
		}
		$db->sql_freeresult($result);
						
						
		$this->tpl_name = 'ucp_reports_list';
	}
	
	function ucp_reports_report($id, $mode)
	{
		global $db, $user, $config, $template, $auth;

		$post_id = request_var('p', 0);
		$report_type = ($post_id > 0) ? REPORT_POST : REPORT_GENERAL;

		// Insert or update report in the database if a form has been submitted
		if (isset($_POST['submit']))
		{
			$report_id = request_var('report_id', 0);
			$reason_id = request_var('reason_id', 0);
			$user_notify = (!empty($_REQUEST['notify']) && $user->data['is_registered']) ? true : false;
			$report_text = request_var('report_text', '');

			$sql = 'SELECT reason_name
				FROM ' . REASONS_TABLE . " 
				WHERE reason_id = $reason_id";
			$result = $db->sql_query($sql);
		
			// TODO: 'other' is used as a special value. Make sure that you can't remove this in the admin.
			if (!($row = $db->sql_fetchrow($result)) || (!$report_text && $row['reason_name'] == 'other'))
			{
				trigger_error('EMPTY_REPORT');
			}
			$db->sql_freeresult($result);
		
			if (!empty($user->lang['report_reasons']['DESCRIPTION'][$row['reason_name']]))
			{
				$reason_desc = $user->lang['report_reasons']['DESCRIPTION'][$row['reason_name']];
			}
			else
			{
				$reason_desc = $row['reason_name'];
			}
		
			$sql_ary = array(
				'reason_id'		=> (int) $reason_id,
				'reason_type'	=> (int) $report_type,
				'post_id'		=> (int) $post_id,
				'user_id'		=> (int) $user->data['user_id'],
				'user_notify'	=> (int) $user_notify,
				'report_time'	=> (int) time(),
				'report_text'	=> (string) $report_text // TODO: Add some BBcode magic
			);
		
			if ($report_id)
			{
				$sql = 'UPDATE ' . REPORTS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE report_id = ' . $report_id . ' user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);
				if ($db->sql_affectedrows() == 0)
				{
					// TODO: i18n?
					trigger_error("You tried to change a report that isn't yours.");
				}
			}
			else
			{
				$sql = 'INSERT INTO ' . REPORTS_TABLE . ' ' . 
					$db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
				$report_id = $db->sql_nextid();
			}
		
			if (!$report_data['post_reported'])
			{
				$sql = 'UPDATE ' . POSTS_TABLE . ' 
					SET post_reported = 1 
					WHERE post_id = ' . $id;
				$db->sql_query($sql);
			}
	
			if (!$report_data['topic_reported'])
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . ' 
					SET topic_reported = 1 
					WHERE topic_id = ' . $report_data['topic_id'];
				$db->sql_query($sql);
			}
		
			// Send Notifications
			// All persons get notified about a new report, if notified by PM, send out email notifications too
			
			// Send notifications to moderators
			$acl_list = $auth->acl_get_list(false, array('m_', 'a_'), array(0, $report_data['forum_id']));
			$notify_user = $acl_list[$report_data['forum_id']]['m_'];
			$notify_user = array_unique(array_merge($notify_user, $acl_list[0]['a_']));
			unset($acl_list);
		
			// How to notify them?
			$sql = 'SELECT user_id, username, user_options, user_lang, user_email, user_notify_type, user_jabber 
				FROM ' . USERS_TABLE . '
				WHERE user_id IN (' . implode(', ', $notify_user) . ')';
			$result = $db->sql_query($sql);
		
			$notify_user = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$notify_user[$row['user_id']] = array(
					'name'	=> $row['username'],
					'email' => $row['user_email'],
					'jabber'=> $row['user_jabber'],
					'lang'	=> $row['user_lang'],
					'notify_type'	=> $row['user_notify_type'],
					
					'pm'	=> $user->optionget('report_pm_notify', $row['user_options'])
				);
			}
			$db->sql_freeresult($result);
		
			$report_data = array(
				'id'		=> $id,
				'report_id'	=> $report_id,
				'reporter'	=> $user->data['username'],
				'reason'	=> $reason_desc,
				'text'		=> $report_text,
				'subject'	=> $report_data['post_subject'],
				'view_post'	=> ($report_type == REPORT_POST) ? "viewtopic.$phpEx?f={$report_data['forum_id']}&t={$report_data['topic_id']}&p=$id&e=$id" : ''
			);
		
			report_notification($notify_user, $report_type, $report_data);
		
			meta_refresh(3, $redirect_url);
		
			$message = $user->lang['POST_REPORTED_SUCCESS'] . '<br /><br />' . sprintf($user->lang[(($report_type == REPORT_POST) ? 'RETURN_TOPIC' : 'RETURN_PREVIOUS')], '<a href="' . $redirect_url . '">', '</a>');
			trigger_error($message);
		}


		// Show the 'create report' form
		// Report about a specific post or a general report (i.e. message to the mods)?
		$post_id = (request_var('p', 0)) ? true : false;

		if ($report_type == REPORT_POST)
		{
			$sql = 'SELECT 
					f.forum_id,
					t.topic_id
				FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
				WHERE p.post_id = $post_id
					AND p.topic_id = t.topic_id
					AND p.forum_id = f.forum_id";
			$result = $db->sql_query($sql);
			
			if (!($report_data = $db->sql_fetchrow($result)))
			{
				$message = $user->lang['POST_NOT_EXIST'];
				trigger_error($message);
			}
			
			$forum_id = $report_data['forum_id'];
			$topic_id = $report_data['topic_id'];
		
			// Check required permissions
			$acl_check_ary = array('f_list' => 'POST_NOT_EXIST', 'f_read' => 'USER_CANNOT_READ', 'f_report' => 'USER_CANNOT_REPORT');
			
			foreach ($acl_check_ary as $acl => $error)
			{
				if (!$auth->acl_get($acl, $forum_id))
				{
					trigger_error($error);
				}
			}
			unset($acl_check_ary);

			// Check if the post has already been reported by this user
			$sql = "SELECT
					report_id, reason_id, post_id, user_notify, report_time, report_text, report_status,
					bbcode_uid, bbcode_bitfield
				FROM " . REPORTS_TABLE . "
				WHERE post_id = $post_id
					AND user_id = " . $user->data['user_id'];
			$result = $db->sql_query($sql);
			
			if ($row = $db->sql_fetchrow($result))
			{
				if ($user->data['is_registered'])
				{
					// A report exists, extract $row if we're going to display the form
					if ($reason_id)
					{
						$report_id = (int) $row['report_id'];
					}
					else
					{
						// Overwrite set variables
						$report_id		= $row['report_id'];
						$reason_id		= $row['reason_id'];
						$post_id		= $row['post_id'];
						$user_notify	= $row['user_notify'];
						$report_time	= $row['report_time'];
						$report_text	= $row['report_text'];
						$report_status	= $row['report_status'];
						$bbcode_uid		= $row['bbcode_uid'];
						$bbcode_bitfield= $row['bbcode_bitfield'];
					}
				}
				else
				{
					// TODO: is this what we want?
					trigger_error($user->lang['ALREADY_REPORTED'] . '<br /><br />' . sprintf($user->lang[(($report_type == REPORT_POST) ? 'RETURN_TOPIC' : 'RETURN_PREVIOUS')], '<a href="' . $redirect_url . '">', '</a>'));
				}
			}
			else
			{
				$report_id = 0;
			}
		}
		
		// Show create report form
		// Generate the form
		$sql = "SELECT * 
			FROM " . REASONS_TABLE . "
			WHERE report_type = $report_type
			ORDER BY reason_priority ASC";
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$row['reason_name'] = strtoupper($row['reason_name']);
		
			$reason_title = (!empty($user->lang['report_reasons']['TITLE'][$row['reason_name']])) ? $user->lang['report_reasons']['TITLE'][$row['reason_name']] : ucwords(str_replace('_', ' ', $row['reason_name']));
		
			$reason_desc = (!empty($user->lang['report_reasons']['DESCRIPTION'][$row['reason_name']])) ? $user->lang['report_reasons']['DESCRIPTION'][$row['reason_name']] : $row['reason_description'];
		
			$template->assign_block_vars('reason', array(
				'ID'			=>	$row['reason_id'],
				'NAME'			=>	htmlspecialchars($reason_title),
				'DESCRIPTION'	=>	htmlspecialchars($reason_desc),
				'S_SELECTED'	=>	($row['reason_id'] == $reason_id) ? true : false)
			);
		}
		
		$template->assign_vars(array(
			'REPORT_TEXT'		=> $report_text,
			'S_REPORT_ACTION'	=> "{$phpbb_root_path}report.$phpEx$SID&amp;p=$id" . (($report_id) ? "&amp;report_id=$report_id" : ''),

			'S_NOTIFY'			=> (!empty($user_notify)) ? true : false,
			'S_CAN_NOTIFY'		=> ($user->data['is_registered']) ? true : false,
			'S_REPORT_POST'		=> ($report_type == REPORT_POST) ? true : false)
		);
		
		$this->tpl_name = 'ucp_reports_report';
	}
}

/**
* @package module_install
*/
class ucp_reports_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_reports',
			'title'		=> 'UCP_REPORTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'list'			=> array('title' => 'UCP_REPORTS_LIST', 'auth' => ''),
				'report'		=> array('title' => 'UCP_REPORTS_REPORT', 'auth' => ''),
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