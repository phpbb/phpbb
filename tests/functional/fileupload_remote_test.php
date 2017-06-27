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

/**
 * @group functional
 */
class phpbb_functional_fileupload_remote_test extends phpbb_functional_test_case
{
	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \phpbb\files\factory */
	protected $factory;

	/** @var \bantu\IniGetWrapper\IniGetWrapper */
	protected $php_ini;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	public function setUp()
	{
		parent::setUp();
		// Only doing this within the functional framework because we need a
		// URL

		// Global $config required by unique_id
		global $config, $phpbb_root_path, $phpEx;

		if (!is_array($config))
		{
			$config = new \phpbb\config\config(array());
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;
		$config['remote_upload_verify'] = 0;

		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->request = $this->getMock('\phpbb\request\request');
		$this->php_ini = new \bantu\IniGetWrapper\IniGetWrapper;

		$container = new phpbb_mock_container_builder();
		$container->set('files.filespec', new \phpbb\files\filespec($this->filesystem, $this->language, $this->php_ini, new \FastImageSize\FastImageSize(), $this->phpbb_root_path));
		$this->factory = new \phpbb\files\factory($container);
		$container->set('files.factory', $this->factory);
		$container->set('files.types.remote', new \phpbb\files\types\remote($config, $this->factory, $this->language, $this->php_ini, $this->request, $phpbb_root_path));
		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function tearDown()
	{
		global $config, $user;
		$user = null;
		$config = array();
	}

	public function test_invalid_extension()
	{
		/** @var \phpbb\files\upload $upload */
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload->set_error_prefix('')
			->set_allowed_extensions(array('jpg'))
			->set_max_filesize(100);
		$file = $upload->handle_upload('files.types.remote', self::$root_url . 'develop/blank.gif');
		$this->assertEquals('URL_INVALID', $file->error[0]);
	}

	public function test_empty_file()
	{
		/** @var \phpbb\files\upload $upload */
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload->set_error_prefix('')
			->set_allowed_extensions(array('jpg'))
			->set_max_filesize(100);
		$file = $upload->handle_upload('files.types.remote', self::$root_url . 'develop/blank.jpg');
		$this->assertEquals('EMPTY_REMOTE_DATA', $file->error[0]);
	}

	public function test_successful_upload()
	{
		/** @var \phpbb\files\upload $upload */
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload->set_error_prefix('')
			->set_allowed_extensions(array('gif'))
			->set_max_filesize(2000);
		$file = $upload->handle_upload('files.types.remote', self::$root_url . 'develop/test.gif');
		$this->assertEquals(0, count($file->error));
		$this->assertTrue(file_exists($file->get('filename')));
		$this->assertTrue($file->is_uploaded());
	}

	public function test_too_large()
	{
		/** @var \phpbb\files\upload $upload */
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload->set_error_prefix('')
			->set_allowed_extensions(array('gif'))
			->set_max_filesize(100);
		$file = $upload->handle_upload('files.types.remote', self::$root_url . 'develop/test.gif');
		$this->assertEquals(1, count($file->error));
		$this->assertEquals('WRONG_FILESIZE', $file->error[0]);
	}
}
