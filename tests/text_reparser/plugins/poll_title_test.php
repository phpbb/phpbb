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
include_once __DIR__ . '/test_row_based_plugin.php';

class phpbb_textreparser_poll_title_test extends phpbb_textreparser_test_row_based_plugin
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/polls.xml');
	}

	protected function get_reparser()
	{
		return new \phpbb\textreparser\plugins\poll_title($this->db, TOPICS_TABLE);
	}

	public function test_filter_like()
	{
		$reparser = $this->get_reparser();
		$reparser->reparse([
			'range-min'        => 100,
			'range-max'        => 101,
			'filter-text-like' => '%foo123%'
		]);

		$expected = [
			[
				'id'   => '100',
				'text' => '<t>Matches LIKE foo123</t>'
			],
			[
				'id'   => '101',
				'text' => 'Does not match LIKE'
			]
		];

		$this->assertEquals($expected, $this->get_rows([100, 101]));
	}
}
