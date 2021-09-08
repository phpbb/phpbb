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

require_once __DIR__ . '/../mock/filespec.php';

class phpbb_fileupload_test extends phpbb_test_case
{
	private $path;

	/** @var \phpbb\filesystem\filesystem */
	private $filesystem;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

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

	protected $mimetype_guesser;

	protected function setUp(): void
	{
		// Global $config required by unique_id
		global $config, $phpbb_root_path, $phpEx;

		if (!is_array($config))
		{
			$config = new \phpbb\config\config(array());
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;

		$this->request = $this->createMock('\phpbb\request\request');
		$this->php_ini = new \bantu\IniGetWrapper\IniGetWrapper;

		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$guessers = array(
			new \Symfony\Component\Mime\FileinfoMimeTypeGuesser(),
			new \Symfony\Component\Mime\FileBinaryMimeTypeGuesser(),
			new \phpbb\mimetype\content_guesser(),
			new \phpbb\mimetype\extension_guesser(),
		);
		$guessers[2]->set_priority(-2);
		$guessers[3]->set_priority(-2);
		$this->mimetype_guesser = new \phpbb\mimetype\guesser($guessers);

		$this->container = new phpbb_mock_container_builder();
		$this->container->set('files.filespec', new \phpbb\files\filespec(
			$this->filesystem,
			$this->language,
			$this->php_ini,
			new \FastImageSize\FastImageSize(),
			$phpbb_root_path,
			new \phpbb\mimetype\guesser(array(
				'mimetype.extension_guesser' => new \phpbb\mimetype\extension_guesser(),
			))));
		$this->factory = new \phpbb\files\factory($this->container);
		$plupload = new \phpbb\plupload\plupload($phpbb_root_path, $config, $this->request, new \phpbb\user($this->language, '\phpbb\datetime'), $this->php_ini, $this->mimetype_guesser);
		$this->container->set('files.types.form', new \phpbb\files\types\form(
			$this->factory,
			$this->language,
			$this->php_ini,
			$plupload,
			$this->request
		));
		$this->container->set('files.types.local', new \phpbb\files\types\local(
			$this->factory,
			$this->language,
			$this->php_ini,
			$this->request
		));

		$this->path = __DIR__ . '/fixture/';
		$this->phpbb_root_path = $phpbb_root_path;
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

	protected function tearDown(): void
	{
		// Clear globals
		global $config, $user;
		$config = array();
		$user = null;
	}

	public function test_common_checks_invalid_extension()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('png'))
			->set_max_filesize(100);
		$file = $this->gen_valid_filespec();
		$upload->common_checks($file);
		$this->assertEquals('DISALLOWED_EXTENSION', $file->error[0]);
	}

	public function test_common_checks_disallowed_content()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('jpg'))
			->set_max_filesize(1000);
		$file = new \phpbb\files\filespec($this->filesystem, $this->language, $this->php_ini, new \FastImageSize\FastImageSize(), $this->phpbb_root_path);
		$file->set_upload_ary(array(
				'size'	=> 50,
				'tmp_name'	=> __DIR__ . '/fixture/disallowed',
				'name'		=> 'disallowed.jpg',
				'type'		=> 'image/jpg'
			))
			->set_upload_namespace($upload);
		file_put_contents(__DIR__ . '/fixture/disallowed', '<body>' . file_get_contents(__DIR__ . '/fixture/jpg'));
		$upload->common_checks($file);
		$this->assertEquals('DISALLOWED_CONTENT', $file->error[0]);
		unlink(__DIR__ . '/fixture/disallowed');
	}

	public function test_common_checks_invalid_filename()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('jpg'))
			->set_max_filesize(100);
		$file = $this->gen_valid_filespec();
		$file->realname = 'invalid?';
		$upload->common_checks($file);
		$this->assertEquals('INVALID_FILENAME', $file->error[0]);
	}

	public function test_common_checks_too_large()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('jpg'))
			->set_max_filesize(100);
		$file = $this->gen_valid_filespec();
		$file->filesize = 1000;
		$upload->common_checks($file);
		$this->assertEquals('WRONG_FILESIZE', $file->error[0]);
	}

	public function test_common_checks_valid_file()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('jpg'))
			->set_max_filesize(1000);
		$file = $this->gen_valid_filespec();
		$upload->common_checks($file);
		$this->assertEquals(0, count($file->error));
	}

	public function test_local_upload()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('jpg'))
			->set_max_filesize(1000);

		copy($this->path . 'jpg', $this->path . 'jpg.jpg');
		// Upload file data should be set to prevent "Undefined array key" PHP 8 warning
		$filedata = [
			'size'		=> 519,
			'realname'	=> $this->path . 'jpg.jpg',
			'type'		=> false,
		];
		$file = $upload->handle_upload('files.types.local', $this->path . 'jpg.jpg', $filedata);
		$this->assertEquals(0, count($file->error));
		$this->assertFalse($file->additional_checks());
		$this->assertTrue($file->move_file('../tests/upload/fixture/copies', true));
		$file->remove();
	}

	public function test_move_existent_file()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('jpg'))
			->set_max_filesize(1000);

		copy($this->path . 'jpg', $this->path . 'jpg.jpg');
		// Upload file data should be set to prevent "Undefined array key" PHP 8 warning
		$filedata = [
			'size'		=> 519,
			'realname'	=> $this->path . 'jpg.jpg',
			'type'		=> false,
		];
		$file = $upload->handle_upload('files.types.local', $this->path . 'jpg.jpg', $filedata);
		$this->assertEquals(0, count($file->error));
		$this->assertFalse($file->move_file('../tests/upload/fixture'));
		$this->assertFalse($file->get('file_moved'));
		$this->assertEquals(1, count($file->error));
	}

	public function test_move_existent_file_overwrite()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('jpg'))
			->set_max_filesize(1000);

		copy($this->path . 'jpg', $this->path . 'jpg.jpg');
		copy($this->path . 'jpg', $this->path . 'copies/jpg.jpg');
		// Upload file data should be set to prevent "Undefined array key" PHP 8 warning
		$filedata = [
			'size'		=> 519,
			'realname'	=> $this->path . 'jpg.jpg',
			'type'		=> false,
		];
		$file = $upload->handle_upload('files.types.local', $this->path . 'jpg.jpg', $filedata);
		$this->assertEquals(0, count($file->error));
		$file->move_file('../tests/upload/fixture/copies', true);
		$this->assertEquals(0, count($file->error));
		unlink($this->path . 'copies/jpg.jpg');
	}

	public function test_valid_dimensions()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(false)
			->set_max_filesize(false)
			->set_allowed_dimensions(1, 1, 100, 100);

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
