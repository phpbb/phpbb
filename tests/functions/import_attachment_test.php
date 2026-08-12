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

class phpbb_functions_import_attachment_test extends phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $storage;

	/** @var string */
	protected $source_dir;

	protected function setUp(): void
	{
		parent::setUp();

		$this->storage = $this->getMockBuilder('\phpbb\storage\storage')
			->disableOriginalConstructor()
			->getMock();

		$this->source_dir = sys_get_temp_dir() . '/phpbb_convert_' . uniqid() . '/';
		mkdir($this->source_dir);
	}

	protected function tearDown(): void
	{
		foreach (glob($this->source_dir . '*') ?: array() as $file)
		{
			@unlink($file);
		}

		if (is_dir($this->source_dir))
		{
			rmdir($this->source_dir);
		}

		parent::tearDown();
	}

	public function test_copy_file_to_storage_writes_through_storage()
	{
		$source = $this->source_dir . 'attach.txt';
		file_put_contents($source, 'attachment contents');

		$this->storage->method('exists')->willReturn(false);
		$this->storage->expects($this->once())
			->method('write')
			->with('attach.txt', $this->isType('resource'));

		$this->assertTrue(_copy_file_to_storage($this->storage, $source, 'attach.txt'));
	}

	public function test_copy_file_to_storage_skips_existing_file()
	{
		$source = $this->source_dir . 'attach.txt';
		file_put_contents($source, 'data');

		$this->storage->method('exists')->willReturn(true);
		$this->storage->expects($this->never())->method('write');

		$this->assertTrue(_copy_file_to_storage($this->storage, $source, 'attach.txt'));
	}

	public function test_copy_file_to_storage_missing_source()
	{
		$this->storage->method('exists')->willReturn(false);
		$this->storage->expects($this->never())->method('write');

		$this->assertFalse(_copy_file_to_storage($this->storage, $this->source_dir . 'nope.txt', 'nope.txt'));
	}

	public function test_copy_dir_to_storage_writes_all_files_recursively()
	{
		file_put_contents($this->source_dir . 'a.txt', 'a');
		file_put_contents($this->source_dir . 'b.txt', 'b');
		mkdir($this->source_dir . 'sub');
		file_put_contents($this->source_dir . 'sub/c.txt', 'c');

		$this->storage->method('exists')->willReturn(false);

		$written = array();
		$this->storage->method('write')->willReturnCallback(function ($path, $resource) use (&$written) {
			$written[] = $path;
		});

		_copy_dir_to_storage($this->storage, $this->source_dir, 'category');

		sort($written);
		$this->assertEquals(array('category/a.txt', 'category/b.txt', 'category/sub/c.txt'), $written);

		@unlink($this->source_dir . 'sub/c.txt');
		@rmdir($this->source_dir . 'sub');
	}

	public function test_import_check_attachment_uses_storage()
	{
		global $convert, $config, $phpbb_container;

		file_put_contents($this->source_dir . 'file.png', 'image');

		$convert = new stdClass();
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
	}

	public function test_import_check_avatar_uses_storage()
	{
		global $convert, $config, $phpbb_container;

		file_put_contents($this->source_dir . 'avatar.png', 'avatar');

		$convert = new stdClass();
		$convert->convertor = array('source_path_absolute' => false, 'avatar_path' => '');
		$convert->options = array('forum_path' => rtrim($this->source_dir, '/'));

		$config = array();

		$avatar_storage = $this->getMockBuilder('\phpbb\storage\storage')
			->disableOriginalConstructor()
			->getMock();
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

		$convert = new stdClass();
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
