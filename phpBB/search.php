<?php
/***************************************************************************
 *                                search.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

//
// Massive overhaul for phpBB2,
// originally based on search code
// I knocked together for my own website
//
// PSO : 2001
//

include('extension.inc');
include('common.'.$phpEx);
include('includes/bbcode.'.$phpEx);

$pagetype = "search";
$page_title = "Search Forums";

//
// Page specific functions
//
function gensearch_sql($searchstring, $override_all = 0)
{

	$searchchars = array("'[\s]+'", "'\/'", "';'", "'@'", "'&'", "'#'", "'_'", "'|'", "'¬'", "'\*'");
	$replacechars = array(" ", "", "", "", " ", "", "", "", " ", "", "%");

	$searchstring = stripslashes(trim(preg_replace($searchchars, $replacechars, preg_quote(strip_tags($searchstring)))));

	//
	// Here could go a file containing words to ignore, 
	// eg. common words such as the, a, to, etc. or 
	// specific words which should not be search on
	//
	// This is what I actually use on the Typhoon site. The
	// complicated thing here is that on my site I maintain
	// a cleaned out version of all stories with these words removed
	// What could be done here is that all non-phrased search
	// words are matched against words to exclude, removing
	// the possibility of missed matches on certain phrases.
	//

	$words = split(" ", strtolower($searchstring));
	$phrase = false;
	$j = 0;

	for($i = 0; $i < count($words); $i++)
	{
		//
		// First see if we've got a single
		// word enclosed in quotes. If so remove
		// quotes and store word
		//
		// Next see if we've got an opening quote
		// if we do then we assume a phrase is being
		// entered so store first word (if any)
		// and set $phrase to true
		//
		// Next check if we've got a closing quote
		// if so end phrase input
		//
		// Finally store any other word (checking
		// to see if phrase is true (if so store word
		// in the same array position as previous
		// word matches)
		//
		if(preg_match("/^([\+\-]*)\"(.*?)\"/", $words[$i], $word))
		{
			$is_phrase[$j] = true;
			$searchwords[$j] = $word[2];
			if($word[1] == "+" || $word[1] == "-")
				$searchwords[$j] = $word[1] . $searchwords[$j];
			$j++;
		}
		elseif(preg_match("/^(.*?)\"$/", $words[$i], $word))
		{
			$phrase = false;
			 $searchwords[$j] .= " " . $word[1];
			$j++;
		}
		elseif(preg_match("/^([\+\-]*)\"(.*?)$/", $words[$i], $word) && !$override_all)
		{
			$phrase = true;
			$is_phrase[$j] = true;
			$searchwords[$j] = trim($word[2]);
			if($word[1] == "+" || $word[1] == "-")
				$searchwords[$j] = $word[1] . $searchwords[$j];
		}
		else
		{
			if($phrase)
			{
				$searchwords[$j] .= " " . $words[$i];
			}
			else
			{
				$searchwords[$j] = $words[$i];
				$j++;
			}
		}
	}

	if(!$override_all)
	{
		$i = 0;
		$searchtype = "OR";
		$bin_and = $bin_not = $bin_or = false;

		while($i < count($searchwords))
		{
			if($searchwords[$i] == "and" || $searchwords[$i] == "+")
				{
				$searchtype = "AND";
				$bin_and = true;
				$i++;
			}
			elseif(ereg("\+", $searchwords[$i]))
			{
				$searchwords[$i] = ereg_replace("(\+)", "", $searchwords[$i]);
				$searchtype = "AND";
				$bin_and = true;
			}
			elseif($searchwords[$i] == "not" || $searchwords[$i] == "-")
			{
				$searchtype = "NOT";
				$bin_not = true;
				$i++;
			}
			elseif(ereg("\-", $searchwords[$i]))
			{
				$searchwords[$i] = ereg_replace("(\-)", "", $searchwords[$i]);
				$searchtype = "NOT";
				$bin_not = true;
			}
			else
			{
				$searchtype = "OR";
				$bin_or = true;
			}
			$searchwords[$i] = ereg_replace("(\+|\-)", "", $searchwords[$i]);
			$searchforwords[] = trim($searchwords[$i]);
			if( trim($searchwords[$i]) )
			{
				$searchlist_isphrase[$searchtype][] = $is_phrase[$i];
				$searchlistandtype[$searchtype][] = trim($searchwords[$i]);
			}
			$i++;
		}

		if($bin_or)
			$binsearchtype[] = "OR";
		if($bin_and)
			$binsearchtype[] = "AND";
		if($bin_not)
			$binsearchtype[] = "NOT";

		//
		// Search for words (OR AND and NOT arrays)
		// 
		$searchstring = "";
		for($i=0;$i<count($binsearchtype);$i++)
		{
			if($binsearchtype[$i] == "AND" && count($searchlistandtype["AND"]))
			{
				if($i > 0) 
					$searchstring .= ") AND (";
				for($j=0;$j<count($searchlistandtype["AND"]);$j++)
				{
					if($j != 0) 
						$searchstring .= " AND ";
					$findword = addslashes($searchlistandtype["AND"][$j]);
					$is_phrase_word = $searchlist_isphrase["AND"][$j];
					if($is_phrase_word)
					{
						$searchstring .= " ( pt.post_text LIKE '% $findword %' OR pt.post_text LIKE '$findword %' OR pt.post_text LIKE '% $findword' OR pt.post_text LIKE '$findword' )";
					}
					else
					{
						$searchstring .= "pt.post_text LIKE '%$findword%'";
					}
				}
			}
			elseif($binsearchtype[$i] == "OR" && count($searchlistandtype["OR"]))
			{
				if($i > 0) 
					$searchstring .= ") AND (";
				for($j=0;$j<count($searchlistandtype["OR"]);$j++)
				{
					if($j != 0) 
						$searchstring .= " OR ";
					$findword = addslashes($searchlistandtype["OR"][$j]);
					$is_phrase_word = $searchlist_isphrase["OR"][$j];
					if($is_phrase_word)
					{
						$searchstring .= " ( pt.post_text LIKE '% $findword %' OR pt.post_text LIKE '$findword %' OR pt.post_text LIKE '% $findword' OR pt.post_text LIKE '$findword' )";
					}
					else
					{
						$searchstring .= "pt.post_text LIKE '%$findword%'";
					}
				}
			}
			elseif($binsearchtype[$i] == "NOT" && count($searchlistandtype["NOT"]))
			{
				if($i > 0) 
					$searchstring .= ") AND (";
				for($j=0;$j<count($searchlistandtype["NOT"]);$j++)
				{
					if($j != 0) 
						$searchstring .= " AND ";
					$findword = addslashes($searchlistandtype["NOT"][$j]);
					$is_phrase_word = $searchlist_isphrase["NOT"][$j];
					if($is_phrase_word)
					{
						$searchstring .=  " ( pt.post_text NOT LIKE '% $findword %' AND pt.post_text NOT LIKE '$findword %' AND pt.post_text NOT LIKE '% $findword'  AND pt.post_text NOT LIKE '$findword' ) ";
					}
					else
					{
						$searchstring .= "pt.post_text NOT LIKE '%$findword%'";
					}
				}
			}
		}
	}
	else
	{
		$searchstring = "";
		$i = 0;

		while($i < count($searchwords))
		{
			$searchwords[$i] = eregi_replace("(\+)|(\-)|(^and$)|(^or$)|(^not$)|(\")|( )", "", $searchwords[$i]);
			if($i > 0 && $i < count($searchwords) && $searchwords[$i] != "")
				$searchstring .= " AND ";
			if($searchwords[$i] != "")
			{
				$searchstring .= "( pt.post_text LIKE '%".$searchwords[$i]."%' )";
				$searchforwords[] = trim($searchwords[$i]);
			}
			$i++;
		}
	}

	$searchstring =  "WHERE ($searchstring) AND (pt.post_id = p.post_id) ";

	$searchdata[0] = $searchstring;
	for($i = 0; $i < count($searchforwords); $i++)
	{
		$searchdata[$i+1] = $searchforwords[$i];
	}

	return $searchdata;
}
//
// End of functions defns
//


//
// Start of page proper
//

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_SEARCH, $session_length);
init_userprefs($userdata);
//
// End session management
//

$start = (isset($HTTP_GET_VARS['start'])) ? $HTTP_GET_VARS['start'] : 0;

$querystring = (isset($HTTP_POST_VARS['querystring'])) ? $HTTP_POST_VARS['querystring'] : ( (isset($HTTP_GET_VARS['q'])) ? stripslashes($HTTP_GET_VARS['q']) : "" );
$authorstring = (isset($HTTP_POST_VARS['authorstring'])) ? $HTTP_POST_VARS['authorstring'] : ( (isset($HTTP_GET_VARS['a'])) ? stripslashes($HTTP_GET_VARS['a']) : "" );

$return_chars = ($HTTP_POST_VARS['charsreqd'] != "all") ? $HTTP_POST_VARS['charsreqd'] : -1;
$return_chars = (isset($HTTP_GET_VARS['c'])) ? ( ($HTTP_GET_VARS['c']!= "all") ? $HTTP_GET_VARS['c'] : -1 ) : $return_chars; 
$searchall = ($HTTP_POST_VARS['addterms'] == "all") ? 1 : ( ($HTTP_GET_VARS['m'] == "all") ? 1 : 0 );
$searchforum = (isset($HTTP_POST_VARS['searchforum'])) ? $HTTP_POST_VARS['searchforum'] : $HTTP_GET_VARS['f'] ;
$sortby = (isset($HTTP_POST_VARS['sortby'])) ? $HTTP_POST_VARS['sortby'] : $HTTP_GET_VARS['b'];
$sortby_dir = (isset($HTTP_POST_VARS['sortdir'])) ? $HTTP_POST_VARS['sortdir'] : $HTTP_GET_VARS['d'];

//
// Define some globally used data
//
$sortby_types = array("Post Time", "Post Subject", "Topic Title", "Author Name", "Forum");
$sortby_sql = array("p.post_time", "pt.post_subject", "t.topic_title", "u.username", "f.forum_id");

if((isset($HTTP_POST_VARS['dosearch']) || isset($HTTP_GET_VARS['dosearch'])) && (!empty($querystring) || !empty($authorstring)))
{

	//
	// Start building appropriate SQL query
	//
	$sql = "SELECT pt.post_text, pt.post_subject, p.forum_id, p.post_id, p.topic_id, p.post_time, f.forum_name, t.topic_title, t.topic_replies, t.topic_views, u.username, u.user_id 
		FROM ".FORUMS_TABLE." f, ".TOPICS_TABLE." t, ".USERS_TABLE." u, ".POSTS_TEXT_TABLE." pt, ".POSTS_TABLE." p ";

	//
	// If user is logged in then we'll
	// check to see which (if any) private
	// forums they are allowed to view and
	// include them in the search.
	//
	// If not logged in we explicitly prevent
	// searching of private forums
	//

	if($querystring != "")
	{
		$search_sql = "";
		$searchdata = gensearch_sql(stripslashes($querystring), $searchall);
		$search_sql = $searchdata[0];

		if($search_sql != "(  )")
		{
			if($authorstring != "")
			{
				$authorstring = stripslashes($authorstring);
				$search_sql .= ($querystring == "") ? "WHERE u.username LIKE '%$authorstring%'" : " AND (u.username LIKE '%$authorstring%')";
			}
			$sql .= $search_sql." 
				AND (pt.post_id = p.post_id) 
				AND (f.forum_id = p.forum_id) 
				AND (p.topic_id = t.topic_id) 
				AND (p.poster_id = u.user_id)";

			if($searchforum != "all")
				$sql .= " AND (f.forum_id = '$searchforum')";

			$sql .= " ORDER BY ".$sortby_sql[$sortby]." $sortby_dir";
		
			$result = $db->sql_query($sql);
			if(!$result)
			{
				error_die(QUERY_ERROR, "Couldn't obtain search results", __LINE__, __FILE__);
			}
			$searchset = $db->sql_fetchrowset($result);

			//
			// Output header
			//
			include('includes/page_header.'.$phpEx);

			$template->set_filenames(array(
				"body" => "search_results_body.tpl",
				"jumpbox" => "jumpbox.tpl")
			);
			$jumpbox = make_jumpbox();
			$template->assign_vars(array(
				"JUMPBOX_LIST" => $jumpbox,
				"SELECT_NAME" => POST_FORUM_URL)
			);
			$template->assign_var_from_handle("JUMPBOX", "jumpbox");

			$template->assign_vars(array(
				"SEARCH_MATCHES" => count($searchset),

				"L_TOPIC" => $lang['Topic'])
			);
			
			if(count($searchset))
			{

				for($j = 1; $j < count($searchdata); $j++)
				{
					$search_string[] = "'(".preg_quote($searchdata[$j], "'").")'i";
					$replace_string[] = "<font color=\"#CC0000\"><b>\\1</b></font>";
				}

				for($i = $start; $i < min($start + $board_config['posts_per_page'], count($searchset)); $i++)
				{
					$forum_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $searchset[$i]['forum_id']);
					$topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $searchset[$i]['topic_id']);
					$poster_url = append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $searchset[$i]['user_id']);
					$post_url = append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=".$searchset[$i]['post_id']."#".$searchset[$i]['post_id']);

					$post_date = create_date($board_config['default_dateformat'], $searchset[$i]['post_time'], $board_config['default_timezone']);

					$message = stripslashes($searchset[$i]['post_text']);

					if($return_chars != 0 )
					{
						if($return_chars != -1)
						{
							$message = (strlen($message) > $return_chars) ? substr($message, 0, $return_chars) . " ..." : $message;
						}
			
						$message = strip_tags($message);
						//
						// Remove BBCode
						//
						$message = preg_replace("/\[.*\]/", "", $message);

						$message = str_replace("\n", "<br />", $message);
						$message = preg_replace($search_string, $replace_string, $message);

					}
					else
					{
						$message = "";
					}

					$template->assign_block_vars("searchresults", array(
						"TOPIC_TITLE" => stripslashes($searchset[$i]['topic_title']),
						"FORUM_NAME" => stripslashes($searchset[$i]['forum_name']),
						"POST_SUBJECT" => stripslashes($searchset[$i]['post_subject']), 
						"POST_DATE" => $post_date, 
						"POSTER_NAME" => stripslashes($searchset[$i]['username']),
						"TOPIC_REPLIES" => $searchset[$i]['topic_replies'],
						"TOPIC_VIEWS" => $searchset[$i]['topic_views'],
						"MESSAGE" => $message,

						"U_POST" => $post_url,
						"U_TOPIC" => $topic_url,
						"U_FORUM" => $forum_url,
						"U_USER_PROFILE" => $poster_url
					));

				}

				$base_url = "search.php?q=" . urlencode($querystring) . "&a=" . urlencode($authorstring) . "&c=$return_chars&m=$searchall&f=$searchforum&b=" . urlencode($sortby) . "&dosearch=1";

				$template->assign_vars(array(
					"PAGINATION" => generate_pagination($base_url, count($searchset), $board_config['posts_per_page'], $start),
					"ON_PAGE" => (floor($start/$board_config['posts_per_page'])+1),
					"TOTAL_PAGES" => ceil((count($searchset))/$board_config['posts_per_page']),
		
					"L_OF" => $lang['of'],
					"L_PAGE" => $lang['Page'],
					"L_GOTO_PAGE" => $lang['Goto_page'])
				);

				$template->pparse("body");

				include('includes/page_tail.'.$phpEx);
			}
		}
	}
}

//
// This will be replaced by
// an auth function return of
// all accessible forums ... I think
//
$sql = "SELECT forum_name, forum_id 
			FROM ".FORUMS_TABLE."
			ORDER BY cat_id, forum_order";
$result = $db->sql_query($sql);
if(!$result)
{
	error_die(QUERY_ERROR, "Couldn't obtain forum_name/forum_id", __LINE__, __FILE__);
}

$s_forums = "<option value=\"all\">".$lang['All']."</option>";
while($row = $db->sql_fetchrow($result))
{
	$s_forums .= "<option value=\"".$row['forum_id']."\">".$row['forum_name']."</option>";
}

$s_characters = "<option value=\"all\">".$lang['All']."</option>";
$s_characters .= "<option value=\"0\">0</option>";
$s_characters .= "<option value=\"25\">25</option>";
$s_characters .= "<option value=\"50\">50</option>";
for($i = 100; $i < 1100 ; $i += 100)
{
	$s_characters .= "<option value=\"$i\"" . ( ($i == 100) ? "selected>" : ">" ). "$i</option>";
}

$s_sortby = "";
for($i = 0; $i < count($sortby_types); $i++)
{
	$s_sortby .= "<option value=\"$i\">".$sortby_types[$i]."</option>";
}

include('includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "search_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);
$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"JUMPBOX_LIST" => $jumpbox,
	"SELECT_NAME" => POST_FORUM_URL)
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

$s_hidden_fields = "<input type=\"hidden\" name=\"dosearch\" value=\"1\">";

$template->assign_vars(array(
	"L_SEARCH_ANY_TERMS" => $lang['Search_for_any'],
	"L_SEARCH_ALL_TERMS" => $lang['Search_for_all'],
	"L_SEARCH_AUTHOR" => $lang['Search_author'],
	"L_LIMIT_CHARACTERS" => $lang['Limit_chars'],
	"L_SORT_BY" => $lang['Sort_by'],
	"L_SORT_ASCENDING" => $lang['Sort_Ascending'], 
	"L_SORT_DECENDING" => $lang['Sort_Decending'],

	"S_SEARCH_ACTION" => append_sid("search.$phpEx"),
	"S_CHARACTER_OPTIONS" => $s_characters,
	"S_FORUM_OPTIONS" => $s_forums, 
	"S_SORT_OPTIONS" => $s_sortby,
	"S_HIDDEN_FIELDS" => $s_hidden_fields)
);

$template->pparse("body");

include('includes/page_tail.'.$phpEx);

?>