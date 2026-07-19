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

namespace phpbb\tests\functional\storage;

use phpbb\config\db_text;
use phpbb\storage\adapter\adapter_interface;
use phpbb\storage\exception\storage_exception;

/**
 * Database storage adapter for functional tests.
 *
 * File contents are kept in the test database so they remain available across
 * the separate HTTP requests and PHP processes used by functional tests.
 */
class db_adapter implements adapter_interface
{
	/** @var db_text */
	protected $config_text;

	/** @var string */
	protected $storage_name;

	/**
	 * @param db_text $config_text
	 */
	public function __construct(db_text $config_text)
	{
		$this->config_text = $config_text;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configure(array $options): void
	{
		$this->storage_name = $options['storage'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function read(string $path)
	{
		$content = $this->config_text->get($this->get_key($path));
		$content = $content !== null ? base64_decode($content, true) : false;

		if ($content === false || ($stream = fopen('php://memory', 'w+b')) === false)
		{
			throw new storage_exception('STORAGE_CANNOT_OPEN_FILE', $path);
		}

		if (fwrite($stream, $content) === false)
		{
			fclose($stream);
			throw new storage_exception('STORAGE_CANNOT_OPEN_FILE', $path);
		}

		rewind($stream);

		return $stream;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write(string $path, $resource): int
	{
		if (($content = stream_get_contents($resource)) === false)
		{
			throw new storage_exception('STORAGE_CANNOT_COPY_RESOURCE');
		}

		$this->config_text->set($this->get_key($path), base64_encode($content));

		return strlen($content);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $path): void
	{
		$this->config_text->delete($this->get_key($path));
	}

	/**
	 * {@inheritdoc}
	 */
	public function free_space(): float
	{
		return (float) PHP_INT_MAX;
	}

	/**
	 * Return the database key for a stored file.
	 *
	 * @param string $path
	 * @return string
	 */
	protected function get_key(string $path): string
	{
		return 'storage.db.' . hash('sha256', $this->storage_name . "\0" . $path);
	}
}
