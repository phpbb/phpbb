<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mcp_topic.php
// STARTED   : Thu Jul 08, 2004
// COPYRIGHT : © 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

//
// TODO:
//

function mcp_topic_view($id, $mode, $action, $url)
{
	global $SID, $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	$user->add_lang('viewtopic');

	$topic_id = request_var('t', 0);
	
	$topic_info = get_topic_data(array($topic_id));

	if (!sizeof($topic_info))
	{
		trigger_error($user->lang['TOPIC_NOT_EXIST']);
	}

	$topic_info = $topic_info[$topic_id];

	// Set up some vars
	$icon_id = request_var('icon', 0);
	$subject = request_var('subject', '');
	$start = request_var('start', 0);
	$to_topic_id = request_var('to_topic_id', 0);
	$to_forum_id = request_var('to_forum_id', 0);

	$post_id_list = get_array('post_id_list', 0);

	$topics_per_page = ($topic_info['forum_topics_per_page']) ? $topic_info['forum_topics_per_page'] : $config['topics_per_page'];

	// Jumpbox, sort selects and that kind of things
	make_jumpbox($url . '&amp;mode=forum_view', $topic_info['forum_id'], false, 'm_');
	mcp_sorting('viewtopic', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $topic_info['forum_id'], $topic_id);

	$forum_topics = ($total == -1) ? $topic_info['forum_topics'] : $total;
	$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

	if ($total == -1)
	{
		$total = $topic_info['topic_replies'] + 1;
	}
	$posts_per_page = max(0, request_var('posts_per_page', intval($config['posts_per_page'])));

	$sql = 'SELECT u.username, u.user_colour, p.*
		FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
		WHERE p.topic_id = {$topic_id}
			AND p.poster_id = u.user_id
		ORDER BY $sort_order_sql";
	$result = $db->sql_query_limit($sql, $posts_per_page, $start);

	$rowset = array();
	$bbcode_bitfield = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		$rowset[] = $row;
		$bbcode_bitfield |= $row['bbcode_bitfield'];
	}

	if ($bbcode_bitfield)
	{
		include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
		$bbcode = new bbcode($bbcode_bitfield);
	}

	foreach ($rowset as $i => $row)
	{
		$has_unapproved_posts = false;
		$poster = ($row['poster_id'] != ANONYMOUS) ? $row['username'] : ((!$row['post_username']) ? $user->lang['GUEST'] : $row['post_username']);
		$poster = ($row['user_colour']) ? '<span style="color:#' . $row['user_colour'] . '">' . $poster . '</span>' : $poster;

		$message = $row['post_text'];
		$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : $topic_info['topic_title'];

		// If the board has HTML off but the post has HTML
		// on then we process it, else leave it alone
		if (!$config['allow_html'] && $row['enable_html'])
		{
			$message = preg_replace('#(<)([\/]?.*?)(>)#is', '&lt;\\2&gt;', $message);
		}

		if ($row['bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
		}

		$message = smilie_text($message);
		$message = str_replace("\n", '<br />', $message);

		$checked = ($post_id_list && in_array(intval($row['post_id']), $post_id_list)) ? 'checked="checked" ' : '';
		$s_checkbox = ($row['post_id'] == $topic_info['topic_first_post_id'] && $action == 'split') ? '&nbsp;' : '<input type="checkbox" name="post_id_list[]" value="' . $row['post_id'] . '" ' . $checked . '/>';

		if (!$row['post_approved'])
		{
			$has_unapproved_posts = true;
		}

		$template->assign_block_vars('postrow', array(
			'POSTER_NAME'	=> $poster,
			'POST_DATE'		=> $user->format_date($row['post_time']),
			'POST_SUBJECT'	=> $post_subject,
			'MESSAGE'		=> $message,
			'POST_ID'		=> $row['post_id'],

			'POST_ICON_IMG' => ($row['post_time'] > $user->data['user_lastvisit'] && $user->data['user_id'] != ANONYMOUS) ? $user->img('icon_post_new', $user->lang['NEW_POST']) : $user->img('icon_post', $user->lang['POST']),

			'S_CHECKBOX'		=> $s_checkbox,
			'S_POST_REPORTED'	=> ($row['post_reported']) ? true : false,
			'S_POST_UNAPPROVED'	=> ($row['post_approved']) ? false : true,
						
			'U_POST_DETAILS'	=> "$url&amp;p={$row['post_id']}&amp;mode=post_details",
			'U_APPROVE'			=> "$url&amp;i=queue&amp;mode=approve&amp;p=" . $row['post_id'])
		);

		unset($rowset[$i]);
	}

	// Display topic icons for split topic
	$s_topic_icons = false;

	if ($auth->acl_get('m_split', $topic_info['forum_id']))
	{
		include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		$s_topic_icons = posting_gen_topic_icons('', $icon_id);

		// Has the user selected a topic for merge?
		if ($to_topic_id)
		{
			$to_topic_info = get_topic_data(array($to_topic_id), 'm_merge');
			
			if (!sizeof($to_topic_info))
			{
				$to_topic_id = 0;
			}
			else
			{
				$to_topic_info = $to_topic_info[$to_topic_id];
			}
			

			if (!$to_topic_info['enable_icons'])
			{
				$s_topic_icons = false;
			}
		}
	}

	$template->assign_vars(array(
		'TOPIC_TITLE'		=> $topic_info['topic_title'],
		'U_VIEWTOPIC'		=> "viewtopic.$phpEx$SID&amp;f=" . $topic_info['forum_id'] . '&amp;t=' . $topic_info['topic_id'],

		'TO_TOPIC_ID'		=> $to_topic_id,
		'TO_TOPIC_INFO'		=> ($to_topic_id) ? sprintf($user->lang['YOU_SELECTED_TOPIC'], $to_topic_id, '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $to_topic_info['forum_id'] . '&amp;t=' . $to_topic_id . '" target="_new">' . $to_topic_info['topic_title'] . '</a>') : '',

		'SPLIT_SUBJECT'		=> $subject,
		'POSTS_PER_PAGE'	=> $posts_per_page,
		'MODE'				=> $mode,

		'REPORTED_IMG'		=> $user->img('icon_reported', 'POST_REPORTED', false, true),
		'UNAPPROVED_IMG'	=> $user->img('icon_unapproved', 'POST_UNAPPROVED', false, true),

		'S_MCP_ACTION'		=> "$url&amp;mode=$mode&amp;start=$start",
		'S_FORUM_SELECT'	=> '<select name="to_forum_id">' . (($to_forum_id) ? make_forum_select($to_forum_id) : make_forum_select($topic_info['forum_id'])) . '</select>',
		'S_CAN_SPLIT'		=> ($auth->acl_get('m_split', $topic_info['forum_id'])) ? true : false,
		'S_CAN_MERGE'		=> ($auth->acl_get('m_merge', $topic_info['forum_id'])) ? true : false,
		'S_CAN_DELETE'		=> ($auth->acl_get('m_delete', $topic_info['forum_id'])) ? true : false,
		'S_CAN_APPROVE'		=> ($has_unapproved_posts && $auth->acl_get('m_approve', $topic_info['forum_id'])) ? true : false,
		'S_CAN_LOCK'		=> ($auth->acl_get('m_lock', $topic_info['forum_id'])) ? true : false,

		'S_SHOW_TOPIC_ICONS'=> $s_topic_icons,
		'S_TOPIC_ICON'		=> $icon_id,

		'RETURN_TOPIC'		=> sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;f={$topic_info['forum_id']}&amp;t={$topic_info['topic_id']}&amp;start=$start\">", '</a>'),
		'RETURN_FORUM'		=> sprintf($user->lang['RETURN_FORUM'], "<a href=\"viewforum.$phpEx$SID&amp;f={$topic_info['forum_id']}&amp;start=$start\">", '</a>'),

		'PAGE_NUMBER'		=> on_page($total, $posts_per_page, $start),
		'PAGINATION'		=> (!$posts_per_page) ? '' : generate_pagination("mcp.$phpEx$SID&amp;t=" . $topic_info['topic_id'] . "&amp;mode=$mode&amp;posts_per_page=$posts_per_page&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $total, $posts_per_page, $start),
		'TOTAL'				=> $total)
	);
}