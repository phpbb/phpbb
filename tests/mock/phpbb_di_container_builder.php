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

class phpbb_mock_phpbb_di_container_builder extends \phpbb\di\container_builder
{
	protected function get_container_filename()
	{
		return $this->get_cache_filename('container.' . $this->php_ext);
	}

	/**
	 * Get the filename under which the dumped extensions autoloader will be stored.
	 *
	 * @return string Path for dumped extensions autoloader
	 */
	protected function get_autoload_filename()
	{
		return $this->get_cache_filename('autoload.' . $this->php_ext);
	}

	protected function get_cache_filename($filename)
	{
		return rtrim($this->cache_dir ?: $this->phpbb_root_path . '../../tmp/', '/\\') . '/' . $filename;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function inject_dbal_driver()
	{
		return;
	}
}
