<?php
/**
 *
 * @package testing
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once __DIR__ . '/../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../phpBB/includes/utf/utf_tools.php';
require_once __DIR__ . '/../../phpBB/includes/functions_upload.php';
require_once __DIR__ . '/../mock/fileupload.php';
require_once __DIR__ . '/../mock/request.php';

class phpbb_filespec_test extends phpbb_test_case
{
	const TEST_COUNT = 100;
	const PREFIX = 'phpbb_';
	const MAX_STR_LEN = 50;
	const UPLOAD_MAX_FILESIZE = 1000;

	private $config;
	private $filespec;
	public $path;

	protected function setUp()
	{
		// Global $config required by unique_id
		global $config;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;
		$config['mime_triggers'] = 'body|head|html|img|plaintext|a href|pre|script|table|title';

		$this->config = &$config;
		$this->path = __DIR__ . '/fixture/';
		$this->init_filespec();

		// Create copies of the files for use in testing move_file
		$it = new DirectoryIterator($this->path);
		foreach ($it as $fileinfo)
		{
			if ($fileinfo->isDot())
			{
				continue;
			}

			copy($fileinfo->getPathname(), $this->path . $fileinfo->getFilename() . '_copy');
			if ($fileinfo->getFilename() === 'txt')
			{
				copy($fileinfo->getPathname(), $this->path . $fileinfo->getFilename() . '_copy_2');
			}
		}
	}

	public function additional_checks_variables()
	{
		return array(
			array('gif', true),
			array('jpg', false),
			array('png', true),
			array('tif', false),
			array('txt', true),
		);
	}

	public function check_content_variables()
	{
		return array(
			array('gif', true),
			array('jpg', true),
			array('png', true),
			array('tif', true),
			array('txt', false),
		);
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

	public function get_extension_variables()
	{
		return array(
			array('file.png', 'png'),
			array('file.phpbb.gif', 'gif'),
			array('file..', ''),
			array('.file..jpg.webp', 'webp'),
		);
	}

	private function init_filespec($override = array())
	{
		// Initialise a blank filespec object for use with trivial methods
		$upload_ary = array(
			'name' => '',
			'type' => '',
			'size' => '',
			'tmp_name' => '',
			'error' => '',
		);

		$this->filespec = new filespec(array_merge($upload_ary, $override), null);
	}

	public function is_image_variables()
	{
		return array(
			array('gif', 'image/gif', true),
			array('jpg', 'image/jpg', true),
			array('png', 'image/png', true),
			array('tif', 'image/tif', true),
			array('txt', 'text/plain', false),
		);
	}

	public function move_file_variables()
	{
		return array(
			array('gif_copy', 'gif_moved', 'image/gif', 'gif', false, true),
			array('non_existant', 'still_non_existant', 'text/plain', 'txt', true, false),
			array('txt_copy', 'txt_as_img', 'image/jpg', 'txt', true, true),
			array('txt_copy_2', 'txt_moved', 'text/plain', 'txt', false, true),
			array('jpg_copy', 'jpg_moved', 'image/png', 'jpg', false, true),
			array('png_copy', 'png_moved', 'image/png', 'jpg', true, true),
		);
	}

	protected function tearDown()
	{
		$it = new DirectoryIterator($this->path);
		foreach ($it as $fileinfo)
		{
			if (strlen($fileinfo->getFilename()) > 3)
			{
				unlink($fileinfo->getPathname());
			}
		}

		$this->config = array();
	}

	/**
	 * @dataProvider additional_checks_variables
	 */
	public function test_additional_checks($filename, $expected)
	{
		// Global $user required by filespec::additional_checks
		global $user;
		$user = new phpbb_mock_user();

		$upload = new phpbb_mock_fileupload();
		$this->init_filespec(array('tmp_name', $this->path . $filename));
		$this->filespec->upload = $upload;
		$this->filespec->file_moved = true;
		$this->filespec->filesize = $this->filespec->get_filesize($this->path . $filename);

		$this->assertEquals($expected, $this->filespec->additional_checks());

		$user = null;
	}

	/**
	 * @dataProvider check_content_variables
	 */
	public function test_check_content($filename, $expected)
	{
		$disallowed_content = explode('|', $this->config['mime_triggers']);
		$this->init_filespec(array('tmp_name' => $this->path . $filename));
		$this->assertEquals($expected, $this->filespec->check_content($disallowed_content));
	}

	/**
	 * @dataProvider clean_filename_variables
	 */
	public function test_clean_filename_real($filename)
	{
		
		$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');
		$this->init_filespec(array('name' => $filename));
		$this->filespec->clean_filename('real', self::PREFIX);
		$name = $this->filespec->realname;

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
			$this->init_filespec();
			$this->filespec->clean_filename('unique', self::PREFIX);
			$name = $this->filespec->realname;
			
			$this->assertTrue(strlen($name) === 32 + strlen(self::PREFIX));
			$this->assertRegExp('#^[A-Za-z0-9]+$#', substr($name, strlen(self::PREFIX)));
			$this->assertFalse(isset($filenames[$name]));
			$filenames[$name] = true;
		}
	}

	/**
	 * @dataProvider get_extension_variables
	 */
	public function test_get_extension($filename, $expected)
	{
		$this->assertEquals($expected, $this->filespec->get_extension($filename));
	}

	/**
	 * @dataProvider is_image_variables
	 */
	public function test_is_image($filename, $mimetype, $expected)
	{
		$this->init_filespec(array('tmp_name' => $this->path . $filename, 'type' => $mimetype));
		$this->assertEquals($expected, $this->filespec->is_image());
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

		$this->init_filespec(array(
			'tmp_name' => $this->path . $tmp_name,
			'name' => $realname,
			'type' => $mime_type,
		));
		$this->filespec->extension = $extension;
		$this->filespec->upload = $upload;
		$this->filespec->local = true;

		$this->assertEquals($expected, $this->filespec->move_file($this->path));
		$this->assertEquals($error, (bool) sizeof($this->filespec->error));
		$this->assertEquals($this->filespec->file_moved, file_exists($this->path . $realname));

		$phpEx = '';
	}
}
