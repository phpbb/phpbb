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

	static public function crawl_adm_files_data()
	{
		global $phpbb_root_path;
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path);
		$data_provider = array();

		$styles = array(
			'adm/style/' => 'adm',
			'styles/prosilver/template/' => 'styles',
			'styles/subsilver2/template/' => 'styles',
		);
		foreach ($styles as $path => $filter)
		{
			$files = $exporter->get_recursive_file_list($phpbb_root_path . $path, $path);
			foreach ($files as $file)
			{
				$data_provider[] = array($filter, $path . $file);
			}
		}

		return $data_provider;
	}

	/**
	 * @dataProvider crawl_adm_files_data
	 */
	public function test_crawl_adm_files($filter, $file)
	{
		global $phpbb_root_path;
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path);
		$exporter->crawl_eventsmd('docs/events.md', $filter);
		$events = $exporter->crawl_file_for_events($file);

		$this->assertGreaterThanOrEqual(0, sizeof($events));
		$this->assertTrue($exporter->validate_events_from_file($file, $events));
	}
}
