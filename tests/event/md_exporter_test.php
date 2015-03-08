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

	static public function crawl_template_file_data()
	{
		global $phpbb_root_path;
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path);
		$data_provider = array();

		$styles = array(
			'adm/style/' => 'adm',
			'styles/prosilver/template/' => 'styles',
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
	 * @dataProvider crawl_template_file_data
	 */
	public function test_crawl_template_file($filter, $file)
	{
		global $phpbb_root_path;
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path);
		$exporter->crawl_eventsmd('docs/events.md', $filter);
		$events = $exporter->crawl_file_for_events($file);

		$this->assertGreaterThanOrEqual(0, sizeof($events));
		$this->assertTrue($exporter->validate_events_from_file($file, $events));
	}
}
