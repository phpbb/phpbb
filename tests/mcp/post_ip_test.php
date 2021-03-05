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

require_once __DIR__ . '/../../phpBB/includes/mcp/mcp_post.php';

class phpbb_mcp_post_ip_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/post_ip.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = $this->new_dbal();
	}

	public function data_get_num_ips()
	{
		return array(
			array(2, 1),
			array(2, 2),
			array(0, 3),
		);
	}

	/**
	 * @dataProvider data_get_num_ips
	 */
	public function test_get_num_ips($expected, $poster_id)
	{
		$this->assertSame($expected, phpbb_get_num_ips_for_poster($this->db, $poster_id));
	}

	public function data_get_num_posters()
	{
		return array(
			array(2, '127.0.0.1'),
			array(1, '127.0.0.2'),
			array(1, '127.0.0.3'),
			array(0, '127.0.0.4'),
		);
	}

	/**
	 * @dataProvider data_get_num_posters
	 */
	public function test_get_num_posters($expected, $ip)
	{
		$this->assertSame($expected, phpbb_get_num_posters_for_ip($this->db, $ip));
	}
}
