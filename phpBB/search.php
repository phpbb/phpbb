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
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

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

$submit			= request_var('submit', false);
$keywords		= request_var('keywords', '', true);
$add_keywords	= request_var('add_keywords', '', true);
$author			= request_var('author', '');
$author_id		= request_var('author_id', 0);
$show_results	= ($topic_id) ? 'posts' : request_var('sr', 'posts');
$show_results	= ($show_results == 'posts') ? 'posts' : 'topics';
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
if (!$auth->acl_get('u_search') || !$auth->acl_getf_global('f_search') || !$config['load_search'])
{
	trigger_error($user->lang['NO_SEARCH']);
}

// Check search load limit
if ($user->load && $config['limit_search_load'] && ($user->load > doubleval($config['limit_search_load'])))
{
	trigger_error($user->lang['NO_SEARCH_TIME']);
}

// Check flood limit ... if applicable
$interval = ($user->data['user_id'] == ANONYMOUS) ? $config['search_anonymous_interval'] : $config['search_interval'];
if ($interval && !$auth->acl_get('u_ignoreflood'))
{
	if ($user->data['user_last_search'] > time() - $interval)
	{
		trigger_error($user->lang['NO_SEARCH_TIME']);
	}
}

// Define some vars
$limit_days		= array(0 => $user->lang['ALL_RESULTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
$sort_by_text	= array('a' => $user->lang['SORT_AUTHOR'], 't' => $user->lang['SORT_TIME'], 'f' => $user->lang['SORT_FORUM'], 'i' => $user->lang['SORT_TOPIC_TITLE'], 's' => $user->lang['SORT_POST_SUBJECT']);

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

if ($keywords || $author || $author_id || $search_id || $submit)
{
	// clear arrays
	$id_ary = array();

	// Which forums should not be searched?
	$ex_fid_ary = array_unique(array_merge(array_keys($auth->acl_getf('!f_read', true)), array_keys($auth->acl_getf('!f_search', true))));

	$not_in_fid = (sizeof($ex_fid_ary)) ? 'WHERE ' . $db->sql_in_set('f.forum_id', $ex_fid_ary, true) . " OR (f.forum_password <> '' AND fa.user_id <> " . (int) $user->data['user_id'] . ')' : "";

	$sql = 'SELECT f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.right_id, f.forum_password, fa.user_id
		FROM ' . FORUMS_TABLE . ' f
		LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa ON  (fa.forum_id = f.forum_id
			AND fa.session_id = '" . $db->sql_escape($user->session_id) . "')
		$not_in_fid
		ORDER BY f.left_id";
	$result = $db->sql_query($sql);

	$right_id = 0;
	$reset_search_forum = true;
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['forum_password'] && ($row['user_id'] != $user->data['user_id']))
		{
			$ex_fid_ary[] = (int) $row['forum_id'];
			continue;
		}

		if (sizeof($search_forum))
		{
			if ($search_child)
			{
				if (in_array($row['forum_id'], $search_forum) && $row['right_id'] > $right_id)
				{
					$right_id = (int) $row['right_id'];
				}
				else if ($row['right_id'] < $right_id)
				{
					continue;
				}
			}

			if (!in_array($row['forum_id'], $search_forum))
			{
				$ex_fid_ary[] = (int) $row['forum_id'];
				$reset_search_forum = false;
			}
		}
	}
	$db->sql_freeresult($result);

	// find out in which forums the user is allowed to view approved posts
	if ($auth->acl_get('m_approve'))
	{
		$m_approve_fid_ary = array(-1);
		$m_approve_fid_sql = '';
	}
	else if ($auth->acl_getf_global('m_approve'))
	{
		$m_approve_fid_ary = array_diff(array_keys($auth->acl_getf('!m_approve', true)), $ex_fid_ary);
		$m_approve_fid_sql = ' AND (p.post_approved = 1' . ((sizeof($m_approve_fid_ary)) ? ' OR ' . $db->sql_in_set('p.forum_id', $m_approve_fid_ary, true) : '') . ')';
	}
	else
	{
		$m_approve_fid_ary = array();
		$m_approve_fid_sql = ' AND p.post_approved = 1';
	}

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
	if ($author_id)
	{
		$author_id_ary[] = $author_id;
	}
	else if ($author)
	{
		if ((strpos($author, '*') !== false) && (str_replace(array('*', '%'), '', $author) < $config['min_search_author_chars']))
		{
			trigger_error(sprintf($user->lang['TOO_FEW_AUTHOR_CHARS'], $config['min_search_author_chars']));
		}

		$sql_where = (strpos($author, '*') !== false) ? ' LIKE ' : ' = ';
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
	$search_type = basename($config['search_type']);

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
		if (empty($search->search_query) && !sizeof($author_id_ary) && !$search_id)
		{
			$ignored = (sizeof($search->common_words)) ? sprintf($user->lang['IGNORED_TERMS_EXPLAIN'], htmlspecialchars(implode(' ', $search->common_words))) . '<br />' : '';
			trigger_error($ignored . sprintf($user->lang['NO_KEYWORDS'], $search->word_length['min'], $search->word_length['max']));
		}
	}

	if (!$keywords && sizeof($author_id_ary))
	{
		// if it is an author search we want to show topics by default
		$show_results = ($topic_id) ? 'posts' : request_var('sr', ($search_id == 'egosearch') ? 'topics' : 'posts');
		$show_results = ($show_results == 'posts') ? 'posts' : 'topics';
	}

	// define some variables needed for retrieving post_id/topic_id information
	$sort_by_sql = array('a' => 'u.username', 't' => (($show_results == 'posts') ? 'p.post_time' : 't.topic_last_post_time'), 'f' => 'f.forum_id', 'i' => 't.topic_title', 's' => (($show_results == 'posts') ? 'p.post_subject' : 't.topic_title'));

	// pre-made searches
	$sql = $field = '';
	if ($search_id)
	{
		switch ($search_id)
		{
			// Oh holy Bob, bring us some activity...
			case 'active_topics':
				$show_results = 'topics';
				$sort_key = 't';
				$sort_dir = 'd';
				$sort_by_sql['t'] = 't.topic_last_post_time';
				$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

				if (!$sort_days)
				{
					$sort_days = 1;
				}
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				$s_sort_key = $s_sort_dir = $u_sort_param = '';

				$last_post_time = (time() - ($sort_days * 24 * 3600));

				$sql = 'SELECT DISTINCT t.topic_last_post_time, t.topic_id
					FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
					WHERE p.post_time > $last_post_time
						AND t.topic_moved_id = 0
						AND p.topic_id = t.topic_id
						$m_approve_fid_sql
						" . ((sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '') . '
					ORDER BY t.topic_last_post_time DESC';
				$field = 'topic_id';
			break;

			case 'unanswered':
				$show_results = request_var('sr', 'topics');
				$show_results = ($show_results == 'posts') ? 'posts' : 'topics';
				$sort_by_sql['t'] = ($show_results == 'posts') ? 'p.post_time' : 't.topic_last_post_time';
				$sort_by_sql['s'] = ($show_results == 'posts') ? 'p.post_subject' : 't.topic_title';
				$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

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
							$m_approve_fid_sql
							" . ((sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '') . "
							$sql_sort";
					$field = 'post_id';
				}
				else
				{
					$sql = 'SELECT DISTINCT ' . $sort_by_sql[$sort_key] . ", p.topic_id
						FROM $sort_join" . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
						WHERE t.topic_replies = 0
							AND t.topic_moved_id = 0
							AND p.topic_id = t.topic_id
							$m_approve_fid_sql
							" . ((sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '') . "
						$sql_sort";
					$field = 'topic_id';
				}
			break;

			case 'newposts':
				// force sorting
				$show_results = (request_var('sr', 'topics') == 'posts') ? 'posts' : 'topics';
				$sort_key = 't';
				$sort_dir = 'd';
				$sort_by_sql['t'] = ($show_results == 'posts') ? 'p.post_time' : 't.topic_last_post_time';
				$sql_sort = 'ORDER BY ' . $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

				if (!$sort_days)
				{
					$sort_days = 1;
				}
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
				$s_sort_key = $s_sort_dir = $u_sort_param = $s_limit_days = '';

				if ($show_results == 'posts')
				{
					$sql = 'SELECT p.post_id
						FROM ' . POSTS_TABLE . ' p
						WHERE p.post_time > ' . $user->data['user_lastvisit'] . "
							$m_approve_fid_sql
							" . ((sizeof($ex_fid_ary)) ? ' AND ' . $db->sql_in_set('p.forum_id', $ex_fid_ary, true) : '') . "
						$sql_sort";
					$field = 'post_id';
				}
				else
				{
					$sql = 'SELECT t.topic_id
						FROM ' . TOPICS_TABLE . ' t
						WHERE t.topic_last_post_time > ' . $user->data['user_lastvisit'] . '
							AND t.topic_moved_id = 0
							' . str_replace(array('p.', 'post_'), array('t.', 'topic_'), $m_approve_fid_sql) . '
							' . ((sizeof($ex_fid_ary)) ? 'AND ' . $db->sql_in_set('t.forum_id', $ex_fid_ary, true) : '') . "
						$sql_sort";
					$field = 'topic_id';
				}
			break;
		}
	}

	// show_results should not change after this
	$per_page = ($show_results == 'posts') ? $config['posts_per_page'] : $config['topics_per_page'];

	if ($search_id)
	{
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

	// make sure that some arrays are always in the same order
	sort($ex_fid_ary);
	sort($m_approve_fid_ary);
	sort($author_id_ary);

	if (!empty($search->search_query))
	{
		$total_match_count = $search->keyword_search($show_results, $search_fields, $search_terms, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $m_approve_fid_ary, $topic_id, $author_id_ary, $id_ary, $start, $per_page);
	}
	else if (sizeof($author_id_ary))
	{
		$total_match_count = $search->author_search($show_results, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $m_approve_fid_ary, $topic_id, $author_id_ary, $id_ary, $start, $per_page);
	}

	if (!sizeof($id_ary))
	{
		trigger_error($user->lang['NO_SEARCH_RESULTS']);
	}

	$sql_where = $db->sql_in_set(($show_results == 'posts') ? 'p.post_id' : 't.topic_id', $id_ary);
	$sql_where .= (sizeof($ex_fid_ary)) ? ' AND (' . $db->sql_in_set('f.forum_id', $ex_fid_ary, true) . ' OR f.forum_id IS NULL)' : '';
	$sql_where .= ($show_results == 'posts') ? $m_approve_fid_sql : str_replace(array('p.post_approved', 'p.forum_id'), array('t.topic_approved', 't.forum_id'), $m_approve_fid_sql);

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
	$hilit = preg_replace('#&amp;(\#[0-9]+;)#', '&$1', htmlspecialchars(implode('|', explode(' ', preg_replace('#\s+#', ' ', str_replace(array('+', '-', '|', '(', ')'), ' ', $keywords))))));
	$u_hilit = urlencode($keywords);
	$u_show_results = ($show_results != 'posts') ? '&amp;sr=' . $show_results : '';
	$u_search_forum = implode('&amp;fid%5B%5D=', $search_forum);

	$u_search = append_sid("{$phpbb_root_path}search.$phpEx", $u_sort_param . $u_show_results);
	$u_search .= ($search_id) ? '&amp;search_id=' . $search_id : '';
	$u_search .= ($u_hilit) ? '&amp;keywords=' . $u_hilit : '';
	$u_search .= ($topic_id) ? '&amp;t=' . $topic_id : '';
	$u_search .= ($author) ? '&amp;author=' . urlencode($author) : '';
	$u_search .= ($author_id) ? '&amp;author_id=' . $author_id : '';
	$u_search .= ($u_search_forum) ? '&amp;fid%5B%5D=' . $u_search_forum : '';
	$u_search .= (!$search_child) ? '&amp;sc=0' : '';
	$u_search .= ($search_fields != 'all') ? '&amp;sf=' . $search_fields : '';
	$u_search .= ($return_chars != 200) ? '&amp;ch=' . $return_chars : '';

	$template->assign_vars(array(
		'SEARCH_MATCHES'	=> $l_search_matches,
		'SEARCH_WORDS'		=> preg_replace('#&amp;(\#[0-9]+;)#', '&$1', htmlspecialchars($search->search_query)),
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

		'GOTO_PAGE_IMG'		=> $user->img('icon_post_target', 'GOTO_PAGE'),
		'NEWEST_POST_IMG'	=> $user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
		'REPORTED_IMG'		=> $user->img('icon_topic_reported', 'TOPIC_REPORTED'),
		'UNAPPROVED_IMG'	=> $user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),

		'U_SEARCH_WORDS'	=> $u_search)
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
				FROM ' . POSTS_TABLE . ' p
					LEFT JOIN ' . TOPICS_TABLE . ' t ON (p.topic_id = t.topic_id)
					LEFT JOIN ' . FORUMS_TABLE . ' f ON (p.forum_id = f.forum_id)
					LEFT JOIN ' . USERS_TABLE . " u ON (p.poster_id = u.user_id)
				WHERE $sql_where";
		}
		else
		{
			$sql_from = TOPICS_TABLE . ' t
				LEFT JOIN ' . FORUMS_TABLE . ' f ON (f.forum_id = t.forum_id)
				' . (($sort_key == 'a') ? ' LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = t.topic_poster) ' : '');
			$sql_select = 't.*, f.forum_id, f.forum_name';

			if ($user->data['is_registered'])
			{
				if ($config['load_db_track'])
				{
					$sql_from .= ' LEFT JOIN ' . TOPICS_POSTED_TABLE . ' tp ON (tp.user_id = ' . $user->data['user_id'] . '
						AND t.topic_id = tp.topic_id)';
					$sql_select .= ', tp.topic_posted';
				}

				if ($config['load_db_lastread'])
				{
					$sql_from .= ' LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.user_id = ' . $user->data['user_id'] . '
							AND t.topic_id = tt.topic_id)
						LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . '
							AND ft.forum_id = f.forum_id)';
					$sql_select .= ', tt.mark_time, ft.mark_time as f_mark_time';
				}
			}

			if ($config['load_anon_lastread'] || ($user->data['is_registered'] && !$config['load_db_lastread']))
			{
				$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? ((STRIP) ? stripslashes($_COOKIE[$config['cookie_name'] . '_track']) : $_COOKIE[$config['cookie_name'] . '_track']) : '';
				$tracking_topics = ($tracking_topics) ? unserialize($tracking_topics) : array();
			}

			$sql = "SELECT $sql_select
				FROM $sql_from
				WHERE $sql_where";
		}
		$sql .= ' ORDER BY ' . $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		$result = $db->sql_query($sql);
		$result_topic_id = 0;

		$rowset = array();

		if ($show_results == 'topics')
		{
			$forums = $rowset = $shadow_topic_list = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['topic_status'] == ITEM_MOVED)
				{
					$shadow_topic_list[$row['topic_moved_id']] = $row['topic_id'];
				}

				$rowset[$row['topic_id']] = $row;

				if (!isset($forums[$row['forum_id']]) && $user->data['is_registered'] && $config['load_db_lastread'])
				{
					$forums[$row['forum_id']]['mark_time'] = $row['f_mark_time'];
				}
				$forums[$row['forum_id']]['topic_list'][] = $row['topic_id'];
				$forums[$row['forum_id']]['rowset'][$row['topic_id']] = &$rowset[$row['topic_id']];
			}
			$db->sql_freeresult($result);
 
			// If we have some shadow topics, update the rowset to reflect their topic informations
			if (sizeof($shadow_topic_list))
			{
				$sql = 'SELECT *
					FROM ' . TOPICS_TABLE . '
					WHERE ' . $db->sql_in_set('topic_id', array_keys($shadow_topic_list));
				$result = $db->sql_query($sql);
			
				while ($row = $db->sql_fetchrow($result))
				{
					$orig_topic_id = $shadow_topic_list[$row['topic_id']];
			
					// We want to retain some values
					$row = array_merge($row, array(
						'topic_moved_id'	=> $rowset[$orig_topic_id]['topic_moved_id'],
						'topic_status'		=> $rowset[$orig_topic_id]['topic_status'],
						'forum_name'		=> $rowset[$orig_topic_id]['forum_name'])
					);
			
					$rowset[$orig_topic_id] = $row;
				}
				$db->sql_freeresult($result);
			}
			unset($shadow_topic_list);

			foreach ($forums as $forum_id => $forum)
			{
				if ($user->data['is_registered'] && $config['load_db_lastread'])
				{
					$topic_tracking_info[$forum_id] = get_topic_tracking($forum_id, $forum['topic_list'], $forum['rowset'], array($forum_id => $forum['mark_time']), ($forum_id) ? false : $forum['topic_list']);
				}
				else if ($config['load_anon_lastread'] || $user->data['is_registered'])
				{
					$topic_tracking_info[$forum_id] = get_complete_topic_tracking($forum_id, $forum['topic_list'], ($forum_id) ? false : $forum['topic_list']);
		
					if (!$user->data['is_registered'])
					{
						$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
					}
				}
			}
			unset($forums);
		}
		else
		{
			while ($row = $db->sql_fetchrow($result))
			{
				$rowset[] = $row;
			}
			$db->sql_freeresult($result);
		}

		if ($hilit)
		{
			// Remove bad highlights
			$hilit_array = array_filter(explode('|', $hilit), 'strlen');
			foreach ($hilit_array as $key => $value)
			{
				$hilit_array[$key] = str_replace('\*', '\w*?', preg_quote($value, '#'));
			}
			$hilit = implode('|', $hilit_array);
		}

		foreach ($rowset as $row)
		{
			$forum_id = $row['forum_id'];
			$result_topic_id = $row['topic_id'];
			$topic_title = censor_text($row['topic_title']);

			// we need to select a forum id for this global topic
			if (!$forum_id)
			{
				if (!isset($g_forum_id))
				{
					// Get a list of forums the user cannot read
					$forum_ary = array_unique(array_keys($auth->acl_getf('!f_read', true)));
	
					// Determine first forum the user is able to read (must not be a category)
					$sql = 'SELECT forum_id 
						FROM ' . FORUMS_TABLE . '
						WHERE forum_type = ' . FORUM_POST;
		
					if (sizeof($forum_ary))
					{
						$sql .= ' AND ' . $db->sql_in_set('forum_id', $forum_ary, true);
					}

					$result = $db->sql_query_limit($sql, 1);
					$g_forum_id = (int) $db->sql_fetchfield('forum_id');
				}
				$u_forum_id = $g_forum_id;
			}
			else
			{
				$u_forum_id = $forum_id;
			}

			$view_topic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$u_forum_id&amp;t=$result_topic_id&amp;hilit=$u_hilit");

			$replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real'] : $row['topic_replies'];

			if ($show_results == 'topics')
			{
				$folder_img = $folder_alt = $topic_type = '';
				topic_status($row, $replies, (isset($topic_tracking_info[$forum_id][$row['topic_id']]) && $row['topic_last_post_time'] > $topic_tracking_info[$forum_id][$row['topic_id']]) ? true : false, $folder_img, $folder_alt, $topic_type);

				$unread_topic = (isset($topic_tracking_info[$forum_id][$row['topic_id']]) && $row['topic_last_post_time'] > $topic_tracking_info[$forum_id][$row['topic_id']]) ? true : false;

				$topic_unapproved = (!$row['topic_approved'] && $auth->acl_gets('m_approve', $forum_id)) ? true : false;
				$posts_unapproved = ($row['topic_approved'] && $row['topic_replies'] < $row['topic_replies_real'] && $auth->acl_gets('m_approve', $forum_id)) ? true : false;
				$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$result_topic_id", true, $user->session_id) : '';

				$tpl_ary = array(
					'TOPIC_AUTHOR'		=> topic_topic_author($row),
					'FIRST_POST_TIME'	=> $user->format_date($row['topic_time']),
					'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
					'LAST_VIEW_TIME'	=> $user->format_date($row['topic_last_view_time']),
					'LAST_POST_AUTHOR'	=> ($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] : $user->lang['GUEST'],
					'PAGINATION'		=> topic_generate_pagination($replies, $view_topic_url),
					'TOPIC_TYPE'		=> $topic_type,

					'LAST_POST_IMG'			=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
					'TOPIC_FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
					'TOPIC_FOLDER_IMG_SRC'	=> $user->img($folder_img, $folder_alt, false, '', 'src'),
					'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
					'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
					'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
					'ATTACH_ICON_IMG'		=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
					'UNAPPROVED_IMG'		=> ($topic_unapproved || $posts_unapproved) ? $user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',

					'S_TOPIC_GLOBAL'		=> (!$forum_id) ? true : false,
					'S_TOPIC_TYPE'			=> $row['topic_type'],
					'S_USER_POSTED'			=> (!empty($row['mark_type'])) ? true : false,
					'S_UNREAD_TOPIC'		=> $unread_topic,

					'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && $auth->acl_gets('m_report', $forum_id)) ? true : false,
					'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
					'S_POSTS_UNAPPROVED'	=> $posts_unapproved,

					'U_LAST_POST'			=> $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#p' . $row['topic_last_post_id'],
					'U_LAST_POST_AUTHOR'	=> ($row['topic_last_poster_id'] != ANONYMOUS && $row['topic_last_poster_id']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['topic_last_poster_id']) : '',
					'U_NEWEST_POST'			=> $view_topic_url . '&amp;view=unread#unread',
					'U_MCP_REPORT'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=reports&amp;t=' . $result_topic_id, true, $user->session_id),
					'U_MCP_QUEUE'			=> $u_mcp_queue,
				);
			}
			else
			{
				if ((isset($zebra['foe']) && in_array($row['poster_id'], $zebra['foe'])) && (!$view || $view != 'show' || $post_id != $row['post_id']))
				{
					$template->assign_block_vars('searchresults', array(
						'S_IGNORE_POST' => true,

						'L_IGNORE_POST' => sprintf($user->lang['POST_BY_FOE'], $row['username'], "<a href=\"$u_search&amp;p=" . $row['post_id'] . '&amp;view=show#p' . $row['post_id'] . '">', '</a>'))
					);

					continue;
				}

				decode_message($row['post_text'], $row['bbcode_uid']);

				if ($return_chars != -1)
				{
					$row['post_text'] = (strlen($row['post_text']) < $return_chars + 3) ? $row['post_text'] : substr($row['post_text'], 0, $return_chars) . '...';
				}

				// Replace naughty words such as farty pants
				$row['post_subject'] = censor_text($row['post_subject']);
				$row['post_text'] = str_replace("\n", '<br />', censor_text($row['post_text']));

				// post highlighting
				$row['post_text'] = preg_replace('#(?!<.*)(?<!\w)(' . $hilit . ')(?!\w|[^<>]*>)#i', '<span class="posthilit">$1</span>', $row['post_text']);

				$row['post_text'] = smiley_text($row['post_text']);

				$tpl_ary = array(
					'POSTER_NAME'		=> ($row['poster_id'] == ANONYMOUS) ? ((!empty($row['post_username'])) ? $row['post_username'] : $user->lang['GUEST']) : $row['username'],
					'U_PROFILE'			=> ($row['poster_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['poster_id']) : '',
					'POST_SUBJECT'		=> $row['post_subject'],
					'POST_DATE'			=> (!empty($row['post_time'])) ? $user->format_date($row['post_time']) : '',
					'MESSAGE'			=> $row['post_text']
				);
			}

			$template->assign_block_vars('searchresults', array_merge($tpl_ary, array(
				'FORUM_ID'			=> $forum_id,
				'TOPIC_ID'			=> $result_topic_id,
				'POST_ID'			=> ($show_results == 'posts') ? $row['post_id'] : false,

				'FORUM_TITLE'		=> $row['forum_name'],
				'TOPIC_TITLE'		=> $topic_title,
				'TOPIC_REPLIES'		=> $replies,
				'TOPIC_VIEWS'		=> $row['topic_views'],

				'U_VIEW_TOPIC'		=> $view_topic_url,
				'U_VIEW_FORUM'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id),
				'U_VIEW_POST'		=> (!empty($row['post_id'])) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=" . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '&amp;hilit=' . $u_hilit) . '#p' . $row['post_id'] : '')
			));
		}

		if ($topic_id && ($topic_id == $result_topic_id))
		{
			$template->assign_vars(array(
				'SEARCH_TOPIC'		=> $topic_title,
				'U_SEARCH_TOPIC'	=> $view_topic_url
			));
		}
	}
	unset($rowset);

	page_header($user->lang['SEARCH']);

	$template->set_filenames(array(
		'body' =>  'search_results.html')
	);
	make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

	page_footer();
}


// Search forum
$s_forums = '';
$sql = 'SELECT f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.left_id, f.right_id, f.forum_password, fa.user_id
	FROM ' . FORUMS_TABLE . ' f
	LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa ON  (fa.forum_id = f.forum_id
		AND fa.session_id = '" . $db->sql_escape($user->session_id) . "')
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
		// if the user does not have permissions to list this forum skip to the next branch
		continue;
	}

	if ($row['left_id'] < $right)
	{
		$padding .= '&nbsp; &nbsp;';
		$pad_store[$row['parent_id']] = $padding;
	}
	else if ($row['left_id'] > $right + 1)
	{
		if (isset($pad_store[$row['parent_id']]))
		{
			$padding = $pad_store[$row['parent_id']];
		}
		else
		{
			continue;
		}
	}

	$right = $row['right_id'];

	if (!$auth->acl_get('f_search', $row['forum_id']))
	{
		// if the user does not have permissions to search this forum skip only this forum/category
		continue;
	}

	$selected = (in_array($row['forum_id'], $search_forum)) ? ' selected="selected"' : '';

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
	'S_HIDDEN_FIELDS'		=> build_hidden_fields(array('sid' => $user->session_id, 't' => $topic_id)),
	'S_CHARACTER_OPTIONS'	=> $s_characters,
	'S_FORUM_OPTIONS'		=> $s_forums,
	'S_SELECT_SORT_DIR'		=> $s_sort_dir,
	'S_SELECT_SORT_KEY'		=> $s_sort_key,
	'S_SELECT_SORT_DAYS'	=> $s_limit_days)
);

$sql = 'SELECT search_time, search_keywords
	FROM ' . SEARCH_RESULTS_TABLE . '
	WHERE search_keywords <> \'\'
	ORDER BY search_time DESC';
$result = $db->sql_query_limit($sql, 5);

while ($row = $db->sql_fetchrow($result))
{
	$keywords = htmlspecialchars($row['search_keywords']);

	$template->assign_block_vars('recentsearch', array(
		'KEYWORDS'	=> $keywords,
		'TIME'		=> $user->format_date($row['search_time']),

		'U_KEYWORDS'	=> append_sid("{$phpbb_root_path}search.$phpEx", 'keywords=' . urlencode($keywords)))
	);
}
$db->sql_freeresult($result);

// Output the basic page
page_header($user->lang['SEARCH']);

$template->set_filenames(array(
	'body' => 'search_body.html')
);
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

page_footer();

?>