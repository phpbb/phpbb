<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_styles
{
	var $u_action;

	var $style_cfg;
	var $template_cfg;
	var $theme_cfg;
	var $imageset_cfg;
	var $imageset_keys;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/styles');

		$this->tpl_name = 'acp_styles';
		$this->page_title = 'ACP_CAT_STYLES';

		$action = request_var('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;
		$style_id = request_var('id', 0);

		$this->u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		// Fill the configuration variables
		$this->style_cfg = $this->template_cfg = $this->theme_cfg = $this->imageset_cfg = '
#
# phpBB {MODE} configuration file
#
# @package phpBB3
# @copyright (c) 2005 phpBB Group 
# @license http://opensource.org/licenses/gpl-license.php GNU Public License 
#
#
# At the left is the name, please do not change this
# At the right the value is entered
# For on/off options the valid values are on, off, 1, 0, true and false
#
# Values get trimmed, if you want to add a space in front or at the end of
# the value, then enclose the value with single or double quotes. 
# Single and double quotes do not need to be escaped.
#
# 

# General Information about this {MODE}
name = {NAME}
copyright = {COPYRIGHT}
version = {VERSION}
';

		$this->theme_cfg .= '
# Some configuration options

#
# You have to turn this option on if you want to use the 
# path template variables ({T_IMAGESET_PATH} for example) within
# your css file.
# This is mostly the case if you want to use language specific
# images within your css file.
#
parse_css_file = {PARSE_CSS_FILE}

#
# This option defines the pagination seperator in templates.
#
pagination_sep = \'{PAGINATION_SEP}\'
';

		$this->imageset_keys = 'site_logo, btn_post, btn_post_pm, btn_reply, btn_reply_pm, btn_locked, btn_profile, btn_pm, btn_delete, btn_info, btn_quote, btn_search, btn_edit, btn_report, btn_email, btn_www, btn_icq, btn_aim, btn_yim, btn_msnm, btn_jabber, btn_online, btn_offline, btn_friend, btn_foe, icon_unapproved, icon_reported, icon_attach, icon_post, icon_post_new, icon_post_latest, icon_post_newest, forum, forum_new, forum_locked, forum_link, sub_forum, sub_forum_new, folder, folder_moved, folder_posted, folder_new, folder_new_posted, folder_hot, folder_hot_posted, folder_hot_new, folder_hot_new_posted, folder_locked, folder_locked_posted, folder_locked_new, folder_locked_new_posted, folder_sticky, folder_sticky_posted, folder_sticky_new, folder_sticky_new_posted, folder_announce, folder_announce_posted, folder_announce_new, folder_announce_new_posted, folder_global, folder_global_posted, folder_global_new, folder_global_new_posted, poll_left, poll_center, poll_right, attach_progress_bar, user_icon1, user_icon2, user_icon3, user_icon4, user_icon5, user_icon6, user_icon7, user_icon8, user_icon9, user_icon10';

		// Execute overall actions
		switch ($action)
		{
			case 'delete':
				if ($style_id)
				{
					$this->remove($mode, $style_id);
					return;
				}
			break;

			case 'export':
				if ($style_id)
				{
					$this->export($mode, $style_id);
					return;
				}
			break;
		
			case 'add':
			case 'install':
				install($mode, $action, $style_id);
			break;

			case 'details':
				if ($style_id)
				{
					$this->details($mode, $style_id);
					return;
				}
			break;
		}

		switch ($mode)
		{
			case 'style':
		
				switch ($action)
				{
					case 'activate':
					case 'deactivate':

						if ($style_id == $config['default_style'])
						{
							trigger_error($user->lang['DEACTIVATE_DEFAULT'] . adm_back_link($this->u_action));
						}

						$sql = 'UPDATE ' . STYLES_TABLE . '
							SET style_active = ' . (($action == 'activate') ? 1 : 0) . ' 
							WHERE style_id = ' . $style_id;
						$db->sql_query($sql);

						// Set style to default for any member using deactivated style
						if ($action == 'deactivate')
						{
							$sql = 'UPDATE ' . USERS_TABLE . ' 
								SET user_style = ' . $config['default_style'] . " 
								WHERE user_style = $style_id";
							$db->sql_query($sql);
						}
					break;
				}

				$this->frontend('style', array('export', 'delete'));
			break;

			case 'template':

				$this->frontend('template', array('cache', 'details', 'refresh', 'export', 'delete'));
			break;

			case 'theme':

				$this->frontend('theme', array('details', 'refresh', 'export', 'delete'));
			break;

			case 'imageset':

				$this->frontend('imageset', array('details', 'delete', 'export'));
			break;
		}
	}

	/**
	* Build Frontend with supplied options
	*/
	function frontend($mode, $options)
	{
		global $user, $template, $db, $config, $phpbb_root_path, $phpEx, $SID;

		$sql_from = '';
		$style_count = array();

		switch ($mode)
		{
			case 'style':
				$sql_from = STYLES_TABLE;

				$sql = 'SELECT user_style, COUNT(user_style) AS style_count
					FROM ' . USERS_TABLE . ' 
					GROUP BY user_style';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$style_count[$row['user_style']] = $row['style_count'];
				}
				$db->sql_freeresult($result);

			break;
	
			case 'template':
				$sql_from = STYLES_TPL_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_CSS_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGE_TABLE;
			break;
		}

		$l_prefix = strtoupper($mode);

		$this->page_title = 'ACP_' . $l_prefix . 'S';

		$template->assign_vars(array(
			'S_FRONTEND'		=> true,
			'S_STYLE'			=> ($mode == 'style') ? true : false,

			'L_TITLE'			=> $user->lang[$this->page_title],
			'L_EXPLAIN'			=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'			=> $user->lang[$l_prefix . '_NAME'],
			'L_INSTALLED'		=> $user->lang['INSTALLED_' . $l_prefix],
			'L_UNINSTALLED'		=> $user->lang['UNINSTALLED_' . $l_prefix],
			'L_NO_UNINSTALLED'	=> $user->lang['NO_UNINSTALLED_' . $l_prefix],
			'L_CREATE'			=> $user->lang['CREATE_' . $l_prefix],

			'U_ACTION'			=> $this->u_action,
			)
		);

		$sql = "SELECT *  
			FROM $sql_from";
		$result = $db->sql_query($sql);

		$installed = array();

		$basis_options = '<option class="sep" value="">' . $user->lang['OPTIONAL_BASIS'] . '</option>';
		while ($row = $db->sql_fetchrow($result))
		{
			$installed[] = $row[$mode . '_name'];
			$basis_options .= '<option value="' . $row[$mode . '_id'] . '">' . $row[$mode . '_name'] . '</option>';
			
			$stylevis = ($mode == 'style' && !$row['style_active']) ? 'activate' : 'deactivate';

			$s_options = array();
			foreach ($options as $option)
			{
				$s_options[] = '<a href="' . $this->u_action . "&amp;action=$option&amp;id=" . $row[$mode . '_id'] . '">' . $user->lang[strtoupper($option)] . '</a>';
			}
			
			$template->assign_block_vars('installed', array(
				'S_DEFAULT_STYLE'		=> ($mode == 'style' && $row['style_id'] == $config['default_style']) ? true : false,
				'U_EDIT'				=> $this->u_action . '&amp;action=' . (($mode == 'style') ? 'details' : 'edit') . '&amp;id=' . $row[$mode . '_id'],
				'U_STYLE_ACT_DEACT'		=> $this->u_action . '&amp;action=' . $stylevis . '&amp;id=' . $row[$mode . '_id'],
				'L_STYLE_ACT_DEACT'		=> $user->lang['STYLE_' . strtoupper($stylevis)],
				'S_OPTIONS'				=> implode(' | ', $s_options),
				'U_PREVIEW'				=> "{$phpbb_root_path}index.$phpEx$SID&amp;$mode=" . $row[$mode . '_id'],

				'NAME'					=> $row[$mode . '_name'],
				'STYLE_COUNT'			=> ($mode == 'style' && isset($style_count[$row['style_id']])) ? $style_count[$row['style_id']] : 0,
				)
			);
		}
		$db->sql_freeresult($result);

		// Grab uninstalled items
		$new_ary = $cfg = array();
		
		/**
		* @todo grab templates/themes/imagesets from style directories
		*/
		$dp = opendir("{$phpbb_root_path}styles");
		while ($file = readdir($dp))
		{
			$subpath = ($mode != 'style') ? "$mode/" : '';
			if ($file{0} != '.' && file_exists("{$phpbb_root_path}styles/$file/$subpath$mode.cfg"))
			{
				if ($cfg = file("{$phpbb_root_path}styles/$file/$subpath$mode.cfg"))
				{
					$items = parse_cfg_file('', $cfg);
					$name = (isset($items['name'])) ? trim($items['name']) : false;

					if ($name && !in_array($name, $installed))
					{
						$new_ary[] = array(
							'path'		=> $file,
							'name'		=> $name,
							'copyright'	=> $items['copyright'],
						);
					}
				}
			}
		}
		unset($installed);
		@closedir($dp);

		if (sizeof($new_ary))
		{
			foreach ($new_ary as $cfg)
			{
				$template->assign_block_vars('uninstalled', array(
					'NAME'			=> $cfg['name'],
					'COPYRIGHT'		=> $cfg['copyright'],
					'U_INSTALL'		=> $this->u_action . '&amp;action=install&amp;path=' . urlencode($cfg['path']))
				);
			}
		}
		unset($new_ary);

		$template->assign_vars(array(
			'S_BASIS_OPTIONS'		=> $basis_options)
		);

	}

	/**
	* Remove style/template/theme/imageset
	*/
	function remove($mode, $style_id)
	{
		global $db, $template, $user, $phpbb_root_path, $cache;
	
		$new_id = request_var('new_id', 0);
		$update = (isset($_POST['update'])) ? true : false;

		switch ($mode)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
				$sql_select = 'style_name';
			break;

			case 'template':
				$sql_from = STYLES_TPL_TABLE;
				$sql_select = 'template_name, template_path, template_storedb';
			break;

			case 'theme':
				$sql_from = STYLES_CSS_TABLE;
				$sql_select = 'theme_name, theme_path, theme_storedb';
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGE_TABLE;
				$sql_select = 'imageset_name, imageset_path';
			break;
		}

		$l_prefix = strtoupper($mode);
		
		$sql = "SELECT $sql_select
			FROM $sql_from 
			WHERE {$mode}_id = $style_id";
		$result = $db->sql_query($sql);
		$style_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$style_row)
		{
			trigger_error($user->lang['NO_' . $l_prefix] . adm_back_link($this->u_action));
		}
		
		$sql = "SELECT {$mode}_id, {$mode}_name 
			FROM $sql_from  
			WHERE {$mode}_id <> $style_id 
			ORDER BY {$mode}_name ASC";
		$result = $db->sql_query($sql);

		$s_options = '';

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$s_options .= '<option value="' . $row[$mode . '_id'] . '">' . $row[$mode . '_name'] . '</option>';
			}
			while ($row = $db->sql_fetchrow($result));
		}
		else
		{
			trigger_error($user->lang['ONLY_' . $l_prefix] . adm_back_link($this->u_action));
		}
		$db->sql_freeresult($result);

		if ($update)
		{
			$sql = "DELETE FROM $sql_from 
				WHERE {$mode}_id = $style_id";
			$db->sql_query($sql);

			if ($mode == 'style')
			{
				$sql = 'UPDATE ' . USERS_TABLE . " 
					SET user_style = $new_id
					WHERE user_style = $style_id";
				$db->sql_query($sql);
			}
			else
			{
				$sql = 'UPDATE ' . STYLES_TABLE . " 
					SET {$mode}_id = $new_id 
					WHERE {$mode}_id = $style_id";
				$db->sql_query($sql);
			}

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_' . $l_prefix . '_DELETE', $style_row[$mode . '_name']);
			$message = ($mode != 'style') ? $l_prefix . '_DELETED_FS' : $l_prefix . '_DELETED';
			trigger_error($user->lang[$message] . adm_back_link($this->u_action));
		}

		$this->page_title = 'DELETE_' . $l_prefix;

		$template->assign_vars(array(
			'S_DELETE'			=> true,
			'S_REPLACE_OPTIONS'	=> $s_options,

			'L_TITLE'			=> $user->lang[$this->page_title],
			'L_EXPLAIN'			=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'			=> $user->lang[$l_prefix . '_NAME'],
			'L_REPLACE'			=> $user->lang['REPLACE_' . $l_prefix],
			'L_REPLACE_EXPLAIN'	=> $user->lang['REPLACE_' . $l_prefix . '_EXPLAIN'],

			'U_ACTION'		=> $this->u_action . "&amp;action=delete&amp;id=$style_id",
			'U_BACK'		=> $this->u_action,
			
			'NAME'			=> $style_row[$mode . '_name'],
			)
		);
	}

	/**
	* Export style or style elements
	*/
	function export($mode, $style_id)
	{
		global $db, $template, $user, $phpbb_root_path, $cache, $phpEx, $config;

		$update = (isset($_POST['update'])) ? true : false;

		$inc_template = request_var('inc_template', 0);
		$inc_theme = request_var('inc_theme', 0);
		$inc_imageset = request_var('inc_imageset', 0);
		$store = request_var('store', 0);
		$format = request_var('format', '');

		$error = array();
		$methods = array('tar');

		$available_methods = array('tar.gz' => 'zlib', 'tar.bz2' => 'bz2', 'zip' => 'zlib');
		foreach ($available_methods as $type => $module)
		{
			if (!@extension_loaded($module))
			{
				continue;
			}

			$methods[] = $type;
		}

		if (!in_array($format, $methods))
		{
			$format = 'tar';
		}

		switch ($mode)
		{
			case 'style':
				if ($update && ($inc_template + $inc_theme + $inc_imageset) < 1)
				{
					$error[] = $user->lang['STYLE_ERR_MORE_ELEMENTS'];
				}

				$name = 'style_name';

				$sql_select = 's.style_id, s.style_name, s.style_copyright';
				$sql_select .= ($inc_template) ? ', t.*' : ', t.template_name';
				$sql_select .= ($inc_theme) ? ', c.*' : ', c.theme_name';
				$sql_select .= ($inc_imageset) ? ', i.*' : ', i.imageset_name';
				$sql_from = STYLES_TABLE . ' s, ' . STYLES_TPL_TABLE . ' t, ' . STYLES_CSS_TABLE . ' c, ' . STYLES_IMAGE_TABLE . ' i';
				$sql_where = "s.style_id = $style_id AND t.template_id = s.template_id AND c.theme_id = s.theme_id AND i.imageset_id = s.imageset_id";

				$l_prefix = 'STYLE';
			break;

			case 'template':
				$name = 'template_name';

				$sql_select = '*';
				$sql_from = STYLES_TPL_TABLE;
				$sql_where = "template_id = $style_id";

				$l_prefix = 'TEMPLATE';
			break;

			case 'theme':
				$name = 'theme_name';

				$sql_select = '*';
				$sql_from = STYLES_CSS_TABLE;
				$sql_where = "theme_id = $style_id";

				$l_prefix = 'THEME';
			break;

			case 'imageset':
				$name = 'imageset_name';

				$sql_select = '*';
				$sql_from = STYLES_IMAGE_TABLE;
				$sql_where = "imageset_id = $style_id";

				$l_prefix = 'IMAGESET';
			break;
		}
		
		if ($update && !sizeof($error))
		{
			$sql = "SELECT $sql_select 
				FROM $sql_from 
				WHERE $sql_where";
			$result = $db->sql_query($sql);
			$style_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$style_row)
			{
				trigger_error($user->lang['NO_' . $l_prefix] . adm_back_link($this->u_action));
			}

			$var_ary = array('style_id', 'style_name', 'style_copyright', 'template_id', 'template_name', 'template_path', 'template_copyright', 'template_storedb', 'bbcode_bitfield', 'theme_id', 'theme_name', 'theme_path', 'theme_copyright', 'theme_storedb', 'theme_mtime', 'theme_data', 'imageset_id', 'imageset_name', 'imageset_path', 'imageset_copyright');

			foreach ($var_ary as $var)
			{
				if (!isset($style_row[$var]))
				{
					$style_row[$var] = '';
				}
			}
		
			$files = $data = array();

			if ($mode == 'style')
			{
				$style_cfg = str_replace(array('{MODE}', '{NAME}', '{COPYRIGHT}', '{VERSION}'), array($mode, $style_row['style_name'], $style_row['style_copyright'], $config['version']), $this->style_cfg);
				
				$style_cfg .= (!$inc_template) ? "\ntemplate = {$style_row['template_name']}" : '';
				$style_cfg .= (!$inc_theme) ? "\ntheme = {$style_row['theme_name']}" : '';
				$style_cfg .= (!$inc_imageset) ? "\nimageset = {$style_row['imageset_name']}" : '';

				$data[] = array(
					'src'		=> $style_cfg, 
					'prefix'	=> 'style.cfg'
				);

				unset($style_cfg);
			}

			// Export template core code
			if ($mode == 'template' || $inc_template)
			{
				$template_cfg = str_replace(array('{MODE}', '{NAME}', '{COPYRIGHT}', '{VERSION}'), array($mode, $style_row['template_name'], $style_row['template_copyright'], $config['version']), $this->template_cfg);
				$template_cfg .= "\nbbcode_bitfield = {$style_row['bbcode_bitfield']}";

				$data[] = array(
					'src'		=> $template_cfg, 
					'prefix'	=> 'template/template.cfg'
				);

				// This is potentially nasty memory-wise ...
				if (!$style_row['template_storedb'])
				{
					$files[] = array(
						'src'		=> "styles/{$style_row['template_path']}/template/", 
						'prefix-'	=> "styles/{$style_row['template_path']}/", 
						'prefix+'	=> false, 
						'exclude'	=> 'template.cfg'
					);
				}
				else
				{
					$sql = 'SELECT template_filename, template_data  
						FROM ' . STYLES_TPLDATA_TABLE . " 
						WHERE template_id = {$style_row['template_id']}";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$data[] = array(
							'src' => $row['template_data'], 
							'prefix' => 'template/' . $row['template_filename']
						);
					}
					$db->sql_freeresult($result);
				}
				unset($template_cfg);
			}

			// Export theme core code
			if ($mode == 'theme' || $inc_theme)
			{
				$theme_cfg = str_replace(array('{MODE}', '{NAME}', '{COPYRIGHT}', '{VERSION}'), array($mode, $style_row['theme_name'], $style_row['theme_copyright'], $config['version']), $this->theme_cfg);
				
				// Read old cfg file
				$items = $cache->obtain_cfg_items($style_row);
				$items = $items['theme'];

				if (!isset($items['parse_css_file']))
				{
					$items['parse_css_file'] = 'off';
				}

				if (!isset($items['pagination_sep']))
				{
					$items['pagination_sep'] = ', ';
				}

				$theme_cfg = str_replace(array('{PARSE_CSS_FILE}', '{PAGINATION_SEP}'), array($items['parse_css_file'], $items['pagination_sep']), $theme_cfg);

				$files[] = array(
					'src'		=> "styles/{$style_row['theme_path']}/theme/", 
					'prefix-'	=> "styles/{$style_row['theme_path']}/", 
					'prefix+'	=> false, 
					'exclude'	=> ($style_row['theme_storedb']) ? 'stylesheet.css,theme.cfg' : 'theme.cfg' 
				);

				$data[] = array(
					'src'		=> $theme_cfg, 
					'prefix'	=> 'theme/theme.cfg'
				);

				if ($style_row['theme_storedb'])
				{
					$data[] = array(
						'src'		=> $style_row['theme_data'], 
						'prefix'	=> 'theme/stylesheet.css'
					);
				}

				unset($items, $theme_cfg);
			}

			// Export imageset core code
			if ($mode == 'imageset' || $inc_imageset)
			{
				$imageset_cfg = str_replace(array('{MODE}', '{NAME}', '{COPYRIGHT}', '{VERSION}'), array($mode, $style_row['imageset_name'], $style_row['imageset_copyright'], $config['version']), $this->imageset_cfg);

				$imageset_definitions = explode(', ', $this->imageset_keys);
				
				foreach ($imageset_definitions as $key)
				{
					$imageset_cfg .= "\n" . $key . ' = ' . str_replace("styles/{$style_row['imageset_path']}/imageset/", '{PATH}', $style_row[$key]);
				}

				$files[] = array(
					'src'		=> "styles/{$style_row['imageset_path']}/imageset/", 
					'prefix-'	=> "styles/{$style_row['imageset_path']}/", 
					'prefix+'	=> false, 
					'exclude'	=> 'imageset.cfg'
				);

				$data[] = array(
					'src'		=> trim($imageset_cfg), 
					'prefix'	=> 'imageset/imageset.cfg'
				);
		
				unset($imageset_cfg);
			}

			switch ($format)
			{
				case 'tar':
					$ext = 'tar';
					$mimetype = 'x-tar';
					$compress = 'compress_tar';
				break;

				case 'zip':
					$ext = 'zip';
					$mimetype = 'zip';
				break;

				case 'tar.gz':
					$ext = 'tar.gz';
					$mimetype = 'x-gzip';
				break;

				case 'tar.bz2':
					$ext = 'tar.bz2';
					$mimetype = 'x-bzip2';
				break;

				default:
					$error[] = $user->lang[$l_prefix . '_ERR_ARCHIVE'];
			}

			if (!sizeof($error))
			{
				include($phpbb_root_path . 'includes/functions_compress.' . $phpEx);

				$path = str_replace(' ', '_', $style_row[$mode . '_name']);

				if ($format == 'zip')
				{
					$compress = new compress_zip('w', $phpbb_root_path . "store/$path.$ext");
				}
				else
				{
					$compress = new compress_tar('w', $phpbb_root_path . "store/$path.$ext", $ext);
				}
				
				if (sizeof($files))
				{
					foreach ($files as $file_ary)
					{
						$compress->add_file($file_ary['src'], $file_ary['prefix-'], $file_ary['prefix+'], $file_ary['exclude']);
					}
				}

				if (sizeof($data))
				{
					foreach ($data as $data_ary)
					{
						$compress->add_data($data_ary['src'], $data_ary['prefix']);
					}
				}

				$compress->close();

				add_log('admin', 'LOG_' . $l_prefix . '_EXPORT', $style_row[$mode . '_name']);

				if (!$store)
				{
					$compress->download($path);
					@unlink("{$phpbb_root_path}store/$path.$ext");
					exit;
				}

				trigger_error(sprintf($user->lang[$l_prefix . '_EXPORTED'], "store/$path.$ext") . adm_back_link($this->u_action));
			}
		}

		$sql = "SELECT {$mode}_id, {$mode}_name 
			FROM " . (($mode == 'style') ? STYLES_TABLE : $sql_from) . "
			WHERE {$mode}_id = $style_id";
		$result = $db->sql_query($sql);
		$style_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		if (!$style_row)
		{
			trigger_error($user->lang['NO_' . $l_prefix] . adm_back_link($this->u_action));
		}

		$this->page_title = $l_prefix . '_EXPORT';

		$format_buttons = '';
		foreach ($methods as $method)
		{
			$format_buttons .= '<input type="radio"' . ((!$format_buttons) ? ' id="format"' : '') . ' value="' . $method . '" name="format"' . (($method == $format) ? ' checked="checked"' : '') . ' />&nbsp;' . $method . '&nbsp;';
		}

		$template->assign_vars(array(
			'S_EXPORT'		=> true,
			'S_ERROR_MSG'	=> (sizeof($error)) ? true : false,
			'S_STYLE'		=> ($mode == 'style') ? true : false,

			'L_TITLE'		=> $user->lang[$this->page_title],
			'L_EXPLAIN'		=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'		=> $user->lang[$l_prefix . '_NAME'],

			'U_ACTION'		=> $this->u_action . '&amp;action=export&amp;id=' . $style_id,
			'U_BACK'		=> $this->u_action,

			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'				=> $style_row[$mode . '_name'],
			'FORMAT_BUTTONS'	=> $format_buttons)
		);
	}

	/**
	* Display details
	*/
	function details($mode, $style_id)
	{
		global $template, $db, $config, $user, $safe_mode, $cache;

		$update = (isset($_POST['update'])) ? true : false;
		$l_type = strtoupper($mode);

		$error = array();
		$element_ary = array('template' => STYLES_TPL_TABLE, 'theme' => STYLES_CSS_TABLE, 'imageset' => STYLES_IMAGE_TABLE);

		switch ($mode)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
			break;
		
			case 'template':
				$sql_from = STYLES_TPL_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_CSS_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGE_TABLE;
			break;
		}

		$sql = "SELECT * 
			FROM $sql_from
			WHERE {$mode}_id = $style_id";
		$result = $db->sql_query($sql);
		$style_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$style_row)
		{
			trigger_error($user->lang['NO_' . $l_type] . adm_back_link($this->u_action));
		}

		$style_row['style_default'] = ($mode == 'style' && $config['default_style'] == $style_id) ? 1 : 0;

		if ($update)
		{
			$name = request_var('name', '');
			$copyright = request_var('copyright', '');

			$template_id = request_var('template_id', 0);
			$theme_id = request_var('theme_id', 0);
			$imageset_id = request_var('imageset_id', 0);

			$style_active = request_var('style_active', 0);
			$style_default = request_var('style_default', 0);
			$store_db = request_var('store_db', 0);

			if ($mode == 'style' && (!$template_id || !$theme_id || !$imageset_id))
			{
				$error[] = $user->lang['STYLE_ERR_NO_IDS'];
			}

			if ($mode == 'style' && $style_row['style_active'] && !$style_active && $config['default_style'] == $style_id)
			{
				$error[] = $user->lang['DEACTIVATE_DEFAULT'];
			}

			if (!$name)
			{
				$error[] = $user->lang[$l_type . '_ERR_STYLE_NAME'];
			}

			if (!sizeof($error))
			{
				// Check if the character set is allowed
				if (!preg_match('/^[a-z0-9_\-\+ ]+$/i', $name))
				{
					$error[] = $user->lang[$l_type . '_ERR_NAME_CHARS'];
				}

				// Check length settings
				if (strlen($name) > 30)
				{
					$error[] = $user->lang[$l_type . '_ERR_NAME_LONG'];
				}

				if (strlen($copyright) > 60)
				{
					$error[] = $user->lang[$l_type . '_ERR_COPY_LONG'];
				}
			}
/*
			if (!sizeof($error))
			{
				// Check if the name already exist
				$sql = "SELECT {$mode}_id
					FROM $sql_from
					WHERE {$mode}_name = '" . $db->sql_escape($name) . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row)
				{
					$error[] = $user->lang[$l_type . '_ERR_NAME_EXIST'];
				}
			}
*/
		}
		
		if ($update && sizeof($error))
		{
			$style_row = array_merge($style_row, array(
				'template_id'			=> $template_id,
				'theme_id'				=> $theme_id,
				'imageset_id'			=> $imageset_id,
				'style_active'			=> $style_active,
				$mode . '_storedb'		=> $store_db,
				$mode . '_name'			=> $name,
				$mode . '_copyright'	=> $copyright)
			);
		}

		// User has submitted form and no errors have occured
		if ($update && !sizeof($error))
		{
			$sql_ary = array(
				$mode . '_name'			=> $name,
				$mode . '_copyright'	=> $copyright
			);

			switch ($mode)
			{
				case 'style':

					$sql_ary += array(
						'template_id'		=> $template_id,
						'theme_id'			=> $theme_id,
						'imageset_id'		=> $imageset_id,
						'style_active'		=> $style_active,
					);
				break;

				case 'imageset':
				break;

				case 'theme':

					if ($style_row['theme_storedb'] != $store_db)
					{
						$theme_data = '';

						if ($style_row['theme_storedb'])
						{
							$theme_data = implode('', file("{$phpbb_root_path}styles/" . $style_row['theme_path'] . '/theme/stylesheet.css'));
			
							// Match CSS imports
							$matches = array();
							preg_match_all('/@import url\(\"(.*)\"\);/i', $theme_data, $matches);
				
							if (sizeof($matches))
							{
								foreach ($matches[0] as $idx => $match)
								{
									$theme_data = str_replace($match, $this->load_css_file($style_row['theme_path'], $matches[1][$idx]), $theme_data);
								}
							}
						}

						if (!$store_db && !$safe_mode && is_writeable("{$phpbb_root_path}styles/{$style_row['theme_path']}/theme/stylesheet.css"))
						{
							$store_db = 1;
							
							if ($fp = @fopen("{$phpbb_root_path}styles/{$style_row['theme_path']}/theme/stylesheet.css", 'wb'))
							{
								$store_db = (@fwrite($fp, str_replace("styles/{$style_row['theme_path']}/theme/", './', $theme_data))) ? 0 : 1;
							}
							fclose($fp);
						}
						$theme_data = str_replace('./', "styles/{$style_row['theme_path']}/theme/", $theme_data);

						$sql_ary += array(
							'theme_mtime'	=> ($store_db) ? filemtime("{$phpbb_root_path}styles/{$style_row['theme_path']}/theme/stylesheet.css") : 0, 
							'theme_storedb'	=> $store_db, 
							'theme_data'	=> ($store_db) ? $theme_data : '',
						);
					}
				break;
					
				case 'template':

					if ($style_row['template_storedb'] != $store_db)
					{
						$filelist = filelist("{$phpbb_root_path}styles/{$style_row['template_path']}/template", '', 'html');

						if (!$store_db && !$safe_mode && is_writeable("{$phpbb_root_path}styles/{$style_row['template_path']}/template"))
						{
							$sql = 'SELECT * 
								FROM ' . STYLES_TPLDATA_TABLE . " 
								WHERE template_id = $style_id";
							$result = $db->sql_query($sql);

							while ($row = $db->sql_fetchrow($result))
							{
								if (!($fp = @fopen("{$phpbb_root_path}styles/{$style_row['template_path']}/template/" . $row['template_filename'], 'wb')))
								{
									$store_db = 1;
									break;
								}

								fwrite($fp, $row['template_data']);
								fclose($fp);
							}
							$db->sql_freeresult($result);

							if (!$store_db)
							{
								$sql = 'DELETE FROM ' . STYLES_TPLDATA_TABLE . " 
									WHERE template_id = $style_id";
								$db->sql_query($sql);
							}
						}

						$sql_ary += array(
							'template_storedb'	=> $store_db, 
						);
					}
				break;
			}

			if (sizeof($sql_ary))
			{
				$sql = "UPDATE $sql_from 
					SET " . $db->sql_build_array('UPDATE', $sql_ary) . " 
					WHERE {$mode}_id = $style_id";
				$db->sql_query($sql);

				// Making this the default style?
				if ($mode == 'style' && $style_default)
				{
					set_config('default_style', $style_id);
				}
			}

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_' . $l_type . '_EDIT_DETAILS', $name);
			trigger_error($user->lang[$l_type . '_DETAILS_UPDATED'] . adm_back_link($this->u_action));
		}

		$style_options = array();

		if ($mode == 'style')
		{
			foreach ($element_ary as $element => $table)
			{
				$sql = "SELECT {$element}_id, {$element}_name
					FROM $table 
					ORDER BY {$element}_id ASC";
				$result = $db->sql_query($sql);

				${$element . '_options'} = '';
				while ($row = $db->sql_fetchrow($result))
				{
					$selected = ($row[$element . '_id'] == $style_row[$element . '_id']) ? ' selected="selected"' : '';
					${$element . '_options'} .= '<option value="' . $row[$element . '_id'] . '"' . $selected . '>' . $row[$element . '_name'] . '</option>';
				}
				$db->sql_freeresult($result);
			}
		}

		$this->page_title = 'EDIT_DETAILS_' . $l_type;

		$template->assign_vars(array(
			'S_DETAILS'				=> true,
			'S_ERROR_MSG'			=> (sizeof($error)) ? true : false,
			'S_STYLE'				=> ($mode == 'style') ? true : false,
			'S_TEMPLATE'			=> ($mode == 'template') ? true : false,
			'S_THEME'				=> ($mode == 'theme') ? true : false,
			'S_IMAGESET'			=> ($mode == 'imageset') ? true : false,
			'S_STORE_DB'			=> (isset($style_row[$mode . '_storedb'])) ? $style_row[$mode . '_storedb'] : 0,
			'S_STYLE_ACTIVE'		=> (isset($style_row['style_active'])) ? $style_row['style_active'] : 0,
			'S_STYLE_DEFAULT'		=> (isset($style_row['style_default'])) ? $style_row['style_default'] : 0,

			'S_TEMPLATE_OPTIONS'	=> ($mode == 'style') ? $template_options : '',
			'S_THEME_OPTIONS'		=> ($mode == 'style') ? $theme_options : '',
			'S_IMAGESET_OPTIONS'	=> ($mode == 'style') ? $imageset_options : '',

			'U_ACTION'		=> $this->u_action . '&amp;action=details&amp;id=' . $style_id,
			'U_BACK'		=> $this->u_action,

			'L_TITLE'				=> $user->lang[$this->page_title],
			'L_EXPLAIN'				=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'				=> $user->lang[$l_type . '_NAME'],
			'L_LOCATION'			=> ($mode == 'template' || $mode == 'theme') ? $user->lang[$l_type . '_LOCATION'] : '',
			'L_LOCATION_EXPLAIN'	=> ($mode == 'template' || $mode == 'theme') ? $user->lang[$l_type . '_LOCATION_EXPLAIN'] : '',

			'ERROR_MSG'		=> (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'			=> $style_row[$mode . '_name'],
			'COPYRIGHT'		=> $style_row[$mode . '_copyright'],
			)
		);
	}

	/**
	* Load css file contents
	*/
	function load_css_file($path, $filename)
	{
		global $phpbb_root_path;
	
		$handle = "{$phpbb_root_path}styles/$path/theme/$filename";
	
		if ($fp = @fopen($handle, 'r'))
		{
			$content = trim(@fread($fp, filesize($handle)));
			@fclose($fp);
		}
		else
		{
			$content = '';
		}
	
		return $content;
	}

}

/**
* @package module_install
*/
class acp_styles_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_styles',
			'title'		=> 'ACP_CAT_STYLES',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'style'		=> array('title' => 'ACP_STYLES', 'auth' => 'acl_a_styles'),
				'template'	=> array('title' => 'ACP_TEMPLATES', 'auth' => 'acl_a_styles'),
				'theme'		=> array('title' => 'ACP_THEMES', 'auth' => 'acl_a_styles'),
				'imageset'	=> array('title' => 'ACP_IMAGESETS', 'auth' => 'acl_a_styles'),
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