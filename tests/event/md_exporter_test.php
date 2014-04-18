<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_event_md_exporter_test extends phpbb_test_case
{

	static public function crawl_eventsmd_data()
	{
		return array(
			array('styles'),
			array('adm'),
		);
	}

	/**
	* @dataProvider crawl_eventsmd_data
	*/
	public function test_crawl_eventsmd($filter)
	{
		global $phpbb_root_path;
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path);
		$this->assertGreaterThan(0, $exporter->crawl_eventsmd('docs/events.md', $filter));
	}
}
