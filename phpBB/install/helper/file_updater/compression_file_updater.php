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

namespace phpbb\install\helper\file_updater;

use phpbb\install\helper\update_helper;

/**
 * File updater for generating archive with updated files
 */
class compression_file_updater implements file_updater_interface
{
	/**
	 * @var \compress
	 */
	protected $compress;

	/**
	 * @var update_helper
	 */
	protected $update_helper;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param update_helper	$update_helper
	 * @param string		$phpbb_root_path
	 * @param string		$php_ext
	 */
	public function __construct(update_helper $update_helper, $phpbb_root_path, $php_ext)
	{
		$this->compress			= null;
		$this->update_helper	= $update_helper;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
	}

	/**
	 * Set the compression method
	 *
	 * @param string	$method	Compression method's file extension
	 *
	 * @return string	Archive's filename
	 */
	public function init($method)
	{
		$this->update_helper->include_file('includes/functions_compress.' . $this->php_ext);

		$archive_filename = 'update_archive_' . time() . '_' . uniqid();
		$path = $this->phpbb_root_path . 'store/' . $archive_filename . '' . $method;

		if ($method === '.zip')
		{
			$this->compress = new \compress_zip('w', $path);
		}
		else
		{
			$this->compress = new \compress_tar('w', $path, $method);
		}

		return $path;
	}

	/**
	 * Close archive writing process
	 */
	public function close()
	{
		$this->compress->close();
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete_file($path_to_file)
	{
		// We do absolutely nothing here, as this function is called when a file should be
		// removed from the filesystem, but since this is an archive generator, it clearly
		// cannot do that.
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_new_file($path_to_file_to_create, $source, $create_from_content = false)
	{
		if ($create_from_content)
		{
			$this->compress->add_data($source, $path_to_file_to_create);
		}
		else
		{
			$this->compress->add_custom_file($source, $path_to_file_to_create);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_file($path_to_file_to_update, $source, $create_from_content = false)
	{
		// Both functions are identical here
		$this->create_new_file($path_to_file_to_update, $source, $create_from_content);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_method_name()
	{
		return 'compression';
	}
}
