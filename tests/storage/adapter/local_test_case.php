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

use FastImageSize\FastImageSize;
use org\bovigo\vfs\vfsStream;
use phpbb\mimetype\extension_guesser;
use phpbb\mimetype\guesser;
use phpbb\storage\adapter\local;

class phpbb_local_test_case extends phpbb_test_case
{
	protected $adapter;

	protected $path;

	protected $filesystem;

	protected function setUp(): void
	{
		parent::setUp();

		$this->filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_root_path = vfsStream::url('phpbb') . '/';

		vfsStream::setup('phpbb', null, array(
			'test_path' => array(),
		));

		$this->adapter = new local(
			$this->filesystem,
			$phpbb_root_path
		);

		$this->path = $phpbb_root_path . 'test_path/';
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		$this->adapter = null;
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
