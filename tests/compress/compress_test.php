<?php
/**
 *
 * @package testing
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once __DIR__ . '/../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../phpBB/includes/functions_admin.php';
require_once __DIR__ . '/../../phpBB/includes/functions_compress.php';

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

		$this->path = __DIR__ . '/fixtures/';

		if (!@extension_loaded('zlib') || !@extension_loaded('bz2'))
		{
			$this->markTestSkipped('PHP needs to be compiled with --with-zlib and --with-bz2 in order to run these tests');
		}
	}

	protected function tearDown()
	{
		foreach (array(__DIR__ . self::EXTRACT_DIR, __DIR__ . self::ARCHIVE_DIR) as $dir)
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
			$path = __DIR__ . self::EXTRACT_DIR . $filename;
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
			array('archive.tar', '.tar'),
			array('archive.tar.gz', '.tar.gz'),
			array('archive.tar.bz2', '.tar.bz2'),
		);
	}

	/**
	 * @dataProvider tar_archive_list
	 */
	public function test_extract_tar($filename, $type)
	{
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
	public function test_compress_tar($filename, $type)
	{
		$tar = __DIR__ . self::ARCHIVE_DIR . $filename;
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
		$zip =  __DIR__ . self::ARCHIVE_DIR . 'archive.zip';
		$compress = new compress_zip('w', $zip);
		$this->archive_files($compress);
		$compress->close();
		$this->assertTrue(file_exists($zip));

		$compress = new compress_zip('r', $zip);
		$compress->extract('tests/compress/' . self::EXTRACT_DIR);
		$this->valid_extraction($this->conflicts);
	}
}
