<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mcp.php
// STARTED   : Mon May 5, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ]
//
// -------------------------------------------------------------

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

// ---------
// FUNCTIONS
//
class module
{
	var $id = 0;
	var $type;
	var $name;
	var $mode;
	var $url;

	// Private methods, should not be overwritten
	function create($module_type, $module_url, $post_id, $topic_id, $forum_id, $selected_mod = false, $selected_submod = false)
	{
		global $template, $auth, $db, $user, $config;
		global $phpbb_root_path, $phpEx;

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

		$sql = 'SELECT module_id, module_title, module_filename, module_subs, module_acl
			FROM ' . MODULES_TABLE . "
			WHERE module_type = '{$module_type}'
				AND module_enabled = 1
			ORDER BY module_order ASC";
		$result = $db->sql_query($sql);

		$i = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			// Authorisation is required for the basic module
			if ($row['module_acl'])
			{
				$is_auth = false;
				eval('$is_auth = (' . preg_replace(array('#acl_([a-z_]+)#e', '#cfg_([a-z_]+)#e'), array('(int) $auth->acl_get("\\1", ' . $forum_id . ')', '(int) $config["\\1"]'), trim($row['module_acl'])) . ');');

				// The user is not authorised to use this module, skip it
				if (!$is_auth)
				{
					continue;
				}
			}

			$selected = ($row['module_filename'] == $selected_mod || $row['module_id'] == $selected_mod || (!$selected_mod && !$i)) ?  true : false;

			// Get the localised lang string if available, or make up our own otherwise
			$module_lang = strtoupper($module_type) . '_' . $row['module_title'];
			$template->assign_block_vars($module_type . '_section', array(
				'L_TITLE'		=> (isset($user->lang[$module_lang])) ? $user->lang[$module_lang] : ucfirst(str_replace('_', ' ', strtolower($row['module_title']))),
				'S_SELECTED'	=> $selected,
				'U_TITLE'		=> $module_url . '&amp;i=' . $row['module_id'])
			);

			if ($selected)
			{
				$module_id = $row['module_id'];
				$module_name = $row['module_filename'];

				if ($row['module_subs'])
				{
					$j = 0;
					$submodules_ary = explode("\n", $row['module_subs']);
					foreach ($submodules_ary as $submodule)
					{
						if (!trim($submodule))
						{
							continue;
						}

						$submodule = explode(',', trim($submodule));
						$submodule_title = array_shift($submodule);

						$is_auth = true;
						foreach ($submodule as $auth_option)
						{
							eval('$is_auth = (' . preg_replace(array('#acl_([a-z_]+)#e', '#cfg_([a-z_]+)#e'), array('(int) $auth->acl_get("\\1", ' . $forum_id . ')', '(int) $config["\\1"]'), trim($auth_option)) . ');');

							if (!$is_auth)
							{
								break;
							}
						}

						if (!$is_auth)
						{
							continue;
						}

						// Only show those rows we are able to access
						if (($submodule_title == 'post_details' && !$post_id) ||
							($submodule_title == 'topic_view' && !$topic_id) ||
							($submodule_title == 'forum_view' && !$forum_id))
						{
							continue;
						}

						$suffix = ($post_id) ? "&amp;p=$post_id" : '';
						$suffix .= ($topic_id) ? "&amp;t=$topic_id" : '';
						$suffix .= ($forum_id) ? "&amp;f=$forum_id" : '';

						$selected = ($submodule_title == $selected_submod || (!$selected_submod && !$j)) ? true : false;

						// Get the localised lang string if available, or make up our own otherwise
						$module_lang = strtoupper($module_type . '_' . $module_name . '_' . $submodule_title);

						$template->assign_block_vars("{$module_type}_section.{$module_type}_subsection", array(
							'L_TITLE'		=> (isset($user->lang[$module_lang])) ? $user->lang[$module_lang] : ucfirst(str_replace('_', ' ', strtolower($module_lang))),
							'S_SELECTED'	=> $selected,
							'ADD_ITEM'		=> $this->add_menu_item($row['module_filename'], $submodule_title),
							'U_TITLE'		=> $module_url . '&amp;i=' . $module_id . '&amp;mode=' . $submodule_title . $suffix)
						);

						if ($selected)
						{
							$this->mode = $submodule_title;
						}

						$j++;
					}
				}
			}

			$i++;
		}
		$db->sql_freeresult($result);

		if (!$module_id)
		{
			trigger_error('MODULE_NOT_EXIST');
		}

		$this->type = $module_type;
		$this->id = $module_id;
		$this->name = $module_name;
		$this->url = "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}";
		$this->url .= ($post_id) ? "&amp;p=$post_id" : '';
		$this->url .= ($topic_id) ? "&amp;t=$topic_id" : '';
		$this->url .= ($forum_id) ? "&amp;f=$forum_id" : '';
	}

	function load($type = false, $name = false, $mode = false, $run = true)
	{
		global $phpbb_root_path, $phpEx;

		if ($type)
		{
			$this->type = $type;
		}

		if ($name)
		{
			$this->name = $name;
		}

		if (!class_exists($this->type . '_' . $this->name))
		{
			require_once($phpbb_root_path . "includes/{$this->type}/{$this->type}_{$this->name}.$phpEx");

			if ($run)
			{
				if (!isset($this->mode))
				{
					$this->mode = $mode;
				}

				eval("\$this->module = new {$this->type}_{$this->name}(\$this->id, \$this->mode, \$this->url);");
				if (method_exists($this->module, 'init'))
				{
					$this->module->init();
				}
			}
		}
	}

	// Displays the appropriate template with the given title
	function display($page_title, $tpl_name)
	{
		global $template;

		page_header($page_title);

		$template->set_filenames(array(
			'body' => $tpl_name)
		);

		page_footer();
	}

	// Add Item to Submodule Title
	function add_menu_item($module_name, $mode)
	{
		global $db, $user, $auth;

		if ($module_name != 'queue')
		{
			return '';
		}

		$forum_id = request_var('f', 0);
		if ($forum_id && $auth->acl_get('m_approve', $forum_id))
		{
			$forum_list = array($forum_id);
		}
		else
		{
			$forum_list = get_forum_list('m_approve');
		}

		switch ($mode)
		{
			case 'unapproved_topics':

				$sql = 'SELECT COUNT(*) AS total
					FROM ' . TOPICS_TABLE . '
					WHERE forum_id IN (' . implode(', ', $forum_list) . ')
						AND topic_approved = 0';
				$result = $db->sql_query($sql);
				$total_topics = $db->sql_fetchfield('total', 0, $result);

				return ($total_topics) ? $total_topics : $user->lang['NONE'];
				break;

			case 'unapproved_posts':

				$sql = 'SELECT COUNT(*) AS total
						FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t
						WHERE p.forum_id IN (' . implode(', ', $forum_list) . ')
							AND p.post_approved = 0
							AND t.topic_id = p.topic_id
							AND t.topic_first_post_id <> p.post_id';
				$result = $db->sql_query($sql);
				$total_posts = $db->sql_fetchfield('total', 0, $result);

				return ($total_posts) ? $total_posts : $user->lang['NONE'];
				break;
		}
	}

	// Public methods to be overwritten by modules
	function module()
	{
		// Module name
		// Module filename
		// Module description
		// Module version
		// Module compatibility
		return false;
	}

	function init()
	{
		return false;
	}

	function install()
	{
		return false;
	}

	function uninstall()
	{
		return false;
	}
}
//
// FUNCTIONS
// ---------


// Start session management
$user->start();
$auth->acl($user->data);
$user->setup('mcp');

$mcp = new module();

// Basic parameter data
$mode	= request_var('mode', '');
$mode2	= (isset($_REQUEST['quick'])) ? request_var('mode2', '') : '';
$module = request_var('i', '');

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
	$module = 'queue';
}

// Only Moderators can go beyond this point
if ($user->data['user_id'] == ANONYMOUS)
{
	login_box('', $user->lang['LOGIN_EXPLAIN_MCP']);

	if ($user->data['user_id'] == ANONYMOUS)
	{
		redirect("index.$phpEx$SID");
	}
}

$quickmod = (isset($_REQUEST['quickmod'])) ? true : false;
$action = request_var('action', '');

if (is_array($action))
{
	list($action, ) = each($action);
}

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

	// Instantiate module system and generate list of available modules
	$mcp->create('mcp', "mcp.$phpEx$SID", $post_id, $topic_id, $forum_id, $module, $mode);

	// Load and execute the relevant module
	$mcp->load('mcp', false, $mode);
	exit;
}

switch ($mode)
{
	case 'lock':
	case 'unlock':
	case 'lock_post':
	case 'unlock_post':
		$mcp->load('mcp', 'main', $mode);
		break;
	case 'make_sticky':
	case 'make_announce':
	case 'make_global':
	case 'make_normal':
		$mcp->load('mcp', 'main', $mode);
		break;
	case 'fork':
	case 'move':
		$mcp->load('mcp', 'main', $mode);
		break;
	case 'delete_post':
	case 'delete_topic':
		$mcp->load('mcp', 'main', $mode);
		break;
	default:
		trigger_error("$mode not allowed as quickmod");
}



//
// LITTLE HELPER

// request_var, the array way
function get_array($var, $default_value)
{
	$ids = request_var($var, $default_value);

	if (!is_array($ids))
	{
		if (!$ids)
		{
			return $default_value;
		}

		$ids = array($ids);
	}

	$ids = array_unique($ids);

	if (sizeof($ids) == 1 && !$ids[0])
	{
		return $default_value;
	}

	return $ids;
}

// Build simple hidden fields from array
function build_hidden_fields($field_ary)
{
	$s_hidden_fields = '';

	foreach ($field_ary as $name => $vars)
	{
		if (is_array($vars))
		{
			foreach ($vars as $key => $value)
			{
				$s_hidden_fields .= '<input type="hidden" name="' . $name . '[' . $key . ']" value="' . $value . '" />';
			}
		}
		else
		{
			$s_hidden_fields .= '<input type="hidden" name="' . $name . '" value="' . $vars . '" />';
		}
	}

	return $s_hidden_fields;
}

// Get simple topic data
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

// Get simple post data
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

//
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