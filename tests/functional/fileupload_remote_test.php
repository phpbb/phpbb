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

require_once __DIR__ . '/../../phpBB/includes/functions_upload.php';

/**
 * @group functional
 */
class phpbb_functional_fileupload_remote_test extends phpbb_functional_test_case
{
	protected $filesystem;

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
	}

	public function tearDown()
	{
		global $config, $user;
		$user = null;
		$config = array();
	}

	public function test_invalid_extension()
	{
		$upload = new fileupload($this->filesystem, '', array('jpg'), 100);
		$file = $upload->remote_upload(self::$root_url . 'develop/blank.gif');
		$this->assertEquals('URL_INVALID', $file->error[0]);
	}

	public function test_empty_file()
	{
		$upload = new fileupload($this->filesystem, '', array('jpg'), 100);
		$file = $upload->remote_upload(self::$root_url . 'develop/blank.jpg');
		$this->assertEquals('EMPTY_REMOTE_DATA', $file->error[0]);
	}

	public function test_successful_upload()
	{
		$upload = new fileupload($this->filesystem, '', array('gif'), 1000);
		$file = $upload->remote_upload(self::$root_url . 'styles/prosilver/theme/images/forum_read.gif');
		$this->assertEquals(0, sizeof($file->error));
		$this->assertTrue(file_exists($file->filename));
	}

	public function test_too_large()
	{
		$upload = new fileupload($this->filesystem, '', array('gif'), 100);
		$file = $upload->remote_upload(self::$root_url . 'styles/prosilver/theme/images/forum_read.gif');
		$this->assertEquals(1, sizeof($file->error));
		$this->assertEquals('WRONG_FILESIZE', $file->error[0]);
	}
}
