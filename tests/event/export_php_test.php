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

class phpbb_event_export_php_test extends phpbb_test_case
{
	/** @var \phpbb\event\php_exporter */
	protected $exporter;

	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path;
		$this->exporter = new \phpbb\event\php_exporter($phpbb_root_path);
	}

	static public function crawl_php_file_data()
	{
		global $phpbb_root_path;
		$exporter = new \phpbb\event\php_exporter($phpbb_root_path);
		$files = $exporter->get_recursive_file_list($phpbb_root_path);

		$data_provider = array();
		foreach ($files as $file)
		{
			$data_provider[] = array($file);
		}

		return $data_provider;
	}

	/**
	* @dataProvider crawl_php_file_data
	*/
	public function test_crawl_php_file($file)
	{
		$this->assertGreaterThanOrEqual(0, $this->exporter->crawl_php_file($file));
	}
}
