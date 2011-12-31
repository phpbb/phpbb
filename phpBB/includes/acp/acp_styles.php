<?php
/**
*
* @package acp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* @package acp
*/
class acp_styles
{
	var $u_action;

	var $style_cfg;
	var $template_cfg;
	var $theme_cfg;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		// Hardcoded template bitfield to add for new templates
		$bitfield = new bitfield();
		$bitfield->set(0);
		$bitfield->set(1);
		$bitfield->set(2);
		$bitfield->set(3);
		$bitfield->set(4);
		$bitfield->set(8);
		$bitfield->set(9);
		$bitfield->set(11);
		$bitfield->set(12);
		$this->template_bitfield = $bitfield->get_base64();
		unset($bitfield);

		$user->add_lang('acp/styles');

		$this->tpl_name = 'acp_styles';
		$this->page_title = 'ACP_CAT_STYLES';

		$action = request_var('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;
		$style_id = request_var('id', 0);

		// Fill the configuration variables
		$this->style_cfg = $this->template_cfg = $this->theme_cfg = '
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

		$this->template_cfg .= '
# Some configuration options

#
# You can use this function to inherit templates from another template.
# The template of the given name has to be installed.
# Templates cannot inherit from inheriting templates.
#';

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

			case 'install':
				$this->install($mode);
				return;
			break;

			case 'add':
				$this->add($mode);
				return;
			break;

			case 'details':
				if ($style_id)
				{
					$this->details($mode, $style_id);
					return;
				}
			break;

			case 'edit':
				if ($style_id)
				{
					switch ($mode)
					{
						case 'template':
							return $this->edit_template($style_id);
						case 'theme':
							return $this->edit_theme($style_id);
					}
				}
			break;

			case 'cache':
				if ($style_id)
				{
					switch ($mode)
					{
						case 'template':
							return $this->template_cache($style_id);
					}
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
							trigger_error($user->lang['DEACTIVATE_DEFAULT'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (($action == 'deactivate' && confirm_box(true)) || $action == 'activate')
						{
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

								$sql = 'UPDATE ' . FORUMS_TABLE . '
									SET forum_style = 0
									WHERE forum_style = ' . $style_id;
								$db->sql_query($sql);
							}
						}
						else if ($action == 'deactivate')
						{
							$s_hidden_fields = array(
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action,
								'style_id'	=> $style_id,
							);
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
						}
					break;
				}

				$this->frontend('style', array('details'), array('export', 'delete'));
			break;

			case 'template':

				switch ($action)
				{
					// Clear cache
					case 'refresh':

						$sql = 'SELECT *
							FROM ' . STYLES_TEMPLATE_TABLE . "
							WHERE template_id = $style_id";
						$result = $db->sql_query($sql);
						$template_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$template_row)
						{
							trigger_error($user->lang['NO_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (confirm_box(true))
						{
							$this->clear_template_cache($template_row);

							trigger_error($user->lang['TEMPLATE_CACHE_CLEARED'] . adm_back_link($this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_TEMPLATE_CLEAR_CACHE'], build_hidden_fields(array(
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action,
								'id'		=> $style_id
							)));
						}

					break;
				}

				$this->frontend('template', array('edit', 'cache', 'details'), array('refresh', 'export', 'delete'));
			break;

			case 'theme':
				$this->frontend('theme', array('edit', 'details'), array('export', 'delete'));
			break;
		}
	}

	/**
	* Build Frontend with supplied options
	*/
	function frontend($mode, $options, $actions)
	{
		global $user, $template, $db, $config, $phpbb_root_path, $phpEx;

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
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
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

			$s_actions = array();
			foreach ($actions as $option)
			{
				$s_actions[] = '<a href="' . $this->u_action . "&amp;action=$option&amp;id=" . $row[$mode . '_id'] . '">' . $user->lang[strtoupper($option)] . '</a>';
			}

			$template->assign_block_vars('installed', array(
				'S_DEFAULT_STYLE'		=> ($mode == 'style' && $row['style_id'] == $config['default_style']) ? true : false,
				'U_EDIT'				=> $this->u_action . '&amp;action=' . (($mode == 'style') ? 'details' : 'edit') . '&amp;id=' . $row[$mode . '_id'],
				'U_STYLE_ACT_DEACT'		=> $this->u_action . '&amp;action=' . $stylevis . '&amp;id=' . $row[$mode . '_id'],
				'L_STYLE_ACT_DEACT'		=> $user->lang['STYLE_' . strtoupper($stylevis)],
				'S_OPTIONS'				=> implode(' | ', $s_options),
				'S_ACTIONS'				=> implode(' | ', $s_actions),
				'U_PREVIEW'				=> ($mode == 'style') ? append_sid("{$phpbb_root_path}index.$phpEx", "$mode=" . $row[$mode . '_id']) : '',

				'NAME'					=> $row[$mode . '_name'],
				'STYLE_COUNT'			=> ($mode == 'style' && isset($style_count[$row['style_id']])) ? $style_count[$row['style_id']] : 0,
				)
			);
		}
		$db->sql_freeresult($result);

		// Grab uninstalled items
		$new_ary = $cfg = array();

		$dp = @opendir("{$phpbb_root_path}styles");

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ($file[0] == '.' || !is_dir($phpbb_root_path . 'styles/' . $file))
				{
					continue;
				}

				$subpath = ($mode != 'style') ? "$mode/" : '';
				if (file_exists("{$phpbb_root_path}styles/$file/$subpath$mode.cfg"))
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
			closedir($dp);
		}

		unset($installed);

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
	* Provides a template editor which allows saving changes to template files on the filesystem or in the database.
	*
	* @param int $template_id specifies which template set is being edited
	*/
	function edit_template($template_id)
	{
		global $phpbb_root_path, $phpEx, $config, $db, $cache, $user, $template, $safe_mode;

		if (defined('PHPBB_DISABLE_ACP_EDITOR'))
		{
			trigger_error($user->lang['EDITOR_DISABLED'] . adm_back_link($this->u_action));
		}

		$this->page_title = 'EDIT_TEMPLATE';

		$filelist = $filelist_cats = array();

		$template_data	= utf8_normalize_nfc(request_var('template_data', '', true));
		$template_data	= htmlspecialchars_decode($template_data);
		$template_file	= utf8_normalize_nfc(request_var('template_file', '', true));
		$text_rows		= max(5, min(999, request_var('text_rows', 20)));
		$save_changes	= (isset($_POST['save'])) ? true : false;

		// make sure template_file path doesn't go upwards
		$template_file = preg_replace('#\.{2,}#', '.', $template_file);

		// Retrieve some information about the template
		$sql = 'SELECT template_path, template_name
			FROM ' . STYLES_TEMPLATE_TABLE . "
			WHERE template_id = $template_id";
		$result = $db->sql_query($sql);
		$template_info = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$template_info)
		{
			trigger_error($user->lang['NO_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Get the filesystem location of the current file
		$template_path = "{$phpbb_root_path}styles/{$template_info['template_path']}/template";
		$file = "$template_path/$template_file";

		if ($template_file)
		{
			$l_not_writable = sprintf($user->lang['TEMPLATE_FILE_NOT_WRITABLE'], htmlspecialchars($template_file)) . adm_back_link($this->u_action);

			if ($safe_mode)
			{
				trigger_error($l_not_writable, E_USER_WARNING);
			}

			if (file_exists($file) && is_file($file) && is_readable($file))
			{
				if (!phpbb_is_writable($file))
				{
					trigger_error($l_not_writable, E_USER_WARNING);
				}
			}
			else
			{
				trigger_error($user->lang['NO_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		if ($save_changes && !check_form_key('acp_styles'))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}
		else if (!$save_changes)
		{
			add_form_key('acp_styles');
		}

		// save changes to the template if the user submitted any
		if ($save_changes && $template_file)
		{
			// Try to write the file
			if (!($fp = @fopen($file, 'wb')))
			{
				// File exists and is writeable, but still not able to be written to
				trigger_error($l_not_writable, E_USER_WARNING);
			}
			fwrite($fp, $template_data);
			fclose($fp);

			// destroy the cached version of the template (filename without extension)
			$this->clear_template_cache($template_info, array(substr($template_file, 0, -5)));

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_TEMPLATE_EDIT', $template_info['template_name'], $template_file);
			trigger_error($user->lang['TEMPLATE_FILE_UPDATED'] . adm_back_link($this->u_action . "&amp;action=edit&amp;id=$template_id&amp;text_rows=$text_rows&amp;template_file=$template_file"));
		}

		// Generate a category array containing template filenames

		$filelist = filelist($template_path, '', 'html');
		$filelist[''] = array_diff($filelist[''], array('bbcode.html'));

		if ($template_file)
		{
			$template_data = file_get_contents($file);

			if (!$template_data)
			{
				trigger_error($user->lang['NO_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		if (empty($filelist['']))
		{
			trigger_error($user->lang['NO_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Now create the categories
		$filelist_cats[''] = array();
		foreach ($filelist as $pathfile => $file_ary)
		{
			// Use the directory name as category name
			if (!empty($pathfile))
			{
				$filelist_cats[$pathfile] = array();
				foreach ($file_ary as $file)
				{
					$filelist_cats[$pathfile][$pathfile . $file] = $file;
				}
			}
			// or if it's in the main category use the word before the first underscore to group files
			else
			{
				$cats = array();
				foreach ($file_ary as $file)
				{
					$cats[] = substr($file, 0, strpos($file, '_'));
					$filelist_cats[substr($file, 0, strpos($file, '_'))][$file] = $file;
				}

				$cats = array_values(array_unique($cats));

				// we don't need any single element categories so put them into the misc '' category
				for ($i = 0, $n = sizeof($cats); $i < $n; $i++)
				{
					if (sizeof($filelist_cats[$cats[$i]]) == 1 && $cats[$i] !== '')
					{
						$filelist_cats[''][key($filelist_cats[$cats[$i]])] = current($filelist_cats[$cats[$i]]);
						unset($filelist_cats[$cats[$i]]);
					}
				}
				unset($cats);
			}
		}
		unset($filelist);

		// Generate list of categorised template files
		$tpl_options = '';
		ksort($filelist_cats);
		foreach ($filelist_cats as $category => $tpl_ary)
		{
			ksort($tpl_ary);

			if (!empty($category))
			{
				$tpl_options .= '<option class="sep" value="">' . $category . '</option>';
			}

			foreach ($tpl_ary as $filename => $file)
			{
				$selected = ($template_file == $filename) ? ' selected="selected"' : '';
				$tpl_options .= '<option value="' . $filename . '"' . $selected . '>' . $file . '</option>';
			}
		}

		$template->assign_vars(array(
			'S_EDIT_TEMPLATE'	=> true,
			'S_HIDDEN_FIELDS'	=> build_hidden_fields(array('template_file' => $template_file)),
			'S_TEMPLATES'		=> $tpl_options,

			'U_ACTION'			=> $this->u_action . "&amp;action=edit&amp;id=$template_id&amp;text_rows=$text_rows",
			'U_BACK'			=> $this->u_action,

			'L_EDIT'			=> $user->lang['EDIT_TEMPLATE'],
			'L_EDIT_EXPLAIN'	=> $user->lang['EDIT_TEMPLATE_EXPLAIN'],
			'L_EDITOR'			=> $user->lang['TEMPLATE_EDITOR'],
			'L_EDITOR_HEIGHT'	=> $user->lang['TEMPLATE_EDITOR_HEIGHT'],
			'L_FILE'			=> $user->lang['TEMPLATE_FILE'],
			'L_SELECT'			=> $user->lang['SELECT_TEMPLATE'],
			'L_SELECTED'		=> $user->lang['SELECTED_TEMPLATE'],
			'L_SELECTED_FILE'	=> $user->lang['SELECTED_TEMPLATE_FILE'],

			'SELECTED_TEMPLATE'	=> $template_info['template_name'],
			'TEMPLATE_FILE'		=> $template_file,
			'TEMPLATE_DATA'		=> utf8_htmlspecialchars($template_data),
			'TEXT_ROWS'			=> $text_rows)
		);
	}

	/**
	* Allows the admin to view cached versions of template files and clear single template cache files
	*
	* @param int $template_id specifies which template's cache is shown
	*/
	function template_cache($template_id)
	{
		global $phpbb_root_path, $phpEx, $config, $db, $cache, $user, $template;

		$source		= str_replace('/', '.', request_var('source', ''));
		$file_ary	= array_diff(request_var('delete', array('')), array(''));
		$submit		= isset($_POST['submit']) ? true : false;

		$sql = 'SELECT *
			FROM ' . STYLES_TEMPLATE_TABLE . "
			WHERE template_id = $template_id";
		$result = $db->sql_query($sql);
		$template_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$template_row)
		{
			trigger_error($user->lang['NO_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// User wants to delete one or more files ...
		if ($submit && $file_ary)
		{
			$this->clear_template_cache($template_row, $file_ary);
			trigger_error($user->lang['TEMPLATE_CACHE_CLEARED'] . adm_back_link($this->u_action . "&amp;action=cache&amp;id=$template_id"));
		}

		$cache_prefix = 'tpl_' . str_replace('_', '-', $template_row['template_path']);

		// Someone wants to see the cached source ... so we'll highlight it,
		// add line numbers and indent it appropriately. This could be nasty
		// on larger source files ...
		if ($source && file_exists("{$phpbb_root_path}cache/{$cache_prefix}_$source.html.$phpEx"))
		{
			adm_page_header($user->lang['TEMPLATE_CACHE']);

			$template->set_filenames(array(
				'body'	=> 'viewsource.html')
			);

			$template->assign_vars(array(
				'FILENAME'	=> str_replace('.', '/', $source) . '.html')
			);

			$code = str_replace(array("\r\n", "\r"), array("\n", "\n"), file_get_contents("{$phpbb_root_path}cache/{$cache_prefix}_$source.html.$phpEx"));

			$conf = array('highlight.bg', 'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string');
			foreach ($conf as $ini_var)
			{
				@ini_set($ini_var, str_replace('highlight.', 'syntax', $ini_var));
			}

			$marker = 'MARKER' . time();
			$code = highlight_string(str_replace("\n", $marker, $code), true);
			$code = str_replace($marker, "\n", $code);
			$str_from = array('<span style="color: ', '<font color="syntax', '</font>', '<code>', '</code>','[', ']', '.', ':');
			$str_to = array('<span class="', '<span class="syntax', '</span>', '', '', '&#91;', '&#93;', '&#46;', '&#58;');

			$code = str_replace($str_from, $str_to, $code);
			$code = preg_replace('#^(<span class="[a-z_]+">)\n?(.*?)\n?(</span>)$#ism', '$1$2$3', $code);
			$code = substr($code, strlen('<span class="syntaxhtml">'));
			$code = substr($code, 0, -1 * strlen('</ span>'));
			$code = explode("\n", $code);

			foreach ($code as $key => $line)
			{
				$template->assign_block_vars('source', array(
					'LINENUM'	=> $key + 1,
					'LINE'		=> preg_replace('#([^ ;])&nbsp;([^ &])#', '$1 $2', $line))
				);
				unset($code[$key]);
			}

			adm_page_footer();
		}

		// Get a list of cached template files and then retrieve additional information about them
		$file_ary = $this->template_cache_filelist($template_row['template_path']);

		foreach ($file_ary as $file)
		{
			$file		= str_replace('/', '.', $file);

			// perform some dirty guessing to get the path right.
			// We assume that three dots in a row were '../'
			$tpl_file	= str_replace('.', '/', $file);
			$tpl_file	= str_replace('///', '../', $tpl_file);

			$filename = "{$cache_prefix}_$file.html.$phpEx";

			if (!file_exists("{$phpbb_root_path}cache/$filename"))
			{
				continue;
			}

			$file_tpl = "{$phpbb_root_path}styles/{$template_row['template_path']}/template/$tpl_file.html";
			$inherited = false;

			if (isset($template_row['template_inherits_id']) && $template_row['template_inherits_id'] && !file_exists($file_tpl))
			{
				$file_tpl = "{$phpbb_root_path}styles/{$template_row['template_inherit_path']}/template/$tpl_file.html";
				$inherited = true;
			}

			$template->assign_block_vars('file', array(
				'U_VIEWSOURCE'	=> $this->u_action . "&amp;action=cache&amp;id=$template_id&amp;source=$file",

				'CACHED'		=> $user->format_date(filemtime("{$phpbb_root_path}cache/$filename")),
				'FILENAME'		=> $file,
				'FILENAME_PATH'	=> $file_tpl,
				'FILESIZE'		=> get_formatted_filesize(filesize("{$phpbb_root_path}cache/$filename")),
				'MODIFIED'		=> $user->format_date(filemtime($file_tpl)),
			));
		}

		$template->assign_vars(array(
			'S_CACHE'			=> true,
			'S_TEMPLATE'		=> true,

			'U_ACTION'			=> $this->u_action . "&amp;action=cache&amp;id=$template_id",
			'U_BACK'			=> $this->u_action)
		);
	}

	/**
	* Provides a css editor and a basic easier to use stylesheet editing tool for less experienced (or lazy) users
	*
	* @param int $theme_id specifies which theme is being edited
	*/
	function edit_theme($theme_id)
	{
		global $phpbb_root_path, $phpEx, $config, $db, $cache, $user, $template, $safe_mode;

		$this->page_title = 'EDIT_THEME';

		$filelist = $filelist_cats = array();

		$theme_data		= utf8_normalize_nfc(request_var('template_data', '', true));
		$theme_data		= htmlspecialchars_decode($theme_data);
		$theme_file		= utf8_normalize_nfc(request_var('template_file', '', true));
		$text_rows		= max(5, min(999, request_var('text_rows', 20)));
		$save_changes	= (isset($_POST['save'])) ? true : false;

		// make sure theme_file path doesn't go upwards
		$theme_file = str_replace('..', '.', $theme_file);

		// Retrieve some information about the theme
		$sql = 'SELECT theme_path, theme_name
			FROM ' . STYLES_THEME_TABLE . "
			WHERE theme_id = $theme_id";
		$result = $db->sql_query($sql);

		if (!($theme_info = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_THEME'] . adm_back_link($this->u_action), E_USER_WARNING);
		}
		$db->sql_freeresult($result);

		// Get the filesystem location of the current file
		$theme_path = "{$phpbb_root_path}styles/{$theme_info['theme_path']}/theme";
		$file = "$theme_path/$theme_file";

		if ($theme_file)
		{
			$l_not_writable = sprintf($user->lang['THEME_FILE_NOT_WRITABLE'], htmlspecialchars($theme_file)) . adm_back_link($this->u_action);

			if ($safe_mode)
			{
				trigger_error($l_not_writable, E_USER_WARNING);
			}

			if (file_exists($file) && is_file($file) && is_readable($file))
			{
				if (!phpbb_is_writable($file))
				{
					trigger_error($l_not_writable, E_USER_WARNING);
				}
			}
			else
			{
				trigger_error($user->lang['NO_THEME'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// save changes to the theme if the user submitted any
		if ($save_changes && $theme_file)
		{
			$message = $user->lang['THEME_UPDATED'];

			if (!($fp = @fopen($file, 'wb')))
			{
				trigger_error($l_not_writable, E_USER_WARNING);
			}
			fwrite($fp, $theme_data);
			fclose($fp);

			$cache->destroy('sql', STYLES_THEME_TABLE);
			add_log('admin', 'LOG_THEME_EDIT_FILE', $theme_info['theme_name'], $theme_file);

			trigger_error($message . adm_back_link($this->u_action . "&amp;action=edit&amp;id=$theme_id&amp;template_file=$theme_file&amp;text_rows=$text_rows"));
		}

		// Generate a category array containing theme filenames
		$filelist = filelist($theme_path, '', 'css');

		if ($theme_file)
		{
			$theme_data = file_get_contents($file);

			if (!$theme_data)
			{
				trigger_error($user->lang['NO_THEME'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// Now create the categories
		$filelist_cats[''] = array();
		foreach ($filelist as $pathfile => $file_ary)
		{
			// Use the directory name as category name
			if (!empty($pathfile))
			{
				$filelist_cats[$pathfile] = array();
				foreach ($file_ary as $file)
				{
					$filelist_cats[$pathfile][$pathfile . $file] = $file;
				}
			}
			// or if it's in the main category use the word before the first underscore to group files
			else
			{
				$cats = array();
				foreach ($file_ary as $file)
				{
					$cats[] = substr($file, 0, strpos($file, '_'));
					$filelist_cats[substr($file, 0, strpos($file, '_'))][$file] = $file;
				}

				$cats = array_values(array_unique($cats));

				// we don't need any single element categories so put them into the misc '' category
				for ($i = 0, $n = sizeof($cats); $i < $n; $i++)
				{
					if (sizeof($filelist_cats[$cats[$i]]) == 1 && $cats[$i] !== '')
					{
						$filelist_cats[''][key($filelist_cats[$cats[$i]])] = current($filelist_cats[$cats[$i]]);
						unset($filelist_cats[$cats[$i]]);
					}
				}
				unset($cats);
			}
		}
		unset($filelist);

		// Generate list of categorised theme files
		$tpl_options = '';
		ksort($filelist_cats);
		foreach ($filelist_cats as $category => $tpl_ary)
		{
			ksort($tpl_ary);

			if (!empty($category))
			{
				$tpl_options .= '<option class="sep" value="">' . $category . '</option>';
			}

			foreach ($tpl_ary as $filename => $file)
			{
				$selected = ($theme_file == $filename) ? ' selected="selected"' : '';
				$tpl_options .= '<option value="' . $filename . '"' . $selected . '>' . $file . '</option>';
			}
		}

		$template->assign_vars(array(
			'S_EDIT_THEME'		=> true,
			'S_HIDDEN_FIELDS'	=> build_hidden_fields(array('template_file' => $theme_file)),
			'S_TEMPLATES'		=> $tpl_options,

			'U_ACTION'			=> $this->u_action . "&amp;action=edit&amp;id=$theme_id&amp;text_rows=$text_rows",
			'U_BACK'			=> $this->u_action,

			'L_EDIT'			=> $user->lang['EDIT_THEME'],
			'L_EDIT_EXPLAIN'	=> $user->lang['EDIT_THEME_EXPLAIN'],
			'L_EDITOR'			=> $user->lang['THEME_EDITOR'],
			'L_EDITOR_HEIGHT'	=> $user->lang['THEME_EDITOR_HEIGHT'],
			'L_FILE'			=> $user->lang['THEME_FILE'],
			'L_SELECT'			=> $user->lang['SELECT_THEME'],
			'L_SELECTED'		=> $user->lang['SELECTED_THEME'],
			'L_SELECTED_FILE'	=> $user->lang['SELECTED_THEME_FILE'],

			'SELECTED_TEMPLATE'	=> $theme_info['theme_name'],
			'TEMPLATE_FILE'		=> $theme_file,
			'TEMPLATE_DATA'		=> utf8_htmlspecialchars($theme_data),
			'TEXT_ROWS'			=> $text_rows,
		));
	}

	/**
	* Remove style/template/theme
	*/
	function remove($mode, $style_id)
	{
		global $db, $template, $user, $phpbb_root_path, $cache, $config;

		$new_id = request_var('new_id', 0);
		$update = (isset($_POST['update'])) ? true : false;
		$sql_where = '';

		switch ($mode)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
				$sql_select = 'style_id, style_name, template_id, theme_id';
				$sql_where = 'AND style_active = 1';
			break;

			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
				$sql_select = 'template_id, template_name, template_path';
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
				$sql_select = 'theme_id, theme_name, theme_path';
			break;
		}

		if ($mode === 'template' && ($conflicts = $this->check_inheritance($mode, $style_id)))
		{
			$l_type = strtoupper($mode);
			$msg = $user->lang[$l_type . '_DELETE_DEPENDENT'];
			foreach ($conflicts as $id => $values)
			{
				$msg .= '<br />' . $values['template_name'];
			}

			trigger_error($msg . adm_back_link($this->u_action), E_USER_WARNING);
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
			trigger_error($user->lang['NO_' . $l_prefix] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$s_only_component = $this->display_component_options($mode, $style_row[$mode . '_id'], $style_row);

		if ($s_only_component)
		{
			trigger_error($user->lang['ONLY_' . $l_prefix] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($update)
		{
			if ($mode == 'style')
			{
				$sql = "DELETE FROM $sql_from
					WHERE {$mode}_id = $style_id";
				$db->sql_query($sql);

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_style = $new_id
					WHERE user_style = $style_id";
				$db->sql_query($sql);

				$sql = 'UPDATE ' . FORUMS_TABLE . "
					SET forum_style = $new_id
					WHERE forum_style = $style_id";
				$db->sql_query($sql);

				if ($style_id == $config['default_style'])
				{
					set_config('default_style', $new_id);
				}

				// Remove the components
				$components = array('template', 'theme');
				foreach ($components as $component)
				{
					$new_id = request_var('new_' . $component . '_id', 0);
					$component_id = $style_row[$component . '_id'];
					$this->remove_component($component, $component_id, $new_id, $style_id);
				}
			}
			else
			{
				$this->remove_component($mode, $style_id, $new_id);
			}

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_' . $l_prefix . '_DELETE', $style_row[$mode . '_name']);
			$message = ($mode != 'style') ? $l_prefix . '_DELETED_FS' : $l_prefix . '_DELETED';
			trigger_error($user->lang[$message] . adm_back_link($this->u_action));
		}

		$this->page_title = 'DELETE_' . $l_prefix;

		$template->assign_vars(array(
			'S_DELETE'			=> true,

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

		if ($mode == 'style')
		{
			$template->assign_vars(array(
				'S_DELETE_STYLE'		=> true,
			));
		}
	}

	/**
	* Remove template/theme entry from the database
	*/
	function remove_component($component, $component_id, $new_id, $style_id = false)
	{
		global $db;

		if (($new_id == 0) || ($component === 'template' && ($conflicts = $this->check_inheritance($component, $component_id))))
		{
			// We can not delete the template, as the user wants to keep the component or an other template is inheriting from this one.
			return;
		}

		$component_in_use = array();
		if ($component != 'style')
		{
			$component_in_use = $this->component_in_use($component, $component_id, $style_id);
		}

		if (($new_id == -1) && !empty($component_in_use))
		{
			// We can not delete the component, as it is still in use
			return;
		}

		switch ($component)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;
		}

		$sql = "DELETE FROM $sql_from
			WHERE {$component}_id = $component_id";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . STYLES_TABLE . "
			SET {$component}_id = $new_id
			WHERE {$component}_id = $component_id";
		$db->sql_query($sql);
	}

	/**
	* Display the options which can be used to replace a style/template/theme
	*
	* @return boolean Returns true if the component is the only component and can not be deleted.
	*/
	function display_component_options($component, $component_id, $style_row = false, $style_id = false)
	{
		global $db, $template, $user;

		$is_only_component = true;
		$component_in_use = array();
		if ($component != 'style')
		{
			$component_in_use = $this->component_in_use($component, $component_id, $style_id);
		}

		$sql_where = '';
		switch ($component)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
				$sql_where = 'WHERE style_active = 1';
			break;

			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
				$sql_where = 'WHERE template_inherits_id <> ' . $component_id;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;
		}

		$s_options = '';
		if (($component != 'style') && empty($component_in_use))
		{
			// If it is not in use, there must be another component
			$is_only_component = false;

			$sql = "SELECT {$component}_id, {$component}_name
				FROM $sql_from
				WHERE {$component}_id = {$component_id}";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$s_options .= '<option value="-1" selected="selected">' . $user->lang['DELETE_' . strtoupper($component)] . '</option>';
			$s_options .= '<option value="0">' . sprintf($user->lang['KEEP_' . strtoupper($component)], $row[$component . '_name']) . '</option>';
		}
		else
		{
			$sql = "SELECT {$component}_id, {$component}_name
				FROM $sql_from
				$sql_where
				ORDER BY {$component}_name ASC";
			$result = $db->sql_query($sql);

			$s_keep_option = $s_options = '';
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row[$component . '_id'] != $component_id)
				{
					$is_only_component = false;
					$s_options .= '<option value="' . $row[$component . '_id'] . '">' . sprintf($user->lang['REPLACE_WITH_OPTION'], $row[$component . '_name']) . '</option>';
				}
				else if ($component != 'style')
				{
					$s_keep_option = '<option value="0" selected="selected">' . sprintf($user->lang['KEEP_' . strtoupper($component)], $row[$component . '_name']) . '</option>';
				}
			}
			$db->sql_freeresult($result);
			$s_options = $s_keep_option . $s_options;
		}

		if (!$style_row)
		{
			$template->assign_var('S_REPLACE_' . strtoupper($component) . '_OPTIONS', $s_options);
		}
		else
		{
			$template->assign_var('S_REPLACE_OPTIONS', $s_options);
			if ($component == 'style')
			{
				$components = array('template', 'theme');
				foreach ($components as $component)
				{
					$this->display_component_options($component, $style_row[$component . '_id'], false, $component_id, true);
				}
			}
		}

		return $is_only_component;
	}

	/**
	* Check whether the component is still used by another style or component
	*/
	function component_in_use($component, $component_id, $style_id = false)
	{
		global $db;

		$component_in_use = array();

		if ($style_id)
		{
			$sql = 'SELECT style_id, style_name
				FROM ' . STYLES_TABLE . "
				WHERE {$component}_id = {$component_id}
					AND style_id <> {$style_id}
				ORDER BY style_name ASC";
		}
		else
		{
			$sql = 'SELECT style_id, style_name
				FROM ' . STYLES_TABLE . "
				WHERE {$component}_id = {$component_id}
				ORDER BY style_name ASC";
		}
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$component_in_use[] = $row['style_name'];
		}
		$db->sql_freeresult($result);

		if ($component === 'template' && ($conflicts = $this->check_inheritance($component, $component_id)))
		{
			foreach ($conflicts as $temp_id => $conflict_data)
			{
				$component_in_use[] = $conflict_data['template_name'];
			}
		}

		return $component_in_use;
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
				if ($update && ($inc_template + $inc_theme) < 1)
				{
					$error[] = $user->lang['STYLE_ERR_MORE_ELEMENTS'];
				}

				$name = 'style_name';

				$sql_select = 's.style_id, s.style_name, s.style_copyright';
				$sql_select .= ($inc_template) ? ', t.*' : ', t.template_name';
				$sql_select .= ($inc_theme) ? ', c.*' : ', c.theme_name';
				$sql_from = STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . ' c';
				$sql_where = "s.style_id = $style_id AND t.template_id = s.template_id AND c.theme_id = s.theme_id";

				$l_prefix = 'STYLE';
			break;

			case 'template':
				$name = 'template_name';

				$sql_select = '*';
				$sql_from = STYLES_TEMPLATE_TABLE;
				$sql_where = "template_id = $style_id";

				$l_prefix = 'TEMPLATE';
			break;

			case 'theme':
				$name = 'theme_name';

				$sql_select = '*';
				$sql_from = STYLES_THEME_TABLE;
				$sql_where = "theme_id = $style_id";

				$l_prefix = 'THEME';
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
				trigger_error($user->lang['NO_' . $l_prefix] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$var_ary = array('style_id', 'style_name', 'style_copyright', 'template_id', 'template_name', 'template_path', 'template_copyright', 'template_inherits_id', 'bbcode_bitfield', 'theme_id', 'theme_name', 'theme_path', 'theme_copyright');

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

				$style_cfg .= (!$inc_template) ? "\nrequired_template = {$style_row['template_name']}" : '';
				$style_cfg .= (!$inc_theme) ? "\nrequired_theme = {$style_row['theme_name']}" : '';

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

				$use_template_name = '';

				// Add the inherit from variable, depending on it's use...
				if ($style_row['template_inherits_id'])
				{
					// Get the template name
					$sql = 'SELECT template_name
						FROM ' . STYLES_TEMPLATE_TABLE . '
						WHERE template_id = ' . (int) $style_row['template_inherits_id'];
					$result = $db->sql_query($sql);
					$use_template_name = (string) $db->sql_fetchfield('template_name');
					$db->sql_freeresult($result);
				}

				$template_cfg .= ($use_template_name) ? "\ninherit_from = $use_template_name" : "\n#inherit_from = ";
				$template_cfg .= "\n\nbbcode_bitfield = {$style_row['bbcode_bitfield']}";

				$data[] = array(
					'src'		=> $template_cfg,
					'prefix'	=> 'template/template.cfg'
				);

				// This is potentially nasty memory-wise ...
				$files[] = array(
					'src'		=> "styles/{$style_row['template_path']}/template/",
					'prefix-'	=> "styles/{$style_row['template_path']}/",
					'prefix+'	=> false,
					'exclude'	=> 'template.cfg'
				);
				unset($template_cfg);
			}

			// Export theme core code
			if ($mode == 'theme' || $inc_theme)
			{
				$theme_cfg = str_replace(array('{MODE}', '{NAME}', '{COPYRIGHT}', '{VERSION}'), array($mode, $style_row['theme_name'], $style_row['theme_copyright'], $config['version']), $this->theme_cfg);

				// Read old cfg file
				$items = $cache->obtain_cfg_items($style_row);
				$items = $items['theme'];

				$files[] = array(
					'src'		=> "styles/{$style_row['theme_path']}/theme/",
					'prefix-'	=> "styles/{$style_row['theme_path']}/",
					'prefix+'	=> false,
					'exclude'	=> 'theme.cfg',
				);

				$data[] = array(
					'src'		=> $theme_cfg,
					'prefix'	=> 'theme/theme.cfg',
				);

				unset($items, $theme_cfg);
			}

			switch ($format)
			{
				case 'tar':
					$ext = '.tar';
				break;

				case 'zip':
					$ext = '.zip';
				break;

				case 'tar.gz':
					$ext = '.tar.gz';
				break;

				case 'tar.bz2':
					$ext = '.tar.bz2';
				break;

				default:
					$error[] = $user->lang[$l_prefix . '_ERR_ARCHIVE'];
			}

			if (!sizeof($error))
			{
				include($phpbb_root_path . 'includes/functions_compress.' . $phpEx);

				if ($mode == 'style')
				{
					$path = preg_replace('#[^\w-]+#', '_', $style_row['style_name']);
				}
				else
				{
					$path = $style_row[$mode . '_path'];
				}

				if ($format == 'zip')
				{
					$compress = new compress_zip('w', $phpbb_root_path . "store/$path$ext");
				}
				else
				{
					$compress = new compress_tar('w', $phpbb_root_path . "store/$path$ext", $ext);
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
					@unlink("{$phpbb_root_path}store/$path$ext");
					exit;
				}

				trigger_error(sprintf($user->lang[$l_prefix . '_EXPORTED'], "store/$path$ext") . adm_back_link($this->u_action));
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
			trigger_error($user->lang['NO_' . $l_prefix] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->page_title = $l_prefix . '_EXPORT';

		$format_buttons = '';
		foreach ($methods as $method)
		{
			$format_buttons .= '<label><input type="radio"' . ((!$format_buttons) ? ' id="format"' : '') . ' class="radio" value="' . $method . '" name="format"' . (($method == $format) ? ' checked="checked"' : '') . ' /> ' . $method . '</label>';
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
		global $template, $db, $config, $user, $safe_mode, $cache, $phpbb_root_path;

		$update = (isset($_POST['update'])) ? true : false;
		$l_type = strtoupper($mode);

		$error = array();
		$element_ary = array('template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE);

		switch ($mode)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
			break;

			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
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
			trigger_error($user->lang['NO_' . $l_type] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$style_row['style_default'] = ($mode == 'style' && $config['default_style'] == $style_id) ? 1 : 0;

		if ($update)
		{
			$name = utf8_normalize_nfc(request_var('name', '', true));
			$copyright = utf8_normalize_nfc(request_var('copyright', '', true));

			$template_id = request_var('template_id', 0);
			$theme_id = request_var('theme_id', 0);

			$style_active = request_var('style_active', 0);
			$style_default = request_var('style_default', 0);

			// If the admin selected the style to be the default style, but forgot to activate it... we will do it for him
			if ($style_default)
			{
				$style_active = 1;
			}

			$sql = "SELECT {$mode}_id, {$mode}_name
				FROM $sql_from
				WHERE {$mode}_id <> $style_id
				AND LOWER({$mode}_name) = '" . $db->sql_escape(strtolower($name)) . "'";
			$result = $db->sql_query($sql);
			$conflict = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($mode == 'style' && (!$template_id || !$theme_id))
			{
				$error[] = $user->lang['STYLE_ERR_NO_IDS'];
			}

			if ($mode == 'style' && $style_row['style_active'] && !$style_active && $config['default_style'] == $style_id)
			{
				$error[] = $user->lang['DEACTIVATE_DEFAULT'];
			}

			if (!$name || $conflict)
			{
				$error[] = $user->lang[$l_type . '_ERR_STYLE_NAME'];
			}

			if (!sizeof($error))
			{
				// Check length settings
				if (utf8_strlen($name) > 30)
				{
					$error[] = $user->lang[$l_type . '_ERR_NAME_LONG'];
				}

				if (utf8_strlen($copyright) > 60)
				{
					$error[] = $user->lang[$l_type . '_ERR_COPY_LONG'];
				}
			}
		}

		if ($update && sizeof($error))
		{
			$style_row = array_merge($style_row, array(
				'template_id'			=> $template_id,
				'theme_id'				=> $theme_id,
				'style_active'			=> $style_active,
				$mode . '_name'			=> $name,
				$mode . '_copyright'	=> $copyright)
			);
		}

		// User has submitted form and no errors have occurred
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
						'template_id'		=> (int) $template_id,
						'theme_id'			=> (int) $theme_id,
						'style_active'		=> (int) $style_active,
					);
				break;

				case 'theme':
				break;

				case 'template':
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
			if (sizeof($error))
			{
				trigger_error(implode('<br />', $error) . adm_back_link($this->u_action), E_USER_WARNING);
			}
			else
			{
				trigger_error($user->lang[$l_type . '_DETAILS_UPDATED'] . adm_back_link($this->u_action));
			}
		}

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

		if ($mode == 'template')
		{
			$super = array();
			if (isset($style_row[$mode . '_inherits_id']) && $style_row['template_inherits_id'])
			{
				$super = $this->get_super($mode, $style_row['template_id']);
			}
		}

		$this->page_title = 'EDIT_DETAILS_' . $l_type;

		$template->assign_vars(array(
			'S_DETAILS'				=> true,
			'S_ERROR_MSG'			=> (sizeof($error)) ? true : false,
			'S_STYLE'				=> ($mode == 'style') ? true : false,
			'S_TEMPLATE'			=> ($mode == 'template') ? true : false,
			'S_THEME'				=> ($mode == 'theme') ? true : false,
			'S_STYLE_ACTIVE'		=> (isset($style_row['style_active'])) ? $style_row['style_active'] : 0,
			'S_STYLE_DEFAULT'		=> (isset($style_row['style_default'])) ? $style_row['style_default'] : 0,
			'S_SUPERTEMPLATE'		=> (isset($style_row[$mode . '_inherits_id']) && $style_row[$mode . '_inherits_id']) ? $super['template_name'] : 0,

			'S_TEMPLATE_OPTIONS'	=> ($mode == 'style') ? $template_options : '',
			'S_THEME_OPTIONS'		=> ($mode == 'style') ? $theme_options : '',

			'U_ACTION'		=> $this->u_action . '&amp;action=details&amp;id=' . $style_id,
			'U_BACK'		=> $this->u_action,

			'L_TITLE'				=> $user->lang[$this->page_title],
			'L_EXPLAIN'				=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'				=> $user->lang[$l_type . '_NAME'],

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

		$file = "{$phpbb_root_path}styles/$path/theme/$filename";

		if (file_exists($file) && ($content = file_get_contents($file)))
		{
			$content = trim($content);
		}
		else
		{
			$content = '';
		}
		if (defined('DEBUG'))
		{
			$content = "/* BEGIN @include $filename */ \n $content \n /* END @include $filename */ \n";
		}

		return $content;
	}

	/**
	* Returns a string containing the value that should be used for the theme_data column in the theme database table.
	* Includes contents of files loaded via @import
	*
	* @param array $theme_row is an associative array containing the theme's current database entry
	* @param mixed $stylesheet can either be the new content for the stylesheet or false to load from the standard file
	* @param string $root_path should only be used in case you want to use a different root path than "{$phpbb_root_path}styles/{$theme_row['theme_path']}"
	*
	* @return string Stylesheet data for theme_data column in the theme table
	*/
	function db_theme_data($theme_row, $stylesheet = false, $root_path = '')
	{
		global $phpbb_root_path;

		if (!$root_path)
		{
			$root_path = $phpbb_root_path . 'styles/' . $theme_row['theme_path'];
		}

		if (!$stylesheet)
		{
			$stylesheet = '';
			if (file_exists($root_path . '/theme/stylesheet.css'))
			{
				$stylesheet = file_get_contents($root_path . '/theme/stylesheet.css');
			}
		}

		// Match CSS imports
		$matches = array();
		preg_match_all('/@import url\((["\'])(.*)\1\);/i', $stylesheet, $matches);

		// remove commented stylesheets (very simple parser, allows only whitespace
		// around an @import statement)
		preg_match_all('#/\*\s*@import url\((["\'])(.*)\1\);\s\*/#i', $stylesheet, $commented);
		$matches[2] = array_diff($matches[2], $commented[2]);

		if (sizeof($matches))
		{
			foreach ($matches[0] as $idx => $match)
			{
				if (isset($matches[2][$idx]))
				{
					$stylesheet = str_replace($match, acp_styles::load_css_file($theme_row['theme_path'], $matches[2][$idx]), $stylesheet);
				}
			}
		}

		// adjust paths
		return str_replace('./', 'styles/' . $theme_row['theme_path'] . '/theme/', $stylesheet);
	}

	/**
	* Returns an array containing all template filenames for one template that are currently cached.
	*
	* @param string $template_path contains the name of the template's folder in /styles/
	*
	* @return array of filenames that exist in /styles/$template_path/template/ (without extension!)
	*/
	function template_cache_filelist($template_path)
	{
		global $phpbb_root_path, $phpEx, $user;

		$cache_prefix = 'tpl_' . str_replace('_', '-', $template_path);

		if (!($dp = @opendir("{$phpbb_root_path}cache")))
		{
			trigger_error($user->lang['TEMPLATE_ERR_CACHE_READ'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$file_ary = array();
		while ($file = readdir($dp))
		{
			if ($file[0] == '.')
			{
				continue;
			}

			if (is_file($phpbb_root_path . 'cache/' . $file) && (strpos($file, $cache_prefix) === 0))
			{
				$file_ary[] = str_replace('.', '/', preg_replace('#^' . preg_quote($cache_prefix, '#') . '_(.*?)\.html\.' . $phpEx . '$#i', '\1', $file));
			}
		}
		closedir($dp);

		return $file_ary;
	}

	/**
	* Destroys cached versions of template files
	*
	* @param array $template_row contains the template's row in the STYLES_TEMPLATE_TABLE database table
	* @param mixed $file_ary is optional and may contain an array of template file names which should be refreshed in the cache.
	*	The file names should be the original template file names and not the cache file names.
	*/
	function clear_template_cache($template_row, $file_ary = false)
	{
		global $phpbb_root_path, $phpEx, $user;

		$cache_prefix = 'tpl_' . str_replace('_', '-', $template_row['template_path']);

		if (!$file_ary || !is_array($file_ary))
		{
			$file_ary = $this->template_cache_filelist($template_row['template_path']);
			$log_file_list = $user->lang['ALL_FILES'];
		}
		else
		{
			$log_file_list = implode(', ', $file_ary);
		}

		foreach ($file_ary as $file)
		{
			$file = str_replace('/', '.', $file);

			$file = "{$phpbb_root_path}cache/{$cache_prefix}_$file.html.$phpEx";
			if (file_exists($file) && is_file($file))
			{
				@unlink($file);
			}
		}
		unset($file_ary);

		add_log('admin', 'LOG_TEMPLATE_CACHE_CLEARED', $template_row['template_name'], $log_file_list);
	}

	/**
	* Install Style/Template/Theme
	*/
	function install($mode)
	{
		global $phpbb_root_path, $phpEx, $config, $db, $cache, $user, $template;

		$l_type = strtoupper($mode);

		$error = $installcfg = $style_row = array();
		$root_path = $cfg_file = '';
		$element_ary = array('template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE);

		$install_path = request_var('path', '');
		$update = (isset($_POST['update'])) ? true : false;

		// Installing, obtain cfg file contents
		if ($install_path)
		{
			$root_path = $phpbb_root_path . 'styles/' . $install_path . '/';
			$cfg_file = ($mode == 'style') ? "$root_path$mode.cfg" : "$root_path$mode/$mode.cfg";

			if (!file_exists($cfg_file))
			{
				$error[] = $user->lang[$l_type . '_ERR_NOT_' . $l_type];
			}
			else
			{
				$installcfg = parse_cfg_file($cfg_file);
			}
		}

		// Installing
		if (sizeof($installcfg))
		{
			$name		= $installcfg['name'];
			$copyright	= $installcfg['copyright'];
			$version	= $installcfg['version'];

			$style_row = array(
				$mode . '_id'			=> 0,
				$mode . '_name'			=> '',
				$mode . '_copyright'	=> ''
			);

			switch ($mode)
			{
				case 'style':

					$style_row = array(
						'style_id'			=> 0,
						'style_name'		=> $installcfg['name'],
						'style_copyright'	=> $installcfg['copyright']
					);

					$reqd_template = (isset($installcfg['required_template'])) ? $installcfg['required_template'] : false;
					$reqd_theme = (isset($installcfg['required_theme'])) ? $installcfg['required_theme'] : false;

					// Check to see if each element is already installed, if it is grab the id
					foreach ($element_ary as $element => $table)
					{
						$style_row = array_merge($style_row, array(
							$element . '_id'			=> 0,
							$element . '_name'			=> '',
							$element . '_copyright'		=> '')
						);

			 			$this->test_installed($element, $error, (${'reqd_' . $element}) ? $phpbb_root_path . 'styles/' . $reqd_template . '/' : $root_path, ${'reqd_' . $element}, $style_row[$element . '_id'], $style_row[$element . '_name'], $style_row[$element . '_copyright']);

						if (!$style_row[$element . '_name'])
						{
							$style_row[$element . '_name'] = $reqd_template;
						}

						// Merge other information to installcfg... if present
						$cfg_file = $phpbb_root_path . 'styles/' . $install_path . '/' . $element . '/' . $element . '.cfg';

						if (file_exists($cfg_file))
						{
							$cfg_contents = parse_cfg_file($cfg_file);

							// Merge only specific things. We may need them later.
							foreach (array('inherit_from') as $key)
							{
								if (!empty($cfg_contents[$key]) && !isset($installcfg[$key]))
								{
									$installcfg[$key] = $cfg_contents[$key];
								}
							}
						}
					}

				break;

				case 'template':
					$this->test_installed('template', $error, $root_path, false, $style_row['template_id'], $style_row['template_name'], $style_row['template_copyright']);
				break;

				case 'theme':
					$this->test_installed('theme', $error, $root_path, false, $style_row['theme_id'], $style_row['theme_name'], $style_row['theme_copyright']);
				break;
			}
		}
		else
		{
			trigger_error($user->lang['NO_' . $l_type] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$style_row['style_active'] = request_var('style_active', 1);
		$style_row['style_default'] = request_var('style_default', 0);

		// User has submitted form and no errors have occurred
		if ($update && !sizeof($error))
		{
			if ($mode == 'style')
			{
				foreach ($element_ary as $element => $table)
				{
					${$element . '_root_path'} = (${'reqd_' . $element}) ? $phpbb_root_path . 'styles/' . ${'reqd_' . $element} . '/' : false;
					${$element . '_path'} = (${'reqd_' . $element}) ? ${'reqd_' . $element} : false;
				}
				$this->install_style($error, 'install', $root_path, $style_row['style_id'], $style_row['style_name'], $install_path, $style_row['style_copyright'], $style_row['style_active'], $style_row['style_default'], $style_row, $template_root_path, $template_path, $theme_root_path, $theme_path);
			}
			else
			{
				$this->install_element($mode, $error, 'install', $root_path, $style_row[$mode . '_id'], $style_row[$mode . '_name'], $install_path, $style_row[$mode . '_copyright']);
			}

			if (!sizeof($error))
			{
				$cache->destroy('sql', STYLES_TABLE);

				trigger_error($user->lang[$l_type . '_ADDED'] . adm_back_link($this->u_action));
			}
		}

		$this->page_title = 'INSTALL_' . $l_type;

		$template->assign_vars(array(
			'S_DETAILS'			=> true,
			'S_INSTALL'			=> true,
			'S_ERROR_MSG'		=> (sizeof($error)) ? true : false,
			'S_LOCATION'		=> (isset($installcfg['inherit_from']) && $installcfg['inherit_from']) ? false : true,
			'S_STYLE'			=> ($mode == 'style') ? true : false,
			'S_TEMPLATE'		=> ($mode == 'template') ? true : false,
			'S_SUPERTEMPLATE'	=> (isset($installcfg['inherit_from'])) ? $installcfg['inherit_from'] : '',
			'S_THEME'			=> ($mode == 'theme') ? true : false,

			'S_STYLE_ACTIVE'		=> (isset($style_row['style_active'])) ? $style_row['style_active'] : 0,
			'S_STYLE_DEFAULT'		=> (isset($style_row['style_default'])) ? $style_row['style_default'] : 0,

			'U_ACTION'			=> $this->u_action . "&amp;action=install&amp;path=" . urlencode($install_path),
			'U_BACK'			=> $this->u_action,

			'L_TITLE'				=> $user->lang[$this->page_title],
			'L_EXPLAIN'				=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'				=> $user->lang[$l_type . '_NAME'],

			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'				=> $style_row[$mode . '_name'],
			'COPYRIGHT'			=> $style_row[$mode . '_copyright'],
			'TEMPLATE_NAME'		=> ($mode == 'style') ? $style_row['template_name'] : '',
			'THEME_NAME'		=> ($mode == 'style') ? $style_row['theme_name'] : '')
		);
	}

	/**
	* Add new style
	*/
	function add($mode)
	{
		global $phpbb_root_path, $phpEx, $config, $db, $cache, $user, $template;

		$l_type = strtoupper($mode);
		$element_ary = array('template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE);
		$error = array();

		$style_row = array(
			$mode . '_name'			=> utf8_normalize_nfc(request_var('name', '', true)),
			$mode . '_copyright'	=> utf8_normalize_nfc(request_var('copyright', '', true)),
			'template_id'			=> 0,
			'theme_id'				=> 0,
			'style_active'			=> request_var('style_active', 1),
			'style_default'			=> request_var('style_default', 0),
		);

		$basis = request_var('basis', 0);
		$update = (isset($_POST['update'])) ? true : false;

		if ($basis)
		{
			switch ($mode)
			{
				case 'style':
					$sql_select = 'template_id, theme_id';
					$sql_from = STYLES_TABLE;
				break;

				case 'template':
					$sql_select = 'template_id';
					$sql_from = STYLES_TEMPLATE_TABLE;
				break;

				case 'theme':
					$sql_select = 'theme_id';
					$sql_from = STYLES_THEME_TABLE;
				break;
			}

			$sql = "SELECT $sql_select
				FROM $sql_from
				WHERE {$mode}_id = $basis";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$error[] = $user->lang['NO_' . $l_type];
			}

			if (!sizeof($error))
			{
				$style_row['template_id']	= (isset($row['template_id'])) ? $row['template_id'] : $style_row['template_id'];
				$style_row['theme_id']		= (isset($row['theme_id'])) ? $row['theme_id'] : $style_row['theme_id'];
			}
		}

		if ($update)
		{
			$style_row['template_id'] = request_var('template_id', $style_row['template_id']);
			$style_row['theme_id'] = request_var('theme_id', $style_row['theme_id']);

			if ($mode == 'style' && (!$style_row['template_id'] || !$style_row['theme_id']))
			{
				$error[] = $user->lang['STYLE_ERR_NO_IDS'];
			}
		}

		// User has submitted form and no errors have occurred
		if ($update && !sizeof($error))
		{
			if ($mode == 'style')
			{
				$style_row['style_id'] = 0;

				$this->install_style($error, 'add', '', $style_row['style_id'], $style_row['style_name'], '', $style_row['style_copyright'], $style_row['style_active'], $style_row['style_default'], $style_row);
			}

			if (!sizeof($error))
			{
				$cache->destroy('sql', STYLES_TABLE);

				trigger_error($user->lang[$l_type . '_ADDED'] . adm_back_link($this->u_action));
			}
		}

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

		$this->page_title = 'ADD_' . $l_type;

		$template->assign_vars(array(
			'S_DETAILS'			=> true,
			'S_ADD'				=> true,
			'S_ERROR_MSG'		=> (sizeof($error)) ? true : false,
			'S_STYLE'			=> ($mode == 'style') ? true : false,
			'S_TEMPLATE'		=> ($mode == 'template') ? true : false,
			'S_THEME'			=> ($mode == 'theme') ? true : false,
			'S_BASIS'			=> ($basis) ? true : false,

			'S_STYLE_ACTIVE'		=> (isset($style_row['style_active'])) ? $style_row['style_active'] : 0,
			'S_STYLE_DEFAULT'		=> (isset($style_row['style_default'])) ? $style_row['style_default'] : 0,
			'S_TEMPLATE_OPTIONS'	=> ($mode == 'style') ? $template_options : '',
			'S_THEME_OPTIONS'		=> ($mode == 'style') ? $theme_options : '',

			'U_ACTION'			=> $this->u_action . '&amp;action=add&amp;basis=' . $basis,
			'U_BACK'			=> $this->u_action,

			'L_TITLE'				=> $user->lang[$this->page_title],
			'L_EXPLAIN'				=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'				=> $user->lang[$l_type . '_NAME'],

			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'				=> $style_row[$mode . '_name'],
			'COPYRIGHT'			=> $style_row[$mode . '_copyright'])
		);

	}

	/**

					$reqd_template = (isset($installcfg['required_template'])) ? $installcfg['required_template'] : false;
					$reqd_theme = (isset($installcfg['required_theme'])) ? $installcfg['required_theme'] : false;

					// Check to see if each element is already installed, if it is grab the id
					foreach ($element_ary as $element => $table)
					{
						$style_row = array_merge($style_row, array(
							$element . '_id'			=> 0,
							$element . '_name'			=> '',
							$element . '_copyright'		=> '')
						);

			 			$this->test_installed($element, $error, $root_path, ${'reqd_' . $element}, $style_row[$element . '_id'], $style_row[$element . '_name'], $style_row[$element . '_copyright']);
	* Is this element installed? If not, grab its cfg details
	*/
	function test_installed($element, &$error, $root_path, $reqd_name, &$id, &$name, &$copyright)
	{
		global $db, $user;

		switch ($element)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;
		}

		$l_element = strtoupper($element);

		$chk_name = ($reqd_name !== false) ? $reqd_name : $name;

		$sql = "SELECT {$element}_id, {$element}_name
			FROM $sql_from
			WHERE {$element}_name = '" . $db->sql_escape($chk_name) . "'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$name = $row[$element . '_name'];
			$id = $row[$element . '_id'];
		}
		else
		{
			if (!($cfg = @file("$root_path$element/$element.cfg")))
			{
				$error[] = sprintf($user->lang['REQUIRES_' . $l_element], $reqd_name);
				return false;
			}

			$cfg = parse_cfg_file("$root_path$element/$element.cfg", $cfg);

			$name = $cfg['name'];
			$copyright = $cfg['copyright'];
			$id = 0;

			unset($cfg);
		}
		$db->sql_freeresult($result);
	}

	/**
	* Install/Add style
	*/
	function install_style(&$error, $action, $root_path, &$id, $name, $path, $copyright, $active, $default, &$style_row, $template_root_path = false, $template_path = false, $theme_root_path = false, $theme_path = false)
	{
		global $config, $db, $user;

		$element_ary = array('template', 'theme');

		if (!$name)
		{
			$error[] = $user->lang['STYLE_ERR_STYLE_NAME'];
		}

		// Check length settings
		if (utf8_strlen($name) > 30)
		{
			$error[] = $user->lang['STYLE_ERR_NAME_LONG'];
		}

		if (utf8_strlen($copyright) > 60)
		{
			$error[] = $user->lang['STYLE_ERR_COPY_LONG'];
		}

		// Check if the name already exist
		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . "
			WHERE style_name = '" . $db->sql_escape($name) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$error[] = $user->lang['STYLE_ERR_NAME_EXIST'];
		}

		if (sizeof($error))
		{
			return false;
		}

		foreach ($element_ary as $element)
		{
			// Zero id value ... need to install element ... run usual checks
			// and do the install if necessary
			if (!$style_row[$element . '_id'])
			{
				$this->install_element($element, $error, $action, (${$element . '_root_path'}) ? ${$element . '_root_path'} : $root_path, $style_row[$element . '_id'], $style_row[$element . '_name'], (${$element . '_path'}) ? ${$element . '_path'} : $path, $style_row[$element . '_copyright']);
			}
		}

		if (!$style_row['template_id'] || !$style_row['theme_id'])
		{
			$error[] = $user->lang['STYLE_ERR_NO_IDS'];
		}

		if (sizeof($error))
		{
			return false;
		}

		$db->sql_transaction('begin');

		$sql_ary = array(
			'style_name'		=> $name,
			'style_copyright'	=> $copyright,
			'style_active'		=> (int) $active,
			'template_id'		=> (int) $style_row['template_id'],
			'theme_id'			=> (int) $style_row['theme_id'],
		);

		$sql = 'INSERT INTO ' . STYLES_TABLE . '
			' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$id = $db->sql_nextid();

		if ($default)
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_style = $id
				WHERE user_style = " . $config['default_style'];
			$db->sql_query($sql);

			set_config('default_style', $id);
		}

		$db->sql_transaction('commit');

		add_log('admin', 'LOG_STYLE_ADD', $name);
	}

	/**
	* Install/add an element, doing various checks as we go
	*/
	function install_element($mode, &$error, $action, $root_path, &$id, $name, $path, $copyright)
	{
		global $phpbb_root_path, $db, $user;

		// we parse the cfg here (again)
		$cfg_data = parse_cfg_file("$root_path$mode/$mode.cfg");

		switch ($mode)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;
		}

		$l_type = strtoupper($mode);

		if (!$name)
		{
			$error[] = $user->lang[$l_type . '_ERR_STYLE_NAME'];
		}

		// Check length settings
		if (utf8_strlen($name) > 30)
		{
			$error[] = $user->lang[$l_type . '_ERR_NAME_LONG'];
		}

		if (utf8_strlen($copyright) > 60)
		{
			$error[] = $user->lang[$l_type . '_ERR_COPY_LONG'];
		}

		// Check if the name already exist
		$sql = "SELECT {$mode}_id
			FROM $sql_from
			WHERE {$mode}_name = '" . $db->sql_escape($name) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			// If it exist, we just use the style on installation
			if ($action == 'install')
			{
				$id = $row[$mode . '_id'];
				return false;
			}

			$error[] = $user->lang[$l_type . '_ERR_NAME_EXIST'];
		}

		if (isset($cfg_data['inherit_from']) && $cfg_data['inherit_from'])
		{
			if ($mode === 'template')
			{
				$select_bf = ', bbcode_bitfield';
			}
			else
			{
				$select_bf = '';
			}

			$sql = "SELECT {$mode}_id, {$mode}_name, {$mode}_path $select_bf
				FROM $sql_from
				WHERE {$mode}_name = '" . $db->sql_escape($cfg_data['inherit_from']) . "'
					AND {$mode}_inherits_id = 0";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$row)
			{
				$error[] = sprintf($user->lang[$l_type . '_ERR_REQUIRED_OR_INCOMPLETE'], $cfg_data['inherit_from']);
			}
			else
			{
				$inherit_id = $row["{$mode}_id"];
				$inherit_path = $row["{$mode}_path"];
				$inherit_bf = ($mode === 'template') ? $row["bbcode_bitfield"] : false;
			}
		}
		else
		{
			$inherit_id = 0;
			$inherit_path = '';
			$inherit_bf = false;
		}

		if (sizeof($error))
		{
			return false;
		}

		$sql_ary = array(
			$mode . '_name'			=> $name,
			$mode . '_copyright'	=> $copyright,
			$mode . '_path'			=> $path,
		);

		switch ($mode)
		{
			case 'template':
				// We check if the template author defined a different bitfield
				if (!empty($cfg_data['template_bitfield']))
				{
					$sql_ary['bbcode_bitfield'] = $cfg_data['template_bitfield'];
				}
				else if ($inherit_bf)
				{
					$sql_ary['bbcode_bitfield'] = $inherit_bf;
				}
				else
				{
					$sql_ary['bbcode_bitfield'] = $this->template_bitfield;
				}

				if (isset($cfg_data['inherit_from']) && $cfg_data['inherit_from'])
				{
					$sql_ary += array(
						'template_inherits_id'	=> $inherit_id,
						'template_inherit_path' => $inherit_path,
					);
				}
			break;

			case 'theme':
			break;
		}

		$db->sql_transaction('begin');

		$sql = "INSERT INTO $sql_from
			" . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$id = $db->sql_nextid();

		$db->sql_transaction('commit');

		add_log('admin', 'LOG_' . $l_type . '_ADD_FS', $name);
	}

	/**
	* Checks downwards dependencies
	*
	* @access public
	* @param string $mode The element type to check - only template is supported
	* @param int $id The template id
	* @returns false if no component inherits, array with name, path and id for each subtemplate otherwise
	*/
	function check_inheritance($mode, $id)
	{
		global $db;

		$l_type = strtoupper($mode);

		switch ($mode)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;
		}

		$sql = "SELECT {$mode}_id, {$mode}_name, {$mode}_path
			FROM $sql_from
			WHERE {$mode}_inherits_id = " . (int) $id;
		$result = $db->sql_query($sql);

		$names = array();
		while ($row = $db->sql_fetchrow($result))
		{

			$names[$row["{$mode}_id"]] = array(
				"{$mode}_id" => $row["{$mode}_id"],
				"{$mode}_name" => $row["{$mode}_name"],
				"{$mode}_path" => $row["{$mode}_path"],
			);
		}
		$db->sql_freeresult($result);

		if (sizeof($names))
		{
			return $names;
		}
		else
		{
			return false;
		}
	}

	/**
	* Checks upwards dependencies
	*
	* @access public
	* @param string $mode The element type to check - only template is supported
	* @param int $id The template id
	* @returns false if the component does not inherit, array with name, path and id otherwise
	*/
	function get_super($mode, $id)
	{
		global $db;

		$l_type = strtoupper($mode);

		switch ($mode)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;
		}

		$sql = "SELECT {$mode}_inherits_id
			FROM $sql_from
			WHERE {$mode}_id = " . (int) $id;
		$result = $db->sql_query_limit($sql, 1);

		if ($row = $db->sql_fetchrow($result))
		{
			$db->sql_freeresult($result);
		}
		else
		{
			return false;
		}

		$super_id = $row["{$mode}_inherits_id"];

		$sql = "SELECT {$mode}_id, {$mode}_name, {$mode}_path
			FROM $sql_from
			WHERE {$mode}_id = " . (int) $super_id;

		$result = $db->sql_query_limit($sql, 1);
		if ($row = $db->sql_fetchrow($result))
		{
			$db->sql_freeresult($result);
			return $row;
		}

		return false;
	}
}
