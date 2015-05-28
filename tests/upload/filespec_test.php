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

class phpbb_filespec_test extends phpbb_test_case
{
	const TEST_COUNT = 100;
	const PREFIX = 'phpbb_';
	const MAX_STR_LEN = 50;
	const UPLOAD_MAX_FILESIZE = 1000;

	private $config;
	private $filesystem;
	public $path;

	protected function setUp()
	{
		// Global $config required by unique_id
		// Global $user required by filespec::additional_checks and
		// filespec::move_file
		global $config, $user, $phpbb_filesystem;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;
		// This config value is normally pulled from the database which is set
		// to this value at install time.
		// See: phpBB/install/schemas/schema_data.sql
		$config['mime_triggers'] = 'body|head|html|img|plaintext|a href|pre|script|table|title';

		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();

		$this->config = &$config;
		$this->path = __DIR__ . '/fixture/';

		// Create copies of the files for use in testing move_file
		$iterator = new DirectoryIterator($this->path);
		foreach ($iterator as $fileinfo)
		{
			if ($fileinfo->isDot() || $fileinfo->isDir())
			{
				continue;
			}

			copy($fileinfo->getPathname(), $this->path . 'copies/' . $fileinfo->getFilename() . '_copy');
			if ($fileinfo->getFilename() === 'txt')
			{
				copy($fileinfo->getPathname(), $this->path . 'copies/' . $fileinfo->getFilename() . '_copy_2');
			}
		}

		$guessers = array(
			new \Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser(),
			new \Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser(),
			new \phpbb\mimetype\content_guesser(),
			new \phpbb\mimetype\extension_guesser(),
		);
		$guessers[2]->set_priority(-2);
		$guessers[3]->set_priority(-2);
		$this->mimetype_guesser = new \phpbb\mimetype\guesser($guessers);

		$this->filesystem = $phpbb_filesystem = new \phpbb\filesystem\filesystem();
	}

	private function get_filespec($override = array())
	{
		// Initialise a blank filespec object for use with trivial methods
		$upload_ary = array(
			'name' => '',
			'type' => '',
			'size' => '',
			'tmp_name' => '',
			'error' => '',
		);

		return new filespec(array_merge($upload_ary, $override), null, $this->filesystem, $this->mimetype_guesser);
	}

	protected function tearDown()
	{
		global $user;
		$this->config = array();
		$user = null;

		$iterator = new DirectoryIterator($this->path . 'copies');
		foreach ($iterator as $fileinfo)
		{
			$name = $fileinfo->getFilename();
			if ($name[0] !== '.')
			{
				unlink($fileinfo->getPathname());
			}
		}
	}

	public function additional_checks_variables()
	{
		// False here just indicates the file is too large and fails the
		// filespec::additional_checks method because of it. All other code
		// paths in that method are covered elsewhere.
		return array(
			array('gif', true),
			array('jpg', false),
			array('png', true),
			array('tif', false),
			array('txt', false),
		);
	}

	/**
	 * @dataProvider additional_checks_variables
	 */
	public function test_additional_checks($filename, $expected)
	{
		$upload = new phpbb_mock_fileupload();
		$filespec = $this->get_filespec();
		$filespec->upload = $upload;
		$filespec->file_moved = true;
		$filespec->filesize = $filespec->get_filesize($this->path . $filename);

		$this->assertEquals($expected, $filespec->additional_checks());
	}

	public function check_content_variables()
	{
		// False here indicates that a file is non-binary and contains
		// disallowed content that makes IE report the mimetype incorrectly.
		return array(
			array('gif', true),
			array('jpg', true),
			array('png', true),
			array('tif', true),
			array('txt', false),
		);
	}

	/**
	 * @dataProvider check_content_variables
	 */
	public function test_check_content($filename, $expected)
	{
		$disallowed_content = explode('|', $this->config['mime_triggers']);
		$filespec = $this->get_filespec(array('tmp_name' => $this->path . $filename));
		$this->assertEquals($expected, $filespec->check_content($disallowed_content));
		// All files should pass if $disallowed_content is empty
		$this->assertEquals(true, $filespec->check_content(array()));
	}

	public function clean_filename_variables()
	{
		$chunks = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ\'\\" /:*?<>|[];(){},#+=-_`', 8);
		return array(
			array($chunks[0] . $chunks[7]),
			array($chunks[1] . $chunks[8]),
			array($chunks[2] . $chunks[9]),
			array($chunks[3] . $chunks[4]),
			array($chunks[5] . $chunks[6]),
		);
	}

	/**
	 * @dataProvider clean_filename_variables
	 */
	public function test_clean_filename_real($filename)
	{
		$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');
		$filespec = $this->get_filespec(array('name' => $filename));
		$filespec->clean_filename('real', self::PREFIX);
		$name = $filespec->realname;

		$this->assertEquals(0, preg_match('/%(\w{2})/', $name));
		foreach ($bad_chars as $char)
		{
			$this->assertFalse(strpos($name, $char));
		}
	}

	public function test_clean_filename_unique()
	{
		$filenames = array();
		for ($tests = 0; $tests < self::TEST_COUNT; $tests++)
		{
			$filespec = $this->get_filespec();
			$filespec->clean_filename('unique', self::PREFIX);
			$name = $filespec->realname;

			$this->assertEquals(strlen($name), 32 + strlen(self::PREFIX));
			$this->assertRegExp('#^[A-Za-z0-9]+$#', substr($name, strlen(self::PREFIX)));
			$this->assertFalse(isset($filenames[$name]));
			$filenames[$name] = true;
		}
	}

	public function get_extension_variables()
	{
		return array(
			array('file.png', 'png'),
			array('file.phpbb.gif', 'gif'),
			array('file..', ''),
			array('.file..jpg.webp', 'webp'),
			array('/test.com/file', ''),
			array('/test.com/file.gif', 'gif'),
		);
	}

	/**
	 * @dataProvider get_extension_variables
	 */
	public function test_get_extension($filename, $expected)
	{
		$this->assertEquals($expected, filespec::get_extension($filename));
	}

	public function is_image_variables()
	{
		return array(
			array('gif', 'image/gif', true),
			array('jpg', 'image/jpg', true),
			array('png', 'image/png', true),
			array('tif', 'image/tif', true),
			array('txt', 'text/plain', false),
			array('jpg', 'application/octet-stream', false),
			array('gif', 'application/octetstream', false),
			array('png', 'application/mime', false),
		);
	}

	/**
	 * @dataProvider is_image_variables
	 */
	public function test_is_image($filename, $mimetype, $expected)
	{
		$filespec = $this->get_filespec(array('tmp_name' => $this->path . $filename, 'type' => $mimetype));
		$this->assertEquals($expected, $filespec->is_image());
	}

	public function is_image_get_mimetype()
	{
		return array(
			array('gif', 'image/gif', true),
			array('jpg', 'image/jpg', true),
			array('png', 'image/png', true),
			array('tif', 'image/tif', true),
			array('txt', 'text/plain', false),
			array('jpg', 'application/octet-stream', true),
			array('gif', 'application/octetstream', true),
			array('png', 'application/mime', true),
		);
	}

	/**
	 * @dataProvider is_image_get_mimetype
	 */
	public function test_is_image_get_mimetype($filename, $mimetype, $expected)
	{
		if (!class_exists('finfo') && strtolower(substr(PHP_OS, 0, 3)) === 'win')
		{
			$this->markTestSkipped('Unable to test mimetype guessing without fileinfo support on Windows');
		}

		$filespec = $this->get_filespec(array('tmp_name' => $this->path . $filename, 'type' => $mimetype));
		$filespec->get_mimetype($this->path . $filename);
		$this->assertEquals($expected, $filespec->is_image());
	}

	public function move_file_variables()
	{
		return array(
			array('gif_copy', 'gif_moved', 'image/gif', 'gif', false, true),
			array('non_existant', 'still_non_existant', 'text/plain', 'txt', 'GENERAL_UPLOAD_ERROR', false),
			array('txt_copy', 'txt_as_img', 'image/jpg', 'txt', false, true),
			array('txt_copy_2', 'txt_moved', 'text/plain', 'txt', false, true),
			array('jpg_copy', 'jpg_moved', 'image/png', 'jpg', false, true),
			array('png_copy', 'png_moved', 'image/png', 'jpg', 'IMAGE_FILETYPE_MISMATCH png jpg', true),
		);
	}

	/**
	 * @dataProvider move_file_variables
	 */
	public function test_move_file($tmp_name, $realname, $mime_type, $extension, $error, $expected)
	{
		// Global $phpbb_root_path and $phpEx are required by phpbb_chmod
		global $phpbb_root_path, $phpEx;
		$phpbb_root_path = '';
		$phpEx = 'php';

		$upload = new phpbb_mock_fileupload();
		$upload->max_filesize = self::UPLOAD_MAX_FILESIZE;

		$filespec = $this->get_filespec(array(
			'tmp_name' => $this->path . 'copies/' . $tmp_name,
			'name' => $realname,
			'type' => $mime_type,
		));
		$filespec->extension = $extension;
		$filespec->upload = $upload;
		$filespec->local = true;

		$this->assertEquals($expected, $filespec->move_file($this->path . 'copies'));
		$this->assertEquals($filespec->file_moved, file_exists($this->path . 'copies/' . $realname));
		if ($error)
		{
			$this->assertEquals($error, $filespec->error[0]);
		}

		$phpEx = '';
	}

	/**
	* @dataProvider clean_filename_variables
	*/
	public function test_uploadname($filename)
	{
		$type_cast_helper = new \phpbb\request\type_cast_helper();

		$upload_name = '';
		$type_cast_helper->set_var($upload_name, $filename, 'string', true, true);
		$filespec = $this->get_filespec(array('name'=> $upload_name));

		$this->assertSame(trim(utf8_basename(htmlspecialchars($filename))), $filespec->uploadname);
	}
}
