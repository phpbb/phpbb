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

class acp_language
{
	var $u_action;
	var $main_files;
	var $language_header = '';
	var $lang_header = '';

	var $language_file = '';
	var $language_directory = '';

	function main($id, $mode)
	{
		global $config, $db, $user, $template, $phpbb_log, $phpbb_container;
		global $phpbb_root_path, $phpEx, $request, $phpbb_dispatcher;

		if (!function_exists('validate_language_iso_name'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		// Check and set some common vars
		$action		= (isset($_POST['update_details'])) ? 'update_details' : '';
		$action		= (isset($_POST['remove_store'])) ? 'details' : $action;

		$submit = (empty($action) && !isset($_POST['update']) && !isset($_POST['test_connection'])) ? false : true;
		$action = (empty($action)) ? $request->variable('action', '') : $action;

		$form_name = 'acp_lang';
		add_form_key('acp_lang');

		$lang_id = $request->variable('id', 0);

		$selected_lang_file = $request->variable('language_file', '|common.' . $phpEx);

		list($this->language_directory, $this->language_file) = explode('|', $selected_lang_file);

		$this->language_directory = basename($this->language_directory);
		$this->language_file = basename($this->language_file);

		$user->add_lang('acp/language');
		$this->tpl_name = 'acp_language';
		$this->page_title = 'ACP_LANGUAGE_PACKS';

		switch ($action)
		{
			case 'update_details':

				if (!$submit || !check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$lang_id)
				{
					trigger_error($user->lang['NO_LANG_ID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . LANG_TABLE . "
					WHERE lang_id = $lang_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$sql_ary	= array(
					'lang_english_name'		=> $request->variable('lang_english_name', $row['lang_english_name']),
					'lang_local_name'		=> $request->variable('lang_local_name', $row['lang_local_name'], true),
					'lang_author'			=> $request->variable('lang_author', $row['lang_author'], true),
				);

				$db->sql_query('UPDATE ' . LANG_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE lang_id = ' . $lang_id);

				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_LANGUAGE_PACK_UPDATED', false, array($sql_ary['lang_english_name']));

				trigger_error($user->lang['LANGUAGE_DETAILS_UPDATED'] . adm_back_link($this->u_action));
			break;

			case 'details':

				if (!$lang_id)
				{
					trigger_error($user->lang['NO_LANG_ID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$this->page_title = 'LANGUAGE_PACK_DETAILS';

				$sql = 'SELECT *
					FROM ' . LANG_TABLE . '
					WHERE lang_id = ' . $lang_id;
				$result = $db->sql_query($sql);
				$lang_entries = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$lang_entries)
				{
					trigger_error($user->lang['LANGUAGE_PACK_NOT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$lang_iso = $lang_entries['lang_iso'];

				$template->assign_vars(array(
					'S_DETAILS'			=> true,
					'U_ACTION'			=> $this->u_action . "&amp;action=details&amp;id=$lang_id",
					'U_BACK'			=> $this->u_action,

					'LANG_LOCAL_NAME'	=> $lang_entries['lang_local_name'],
					'LANG_ENGLISH_NAME'	=> $lang_entries['lang_english_name'],
					'LANG_ISO'			=> $lang_iso,
					'LANG_AUTHOR'		=> $lang_entries['lang_author'],
					'L_MISSING_FILES'			=> $user->lang('THOSE_MISSING_LANG_FILES', $lang_entries['lang_local_name']),
					'L_MISSING_VARS_EXPLAIN'	=> $user->lang('THOSE_MISSING_LANG_VARIABLES', $lang_entries['lang_local_name']),
				));

				// If current lang is different from the default lang, then highlight missing files and variables
				if ($lang_iso != $config['default_lang'])
				{
					try
					{
						$iterator = new \RecursiveIteratorIterator(
							new \phpbb\recursive_dot_prefix_filter_iterator(
								new \RecursiveDirectoryIterator(
									$phpbb_root_path . 'language/' . $config['default_lang'] . '/',
									\FilesystemIterator::SKIP_DOTS
								)
							),
							\RecursiveIteratorIterator::LEAVES_ONLY
						);
					}
					catch (\Exception $e)
					{
						return array();
					}

					foreach ($iterator as $file_info)
					{
						/** @var \RecursiveDirectoryIterator $file_info */
						$relative_path = $iterator->getInnerIterator()->getSubPathname();
						$relative_path = str_replace(DIRECTORY_SEPARATOR, '/', $relative_path);

						if (file_exists($phpbb_root_path . 'language/' . $lang_iso . '/' . $relative_path))
						{
							if (substr($relative_path, 0 - strlen($phpEx)) === $phpEx)
							{
								$missing_vars = $this->compare_language_files($config['default_lang'], $lang_iso, $relative_path);

								if (!empty($missing_vars))
								{
									$template->assign_block_vars('missing_varfile', array(
										'FILE_NAME'			=> $relative_path,
									));

									foreach ($missing_vars as $var)
									{
										$template->assign_block_vars('missing_varfile.variable', array(
												'VAR_NAME'			=> $var,
										));
									}
								}
							}
						}
						else
						{
							$template->assign_block_vars('missing_files', array(
								'FILE_NAME' => $relative_path,
							));
						}
					}
				}
				return;
			break;

			case 'delete':

				if (!$lang_id)
				{
					trigger_error($user->lang['NO_LANG_ID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . LANG_TABLE . '
					WHERE lang_id = ' . $lang_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row['lang_iso'] == $config['default_lang'])
				{
					trigger_error($user->lang['NO_REMOVE_DEFAULT_LANG'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$db->sql_query('DELETE FROM ' . LANG_TABLE . ' WHERE lang_id = ' . $lang_id);

					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_lang = '" . $db->sql_escape($config['default_lang']) . "'
						WHERE user_lang = '" . $db->sql_escape($row['lang_iso']) . "'";
					$db->sql_query($sql);

					// We also need to remove the translated entries for custom profile fields - we want clean tables, don't we?
					$sql = 'DELETE FROM ' . PROFILE_LANG_TABLE . ' WHERE lang_id = ' . $lang_id;
					$db->sql_query($sql);

					$sql = 'DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . ' WHERE lang_id = ' . $lang_id;
					$db->sql_query($sql);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_LANGUAGE_PACK_DELETED', false, array($row['lang_english_name']));

					$delete_message = sprintf($user->lang['LANGUAGE_PACK_DELETED'], $row['lang_english_name']);
					$lang_iso = $row['lang_iso'];
					/**
					 * Run code after language deleted
					 *
					 * @event core.acp_language_after_delete
					 * @var	string 	lang_iso     	Language ISO code
					 * @var	string  delete_message  Delete message appear to user
					 * @since 3.2.2-RC1
					 */
					$vars = array('lang_iso', 'delete_message');
					extract($phpbb_dispatcher->trigger_event('core.acp_language_after_delete', compact($vars)));

					trigger_error($delete_message . adm_back_link($this->u_action));
				}
				else
				{
					$s_hidden_fields = array(
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
						'id'		=> $lang_id,
					);
					confirm_box(false, $user->lang('DELETE_LANGUAGE_CONFIRM', $row['lang_english_name']), build_hidden_fields($s_hidden_fields));
				}
			break;

			case 'install':
				if (!check_link_hash($request->variable('hash', ''), 'acp_language'))
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$lang_iso = $request->variable('iso', '');
				$lang_iso = basename($lang_iso);

				if (!$lang_iso || !file_exists("{$phpbb_root_path}language/$lang_iso/iso.txt"))
				{
					trigger_error($user->lang['LANGUAGE_PACK_NOT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$file = file("{$phpbb_root_path}language/$lang_iso/iso.txt");

				$lang_pack = array(
					'iso'		=> $lang_iso,
					'name'		=> trim(htmlspecialchars($file[0])),
					'local_name'=> trim(htmlspecialchars($file[1], ENT_COMPAT, 'UTF-8')),
					'author'	=> trim(htmlspecialchars($file[2], ENT_COMPAT, 'UTF-8'))
				);
				unset($file);

				$sql = 'SELECT lang_iso
					FROM ' . LANG_TABLE . "
					WHERE lang_iso = '" . $db->sql_escape($lang_iso) . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row)
				{
					trigger_error($user->lang['LANGUAGE_PACK_ALREADY_INSTALLED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$lang_pack['name'] || !$lang_pack['local_name'])
				{
					trigger_error($user->lang['INVALID_LANGUAGE_PACK'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Add language pack
				$sql_ary = array(
					'lang_iso'			=> $lang_pack['iso'],
					'lang_dir'			=> $lang_pack['iso'],
					'lang_english_name'	=> $lang_pack['name'],
					'lang_local_name'	=> $lang_pack['local_name'],
					'lang_author'		=> $lang_pack['author']
				);

				$db->sql_query('INSERT INTO ' . LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				$lang_id = $db->sql_nextid();

				// Now let's copy the default language entries for custom profile fields for this new language - makes admin's life easier.
				$sql = 'SELECT lang_id
					FROM ' . LANG_TABLE . "
					WHERE lang_iso = '" . $db->sql_escape($config['default_lang']) . "'";
				$result = $db->sql_query($sql);
				$default_lang_id = (int) $db->sql_fetchfield('lang_id');
				$db->sql_freeresult($result);

				// We want to notify the admin that custom profile fields need to be updated for the new language.
				$notify_cpf_update = false;

				// From the mysql documentation:
				// Prior to MySQL 4.0.14, the target table of the INSERT statement cannot appear in the FROM clause of the SELECT part of the query. This limitation is lifted in 4.0.14.
				// Due to this we stay on the safe side if we do the insertion "the manual way"

				$sql = 'SELECT field_id, lang_name, lang_explain, lang_default_value
					FROM ' . PROFILE_LANG_TABLE . '
					WHERE lang_id = ' . $default_lang_id;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row['lang_id'] = $lang_id;
					$db->sql_query('INSERT INTO ' . PROFILE_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $row));
					$notify_cpf_update = true;
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT field_id, option_id, field_type, lang_value
					FROM ' . PROFILE_FIELDS_LANG_TABLE . '
					WHERE lang_id = ' . $default_lang_id;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row['lang_id'] = $lang_id;
					$db->sql_query('INSERT INTO ' . PROFILE_FIELDS_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $row));
					$notify_cpf_update = true;
				}
				$db->sql_freeresult($result);

				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_LANGUAGE_PACK_INSTALLED', false, array($lang_pack['name']));

				$message = sprintf($user->lang['LANGUAGE_PACK_INSTALLED'], $lang_pack['name']);
				$message .= ($notify_cpf_update) ? '<br /><br />' . $user->lang['LANGUAGE_PACK_CPF_UPDATE'] : '';
				trigger_error($message . adm_back_link($this->u_action));

			break;
		}

		$sql = 'SELECT user_lang, COUNT(user_lang) AS lang_count
			FROM ' . USERS_TABLE . '
			GROUP BY user_lang';
		$result = $db->sql_query($sql);

		$lang_count = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$lang_count[$row['user_lang']] = $row['lang_count'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT *
			FROM ' . LANG_TABLE . '
			ORDER BY lang_english_name';
		$result = $db->sql_query($sql);

		$installed = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$installed[] = $row['lang_iso'];
			$tagstyle = ($row['lang_iso'] == $config['default_lang']) ? '*' : '';

			$template->assign_block_vars('lang', array(
				'U_DETAILS'			=> $this->u_action . "&amp;action=details&amp;id={$row['lang_id']}",
				'U_DOWNLOAD'		=> $this->u_action . "&amp;action=download&amp;id={$row['lang_id']}",
				'U_DELETE'			=> $this->u_action . "&amp;action=delete&amp;id={$row['lang_id']}",

				'ENGLISH_NAME'		=> $row['lang_english_name'],
				'TAG'				=> $tagstyle,
				'LOCAL_NAME'		=> $row['lang_local_name'],
				'ISO'				=> $row['lang_iso'],
				'USED_BY'			=> (isset($lang_count[$row['lang_iso']])) ? $lang_count[$row['lang_iso']] : 0,
			));
		}
		$db->sql_freeresult($result);

		$new_ary = $iso = array();

		/** @var \phpbb\language\language_file_helper $language_helper */
		$language_helper = $phpbb_container->get('language.helper.language_file');
		$iso = $language_helper->get_available_languages();

		foreach ($iso as $lang_array)
		{
			$lang_iso = $lang_array['iso'];

			if (!in_array($lang_iso, $installed))
			{
				$new_ary[$lang_iso] = $lang_array;
			}
		}

		unset($installed);

		if (count($new_ary))
		{
			foreach ($new_ary as $iso => $lang_ary)
			{
				$template->assign_block_vars('notinst', array(
					'ISO'			=> htmlspecialchars($lang_ary['iso']),
					'LOCAL_NAME'	=> htmlspecialchars($lang_ary['local_name'], ENT_COMPAT, 'UTF-8'),
					'NAME'			=> htmlspecialchars($lang_ary['name'], ENT_COMPAT, 'UTF-8'),
					'U_INSTALL'		=> $this->u_action . '&amp;action=install&amp;iso=' . urlencode($lang_ary['iso']) . '&amp;hash=' . generate_link_hash('acp_language'))
				);
			}
		}

		unset($new_ary);
	}

	/**
	* Compare two language files
	*/
	function compare_language_files($source_lang, $dest_lang, $file)
	{
		global $phpbb_root_path;

		$source_file = $phpbb_root_path . 'language/' . $source_lang . '/' . $file;
		$dest_file = $phpbb_root_path . 'language/' . $dest_lang . '/' . $file;

		if (!file_exists($dest_file))
		{
			return array();
		}

		$lang = array();
		include($source_file);
		$lang_entry_src = $lang;

		$lang = array();
		include($dest_file);
		$lang_entry_dst = $lang;

		unset($lang);

		return array_diff(array_keys($lang_entry_src), array_keys($lang_entry_dst));
	}
}
