<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_toplist.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_toplist extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $board_config, $phpEx, $pafiledb_config, $db;
		global $phpbb_root_path, $tplEx;
		global $mx_root_path, $module_root_path, $is_block;

		if ( !$this->auth_global['auth_toplist'] )
		{
			if ( !$user->data['is_registered'] )
			{
				mx_redirect( mx_append_sid( "login.$phpEx?redirect=dload.$phpEx?action=stats", true ) );
			}

			$message = sprintf( $user->lang['Sorry_auth_toplist'], $this->auth_global['auth_toplist_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$mode = ( isset( $_REQUEST['mode'] ) ) ? htmlspecialchars( $_REQUEST['mode'] ) : 'newest';
		$days = ( isset( $_REQUEST['days'] ) ) ? intval( $_REQUEST['days'] ) : 7;
		$selected_date = ( isset( $_REQUEST['selected_date'] ) ) ? $_REQUEST['selected_date'] : '';
		$most_num = ( isset( $_REQUEST['most_num'] ) ) ? intval( $_REQUEST['most_num'] ) : 10;
		$most_type = ( isset( $_REQUEST['most_type'] ) ) ? htmlspecialchars( $_REQUEST['most_type'] ) : 'num';

		if ( $mode == 'downloads' )
		{
			$l_current_toplist = $user->lang['Most_downloads'];
		}
		elseif ( $mode == 'rating' )
		{
			$l_current_toplist = $user->lang['Rated_downloads'];
		}
		else
		{
			$l_current_toplist = $user->lang['Latest_downloads'];
		}

		$template->assign_vars( array(
			'DOWNLOAD' => $pafiledb_config['module_name'],

			'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
			'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ),
			'U_NEWEST_FILE' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=newest' ) ),
			'U_MOST_POPULAR' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=downloads' ) ),
			'U_TOP_RATED' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=rating' ) ),

			'L_CURRENT_TOPLIST' => $l_current_toplist,
			'L_NEWEST_FILE' => $user->lang['Latest_downloads'],
			'L_MOST_POPULAR' => $user->lang['Most_downloads'],
			'L_TOP_RATED' => $user->lang['Rated_downloads'],

			'L_INDEX' => "<<",
			'L_TOPLIST' => $user->lang['Toplist'] )
		);

		$sql = 'SELECT file_time, file_id, file_catid
			FROM ' . PA_FILES_TABLE . "
			WHERE file_approved = '1'
			ORDER BY file_time DESC";

		if (!( $result = $db->sql_query_limit($sql, 300)))
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( $this->auth_user[$row['file_catid']]['auth_read'] )
			{
				$rowset[] = $row;
			}
		}

		$db->sql_freeresult( $result );

		switch ( $mode )
		{
			case 'newest':
				//
				// get number of files in the last week
				//
				$file_num_week = 0;
				$day_time = ( time() - ( 86400 * 7 ) );
				for( $i = 0; $i < count( $rowset ); $i++ )
				{
					if ( ( $rowset[$i]['file_time'] ) >= $day_time )
					{
						$file_num_week++;
					}
				}

				$file_num_month = 0;

				$day_time = ( time() - ( 86400 * 30 ) );
				for( $i = 0; $i < count( $rowset ); $i++ )
				{
					if ( ( $rowset[$i]['file_time'] ) >= $day_time )
					{
						$file_num_month++;
					}
				}

				$template->assign_vars( array(
					'IS_NEWEST' => true,
					'FILE_DATE' => ( empty( $selected_date ) ) ? true : false,

					'TOTAL_FILE_WEEK' => $file_num_week,
					'TOTAL_FILE_MONTH' => $file_num_month,

					'L_TOTAL_NEW_FILE' => $user->lang['Total_new_files'],
					'L_LAST_WEEK' => $user->lang['Last_week'],
					'L_LAST_30_DAYS' => $user->lang['Last_30_days'],
					'L_SHOW' => $user->lang['Show'],
					'L_ONE_WEEK' => $user->lang['One_week'],
					'L_TWO_WEEK' => $user->lang['Two_week'],
					'L_30_DAYS' => $user->lang['30_days'],
					'L_NEW_FILES' => sprintf( $user->lang['New_Files'], $days ),

					'U_ONE_WEEK' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=newest&days=7' ) ),
					'U_TWO_WEEK' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=newest&days=14' ) ),
					'U_30_DAYS' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=newest&days=30' ) ) )
				);

				if ( empty( $selected_date ) )
				{
					for( $j = 0; $j <= $days - 1; $j++ )
					{
						$day_time = ( time() - ( 86400 * $j ) );
						$day_date = Date( 'Y-m-d', $day_time );
						$file_num = 0;
						for( $i = 0; $i < count( $rowset ); $i++ )
						{
							$file_date = Date( 'Y-m-d', $rowset[$i]['file_time'] );
							if ( $file_date == $day_date )
							{
								$file_num++;
							}
						}

						$template->assign_block_vars( 'files_date', array(
								'U_DATES' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=newest&days=7&selected_date=' . $day_time ) ),
								'DATES' => date( 'F d, Y', $day_time ),
								'TOTAL_DOWNLOADS' => $file_num )
							);
					}
				}
				else
				{
					$template->assign_vars( array(
						'FILE_LIST' => true,

						'L_NEW_FILE' => $user->lang['New_file'],
						'L_RATE' => $user->lang['DlRating'],
						'L_DOWNLOADS' => $user->lang['Dls'],
						'L_DATE' => $user->lang['Date'],
						'L_UPDATE_TIME' => $user->lang['Update_time'],
						'L_NAME' => $user->lang['Name'],
						'L_FILE' => $user->lang['File'],
						'L_SUBMITER' => $user->lang['Submiter'],
						'L_CATEGORY' => $user->lang['Category'] )
					);

					$file_ids = array();
					for( $i = 0; $i < count( $rowset ); $i++ )
					{
						$formated_date = Date( 'Y-m-d', $selected_date );
						$file_date = Date( 'Y-m-d', $rowset[$i]['file_time'] );
						if ( $file_date == $formated_date )
						{
							$file_ids[] = $rowset[$i]['file_id'];
						}
					}
					$file_ids = implode( ', ', $file_ids );
					if ( !empty( $file_ids ) )
					{
						switch ( SQL_LAYER )
						{
							case 'oracle':
								$sql = "SELECT f1.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, c.cat_id, c.cat_name, COUNT(c.comments_id) AS total_comments, c.cat_name, c.cat_allow_ratings, c.cat_allow_comments
								FROM " . PA_FILES_TABLE . " AS f1, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_CATEGORY_TABLE . " AS c, " . PA_COMMENTS_TABLE . " AS cm
								WHERE f1.file_id = r.votes_file(+)
								AND f1.user_id = u.user_id(+)
								AND c.cat_id = f1.file_catid
								AND f1.file_id IN ($file_ids)
								AND f1.file_approved = '1'
								AND f1.file_id = cm.file_id(+)
								GROUP BY f1.file_id
								ORDER BY file_time DESC";
								break;

							default:
								$sql = "SELECT f1.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, c.cat_id, c.cat_name, COUNT(cm.comments_id) AS total_comments, c.cat_name, c.cat_allow_ratings, c.cat_allow_comments
								FROM  " . PA_CATEGORY_TABLE . " AS c, " . PA_FILES_TABLE . " AS f1
									LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f1.file_id = r.votes_file
									LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
									LEFT JOIN " . PA_COMMENTS_TABLE . " AS cm ON f1.file_id = cm.file_id
								WHERE c.cat_id = f1.file_catid
								AND f1.file_id IN ($file_ids)
								AND f1.file_approved = '1'
								GROUP BY f1.file_id
								ORDER BY file_time DESC";
								break;
						}

						if ( !( $result = $db->sql_query_limit($sql, 300) ) )
						{
							mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
						}

						$file_rowset = array();
						while ( $row = $db->sql_fetchrow( $result ) )
						{
							$file_rowset[] = $row;
						}

						$db->sql_freeresult( $result );
					}
					else
					{
						$file_rowset = array();
					}

					$pa_use_ratings = false;
					for ( $i = 0; $i < count( $file_rowset ); $i++ )
					{
						if ( $this->ratings[$file_rowset[$i]['file_catid']]['activated'] )
						{
							$pa_use_ratings = true;
							break;
						}
					}

					for ( $i = 0; $i < count( $file_rowset ); $i++ )
					{
						$cat_url = mx_append_sid( $this->this_mxurl( 'action=category&cat_id=' . $file_rowset[$i]['file_catid'] ) );
						$file_url = mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $file_rowset[$i]['file_id'] ) );
						// ===================================================
						// Format the date for the given file
						// ===================================================
						$date = phpBB2::create_date( $board_config['default_dateformat'], $file_rowset[$i]['file_time'], $board_config['board_timezone'] );
						$date_updated = phpBB2::create_date( $board_config['default_dateformat'], $file_rowset[$i]['file_update_time'], $board_config['board_timezone'] );
						// ===================================================
						// Get rating for the file and format it
						// ===================================================
						$rating = ( $file_rowset[$i]['rating'] != 0 ) ? round( $file_rowset[$i]['rating'], 2 ) . ' / 10' : $user->lang['Not_rated'];
						// ===================================================
						// If the file is new then put a new image in front of it
						// ===================================================
						$is_new = false;
						if ( time() - ( $pafiledb_config['settings_newdays'] * 24 * 60 * 60 ) < $file_rowset[$i]['file_time'] )
						{
							$is_new = true;
						}

						$cat_name = $file_rowset[$i]['cat_name'];
						// ===================================================
						// Get the post icon fot this file
						// ===================================================
						if ( $file_rowset[$i]['file_pin'] != FILE_PINNED )
						{
							if ( $file_rowset[$i]['file_posticon'] == 'none' || $file_rowset[$i]['file_posticon'] == 'none.gif' )
							{
								$posticon = '&nbsp;';
							}
							else
							{
								$posticon = '<img src="' . $module_root_path . ICONS_DIR . $file_rowset[$i]['file_posticon'] . '" border="0" />';
							}
						}
						else
						{
							$posticon = '<img src="' . $user->img('icon_pa_folder_sticky', '', false, '', 'src') . '" border="0" />';
						}

						$poster = ( $file_rowset[$i]['user_id'] != ANONYMOUS ) ? '<a href="' . mx_append_sid( $phpbb_root_path . 'profile.' . $phpEx . '?mode=viewprofile&amp;' . POST_USERS_URL . '=' . $file_rowset[$i]['user_id'] ) . '">' : '';
						$poster .= ( $file_rowset[$i]['user_id'] != ANONYMOUS ) ? $file_rowset[$i]['username'] : $user->lang['Guest'];
						$poster .= ( $file_rowset[$i]['user_id'] != ANONYMOUS ) ? '</a>' : '';
						// ===================================================
						// Assign Vars
						// ===================================================
						$template->assign_block_vars( 'files_row', array( 'CAT_NAME' => $cat_name,
							'FILE_NEW_IMAGE' => $user->img('icon_pa_file_new', '', false, '', 'src'),
							'PIN_IMAGE' => $posticon,

							'IS_NEW_FILE' => $is_new,
							'FILE_NAME' => $file_rowset[$i]['file_name'],
							'FILE_DESC' => $file_rowset[$i]['file_desc'],
							'FILE_SUBMITER' => $poster,
							'DATE' => $date,
							'UPDATED' => $date_updated,
							'RATING' => ( $file_rowset[$i]['cat_allow_ratings'] ? $rating : $user->lang['kb_no_ratings'] ),
							'DOWNLOADS' => $file_rowset[$i]['file_dls'],

							'SHOW_RATINGS' => ( $pa_use_ratings ) ? true : false,

							'U_FILE' => $file_url,
							'U_CAT' => $cat_url )
						);
					}
				}

				break;
			case 'downloads':
			case 'rating':
				$rating_field = ( $mode == 'rating' ) ? ', AVG(r.rate_point) AS rating' : '';
				$join_statement = ( $mode == 'rating' ) ? 'LEFT JOIN ' . PA_VOTES_TABLE . ' AS r ON f.file_id = r.votes_file' : '';
				$group_statement = ( $mode == 'rating' ) ? 'GROUP BY f.file_id' : '';

				$sql = "SELECT file_id$rating_field
					FROM " . PA_FILES_TABLE . " AS f
					$join_statement
					WHERE f.file_approved = '1'
					$group_statement
					ORDER BY f.file_time DESC";

				if ( !( $result = $db->sql_query_limit( $sql, 300 ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Couldnt Query category info for parent categories", '', __LINE__, __FILE__, $sql );
				}

				$file_num = 0;

				if ( $mode == 'downloads' )
				{
					while ( $row = $db->sql_fetchrow( $result ) )
					{
						$file_num = count($row);
					}				
				}
				else
				{
					while ( $row = $db->sql_fetchrow( $result ) )
					{
						if ( !empty( $row['rating'] ) )
						{
							$file_num++;
						}
					}
				}

				$limit = $most_num;
				if ( $most_type == 'per' )
				{
					$limit = $most_num / 100;
					$limit = $file_num * $limit;
					$limit = round( $limit );
				}
				$limit = ( $limit <= 0 ) ? 1 : $limit;

				$template->assign_vars( array(
					'IS_POPULAR' => true,
					'FILE_LIST' => true,

					'L_NEW_FILES' => sprintf( ( $most_type == 'num' ) ? $user->lang['Popular_num'] : $user->lang['Popular_per'], $most_num, $file_num ),
					'L_NEW_FILE' => $user->lang['New_file'],
					'L_SHOW_TOP' => $user->lang['Show_top'],
					'L_OR_TOP' => $user->lang['Or_top'],
					'L_RATE' => $user->lang['DlRating'],
					'L_DOWNLOADS' => $user->lang['Dls'],
					'L_DATE' => $user->lang['Date'],
					'L_UPDATE_TIME' => $user->lang['Update_time'],
					'L_NAME' => $user->lang['Name'],
					'L_FILE' => $user->lang['File'],
					'L_SUBMITER' => $user->lang['Submiter'],
					'L_CATEGORY' => $user->lang['Category'],

					'U_TOP_10' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=' . $mode . '&most_type=num&most_num=10' ) ),
					'U_TOP_25' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=' . $mode . '&most_type=num&most_num=25' ) ),
					'U_TOP_50' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=' . $mode . '&most_type=num&most_num=50' ) ),

					'U_TOP_PER_1' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=' . $mode . '&most_type=per&most_num=1' ) ),
					'U_TOP_PER_5' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=' . $mode . '&most_type=per&most_num=5' ) ),
					'U_TOP_PER_10' => mx_append_sid( $this->this_mxurl( 'action=toplist&mode=' . $mode . '&most_type=per&most_num=10' ) ) )
				);

				if ( $limit )
				{
					$sort_method = ( $mode == 'downloads' ) ? 'file_dls' : 'rating';
					$sql_limit = "LIMIT 0, $limit ";
					switch ( SQL_LAYER )
					{
						case 'oracle':
							$sql = "SELECT f1.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, c.cat_id, c.cat_name, c.cat_name, c.cat_allow_ratings, c.cat_allow_comments
								FROM " . PA_FILES_TABLE . " AS f1, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_CATEGORY_TABLE . " AS c
								WHERE f1.file_id = r.votes_file(+)
								AND f1.user_id = u.user_id(+)
								AND c.cat_id = f1.file_catid
								AND f1.file_approved = '1'
								GROUP BY f1.file_id
								ORDER BY $sort_method DESC
								$sql_limit";
							break;

						default:
							$sql = "SELECT f1.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, c.cat_id, c.cat_name, c.cat_allow_ratings, c.cat_allow_comments
								FROM  " . PA_CATEGORY_TABLE . " AS c, " . PA_FILES_TABLE . " AS f1
								LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f1.file_id = r.votes_file
								LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
								WHERE c.cat_id = f1.file_catid
								AND f1.file_approved = '1'
								GROUP BY f1.file_id
								ORDER BY $sort_method DESC";
							break;
					}

					if ( !( $result = $db->sql_query_limit( $sql, $sql_limit ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldnt Query category info for parent categories', '', __LINE__, __FILE__, $sql );
					}
				}
				$searchset = array();
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$searchset[] = $row;
				}

				$pa_use_ratings = false;
				for ( $i = 0; $i < count( $searchset ); $i++ )
				{
					if ( $this->ratings[$searchset[$i]['file_catid']]['activated'] )
					{
						$pa_use_ratings = true;
						break;
					}
				}

				for( $i = 0; $i < count( $searchset ); $i++ )
				{
					if ( $mode == 'rating' )
					{
						if ( empty( $searchset[$i]['rating'] ) )
						{
							continue;
						}
					}

					$cat_url = mx_append_sid( $this->this_mxurl( 'action=category&cat_id=' . $searchset[$i]['cat_id'] ) );
					$file_url = mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $searchset[$i]['file_id'] ) );
					// ===================================================
					// Format the date for the given file
					// ===================================================
					$date = phpBB2::create_date( $board_config['default_dateformat'], $searchset[$i]['file_time'], $board_config['board_timezone'] );
					$date_updated = phpBB2::create_date( $board_config['default_dateformat'], $searchset[$i]['file_update_time'], $board_config['board_timezone'] );
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

					$template->assign_block_vars( 'files_row', array(
						'CAT_NAME' => $searchset[$i]['cat_name'],
						'FILE_NEW_IMAGE' => $user->img('icon_pa_file_new', '', false, '', 'src'),
						'PIN_IMAGE' => $posticon,

						'IS_NEW_FILE' => $is_new,
						'FILE_NAME' => $searchset[$i]['file_name'],
						'FILE_DESC' => $searchset[$i]['file_desc'],
						'FILE_SUBMITER' => $poster,
						'DATE' => $date,
						'UPDATED' => $date_updated,
						'RATING' => ( $searchset[$i]['cat_allow_ratings'] ? $rating : $user->lang['kb_no_ratings'] ),
						'SHOW_RATINGS' => ( $pa_use_ratings ?  true : false ),
						'DOWNLOADS' => $searchset[$i]['file_dls'],
						'U_FILE' => $file_url,
						'U_CAT' => $cat_url )
					);
				}
				break;
		}

		$template->assign_vars( array( 'SHOW_RATINGS' => ( $pa_use_ratings ) ? true : false ) );

		// ===================================================
		// assign var for navigation
		// ===================================================

		$this->display( $user->lang['Download'], 'pa_toplist_body.'.$tplEx );
	}
}

?>