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

require_once __DIR__ . '/../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../phpBB/includes/utf/utf_tools.php';
require_once __DIR__ . '/../../phpBB/includes/functions_upload.php';
require_once __DIR__ . '/../mock/filespec.php';

class phpbb_fileupload_test extends phpbb_test_case
{
	private $path;

	private $filesystem;

	protected function setUp()
	{
		// Global $config required by unique_id
		// Global $user required by several functions dealing with translations
		// Global $request required by form_upload, local_upload and is_valid
		global $config, $user, $request, $phpbb_filesystem;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;

		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();

		$request = new phpbb_mock_request();

		$this->filesystem = $phpbb_filesystem = new \phpbb\filesystem\filesystem();

		$this->path = __DIR__ . '/fixture/';
	}

	private function gen_valid_filespec()
	{
		$filespec = new phpbb_mock_filespec();
		$filespec->filesize = 1;
		$filespec->extension = 'jpg';
		$filespec->realname = 'valid';
		$filespec->width = 2;
		$filespec->height = 2;

		return $filespec;
	}

	protected function tearDown()
	{
		// Clear globals
		global $config, $user;
		$config = array();
		$user = null;
	}

	public function test_common_checks_invalid_extension()
	{
		$upload = new fileupload($this->filesystem, '', array('png'), 100);
		$file = $this->gen_valid_filespec();
		$upload->common_checks($file);
		$this->assertEquals('DISALLOWED_EXTENSION', $file->error[0]);
	}

	public function test_common_checks_invalid_filename()
	{
		$upload = new fileupload($this->filesystem, '', array('jpg'), 100);
		$file = $this->gen_valid_filespec();
		$file->realname = 'invalid?';
		$upload->common_checks($file);
		$this->assertEquals('INVALID_FILENAME', $file->error[0]);
	}

	public function test_common_checks_too_large()
	{
		$upload = new fileupload($this->filesystem, '', array('jpg'), 100);
		$file = $this->gen_valid_filespec();
		$file->filesize = 1000;
		$upload->common_checks($file);
		$this->assertEquals('WRONG_FILESIZE', $file->error[0]);
	}

	public function test_common_checks_valid_file()
	{
		$upload = new fileupload($this->filesystem, '', array('jpg'), 1000);
		$file = $this->gen_valid_filespec();
		$upload->common_checks($file);
		$this->assertEquals(0, sizeof($file->error));
	}

	public function test_local_upload()
	{
		$upload = new fileupload($this->filesystem, '', array('jpg'), 1000);

		copy($this->path . 'jpg', $this->path . 'jpg.jpg');
		$file = $upload->local_upload($this->path . 'jpg.jpg');
		$this->assertEquals(0, sizeof($file->error));
		unlink($this->path . 'jpg.jpg');
	}

	public function test_move_existent_file()
	{
		$upload = new fileupload($this->filesystem, '', array('jpg'), 1000);

		copy($this->path . 'jpg', $this->path . 'jpg.jpg');
		$file = $upload->local_upload($this->path . 'jpg.jpg');
		$this->assertEquals(0, sizeof($file->error));
		$this->assertFalse($file->move_file('../tests/upload/fixture'));
		$this->assertFalse($file->file_moved);
		$this->assertEquals(1, sizeof($file->error));
	}

	public function test_move_existent_file_overwrite()
	{
		$upload = new fileupload($this->filesystem, '', array('jpg'), 1000);

		copy($this->path . 'jpg', $this->path . 'jpg.jpg');
		copy($this->path . 'jpg', $this->path . 'copies/jpg.jpg');
		$file = $upload->local_upload($this->path . 'jpg.jpg');
		$this->assertEquals(0, sizeof($file->error));
		$file->move_file('../tests/upload/fixture/copies', true);
		$this->assertEquals(0, sizeof($file->error));
		unlink($this->path . 'copies/jpg.jpg');
	}

	public function test_valid_dimensions()
	{
		$upload = new fileupload($this->filesystem, '', false, false, 1, 1, 100, 100);

		$file1 = $this->gen_valid_filespec();
		$file2 = $this->gen_valid_filespec();
		$file2->height = 101;
		$file3 = $this->gen_valid_filespec();
		$file3->width = 0;

		$this->assertTrue($upload->valid_dimensions($file1));
		$this->assertFalse($upload->valid_dimensions($file2));
		$this->assertFalse($upload->valid_dimensions($file3));
	}
}
