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
* @todo check for writable cache/store/files directory
*/

if (!defined('IN_INSTALL'))
{
	// Someone has tried to access the file directly. This is not a good idea, so exit
	exit;
}

if (!empty($setmodules))
{
	// If phpBB is not installed we do not include this module
	if (!phpbb_check_installation_exists($phpbb_root_path, $phpEx) || file_exists($phpbb_root_path . 'cache/install_lock'))
	{
		return;
	}

	$module[] = array(
		'module_type'		=> 'update',
		'module_title'		=> 'UPDATE',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 30,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'VERSION_CHECK', 'FILE_CHECK', 'UPDATE_FILES', 'UPDATE_DB'),
		'module_reqs'		=> ''
	);
}

/**
* Update Installation
*/
class install_update extends module
{
	var $p_master;
	var $update_info;

	var $old_location;
	var $new_location;
	var $latest_version;
	var $current_version;

	var $update_to_version;

	protected $filesystem;

	// Set to false
	var $test_update = false;

	function install_update(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $template, $phpEx, $phpbb_root_path, $user, $db, $config, $cache, $auth, $language;
		global $request, $phpbb_admin_path, $phpbb_adm_relative_path, $phpbb_container, $phpbb_config_php_file;

		// We must enable super globals, otherwise creating a new instance of the request class,
		// using the new container with a dbal connection will fail with the following PHP Notice:
		// Object of class phpbb_request_deactivated_super_global could not be converted to int
		$request->enable_super_globals();

		// Create a normal container now
		$phpbb_container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
		$phpbb_container = $phpbb_container_builder
			->with_config($phpbb_config_php_file)
			->without_cache()
			->without_extensions()
		;

		if (file_exists($phpbb_root_path . 'install/update/new/config'))
		{
			$phpbb_container_builder->with_config_path($phpbb_root_path . 'install/update/new/config');
		}
		$phpbb_container = $phpbb_container_builder->get_container();

		// Writes into global $cache
		/* @var $cache \phpbb\cache\service */
		$cache = $phpbb_container->get('cache');

		$this->filesystem = $phpbb_container->get('filesystem');

		$this->tpl_name = 'install_update';
		$this->page_title = 'UPDATE_INSTALLATION';

		$this->old_location = $phpbb_root_path . 'install/update/old/';
		$this->new_location = $phpbb_root_path . 'install/update/new/';

		// Init DB
		extract($phpbb_config_php_file->get_all());
		require($phpbb_root_path . 'includes/constants.' . $phpEx);

		// Special options for conflicts/modified files
		define('MERGE_NO_MERGE_NEW', 1);
		define('MERGE_NO_MERGE_MOD', 2);
		define('MERGE_NEW_FILE', 3);
		define('MERGE_MOD_FILE', 4);

		$dbms = $phpbb_config_php_file->convert_30_dbms_to_31($dbms);

		$db = new $dbms();

		// Connect to DB
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false);

		// We do not need this any longer, unset for safety purposes
		unset($dbpasswd);

		// We need to fill the config to let internal functions correctly work
		$config = new \phpbb\config\db($db, new \phpbb\cache\driver\dummy, CONFIG_TABLE);

		// Force template recompile
		$config['load_tplcompile'] = 1;

		// First of all, init the user session
		$user->session_begin();
		$auth->acl($user->data);

		// Overwrite user's language with the selected one.
		// Config needs to be changed to ensure that guests also get the selected language.
		$config_default_lang = $config['default_lang'];
		$config['default_lang'] = $language;
		$user->data['user_lang'] = $language;

		$user->add_lang(array('common', 'acp/common', 'acp/board', 'install', 'posting'));

		// Reset the default_lang
		$config['default_lang'] = $config_default_lang;
		unset($config_default_lang);

		// If we are within the intro page we need to make sure we get up-to-date version info
		if ($sub == 'intro')
		{
			$cache->destroy('_version_info');
		}

		// Set custom template again. ;)
		$paths = array($phpbb_root_path . 'install/update/new/adm/style', $phpbb_admin_path . 'style');
		$paths = array_filter($paths, 'is_dir');
		$template->set_custom_style(array(
			array(
				'name' 		=> 'adm',
				'ext_path' 	=> 'adm/style/',
			),
		), $paths);

		$template->assign_vars(array(
			'S_USER_LANG'			=> $user->lang['USER_LANG'],
			'S_CONTENT_DIRECTION'	=> $user->lang['DIRECTION'],
			'S_CONTENT_ENCODING'	=> 'UTF-8',
			'S_CONTENT_FLOW_BEGIN'	=> ($user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
			'S_CONTENT_FLOW_END'	=> ($user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
		));

		// Get current and latest version
		/* @var $version_helper \phpbb\version_helper */
		$version_helper = $phpbb_container->get('version_helper');
		try
		{
			$this->latest_version = $version_helper->get_latest_on_current_branch(true);
		}
		catch (\RuntimeException $e)
		{
			$this->latest_version = false;

			$update_info = array();
			include($phpbb_root_path . 'install/update/index.' . $phpEx);
			$info = (empty($update_info) || !is_array($update_info)) ? false : $update_info;

			if ($info !== false)
			{
				$this->latest_version = (!empty($info['version']['to'])) ? trim($info['version']['to']) : false;
			}
		}

		// For the current version we trick a bit. ;)
		$this->current_version = (!empty($config['version_update_from'])) ? $config['version_update_from'] : $config['version'];

		$up_to_date = (version_compare(str_replace('rc', 'RC', strtolower($this->current_version)), str_replace('rc', 'RC', strtolower($this->latest_version)), '<')) ? false : true;

		// Check for a valid update directory, else point the user to the phpbb.com website
		if (!file_exists($phpbb_root_path . 'install/update') || !file_exists($phpbb_root_path . 'install/update/index.' . $phpEx) || !file_exists($this->old_location) || !file_exists($this->new_location))
		{
			$template->assign_vars(array(
				'S_ERROR'		=> true,
				'ERROR_MSG'		=> ($up_to_date) ? $user->lang['NO_UPDATE_FILES_UP_TO_DATE'] : sprintf($user->lang['NO_UPDATE_FILES_OUTDATED'], $config['version'], $this->current_version, $this->latest_version))
			);

			return;
		}

		$this->update_info = $this->get_file('update_info');

		// Make sure the update directory holds the correct information
		// Since admins are able to run the update/checks more than once we only check if the current version is lower or equal than the version to which we update to.
		if (version_compare(str_replace('rc', 'RC', strtolower($this->current_version)), str_replace('rc', 'RC', strtolower($this->update_info['version']['to'])), '>'))
		{
			$template->assign_vars(array(
				'S_ERROR'		=> true,
				'ERROR_MSG'		=> sprintf($user->lang['INCOMPATIBLE_UPDATE_FILES'], $config['version'], $this->update_info['version']['from'], $this->update_info['version']['to']))
			);

			return;
		}

		// Check if the update files are actually meant to update from the current version
		if ($this->current_version != $this->update_info['version']['from'])
		{
			$template->assign_vars(array(
				'S_ERROR'	=> true,
				'ERROR_MSG'	=> sprintf($user->lang['INCOMPATIBLE_UPDATE_FILES'], $this->current_version, $this->update_info['version']['from'], $this->update_info['version']['to']),
			));
		}

		// Check if the update files stored are for the latest version...
		if (version_compare(strtolower($this->latest_version), strtolower($this->update_info['version']['to']), '>'))
		{
			$template->assign_vars(array(
				'S_WARNING'		=> true,
				'WARNING_MSG'	=> sprintf($user->lang['OLD_UPDATE_FILES'], $this->update_info['version']['from'], $this->update_info['version']['to'], $this->latest_version))
			);
		}

		// We store the "update to" version, because it is not always the latest. ;)
		$this->update_to_version = $this->update_info['version']['to'];

		// Fill DB version
		if (empty($config['dbms_version']))
		{
			$config->set('dbms_version', $db->sql_server_info(true));
		}

		if ($this->test_update === false)
		{
			// What about the language file? Got it updated?
			if (in_array('language/' . $language . '/install.' . $phpEx, $this->update_info['files']))
			{
				$lang = array();
				include($this->new_location . 'language/' . $language . '/install.' . $phpEx);
				// this is the user's language.. just merge it
				$user->lang = array_merge($user->lang, $lang);
			}
			if ($language != 'en' && in_array('language/en/install.' . $phpEx, $this->update_info['files']))
			{
				$lang = array();
				include($this->new_location . 'language/en/install.' . $phpEx);
				// only add new keys to user's language in english
				$new_keys = array_diff(array_keys($lang), array_keys($user->lang));
				foreach ($new_keys as $i => $new_key)
				{
					$user->lang[$new_key] = $lang[$new_key];
				}
			}
		}

		// Include renderer and engine
		$this->include_file('includes/diff/diff.' . $phpEx);
		$this->include_file('includes/diff/engine.' . $phpEx);
		$this->include_file('includes/diff/renderer.' . $phpEx);

		// Make sure we stay at the file check if checking the files again
		if ($request->variable('check_again', false, false, \phpbb\request\request_interface::POST))
		{
			$sub = $this->p_master->sub = 'file_check';
		}

		switch ($sub)
		{
			case 'intro':
				$this->page_title = 'UPDATE_INSTALLATION';

				$template->assign_vars(array(
					'S_INTRO'		=> true,
					'U_ACTION'		=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=version_check"),
				));

				// Make sure the update list is destroyed.
				$cache->destroy('_update_list');
				$cache->destroy('_diff_files');
				$cache->destroy('_expected_files');
			break;

			case 'version_check':
				$this->page_title = 'STAGE_VERSION_CHECK';

				$template->assign_vars(array(
					'S_VERSION_CHECK'	=> true,

					'U_ACTION'			=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=file_check"),

					'S_UP_TO_DATE'		=> $up_to_date,
					'LATEST_VERSION'	=> $this->latest_version,
					'CURRENT_VERSION'	=> $this->current_version,
				));

				// Print out version the update package updates to
				if ($this->latest_version != $this->update_info['version']['to'])
				{
					$template->assign_var('PACKAGE_VERSION', $this->update_info['version']['to']);
				}

				// Since some people try to update to RC releases, but phpBB.com tells them the last version is the version they currently run
				// we are faced with the updater thinking the database schema is up-to-date; which it is, but should be updated none-the-less
				// We now try to cope with this by triggering the update process
				if (version_compare(str_replace('rc', 'RC', strtolower($this->current_version)), str_replace('rc', 'RC', strtolower($this->update_info['version']['to'])), '<'))
				{
					$template->assign_vars(array(
						'S_UP_TO_DATE'		=> false,
					));
				}

			break;

			case 'update_db':
				// Redirect the user to the database update script with some explanations...
				$template->assign_vars(array(
					'S_DB_UPDATE'			=> true,
					'S_DB_UPDATE_FINISHED'	=> ($config['version'] == $this->update_info['version']['to']) ? true : false,
					'U_DB_UPDATE'			=> append_sid($phpbb_root_path . 'install/database_update.' . $phpEx, 'type=1&amp;language=' . $user->data['user_lang']),
					'U_DB_UPDATE_ACTION'	=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=update_db"),
					'U_ACTION'				=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=file_check"),
					'L_EVERYTHING_UP_TO_DATE'	=> $user->lang('EVERYTHING_UP_TO_DATE', append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login'), append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login&amp;redirect=' . $phpbb_adm_relative_path . 'index.php%3Fi=send_statistics%26mode=send_statistics')),
				));

				// Do not display incompatible package note after successful update
				if ($config['version'] == $this->update_info['version']['to'])
				{
					$template->assign_var('S_ERROR', false);
				}
			break;

			case 'file_check':

				// retrieve info on what changes should have already been made to the files.
				$expected_files = $cache->get('_expected_files');
				if (!$expected_files)
				{
					$expected_files = array();
				}

				// Now make sure the previous file collection is no longer valid...
				$cache->destroy('_diff_files');

				$this->page_title = 'STAGE_FILE_CHECK';

				// Now make sure our update list is correct if the admin refreshes
				$action = $request->variable('action', '');

				// We are directly within an update. To make sure our update list is correct we check its status.
				$update_list = ($request->variable('check_again', false, false, \phpbb\request\request_interface::POST)) ? false : $cache->get('_update_list');
				$modified = ($update_list !== false) ? @filemtime($cache->get_driver()->cache_dir . 'data_update_list.' . $phpEx) : 0;

				// Make sure the list is up-to-date
				if ($update_list !== false)
				{
					$get_new_list = false;
					foreach ($this->update_info['files'] as $file)
					{
						if (file_exists($phpbb_root_path . $file) && filemtime($phpbb_root_path . $file) > $modified)
						{
							$get_new_list = true;
							break;
						}
					}
				}
				else
				{
					$get_new_list = true;
				}

				if (!$get_new_list && $update_list['status'] != -1)
				{
					$get_new_list = true;
				}

				if ($get_new_list)
				{
					$this->get_update_structure($update_list, $expected_files);
					$cache->put('_update_list', $update_list);

					// Refresh the page if we are still not finished...
					if ($update_list['status'] != -1)
					{
						$refresh_url = append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=file_check");
						meta_refresh(2, $refresh_url);

						$template->assign_vars(array(
							'S_IN_PROGRESS'		=> true,
							'S_COLLECTED'		=> (int) $update_list['status'],
							'S_TO_COLLECT'		=> sizeof($this->update_info['files']),
							'L_IN_PROGRESS'				=> $user->lang['COLLECTING_FILE_DIFFS'],
							'L_IN_PROGRESS_EXPLAIN'		=> sprintf($user->lang['NUMBER_OF_FILES_COLLECTED'], (int) $update_list['status'], sizeof($this->update_info['files']) + sizeof($this->update_info['deleted'])),
						));

						return;
					}
				}

				if ($action == 'diff')
				{
					$this->show_diff($update_list);
					return;
				}

				if (sizeof($update_list['no_update']))
				{
					$template->assign_vars(array(
						'S_NO_UPDATE_FILES'		=> true,
						'NO_UPDATE_FILES'		=> implode(', ', array_map('htmlspecialchars', $update_list['no_update'])))
					);
				}

				$new_expected_files = array();

				// Now assign the list to the template
				foreach ($update_list as $status => $filelist)
				{
					if ($status == 'no_update' || !sizeof($filelist) || $status == 'status' || $status == 'status_deleted')
					{
						continue;
					}

/*					$template->assign_block_vars('files', array(
						'S_STATUS'		=> true,
						'STATUS'		=> $status,
						'L_STATUS'		=> $user->lang['STATUS_' . strtoupper($status)],
						'TITLE'			=> $user->lang['FILES_' . strtoupper($status)],
						'EXPLAIN'		=> $user->lang['FILES_' . strtoupper($status) . '_EXPLAIN'],
						)
					);*/

					foreach ($filelist as $file_struct)
					{
						$s_binary = (!empty($this->update_info['binary']) && in_array($file_struct['filename'], $this->update_info['binary'])) ? true : false;

						$filename = htmlspecialchars($file_struct['filename']);
						if (strrpos($filename, '/') !== false)
						{
							$dir_part = substr($filename, 0, strrpos($filename, '/') + 1);
							$file_part = substr($filename, strrpos($filename, '/') + 1);
						}
						else
						{
							$dir_part = '';
							$file_part = $filename;
						}

						$diff_url = append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=file_check&amp;action=diff&amp;status=$status&amp;file=" . urlencode($file_struct['filename']));

						if (isset($file_struct['as_expected']) && $file_struct['as_expected'])
						{
							$new_expected_files[$file_struct['filename']] = $expected_files[$file_struct['filename']];
						}
						else
						{
							$template->assign_block_vars($status, array(
								'STATUS'			=> $status,

								'FILENAME'			=> $filename,
								'DIR_PART'			=> $dir_part,
								'FILE_PART'			=> $file_part,
								'NUM_CONFLICTS'		=> (isset($file_struct['conflicts'])) ? $file_struct['conflicts'] : 0,

								'S_CUSTOM'			=> ($file_struct['custom']) ? true : false,
								'S_BINARY'			=> $s_binary,
								'CUSTOM_ORIGINAL'	=> ($file_struct['custom']) ? $file_struct['original'] : '',

								'U_SHOW_DIFF'		=> $diff_url,
								'L_SHOW_DIFF'		=> ($status != 'up_to_date') ? $user->lang['SHOW_DIFF_' . strtoupper($status)] : '',

								'U_VIEW_MOD_FILE'		=> $diff_url . '&amp;op=' . MERGE_MOD_FILE,
								'U_VIEW_NEW_FILE'		=> $diff_url . '&amp;op=' . MERGE_NEW_FILE,
								'U_VIEW_NO_MERGE_MOD'	=> $diff_url . '&amp;op=' . MERGE_NO_MERGE_MOD,
								'U_VIEW_NO_MERGE_NEW'	=> $diff_url . '&amp;op=' . MERGE_NO_MERGE_NEW,
							));
						}
					}
				}

				$cache->put('_expected_files', $new_expected_files);

				$all_up_to_date = true;
				foreach ($update_list as $status => $filelist)
				{
					if ($status != 'up_to_date' && $status != 'custom' && $status != 'status' && $status != 'status_deleted' && sizeof($filelist))
					{
						$all_up_to_date = false;
						break;
					}
				}

				$template->assign_vars(array(
					'S_FILE_CHECK'			=> true,
					'S_ALL_UP_TO_DATE'		=> $all_up_to_date,
					'S_VERSION_UP_TO_DATE'	=> $up_to_date,
					'S_UP_TO_DATE'			=> $up_to_date,
					'U_ACTION'				=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=file_check"),
					'U_UPDATE_ACTION'		=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=update_files"),
					'U_DB_UPDATE_ACTION'	=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=update_db"),
				));

				// Since some people try to update to RC releases, but phpBB.com tells them the last version is the version they currently run
				// we are faced with the updater thinking the database schema is up-to-date; which it is, but should be updated none-the-less
				// We now try to cope with this by triggering the update process
				if (version_compare(str_replace('rc', 'RC', strtolower($this->current_version)), str_replace('rc', 'RC', strtolower($this->update_info['version']['to'])), '<'))
				{
					$template->assign_vars(array(
						'S_UP_TO_DATE'		=> false,
					));
				}

				if ($all_up_to_date)
				{
					global $phpbb_container;

					/* @var $phpbb_log \phpbb\log\log_interface */
					$phpbb_log = $phpbb_container->get('log');

					// Add database update to log
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_UPDATE_PHPBB', time(), array($this->current_version, $this->update_to_version));

					$db->sql_return_on_error(true);
					$db->sql_query('DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = 'version_update_from'");
					$db->sql_return_on_error(false);

					$cache->purge();
				}

			break;

			case 'update_files':

				$this->page_title = 'STAGE_UPDATE_FILES';

				$s_hidden_fields = '';
				$params = array();
				$conflicts = $request->variable('conflict', array('' => 0));
				$modified = $request->variable('modified', array('' => 0));

				foreach ($conflicts as $filename => $merge_option)
				{
					$s_hidden_fields .= '<input type="hidden" name="conflict[' . htmlspecialchars($filename) . ']" value="' . $merge_option . '" />';
					$params[] = 'conflict[' . urlencode($filename) . ']=' . urlencode($merge_option);
				}

				foreach ($modified as $filename => $merge_option)
				{
					if (!$merge_option)
					{
						continue;
					}
					$s_hidden_fields .= '<input type="hidden" name="modified[' . htmlspecialchars($filename) . ']" value="' . $merge_option . '" />';
					$params[] = 'modified[' . urlencode($filename) . ']=' . urlencode($merge_option);
				}

				$no_update = $request->variable('no_update', array(0 => ''));

				foreach ($no_update as $index => $filename)
				{
					$s_hidden_fields .= '<input type="hidden" name="no_update[]" value="' . htmlspecialchars($filename) . '" />';
					$params[] = 'no_update[]=' . urlencode($filename);
				}

				// Before the user is choosing his preferred method, let's create the content list...
				$update_list = $cache->get('_update_list');

				if ($update_list === false)
				{
					trigger_error($user->lang['NO_UPDATE_INFO'], E_USER_ERROR);
				}

				// Check if the conflicts data is valid
				if (sizeof($conflicts))
				{
					$conflict_filenames = array();
					foreach ($update_list['conflict'] as $files)
					{
						$conflict_filenames[] = $files['filename'];
					}

					$new_conflicts = array();
					foreach ($conflicts as $filename => $diff_method)
					{
						if (in_array($filename, $conflict_filenames))
						{
							$new_conflicts[$filename] = $diff_method;
						}
					}

					$conflicts = $new_conflicts;
				}

				// Build list for modifications
				if (sizeof($modified))
				{
					$modified_filenames = array();
					foreach ($update_list['modified'] as $files)
					{
						$modified_filenames[] = $files['filename'];
					}

					$new_modified = array();
					foreach ($modified as $filename => $diff_method)
					{
						if (in_array($filename, $modified_filenames))
						{
							$new_modified[$filename] = $diff_method;
						}
					}

					$modified = $new_modified;
				}

				// Check number of conflicting files, they need to be equal. For modified files the number can differ
				if (sizeof($update_list['conflict']) != sizeof($conflicts))
				{
					trigger_error($user->lang['MERGE_SELECT_ERROR'], E_USER_ERROR);
				}

				// Before we do anything, let us diff the files and store the raw file information "somewhere"
				$get_files = false;
				$file_list = $cache->get('_diff_files');
				$expected_files = $cache->get('_expected_files');

				if ($file_list === false || $file_list['status'] != -1)
				{
					$get_files = true;
				}

				if ($get_files)
				{
					if ($file_list === false)
					{
						$file_list = array(
							'status'	=> 0,
						);
					}

					if (!isset($expected_files) || $expected_files === false)
					{
						$expected_files = array();
					}

					$processed = 0;
					foreach ($update_list as $status => $files)
					{
						if (!is_array($files))
						{
							continue;
						}

						foreach ($files as $file_struct)
						{
							// Skip this file if the user selected to not update it
							if (in_array($file_struct['filename'], $no_update))
							{
								$expected_files[$file_struct['filename']] = false;
								continue;
							}

							// Already handled... then skip of course...
							if (isset($file_list[$file_struct['filename']]))
							{
								continue;
							}

							// Refresh if we reach 5 diffs...
							if ($processed >= 5)
							{
								$cache->put('_diff_files', $file_list);

								if ($request->variable('download', false))
								{
									$params[] = 'download=1';
								}

								$redirect_url = append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=update_files&amp;" . implode('&amp;', $params));
								meta_refresh(3, $redirect_url);

								$template->assign_vars(array(
									'S_IN_PROGRESS'			=> true,
									'L_IN_PROGRESS'			=> $user->lang['MERGING_FILES'],
									'L_IN_PROGRESS_EXPLAIN'	=> $user->lang['MERGING_FILES_EXPLAIN'],
								));

								return;
							}

							if (file_exists($phpbb_root_path . $file_struct['filename']))
							{
								$contents = file_get_contents($phpbb_root_path . $file_struct['filename']);
								if (isset($expected_files[$file_struct['filename']]) && md5($contents) == $expected_files[$file_struct['filename']])
								{
									continue;
								}
							}

							$original_filename = ($file_struct['custom']) ? $file_struct['original'] : $file_struct['filename'];

							switch ($status)
							{
								case 'modified':

									$option = (isset($modified[$file_struct['filename']])) ? $modified[$file_struct['filename']] : 0;

									switch ($option)
									{
										case MERGE_NO_MERGE_NEW:
											$contents = file_get_contents($this->new_location . $original_filename);
										break;

										case MERGE_NO_MERGE_MOD:
											$contents = file_get_contents($phpbb_root_path . $file_struct['filename']);
										break;

										default:
											$diff = $this->return_diff($this->old_location . $original_filename, $phpbb_root_path . $file_struct['filename'], $this->new_location . $original_filename);

											$contents = implode("\n", $diff->merged_output());
											unset($diff);
										break;
									}

									$expected_files[$file_struct['filename']] = md5($contents);
									$file_list[$file_struct['filename']] = '_file_' . md5($file_struct['filename']);
									$cache->put($file_list[$file_struct['filename']], base64_encode($contents));

									$file_list['status']++;
									$processed++;

								break;

								case 'conflict':

									$option = $conflicts[$file_struct['filename']];
									$contents = '';

									switch ($option)
									{
										case MERGE_NO_MERGE_NEW:
											$contents = file_get_contents($this->new_location . $original_filename);
										break;

										case MERGE_NO_MERGE_MOD:
											$contents = file_get_contents($phpbb_root_path . $file_struct['filename']);
										break;

										default:

											$diff = $this->return_diff($this->old_location . $original_filename, $phpbb_root_path . $file_struct['filename'], $this->new_location . $original_filename);

											if ($option == MERGE_NEW_FILE)
											{
												$contents = implode("\n", $diff->merged_new_output());
											}
											else if ($option == MERGE_MOD_FILE)
											{
												$contents = implode("\n", $diff->merged_orig_output());
											}
											else
											{
												unset($diff);
												break 2;
											}

											unset($diff);
										break;
									}

									$expected_files[$file_struct['filename']] = md5($contents);
									$file_list[$file_struct['filename']] = '_file_' . md5($file_struct['filename']);
									$cache->put($file_list[$file_struct['filename']], base64_encode($contents));

									$file_list['status']++;
									$processed++;

								break;
							}
						}
					}
					$cache->put('_expected_files', $expected_files);
				}

				$file_list['status'] = -1;
				$cache->put('_diff_files', $file_list);

				if ($request->variable('download', false))
				{
					$this->include_file('includes/functions_compress.' . $phpEx);

					$use_method = $request->variable('use_method', '');
					$methods = array('.tar');

					$available_methods = array('.tar.gz' => 'zlib', '.tar.bz2' => 'bz2', '.zip' => 'zlib');
					foreach ($available_methods as $type => $module)
					{
						if (!@extension_loaded($module))
						{
							continue;
						}

						$methods[] = $type;
					}

					// Let the user decide in which format he wants to have the pack
					if (!$use_method)
					{
						$this->page_title = 'SELECT_DOWNLOAD_FORMAT';

						$radio_buttons = '';
						foreach ($methods as $method)
						{
							$radio_buttons .= '<label><input type="radio"' . ((!$radio_buttons) ? ' id="use_method"' : '') . ' class="radio" value="' . $method . '" name="use_method" /> ' . $method . '</label>';
						}

						$template->assign_vars(array(
							'S_DOWNLOAD_FILES'		=> true,
							'U_ACTION'				=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=update_files"),
							'RADIO_BUTTONS'			=> $radio_buttons,
							'S_HIDDEN_FIELDS'		=> $s_hidden_fields)
						);

						// To ease the update process create a file location map
						$update_list = $cache->get('_update_list');
						$script_path = ($config['force_server_vars']) ? (($config['script_path'] == '/') ? '/' : $config['script_path'] . '/') : $user->page['root_script_path'];

						foreach ($update_list as $status => $files)
						{
							if ($status == 'up_to_date' || $status == 'no_update' || $status == 'status' || $status == 'status_deleted')
							{
								continue;
							}

							foreach ($files as $file_struct)
							{
								if (in_array($file_struct['filename'], $no_update))
								{
									continue;
								}

								$template->assign_block_vars('location', array(
									'SOURCE'		=> htmlspecialchars($file_struct['filename']),
									'DESTINATION'	=> $script_path . htmlspecialchars($file_struct['filename']),
								));
							}
						}
						return;
					}

					if (!in_array($use_method, $methods))
					{
						$use_method = '.tar';
					}

					$update_mode = 'download';
				}
				else
				{
					$this->include_file('includes/functions_transfer.' . $phpEx);

					// Choose FTP, if not available use fsock...
					$method = basename($request->variable('method', ''));
					$submit = (isset($_POST['submit'])) ? true : false;
					$test_ftp_connection = $request->variable('test_connection', '');

					if (!$method || !class_exists($method))
					{
						$method = 'ftp';
						$methods = transfer::methods();

						if (!in_array('ftp', $methods))
						{
							$method = $methods[0];
						}
					}

					$test_connection = false;
					if ($test_ftp_connection || $submit)
					{
						$transfer = new $method(
							$request->variable('host', ''),
							$request->variable('username', ''),
							htmlspecialchars_decode($request->untrimmed_variable('password', '')),
							$request->variable('root_path', ''),
							$request->variable('port', ''),
							$request->variable('timeout', '')
						);
						$test_connection = $transfer->open_session();

						// Make sure that the directory is correct by checking for the existence of common.php
						if ($test_connection === true)
						{
							// Check for common.php file
							if (!$transfer->file_exists($phpbb_root_path, 'common.' . $phpEx))
							{
								$test_connection = 'ERR_WRONG_PATH_TO_PHPBB';
							}
						}

						$transfer->close_session();

						// Make sure the login details are correct before continuing
						if ($submit && $test_connection !== true)
						{
							$submit = false;
							$test_ftp_connection = true;
						}
					}

					$s_hidden_fields .= build_hidden_fields(array('method' => $method));

					if (!$submit)
					{
						$this->page_title = 'SELECT_FTP_SETTINGS';

						if (!class_exists($method))
						{
							trigger_error('Method does not exist.', E_USER_ERROR);
						}

						$requested_data = call_user_func(array($method, 'data'));
						foreach ($requested_data as $data => $default)
						{
							$template->assign_block_vars('data', array(
								'DATA'		=> $data,
								'NAME'		=> $user->lang[strtoupper($method . '_' . $data)],
								'EXPLAIN'	=> $user->lang[strtoupper($method . '_' . $data) . '_EXPLAIN'],
								'DEFAULT'	=> $request->variable($data, (string) $default),
							));
						}

						$template->assign_vars(array(
							'S_CONNECTION_SUCCESS'		=> ($test_ftp_connection && $test_connection === true) ? true : false,
							'S_CONNECTION_FAILED'		=> ($test_ftp_connection && $test_connection !== true) ? true : false,
							'ERROR_MSG'					=> ($test_ftp_connection && $test_connection !== true) ? $user->lang[$test_connection] : '',

							'S_FTP_UPLOAD'		=> true,
							'UPLOAD_METHOD'		=> $method,
							'U_ACTION'			=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=update_files"),
							'U_DOWNLOAD_METHOD'	=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=update_files&amp;download=1"),
							'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
						));

						return;
					}

					$update_mode = 'upload';
				}

				// Now update the installation or download the archive...
				$download_filename = 'update_' . $this->update_info['version']['from'] . '_to_' . $this->update_info['version']['to'];
				$archive_filename = $download_filename . '_' . time() . '_' . unique_id();

				// Now init the connection
				if ($update_mode == 'download')
				{
					if ($this->filesystem->is_writable($phpbb_root_path . 'store/'))
					{
						trigger_error(sprintf('The directory “%s” is not writable.', $phpbb_root_path . 'store/'), E_USER_ERROR);
					}

					if ($use_method == '.zip')
					{
						$compress = new compress_zip('w', $phpbb_root_path . 'store/' . $archive_filename . $use_method);
					}
					else
					{
						$compress = new compress_tar('w', $phpbb_root_path . 'store/' . $archive_filename . $use_method, $use_method);
					}
				}
				else
				{
					$transfer = new $method(
						$request->variable('host', ''),
						$request->variable('username', ''),
						htmlspecialchars_decode($request->untrimmed_variable('password', '')),
						$request->variable('root_path', ''),
						$request->variable('port', ''),
						$request->variable('timeout', '')
					);
					$transfer->open_session();
				}

				// Ok, go through the update list and do the operations based on their status
				foreach ($update_list as $status => $files)
				{
					if (!is_array($files))
					{
						continue;
					}

					foreach ($files as $file_struct)
					{
						// Skip this file if the user selected to not update it
						if (in_array($file_struct['filename'], $no_update))
						{
							continue;
						}

						$original_filename = ($file_struct['custom']) ? $file_struct['original'] : $file_struct['filename'];

						switch ($status)
						{
							case 'new':
							case 'new_conflict':
							case 'not_modified':

								if ($update_mode == 'download')
								{
									$compress->add_custom_file($this->new_location . $original_filename, $file_struct['filename']);
								}
								else
								{
									if ($status != 'new')
									{
										$transfer->rename($file_struct['filename'], $file_struct['filename'] . '.bak');
									}

									// New directory too?
									$dirname = dirname($file_struct['filename']);

									if ($dirname && !file_exists($phpbb_root_path . $dirname))
									{
										$transfer->make_dir($dirname);
									}

									$transfer->copy_file($this->new_location . $original_filename, $file_struct['filename']);
								}
							break;

							case 'modified':

								$contents = base64_decode($cache->get($file_list[$file_struct['filename']]));

								if ($update_mode == 'download')
								{
									$compress->add_data($contents, $file_struct['filename']);
								}
								else
								{
									// @todo add option to specify if a backup file should be created?
									$transfer->rename($file_struct['filename'], $file_struct['filename'] . '.bak');
									$transfer->write_file($file_struct['filename'], $contents);
								}
							break;

							case 'conflict':

								$contents = base64_decode($cache->get($file_list[$file_struct['filename']]));

								if ($update_mode == 'download')
								{
									$compress->add_data($contents, $file_struct['filename']);
								}
								else
								{
									$transfer->rename($file_struct['filename'], $file_struct['filename'] . '.bak');
									$transfer->write_file($file_struct['filename'], $contents);
								}
							break;

							case 'deleted':

								if ($update_mode != 'download')
								{
									$transfer->rename($file_struct['filename'], $file_struct['filename'] . '.bak');
								}
							break;
						}
					}
				}

				if ($update_mode == 'download')
				{
					$compress->close();

					$compress->download($archive_filename, $download_filename);
					@unlink($phpbb_root_path . 'store/' . $archive_filename . $use_method);

					exit;
				}
				else
				{
					$transfer->close_session();

					$template->assign_vars(array(
						'S_UPLOAD_SUCCESS'	=> true,
						'U_ACTION'			=> append_sid($this->p_master->module_url, "language=$language&amp;mode=$mode&amp;sub=file_check"))
					);
					return;
				}

			break;

		}
	}

	/**
	* Show file diff
	*/
	function show_diff(&$update_list)
	{
		global $phpbb_root_path, $template, $user, $request, $phpbb_adm_relative_path;

		$this->tpl_name = 'install_update_diff';

		$this->page_title = 'VIEWING_FILE_DIFF';

		$status = $request->variable('status', '');
		$file = $request->variable('file', '');
		$diff_mode = $request->variable('diff_mode', 'inline');

		// First of all make sure the file is within our file update list with the correct status
		$found_entry = array();
		foreach ($update_list[$status] as $index => $file_struct)
		{
			if ($file_struct['filename'] === $file)
			{
				$found_entry = $update_list[$status][$index];
			}
		}

		if (empty($found_entry))
		{
			trigger_error($user->lang['FILE_DIFF_NOT_ALLOWED'], E_USER_ERROR);
		}

		// If the status is 'up_to_date' then we do not need to show a diff
		if ($status == 'up_to_date')
		{
			trigger_error($user->lang['FILE_ALREADY_UP_TO_DATE'], E_USER_ERROR);
		}

		$original_file = ($found_entry['custom']) ? $found_entry['original'] : $file;

		// Get the correct diff
		switch ($status)
		{
			case 'conflict':
				$option = $request->variable('op', 0);

				switch ($option)
				{
					case MERGE_NO_MERGE_NEW:
					case MERGE_NO_MERGE_MOD:

						$diff = $this->return_diff(array(), ($option == MERGE_NO_MERGE_NEW) ? $this->new_location . $original_file : $phpbb_root_path . $file);

						$template->assign_var('S_DIFF_NEW_FILE', true);
						$diff_mode = 'inline';
						$this->page_title = 'VIEWING_FILE_CONTENTS';

					break;

					// Merge differences and use new phpBB code for conflicted blocks
					case MERGE_NEW_FILE:
					case MERGE_MOD_FILE:

						$diff = $this->return_diff($this->old_location . $original_file, $phpbb_root_path . $file, $this->new_location . $original_file);

						$template->assign_vars(array(
							'S_DIFF_CONFLICT_FILE'	=> true,
							'NUM_CONFLICTS'			=> $diff->get_num_conflicts())
						);

						$diff = $this->return_diff($phpbb_root_path . $file, ($option == MERGE_NEW_FILE) ? $diff->merged_new_output() : $diff->merged_orig_output());
					break;

					// Download conflict file
					default:

						$diff = $this->return_diff($this->old_location . $original_file, $phpbb_root_path . $file, $this->new_location . $original_file);

						header('Pragma: no-cache');
						header("Content-Type: application/octetstream; name=\"$file\"");
						header("Content-disposition: attachment; filename=$file");

						@set_time_limit(0);

						echo implode("\n", $diff->get_conflicts_content());

						flush();
						exit;

					break;
				}

			break;

			case 'modified':
				$option = $request->variable('op', 0);

				switch ($option)
				{
					case MERGE_NO_MERGE_NEW:
					case MERGE_NO_MERGE_MOD:

						$diff = $this->return_diff(array(), ($option == MERGE_NO_MERGE_NEW) ? $this->new_location . $original_file : $phpbb_root_path . $file);

						$template->assign_var('S_DIFF_NEW_FILE', true);
						$diff_mode = 'inline';
						$this->page_title = 'VIEWING_FILE_CONTENTS';

					break;

					default:
						$diff = $this->return_diff($this->old_location . $original_file, $phpbb_root_path . $original_file, $this->new_location . $file);
						$diff = $this->return_diff($phpbb_root_path . $file, $diff->merged_output());
					break;
				}
			break;

			case 'not_modified':
			case 'new_conflict':
				$diff = $this->return_diff($phpbb_root_path . $file, $this->new_location . $original_file);
			break;

			case 'new':

				$diff = $this->return_diff(array(), $this->new_location . $original_file);

				$template->assign_var('S_DIFF_NEW_FILE', true);
				$diff_mode = 'inline';
				$this->page_title = 'VIEWING_FILE_CONTENTS';

			break;

			case 'deleted':

				$diff = $this->return_diff(array(), $phpbb_root_path . $original_file);

				$template->assign_var('S_DIFF_NEW_FILE', true);
				$diff_mode = 'inline';
				$this->page_title = 'VIEWING_FILE_CONTENTS';

			break;
		}

		$diff_mode_options = '';
		foreach (array('side_by_side', 'inline', 'unified', 'raw') as $option)
		{
			$diff_mode_options .= '<option value="' . $option . '"' . (($diff_mode == $option) ? ' selected="selected"' : '') . '>' . $user->lang['DIFF_' . strtoupper($option)] . '</option>';
		}

		// Now the correct renderer
		$render_class = 'diff_renderer_' . $diff_mode;

		if (!class_exists($render_class))
		{
			trigger_error('Chosen diff mode is not supported', E_USER_ERROR);
		}

		$renderer = new $render_class();

		$template->assign_vars(array(
			'DIFF_CONTENT'			=> $renderer->get_diff_content($diff),
			'DIFF_MODE'				=> $diff_mode,
			'S_DIFF_MODE_OPTIONS'	=> $diff_mode_options,
			'S_SHOW_DIFF'			=> true,
		));

		unset($diff, $renderer);
	}

	/**
	* Collect all file status infos we need for the update by diffing all files
	*/
	function get_update_structure(&$update_list, $expected_files)
	{
		global $phpbb_root_path, $phpEx, $user;

		if ($update_list === false)
		{
			$update_list = array(
				'up_to_date'	=> array(),
				'new'			=> array(),
				'not_modified'	=> array(),
				'modified'		=> array(),
				'new_conflict'	=> array(),
				'conflict'		=> array(),
				'no_update'		=> array(),
				'deleted'		=> array(),
				'status'		=> 0,
				'status_deleted'=> 0,
			);
		}

		/* if (!empty($this->update_info['custom']))
		{
			foreach ($this->update_info['custom'] as $original_file => $file_ary)
			{
				foreach ($file_ary as $index => $file)
				{
					$this->make_update_diff($update_list, $original_file, $file, true);
				}
			}
		} */

		// Get a list of those files which are completely new by checking with file_exists...
		$num_bytes_processed = 0;

		foreach ($this->update_info['files'] as $index => $file)
		{
			if (is_int($update_list['status']) && $index < $update_list['status'])
			{
				continue;
			}

			if ($num_bytes_processed >= 500 * 1024)
			{
				return;
			}

			if (!file_exists($phpbb_root_path . $file))
			{
				// Make sure the update files are consistent by checking if the file is in new_files...
				if (!file_exists($this->new_location . $file))
				{
					trigger_error($user->lang['INCOMPLETE_UPDATE_FILES'], E_USER_ERROR);
				}

				// If the file exists within the old directory the file got removed and we will write it back
				// not a biggie, but we might want to state this circumstance separately later.
				//	if (file_exists($this->old_location . $file))
				//	{
				//		$update_list['removed'][] = $file;
				//	}

				/* Only include a new file as new if the underlying path exist
				// The path normally do not exist if the original style or language has been removed
				if (file_exists($phpbb_root_path . dirname($file)))
				{
					$this->get_custom_info($update_list['new'], $file);
					$update_list['new'][] = array('filename' => $file, 'custom' => false);
				}
				else
				{
					// Do not include style-related or language-related content
					if (strpos($file, 'styles/') !== 0 && strpos($file, 'language/') !== 0)
					{
						$update_list['no_update'][] = $file;
					}
				}*/

				if (!phpbb_ignore_new_file_on_update($phpbb_root_path, $file))
				{
					$this->get_custom_info($update_list['new'], $file);
					$update_list['new'][] = array('filename' => $file, 'custom' => false);
				}

				// unset($this->update_info['files'][$index]);
			}
			else
			{
				// not modified?
				$this->make_update_diff($update_list, $file, $file, $expected_files);
			}

			$num_bytes_processed += (file_exists($this->new_location . $file)) ? filesize($this->new_location . $file) : 100 * 1024;
			$update_list['status']++;
		}

		foreach ($this->update_info['deleted'] as $index => $file)
		{
			if (is_int($update_list['status_deleted']) && $index < $update_list['status_deleted'])
			{
				continue;
			}

			if ($num_bytes_processed >= 500 * 1024)
			{
				return;
			}

			if (file_exists($phpbb_root_path . $file))
			{
				$update_list['deleted'][] = array('filename' => $file, 'custom' => false, 'as_expected' => false);
				$num_bytes_processed += filesize($phpbb_root_path . $file);
			}

			$update_list['status_deleted']++;
			$update_list['status']++;
		}

		$update_list['status_deleted'] = -1;
		$update_list['status'] = -1;

/*		if (!sizeof($this->update_info['files']))
		{
			return $update_list;
		}

		// Now diff the remaining files to get information about their status (not modified/modified/up-to-date)

		// not modified?
		foreach ($this->update_info['files'] as $index => $file)
		{
			$this->make_update_diff($update_list, $file, $file);
		}

		// Now to the styles...
		if (empty($this->update_info['custom']))
		{
			return $update_list;
		}

		foreach ($this->update_info['custom'] as $original_file => $file_ary)
		{
			foreach ($file_ary as $index => $file)
			{
				$this->make_update_diff($update_list, $original_file, $file, true);
			}
		}

		return $update_list;*/
	}

	/**
	* Compare files for storage in update_list
	*/
	function make_update_diff(&$update_list, $original_file, $file, $expected_files, $custom = false)
	{
		global $phpbb_root_path, $user;

		$update_ary = array('filename' => $file, 'custom' => $custom, 'as_expected' => false);

		if ($custom)
		{
			$update_ary['original'] = $original_file;
		}

		if (file_exists($phpbb_root_path . $file))
		{
			$content = file_get_contents($phpbb_root_path . $file);

			if (isset($expected_files[$file]) && // the user already selected what to do with this file
				($expected_files[$file] === false || // the user wanted this file to stay the same, so just assume it's alright
				$expected_files[$file] === md5($content)))
			{
				// the file contains what it was supposed to contain after the merge
				$update_ary['as_expected'] = true;
				$update_ary['was_ignored'] = ($expected_files[$file] === false);
				$update_list['up_to_date'][] = $update_ary;

				return;
			}
		}

		// we only want to know if the files are successfully merged and newlines could result in errors (duplicate addition of lines and such things)
		// Therefore we check for empty diffs with two methods, preserving newlines and not preserving them (which mostly works best, therefore the first option)

		// On a successfull update the new location file exists but the old one does not exist.
		// Check for this circumstance, the new file need to be up-to-date with the current file then...
		if (!file_exists($this->old_location . $original_file) && file_exists($this->new_location . $original_file) && file_exists($phpbb_root_path . $file))
		{
			$tmp = array(
				'file1'		=> file_get_contents($this->new_location . $original_file),
				'file2'		=> $content,
			);

			// We need to diff the contents here to make sure the file is really the one we expect
			$diff = new diff($tmp['file1'], $tmp['file2'], false);
			$empty = $diff->is_empty();

			unset($tmp, $diff);

			// if there are no differences we have an up-to-date file...
			if ($empty)
			{
				$update_list['up_to_date'][] = $update_ary;
				return;
			}

			// If no other status matches we have another file in the way...
			$update_list['new_conflict'][] = $update_ary;
			return;
		}

		// Old file removed?
		if (file_exists($this->old_location . $original_file) && !file_exists($this->new_location . $original_file))
		{
			return;
		}

		// Check for existance, else abort immediately
		if (!file_exists($this->old_location . $original_file) || !file_exists($this->new_location . $original_file))
		{
			trigger_error($user->lang['INCOMPLETE_UPDATE_FILES'], E_USER_ERROR);
		}

		$preserve_cr_ary = array(false, true);

		foreach ($preserve_cr_ary as $preserve_cr)
		{
			$tmp = array(
				'file1'		=> file_get_contents($this->old_location . $original_file),
				'file2'		=> $content,
			);

			// We need to diff the contents here to make sure the file is really the one we expect
			$diff = new diff($tmp['file1'], $tmp['file2'], $preserve_cr);
			$empty_1 = $diff->is_empty();

			unset($tmp, $diff);

			$tmp = array(
				'file1'		=> file_get_contents($this->new_location . $original_file),
				'file2'		=> $content,
			);

			$diff = new diff($tmp['file1'], $tmp['file2'], $preserve_cr);
			$empty_2 = $diff->is_empty();

			unset($tmp, $diff);

			// If the file is not modified we are finished here...
			if ($empty_1)
			{
				// Further check if it is already up to date - it could happen that non-modified files
				// slip through
				if ($empty_2)
				{
					$update_list['up_to_date'][] = $update_ary;
					return;
				}

				$update_list['not_modified'][] = $update_ary;
				return;
			}

			// If the file had been modified then we need to check if it is already up to date

			// if there are no differences we have an up-to-date file...
			if ($empty_2)
			{
				$update_list['up_to_date'][] = $update_ary;
				return;
			}
		}

		$conflicts = false;

		foreach ($preserve_cr_ary as $preserve_cr)
		{
			// if the file is modified we try to make sure a merge succeed
			$tmp = array(
				'orig'		=> file_get_contents($this->old_location . $original_file),
				'final1'	=> file_get_contents($phpbb_root_path . $file),
				'final2'	=> file_get_contents($this->new_location . $original_file),
			);

			$diff = new diff3($tmp['orig'], $tmp['final1'], $tmp['final2'], $preserve_cr);
			unset($tmp);

			if (!$diff->get_num_conflicts())
			{
				$tmp = array(
					'file1'		=> file_get_contents($phpbb_root_path . $file),
					'file2'		=> implode("\n", $diff->merged_output()),
				);

				// now compare the merged output with the original file to see if the modified file is up to date
				$diff2 = new diff($tmp['file1'], $tmp['file2'], $preserve_cr);
				$empty = $diff2->is_empty();

				unset($diff, $diff2);

				if ($empty)
				{
					$update_list['up_to_date'][] = $update_ary;
					return;
				}

				// If we preserve cr tag it as modified because the conflict would not show in this mode anyway
				if ($preserve_cr)
				{
					$update_list['modified'][] = $update_ary;
					return;
				}
			}
			else
			{
				// There is one special case... users having merged with a conflicting file... we need to check this
				$tmp = array(
					'file1'		=> file_get_contents($phpbb_root_path . $file),
					'file2'		=> implode("\n", $diff->merged_new_output()),
				);

				$diff2 = new diff($tmp['file1'], $tmp['file2'], $preserve_cr);
				$empty = $diff2->is_empty();

				if (!$empty)
				{
					unset($tmp, $diff2);

					// We check if the user merged with his output
					$tmp = array(
						'file1'		=> file_get_contents($phpbb_root_path . $file),
						'file2'		=> implode("\n", $diff->merged_orig_output()),
					);

					$diff2 = new diff($tmp['file1'], $tmp['file2'], $preserve_cr);
					$empty = $diff2->is_empty();
				}

				if (!$empty)
				{
					$conflicts = $diff->get_num_conflicts();
				}

				unset($diff, $diff2);

				if ($empty)
				{
					// A conflict got resolved...
					$update_list['up_to_date'][] = $update_ary;
					return;
				}
			}
		}

		if ($conflicts !== false)
		{
			$update_ary['conflicts'] = $conflicts;
			$update_list['conflict'][] = $update_ary;
			return;
		}

		// If no other status matches we have a modified file...
		$update_list['modified'][] = $update_ary;
	}

	/**
	* Update update_list with custom new files
	*/
	function get_custom_info(&$update_list, $file)
	{
		if (empty($this->update_info['custom']))
		{
			return;
		}

		if (isset($this->update_info['custom'][$file]))
		{
			foreach ($this->update_info['custom'][$file] as $_file)
			{
				$update_list[] = array('filename' => $_file, 'custom' => true, 'original' => $file);
			}
		}
	}

	/**
	* Get remote file
	*/
	function get_file($mode)
	{
		global $user, $db;

		$errstr = '';
		$errno = 0;

		switch ($mode)
		{
			case 'update_info':
				global $phpbb_root_path, $phpEx;

				$update_info = array();
				include($phpbb_root_path . 'install/update/index.' . $phpEx);

				$info = (empty($update_info) || !is_array($update_info)) ? false : $update_info;
				$errstr = ($info === false) ? $user->lang['WRONG_INFO_FILE_FORMAT'] : '';

				if ($info !== false)
				{
					// We assume that all file extensions have been renamed to .$phpEx,
					// if someone is using a non .php file extension for php files.
					// However, in $update_info['files'] we use hardcoded .php.
					// We therefore replace .php with .$phpEx.
					$info['files'] = preg_replace('/\.php$/i', ".$phpEx", $info['files']);

					// Adjust the update info file to hold some specific style-related information
					$info['custom'] = array();
/*
					// Get custom installed styles...
					$sql = 'SELECT style_name, style_path
						FROM ' . STYLES_TABLE . "
						WHERE LOWER(style_name) NOT IN ('prosilver')";
					$result = $db->sql_query($sql);

					$templates = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$templates[] = $row;
					}
					$db->sql_freeresult($result);

					if (sizeof($templates))
					{
						foreach ($info['files'] as $filename)
						{
							// Template update?
							if (strpos(strtolower($filename), 'styles/prosilver/template/') === 0)
							{
								foreach ($templates as $row)
								{
									$info['custom'][$filename][] = str_replace('/prosilver/', '/' . $row['style_path'] . '/', $filename);
								}
							}
						}
					}
*/
				}
			break;

			default:
				trigger_error('Mode for getting remote file not specified', E_USER_ERROR);
			break;
		}

		if ($info === false)
		{
			trigger_error($errstr, E_USER_ERROR);
		}

		return $info;
	}

	/**
	* Function for including files...
	*/
	function include_file($filename)
	{
		global $phpbb_root_path, $phpEx;

		if (!empty($this->update_info['files']) && in_array($filename, $this->update_info['files']))
		{
			include_once($this->new_location . $filename);
		}
		else
		{
			include_once($phpbb_root_path . $filename);
		}
	}

	/**
	* Wrapper for returning a diff object
	*/
	function return_diff()
	{
		$args = func_get_args();
		$three_way_diff = (func_num_args() > 2) ? true : false;

		$file1 = array_shift($args);
		$file2 = array_shift($args);

		$tmp['file1'] = (!empty($file1) && is_string($file1)) ? file_get_contents($file1) : $file1;
		$tmp['file2'] = (!empty($file2) && is_string($file2)) ? file_get_contents($file2) : $file2;

		if ($three_way_diff)
		{
			$file3 = array_shift($args);
			$tmp['file3'] = (!empty($file3) && is_string($file3)) ? file_get_contents($file3) : $file3;

			$diff = new diff3($tmp['file1'], $tmp['file2'], $tmp['file3']);
		}
		else
		{
			$diff = new diff($tmp['file1'], $tmp['file2']);
		}

		unset($tmp);

		return $diff;
	}
}
