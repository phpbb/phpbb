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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_admin.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_compress.php';

class phpbb_compress_test extends phpbb_test_case
{
	const EXTRACT_DIR = '/extract/';
	const ARCHIVE_DIR = '/archive/';

	private $path;

	protected $filelist = array(
		'1.txt',
		'dir/2.txt',
		'dir/3.txt',
		'dir/subdir/4.txt',
	);

	protected $conflicts = array(
		'1_1.txt',
		'1_2.txt',
		'dir/2_1.txt',
	);

	protected function setUp()
	{
		// Required for compress::add_file
		global $phpbb_root_path;
		$phpbb_root_path = '';

		$this->path = dirname(__FILE__) . '/fixtures/';
	}

	protected function check_extensions($extensions)
	{
		foreach ($extensions as $extension)
		{
			if (!@extension_loaded($extension))
			{
				$this->markTestSkipped("$extension extension is not loaded");
			}
		}
	}

	protected function tearDown()
	{
		foreach (array(dirname(__FILE__) . self::EXTRACT_DIR, dirname(__FILE__) . self::ARCHIVE_DIR) as $dir)
		{
			$this->clear_dir($dir);
		}
	}

	protected function clear_dir($dir)
	{
		$iterator = new DirectoryIterator($dir);
		foreach ($iterator as $fileinfo)
		{
			$name = $fileinfo->getFilename();
			$path = $fileinfo->getPathname();

			if ($name[0] !== '.')
			{
				if ($fileinfo->isDir())
				{
					$this->clear_dir($path);
					rmdir($path);
				}
				else
				{
					unlink($path);
				}
			}
		}
	}

	protected function archive_files($compress)
	{
		$compress->add_file($this->path . '1.txt', $this->path);
		$compress->add_file(
			'tests/compress/fixtures/dir/',
			'tests/compress/fixtures/',
			'',
			// The comma here is not an error, this is a comma-separated list
			'subdir/4.txt,3.txt'
		);
		$compress->add_custom_file($this->path . 'dir/3.txt', 'dir/3.txt');
		$compress->add_data(file_get_contents($this->path . 'dir/subdir/4.txt'), 'dir/subdir/4.txt');

		// Add multiples of the same file to check conflicts are handled
		$compress->add_file($this->path . '1.txt', $this->path);
		$compress->add_file($this->path . '1.txt', $this->path);
		$compress->add_file($this->path . 'dir/2.txt', $this->path);
	}

	protected function valid_extraction($extra = array())
	{
		$filelist = array_merge($this->filelist, $extra);

		foreach ($filelist as $filename)
		{
			$path = dirname(__FILE__) . self::EXTRACT_DIR . $filename;
			$this->assertTrue(file_exists($path));

			// Check the file's contents is correct
			$contents = explode('_', basename($filename, '.txt'));
			$contents = $contents[0];
			$this->assertEquals($contents . "\n", file_get_contents($path));
		}
	}

	public function tar_archive_list()
	{
		return array(
			array('archive.tar', '.tar', array()),
			array('archive.tar.gz', '.tar.gz', array('zlib')),
			array('archive.tar.bz2', '.tar.bz2', array('bz2')),
		);
	}

	/**
	 * @dataProvider tar_archive_list
	 */
	public function test_extract_tar($filename, $type, $extensions)
	{
		$this->check_extensions($extensions);
		$compress = new compress_tar('r', $this->path . $filename);
		$compress->extract('tests/compress/' . self::EXTRACT_DIR);
		$this->valid_extraction();
	}

	public function test_extract_zip()
	{
		$compress = new compress_zip('r', $this->path . 'archive.zip');
		$compress->extract('tests/compress/' . self::EXTRACT_DIR);
		$this->valid_extraction();
	}

	/**
	 * @depends test_extract_tar
	 * @dataProvider tar_archive_list
	 */
	public function test_compress_tar($filename, $type, $extensions)
	{
		$this->check_extensions($extensions);

		$tar = dirname(__FILE__) . self::ARCHIVE_DIR . $filename;
		$compress = new compress_tar('w', $tar);
		$this->archive_files($compress);
		$compress->close();
		$this->assertTrue(file_exists($tar));

		$compress->mode = 'r';
		$compress->open();
		$compress->extract('tests/compress/' . self::EXTRACT_DIR);
		$this->valid_extraction($this->conflicts);
	}

	/**
	 * @depends test_extract_zip
	 */
	public function test_compress_zip()
	{
		$this->check_extensions(array('zlib'));

		$zip =  dirname(__FILE__) . self::ARCHIVE_DIR . 'archive.zip';
		$compress = new compress_zip('w', $zip);
		$this->archive_files($compress);
		$compress->close();
		$this->assertTrue(file_exists($zip));

		$compress = new compress_zip('r', $zip);
		$compress->extract('tests/compress/' . self::EXTRACT_DIR);
		$this->valid_extraction($this->conflicts);
	}
}
