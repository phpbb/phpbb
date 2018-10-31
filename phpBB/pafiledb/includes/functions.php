<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: functions.php,v 1.3 2011/12/30 04:21:03 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

/**
 * pafiledb_functions.
 *
 * This class is used for general pa handling
 *
 * @access public
 * @author Jon Ohlsson
 *
 */
class pafiledb_functions
{
	/**
	 * This class is used for general pafiledb handling
	 *
	 * @param unknown_type $config_name
	 * @param unknown_type $config_value
	 */
	function set_config($config_name, $config_value)
	{
		global $db, $pafiledb_cache, $pafiledb_config;

		$sql = 'UPDATE ' . PA_CONFIG_TABLE . "
			SET config_value = '" . $db->sql_escape($config_value) . "'
			WHERE config_name = '" . $db->sql_escape($config_name) . "'";

		if (!@$db->sql_query($sql) && !isset($pafiledb_config[$config_name]))
		{
			$sql = 'INSERT INTO ' . PA_CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'config_name'	=> $config_name,
				'config_value'	=> $config_value));
			$db->sql_query($sql);
		}

		$pafiledb_config[$config_name] = $config_value;
		$pafiledb_cache->put( 'config', $pafiledb_config );
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function pafiledb_config()
	{
		global $db;

		$sql = "SELECT *
			FROM " . PA_CONFIG_TABLE;

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt query pafiledb configuration', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$pafiledb_config[$row['config_name']] = trim( $row['config_value'] );
		}

		$db->sql_freeresult( $result );

		return ( $pafiledb_config );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $query
	 * @param unknown_type $total
	 * @param unknown_type $offset
	 * @return unknown
	 */
	function sql_query_limit( $query, $total, $offset = 0, $sql_cache = false )
	{
		global $db;

		$query .= ' LIMIT ' . ( ( !empty( $offset ) ) ? $offset . ', ' . $total : $total );
		return $sql_cache ? $db->sql_query( $query, $sql_cache ) : $db->sql_query( $query );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $file_rating
	 * @return unknown
	 */
	function get_rating( $file_id, $file_rating = '' )
	{
		global $db, $user;

		$sql = "SELECT AVG(rate_point) AS rating
			FROM " . PA_VOTES_TABLE . "
			WHERE votes_file = '" . $file_id . "'";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt rating info for the giving file', '', __LINE__, __FILE__, $sql );
		}

		$row = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );
		$file_rating = $row['rating'];

		return ( $file_rating != 0 ) ? round( $file_rating, 2 ) . ' / 10' : $user->lang['Not_rated'];
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
		global $images, $user;
		
		$this->buttontype = MX_BUTTON_IMAGE;

		switch($this->buttontype)
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
		global $images, $user;

		$this->buttontype = MX_BUTTON_IMAGE;

		switch($this->buttontype)
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

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $page_title
	 */
	function page_header( $page_title )
	{
		global $pafiledb_config, $user, $template, $pafiledb;
		global $template, $db, $theme, $gen_simple_header, $starttime, $phpEx, $board_config, $user_ip;
		global $admin_level, $level_prior, $tree, $do_gzip_compress;
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $title, $mx_block;
		global $action;

		if ( $action != 'download' )
		{
			//page_header($page_title);
		}

		if ( $action == 'category' )
		{
			$upload_url = mx_append_sid( $pafiledb->this_mxurl( "action=user_upload&cat_id={$_REQUEST['cat_id']}" ) );
			$mcp_url = mx_append_sid( $pafiledb->this_mxurl( "action=mcp&cat_id={$_REQUEST['cat_id']}" ) );

			$upload_auth = $pafiledb->modules[$pafiledb->module_name]->auth_user[$_REQUEST['cat_id']]['auth_upload'];
			$mcp_auth = $pafiledb->modules[$pafiledb->module_name]->auth_user[$_REQUEST['cat_id']]['auth_mod'];
		}
		else
		{
			$upload_url = mx_append_sid( $pafiledb->this_mxurl( "action=user_upload" ) );

			$cat_list = $pafiledb->modules[$pafiledb->module_name]->generate_jumpbox( 0, 0, '', true, true );
			// $upload_auth = (empty($cat_list)) ? FALSE : TRUE;
			$upload_auth = false;
			$mcp_auth = false;
			unset( $cat_list );
		}

		$template->assign_vars( array(
				'L_TITLE' => $title,
				'IS_AUTH_VIEWALL' => ( $pafiledb_config['settings_viewall'] ) ? ( ( $pafiledb->modules[$pafiledb->module_name]->auth_global['auth_viewall'] ) ? true : false ) : false,
				'IS_AUTH_SEARCH' => ( $pafiledb->modules[$pafiledb->module_name]->auth_global['auth_search'] ) ? true : false,
				'IS_AUTH_STATS' => ( $pafiledb->modules[$pafiledb->module_name]->auth_global['auth_stats'] ) ? true : false,
				'IS_AUTH_TOPLIST' => ( $pafiledb->modules[$pafiledb->module_name]->auth_global['auth_toplist'] ) ? true : false,

				'IS_AUTH_UPLOAD' => $upload_auth,
				'IS_ADMIN' => ( $user->data['user_level'] == ADMIN && $user->data['session_logged_in'] ) ? true : 0,
				'IS_MOD' => $pafiledb->modules[$pafiledb->module_name]->auth_user[$_REQUEST['cat_id']]['auth_mod'],
				'IS_AUTH_MCP' => $mcp_auth,

				'L_OPTIONS' => $user->lang['Options'],
				'L_SEARCH' => $user->lang['Search'],
				'L_STATS' => $user->lang['Statistics'],
				'L_TOPLIST' => $user->lang['Toplist'],
				'L_UPLOAD' => $user->lang['User_upload'],
				'L_VIEW_ALL' => $user->lang['Viewall'],

				'SEARCH_IMG' => $user->img('icon_pa_search', '', false, '', 'src'),
				'STATS_IMG' => $user->img('icon_pa_stats', '', false, '', 'src'),
				'TOPLIST_IMG' => $user->img('icon_pa_toplist', '', false, '', 'src'),
				'UPLOAD_IMG' => $user->img('icon_pa_upload', '', false, '', 'src'),
				'VIEW_ALL_IMG' => $user->img('icon_pa_viewall', '', false, '', 'src'),
				
				'MCP_LINK' => $user->lang['MCP_title'],
				'L_NEW_FILE' => 'New File',

				'U_TOPLIST' => mx_append_sid( $pafiledb->this_mxurl( "action=toplist" ) ),
				'U_PASEARCH' => mx_append_sid( $pafiledb->this_mxurl( "action=search" ) ),
				'U_UPLOAD' => $upload_url,
				'U_VIEW_ALL' => mx_append_sid( $pafiledb->this_mxurl( "action=viewall" ) ),
				'U_PASTATS' => mx_append_sid( $pafiledb->this_mxurl( "action=stats" ) ),
				'U_MCP' => $mcp_url,

				'MX_ROOT_PATH' => $mx_root_path,
				'BLOCK_ID' => $mx_block->block_id,

				// Buttons
				'B_SEARCH_IMG' => $this->create_button('icon_pa_search', $user->lang['Search'], mx_append_sid($pafiledb->this_mxurl("action=search"))),
				'B_STATS_IMG' => $this->create_button('icon_pa_stats', $user->lang['Statistics'], mx_append_sid($pafiledb->this_mxurl("action=stats"))),
				'B_TOPLIST_IMG' => $this->create_button('icon_pa_toplist', $user->lang['Toplist'], mx_append_sid($pafiledb->this_mxurl("action=toplist"))),
				'B_UPLOAD_IMG' => $this->create_button('icon_pa_upload', $user->lang['User_upload'], $upload_url),
				'B_VIEW_ALL_IMG' => $this->create_button('icon_pa_viewall', $user->lang['Viewall'], mx_append_sid($pafiledb->this_mxurl("action=viewall"))),
				'B_MCP_LINK' => $this->create_button('icon_pa_moderator', $user->lang['MCP_title'], $mcp_url),
			));
	}
	

	/**
	 * Enter description here...
	 *
	 */
	function page_footer()
	{
		global $pafiledb_cache, $user, $template, $board_config, $pafiledb, $user;
		global $phpEx, $template, $do_gzip_compress, $debug, $db, $starttime;
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $page_id;
		global $pa_module_version, $pa_module_orig_author, $pa_module_author;

		$template->assign_vars( array(
			'L_QUICK_GO' => $user->lang['Quick_go'],
			'L_QUICK_NAV' => $user->lang['Quick_nav'],
			'L_QUICK_JUMP' => $user->lang['Quick_jump'],
			'JUMPMENU' => $pafiledb->modules[$pafiledb->module_name]->generate_jumpbox( 0, 0, array( $_GET['cat_id'] => 1 ) ),
			'S_JUMPBOX_ACTION' => mx_append_sid( $pafiledb->this_mxurl( ) ),

			'S_AUTH_LIST' => $pafiledb->modules[$pafiledb->module_name]->auth_can_list,

			'MX_PAGE' => $page_id,
			'L_MODULE_VERSION' => $pa_module_version,
			'L_MODULE_ORIG_AUTHOR' => $pa_module_orig_author,
			'L_MODULE_AUTHOR' => $pa_module_author,
			'S_TIMEZONE' => sprintf( $user->lang['All_times'], $user->lang[number_format( $board_config['board_timezone'] )] ) )
		);

		$pafiledb->modules[$pafiledb->module_name]->_pafiledb();

		if ( !MXBB_MODULE || MXBB_27x )
		{
			$template->assign_block_vars( 'copy_footer', array() );
		}

		if ( !isset( $_GET['explain'] ) )
		{
			//$template->pparse( 'body' );
		}

		$pafiledb_cache->unload();

		if ( $action != 'download' )
		{
			if ( !$is_block )
			{
				//page_footer();
			}
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_posticon
	 * @return unknown
	 */
	function post_icons( $file_posticon = '' )
	{
		global $user, $phpbb_root_path;
		global $mx_root_path, $module_root_path, $is_block, $phpEx;
		$curicons = 1;

		if ( $file_posticon == 'none' || $file_posticon == 'none.gif' or empty( $file_posticon ) )
		{
			$posticons .= '<input type="radio" name="posticon" value="none" checked><a class="gensmall">' . $user->lang['None'] . '</a>&nbsp;';
		}
		else
		{
			$posticons .= '<input type="radio" name="posticon" value="none"><a class="gensmall">' . $user->lang['None'] . '</a>&nbsp;';
		}

		$handle = @opendir( $module_root_path . ICONS_DIR );

		while ( $icon = @readdir( $handle ) )
		{
			if ( $icon !== '.' && $icon !== '..' && $icon !== 'index.htm' )
			{
				if ( $file_posticon == $icon )
				{
					$posticons .= '<input type="radio" name="posticon" value="' . $icon . '" checked><img src="' . $module_root_path . ICONS_DIR . $icon . '">&nbsp;';
				}
				else
				{
					$posticons .= '<input type="radio" name="posticon" value="' . $icon . '"><img src="' . $module_root_path . ICONS_DIR . $icon . '">&nbsp;';
				}

				$curicons++;

				if ( $curicons == 8 )
				{
					$posticons .= '<br>';
					$curicons = 0;
				}
			}
		}
		@closedir( $handle );
		return $posticons;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $license_id
	 * @return unknown
	 */
	function license_list( $license_id = 0 )
	{
		global $db, $user;

		if ( $license_id == 0 )
		{
			$list .= '<option calue="0" selected>' . $user->lang['None'] . '</option>';
		}
		else
		{
			$list .= '<option calue="0">' . $user->lang['None'] . '</option>';
		}

		$sql = 'SELECT *
			FROM ' . PA_LICENSE_TABLE . '
			ORDER BY license_id';

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
		}

		while ( $license = $db->sql_fetchrow( $result ) )
		{
			if ( $license_id == $license['license_id'] )
			{
				$list .= '<option value="' . $license['license_id'] . '" selected>' . $license['license_name'] . '</option>';
			}
			else
			{
				$list .= '<option value="' . $license['license_id'] . '">' . $license['license_name'] . '</option>';
			}
		}
		return $list;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_type
	 * @return unknown
	 */
	function gen_unique_name( $file_type )
	{
		global $pafiledb_config;
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		srand( ( double )microtime() * 1000000 ); // for older than version 4.2.0 of PHP

		do
		{
			$filename = md5( uniqid( rand() ) ) . $file_type;
		}
		while ( file_exists( $pafiledb_config['upload_dir'] . '/' . $filename ) );

		return $filename;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	 */
	function get_extension( $filename )
	{
		//return strtolower( array_pop( explode( '.', $filename ) ) );
		return strtolower( array_pop( $array = (explode( '.', $filename ))) );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $userfile
	 * @param unknown_type $userfile_name
	 * @param unknown_type $userfile_size
	 * @param unknown_type $upload_dir
	 * @param unknown_type $local
	 * @return unknown
	 */
	function upload_file( $userfile, $userfile_name, $userfile_size, $upload_dir = '', $local = false )
	{
		global $phpbb_root_path, $user, $phpEx, $board_config, $pafiledb_config;
		global $pafiledb, $cat_id, $mx_root_path, $module_root_path, $is_block, $phpEx;

		@set_time_limit( 0 );
		$file_info = array();

		$file_info['error'] = false;

		if ( file_exists( $module_root_path . $upload_dir . $userfile_name ) )
		{
			$userfile_name = time() . $userfile_name;
		}
		// =======================================================
		// if the file size is more than the allowed size another error message
		// =======================================================
		if ( $userfile_size > $pafiledb_config['max_file_size'] && ( $pafiledb->modules[$pafiledb->module_name]->auth_user[$cat_id]['auth_mod'] || $user->data['user_level'] != ADMIN ) && $user->data['session_logged_in'] )
		{
			$file_info['error'] = true;
			if ( !empty( $file_info['message'] ) )
			{
				$file_info['message'] .= '<br>';
			}
			$file_info['message'] .= $user->lang['Filetoobig'];
		}
		// =======================================================
		// Then upload the file, and check the php version
		// =======================================================
		else
		{
			$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

			$upload_mode = ( @$ini_val( 'open_basedir' ) || @$ini_val( 'safe_mode' ) ) ? 'move' : 'copy';
			$upload_mode = ( $local ) ? 'local' : $upload_mode;

			if ( $this->do_upload_file( $upload_mode, $userfile, $module_root_path . $upload_dir . $userfile_name ) )
			{
				$file_info['error'] = true;
				if ( !empty( $file_info['message'] ) )
				{
					$file_info['message'] .= '<br>';
				}
				$file_info['message'] .= 'Couldn\'t Upload the File.';
			}
			$file_info['url'] = get_formated_url() . '/' . $module_root_path . $upload_dir . $userfile_name;
		}
		return $file_info;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $upload_mode
	 * @param unknown_type $userfile
	 * @param unknown_type $userfile_name
	 * @return unknown
	 */
	function do_upload_file( $upload_mode, $userfile, $userfile_name )
	{
		switch ( $upload_mode )
		{
			case 'copy':
				if ( !@copy( $userfile, $userfile_name ) )
				{
					if ( !@move_uploaded_file( $userfile, $userfile_name ) )
					{
						return false;
					}
				}
				@chmod( $userfile_name, 0666 );
				break;

			case 'move':
				if ( !@move_uploaded_file( $userfile, $userfile_name ) )
				{
					if ( !@copy( $userfile, $userfile_name ) )
					{
						return false;
					}
				}
				@chmod( $userfile_name, 0666 );
				break;

			case 'local':
				if ( !@copy( $userfile, $userfile_name ) )
				{
					return false;
				}
				@chmod( $userfile_name, 0666 );
				@unlink( $userfile );
				break;
		}

		return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $file_data
	 * @return unknown
	 */
	function get_file_size( $file_id, $file_data = '' )
	{
		global $db, $user, $phpbb_root_path, $pafiledb_config;
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		$directory = $module_root_path . $pafiledb_config['upload_dir'];

		if ( empty( $file_data ) )
		{
			$sql = "SELECT file_dlurl, file_size, unique_name, file_dir
				FROM " . PA_FILES_TABLE . "
				WHERE file_id = '" . $file_id . "'";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt query Download URL', '', __LINE__, __FILE__, $sql );
			}

			$file_data = $db->sql_fetchrow( $result );

			$db->sql_freeresult( $result );
		}

		$file_url = $file_data['file_dlurl'];
		$file_size = $file_data['file_size'];

		$formated_url = get_formated_url();
		$html_path = $formated_url . '/' . $directory;
		$update_filesize = false;

		if ( ( ( substr( $file_url, 0, strlen( $html_path ) ) == $html_path ) || !empty( $file_data['unique_name'] ) ) && empty( $file_size ) )
		{
			$file_url = basename( $file_url ) ;
			$file_name = basename( $file_url );

			if ( ( !empty( $file_data['unique_name'] ) ) && ( !file_exists( $module_root_path . $file_data['file_dir'] . $file_data['unique_name'] ) ) )
			{
				return $user->lang['Not_available'];
			}

			if ( empty( $file_data['unique_name'] ) )
			{
				$file_size = @filesize( $directory . $file_name );
			}
			else
			{
				$file_size = @filesize( $module_root_path . $file_data['file_dir'] . $file_data['unique_name'] );
			}

			$update_filesize = true;
		}
		elseif ( empty( $file_size ) && ( ( !( substr( $file_url, 0, strlen( $html_path ) ) == $html_path ) ) || empty( $file_data['unique_name'] ) ) )
		{
			$ourhead = "";
			$url = parse_url( $file_url );
			$host = $url['host'];
			$path = $url['path'];
			$port = ( !empty( $url['port'] ) ) ? $url['port'] : 80;
			$errno = ''; 
			$errstr = '';

			$fp = @fsockopen( $host, $port, $errno, $errstr, 20 );

			if ( !$fp )
			{
				return $user->lang['Not_available'];
			}
			else
			{
				fputs( $fp, "HEAD $file_url HTTP/1.1\r\n" );
				fputs( $fp, "HOST: $host\r\n" );
				fputs( $fp, "Connection: close\r\n\r\n" );

				while ( !feof( $fp ) )
				{
					$ourhead = sprintf( '%s%s', $ourhead, fgets ( $fp, 128 ) );
				}
			}
			@fclose ( $fp );

			$split_head = explode( 'Content-Length: ', $ourhead );

			$file_size = round( abs( $split_head[1] ) );
			$update_filesize = true;
		}
		
		if ( !$file_size )
		{
			//Check if file is not hosted on same domain relative to mx_root_path
			if (file_exists(str_replace(PORTAL_URL, "./", $file_url)))
			{
				$file_size = filesize(str_replace(PORTAL_URL, "./", $file_url));
			}
			elseif  (file_exists($mx_root_path . $module_root_path . $file_data['file_dir'] . str_replace(PORTAL_URL, "./", $file_url)))
			{			
				$file_size = filesize($mx_root_path . $module_root_path . $file_data['file_dir'] . str_replace(PORTAL_URL, "./", $file_url));
			}				
			else
			{
				return $user->lang['Not_available'];
			}				
		}		

		if ( $update_filesize )
		{
			$sql = 'UPDATE ' . PA_FILES_TABLE . "
				SET file_size = '$file_size'
				WHERE file_id = '$file_id'";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not update filesize', '', __LINE__, __FILE__, $sql );
			}
		}

		if ( $file_size < 1024 )
		{
			$file_size_out = intval( $file_size ) . ' ' . $user->lang['Bytes'];
		}
		if ( $file_size >= 1025 )
		{
			$file_size_out = round( intval( $file_size ) / 1024 * 100 ) / 100 . ' ' . $user->lang['KB'];
		}
		if ( $file_size >= 1048575 )
		{
			$file_size_out = round( intval( $file_size ) / 1048576 * 100 ) / 100 . ' ' . $user->lang['MB'];
		}

		return $file_size_out;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $filename
	 * @return unknown
	 */
	function pafiledb_unlink( $filename )
	{
		global $pafiledb_config, $user;

		$deleted = @unlink( $filename );

		if ( @file_exists( $this->pafiledb_realpath( $filename ) ) )
		{
			$filesys = eregi_replace( '/', '\\', $filename );
			$deleted = @system( "del $filesys" );

			if ( @file_exists( $this->pafiledb_realpath( $filename ) ) )
			{
				$deleted = @chmod ( $filename, 0775 );
				$deleted = @unlink( $filename );
				$deleted = @system( "del $filesys" );
			}
		}

		return ( $deleted );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $path
	 * @return unknown
	 */
	function pafiledb_realpath( $path )
	{
		global $phpbb_root_path, $phpEx;

		return ( !@function_exists( 'realpath' ) || !@realpath( $phpbb_root_path . 'includes/functions.' . $phpEx ) ) ? $path : @realpath( $path );
	}
}

/**
 * mx_user_info
 *
 * This class is used to determin Browser and operating system info of the user
 *
 * @access public
 * @author http://www.chipchapin.com
 * @copyright (c) 2002 Chip Chapin <cchapin@chipchapin.com>
 */
class mx_user_info
{
	var $agent = 'unknown';
	var $ver = 0;
	var $majorver = 0;
	var $minorver = 0;
	var $platform = 'unknown';

	/**
	 * Constructor.
	 *
	 * Determine client browser type, version and platform using heuristic examination of user agent string.
	 *
	 * @param unknown_type $user_agent allows override of user agent string for testing.
	 */
	function mx_user_info( $user_agent = '' )
	{
		global $_SERVER, $HTTP_USER_AGENT, $HTTP_SERVER_VARS;

		if ( !empty( $_SERVER['HTTP_USER_AGENT'] ) )
		{
			$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
		}
		else if ( !empty( $HTTP_SERVER_VARS['HTTP_USER_AGENT'] ) )
		{
			$HTTP_USER_AGENT = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
		}
		else if ( !isset( $HTTP_USER_AGENT ) )
		{
			$HTTP_USER_AGENT = '';
		}

		if ( empty( $user_agent ) )
		{
			$user_agent = $HTTP_USER_AGENT;
		}

		$user_agent = strtolower( $user_agent );
		// Determine browser and version
		// The order in which we test the agents patterns is important
		// Intentionally ignore Konquerer.  It should show up as Mozilla.
		// post-Netscape Mozilla versions using Gecko show up as Mozilla 5.0
		// known browsers, list will be updated routinely, check back now and then
		if ( preg_match( '/(android\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(iphone\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(ipod\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(mozilla\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(firefox\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;	
		elseif ( preg_match( '/(phoenix\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(firebird\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;	
		elseif ( preg_match( '/(konqueror |konq\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(netscape\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(opera |opera\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(msie )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(chrome\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		elseif ( preg_match( '/(safari |saf\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;		
		// covers Netscape 6-7, K-Meleon, Most linux versions, uses moz array below
		elseif ( preg_match( '/(gecko |moz\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(netpositive |netp\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(lynx |lynx\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(elinks |elinks\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(links |links\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(w3m |w3m\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(webtv |webtv\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(amaya |amaya\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(dillo |dillo\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(ibrowsevibrowse |ibrowsevibrowse\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(icab |icab\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(crazy browser |ie\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(sonyericssonp800 |sonyericssonp800\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(aol )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(camino )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		// search engine spider bots:
		elseif ( preg_match( '/(googlebot |google\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(mediapartners-google |adsense\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(yahoo-verticalcrawler |yahoo\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(yahoo! slurp |yahoo\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(yahoo-mm |yahoomm\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(inktomi |inktomi\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(slurp |inktomi\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(fast-webcrawler |fast\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(msnbot |msn\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(ask jeeves |ask\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(teoma |ask\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(scooter |scooter\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(openbot |openbot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(ia_archiver |ia_archiver\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(zyborg |looksmart\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(almaden |ibm\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(baiduspider |baidu\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(psbot |psbot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(gigabot |gigabot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(naverbot |naverbot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(surveybot |surveybot\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(boitho.com-dc |boitho\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(objectssearch |objectsearch\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(answerbus |answerbus\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(sohu-search |sohu\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(iltrovatore-setaccio |il-set\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		// various http utility libaries
		elseif ( preg_match( '/(w3c_validator |w3c\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(wdg_validator |wdg\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(libwww-perl |libwww-perl\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(jakarta commons-httpclient |jakarta\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(python-urllib |python-urllib\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		// download apps
		elseif ( preg_match( '/(getright |getright\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(wget |wget\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		else
		{
			$matches[1] = 'unknown';
			$matches[2] = 0;
			$matches[3] = 0;
		}

		$this->majorver = $matches[2];
		$this->minorver = $matches[3];
		$this->ver = $matches[2] . '.' . $matches[3];
		
		switch ( $matches[1] )
		{
			case 'Android/':
			case 'android ':
				$this->agent = 'ANDROID';
			break;
			case 'iPhone/':
			case 'iphone ':
				$this->agent = 'IPHONE';
			break;
			case 'iPod/':
			case 'ipod ':
				$this->agent = 'IPOD';
			break;
			case 'Chrome/':
			case 'chrome ':
				$this->agent = 'GOOGLE_CHROME';
			break;
			case 'opera/':
			case 'opera ':
				$this->agent = 'OPERA';
			break;				
			case 'opera/':
			case 'opera ':
				$this->agent = 'OPERA';
			break;
			case 'msie ':
				$this->agent = 'IE';
			break;
			case 'mozilla/':
				$this->agent = 'NETSCAPE';
				if ( $this->majorver >= 5 )
				{
					$this->agent = 'MOZILLA';
				}
			break;
			case 'firefox/':
			case 'firefox ':			
				$this->agent = 'MOZILLA';
			break;			
 			case 'phoenix ':
 			case 'firebird ':
				$this->agent = 'MOZILLA';
			break;
			case 'konqueror ':
			case 'konq ':
				$this->agent = 'KONQUEROR';
			break;
			case 'lynx/':
			case 'lynx ':
				$this->agent = 'LYNX';
			break;
			case 'safari ':
			case 'saf ':
				$this->agent = 'SAFARI';
			break;
			case 'aol/':
			case 'aol ':
				$this->agent = 'AOL';
			break;
			case 'omniweb':
			case 'omni ':
				$this->agent = 'OTHER';
			break;
			case 'gecko ':
 			case 'moz ':
				$this->agent = 'OTHER';
			break;
			case 'netpositive ':
			case 'netp ':
				$this->agent = 'OTHER';
			break;

			case 'elinks/':
			case 'elinks ':
				$this->agent = 'OTHER';
			break;
			case 'links/':
			case 'links ':
				$this->agent = 'OTHER';
			break;
			case 'w3m/':
			case 'w3m ':
				$this->agent = 'OTHER';
			break;
			case 'webtv/':
			case 'webtv ':
				$this->agent = 'OTHER';
			break;
			case 'amaya/':
			case 'amaya ':
				$this->agent = 'OTHER';
			break;
			case 'dillo/':
			case 'dillo ':
				$this->agent = 'OTHER';
			break;
			case 'ibrowsevibrowse/':
			case 'ibrowsevibrowse ':
				$this->agent = 'OTHER';
			break;
			case 'icab/':
			case 'icab ':
				$this->agent = 'OTHER';
			break;
			case 'crazy browser ':
			case 'ie ':
				$this->agent = 'OTHER';
			break;
			case 'camino/ ':
			case 'camino ':
				$this->agent = 'OTHER';
			break;
			case 'sonyericssonp800/':
			case 'sonyericssonp800 ':
				$this->agent = 'OTHER';
			break;
			
			case 'googlebot ':
			case 'google ':
			case 'mediapartners-google ':
			case 'adsense ':
			case 'yahoo-verticalcrawler ':
			case 'yahoo ':
			case 'yahoo! slurp ':
			case 'yahoo-mm ':
			case 'yahoomm ':
			case 'inktomi ':
			case 'slurp ':
			case 'fast-webcrawler ':
			case 'msnbot ':
			case 'msn ':
			case 'ask jeeves ':
			case 'ask ':
			case 'teoma ':
			case 'scooter ':
			case 'openbot ':
			case 'ia_archiver ':
			case 'zyborg ':
			case 'looksmart ':
			case 'almaden ':
			case 'baiduspider ':
			case 'baidu ':
			case 'psbot ':
			case 'gigabot ':
			case 'naverbot ':
			case 'surveybot ':
			case 'boitho.com-dc ':
			case 'boitho ':
			case 'objectssearch ':
			case 'answerbus ':
			case 'sohu-search ':
			case 'sohu ':
			case 'iltrovatore-setaccio ':
			case 'il-set ':
				$this->agent = 'BOT';
			break;
			case 'unknown':
				$this->agent = 'OTHER';
			break;
			default:
				$this->agent = 'Oops!';
		}
		
		// Determine platform
		// This is very incomplete for platforms other than Win/Mac
		if ( preg_match( '/(android|iphone|ipod|win|mac|linux|unix|x11|freebsd|beos|ubuntu|fedora|os2|irix|sunos|aix)/', $user_agent, $matches ) );
		else $matches[1] = 'unknown';
		
		switch ( $matches[1] )
		{
			// Mobiles		
			case 'android':
				$this->platform = 'Android';
			break;		
			case 'iphone':
				$this->platform = 'IOS';
			break;	
			case 'ipod':
				$this->platform = 'IOS';
			break;
			// Windows			
			case 'win':
				$this->platform = 'Win';
			break;		
			// Mac			
			case 'mac':
				$this->platform = 'Mac';
			break;
			case 'os2':
				$this->platform = 'OS2';
				break;			
			// Linux		
			case 'linux':
				$this->platform = 'Linux';
			break;
			case 'unix':
			case 'x11':
				$this->platform = 'Unix';
			break;
			case 'freebsd':
				$this->platform = 'FreeBSD';
			break;
			case 'beos':
				$this->platform = 'BeOS';
			break;
			case 'ubuntu':
				$this->platform = 'Ubuntu';
			break;
			case 'fedora':
				$this->platform = 'Fedora';
			break;			
			
            case 'irix':
				$this->platform = 'IRIX';
			break;
            case 'sunos':
				$this->platform = 'SunOS';
			break;
            case 'aix':
				$this->platform = 'Aix';
			break;
            case 'palm':
				$this->platform = 'PalmOS';
			break;
			case 'unknown':
				$this->platform = 'Other';
			break;
			default:
				$this->platform = 'Oops!';
		}
	}

	/**
	 * update_info.
	 *
	 * @param unknown_type $id
	 */
	function update_info( $id )
	{
		global $user_ip, $db, $user;

		$where_sql = ( $user->data['user_id'] != ANONYMOUS ) ? "user_id = '" . $user->data['user_id'] . "'" : "downloader_ip = '" . $user_ip . "'";

		$sql = "SELECT user_id, downloader_ip
			FROM " . PA_DOWNLOAD_INFO_TABLE . "
			WHERE $where_sql";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query User id', '', __LINE__, __FILE__, $sql );
		}

		if ($row = $db->sql_fetchrow($result))
		{
		   $sql_numrows = count($row);		
		}
		else
		{
		   $sql_numrows = false;		
		}
		
		if (!$sql_numrows)
		{
			$sql = "INSERT INTO " . PA_DOWNLOAD_INFO_TABLE . " (file_id, user_id, downloader_ip, downloader_os, downloader_browser, browser_version)
						VALUES('" . $id . "', '" . $user->data['user_id'] . "', '" . $user_ip . "', '" . $this->platform . "', '" . $this->agent . "', '" . $this->ver . "')";
			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Update Downloader Table Info', '', __LINE__, __FILE__, $sql );
			}
		}

		$db->sql_freeresult( $result );
	}
}

/**
 * mx_pa_notification.
 *
 * This class extends general mx_notification class
 *
 * // MODE: MX_PM_MODE/MX_MAIL_MODE, $id: get all file/article data for this id
 * $mx_notification->init($mode, $id); // MODE: MX_PM_MODE/MX_MAIL_MODE
 *
 * // MODE: MX_PM_MODE/MX_MAIL_MODE, ACTION: MX_NEW_NOTIFICATION/MX_EDITED_NOTIFICATION/MX_APPROVED_NOTIFICATION/MX_UNAPPROVED_NOTIFICATION
 * $mx_notification->notify( $mode = MX_PM_MODE, $action = MX_NEW_NOTIFICATION, $to_id, $from_id, $subject, $message, $html_on, $bbcode_on, $smilies_on )
 *
 * @access public
 * @author Jon Ohlsson
 */
class mx_pa_notification extends mx_notification
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $item_id
	 */
	function init( $item_id = 0, $allow_comment_wysiwyg = 0)
	{
		global $db, $user, $module_root_path, $phpbb_root_path, $mx_root_path, $phpEx, $pafiledb;

			// =======================================================
			// item id is not set, give him/her a nice error message
			// =======================================================
			if (empty($item_id))
			{
				mx_message_die(GENERAL_ERROR, 'Bad Init pars');
			}

			unset($this->langs);

			//
			// Build up generic lang keys
			//
			$this->langs['item_not_exist'] = $user->lang['File_not_exist'];
			$this->langs['module_title'] = $user->lang['PA_prefix'];

			$this->langs['notify_subject_new'] = $user->lang['PA_notify_subject_new'];
			$this->langs['notify_subject_edited'] = $user->lang['PA_notify_subject_edited'];
			$this->langs['notify_subject_approved'] = $user->lang['PA_notify_subject_approved'];
			$this->langs['notify_subject_unapproved'] = $user->lang['PA_notify_subject_unapproved'];
			$this->langs['notify_subject_deleted'] = $user->lang['PA_notify_subject_deleted'];

			$this->langs['notify_new_body'] = $user->lang['PA_notify_new_body'];
			$this->langs['notify_edited_body'] = $user->lang['PA_notify_edited_body'];
			$this->langs['notify_approved_body'] = $user->lang['PA_notify_approved_body'];
			$this->langs['notify_unapproved_body'] = $user->lang['PA_notify_unapproved_body'];
			$this->langs['notify_deleted_body'] = $user->lang['PA_notify_deleted_body'];

			$this->langs['item_title'] = $user->lang['File'];
			$this->langs['author'] = $user->lang['Submited'] ? $user->lang['Submited'] : $user->lang['Creator'];
			$this->langs['item_description'] = $user->lang['Desc'];
			$this->langs['item_type'] = '';
			$this->langs['category'] = $user->lang['Category'];
			$this->langs['read_full_item'] = $user->lang['PA_goto'];
			$this->langs['edited_item_info'] = $user->lang['Edited_Article_info'];

			switch ( SQL_LAYER )
			{
				case 'oracle':
					$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username
						FROM " . PA_FILES_TABLE . " AS f, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_CATEGORY_TABLE . " AS c
						WHERE f.file_id = r.votes_file(+)
						AND f.user_id = u.user_id(+)
						AND c.cat_id = a.file_catid
						AND f.file_id = '" . $item_id . "'
						GROUP BY f.file_id ";
					break;

				default:
            		$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username
                  		FROM " . PA_FILES_TABLE . " AS f
                     		LEFT JOIN " . PA_CATEGORY_TABLE . " AS cat ON f.file_catid = cat.cat_id
                     		LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f.file_id = r.votes_file
                     		LEFT JOIN " . USERS_TABLE . " AS u ON f.user_id = u.user_id
                  		WHERE f.file_id = '" . $item_id . "'
                  		GROUP BY f.file_id ";
					break;
			}

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
			}

			// ===================================================
			// file doesn't exist'
			// ===================================================
			if ( !$item_data = $db->sql_fetchrow( $result ) )
			{
				mx_message_die( GENERAL_MESSAGE, $this->langs['Item_not_exist'] );
			}

			$db->sql_freeresult( $result );

			unset($this->data);

			//
			// File data
			//
			$this->data['item_id'] = $item_id;
			$this->data['item_title'] = $item_data['file_name'];
			$this->data['item_desc'] = $item_data['file_desc'];


			//
			// Category data
			//
			$this->data['item_category_id'] = $item_data['cat_id'];
			$this->data['item_category_name'] = $item_data['cat_name'];

			//
			// File author
			//
			$this->data['item_author_id'] = $item_data['user_id'];
			$this->data['item_author'] = ( $item_data['user_id'] != ANONYMOUS ) ? $item_data['username'] : $user->lang['Guest'];

			//
			// File editor
			//
			$this->data['item_editor_id'] = $user->data['user_id'];
			$this->data['item_editor'] = ( $user->data['user_id'] != '-1' ) ? $user->data['username'] : $user->lang['Guest'];

			$mx_root_path_tmp = $mx_root_path; // Stupid workaround, since phpbb posts need full paths.
			$mx_root_path = '';
			$this->temp_url = PORTAL_URL . $pafiledb->this_mxurl("action=" . "file&file_id=" . $this->data['item_id'], false, true);
			$mx_root_path = $mx_root_path_tmp;

			//
			// Toggles
			//
			$this->allow_comment_wysiwyg = $allow_comment_wysiwyg;
	}
}

/**
 * This is a generic class for custom fields.
 *
 * Note: This class doesn't differ from the core mx_custom_fields class, besides the templating.
 *
 */
class custom_field
{
	var $field_rowset = array();
	var $field_data_rowset = array();

	var $custom_table = PA_CUSTOM_TABLE;
	var $custom_data_table = PA_CUSTOM_DATA_TABLE;

	/**
	 * prepare data
	 *
	 */
	function init()
	{
		global $db;

		$sql = "SELECT *
			FROM " . $this->custom_table . "
			ORDER BY field_order ASC";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query Custom field', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$this->field_rowset[$row['custom_id']] = $row;
		}
		unset( $row );
		$db->sql_freeresult( $result );

		$sql = "SELECT *
			FROM " . $this->custom_data_table;

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query Custom field', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$this->field_data_rowset[$row['customdata_file']][$row['customdata_custom']] = $row;
		}

		unset( $row );

		$db->sql_freeresult( $result );
	}

	/**
	 * check if there is a data in the database.
	 *
	 * @return unknown
	 */
	function field_data_exist()
	{
		if ( !empty( $this->field_data_rowset ) )
		{
			return true;
		}
		return false;
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function field_exist()
	{
		if ( !empty( $this->field_rowset ) )
		{
			return true;
		}
		return false;
	}

	/**
	 * display data in the comment.
	 *
	 * @param unknown_type $file_id
	 * @return unknown
	 */
	function add_comment( $file_id )
	{
		global $template;
		if ( $this->field_data_exist() )
		{
			if ( isset( $this->field_data_rowset[$file_id] ) )
			{
				$message = '';
				foreach( $this->field_data_rowset[$file_id] as $field_id => $data )
				{
					if ( !empty( $data['data'] ) )
					{
						switch ( $this->field_rowset[$field_id]['field_type'] )
						{
							case INPUT:
							case TEXTAREA:
							case RADIO:
							case SELECT:
								$field_data = $data['data'];
								break;
							case SELECT_MULTIPLE:
							case CHECKBOX:
								$field_data = @implode( ', ', unserialize( $data['data'] ) );
								break;
						}
						$message .= "\n" . "[b]" . $this->field_rowset[$field_id]['custom_name'] . ":[/b] " . $field_data . "\n";
					}
					else
					{
						global $db;

						$sql = "DELETE FROM " . $this->custom_data_table . "
							WHERE customdata_file = '$file_id'
							AND customdata_custom = '$field_id'";

						if ( !( $db->sql_query( $sql ) ) )
						{
							mx_message_die( GENERAL_ERROR, 'Could not delete custom data', '', __LINE__, __FILE__, $sql );
						}
					}
				}
				return $message;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * display data in the file page.
	 *
	 * @param unknown_type $file_id
	 * @return unknown
	 */
	function display_data( $file_id )
	{
		global $template;
		if ( $this->field_data_exist() )
		{
			if ( isset( $this->field_data_rowset[$file_id] ) )
			{
				foreach( $this->field_data_rowset[$file_id] as $field_id => $data )
				{
					if ( !empty( $data['data'] ) )
					{
						switch ( $this->field_rowset[$field_id]['field_type'] )
						{
							case INPUT:
							case TEXTAREA:
							case RADIO:
							case SELECT:
								$field_data = $data['data'];
								break;
							case SELECT_MULTIPLE:
							case CHECKBOX:
								$field_data = @implode( ', ', unserialize( $data['data'] ) );
								break;
						}

						$template->assign_block_vars( 'custom_field', array(
							'CUSTOM_NAME' => $this->field_rowset[$field_id]['custom_name'],
							'DATA' => $field_data )
						);
					}
					else
					{
						global $db;

						$sql = "DELETE FROM " . $this->custom_data_table . "
							WHERE customdata_file = '$file_id'
							AND customdata_custom = '$field_id'";

						if ( !( $db->sql_query( $sql ) ) )
						{
							mx_message_die( GENERAL_ERROR, 'Could not delete custom data', '', __LINE__, __FILE__, $sql );
						}
					}
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * display custom field and data in the add/edit page.
	 *
	 * @param unknown_type $file_id
	 * @return unknown
	 */
	function display_edit( $file_id = false )
	{
		$return = false;
		if ( $this->field_exist() )
		{
			foreach( $this->field_rowset as $field_id => $field_data )
			{
				switch ( $field_data['field_type'] )
				{
					case INPUT:
						$this->display_edit_input( $file_id, $field_id, $field_data );
						break;
					case TEXTAREA:
						$this->display_edit_textarea( $file_id, $field_id, $field_data );
						break;
					case RADIO:
						$this->display_edit_radio( $file_id, $field_id, $field_data );
						break;
					case SELECT:
						$this->display_edit_select( $file_id, $field_id, $field_data );
						break;
					case SELECT_MULTIPLE:
						$this->display_edit_select_multiple( $file_id, $field_id, $field_data );
						break;
					case CHECKBOX:
						$this->display_edit_checkbox( $file_id, $field_id, $field_data );
						break;
				}

				$return = true;
			}
		}
		return $return;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $field_id
	 * @param unknown_type $field_data
	 */
	function display_edit_input( $file_id, $field_id, $field_data )
	{
		global $template;
		$field_value_temp =  (!empty( $this->field_data_rowset[$file_id][$field_id]['data'] )) ? $this->field_data_rowset[$file_id][$field_id]['data'] : '';
		$field_value = !empty( $_POST['field'][$field_data['custom_id']] ) ? $_POST['field'][$field_data['custom_id']] : $field_value_temp ;
		$template->assign_block_vars( 'input', array(
			'FIELD_NAME' => $field_data['custom_name'],
			'FIELD_ID' => $field_data['custom_id'],
			'FIELD_DESCRIPTION' => $field_data['custom_description'],
			'FIELD_VALUE' =>  $field_value )
		);
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $field_id
	 * @param unknown_type $field_data
	 */
	function display_edit_textarea( $file_id, $field_id, $field_data )
	{
		global $template;
		$field_value_temp = ( !empty( $this->field_data_rowset[$file_id][$field_id]['data'] ) ) ? $this->field_data_rowset[$file_id][$field_id]['data'] : '';
		$field_value = !empty( $_POST['field'][$field_data['custom_id']] ) ? $_POST['field'][$field_data['custom_id']] : $field_value_temp ;
		$template->assign_block_vars( 'textarea', array(
			'FIELD_NAME' => $field_data['custom_name'],
			'FIELD_ID' => $field_data['custom_id'],
			'FIELD_DESCRIPTION' => $field_data['custom_description'],
			'FIELD_VALUE' => $field_value )
		);
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $field_id
	 * @param unknown_type $field_data
	 */
	function display_edit_radio( $file_id, $field_id, $field_data )
	{
		global $template;
		$template->assign_block_vars( 'radio', array(
			'FIELD_NAME' => $field_data['custom_name'],
			'FIELD_ID' => $field_data['custom_id'],
			'FIELD_DESCRIPTION' => $field_data['custom_description'] )
		);

		$data_temp = ( !empty( $this->field_data_rowset[$file_id][$field_id]['data'] ) ) ? $this->field_data_rowset[$file_id][$field_id]['data'] : array();
		$data = !empty( $_POST['field'][$field_data['custom_id']] ) ? $_POST['field'][$field_data['custom_id']] : $data_temp ;
		$field_datas = ( !empty( $field_data['data'] ) ) ? unserialize( stripslashes( $field_data['data'] ) ) : array();

		if ( !empty( $field_datas ) )
		{
			foreach( $field_datas as $key => $value )
			{
				$template->assign_block_vars( 'radio.row', array(
					'FIELD_VALUE' => $value,
					'FIELD_SELECTED' => ( $data == $value ) ? ' checked="checked"' : '' )
				);
			}
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $field_id
	 * @param unknown_type $field_data
	 */
	function display_edit_select( $file_id, $field_id, $field_data )
	{
		global $template;
		$template->assign_block_vars( 'select', array(
			'FIELD_NAME' => $field_data['custom_name'],
			'FIELD_ID' => $field_data['custom_id'],
			'FIELD_DESCRIPTION' => $field_data['custom_description'] )
		);

		$data_temp = ( !empty( $this->field_data_rowset[$file_id][$field_id]['data'] ) ) ? $this->field_data_rowset[$file_id][$field_id]['data'] : '';
		$data = !empty( $_POST['field'][$field_data['custom_id']] ) ? $_POST['field'][$field_data['custom_id']] : $data_temp ;
		$field_datas = ( !empty( $field_data['data'] ) ) ? unserialize( stripslashes( $field_data['data'] ) ) : array();

		if ( !empty( $field_datas ) )
		{
			foreach( $field_datas as $key => $value )
			{
				$template->assign_block_vars( 'select.row', array(
					'FIELD_VALUE' => $value,
					'FIELD_SELECTED' => ( $data == $value ) ? ' selected="selected"' : '' )
				);
			}
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $field_id
	 * @param unknown_type $field_data
	 */
	function display_edit_select_multiple( $file_id, $field_id, $field_data )
	{
		global $template;
		$template->assign_block_vars( 'select_multiple', array(
			'FIELD_NAME' => $field_data['custom_name'],
			'FIELD_ID' => $field_data['custom_id'],
			'FIELD_DESCRIPTION' => $field_data['custom_description'] )
		);

		$data_temp = ( !empty( $this->field_data_rowset[$file_id][$field_id]['data'] ) ) ? unserialize( $this->field_data_rowset[$file_id][$field_id]['data'] ) : array();
		$data = !empty( $_POST['field'][$field_data['custom_id']] ) ? $_POST['field'][$field_data['custom_id']] : $data_temp ;
		$field_datas = ( !empty( $field_data['data'] ) ) ? unserialize( stripslashes( $field_data['data'] ) ) : array();

		if ( !empty( $field_datas ) )
		{
			foreach( $field_datas as $key => $value )
			{
				$selected = '';
				foreach( $data as $field_value )
				{
					if ( $field_value == $value )
					{
						$selected = '  selected="selected"';
						break;
					}
				}
				$template->assign_block_vars( 'select_multiple.row', array(
					'FIELD_VALUE' => $value,
					'FIELD_SELECTED' => $selected )
				);
			}
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $file_id
	 * @param unknown_type $field_id
	 * @param unknown_type $field_data
	 */
	function display_edit_checkbox( $file_id, $field_id, $field_data )
	{
		global $template;
		$template->assign_block_vars( 'checkbox', array(
			'FIELD_NAME' => $field_data['custom_name'],
			'FIELD_ID' => $field_data['custom_id'],
			'FIELD_DESCRIPTION' => $field_data['custom_description'] )
		);

		$data_temp = ( !empty( $this->field_data_rowset[$file_id][$field_id]['data'] ) ) ? unserialize( $this->field_data_rowset[$file_id][$field_id]['data'] ) : array();
		$data = !empty( $_POST['field'][$field_data['custom_id']] ) ? $_POST['field'][$field_data['custom_id']] : $data_temp ;
		$field_datas = ( !empty( $field_data['data'] ) ) ? unserialize( stripslashes( $field_data['data'] ) ) : array();

		if ( !empty( $field_datas ) )
		{
			foreach( $field_datas as $key => $value )
			{
				$checked = '';
				foreach( $data as $field_value )
				{
					if ( $field_value == $value )
					{
						$checked = ' checked';
						break;
					}
				}
				$template->assign_block_vars( 'checkbox.row', array(
					'FIELD_VALUE' => $value,
					'FIELD_CHECKED' => $checked )
				);
			}
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $field_type
	 * @param unknown_type $field_id
	 */
	function update_add_field( $field_type, $field_id = false )
	{
		global $db, $db, $_POST, $user;

		$field_name = ( isset( $_POST['field_name'] ) ) ? htmlspecialchars( $_POST['field_name'] ) : '';
		$field_desc = ( isset( $_POST['field_desc'] ) ) ? htmlspecialchars( $_POST['field_desc'] ) : '';
		$regex = ( isset( $_POST['regex'] ) ) ? $_POST['regex'] : '';
		$data = ( isset( $_POST['data'] ) ) ? $_POST['data'] : '';
		$field_order = ( isset( $_POST['field_order'] ) ) ? $_POST['field_order'] : '';

		if ( $field_id )
		{
			$field_order = ( isset( $_POST['field_order'] ) ) ? intval( $_POST['field_order'] ) : '';
		}

		if ( !empty( $data ) )
		{
			$data = explode( "\n", htmlspecialchars( trim( $data ) ) );

			foreach( $data as $key => $value )
			{
				$data[$key] = trim( $value );
			}
			$data = addslashes( serialize( $data ) );
		}

		if ( empty( $field_name ) )
		{
			mx_message_die( GENERAL_ERROR, $user->lang['Missing_field'] );
		}

		if ( ( ( $field_type != INPUT && $field_type != TEXTAREA ) && empty( $data ) ) )
		{
			mx_message_die( GENERAL_ERROR, $user->lang['Missing_field'] );
		}

		if ( !$field_id )
		{
			$sql_array = array(
				'custom_name' => utf8_normalize_nfc($field_name),
				'custom_description' => utf8_normalize_nfc($field_desc), 
				'data' => $data, 
				'regex' => $regex, 
				'field_type' => $field_type,
			);							
							
			$sql = "INSERT INTO " . $this->custom_table . $db->sql_build_array('INSERT', $sql_array);
			
			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not add the new fields', '', __LINE__, __FILE__, $sql );
			}

			$field_id = $db->sql_nextid();
			
			$sql = "UPDATE " . $this->custom_table . "
				SET field_order = '$field_id'
				WHERE custom_id = $field_id";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not set the order for the giving field', '', __LINE__, __FILE__, $sql );
			}
		}
		else
		{
		
			$sql_array = array(
				'custom_name' => utf8_normalize_nfc($field_name),
				'custom_description' => utf8_normalize_nfc($field_desc),
				'data' => $data,
				'regex' => $regex,
				'field_order' => (int) $field_order,				
			);

			$sql = "UPDATE " . $this->custom_table . "SET " . $db->sql_build_array('UPDATE', $sql_array) . "
						WHERE custom_id = '" . $db->sql_escape($field_id) . "'";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not update information for the giving field', '', __LINE__, __FILE__, $sql );
			}
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $field_id
	 */
	function delete_field( $field_id )
	{
		global $db;

		$sql = "DELETE FROM " . $this->custom_data_table . "
			WHERE customdata_custom = '$field_id'";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not delete custom data', '', __LINE__, __FILE__, $sql );
		}

		$sql = "DELETE FROM " . $this->custom_table . "
			WHERE custom_id = '$field_id'";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not delete the selected field', '', __LINE__, __FILE__, $sql );
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $field_id
	 * @return unknown
	 */
	function get_field_data( $field_id )
	{
		$return_array = $this->field_rowset[$field_id];
		$return_array['data'] = !empty( $return_array['data'] ) ? implode( "\n", unserialize( stripslashes( $return_array['data'] ) ) ) : '';
		return $return_array;
	}

	/**
	 * file data in custom field operations.
	 *
	 * @param unknown_type $file_id
	 */
	function file_update_data( $file_id )
	{
		global $_POST, $db;
		$field = ( isset( $_POST['field'] ) ) ? $_POST['field'] : '';
		if ( !empty( $field ) )
		{
			foreach( $field as $field_id => $field_data )
			{
				if ( !empty( $this->field_rowset[$field_id]['regex'] ) )
				{
					if ( !preg_match( '#' . $this->field_rowset[$field_id]['regex'] . '#siU', $field_data ) )
					{
						$field_data = '';
					}
				}

				switch ( $this->field_rowset[$field_id]['field_type'] )
				{
					case INPUT:
					case TEXTAREA:
					case RADIO:
					case SELECT:
						$data = htmlspecialchars( $field_data );
						break;
					case SELECT_MULTIPLE:
					case CHECKBOX:
						$data = addslashes( serialize( $field_data ) );
						break;
				}

				$sql = "DELETE FROM " . $this->custom_data_table . "
					WHERE customdata_file = '$file_id'
					AND customdata_custom = '$field_id'";

				if ( !$db->sql_query( $sql ) )
				{
					mx_message_die( GENERAL_ERROR, 'Could not delete data from custom data table', '', __LINE__, __FILE__, $sql );
				}

				if ( !empty( $data ) )
				{
					$sql = "INSERT INTO " . $this->custom_data_table . " (customdata_file, customdata_custom, data)
						VALUES('$file_id', '$field_id', '$data')";

					if ( !$db->sql_query( $sql ) )
					{
						mx_message_die( GENERAL_ERROR, 'Could not add additional data', '', __LINE__, __FILE__, $sql );
					}
				}
			}
		}
	}
}

// ------------------------------------
// Functions
// ------------------------------------

/**
 * Enter description here...
 *
 * @return unknown
 */
function get_formated_url()
{
	global $board_config;
	global $mx_script_name;

	$server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
	$server_name = preg_replace( '#^\/?(.*?)\/?$#', '\1', trim( $board_config['server_name'] ) );
	$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim( $board_config['server_port'] ) : '';
	$script_name = preg_replace( '#^\/?(.*?)\/?$#', '\1', trim( $mx_script_name ) );
	$script_name = ( $script_name == '' ) ? $script_name : '/' . $script_name;
	$formated_url = $server_protocol . $server_name . $server_port . $script_name;

	return $formated_url;
}

/**
 * Enter description here...
 *
 * @param unknown_type $rating
 * @return unknown
 */
function paImageRating( $rating )
{
	global $db, $album_sp_config, $module_root_path;

	if ( !$rating )
		return( "<i>Not Rated</i>" );
	else
		return ( round( $rating, 2 ) );
}

// =========================================================================
// this function Borrowed from Acyd Burn attachment mod, (thanks Acyd for this great mod)
// =========================================================================
function send_file_to_browser( $real_filename, $physical_filename, $upload_dir )
{
	global $_SERVER, $HTTP_USER_AGENT, $HTTP_SERVER_VARS, $user, $db, $pafiledb_functions;

	if ( $upload_dir == '' )
	{
		$filename = $physical_filename;
	}
	else
	{
		$filename = $upload_dir . $physical_filename;
	}

	$gotit = false;
	if ( @!file_exists( @$pafiledb_functions->pafiledb_realpath( $filename ) ) )
	{
		mx_message_die( GENERAL_ERROR, $user->lang['Error_no_download'] . '<br /><br /><b>404 File Not Found:</b> The File <i>' . $filename . '</i> does not exist.' );
	}
	else
	{
		$gotit = true;
		$size = @filesize( $filename );
		if ( $size > ( 1048575 * 6 ) )
		{
			return false;
		}
	}

	// Determine the Browser the User is using, because of some nasty incompatibilities.
	// borrowed from phpMyAdmin. :)
	$user_agent = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';

	if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $user_agent, $log_version))
	{
		$browser_version = $log_version[2];
		$browser_agent = 'opera';
	}
	else if (ereg('MSIE ([0-9].[0-9]{1,2})', $user_agent, $log_version))
	{
		$browser_version = $log_version[1];
		$browser_agent = 'ie';
	}
	else if (ereg('OmniWeb/([0-9].[0-9]{1,2})', $user_agent, $log_version))
	{
		$browser_version = $log_version[1];
		$browser_agent = 'omniweb';
	}
	else if (ereg('(Konqueror/)(.*)(;)', $user_agent, $log_version))
	{
		$browser_version = $log_version[2];
		$browser_agent = 'konqueror';
	}
	else if (ereg('Mozilla/([0-9].[0-9]{1,2})', $user_agent, $log_version) && ereg('Safari/([0-9]*)', $user_agent, $log_version2))
	{
		$browser_version = $log_version[1] . '.' . $log_version2[1];
		$browser_agent = 'safari';
	}
	else if (ereg('Mozilla/([0-9].[0-9]{1,2})', $user_agent, $log_version))
	{
		$browser_version = $log_version[1];
		$browser_agent = 'mozilla';
	}
	else
	{
		$browser_version = 0;
		$browser_agent = 'other';
	}

	//
	// Get mimetype
	//
	switch ($pafiledb_functions->get_extension($physical_filename))
	{
		case 'pdf':
			$mimetype = 'application/pdf';
		break;

		case 'zip':
			$mimetype = 'application/zip';
		break;

		case 'gzip':
			$mimetype = 'application/x-gzip';
		break;

		case 'tar':
			$mimetype = 'application/x-tar';
		break;

		case 'tar.gz':
			$mimetype = 'application/x-gzip';
		break;

		case 'tar.bz2':
			$mimetype = 'application/x-bzip2';
		break;

		case 'doc':
			$mimetype = 'application/msword';
		break;

		// Windows Media Player
		case 'mpg':
			$mimetype = 'application/x-mplayer2';
		break;

		case 'mp3':
			$mimetype = 'audio/mp3';
		break;

		/*
		case 'asx':
			$mimetype = 'video/x-ms-asf';
		break;

		case 'wma':
			$mimetype = 'audio/x-ms-wma';
		break;

		case 'wax':
			$mimetype = 'audio/x-ms-wax';
		break;

		case 'wmv':
			$mimetype = 'video/x-ms-wmv';
		break;

		case 'wvx':
			$mimetype = 'video/x-ms-wvx';
		break;

		case 'wm':
			$mimetype = 'video/x-ms-wm';
		break;

		case 'wmx':
			$mimetype = 'video/x-ms-wmx';
		break;

		case 'wmz':
			$mimetype = 'application/x-ms-wmz';
		break;

		case 'wmd':
			$mimetype = 'application/x-ms-wmd';
		break;
		*/

		// Real Player
		case 'rpm':
			$mimetype = 'audio/x-pn-realaudio-plugin';
		break;

		default:
			$mimetype = ($browser_agent == 'ie' || $browser_agent == 'opera') ? 'application/octetstream' : 'application/octet-stream';
		break;
	}

	//
	// Correct the Mime Type, if it's an octetstream
	//
	/*
	if ( ( $mimetype == 'application/octet-stream' ) || ( $mimetype == 'application/octetstream' ) )
	{
		$mimetype = ($browser_agent == 'ie' || $browser_agent == 'opera') ? 'application/octetstream' : 'application/octet-stream';
	}
	*/

	// Correct the mime type - we force application/octetstream for all files, except images
	// Please do not change this, it is a security precaution
	//$mimetype = ($browser_agent == 'ie' || $browser_agent == 'opera') ? 'application/octetstream' : 'application/octet-stream';

	if (@ob_get_length())
	{
		@ob_end_clean();
	}
	@ini_set( 'zlib.output_compression', 'Off' );

	header('Pragma: public');
	header('Cache-control: private, must-revalidate');

	// Send out the Headers
	if ( isset($_GET['save_as']) || true)
	{
		//
		// Force the "save file as" dialog
		//
		$mimetype = 'application/x-download'; // Fix for avoiding browser doing an 'inline' for known mimetype anyway
		header('Content-Type: ' . $mimetype . '; name="' . $real_filename . '"');
		header('Content-Disposition: attachment; filename="' . $real_filename . '"');
	}
	else
	{
		header('Content-Type: ' . $mimetype . '; name="' . $real_filename . '"');
		header('Content-Disposition: inline; filename="' . $real_filename . '"');
	}

	// Now send the File Contents to the Browser
	$size = @filesize($filename);
	if ($size)
	{
		header("Content-length: $size");
	}
	$result = @readfile($filename);

	if (!$result)
	{
		// PHP track_errors setting On?
		if (!empty($php_errormsg))
		{
			mx_message_die( GENERAL_ERROR, 'Unable to deliver file.<br />Error was: ' . $php_errormsg, E_USER_WARNING);
		}

		mx_message_die( GENERAL_ERROR, 'Unable to deliver file.');
	}

	flush();
	exit;
}

function pa_redirect( $file_url )
{
	global $pafiledb_cache, $db;
	if ( isset( $db ) )
	{
		$db->sql_close();
	}

	if ( isset( $pafiledb_cache ) )
	{
		$pafiledb_cache->unload();
	}
	// Redirect via an HTML form for PITA webservers
	if ( @preg_match( '/Microsoft|WebSTAR|Xitami/', getenv( 'SERVER_SOFTWARE' ) ) )
	{
		header( 'Refresh: 0; URL=' . $file_url );
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta http-equiv="refresh" content="0; url=' . $file_url . '"><title>Redirect</title></head><body><div align="center">If your browser does not support meta redirection please click <a href="' . $file_url . '">HERE</a> to be redirected</div></body></html>';
		exit;
	}
	// Behave as per HTTP/1.1 spec for others
	Header( "Location: $file_url" );
	exit();
}
?>