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
	var $modules;

	// Private methods, should not be overwritten
	function create($module_type, $module_url, $selected_mod = false, $selected_submod = false)
	{
		global $template, $auth, $db, $user;

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
				eval('$is_auth = (' . preg_replace(array('#acl_([a-z_]+)#e', '#cfg_([a-z_]+)#e'), array('(int) $auth->acl_get("\\1")', '(int) $config["\\1"]'), trim($row['module_acl'])) . ');');

				// The user is not authorised to use this module, skip it
				if (!$is_auth)
				{
					continue;
				}
			}

			$selected = ($row['module_filename'] == $selected_mod || $row['module_id'] == $selected_mod || (!$selected_mod && !$i)) ?  true : false;

			// Get the localised lang string if available, or make up our own otherwise
/*
			$template->assign_block_vars($module_type . '_section', array(
				'L_TITLE'		=> (isset($user->lang[strtoupper($module_type) . '_' . $row['module_title']])) ? $user->lang[strtoupper($module_type) . '_' . $row['module_title']] : ucfirst(str_replace('_', ' ', strtolower($row['module_title']))),
				'S_SELECTED'	=> $selected, 
				'U_TITLE'		=> $module_url . '&amp;i=' . $row['module_id'])
			);
*/
			$this->modules[intval($row['module_id'])] = array(
				'title'		=>	(isset($user->lang[strtoupper($module_type) . '_' . strtoupper($row['module_title'])])) ? $user->lang[strtoupper($module_type) . '_' . strtoupper($row['module_title'])] : ucfirst(str_replace('_', ' ', strtolower($row['module_title']))),
				'name'		=>	$row['module_filename'],
				'link'		=>	'&amp;i=' . $row['module_id'],
				'selected'	=>	$selected,
				'subs'		=>	array()
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
						$submodule = explode(',', trim($submodule));
						$submodule_title = array_shift($submodule);

						$is_auth = true;
						foreach ($submodule as $auth_option)
						{
							if (!$auth->acl_get($auth_option))
							{
								$is_auth = false;
							}
						}

						if (!$is_auth)
						{
							continue;
						}

						$selected = ($submodule_title == $selected_submod || (!$selected_submod && !$j)) ? true : false;

						// Get the localised lang string if available, or make up our own otherwise
/*
						$template->assign_block_vars("{$module_type}_section.{$module_type}_subsection", array(
							'L_TITLE'		=> (isset($user->lang[strtoupper($module_type) . '_' . strtoupper($submodule_title)])) ? $user->lang[strtoupper($module_type) . '_' . strtoupper($submodule_title)] : ucfirst(str_replace('_', ' ', strtolower($submodule_title))),
							'S_SELECTED'	=> $selected, 
							'U_TITLE'		=> $module_url . '&amp;i=' . $module_id . '&amp;mode=' . $submodule_title
						));
*/
						$this->modules[intval($row['module_id'])]['subs'][$submodule_title] = array(
							'title'		=>	(isset($user->lang[strtoupper($module_type) . '_' . strtoupper($submodule_title)])) ? $user->lang[strtoupper($module_type) . '_' . strtoupper($submodule_title)] : ucfirst(str_replace('_', ' ', strtolower($submodule_title))),
							'name'		=>	$submodule_title,
							'link'		=>	'&amp;i=' . $module_id . '&amp;mode=' . $submodule_title,
							'selected'	=>	$selected
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
		$this->url = $module_url;
	}

	function load($type = false, $name = false, $mode = false, $run = true)
	{
		global $template, $phpbb_root_path, $phpEx;

		if ($type)
		{
			$this->type = $type;
		}

		if ($name)
		{
			$this->name = $name;
		}

		if (!$mode && $this->mode)
		{
			$mode = $this->mode;
		}

		if (!class_exists($this->type . '_' . $this->name))
		{
			$filename = $phpbb_root_path . "includes/{$this->type}/{$this->type}_{$this->name}.$phpEx";
			include_once($filename);

			if (!class_exists($this->type . '_' . $this->name) && !file_exists($filename))
			{
				trigger_error('MODULE_NOT_EXIST');
			}

			if ($run)
			{
				eval("\$this->module = new {$this->type}_{$this->name}(\$this->id, \$mode);");

				$vars = array('modules', 'id', 'name', 'type', 'url', 'mode');
				foreach ($vars as $var)
				{
					$this->module->$var = $this->$var;
				}

				// Shortcut to submodules
				$this->module->subs =& $this->module->modules[$this->id]['subs'];

				if (method_exists($this->module, 'init'))
				{
					$this->module->init();
				}
				if (method_exists($this->module, 'main'))
				{
					$this->module->main($mode);
				}

				// We're not exactly supposed to ever get there
				$template->assign_var('MESSAGE', 'This mode is currently unavailable');
				$this->display('An error occured', 'mcp_front.html');
			}
		}
	}

	// Displays the appropriate template with the given title
	function display($page_title, $tpl_name)
	{
		global $template;

		// This method is used to put variables in menu titles
		if (method_exists($this, 'alter_menu'))
		{
			$this->alter_menu();
		}

		foreach ($this->modules as $id => $section_data)
		{
			$template->assign_block_vars($this->type . '_section', array(
				'L_TITLE'		=> $section_data['title'],
				'S_SELECTED'	=> ($id == $this->id) ?  TRUE : FALSE, 
				'U_TITLE'		=> $this->url . $section_data['link'])
			);

			foreach ($section_data['subs'] as $sub)
			{
				$template->assign_block_vars("{$this->type}_section.{$this->type}_subsection", array(
					'L_TITLE'		=> $sub['title'],
					'S_SELECTED'	=> ($sub['name'] == $this->mode) ? TRUE : FALSE, 
					'U_TITLE'		=> $this->url . $sub['link']
				));
			}
		}

		page_header($page_title);

		$template->set_filenames(array(
			'body' => $tpl_name)
		);

		page_footer();
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

class mcp extends module
{
	var $forum_id = 0;
	var $topic_id = 0;
	var $post_id = 0;
	var $topic_id_list = array();
	var $post_id_list = array();

	function get_topic_ids($acl_list = '')
	{
		if (!$this->topic_id_list)
		{
			return;
		}

		global $auth, $db;
		$topic_ids = array();

		$sql = 'SELECT topic_id, forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id IN (' . implode(', ', $this->topic_id_list) . ')';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$acl_list || $auth->acl_get($acl_list, $row['forum_id']))
			{
				$topic_ids[] = $row['topic_id'];
			}
		}

		return $topic_ids;
	}

	function get_post_ids($acl_list = '')
	{
		if (!$this->post_id_list)
		{
			return;
		}

		global $auth, $db;
		$post_ids = array();

		$sql = 'SELECT post_id, forum_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id IN (' . implode(', ', $this->post_id_list) . ')';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$acl_list || $auth->acl_get($acl_list, $row['forum_id']))
			{
				$post_ids[] = $row['post_id'];
			}
		}

		return $post_ids;
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

		if (empty($rowset))
		{
			return FALSE;
		}
		elseif (is_array($forum_id))
		{
			return $rowset;
		}
		else
		{
			return array_pop($rowset);
		}
	}

	function get_topic_data($topic_id, $acl_list = '')
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
			elseif (!$row['topic_approved'])
			{
				// TODO: should moderators without m_approve be allowed to perform any action of unapproved items?
				continue;
			}

			$rowset[$row['topic_id']] = $row;
		}

		if (empty($rowset))
		{
			// DEBUG
			global $template;
			$template->assign_var('MESSAGE', 'Error while retrieving topic data #' . $topic_id);
			// -----

			return FALSE;
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

	function get_post_data($post_id, $acl_list = '')
	{
		global $auth, $db;
		$rowset = array();

		// DEBUG: won't probably work on global announcements
		$sql = 'SELECT p.*, u.*, t.*, f.*
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
			WHERE p.post_id ' . ((is_array($post_id)) ? 'IN (' . implode(', ', $post_id) . ')' : "= $post_id") . '
				AND u.user_id = p.poster_id
				AND t.topic_id = p.topic_id
				AND f.forum_id = p.forum_id';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			if ($acl_list && !$auth->acl_get($acl_list, $row['forum_id']))
			{
				continue;
			}
			if (!$row['post_approved'] && !$auth->acl_get('m_approve', $row['forum_id']))
			{
				// TODO: should moderators without m_approve be allowed to perform any action of unapproved items?
				continue;
			}

			$rowset[$row['post_id']] = $row;
		}

		if (empty($rowset))
		{
			return FALSE;
		}
		elseif (is_array($post_id))
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
		$this->forum_id = request_var('f', 0);
		$this->topic_id = request_var('t', 0);
		$this->post_id = request_var('p', 0);

		$this->topic_id_list = ($this->topic_id) ? array($this->topic_id) : array();
		$this->post_id_list = ($this->post_id) ? array($this->post_id) : array();

		$this->to_forum_id = request_var('to_forum_id', 0);
		$this->to_topic_id = request_var('to_topic_id', 0);

		$this->cancel = request_var('cancel', FALSE);
		$this->confirm = ($this->cancel) ? FALSE : request_var('confirm', FALSE);
		$this->quickmod = request_var('quickmod', FALSE);

		$this->view = request_var('view', '');
		$this->start = request_var('start', 0);
		$this->action = request_var('action', '');
		if (is_array($this->action))
		{
			list($this->action, $void) = each($this->action);
		}

		// Cleanse input
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

		// Put the forum_id and al in the url
		if (!$this->post_id && !empty($this->post_id_list))
		{
			$this->post_id = $this->post_id_list[0];
		}
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

		if (!$this->topic_id && !empty($this->topic_id_list))
		{
			$this->topic_id = $this->topic_id_list[0];
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
		$result = $db->sql_query($sql);

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

		$sort_days = request_var('sort_days', '0');
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

function return_link($msg, $url)
{
	global $template, $user;

	$template->assign_block_vars('return_links', array(
		'U_LINK'		=>	$url,
		'MESSAGE_LINK'	=>	sprintf($user->lang[$msg], '<a href="' . $url . '">', '</a>')
	));
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
$module = request_var('i', 'main');
if (isset($_POST['jumpbox']) || isset($_POST['sort']) || isset($_POST['confirm']) || isset($_POST['cancel']))
{
	// Sometimes we want to ignore input from the dropdown list of modes
	// when the jumpbox is used or when confirming an action for example
	$mode = request_var('mode', '', 'GET');
}
elseif (isset($_POST['mode']) && is_array($_POST['mode']))
{
	list($mode, $void) = each($_POST['mode']);
	$mode = htmlspecialchars($mode);
}
else
{
	$mode = request_var('mode', '');
}

// Instantiate module system and generate list of available modules
$mcp->create('mcp', "mcp.$phpEx$SID", $module, $mode);

// Load and execute the relevant module
$mcp->load('mcp', false, $mode, TRUE);

?>