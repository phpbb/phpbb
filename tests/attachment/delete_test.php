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

require_once(dirname(__FILE__) . '/../../phpBB/includes/functions_admin.php');

class phpbb_attachment_delete_test extends \phpbb_database_test_case
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var \phpbb\attachment\resync */
	protected $resync;

	/** @var \phpbb\attachment\delete */
	protected $attachment_delete;

	protected $phpbb_root_path;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/resync.xml');
	}

	public function setUp()
	{
		global $db, $phpbb_root_path;

		parent::setUp();

		$this->config = new \phpbb\config\config(array());
		$this->db = $this->new_dbal();
		$db = $this->db;
		$this->resync = new \phpbb\attachment\resync($this->db);
		$this->filesystem = $this->createMock('\phpbb\filesystem\filesystem', array('remove', 'exists'));
		$this->filesystem->expects($this->any())
			->method('remove')
			->willReturn(false);
		$this->filesystem->expects($this->any())
			->method('exists')
			->willReturn(true);
		$this->phpbb_root_path = $phpbb_root_path;
		$this->dispatcher = new \phpbb_mock_event_dispatcher();
		$this->attachment_delete = new \phpbb\attachment\delete($this->config, $this->db, $this->dispatcher, $this->filesystem, $this->resync, $phpbb_root_path);
	}

	public function data_attachment_delete()
	{
		return array(
			array('attach', '', false, false),
			array('meh', 5, false, 0),
			array('attach', array(5), false, 0),
			array('attach', array(1,2), false, 2),
			array('attach', array(1,2), true, 2),
			array('post', 5, false, 0),
			array('topic', 5, false, 0),
			array('topic', 1, true, 3),
			array('user', 1, false, 0),
		);
	}

	/**
	 * @dataProvider data_attachment_delete
	 */
	public function test_attachment_delete($mode, $ids, $resync, $expected)
	{
		// We need to reset the attachment ID sequence to properly test this
		if ($this->db->get_sql_layer() === 'postgres')
		{
			$sql = 'ALTER SEQUENCE phpbb_attachments_seq RESTART WITH 1';
			$this->db->sql_query($sql);
		}

		$this->assertSame($expected, $this->attachment_delete->delete($mode, $ids, $resync));
	}

	public function data_attachment_unlink()
	{
		return array(
			array(true, true, true),
			array(true, false, false),
			array(true, true, false, true),
		);
	}

	/**
	 * @dataProvider data_attachment_unlink
	 */
	public function test_attachment_delete_success($remove_success, $exists_success, $expected, $throw_exception = false)
	{
		$this->filesystem = $this->createMock('\phpbb\filesystem\filesystem', array('remove', 'exists'));
		if ($throw_exception)
		{
			$this->filesystem->expects($this->any())
				->method('remove')
				->willThrowException(new \phpbb\filesystem\exception\filesystem_exception);;
		}
		else
		{
			$this->filesystem->expects($this->any())
				->method('remove')
				->willReturn($remove_success);
		}

		$this->filesystem->expects($this->any())
			->method('exists')
			->willReturn($exists_success);

		$this->attachment_delete = new \phpbb\attachment\delete($this->config, $this->db, $this->dispatcher, $this->filesystem, $this->resync, $this->phpbb_root_path);
		$this->assertSame($expected, $this->attachment_delete->unlink_attachment('foobar'));
	}
}
