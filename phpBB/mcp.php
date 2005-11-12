<?php
/** 
*
* @package mcp
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
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
require($phpbb_root_path . 'includes/functions_module.'.$phpEx);

/**
* @package mcp
* MCP Module
*/
class mcp extends p_master
{
	/**
	* List modules
	*
	* This creates a list, stored in $this->module_ary of all available
	* modules for the given class (ucp, mcp and acp). Additionally
	* $this->module_y_ary is created with indentation information for
	* displaying the module list appropriately. Only modules for which
	* the user has access rights are included in these lists.
	*
	* The mcp performs additional checks on the modules loaded to ensure
	* that only relevant links are presented
	*
	* @final
	*/
	function list_modules($p_class, $forum_id = 0, $topic_id = 0, $post_id = 0)
	{
		global $auth, $db, $user;
		global $config, $phpbb_root_path, $phpEx;

		$get_cache_data = true;

		// Empty cached contents
		$this->module_cache = array();

		// Sanitise for future path use, it's escaped as appropriate for queries
		$this->p_class = str_replace(array('.', '/', '\\'), '', basename($p_class));
		
		if (file_exists($phpbb_root_path . 'cache/' . $this->p_class . '_modules.' . $phpEx))
		{
			include($phpbb_root_path . 'cache/' . $this->p_class . '_modules.' . $phpEx);
			$get_cache_data = false;
		}

		if ($get_cache_data)
		{
			global $cache;
			
			// Get active modules
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_class = '" . $db->sql_escape($p_class) . "'
					AND module_enabled = 1
				ORDER BY left_id ASC";
			$result = $db->sql_query($sql);
			
			$this->module_cache['modules'] = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$this->module_cache['modules'][] = $row;
			}
			$db->sql_freeresult($result);

			// Get module parents
			$this->module_cache['parents'] = array();
			foreach ($this->module_cache['modules'] as $row)
			{
				$this->module_cache['parents'][$row['module_id']] = $this->get_parents($row['parent_id'], $row['left_id'], $row['right_id']);
			}

			$file = '<?php $this->module_cache=' . $cache->format_array($this->module_cache) . "; ?>";

			if ($fp = @fopen($phpbb_root_path . 'cache/' . $this->p_class . '_modules.' . $phpEx, 'wb'))
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, $file);
				@flock($fp, LOCK_UN);
				fclose($fp);
			}

			unset($file);
		}

		$right = $depth = $i = 0;
		$depth_ary = array();

		foreach ($this->module_cache['modules'] as $row)
		{
			/**
			* Authorisation is required ... not authed, skip
			* @todo implement $this->is_module_id
			* @todo put in seperate method for authentication
			*/
			if ($row['module_auth'])
			{
				$is_auth = false;
				eval('$is_auth = (int) (' . preg_replace(array('#acl_([a-z_]+)(,\$id)?#e', '#\$id#', '#cfg_([a-z_]+)#e'), array('(int) $auth->acl_get("\\1"\\2)', '$this->acl_forup_id', '(int) $config["\\1"]'), trim($row['module_auth'])) . ');');
				if (!$is_auth)
				{
					continue;
				}
			}

			// Category with no members, ignore
			if (!$row['module_name'] && ($row['left_id'] + 1 == $row['right_id']))
			{
				continue;
			}

			// Ignore those rows we don't have enough information to access
			if (($row['module_mode'] == 'post_details' && !$post_id) ||
				($row['module_mode'] == 'topic_view' && !$topic_id) ||
				($row['module_mode'] == 'forum_view' && !$forum_id))
			{
				continue;
			}

			$url_extra = '';
			$url_extra .= ($forum_id) ? "&amp;f=$forum_id" : '';
			$url_extra .= ($topic_id) ? "&amp;t=$topic_id" : '';
			$url_extra .= ($post_id) ? "&amp;p=$post_id" : '';

			if ($row['left_id'] < $right)
			{
				$depth++;
				$depth_ary[$row['parent_id']] = $depth;
			}
			else if ($row['left_id'] > $right + 1)
			{
				if (!isset($depth_ary[$row['parent_id']]))
				{
					$depth = 0;
				}
				else
				{
					$depth = $depth_ary[$row['parent_id']];
				}
			}

			$right = $row['right_id'];

			$this->module_ary[$i] = array(
				'depth'		=> $depth,

				'id'		=> (int) $row['module_id'],
				'parent'	=> (int) $row['parent_id'],
				'cat'		=> ($row['right_id'] > $row['left_id'] + 1) ? true : false,

				'name'		=> (string) $row['module_name'],
				'mode'		=> (string) $row['module_mode'],
				'display'	=> (int) $row['module_display'],
				
				'lang'		=> (function_exists($row['module_name'])) ? $row['module_name']($row['module_mode'], $row['module_langname']) : ((!empty($user->lang[$row['module_langname']])) ? $user->lang[$row['module_langname']] : $row['module_langname']),
				'langname'	=> $row['module_langname'],

				'url_extra'	=> $url_extra,

				'left'		=> $row['left_id'],
				'right'		=> $row['right_id'],
			);

			$i++;
		}

		unset($this->module_cache['modules']);
	}
}

/**
*/

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mcp');

$module = new mcp();

// Basic parameter data
$mode	= request_var('mode', '');
$mode2	= (isset($_REQUEST['quick'])) ? request_var('mode2', '') : '';
$id = request_var('i', '');

if (is_array($mode))
{
	list($mode, ) = each($mode);
}

if ($mode2)
{
	$mode = $mode2;
	$action = '';
	unset($mode2);
}

// Make sure we are using the correct module
if ($mode == 'approve' || $mode == 'disapprove')
{
	$id = 'queue';
}

// Only Moderators can go beyond this point
if (!$user->data['is_registered'])
{
	if ($user->data['is_bot'])
	{
		redirect("index.$phpEx$SID");
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_MCP']);
}

$quickmod = (isset($_REQUEST['quickmod'])) ? true : false;
$action = request_var('action', '');
$action_ary = request_var('action', array('' => 0));

if (sizeof($action_ary))
{
	list($action, ) = each($action);
}
unset($action_ary);

if ($action == 'merge_select')
{
	$mode = 'forum_view';
}

// Topic view modes
if (in_array($mode, array('split', 'split_all', 'split_beyond', 'merge', 'merge_posts')))
{
	$_REQUEST['action'] = $action = $mode;
	$mode = 'topic_view';
	$quickmod = false;
}

// Forum view modes
if (in_array($mode, array('resync')))
{
	$_REQUEST['action'] = $action = $mode;
	$mode = 'forum_view';
	$quickmod = false;
}

if (!$quickmod)
{
	$post_id = request_var('p', 0);
	$topic_id = request_var('t', 0);
	$forum_id = request_var('f', 0);

	if ($post_id)
	{
		// We determine the topic and forum id here, to make sure the moderator really has moderative rights on this post
		$sql = 'SELECT topic_id, forum_id
			FROM ' . POSTS_TABLE . "
			WHERE post_id = $post_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$topic_id = (int) $row['topic_id'];
		$forum_id = (int) $row['forum_id'];
	}

	if ($topic_id && !$forum_id)
	{
		$sql = 'SELECT forum_id
			FROM ' . TOPICS_TABLE . "
			WHERE topic_id = $topic_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$forum_id = (int) $row['forum_id'];
	}

	// If we do not have a forum id and the user is not a super moderator (global options are set to false, even if the user is able to moderator at least one forum
	if (!$forum_id && !$auth->acl_get('m_'))
	{
		$forum_list = get_forum_list('m_');

		if (!sizeof($forum_list))
		{
			trigger_error('MODULE_NOT_EXIST');
		}

		// We do not check all forums, only the first one should be sufficiant.
		$forum_id = $forum_list[0];
	}

	// Instantiate module system and generate list of available modules
	$module->list_modules('mcp', $forum_id, $topic_id, $post_id);

	// Select the active module
	$module->set_active($id, $mode);

	// Load and execute the relevant module
	$module->load_active();

	// Assign data to the template engine for the list of modules
	$module->assign_tpl_vars("mcp.$phpEx$SID");

	// Generate the page
	page_header($user->lang['MCP_MAIN']);

	$template->set_filenames(array(
		'body' => $module->get_tpl_name())
	);

	page_footer();

}

switch ($mode)
{
	case 'lock':
	case 'unlock':
	case 'lock_post':
	case 'unlock_post':
		$module->load('mcp', 'main', $mode);
		break;
	case 'make_sticky':
	case 'make_announce':
	case 'make_global':
	case 'make_normal':
		$module->load('mcp', 'main', $mode);
		break;
	case 'fork':
	case 'move':
		$module->load('mcp', 'main', $mode);
		break;
	case 'delete_post':
	case 'delete_topic':
		$module->load('mcp', 'main', $mode);
		break;
	default:
		trigger_error("$mode not allowed as quickmod");
}


//
// LITTLE HELPER

/**
* Get simple topic data
*/
function get_topic_data($topic_ids, $acl_list = false)
{
	global $auth, $db;
	$rowset = array();

	if (implode(', ', $topic_ids) == '')
	{
		return array();
	}

	$sql = 'SELECT f.*, t.*
		FROM ' . TOPICS_TABLE . ' t
			LEFT JOIN ' . FORUMS_TABLE . ' f ON t.forum_id = f.forum_id
		WHERE t.topic_id IN (' . implode(', ', $topic_ids) . ')';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && !$auth->acl_get($acl_list, $row['forum_id']))
		{
			continue;
		}

		$rowset[$row['topic_id']] = $row;
	}

	return $rowset;
}

/**
* Get simple post data
*/
function get_post_data($post_ids, $acl_list = false)
{
	global $db, $auth;
	$rowset = array();

	$sql = 'SELECT p.*, u.*, t.*, f.*
		FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u, ' . TOPICS_TABLE . ' t
			LEFT JOIN ' . FORUMS_TABLE . ' f ON f.forum_id = p.forum_id
		WHERE p.post_id IN (' . implode(', ', $post_ids) . ')
			AND u.user_id = p.poster_id
			AND t.topic_id = p.topic_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && !$auth->acl_get($acl_list, $row['forum_id']))
		{
			continue;
		}

		if (!$row['post_approved'] && !$auth->acl_get('m_approve', $row['forum_id']))
		{
			// Moderators without the permission to approve post should at least not see them. ;)
			continue;
		}

		$rowset[$row['post_id']] = $row;
	}

	return $rowset;
}

/**
* Get simple forum data
*/
function get_forum_data($forum_id, $acl_list = 'f_list')
{
	global $auth, $db;
	$rowset = array();

	$sql = 'SELECT *
		FROM ' . FORUMS_TABLE . '
		WHERE forum_id ' . ((is_array($forum_id)) ? 'IN (' . implode(', ', $forum_id) . ')' : "= $forum_id");
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && !$auth->acl_get($acl_list, $row['forum_id']))
		{
			continue;
		}
		if ($auth->acl_get('m_approve', $row['forum_id']))
		{
			$row['forum_topics'] = $row['forum_topics_real'];
		}

		$rowset[$row['forum_id']] = $row;
	}

	return $rowset;
}

/**
* sorting in mcp
*/
function mcp_sorting($mode, &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id = 0, $topic_id = 0, $where_sql = 'WHERE')
{
	global $db, $user, $auth, $template;

	$sort_days = request_var('sort_days', 0);
	$min_time = ($sort_days) ? time() - ($sort_days * 86400) : 0;

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
				$sql .= 'AND topic_approved = 1';
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
				$sql .= 'AND post_approved = 1';
			}
			break;

		case 'unapproved_posts':
			$type = 'posts';
			$default_key = 't';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(post_id) AS total
				FROM ' . POSTS_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_approve'))) . ')
					AND post_approved = 0
					AND post_time >= ' . $min_time;
			break;

		case 'unapproved_topics':
			$type = 'topics';
			$default_key = 't';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(topic_id) AS total
				FROM ' . TOPICS_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_approve'))) . ')
					AND topic_approved = 0
					AND topic_time >= ' . $min_time;
			break;

		case 'reports':
			$type = 'reports';
			$default_key = 'p';
			$default_dir = 'd';
			$limit_time_sql = ($min_time) ? "AND r.report_time >= $min_time" : '';

			if ($topic_id)
			{
				$where_sql .= ' p.topic_id = ' . $topic_id;
			}
			else if ($forum_id)
			{
				$where_sql .= ' p.forum_id = ' . $forum_id;
			}
			else
			{
				$where_sql .= ' p.forum_id IN (' . implode(', ', get_forum_list('m_')) . ')';
			}
			$sql = 'SELECT COUNT(r.report_id) AS total
				FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . " p
				$where_sql
					AND p.post_id = r.post_id
					$limit_time_sql";
			break;

		case 'viewlogs':
			$type = 'logs';
			$default_key = 't';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(log_id) AS total
				FROM ' . LOG_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_'))) . ')
					AND log_time >= ' . $min_time . '
					AND log_type = ' . LOG_MOD;
			break;
	}

	$sort_key = request_var('sk', $default_key);
	$sort_dir = request_var('sd', $default_dir);
	$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

	switch ($type)
	{
		case 'topics':
			$limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'tt' => $user->lang['TOPIC_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);

			$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_time', 'tt' => 't.topic_time', 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_replies_real' : 't.topic_replies'), 's' => 't.topic_title', 'v' => 't.topic_views');
			$limit_time_sql = ($min_time) ? "AND t.topic_last_post_time >= $min_time" : '';
			break;

		case 'posts':
			$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
			$sort_by_sql = array('a' => 'u.username', 't' => 'p.post_id', 's' => 'p.post_subject');
			$limit_time_sql = ($min_time) ? "AND p.post_time >= $min_time" : '';
			break;

		case 'reports':
			$limit_days = array(0 => $user->lang['ALL_REPORTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('p' => $user->lang['REPORT_PRIORITY'], 'r' => $user->lang['REPORTER'], 't' => $user->lang['REPORT_TIME']);
			$sort_by_sql = array('p' => 'rr.reason_priority', 'r' => 'u.username', 't' => 'r.report_time');
			break;

		case 'logs':
			$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);

			$sort_by_sql = array('u' => 'l.user_id', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');
			$limit_time_sql = ($min_time) ? "AND l.log_time >= $min_time" : '';
			break;
	}

	$sort_order_sql = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

	$s_limit_days = $s_sort_key = $s_sort_dir = $sort_url = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $sort_url);

	$template->assign_vars(array(
		'S_SELECT_SORT_DIR'	=>	$s_sort_dir,
		'S_SELECT_SORT_KEY' =>	$s_sort_key,
		'S_SELECT_SORT_DAYS'=>	$s_limit_days)
	);

	if (($sort_days && $mode != 'viewlogs') || $mode == 'reports' || $where_sql != 'WHERE')
	{
		$result = $db->sql_query($sql);
		$total = ($row = $db->sql_fetchrow($result)) ? $row['total'] : 0;
	}
	else
	{
		$total = -1;
	}
}

/**
* Validate ids
*/
function check_ids(&$ids, $table, $sql_id, $acl_list = false)
{
	global $db, $auth;

	if (!is_array($ids) || !$ids)
	{
		return 0;
	}

	// a small logical error, since global announcement are assigned to forum_id == 0
	// If the first topic id is a global announcement, we can force the forum. Though only global announcements can be
	// tricked... i really do not know how to prevent this atm.

	// With those two queries we make sure all ids are within one forum...
	$sql = "SELECT forum_id FROM $table
		WHERE $sql_id = {$ids[0]}";
	$result = $db->sql_query($sql);
	$forum_id = (int) $db->sql_fetchfield('forum_id', 0, $result);
	$db->sql_freeresult($result);

	if (!$forum_id)
	{
		// Global Announcement?
		$forum_id = request_var('f', 0);
	}

	if ($acl_list && !$auth->acl_get($acl_list, $forum_id))
	{
		trigger_error('NOT_AUTHORIZED');
	}

	if (!$forum_id)
	{
		trigger_error('Missing forum_id, has to be in url if global announcement...');
	}

	$sql = "SELECT $sql_id FROM $table
		WHERE $sql_id IN (" . implode(', ', $ids) . ")
			AND (forum_id = $forum_id OR forum_id = 0)";
	$result = $db->sql_query($sql);

	$ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$ids[] = $row[$sql_id];
	}
	$db->sql_freeresult($result);

	return $forum_id;
}

// LITTLE HELPER
//

?>