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
/*

/* ====================================================================
 * Copyright (c) 2000 Steven Haryanto.  All rights reserved.
 *
 * KwIndex
 *   A PHP module to do full text indexing with MySQL database
 * 
 * This module is released under the GNU General Public License. See:
 *   http://www.gnu.org/copyleft/gpl.html
 * 
 * Version
 *   0.04, Sat Dec  2 14:54:41 2000
 *   based on DBIx::KwIndex 0.03
 *
 * For documentation, support and latest version, visit:
 *   http://steven.haryan.to/php/KwIndex.html
 * 
 * ====================================================================
 *
 */
/*

class KwIndex {

	# CONSTRUCTOR
	#############

	function KwIndex($args) {
		# check for argument type
		if (!is_array(&$args))
			die("KwIndex: constructor: syntax: KwIndex(array \$args)");

		# check for unknown arguments
		$known_arguments = array_flip(array(
			"linkid", "db_name", "hostname", "username", "password", 
			"index_name", "wordlist_cardinality", "doclist_cardinality", 
			"stoplist_cardinality", "vectorlist_cardinality",
			"max_word_length", "use_persistent_connection"));
		while(list($k,$v) = each($args))
			if (!isset($known_arguments[$k]))
				die("KwIndex: constructor: unknown argument `$k'");

		# required for required arguments
		if (!isset($args["db_name"]))
			die("KwIndex: constructor: You must specify 'db_name'");
		if (!isset($args["linkid"]) &&
		    (!isset($args["hostname"]) || !isset($args["username"]) || 
		     !isset($args["password"])))
			die("KwIndex: constructor: You must either specify 'linkid' or ".
			    "arguments to mysql_connect ('hostname', 'username', and ".
				   "'password')");

		# supply default values for optional arguments
		if (!isset($args["index_name"]))
			$args["index_name"] = "kwindex";
		if (!isset($args["wordlist_cardinality"]))
			$args["wordlist_cardinality"] = 100000;
		if (!isset($args["stoplist_cardinality"]))
			$args["stoplist_cardinality"] = 10000;
		if (!isset($args["vectorlist_cardinality"]))
			$args["vectorlist_cardinality"] = 100000000;
		if (!isset($args["doclist_cardinality"]))
			$args["doclist_cardinality"] = 1000000;
		if (!isset($args["max_word_length"]))
			$args["max_word_length"] = 32;
		if (!isset($args["use_persistent_connection"]))
			$args["use_persistent_connection"] = 1;

		# set object attributes
		$this->db_name = $args["db_name"];
		$this->index_name = $args["index_name"];
		$this->wordlist_cardinality = $args["wordlist_cardinality"];
		$this->stoplist_cardinality = $args["stoplist_cardinality"];
		$this->vectorlist_cardinality = $args["vectorlist_cardinality"];
		$this->doclist_cardinality = $args["doclist_cardinality"];
		$this->max_word_length = $args["max_word_length"];

		if (!isset($args["linkid"])) {
			if ($args["use_persistent_connection"]) {
				$linkid = mysql_pconnect($args["hostname"], $args["username"],
				                         $args["password"]);
			} else {
				$linkid = mysql_connect($args["hostname"], $args["username"],
				                        $args["password"]);
			}
			if (!$linkid)
				die("KwIndex: constructor: Can't connect to database: ".
				    mysql_error());
		} else {
			$linkid = $args["linkid"];
		}

		$this->linkid = $linkid;
		$idx = $this->index_name;

		if (!mysql_select_db($this->db_name, $linkid)) 
			die("KwIndex: constructor: Can't select DB: ".
			    mysql_error($linkid));

		if (!$this->_index_exists()) $this->_create_index();

		# load stoplist as keys of array
		$this->stoplist = array();
		if (!($res = mysql_query("SELECT word FROM ${idx}_stoplist",
		                         $linkid)))
			die("KwIndex: constructor: Can't load stoplist: ".
			    mysql_error($linkid));
		while($row = mysql_fetch_row($res)) {
			$this->stoplist[ strtolower($row[0]) ] = 1;
		}
	} // constructor


	# PUBLIC METHODS
	################

	function &document_sub($doc_ids) {
		die("KwIndex: document_sub: this method must be overriden");
	}

	function add_document($doc_ids) {
		if (!is_array(&$doc_ids))
			die("KwIndex: syntax: add_document(array \$doc_ids)");

		if (!sizeof(&$doc_ids)) return 1;

		$wordlist = array();
			# structure: ( 'word1' => [ [doc_id,freq], ... ], ... )
		$doclist = array();
			# format: ( doc_id => n, ... ); # n = number of words in document

		# retrieve documents
		####################
		$docs = $this->document_sub(&$doc_ids);

		if(!is_array(&$docs))
			die("KwIndex: add_document: ".
			    "'document_sub' does not return an array");
		if (sizeof(&$doc_ids) < sizeof(&$docs))
			die("KwIndex: add_document: ".
			    "'document_sub' does not return enough documents");
		if (sizeof(&$doc_ids) > sizeof(&$docs))
			die("KwIndex: add_document: ".
			    "'document_sub' returns too many documents");
    
		# split documents into words
		############################
		while(list($id, $doc) = each($docs)) {
			if (!isset($doc) || !strlen($doc)) continue;
    	
			$words = $this->_split_to_words($doc);
			$num_of_words = sizeof(&$words);
				# note: this means that numbers, etc are counted
			$doclist[$id] = $num_of_words;

			# filter non-qualifying words: 1-char length, numbers, words
			# that are too long

			$w2 = array();
			while(list($k, $v) = each($words)) {
				$len = strlen($v);
				$lower_v = strtolower($v);
				if ($len > 1 &&
				    $len <= $this->max_word_length && 
				    preg_match("/[a-z]/", $lower_v) &&
				    !isset($this->stoplist[$lower_v])) ++$w2[ $lower_v ];
			}

			while(list($k, $v) = each($w2)) {
				$lower_k = strtolower($k); 
				if (!isset($wordlist[$lower_k]))
					$wordlist[$lower_k] = array();
				array_push($wordlist[$lower_k], array($id, $v/$num_of_words));
			}
		}

#_debug("wordlist: ", Dumper($wordlist));

		# submit to database
		####################
		$linkid = $this->linkid;
		$idx = $this->index_name;

		# lock the tables in case some other process remove a certain word
		# between step 0 and 1 and 2 and 3
		if(!mysql_query("LOCK TABLES ${idx}_doclist WRITE, ".
		                            "${idx}_vectorlist WRITE, ".
		                            "${idx}_wordlist WRITE",
		                $linkid)) {
			$this->ERROR = "Can't lock tables when adding documents: ".
			                mysql_error($linkid);
			return;
		}

		# 0
		# add the docs first
#_debug( "doclist = ", Dumper($doclist));
		while(list($k,$v) = each($doclist)) {
			if(!mysql_query("REPLACE INTO ${idx}_doclist (id,n) VALUES (".
			                   "'".(addslashes($k))."'".
			                   ",".
			                   "'".(addslashes($v))."'".
			                   ")",
		                    $linkid)) {
				$this->ERROR = "Can't add doc id=`$_' to doclist: ".
				               mysql_error($linkid);
				mysql_query("UNLOCK TABLES", $linkid);
				return;
			}
		}

		# 1
		# and then add the words 
		while(list($k,$v) = each($wordlist)) {
			if(!mysql_query("INSERT IGNORE INTO ${idx}_wordlist (word) ".
			                        "VALUES (".
			                           "'".(addslashes($k))."'".
			                        ")",
		                    $linkid)) {
				$this->ERROR = "Can't add word `$k' to wordlist: ".
				               mysql_error($linkid);
				mysql_query("UNLOCK TABLES", $linkid);
				return;
			}
		}

		# 2
		# get the resulting word ids
		$word_ids = array();
		$e_wordlist = array();
		reset($wordlist);
		while(list($k,$v) = each($wordlist))
			array_push($e_wordlist, "'".addslashes($k)."'");

		if (sizeof(&$wordlist)) {
			if(!($res = mysql_query("SELECT id,word FROM ${idx}_wordlist ".
			                           "WHERE word IN (".
			                              join(',', $e_wordlist).
			                           ")",
			                        $linkid))) {
				$this->ERROR = "Can't get data from wordlist: ".
				               mysql_error($linkid);
				mysql_query('UNLOCK TABLES', $linkid);
				return;
			}
			while($row = mysql_fetch_row($res)) {
#echo "row = ", Dumper($row), "<br>\n";
				$word_ids[ $row[1] ] = $row[0];
			}
		}

		# 3
		# now add the vectors
#_debug("word_ids = ", Dumper($word_ids));
		reset($wordlist);
		while (list($word, $hitlist) = each($wordlist)) {
			while(list($i, $hit) = each($hitlist)) {
#echo "adding word=$word, hit = ($hit[0], $hit[1])<br>\n";
				if(!mysql_query("INSERT INTO ${idx}_vectorlist (wid,did,f)".
				                  "VALUES (".
				                     ("'".addslashes($word_ids[$word]))."',".
				                     ("'".addslashes($hit[0]))."',".
				                     ("'".addslashes($hit[1]))."')",
				                $linkid)) {
					$this->ERROR = "Can't add to vectorlist: ".
					               mysql_error($linkid);
					mysql_query('UNLOCK TABLES', $linkid);
					return;
				}
			}
		}

		# if all goes well, return TRUE
		mysql_query('UNLOCK TABLES', $linkid);
		return 1;
	}


	function remove_document($doc_ids) {
		if (!is_array(&$doc_ids))
			die("KwIndex: syntax: remove_document(array \$doc_ids)");

		if (!sizeof(&$doc_ids)) return 1;

		$linkid = $this->linkid;
		$idx = $this->index_name;

		if(!mysql_query("LOCK TABLES ${idx}_doclist WRITE, ".
		                            "${idx}_vectorlist WRITE",
		                $linkid)) {
			$this->ERROR = "Can't lock tables when removing documents: ".
			                mysql_error($linkid);
			return;
		}

		if(!mysql_query("DELETE FROM ${idx}_doclist WHERE id IN (".
		                   join(',', $doc_ids).")",
		                $linkid)) {
			$this->ERROR = "Can't delete from doclist: ".
			               mysql_error($linkid);
			mysql_query('UNLOCK TABLES', $linkid);
			return;
		}

		if(!mysql_query("DELETE FROM ${idx}_vectorlist WHERE did IN (".
		                   join(',', $doc_ids).")",
		                $linkid)) {
			$this->ERROR = "Can't delete from vectorlist: ".
			               mysql_error($linkid);
			mysql_query('UNLOCK TABLES', $linkid);
			return;
		}

		# if all goes well, return TRUE
		mysql_query("UNLOCK TABLES", $linkid);
		return 1;
	}


	function update_document($doc_ids) {
		if (!is_array(&$doc_ids))
			die("KwIndex: syntax: update_document(array \$doc_ids)");

		if (!sizeof(&$doc_ids)) return 1;

		return $this->remove_document(&$doc_ids) && 
		       $this->add_document(&$doc_ids);
	}


	# find all words that are contained in at least $k % of all documents

	function &common_word($k = 80) {
		$linkid = $this->linkid;
		$idx = $this->index_name;

		# first select the number of documents
		$num = $this->document_count();
		if ($num == -1) {
			$this->ERROR = "Can't retrieve the number of documents: ".
			               mysql_error($linkid);
			return;
		}

		# get the statistics from vectorlist
		if(!($res1 = mysql_query("SELECT wid,COUNT(*)/$num as k FROM
		                             ${idx}_vectorlist GROUP BY wid HAVING k>=".
		                             ($k/100),
		                         $linkid))) {
			$this->ERROR = "Can't retrieve common words: ".
			               mysql_error($linkid);
			return;
		}
		$wids = array();
		while($row = mysql_fetch_row($res1)) array_push($wids, $row[0]);

		# convert it to word by consulting the wordlist table
		$words = array();
		if (sizeof(&$wids)) {
			if (!($res2 = mysql_query("SELECT word FROM ${idx}_wordlist ".
			                             "WHERE id IN (".join(',', $wids).
			                              ")",
			                          $linkid))) {
				$this->ERROR = "Can't retrieve common words: ".
				               mysql_error($linkid);
				return;
			}

			while($row = mysql_fetch_row($res2))
				array_push($words, $row[0]);
		}

		return $words;
	}


	# find all words that are not contained in all documents (vectorlist)
	# XXX not yet written

	function &orphan_word() {
		return array();
	}


	# remove words from index

	function remove_word($words) {
		if (!is_array(&$words))
			die("KwIndex: syntax: remove_word(array \$words)");

		$linkid = $this->linkid;
		$idx = $this->index_name;
	
		if(!mysql_query("LOCK TABLES ${idx}_wordlist WRITE, ".
		                            "${idx}_vectorlist WRITE",
		                $linkid)) {
			$this->ERROR = "Can't lock tables when removing words: ".
			                mysql_error($linkid);
			return;
		}

		$e_words = array();
		while(list($k,$v) = each($words))
			array_push($e_words, "'".addslashes(strtolower($v))."'");

		# retrieve word ids
		if(!($res0 = mysql_query("SELECT id FROM ${idx}_wordlist WHERE ".
		                            "word IN (".join(',', $e_words).")",
		                         $linkid))) {
			$this->ERROR = "Can't delete from wordlist: ".
			               mysql_error($linkid);
			mysql_query('UNLOCK TABLES', $linkid);
			return;
		}
		
		$word_ids = array();
		while($row = mysql_fetch_row($res0))
			array_push($word_ids, $row[0]);
		$word_ids = join(',', $word_ids); # we'll make it a string

		if (!strlen($word_ids)) return 1;
	
		# delete from wordlist
		if(!mysql_query("DELETE FROM ${idx}_wordlist WHERE id IN ".
		                   "($word_ids)",
		                $linkid)) {
			$this->ERROR = "Can't delete from wordlist: ".
			               mysql_error($linkid);
			mysql_query("UNLOCK TABLES", $linkid);
			return;
		}
		if(!mysql_query("DELETE FROM ${idx}_vectorlist WHERE wid IN ".
		                   "($word_ids)", $linkid)) {
			$this->ERROR = "Can't delete from vectorlist: ".
			               mysql_error($linkid);
			mysql_query("UNLOCK TABLES", $linkid);
			return;
		}

		return 1;
	}

	# add stop words. note: you must manually delete previously indexed
	# words with delete_word()

	function add_stop_word($words) {
		if (!is_array(&$words))
			die("KwIndex: syntax: add_stop_word(array words)");
			
		if (!sizeof(&$words)) return 1;
		
		$linkid = $this->linkid;
		$idx = $this->index_name;
	
		while(list($k, $v) = each($words)) {
			$lv = strtolower($v);
			if(!mysql_query("REPLACE INTO ${idx}_stoplist (word) VALUES (".
			                   ("'".addslashes($lv)."'").
			                   ")", $linkid)) {
				$this->ERROR = "Can't add to stoplist: ".
				               mysql_error($linkid);
				return;
			}
			$this->stoplist[ $lv ] = 1;
		}

		return 1;
	}

	# remove stop words from index

	function remove_stop_word($words) {
		if (!is_array(&$words))
			die("KwIndex: syntax: remove_stop_word(array words)");

		if (!sizeof(&$words)) return 1;
		
		$linkid = $this->linkid;
		$idx = $this->index_name;

		$e_words = array();
		while(list($k,$v) = each($words))
			array_push($e_words, "'".addslashes(strtolower($v))."'");
		 
		if(!mysql_query("DELETE FROM ${idx}_stoplist WHERE word IN (".
		                   join(',', $e_words).")",
		                $linkid)) {
			$this->ERROR = "Can't delete from stoplist: ".
			               mysql_error($linkid);
			return;
		}

		while(list($k,$v) = each($words))
			unset($this->stoplist[ strtolower($v) ]);
		
		return 1;
	}

	function is_stop_word($word) {
		return isset($this->stoplist[ strtolower($word) ]);
	}

	function &_search_or_match_count($is_count, &$args) {
		if (!isset($args["words"]))
			die("KwIndex: search: option 'words' must be defined");

		$linkid = $this->linkid;
		$idx = $this->index_name;

		# split the words if we are offered a single string/not array
		# (assume it's a phrase)

		$words = array();
		if (is_array(&$args["words"])) {
			$words = $args["words"];
		} else {
			$words = $this->_split_to_words($args["words"]);
		}

		# delete duplicate words, convert them all to lowercase
		$hashwords = array();
		while(list($k,$v) = each($words)) $hashwords[ strtolower($v) ] = 1;
		$words = array_keys(&$hashwords);
		
		if (!sizeof(&$words)) {
			if ($is_count) {
				return 0;
			} else {
				return array();
			}
		}

		# first we retrieve the word ids
		$op = $args["re"] ? 'REGEXP':'LIKE';
		$bool = isset($args['boolean']) && $args['boolean'] &&
		        strtoupper($args['boolean']) == 'AND' ? 'AND':'OR';
		
		$op_phrases = array();
		while(list($k,$v) = each($words))
			array_push($op_phrases, "word $op '".addslashes($v)."'");
		
		if (!($res0 = mysql_query("SELECT id FROM ${idx}_wordlist WHERE ".
		                             join(' OR ', $op_phrases),
		                          $linkid))) {
			$this->ERROR = "Can't retrieve word ids: ".mysql_error($linkid);
			return;
		}
		$word_ids = array();
		while($row = mysql_fetch_row($res0)) array_push($word_ids, $row[0]);
		
		if (!sizeof(&$word_ids) || 
		    ($bool == 'AND' && sizeof(&$word_ids) < sizeof(&$words))) {
			if ($is_count) {
				return 0;
			} else {
				return array();
			}
		}

		# and then we search the vectorlist
		$can_optimize=0;
		$stmt = '';

		if ($is_count) {

			if ($bool == 'AND' && !$args['re']) {
				$stmt = 'SELECT did,count(wid) as c '.
			    	    "FROM ${idx}_vectorlist WHERE wid IN (".
				           join(',',$word_ids).
				         ") ".
				        "GROUP BY did ".
				        "HAVING c >= ".sizeof(&$word_ids);
			} else {
				$can_optimize=1;
				$stmt = "SELECT COUNT(DISTINCT did) ".
				        "FROM ${idx}_vectorlist WHERE wid IN (".
				           join(',',$word_ids).
				        ")";
			}

		} else { // ! $is_count

			$stmt = "SELECT did, count(wid) as c, avg(f) as a, ".
			               "count(wid)*count(wid)*count(wid)*avg(f) as ca ".

			        "FROM ${idx}_vectorlist WHERE wid IN (".
			            join(',',$word_ids).
			        ") ".

			        "GROUP BY did ".
			        ($bool == 'AND' && !$args['re'] ? 
			        "HAVING c >= ".sizeof(&$word_ids):'').
			        " ORDER BY ca DESC ".
			        (isset($args['num']) ? "LIMIT " . (isset($args['start']) ?
			        (($args['start'] - 1).",".$args['num']) : $args['num'])
			        :'');

		}

_debug("search SQL: ", $stmt);

		if(!($res = mysql_query($stmt, $linkid))) {
			$this->ERROR = "Can't search vectorlist: ".mysql_error($linkid);
			return;
		}

		if ($is_count) {

			if ($can_optimize) {
				$row = mysql_fetch_row($res);
				return $row[0];
			} else {
				return mysql_num_rows($res);
			}
	
		} else { // ! $is_count

			$doc_ids = array();
			while($row = mysql_fetch_row($res)) array_push($doc_ids, $row[0]);
			return $doc_ids;
		}
	}

	function &search($args) {
		return $this->_search_or_match_count(0, &$args);
	}

	function &match_count($args) {
		return $this->_search_or_match_count(1, &$args);
	}

	function remove_index() {
		$linkid = $this->linkid;
    	$idx = $this->index_name;

		if(!mysql_query("DROP TABLE IF EXISTS ${idx}_wordlist", $linkid)) {
			$this->ERROR = "Can't remove table ${idx}_wordlist: ".
			               mysql_error($linkid);
			return;
		}
		if(!mysql_query("DROP TABLE IF EXISTS ${idx}_doclist", $linkid)) {
			$this->ERROR = "Can't remove table ${idx}_doclist: ".
			               mysql_error($linkid);
			return;
		}
		if(!mysql_query("DROP TABLE IF EXISTS ${idx}_vectorlist", $linkid)) {
			$this->ERROR = "Can't remove table ${idx}_vectorlist: ".
			               mysql_error($linkid);
			return;
		}
		if(!mysql_query("DROP TABLE IF EXISTS ${idx}_stoplist", $linkid)) {
			$this->ERROR = "Can't remove table ${idx}_stoplist: ".
			               mysql_error($linkid);
			return;
		}
		
		return 1;
	}

	function empty_index() {
		return $this->remove_index() && $this->_create_index();
	}

	# number of documents in the collection
	function document_count() {
		$linkid = $this->linkid;
    	$idx = $this->index_name;
	    
   		if (!($res = mysql_query("SELECT COUNT(*) FROM ${idx}_doclist", 
   		                         $linkid))) return;
   		$row = mysql_fetch_row($res);
   		return $row[0];
	}

	# number of unique words 
	function word_count() {
		$linkid = $this->linkid;
		$idx = $this->index_name;
	    
		if (!($res = mysql_query("SELECT COUNT(*) FROM ${idx}_wordlist", 
		                         $linkid))) return;
		$row = mysql_fetch_row($res);
		return $row[0];
	}


	# PRIVATE METHODS
	#################

	function &_split_to_words($str) {
		preg_match_all("/\b(\w[\w']*\w+|\w+)\b/", $str, $matches);
		return $matches[1];
	}

	function _create_index() {
		$linkid = $this->linkid;
    	$idx = $this->index_name;

		$stmt = '';

		# drop previous tables, if they exist
		if (!$this->remove_index()) return;

		# create doclist table
		$stmt = "CREATE TABLE ${idx}_doclist " . 
		        '(id ' . $this->_int_column_type($this->doclist_cardinality).
		        ' AUTO_INCREMENT PRIMARY KEY, ' .
		        ' n ' . $this->_int_column_type($this->wordlist_cardinality).
		        ' NOT NULL'.
		        ')';
		if (!mysql_query($stmt, $linkid))
			die("KwIndex: Can't create table ${idx}_doclist: ".
			    mysql_error($linkid));
		
		# create wordlist table
		$stmt = "CREATE TABLE ${idx}_wordlist " . 
		        '(id ' . $this->_int_column_type($this->wordlist_cardinality) .
		        ' AUTO_INCREMENT PRIMARY KEY, ' .
		        " word VARCHAR($this->max_word_length)" .
		        ' BINARY NOT NULL, ' .
		        "UNIQUE (word) " .
		        ')';
		if(!mysql_query($stmt, $linkid))
			die("KwIndex: Can't create table ${idx}_wordlist: ".
			    mysql_error($linkid));

		# create stoplist table
		$stmt = "CREATE TABLE ${idx}_stoplist " .
		        '(id ' . $this->_int_column_type($this->wordlist_cardinality).
		        ' AUTO_INCREMENT PRIMARY KEY, ' .
		        " word VARCHAR($this->max_word_length)" .
		        ' BINARY NOT NULL, ' .
		        "UNIQUE (word) " .
		        ')';
		if (!mysql_query($stmt, $linkid)) 
			die("KwIndex: Can't create table ${idx}_stoplist: ".
			    mysql_error($linkid));

		# create vectorlist table
		$stmt = "CREATE TABLE ${idx}_vectorlist " .
		        '(wid '. $this->_int_column_type($this->wordlist_cardinality). 
		        ' NOT NULL, ' .
		        'did '. $this->_int_column_type($this->doclist_cardinality). 
		        ' NOT NULL, '.
		        'UNIQUE (wid,did), '.
		        'f FLOAT(10,4) NOT NULL'.
		        ')';
		if (!mysql_query($stmt, $linkid)) {
			die("KwIndex: Can't create table ${idx}_stoplist: ".
			    mysql_error($linkid));
    	}
    	
		$this->stoplist = array();

		return 1;
	}

	function _int_column_type($cardinality) {
		if ($cardinality >= 16*1024*1024) return 'INT UNSIGNED';
		if ($cardinality >= 64*1024) return 'MEDIUMINT UNSIGNED';
		if ($cardinality >= 256) return 'SMALLINT UNSIGNED';
		return 'TINYINT UNSIGNED';
	}

	function &_index_tables() {
		$idx = $this->index_name;

		$tables = array(
			"${idx}_doclist",
			"${idx}_wordlist",
			"${idx}_vectorlist",
			"${idx}_stoplist");

		return $tables;
	}

	function _index_exists() {
		$linkid = $this->linkid;
    	$idx = $this->index_name;

		if (!($res = mysql_list_tables($this->db_name, $linkid)))
			die("Can't list table: ".mysql_error($linkid));
		
		$existing_tables = array();
		$i = 0;
		while ($i < mysql_num_rows($res)) {
			$existing_tables[ mysql_tablename($res, $i) ] = 1;
			$i++;
		}

		$index_tables = $this->_index_tables();
		while(list($k,$v) = each($index_tables)) {
			if (!isset($existing_tables[$v])) return 0;
		}
		
		return 1;
	}

} // class

*/

//
// Massive overhaul for phpBB2, originally based on search code
// I knocked together for my own website
//
// PSO : 2001
//
$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

// -----------------------
// Page specific functions
//
function gensearch_sql($searchstring, $override_all = 0)
{

	$searchchars = array("'[\s]+'", "'\/'", "';'", "'@'", "'#'", "'_'", "'|'", "'¬'", "'\*'");
	$replacechars = array(" ", "", "", "", " ", "", "", " ", "", "%");

	$searchstring = trim(preg_replace($searchchars, $replacechars, strip_tags($searchstring)));

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
		// First see if we've got a single word enclosed in quotes. If so remove
		// quotes and store word
		//
		// Next see if we've got an opening quote if we do then we assume a phrase is
		// being entered so store first word (if any) and set $phrase to true
		//
		// Next check if we've got a closing quote if so end phrase input
		//
		// Finally store any other word (checking to see if phrase is true (if so
		// store word in the same array position as previous word matches)
		//
		if(preg_match("/^([\+\-]*)\"(.*?)\"/", $words[$i], $word))
		{
			$is_phrase[$j] = true;
			$searchwords[$j] = $word[2];
			if($word[1] == "+" || $word[1] == "-")
			{
				$searchwords[$j] = $word[1] . $searchwords[$j];
			}
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
			{
				$searchwords[$j] = $word[1] . $searchwords[$j];
			}
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
		{
			$binsearchtype[] = "OR";
		}
		if($bin_and)
		{
			$binsearchtype[] = "AND";
		}
		if($bin_not)
		{
			$binsearchtype[] = "NOT";
		}

		//
		// Search for words (OR AND and NOT arrays)
		//
		$searchstring = "";
		for($i = 0; $i < count($binsearchtype); $i++)
		{
			if($binsearchtype[$i] == "AND" && count($searchlistandtype["AND"]))
			{
				if($i > 0)
				{
					$searchstring .= ") AND (";
				}
				for($j = 0; $j < count($searchlistandtype["AND"]); $j++)
				{
					if($j != 0)
					{
						$searchstring .= " AND ";
					}
					$findword = $searchlistandtype["AND"][$j];

					$searchstring .= " ( pt.post_text LIKE '$findword')";
				}// OR pt.post_text LIKE '$findword %' OR pt.post_text LIKE '% $findword'
			}
			elseif($binsearchtype[$i] == "OR" && count($searchlistandtype["OR"]))
			{
				if($i > 0)
				{
					$searchstring .= ") AND (";
				}
				for($j = 0; $j < count($searchlistandtype["OR"]); $j++)
				{
					if($j != 0)
					{
						$searchstring .= " OR ";
					}
					$findword = $searchlistandtype["OR"][$j];

					$searchstring .= " ( pt.post_text LIKE '$findword' )";
				}// OR pt.post_text LIKE '$findword %' OR pt.post_text LIKE '% $findword'
			}
			elseif($binsearchtype[$i] == "NOT" && count($searchlistandtype["NOT"]))
			{
				if($i > 0)
				{
					$searchstring .= ") AND (";
				}
				for($j = 0; $j < count($searchlistandtype["NOT"]); $j++)
				{
					if($j != 0)
					{
						$searchstring .= " AND ";
					}
					$findword = $searchlistandtype["NOT"][$j];

					$searchstring .=  " ( pt.post_text NOT LIKE '% $findword %' AND pt.post_text NOT LIKE '$findword %' AND pt.post_text NOT LIKE '% $findword' AND pt.post_text NOT LIKE '$findword' ) ";
				}//
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
			{
				$searchstring .= " AND ";
			}
			if($searchwords[$i] != "")
			{
				$searchstring .= "( pt.post_text LIKE '%".$searchwords[$i]."%' )";
				$searchforwords[] = trim($searchwords[$i]);
			}
			$i++;
		}
	}

	$searchstring =  "( $searchstring )";

	$searchdata[0] = $searchstring;
	for($i = 0; $i < count($searchforwords); $i++)
	{
		$searchdata[$i+1] = $searchforwords[$i];
	}

	return $searchdata;
}
//
// End of functions defns
// ----------------------

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_SEARCH, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Define initial vars
//
if( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = "";
}

if( isset($HTTP_POST_VARS['search_keywords']) || isset($HTTP_GET_VARS['search_keywords']) )
{
	$query_keywords = ( isset($HTTP_POST_VARS['search_keywords']) ) ? $HTTP_POST_VARS['search_keywords'] : $HTTP_GET_VARS['search_keywords'];
}
else
{
	$query_keywords = "";
}

if( isset($HTTP_POST_VARS['search_author']) || isset($HTTP_GET_VARS['search_author']) )
{
	$query_author = ( isset($HTTP_POST_VARS['search_author']) ) ? $HTTP_POST_VARS['search_author'] : $HTTP_GET_VARS['search_author'];
}
else
{
	$query_author = "";
}

$search_id = ( isset($HTTP_GET_VARS['search_id']) ) ? $HTTP_GET_VARS['search_id'] : "";

if( isset($HTTP_POST_VARS['addterms']) )
{
	$search_all_terms = ( $HTTP_POST_VARS['addterms'] == "all" ) ? 1 : 0;
}
else if( isset($HTTP_GET_VARS['addterms']) )
{
	$search_all_terms = ( $HTTP_GET_VARS['addterms'] == "all" ) ? 1 : 0;
}
else
{
	$search_all_terms = 0;
}

if( isset($HTTP_POST_VARS['charsreqd']) || isset($HTTP_GET_VARS['charsreqd']) )
{
	$return_chars = ( isset($HTTP_POST_VARS['charsreqd']) ) ? intval($HTTP_POST_VARS['charsreqd']) : intval($HTTP_GET_VARS['charsreqd']);
	if( $return_chars == "all" )
	{
		$return_chars = -1;
	}
}
else
{
	$return_chars = 200;
}

if( isset($HTTP_POST_VARS['searchcat']) || isset($HTTP_GET_VARS['searchcat']) )
{
	$search_cat = ( isset($HTTP_POST_VARS['searchcat']) ) ? intval($HTTP_POST_VARS['searchcat']) : intval($HTTP_GET_VARS['searchcat']);
}
else
{
	$search_cat = "all";
}

if( isset($HTTP_POST_VARS['searchforum']) || isset($HTTP_GET_VARS['searchforum']) )
{
	$search_forum = ( isset($HTTP_POST_VARS['searchforum']) ) ? intval($HTTP_POST_VARS['searchforum']) : intval($HTTP_GET_VARS['searchforum']);
}
else
{
	$search_forum = "all";
}

if( isset($HTTP_POST_VARS['sortby']) || isset($HTTP_GET_VARS['sortby']) )
{
	$sortby = (isset($HTTP_POST_VARS['sortby'])) ? intval($HTTP_POST_VARS['sortby']) : intval($HTTP_GET_VARS['sortby']);
}
else
{
	$sortby = 0;
}

if( isset($HTTP_POST_VARS['sortdir']) || isset($HTTP_GET_VARS['sortdir']) )
{
	$sortby_dir = (isset($HTTP_POST_VARS['sortdir'])) ? $HTTP_POST_VARS['sortdir'] : $HTTP_GET_VARS['sortdir'];
}
else
{
	$sortby_dir = "DESC";
}

if( isset($HTTP_POST_VARS['showresults']) || isset($HTTP_GET_VARS['showresults']) )
{
	$show_results = (isset($HTTP_POST_VARS['showresults'])) ? $HTTP_POST_VARS['showresults'] : $HTTP_GET_VARS['showresults'];
}
else
{
	$show_results = "posts";
}

if(!empty($HTTP_POST_VARS['resultdays']) )
{
	$search_time = time() - ( $HTTP_POST_VARS['resultdays'] * 86400 );
}
else
{
	$search_time = 0;
}

$start = ( isset($HTTP_GET_VARS['start']) ) ? $HTTP_GET_VARS['start'] : 0;

//
// Define some globally used data
//
$sortby_types = array("Post Time", "Post Subject", "Topic Title", "Author Name", "Forum");
$sortby_sql = array("p.post_time", "pt.post_subject", "t.topic_title", "u.username", "f.forum_id");

//
// Begin core code
//
if( $query_keywords != "" || $query_author != "" || $search_id )
{

	if( $query_keywords != "" || $query_author != "" || $search_id == "newposts" )
	{
		if( $search_id == "newposts" )
		{
			$show_results = "topics";
			$search_time = $userdata['session_last_visit'];
			$sortby = 0;
			$sortby_dir = "DESC";
		}

		//
		// Start building appropriate SQL query
		//
		$sql_fields = ( $show_results == "posts") ? "pt.post_text, pt.post_subject, p.*, f.forum_name, t.*, u.username, u.user_id, u.user_sig" : "f.forum_id, f.forum_name, t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_time, p.post_username" ;

		$sql_from = ( $show_results == "posts") ? FORUMS_TABLE . " f, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . POSTS_TEXT_TABLE . " pt" : FORUMS_TABLE . " f, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . POSTS_TEXT_TABLE . " pt, " . POSTS_TABLE . " p2, " . USERS_TABLE . " u2";

		$sql_where = ( $show_results == "posts") ? "pt.post_id = p.post_id AND f.forum_id = p.forum_id AND p.topic_id = t.topic_id AND p.poster_id = u.user_id" : "pt.post_id = p.post_id AND f.forum_id = p.forum_id AND t.topic_id = p.topic_id AND u.user_id = t.topic_poster AND p2.post_id = t.topic_last_post_id AND u2.user_id = p2.poster_id";

		//
		// If user is logged in then we'll
		// check to see which (if any) private
		// forums they are allowed to view and
		// include them in the search.
		//
		// If not logged in we explicitly prevent
		// searching of private forums
		//

		if( $query_keywords != "" || $query_author != "" || $search_id == "newposts" )
		{
			$search_sql = "";
			if($query_keywords != "")
			{
				$searchdata = gensearch_sql($query_keywords, $search_all_terms);
				$search_sql = $searchdata[0];
			}

			if($query_author != "")
			{
				$search_sql = preg_replace("/\(\)/", "", $search_sql); 
				$query_author = preg_replace("/\*/", "%", $query_author);

				if( $show_results == "posts" )
				{
					$search_sql .= ( $search_sql == "" ) ? "u.username LIKE '$query_author' " : " AND u.username LIKE '$query_author' ";
				}
				else
				{
					$search_sql .= ( $search_sql == "" ) ? "us.username LIKE '$query_author' AND us.user_id = p.poster_id " : " AND us.username LIKE '$query_author' AND us.user_id = p.poster_id ";
					$sql_from .= ", " . USERS_TABLE . " us ";
				}
			}

			if( !ereg("\([ ]*\)", $search_sql) || $search_id == "newposts" )
			{
				$sql = "SELECT  $sql_fields 
					FROM $sql_from ";

				$sql .= ( $search_id == "newposts" ) ? "WHERE $sql_where" : "WHERE $search_sql AND $sql_where";

				if( $search_forum != "all" )
				{
					$is_auth = auth(AUTH_READ, $search_forum, $userdata);

					if( !$is_auth['auth_read'] )
					{
						message_die(GENERAL_MESSAGE, $lang['No_search_match']);
					}
					else
					{
						$sql .= " AND f.forum_id = $search_forum";
					}
				}
				else
				{
					$is_auth_ary = auth(AUTH_READ, AUTH_LIST_ALL, $userdata); 

					if( $search_cat != "all" )
					{
						$sql .= " AND f.cat_id = $search_cat";
					}

					$ignore_forum_sql = "";
					while( list($key, $value) = each($is_auth_ary) )
					{
						if( !$value['auth_read'] )
						{
							if( $ignore_forum_sql != "" )
							{
								$ignore_forum_sql = ", ";
							}
							$ignore_forum_sql = $key;
						}
					}

					if( $ignore_forum_sql != "" )
					{
						$sql .= " AND f.forum_id NOT IN ($ignore_forum_sql) ";
					}
				}

				if( $search_time )
				{
					$sql .= " AND p.post_time >= $search_time ";
				}

				if( $show_results != "posts")
				{
					$sql .= " GROUP BY t.topic_id ";
				}

				$sql .= " ORDER BY " . $sortby_sql[$sortby] . " $sortby_dir";
				
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't obtain search results", "", __LINE__, __FILE__, $sql);
				}
				$searchset = $db->sql_fetchrowset($result);

				//
				// Clean up search results table
				//
				$sql = "SELECT session_id 
					FROM " . SESSIONS_TABLE;
				if( $result = $db->sql_query($sql) )
				{
					$delete_search_id_sql = "";
					while( $row = $db->sql_fetchrow($result) )
					{
						if( $delete_search_id_sql != "" )
						{
							$delete_search_id_sql .= ", ";
						}
						$delete_search_id_sql .= "'" . $row['session_id'] . "'";
					}

					if( $delete_search_id_sql != "" )
					{
						$sql = "DELETE FROM " . SEARCH_TABLE . " 
							WHERE session_id NOT IN ($delete_search_id_sql)";
						if( !$result = $db->sql_query($sql) )
						{
							message_die(GENERAL_ERROR, "Couldn't delete old search id sessions", "", __LINE__, __FILE__, $sql);
						}
					}
				}

				//
				// Store new result data
				//
				if( count($searchset) )
				{
					$search_results = "";
					for($i = 0; $i < count($searchset); $i++)
					{
						if( $show_results == "posts")
						{
							$search_results .= ($search_results != "") ? ", " . $searchset[$i]['post_id'] : $searchset[$i]['post_id']; 
						}
						else
						{
							$search_results .= ($search_results != "") ? ", " . $searchset[$i]['topic_id'] : $searchset[$i]['topic_id']; 
						}
					}

					//
					// Combine both results and search data (apart from original query)
					// so we can serialize it and place it in the DB
					//
					$store_search_data = array();
					$store_search_data['results'] = $search_results;
					$store_search_data['data'] = $searchdata;
					$store_search_data['data'][0] = "";

					$result_array = serialize($store_search_data);
					unset($store_search_data);
					unset($search_results);

					mt_srand ((double) microtime() * 1000000);
					$search_id = mt_rand();

					$sql = "UPDATE " . SEARCH_TABLE . " 
						SET search_id = $search_id, search_array = '$result_array'
						WHERE session_id = '" . $userdata['session_id'] . "'";
					$result = $db->sql_query($sql);
					if( !$result || !$db->sql_affectedrows() )
					{
						$sql = "INSERT INTO " . SEARCH_TABLE . " (search_id, session_id, search_array) 
							VALUES($search_id, '" . $userdata['session_id'] . "', '$result_array')";
						if( !$result = $db->sql_query($sql) )
						{
							message_die(GENERAL_ERROR, "Couldn't insert search results", "", __LINE__, __FILE__, $sql);
						}
					}
				}
				else
				{
					message_die(GENERAL_MESSAGE, $lang['No_search_match']);
				}
			}
		}
	}
	else
	{
		$search_id = $HTTP_GET_VARS['search_id'];

		$sql = "SELECT search_array 
			FROM " . SEARCH_TABLE . " 
			WHERE search_id = '$search_id' 
				AND session_id = '". $userdata['session_id'] . "'";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain search results", "", __LINE__, __FILE__, $sql);
		}

		if( $db->sql_numrows($result) )
		{
			$row = $db->sql_fetchrow($result);

			$row = unserialize($row['search_array']);
			$search_results = $row['results'];
			$searchdata = $row['data'];

			if( $show_results == "posts")
			{
				$sql = "SELECT pt.post_text, pt.post_subject, p.*, f.forum_name, t.*, u.username, u.user_id, u.user_sig  
					FROM " . FORUMS_TABLE . " f, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . POSTS_TEXT_TABLE . " pt 
					WHERE pt.post_id = p.post_id
						AND f.forum_id = p.forum_id
						AND p.topic_id = t.topic_id
						AND p.poster_id = u.user_id 
						AND p.post_id IN ($search_results)";
			}
			else
			{
				$sql = "SELECT t.*, f.forum_id, f.forum_name, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_time, p.post_username 
					FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f, " . USERS_TABLE . " u, " . USERS_TABLE . " u2, " . POSTS_TABLE . " p 
					WHERE t.topic_id IN ($search_results) 
						AND f.forum_id = t.forum_id 
						AND u.user_id = t.topic_poster 
						AND p.post_id = t.topic_last_post_id 
						AND p.poster_id = u2.user_id";
			}

			$sql .= " ORDER BY " . $sortby_sql[$sortby] . " $sortby_dir";

			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't obtain search results", "", __LINE__, __FILE__, $sql);
			}

			$searchset = $db->sql_fetchrowset($result);
		}
		else
		{
			header("Location: " . append_sid("search.$phpEx", true));
		}
	}

	if( count($searchset) )
	{

		//
		// Output header
		//
		$page_title = $lang['Search'];
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);	

		//
		// Define censored word matches
		//
		$orig_word = array();
		$replacement_word = array();
		obtain_word_list($orig_word, $replacement_word);

		if( $showresults == "posts" )
		{
			$template->set_filenames(array(
				"body" => "search_results_posts.tpl",
				"jumpbox" => "jumpbox.tpl")
			);
		}
		else
		{
			$template->set_filenames(array(
				"body" => "search_results_topics.tpl",
				"jumpbox" => "jumpbox.tpl")
			);
		}

		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"L_GO" => $lang['Go'],
			"L_JUMP_TO" => $lang['Jump_to'],
			"L_SELECT_FORUM" => $lang['Select_forum'],

			"S_JUMPBOX_LIST" => $jumpbox,
			"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
		);
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");

		$template->assign_vars(array(
			"SEARCH_MATCHES" => count($searchset), 

			"L_FOUND" => $lang['found'], 
			"L_MATCHES" => (count($searchset) == 1) ? $lang['match'] : $lang['matches'], 
			"L_TOPIC" => $lang['Topic'])
		);

		for($j = 1; $j < count($searchdata); $j++)
		{
			$search_string[] = "'(" . preg_quote($searchdata[$j], "'") . ")'i";
			$replace_string[] = "<font color=\"#" . $theme['fontcolor3'] . "\"><b>\\1</b></font>";
		}

		for($i = $start; $i < min($start + $board_config['posts_per_page'], count($searchset)); $i++)
		{
			$forum_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $searchset[$i]['forum_id']);
			$topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $searchset[$i]['topic_id']);
			$poster_url = append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $searchset[$i]['user_id']);
			$post_url = append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=".$searchset[$i]['post_id']."#".$searchset[$i]['post_id']);

			$post_date = create_date($board_config['default_dateformat'], $searchset[$i]['post_time'], $board_config['board_timezone']);

			$message = $searchset[$i]['post_text'];

			if( $showresults == "posts" )
			{
				if($return_chars != 0 )
				{
					if($return_chars != -1)
					{
						$message = (strlen($message) > $return_chars) ? substr($message, 0, $return_chars) . " ..." : $message;
					}
		
					//
					// If the board has HTML off but the post has HTML
					// on then we process it, else leave it alone
					//
					if( $return_chars != -1 )
					{
						if( $searchset[$i]['enable_html'] )
						{
							$message = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $message);
						}

						$message = preg_replace("/\:[0-9a-z\:]+\]/si", "]", $message);
					}
					else
					{
						$bbcode_uid = $searchset[$i]['bbcode_uid'];
						$user_sig = $searchset[$i]['user_sig'];

						if( !$board_config['allow_html'] )
						{
							if( $user_sig != "" && $searchset[$i]['enable_sig'] )
							{
								$user_sig = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $user_sig);
							}

							if( $searchset[$i]['enable_html'] )
							{
								$message = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $message);
							}
						}

						if( $board_config['allow_bbcode'] && $bbcode_uid != "" )
						{
							if( $user_sig != "" && $searchset[$i]['enable_sig'] )
							{
								$sig_uid = make_bbcode_uid();
								$user_sig = bbencode_first_pass($user_sig, $sig_uid);
								$user_sig = bbencode_second_pass($user_sig, $sig_uid);
							}

							$message = bbencode_second_pass($message, $bbcode_uid);
						}
						else if( !$board_config['allow_bbcode'] && $bbcode_uid != "" )
						{
							$message = preg_replace("/\:[0-9a-z\:]+\]/si", "]", $message);
						}

						$message = make_clickable($message);

						if( $searchset[$i]['enable_sig'] )
						{
							$message .= "<br /><br />_________________<br />" . make_clickable($user_sig);
						}
					}

					if( count($orig_word) )
					{
						$topic_title = preg_replace($orig_word, $replacement_word, $searchset[$i]['topic_title']);
						$post_subject = ( $searchset[$i]['post_subject'] != "" ) ? preg_replace($orig_word, $replacement_word, $searchset[$i]['post_subject']) : $topic_title;

						$message = preg_replace($orig_word, $replacement_word, $message);
					}

					if($board_config['allow_smilies'] && $searchset[$i]['enable_smilies'])
					{
						$message = smilies_pass($message);
					}

					$message = str_replace("\n", "<br />", $message);

					if( count($searchdata) > 1 )
					{
						$message = preg_replace($search_string, $replace_string, $message);
					}
				}

				$template->assign_block_vars("searchresults", array( 
					"TOPIC_TITLE" => $topic_title,
					"FORUM_NAME" => $searchset[$i]['forum_name'],
					"POST_SUBJECT" => $post_subject,
					"POST_DATE" => $post_date,
					"POSTER_NAME" => $searchset[$i]['username'],
					"TOPIC_REPLIES" => $searchset[$i]['topic_replies'],
					"TOPIC_VIEWS" => $searchset[$i]['topic_views'],
					"MESSAGE" => $message,

					"U_POST" => $post_url,
					"U_TOPIC" => $topic_url,
					"U_FORUM" => $forum_url,
					"U_USER_PROFILE" => $poster_url)
				);
			}
			else
			{
				$message = "";

				if( count($orig_word) )
				{
					$topic_title = preg_replace($orig_word, $replacement_word, $searchset[$i]['topic_title']);
				}

				$topic_type = $searchset[$i]['topic_type'];

				if($topic_type == POST_ANNOUNCE)
				{
					$topic_type = $lang['Topic_Announcement'] . " ";
				}
				else if($topic_type == POST_STICKY)
				{
					$topic_type = $lang['Topic_Sticky'] . " ";
				}
				else
				{
					$topic_type = "";
				}

				if( $searchset[$i]['topic_vote'] )
				{
					$topic_type .= $lang['Topic_Poll'] . " ";
				}

				$forum_id = $searchset[$i]['forum_id'];
				$topic_id = $searchset[$i]['topic_id'];

				$replies = $searchset[$i]['topic_replies'];

				if($replies > $board_config['posts_per_page'])
				{
					$goto_page = "&nbsp;&nbsp;&nbsp;(<img src=\"" . $images['icon_minipost'] . "\" alt=\"" . $lang['Goto_page'] . "\" />" . $lang['Goto_page'] . ": ";

					$times = 1;
					for($j = 0; $j < $replies + 1; $j += $board_config['posts_per_page'])
					{
						if($times > 4)
						{
							if( $j + $board_config['posts_per_page'] >= $replies + 1 )
							{
								$goto_page .= " ... <a href=\"".append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&amp;start=$j") . "\">$times</a>";
							}
						}
						else
						{
							if($times != 1)
							{
								$goto_page .= ", ";
							}
							$goto_page .= "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&amp;start=$j") . "\">$times</a>";
						}
						$times++;
					}
					$goto_page .= ")";
				}
				else
				{
					$goto_page = "";
				}

				if($searchset[$i]['topic_status'] == TOPIC_LOCKED)
				{
					$folder_image = "<img src=\"" . $images['folder_locked'] . "\" alt=\"" . $lang['Topic_locked'] . "\" />";
				}
				else if( $searchset[$i]['topic_status'] == TOPIC_MOVED )
				{
					$topic_type = $lang['Topic_Moved'] . " ";
					$topic_id = $searchset[$i]['topic_moved_id'];
				}
				else
				{
					if( $searchset[$i]['topic_type'] == POST_ANNOUNCE )
					{
						$folder = $images['folder_announce'];
						$folder_new = $images['folder_announce_new'];
					}
					else if( $searchset[$i]['topic_type'] == POST_STICKY )
					{
						$folder = $images['folder_sticky'];
						$folder_new = $images['folder_sticky_new'];
					}
					else
					{
						if( $replies >= $board_config['hot_threshold'] )
						{
							$folder = $images['folder_hot'];
							$folder_new = $images['folder_new_hot'];
						}
						else
						{
							$folder = $images['folder'];
							$folder_new = $images['folder_new'];
						}
					}

					if( empty($HTTP_COOKIE_VARS['phpbb2_' . $forum_id . '_' . $topic_id]) && $searchset[$i]['post_time'] > $userdata['session_last_visit'] )
					{
						$folder_image = "<img src=\"$folder_new\" alt=\"" . $lang['New_posts'] . "\" />";
					}
					else
					{
						if( isset($HTTP_COOKIE_VARS['phpbb2_' . $forum_id . '_' . $topic_id]) )
						{
							$folder_image = ($HTTP_COOKIE_VARS['phpbb2_' . $forum_id . '_' . $topic_id] < $searchset[$i]['post_time'] ) ? "<img src=\"$folder_new\" alt=\"" . $lang['New_posts'] . "\" />" : "<img src=\"$folder\" alt=\"" . $lang['No_new_posts'] . "\" />";
						}
						else
						{
							$folder_image = "<img src=\"$folder\" alt=\"" . $lang['No_new_posts'] . "\" />";
						}
					}
				}

				if($searchset[$i]['post_time'] >= $userdata['session_last_visit'])
				{
					$newest_post_img = "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=newest\"><img src=\"" . $images['icon_newest_reply'] . "\" alt=\"" . $lang['View_newest_posts'] . "\" border=\"0\" /></a> ";
				}
				else
				{
					$newest_post_img = "";
				}

				$topic_poster = $searchset[$i]['username'];

				$last_post_time = create_date($board_config['default_dateformat'], $searchset[$i]['post_time'], $board_config['board_timezone']);

				if($searchset[$i]['id2'] == ANONYMOUS && $searchset[$i]['post_username'] != '')
				{
					$last_post_user = $searchset[$i]['post_username'];
				}
				else
				{
					$last_post_user = $searchset[$i]['user2'];
				}

				$last_post = $last_post_time . "<br />" . $lang['by'] . " ";
				$last_post .= "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "="  . $searchset[$i]['id2']) . "\">" . $last_post_user . "</a>&nbsp;";
				$last_post .= "<a href=\"" . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . "=" . $searchset[$i]['topic_last_post_id']) . "#" . $searchset[$i]['topic_last_post_id'] . "\"><img src=\"" . $images['icon_latest_reply'] . "\" border=\"0\" alt=\"" . $lang['View_latest_post'] . "\" /></a>";

				$views = $searchset[$i]['topic_views'];

				$template->assign_block_vars("searchresults", array( 
					"FORUM_NAME" => $searchset[$i]['forum_name'],
					"FORUM_ID" => $forum_id,
					"TOPIC_ID" => $topic_id,
					"FOLDER" => $folder_image,
					"NEWEST_POST_IMG" => $newest_post_img, 
					"TOPIC_POSTER" => $topic_poster,
					"GOTO_PAGE" => $goto_page,
					"REPLIES" => $replies,
					"TOPIC_TITLE" => $topic_title,
					"TOPIC_TYPE" => $topic_type,
					"VIEWS" => $views,
					"LAST_POST" => $last_post,

					"U_VIEW_FORUM" => $forum_url, 
					"U_VIEW_TOPIC" => $topic_url,
					"U_TOPIC_POSTER_PROFILE" => $topic_poster_profile_url)
				);
			}
		}

		$base_url = "search.$phpEx?search_id=$search_id&amp;showresults=" . $show_results . "&amp;sortby=" . $sortby . "&amp;sortdir=" . $sortby_dir . "&amp;charsreqd=" . $return_chars;

		$template->assign_vars(array(
			"PAGINATION" => generate_pagination($base_url, count($searchset), $board_config['posts_per_page'], $start),
			"ON_PAGE" => (floor($start/$board_config['posts_per_page'])+1),
			"TOTAL_PAGES" => ceil((count($searchset))/$board_config['posts_per_page']),

			"L_OF" => $lang['of'],
			"L_PAGE" => $lang['Page'],
			"L_GOTO_PAGE" => $lang['Goto_page'])
		);

		$template->pparse("body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}

//
// Search forum
//
$sql = "SELECT c.cat_title, c.cat_id, f.forum_name, f.forum_id  
	FROM " . CATEGORIES_TABLE . " c, " . FORUMS_TABLE . " f
	WHERE f.cat_id = c.cat_id 
	ORDER BY c.cat_id, f.forum_order";
$result = $db->sql_query($sql);
if(!$result)
{
	message_die(GENERAL_ERROR, "Couldn't obtain forum_name/forum_id", "", __LINE__, __FILE__, $sql);
}

$is_auth_ary = auth(AUTH_READ, AUTH_LIST_ALL, $userdata);

$s_forums = "<option value=\"all\">" . $lang['All'] . "</option>";

while($row = $db->sql_fetchrow($result))
{
	if($is_auth_ary[$row['forum_id']]['auth_read'])
	{
		$s_forums .= "<option value=\"" . $row['forum_id'] . "\">" . $row['forum_name'] . "</option>";
		if(empty($list_cat[$row['cat_id']]))
		{
			$list_cat[$row['cat_id']] = $row['cat_title'];
		}
	}
}

//
// Category to search
//
$s_categories = "<option value=\"all\">" . $lang['All'] . "</option>";
while( list($cat_id, $cat_title) = each($list_cat))
{
	$s_categories .= "<option value=\"$cat_id\">$cat_title</option>";
}

//
// Number of chars returned
//
$s_characters = "<option value=\"all\">" . $lang['All'] . "</option>";
$s_characters .= "<option value=\"0\">0</option>";
$s_characters .= "<option value=\"25\">25</option>";
$s_characters .= "<option value=\"50\">50</option>";

for($i = 100; $i < 1100 ; $i += 100)
{
	$selected = ($i == 200) ? "selected=\"selected\"" : "";
	$s_characters .= "<option value=\"$i\"$selected>$i</option>";
}

//
// Sorting
//
$s_sortby = "";
for($i = 0; $i < count($sortby_types); $i++)
{
	$s_sortby .= "<option value=\"$i\">" . $sortby_types[$i] . "</option>";
}

//
// Search time
//
$previous_days = array(0, 1, 7, 14, 30, 90, 180, 364);
$previous_days_text = array($lang['All'], "1 " . $lang['Day'], "7 " . $lang['Days'], "2 " . $lang['Weeks'], "1 " . $lang['Month'], "3 ". $lang['Months'], "6 " . $lang['Months'], "1 " . $lang['Year']);

$s_time = "";
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ($topic_days == $previous_days[$i]) ? " selected=\"selected\"" : "";
	$s_time .= "<option value=\"" . $previous_days[$i] . "\"$selected>" . $previous_days_text[$i] . "</option>";
}

//
// Output the basic page
//
$page_title = $lang['Search'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "search_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);

$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"L_GO" => $lang['Go'],
	"L_JUMP_TO" => $lang['Jump_to'],
	"L_SELECT_FORUM" => $lang['Select_forum'],

	"S_JUMPBOX_LIST" => $jumpbox,
	"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

$template->assign_vars(array(
	"L_SEARCH_QUERY" => $lang['Search_query'], 
	"L_SEARCH_OPTIONS" => $lang['Search_options'], 
	"L_SEARCH_KEYWORDS" => $lang['Search_keywords'], 
	"L_SEARCH_KEYWORDS_EXPLAIN" => $lang['Search_keywords_explain'], 
	"L_SEARCH_AUTHOR" => $lang['Search_author'],
	"L_SEARCH_AUTHOR_EXPLAIN" => $lang['Search_author_explain'], 
	"L_SEARCH_ANY_TERMS" => $lang['Search_for_any'],
	"L_SEARCH_ALL_TERMS" => $lang['Search_for_all'],
	"L_CATEGORY" => $lang['Category'], 
	"L_RETURN_FIRST" => $lang['Return_first'],
	"L_CHARACTERS" => $lang['characters_posts'], 
	"L_SORT_BY" => $lang['Sort_by'],
	"L_SORT_ASCENDING" => $lang['Sort_Ascending'],
	"L_SORT_DESCENDING" => $lang['Sort_Decending'],
	"L_SEARCH_PREVIOUS" => $lang['Search_previous'], 
	"L_DISPLAY_RESULTS" => $lang['Display_results'], 

	"S_SEARCH_ACTION" => append_sid("search.$phpEx"),
	"S_CHARACTER_OPTIONS" => $s_characters,
	"S_FORUM_OPTIONS" => $s_forums, 
	"S_CATEGORY_OPTIONS" => $s_categories, 
	"S_TIME_OPTIONS" => $s_time, 
	"S_SORT_OPTIONS" => $s_sortby,
	"S_HIDDEN_FIELDS" => $s_hidden_fields)
);

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>