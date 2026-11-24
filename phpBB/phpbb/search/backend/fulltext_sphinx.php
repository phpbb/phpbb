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
use phpbb\log\log_interface;
use phpbb\search\backend\sphinx\client;
use phpbb\search\backend\sphinx\client_interface;
use phpbb\search\backend\sphinx\exception as sphinx_exception;
use phpbb\textformatter\utils_interface;
use phpbb\user;

/**
 * Fulltext search based on Sphinx / Manticore Real-Time (rt) indexes using SphinxQL.
 */
class fulltext_sphinx implements search_backend_interface
{
	protected const SPHINX_MAX_MATCHES = 20000;
	protected const INDEX_BATCH_SIZE = 500;
	protected const DELETE_BATCH_SIZE = 500;
	protected const CHARSET_TABLE = 'U+FF10..U+FF19->0..9, 0..9, U+FF41..U+FF5A->a..z, U+FF21..U+FF3A->a..z, A..Z->a..z, a..z, U+0149, U+017F, U+0138, U+00DF, U+00FF, U+00C0..U+00D6->U+00E0..U+00F6, U+00E0..U+00F6, U+00D8..U+00DE->U+00F8..U+00FE, U+00F8..U+00FE, U+0100->U+0101, U+0101, U+0102->U+0103, U+0103, U+0104->U+0105, U+0105, U+0106->U+0107, U+0107, U+0108->U+0109, U+0109, U+010A->U+010B, U+010B, U+010C->U+010D, U+010D, U+010E->U+010F, U+010F, U+0110->U+0111, U+0111, U+0112->U+0113, U+0113, U+0114->U+0115, U+0115, U+0116->U+0117, U+0117, U+0118->U+0119, U+0119, U+011A->U+011B, U+011B, U+011C->U+011D, U+011D, U+011E->U+011F, U+011F, U+0130->U+0131, U+0131, U+0132->U+0133, U+0133, U+0134->U+0135, U+0135, U+0136->U+0137, U+0137, U+0139->U+013A, U+013A, U+013B->U+013C, U+013C, U+013D->U+013E, U+013E, U+013F->U+0140, U+0140, U+0141->U+0142, U+0142, U+0143->U+0144, U+0144, U+0145->U+0146, U+0146, U+0147->U+0148, U+0148, U+014A->U+014B, U+014B, U+014C->U+014D, U+014D, U+014E->U+014F, U+014F, U+0150->U+0151, U+0151, U+0152->U+0153, U+0153, U+0154->U+0155, U+0155, U+0156->U+0157, U+0157, U+0158->U+0159, U+0159, U+015A->U+015B, U+015B, U+015C->U+015D, U+015D, U+015E->U+015F, U+015F, U+0160->U+0161, U+0161, U+0162->U+0163, U+0163, U+0164->U+0165, U+0165, U+0166->U+0167, U+0167, U+0168->U+0169, U+0169, U+016A->U+016B, U+016B, U+016C->U+016D, U+016D, U+016E->U+016F, U+016F, U+0170->U+0171, U+0171, U+0172->U+0173, U+0173, U+0174->U+0175, U+0175, U+0176->U+0177, U+0177, U+0178->U+00FF, U+00FF, U+0179->U+017A, U+017A, U+017B->U+017C, U+017C, U+017D->U+017E, U+017E, U+0410..U+042F->U+0430..U+044F, U+0430..U+044F, U+4E00..U+9FFF';

	/**
	 * Associative array holding index stats
	 * @var array<string, bool|int|string>
	 */
	protected $stats = [];

	/**
	 * Holds the words entered by user, obtained by splitting the entered query on whitespace
	 * @var array<int, string>
	 */
	protected $split_words = [];

	/**
	 * Current match mode ('all' or 'any') set by split_keywords()
	 * @var string
	 */
	protected $match_terms = 'all';

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
	 * Language service
	 * @var language
	 */
	protected $language;

	/**
	 * Log service
	 * @var log_interface
	 */
	protected $log;

	/**
	 * SphinxQL client
	 * @var client_interface
	 */
	protected $client;

	/**
	 * Text formatter utilities
	 * @var utils_interface
	 */
	protected $text_formatter_utils;

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
	 * Constructor
	 *
	 * @param auth					$auth
	 * @param config				$config
	 * @param driver_interface		$db
	 * @param dispatcher_interface	$phpbb_dispatcher
	 * @param language				$language
	 * @param log_interface			$log
	 * @param client_interface		$client
	 * @param utils_interface		$text_formatter_utils
	 * @param user					$user
	 */
	public function __construct(
		auth $auth,
		config $config,
		driver_interface $db,
		dispatcher_interface $phpbb_dispatcher,
		language $language,
		log_interface $log,
		client_interface $client,
		utils_interface $text_formatter_utils,
		user $user
	)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;
		$this->log = $log;
		$this->client = $client;
		$this->text_formatter_utils = $text_formatter_utils;
		$this->user = $user;
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
	public function get_type(): string
	{
		return static::class;
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

		$this->get_id();

		return false;
	}

	/**
	 * Returns the sanitized unique Sphinx board identifier, generating one if empty.
	 *
	 * @return string
	 */
	public function get_id(): string
	{
		$id = (string) $this->config['fulltext_sphinx_id'];
		$id = preg_replace('/[^a-zA-Z0-9_]/', '', $id) ?? '';

		if ($id === '')
		{
			$id = unique_id();
			$this->config->set('fulltext_sphinx_id', $id);
		}

		return $id;
	}

	/**
	 * Returns the Real-Time index name for this board.
	 *
	 * @return string
	 */
	public function get_index_name(): string
	{
		return 'index_phpbb_' . $this->get_id();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_search_query(): string
	{
		return $this->search_query;
	}

	/**
	 * Returns the split words array.
	 *
	 * @return array<int, string>
	 */
	public function get_split_words(): array
	{
		return $this->split_words;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_common_words(): array
	{
		return [];
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
	public function supports_phrase_search(): bool
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function tidy(): void
	{
		$this->config->set('search_last_gc', time(), false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function split_keywords(string &$keywords, string $terms): bool
	{
		$this->split_words = [];
		$this->search_query = '';
		$this->match_terms = ($terms === 'any') ? 'any' : 'all';

		// Keep quotes and normalize whitespace/newlines
		$keywords = str_replace(['&quot;', "\r\n", "\r", "\n"], ['"', ' ', ' ', ' '], trim($keywords));

		if ($this->match_terms === 'all')
		{
			// Replace verbal operators OR and NOT with | and -, unless appearing inside quotation marks
			$match = ['#\sor\s(?=([^"]*"[^"]*")*[^"]*$)#i', '#\snot\s(?=([^"]*"[^"]*")*[^"]*$)#i'];
			$replace = [' | ', ' -'];

			$keywords = preg_replace($match, $replace, $keywords) ?? '';
		}
		else
		{
			$match = ['\\', '(', ')', '|', '!', '@', '~', '/', '^', '$', '=', '&amp;', '&lt;', '&gt;', '"'];
			$keywords = trim(preg_replace('/\s+/', ' ', str_replace($match, ' ', $keywords)) ?? '');
		}

		if ($keywords === '')
		{
			return false;
		}

		$this->search_query = str_replace('"', '&quot;', $keywords);
		$tokens = preg_split('/\s+/', str_replace(['"', '|', '(', ')'], ' ', $keywords), -1, PREG_SPLIT_NO_EMPTY);
		if (is_array($tokens))
		{
			$this->split_words = array_values(array_filter($tokens, static function (string $token): bool {
				return $token !== '-';
			}));
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function keyword_search(string $type, string $fields, string $terms, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page)
	{
		// No keywords and no author filter? Nothing to search.
		if ($this->search_query === '' && empty($author_ary))
		{
			return false;
		}

		$id_ary = [];
		$select_columns = ['id', 'topic_id'];
		$where = [];
		$options = [];

		// Build fulltext MATCH(...) clause when keywords are provided
		if ($this->search_query !== '')
		{
			$effective_terms = ($terms === 'any' || $this->match_terms === 'any') ? 'any' : 'all';
			$match_expr = $this->build_match_expression(str_replace('&quot;', '"', $this->search_query), $fields, $effective_terms);

			if ($match_expr === null)
			{
				return false;
			}

			$where[] = 'MATCH(' . $this->client->quote_string($match_expr) . ')';
		}

		// Field weights and first-post restrictions
		switch ($fields)
		{
			case 'titleonly':
				$options['field_weights'] = '(title=5, data=1)';
				$where[] = 'topic_first_post = 1';
			break;

			case 'msgonly':
				$options['field_weights'] = '(title=1, data=5)';
			break;

			case 'firstpost':
				$options['field_weights'] = '(title=5, data=1)';
				$where[] = 'topic_first_post = 1';
			break;

			default:
				$options['field_weights'] = '(title=5, data=1)';
			break;
		}

		// Most narrow filters first
		if ($topic_id > 0)
		{
			$where[] = 'topic_id = ' . $topic_id;
		}

		$author_ids = $this->sanitize_positive_ints($author_ary);
		if (!empty($author_ids))
		{
			$where[] = (count($author_ids) === 1)
				? 'poster_id = ' . $author_ids[0]
				: 'poster_id IN (' . implode(', ', $author_ids) . ')';
		}

		if ((int) $sort_days > 0)
		{
			$min_post_time = time() - ((int) $sort_days * 86400);
			$where[] = 'post_time >= ' . $min_post_time;
		}

		// Forum access & exclusions
		if (!empty($ex_fid_ary))
		{
			// All forums that a user is allowed to access
			$readable_forums = array_unique(array_intersect(
				array_keys($this->auth->acl_getf('f_read', true)),
				array_keys($this->auth->acl_getf('f_search', true))
			));
			// All forums that the user wants to and can search in
			$search_forums = array_values(array_map('intval', array_diff($readable_forums, $ex_fid_ary)));

			if (empty($search_forums))
			{
				return false;
			}

			// Include forum_id = 0 for global announcements
			if (!in_array(0, $ex_fid_ary, true) && !in_array(0, $search_forums, true))
			{
				$search_forums[] = 0;
			}

			$where[] = 'forum_id IN (' . implode(', ', $search_forums) . ')';
		}

		// Post visibility (approved posts, plus all visibility states in forums moderated by the current user)
		$approve_forums = array_values(array_map(
			'intval',
			array_diff(array_keys($this->auth->acl_getf('m_approve', true)), $ex_fid_ary)
		));

		if (!empty($approve_forums))
		{
			$select_columns[] = '(post_visibility = ' . (int) ITEM_APPROVED . ' OR IN(forum_id, ' . implode(', ', $approve_forums) . ')) AS is_visible';
			$where[] = 'is_visible = 1';
		}
		else
		{
			$where[] = 'post_visibility = ' . (int) ITEM_APPROVED;
		}

		// Sorting & grouping
		$direction = ($sort_dir === 'a') ? 'ASC' : 'DESC';
		switch ($sort_key)
		{
			case 'a':
				$sort_column = 'poster_id';
			break;

			case 'f':
				$sort_column = 'forum_id';
			break;

			case 'i':
			case 's':
				$sort_column = 'post_subject';
			break;

			case 't':
			default:
				$sort_column = ($type === 'topics') ? 'topic_last_post_time' : 'post_time';
			break;
		}

		$order_by = $sort_column . ' ' . $direction;
		$group_by = ($type === 'topics') ? 'topic_id' : '';
		$within_group_order_by = ($type === 'topics') ? $order_by : '';

		$start = max(0, (int) $start);
		$per_page = max(1, (int) $per_page);
		$options['max_matches'] = (string) max(self::SPHINX_MAX_MATCHES, $start + $per_page);

		/**
		 * Allow modifying the SphinxQL query clauses before execution
		 *
		 * @event core.search_sphinx_keywords_modify_options
		 * @var string	type					Searching type ('posts', 'topics')
		 * @var string	fields					Searching fields ('titleonly', 'msgonly', 'firstpost', 'all')
		 * @var string	terms					Searching terms ('all', 'any')
		 * @var string	sort_days				Time, in days, of the oldest possible post to list
		 * @var string	sort_key				The sort type used from the possible sort types
		 * @var string	sort_dir				Sort direction ('a' or 'd')
		 * @var int		topic_id				Limit the search to this topic_id only
		 * @var array	ex_fid_ary				Which forums not to search on
		 * @var string	post_visibility			Post visibility SQL data
		 * @var array	author_ary				Array of user_id containing the users to filter the results to
		 * @var string	author_name				The username to search on
		 * @var array	select_columns			Columns/expressions for the SphinxQL SELECT clause
		 * @var array	where					Conditions for the SphinxQL WHERE clause
		 * @var string	group_by				Column for GROUP BY (empty for posts)
		 * @var string	within_group_order_by	Clause for WITHIN GROUP ORDER BY
		 * @var string	order_by				Clause for ORDER BY
		 * @var array	options					Key-value map for the SphinxQL OPTION clause
		 * @since 3.1.7-RC1
		 * @changed 4.0.0-a3 Replace SphinxClient with SphinxQL query clauses
		 */
		$vars = [
			'type',
			'fields',
			'terms',
			'sort_days',
			'sort_key',
			'sort_dir',
			'topic_id',
			'ex_fid_ary',
			'post_visibility',
			'author_ary',
			'author_name',
			'select_columns',
			'where',
			'group_by',
			'within_group_order_by',
			'order_by',
			'options',
		];
		extract($this->phpbb_dispatcher->trigger_event('core.search_sphinx_keywords_modify_options', compact($vars)));

		try
		{
			$sql = $this->compile_select_query($select_columns, $where, $group_by, $within_group_order_by, $order_by, $start, $per_page, $options);
			$result = $this->client->select_with_meta($sql);
			$result_count = (int) ($result['meta']['total_found'] ?? 0);

			if ($result_count > 0 && $start >= $result_count)
			{
				$start = (int) (floor(($result_count - 1) / $per_page) * $per_page);
				$options['max_matches'] = (string) max(self::SPHINX_MAX_MATCHES, $start + $per_page);
				$sql = $this->compile_select_query($select_columns, $where, $group_by, $within_group_order_by, $order_by, $start, $per_page, $options);
				$result = $this->client->select_with_meta($sql);
			}
		}
		catch (sphinx_exception $e)
		{
			$this->log_sphinx_error($e);
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

		if ($result_count <= 0 || empty($result['rows']))
		{
			return false;
		}

		$id_key = ($type === 'posts') ? 'id' : 'topic_id';
		foreach (array_slice($result['rows'], 0, $per_page) as $row)
		{
			$id_ary[] = (int) $row[$id_key];
		}

		return $result_count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function author_search(string $type, bool $firstpost_only, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page)
	{
		if (empty($author_ary))
		{
			return false;
		}

		$this->search_query = '';
		$fields = $firstpost_only ? 'firstpost' : 'all';

		return $this->keyword_search($type, $fields, 'all', $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $post_visibility, $topic_id, $author_ary, $author_name, $id_ary, $start, $per_page);
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
		 * @var string	mode		Contains the post mode: edit, post, reply, quote
		 * @var int		post_id		The id of the post which is modified/created
		 * @var string	message		New or updated post content
		 * @var string	subject		New or updated post subject
		 * @var int		poster_id	Post author's user id
		 * @var int		forum_id	The id of the forum in which the post is located
		 * @since 3.2.3-RC1
		 */
		$vars = [
			'mode',
			'post_id',
			'message',
			'subject',
			'poster_id',
			'forum_id',
		];
		extract($this->phpbb_dispatcher->trigger_event('core.search_sphinx_index_before', compact($vars)));

		if ($post_id <= 0 || !$this->client->index_exists($this->get_index_name()))
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
			$forum_sql = 'SELECT enable_indexing
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $forum_id;
			$forum_result = $this->db->sql_query($forum_sql);
			$indexing_enabled = (bool) $this->db->sql_fetchfield('enable_indexing');
			$this->db->sql_freeresult($forum_result);
		}

		if (!$indexing_enabled)
		{
			return;
		}

		// Honor argument overrides from caller / core.search_sphinx_index_before
		$row['post_subject'] = $subject;
		$row['post_text'] = $message;
		$row['poster_id'] = $poster_id;
		$row['forum_id'] = $forum_id;

		try
		{
			$this->upsert_documents([$this->build_document_from_row($row)]);

			// Update topic_last_post_time across the full topic on reply/quote
			if ($mode !== 'post' && $mode !== 'edit' && (int) $row['topic_id'] > 0)
			{
				$update_sql = sprintf(
					'UPDATE %s SET topic_last_post_time = %d WHERE topic_id = %d',
					$this->get_index_name(),
					(int) $row['topic_last_post_time'],
					(int) $row['topic_id']
				);
				$this->client->execute($update_sql);
			}
		}
		catch (sphinx_exception $e)
		{
			$this->log_sphinx_error($e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function index_remove(array $post_ids, array $author_ids, array $forum_ids): void
	{
		$post_ids = $this->sanitize_positive_ints($post_ids);
		if (empty($post_ids) || !$this->client->index_exists($this->get_index_name()))
		{
			return;
		}

		try
		{
			foreach (array_chunk($post_ids, self::DELETE_BATCH_SIZE) as $chunk)
			{
				$this->client->execute(sprintf(
					'DELETE FROM %s WHERE id IN (%s)',
					$this->get_index_name(),
					implode(', ', $chunk)
				));
			}
		}
		catch (sphinx_exception $e)
		{
			$this->log_sphinx_error($e);
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
		$topic_ids = $this->sanitize_positive_ints($topic_ids);
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
		$post_ids = $this->sanitize_positive_ints($post_ids);
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
		$user_ids = $this->sanitize_positive_ints($user_ids);
		if (empty($user_ids) || !$this->client->index_exists($this->get_index_name()))
		{
			return;
		}

		try
		{
			$this->client->execute(sprintf(
				'UPDATE %s SET poster_id = %d WHERE poster_id IN (%s)',
				$this->get_index_name(),
				$new_poster_id,
				implode(', ', $user_ids)
			));
		}
		catch (sphinx_exception $e)
		{
			$this->log_sphinx_error($e);
		}
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

		$max_post_id = $this->get_max_post_id();
		$starttime = microtime(true);
		$row_count = 0;

		while (still_on_time() && $post_counter < $max_post_id)
		{
			$sql = $this->build_posts_select_sql('p.post_id > ' . (int) $post_counter, 'p.post_id ASC');
			$result = $this->db->sql_query_limit($sql, self::INDEX_BATCH_SIZE);

			$batch_docs = [];
			$batch_rows = 0;
			while ($row = $this->db->sql_fetchrow($result))
			{
				$batch_rows++;
				$post_counter = (int) $row['post_id'];
				$indexing_enabled = ((int) $row['forum_id'] === 0) || (bool) $row['enable_indexing'];

				if ($indexing_enabled)
				{
					$batch_docs[] = $this->build_document_from_row($row);
				}
			}
			$this->db->sql_freeresult($result);

			if ($batch_rows === 0)
			{
				$post_counter = $max_post_id;
				break;
			}

			$this->upsert_documents($batch_docs);
			$row_count += $batch_rows;

			// With CLI process only one batch per call so progress bar updates smoothly
			if (PHP_SAPI === 'cli')
			{
				break;
			}
		}

		if ($post_counter < $max_post_id)
		{
			$totaltime = max(0.0001, microtime(true) - $starttime);

			return [
				'row_count'			=> $row_count,
				'post_counter'		=> $post_counter,
				'max_post_id'		=> $max_post_id,
				'rows_per_second'	=> $row_count / $totaltime,
			];
		}

		$this->config->set('fulltext_sphinx_indexed', 1);
		$this->stats = [];

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
		$this->stats = [];

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function index_created(): bool
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

		$indexed = $this->index_created();
		$stats = [
			$this->language->lang('FULLTEXT_SPHINX_TOTAL_POSTS')	=> $indexed ? (int) $this->stats['total_posts'] : 0,
		];

		if (isset($this->stats['ram_bytes']))
		{
			$stats[$this->language->lang('FULLTEXT_SPHINX_RAM_BYTES')] = $indexed ? (int) $this->stats['ram_bytes'] : 0;
		}

		if (isset($this->stats['disk_bytes']))
		{
			$stats[$this->language->lang('FULLTEXT_SPHINX_DISK_BYTES')] = $indexed ? (int) $this->stats['disk_bytes'] : 0;
		}

		return $stats;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_acp_options(): array
	{
		$config_vars = [
			'fulltext_sphinx_data_path'		=> 'string',
			'fulltext_sphinx_host'			=> 'string',
			'fulltext_sphinx_port'			=> 'string',
			'fulltext_sphinx_rt_mem_limit'	=> 'int',
		];

		$tpl = '
		<span class="error">' . $this->language->lang('FULLTEXT_SPHINX_CONFIGURE') . '</span>
		<dl>
			<dt><label for="fulltext_sphinx_data_path">' . $this->language->lang('FULLTEXT_SPHINX_DATA_PATH') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_DATA_PATH_EXPLAIN') . '</span></dt>
			<dd><input id="fulltext_sphinx_data_path" type="text" size="40" maxlength="255" name="config[fulltext_sphinx_data_path]" value="' . htmlspecialchars((string) $this->config['fulltext_sphinx_data_path'], ENT_COMPAT) . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_sphinx_host">' . $this->language->lang('FULLTEXT_SPHINX_HOST') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_HOST_EXPLAIN') . '</span></dt>
			<dd><input id="fulltext_sphinx_host" type="text" size="40" maxlength="255" name="config[fulltext_sphinx_host]" value="' . htmlspecialchars((string) $this->config['fulltext_sphinx_host'], ENT_COMPAT) . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_sphinx_port">' . $this->language->lang('FULLTEXT_SPHINX_PORT') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_PORT_EXPLAIN') . '</span></dt>
			<dd><input id="fulltext_sphinx_port" type="number" min="0" max="65535" name="config[fulltext_sphinx_port]" value="' . htmlspecialchars((string) $this->config['fulltext_sphinx_port'], ENT_COMPAT) . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_sphinx_rt_mem_limit">' . $this->language->lang('FULLTEXT_SPHINX_RT_MEM_LIMIT') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_RT_MEM_LIMIT_EXPLAIN') . '</span></dt>
			<dd><input id="fulltext_sphinx_rt_mem_limit" type="number" min="8" max="999999" name="config[fulltext_sphinx_rt_mem_limit]" value="' . (int) ($this->config['fulltext_sphinx_rt_mem_limit'] ?: 128) . '" /> ' . $this->language->lang('MIB') . '</dd>
		</dl>
		<dl>
			<dt><label for="sphinx_config_data">' . $this->language->lang('FULLTEXT_SPHINX_CONFIG_FILE') . $this->language->lang('COLON') . '</label><br /><span>' . $this->language->lang('FULLTEXT_SPHINX_CONFIG_FILE_EXPLAIN') . '</span></dt>
			<dd>' . ($this->config_generate() ? '<textarea readonly="readonly" rows="6" id="sphinx_config_data">' . htmlspecialchars($this->config_file_data, ENT_COMPAT) . '</textarea>' : $this->config_file_data) . '</dd>
		</dl>
		';

		return [
			'tpl'		=> $tpl,
			'config'	=> $config_vars,
		];
	}

	/**
	 * Generates the content of sphinx.conf for a Real-Time (rt) index.
	 *
	 * @return bool
	 */
	protected function config_generate(): bool
	{
		if (!$this->is_available())
		{
			$this->config_file_data = $this->language->lang('FULLTEXT_SPHINX_DRIVER_NOT_AVAILABLE');
			return false;
		}

		$data_path = (string) $this->config['fulltext_sphinx_data_path'];
		if ($data_path === '')
		{
			$this->config_file_data = $this->language->lang('FULLTEXT_SPHINX_NO_CONFIG_DATA');
			return false;
		}

		$data_path_normalized = rtrim($data_path, '/\\') . '/';
		$index_name = $this->get_index_name();
		$listen_host = client::normalize_host((string) $this->config['fulltext_sphinx_host']);
		$listen_port = client::normalize_port((string) $this->config['fulltext_sphinx_port']);
		$rt_mem_limit = (int) ($this->config['fulltext_sphinx_rt_mem_limit'] ?: 128);

		$config_data = [
			'index ' . $index_name => [
				['type',				'rt'],
				['path',				$data_path_normalized . $index_name],
				['rt_mem_limit',		$rt_mem_limit . 'M'],
				['rt_field',			'title'],
				['rt_field',			'data'],
				['rt_attr_uint',		'forum_id'],
				['rt_attr_uint',		'topic_id'],
				['rt_attr_uint',		'poster_id'],
				['rt_attr_uint',		'post_visibility'],
				['rt_attr_uint',		'topic_first_post'],
				['rt_attr_timestamp',	'post_time'],
				['rt_attr_timestamp',	'topic_last_post_time'],
				['rt_attr_string',		'post_subject'],
				['morphology',			'none'],
				['stopwords',			''],
				['wordforms',			'# optional, specify path to wordforms file. See ./docs/sphinx_wordforms.txt for example'],
				['exceptions',			'# optional, specify path to exceptions file. See ./docs/sphinx_exceptions.txt for example'],
				['min_word_len',		'2'],
				['charset_table',		self::CHARSET_TABLE],
				['ignore_chars',		'U+0027, U+002C'],
				['min_prefix_len',		'3 # Minimum number of characters for wildcard searches by prefix (min 1). Default is 3. If specified, set min_infix_len to 0'],
				['min_infix_len',		'0 # Minimum number of characters for wildcard searches by infix (min 2). If specified, set min_prefix_len to 0'],
				['html_strip',			'1'],
				['index_exact_words',	'0 # Set to 1 to enable exact search operator. Requires wordforms or morphology'],
				['blend_chars',			'U+23, U+24, U+25, U+26, U+40'],
			],
			'searchd' => [
				['listen',			$listen_host . ':' . $listen_port . ':mysql41'],
				['log',				$data_path_normalized . 'log/searchd.log'],
				['query_log',		$data_path_normalized . 'log/sphinx-query.log'],
				['read_timeout',	'5'],
				['max_children',	'30'],
				['pid_file',		$data_path_normalized . 'searchd.pid'],
				['binlog_path',		rtrim($data_path, '/\\')],
				['workers',			'threads'],
			],
		];

		/**
		 * Allow adding/changing the Sphinx configuration data
		 *
		 * @event core.search_sphinx_modify_config_data
		 * @var array	config_data	Array of section_name => list of [key, value] pairs
		 * @since 3.1.7-RC1
		 * @changed 4.0.0-a3 Update Sphinx configuration structure for Real-Time (rt) indexes
		 */
		$vars = [
			'config_data',
		];
		extract($this->phpbb_dispatcher->trigger_event('core.search_sphinx_modify_config_data', compact($vars)));

		$lines = [];
		foreach ($config_data as $section_name => $entries)
		{
			$lines[] = $section_name . "\n{";
			foreach ($entries as [$key, $value])
			{
				$lines[] = "\t" . $key . ' = ' . $value;
			}
			$lines[] = "}\n";
		}

		$this->config_file_data = implode("\n", $lines);

		return true;
	}

	/**
	 * Queries live document count and RT index status from SphinxQL.
	 *
	 * @return void
	 */
	protected function get_stats(): void
	{
		$this->stats = [
			'index_exists'	=> false,
			'total_posts'	=> 0,
		];

		$index_name = $this->get_index_name();
		if (!$this->client->index_exists($index_name))
		{
			return;
		}

		$this->stats['index_exists'] = true;

		try
		{
			$count_result = $this->client->select_with_meta('SELECT id FROM ' . $index_name . ' LIMIT 0, 1');
			$this->stats['total_posts'] = (int) ($count_result['meta']['total_found'] ?? 0);

			$status_rows = $this->client->query('SHOW INDEX ' . $index_name . ' STATUS');
			foreach ($status_rows as $status_row)
			{
				if (isset($status_row['Variable_name'], $status_row['Value']))
				{
					$var_name = (string) $status_row['Variable_name'];
					if ($var_name === 'ram_bytes' || $var_name === 'disk_bytes')
					{
						$this->stats[$var_name] = (int) $status_row['Value'];
					}
				}
			}
		}
		catch (sphinx_exception $e)
		{
			$this->stats['total_posts'] = 0;
		}
	}

	/**
	 * Synchronizes RT index documents matching a SQL WHERE clause (upserting indexed forums, deleting non-indexed/missing).
	 *
	 * @param string $where_sql
	 * @param array<int, int> $expected_post_ids
	 * @return void
	 */
	protected function sync_posts_where(string $where_sql, array $expected_post_ids = []): void
	{
		if (!$this->client->index_exists($this->get_index_name()))
		{
			return;
		}

		$result = $this->db->sql_query($this->build_posts_select_sql($where_sql));
		$found_ids = [];
		$docs_to_upsert = [];
		$ids_to_delete = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$pid = (int) $row['post_id'];
			$found_ids[] = $pid;
			$indexing_enabled = ((int) $row['forum_id'] === 0) || (bool) $row['enable_indexing'];

			if ($indexing_enabled)
			{
				$docs_to_upsert[] = $this->build_document_from_row($row);
			}
			else
			{
				$ids_to_delete[] = $pid;
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($expected_post_ids))
		{
			$missing_ids = array_diff($expected_post_ids, $found_ids);
			$ids_to_delete = array_values(array_unique(array_merge($ids_to_delete, $missing_ids)));
		}

		try
		{
			foreach (array_chunk($docs_to_upsert, self::INDEX_BATCH_SIZE) as $batch)
			{
				$this->upsert_documents($batch);
			}

			if (!empty($ids_to_delete))
			{
				$this->index_remove($ids_to_delete, [], []);
			}
		}
		catch (sphinx_exception $e)
		{
			$this->log_sphinx_error($e);
		}
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
	 * Returns the highest post_id in the posts table.
	 *
	 * @return int
	 */
	protected function get_max_post_id(): int
	{
		$sql = 'SELECT MAX(post_id) AS max_post_id
			FROM ' . POSTS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_post_id = (int) $this->db->sql_fetchfield('max_post_id');
		$this->db->sql_freeresult($result);

		return $max_post_id;
	}

	/**
	 * Filters an array to unique positive integers.
	 *
	 * @param array<int|string, mixed> $ids
	 * @return array<int, int>
	 */
	protected function sanitize_positive_ints(array $ids): array
	{
		return array_values(array_unique(array_filter(array_map('intval', $ids), static function (int $id): bool {
			return $id > 0;
		})));
	}

	/**
	 * Logs a critical Sphinx error to the phpBB error log.
	 *
	 * @param sphinx_exception $e
	 * @return void
	 */
	protected function log_sphinx_error(sphinx_exception $e): void
	{
		$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_SPHINX_ERROR', false, [$e->getMessage()]);
	}

	/**
	 * Builds a Sphinx extended2 MATCH() expression for the given raw search query, field scope, and term mode.
	 * Returns null if no computable search terms remain.
	 *
	 * @param string $raw_query
	 * @param string $fields
	 * @param string $terms
	 * @return string|null
	 */
	protected function build_match_expression(string $raw_query, string $fields, string $terms): string|null
	{
		$cleaned = $this->sphinx_clean_search_string($raw_query);

		if ($terms === 'any')
		{
			// Split cleaned words into OR-ed terms for SphinxQL extended2 mode
			$parts = preg_split('/\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);
			if (!is_array($parts) || empty($parts))
			{
				return null;
			}

			$expr = implode(' | ', $parts);
		}
		else
		{
			if ($cleaned === '')
			{
				return null;
			}

			// Guard against pure-negative queries (e.g. "-foo -bar") which Sphinx rejects as non-computable
			$without_negated = preg_replace('/(?:^|\s)-(?:"[^"]+"|\S+)/u', '', $cleaned) ?? '';
			if (trim($without_negated) === '')
			{
				return null;
			}

			$expr = $cleaned;
		}

		if ($fields === 'titleonly')
		{
			return '@title (' . $expr . ')';
		}

		if ($fields === 'msgonly')
		{
			return '@data (' . $expr . ')';
		}

		return $expr;
	}

	/**
	 * Cleans search query passed into Sphinx search engine, as follows:
	 * 1. Hyphenated words are replaced with keyword search for either the exact phrase with spaces
	 *    or as a single word without spaces, e.g. "know-it-all" becomes ("know it all"|"knowitall*").
	 *    Standalone hyphens surrounded by whitespace and trailing word hyphens are stripped.
	 * 2. Words with apostrophes are contracted, e.g. "it's" becomes "its".
	 * 3. <, >, " and & are decoded from HTML entities.
	 * 4. Following special characters used as search operators in Sphinx are preserved when used with correct syntax:
	 *    (a) quorum matching: "the world is a wonderful place"/3 (number between 1 and 9)
	 *    (b) proximity search: "hello world"~10 (number between 1 and 99)
	 *    (c) strict word order: aaa << bbb << ccc
	 *    (d) exact match operator: =cats and =dogs or ="search this exact phrase"
	 * 5. Special characters /, ~, << and = not complying with the correct syntax
	 *    and other reserved operators are escaped and searched literally.
	 *
	 * @param string $search_string
	 * @return string
	 */
	protected function sphinx_clean_search_string(string $search_string): string
	{
		$from = ['@', '^', '$', '!', '&lt;', '&gt;', '&quot;', '&amp;', '\''];
		$to = ['\\@', '\\^', '\\$', '\\!', '<', '>', '"', '&', ''];

		$search_string = str_replace($from, $to, $search_string);

		// Remove standalone hyphens (surrounded by spaces or at string boundaries) and trailing word hyphens
		$search_string = preg_replace('/(?:^|\s)+-+(?=\s|$)/u', ' ', $search_string) ?? '';
		$search_string = preg_replace('/(?<=[\p{L}\p{N}])-+(?=\s|$)/u', '', $search_string) ?? '';

		$search_string = strrev($search_string);
		$search_string = preg_replace(['#\/(?!"[^"]+")#', '#~(?!"[^"]+")#'], ['/\\', '~\\'], $search_string) ?? '';
		$search_string = strrev($search_string);

		$match = [
			'#(/|\\\\/)(?![1-9](\s|$))#',
			'#(~|\\\\~)(?!\d{1,2}(\s|$))#',
			'#((?:\p{L}|\p{N})+)-((?:\p{L}|\p{N})+)(?:-((?:\p{L}|\p{N})+))?(?:-((?:\p{L}|\p{N})+))?#iu',
			'#<<\s*$#',
			'#(\S\K=|=(?=\s)|=$)#',
		];
		$replace = [
			'\\/',
			'\\~',
			'("$1 $2 $3 $4"|$1$2$3$4*)',
			'\\<\\<',
			'\\=',
		];

		$search_string = preg_replace($match, $replace, $search_string) ?? '';
		$search_string = preg_replace('#\s+"\|#', '"|', $search_string) ?? '';
		$search_string = preg_replace('/\s+/u', ' ', $search_string) ?? '';

		return trim($search_string);
	}

	/**
	 * Compiles a SphinxQL SELECT statement from structured clauses.
	 *
	 * @param array<int, string> $select_columns
	 * @param array<int, string> $where
	 * @param string $group_by
	 * @param string $within_group_order_by
	 * @param string $order_by
	 * @param int $start
	 * @param int $per_page
	 * @param array<string, string> $options
	 * @return string
	 */
	protected function compile_select_query(
		array $select_columns,
		array $where,
		string $group_by,
		string $within_group_order_by,
		string $order_by,
		int $start,
		int $per_page,
		array $options
	): string
	{
		$sql = 'SELECT ' . implode(', ', $select_columns)
			. ' FROM ' . $this->get_index_name();

		if (!empty($where))
		{
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}

		if ($group_by !== '')
		{
			$sql .= ' GROUP BY ' . $group_by;
			if ($within_group_order_by !== '')
			{
				$sql .= ' WITHIN GROUP ORDER BY ' . $within_group_order_by;
			}
		}

		if ($order_by !== '')
		{
			$sql .= ' ORDER BY ' . $order_by;
		}

		$sql .= sprintf(' LIMIT %d, %d', $start, $per_page);

		if (!empty($options))
		{
			$option_parts = [];
			foreach ($options as $opt_key => $opt_val)
			{
				$option_parts[] = $opt_key . '=' . $opt_val;
			}
			$sql .= ' OPTION ' . implode(', ', $option_parts);
		}

		return $sql;
	}

	/**
	 * Builds a normalized RT document array from a joined database row.
	 *
	 * @param array<string, mixed> $row
	 * @return array<string, int|string>
	 */
	protected function build_document_from_row(array $row): array
	{
		$post_id = (int) $row['post_id'];
		$clean_subject = $this->clean_post_subject((string) $row['post_subject']);

		return [
			'post_id'				=> $post_id,
			'title'					=> $clean_subject,
			'data'					=> $this->clean_post_text((string) $row['post_text']),
			'forum_id'				=> (int) $row['forum_id'],
			'topic_id'				=> (int) $row['topic_id'],
			'poster_id'				=> (int) $row['poster_id'],
			'post_visibility'		=> (int) $row['post_visibility'],
			'topic_first_post'		=> ($post_id === (int) $row['topic_first_post_id']) ? 1 : 0,
			'post_time'				=> (int) $row['post_time'],
			'topic_last_post_time'	=> (int) $row['topic_last_post_time'],
			'post_subject'			=> $clean_subject,
		];
	}

	/**
	 * Inserts or replaces a batch of documents in the Real-Time index.
	 *
	 * @param array<int, array<string, int|string>> $docs
	 * @return void
	 * @throws sphinx_exception
	 */
	protected function upsert_documents(array $docs): void
	{
		if (empty($docs))
		{
			return;
		}

		$values_sql = [];
		foreach ($docs as $doc)
		{
			$values_sql[] = sprintf(
				'(%d, %s, %s, %d, %d, %d, %d, %d, %d, %d, %s)',
				(int) $doc['post_id'],
				$this->client->quote_string((string) $doc['title']),
				$this->client->quote_string((string) $doc['data']),
				(int) $doc['forum_id'],
				(int) $doc['topic_id'],
				(int) $doc['poster_id'],
				(int) $doc['post_visibility'],
				(int) $doc['topic_first_post'],
				(int) $doc['post_time'],
				(int) $doc['topic_last_post_time'],
				$this->client->quote_string((string) $doc['post_subject'])
			);
		}

		$sql = 'REPLACE INTO ' . $this->get_index_name()
			. ' (id, title, data, forum_id, topic_id, poster_id, post_visibility, topic_first_post, post_time, topic_last_post_time, post_subject)'
			. ' VALUES ' . implode(', ', $values_sql);

		$this->client->execute($sql);
	}

	/**
	 * Cleans post XML/BBCode/HTML content into plain UTF-8 text for RT fulltext indexing.
	 *
	 * @param string $text
	 * @return string
	 */
	protected function clean_post_text(string $text): string
	{
		if ($text === '')
		{
			return '';
		}

		$trimmed = ltrim($text);
		if (str_starts_with($trimmed, '<'))
		{
			try
			{
				$text = $this->text_formatter_utils->clean_formatting($text);
			}
			catch (\Throwable $e)
			{
				$text = strip_tags($text);
			}
		}
		else
		{
			$text = strip_tags($text);
		}

		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
	}

	/**
	 * Cleans post subject HTML entities into plain UTF-8 text.
	 *
	 * @param string $subject
	 * @return string
	 */
	protected function clean_post_subject(string $subject): string
	{
		if ($subject === '')
		{
			return '';
		}

		return trim(html_entity_decode($subject, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	}
}
