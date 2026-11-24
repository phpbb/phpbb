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

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\event\data as event_data;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\log\log_interface;
use phpbb\search\backend\fulltext_sphinx;
use phpbb\search\backend\sphinx\client_interface;
use phpbb\search\backend\sphinx\event_subscriber;
use phpbb\textformatter\s9e\utils as s9e_utils;
use phpbb\user;

class phpbb_search_sphinx_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var config */
	protected $config;

	/** @var auth&\PHPUnit\Framework\MockObject\MockObject */
	protected $auth;

	/** @var client_interface&\PHPUnit\Framework\MockObject\MockObject */
	protected $client;

	/** @var log_interface&\PHPUnit\Framework\MockObject\MockObject */
	protected $log;

	/** @var fulltext_sphinx */
	protected $search;

	/** @var array<int, string> */
	protected $executed_sql = [];

	/** @var array<int, string> */
	protected $selected_sql = [];

	/** @var array{rows: array<int, array<string, mixed>>, meta: array<string, string>} */
	protected $next_select_result = ['rows' => [], 'meta' => ['total_found' => '0']];

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/sphinx_posts.xml');
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		parent::setUp();

		$this->db = $this->new_dbal();
		$this->auth = $this->createMock(auth::class);
		$this->log = $this->createMock(log_interface::class);

		$language = new language(new language_file_loader($phpbb_root_path, $phpEx));
		$language->add_lang(['common', 'search', 'acp/search']);

		$user = new user($language, '\phpbb\datetime');
		$user->data['user_id'] = 2;
		$user->ip = '127.0.0.1';

		$this->config = new config([
			'fulltext_sphinx_id'			=> 'unit12345',
			'fulltext_sphinx_host'			=> 'localhost',
			'fulltext_sphinx_port'			=> '9306',
			'fulltext_sphinx_data_path'		=> '/var/lib/sphinx/',
			'fulltext_sphinx_rt_mem_limit'	=> 128,
			'fulltext_sphinx_indexed'		=> 1,
			'load_search'					=> 1,
			'search_type'					=> fulltext_sphinx::class,
		]);

		$this->executed_sql = [];
		$this->selected_sql = [];
		$this->next_select_result = ['rows' => [], 'meta' => ['total_found' => '0']];

		$this->client = $this->createMock(client_interface::class);
		$this->client->method('is_driver_available')->willReturn(true);
		$this->client->method('index_exists')->willReturn(true);
		$this->client->method('quote_string')->willReturnCallback(static function (string $val): string {
			return "'" . strtr($val, [
				'\\'	=> '\\\\',
				"'"		=> "\\'",
				'"'		=> '\\"',
				"\0"	=> '\\0',
				"\n"	=> '\\n',
				"\r"	=> '\\r',
				"\x1a"	=> '\\Z',
			]) . "'";
		});
		$this->client->method('execute')->willReturnCallback(function (string $sql): int {
			$this->executed_sql[] = $sql;
			return 1;
		});
		$this->client->method('select_with_meta')->willReturnCallback(function (string $sql): array {
			$this->selected_sql[] = $sql;
			return $this->next_select_result;
		});
		$this->client->method('query')->willReturn([
			['Variable_name' => 'ram_bytes', 'Value' => '4096'],
			['Variable_name' => 'disk_bytes', 'Value' => '8192'],
		]);

		$dispatcher = new phpbb_mock_event_dispatcher();
		$text_utils = new s9e_utils();

		$this->search = new fulltext_sphinx(
			$this->auth,
			$this->config,
			$this->db,
			$dispatcher,
			$language,
			$this->log,
			$this->client,
			$text_utils,
			$user
		);
	}

	public function test_split_keywords_modes_and_operators(): void
	{
		$keywords = 'alpha OR beta NOT "exact or not phrase"';
		$this->assertTrue($this->search->split_keywords($keywords, 'all'));
		$this->assertSame('alpha | beta -&quot;exact or not phrase&quot;', $this->search->get_search_query());

		$any_keywords = 'alpha (beta) | gamma';
		$this->assertTrue($this->search->split_keywords($any_keywords, 'any'));
		$this->assertSame('alpha beta gamma', $this->search->get_search_query());

		$empty = '   ';
		$this->assertFalse($this->search->split_keywords($empty, 'all'));
	}

	public function test_keyword_search_posts_and_topics_queries(): void
	{
		$this->auth->method('acl_getf')->willReturnCallback(static function (string $opt): array {
			if ($opt === 'm_approve')
			{
				return [];
			}
			return [1 => true, 3 => true];
		});

		$keywords = 'barsearch -testing';
		$this->search->split_keywords($keywords, 'all');

		$this->next_select_result = [
			'rows'	=> [
				['id' => 100, 'topic_id' => 10],
			],
			'meta'	=> ['total_found' => '1'],
		];

		$id_ary = [];
		$start = 0;
		$count = $this->search->keyword_search(
			'posts',
			'titleonly',
			'all',
			[],
			's',
			'a',
			'7',
			[2],
			'',
			10,
			[2],
			'',
			$id_ary,
			$start,
			10
		);

		$this->assertSame(1, $count);
		$this->assertSame([100], $id_ary);
		$this->assertCount(1, $this->selected_sql);

		$sql = $this->selected_sql[0];
		$this->assertStringContainsString("SELECT id, topic_id FROM index_phpbb_unit12345", $sql);
		$this->assertStringContainsString("MATCH('@title (barsearch -testing)')", $sql);
		$this->assertStringContainsString('topic_first_post = 1', $sql);
		$this->assertStringContainsString('topic_id = 10', $sql);
		$this->assertStringContainsString('poster_id = 2', $sql);
		$this->assertStringContainsString('forum_id IN (1, 3, 0)', $sql);
		$this->assertStringContainsString('post_visibility = 1', $sql);
		$this->assertStringContainsString('ORDER BY post_subject ASC', $sql);
		$this->assertStringContainsString('OPTION field_weights=(title=5, data=1), max_matches=20000', $sql);
	}

	public function test_keyword_search_topics_with_moderator_visibility_and_any_terms(): void
	{
		$this->auth->method('acl_getf')->willReturnCallback(static function (string $opt): array {
			if ($opt === 'm_approve')
			{
				return [1 => true, 5 => true];
			}
			return [1 => true, 5 => true];
		});

		$keywords = 'hello world';
		$this->search->split_keywords($keywords, 'any');

		$this->next_select_result = [
			'rows'	=> [
				['id' => 101, 'topic_id' => 10, 'is_visible' => 1],
			],
			'meta'	=> ['total_found' => '1'],
		];

		$id_ary = [];
		$start = 0;
		$count = $this->search->keyword_search(
			'topics',
			'msgonly',
			'any',
			[],
			't',
			'd',
			'0',
			[],
			'',
			0,
			[],
			'',
			$id_ary,
			$start,
			25
		);

		$this->assertSame(1, $count);
		$this->assertSame([10], $id_ary);
		$sql = $this->selected_sql[0];
		$this->assertStringContainsString('(post_visibility = 1 OR IN(forum_id, 1, 5)) AS is_visible', $sql);
		$this->assertStringContainsString("MATCH('@data (hello | world)')", $sql);
		$this->assertStringContainsString('is_visible = 1', $sql);
		$this->assertStringContainsString('GROUP BY topic_id WITHIN GROUP ORDER BY topic_last_post_time DESC ORDER BY topic_last_post_time DESC', $sql);
		$this->assertStringContainsString('OPTION field_weights=(title=1, data=5)', $sql);
	}

	public function test_standalone_hyphen_and_pure_negative_queries(): void
	{
		$this->auth->method('acl_getf')->willReturn([]);

		// Standalone hyphen "barsearch - testing" must not leave a dangling '-' operator
		$keywords = 'barsearch - testing';
		$this->search->split_keywords($keywords, 'all');
		$this->next_select_result = ['rows' => [], 'meta' => ['total_found' => '0']];
		$id_ary = [];
		$start = 0;
		$this->search->keyword_search('posts', 'all', 'all', [], 't', 'd', '0', [], '', 0, [], '', $id_ary, $start, 10);
		$this->assertStringContainsString("MATCH('barsearch testing')", $this->selected_sql[0]);

		// Pure-negative query "-onlynegated" must short-circuit cleanly without hitting Sphinx
		$this->selected_sql = [];
		$neg_keywords = '-onlynegated';
		$this->search->split_keywords($neg_keywords, 'all');
		$result = $this->search->keyword_search('posts', 'all', 'all', [], 't', 'd', '0', [], '', 0, [], '', $id_ary, $start, 10);
		$this->assertFalse($result);
		$this->assertEmpty($this->selected_sql);
	}

	public function test_author_search_omits_match_clause(): void
	{
		$this->auth->method('acl_getf')->willReturn([]);
		$this->next_select_result = [
			'rows'	=> [['id' => 100, 'topic_id' => 10]],
			'meta'	=> ['total_found' => '1'],
		];

		$id_ary = [];
		$start = 0;
		$count = $this->search->author_search('posts', true, [], 'a', 'a', '0', [], '', 0, [2, 4], '', $id_ary, $start, 10);

		$this->assertSame(1, $count);
		$this->assertSame([100], $id_ary);
		$sql = $this->selected_sql[0];
		$this->assertStringNotContainsString('MATCH(', $sql);
		$this->assertStringContainsString('poster_id IN (2, 4)', $sql);
		$this->assertStringContainsString('topic_first_post = 1', $sql);
		$this->assertStringContainsString('ORDER BY poster_id ASC', $sql);
	}

	public function test_create_index_and_delete_index(): void
	{
		$counter = 0;
		$status = $this->search->create_index($counter);

		// In CLI mode create_index processes one batch (500 >= all 3 fixture rows) and finishes
		$this->assertNull($status);
		$this->assertSame(200, $counter);

		// First statement truncates RT index; second batch-upserts only posts 100 & 101 (post 200 is in forum 2 where enable_indexing=0)
		$this->assertCount(2, $this->executed_sql);
		$this->assertSame('TRUNCATE RTINDEX index_phpbb_unit12345', $this->executed_sql[0]);
		$this->assertStringStartsWith('REPLACE INTO index_phpbb_unit12345', $this->executed_sql[1]);
		$this->assertStringContainsString("(100, 'First & Topic Subject', 'Bold opening post content', 1, 10, 2, 1, 1, 1700000100, 1700000200, 'First & Topic Subject')", $this->executed_sql[1]);
		$this->assertStringContainsString("(101, 'Re: First & Topic Subject', 'Soft deleted reply content', 1, 10, 4, 2, 0, 1700000200, 1700000200, 'Re: First & Topic Subject')", $this->executed_sql[1]);
		$this->assertStringNotContainsString('(200,', $this->executed_sql[1]);

		// Delete index truncates RT index and resets indexed flag
		$this->executed_sql = [];
		$del_counter = 0;
		$this->assertNull($this->search->delete_index($del_counter));
		$this->assertSame(['TRUNCATE RTINDEX index_phpbb_unit12345'], $this->executed_sql);
		$this->assertSame(0, (int) $this->config['fulltext_sphinx_indexed']);
	}

	public function test_index_reply_and_index_remove_empty_guard(): void
	{
		$message = '<t>Updated reply body</t>';
		$subject = 'Re: First &amp; Topic Subject';
		$this->search->index('reply', 101, $message, $subject, 4, 1);

		$this->assertCount(2, $this->executed_sql);
		$this->assertStringStartsWith('REPLACE INTO index_phpbb_unit12345', $this->executed_sql[0]);
		$this->assertSame('UPDATE index_phpbb_unit12345 SET topic_last_post_time = 1700000200 WHERE topic_id = 10', $this->executed_sql[1]);

		// Empty post_ids (as called from mcp_post change poster) must not issue a DELETE
		$this->executed_sql = [];
		$this->search->index_remove([], [2, 4], []);
		$this->assertEmpty($this->executed_sql);

		// Non-empty post_ids deletes from RT index
		$this->search->index_remove([100, 101], [], []);
		$this->assertSame(['DELETE FROM index_phpbb_unit12345 WHERE id IN (100, 101)'], $this->executed_sql);
	}

	public function test_event_subscriber_synchronizes_rt_index(): void
	{
		$subscriber = new event_subscriber($this->config, $this->search);

		// 1. Visibility change syncs topic posts
		$subscriber->on_set_post_visibility_after(new event_data(['topic_id' => 10]));
		$this->assertCount(1, $this->executed_sql);
		$this->assertStringStartsWith('REPLACE INTO index_phpbb_unit12345', $this->executed_sql[0]);

		// 2. Moving topic into non-indexed forum 2 deletes its posts from RT index
		$this->executed_sql = [];
		$subscriber->on_topics_changed(new event_data(['topic_ids' => [20]]));
		$this->assertSame(['DELETE FROM index_phpbb_unit12345 WHERE id IN (200)'], $this->executed_sql);

		// 3. Retaining posts on user_delete updates poster_id to ANONYMOUS
		$this->executed_sql = [];
		$subscriber->on_delete_user_after(new event_data([
			'mode'		=> 'retain',
			'user_ids'	=> [4],
		]));
		$this->assertSame(['UPDATE index_phpbb_unit12345 SET poster_id = ' . (int) ANONYMOUS . ' WHERE poster_id IN (4)'], $this->executed_sql);
	}

	public function test_acp_options_generates_realtime_sphinx_conf(): void
	{
		$acp = $this->search->get_acp_options();
		$this->assertArrayHasKey('tpl', $acp);
		$this->assertArrayHasKey('config', $acp);
		$this->assertSame('Sphinx Fulltext', $this->search->get_name());
		$this->assertStringContainsString('index index_phpbb_unit12345', $acp['tpl']);
		$this->assertStringContainsString('type = rt', $acp['tpl']);
		$this->assertStringContainsString('listen = 127.0.0.1:9306:mysql41', $acp['tpl']);
		$this->assertStringContainsString('workers = threads', $acp['tpl']);
	}
}
