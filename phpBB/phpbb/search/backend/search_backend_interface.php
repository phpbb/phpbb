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

interface search_backend_interface
{
	/**
	 * Returns the name of this search backend to be displayed to administrators
	 *
	 * @return string Name
	 */
	public function get_name(): string;

	/**
	 * Returns if the search engine is available
	 *
	 * @return bool
	 */
	public function is_available(): bool;

	/**
	 * Method executed when a search backend is set from acp.
	 *
	 * Checks permissions and paths, if everything is correct it generates the config file
	 *
	 * @return string|false False if everything was ok or string with error message
	 */
	public function init();

	/**
	 * Returns the search_query
	 *
	 * @return string search query
	 */
	public function get_search_query(): string;

	/**
	 * Returns the common_words array
	 *
	 * @return array common words that are ignored by search backend
	 */
	public function get_common_words(): array;

	/**
	 * Returns the word_length array
	 *
	 * @return array|false min and max word length for searching
	 */
	public function get_word_length();

	/**
	 * Splits keywords entered by a user into an array of words stored in $this->split_words
	 * This function fills $this->search_query with the cleaned user search query
	 *
	 * If $terms is 'any' then the words will be extracted from the search query
	 * and combined with | inside brackets. They will afterwards be treated like
	 * an standard search query.
	 *
	 * Then it analyses the query and fills the internal arrays $must_not_contain_ids,
	 * $must_contain_ids and $must_exclude_one_ids which are later used by keyword_search()
	 *
	 * @param	string	$keywords	contains the search query string as entered by the user
	 * @param	string	$terms		is either 'all' (use search query as entered, default words to 'must be contained in post')
	 * 	or 'any' (find all posts containing at least one of the given words)
	 * @return	boolean				false if no valid keywords were found and otherwise true
	 */
	public function split_keywords(string &$keywords, string $terms): bool;

	/**
	 * Performs a search on keywords depending on display specific params. You have to run split_keywords() first
	 *
	 * @param string $type contains either posts or topics depending on what should be searched for
	 * @param string $fields contains either titleonly (topic titles should be searched), msgonly (only message bodies should be searched), firstpost (only subject and body of the first post should be searched) or all (all post bodies and subjects should be searched)
	 * @param string $terms is either 'all' (use query as entered, words without prefix should default to "have to be in field") or 'any' (ignore search query parts and just return all posts that contain any of the specified words)
	 * @param array $sort_by_sql contains SQL code for the ORDER BY part of a query
	 * @param string $sort_key is the key of $sort_by_sql for the selected sorting
	 * @param string $sort_dir is either a or d representing ASC and DESC
	 * @param string $sort_days specifies the maximum amount of days a post may be old
	 * @param array $ex_fid_ary specifies an array of forum ids which should not be searched
	 * @param string $post_visibility specifies which types of posts the user can view in which forums
	 * @param int $topic_id is set to 0 or a topic id, if it is not 0 then only posts in this topic should be searched
	 * @param array $author_ary an array of author ids if the author should be ignored during the search the array is empty
	 * @param string $author_name specifies the author match, when ANONYMOUS is also a search-match
	 * @param array        &$id_ary passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	 * @param int $start indicates the first index of the page
	 * @param int $per_page number of ids each page is supposed to contain
	 * @return    boolean|int                        total number of results
	 */
	public function keyword_search(string $type, string $fields, string $terms, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page);

	/**
	 * Performs a search on an author's posts without caring about message contents. Depends on display specific params
	 *
	 * @param string $type contains either posts or topics depending on what should be searched for
	 * @param boolean $firstpost_only if true, only topic starting posts will be considered
	 * @param array $sort_by_sql contains SQL code for the ORDER BY part of a query
	 * @param string $sort_key is the key of $sort_by_sql for the selected sorting
	 * @param string $sort_dir is either a or d representing ASC and DESC
	 * @param string $sort_days specifies the maximum amount of days a post may be old
	 * @param array $ex_fid_ary specifies an array of forum ids which should not be searched
	 * @param string $post_visibility specifies which types of posts the user can view in which forums
	 * @param int $topic_id is set to 0 or a topic id, if it is not 0 then only posts in this topic should be searched
	 * @param array $author_ary an array of author ids
	 * @param string $author_name specifies the author match, when ANONYMOUS is also a search-match
	 * @param array        &$id_ary passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	 * @param int $start indicates the first index of the page
	 * @param int $per_page number of ids each page is supposed to contain
	 * @return    boolean|int                        total number of results
	 */
	public function author_search(string $type, bool $firstpost_only, array $sort_by_sql, string $sort_key, string $sort_dir, string $sort_days, array $ex_fid_ary, string $post_visibility, int $topic_id, array $author_ary, string $author_name, array &$id_ary, int &$start, int $per_page);

	/**
	 * Returns if phrase search is supported or not
	 *
	 * @return bool
	 */
	public function supports_phrase_search(): bool;

	/**
	 * Updates wordlist and wordmatch tables when a message is posted or changed
	 * Destroys cached search results, that contained one of the new words in a post so the results won't be outdated
	 *
	 * @param string $mode contains the post mode: edit, post, reply, quote ...
	 * @param int $post_id contains the post id of the post to index
	 * @param string $message contains the post text of the post
	 * @param string $subject contains the subject of the post to index
	 * @param int $poster_id contains the user id of the poster
	 * @param int $forum_id contains the forum id of parent forum of the post
	 */
	public function index(string $mode, int $post_id, string &$message, string &$subject, int $poster_id, int $forum_id);

	/**
	 * Destroy cached results, that might be outdated after deleting a post
	 * @param array $post_ids
	 * @param array $author_ids
	 * @param array $forum_ids
	 *
	 * @return void
	 */
	public function index_remove(array $post_ids, array $author_ids, array $forum_ids): void;

	/**
	 * Destroy old cache entries
	 *
	 * @return void
	 */
	public function tidy(): void;

	/**
	 * Create fulltext index
	 *
	 * @param int $post_counter
	 * @return array|null array with current status or null if finished
	 */
	public function create_index(int &$post_counter = 0): ?array;

	/**
	 * Drop fulltext index
	 *
	 * @param int $post_counter
	 * @return array|null array with current status or null if finished
	 */
	public function delete_index(int &$post_counter = 0): ?array;

	/**
	 * Returns true if both FULLTEXT indexes exist
	 *
	 * @return bool
	 */
	public function index_created(): bool;

	/**
	 * Returns an associative array containing information about the indexes
	 *
	 * @return array|false Language string of error false otherwise
	 */
	public function index_stats();

	/**
	 * Display various options that can be configured for the backend from the acp
	 *
	 * @return array array containing template and config variables
	 */
	public function get_acp_options(): array;

	/**
	 * Gets backend class
	 *
	 * @return string
	 */
	public function get_type(): string;
}
