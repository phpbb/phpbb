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

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_SEARCH_INDEX_EXPLAIN'				=> 'Here you can manage the search backend’s indexes. Since you normally use only one backend you should delete all indexes that you do not make use of. After altering some of the search settings (e.g. the number of minimum/maximum chars) it might be worth recreating the index so it reflects those changes.',
	'ACP_SEARCH_SETTINGS_EXPLAIN'			=> 'Here you can define what search backend will be used for indexing posts and performing searches. You can set various options that can influence how much processing these actions require. Some of these settings are the same for all search engine backends.',

	'COMMON_WORD_THRESHOLD'					=> 'Common word threshold',
	'COMMON_WORD_THRESHOLD_EXPLAIN'			=> 'Words which are contained in a greater percentage of all posts will be regarded as common. Common words are ignored in search queries. Set to zero to disable. Only takes effect if there are more than 100 posts. If you want words that are currently regarded as common to be reconsidered you have to recreate the index.',
	'CONFIRM_SEARCH_BACKEND'				=> 'Are you sure you wish to switch to a different search backend? After changing the search backend you will have to create an index for the new search backend. If you don’t plan on switching back to the old search backend you can also delete the old backend’s index in order to free system resources.',
	'CONTINUE_DELETING_INDEX'				=> 'Continue previous index removal process',
	'CONTINUE_DELETING_INDEX_EXPLAIN'		=> 'An index removal process has been started. In order to access the search index page you will have to complete it or cancel it.',
	'CONTINUE_INDEXING'						=> 'Continue previous indexing process',
	'CONTINUE_INDEXING_EXPLAIN'				=> 'An indexing process has been started. In order to access the search index page you will have to complete it or cancel it.',
	'CREATE_INDEX'							=> 'Create index',

	'DELETE_INDEX'							=> 'Delete index',
	'DELETING_INDEX_IN_PROGRESS'			=> 'Deleting the index in progress',
	'DELETING_INDEX_IN_PROGRESS_EXPLAIN'	=> 'The search backend is currently cleaning its index. This can take a few minutes.',

	'FULLTEXT_MYSQL_INCOMPATIBLE_DATABASE'	=> 'The MySQL fulltext backend can only be used with MySQL4 and above.',
	'FULLTEXT_MYSQL_NOT_SUPPORTED'			=> 'MySQL fulltext indexes can only be used with MyISAM or InnoDB tables. MySQL 5.6.4 or later is required for fulltext indexes on InnoDB tables.',
	'FULLTEXT_MYSQL_TOTAL_POSTS'			=> 'Total number of indexed posts',
	'FULLTEXT_MYSQL_MIN_SEARCH_CHARS_EXPLAIN'	=> 'Words with at least this many characters will be indexed for searching. You or your host can only change this setting by changing the mysql configuration.',
	'FULLTEXT_MYSQL_MAX_SEARCH_CHARS_EXPLAIN'	=> 'Words with no more than this many characters will be indexed for searching. You or your host can only change this setting by changing the mysql configuration.',

	'FULLTEXT_POSTGRES_INCOMPATIBLE_DATABASE'	=> 'The PostgreSQL fulltext backend can only be used with PostgreSQL.',
	'FULLTEXT_POSTGRES_TOTAL_POSTS'			=> 'Total number of indexed posts',
	'FULLTEXT_POSTGRES_VERSION_CHECK'		=> 'PostgreSQL version',
	'FULLTEXT_POSTGRES_TS_NAME'				=> 'Text search Configuration Profile:',
	'FULLTEXT_POSTGRES_MIN_WORD_LEN'			=> 'Minimum word length for keywords',
	'FULLTEXT_POSTGRES_MAX_WORD_LEN'			=> 'Maximum word length for keywords',
	'FULLTEXT_POSTGRES_VERSION_CHECK_EXPLAIN'		=> 'This search backend requires PostgreSQL version 8.3 and above.',
	'FULLTEXT_POSTGRES_TS_NAME_EXPLAIN'				=> 'The Text search configuration profile used to determine the parser and dictionary.',
	'FULLTEXT_POSTGRES_MIN_WORD_LEN_EXPLAIN'			=> 'Words with at least this many characters will be included in the query to the database.',
	'FULLTEXT_POSTGRES_MAX_WORD_LEN_EXPLAIN'			=> 'Words with no more than this many characters will be included in the query to the database.',

	'FULLTEXT_SPHINX_CONFIGURE'				=> 'Configure the following settings to generate sphinx config file',
	'FULLTEXT_SPHINX_DATA_PATH'				=> 'Path to data directory',
	'FULLTEXT_SPHINX_DATA_PATH_EXPLAIN'		=> 'It will be used to store the indexes and log files. You should create this directory outside the web accessible directories. (should have a trailing slash)',
	'FULLTEXT_SPHINX_DELTA_POSTS'			=> 'Number of posts in frequently updated delta index',
	'FULLTEXT_SPHINX_HOST'					=> 'Sphinx search daemon host',
	'FULLTEXT_SPHINX_HOST_EXPLAIN'			=> 'Host on which the sphinx search daemon (searchd) listens. Leave empty to use the default localhost',
	'FULLTEXT_SPHINX_INDEXER_MEM_LIMIT'		=> 'Indexer memory limit',
	'FULLTEXT_SPHINX_INDEXER_MEM_LIMIT_EXPLAIN'	=> 'This number should at all times be lower than the RAM available on your machine. If you experience periodic performance problems this might be due to the indexer consuming too many resources. It might help to lower the amount of memory available to the indexer.',
	'FULLTEXT_SPHINX_MAIN_POSTS'			=> 'Number of posts in main index',
	'FULLTEXT_SPHINX_PORT'					=> 'Sphinx search daemon port',
	'FULLTEXT_SPHINX_PORT_EXPLAIN'			=> 'Port on which the sphinx search daemon (searchd) listens. Leave empty to use the default Sphinx API port 9312',
	'FULLTEXT_SPHINX_WRONG_DATABASE'		=> 'The sphinx search for phpBB supports MySQL and PostgreSQL only.',
	'FULLTEXT_SPHINX_CONFIG_FILE'			=> 'Sphinx config file',
	'FULLTEXT_SPHINX_CONFIG_FILE_EXPLAIN'	=> 'The generated content of the sphinx config file. This data needs to be pasted into the sphinx.conf which is used by sphinx search daemon.',
	'FULLTEXT_SPHINX_NO_CONFIG_DATA'		=> 'The sphinx data and config directory paths are not defined. Please define them to generate the config file.',

	'GENERAL_SEARCH_SETTINGS'				=> 'General search settings',
	'GO_TO_SEARCH_INDEX'					=> 'Go to search index page',

	'INDEX_STATS'							=> 'Index statistics',
	'INDEXING_IN_PROGRESS'					=> 'Indexing in progress',
	'INDEXING_IN_PROGRESS_EXPLAIN'			=> 'The search backend is currently indexing all posts on the board. This can take from a few minutes to a few hours depending on your board’s size.',

	'LIMIT_SEARCH_LOAD'						=> 'Search page system load limit',
	'LIMIT_SEARCH_LOAD_EXPLAIN'				=> 'If the 1 minute system load exceeds this value the search page will go offline, 1.0 equals ~100% utilisation of one processor. This only functions on UNIX based servers.',

	'MAX_SEARCH_CHARS'						=> 'Max characters indexed by search',
	'MAX_SEARCH_CHARS_EXPLAIN'				=> 'Words with no more than this many characters will be indexed for searching.',
	'MAX_NUM_SEARCH_KEYWORDS'				=> 'Maximum number of allowed keywords',
	'MAX_NUM_SEARCH_KEYWORDS_EXPLAIN'		=> 'Maximum number of words the user is able to search for. A value of 0 allows an unlimited number of words.',
	'MIN_SEARCH_CHARS'						=> 'Min characters indexed by search',
	'MIN_SEARCH_CHARS_EXPLAIN'				=> 'Words with at least this many characters will be indexed for searching.',
	'MIN_SEARCH_AUTHOR_CHARS'				=> 'Min author name characters',
	'MIN_SEARCH_AUTHOR_CHARS_EXPLAIN'		=> 'Users have to enter at least this many characters of the name when performing a wildcard author search. If the author’s username is shorter than this number you can still search for the author’s posts by entering the complete username.',

	'PROGRESS_BAR'							=> 'Progress bar',

	'SEARCH_GUEST_INTERVAL'					=> 'Guest search flood interval',
	'SEARCH_GUEST_INTERVAL_EXPLAIN'			=> 'Number of seconds guests must wait between searches. If one guest searches all others have to wait until the time interval passed.',
	'SEARCH_INDEX_CREATE_REDIRECT'			=> array(
		2	=> 'All posts up to post id %2$d have now been indexed, of which %1$d posts were within this step.<br />',
	),
	'SEARCH_INDEX_CREATE_REDIRECT_RATE'		=> array(
		2	=> 'The current rate of indexing is approximately %1$.1f posts per second.<br />Indexing in progress…',
	),
	'SEARCH_INDEX_DELETE_REDIRECT'			=> array(
		2	=> 'All posts up to post id %2$d have been removed from the search index.<br />Deleting in progress…',
	),
	'SEARCH_INDEX_CREATED'					=> 'Successfully indexed all posts in the board database.',
	'SEARCH_INDEX_REMOVED'					=> 'Successfully deleted the search index for this backend.',
	'SEARCH_INTERVAL'						=> 'User search flood interval',
	'SEARCH_INTERVAL_EXPLAIN'				=> 'Number of seconds users must wait between searches. This interval is checked independently for each user.',
	'SEARCH_STORE_RESULTS'					=> 'Search result cache length',
	'SEARCH_STORE_RESULTS_EXPLAIN'			=> 'Cached search results will expire after this time, in seconds. Set to 0 if you want to disable search cache.',
	'SEARCH_TYPE'							=> 'Search backend',
	'SEARCH_TYPE_EXPLAIN'					=> 'phpBB allows you to choose the backend that is used for searching text in post contents. By default the search will use phpBB’s own fulltext search.',
	'SWITCHED_SEARCH_BACKEND'				=> 'You switched the search backend. In order to use the new search backend you should make sure that there is an index for the backend you chose.',

	'TOTAL_WORDS'							=> 'Total number of indexed words',
	'TOTAL_MATCHES'							=> 'Total number of word to post relations indexed',

	'YES_SEARCH'							=> 'Enable search facilities',
	'YES_SEARCH_EXPLAIN'					=> 'Enables user facing search functionality including member search.',
	'YES_SEARCH_UPDATE'						=> 'Enable fulltext updating',
	'YES_SEARCH_UPDATE_EXPLAIN'				=> 'Updating of fulltext indexes when posting, overridden if search is disabled.',
));
