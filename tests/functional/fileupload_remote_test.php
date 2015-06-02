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

	public function setUp()
	{
		parent::setUp();
		// Only doing this within the functional framework because we need a
		// URL

		// Global $config required by unique_id
		// Global $user required by fileupload::remote_upload
		global $config, $user;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;

		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();
		$this->filesystem = new \phpbb\filesystem\filesystem();

		$container = new phpbb_mock_container_builder();
		$container->set('files.filespec', new \phpbb\files\filespec($this->filesystem));
		$this->factory = new \phpbb\files\factory($container);
		$container->set('files.factory', $this->factory);
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
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory);
		$upload->set_error_prefix('')
			->set_allowed_extensions(array('jpg'))
			->set_max_filesize(100);
		$file = $upload->remote_upload(self::$root_url . 'develop/blank.gif');
		$this->assertEquals('URL_INVALID', $file->error[0]);
	}

	public function test_empty_file()
	{
		/** @var \phpbb\files\upload $upload */
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory);
		$upload->set_error_prefix('')
			->set_allowed_extensions(array('jpg'))
			->set_max_filesize(100);
		$file = $upload->remote_upload(self::$root_url . 'develop/blank.jpg');
		$this->assertEquals('EMPTY_REMOTE_DATA', $file->error[0]);
	}

	public function test_successful_upload()
	{
		/** @var \phpbb\files\upload $upload */
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory);
		$upload->set_error_prefix('')
			->set_allowed_extensions(array('gif'))
			->set_max_filesize(1000);
		$file = $upload->remote_upload(self::$root_url . 'styles/prosilver/theme/images/forum_read.gif');
		$this->assertEquals(0, sizeof($file->error));
		$this->assertTrue(file_exists($file->filename));
	}

	public function test_too_large()
	{
		/** @var \phpbb\files\upload $upload */
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory);
		$upload->set_error_prefix('')
			->set_allowed_extensions(array('gif'))
			->set_max_filesize(100);
		$file = $upload->remote_upload(self::$root_url . 'styles/prosilver/theme/images/forum_read.gif');
		$this->assertEquals(1, sizeof($file->error));
		$this->assertEquals('WRONG_FILESIZE', $file->error[0]);
	}
}
