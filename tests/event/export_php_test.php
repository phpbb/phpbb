<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
