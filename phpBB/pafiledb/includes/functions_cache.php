<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: functions_cache.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Mohd Basri, PHP Arena, pafileDB, Jon Ohlsson] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

/**
 * Generic module cache.
 *
 */
class pafiledb_cache
{
	var $vars = '';
	var $vars_ts = array();
	var $modified = false;

	/**
	 * Enter description here...
	 *
	 * @return pafiledb_cache
	 */
	function pafiledb_cache($dir=false)
	{
		global $phpbb_root_path;
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		if (!$dir)
		{
			mx_message_die(GENERAL_ERROR, 'The module cache need a init dir.');
		}

		$this->cache_dir = $dir . 'cache/';
	}

	/**
	 * Enter description here...
	 *
	 */
	function load()
	{
		global $phpEx;
		@include( $this->cache_dir . 'data_global.' . $phpEx );
	}

	/**
	 * Enter description here...
	 *
	 */
	function unload()
	{
		$this->save();
		unset( $this->vars );
		unset( $this->vars_ts );
	}

	/**
	 * Enter description here...
	 *
	 */
	function save()
	{
		if ( !$this->modified )
		{
			return;
		}

		global $phpEx;
		$file = '<?php $this->vars=' . $this->format_array( $this->vars ) . ";\n\$this->vars_ts=" . $this->format_array( $this->vars_ts ) . ' ?>';

		if ( $fp = @fopen( $this->cache_dir . 'data_global.' . $phpEx, 'wb' ) )
		{
			@flock( $fp, LOCK_EX );
			fwrite( $fp, $file );
			@flock( $fp, LOCK_UN );
			fclose( $fp );
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $expire_time
	 */
	function tidy( $expire_time = 0 )
	{
		global $phpEx;

		$dir = opendir( $this->cache_dir );
		while ( $entry = readdir( $dir ) )
		{
			if ( $entry{0} == '.' || substr( $entry, 0, 4 ) != 'sql_' )
			{
				continue;
			}

			if ( time() - $expire_time >= filemtime( $this->cache_dir . $entry ) )
			{
				unlink( $this->cache_dir . $entry );
			}
		}

		if ( file_exists( $this->cache_dir . 'data_global.' . $phpEx ) )
		{
			foreach ( $this->vars_ts as $varname => $timestamp )
			{
				if ( time() - $expire_time >= $timestamp )
				{
					$this->destroy( $varname );
				}
			}
		}
		else
		{
			$this->vars = $this->vars_ts = array();
			$this->modified = true;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $varname
	 * @param unknown_type $expire_time
	 * @return unknown
	 */
	function get( $varname, $expire_time = 0 )
	{
		return ( $this->exists( $varname, $expire_time ) ) ? $this->vars[$varname] : null;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $varname
	 * @param unknown_type $var
	 */
	function put( $varname, $var )
	{
		$this->vars[$varname] = $var;
		$this->vars_ts[$varname] = time();
		$this->modified = true;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $varname
	 */
	function destroy( $varname )
	{
		if ( isset( $this->vars[$varname] ) )
		{
			$this->modified = true;
			unset( $this->vars[$varname] );
			unset( $this->vars_ts[$varname] );
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $varname
	 * @param unknown_type $expire_time
	 * @return unknown
	 */
	function exists( $varname, $expire_time = 0 )
	{
		if ( !is_array( $this->vars ) )
		{
			$this->load();
		}

		if ( $expire_time > 0 && isset( $this->vars_ts[$varname] ) )
		{
			if ( $this->vars_ts[$varname] <= time() - $expire_time )
			{
				$this->destroy( $varname );
				return false;
			}
		}

		return isset( $this->vars[$varname] );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $array
	 * @return unknown
	 */
	function format_array( $array )
	{
		$lines = array();
		foreach ( $array as $k => $v )
		{
			if ( is_array( $v ) )
			{
				$lines[] = "'$k'=>" . $this->format_array( $v );
			}elseif ( is_int( $v ) )
			{
				$lines[] = "'$k'=>$v";
			}elseif ( is_bool( $v ) )
			{
				$lines[] = "'$k'=>" . ( ( $v ) ? 'TRUE' : 'FALSE' );
			}
			else
			{
				$lines[] = "'$k'=>'" . str_replace( "'", "\'", str_replace( '\\', '\\\\', $v ) ) . "'";
			}
		}
		return 'array(' . implode( ',', $lines ) . ')';
	}
}
?>