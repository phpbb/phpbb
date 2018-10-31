<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_lists.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
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
class pafiledb_lists extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $phpEx, $pafiledb_config, $userdata, $tplEx;
		global $mx_root_path, $module_root_path, $is_block, $mx_request_vars;
		global $toplist_page_id, $toplist_config;

		// =======================================================
		// Request vars
		// =======================================================
		$start = $mx_request_vars->request('pa_start', MX_TYPE_INT, 0);

		//
		// Sorting of items
		//
		if ( isset( $toplist_config['toplist_sort_method'] ) )
		{
			switch ( $toplist_config['toplist_sort_method'] )
			{
				case 'latest':
					$sort_method = 'file_time';
					break;
				case 'most_popular':
					$sort_method = 'file_dls';
					break;
				case 'toprated':
					$sort_method = 'rating';
					break;
				case 'random':
					$sort_method = 'RAND()';
					break;
				default:
					$sort_method = $pafiledb_config['sort_method'];
			}
		}
		else
		{
			$sort_method = $pafiledb_config['sort_method'];
		}

		$sort_order = 'DESC';

		$msg_today = date('mdY');
		switch( $toplist_config['toplist_filter_date'] )
		{
			case '0':
				$msg_time_filter_lo = 'no';
				break;
			case '1':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2)), intval(substr($msg_today, 2, 2) - 1), intval(substr($msg_today, 4, 4)));
				break;
			case '2':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2)), intval(substr($msg_today, 2, 2) - 1), intval(substr($msg_today, 4, 4)));
				break;
			case '3':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2)), intval(substr($msg_today, 2, 2) - 1), intval(substr($msg_today, 4, 4)));
				break;
			case '4':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2)), intval(substr($msg_today, 2, 2) - 7), intval(substr($msg_today, 4, 4)));
				break;
			case '5':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2)), intval(substr($msg_today, 2, 2) - 14), intval(substr($msg_today, 4, 4)));
				break;
			case '6':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2)), intval(substr($msg_today, 2, 2) - 21), intval(substr($msg_today, 4, 4)));
				break;
			case '7':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2) - 1), intval(substr($msg_today, 2, 2)), intval(substr($msg_today, 4, 4)));
				break;
			case '8':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2) - 2), intval(substr($msg_today, 2, 2)), intval(substr($msg_today, 4, 4)));
				break;
			case '9':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2) - 3), intval(substr($msg_today, 2, 2)), intval(substr($msg_today, 4, 4)));
				break;
			case '10':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2) - 6), intval(substr($msg_today, 2, 2)), intval(substr($msg_today, 4, 4)));
				break;
			case '11':
				$msg_time_filter_lo = mktime(0, 0, 0 , intval(substr($msg_today, 0, 2)), intval(substr($msg_today, 2, 2)), intval(substr($msg_today, 4, 4) - 1));
				break;
			default:
				$msg_time_filter_lo = 'no';
				break;
		}

		if ( $msg_time_filter_lo != 'no' && !empty($msg_time_filter_lo) )
		{
			$sql_xtra = " AND f1.file_time > " . $msg_time_filter_lo;
		}

		$filter_cat_id = false;
		if (intval($toplist_config['toplist_cat_id']) > 0)
		{
			$filter_cat_id = array();
			$filter_cat_id['parent'] = $toplist_config['toplist_cat_id'];
		}

		$no_file_message = true;
		$filelist = false;

		$sort_options_list = unserialize($toplist_config['toplist_display_options']);

		$tmp_pag = $pafiledb_config['pagination'];
		$pafiledb_config['pagination'] = $toplist_config['toplist_pagination'];
		$total_num_items = $this->display_items( $sort_method, $sort_order, $start, $filter_cat_id, true, $sort_options_list, $sql_xtra, $toplist_page_id );
		$pafiledb_config['pagination'] = $tmp_pag;

		$template->assign_vars( array(
			'U_DOWNLOAD' => mx_append_sid( $this->this_mxurl() ),
			'DOWNLOAD' => $pafiledb_config['module_name'],

			'BLOCK_PAGINATION' => mx_generate_pagination(mx_append_sid( $this->this_mxurl( '', false, false ) ), $total_num_items, $toplist_config['toplist_pagination'], $start, true, true, true, false, 'pa_start'),
		));

		if ($toplist_config['toplist_use_pagination'] == 'TRUE')
		{
			$template->assign_block_vars( "toplist_pagination", array());
		}

		$this->display( $user->lang['Download'], 'pa_lists.'.$tplEx );
	}
}
?>