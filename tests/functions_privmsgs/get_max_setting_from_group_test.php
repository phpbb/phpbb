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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_privmsgs.php';

class phpbb_functions_privmsgs_get_max_setting_from_group_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/get_max_setting_from_group.xml');
	}

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
	}

	static public function get_max_setting_from_group_data()
	{
		return array(
			array(1, 0, 'message_limit'),
			array(2, 2, 'message_limit'),
			array(3, 0, 'message_limit'),
			array(4, 0, 'message_limit'),
			array(5, 2, 'message_limit'),
			array(1, 0, 'max_recipients'),
			array(2, 4, 'max_recipients'),
			array(3, 0, 'max_recipients'),
			array(4, 5, 'max_recipients'),
			array(5, 4, 'max_recipients'),
		);
	}

	/**
	* @dataProvider get_max_setting_from_group_data
	*/
	public function test_get_max_setting_from_group($user_id, $expected, $setting)
	{
		$this->assertEquals($expected, phpbb_get_max_setting_from_group($this->db, $user_id, $setting));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_get_max_setting_from_group_throws()
	{
		phpbb_get_max_setting_from_group($this->db, ANONYMOUS, 'not_a_setting');
	}
}
