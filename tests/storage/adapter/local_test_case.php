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

use org\bovigo\vfs\vfsStream;
use phpbb\storage\adapter\local;

class phpbb_local_test_case extends phpbb_test_case
{
	protected $adapter;

	protected $path;

	protected function setUp(): void
	{
		parent::setUp();

		vfsStream::setup('phpbb', null, array(
			'test_path' => array(),
		));
		$phpbb_root_path = vfsStream::url('phpbb') . '/';

		$this->adapter = new local(
			new \phpbb\filesystem\filesystem(),
			$phpbb_root_path
		);
		$this->adapter->configure(['path' => 'test_path']);

		$this->path = vfsStream::url('phpbb/test_path/');
	}

	/**
	 * Check if a file contains a string
	 *
	 * @param string $file
	 * @param string $content
	 */
	protected function assertFileContains(string $file, string $content): void
	{
		$this->assertEquals($content, file_get_contents($file));
	}
}
