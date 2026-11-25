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

namespace phpbb\search\backend;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher_interface;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\search\backend\sphinx\client;
use phpbb\search\backend\sphinx\client_interface;
use phpbb\search\backend\sphinx\exception as sphinx_exception;
use phpbb\user;

/**
* Fulltext search based on the sphinx search daemon
*/
class fulltext_sphinx implements search_backend_interface
{
	protected const SPHINX_MAX_MATCHES = 20000;
	protected const INDEX_BATCH_SIZE = 500;

	/**
	 * Associative array holding index stats
	 * @var array
	 */
	protected $stats = array();

	/**
	 * Holds the words entered by user, obtained by splitting the entered query on whitespace
	 * @var array
	 */
	protected $split_words = array();

	/**
	 * Holds unique sphinx id
	 * @var string
	 */
	protected $id;

	/**
	 * Stores the name of the sphinx Real-Time index
	 * @var string
	 */
	protected $indexes;

	/**
	 * Sphinx search client object
	 * @var client_interface
	 */
	protected $client;

	/**
	 * Auth object
	 * @var auth
	 */
	protected $auth;

	/**
	 * Config object
	 * @var config
	 */
	protected $config;

	/**
	 * Database connection
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * phpBB event dispatcher object
	 * @var dispatcher_interface
	 */
	protected $phpbb_dispatcher;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var log
	 */
	protected $log;

	/**
	 * User object
	 * @var user
	 */
	protected $user;

	/**
	 * Stores the generated content of the sphinx config file
	 * @var string
	 */
	protected $config_file_data = '';

	/**
	 * Contains tidied search query.
	 * Operators are prefixed in search query and common words excluded
	 * @var string
	 */
	protected $search_query = '';

	/**
	 * Current match mode ('all' or 'any') set by split_keywords()
	 * @var string
	 */
	protected $match_terms = 'all';

	/**
	 * Constructor
	 * Creates a new \phpbb\search\backend\fulltext_postgres, which is used as a search backend
	 *
	 * @param auth $auth Auth object
	 * @param config $config Config object
	 * @param driver_interface $db Database object
	 * @param dispatcher_interface $phpbb_dispatcher Event dispatcher object
	 * @param language $language
	 * @param log $log
	 * @param client_interface $client
	 * @param user $user User object
	 */
	public function __construct(auth $auth, config $config, driver_interface $db, dispatcher_interface $phpbb_dispatcher, language $language, log $log, client_interface $client, user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;
		$this->log = $log;
		$this->client = $client;
		$this->user = $user;

		$this->id = (string) $this->config['fulltext_sphinx_id'];
		$this->indexes = 'index_phpbb_' . $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string
	{
		return 'Sphinx Fulltext';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool
	{
		return $this->client->is_driver_available();
	}

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		if (!$this->is_available())
		{
			return $this->language->lang('FULLTEXT_SPHINX_DRIVER_NOT_AVAILABLE');
		}

		$this->get_index_name();

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_search_query(): string
	{
		return $this->search_query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_common_words(): array
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_word_length()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function split_keywords(string &$keywords, string $terms): bool
	{
		// Keep quotes and new lines
		$keywords = str_replace(['&quot;', "\n"], ['"', ' '], trim($keywords));

		if ($terms == 'all')
		{
			// Replaces verbal operators OR and NOT with special characters | and -, unless appearing within quotation marks
			$match		= ['#\sor\s(?=([^"]*"[^"]*")*[^"]*$)#i', '#\snot\s(?=([^"]*"[^"]*")*[^"]*$)#i'];
			$replace	= [' | ', ' -'];

			$keywords = preg_replace($match, $replace, $keywords);
			$this->match_terms = 'all';
		}
		else
		{
			$match = ['\\', '(',')', '|', '!', '@', '~', '/', '^', '$', '=', '&amp;', '&lt;', '&gt;', '"'];

			$keywords = str_replace($match, ' ', $keywords);
			$this->match_terms = 'any';
		}

		if (strlen($keywords) > 0)
		{
			$this->search_query = str_replace('"', '&quot;', $keywords);
			return true;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function keyword_search(string $type, string $fields, string $terms, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page)
	{
		// No keywords? No posts.
		if (!strlen($this->search_query) && !count($author_ary))
		{
			return false;
		}

		$id_ary = array();
		$where = array();
		$options = array();
		$group_by = '';

		// Sorting

		if ($type == 'topics')
		{
			$group_by = 'topic_id';
			switch ($sort_key)
			{
				case 'a':
					$order_by = 'poster_id ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
				break;

				case 'f':
					$order_by = 'forum_id ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
				break;

				case 'i':

				case 's':
					$order_by = 'post_subject ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
				break;

				case 't':

				default:
					$order_by = 'topic_last_post_time ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
				break;
			}
		}
		else
		{
			switch ($sort_key)
			{
				case 'a':
					$order_by = 'poster_id ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
				break;

				case 'f':
					$order_by = 'forum_id ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
				break;

				case 'i':

				case 's':
					$order_by = 'post_subject ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
				break;

				case 't':

				default:
					$order_by = 'post_time ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
				break;
			}
		}

		// Most narrow filters first
		if ($topic_id)
		{
			$where[] = 'topic_id = ' . $topic_id;
		}

		$search_query_prefix = '';

		switch ($fields)
		{
			case 'titleonly':
				// Only search the title
				if ($terms == 'all')
				{
					$search_query_prefix = '@title ';
				}
				// Weight for the title
				$options['field_weights'] = '(title=5, data=1)';
				// 1 is first_post, 0 is not first post
				$where[] = 'topic_first_post = 1';
			break;

			case 'msgonly':
				// Only search the body
				if ($terms == 'all')
				{
					$search_query_prefix = '@data ';
				}
				// Weight for the body
				$options['field_weights'] = '(title=1, data=5)';
			break;

			case 'firstpost':
				// More relative weight for the title, also search the body
				$options['field_weights'] = '(title=5, data=1)';
				// 1 is first_post, 0 is not first post
				$where[] = 'topic_first_post = 1';
			break;

			default:
				// More relative weight for the title, also search the body
				$options['field_weights'] = '(title=5, data=1)';
			break;
		}

		if (strlen($this->search_query))
		{
			$cleaned_query = $this->sphinx_clean_search_string(str_replace('&quot;', '"', $this->search_query));
			if ($terms != 'all' || $this->match_terms == 'any')
			{
				$parts = preg_split('/\s+/', $cleaned_query, -1, PREG_SPLIT_NO_EMPTY);
				$cleaned_query = $parts ? implode(' | ', $parts) : '';
			}
			else
			{
				$without_negated = (string) preg_replace('/(?:^|\s)-(?:"[^"]+"|\S+)/u', '', $cleaned_query);
				if (trim($without_negated) === '')
				{
					return false;
				}
			}

			if ($cleaned_query === '')
			{
				return false;
			}

			$where[] = 'MATCH(' . $this->client->quote_string($search_query_prefix . $cleaned_query) . ')';
		}

		if (count($author_ary))
		{
			$where[] = 'poster_id IN (' . implode(', ', array_map('intval', $author_ary)) . ')';
		}

		// As this is not simply possible at the moment, we limit the result to approved posts.
		// This will make it impossible for moderators to search unapproved and softdeleted posts,
		// but at least it will also cause the same for normal users.
		$where[] = 'post_visibility = ' . (int) ITEM_APPROVED;

		if (count($ex_fid_ary))
		{
			// All forums that a user is allowed to access
			$fid_ary = array_unique(array_intersect(array_keys($this->auth->acl_getf('f_read', true)), array_keys($this->auth->acl_getf('f_search', true))));
			// All forums that the user wants to and can search in
			$search_forums = array_diff($fid_ary, $ex_fid_ary);

			if (count($search_forums))
			{
				$where[] = 'forum_id IN (' . implode(', ', array_map('intval', $search_forums)) . ')';
			}
		}

		$start = max(0, (int) $start);
		$per_page = max(1, (int) $per_page);
		$options['max_matches'] = (string) max(self::SPHINX_MAX_MATCHES, $start + $per_page);

		/**
		* Allow modifying the Sphinx search options
		*
		* @event core.search_sphinx_keywords_modify_options
		* @var	string	type				Searching type ('posts', 'topics')
		* @var	string	fields				Searching fields ('titleonly', 'msgonly', 'firstpost', 'all')
		* @var	string	terms				Searching terms ('all', 'any')
		* @var	int		sort_days			Time, in days, of the oldest possible post to list
		* @var	string	sort_key			The sort type used from the possible sort types
		* @var	int		topic_id			Limit the search to this topic_id only
		* @var	array	ex_fid_ary			Which forums not to search on
		* @var	string	post_visibility		Post visibility data
		* @var	array	author_ary			Array of user_id containing the users to filter the results to
		* @var	string	author_name			The username to search on
		* @var	object	client				The SphinxQL client object
		* @var	array	where				Conditions for the SphinxQL WHERE clause
		* @var	string	group_by			Column for GROUP BY
		* @var	string	order_by			Clause for ORDER BY
		* @var	array	options				Key-value map for the SphinxQL OPTION clause
		* @since 3.1.7-RC1
		* @changed 4.0.0-a3 Replace SphinxClient with SphinxQL client and query clauses
		*/
		$client = $this->client;
		$vars = array(
			'type',
			'fields',
			'terms',
			'sort_days',
			'sort_key',
			'topic_id',
			'ex_fid_ary',
			'post_visibility',
			'author_ary',
			'author_name',
			'client',
			'where',
			'group_by',
			'order_by',
			'options',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_sphinx_keywords_modify_options', compact($vars)));
		$this->client = $client;
		unset($client);

		try
		{
			$sql = $this->build_search_sql($where, $group_by, $order_by, $start, $per_page, $options);
			$result = $this->client->select_with_meta($sql);
			$result_count = (int) ($result['meta']['total_found'] ?? 0);

			if ($result_count && $start >= $result_count)
			{
				$start = (int) (floor(($result_count - 1) / $per_page) * $per_page);
				$options['max_matches'] = (string) max(self::SPHINX_MAX_MATCHES, $start + $per_page);
				$sql = $this->build_search_sql($where, $group_by, $order_by, $start, $per_page, $options);
				$result = $this->client->select_with_meta($sql);
			}
		}
		catch (sphinx_exception $e)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_SPHINX_ERROR', false, array($e->getMessage()));
			if ($this->auth->acl_get('a_'))
			{
				trigger_error($this->language->lang('SPHINX_SEARCH_FAILED', $e->getMessage()));
			}
			else
			{
				trigger_error($this->language->lang('SPHINX_SEARCH_FAILED_LOG'));
			}

			return false;
		}

		$id_ary = array();
		if (!empty($result['rows']))
		{
			if ($type == 'posts')
			{
				foreach ($result['rows'] as $row)
				{
					$id_ary[] = (int) $row['id'];
				}
			}
			else
			{
				foreach ($result['rows'] as $row)
				{
					$id_ary[] = (int) $row['topic_id'];
				}
			}
		}
		else
		{
			return false;
		}

		$id_ary = array_slice($id_ary, 0, (int) $per_page);

		return $result_count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function author_search(string $type, bool $firstpost_only, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page)
	{
		$this->search_query = '';

		$fields = ($firstpost_only) ? 'firstpost' : 'all';
		$terms = 'all';
		return $this->keyword_search($type, $fields, $terms, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $post_visibility, $topic_id, $author_ary, $author_name, $id_ary, $start, $per_page);
	}

	/**
	 * {@inheritdoc}
	 */
	public function supports_phrase_search(): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function index(string $mode, int $post_id, string &$message, string &$subject, int $poster_id, int $forum_id)
	{
		/**
		* Event to modify method arguments before the Sphinx search index is updated
		*
		* @event core.search_sphinx_index_before
		* @var string	mode				Contains the post mode: edit, post, reply, quote
		* @var int		post_id				The id of the post which is modified/created
		* @var string	message				New or updated post content
		* @var string	subject				New or updated post subject
		* @var int		poster_id			Post author's user id
		* @var int		forum_id			The id of the forum in which the post is located
		* @since 3.2.3-RC1
		*/
		$vars = array(
			'mode',
			'post_id',
			'message',
			'subject',
			'poster_id',
			'forum_id',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_sphinx_index_before', compact($vars)));

		$index_name = $this->get_index_name();
		if ($post_id <= 0 || !$this->client->index_exists($index_name))
		{
			return;
		}

		$result = $this->db->sql_query($this->build_posts_select_sql('p.post_id = ' . (int) $post_id));
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return;
		}

		if ($forum_id === 0)
		{
			$indexing_enabled = true;
		}
		else if ($forum_id === (int) $row['forum_id'])
		{
			$indexing_enabled = (bool) $row['enable_indexing'];
		}
		else
		{
			$sql = 'SELECT enable_indexing
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $forum_id;
			$forum_result = $this->db->sql_query($sql);
			$indexing_enabled = (bool) $this->db->sql_fetchfield('enable_indexing');
			$this->db->sql_freeresult($forum_result);
		}

		if (!$indexing_enabled)
		{
			return;
		}

		$row['post_subject'] = $subject;
		$row['post_text'] = $message;
		$row['poster_id'] = $poster_id;
		$row['forum_id'] = $forum_id;

		try
		{
			$this->upsert_documents(array($row));

			if ($mode != 'post' && $mode != 'edit' && (int) $row['topic_id'] > 0)
			{
				$this->client->execute(sprintf(
					'UPDATE %s SET topic_last_post_time = %d WHERE topic_id = %d',
					$index_name,
					(int) $row['topic_last_post_time'],
					(int) $row['topic_id']
				));
			}
		}
		catch (sphinx_exception $e)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_SPHINX_ERROR', false, array($e->getMessage()));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function index_remove(array $post_ids, array $author_ids, array $forum_ids): void
	{
		$post_ids = array_values(array_unique(array_filter(array_map('intval', $post_ids), static function (int $id): bool {
			return $id > 0;
		})));

		$index_name = $this->get_index_name();
		if (empty($post_ids) || !$this->client->index_exists($index_name))
		{
			return;
		}

		try
		{
			foreach (array_chunk($post_ids, self::INDEX_BATCH_SIZE) as $chunk)
			{
				$this->client->execute(sprintf(
					'DELETE FROM %s WHERE id IN (%s)',
					$index_name,
					implode(', ', $chunk)
				));
			}
		}
		catch (sphinx_exception $e)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_SPHINX_ERROR', false, array($e->getMessage()));
		}
	}

	/**
	 * Synchronizes all posts belonging to the given topic IDs in the Real-Time index.
	 *
	 * @param array<int, int> $topic_ids
	 * @return void
	 */
	public function sync_topics_by_ids(array $topic_ids): void
	{
		$topic_ids = array_values(array_unique(array_filter(array_map('intval', $topic_ids), static function (int $id): bool {
			return $id > 0;
		})));

		if (!empty($topic_ids))
		{
			$this->sync_posts_where($this->db->sql_in_set('p.topic_id', $topic_ids));
		}
	}

	/**
	 * Synchronizes specific post IDs in the Real-Time index.
	 *
	 * @param array<int, int> $post_ids
	 * @return void
	 */
	public function sync_posts_by_ids(array $post_ids): void
	{
		$post_ids = array_values(array_unique(array_filter(array_map('intval', $post_ids), static function (int $id): bool {
			return $id > 0;
		})));

		if (!empty($post_ids))
		{
			$this->sync_posts_where($this->db->sql_in_set('p.post_id', $post_ids), $post_ids);
		}
	}

	/**
	 * Reassigns poster_id in the Real-Time index for posts authored by the specified user IDs.
	 *
	 * @param array<int, int> $user_ids
	 * @param int $new_poster_id
	 * @return void
	 */
	public function update_poster_id_for_users(array $user_ids, int $new_poster_id): void
	{
		$user_ids = array_values(array_unique(array_filter(array_map('intval', $user_ids), static function (int $id): bool {
			return $id > 0;
		})));

		$index_name = $this->get_index_name();
		if (empty($user_ids) || !$this->client->index_exists($index_name))
		{
			return;
		}

		try
		{
			$this->client->execute(sprintf(
				'UPDATE %s SET poster_id = %d WHERE poster_id IN (%s)',
				$index_name,
				$new_poster_id,
				implode(', ', $user_ids)
			));
		}
		catch (sphinx_exception $e)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_SPHINX_ERROR', false, array($e->getMessage()));
		}
	}

	/**
	 * Nothing needs to be destroyed
	 */
	public function tidy(): void
	{
		$this->config->set('search_last_gc', time(), false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_index(int &$post_counter = 0): array|null
	{
		if ($error = $this->init())
		{
			throw new sphinx_exception($error);
		}

		$index_name = $this->get_index_name();
		if (!$this->client->index_exists($index_name))
		{
			throw new sphinx_exception($this->language->lang('FULLTEXT_SPHINX_INDEX_NOT_FOUND', $index_name));
		}

		if ($post_counter === 0)
		{
			$this->client->execute('TRUNCATE RTINDEX ' . $index_name);
		}

		$sql = 'SELECT MAX(post_id) AS max_post_id
			FROM ' . POSTS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_post_id = (int) $this->db->sql_fetchfield('max_post_id');
		$this->db->sql_freeresult($result);

		$starttime = microtime(true);
		$row_count = 0;

		while (still_on_time() && $post_counter < $max_post_id)
		{
			$sql = $this->build_posts_select_sql('p.post_id > ' . (int) $post_counter, 'p.post_id ASC');
			$result = $this->db->sql_query_limit($sql, self::INDEX_BATCH_SIZE);

			$batch_rows = array();
			$fetched = 0;
			while ($row = $this->db->sql_fetchrow($result))
			{
				$fetched++;
				$post_counter = (int) $row['post_id'];
				if (((int) $row['forum_id'] === 0) || (bool) $row['enable_indexing'])
				{
					$batch_rows[] = $row;
				}
			}
			$this->db->sql_freeresult($result);

			if ($fetched === 0)
			{
				$post_counter = $max_post_id;
				break;
			}

			$this->upsert_documents($batch_rows);
			$row_count += $fetched;

			if (PHP_SAPI === 'cli')
			{
				break;
			}
		}

		if ($post_counter < $max_post_id)
		{
			$totaltime = max(0.0001, microtime(true) - $starttime);

			return array(
				'row_count'			=> $row_count,
				'post_counter'		=> $post_counter,
				'max_post_id'		=> $max_post_id,
				'rows_per_second'	=> $row_count / $totaltime,
			);
		}

		$this->config->set('fulltext_sphinx_indexed', 1);
		$this->stats = array();

		return null;
	}

	/**
	 * {@inheritdoc}
	*/
	public function delete_index(int|null &$post_counter = null): array|null
	{
		$index_name = $this->get_index_name();
		if ($this->client->index_exists($index_name))
		{
			$this->client->execute('TRUNCATE RTINDEX ' . $index_name);
		}

		$this->config->set('fulltext_sphinx_indexed', 0);
		$this->stats = array();

		return null;
	}

	/**
	 * {@inheritdoc}
	*/
	public function index_created($allow_new_files = true): bool
	{
		if (empty($this->stats))
		{
			$this->get_stats();
		}

		if (!$this->stats['index_exists'])
		{
			return false;
		}

		return ((int) $this->stats['total_posts'] > 0) || (bool) $this->config['fulltext_sphinx_indexed'];
	}

	/**
	 * {@inheritdoc}
	*/
	public function index_stats()
	{
		if (empty($this->stats))
		{
			$this->get_stats();
		}

		return array(
			$this->language->lang('FULLTEXT_MYSQL_TOTAL_POSTS')			=> ($this->index_created()) ? $this->stats['total_posts'] : 0,
		);
	}

	/**
	 * Computes the stats and store them in the $this->stats associative array
	 */
	protected function get_stats()
	{
		$this->stats = array(
			'index_exists'	=> false,
			'total_posts'	=> 0,
		);

		$index_name = $this->get_index_name();
		if (!$this->client->index_exists($index_name))
		{
			return;
		}

		$this->stats['index_exists'] = true;

		try
		{
			$result = $this->client->select_with_meta('SELECT id FROM ' . $index_name . ' LIMIT 0, 1');
			$this->stats['total_posts'] = (int) ($result['meta']['total_found'] ?? 0);
		}
		catch (sphinx_exception $e)
		{
			$this->stats['total_posts'] = 0;
		}
	}

	/**
	 * Cleans search query passed into Sphinx search engine, as follows:
	 * 1. Hyphenated words are replaced with keyword search for either the exact phrase with spaces
	 *    or as a single word without spaces eg search for "know-it-all" becomes ("know it all"|"knowitall*")
	 * 2. Words with apostrophes are contracted eg "it's" becomes "its"
	 * 3. <, >, " and & are decoded from HTML entities.
	 * 4. Following special characters used as search operators in Sphinx are preserved when used with correct syntax:
	 *    (a) quorum matching: "the world is a wonderful place"/3
	 *        Finds 3 of the words within the phrase. Number must be between 1 and 9.
	 *    (b) proximity search: "hello world"~10
	 *        Finds hello and world within 10 words of each other. Number can be between 1 and 99.
	 *    (c) strict word order: aaa << bbb << ccc
	 *        Finds "aaa" only where it appears before "bbb" and only where "bbb" appears before "ccc".
	 *    (d) exact match operator: if lemmatizer or stemming enabled,
	 *        search will find exact match only and ignore other grammatical forms of the same word stem.
	 *        eg. raining =cats and =dogs
	 *            will not return "raining cat and dog"
	 *        eg. ="search this exact phrase"
	 *            will not return "searched this exact phrase", "searching these exact phrases".
	 * 5. Special characters /, ~, << and = not complying with the correct syntax
	 *    and other reserved operators are escaped and searched literally.
	 *    Special characters not explicitly listed in charset_table or blend_chars in sphinx.conf
	 *    will not be indexed and keywords containing them will be ignored by Sphinx.
	 *    By default, only $, %, & and @ characters are indexed and searchable.
	 *    String transformation is in backend only and not visible to the end user
	 *    nor reflected in the results page URL or keyword highlighting.
	 *
	 * @param string	$search_string
	 * @return string
	 */
	protected function sphinx_clean_search_string($search_string)
	{
		$from = ['@', '^', '$', '!', '&lt;', '&gt;', '&quot;', '&amp;', '\''];
		$to = ['\@', '\^', '\$', '\!', '<', '>', '"', '&', ''];

		$search_string = str_replace($from, $to, $search_string);

		$search_string = preg_replace('/(?:^|\s)+-+(?=\s|$)/u', ' ', $search_string);

		$search_string = strrev($search_string);
		$search_string = preg_replace(['#\/(?!"[^"]+")#', '#~(?!"[^"]+")#'], ['/\\', '~\\'], $search_string);
		$search_string = strrev($search_string);

		$match = ['#(/|\\\\/)(?![1-9](\s|$))#', '#(~|\\\\~)(?!\d{1,2}(\s|$))#', '#((?:\p{L}|\p{N})+)-((?:\p{L}|\p{N})+)(?:-((?:\p{L}|\p{N})+))?(?:-((?:\p{L}|\p{N})+))?#i', '#<<\s*$#', '#(\S\K=|=(?=\s)|=$)#'];
		$replace = ['\/', '\~', '("$1 $2 $3 $4"|$1$2$3$4*)', '\<\<', '\='];

		$search_string = preg_replace($match, $replace, $search_string);
		$search_string = preg_replace('#\s+"\|#', '"|', $search_string);

		/**
		 * OPTIONAL: Thousands separator stripped from numbers, eg search for '90,000' is queried as '90000'.
		 * By default commas are stripped from search index so that '90,000' is indexed as '90000'
		 */
		// $search_string = preg_replace('#[0-9]{1,3}\K,(?=[0-9]{3})#', '', $search_string);

		return $search_string ? trim($search_string) : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_acp_options(): array
	{
		$config_vars = array(
			'fulltext_sphinx_data_path' => 'string',
			'fulltext_sphinx_host' => 'string',
			'fulltext_sphinx_port' => 'string',
			'fulltext_sphinx_rt_mem_limit' => 'int',
		);

		$tpl = '
		<span class="error">' . $this->language->lang('FULLTEXT_SPHINX_CONFIGURE'). '</span>
		<dl>
			<dt><label for="fulltext_sphinx_data_path">' . $this->language->lang('FULLTEXT_SPHINX_DATA_PATH') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_DATA_PATH_EXPLAIN') . '</span></dt>
			<dd><input id="fulltext_sphinx_data_path" type="text" size="40" maxlength="255" name="config[fulltext_sphinx_data_path]" value="' . $this->config['fulltext_sphinx_data_path'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_sphinx_host">' . $this->language->lang('FULLTEXT_SPHINX_HOST') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_HOST_EXPLAIN') . '</span></dt>
			<dd><input id="fulltext_sphinx_host" type="text" size="40" maxlength="255" name="config[fulltext_sphinx_host]" value="' . $this->config['fulltext_sphinx_host'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_sphinx_port">' . $this->language->lang('FULLTEXT_SPHINX_PORT') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_PORT_EXPLAIN') . '</span></dt>
			<dd><input id="fulltext_sphinx_port" type="number" min="0" max="9999999999" name="config[fulltext_sphinx_port]" value="' . $this->config['fulltext_sphinx_port'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_sphinx_rt_mem_limit">' . $this->language->lang('FULLTEXT_SPHINX_RT_MEM_LIMIT') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_RT_MEM_LIMIT_EXPLAIN') . '</span></dt>
			<dd><input id="fulltext_sphinx_rt_mem_limit" type="number" min="8" max="9999999999" name="config[fulltext_sphinx_rt_mem_limit]" value="' . (int) ($this->config['fulltext_sphinx_rt_mem_limit'] ?: 128) . '" /> ' . $this->language->lang('MIB') . '</dd>
		</dl>
		<dl>
			<dt><label for="fulltext_sphinx_config_file">' . $this->language->lang('FULLTEXT_SPHINX_CONFIG_FILE') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_CONFIG_FILE_EXPLAIN') . '</span></dt>
			<dd>' . (($this->config_generate()) ? '<textarea readonly="readonly" rows="6" id="sphinx_config_data">' . htmlspecialchars($this->config_file_data, ENT_COMPAT) . '</textarea>' : $this->config_file_data) . '</dd>
		<dl>
		';

		// These are fields required in the config table
		return array(
			'tpl'		=> $tpl,
			'config'	=> $config_vars
		);
	}

	/**
	 * Generates content of sphinx.conf
	 *
	 * @return bool True if sphinx.conf content is correctly generated, false otherwise
	 */
	protected function config_generate()
	{
		if (!$this->is_available())
		{
			$this->config_file_data = $this->language->lang('FULLTEXT_SPHINX_DRIVER_NOT_AVAILABLE');
			return false;
		}

		// Check if directory paths have been filled
		if (!$this->config['fulltext_sphinx_data_path'])
		{
			$this->config_file_data = $this->language->lang('FULLTEXT_SPHINX_NO_CONFIG_DATA');
			return false;
		}

		/* Now that we're sure everything was entered correctly,
		generate a config for the index. We use a config value
		fulltext_sphinx_id for this, as it should be unique. */
		$index_name = $this->get_index_name();
		$listen_host = client::normalize_host((string) $this->config['fulltext_sphinx_host']);
		$listen_port = client::normalize_port((string) $this->config['fulltext_sphinx_port']);
		$rt_mem_limit = (int) ($this->config['fulltext_sphinx_rt_mem_limit'] ?: 128);

		$config_data = array(
			'index ' . $index_name => array(
				array('type',						'rt'),
				array('path',						$this->config['fulltext_sphinx_data_path'] . $index_name),
				array('rt_mem_limit',				$rt_mem_limit . 'M'),
				array('rt_field',					'title'),
				array('rt_field',					'data'),
				array('rt_attr_uint',				'forum_id'),
				array('rt_attr_uint',				'topic_id'),
				array('rt_attr_uint',				'poster_id'),
				array('rt_attr_uint',				'post_visibility'),
				array('rt_attr_uint',				'topic_first_post'),
				array('rt_attr_timestamp',			'post_time'),
				array('rt_attr_timestamp',			'topic_last_post_time'),
				array('rt_attr_string',				'post_subject'),
				array('morphology',					'none'),
				array('stopwords',					''),
				array('wordforms',					'  # optional, specify path to wordforms file. See ./docs/sphinx_wordforms.txt for example'),
				array('exceptions',					'  # optional, specify path to exceptions file. See ./docs/sphinx_exceptions.txt for example'),
				array('min_word_len',				'2'),
				array('charset_table',				'U+FF10..U+FF19->0..9, 0..9, U+FF41..U+FF5A->a..z, U+FF21..U+FF3A->a..z, A..Z->a..z, a..z, U+0149, U+017F, U+0138, U+00DF, U+00FF, U+00C0..U+00D6->U+00E0..U+00F6, U+00E0..U+00F6, U+00D8..U+00DE->U+00F8..U+00FE, U+00F8..U+00FE, U+0100->U+0101, U+0101, U+0102->U+0103, U+0103, U+0104->U+0105, U+0105, U+0106->U+0107, U+0107, U+0108->U+0109, U+0109, U+010A->U+010B, U+010B, U+010C->U+010D, U+010D, U+010E->U+010F, U+010F, U+0110->U+0111, U+0111, U+0112->U+0113, U+0113, U+0114->U+0115, U+0115, U+0116->U+0117, U+0117, U+0118->U+0119, U+0119, U+011A->U+011B, U+011B, U+011C->U+011D, U+011D, U+011E->U+011F, U+011F, U+0130->U+0131, U+0131, U+0132->U+0133, U+0133, U+0134->U+0135, U+0135, U+0136->U+0137, U+0137, U+0139->U+013A, U+013A, U+013B->U+013C, U+013C, U+013D->U+013E, U+013E, U+013F->U+0140, U+0140, U+0141->U+0142, U+0142, U+0143->U+0144, U+0144, U+0145->U+0146, U+0146, U+0147->U+0148, U+0148, U+014A->U+014B, U+014B, U+014C->U+014D, U+014D, U+014E->U+014F, U+014F, U+0150->U+0151, U+0151, U+0152->U+0153, U+0153, U+0154->U+0155, U+0155, U+0156->U+0157, U+0157, U+0158->U+0159, U+0159, U+015A->U+015B, U+015B, U+015C->U+015D, U+015D, U+015E->U+015F, U+015F, U+0160->U+0161, U+0161, U+0162->U+0163, U+0163, U+0164->U+0165, U+0165, U+0166->U+0167, U+0167, U+0168->U+0169, U+0169, U+016A->U+016B, U+016B, U+016C->U+016D, U+016D, U+016E->U+016F, U+016F, U+0170->U+0171, U+0171, U+0172->U+0173, U+0173, U+0174->U+0175, U+0175, U+0176->U+0177, U+0177, U+0178->U+00FF, U+00FF, U+0179->U+017A, U+017A, U+017B->U+017C, U+017C, U+017D->U+017E, U+017E, U+0410..U+042F->U+0430..U+044F, U+0430..U+044F, U+4E00..U+9FFF'),
				array('ignore_chars', 				'U+0027, U+002C'),
				array('min_prefix_len',				'3 # Minimum number of characters for wildcard searches by prefix (min 1). Default is 3. If specified, set min_infix_len to 0'),
				array('min_infix_len',				'0 # Minimum number of characters for wildcard searches by infix (min 2). If specified, set min_prefix_len to 0'),
				array('html_strip',					'1'),
				array('index_exact_words',			'0 # Set to 1 to enable exact search operator. Requires wordforms or morphology'),
				array('blend_chars', 				'U+23, U+24, U+25, U+26, U+40'),
			),
			'searchd' => array(
				array('listen'	,					$listen_host . ':' . $listen_port . ':mysql41'),
				array('log',						$this->config['fulltext_sphinx_data_path'] . 'log/searchd.log'),
				array('query_log',					$this->config['fulltext_sphinx_data_path'] . 'log/sphinx-query.log'),
				array('read_timeout',				'5'),
				array('max_children',				'30'),
				array('pid_file',					$this->config['fulltext_sphinx_data_path'] . 'searchd.pid'),
				array('binlog_path',				rtrim($this->config['fulltext_sphinx_data_path'], '/\\')), // Trim trailing slash
				array('workers',					'threads'),
			),
		);

		/**
		 * Allow adding/changing the Sphinx configuration data
		 *
		 * @event core.search_sphinx_modify_config_data
		 * @var	array	config_data	Array with the Sphinx configuration data
		 * @since 3.1.7-RC1
		 * @changed 4.0.0-a3 Update Sphinx configuration structure for Real-Time (rt) indexes
		 */
		$vars = array(
			'config_data',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.search_sphinx_modify_config_data', compact($vars)));

		$lines = array();
		foreach ($config_data as $section_name => $section_data)
		{
			$lines[] = $section_name . "\n{";
			foreach ($section_data as $entry)
			{
				$lines[] = "\t" . $entry[0] . ' = ' . $entry[1];
			}
			$lines[] = "}\n";
		}
		$this->config_file_data = implode("\n", $lines);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type(): string
	{
		return static::class;
	}

	/**
	 * Returns the Real-Time index name, generating fulltext_sphinx_id if empty.
	 *
	 * @return string
	 */
	protected function get_index_name(): string
	{
		if (!$this->config['fulltext_sphinx_id'])
		{
			$this->config->set('fulltext_sphinx_id', unique_id());
		}

		$this->id = (string) $this->config['fulltext_sphinx_id'];
		$this->indexes = 'index_phpbb_' . $this->id;

		return $this->indexes;
	}

	/**
	 * Builds a SphinxQL SELECT statement.
	 *
	 * @param array<int, string> $where
	 * @param string $group_by
	 * @param string $order_by
	 * @param int $start
	 * @param int $per_page
	 * @param array<string, string> $options
	 * @return string
	 */
	protected function build_search_sql(array $where, string $group_by, string $order_by, int $start, int $per_page, array $options): string
	{
		$sql = 'SELECT id, topic_id FROM ' . $this->get_index_name();

		if (!empty($where))
		{
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}

		if ($group_by !== '')
		{
			$sql .= ' GROUP BY ' . $group_by;
		}

		if ($order_by !== '')
		{
			$sql .= ' ORDER BY ' . $order_by;
		}

		$sql .= sprintf(' LIMIT %d, %d', $start, $per_page);

		if (!empty($options))
		{
			$option_parts = array();
			foreach ($options as $opt_key => $opt_val)
			{
				$option_parts[] = $opt_key . '=' . $opt_val;
			}
			$sql .= ' OPTION ' . implode(', ', $option_parts);
		}

		return $sql;
	}

	/**
	 * Builds a SELECT query joining posts, topics, and forums for RT document indexing.
	 *
	 * @param string $where_sql
	 * @param string $order_by
	 * @return string
	 */
	protected function build_posts_select_sql(string $where_sql, string $order_by = ''): string
	{
		$sql = 'SELECT p.post_id, p.forum_id, p.topic_id, p.poster_id, p.post_visibility,
				p.post_time, p.post_subject, p.post_text,
				t.topic_first_post_id, t.topic_last_post_time, f.enable_indexing
			FROM ' . POSTS_TABLE . ' p
			JOIN ' . TOPICS_TABLE . ' t ON (p.topic_id = t.topic_id)
			LEFT JOIN ' . FORUMS_TABLE . ' f ON (p.forum_id = f.forum_id)
			WHERE ' . $where_sql;

		if ($order_by !== '')
		{
			$sql .= ' ORDER BY ' . $order_by;
		}

		return $sql;
	}

	/**
	 * Inserts or replaces a batch of post rows in the Real-Time index.
	 *
	 * @param array<int, array<string, mixed>> $rows
	 * @return void
	 * @throws sphinx_exception
	 */
	protected function upsert_documents(array $rows): void
	{
		if (empty($rows))
		{
			return;
		}

		$values_sql = array();
		foreach ($rows as $row)
		{
			$post_id = (int) $row['post_id'];
			$subject = $this->client->quote_string((string) $row['post_subject']);

			$values_sql[] = sprintf(
				'(%d, %s, %s, %d, %d, %d, %d, %d, %d, %d, %s)',
				$post_id,
				$subject,
				$this->client->quote_string((string) $row['post_text']),
				(int) $row['forum_id'],
				(int) $row['topic_id'],
				(int) $row['poster_id'],
				(int) $row['post_visibility'],
				($post_id === (int) $row['topic_first_post_id']) ? 1 : 0,
				(int) $row['post_time'],
				(int) $row['topic_last_post_time'],
				$subject
			);
		}

		$sql = 'REPLACE INTO ' . $this->get_index_name()
			. ' (id, title, data, forum_id, topic_id, poster_id, post_visibility, topic_first_post, post_time, topic_last_post_time, post_subject)'
			. ' VALUES ' . implode(', ', $values_sql);

		$this->client->execute($sql);
	}

	/**
	 * Synchronizes RT index documents matching a SQL WHERE clause.
	 *
	 * @param string $where_sql
	 * @param array<int, int> $expected_post_ids
	 * @return void
	 */
	protected function sync_posts_where(string $where_sql, array $expected_post_ids = array()): void
	{
		if (!$this->client->index_exists($this->get_index_name()))
		{
			return;
		}

		$result = $this->db->sql_query($this->build_posts_select_sql($where_sql));
		$found_ids = array();
		$rows_to_upsert = array();
		$ids_to_delete = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$pid = (int) $row['post_id'];
			$found_ids[] = $pid;

			if (((int) $row['forum_id'] === 0) || (bool) $row['enable_indexing'])
			{
				$rows_to_upsert[] = $row;
			}
			else
			{
				$ids_to_delete[] = $pid;
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($expected_post_ids))
		{
			$ids_to_delete = array_values(array_unique(array_merge($ids_to_delete, array_diff($expected_post_ids, $found_ids))));
		}

		try
		{
			foreach (array_chunk($rows_to_upsert, self::INDEX_BATCH_SIZE) as $batch)
			{
				$this->upsert_documents($batch);
			}

			if (!empty($ids_to_delete))
			{
				$this->index_remove($ids_to_delete, array(), array());
			}
		}
		catch (sphinx_exception $e)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_SPHINX_ERROR', false, array($e->getMessage()));
		}
	}
}
