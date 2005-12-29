<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('search');

// Define initial vars
$mode		= request_var('mode', '');
$search_id	= request_var('search_id', '');
$search_session_id	= request_var('search_session_id', 0);
$start		= request_var('start', 0);
$post_id	= request_var('p', 0);
$view		= request_var('view', '');

$keywords		= request_var('keywords', '');
$author			= request_var('author', '');
$show_results	= request_var('show_results', 'topics');
$search_terms	= request_var('search_terms', 'all');
$search_fields	= request_var('search_fields', 'all');
$search_child	= request_var('search_child', true);

$return_chars	= request_var('return_chars', 200);
$search_forum	= request_var('search_forum', array(0));

$sort_days	= request_var('st', 0);
$sort_key	= request_var('sk', 't');
$sort_dir	= request_var('sd', 'd');

// Is user able to search? Has search been disabled?
if (!$auth->acl_get('u_search') || !$config['load_search'])
{
	trigger_error($user->lang['NO_SEARCH']);
}

// Define some vars
$limit_days		= array(0 => $user->lang['ALL_RESULTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
$sort_by_text	= array('a' => $user->lang['SORT_AUTHOR'], 't' => $user->lang['SORT_TIME'], 'f' => $user->lang['SORT_FORUM'], 'i' => $user->lang['SORT_TOPIC_TITLE'], 's' => $user->lang['SORT_POST_SUBJECT']);

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

$store_vars		= array('sort_key', 'sort_dir', 'sort_days', 'show_results', 'return_chars', 'total_match_count');
$current_time	= time();

// Check last search time ... if applicable
if ($config['search_interval'])
{
	$sql = 'SELECT MAX(search_time) as last_time
		FROM ' . SEARCH_TABLE;
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		if ($row['last_time'] > time() - $config['search_interval'])
		{
			trigger_error($user->lang['NO_SEARCH_TIME']);
		}
	}
}

if ($keywords || $author || $search_id || $search_session_id)
{
	// clear arrays
	$pid_ary = $fid_ary = array();

	// Which forums can we view?
	$sql_where = (sizeof($search_forum) && !$search_child) ? 'WHERE f.forum_id IN (' . implode(', ', $search_forum) . ')' : '';
	$sql = 'SELECT f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.right_id, f.forum_password, fa.user_id
		FROM ' . FORUMS_TABLE . ' f
		LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa ON  (fa.forum_id = f.forum_id
			AND fa.session_id = '" . $db->sql_escape($user->data['session_id']) . "')
		$sql_where
		ORDER BY f.left_id";
	$result = $db->sql_query($sql);

	$right_id = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		if ($search_child)
		{
			if (!$search_forum || (in_array($row['forum_id'], $search_forum) && $row['right_id'] > $right_id))
			{
				$right_id = $row['right_id'];
			}
			else if ($row['right_id'] > $right_id)
			{
				continue;
			}
		}

		if ($auth->acl_get('f_read', $row['forum_id']) && (!$row['forum_password'] || $row['user_id'] == $user->data['user_id']))
		{
			$fid_ary[] = $row['forum_id'];
		}
	}
	$db->sql_freeresult($result);
	unset($search_forum);

	if (!sizeof($fid_ary))
	{
		trigger_error($user->lang['NO_SEARCH_RESULTS']);
	}

	if ($search_id == 'egosearch')
	{
		$author = $user->data['username'];
	}

	// Are we looking for a user?
	$author_id = 0;
	if ($author)
	{
		$sql_where = (strstr($author, '*') !== false) ? ' LIKE ' : ' = ';
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
			WHERE username $sql_where '" . $db->sql_escape(preg_replace('#\*+#', '%', $author)) . "'
				AND user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		$result = $db->sql_query($sql);

		if (!$row = $db->sql_fetchrow($result))
		{
			trigger_error($user->lang['NO_SEARCH_RESULTS']);
		}
		$db->sql_freeresult($result);

		$author_id = (int) $row['user_id'];
	}


	if ($search_id)
	{
		$sql_in = $sql_where = '';

		switch ($search_id)
		{
			// Oh holy Bob, bring us some activity...
			case 'active_topics':
				$show_results = 'topics';

				if (!$sort_days)
				{
					$sort_days = 1;
					gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				}

				$last_post_time = (time() - ($sort_days * 24 * 3600));

				$sql = 'SELECT DISTINCT t.topic_id
					FROM ' . POSTS_TABLE . ' p
					LEFT JOIN ' . TOPICS_TABLE . " t ON (t.topic_approved = 1 AND p.topic_id = t.topic_id)
					WHERE p.post_time > $last_post_time
						" . ((sizeof($fid_ary)) ? ' AND p.forum_id IN (' . implode(',', $fid_ary) . ')' : '') . '
					ORDER BY t.topic_last_post_time DESC';
				$result = $db->sql_query_limit($sql, 1000);

				while ($row = $db->sql_fetchrow($result))
				{
					$pid_ary[] = $row['topic_id'];
				}
				$db->sql_freeresult($result);
				break;

			case 'egosearch':
				break;

			case 'unanswered':
				if ($show_results == 'posts')
				{
					$sql = 'SELECT p.post_id
						FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
						WHERE t.topic_replies = 0
							AND p.topic_id = t.topic_id
							" . ((sizeof($fid_ary)) ? ' AND p.forum_id IN (' . implode(',', $fid_ary) . ')' : '');
					$field = 'post_id';
				}
				else
				{
					$sql = 'SELECT t.topic_id
						FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
						WHERE t.topic_replies = 0
							AND p.topic_id = t.topic_id
							" . ((sizeof($fid_ary)) ? ' AND p.forum_id IN (' . implode(',', $fid_ary) . ')' : '') . '
						GROUP BY p.topic_id';
					$field = 'topic_id';
				}
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$pid_ary[] = $row[$field];
				}
				$db->sql_freeresult($result);

				if (!sizeof($pid_ary))
				{
					trigger_error($user->lang['NO_SEARCH_RESULTS']);
				}
				break;

			case 'newposts':
				if ($show_results == 'posts')
				{
					$sql = 'SELECT p.post_id
						FROM ' . POSTS_TABLE . ' p
						WHERE p.post_time > ' . $user->data['user_lastvisit'] . "
							" . ((sizeof($fid_ary)) ? ' AND p.forum_id IN (' . implode(',', $fid_ary) . ')' : '');
					$field = 'post_id';
				}
				else
				{
					$sql = 'SELECT t.topic_id
						FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
						WHERE p.post_time > ' . $user->data['user_lastvisit'] . "
							AND t.topic_id = p.topic_id
							" . ((sizeof($fid_ary)) ? ' AND p.forum_id IN (' . implode(',', $fid_ary) . ')' : '') . '
						GROUP by p.topic_id';
					$field = 'topic_id';
				}
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$pid_ary[] = $row[$field];
				}
				$db->sql_freeresult($result);

				if (!sizeof($pid_ary))
				{
					trigger_error($user->lang['NO_SEARCH_RESULTS']);
				}
				break;
		}
	}	
	
	/**
	* @todo add to config
	*/
	$config['search_type'] = 'phpbb';

	// Select which method we'll use to obtain the post_id information
	$smid = '';
	switch ($config['search_type'])
	{
		case 'phpbb':
			$smid = 'fulltext_phpbb';
			break;
		case 'mysql':
			$smid = 'fulltext_mysql';
			break;
/*		case 'mssql':
		case 'pgsql':
			$smid = 'fulltext_pgmssql';
			break;
		case 'like':
			$smid = 'like';
			break;
		case 'preg':
			$smid = 'preg_mysql';
			break;*/
		default:
			trigger_error('NO_SUCH_SEARCH_MODULE');
	}

	require($phpbb_root_path . 'includes/search/' . $smid . '.' . $phpEx);

	// We do some additional checks in each module to ensure it can actually be utilised
	$error = false;
	$search = new $smid($error);
	
	if ($error)
	{
		trigger_error($error);
	}

	if ($search_session_id)
	{
		$sql = 'SELECT search_array
			FROM ' . SEARCH_TABLE . "
			WHERE search_id = $search_session_id
				AND session_id = '" . $db->sql_escape($user->data['session_id']) . "'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$pid_ary = explode('#', $row['search_array']);

			$search->split_words = unserialize(array_shift($pid_ary));
			if ($keywords)
			{
				// If we're wanting to search on these results we store the existing split word array
				$search->old_split_words = $search->split_words;
			}
			$search->common_words = unserialize(array_shift($pid_ary));

			foreach ($store_vars as $var)
			{
				$$var = array_shift($pid_ary);
			}
		}
		$db->sql_freeresult($result);
	}

	$total_match_count = 0;
	$search->search($show_results, $search_fields, $search_terms, $fid_ary, $keywords, $author_id, $pid_ary, $sort_days);

	if ($pid_ary)
	{
		// Finish building query (for all combinations) and run it ...
		$sql = 'SELECT session_id
			FROM ' . SESSIONS_TABLE;
		$result = $db->sql_query($sql);

		$delete_search_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$delete_search_ids[] = "'" . $db->sql_escape($row['session_id']) . "'";
		}

		if (sizeof($delete_search_ids))
		{
			$sql = 'DELETE FROM ' . SEARCH_TABLE . '
				WHERE session_id NOT IN (' . implode(", ", $delete_search_ids) . ')';
			$db->sql_query($sql);
		}

		$total_match_count = sizeof($pid_ary);
		$sql_where = (($show_results == 'posts') ? 'p.post_id' : 't.topic_id') . ' IN (' . implode(', ', $pid_ary) . ')';

		if (sizeof($search->old_split_words) && array_diff($search->split_words, $search->old_split_words))
		{
			$search->split_words = array_merge($search->split_words, $search->old_split_words);
		}

		$data = serialize($search->split_words);
		$data .= '#' . serialize($search->common_words);
		
		foreach ($store_vars as $var)
		{
			$data .= '#' . $$var;
		}
		$data .= '#' . implode('#', $pid_ary);
		
		unset($pid_ary);

		srand ((double) microtime() * 1000000);
		$search_session_id = rand();

		$sql_ary = array(
			'search_id'		=> $search_session_id,
			'session_id'	=> $user->data['session_id'],
			'search_time'	=> $current_time,
			'search_array'	=> $data
		);

		$sql = 'INSERT INTO ' . SEARCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);
		unset($data);
	}

	if ($show_results == 'posts')
	{
		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
	}
	else
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	// Look up data ...
	$per_page = ($show_results == 'posts') ? $config['posts_per_page'] : $config['topics_per_page'];

	// Grab icons
	$icons = array();
	$cache->obtain_icons($icons);

	// Output header
	$l_search_matches = ($total_match_count == 1) ? sprintf($user->lang['FOUND_SEARCH_MATCH'], $total_match_count) : sprintf($user->lang['FOUND_SEARCH_MATCHES'], $total_match_count);

	$hilit = htmlspecialchars(implode('|', str_replace(array('+', '-', '|'), '', $search->split_words)));
	$split_words = (sizeof($search->split_words)) ? htmlspecialchars(implode(' ', $search->split_words)) : '';

	$template->assign_vars(array(
		'SEARCH_MATCHES'	=> $l_search_matches,
		'SEARCH_WORDS'		=> $split_words, 
		'IGNORED_WORDS'		=> (sizeof($search->common_words)) ? htmlspecialchars(implode(' ', $search->common_words)) : '', 
		'PAGINATION'		=> generate_pagination("{$phpbb_root_path}search.$phpEx$SID&amp;search_session_id=$search_session_id&amp;search_id=$search_id&amp;hilit=$hilit&amp;$u_sort_param", $total_match_count, $per_page, $start),
		'PAGE_NUMBER'		=> on_page($total_match_count, $per_page, $start),
		'TOTAL_MATCHES'		=> $total_match_count,

		'S_SELECT_SORT_DIR'		=> $s_sort_dir,
		'S_SELECT_SORT_KEY'		=> $s_sort_key,
		'S_SELECT_SORT_DAYS'	=> $s_limit_days,
		'S_SEARCH_ACTION'		=> "{$phpbb_root_path}search.$phpEx$SID&amp;search_session_id=$search_session_id&amp;search_id=$search_id", 
		'S_SHOW_TOPICS'			=> ($show_results == 'posts') ? false : true,

		'REPORTED_IMG'			=> $user->img('icon_reported', 'TOPIC_REPORTED'),
		'UNAPPROVED_IMG'		=> $user->img('icon_unapproved', 'TOPIC_UNAPPROVED'),
		'GOTO_PAGE_IMG'			=> $user->img('icon_post', 'GOTO_PAGE'),

		'U_SEARCH_WORDS'	=> "{$phpbb_root_path}search.$phpEx$SID&amp;show_results=$show_results&amp;keywords=" . urlencode($split_words))
	);

	$u_hilit = urlencode($split_words);

	// Define ordering sql field, do it here because the order may be defined
	// within an existing search result set
	$sort_by_sql	= array('a' => (($show_results == 'posts') ? 'u.username' : 't.topic_poster'), 't' => (($show_results == 'posts') ? 'p.post_time' : 't.topic_last_post_time'), 'f' => 'f.forum_id', 'i' => 't.topic_title', 's' => (($show_results == 'posts') ? 'pt.post_subject' : 't.topic_title'));

	if ($sql_where)
	{
		if ($show_results == 'posts')
		{
			// Not joining this query to the one below at present ... may do in future
			$sql = 'SELECT zebra_id, friend, foe
				FROM ' . ZEBRA_TABLE . ' 
				WHERE user_id = ' . $user->data['user_id'];
			$result = $db->sql_query($sql);

			$zebra = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$zebra[($row['friend']) ? 'friend' : 'foe'][] = $row['zebra_id'];
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT p.*, f.forum_id, f.forum_name, t.*, u.username, u.user_sig, u.user_sig_bbcode_uid
				FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u, ' . POSTS_TABLE . " p 
				WHERE $sql_where 
					AND f.forum_id = p.forum_id
					AND p.topic_id = t.topic_id
					AND p.poster_id = u.user_id";
		}
		else
		{
			$sql = 'SELECT t.*, f.forum_id, f.forum_name
				FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f 
				WHERE $sql_where 
					AND f.forum_id = t.forum_id";
		}
		$sql .= ' ORDER BY ' . $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		$result = $db->sql_query_limit($sql, $per_page, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_id = $row['forum_id'];
			$topic_id = $row['topic_id'];

			$view_topic_url = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;hilit=$u_hilit";

			if ($show_results == 'topics')
			{
				$replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real'] : $row['topic_replies'];

				$folder_img = $folder_alt = $topic_type = '';
				topic_status($row, $replies, false, $folder_img, $folder_alt, $topic_type);

				$tpl_ary = array(
					'TOPIC_AUTHOR' 		=> topic_topic_author($row),
					'FIRST_POST_TIME' 	=> $user->format_date($row['topic_time']),
					'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
					'LAST_VIEW_TIME'	=> $user->format_date($row['topic_last_view_time']),
					'LAST_POST_AUTHOR' 	=> ($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] : $user->lang['GUEST'],
					'PAGINATION' 		=> topic_generate_pagination($replies, $view_topic_url),
					'REPLIES' 			=> $replies,
					'VIEWS' 			=> $row['topic_views'],
					'TOPIC_TYPE' 		=> $topic_type,

					'LAST_POST_IMG' 	=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'),
					'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
					'TOPIC_FOLDER_IMG_SRC' 	=> $user->img($folder_img, $folder_alt, false, '', 'src'),
					'TOPIC_ICON_IMG'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
					'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
					'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
					'ATTACH_ICON_IMG'	=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',

					'S_TOPIC_TYPE'			=> $row['topic_type'],
					'S_USER_POSTED'			=> (!empty($row['mark_type'])) ? true : false,

					'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && $auth->acl_gets('m_', $forum_id)) ? true : false,
					'S_TOPIC_UNAPPROVED'	=> (!$row['topic_approved'] && $auth->acl_gets('m_approve', $forum_id)) ? true : false,

					'U_LAST_POST'		=> $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'],
					'U_LAST_POST_AUTHOR'=> ($row['topic_last_poster_id'] != ANONYMOUS && $row['topic_last_poster_id']) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['topic_last_poster_id']}" : '',
					'U_MCP_REPORT'		=> "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}&amp;mode=reports&amp;t=$topic_id",
					'U_MCP_QUEUE'		=> "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}&amp;i=queue&amp;mode=approve_details&amp;t=$topic_id"
				);
			}
			else
			{
				if ((isset($zebra['foe']) && in_array($row['poster_id'], $zebra['foe'])) && (!$view || $view != 'show' || $post_id != $row['post_id']))
				{
					$template->assign_block_vars('searchresults', array(
						'S_IGNORE_POST' => true, 

						'L_IGNORE_POST' => sprintf($user->lang['POST_BY_FOE'], $row['username'], "<a href=\"search.$phpEx$SID&amp;search_session_id=$search_session_id&amp;$u_sort_param&amp;p=" . $row['post_id'] . '&amp;view=show#' . $row['post_id'] . '">', '</a>'))
					);
	
					continue;
				}

				if ($row['enable_html'])
				{
					$row['post_text'] = preg_replace('#(<!\-\- h \-\-><)([\/]?.*?)(><!\-\- h \-\->)#is', "&lt;\\2&gt;", $row['post_text']);
				}

				$row['post_text'] = censor_text($row['post_text']);
				decode_message($row['post_text'], $row['bbcode_uid']);
		
				if ($return_chars)
				{
					$row['post_text'] = (strlen($row['post_text']) < $return_chars + 3) ? $row['post_text'] : substr($row['post_text'], 0, $return_chars) . '...';
				}

				if ($hilit)
				{
					$row['post_text'] = preg_replace('#(?!<.*)(?<!\w)(' . $hilit . ')(?!\w|[^<>]*>)#i', '<span class="posthilit">\1</span>', $row['post_text']);
				}

				$row['post_text'] = smiley_text($row['post_text']);

				// Replace naughty words such as farty pants
				$row['post_subject'] = censor_text($row['post_subject']);
				$row['post_text'] = str_replace("\n", '<br />', censor_text($row['post_text']));

				$tpl_ary = array(
					'POSTER_NAME'		=> ($row['poster_id'] == ANONYMOUS) ? ((!empty($row['post_username'])) ? $row['post_username'] : $user->lang['GUEST']) : $row['username'], 
					'POST_SUBJECT'		=> censor_text($row['post_subject']), 
					'POST_DATE'			=> (!empty($row['post_time'])) ? $user->format_date($row['post_time']) : '', 
					'MESSAGE' 			=> $row['post_text']
				);
			}

			$template->assign_block_vars('searchresults', array_merge($tpl_ary, array(
				'FORUM_ID' 			=> $forum_id,
				'TOPIC_ID' 			=> $topic_id,
				'POST_ID'			=> ($show_results == 'posts') ? $row['post_id'] : false, 

				'FORUM_TITLE'		=> $row['forum_name'], 
				'TOPIC_TITLE' 		=> censor_text($row['topic_title']),

				'U_VIEW_TOPIC'		=> $view_topic_url,
				'U_VIEW_FORUM'		=> "viewforum.$phpEx$SID&amp;f=$forum_id", 
				'U_VIEW_POST'		=> (!empty($row['post_id'])) ? "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=" . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '&amp;hilit=' . $u_hilit . '#' . $row['post_id'] : '')
			));
		}
		$db->sql_freeresult($result);
	}
	else
	{
		$template->assign_vars(array(
			'S_NO_SEARCH_RESULTS'	=> true)
		);
	}

	page_header($user->lang['SEARCH']);

	$template->set_filenames(array(
		'body' =>  'search_results.html')
	);
	make_jumpbox('viewforum.'.$phpEx);

	page_footer();
}


// Search forum
$s_forums = '';
$sql = 'SELECT f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.left_id, f.right_id, f.forum_password, fa.user_id
	FROM ' . FORUMS_TABLE . ' f
	LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa ON  (fa.forum_id = f.forum_id
		AND fa.session_id = '" . $db->sql_escape($user->data['session_id']) . "')
	ORDER BY f.left_id ASC";
$result = $db->sql_query($sql);

$right = $cat_right = $padding_inc = 0;
$padding = $forum_list = $holding = '';
$pad_store = array('0' => '');
$search_forums = array();

while ($row = $db->sql_fetchrow($result))
{
	if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
	{
		// Non-postable forum with no subforums, don't display
		continue;
	}

	if (!$auth->acl_get('f_list', $row['forum_id']) || $row['forum_type'] == FORUM_LINK || ($row['forum_password'] && !$row['user_id']))
	{
		// if the user does not have permissions to list this forum skip
		continue;
	}

	if ($row['left_id'] < $right)
	{
		$padding .= '&nbsp; &nbsp;';
		$pad_store[$row['parent_id']] = $padding;
	}
	else if ($row['left_id'] > $right + 1)
	{
		$padding = $pad_store[$row['parent_id']];
	}

	$right = $row['right_id'];

	$selected = (!sizeof($search_forums) || in_array($row['forum_id'], $search_forums)) ? ' selected="selected"' : '';

	if ($row['left_id'] > $cat_right)
	{
		$holding = '';
	}

	if ($row['right_id'] - $row['left_id'] > 1)
	{
		$cat_right = max($cat_right, $row['right_id']);

		$holding .= '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . $row['forum_name'] . '</option>';
	}
	else
	{
		$s_forums .= $holding . '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . $row['forum_name'] . '</option>';
		$holding = '';
	}
}
$db->sql_freeresult($result);
unset($pad_store);

// Number of chars returned
$s_characters = '<option value="-1">' . $user->lang['ALL_AVAILABLE'] . '</option>';
$s_characters .= '<option value="0">0</option>';
$s_characters .= '<option value="25">25</option>';
$s_characters .= '<option value="50">50</option>';

for ($i = 100; $i <= 1000 ; $i += 100)
{
	$selected = ($i == 200) ? ' selected="selected"' : '';
	$s_characters .= '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
}

$template->assign_vars(array(
	'S_SEARCH_ACTION'		=> "{$phpbb_root_path}search.$phpEx$SID&amp;mode=results",
	'S_CHARACTER_OPTIONS'	=> $s_characters,
	'S_FORUM_OPTIONS'		=> $s_forums,
	'S_SELECT_SORT_DIR'		=> $s_sort_dir,
	'S_SELECT_SORT_KEY'		=> $s_sort_key,
	'S_SELECT_SORT_DAYS'	=> $s_limit_days)
);

$sql = 'SELECT search_id, search_time, search_array
	FROM ' . SEARCH_TABLE . '
	ORDER BY search_time DESC';
$result = $db->sql_query($sql);

$i = 0;
while ($row = $db->sql_fetchrow($result))
{
	if ($i == 5)
	{
		break;
	}

	$data = explode('#', $row['search_array']);
	$split_words = htmlspecialchars(implode(' ', unserialize(array_shift($data))));

	if (!$split_words)
	{
		continue;
	}

	$common_words = htmlspecialchars(implode(' ', unserialize(array_shift($data))));
	unset($data);

	$template->assign_block_vars('recentsearch', array(
		'KEYWORDS'	=> $split_words,
		'TIME'		=> $user->format_date($row['search_time']),

		'U_KEYWORDS'	=> "{$phpbb_root_path}search.$phpEx$SID&amp;keywords=" . urlencode($split_words))
	);

	$i++;
}
$db->sql_freeresult($result);

// Output the basic page
page_header($user->lang['SEARCH']);

$template->set_filenames(array(
	'body' => 'search_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

page_footer();

?>