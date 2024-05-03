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

class phpbb_textreparser_post_text_test extends phpbb_textreparser_test_row_based_plugin
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/posts.xml');
	}

	protected function get_reparser()
	{
		return new \phpbb\textreparser\plugins\post_text($this->db, POSTS_TABLE);
	}

	public function data_reparse_url(): array
	{
		return [
			[ // Reparse the same
				'<r><URL url="https://www.example.com">https://www.example.com</URL> and some more text test included</r>',
				'<r><URL url="https://www.example.com">https://www.example.com</URL> and some more text test included</r>',
			],
			[ // Reparse without magic URL, shouldn't change
				'https://www.example.com and some more text test included',
				'<t>https://www.example.com and some more text test included</t>',
			],
			[ // Reparse new format without magic URL, shouldn't change
				'<t>https://www.example.com and some more text test included</t>',
				'<t>https://www.example.com and some more text test included</t>',
			],
			[ // Reparse with magic URL, should update to text formatter format
				'Foo is <!-- m --><a class="postlink" href="https://symfony.com/doc/current/service_container.html">https://symfony.com/doc/current/service_container.html</a><!-- m --> good',
				'<r>Foo is <URL url="https://symfony.com/doc/current/service_container.html">https://symfony.com/doc/current/service_container.html</URL> good</r>',
			],
			[ // Reparse new format with magic URL, shouldn't change
				'<r>Foo is <URL url="https://symfony.com/doc/current/service_container.html">https://symfony.com/doc/current/service_container.html</URL> good</r>',
				'<r>Foo is <URL url="https://symfony.com/doc/current/service_container.html">https://symfony.com/doc/current/service_container.html</URL> good</r>',
			]
		];
	}

	/**
	 * @dataProvider data_reparse_url
	 */
	public function test_reparse_url(string $input_text, string $expected_text)
	{
		foreach ([true, false] as $enable_magic_url)
		{
			$record = [
				'enable_bbcode'			=> true,
				'enable_smilies'		=> true,
				'enable_magic_url'		=> $enable_magic_url,
				'post_text'					=> $input_text,
				'bbcode_uid'			=> '',
			];

			$sql = 'INSERT INTO ' . POSTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $record);
			$this->db->sql_query($sql);

			$record['id'] = $this->db->sql_last_inserted_id();
			$record['text'] = $record['post_text'];

			// Call reparse_record via reflection
			$reparser = $this->get_reparser();
			$reparser_reflection = new \ReflectionMethod($reparser, 'reparse_record');
			$reparser_reflection->setAccessible(true);
			$reparser_reflection->invoke($reparser, $record);

			// Retrieve reparsed post text and compare with expectec
			$sql = 'SELECT post_id, post_text FROM ' . POSTS_TABLE . ' WHERE post_id = ' . (int) $record['id'];
			$result = $this->db->sql_query($sql);
			$actual_text = $this->db->sql_fetchfield('post_text');
			$this->db->sql_freeresult($result);

			$this->assertSame($expected_text, $actual_text);
		}
	}
}
