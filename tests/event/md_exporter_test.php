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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_event_md_exporter_test extends phpbb_test_case
{
	static public function crawl_eventsmd_data()
	{
		return array(
			array('normal_events.md.test', null, null, array(
				'acp_bbcodes_actions_append' => array(
					'event' => 'acp_bbcodes_actions_append',
					'files' => array(
						'prosilver' => array(),
						'adm' => array('acp_bbcodes.html'),
					),
					'since' => '3.1.0-a3',
					'changed' => array(
						'3.1.0-a4' => '',
					),
					'description' => 'desc1' . "\n",
				),
				'acp_bbcodes_actions_prepend' => array(
					'event' => 'acp_bbcodes_actions_prepend',
					'files' => array(
						'prosilver' => array(),
						'adm' => array('acp_bbcodes.html'),
					),
					'since' => '3.1.0-a5',
					'changed' => array(),
					'description' => 'desc2' . "\n",
				),
				'acp_bbcodes_actions_prepend2' => array(
					'event' => 'acp_bbcodes_actions_prepend2',
					'files' => array(
						'prosilver' => array(),
						'adm' => array('acp_bbcodes.html'),
					),
					'since' => '3.1.0-a4',
					'changed' => array(
						'3.1.0-a5' => 'Moved up',
						'3.1.0-a6' => 'Moved down',
					),
					'description' => 'desc2' . "\n",
				),
			)),
			array('normal_events.md.test', '3.1.0-a5', '3.1.0-a5', array(
				'acp_bbcodes_actions_prepend' => array(
					'event' => 'acp_bbcodes_actions_prepend',
					'files' => array(
						'prosilver' => array(),
						'adm' => array('acp_bbcodes.html'),
					),
					'since' => '3.1.0-a5',
					'changed' => array(),
					'description' => 'desc2' . "\n",
				),
				'acp_bbcodes_actions_prepend2' => array(
					'event' => 'acp_bbcodes_actions_prepend2',
					'files' => array(
						'prosilver' => array(),
						'adm' => array('acp_bbcodes.html'),
					),
					'since' => '3.1.0-a4',
					'changed' => array(
						'3.1.0-a5' => 'Moved up',
						'3.1.0-a6' => 'Moved down',
					),
					'description' => 'desc2' . "\n",
				),
			)),
		);
	}

	/**
	 * @dataProvider crawl_eventsmd_data
	 *
	 * @param string $file
	 * @param string $min_version
	 * @param string $max_version
	 * @param array $events
	 */
	public function test_crawl_eventsmd($file, $min_version, $max_version, $events)
	{
		$exporter = new \phpbb\event\md_exporter(dirname(__FILE__) . '/fixtures/', null, $min_version, $max_version);
		$this->assertSame(sizeof($events), $exporter->crawl_eventsmd($file, 'adm'));
		$this->assertEquals($events, $exporter->get_events());
	}

	static public function crawl_phpbb_eventsmd_data()
	{
		return array(
			array('styles'),
			array('adm'),
		);
	}

	/**
	 * @dataProvider crawl_phpbb_eventsmd_data
	 */
	public function test_crawl_phpbb_eventsmd($filter)
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
