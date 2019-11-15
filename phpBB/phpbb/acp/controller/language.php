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

namespace phpbb\acp\controller;

class language
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\language\language_file_helper */
	protected $lang_helper;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config					$config			Config object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\event\dispatcher				$dispatcher		Event dispatcher object
	 * @param \phpbb\acp\helper\controller			$helper			ACP Controller helper object
	 * @param \phpbb\language\language				$language		Language object
	 * @param \phpbb\language\language_file_helper	$lang_helper	Language helper object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 * @param string								$root_path		phpBB root path
	 * @param string								$php_ext		php File extension
	 * @param array									$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\language\language_file_helper $lang_helper,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->lang_helper	= $lang_helper;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main()
	{
		$this->language->add_lang('acp/language');

		if (!function_exists('validate_language_iso_name'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		// Check and set some common vars
		$action = $this->request->is_set_post('update_details') ? 'update_details' : '';
		$action = $this->request->is_set_post('remove_store') ? 'details' : $action;
		$action = empty($action) ? $this->request->variable('action', '') : $action;
		$submit = !empty($action) || $this->request->is_set_post('update') || $this->request->is_set_post('test_connection');

		$lang_id = $this->request->variable('id', 0);

		$form_key = 'acp_lang';
		add_form_key($form_key);

		switch ($action)
		{
			case 'update_details':
				if (!$submit || !check_form_key($form_key))
				{
					return trigger_error($this->language->lang('FORM_INVALID'). $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				if (!$lang_id)
				{
					return trigger_error($this->language->lang('NO_LANG_ID') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . $this->tables['lang'] . '
					WHERE lang_id = ' . (int) $lang_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$sql_ary = [
					'lang_english_name'		=> $this->request->variable('lang_english_name', $row['lang_english_name']),
					'lang_local_name'		=> $this->request->variable('lang_local_name', $row['lang_local_name'], true),
					'lang_author'			=> $this->request->variable('lang_author', $row['lang_author'], true),
				];

				$sql = 'UPDATE ' . $this->tables['lang'] . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE lang_id = ' . (int) $lang_id;
				$this->db->sql_query($sql);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_LANGUAGE_PACK_UPDATED', false, [$sql_ary['lang_english_name']]);

				return $this->helper->message_back('LANGUAGE_DETAILS_UPDATED', 'acp_language_manage');
			break;

			case 'details':
				if (!$lang_id)
				{
					return trigger_error($this->language->lang('NO_LANG_ID') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . $this->tables['lang'] . '
					WHERE lang_id = ' . $lang_id;
				$result = $this->db->sql_query($sql);
				$lang_entries = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$lang_entries)
				{
					return trigger_error($this->language->lang('LANGUAGE_PACK_NOT_EXIST') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				$lang_iso = $lang_entries['lang_iso'];

				$this->template->assign_vars([
					'LANG_AUTHOR'		=> $lang_entries['lang_author'],
					'LANG_LOCAL_NAME'	=> $lang_entries['lang_local_name'],
					'LANG_ENGLISH_NAME'	=> $lang_entries['lang_english_name'],
					'LANG_ISO'			=> $lang_iso,

					'L_MISSING_FILES'			=> $this->language->lang('THOSE_MISSING_LANG_FILES', $lang_entries['lang_local_name']),
					'L_MISSING_VARS_EXPLAIN'	=> $this->language->lang('THOSE_MISSING_LANG_VARIABLES', $lang_entries['lang_local_name']),

					'S_DETAILS'			=> true,

					'U_ACTION'			=> $this->helper->route('acp_language_manage', ['action' => 'details', 'id' => $lang_id]),
					'U_BACK'			=> $this->helper->route('acp_language_manage'),
				]);

				// If current lang is different from the default lang, then highlight missing files and variables
				if ($lang_iso != $this->config['default_lang'])
				{
					try
					{
						$iterator = new \RecursiveIteratorIterator(
							new \phpbb\recursive_dot_prefix_filter_iterator(
								new \RecursiveDirectoryIterator(
									$this->root_path . 'language/' . $this->config['default_lang'] . '/',
									\FilesystemIterator::SKIP_DOTS
								)
							),
							\RecursiveIteratorIterator::LEAVES_ONLY
						);
					}
					catch (\Exception $e)
					{
						return trigger_error($e->getMessage() . $this->helper->adm_back_route('acp_language_manage'));
					}

					/** @var  $file_info */
					foreach ($iterator as $file_info)
					{
						/** @var \RecursiveDirectoryIterator $inner_iterator */
						$inner_iterator = $iterator->getInnerIterator();
						$relative_path = $inner_iterator->getSubPathname();
						$relative_path = str_replace(DIRECTORY_SEPARATOR, '/', $relative_path);

						if (file_exists($this->root_path . 'language/' . $lang_iso . '/' . $relative_path))
						{
							if (substr($relative_path, 0 - strlen($this->php_ext)) === $this->php_ext)
							{
								$missing_vars = $this->compare_language_files($this->config['default_lang'], $lang_iso, $relative_path);

								if (!empty($missing_vars))
								{
									$this->template->assign_block_vars('missing_varfile', ['FILE_NAME' => $relative_path]);

									foreach ($missing_vars as $var)
									{
										$this->template->assign_block_vars('missing_varfile.variable', ['VAR_NAME'=> $var]);
									}
								}
							}
						}
						else
						{
							$this->template->assign_block_vars('missing_files', ['FILE_NAME' => $relative_path]);
						}
					}
				}

				return $this->helper->render('acp_language.html', $this->language->lang('LANGUAGE_PACK_DETAILS'));
			break;

			case 'delete':
				if (!$lang_id)
				{
					return trigger_error($this->language->lang('NO_LANG_ID') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . $this->tables['lang'] . '
					WHERE lang_id = ' . $lang_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row['lang_iso'] == $this->config['default_lang'])
				{
					return trigger_error($this->language->lang('NO_REMOVE_DEFAULT_LANG') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . $this->tables['lang'] . ' WHERE lang_id = ' . (int) $lang_id;
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->tables['users'] . "
						SET user_lang = '" . $this->db->sql_escape($this->config['default_lang']) . "'
						WHERE user_lang = '" . $this->db->sql_escape($row['lang_iso']) . "'";
					$this->db->sql_query($sql);

					// We also need to remove the translated entries for custom profile fields - we want clean tables, don't we?
					$sql = 'DELETE FROM ' . $this->tables['profile_lang'] . ' WHERE lang_id = ' . (int) $lang_id;
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->tables['profile_fields_lang'] . ' WHERE lang_id = ' . (int) $lang_id;
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_LANGUAGE_PACK_DELETED', false, [$row['lang_english_name']]);

					$delete_message = $this->language->lang('LANGUAGE_PACK_DELETED', $row['lang_english_name']);
					$lang_iso = $row['lang_iso'];

					/**
					 * Run code after language deleted
					 *
					 * @event core.acp_language_after_delete
					 * @var string	lang_iso		Language ISO code
					 * @var string	delete_message	Delete message appear to user
					 * @since 3.2.2-RC1
					 */
					$vars = ['lang_iso', 'delete_message'];
					extract($this->dispatcher->trigger_event('core.acp_language_after_delete', compact($vars)));

					unset($lang_iso);

					return $this->helper->message_back($delete_message, 'acp_language_manage');
				}
				else
				{
					confirm_box(false, $this->language->lang('DELETE_LANGUAGE_CONFIRM', $row['lang_english_name']), build_hidden_fields([
						'action'	=> $action,
						'id'		=> $lang_id,
					]));

					return redirect($this->helper->route('acp_language_manage'));
				}
			break;

			case 'install':
				if (!check_link_hash($this->request->variable('hash', ''), 'acp_language'))
				{
					return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				$lang_iso = $this->request->variable('iso', '');
				$lang_iso = basename($lang_iso);

				if (!$lang_iso || !file_exists("{$this->root_path}language/$lang_iso/iso.txt"))
				{
					return trigger_error($this->language->lang('LANGUAGE_PACK_NOT_EXIST') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				$file = file("{$this->root_path}language/$lang_iso/iso.txt");

				$lang_pack = [
					'iso'		=> $lang_iso,
					'name'		=> trim(htmlspecialchars($file[0])),
					'local_name'=> trim(htmlspecialchars($file[1], ENT_COMPAT, 'UTF-8')),
					'author'	=> trim(htmlspecialchars($file[2], ENT_COMPAT, 'UTF-8')),
				];
				unset($file);

				$sql = 'SELECT lang_iso
					FROM ' . $this->tables['lang'] . "
					WHERE lang_iso = '" . $this->db->sql_escape($lang_iso) . "'";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row)
				{
					return trigger_error($this->language->lang('LANGUAGE_PACK_ALREADY_INSTALLED') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				if (!$lang_pack['name'] || !$lang_pack['local_name'])
				{
					return trigger_error($this->language->lang('INVALID_LANGUAGE_PACK') . $this->helper->adm_back_route('acp_language_manage'), E_USER_WARNING);
				}

				// Add language pack
				$sql_ary = [
					'lang_iso'			=> $lang_pack['iso'],
					'lang_dir'			=> $lang_pack['iso'],
					'lang_english_name'	=> $lang_pack['name'],
					'lang_local_name'	=> $lang_pack['local_name'],
					'lang_author'		=> $lang_pack['author'],
				];

				$sql = 'INSERT INTO ' . $this->tables['lang'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				$this->db->sql_query($sql);
				$lang_id = (int) $this->db->sql_nextid();

				// Now let's copy the default language entries for custom profile fields for this new language - makes admin's life easier.
				$sql = 'SELECT lang_id
					FROM ' . $this->tables['lang'] . "
					WHERE lang_iso = '" . $this->db->sql_escape($this->config['default_lang']) . "'";
				$result = $this->db->sql_query($sql);
				$default_lang_id = (int) $this->db->sql_fetchfield('lang_id');
				$this->db->sql_freeresult($result);

				// We want to notify the admin that custom profile fields need to be updated for the new language.
				$notify_cpf_update = false;

				/**
				 * From the MySQL Documentation:
				 * Prior to MySQL 4.0.14, the target table of the INSERT statement
				 * cannot appear in the FROM clause of the SELECT part of the query.
				 * This limitation is lifted in 4.0.14
				 * Due to this we stay on the safe side if we do the insertion "the manual way".
				 */

				$sql = 'SELECT field_id, lang_name, lang_explain, lang_default_value
					FROM ' . $this->tables['profile_lang'] . '
					WHERE lang_id = ' . $default_lang_id;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$row['lang_id'] = $lang_id;

					$sql = 'INSERT INTO ' . $this->tables['profile_lang'] . ' ' . $this->db->sql_build_array('INSERT', $row);
					$this->db->sql_query($sql);

					$notify_cpf_update = true;
				}
				$this->db->sql_freeresult($result);

				$sql = 'SELECT field_id, option_id, field_type, lang_value
					FROM ' . $this->tables['profile_fields_lang'] . '
					WHERE lang_id = ' . $default_lang_id;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$row['lang_id'] = $lang_id;

					$sql = 'INSERT INTO ' . $this->tables['profile_fields_lang'] . ' ' . $this->db->sql_build_array('INSERT', $row);
					$this->db->sql_query($sql);

					$notify_cpf_update = true;
				}
				$this->db->sql_freeresult($result);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_LANGUAGE_PACK_INSTALLED', false, [$lang_pack['name']]);

				$message = $this->language->lang('LANGUAGE_PACK_INSTALLED', $lang_pack['name']);
				$message .= $notify_cpf_update ? '<br /><br />' . $this->language->lang('LANGUAGE_PACK_CPF_UPDATE') : '';

				return $this->helper->message_back($message, 'acp_language_manage');
			break;
		}

		$installed = [];
		$not_installed = [];
		$lang_count = [];

		$sql = 'SELECT user_lang, COUNT(user_lang) AS lang_count
			FROM ' . $this->tables['users'] . '
			GROUP BY user_lang';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$lang_count[$row['user_lang']] = (int) $row['lang_count'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT *
			FROM ' . $this->tables['lang'] . '
			ORDER BY lang_english_name';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$installed[] = $row['lang_iso'];
			$tag_style = $row['lang_iso'] == $this->config['default_lang'] ? '*' : '';

			$this->template->assign_block_vars('lang', [
				'TAG'				=> $tag_style,
				'ISO'				=> $row['lang_iso'],
				'LOCAL_NAME'		=> $row['lang_local_name'],
				'ENGLISH_NAME'		=> $row['lang_english_name'],
				'USED_BY'			=> isset($lang_count[$row['lang_iso']]) ? $lang_count[$row['lang_iso']] : 0,

				'U_DETAILS'			=> $this->helper->route('acp_language_manage', ['id' => $row['lang_id'], 'action' => 'details']),
				'U_DOWNLOAD'		=> $this->helper->route('acp_language_manage', ['id' => $row['lang_id'], 'action' => 'download']),
				'U_DELETE'			=> $this->helper->route('acp_language_manage', ['id' => $row['lang_id'], 'action' => 'delete']),
			]);
		}
		$this->db->sql_freeresult($result);

		$iso = $this->lang_helper->get_available_languages();

		foreach ($iso as $lang_array)
		{
			$lang_iso = $lang_array['iso'];

			if (!in_array($lang_iso, $installed))
			{
				$not_installed[$lang_iso] = $lang_array;
			}
		}

		unset($installed);

		foreach ($not_installed as $iso => $lang_ary)
		{
			$this->template->assign_block_vars('notinst', [
				'ISO'			=> htmlspecialchars($lang_ary['iso']),
				'LOCAL_NAME'	=> htmlspecialchars($lang_ary['local_name'], ENT_COMPAT, 'UTF-8'),
				'NAME'			=> htmlspecialchars($lang_ary['name'], ENT_COMPAT, 'UTF-8'),
				'U_INSTALL'		=> $this->helper->route('acp_language_manage', [
					'action'	=> 'install',
					'iso'		=> urlencode($lang_ary['iso']),
					'hash'		=> generate_link_hash('acp_language'),
				]),
			]);
		}

		unset($not_installed);

		return $this->helper->render('acp_language.html', $this->language->lang('ACP_LANGUAGE_PACKS'));
	}

	/**
	 * Compare two language files.
	 *
	 * @param string	$source_lang	The source (default) language directory
	 * @param string	$dest_lang		The destination language directory
	 * @param string	$file			The language file
	 * @return array					Array with missing language variables
	 */
	function compare_language_files($source_lang, $dest_lang, $file)
	{
		$source_file = $this->root_path . 'language/' . $source_lang . '/' . $file;
		$dest_file = $this->root_path . 'language/' . $dest_lang . '/' . $file;

		if (!file_exists($dest_file))
		{
			return [];
		}

		$lang = [];
		include($source_file);
		$lang_entry_src = $lang;

		$lang = [];
		include($dest_file);
		$lang_entry_dst = $lang;

		unset($lang);

		return array_diff(array_keys($lang_entry_src), array_keys($lang_entry_dst));
	}
}
