<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_stats.php,v 1.3 2011/12/30 04:21:03 orynider Exp $
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
class pafiledb_stats extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $board_config, $phpEx, $pafiledb_config, $db, $images;
		global $phpbb_root_path, $userdata, $tplEx;
		global $mx_root_path, $module_root_path, $is_block;

		if ( !$this->auth_global['auth_stats'] )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(mx_append_sid($mx_root_path . "login.$phpEx?redirect=".$this->this_mxurl("action=stats"), true));
			}

			$message = sprintf( $user->lang['Sorry_auth_stats'], $this->auth_global['auth_stats_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$num['cats'] = $this->total_cat;

		$sql = "SELECT file_id
			FROM " . PA_FILES_TABLE . "
			WHERE file_approved = '1'";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}

		$num['files'] = count($db->sql_fetchrow($result));
		$db->sql_freeresult( $result );

		$sql = 'SELECT file_id, file_name
			FROM ' . PA_FILES_TABLE . "
			WHERE file_approved = '1'
			ORDER BY file_time DESC";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}

		$newest = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );

		$sql = 'SELECT file_id, file_name
			FROM ' . PA_FILES_TABLE . "
			WHERE file_approved = '1'
			ORDER BY file_time ASC";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}

		$oldest = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );

		$sql = "SELECT r.votes_file, AVG(r.rate_point) AS rating, f.file_id, f.file_name
			FROM " . PA_VOTES_TABLE . " AS r, " . PA_FILES_TABLE . " AS f
			WHERE r.votes_file = f.file_id
			AND f.file_approved = '1'
			GROUP BY f.file_id
			ORDER BY rating DESC";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}
		$popular = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );

		$sql = "SELECT r.votes_file, AVG(r.rate_point) AS rating, f.file_id, f.file_name
			FROM " . PA_VOTES_TABLE . " AS r, " . PA_FILES_TABLE . " AS f
			WHERE r.votes_file = f.file_id
			AND f.file_approved = '1'
			GROUP BY f.file_id
			ORDER BY rating ASC";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}

		$lpopular = $db->sql_fetchrow( $result );
		$total_votes = $total_rating = 0;

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$total_rating += $row['rating'];
			$total_votes++;
		}
		$db->sql_freeresult( $result );
		$sql = "SELECT file_id, file_name, file_dls
			FROM " . PA_FILES_TABLE . "
			WHERE file_approved = '1'
			ORDER BY file_dls DESC";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}

		$mostdl = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );

		$sql = "SELECT file_id, file_name, file_dls
			FROM " . PA_FILES_TABLE . "
			WHERE file_approved = '1'
			ORDER BY file_dls ASC";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}

		$leastdl = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );

		$sql = "SELECT file_dls
			FROM " . PA_FILES_TABLE . "
			WHERE file_approved = '1'";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query stat info', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$totaldls += $row['file_dls'];
		}
		$db->sql_freeresult( $result );

		$avg = @round( $total_rating / $total_votes );

		$avgdls = @round( $totaldls / $num['files'] );

		$user->setup('mods/pafiledb_main');

		$user->lang['Stats_text'] = str_replace( "{total_files}", $num['files'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{total_categories}", $num['cats'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{total_downloads}", $totaldls, $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{u_newest_file}", mx_append_sid( $this->this_mxurl( "action=file&file_id=" . $newest['file_id'] ) ), $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{newest_file}", $newest['file_name'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{u_oldest_file}", mx_append_sid( $this->this_mxurl( "action=file&file_id=" . $oldest['file_id'] ) ), $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{oldest_file}", $oldest['file_name'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{average}", $avg, $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{u_popular}", mx_append_sid( $this->this_mxurl( "action=file&file_id=" . $popular['file_id'] ) ), $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{popular}", $popular['file_name'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{most}", round( $popular['rating'], 2 ), $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{u_lpopular}", mx_append_sid( $this->this_mxurl( "action=file&file_id=" . $lpopular['file_id'] ) ), $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{lpopular}", $lpopular['file_name'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{least}", round( $lpopular['rating'], 2 ), $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{avg_dls}", $avgdls, $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{u_most_dl}", mx_append_sid( $this->this_mxurl( "action=file&file_id=" . $mostdl['file_id'] ) ), $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{most_dl}", $mostdl['file_name'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{most_no}", $mostdl['file_dls'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{u_least_dl}", mx_append_sid( $this->this_mxurl( "action=file&file_id=" . $leastdl['file_id'] ) ), $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{least_dl}", $leastdl['file_name'], $user->lang['Stats_text'] );
		$user->lang['Stats_text'] = str_replace( "{least_no}", $leastdl['file_dls'], $user->lang['Stats_text'] );

		$agent_lang = array('ANDROID' => 'Android', 'IPHONE' => 'iPhone', 'IPOD' => 'iPod', 'GOOGLE_CHROME' => 'Google Chrome', 'OPERA' => 'Opera', 'IE' => 'Internet Explorer', 'MOZILLA' => 'Mozilla', 'NETSCAPE' => 'NetScape', 'LYNX' => 'Lynx', 'KONQUEROR' => 'Konqueror', 'SAFARI' => 'Safari', 'AOL' => 'AOL' , 'BOT' => 'Bot' , 'OTHER' => 'Other', '' => 'Unknown' );
		$agent_image = array('ANDROID' => 'adroid.gif', 'IPHONE' => 'iphone.gif', 'IPOD' => 'ipod.gif', 'GOOGLE_CHROME' => 'chrome.gif', 'OPERA' => 'opera.gif', 'IE' => 'explorer.gif', 'MOZILLA' => 'mozilla.gif', 'NETSCAPE' => 'netscape.gif', 'LYNX' => 'lynx.gif', 'KONQUEROR' => 'konqueror.gif', 'SAFARI' => 'safari.gif', 'AOL' => 'aol.gif', 'BOT' => 'bot.gif', 'OTHER' => 'altavista.gif', '' => 'none.gif' );
		$agent_point = array('ANDROID' => 0, 'IPHONE' => 0, 'IPOD' => 0, 'OPERA' => 0, 'GOOGLE_CHROME' => 0, 'IE' => 0, 'MOZILLA' => 0, 'NETSCAPE' => 0, 'LYNX' => 0, 'KONQUEROR' => 0, 'SAFARI' => 0, 'AOL' => 0, 'BOT' => 0, 'OTHER' => 0, '' => 0);

		$os_lang = array('Android' => 'Android', 'Win' => 'Windows', 'Mac' => 'Macintosh', 'Linux' => 'Linux', 'Unix' => 'Unix', 'FreeBSD' => 'FreeBSD', 'BeOS' => 'BeOS', 'Ubuntu' => 'Linux Ubuntu', 'Ubuntu' => 'Linux Fedora', 'OS2' => 'OS2', 'IRIX' => 'Irix', 'SunOS' => 'SunOS', 'Aix' => 'Aix', 'PalmOS' => 'Palm OS', 'Other' => 'Other', '' => 'Unknown' );
		$os_image = array('Android' => 'android.gif', 'Win' => 'windows.gif', 'Mac' => 'mac.gif', 'Linux' => 'linux.gif', 'Unix' => 'unix.gif', 'FreeBSD' => 'bsd.gif', 'BeOS' => 'be.gif', 'Ubuntu' => 'ubuntu.gif', 'Fedora' => 'fedora.gif', 'OS2' => 'os2.gif', 'IRIX' => 'irix.gif', 'SunOS' => 'sun.gif', 'Aix' => 'aix.gif', 'PalmOS' => 'palm.gif', 'Other' => 'question.gif', '' => 'none.gif' );
		$os_point = array('Android' => 0, 'Win' => 0, 'Mac' => 0, 'Linux' => 0, 'Unix' => 0, 'FreeBSD' => 0, 'BeOS' => 0, 'Ubuntu' => 0, 'Fedora' => 0, 'OS2' => 0, 'IRIX' => 0, 'SunOS' => 0, 'Aix' => 0, 'PalmOS' => 0, 'Other' => 0, '' => 0 );

		$sql = "SELECT downloader_os, downloader_browser
			FROM " . PA_DOWNLOAD_INFO_TABLE;

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not obtain downloads info', '', __LINE__, __FILE__, $sql );
		}

		$row_downloads = $db->sql_fetchrowset( $result );
		$db->sql_freeresult( $result );

		for( $i = 0; $i < count( $row_downloads ); $i++ )
		{
			$os_point[$row_downloads[$i]['downloader_os']]++;
			$agent_point[$row_downloads[$i]['downloader_browser']]++;
		}

		$os_graphic = 0;
		$os_graphic_max = count( $images['pa_voting_graphic'] );

		foreach( $os_point as $index => $point )
		{
			$temp_point = ( $point > 100 ) ? 100 : $point;
			$os_graphic_img = $images['pa_voting_graphic'][$os_graphic];
			$os_graphic = ( $os_graphic < $os_graphic_max - 1 ) ? $os_graphic + 1 : 0;

			$template->assign_block_vars( "downloads_os", array( 'OS_IMG' => $module_root_path . "pafiledb/images/stats/" . $os_image[$index],
				'OS_NAME' => $os_lang[$index],
				'OS_OPTION_RESULT' => $point,
				'OS_OPTION_IMG' =>  $os_graphic_img,
				'OS_OPTION_IMG_WIDTH' => $temp_point * 2 )
			);
		}

		$b_graphic = 0;
		$b_graphic_max = count( $images['pa_voting_graphic'] );

		foreach( $agent_point as $index => $point )
		{
			$temp_point = ( $point > 100 ) ? 100 : $point;
			$b_graphic_img = $images['pa_voting_graphic'][$b_graphic];
			$b_graphic = ( $b_graphic < $b_graphic_max - 1 ) ? $b_graphic + 1 : 0;

			$template->assign_block_vars( "downloads_b", array(
				'B_IMG' => $module_root_path . "pafiledb/images/stats/" . $agent_image[$index],
				'B_NAME' => $agent_lang[$index],
				'B_OPTION_RESULT' => $point,
				'B_OPTION_IMG' => $b_graphic_img,
				'B_OPTION_IMG_WIDTH' => $temp_point * 2 )
			);
		}

		$agent_point = array('ANDROID' => 0, 'IPHONE' => 0, 'IPOD' => 0, 'OPERA' => 0, 'GOOGLE_CHROME' => 0, 'IE' => 0, 'MOZILLA' => 0, 'NETSCAPE' => 0, 'LYNX' => 0, 'KONQUEROR' => 0, 'SAFARI' => 0, 'AOL' => 0, 'BOT' => 0, 'OTHER' => 0, '' => 0);
		$os_point = array('Android' => 0, 'Win' => 0, 'Mac' => 0, 'Linux' => 0, 'Unix' => 0, 'FreeBSD' => 0, 'BeOS' => 0, 'Ubuntu' => 0, 'Fedora' => 0, 'OS2' => 0, 'IRIX' => 0, 'SunOS' => 0, 'Aix' => 0, 'PalmOS' => 0, 'Other' => 0, '' => 0);

		$sql = "SELECT voter_os, voter_browser
			FROM " . PA_VOTES_TABLE;

		if (!($result = $db->sql_query($sql)))
		{
			mx_message_die(GENERAL_ERROR, 'Could not obtain downloads info', '', __LINE__, __FILE__, $sql);
		}

		$row_ratings = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		for( $i = 0; $i < count( $row_ratings ); $i++ )
		{
			$os_point[$row_ratings[$i]['voter_os']]++;
			$agent_point[$row_ratings[$i]['voter_browser']]++;
		}

		$os_graphic = 0;
		$os_graphic_max = count( $images['pa_voting_graphic'] );

		foreach( $os_point as $index => $point )
		{
			$temp_point = ( $point > 100 ) ? 100 : $point;
			$os_graphic_img = $images['pa_voting_graphic'][$os_graphic];
			$os_graphic = ( $os_graphic < $os_graphic_max - 1 ) ? $os_graphic + 1 : 0;

			$template->assign_block_vars( "rating_os", array(
				'OS_IMG' => $module_root_path . "pafiledb/images/stats/" . $os_image[$index],
				'OS_NAME' => $os_lang[$index],
				'OS_OPTION_RESULT' => $point,
				'OS_OPTION_IMG' => $os_graphic_img,
				'OS_OPTION_IMG_WIDTH' => $temp_point )
			);
		}

		$b_graphic = 0;
		$b_graphic_max = count( $images['pa_voting_graphic'] );

		foreach( $agent_point as $index => $point )
		{
			$temp_point = ( $point > 100 ) ? 100 : $point;
			$b_graphic_img = $images['pa_voting_graphic'][$b_graphic];
			$b_graphic = ( $b_graphic < $b_graphic_max - 1 ) ? $b_graphic + 1 : 0;

			$template->assign_block_vars( "rating_b", array(
				'B_IMG' => $module_root_path . "pafiledb/images/stats/" . $agent_image[$index],
				'B_NAME' => $agent_lang[$index],
				'B_OPTION_RESULT' => $point,
				'B_OPTION_IMG' => $b_graphic_img,
				'B_OPTION_IMG_WIDTH' => $temp_point )
			);
		}

		$template->assign_vars( array(
			'S_ACTION_CHART' => mx_append_sid( $this->this_mxurl( 'action=stats' ) ),
			'L_STATISTICS' => $user->lang['Statistics'],

			'L_INDEX' => "<<",
			'L_GENERAL_INFO' => $user->lang['General_Info'],
			'L_DOWNLOADS_STATS' => $user->lang['Downloads_stats'],
			'L_RATING_STATS' => $user->lang['Rating_stats'],
			'L_OS' => $user->lang['Os'],
			'L_BROWSERS' => $user->lang['Browsers'],

			'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
			'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ),

			'U_VOTE_LCAP' => $images['mx_vote_lcap'],
			'U_VOTE_RCAP' => $images['mx_vote_rcap'],

			'DOWNLOAD' => $pafiledb_config['module_name'],
			'STATS_TEXT' => $user->lang['Stats_text'] )
		);

		// ===================================================
		// assign var for navigation
		// ===================================================

		$this->display( $user->lang['Download'], 'pa_stats_body.'.$tplEx );
	}
}
?>