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

namespace phpbb\storage\adapter;

/**
* Base class for avatar drivers
*/
abstract class adapter implements adapter_interface
{
	/**
	 * @var string storage name
	 */
	protected $storage;

	/**
	 * {@inheritdoc}
	 */
	abstract public function configure($options);

	/**
	 * Set storage
	 *
	 * @param string	storage_name		The storage name.
	 */
	public function set_storage($storage_name)
	{
		$this->storage = $storage_name;
	}

	/**
	 * {@inheritdoc}
	 */
	abstract public function put_contents($path, $content);

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_contents($path);

	/**
	 * {@inheritdoc}
	 */
	abstract public function exists($path);

	/**
	 * {@inheritdoc}
	 */
	abstract public function delete($path);

	/**
	 * {@inheritdoc}
	 */
	abstract public function rename($path_orig, $path_dest);

	/**
	 * {@inheritdoc}
	 */
	abstract public function copy($path_orig, $path_dest);

	/**
	 * {@inheritdoc}
	 */
	abstract public function free_space();
}
