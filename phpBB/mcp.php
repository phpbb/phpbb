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

/*
CREATE TABLE phpbb_modules (
  module_id mediumint(8) NOT NULL auto_increment,
  module_type char(3) NOT NULL default '',
  module_title varchar(50) NOT NULL default '',
  module_filename varchar(50) NOT NULL default '',
  module_order mediumint(4) NOT NULL default '0',
  module_enabled tinyint(1) unsigned NOT NULL default '1',
  module_acl varchar(255) NOT NULL default '',
  PRIMARY KEY  (module_id),
  KEY module_type (module_type,module_enabled)
);

INSERT INTO phpbb_modules VALUES (6, 'mcp', 'MAIN', 'main', 1, 1, '');
*/
@define('MODULES_TABLE', $table_prefix . 'modules');

// ---------
// FUNCTIONS
//
class module
{
	var $module_id = 0;
	var $module_url;
	var $modules = array();
	var $submodules = array();

	function module($module_type, $module_url, $selected)
	{
		global $auth, $db, $phpbb_root_path, $phpEx, $user;

		$sql = 'SELECT module_id, module_title, module_filename, module_acl
			FROM ' . MODULES_TABLE . "
			WHERE module_type = '{$module_type}'
				AND module_enabled = 1
			ORDER BY module_order ASC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['module_acl'])
			{
				// Authorisation is required

				$is_auth = FALSE;
				foreach (explode(',', $row['module_acl']) as $auth_option)
				{
					if ($auth->acl_get($auth_option))
					{
						$is_auth = TRUE;
						break;
					}
				}
				if (!$is_auth)
				{
					// The user is not authorised to use this module, skip it
					continue;
				}
			}

			if ($row['module_filename'] == $selected || $row['module_id'] == $selected)
			{
				$module_id = intval($row['module_id']);
				$module_name = $row['module_filename'];
			}

			// Get the localised lang string if available, or make up our own otherwise
			$title = (isset($user->lang[strtoupper($module_type) . '_' . $row['module_title']])) ? $user->lang[strtoupper($module_type) . '_' . $row['module_title']] : ucfirst(str_replace('_', ' ', strtolower($row['module_title'])));

			$modules[intval($row['module_id'])] = array(
				'title'	=>	$title,
				'name'	=>	$row['module_filename'],
				'link'	=>	'&amp;i=' . $row['module_id']
			);
		}
		$db->sql_freeresult($result);

		if (empty($module_id))
		{
			trigger_error('MODULE_NOT_EXIST');
		}

		require($phpbb_root_path . "includes/{$module_type}/{$module_type}_{$module_name}.$phpEx");
		eval("\$this->module = new {$module_type}_{$module_name}(\$module_id);");

		$this->module->modules = $modules;
		$this->module->module_url = $module_url;
	}

	// This generates the block template variable for outputting the list
	// of submodules, should be called with an associative array of modules
	// in the form 'LANG_STRING' => 'LINK'
	function menu($selected)
	{
		global $template, $user;

		foreach ($this->modules as $module_id => $section_data)
		{
			$template->assign_block_vars($this->module_type . '_section', array(
				'L_TITLE'		=> $section_data['title'],
				'S_SELECTED'	=> ($module_id == $this->module_id) ? TRUE : FALSE, 
				'U_TITLE'		=> $this->url . $section_data['link'])
			);

			if ($module_id == $this->module_id)
			{
				foreach ($this->submodules as $title => $module_link)
				{
					// Get the localised lang string if available, or make up our own otherwise
					$section_title = (isset($user->lang[$title])) ? $user->lang[$title] : ucfirst(str_replace('_', ' ', strtolower($title)));

					$template->assign_block_vars("{$this->module_type}_section.{$this->module_type}_subsection", array(
						'L_TITLE'		=> $section_title,
						'S_SELECTED'	=> ($title == $selected) ? TRUE : FALSE, 
						'U_TITLE'		=> $this->url . $module_link
					));
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

	function message_die($msg)
	{
		global $template, $user;

		if (isset($user->lang[$msg]))
		{
			$msg = $user->lang[$msg];
		}

		$template->assign_vars(array(
			'MESSAGE_TITLE'	=>	$user->lang['MESSAGE'],
			'MESSAGE_TEXT'	=>	$msg
		));
		$this->display('MCP', 'mcp_message.html');
	}
}

class mcp extends module
{
	var $module_type = 'mcp';
	var $forum_id = 0;
	var $topic_id = 0;
	var $post_id = 0;

	function get_forum_data($forum_id, $acl_list = '', $return_on_error = FALSE)
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

		if (!$return_on_error && empty($rowset))
		{
			$this->message_die('FORUM_NOT_EXIST');
		}

		if (is_array($forum_id))
		{
			return $rowset;
		}
		else
		{
			return array_pop($rowset);
		}
	}

	function get_topic_data($topic_id, $acl_list = '', $return_on_error = FALSE)
	{
		global $auth, $db;
		$rowset = array();

		// TODO: Known Bug: will fail on global announcements because of forum_id = 0
		$sql = 'SELECT t.*, f.*
			FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
			WHERE t.topic_id ' . ((is_array($topic_id)) ? 'IN (' . implode(', ', $topic_id) . ')' : "= $topic_id") . '
				AND t.forum_id = f.forum_id';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			if ($acl_list && !$auth->acl_get($acl_list, $row['forum_id']))
			{
				continue;
			}
			if ($auth->acl_get('m_approve', $row['forum_id']))
			{
				$row['topic_replies'] = $row['topic_replies_real'];
				$row['forum_topics'] = $row['forum_topics_real'];
			}

			$rowset[$row['topic_id']] = $row;
		}

		if (!$return_on_error && empty($rowset))
		{
			$this->message_die('TOPIC_NOT_EXIST');
		}

		if (is_array($topic_id))
		{
			return $rowset;
		}
		else
		{
			return array_pop($rowset);
		}
	}

	function mcp_init()
	{
		global $db;

		// Obtain initial var settings
		$this->forum_id = (isset($_REQUEST['f'])) ? max(0, intval($_REQUEST['f'])) : 0;
		$this->topic_id = (!empty($_REQUEST['t'])) ? intval($_REQUEST['t']) : 0;
		$this->post_id = (!empty($_REQUEST['p'])) ? intval($_REQUEST['p']) : 0;

		$this->topic_id_list = ($this->topic_id) ? array($this->topic_id) : array();
		$this->post_id_list = ($this->post_id) ? array($this->post_id) : array();

		$this->to_forum_id = (!empty($_REQUEST['to_forum_id'])) ? intval($_REQUEST['to_forum_id']) : 0;
		$this->to_topic_id = (!empty($_REQUEST['to_topic_id'])) ? intval($_REQUEST['to_topic_id']) : 0;

		$this->confirm = (!empty($_POST['confirm'])) ? TRUE : FALSE;
		$this->action = (!empty($_REQUEST['action'])) ? $_REQUEST['action'] : '';
		$this->quickmod = (!empty($_REQUEST['quickmod'])) ? TRUE : FALSE;

		// Put the forum_id and al in the url
		if ($this->post_id)
		{
			$this->url .= '&amp;p=' . $this->post_id;

			if (!$this->forum_id || !$this->topic_id)
			{
				$sql = 'SELECT forum_id, topic_id
					FROM ' . POSTS_TABLE . '
					WHERE post_id = ' . $this->post_id;
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					if (!$this->forum_id)
					{
						$this->forum_id = intval($row['forum_id']);
					}
					if (!$this->topic_id)
					{
						$this->topic_id = intval($row['topic_id']);
					}
				}
			}
		}
		if ($this->topic_id)
		{
			$this->url .= '&amp;t=' . $this->topic_id;

			if (!$this->forum_id)
			{
				$sql = 'SELECT forum_id
					FROM ' . TOPICS_TABLE . '
					WHERE topic_id = ' . $this->topic_id;
				$result = $db->sql_query($sql);
				$this->forum_id = intval($db->sql_fetchfield('forum_id', 0, $result));
			}
		}
		if ($this->forum_id)
		{
			$this->url .= '&amp;f=' . $this->forum_id;
		}

		// Cleanse inputted values
		if (!empty($_POST['topic_id_list']) && is_array($_POST['topic_id_list']))
		{
			foreach ($_POST['topic_id_list'] as $t_id)
			{
				if ($t_id = intval($t_id))
				{
					$this->topic_id_list[] = $t_id;
				}
			}
		}
		if (!empty($_POST['post_id_list']) && is_array($_POST['post_id_list']))
		{
			foreach ($_POST['post_id_list'] as $p_id)
			{
				if ($p_id = intval($p_id))
				{
					$this->post_id_list[] = $p_id;
				}
			}
		}

		// Build short_id_list
		if (!empty($_GET['selected_ids']))
		{
			$len = $_GET['selected_ids']{0};
			for ($i = 1; $i < strlen($_GET['selected_ids']); $i += $len)
			{
				$short = substr($_GET['selected_ids'], $i, $len);
				$this->post_id_list[] = base_convert($short, 36, 10);
			}
		}
		$this->selected_ids = (!empty($this->post_id_list)) ? '&amp;selected_ids=' . $this->short_id_list($this->post_id_list) : '';
	}

	function mcp_jumpbox($action, $acl_list = 'f_list', $forum_id = false, $enable_select_all = false)
	{
		global $auth, $template, $user, $db, $phpEx, $SID;

		$sql = 'SELECT forum_id, forum_name, forum_type, left_id, right_id
			FROM ' . FORUMS_TABLE . '
			ORDER BY left_id ASC';
		$result = $db->sql_query($sql, 120);

		$right = $cat_right = 0;
		$padding = $forum_list = $holding = '';
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
			{
				// Non-postable forum with no subforums, don't display
				continue;
			}

			if (!$auth->acl_get('f_list', $row['forum_id']))
			{
				// if the user does not have permissions to list this forum skip
				continue;
			}

			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = substr($padding, 0, -13 * ($row['left_id'] - $right + 1));
			}

			$right = $row['right_id'];

			$selected = ($row['forum_id'] == $forum_id) ? ' selected="selected"' : '';

			if ($row['right_id'] - $row['left_id'] > 1)
			{
				$cat_right = max($cat_right, $row['right_id']);
				$char = '+ ';
			}
			else
			{
				$char = '- ';
			}

			$template->assign_block_vars('options', array(
				'VALUE'		=>	($row['forum_type'] != FORUM_POST || !$auth->acl_gets($acl_list, $row['forum_id'])) ? -1 : $row['forum_id'],
				'SELECTED'	=>	$selected,
				'TEXT'		=>	$padding . $char . $row['forum_name'])
			);
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_JUMPBOX_ACTION'		=>	$action,
			'S_MCP_ACTION'			=>	$action,
			'S_ENABLE_SELECT_ALL'	=>	$enable_select_all,
			'S_CURRENT_FORUM'		=>	intval($forum_id)
		));
	}

	function short_id_list($id_list)
	{
		$max_len = 0;
		$short_id_list = array();

		foreach ($id_list as $id)
		{
			$short = (string) base_convert($id, 10, 36);
			$max_len = max(strlen($short), $max_len);
			$short_id_list[] = $short;
		}

		$id_str = (string) $max_len;
		foreach ($short_id_list as $short)
		{
			$id_str .= str_pad($short, $max_len, '0', STR_PAD_LEFT);
		}

		return $id_str;
	}

	function mcp_sorting($mode, &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id = 0, $topic_id = 0, $where_sql = 'WHERE')
	{
		global $db, $user, $auth, $template;

		$sort_days = (!empty($_REQUEST['st'])) ? max(intval($_REQUEST['st']), 0) : 0;
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
						AND topic_type <> " . POST_ANNOUNCE . "
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

			case 'unapproved':
				$type = 'posts';
				$default_key = 't';
				$default_dir = 'd';
				$sql = 'SELECT COUNT(post_id) AS total
					FROM ' . POSTS_TABLE . "
					$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_approve'))) . ')
						AND post_approved = 0
						AND post_time >= ' . $min_time;
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
				elseif ($forum_id)
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

		$sort_key = (!empty($_REQUEST['sk'])) ? htmlspecialchars($_REQUEST['sk']) : $default_key;
		$sort_dir = (!empty($_REQUEST['sd'])) ? htmlspecialchars($_REQUEST['sd']) : $default_dir;
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
				$sort_by_sql = array('a' => 'u.username', 't' => 'p.post_time', 's' => 'p.post_subject');
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

		$s_limit_days = $s_sort_key = $s_sort_dir = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir);

		$template->assign_vars(array(
			'S_SELECT_SORT_DIR'	=>	$s_sort_dir,
			'S_SELECT_SORT_KEY' =>	$s_sort_key,
			'S_SELECT_SORT_DAYS'=>	$s_limit_days
		));

		if (($sort_days && $mode != 'viewlogs') || $mode == 'reports')
		{
			$result = $db->sql_query($sql);
			$total = ($row = $db->sql_fetchrow($result)) ? $row['total'] : 0;
		}
		else
		{
			$total = -1;
		}
	}
}
// FUNCTIONS
// ---------


// Start session management
$user->start();
$auth->acl($user->data);

$user->setup();

// Basic parameter data
$module = (!empty($_REQUEST['i'])) ? intval($_REQUEST['i']) : 0;
$start = (!empty($_REQUEST['start'])) ? intval($_REQUEST['start']) : 0;
if (!empty($_REQUEST['mode']))
{
	if (is_array($_REQUEST['mode']))
	{
		list($mode, $void) = each($_REQUEST['mode']);
	}
	else
	{
		$mode = $_REQUEST['mode'];
	}
}
else
{
	$mode = 'front';
}

// Basic "global" modes
if (!$module)
{
	switch ($mode)
	{
		// NOTE: below are basic modes that must not require a module_id to ne passed
		case 'topic_view':
		case 'post_details':
		case 'approve':
			// used in viewtopic.php

		case 'split':
		case 'delete':
		case 'merge':
		case 'move':
		case 'fork':
		case 'make_normal':
		case 'make_sticky':
		case 'make_announce':
		case 'make_global':
			// quick-mod

		case 'forum_view':
		case 'front':
		default:
			$module = 'main';
			break;

	}
}

// Instantiate a new mcp object
// NOTE: if $module is an integer, the module corresponding to this module_id will be loaded
//       if it's a string, the module of this name will be loaded
$mcp = new module('mcp', "mcp.$phpEx$SID", $module);
$mcp->module->main($mode);

?>