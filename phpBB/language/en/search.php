<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : search.php [ English ]
// STARTED   : Sat Dec 16, 2000
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// DO NOT CHANGE
if (empty($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE 
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang += array(
	'ALL_AVAILABLE'			=> 'All available',
	'ALL_RESULTS'			=> 'All results',

	'DISPLAY_RESULTS'		=> 'Display results as',

	'FOUND_SEARCH_MATCH'	=> 'Search found %d match',
	'FOUND_SEARCH_MATCHES'	=> 'Search found %d matches',

	'IGNORED_TERMS'			=> 'ignored',

	'NO_RECENT_SEARCHES'	=> 'No searches have been carried out recently',
	'NO_SEARCH'				=> 'Sorry but you are not permitted to use the search system.',
	'NO_SEARCH_RESULTS'		=> 'No suitable matches were found.',
	'NO_SEARCH_TIME'		=> 'Sorry but you cannot use search at this time. Please try again in a few minutes.',

	'POST_CHARACTERS'		=> 'characters of posts',

	'RECENT_SEARCHES'		=> 'Recent searches',
	'RESULT_DAYS'			=> 'Limit results to previous',
	'RESULT_SORT'			=> 'Sort results by',
	'RETURN_FIRST'			=> 'Return first',

	'SEARCHED_FOR'			=> 'Search term used',
	'SEARCH_ALL_TERMS'		=> 'Search for all terms or use query as entered',
	'SEARCH_ANY_TERMS'		=> 'Search for any terms',
	'SEARCH_AUTHOR'			=> 'Search for Author',
	'SEARCH_AUTHOR_EXPLAIN'	=> 'Use * as a wildcard for partial matches',
	'SEARCH_FORUMS'			=> 'Search in forums',
	'SEARCH_FORUMS_EXPLAIN'	=> 'Select the forum or forums you wish to search in. For speed all subforums can be searched by selecting the parent and setting enable search subforums below.',
	'SEARCH_IN_RESULTS'		=> 'Search these results',
	'SEARCH_KEYWORDS'		=> 'Search for Keywords',
	'SEARCH_KEYWORDS_EXPLAIN'	=> 'Use <b>+</b> for words which must be found, <b>-</b> for words which must not be found and <b>|</b> for words which may or may not be found. Use * as a wildcard for partial matches',
	'SEARCH_MSG_ONLY'		=> 'Message text only',
	'SEARCH_OPTIONS'		=> 'Search Options',
	'SEARCH_QUERY'			=> 'Search Query',
	'SEARCH_SUBFORUMS'		=> 'Search subforums',
	'SEARCH_TITLE_MSG'		=> 'Topic titles and message text',
	'SEARCH_TITLE_ONLY'		=> 'Topic titles only',
	'SEARCH_WITHIN'			=> 'Search within',
	'SORT_ASCENDING'		=> 'Ascending',
	'SORT_AUTHOR'			=> 'Author',
	'SORT_DESCENDING'		=> 'Descending',
	'SORT_FORUM'			=> 'Forum',
	'SORT_POST_SUBJECT'		=> 'Post Subject',
	'SORT_TIME'				=> 'Post Time',
);

?>