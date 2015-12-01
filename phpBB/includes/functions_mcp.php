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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Functions used to generate additional URL paramters
*/
function phpbb_module__url($mode, &$module_row)
{
	return phpbb_extra_url();
}

function phpbb_module_notes_url($mode, &$module_row)
{
	if ($mode == 'front')
	{
		return '';
	}

	global $user_id;
	return ($user_id) ? "&amp;u=$user_id" : '';
}

function phpbb_module_warn_url($mode, &$module_row)
{
	if ($mode == 'front' || $mode == 'list')
	{
		global $forum_id;

		return ($forum_id) ? "&amp;f=$forum_id" : '';
	}

	if ($mode == 'warn_post')
	{
		global $forum_id, $post_id;

		$url_extra = ($forum_id) ? "&amp;f=$forum_id" : '';
		$url_extra .= ($post_id) ? "&amp;p=$post_id" : '';

		return $url_extra;
	}
	else
	{
		global $user_id;

		return ($user_id) ? "&amp;u=$user_id" : '';
	}
}

function phpbb_module_main_url($mode, &$module_row)
{
	return phpbb_extra_url();
}

function phpbb_module_logs_url($mode, &$module_row)
{
	return phpbb_extra_url();
}

function phpbb_module_ban_url($mode, &$module_row)
{
	return phpbb_extra_url();
}

function phpbb_module_queue_url($mode, &$module_row)
{
	return phpbb_extra_url();
}

function phpbb_module_reports_url($mode, &$module_row)
{
	return phpbb_extra_url();
}

function phpbb_extra_url()
{
	global $forum_id, $topic_id, $post_id, $report_id, $user_id;

	$url_extra = '';
	$url_extra .= ($forum_id) ? "&amp;f=$forum_id" : '';
	$url_extra .= ($topic_id) ? "&amp;t=$topic_id" : '';
	$url_extra .= ($post_id) ? "&amp;p=$post_id" : '';
	$url_extra .= ($user_id) ? "&amp;u=$user_id" : '';
	$url_extra .= ($report_id) ? "&amp;r=$report_id" : '';

	return $url_extra;
}

/**
* Get simple topic data
*/
function phpbb_get_topic_data($topic_ids, $acl_list = false, $read_tracking = false)
{
	global $auth, $db, $config, $user;
	static $rowset = array();

	$topics = array();

	if (!sizeof($topic_ids))
	{
		return array();
	}

	// cache might not contain read tracking info, so we can't use it if read
	// tracking information is requested
	if (!$read_tracking)
	{
		$cache_topic_ids = array_intersect($topic_ids, array_keys($rowset));
		$topic_ids = array_diff($topic_ids, array_keys($rowset));
	}
	else
	{
		$cache_topic_ids = array();
	}

	if (sizeof($topic_ids))
	{
		$sql_array = array(
			'SELECT'	=> 't.*, f.*',

			'FROM'		=> array(
				TOPICS_TABLE	=> 't',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE => 'f'),
					'ON'	=> 'f.forum_id = t.forum_id'
				)
			),

			'WHERE'		=> $db->sql_in_set('t.topic_id', $topic_ids)
		);

		if ($read_tracking && $config['load_db_lastread'])
		{
			$sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
				'ON'	=> 'tt.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tt.topic_id'
			);

			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
				'ON'	=> 'ft.user_id = ' . $user->data['user_id'] . ' AND t.forum_id = ft.forum_id'
			);
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$rowset[$row['topic_id']] = $row;

			if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
			{
				continue;
			}

			$topics[$row['topic_id']] = $row;
		}
		$db->sql_freeresult($result);
	}

	foreach ($cache_topic_ids as $id)
	{
		if (!$acl_list || $auth->acl_gets($acl_list, $rowset[$id]['forum_id']))
		{
			$topics[$id] = $rowset[$id];
		}
	}

	return $topics;
}

/**
* Get simple post data
*/
function phpbb_get_post_data($post_ids, $acl_list = false, $read_tracking = false)
{
	global $db, $auth, $config, $user;

	$rowset = array();

	if (!sizeof($post_ids))
	{
		return array();
	}

	$sql_array = array(
		'SELECT'	=> 'p.*, u.*, t.*, f.*',

		'FROM'		=> array(
			USERS_TABLE		=> 'u',
			POSTS_TABLE		=> 'p',
			TOPICS_TABLE	=> 't',
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(FORUMS_TABLE => 'f'),
				'ON'	=> 'f.forum_id = t.forum_id'
			)
		),

		'WHERE'		=> $db->sql_in_set('p.post_id', $post_ids) . '
			AND u.user_id = p.poster_id
			AND t.topic_id = p.topic_id',
	);

	if ($read_tracking && $config['load_db_lastread'])
	{
		$sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
			'ON'	=> 'tt.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tt.topic_id'
		);

		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
			'ON'	=> 'ft.user_id = ' . $user->data['user_id'] . ' AND t.forum_id = ft.forum_id'
		);
	}

	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql);
	unset($sql_array);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
		{
			continue;
		}

		if ($row['post_visibility'] != ITEM_APPROVED && !$auth->acl_get('m_approve', $row['forum_id']))
		{
			// Moderators without the permission to approve post should at least not see them. ;)
			continue;
		}

		$rowset[$row['post_id']] = $row;
	}
	$db->sql_freeresult($result);

	return $rowset;
}

/**
* Get simple forum data
*/
function phpbb_get_forum_data($forum_id, $acl_list = 'f_list', $read_tracking = false)
{
	global $auth, $db, $user, $config, $phpbb_container;

	$rowset = array();

	if (!is_array($forum_id))
	{
		$forum_id = array($forum_id);
	}

	if (!sizeof($forum_id))
	{
		return array();
	}

	if ($read_tracking && $config['load_db_lastread'])
	{
		$read_tracking_join = ' LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . '
			AND ft.forum_id = f.forum_id)';
		$read_tracking_select = ', ft.mark_time';
	}
	else
	{
		$read_tracking_join = $read_tracking_select = '';
	}

	$sql = "SELECT f.* $read_tracking_select
		FROM " . FORUMS_TABLE . " f$read_tracking_join
		WHERE " . $db->sql_in_set('f.forum_id', $forum_id);
	$result = $db->sql_query($sql);

	/* @var $phpbb_content_visibility \phpbb\content_visibility */
	$phpbb_content_visibility = $phpbb_container->get('content.visibility');

	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
		{
			continue;
		}

		$row['forum_topics_approved'] = $phpbb_content_visibility->get_count('forum_topics', $row, $row['forum_id']);

		$rowset[$row['forum_id']] = $row;
	}
	$db->sql_freeresult($result);

	return $rowset;
}

/**
* Get simple pm data
*/
function phpbb_get_pm_data($pm_ids)
{
	global $db;

	$rowset = array();

	if (!sizeof($pm_ids))
	{
		return array();
	}

	$sql_array = array(
		'SELECT'	=> 'p.*, u.*',

		'FROM'		=> array(
			USERS_TABLE			=> 'u',
			PRIVMSGS_TABLE		=> 'p',
		),

		'WHERE'		=> $db->sql_in_set('p.msg_id', $pm_ids) . '
			AND u.user_id = p.author_id',
	);

	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql);
	unset($sql_array);

	while ($row = $db->sql_fetchrow($result))
	{
		$rowset[$row['msg_id']] = $row;
	}
	$db->sql_freeresult($result);

	return $rowset;
}

/**
* sorting in mcp
*
* @param string $where_sql should either be WHERE (default if ommited) or end with AND or OR
*
* $mode reports and reports_closed: the $where parameters uses aliases p for posts table and r for report table
* $mode unapproved_posts: the $where parameters uses aliases p for posts table and t for topic table
*/
function phpbb_mcp_sorting($mode, &$sort_days_val, &$sort_key_val, &$sort_dir_val, &$sort_by_sql_ary, &$sort_order_sql, &$total_val, $forum_id = 0, $topic_id = 0, $where_sql = 'WHERE')
{
	global $db, $user, $auth, $template, $request, $phpbb_dispatcher;

	$sort_days_val = $request->variable('st', 0);
	$min_time = ($sort_days_val) ? time() - ($sort_days_val * 86400) : 0;

	switch ($mode)
	{
		case 'viewforum':
			$type = 'topics';
			$default_key = 't';
			$default_dir = 'd';

			$sql = 'SELECT COUNT(topic_id) AS total
				FROM ' . TOPICS_TABLE . "
				$where_sql forum_id = $forum_id
					AND topic_type NOT IN (" . POST_ANNOUNCE . ', ' . POST_GLOBAL . ")
					AND topic_last_post_time >= $min_time";

			if (!$auth->acl_get('m_approve', $forum_id))
			{
				$sql .= ' AND topic_visibility = ' . ITEM_APPROVED;
			}
			break;

		case 'viewtopic':
			$type = 'posts';
			$default_key = 't';
			$default_dir = 'a';

			$sql = 'SELECT COUNT(post_id) AS total
				FROM ' . POSTS_TABLE . "
				$where_sql topic_id = $topic_id
					AND post_time >= $min_time";

			if (!$auth->acl_get('m_approve', $forum_id))
			{
				$sql .= ' AND post_visibility = ' . ITEM_APPROVED;
			}
			break;

		case 'unapproved_posts':
		case 'deleted_posts':
			$visibility_const = ($mode == 'unapproved_posts') ? array(ITEM_UNAPPROVED, ITEM_REAPPROVE) : ITEM_DELETED;
			$type = 'posts';
			$default_key = 't';
			$default_dir = 'd';
			$where_sql .= ($topic_id) ? ' p.topic_id = ' . $topic_id . ' AND' : '';

			$sql = 'SELECT COUNT(p.post_id) AS total
				FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
				$where_sql " . $db->sql_in_set('p.forum_id', ($forum_id) ? array($forum_id) : array_intersect(get_forum_list('f_read'), get_forum_list('m_approve'))) . '
					AND ' . $db->sql_in_set('p.post_visibility', $visibility_const) .'
					AND t.topic_id = p.topic_id
					AND t.topic_visibility <> p.post_visibility';

			if ($min_time)
			{
				$sql .= ' AND post_time >= ' . $min_time;
			}
			break;

		case 'unapproved_topics':
		case 'deleted_topics':
			$visibility_const = ($mode == 'unapproved_topics') ? array(ITEM_UNAPPROVED, ITEM_REAPPROVE) : ITEM_DELETED;
			$type = 'topics';
			$default_key = 't';
			$default_dir = 'd';

			$sql = 'SELECT COUNT(topic_id) AS total
				FROM ' . TOPICS_TABLE . "
				$where_sql " . $db->sql_in_set('forum_id', ($forum_id) ? array($forum_id) : array_intersect(get_forum_list('f_read'), get_forum_list('m_approve'))) . '
					AND ' . $db->sql_in_set('topic_visibility', $visibility_const);

			if ($min_time)
			{
				$sql .= ' AND topic_time >= ' . $min_time;
			}
			break;

		case 'pm_reports':
		case 'pm_reports_closed':
		case 'reports':
		case 'reports_closed':
			$pm = (strpos($mode, 'pm_') === 0) ? true : false;

			$type = ($pm) ? 'pm_reports' : 'reports';
			$default_key = 't';
			$default_dir = 'd';
			$limit_time_sql = ($min_time) ? "AND r.report_time >= $min_time" : '';

			if ($topic_id)
			{
				$where_sql .= ' p.topic_id = ' . $topic_id . ' AND ';
			}
			else if ($forum_id)
			{
				$where_sql .= ' p.forum_id = ' . $forum_id . ' AND ';
			}
			else if (!$pm)
			{
				$where_sql .= ' ' . $db->sql_in_set('p.forum_id', get_forum_list(array('!f_read', '!m_report')), true, true) . ' AND ';
			}

			if ($mode == 'reports' || $mode == 'pm_reports')
			{
				$where_sql .= ' r.report_closed = 0 AND ';
			}
			else
			{
				$where_sql .= ' r.report_closed = 1 AND ';
			}

			if ($pm)
			{
				$sql = 'SELECT COUNT(r.report_id) AS total
					FROM ' . REPORTS_TABLE . ' r, ' . PRIVMSGS_TABLE . " p
					$where_sql r.post_id = 0
						AND p.msg_id = r.pm_id
						$limit_time_sql";
			}
			else
			{
				$sql = 'SELECT COUNT(r.report_id) AS total
					FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . " p
					$where_sql r.pm_id = 0
						AND p.post_id = r.post_id
						$limit_time_sql";
			}
			break;

		case 'viewlogs':
			$type = 'logs';
			$default_key = 't';
			$default_dir = 'd';

			$sql = 'SELECT COUNT(log_id) AS total
				FROM ' . LOG_TABLE . "
				$where_sql " . $db->sql_in_set('forum_id', ($forum_id) ? array($forum_id) : array_intersect(get_forum_list('f_read'), get_forum_list('m_'))) . '
					AND log_time >= ' . $min_time . '
					AND log_type = ' . LOG_MOD;
			break;
	}

	$sort_key_val = $request->variable('sk', $default_key);
	$sort_dir_val = $request->variable('sd', $default_dir);
	$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

	switch ($type)
	{
		case 'topics':
			$limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'tt' => $user->lang['TOPIC_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);

			$sort_by_sql_ary = array('a' => 't.topic_first_poster_name', 't' => array('t.topic_last_post_time', 't.topic_last_post_id'), 'tt' => 't.topic_time', 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_posts_approved + t.topic_posts_unapproved + t.topic_posts_softdeleted' : 't.topic_posts_approved'), 's' => 't.topic_title', 'v' => 't.topic_views');
			$limit_time_sql = ($min_time) ? "AND t.topic_last_post_time >= $min_time" : '';
			break;

		case 'posts':
			$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
			$sort_by_sql_ary = array('a' => 'u.username_clean', 't' => array('p.post_time', 'p.post_id'), 's' => 'p.post_subject');
			$limit_time_sql = ($min_time) ? "AND p.post_time >= $min_time" : '';
			break;

		case 'reports':
			$limit_days = array(0 => $user->lang['ALL_REPORTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 'r' => $user->lang['REPORTER'], 'p' => $user->lang['POST_TIME'], 't' => $user->lang['REPORT_TIME'], 's' => $user->lang['SUBJECT']);
			$sort_by_sql_ary = array('a' => 'u.username_clean', 'r' => 'ru.username', 'p' => array('p.post_time', 'p.post_id'), 't' => 'r.report_time', 's' => 'p.post_subject');
			break;

		case 'pm_reports':
			$limit_days = array(0 => $user->lang['ALL_REPORTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 'r' => $user->lang['REPORTER'], 'p' => $user->lang['POST_TIME'], 't' => $user->lang['REPORT_TIME'], 's' => $user->lang['SUBJECT']);
			$sort_by_sql_ary = array('a' => 'u.username_clean', 'r' => 'ru.username', 'p' => 'p.message_time', 't' => 'r.report_time', 's' => 'p.message_subject');
			break;

		case 'logs':
			$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
			$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);

			$sort_by_sql_ary = array('u' => 'u.username_clean', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');
			$limit_time_sql = ($min_time) ? "AND l.log_time >= $min_time" : '';
			break;
	}

	// Default total to -1 to allow editing by the event
	$total_val = -1;

	$sort_by_sql = $sort_by_sql_ary;
	$sort_days = $sort_days_val;
	$sort_dir = $sort_dir_val;
	$sort_key = $sort_key_val;
	$total = $total_val;
	/**
	* This event allows you to control the SQL query used to get the total number
	* of reports the user can access.
	*
	* This total is used for the pagination and for displaying the total number
	* of reports to the user
	*
	*
	* @event core.mcp_sorting_query_before
	* @var	string	sql					The current SQL search string
	* @var	string	mode				An id related to the module(s) the user is viewing
	* @var	string	type				Which kind of information is this being used for displaying. Posts, topics, etc...
	* @var	int		forum_id			The forum id of the posts the user is trying to access, if not 0
	* @var	int		topic_id			The topic id of the posts the user is trying to access, if not 0
	* @var	int		sort_days			The max age of the oldest report to be shown, in days
	* @var	string	sort_key			The way the user has decided to sort the data.
	*									The valid values must be in the keys of the sort_by_* variables
	* @var	string	sort_dir			Either 'd' for "DESC" or 'a' for 'ASC' in the SQL query
	* @var	int		limit_days			The possible max ages of the oldest report for the user to choose, in days.
	* @var	array	sort_by_sql			SQL text (values) for the possible names of the ways of sorting data (keys).
	* @var	array	sort_by_text		Language text (values) for the possible names of the ways of sorting data (keys).
	* @var	int		min_time			Integer with the minimum post time that the user is searching for
	* @var	int		limit_time_sql		Time limiting options used in the SQL query.
	* @var	int		total				The total number of reports that exist. Only set if you want to override the result
	* @var	string	where_sql			Extra information included in the WHERE clause. It must end with "WHERE" or "AND" or "OR".
	*									Set to "WHERE" and set total above -1 to override the total value
	* @since 3.1.4-RC1
	*/
	$vars = array(
		'sql',
		'mode',
		'type',
		'forum_id',
		'topic_id',
		'sort_days',
		'sort_key',
		'sort_dir',
		'limit_days',
		'sort_by_sql',
		'sort_by_text',
		'min_time',
		'limit_time_sql',
		'total',
		'where_sql',
	);
	extract($phpbb_dispatcher->trigger_event('core.mcp_sorting_query_before', compact($vars)));
	$sort_by_sql_ary = $sort_by_sql;
	$sort_days_val = $sort_days;
	$sort_key_val = $sort_key;
	$sort_dir_val = $sort_dir;
	$total_val = $total;
	unset($sort_by_sql);
	unset($sort_days);
	unset($sort_key);
	unset($sort_dir);
	unset($total);

	if (!isset($sort_by_sql_ary[$sort_key_val]))
	{
		$sort_key_val = $default_key;
	}

	$direction = ($sort_dir_val == 'd') ? 'DESC' : 'ASC';

	if (is_array($sort_by_sql_ary[$sort_key_val]))
	{
		$sort_order_sql = implode(' ' . $direction . ', ', $sort_by_sql_ary[$sort_key_val]) . ' ' . $direction;
	}
	else
	{
		$sort_order_sql = $sort_by_sql_ary[$sort_key_val] . ' ' . $direction;
	}

	$s_limit_days = $s_sort_key = $s_sort_dir = $sort_url = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days_val, $sort_key_val, $sort_dir_val, $s_limit_days, $s_sort_key, $s_sort_dir, $sort_url);

	$template->assign_vars(array(
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days)
	);

	if (($sort_days_val && $mode != 'viewlogs') || in_array($mode, array('reports', 'unapproved_topics', 'unapproved_posts', 'deleted_topics', 'deleted_posts')) || $where_sql != 'WHERE')
	{
		$result = $db->sql_query($sql);
		$total_val = (int) $db->sql_fetchfield('total');
		$db->sql_freeresult($result);
	}
	else if ($total_val < -1)
	{
		$total_val = -1;
	}
}

/**
* Validate ids
*
* @param	array	&$ids			The relevant ids to check
* @param	string	$table			The table to find the ids in
* @param	string	$sql_id			The ids relevant column name
* @param	array	$acl_list		A list of permissions the user need to have
* @param	mixed	$singe_forum	Limit to one forum id (int) or the first forum found (true)
*
* @return	mixed	False if no ids were able to be retrieved, true if at least one id left.
*					Additionally, this value can be the forum_id assigned if $single_forum was set.
*					Therefore checking the result for with !== false is the best method.
*/
function phpbb_check_ids(&$ids, $table, $sql_id, $acl_list = false, $single_forum = false)
{
	global $db, $auth;

	if (!is_array($ids) || empty($ids))
	{
		return false;
	}

	$sql = "SELECT $sql_id, forum_id FROM $table
		WHERE " . $db->sql_in_set($sql_id, $ids);
	$result = $db->sql_query($sql);

	$ids = array();
	$forum_id = false;

	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && $row['forum_id'] && !$auth->acl_gets($acl_list, $row['forum_id']))
		{
			continue;
		}

		if ($acl_list && !$row['forum_id'] && !$auth->acl_getf_global($acl_list))
		{
			continue;
		}

		// Limit forum? If not, just assign the id.
		if ($single_forum === false)
		{
			$ids[] = $row[$sql_id];
			continue;
		}

		// Limit forum to a specific forum id?
		// This can get really tricky, because we do not want to create a failure on global topics. :)
		if ($row['forum_id'])
		{
			if ($single_forum !== true && $row['forum_id'] == (int) $single_forum)
			{
				$forum_id = (int) $single_forum;
			}
			else if ($forum_id === false)
			{
				$forum_id = $row['forum_id'];
			}

			if ($row['forum_id'] == $forum_id)
			{
				$ids[] = $row[$sql_id];
			}
		}
		else
		{
			// Always add a global topic
			$ids[] = $row[$sql_id];
		}
	}
	$db->sql_freeresult($result);

	if (!sizeof($ids))
	{
		return false;
	}

	// If forum id is false and ids populated we may have only global announcements selected (returning 0 because of (int) $forum_id)

	return ($single_forum === false) ? true : (int) $forum_id;
}
