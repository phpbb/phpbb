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

namespace phpbb\install\module\update_filesystem\task;

use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\helper\update_helper;
use phpbb\install\task_base;

/**
 * Merges user made changes into the files
 */
class diff_files extends task_base
{
	/**
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * @var config
	 */
	protected $installer_config;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var update_helper
	 */
	protected $update_helper;

	/**
	 * Constructor
	 *
	 * @param container_factory		$container
	 * @param config				$config
	 * @param iohandler_interface	$iohandler
	 * @param update_helper			$update_helper
	 * @param string				$phpbb_root_path
	 * @param string				$php_ext
	 */
	public function __construct(container_factory $container, config $config, iohandler_interface $iohandler, update_helper $update_helper, $phpbb_root_path, $php_ext)
	{
		$this->installer_config	= $config;
		$this->iohandler		= $iohandler;
		$this->update_helper	= $update_helper;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;

		$this->cache			= $container->get('cache.driver');

		parent::__construct(false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_requirements()
	{
		$files_to_diff = $this->installer_config->get('update_files', array());
		$files_to_diff = (isset($files_to_diff['update_with_diff'])) ? $files_to_diff['update_with_diff'] : array();

		return $this->installer_config->get('do_update_files', false) && count($files_to_diff) > 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Include diff engine
		$this->update_helper->include_file('includes/diff/diff.' . $this->php_ext);
		$this->update_helper->include_file('includes/diff/engine.' . $this->php_ext);

		// Set up basic vars
		$old_path = $this->update_helper->get_path_to_old_update_files();
		$new_path = $this->update_helper->get_path_to_new_update_files();

		$update_files = $this->installer_config->get('update_files', array());
		$files_to_diff = $update_files['update_with_diff'];

		// Set progress bar
		$this->iohandler->set_task_count(count($files_to_diff), true);
		$this->iohandler->set_progress('UPDATE_FILE_DIFF', 0);
		$progress_count = $this->installer_config->get('file_diff_update_count', 0);

		// Recover progress
		$progress_key = $this->installer_config->get('differ_progress_key', -1);
		$progress_recovered = ($progress_key === -1);
		$merge_conflicts = $this->installer_config->get('merge_conflict_list', array());

		foreach ($files_to_diff as $key => $filename)
		{
			if ($progress_recovered === false)
			{
				if ($progress_key === $key)
				{
					$progress_recovered = true;
				}

				continue;
			}

			// Read in files' content
			$file_contents = array();

			// Handle the special case when user created a file with the filename that is now new in the core
			if (file_exists($old_path . $filename))
			{
				$file_contents[0] = file_get_contents($old_path . $filename);

				$filenames = array(
					$this->phpbb_root_path . $filename,
					$new_path . $filename
				);

				foreach ($filenames as $file_to_diff)
				{
					$file_contents[] = file_get_contents($file_to_diff);

					if ($file_contents[count($file_contents) - 1] === false)
					{
						$this->iohandler->add_error_message(array('FILE_DIFFER_ERROR_FILE_CANNOT_BE_READ', $files_to_diff));
						unset($file_contents);
						throw new user_interaction_required_exception();
					}
				}

				$diff = new \diff3($file_contents[0], $file_contents[1], $file_contents[2]);

				$file_is_merged = $diff->merged_output() === $file_contents[1];

				// Handle conflicts
				if ($diff->get_num_conflicts() !== 0)
				{
					// Check if current file content is merge of new or original file
					$tmp = [
						'file1'		=> $file_contents[1],
						'file2'		=> implode("\n", $diff->merged_new_output()),
					];

					$diff2 = new \diff($tmp['file1'], $tmp['file2']);
					$empty = $diff2->is_empty();

					if (!$empty)
					{
						unset($tmp, $diff2);

						// We check if the user merged with his output
						$tmp = [
							'file1'		=> $file_contents[1],
							'file2'		=> implode("\n", $diff->merged_orig_output()),
						];

						$diff2 = new \diff($tmp['file1'], $tmp['file2']);
						$empty = $diff2->is_empty();
					}

					unset($diff2);

					if (!$empty && in_array($filename, $merge_conflicts))
					{
					$merge_conflicts[] = $filename;
				}
					else
					{
						$file_is_merged = true;
					}
				}

				if (!$file_is_merged)
				{
					// Save merged output
					$this->cache->put(
						'_file_' . md5($filename),
						base64_encode(implode("\n", $diff->merged_output()))
					);
				}
				else
				{
					unset($update_files['update_with_diff'][$key]);
				}

				unset($file_contents);
				unset($diff);
			}
			else
			{
				$new_file_content = file_get_contents($new_path . $filename);

				if ($new_file_content === false)
				{
					$this->iohandler->add_error_message(array('FILE_DIFFER_ERROR_FILE_CANNOT_BE_READ', $files_to_diff));
					unset($new_file_content );
					throw new user_interaction_required_exception();
				}

				// Save new file content to cache
				$this->cache->put(
					'_file_' . md5($filename),
					base64_encode($new_file_content)
				);
				unset($new_file_content);
			}

			$progress_count++;
			$this->iohandler->set_progress('UPDATE_FILE_DIFF', $progress_count);

			if ($this->installer_config->get_time_remaining() <= 0 || $this->installer_config->get_memory_remaining() <= 0)
			{
				// Save differ progress
				$this->installer_config->set('differ_progress_key', $key);
				$this->installer_config->set('merge_conflict_list', $merge_conflicts);
				$this->installer_config->set('file_diff_update_count', $progress_count);

				foreach ($update_files as $type => $files)
				{
					if (empty($files))
					{
						unset($update_files[$type]);
					}
				}

				$this->installer_config->set('update_files', $update_files);

				// Request refresh
				throw new resource_limit_reached_exception();
			}
		}

		$this->iohandler->finish_progress('ALL_FILES_DIFFED');
		$this->installer_config->set('merge_conflict_list', $merge_conflicts);
		$this->installer_config->set('differ_progress_key', -1);

		foreach ($update_files as $type => $files)
		{
			if (empty($files))
			{
				unset($update_files[$type]);
			}
		}

		$this->installer_config->set('update_files', $update_files);
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return '';
	}
}
