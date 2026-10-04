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

require_once __DIR__ . '/../../phpBB/includes/functions_convert.php';

/**
* Records the errors the convertor would display
*/
class phpbb_functions_import_attachment_test_convertor
{
	/** @var array */
	public $errors = array();

	public function error($error, $line, $file, $skip = false)
	{
		$this->errors[] = array('error' => $error, 'skip' => $skip);
	}
}

class phpbb_functions_import_attachment_test extends phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $storage;

	/** @var phpbb_functions_import_attachment_test_convertor */
	protected $convertor;

	/** @var string */
	protected $source_dir;

	protected function setUp(): void
	{
		global $convert, $user;

		parent::setUp();

		$this->storage = $this->get_storage_mock();

		$this->convertor = new phpbb_functions_import_attachment_test_convertor();

		$convert = new stdClass();
		$convert->p_master = $this->convertor;
		$convert->convertor = array();
		$convert->options = array();

		$user = new phpbb_mock_user();
		$user->lang = array(
			'COULD_NOT_COPY'				=> 'Could not copy file %1$s to %2$s',
			'CONV_ERROR_COULD_NOT_READ'		=> 'Unable to access/read %s',
		);

		$this->source_dir = sys_get_temp_dir() . '/phpbb_convert_' . uniqid() . '/';
		mkdir($this->source_dir);
	}

	protected function tearDown(): void
	{
		foreach (glob($this->source_dir . '{,.}*', GLOB_BRACE) ?: array() as $file)
		{
			if (is_file($file))
			{
				@unlink($file);
			}
		}

		foreach (array('sub', 'CVS') as $dir)
		{
			if (is_dir($this->source_dir . $dir))
			{
				array_map('unlink', glob($this->source_dir . $dir . '/*') ?: array());
				rmdir($this->source_dir . $dir);
			}
		}

		if (is_dir($this->source_dir))
		{
			rmdir($this->source_dir);
		}

		parent::tearDown();
	}

	protected function get_storage_mock()
	{
		$storage = $this->getMockBuilder('\phpbb\storage\storage')
			->disableOriginalConstructor()
			->getMock();
		$storage->method('get_name')->willReturn('attachment');

		return $storage;
	}

	public function test_copy_file_to_storage_writes_through_storage()
	{
		$source = $this->source_dir . 'attach.txt';
		file_put_contents($source, 'attachment contents');

		$this->storage->method('exists')->willReturn(false);
		$this->storage->expects($this->once())
			->method('write')
			->with('attach.txt', $this->isType('resource'));

		$this->assertTrue(phpbb_copy_file_to_storage($this->storage, $source, 'attach.txt'));
		$this->assertSame(array(), $this->convertor->errors);
	}

	public function test_copy_file_to_storage_skips_existing_file()
	{
		$source = $this->source_dir . 'attach.txt';
		file_put_contents($source, 'data');

		$this->storage->method('exists')->willReturn(true);
		$this->storage->expects($this->never())->method('write');

		$this->assertTrue(phpbb_copy_file_to_storage($this->storage, $source, 'attach.txt'));
		$this->assertSame(array(), $this->convertor->errors);
	}

	public static function die_on_failure_data()
	{
		return array(
			// $die_on_failure, expected $skip passed to the convertor error handler
			array(true, false),
			array(false, true),
		);
	}

	/**
	* @dataProvider die_on_failure_data
	*/
	public function test_copy_file_to_storage_missing_source_is_reported($die_on_failure, $expected_skip)
	{
		$this->storage->method('exists')->willReturn(false);
		$this->storage->expects($this->never())->method('write');

		$this->assertFalse(phpbb_copy_file_to_storage($this->storage, $this->source_dir . 'nope.txt', 'nope.txt', $die_on_failure));

		$this->assertCount(1, $this->convertor->errors);
		$this->assertSame('Could not copy file ' . $this->source_dir . 'nope.txt to attachment/nope.txt', $this->convertor->errors[0]['error']);
		$this->assertSame($expected_skip, $this->convertor->errors[0]['skip']);
	}

	/**
	* @dataProvider die_on_failure_data
	*/
	public function test_copy_file_to_storage_write_failure_is_reported($die_on_failure, $expected_skip)
	{
		$source = $this->source_dir . 'attach.txt';
		file_put_contents($source, 'data');

		$this->storage->method('exists')->willReturn(false);
		$this->storage->expects($this->once())
			->method('write')
			->willThrowException(new \phpbb\storage\exception\storage_exception('STORAGE_CANNOT_CREATE_FILE', 'attach.txt'));

		$this->assertFalse(phpbb_copy_file_to_storage($this->storage, $source, 'attach.txt', $die_on_failure));

		$this->assertCount(1, $this->convertor->errors);
		$this->assertStringStartsWith('Could not copy file ' . $source . ' to attachment/attach.txt', $this->convertor->errors[0]['error']);
		// The storage error is appended, translated through $user->lang()
		$this->assertStringEndsWith('<br />STORAGE_CANNOT_CREATE_FILE', $this->convertor->errors[0]['error']);
		$this->assertSame($expected_skip, $this->convertor->errors[0]['skip']);
	}

	public function test_copy_dir_to_storage_writes_the_files_of_the_directory()
	{
		file_put_contents($this->source_dir . 'a.txt', 'a');
		file_put_contents($this->source_dir . 'b.txt', 'b');
		// Skipped like copy_dir() does
		file_put_contents($this->source_dir . '.htaccess', 'deny');
		file_put_contents($this->source_dir . 'index.htm', '');
		mkdir($this->source_dir . 'CVS');
		file_put_contents($this->source_dir . 'CVS/Entries', '');
		// The storage system has no directories, subdirectories are not imported
		mkdir($this->source_dir . 'sub');
		file_put_contents($this->source_dir . 'sub/c.txt', 'c');

		$this->storage->method('exists')->willReturn(false);

		$written = array();
		$this->storage->method('write')->willReturnCallback(function ($path, $resource) use (&$written) {
			$written[] = $path;
		});

		phpbb_copy_dir_to_storage($this->storage, $this->source_dir, 'category');

		sort($written);
		$this->assertEquals(array('category/a.txt', 'category/b.txt'), $written);
		$this->assertSame(array(), $this->convertor->errors);
	}

	public function test_copy_dir_to_storage_unreadable_directory_is_reported()
	{
		$this->storage->expects($this->never())->method('write');

		phpbb_copy_dir_to_storage($this->storage, $this->source_dir . 'missing');

		$this->assertCount(1, $this->convertor->errors);
		$this->assertSame('Unable to access/read ' . $this->source_dir . 'missing/', $this->convertor->errors[0]['error']);
		$this->assertFalse($this->convertor->errors[0]['skip']);
	}

	/**
	* @dataProvider die_on_failure_data
	*/
	public function test_copy_dir_to_storage_passes_die_on_failure_to_file_copies($die_on_failure, $expected_skip)
	{
		file_put_contents($this->source_dir . 'a.txt', 'a');

		$this->storage->method('exists')->willReturn(false);
		$this->storage->method('write')
			->willThrowException(new \phpbb\storage\exception\storage_exception('STORAGE_CANNOT_CREATE_FILE', 'a.txt'));

		phpbb_copy_dir_to_storage($this->storage, $this->source_dir, '', $die_on_failure);

		$this->assertCount(1, $this->convertor->errors);
		$this->assertSame($expected_skip, $this->convertor->errors[0]['skip']);
	}

	public function test_import_check_attachment_uses_storage()
	{
		global $convert, $config, $phpbb_container;

		file_put_contents($this->source_dir . 'file.png', 'image');

		$convert->convertor = array('source_path_absolute' => false, 'upload_path' => '');
		$convert->options = array('forum_path' => rtrim($this->source_dir, '/'));

		$config = array();

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('storage.attachment', $this->storage);

		$this->storage->method('exists')->willReturn(false);
		$this->storage->expects($this->once())
			->method('write')
			->with('file.png', $this->isType('resource'));

		$result = _import_check('upload_path', 'file.png', false);

		$this->assertTrue($result['copied']);
		$this->assertSame('file.png', $result['target']);
		$this->assertSame(array(), $this->convertor->errors);
	}

	public function test_import_check_attachment_copy_failure_is_not_fatal()
	{
		global $convert, $config, $phpbb_container;

		file_put_contents($this->source_dir . 'file.png', 'image');

		$convert->convertor = array('source_path_absolute' => false, 'upload_path' => '');
		$convert->options = array('forum_path' => rtrim($this->source_dir, '/'));

		$config = array();

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('storage.attachment', $this->storage);

		$this->storage->method('exists')->willReturn(false);
		$this->storage->method('write')
			->willThrowException(new \phpbb\storage\exception\storage_exception('STORAGE_CANNOT_CREATE_FILE', 'file.png'));

		$result = _import_check('upload_path', 'file.png', false);

		$this->assertFalse($result['copied']);
		// copy_file() was called with $die_on_failure = false here, the error is reported but skipped
		$this->assertCount(1, $this->convertor->errors);
		$this->assertTrue($this->convertor->errors[0]['skip']);
	}

	public function test_import_check_avatar_uses_storage()
	{
		global $convert, $config, $phpbb_container;

		file_put_contents($this->source_dir . 'avatar.png', 'avatar');

		$convert->convertor = array('source_path_absolute' => false, 'avatar_path' => '');
		$convert->options = array('forum_path' => rtrim($this->source_dir, '/'));

		$config = array();

		$avatar_storage = $this->get_storage_mock();
		$avatar_storage->method('exists')->willReturn(false);
		$avatar_storage->expects($this->once())
			->method('write')
			->with('avatar.png', $this->isType('resource'));

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('storage.avatar', $avatar_storage);

		$result = _import_check('avatar_path', 'avatar.png', false);

		$this->assertTrue($result['copied']);
		$this->assertSame('avatar.png', $result['target']);
	}

	public function test_import_check_ranks_use_copy_file_not_storage()
	{
		global $convert, $config, $phpbb_container, $phpbb_root_path, $phpbb_filesystem;

		file_put_contents($this->source_dir . 'rank.gif', 'gif');

		$convert->convertor = array('source_path_absolute' => false, 'ranks_path' => '');
		$convert->options = array('forum_path' => rtrim($this->source_dir, '/'));

		$target_dir = 'cache/test_ranks_' . uniqid();
		$config = array('ranks_path' => $target_dir);

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();

		// Ranks are not storage-backed: storage must never be touched.
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('storage.attachment', $this->storage);
		$this->storage->expects($this->never())->method('write');

		$result = _import_check('ranks_path', 'rank.gif', false);

		// The file was copied to the local disk via copy_file(), not storage.
		$this->assertTrue($result['copied']);
		$this->assertFileExists($phpbb_root_path . $target_dir . '/rank.gif');

		@unlink($phpbb_root_path . $target_dir . '/rank.gif');
		@rmdir($phpbb_root_path . $target_dir);
	}
}
