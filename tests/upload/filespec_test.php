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

class phpbb_filespec_test extends phpbb_test_case
{
	const TEST_COUNT = 100;
	const PREFIX = 'phpbb_';
	const UPLOAD_MAX_FILESIZE = 1000;

	private $config;
	private $filesystem;
	public $path;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	protected $mimetype_guesser;

	protected function setUp(): void
	{
		// Global $config required by unique_id
		global $config, $phpbb_root_path, $phpEx;

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
			new \Symfony\Component\Mime\FileinfoMimeTypeGuesser(),
			new \Symfony\Component\Mime\FileBinaryMimeTypeGuesser(),
			new \phpbb\mimetype\content_guesser(),
			new \phpbb\mimetype\extension_guesser(),
		);
		$guessers[2]->set_priority(-2);
		$guessers[3]->set_priority(-2);
		$this->mimetype_guesser = new \phpbb\mimetype\guesser($guessers);
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->phpbb_root_path = $phpbb_root_path;
	}

	private function set_reflection_property($class, $property_name, $value)
	{
		$property = new ReflectionProperty($class, $property_name);
		$property->setAccessible(true);
		$property->setValue($class, $value);
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

		$filespec = new \phpbb\files\filespec($this->filesystem, $this->language, new \bantu\IniGetWrapper\IniGetWrapper, new \FastImageSize\FastImageSize(), $this->phpbb_root_path, $this->mimetype_guesser);
		return $filespec->set_upload_ary(array_merge($upload_ary, $override));
	}

	protected function tearDown(): void
	{
		$this->config = array();

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

	public function test_empty_upload_ary()
	{
		$filespec = new \phpbb\files\filespec($this->filesystem, $this->language, new \bantu\IniGetWrapper\IniGetWrapper, new \FastImageSize\FastImageSize(), $this->phpbb_root_path, $this->mimetype_guesser);
		$this->assertInstanceOf('\phpbb\files\filespec', $filespec->set_upload_ary(array()));
		$this->assertTrue($filespec->init_error());
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
		$filespec->set_upload_namespace($upload);
		$this->set_reflection_property($filespec, 'file_moved', true);
		$this->set_reflection_property($filespec, 'filesize', $filespec->get_filesize($this->path . $filename));

		$this->assertEquals($expected, $filespec->additional_checks());
	}

	public function test_additional_checks_dimensions()
	{
		$upload = new phpbb_mock_fileupload();
		$filespec = $this->get_filespec();
		$filespec->set_upload_namespace($upload);
		$upload->valid_dimensions = false;
		$this->set_reflection_property($filespec, 'file_moved', true);
		$upload->max_filesize = 0;

		$this->assertEquals(false, $filespec->additional_checks());
		$this->assertSame(array('WRONG_SIZE'), $filespec->error);
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
			array('foobar.png'),
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
		$name = $filespec->get('realname');

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
			$name = $filespec->get('realname');

			$this->assertEquals(strlen($name), 32 + strlen(self::PREFIX));
			$this->assertMatchesRegularExpression('#^[A-Za-z0-9]+$#', substr($name, strlen(self::PREFIX)));
			$this->assertFalse(isset($filenames[$name]));
			$filenames[$name] = true;
		}
	}

	public function test_clean_filename_unique_ext()
	{
		$filenames = array();
		for ($tests = 0; $tests < self::TEST_COUNT; $tests++)
		{
			$filespec = $this->get_filespec(array('name' => 'foobar.jpg'));
			$filespec->clean_filename('unique_ext', self::PREFIX);
			$name = $filespec->get('realname');

			$this->assertEquals(strlen($name), 32 + strlen(self::PREFIX) + strlen('.jpg'));
			$this->assertMatchesRegularExpression('#^[A-Za-z0-9]+\.jpg$#', substr($name, strlen(self::PREFIX)));
			$this->assertFalse(isset($filenames[$name]));
			$filenames[$name] = true;
		}
	}

	public function data_clean_filename_avatar()
	{
		return array(
			array(false, false, ''),
			array('foobar.png', 'u5.png', 'avatar', 'u', 5),
			array('foobar.png', 'g9.png', 'avatar', 'g', 9),

		);
	}

	/**
	 * @dataProvider data_clean_filename_avatar
	 */
	public function test_clean_filename_avatar($filename, $expected, $mode, $prefix = '', $user_id = '')
	{
		$filespec = new \phpbb\files\filespec($this->filesystem, $this->language, new \bantu\IniGetWrapper\IniGetWrapper, new \FastImageSize\FastImageSize(), $this->phpbb_root_path, $this->mimetype_guesser);

		if ($filename)
		{
			$upload_ary = array(
				'name' => $filename,
				'type' => '',
				'size' => '',
				'tmp_name' => '',
				'error' => '',
			);
			$filespec->set_upload_ary($upload_ary);
		}
		$filespec->clean_filename($mode, $prefix, $user_id);

		$this->assertSame($expected, $filespec->get('realname'));
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
		$this->assertEquals($expected, \phpbb\files\filespec::get_extension($filename));
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
			array('png_copy', 'png_moved', 'image/png', 'jpg', 'Image file type mismatch: expected extension png but extension jpg given.', true),
		);
	}

	/**
	 * @dataProvider move_file_variables
	 */
	public function test_move_file($tmp_name, $realname, $mime_type, $extension, $error, $expected)
	{
		// Global $phpbb_root_path and $phpEx are required by phpbb_chmod
		global $phpbb_root_path;
		$this->phpbb_root_path = '';

		$upload = new phpbb_mock_fileupload();
		$upload->max_filesize = self::UPLOAD_MAX_FILESIZE;

		$filespec = $this->get_filespec(array(
			'tmp_name' => $this->path . 'copies/' . $tmp_name,
			'name' => $realname,
			'type' => $mime_type,
		));
		$this->set_reflection_property($filespec, 'extension', $extension);
		$filespec->set_upload_namespace($upload);
		$this->set_reflection_property($filespec, 'local', true);

		$this->assertEquals($expected, $filespec->move_file($this->path . 'copies'));
		$this->assertEquals($filespec->get('file_moved'), file_exists($this->path . 'copies/' . $realname));
		if ($error)
		{
			$this->assertEquals($error, $filespec->error[0]);
		}

		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function test_move_file_error()
	{
		$filespec = $this->get_filespec();
		$this->assertFalse($filespec->move_file('foobar'));
		$filespec->error[] = 'foo';
		$this->assertFalse($filespec->move_file('foo'));
	}

	public function data_move_file_copy()
	{
		return array(
			array('gif_copy', true, false, array()),
			array('gif_copy', true, true, array()),
			array('foo_bar', false, false, array('GENERAL_UPLOAD_ERROR')),
			array('foo_bar', false, true, array('GENERAL_UPLOAD_ERROR')),
		);
	}

	/**
	 * @dataProvider data_move_file_copy
	 */
	public function test_move_file_copy($tmp_name, $move_success, $open_basedir_on, $expected_error)
	{
		// Initialise a blank filespec object for use with trivial methods
		$upload_ary = array(
			'name' => 'gif_moved',
			'type' => 'image/gif',
			'size' => '',
			'tmp_name' => $this->path . 'copies/' . $tmp_name,
			'error' => '',
		);

		$php_ini = $this->getMockBuilder('\bantu\IniGetWrapper\IniGetWrapper')
			->getMock();
		$php_ini->expects($this->any())
			->method('getBool')
			->with($this->anything())
			->willReturn($open_basedir_on);
		$upload = new phpbb_mock_fileupload();
		$upload->max_filesize = self::UPLOAD_MAX_FILESIZE;
		$filespec = new \phpbb\files\filespec($this->filesystem, $this->language, $php_ini, new \FastImageSize\FastImagesize,  '', $this->mimetype_guesser);
		$filespec->set_upload_ary($upload_ary);
		$this->set_reflection_property($filespec, 'local', false);
		$this->set_reflection_property($filespec, 'extension', 'gif');
		$filespec->set_upload_namespace($upload);

		$this->assertEquals($move_success, $filespec->move_file($this->path . 'copies'));
		$this->assertEquals($filespec->get('file_moved'), file_exists($this->path . 'copies/gif_moved'));
		$this->assertSame($expected_error, $filespec->error);
	}

	public function data_move_file_imagesize()
	{
		return array(
			array(
				array(
					'width'		=> 2,
					'height'	=> 2,
					'type'		=> IMAGETYPE_GIF,
				),
				array()
			),
			array(
				array(
					'width'		=> 2,
					'height'	=> 2,
					'type'		=> -1,
				),
				array('Image file type -1 for mimetype image/gif not supported.')
			),
			array(
				array(
					'width'		=> 0,
					'height'	=> 0,
					'type'		=> IMAGETYPE_GIF,
				),
				array('The image file you tried to attach is invalid.')
			),
			array(
				false,
				array('It was not possible to determine the dimensions of the image. Please verify that the URL you entered is correct.')
			)
		);
	}

	/**
	 * @dataProvider data_move_file_imagesize
	 */
	public function test_move_file_imagesize($imagesize_return, $expected_error)
	{
		// Initialise a blank filespec object for use with trivial methods
		$upload_ary = array(
			'name' => 'gif_moved',
			'type' => 'image/gif',
			'size' => '',
			'tmp_name' => $this->path . 'copies/gif_copy',
			'error' => '',
		);

		$imagesize = $this->getMockBuilder('\FastImageSize\FastImageSize')
			->getMock();
		$imagesize->expects($this->any())
			->method('getImageSize')
			->with($this->anything())
			->willReturn($imagesize_return);

		$upload = new phpbb_mock_fileupload();
		$upload->max_filesize = self::UPLOAD_MAX_FILESIZE;
		$filespec = new \phpbb\files\filespec($this->filesystem, $this->language, new \bantu\IniGetWrapper\IniGetWrapper, $imagesize,  '', $this->mimetype_guesser);
		$filespec->set_upload_ary($upload_ary);
		$this->set_reflection_property($filespec, 'local', false);
		$this->set_reflection_property($filespec, 'extension', 'gif');
		$filespec->set_upload_namespace($upload);

		$this->assertEquals(true, $filespec->move_file($this->path . 'copies'));
		$this->assertEquals($filespec->get('file_moved'), file_exists($this->path . 'copies/gif_moved'));
		$this->assertSame($expected_error, $filespec->error);
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

		$this->assertSame(trim(utf8_basename(htmlspecialchars($filename, ENT_COMPAT))), $filespec->get('uploadname'));
	}

	public function test_is_uploaded()
	{
		$filespec = new \phpbb\files\filespec($this->filesystem, $this->language, new \bantu\IniGetWrapper\IniGetWrapper, new \FastImageSize\FastImageSize(), $this->phpbb_root_path, null);
		$reflection_filespec = new ReflectionClass($filespec);
		$plupload_property = $reflection_filespec->getProperty('plupload');
		$plupload_property->setAccessible(true);
		$plupload_mock = $this->getMockBuilder('\phpbb\plupload\plupload')
			->disableOriginalConstructor()
			->getMock();
		$plupload_mock->expects($this->any())
			->method('is_active')
			->will($this->returnValue(true));
		$plupload_property->setValue($filespec, $plupload_mock);
		$is_uploaded = $reflection_filespec->getMethod('is_uploaded');

		// Plupload is active and file does not exist
		$this->assertFalse($is_uploaded->invoke($filespec));

		// Plupload is not active and file was not uploaded
		$plupload_property->setValue($filespec, null);
		$this->assertFalse($is_uploaded->invoke($filespec));
	}
}
