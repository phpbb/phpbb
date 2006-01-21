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
$mode			= request_var('mode', '');
$search_id		= request_var('search_id', '');
$start			= max(request_var('start', 0), 0);
$post_id		= request_var('p', 0);
$topic_id		= request_var('t', 0);
$view			= request_var('view', '');

$keywords		= request_var('keywords', '');
$add_keywords	= request_var('add_keywords', '');
$author			= request_var('author', '');
$show_results	= ($topic_id) ? 'posts' : request_var('sr', 'topics');
$search_terms	= request_var('terms', 'all');
$search_fields	= request_var('sf', 'all');
$search_child	= request_var('sc', true);

$sort_days		= request_var('st', 0);
$sort_key		= request_var('sk', 't');
$sort_dir		= request_var('sd', 'd');

$return_chars	= request_var('ch', ($topic_id) ? -1 : 200);
$search_forum	= request_var('fid', array(0));

if ($search_forum == array(0))
{
	$search_forum = array();
}

// Is user able to search? Has search been disabled?
if (!$auth->acl_get('u_search') || !$config['load_search'])
{
	trigger_error($user->lang['NO_SEARCH']);
}

// Check search load limit
if ($user->load && $config['limit_search_load'] && ($user->load > doubleval($config['limit_search_load'])))
{
	trigger_error($user->lang['NO_SEARCH_TIME']);
}

// Check last search time ... if applicable
if ($config['search_interval'])
{
	if ($config['last_search_time'] > time() - $config['search_interval'])
	{
		trigger_error($user->lang['NO_SEARCH_TIME']);
	}
}

// Define some vars
$limit_days		= array(0 => $user->lang['ALL_RESULTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
$sort_by_text	= array('a' => $user->lang['SORT_AUTHOR'], 't' => $user->lang['SORT_TIME'], 'f' => $user->lang['SORT_FORUM'], 'i' => $user->lang['SORT_TOPIC_TITLE'], 's' => $user->lang['SORT_POST_SUBJECT']);

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

if ($keywords || $author || $search_id)
{
	// clear arrays
	$id_ary = array();

	// Which forums should not be searched?
	$ex_fid_ary = array_keys($auth->acl_getf('!f_read', true));

	$not_in_fid = (sizeof($ex_fid_ary)) ? 'f.forum_id NOT IN (' . implode(', ', $ex_fid_ary) . ') OR ' : '';
	$sql = 'SELECT f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.right_id, f.forum_password, fa.user_id
		FROM ' . FORUMS_TABLE . ' f
		LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa ON  (fa.forum_id = f.forum_id
			AND fa.session_id = '" . $db->sql_escape($user->data['session_id']) . "')
		WHERE $not_in_fid(f.forum_password <> '' AND fa.user_id <> " . (int) $user->data['user_id'] . ')
		ORDER BY f.left_id';
	$result = $db->sql_query($sql);

	$right_id = 0;
	$reset_search_forum = true;
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['forum_password'] && ($row['user_id'] != $user->data['user_id']))
		{
			$ex_fid_ary[] = $row['forum_id'];
			continue;
		}

		if (sizeof($search_forum))
		{
			if ($search_child)
			{
				if (in_array($row['forum_id'], $search_forum) && $row['right_id'] > $right_id)
				{
					$right_id = $row['right_id'];
				}
				else if ($row['right_id'] < $right_id)
				{
					continue;
				}
			}

			if (!in_array($row['forum_id'], $search_forum))
			{
				$ex_fid_ary[] = $row['forum_id'];
				$reset_search_forum = false;
			}
		}
	}
	$db->sql_freeresult($result);

	if ($reset_search_forum)
	{
		$search_forum = array();
	}

	// egosearch is an author search
	if ($search_id == 'egosearch')
	{
		$author = $user->data['username'];
	}

	// If we are looking for authors get their ids
	$author_id_ary = array();
	if ($author)
	{
		if ((strstr($author, '*') !== false) && (str_replace(array('*', '%'), '', $author) < $config['min_search_author_chars']))
		{
			trigger_error(sprintf($user->lang['TOO_FEW_AUTHOR_CHARS'], $config['min_search_author_chars']));
		}

		$sql_where = (strstr($author, '*') !== false) ? ' LIKE ' : ' = ';
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
			WHERE username $sql_where '" . $db->sql_escape(preg_replace('#\*+#', '%', $author)) . "'
				AND user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		$result = $db->sql_query_limit($sql, 100);

		while ($row = $db->sql_fetchrow($result))
		{
			$author_id_ary[] = (int) $row['user_id'];
		}

		$db->sql_freeresult($result);

		if (!sizeof($author_id_ary))
		{
			trigger_error($user->lang['NO_SEARCH_RESULTS']);
		}
	}

	// if we search in an existing search result just add the additional keywords. But we need to use "all search terms"-mode
	// so we can keep the old keywords in their old mode, but add the new ones as required words
	if ($add_keywords)
	{
		if ($search_terms == 'all')
		{
			$keywords .= ' ' . $add_keywords;
		}
		else
		{
			$search_terms = 'all';
			$keywords = implode(' |', explode(' ', preg_replace('#\s+#', ' ', $keywords))) . ' ' .$add_keywords;
		}
	}

	// Select which method we'll use to obtain the post_id or topic_id information
	$search_type = $config['search_type'];

	if (!file_exists($phpbb_root_path . 'includes/search/' . $search_type . '.' . $phpEx))
	{
		trigger_error('NO_SUCH_SEARCH_MODULE');
	}

	require("{$phpbb_root_path}includes/search/$search_type.$phpEx");

	// We do some additional checks in the module to ensure it can actually be utilised
	$error = false;
	$search = new $search_type($error);

	if ($error)
	{
		trigger_error($error);
	}

	// let the search module split up the keywords
	if ($keywords)
	{
		$search->split_keywords($keywords, $search_terms);
		if (!sizeof($search->split_words) && !sizeof($author_id_ary) && !$search_id)
		{
			trigger_error(sprintf($user->lang['NO_KEYWORDS'], $config['min_search_chars'], $config['max_search_chars']));
		}
	}

	// define some variables needed for retrieving post_id/topic_id information
	$per_page = ($show_results == 'posts') ? $config['posts_per_page'] : $config['topics_per_page'];
	$sort_by_sql = array('a' => (($show_results == 'posts') ? 'u.username' : 't.topic_poster'), 't' => (($show_results == 'posts') ? 'p.post_time' : 't.topic_last_post_time'), 'f' => 'f.forum_id', 'i' => 't.topic_title', 's' => (($show_results == 'posts') ? 'p.post_subject' : 't.topic_title'));

	// pre-made searches
	$sql = $field = '';
	if ($search_id)
	{
		// Build sql string for sorting
		$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

		switch ($search_id)
		{
			// Oh holy Bob, bring us some activity...
			case 'active_topics':
				$show_results = 'topics';
				$sort_key = 't';
				$sort_dir = 'd';
				$sort_by_sql['t'] = 't.topic_last_post_time';

				if (!$sort_days)
				{
					$sort_days = 1;
				}
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				$s_sort_key = $s_sort_dir = $u_sort_param = '';

				$last_post_time = (time() - ($sort_days * 24 * 3600));

				$sql = 'SELECT DISTINCT t.topic_id
					FROM ' . POSTS_TABLE . ' p
					LEFT JOIN ' . TOPICS_TABLE . " t ON (t.topic_approved = 1 AND p.topic_id = t.topic_id)
					WHERE p.post_time > $last_post_time
						" . ((sizeof($ex_fid_ary)) ? ' AND p.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ')' : '') . '
					ORDER BY t.topic_last_post_time DESC';
				$field = 'topic_id';
				break;

			case 'unanswered':
				$sort_join = ($sort_key == 'f') ? FORUMS_TABLE . ' f, ' : '';
				$sql_sort = ($sort_key == 'f') ? ' AND f.forum_id = p.forum_id ' . $sql_sort : $sql_sort;
				if ($show_results == 'posts')
				{
					if ($sort_key == 'a')
					{
						$sort_join = USERS_TABLE . ' u, ';
						$sql_sort = ' AND u.user_id = p.poster_id ' . $sql_sort;
					}
					$sql = "SELECT p.post_id
						FROM $sort_join" . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
						WHERE t.topic_replies = 0
							AND p.topic_id = t.topic_id
							" . ((sizeof($ex_fid_ary)) ? ' AND p.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ')' : '') . "
							$sql_sort";
					$field = 'post_id';
				}
				else
				{
					$sql = "SELECT DISTINCT p.topic_id
						FROM $sort_join" . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
						WHERE t.topic_replies = 0
							AND p.topic_id = t.topic_id
							" . ((sizeof($ex_fid_ary)) ? ' AND p.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ')' : '') . "
						$sql_sort";
					$field = 'topic_id';
				}
				break;

			case 'newposts':
				$sort_join = ($sort_key == 'f') ? FORUMS_TABLE . ' f, ' : '';
				$sql_sort = ($sort_key == 'f') ? ' AND f.forum_id = p.forum_id ' . $sql_sort : $sql_sort;
				if ($show_results == 'posts')
				{
					if ($sort_key == 'i')
					{
						$sort_join = TOPICS_TABLE . ' t, ';
						$sql_sort = ' AND t.topic_id = p.topic_id ' . $sql_sort;
					}
					elseif ($sort_key == 'a')
					{
						$sort_join = USERS_TABLE . ' u, ';
						$sql_sort = ' AND u.user_id = p.poster_id ' . $sql_sort;
					}

					$sql = "SELECT p.post_id
						FROM $sort_join" . POSTS_TABLE . ' p
						WHERE p.post_time > ' . $user->data['user_lastvisit'] . "
							" . ((sizeof($ex_fid_ary)) ? ' AND p.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ')' : '') . "
						$sql_sort";
					$field = 'post_id';
				}
				else
				{
					$sql = "SELECT DISTINCT p.topic_id
						FROM $sort_join" . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
						WHERE p.post_time > ' . $user->data['user_lastvisit'] . "
							AND t.topic_id = p.topic_id
							" . ((sizeof($ex_fid_ary)) ? ' AND p.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ')' : '') . "
						$sql_sort";
					$field = 'topic_id';
				}
				break;
		}

		if ($sql)
		{
			// only return up to 1000 ids (the last one will be removed later)
			$result = $db->sql_query_limit($sql, 1001 - $start, $start);

			while ($row = $db->sql_fetchrow($result))
			{
				$id_ary[] = $row[$field];
			}
			$db->sql_freeresult($result);

			$total_match_count = sizeof($id_ary) + $start;
			$id_ary = array_slice($id_ary, 0, $per_page);
		}
		else
		{
			$search_id = '';
		}
	}

	if (sizeof($search->split_words))
	{
		$total_match_count = $search->keyword_search($show_results, $search_fields, $search_terms, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $topic_id, $author_id_ary, $id_ary, $start, $per_page);
	}
	else if (sizeof($author_id_ary))
	{
		// default to showing results as posts when performing an author search
		$show_results = ($topic_id) ? 'posts' : request_var('sr', 'posts');

		$total_match_count = $search->author_search($show_results, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $topic_id, $author_id_ary, $id_ary, $start, $per_page);
	}

	if (!sizeof($id_ary))
	{
		trigger_error($user->lang['NO_SEARCH_RESULTS']);
	}

	$sql_where = (($show_results == 'posts') ? 'p.post_id' : 't.topic_id') . ' IN (' . implode(', ', $id_ary) . ')';
	$sql_where .= (sizeof($ex_fid_ary)) ? ' AND (f.forum_id NOT IN (' . implode(',', $ex_fid_ary) . ') OR f.forum_id IS NULL)' : '';

	if ($show_results == 'posts')
	{
		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
	}
	else
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	// Grab icons
	$icons = array();
	$cache->obtain_icons($icons);

	// Output header
	if ($search_id && ($total_match_count > 1000))
	{
		// limit the number to 1000 for pre-made searches
		$total_match_count--;
		$l_search_matches = sprintf($user->lang['FOUND_MORE_SEARCH_MATCHES'], $total_match_count);
	}
	else
	{
		$l_search_matches = ($total_match_count == 1) ? sprintf($user->lang['FOUND_SEARCH_MATCH'], $total_match_count) : sprintf($user->lang['FOUND_SEARCH_MATCHES'], $total_match_count);
	}

	// define some vars for urls
	$hilit = htmlspecialchars(implode('|', str_replace(array('+', '-', '|'), '', $search->split_words)));
	$split_words = (sizeof($search->split_words)) ? htmlspecialchars(implode(' ', $search->split_words)) : '';
	$u_hilit = urlencode($split_words);
	$u_show_results = ($show_results != 'topic') ? '&amp;sr=' . $show_results : '';
	$u_search_forum = implode('&amp;fid%5B%5D=', $search_forum);

	$u_search = "{$phpbb_root_path}search.$phpEx$SID";
	$u_search .= ($search_id) ? '&amp;search_id=' . $search_id : '';
	$u_search .= ($u_hilit) ? '&amp;keywords=' . $u_hilit : '';
	$u_search .= ($topic_id) ? '&amp;ch=' . $topic_id : '';
	$u_search .= ($author) ? '&amp;author=' . urlencode($author) : '';
	$u_search .= ($u_search_forum) ? '&amp;fid%5B%5D=' . $u_search_forum : '';
	$u_search .= (!$search_child) ? '&amp;sc=0' : '';
	$u_search .= ($search_fields != 'all') ? '&amp;sf=' . $search_fields : '';
	$u_search .= '&amp;' . $u_sort_param . $u_show_results;
	$u_search .= ($return_chars != 200) ? '&amp;ch=' . $return_chars : '';


	$template->assign_vars(array(
		'SEARCH_MATCHES'	=> $l_search_matches,
		'SEARCH_WORDS'		=> $split_words,
		'IGNORED_WORDS'		=> (sizeof($search->common_words)) ? htmlspecialchars(implode(' ', $search->common_words)) : '',
		'PAGINATION'		=> generate_pagination($u_search, $total_match_count, $per_page, $start),
		'PAGE_NUMBER'		=> on_page($total_match_count, $per_page, $start),
		'TOTAL_MATCHES'		=> $total_match_count,
		'SEARCH_IN_RESULTS'	=> ($search_id) ? false : true,

		'S_SELECT_SORT_DIR'		=> $s_sort_dir,
		'S_SELECT_SORT_KEY'		=> $s_sort_key,
		'S_SELECT_SORT_DAYS'	=> $s_limit_days,
		'S_SEARCH_ACTION'		=> $u_search,
		'S_SHOW_TOPICS'			=> ($show_results == 'posts') ? false : true,

		'REPORTED_IMG'			=> $user->img('icon_reported', 'TOPIC_REPORTED'),
		'UNAPPROVED_IMG'		=> $user->img('icon_unapproved', 'TOPIC_UNAPPROVED'),
		'GOTO_PAGE_IMG'			=> $user->img('icon_post', 'GOTO_PAGE'),

		'U_SEARCH_WORDS'	=> "{$phpbb_root_path}search.$phpEx$SID$u_show_results&amp;keywords=$u_hilit")
	);

	if ($sql_where)
	{
		if ($show_results == 'posts')
		{
			/**
			* @todo Joining this query to the one below?
			*/
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
				FROM (' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p)
				LEFT JOIN ' . FORUMS_TABLE . " f ON (p.forum_id = f.forum_id)
				WHERE $sql_where
					AND p.topic_id = t.topic_id
					AND p.poster_id = u.user_id";
		}
		else
		{
			$sql = 'SELECT t.*, f.forum_id, f.forum_name
				FROM ' . TOPICS_TABLE . ' t
				LEFT JOIN ' . FORUMS_TABLE . " f ON (f.forum_id = t.forum_id)
				WHERE $sql_where";
		}
		$sql .= ' ORDER BY ' . $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		$result = $db->sql_query($sql);
		$result_topic_id = 0;

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_id = $row['forum_id'];
			$result_topic_id = $row['topic_id'];
			$topic_title = censor_text($row['topic_title']);

			$view_topic_url = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$result_topic_id&amp;hilit=$u_hilit";

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

					'S_TOPIC_GLOBAL'		=> (!$forum_id) ? true : false,
					'S_TOPIC_TYPE'			=> $row['topic_type'],
					'S_USER_POSTED'			=> (!empty($row['mark_type'])) ? true : false,

					'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && $auth->acl_gets('m_', $forum_id)) ? true : false,
					'S_TOPIC_UNAPPROVED'	=> (!$row['topic_approved'] && $auth->acl_gets('m_approve', $forum_id)) ? true : false,

					'U_LAST_POST'		=> $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'],
					'U_LAST_POST_AUTHOR'=> ($row['topic_last_poster_id'] != ANONYMOUS && $row['topic_last_poster_id']) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['topic_last_poster_id']}" : '',
					'U_MCP_REPORT'		=> "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}&amp;mode=reports&amp;t=$result_topic_id",
					'U_MCP_QUEUE'		=> "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}&amp;i=queue&amp;mode=approve_details&amp;t=$result_topic_id"
				);
			}
			else
			{
				if ((isset($zebra['foe']) && in_array($row['poster_id'], $zebra['foe'])) && (!$view || $view != 'show' || $post_id != $row['post_id']))
				{
					$template->assign_block_vars('searchresults', array(
						'S_IGNORE_POST' => true,

						'L_IGNORE_POST' => sprintf($user->lang['POST_BY_FOE'], $row['username'], "<a href=\"$u_search&amp;p=" . $row['post_id'] . '&amp;view=show#' . $row['post_id'] . '">', '</a>'))
					);

					continue;
				}

				if ($row['enable_html'])
				{
					$row['post_text'] = preg_replace('#(<!\-\- h \-\-><)([\/]?.*?)(><!\-\- h \-\->)#is', "&lt;\\2&gt;", $row['post_text']);
				}

				decode_message($row['post_text'], $row['bbcode_uid']);

				if ($return_chars != -1)
				{
					$row['post_text'] = (strlen($row['post_text']) < $return_chars + 3) ? $row['post_text'] : substr($row['post_text'], 0, $return_chars) . '...';
				}

				// Replace naughty words such as farty pants
				$row['post_subject'] = censor_text($row['post_subject']);
				$row['post_text'] = str_replace("\n", '<br />', censor_text($row['post_text']));

				if ($hilit)
				{
					$row['post_text'] = preg_replace('#(?!<.*)(?<!\w)(' . preg_quote($hilit) . ')(?!\w|[^<>]*>)#i', '<span class="posthilit">$1</span>', $row['post_text']);
				}

				$row['post_text'] = smiley_text($row['post_text']);

				$tpl_ary = array(
					'POSTER_NAME'		=> ($row['poster_id'] == ANONYMOUS) ? ((!empty($row['post_username'])) ? $row['post_username'] : $user->lang['GUEST']) : $row['username'],
					'U_PROFILE'			=> ($row['poster_id'] != ANONYMOUS) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['poster_id']}" : '',
					'POST_SUBJECT'		=> $row['post_subject'],
					'POST_DATE'			=> (!empty($row['post_time'])) ? $user->format_date($row['post_time']) : '',
					'MESSAGE' 			=> $row['post_text']
				);
			}

			$template->assign_block_vars('searchresults', array_merge($tpl_ary, array(
				'FORUM_ID' 			=> $forum_id,
				'TOPIC_ID' 			=> $result_topic_id,
				'POST_ID'			=> ($show_results == 'posts') ? $row['post_id'] : false,

				'FORUM_TITLE'		=> $row['forum_name'],
				'TOPIC_TITLE' 		=> $topic_title,

				'U_VIEW_TOPIC'		=> $view_topic_url,
				'U_VIEW_FORUM'		=> "viewforum.$phpEx$SID&amp;f=$forum_id",
				'U_VIEW_POST'		=> (!empty($row['post_id'])) ? "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=" . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '&amp;hilit=' . $u_hilit . '#' . $row['post_id'] : '')
			));
		}
		$db->sql_freeresult($result);

		if ($topic_id && ($topic_id == $result_topic_id))
		{
			$template->assign_vars(array(
				'SEARCH_TOPIC'		=> $topic_title,
				'U_SEARCH_TOPIC'	=> $view_topic_url
			));
		}
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

	$selected = (!sizeof($search_forum) || in_array($row['forum_id'], $search_forum)) ? ' selected="selected"' : '';

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
	'S_SEARCH_ACTION'		=> "{$phpbb_root_path}search.$phpEx",
	'S_HIDDEN_FIELDS'		=> build_hidden_fields(array('sid' => $user->session_id)),
	'S_CHARACTER_OPTIONS'	=> $s_characters,
	'S_FORUM_OPTIONS'		=> $s_forums,
	'S_SELECT_SORT_DIR'		=> $s_sort_dir,
	'S_SELECT_SORT_KEY'		=> $s_sort_key,
	'S_SELECT_SORT_DAYS'	=> $s_limit_days)
);

$sql = 'SELECT search_time, search_keywords
	FROM ' . SEARCH_TABLE . '
	WHERE search_keywords <> \'\'
	ORDER BY search_time DESC';
$result = $db->sql_query_limit($sql, 5);

while ($row = $db->sql_fetchrow($result))
{
	$keywords = htmlspecialchars($row['search_keywords']);

	$template->assign_block_vars('recentsearch', array(
		'KEYWORDS'	=> $keywords,
		'TIME'		=> $user->format_date($row['search_time']),

		'U_KEYWORDS'	=> "{$phpbb_root_path}search.$phpEx$SID&amp;keywords=" . urlencode($keywords))
	);
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