<?php 
/**
*
* @package mx_mod
* @version $Id: functions_mxp.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 mxBB Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
* @link http://www.mxbb.net
*
*/

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
}


if(!function_exists('mx_message_die'))
{
	/**
	 * Dummy function
	 */
	function mx_message_die($msg_code, $msg_text = '', $msg_title = '', $err_line = '', $err_file = '', $sql = '')
	{
		global $user;
	
		switch($msg_code)
		{
			case GENERAL_MESSAGE:
				if ( $msg_title == '' )
				{
					$msg_title = $user->lang['Information'];
				}
				break;

			case CRITICAL_MESSAGE:
				if ( $msg_title == '' )
				{
					$msg_title = $user->lang['Critical_Information'];
				}
				break;

			case GENERAL_ERROR:
				if ( $msg_text == '' )
				{
					$msg_text = $user->lang['An_error_occured'];
				}

				if ( $msg_title == '' )
				{
					$msg_title = $user->lang['General_Error'];
				}
				break;

			case CRITICAL_ERROR:

				if ($msg_text == '')
				{
					$msg_text = $user->lang['A_critical_error'];
				}

				if ($msg_title == '')
				{
					$msg_title = 'MX-Publisher : <b>' . $user->lang['Critical_Error'] . '</b>';
				}
				break;
		}
	
	
		trigger_error($msg_title . ': ' . $msg_text);
	}
}
/**
* Append session id to url
 * Dummy function
 */
if(!function_exists('mx3_append_sid'))
{
	function mx3_append_sid($url, $params = false, $is_amp = true, $session_id = false, $mod_rewrite_only = false)
	{

		// Append session id and parameters (even if they are empty)
		return append_sid($url, $params, $is_amp, $session_id);
	}
}
if(!function_exists('mx_append_sid'))
{
	function mx_append_sid($url, $non_html_amp = false, $mod_rewrite_only = false)
	{
		// Append session id and parameters (even if they are empty)
		return append_sid($url, false, $non_html_amp, false);
	}
}

/**
 * Redirect.
 *
 * mxBB version of phpBB redirect().
 *
 * @param string $url
 * @param string $redirect_msg
 * @param string $redirect_link
 */
if(!function_exists('mx_redirect'))
{
	function mx_redirect($url, $redirect_msg = '', $redirect_link = '')
	{
		global $db, $user;

		if ( empty($redirect_msg) )
		{
			$redirect_msg = $user->lang['Page_Not_Authorised'];
		}
		if ( empty($redirect_link) )
		{
			$redirect_link = $user->lang['Redirect_login'];
		}

		if ( defined('HEADER_INC') )
		{
			$message = $redirect_msg . '<br /><br />' . sprintf($redirect_link, '<a href="' . PHPBB_URL . $url . '">', "</a>") . '<br /><br />';
			mx_message_die(GENERAL_MESSAGE, $message);
		}

		//
		// Save any possible changes made in session variables, otherwise we will loose them.
		// See comments here:
		// http://www.php.net/session
		// http://www.php.net/session_write_close
		//
		@session_write_close();
		@session_start();

		if ( !empty($db) )
		{
			@$db->sql_close();
		}

		if ( strstr(urldecode($url), "\n") || strstr(urldecode($url), "\r") )
		{
			mx_message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
		}

		// Redirect via an HTML form for PITA webservers
		if ( @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) )
		{
			header('Refresh: 0; URL=' . PHPBB_URL . $url);
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta http-equiv="refresh" content="0; url=' . PHPBB_URL . $url . '"><title>Redirect</title></head><body><div align="center">If your browser does not support meta redirection please click <a href="' . PHPBB_URL . $url . '">HERE</a> to be redirected</div></body></html>';
			exit;
		}

		// Behave as per HTTP/1.1 spec for others
		header('Location: ' . PHPBB_URL . $url);
		exit;
	}
}
/**
 * Get groups
 *
 * @param unknown_type $sel_id
 * @param unknown_type $field_entry
 * @param unknown_type $group_rowset
 * @return unknown
 */
if(!function_exists('mx_get_groups'))
{
	function mx_get_groups($sel_id, $field_entry = 'auth_view_group', $group_rowset = array())
	{
	 	global $db, $user;

	 	if (empty($group_rowset))
	 	{
			// Get us all the groups
			$sql = 'SELECT g.group_id, g.group_name, g.group_type
				FROM ' . GROUPS_TABLE . ' g
				ORDER BY g.group_type ASC, g.group_name';
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Couldn't get group list", "", __LINE__, __FILE__, $sql );
			}

			$group_rowset = @$db->sql_fetchrowset($result);
	 	}

		$grouplist = '<select name="'.$field_entry.'">';
		$grouplist .= '<option value="0">' . $user->lang['Select_group'] . '</option>';

		if ($group_rowset)
		{
			foreach($group_rowset as $key => $row)
			{
				$selected = ( $sel_id == $row['group_id'] ? ' selected="selected"' : '' );
				$grouplist .= '<option value="' .$row['group_id'] . '"' . $selected . '>' . $row['group_name'] . '</option>';
			}
		}
		else
		{
				$row = @$db->sql_fetchrow($result);
				$selected = ( $sel_id == $row['group_id'] ? ' selected="selected"' : '' );
				$grouplist .= '<option value="' .$row['group_id'] . '"' . $selected . '>' . $row['group_name'] . '</option>';
		}
	 	$db->sql_freeresult($result);

		$grouplist .= '</select>';
		return $grouplist;
	}
}

/**
 * Is group member?
 *
 * Validates if user belongs to group included in group_ids list.
 * Also, adds all usergroups to userdata array.
 *
 * @param unknown_type $group_ids
 * @param unknown_type $group_mod_mode
 * @return unknown
 */
if(!function_exists('mx_is_group_member'))
{
	function mx_is_group_member($group_ids = '', $group_mod_mode = false)
	{
		global $userdata, $db;

		if( empty($group_ids) )
		{
			return false;
		}

		//
		// Try to reuse group_id results.
		//
		$userdata_key = 'mx_usergroups' . ( $group_mod_mode ? '_mod' : '' ) . $userdata['user_id'];

		if( empty($userdata[$userdata_key]) )
		{
			if( $group_mod_mode )	// Get the groups the user is moderator of.
			{
				$sql = "SELECT group_id FROM " . GROUPS_TABLE . "
					WHERE group_moderator = '" . $userdata['user_id'] . "' AND group_single_user = 0";
			}
			else					// Get the groups the user is member of.
			{
				$sql = "SELECT group_id FROM " . USER_GROUP_TABLE . "
					WHERE user_id = '" . $userdata['user_id'] . "' AND user_pending = 0";
			}
			if ( !($result = $db->sql_query($sql, 300)) )
			{
				mx_message_die(GENERAL_ERROR, "Could not query group rights information");
			}
			$userdata[$userdata_key] = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
		}

		$group_ids_array = explode(',', $group_ids);

		for( $i = 0; $i < count($userdata[$userdata_key]); $i++ )
		{
			if( in_array($userdata[$userdata_key][$i]['group_id'], $group_ids_array) )
			{
				return true;
			}
		}
		return false;
	}
}

/**
 * Get langcode.
 *
 * This function loops all meta langcodes, to convert internal MX-Publisher lang to standard langcode
 *
 */
if(!function_exists('mx_get_langcode'))
{
	function mx_get_langcode()
	{
		global $userdata, $mx_mod_path, $board_config, $phpEx;

		//
		// Load language file.
		//
		if( @file_exists($mx_mod_path . 'language/lang_' . $board_config['default_lang'] . '/lang_meta.' . $phpEx) )
		{
			include($mx_mod_path . 'language/lang_' . $board_config['default_lang'] . '/lang_meta.' . $phpEx);
		}
		else
		{
			include($mx_mod_path . 'language/lang_english/lang_meta.' . $phpEx);
		}

		foreach ($user->lang['mx_meta']['langcode'] as $user->langcode => $mxbbLang)
		{
			if ( strtolower($mxbbLang) == $userdata['user_lang'] )
			{
				return $user->langcode;
			}
		}
	}
}

/**
 * Generate Pagination.
 *
 * Pagination routine, generates page number sequence.
 * Only difference from standard phpbb function is you can use more settings.
 *
 * @param string $base_url
 * @param integer $num_items
 * @param integer $per_page
 * @param integer $start_item
 * @param boolean $add_prevnext_text
 * @param boolean $use_next_symbol
 * @param boolean $use_previous_symbol
 * @param boolean $add_preinfo_text
 * @param integer $name_id
 * @return string (html)
 */
if(!function_exists('mx_generate_pagination'))
{
	function mx_generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE, $use_next_symbol = false,$use_previous_symbol = false,$add_preinfo_text = TRUE, $name_id = 'start')
	{
		global $user;

		$total_pages = ceil($num_items/$per_page);

		if ( $total_pages == 1 )
		{
			return '';
		}

		$previous_string = $use_next_symbol ? '&laquo;' : $user->lang['Previous'];
		$next_string = $use_previous_symbol ? '&raquo;' : $user->lang['Next'];

		$class = 'class="mx_pagination" onmouseover="if(this.className){this.className=\'mx_pagination_over\';}" onmouseout="if(this.className){this.className=\'mx_pagination\';}"';

		$on_page = floor($start_item / $per_page) + 1;

		$page_string = '';
		if ( $total_pages > 10 )
		{
			$init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;

			for($i = 1; $i < $init_page_max + 1; $i++)
			{
				$page_string .= ( $i == $on_page ) ? '<b class="mx_pagination_sele">' . $i . '</b>' : '<a '.$class.' href="' . mx_append_sid($base_url . "&amp;".$name_id."=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
				if ( $i <  $init_page_max )
				{
					$page_string .= ",";
				}
			}

			if ( $total_pages > 3 )
			{
				if ( $on_page > 1  && $on_page < $total_pages )
				{
					$page_string .= ( $on_page > 5 ) ? ' ... ' : ',';

					$init_page_min = ( $on_page > 4 ) ? $on_page : 5;
					$init_page_max = ( $on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;

					for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++)
					{
						$page_string .= ($i == $on_page) ? '<b class="mx_pagination_sele">' . $i . '</b>' : '<a '.$class.' href="' . mx_append_sid($base_url . "&amp;".$name_id."=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
						if ( $i <  $init_page_max + 1 )
						{
							$page_string .= ',';
						}
					}

					$page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : ', ';
				}
				else
				{
					$page_string .= ' ... ';
				}

				for($i = $total_pages - 2; $i < $total_pages + 1; $i++)
				{
					$page_string .= ( $i == $on_page ) ? '<b class="mx_pagination_sele">' . $i . '</b>'  : '<a '.$class.' href="' . mx_append_sid($base_url . "&amp;".$name_id."=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
					if( $i <  $total_pages )
					{
						$page_string .= ",";
					}
				}
			}
		}
		else
		{
			for($i = 1; $i < $total_pages + 1; $i++)
			{
				$page_string .= ( $i == $on_page ) ? '<b class="mx_pagination_sele">' . $i . '</b>' : '<a '.$class.' href="' . mx_append_sid($base_url . "&amp;".$name_id."=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
				if ( $i <  $total_pages )
				{
					$page_string .= ',';
				}
			}
		}

		if ( $add_prevnext_text )
		{
			if ( $on_page > 1 )
			{
				$page_string = ' <a '.$class.' href="' . mx_append_sid($base_url . "&amp;".$name_id."=" . ( ( $on_page - 2 ) * $per_page ) ) . '">' . $previous_string . '</a>&nbsp;' . $page_string;
			}

			if ( $on_page < $total_pages )
			{
				$page_string .= '&nbsp;<a '.$class.' href="' . mx_append_sid($base_url . "&amp;".$name_id."=" . ( $on_page * $per_page ) ) . '">' . $next_string . '</a>';
			}

		}
		$pre_text = $add_preinfo_text ? $user->lang['Goto_page'] : '';
		$page_string = $pre_text . ' ' . $page_string;

		return $page_string;
	}
}

/**
* Our own generator of random values
* This uses a constantly changing value as the base for generating the values
* The board wide setting is updated once per page if this code is called
* With thanks to Anthrax101 for the inspiration on this one
* Added in phpBB 2.0.20
*/
if(!function_exists('mx_dss_rand'))
{
	function mx_dss_rand()
	{
		global $db, $portal_config, $board_config, $dss_seeded;

		if($dss_seeded !== true)
		{
			switch (PORTAL_BACKEND)
			{
				case 'internal':

				$val = $portal_config['rand_seed'] . microtime();
				$val = md5($val);
				$portal_config['rand_seed'] = md5($portal_config['rand_seed'] . $val . 'a');

				$sql = "UPDATE " . PORTAL_TABLE . " SET
					rand_seed = '" . $portal_config['rand_seed'] . "'
					WHERE portal_id = '1'";
					break;
					break;

				case 'phpbb2':
				case 'phpbb3':

				$val = $board_config['rand_seed'] . microtime();
				$val = md5($val);
				$board_config['rand_seed'] = md5($board_config['rand_seed'] . $val . 'a');

				$sql = "UPDATE " . CONFIG_TABLE . " SET
					config_value = '" . $board_config['rand_seed'] . "'
					WHERE config_name = 'rand_seed'";
					break;
			}

			if( !$db->sql_query($sql) )
			{
				mx_message_die(GENERAL_ERROR, "Unable to reseed PRNG", "", __LINE__, __FILE__, $sql);
			}

			$dss_seeded = true;
		}

		return substr($val, 4, 16);
	}
}

/**
 * phpBB Smilies pass.
 *
 * Hacking smilies_pass from phpbb/includes/bbcode.php
 *
 * @param string $message
 * @return string
 *
*/
if(!function_exists('mx_smilies_pass'))
{
	function mx_smilies_pass($message)
	{
		static $orig, $repl;
		global $board_config, $mx_root_path, $phpbb_root_path, $phpEx;

		switch (PORTAL_BACKEND)
		{
			case 'internal':
				$smiley_path_url = PHPBB_URL; //change this to PORTAL_URL when shared folder will be removed
				$smiley_url = 'smile_url';
				break;
			case 'phpbb2':
				$smiley_path_url = PHPBB_URL;
				$smiley_url = 'smile_url';
				break;
			case 'phpbb3':
				$smiley_path_url = PHPBB_URL;
				$smiley_url = 'smiley_url';
				$board_config['smilies_path'] = str_replace("smiles", "smilies", $board_config['smilies_path']);
				break;
		}

		$smilies_path = $board_config['smilies_path'];
		$board_config['smilies_path'] = $smiley_path_url . $board_config['smilies_path'];

		if (!isset($orig))
		{
			global $db;
			$orig = $repl = array();

			$sql = 'SELECT * FROM ' . SMILIES_TABLE;
			if( !$result = $db->sql_query($sql) )
			{
				mx_message_die(GENERAL_ERROR, "Couldn't obtain smilies data", "", __LINE__, __FILE__, $sql);
			}

			$smilies = $db->sql_fetchrowset($result);

			if (count($smilies))
			{
				@usort($smilies, 'smiley_sort');
			}

			for ($i = 0; $i < count($smilies); $i++)
			{
				$orig[] = "/(?<=.\W|\W.|^\W)" . preg_quote($smilies[$i]['code'], "/") . "(?=.\W|\W.|\W$)/";
				$repl[] = '<img src="'. $board_config['smilies_path'] . '/' . $smilies[$i][$smiley_url] . '" alt="' . $smilies[$i]['emoticon'] . '" border="0" />';
			}
		}

		if (count($orig))
		{
			$message = preg_replace($orig, $repl, ' ' . $message . ' ');
			$message = substr($message, 1, -1);
		}

		$board_config['smilies_path'] = $smilies_path;

		return $message;
	}
}

/**
 * Generate phpBB smilies.
 *
 * Hacking generate_smilies from phpbb/includes/functions_post.php
 *
 * @param string $mode
 * @param integer $page_id
 */
if(!function_exists('mx_generate_smilies'))
{
	function mx_generate_smilies($mode, $page_id)
	{
		global $mx_page, $board_config, $template, $mx_root_path, $phpbb_root_path, $phpEx;

		if( !function_exists('generate_smilies') )
		{
			mx_page::load_file('functions_post');
		}

		$smilies_path = $board_config['smilies_path'];
		$board_config['smilies_path'] = PHPBB_URL . $board_config['smilies_path'];
		generate_smilies($mode, $page_id);
		$board_config['smilies_path'] = $smilies_path;
		$template->assign_vars(array(
			'U_MORE_SMILIES' => mx3_append_sid(PHPBB_URL . "posting.$phpEx", "mode=smilies"))
		);
	}
}

/**
 * Return data from table.
 *
 * This function returns data from table, where field value matches id (and field2 value matches id2).
 *
 * @access public
 * @param string $table target
 * @param string $idfield  field
 * @param string $id needle
 * @param string $idfield2 additional field (optional)
 * @param string $id2 needle
 * @return array results
 */
if(!function_exists('mx_get_info'))
{
	function mx_get_info($table, $idfield = '', $id = 0, $idfield2 = '', $id2 = 0)
	{
		global $db;

		$sql = "SELECT * FROM $table WHERE $idfield = '$id'";
		$sql .= ( $idfield2 != '' && $id2 != '' ) ? " AND $idfield2 = '$id2'" : '';
		$sql .= ' LIMIT 1';
		if( !($result = $db->sql_query($sql)) )
		{
			mx_message_die(GENERAL_ERROR, "Couldn't get $table information", '', __LINE__, __FILE__, $sql);
		}
		$return = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		return $return;
	}
}
/**
 * Return number of results, if exists.
 *
 * This function returns the number of results, where field value matches id.
 *
 * @access public
 * @param string $table target
 * @param string $idfield field
 * @param string $id needle
 * @return array array('number', num_of_results)
 */
if(!function_exists('mx_get_exists'))
{
	function mx_get_exists($table, $idfield = '', $id = 0)
	{
		global $db;

		$sql = "SELECT COUNT(*) AS total FROM $table WHERE $idfield = '$id'";
		if( !($result = $db->sql_query($sql)) )
		{
			mx_message_die(GENERAL_ERROR, "Couldn't get block/Column information", '', __LINE__, __FILE__, $sql);
		}
		$count = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$count = $count['total'];
		return array('number' => $count);
	}
}
/**
 * Get html select list - from array().
 *
 * This function generates and returns a html select list (name = $nameselect).
 *
 * @access public
 * @param string $name_select select name
 * @param array $row source data
 * @param string $id needle
 * @param boolean $full_list expanded or dropdown list
 * @return unknown
 */
if(!function_exists('get_list_static'))
{
	function get_list_static($name_select, $row, $id, $full_list = true)
	{
		$rows_count = ( count($row) < '25' ) ? count($row) : '25';
		$full_list_true = $full_list ? ' size="' . $rows_count . '"' : '';

		$column_list = '<select name="' . $name_select .'" ' . $full_list_true . '>';
		foreach( $row as $idfield => $namefield )
		{
			$selected = ( $idfield == $id ) ? ' selected="selected"' : '';
			$column_list .= '<option value="' . $idfield . '"' . $selected . '>' . $namefield . "</option>\n";
		}
		$column_list .= '</select>';

		unset($row);
		return $column_list;
	}
}
/**
 * Get html select list - from db query.
 *
 * This function generates and returns a html select list (name = $nameselect) with option labels $namefield,
 * with data from $table, (where $idfield2 matches $id2).
 * Use $select=true to select where $idfield value matches $id.
 * <code>
 * 	<select name=$nameselect>
 * 		// $idfield = $id
 * 		<option value=$idfield selected="selected">$namefield</option>
 * 		// $idfield != $id
 * 		<option value=$idfield >$namefield</option>
 * 		<option value=$idfield >$namefield</option>
 * 		<option value=$idfield >$namefield</option>
 * 	</select>
 * </code>
 *
 * @access public
 * @param string $name_select select name
 * @param string $table target
 * @param string $idfield field
 * @param string $namefield option labels
 * @param string $id needle
 * @param boolean $select select idfiled = id
 * @param string $idfield2 field
 * @param string $id2 needle
 * @return string (html)
 */
if(!function_exists('mx_get_list'))
{
	function mx_get_list($name_select, $table, $idfield, $namefield, $id, $select = false, $idfield2 = '' , $id2 = '')
	{
		global $db;

		$sql = "SELECT * FROM $table";
		if( !$select )
		{
			$sql .= " WHERE $idfield <> $id";
		}
		if( !$select && !empty($id2) )
		{
			$sql .= " AND $idfield2 = $id2";
		}
		if( $select && !empty($id2) )
		{
			$sql .= " WHERE $idfield2 = $id2";
		}
		$sql .= " ORDER BY $namefield";

		if( !($result = $db->sql_query($sql)) )
		{
			mx_message_die(GENERAL_ERROR, "Couldn't get list of Column/blocks", '', __LINE__, __FILE__, $sql);
		}

		$column_list = '<select name="' . $name_select . '">';
		while( $row = $db->sql_fetchrow($result) )
		{
			$selected = ( $row[$idfield] == $id ) ? ' selected="selected"' : '';
			$column_list .= '<option value="' . $row[$idfield] . '"' . $selected . '>' . $row[$namefield] . "</option>\n";
		}
		$column_list .= '</select>';

		unset($row);
		$db->sql_freeresult($result);

		return $column_list;
	}
}
/**
 * Get html mutiple select list - from db query.
 *
 * This function generates and returns a html multiple select list (name = $nameselect) with option labels $namefield ($namefield2),
 * with data from $table. Use $select=true to select where $idfield value matches list($id).
 * <code>
 * 	<select name=$nameselect multiple="multiple">
 * 		// $idfield in list($id)
 * 		<option value=$idfield selected="selected">$namefield ($namefield2)</option>
 * 		// $idfield in list($id)
 * 		<option value=$idfield selected="selected">$namefield ($namefield2)</option>
 * 		// $idfield not in list($id)
 * 		<option value=$idfield >$namefield ($namefield2) ($namefield2)</option>
 * 		<option value=$idfield >$namefield ($namefield2) ($namefield2)</option>
 * 	</select>
 * </code>
 *
 * @access public
 * @param string $name_select select name
 * @param string $table target
 * @param string $idfield field
 * @param string $namefield option labels
 * @param array $id_list needle array
 * @param boolean $select select select idfiled = list(id)
 * @param string $namefield2 option labels desc
 * @return string (html)
 */
if(!function_exists('get_list_multiple'))
{
	function get_list_multiple($name_select, $table, $idfield, $namefield, $id_list, $select, $namefield2 = '')
	{
		global $db;

		$sql = "SELECT * FROM $table";
		if( !$select )
		{
			$sql .= " WHERE $idfield NOT IN ( $id_list )";
		}
		$sql .= " ORDER BY $namefield";

		if( !($result = $db->sql_query($sql)) )
		{
			mx_message_die(GENERAL_ERROR, "Couldn't get list of Column/blocks", '', __LINE__, __FILE__, $sql);
		}

		$id_list = explode(',', $id_list);
		$rows_count = $db->sql_numrows($result);
		$rows_count = ( $rows_count < '25' ) ? $rows_count : '25';

		$column_list = '<select name="' . $name_select . '" size="' . $rows_count . '" multiple="multiple">';
		while( $row = $db->sql_fetchrow($result) )
		{
			$namefield_desc = !empty($row[$namefield2]) ? ' (' . $row[$namefield2] . ')' : '';
			$selected = ( in_array($row[$idfield], $id_list) ) ? ' selected="selected"' : '';
			$column_list .= '<option value="' . $row[$idfield] . '"' . $selected . '>' . $row[$namefield] . $namefield_desc . "</option>\n";
		}
		$column_list .= '</select>';

		unset($row);
		$db->sql_freeresult($result);
		return $column_list;
	}
}
/**
 * Get html select list - from db query - with formatted output.
 *
 * This function generates and returns a html select list (name = $nameselect). Supported $type options are:
 * - page_list
 * - function_list
 * - block_list
 * - dyn_block_list
 * Or the function generates a block_list for given $function_file.
 *
 * @access public
 * @param string $type list types
 * @param string $id needle
 * @param string $name_select select name
 * @param string $function_file get block_list for $function_file
 * @param boolean $multiple_select
 * @param string $function_file2 get block_list also for $function_file2
 * @return string (html)
 */
if(!function_exists('get_list_formatted'))
{
	function get_list_formatted($type, $id, $name_select = '', $function_file = '', $multiple_select = false, $function_file2 = '')
	{
		global $db, $user;

		if( $type == 'page_list' )
		{
			//
			// get pages dropdown
			//
			$name_select = empty($name_select) ? 'page_id' : $name_select;
			$idfield = 'page_id';
			$namefield = 'page_name';
			$descfield = 'page_desc';

			$sql = "SELECT *
				FROM " . PAGE_TABLE . "
				ORDER BY page_name ASC, page_desc ASC";
		}
		elseif( $type == 'function_list' )
		{
			//
			// get functions dropdown
			//
			$name_select = 'function_id';
			$idfield = 'function_id';
			$namefield = 'function_name';

			$sql = "SELECT function_admin, fnc.function_name, fnc.function_id, fnc.function_desc, fnc.module_id, mdl.module_name, mdl.module_id, mdl.module_desc
				FROM " . FUNCTION_TABLE . " fnc,
					" . MODULE_TABLE . " mdl
				WHERE mdl.module_id = fnc.module_id
				ORDER BY mdl.module_name ASC, fnc.function_name ASC";
		}
		elseif( $type == 'block_list' )
		{
			//
			// get all blocks dropdown (optionally filtering by function_file)
			//
			$idfield = 'block_id';
			$namefield = 'block_title';
			$descfield = 'block_desc';

			$function_file_filter_temp = ( !empty($function_file2) ? " OR fnc.function_file = '$function_file2'" : '' );
			$function_file_filter = ( !empty($function_file) ? " AND ( fnc.function_file = '$function_file' ".$function_file_filter_temp.")" : '' );

			$sql = "SELECT blk.*, function_admin, fnc.function_name, fnc.function_id, fnc.function_desc, fnc.module_id, mdl.module_name, mdl.module_id, mdl.module_desc
				FROM " . BLOCK_TABLE . " blk,
					" . FUNCTION_TABLE . " fnc,
					" . MODULE_TABLE . " mdl
				WHERE blk.function_id = fnc.function_id
					AND mdl.module_id = fnc.module_id
					$function_file_filter
				ORDER BY mdl.module_name ASC, fnc.function_name ASC";
		}
		elseif( $type == 'dyn_block_list' )
		{
			//
			// get all dynamic blocks dropdown (2.8)
			//
			$idfield = 'block_id';
			$namefield = 'block_title';
			$descfield = 'block_desc';

			$sql = "SELECT blk.*, function_admin, fnc.function_name, fnc.function_id, fnc.function_desc, fnc.module_id, mdl.module_name, mdl.module_id, mdl.module_desc
				FROM " . BLOCK_TABLE . " blk,
					" . FUNCTION_TABLE . " fnc,
					" . MODULE_TABLE . " mdl
				WHERE blk.function_id = fnc.function_id
					AND mdl.module_id = fnc.module_id
					AND fnc.function_file = 'mx_dynamic.php'
				ORDER BY mdl.module_name ASC, fnc.function_name ASC";
		}

		if( !($result = $db->sql_query($sql)) )
		{
			//mx_message_die(GENERAL_ERROR, "Couldn't get list of Column/blocks", '', __LINE__, __FILE__, $sql);
		}

		if ($multiple_select)
		{
			$multiple_select_option = 'multiple="multiple"';
		}

		$column_list = '<select name="' . $name_select . '" '.$multiple_select_option.'>';
		if( $type == 'page_list' )
		{
			$column_list .= '<option value="0">' . "- not selected</option>\n";
		}

		if( $total_blocks = $db->sql_numrows($result) )
		{
			$row = $db->sql_fetchrowset($result);
		}

		for( $j = 0; $j < $total_blocks; $j++ )
		{
			if( $row[$j]['module_name'] != $row[$j-1]['module_name'] )
			{
				$column_list .= '<option value="">' . 'Module: ' . $row[$j]['module_name'] . '----------' . "</option>\n";
			}

			if( $type == 'block_list' )
			{
				if( $row[$j]['function_name'] != $row[$j-1]['function_name'] )
				{
					$block_type = $row[$j]['function_name'] . ': ';
				}
			}
			else
			{
				$block_type = '';
			}

			if( !empty($descfield) )
			{
				$block_description_str = !empty($row[$j][$descfield]) ? ' (' . $row[$j][$descfield] . ')' : ' (no desc)';
			}
			else
			{
				$block_description_str = '';
			}

			$selected = ( $row[$j][$idfield] == $id ) ? ' selected="selected"' : '';
			$column_list .= '<option value="' . $row[$j][$idfield] . '"' . $selected . '>&nbsp;&nbsp;- ' . $block_type . $row[$j][$namefield] . $block_description_str . "</option>\n";
		}
		$column_list .= '</select>';

		unset($row);
		$db->sql_freeresult($result);
		return $column_list;
	}
}
/**
 * Get simple html select list - from db query.
 *
 * This function generates and returns a html select list (name = $nameselect) with option labels $namefield,
 * with data from $table. Use $select=true to select where $idfield value matches $id.
 * <code>
 * 	<select name=$nameselect>
 * 		// $idfield = $id
 * 		<option value=$idfield selected="selected">$namefield</option>
 * 		// $idfield != $id
 * 		<option value=$idfield >$namefield</option>
 * 		<option value=$idfield >$namefield</option>
 * 		<option value=$idfield >$namefield</option>
 * 	</select>
 * </code>
 * Note: This function auto inserts a top option 'not selected'.
 *
 * @access public
 * @param string $name_select select name
 * @param string $table target
 * @param string $idfield field
 * @param string $namefield option labels
 * @param string $id needle
 * @param boolean $select select idfield = id
 * @return string (html)
 */
if(!function_exists('get_list_opt'))
{
	function get_list_opt($name_select, $table, $idfield, $namefield, $id, $select)
	{
		global $db, $user;

		$sql = "SELECT * FROM $table";
		if( ! $select )
		{
			$sql .= " WHERE $idfield <> $id";
		}
		$sql .= " ORDER BY $namefield";

		if( !($result = $db->sql_query($sql)) )
		{
			mx_message_die(GENERAL_ERROR, "Couldn't get list of Column/blocks", '', __LINE__, __FILE__, $sql);
		}

		$column_list = '<select name="'. $name_select . '">';
		$selected = ( $id == 0 ) ? ' selected="selected"' : '';
		$column_list .= '<option value="0"' . $selected . '>' . $user->lang['Not_Specified'] . "</option>\n";
		while( $row = $db->sql_fetchrow($result) )
		{
			$selected = ( $row[$idfield] == $id ) ? ' selected="selected"' : '';
			$column_list .= "<option value=\"$row[$idfield]\"$selected>" . $row[$namefield] . "</option>\n";
		}
		$column_list .= '</select>';

		unset($row);
		$db->sql_freeresult($result);
		return $column_list;
	}
}
/**
 * Generate MX-Publisher URL, with arguments.
 *
 * This function returns a MX-Publisher URL with GET vars, and accepts any number of parwise arguments.
 *
 * @access public
 * @return string (url)
 */
if(!function_exists('mx_url'))
{
	function mx_url()
	{
		global $SID;

		$numargs = func_num_args();
		$url = $PHP_SELF . '?' . $_SERVER['QUERY_STRING'];
		$url = parse_url($url);

		$url_array = array();
		if( ! empty($url['query']) )
		{
			$url_array = explode('&', $url['query']);
		}

		$arg_list = func_get_args();

		// Check for each option if exists in the parameter list
		for( $i = 0; $i < $numargs; $i++ )
		{
			$option = $arg_list[$i];
			$i++;
			$value = $arg_list[$i];
			// If not exists in the parameter list then add the parameter
			$opt_fund = false;
			for( $j = 0; $j < count($url_array); $j++ )
			{
				$tmp = explode('=', $url_array[$j]);
				if( $option == $tmp[0] )
				{
					$url_array[$j] = $option . '=' . $value ;
					$opt_fund = true;
				}
			}
			if( !$opt_fund )
			{
				$next = count($url_array);
				$url_array[$next] = $option . '=' . $value ;
			}
		}

		$url = $url['path'];

		// Build the parameter list
		if( !strpos($url, '?') )
		{
			$url .= '?';
		}

		$url .= implode('&', $url_array);
		/*
		for ($j = 0; $j < count($url_array); $j++)
		{
			if( $j < count($url_array) -1 )
			{
			$url .= $url_array[$j] . "&"  ;
			}
			else
			{
			$url .= $url_array[$j];
			}
		}
		*/
		$url = str_replace('?&', '?', $url);
		$url = str_replace('.php&', ".php?", $url);
		return $url;
	}
}
/**
 * Generate MX-Publisher URL, with arguments.
 *
 * This function returns a MX-Publisher URL with GET vars, and accepts arguments in the $args array().
 *
 * @access public
 * @param array $args source arguments
 * @param boolean $force_standalone_mode nonstandard file
 * @param string $file optional nonstandard file
 * @return string (url)
 */
if(!function_exists('mx_this_url'))
{
	function mx_this_url($args = '', $force_standalone_mode = false, $file = '')
	{
		global $mx_root_path, $module_root_path, $page_id, $phpEx, $is_block;

		if( $force_standalone_mode )
		{
			$mxurl = ( $file == '' ? "./" : $file . '/' ) . ( $args == '' ? '' : '?' . $args );
		}
		else
		{
			$mxurl = $mx_root_path . 'index.' . $phpEx;
			if( is_numeric($page_id) )
			{
				$mxurl .= '?page=' . $page_id . ( $args == '' ? '' : '&amp;' . $args );
			}
			else
			{
				$mxurl = "./" . ( $args == '' ? '' : '?' . $args );
			}
		}
		return $mxurl;
	}
}

/**
 * Get userdata
 *
 * Get Userdata, $user can be username or user_id. If force_str is true, the username will be forced.
 * Cached sql, since this function is used for every block.
 *
 * @param unknown_type $user id or name
 * @param boolean $force_str force clean_username
 * @return array
 */
function mx_get_userdata($user, $force_str = false)
{
	global $db;

	if (!is_numeric($user) || $force_str)
	{
		$user = substr(htmlspecialchars(str_replace("\'", "'", trim($user))), 0, 25);
		$user = rtrim($user, "\\"); // php5
		$user= str_replace("'", "\'", $user);
	}
	else
	{
		$user = intval($user);
	}

	$sql = "SELECT *
		FROM " . USERS_TABLE . "
		WHERE ";
	$sql .= ( ( is_integer($user) ) ? "user_id = $user" : "username = '" .  str_replace("\'", "''", $user) . "'" ) . " AND user_id <> " . ANONYMOUS;
	if ( !( $result = $db->sql_query( $sql ) ) )
	{
		trigger_error("Couldn't obtain user/group information for: " . $user . __LINE__ . __FILE__ . $sql );
	}

	$return = ( $row = $db->sql_fetchrow($result) ) ? $row : false;
	$db->sql_freeresult($result);
	return $return;
}

/**
 * Create buttons.
 *
 * You can create code for buttons:
 * 1) Simple textlinks (MX_BUTTON_TEXT)
 * 2) Standard image links (MX_BUTTON_IMAGE)
 * 3) Generic buttons, with spanning text on top background image (MX_BUTTON_GENERIC)
 *
 * Note: The rollover feature is done using a css shift technique, so you do not need separate images
 *
 * @param unknown_type $type
 * @param unknown_type $label
 * @param unknown_type $url
 * @param unknown_type $img
 */
function create_button($key, $label, $url)
{
	global $user;
		
	$this_buttontype = MX_BUTTON_IMAGE;

	switch($this_buttontype)
	{
		case MX_BUTTON_TEXT:
			return '<a class="textbutton" href="'. $url .'"><span>' . $label . '</span></a>';
		break;

		case MX_BUTTON_IMAGE:
			return '<a class="imagebutton" href="'. $url .'"><img src = "' . $user->img($key, $label, false, '', 'src') . '" alt="' . $label . '" title="' . $label . '" border="0"></a>';
		break;

		case MX_BUTTON_GENERIC:
			return '<a class="genericbutton" href="'. $url .'"><span>' . $label . '</span></a>';
		break;

		default:
			return '<a class="imagebutton" href="'. $url .'"><img src = "' . $user->img($key, $label, false, '', 'src') . '" alt="' . $label . '" title="' . $label . '" border="0"></a>';
		break;
	}
}

/**
 * Create icons.
 *
 * You can create code for icons:
 * 1) Simple textlinks (MX_BUTTON_TEXT)
 * 2) Standard image links (MX_BUTTON_IMAGE)
 * 3) Generic buttons, with spanning text on top background image (MX_BUTTON_GENERIC)
 *
 * Note: The rollover feature is done using a css shift technique, so you do not need separate images
 *
 * @param unknown_type $type
 * @param unknown_type $label
 * @param unknown_type $url
 * @param unknown_type $img
 */
function create_icon($key, $label, $url)
{
	global $user;

	$this_buttontype = MX_BUTTON_IMAGE;

	switch($this_buttontype)
	{
		case MX_BUTTON_TEXT:
			return '<a class="textbutton" href="'. $url .'"><span>' . $label . '</span></a>';
		break;

		case MX_BUTTON_IMAGE:
			return '<a class="imagebutton" href="'. $url .'"><img src = "' . $user->img($key, '', false, '', 'src') . '" alt="' . $label . '" title="' . $label . '" border="0"></a>';
		break;

		case MX_BUTTON_GENERIC:
			return '<a class="genericbutton" href="'. $url .'"><span>' . $label . '</span></a>';
			break;

		default:
			return '<a class="imagebutton" href="'. $url .'"><img src = "' . $user->img($key, '', false, '', 'src') . '" alt="' . $label . '" title="' . $label . '" border="0"></a>';
			break;
	}
}	


/**#@+
 * Class mx_request_vars specific definitions
 *
 * Following flags are options for the $type parameter in method _read()
 *
 */
define('MX_TYPE_ANY'		, 0);		// Retrieve the get/post var as-is (only stripslashes() will be applied).
define('MX_TYPE_INT'		, 1);		// Be sure we get a request var of type INT.
define('MX_TYPE_FLOAT'		, 2);		// Be sure we get a request var of type FLOAT.
define('MX_TYPE_NO_HTML'	, 4);		// Be sure we get a request var of type STRING (htmlspecialchars).
define('MX_TYPE_NO_TAGS'	, 8);		// Be sure we get a request var of type STRING (strip_tags + htmlspecialchars).
define('MX_TYPE_NO_STRIP'	, 16);		// By default strings are slash stripped, this flag avoids this.
define('MX_TYPE_SQL_QUOTED'	, 32);		// Be sure we get a request var of type STRING, safe for SQL statements (single quotes escaped)
define('MX_TYPE_POST_VARS'	, 64);		// Read a POST variable.
define('MX_TYPE_GET_VARS'	, 128);		// Read a GET variable.
/**#@-*/

/**#@+
 * Class mx_request_vars specific definitions
 *
 * Following flags are options for the $type parameter in method _read()
 *
 */
define('MX_TYPE_ANY'		, 0);		// Retrieve the get/post var as-is (only stripslashes() will be applied).
define('MX_TYPE_INT'		, 1);		// Be sure we get a request var of type INT.
define('MX_TYPE_FLOAT'		, 2);		// Be sure we get a request var of type FLOAT.
define('MX_TYPE_NO_HTML'	, 4);		// Be sure we get a request var of type STRING (htmlspecialchars).
define('MX_TYPE_NO_TAGS'	, 8);		// Be sure we get a request var of type STRING (strip_tags + htmlspecialchars).
define('MX_TYPE_NO_STRIP'	, 16);		// By default strings are slash stripped, this flag avoids this.
define('MX_TYPE_SQL_QUOTED'	, 32);		// Be sure we get a request var of type STRING, safe for SQL statements (single quotes escaped)
define('MX_TYPE_POST_VARS'	, 64);		// Read a POST variable.
define('MX_TYPE_GET_VARS'	, 128);		// Read a GET variable.
define('MX_NOT_EMPTY'		, true);	//

/**#@-*/

/**
 * Class: mx_request_vars.
 *
 * This is the CORE request vars object. Encapsulate several functions related to GET/POST variables.
 * More than one flag can specified by OR'ing the $type argument. Examples:
 * - For instance, we could use ( MX_TYPE_POST_VARS | MX_TYPE_GET_VARS ), see method request().
 * - or we could use ( MX_TYPE_NO_TAGS | MX_TYPE_SQL_QUOTED ).
 * - However, MX_TYPE_NO_HTML and MX_TYPE_NO_TAGS can't be specified at a time (defaults to MX_TYPE_NO_TAGS which is more restritive).
 * - Also, MX_TYPE_INT and MX_TYPE_FLOAT ignore flags MX_TYPE_NO_*
 * Usage examples:
 * - $mode = $mx_request_vars->post('mode', MX_TYPE_NO_TAGS, '');
 * - $page_id = $mx_request_vars->get('page', MX_TYPE_INT, 1);
 * This class IS instatiated in common.php ;-)
 *
 * @access public
 * @author Markus
 * @package Core
 */
class mx_request_vars
{
	//
	// Implementation Conventions:
	// Properties and methods prefixed with underscore are intented to be private. ;-)
	//

	// ------------------------------
	// Properties
	//

	// ------------------------------
	// Constructor
	//

	// ------------------------------
	// Private Methods
	//

	/**
	 * Function: _read().
	 *
	 * Get the value of the specified request var (post or get) and force the result to be
	 * of specified type. It might also transform the result (stripslashes, htmlspecialchars) for security
	 * purposes. It all depends on the $type argument.
	 * If the specified request var does not exist, then the default ($dflt) value is returned.
	 * Note the $type argument behaves as a bit array where more than one option can be specified by OR'ing
	 * the passed argument. This is tipical practice in languages like C, but it can also be done with PHP.
	 *
	 * @access private
	 * @param unknown_type $var
	 * @param unknown_type $type
	 * @param unknown_type $dflt
	 * @return unknown
	 */
	function _read($var, $type = MX_TYPE_ANY, $dflt = '', $not_null = false)
	{
		global $_POST, $_GET;
	
		if( ($type & (MX_TYPE_POST_VARS|MX_TYPE_GET_VARS)) == 0 )
		{
			$type |= (MX_TYPE_POST_VARS|MX_TYPE_GET_VARS);
		}

		if( ($type & MX_TYPE_POST_VARS) && isset($_POST[$var]) ||
			($type & MX_TYPE_GET_VARS)  && isset($_GET[$var]) )
		{
			$val = ( ($type & MX_TYPE_POST_VARS) && isset($_POST[$var]) ? $_POST[$var] : $_GET[$var] );
			if( !($type & MX_TYPE_NO_STRIP) )
			{
				if( is_array($val) )
				{
					foreach( $val as $k => $v )
					{
						$val[$k] = trim(stripslashes($v));
					}
				}
				else
				{
					$val = trim(stripslashes($val));
				}
			}
		}
		else
		{
			$val = $dflt;
		}

		if( $type & MX_TYPE_INT )		// integer
		{
			return $not_null && empty($val) ? $dflt : intval($val);
		}

		if( $type & MX_TYPE_FLOAT )		// float
		{
			return $not_null && empty($val) ? $dflt : floatval($val);
		}

		if( $type & MX_TYPE_NO_TAGS )	// ie username
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = htmlspecialchars(strip_tags(ltrim(rtrim($v, " \t\n\r\0\x0B\\"))));
				}
			}
			else
			{
				$val = htmlspecialchars(strip_tags(ltrim(rtrim($val, " \t\n\r\0\x0B\\"))));
			}
		}
		elseif( $type & MX_TYPE_NO_HTML )	// no slashes nor html
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = htmlspecialchars(ltrim(rtrim($v, " \t\n\r\0\x0B\\")));
				}
			}
			else
			{
				$val = htmlspecialchars(ltrim(rtrim($val, " \t\n\r\0\x0B\\")));
			}
		}

		if( $type & MX_TYPE_SQL_QUOTED )
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = str_replace(($type & MX_TYPE_NO_STRIP ? "\'" : "'"), "''", $v);
				}
			}
			else
			{
				$val = str_replace(($type & MX_TYPE_NO_STRIP ? "\'" : "'"), "''", $val);
			}
		}

		return $not_null && empty($val) ? $dflt : $val;
	}

	// ------------------------------
	// Public Methods
	//

	/**
	 * Request POST variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	function post($var, $type = MX_TYPE_ANY, $dflt = '', $not_null = false)
	{
		return $this->_read($var, ($type | MX_TYPE_POST_VARS), $dflt, $not_null);
	}

	/**
	 * Request GET variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	function get($var, $type = MX_TYPE_ANY, $dflt = '', $not_null = false)
	{
		return $this->_read($var, ($type | MX_TYPE_GET_VARS), $dflt, $not_null);
	}

	/**
	 * Request GET or POST variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	function request($var, $type = MX_TYPE_ANY, $dflt = '', $not_null = false)
	{
		return $this->_read($var, ($type | MX_TYPE_POST_VARS | MX_TYPE_GET_VARS), $dflt, $not_null);
	}

	/**
	 * Is POST var?
	 *
	 * Boolean method to check for existence of POST variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	function is_post($var)
	{
		global $_POST;
		// Note: _x and _y are used by (at least IE) to return the mouse position at onclick of INPUT TYPE="img" elements.
		return (isset($_POST[$var]) || ( isset($_POST[$var.'_x']) && isset($_POST[$var.'_y']))) ? 1 : 0;
	}

	/**
	 * Is GET var?
	 *
	 * Boolean method to check for existence of GET variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	function is_get($var)
	{
		global $_GET;	
		return isset($_GET[$var]) ? 1 : 0 ;
	}

	/**
	 * Is REQUEST (either GET or POST) var?
	 *
	 * Boolean method to check for existence of any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	function is_request($var)
	{
		return ($this->is_get($var) || $this->is_post($var)) ? 1 : 0;
	}
	/**
	 * Is POST var empty?
	 *
	 * Boolean method to check if POST variable is empty
	 * as it might be set but still be empty.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	function is_empty_post($var)
	{
		global $_POST;
	
		return (empty($_POST[$var]) && ( empty($_POST[$var.'_x']) || empty($_POST[$var.'_y']))) ? 1 : 0 ;
	}
	/**
	 * Is GET var empty?
	 *
	 * Boolean method to check if GET variable is empty
	 * as it might be set but still be empty
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	function is_empty_get($var)
	{
		global $_GET;
		return empty($_GET[$var]) ? 1 : 0 ;
	}

	/**
	 * Is REQUEST empty (GET and POST) var?
	 *
	 * Boolean method to check if REQUEST (both) variable is empty.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	function is_empty_request($var)
	{
		return ($this->is_empty_get($var) && $this->is_empty_post($var)) ? 1 : 0;
	}

}	// class mx_request_vars
?>