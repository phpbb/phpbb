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

namespace phpbb\update;

use phpbb\filesystem\filesystem_interface;
use phpbb\language\language;
use phpbb\user;
use phpbb\exception\http_exception;
use phpbb\config\config;

class controller
{
	/** @var filesystem_interface Filesystem manager */
	private filesystem_interface $filesystem;

	/** @var get_updates Updater class */
	private get_updates $updater;

	/** @var language Translation handler */
	private language $language;

	/** @var user User object */
	private user $user;

	/** @var config Config object */
	private config $config;

	/** @var string phpBB root path */
	private string $phpbb_root_path;

	/**
	 * Constructor.
	 *
	 * @param filesystem_interface $filesystem
	 * @param get_updates $updater
	 * @param language $language
	 * @param user $user
	 * @param config $config
	 * @param string $phpbb_root_path
	 */
	public function __construct(
		filesystem_interface $filesystem,
		get_updates $updater,
		language $language,
		user $user,
		config $config,
		string $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->language = $language;
		$this->user = $user;
		$this->updater = $updater;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Handle requests.
	 *
	 * @param string $download The download URL.
	 *
	 * @return string[] Unencoded json response.
	 */
	public function handle(string $download): array
	{
		if ($this->user->data['user_type'] != USER_FOUNDER)
		{
			throw new http_exception(403);
		}

		$update_path = $this->phpbb_root_path . 'store/update.zip';
		$status = ['type' => 'resubmit'];
		if (!$this->filesystem->exists($update_path))
		{
			$result = $this->updater->download($download, $update_path);
			if (!$result)
			{
				return $this->error_response('UPDATE_PACKAGE_DOWNLOAD_FAILURE');
			}

			return $status;
		}

		if (!$this->filesystem->exists($update_path . '.sig'))
		{
			$result = $this->updater->download($download . '.sig', $update_path . '.sig');
			if (!$result)
			{
				return $this->error_response('UPDATE_SIGNATURE_DOWNLOAD_FAILURE');
			}

			return $status;
		}

		if (!$this->filesystem->exists($this->phpbb_root_path . 'store/update') || !is_dir($this->phpbb_root_path . 'store/update'))
		{
			$result = $this->updater->validate($update_path, $update_path . '.sig');
			if (!$result)
			{
				return $this->error_response('UPDATE_SIGNATURE_INVALID');
			}

			$result = $this->updater->extract($update_path, $this->phpbb_root_path . 'store/update');
			if (!$result)
			{
				return $this->error_response('UPDATE_PACKAGE_EXTRACT_FAILURE');
			}

			return $status;
		}

		if (!$this->filesystem->exists($this->phpbb_root_path . 'install') || !is_dir($this->phpbb_root_path . 'install'))
		{
			$result = $this->updater->copy($this->phpbb_root_path . 'store/update');
			if (!$result)
			{
				return $this->error_response('UPDATE_FILES_COPY_FAILURE');
			}

			return $status;
		}

		$this->filesystem->remove([
			$this->phpbb_root_path . 'store/update',
			$update_path,
			$update_path . '.sig'
		]);

		$status['type'] = 'message';
		$status['status'] = $this->language->lang('AUTO_UPDATE_SUCCESS');
		$status['msg'] = $this->language->lang('AUTOMATIC_UPDATE_SUCCESS', $this->config['cookie_path'] . 'install/');
		return $status;
	}

	/**
	 * Create error response
	 *
	 * @param string $error_key
	 * @return array Error response
	 */
	protected function error_response(string $error_key): array
	{
		return [
			'type' => 'error',
			'status' => $this->language->lang('ERROR'),
			'error' => $this->language->lang($error_key),
		];
	}
}
