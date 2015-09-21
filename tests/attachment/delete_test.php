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

	/** @var \phpbb\attachment\resync */
	protected $resync;

	/** @var \phpbb\attachment\delete */
	protected $attachment_delete;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/resync.xml');
	}

	public function setUp()
	{
		global $db;

		parent::setUp();

		$this->config = new \phpbb\config\config(array());
		$this->db = $this->new_dbal();
		$db = $this->db;
		$this->resync = new \phpbb\attachment\resync($this->db);
		$this->attachment_delete = new \phpbb\attachment\delete($this->config, $this->db, $this->resync);
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
			array('topic', 1, true, 2),
			array('user', 1, false, 0),
		);
	}

	/**
	 * @dataProvider data_attachment_delete
	 */
	public function test_attachment_delete($mode, $ids, $resync, $expected)
	{
		$this->assertSame($expected, $this->attachment_delete->delete($mode, $ids, $resync));
	}
}
