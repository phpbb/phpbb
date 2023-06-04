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
 * ACP Module: phpBB update.
 */
class acp_update
{
	/** @var string Page title */
	public $page_title;

	/** @var string Template file name */
	public $tpl_name;

	/** @var string Custom form action */
	public $u_action;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * @param $id
	 * @param $mode
	 * @throws Exception
	 * @return void
	 */
	function main($id, $mode)
	{
		global $config, $language, $request, $template;
		global $phpbb_container, $phpbb_root_path, $phpEx;

		$this->config = $config;
		$this->container = $phpbb_container;
		$this->language = $language;
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$this->tpl_name = 'acp_update';
		$this->page_title = 'ACP_VERSION_CHECK';

		$language->add_lang('install');

		/* @var \phpbb\version_helper $version_helper */
		$version_helper = $this->container->get('version_helper');

		try
		{
			$recheck = $request->variable('versioncheck_force', false);

			$updates_available = $version_helper->get_update_on_branch($recheck);
			$upgrades_available = $version_helper->get_suggested_updates();

			if (!empty($upgrades_available))
			{
				$upgrades_available = array_pop($upgrades_available);
			}
		}
		catch (\RuntimeException $e)
		{
			$template->assign_var('S_VERSIONCHECK_FAIL', true);

			$updates_available = [];
		}

		if ($request->is_set('instant_update'))
		{
			if (empty($updates_available))
			{
				trigger_error($this->language->lang('VERSION_UP_TO_DATE_ACP') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if (confirm_box(true))
			{
				$this->instant_update($updates_available);
			}
			else
			{
				confirm_box(false, 'INSTANT_UPDATE', build_hidden_fields([
					'i'						=> $id,
					'mode'					=> $mode,
					'instant_update'		=> true,
					// Ensure checked versions are up to date
					'versioncheck_force'	=> true,
				]));

				redirect($this->u_action);
			}
		}

		if (!empty($updates_available))
		{
			$template->assign_block_vars('updates_available', $updates_available);
		}

		$template->assign_vars([
			'S_UP_TO_DATE'				=> empty($updates_available),
			'U_ACTION'					=> $this->u_action,
			'U_INSTANT_UPDATE'			=> append_sid($this->u_action . '&amp;instant_update=1'),
			'U_VERSIONCHECK_FORCE'		=> append_sid($this->u_action . '&amp;versioncheck_force=1'),

			'CURRENT_VERSION'			=> $config['version'],

			'UPDATE_INSTRUCTIONS'		=> $this->language->lang('UPDATE_INSTRUCTIONS', "{$this->root_path}install/app.{$this->php_ext}"),
			'S_VERSION_UPGRADEABLE'		=> !empty($upgrades_available),
			'UPGRADE_INSTRUCTIONS'		=> !empty($upgrades_available) ? $this->language->lang('UPGRADE_INSTRUCTIONS', $upgrades_available['current'], $upgrades_available['announcement']) : false,
		]);

		// Incomplete update?
		if ($version_helper->compare($config['version'], PHPBB_VERSION, '<'))
		{
			$template->assign_vars([
				'S_UPDATE_INCOMPLETE'		=> true,
				'FILES_VERSION'				=> PHPBB_VERSION,
				'INCOMPLETE_INSTRUCTIONS'	=> $language->lang('UPDATE_INCOMPLETE_EXPLAIN', "{$this->root_path}install/app.{$this->php_ext}/update"),
			]);
		}
	}

	/**
	 * Perform an instant update.
	 *
	 * @param array		$update		Update data
	 * @return void
	 */
	protected function instant_update(array $update): void
	{
		if ($this->check_english_language() === false)
		{
			$this->instant_update_error($this->language->lang('INSTANT_UPDATE_ERROR_ENGLISH_REQUIRED'));
		}

		/** @var \phpbb\filesystem\filesystem $filesystem */
		$filesystem = $this->container->get('filesystem');

		// Ensure that the store directory is writable
		if ($filesystem->is_writable($this->root_path . 'store') === false)
		{
			$this->instant_update_error(
				$this->language->lang('DIRECTORY_NOT_WRITABLE') . '<br>' .
				$this->language->lang('MAKE_FOLDER_WRITABLE', '/store')
			);
		}

		$temp_dir = "{$this->root_path}store/phpbb-update-{$update['current']}";
		$temp_zip = "{$temp_dir}.zip";

		// Make sure the temporary directories and files do not already exist
		$filesystem->remove([$temp_dir, $temp_zip]);

		try
		{
			// Download the new version's package
			(new \GuzzleHttp\Client)->request('GET', $update['download'], ['sink' => $temp_zip]);
		}
		catch (\GuzzleHttp\Exception\GuzzleException $e)
		{
			$filesystem->remove($temp_zip);

			$this->instant_update_error($this->language->lang('INSTANT_UPDATE_ERROR_DOWNLOAD', $e->getMessage()));
		}

		// Verify the sha256 checksum of the download
		if ($update['checksum'] !== hash_file('sha256', $filesystem->realpath($temp_zip)))
		{
			$filesystem->remove($temp_zip);

			$this->instant_update_error($this->language->lang('INSTANT_UPDATE_ERROR_CHECKSUM_MISMATCH'));
		}

		$zip = new \ZipArchive;

		// Open the downloaded zip archive
		if ($zip->open($temp_zip) !== true)
		{
			$filesystem->remove($temp_zip);

			$this->instant_update_error($this->language->lang('INSTANT_UPDATE_ERROR_ZIP_OPEN', $zip->getStatusString()));
		}

		// Extract the downloaded zip archive
		if ($zip->extractTo($temp_dir) === false)
		{
			$filesystem->remove([$temp_dir, $temp_zip]);

			$this->instant_update_error($this->language->lang('INSTANT_UPDATE_ERROR_ZIP_EXTRACT'));
		}

		$zip->close();

		// Only once all possible errors are dealt with,
		// we update the board's default style and language.
		$this->set_board_defaults();

		// Remove necessary directories and files from the download
		$filesystem->remove([
			$temp_dir . '/phpBB3/.htaccess',
			$temp_dir . '/phpBB3/config.php',
			$temp_dir . '/phpBB3/files',
			$temp_dir . '/phpBB3/images',
			$temp_dir . '/phpBB3/store',
		]);

		// Remove necessary directories from the current installation
		$filesystem->remove([
			$this->root_path . 'cache',
			$this->root_path . 'vendor',
		]);

		// Copy (mirror) the download to the current installation
		$filesystem->mirror($temp_dir . '/phpBB3/', $this->root_path);

		// Remove temporary directories and files
		$filesystem->remove([$temp_dir, $temp_zip]);

		// Redirect to the database update
		redirect(append_sid("{$this->root_path}install/app.{$this->php_ext}/update"));
	}

	/**
	 * Trigger an instant update error.
	 *
	 * @param string	$message	The instant update error message
	 * @return void
	 */
	protected function instant_update_error(string $message): void
	{
		$error_msg = $this->language->lang('INSTANT_UPDATE_ERROR') . '<br><br>';

		trigger_error($error_msg . $message . adm_back_link($this->u_action), E_USER_WARNING);
	}

	/**
	 * Check if the English language pack is installed.
	 *
	 * Perhaps once everything is moved, this check is no longer necessary.
	 * But as it stands, it's too much code to copy and a function can not simply be called to enable it.
	 * So 'quickly' enabling a language pack is out of scope for this updater,
	 * therefore this check is performed and if necessary, the administrator will have to enable it themselves.
	 *
	 * @return bool	Whether the English language pack is installed or not
	 */
	protected function check_english_language(): bool
	{
		$db = $this->container->get('dbal.conn');
		$tables = $this->container->getParameter('tables');

		$sql = 'SELECT 1
			FROM ' . $tables['lang'] . "
			WHERE lang_iso = '" . \phpbb\language\language::FALLBACK_LANGUAGE . "'";
		$result = $db->sql_query_limit($sql, 1);
		$english = (bool) $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $english;
	}

	/**
	 * Set board defaults.
	 *
	 * Enable the Prosilver style.
	 * Set the default style to Prosilver.
	 * Set the default language to English.
	 *
	 * @return void
	 */
	protected function set_board_defaults(): void
	{
		$db = $this->container->get('dbal.conn');
		$cache = $this->container->get('cache.driver');
		$tables = $this->container->getParameter('tables');

		$sql = 'SELECT style_id
			FROM ' . $tables['styles'] . "
			WHERE style_name = 'prosilver'";
		$result = $db->sql_query_limit($sql, 1);
		$prosilver_id = (int) $db->sql_fetchfield('style_id');
		$db->sql_freeresult($result);

		if ($prosilver_id !== (int) $this->config['default_style'])
		{
			$sql = 'UPDATE ' . $tables['styles'] . '
				SET style_active = 1
				WHERE style_id = ' . $prosilver_id;
			$db->sql_query($sql);

			$cache->destroy('sql', $tables['styles']);

			$this->config->set('default_style', $prosilver_id);
		}

		$this->config->set('default_lang', \phpbb\language\language::FALLBACK_LANGUAGE);
	}
}
