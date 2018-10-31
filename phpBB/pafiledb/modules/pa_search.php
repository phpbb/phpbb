<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_search.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

/**
 * Enter description here...
 *
 */
class pafiledb_search extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $config, $phpEx, $pafiledb_config, $db;
		global $phpbb_root_path, $tplEx;
		global $mx_root_path, $module_root_path, $is_block;

		if ( !$this->auth_global['auth_search'] )
		{
			if ( !$user->data['is_registered'] )
			{
				// mx_redirect(mx_append_sid($mx_root_path . "login.$phpEx?redirect=".$this->this_mxurl("action=stats"), true));
			}

			$message = sprintf( $user->lang['Sorry_auth_search'], $this->auth_global['auth_search_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		if( !function_exists('add_search_words') )
		{
			mx_cache::load_file('functions_search', 'phpbb2');
		}

		if ( isset( $_REQUEST['search_keywords'] ) )
		{
			$search_keywords = htmlspecialchars( $_REQUEST['search_keywords'] );
		}
		else
		{
			$search_keywords = '';
		}

		$search_author = ( isset( $_REQUEST['search_author'] ) ) ? htmlspecialchars( $_REQUEST['search_author'] ) : '';

		$search_id = ( isset( $_REQUEST['search_id'] ) ) ? intval( $_REQUEST['search_id'] ) : 0;

		if ( isset( $_REQUEST['search_terms'] ) )
		{
			$search_terms = ( $_REQUEST['search_terms'] == 'all' ) ? 1 : 0;
		}
		else
		{
			$search_terms = 0;
		}

		$cat_id = ( isset( $_REQUEST['cat_id'] ) ) ? intval( $_REQUEST['cat_id'] ) : 0;

		if ( isset( $_REQUEST['comments_search'] ) )
		{
			$comments_search = ( $_REQUEST['comments_search'] == 'YES' ) ? 1 : 0;
		}
		else
		{
			$comments_search = 0;
		}

		$start = ( isset( $_REQUEST['start'] ) ) ? intval( $_REQUEST['start'] ) : 0;

		if ( isset( $_REQUEST['sort_method'] ) )
		{
			switch ( $_REQUEST['sort_method'] )
			{
				case 'file_name':
					$sort_method = 'file_name';
					break;
				case 'file_time':
					$sort_method = 'file_time';
					break;
				case 'file_dls':
					$sort_method = 'file_dls';
					break;
				case 'file_rating':
					$sort_method = 'rating';
					break;
				case 'file_update_time':
					$sort_method = 'file_update_time';
					break;
				default:
					$sort_method = $pafiledb_config['sort_method'];
			}
		}
		else
		{
			$sort_method = $pafiledb_config['sort_method'];
		}

		if ( isset( $_REQUEST['sort_order'] ) )
		{
			switch ( $_REQUEST['sort_order'] )
			{
				case 'ASC':
					$sort_order = 'ASC';
					break;
				case 'DESC':
					$sort_order = 'DESC';
					break;
				default:
					$sort_order = $pafiledb_config['sort_order'];
			}
		}
		else
		{
			$sort_order = $pafiledb_config['sort_order'];
		}

		$limit_sql = ( $start == 0 ) ? $pafiledb_config['pagination'] : $start . ',' . $pafiledb_config['pagination'];

		//
		// encoding match for workaround
		//
		$multibyte_charset = 'utf-8, big5, shift_jis, euc-kr, gb2312';

		if ( isset( $_POST['submit'] ) || $search_author != '' || $search_keywords != '' || $search_id )
		{
			$store_vars = array( 'search_results', 'total_match_count', 'split_search', 'sort_method', 'sort_order' );

			if ( $search_author != '' || $search_keywords != '' )
			{
				if ( $search_author != '' && $search_keywords == '' )
				{
					$search_author = str_replace( '*', '%', trim( $search_author ) );

					$sql = "SELECT user_id
					FROM " . USERS_TABLE . "
					WHERE username LIKE '" . str_replace( "\'", "''", $search_author ) . "'";
					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, "Couldn't obtain list of matching users (searching for: $search_author)", "", __LINE__, __FILE__, $sql );
					}

					$matching_userids = '';
					if ( $row = $db->sql_fetchrow( $result ) )
					{
						do
						{
							$matching_userids .= ( ( $matching_userids != '' ) ? ', ' : '' ) . $row['user_id'];
						}
						while ( $row = $db->sql_fetchrow( $result ) );
					}
					else
					{
						mx_message_die( GENERAL_MESSAGE, $user->lang['No_search_match'] );
					}

					$sql = "SELECT *
					FROM " . PA_FILES_TABLE . "
					WHERE user_id IN ($matching_userids)";

					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Could not obtain matched files list', '', __LINE__, __FILE__, $sql );
					}

					$search_ids = array();
					while ( $row = $db->sql_fetchrow( $result ) )
					{
						if ( $this->auth_user[$row['file_catid']]['auth_read'] )
						{
							$search_ids[] = $row['file_id'];
						}
					}
					$db->sql_freeresult( $result );

					$total_match_count = count( $search_ids );
				}
				else if ( $search_keywords != '' )
				{
					$stopword_array = @file( $phpbb_root_path . 'language/lang_' . $config['default_lang'] . '/search_stopwords.txt' );
					$synonym_array = @file( $phpbb_root_path . 'language/lang_' . $config['default_lang'] . '/search_synonyms.txt' );

					$split_search = array();
					$split_search = ( !strstr( $multibyte_charset, $user->lang['ENCODING'] ) ) ? split_words( clean_words( 'search', stripslashes( $search_keywords ), $stopword_array, $synonym_array ), 'search' ) : split( ' ', $search_keywords );

					$word_count = 0;
					$current_match_type = 'or';

					$word_match = array();
					$result_list = array();

					for( $i = 0; $i < count( $split_search ); $i++ )
					{
						switch ( $split_search[$i] )
						{
							case 'and':
								$current_match_type = 'and';
								break;

							case 'or':
								$current_match_type = 'or';
								break;

							case 'not':
								$current_match_type = 'not';
								break;

							default:
								if ( !empty( $search_terms ) )
								{
									$current_match_type = 'and';
								}
								$match_word = addslashes( '%' . str_replace( '*', '', $split_search[$i] ) . '%' );

								$sql = "SELECT file_id
								FROM " . PA_FILES_TABLE . "
								WHERE (file_name LIKE '$match_word'
								OR file_creator LIKE '$match_word'
								OR file_desc LIKE '$match_word'
								OR file_longdesc LIKE '$match_word')";

								if ( !( $result = $db->sql_query( $sql ) ) )
								{
									mx_message_die( GENERAL_ERROR, 'Could not obtain matched files list', '', __LINE__, __FILE__, $sql );
								}

								$row = array();
								while ( $temp_row = $db->sql_fetchrow( $result ) )
								{
									$row[$temp_row['file_id']] = 1;

									if ( !$word_count )
									{
										$result_list[$temp_row['file_id']] = 1;
									}
									else if ( $current_match_type == 'or' )
									{
										$result_list[$temp_row['file_id']] = 1;
									}
									else if ( $current_match_type == 'not' )
									{
										$result_list[$temp_row['file_id']] = 0;
									}
								}

								if ( $current_match_type == 'and' && $word_count )
								{
									@reset( $result_list );
									while ( list( $file_id, $match_count ) = @each( $result_list ) )
									{
										if ( !$row[$file_id] )
										{
											$result_list[$file_id] = 0;
										}
									}
								}

								if ( $comments_search )
								{
									$sql = "SELECT file_id
										FROM " . PA_COMMENTS_TABLE . "
										WHERE (comments_title LIKE '$match_word'
										OR comments_text LIKE '$match_word')";

									if ( !( $result = $db->sql_query( $sql ) ) )
									{
										mx_message_die( GENERAL_ERROR, 'Could not obtain matched files list', '', __LINE__, __FILE__, $sql );
									}

									$row = array();
									while ( $temp_row = $db->sql_fetchrow( $result ) )
									{
										$row[$temp_row['file_id']] = 1;

										if ( !$word_count )
										{
											$result_list[$temp_row['file_id']] = 1;
										}
										else if ( $current_match_type == 'or' )
										{
											$result_list[$temp_row['file_id']] = 1;
										}
										else if ( $current_match_type == 'not' )
										{
											$result_list[$temp_row['file_id']] = 0;
										}
									}

									if ( $current_match_type == 'and' && $word_count )
									{
										@reset( $result_list );
										while ( list( $file_id, $match_count ) = @each( $result_list ) )
										{
											if ( !$row[$file_id] )
											{
												$result_list[$file_id] = 0;
											}
										}
									}
								}

								$word_count++;

								$db->sql_freeresult( $result );
						}
					}
					@reset( $result_list );

					$search_ids = array();
					while ( list( $file_id, $matches ) = each( $result_list ) )
					{
						if ( $matches )
						{
							$search_ids[] = $file_id;
						}
					}

					unset( $result_list );
					$total_match_count = count( $search_ids );
				}

				// Author name search

				if ( $search_author != '' )
				{
					$search_author = str_replace( '*', '%', trim( str_replace( "\'", "''", $search_author ) ) );
				}

				if ( $total_match_count )
				{
					$where_sql = ( $cat_id ) ? 'AND file_catid IN (' . $this->gen_cat_ids( $cat_id, '' ) . ')' : '';

					if ( $search_author == '' )
					{
						$sql = "SELECT file_id, file_catid
							FROM " . PA_FILES_TABLE . "
							WHERE file_id IN (" . implode( ", ", $search_ids ) . ")
								$where_sql
							GROUP BY file_id";
					}
					else
					{
						$from_sql = PA_FILES_TABLE . " f";
						if ( $search_author != '' )
						{
							$from_sql .= ", " . USERS_TABLE . " u";
							$where_sql .= " AND u.user_id = f.user_id AND u.username LIKE '$search_author' ";
						}

						$where_sql .= ( $cat_id ) ? 'AND file_catid IN (' . $this->gen_cat_ids( $cat_id, '' ) . ')' : '';

						$sql = "SELECT f.file_id, f.file_catid
							FROM $from_sql
							WHERE f.file_id IN (" . implode( ", ", $search_ids ) . ")
							$where_sql
							GROUP BY f.file_id";
					}

					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Could not obtain file ids', '', __LINE__, __FILE__, $sql );
					}

					$search_ids = array();
					while ( $row = $db->sql_fetchrow( $result ) )
					{
						if ( $this->auth_user[$row['file_catid']]['auth_read'] )
						{
							$search_ids[] = $row['file_id'];
						}
					}
					$db->sql_freeresult( $result );
					$total_match_count = sizeof( $search_ids );
				}
				else
				{
					mx_message_die( GENERAL_MESSAGE, $user->lang['No_search_match'] );
				}

				// Finish building query (for all combinations)
				// and run it ...

				$expiry_time = $current_time - $config['session_length'];
				$sql = "SELECT session_id
					FROM " . SESSIONS_TABLE . "
					WHERE session_time > $expiry_time";

				if ( $result = $db->sql_query( $sql ) )
				{
					$delete_search_ids = array();
					while ( $row = $db->sql_fetchrow( $result ) )
					{
						$delete_search_ids[] = "'" . $row['session_id'] . "'";
					}

					if ( count( $delete_search_ids ) )
					{
						$sql = "DELETE FROM " . PA_SEARCH_TABLE . "
							WHERE session_id NOT IN (" . implode( ", ", $delete_search_ids ) . ")";
						if ( !$result = $db->sql_query( $sql ) )
						{
							mx_message_die( GENERAL_ERROR, 'Could not delete old search id sessions', '', __LINE__, __FILE__, $sql );
						}
					}
				}

				// Store new result data

				$search_results = implode( ', ', $search_ids );

				$store_search_data = array();

				for( $i = 0; $i < count( $store_vars ); $i++ )
				{
					$store_search_data[$store_vars[$i]] = $$store_vars[$i];
				}

				$result_array = serialize( $store_search_data );
				unset( $store_search_data );

				mt_srand ( ( double ) microtime() * 1000000 );
				$search_id = mt_rand();

				$sql = "UPDATE " . PA_SEARCH_TABLE . "
					SET search_id = $search_id, search_array = '" . str_replace( "\'", "''", $result_array ) . "'
					WHERE session_id = '" . $user->data['session_id'] . "'";
				if ( !( $result = $db->sql_query( $sql ) ) || !$db->sql_affectedrows() )
				{
					$sql = "INSERT INTO " . PA_SEARCH_TABLE . " (search_id, session_id, search_array)
						VALUES($search_id, '" . $user->data['session_id'] . "', '" . str_replace( "\'", "''", $result_array ) . "')";
					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Could not insert search results', '', __LINE__, __FILE__, $sql );
					}
				}
			}
			else
			{
				$search_id = intval( $search_id );
				if ( $search_id )
				{
					$sql = "SELECT search_array
						FROM " . PA_SEARCH_TABLE . "
						WHERE search_id = $search_id
						AND session_id = '" . $user->data['session_id'] . "'";
					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Could not obtain search results', '', __LINE__, __FILE__, $sql );
					}

					if ( $row = $db->sql_fetchrow( $result ) )
					{
						$search_data = unserialize( $row['search_array'] );
						for( $i = 0; $i < count( $store_vars ); $i++ )
						{
							$$store_vars[$i] = $search_data[$store_vars[$i]];
						}
					}
				}
			}

			if ( $search_results != '' )
			{
				switch ( SQL_LAYER )
				{
					case 'oracle':
						$sql = "SELECT f1.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, c.cat_id, c.cat_name, COUNT(cm.comments_id) AS total_comments
							FROM " . PA_FILES_TABLE . " AS f1, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_CATEGORY_TABLE . " AS c, " . PA_COMMENTS_TABLE . " AS cm
							WHERE f1.file_id IN ($search_results)
							AND f1.file_id = r.votes_file(+)
							AND f1.user_id = u.user_id(+)
							AND f1.file_id = cm.file_id(+)
							AND c.cat_id = f1.file_catid
							AND f1.file_approved = '1'
							GROUP BY f1.file_id
							ORDER BY $sort_method $sort_order
							LIMIT $limit_sql";
						break;

					default:
						$sql = "SELECT f1.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, c.cat_id, c.cat_name, COUNT(cm.comments_id) AS total_comments
							FROM " . PA_CATEGORY_TABLE . " AS c, " . PA_FILES_TABLE . " AS f1
								LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f1.file_id = r.votes_file
								LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
								LEFT JOIN " . PA_COMMENTS_TABLE . " AS cm ON f1.file_id = cm.file_id
							WHERE f1.file_id IN ($search_results)
							AND c.cat_id = f1.file_catid
							AND f1.file_approved = '1'
							GROUP BY f1.file_id
							ORDER BY $sort_method $sort_order
							LIMIT $limit_sql";
						break;
				}

				if ( !$result = $db->sql_query( $sql ) )
				{
					mx_message_die( GENERAL_ERROR, 'Could not obtain search results', '', __LINE__, __FILE__, $sql );
				}

				$searchset = array();
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$searchset[] = $row;
				}

				$db->sql_freeresult( $result );

				$l_search_matches = ( $total_match_count == 1 ) ? sprintf( $user->lang['Found_search_match'], $total_match_count ) : sprintf( $user->lang['Found_search_matches'], $total_match_count );

				$template->assign_vars( array( 'L_SEARCH_MATCHES' => $l_search_matches ) );

				for( $i = 0; $i < count( $searchset ); $i++ )
				{
					$cat_url = mx_append_sid( $this->this_mxurl( 'action=category&cat_id=' . $searchset[$i]['cat_id'] ) );
					$file_url = mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $searchset[$i]['file_id'] ) );
					// ===================================================
					// Format the date for the given file
					// ===================================================
					$date = phpBB2::create_date( $config['default_dateformat'], $searchset[$i]['file_time'], $config['board_timezone'] );
					// ===================================================
					// Get rating for the file and format it
					// ===================================================
					$rating = ( $searchset[$i]['rating'] != 0 ) ? round( $searchset[$i]['rating'], 2 ) . ' / 10' : $user->lang['Not_rated'];
					// ===================================================
					// If the file is new then put a new image in front of it
					// ===================================================
					$is_new = false;
					if ( time() - ( $pafiledb_config['settings_newdays'] * 24 * 60 * 60 ) < $searchset[$i]['file_time'] )
					{
						$is_new = true;
					}
					// ===================================================
					// Get the post icon fot this file
					// ===================================================
					if ( $searchset[$i]['file_pin'] != FILE_PINNED )
					{
						if ( $searchset[$i]['file_posticon'] == 'none' || $searchset[$i]['file_posticon'] == 'none.gif' )
						{
							$posticon = '&nbsp;';
						}
						else
						{
							$posticon = '<img src="' . $module_root_path . ICONS_DIR . $searchset[$i]['file_posticon'] . '" border="0" />';
						}
					}
					else
					{
						$posticon = '<img src="' . $user->img('icon_pa_folder_sticky', '', false, '', 'src') . '" border="0" />';
					}

					$poster = ( $searchset[$i]['user_id'] != ANONYMOUS ) ? '<a href="' . mx_append_sid( $phpbb_root_path . 'profile.' . $phpEx . '?mode=viewprofile&amp;' . POST_USERS_URL . '=' . $searchset[$i]['user_id'] ) . '">' : '';
					$poster .= ( $searchset[$i]['user_id'] != ANONYMOUS ) ? $searchset[$i]['username'] : $user->lang['Guest'];
					$poster .= ( $searchset[$i]['user_id'] != ANONYMOUS ) ? '</a>' : '';

					$template->assign_block_vars( 'searchresults', array(
						'CAT_NAME' => $searchset[$i]['cat_name'],
						'FILE_NEW_IMAGE' => $user->img('icon_pa_file_new', '', false, '', 'src'),
						'PIN_IMAGE' => $posticon,

						'IS_NEW_FILE' => $is_new,
						'FILE_NAME' => $searchset[$i]['file_name'],
						'FILE_DESC' => $searchset[$i]['file_desc'],
						'FILE_SUBMITER' => $poster,
						'DATE' => $date,
						'RATING' => $rating,
						'DOWNLOADS' => $searchset[$i]['file_dls'],
						'U_FILE' => $file_url,
						'U_CAT' => $cat_url )
					);
				}
				$base_url = mx_append_sid( $this->this_mxurl( "action=search&amp;search_id=$search_id" ) );

				$template->assign_vars( array(
					'PAGINATION' => phpBB2::generate_pagination( $base_url, $total_match_count, $pafiledb_config['pagination'], $start ),
					'PAGE_NUMBER' => sprintf( $user->lang['Page_of'], ( floor( $start / $pafiledb_config['pagination'] ) + 1 ), ceil( $total_match_count / $pafiledb_config['pagination'] ) ),
					'L_MODULE' => $pafiledb_config['module_name'],

					'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
					'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ),

					'L_INDEX' => "<<",
					'L_RATE' => $user->lang['DlRating'],
					'L_DOWNLOADS' => $user->lang['Dls'],
					'L_DATE' => $user->lang['Date'],
					'L_NAME' => $user->lang['Name'],
					'L_FILE' => $user->lang['File'],
					'L_SUBMITER' => $user->lang['Submiter'],
					'L_CATEGORY' => $user->lang['Category'],
					'L_NEW_FILE' => $user->lang['New_file'] )
				);

				$this->display( $user->lang['Download'], 'pa_search_result.'.$tplEx );
			}
			else
			{
				mx_message_die( GENERAL_MESSAGE, $user->lang['No_search_match'] );
			}
		}
		if ( !isset( $_POST['submit'] ) || ( $search_author == '' && $search_keywords == '' && !$search_id ) )
		{
			$dropmenu = $this->generate_jumpbox();

			$template->assign_vars( array(
				'S_SEARCH_ACTION' => mx_append_sid( $this->this_mxurl() ),
				'S_CAT_MENU' => $dropmenu,

				'L_MODULE' => $pafiledb_config['module_name'],

				'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ),

				'L_YES' => $user->lang['Yes'],
				'L_NO' => $user->lang['No'],
				'L_SEARCH_OPTIONS' => $user->lang['Search_options'],
				'L_SEARCH_KEYWORDS' => $user->lang['Search_keywords'],
				'L_SEARCH_KEYWORDS_EXPLAIN' => $user->lang['Search_keywords_explain'],
				'L_SEARCH_AUTHOR' => $user->lang['Search_author'],
				'L_SEARCH_AUTHOR_EXPLAIN' => $user->lang['Search_author_explain'],
				'L_SEARCH_ANY_TERMS' => $user->lang['Search_for_any'],
				'L_SEARCH_ALL_TERMS' => $user->lang['Search_for_all'],
				'L_INCLUDE_COMMENTS' => $user->lang['Include_comments'],
				'L_SORT_BY' => $user->lang['Select_sort_method'],
				'L_SORT_DIR' => $user->lang['Order'],
				'L_SORT_ASCENDING' => $user->lang['Sort_Ascending'],
				'L_SORT_DESCENDING' => $user->lang['Sort_Descending'],

				'L_INDEX' => "<<",

				'L_RATING' => $user->lang['Rated_downloads'],
				'L_DOWNLOADS' => $user->lang['Most_downloads'],
				'L_DATE' => $user->lang['Latest_downloads'],
				'L_NAME' => $user->lang['File_Title'],
				'L_UPDATE_TIME' => $user->lang['Update_time'],

				'L_SEARCH' => $user->lang['Search'],
				'L_SEARCH_FOR' => $user->lang['Search_for'],
				'L_ALL' => $user->lang['All'],
				'L_CHOOSE_CAT' => $user->lang['Choose_cat'] )
			);

			$this->display( $user->lang['Download'], 'pa_search_body.'.$tplEx );
		}

		//
		// Get footer quick dropdown jumpbox
		//
		$this->generate_jumpbox( );
	}
}
?>