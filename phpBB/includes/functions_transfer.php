<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Transfer class, wrapper for ftp/sftp/ssh
*/
class transfer
{
	var $connection;
	var $host;
	var $port;
	var $username;
	var $password;
	var $timeout;
	var $root_path;
	var $tmp_path;
	var $file_perms;
	var $dir_perms;

	/**
	* Constructor - init some basic values
	*/
	function transfer()
	{
		global $phpbb_root_path;

		$this->file_perms	= 0644;
		$this->dir_perms	= 0777;

		// We use the store directory as temporary path to circumvent open basedir restrictions
		$this->tmp_path = $phpbb_root_path . 'store/';
	}

	/**
	* Write file to location
	*/
	function write_file($destination_file = '', $contents = '')
	{
		global $phpbb_root_path;

		$destination_file = $this->root_path . str_replace($phpbb_root_path, '', $destination_file);

		// need to create a temp file and then move that temp file.
		// ftp functions can only move files around and can't create.
		// This means that the users will need to have access to write
		// temporary files or have write access on a folder within phpBB
		// like the cache folder. If the user can't do either, then
		// he/she needs to use the fsock ftp method
		$temp_name = tempnam($this->tmp_path, 'transfer_');
		@unlink($temp_name);

		$fp = @fopen($temp_name, 'w');

		if (!$fp)
		{
			trigger_error('Unable to create temporary file ' . $temp_name, E_USER_ERROR);
		}

		@fwrite($fp, $contents);
		@fclose($fp);

		$result = $this->overwrite_file($temp_name, $destination_file);

		// remove temporary file now
		@unlink($temp_name);

		return $result;
	}

	/**
	* Moving file into location. If the destination file already exists it gets overwritten
	*/
	function overwrite_file($source_file, $destination_file)
	{
		/**
		* @todo generally think about overwriting files in another way, by creating a temporary file and then renaming it
		* @todo check for the destination file existance too
		*/
		$this->_delete($destination_file);
		$result = $this->_put($source_file, $destination_file);
		$this->_chmod($destination_file, $this->file_perms);

		return $result;
	}

	/**
	* Create directory structure
	*/
	function make_dir($dir)
	{
		global $phpbb_root_path;

		$dir = str_replace($phpbb_root_path, '', $dir);
		$dir = explode('/', $dir);
		$dirs = '';

		for ($i = 0, $total = sizeof($dir); $i < $total; $i++)
		{
			$result = true;

			if (strpos($dir[$i], '.') === 0)
			{
				continue;
			}
			$cur_dir = $dir[$i] . '/';

			if (!file_exists($phpbb_root_path . $dirs . $cur_dir))
			{
				// create the directory
				$result = $this->_mkdir($dir[$i]);
				$this->_chmod($dir[$i], $this->dir_perms);
			}

			$this->_chdir($this->root_path . $dirs . $dir[$i]);
			$dirs .= $cur_dir;
		}

		$this->_chdir($this->root_path);

		/**
		* @todo stack result into array to make sure every path creation has been taken care of
		*/
		return $result;
	}

	/**
	* Copy file from source location to destination location
	*/
	function copy_file($from_loc, $to_loc)
	{
		global $phpbb_root_path;

		$from_loc = ((strpos($from_loc, $phpbb_root_path) !== 0) ? $phpbb_root_path : '') . $from_loc;
		$to_loc = $this->root_path . str_replace($phpbb_root_path, '', $to_loc);

		if (!file_exists($from_loc))
		{
			return false;
		}

		$result = $this->overwrite_file($from_loc, $to_loc);

		return $result;
	}

	/**
	* Remove file
	*/
	function delete_file($file)
	{
		global $phpbb_root_path;

		$file = $this->root_path . str_replace($phpbb_root_path, '', $file);

		return $this->_delete($file);
	}

	/**
	* Remove directory
	* @todo remove child directories?
	*/
	function remove_dir($dir)
	{
		global $phpbb_root_path;

		$dir = $this->root_path . str_replace($phpbb_root_path, '', $dir);

		return $this->_rmdir($dir);
	}

	/**
	* Rename a file or folder
	*/
	function rename($old_handle, $new_handle)
	{
		global $phpbb_root_path;

		$old_handle = $this->root_path . str_replace($phpbb_root_path, '', $old_handle);

		return $this->_rename($old_handle, $new_handle);
	}

	/**
	* Check if a specified file exist...
	*/
	function file_exists($directory, $filename)
	{
		global $phpbb_root_path;

		$directory = $this->root_path . str_replace($phpbb_root_path, '', $directory);

		$this->_chdir($directory);
		$result = $this->_ls();

		if ($result !== false && is_array($result))
		{
			return (in_array($filename, $result)) ? true : false;
		}

		return false;
	}

	/**
	* Open session
	*/
	function open_session()
	{
		return $this->_init();
	}

	/**
	* Close current session
	*/
	function close_session()
	{
		return $this->_close();
	}

	/**
	* Determine methods able to be used
	*/
	static public function methods()
	{
		$methods = array();
		$disabled_functions = explode(',', @ini_get('disable_functions'));

		if (@extension_loaded('ftp'))
		{
			$methods[] = 'ftp';
		}

		if (!in_array('fsockopen', $disabled_functions))
		{
			$methods[] = 'ftp_fsock';
		}

		return $methods;
	}
}

/**
* FTP transfer class
*/
class ftp extends transfer
{
	/**
	* Standard parameters for FTP session
	*/
	function ftp($host, $username, $password, $root_path, $port = 21, $timeout = 10)
	{
		$this->host			= $host;
		$this->port			= $port;
		$this->username		= $username;
		$this->password		= $password;
		$this->timeout		= $timeout;

		// Make sure $this->root_path is layed out the same way as the $user->page['root_script_path'] value (/ at the end)
		$this->root_path	= str_replace('\\', '/', $this->root_path);

		if (!empty($root_path))
		{
			$this->root_path = (($root_path[0] != '/' ) ? '/' : '') . $root_path . ((substr($root_path, -1, 1) == '/') ? '' : '/');
		}

		// Init some needed values
		$this->transfer();

		return;
	}

	/**
	* Requests data
	*/
	static public function data()
	{
		global $user;

		return array(
			'host'		=> 'localhost',
			'username'	=> 'anonymous',
			'password'	=> '',
			'root_path'	=> $user->page['root_script_path'],
			'port'		=> 21,
			'timeout'	=> 10
		);
	}

	/**
	* Init FTP Session
	* @access private
	*/
	function _init()
	{
		// connect to the server
		$this->connection = @ftp_connect($this->host, $this->port, $this->timeout);

		if (!$this->connection)
		{
			return 'ERR_CONNECTING_SERVER';
		}

		// login to the server
		if (!@ftp_login($this->connection, $this->username, $this->password))
		{
			return 'ERR_UNABLE_TO_LOGIN';
		}

		// attempt to turn pasv mode on
		@ftp_pasv($this->connection, true);

		// change to the root directory
		if (!$this->_chdir($this->root_path))
		{
			return 'ERR_CHANGING_DIRECTORY';
		}

		return true;
	}

	/**
	* Create Directory (MKDIR)
	* @access private
	*/
	function _mkdir($dir)
	{
		return @ftp_mkdir($this->connection, $dir);
	}

	/**
	* Remove directory (RMDIR)
	* @access private
	*/
	function _rmdir($dir)
	{
		return @ftp_rmdir($this->connection, $dir);
	}

	/**
	* Rename file
	* @access private
	*/
	function _rename($old_handle, $new_handle)
	{
		return @ftp_rename($this->connection, $old_handle, $new_handle);
	}

	/**
	* Change current working directory (CHDIR)
	* @access private
	*/
	function _chdir($dir = '')
	{
		if ($dir && $dir !== '/')
		{
			if (substr($dir, -1, 1) == '/')
			{
				$dir = substr($dir, 0, -1);
			}
		}

		return @ftp_chdir($this->connection, $dir);
	}

	/**
	* change file permissions (CHMOD)
	* @access private
	*/
	function _chmod($file, $perms)
	{
		if (function_exists('ftp_chmod'))
		{
			$err = @ftp_chmod($this->connection, $perms, $file);
		}
		else
		{
			// Unfortunatly CHMOD is not expecting an octal value...
			// We need to transform the integer (which was an octal) to an octal representation (to get the int) and then pass as is. ;)
			$chmod_cmd = 'CHMOD ' . base_convert($perms, 10, 8) . ' ' . $file;
			$err = $this->_site($chmod_cmd);
		}

		return $err;
	}

	/**
	* Upload file to location (PUT)
	* @access private
	*/
	function _put($from_file, $to_file)
	{
		// get the file extension
		$file_extension = strtolower(substr(strrchr($to_file, '.'), 1));

		// We only use the BINARY file mode to cicumvent rewrite actions from ftp server (mostly linefeeds being replaced)
		$mode = FTP_BINARY;

		$to_dir = dirname($to_file);
		$to_file = basename($to_file);
		$this->_chdir($to_dir);

		$result = @ftp_put($this->connection, $to_file, $from_file, $mode);
		$this->_chdir($this->root_path);

		return $result;
	}

	/**
	* Delete file (DELETE)
	* @access private
	*/
	function _delete($file)
	{
		return @ftp_delete($this->connection, $file);
	}

	/**
	* Close ftp session (CLOSE)
	* @access private
	*/
	function _close()
	{
		if (!$this->connection)
		{
			return false;
		}

		return @ftp_quit($this->connection);
	}

	/**
	* Return current working directory (CWD)
	* At the moment not used by parent class
	* @access private
	*/
	function _cwd()
	{
		return @ftp_pwd($this->connection);
	}

	/**
	* Return list of files in a given directory (LS)
	* @access private
	*/
	function _ls($dir = './')
	{
		$list = @ftp_nlist($this->connection, $dir);

		// See bug #46295 - Some FTP daemons don't like './'
		if ($dir === './')
		{
			// Let's try some alternatives
			$list = (empty($list)) ? @ftp_nlist($this->connection, '.') : $list;
			$list = (empty($list)) ? @ftp_nlist($this->connection, '') : $list;
		}

		// Return on error
		if ($list === false)
		{
			return false;
		}

		// Remove path if prepended
		foreach ($list as $key => $item)
		{
			// Use same separator for item and dir
			$item = str_replace('\\', '/', $item);
			$dir = str_replace('\\', '/', $dir);

			if (!empty($dir) && strpos($item, $dir) === 0)
			{
				$item = substr($item, strlen($dir));
			}

			$list[$key] = $item;
		}

		return $list;
	}

	/**
	* FTP SITE command (ftp-only function)
	* @access private
	*/
	function _site($command)
	{
		return @ftp_site($this->connection, $command);
	}
}

/**
* FTP fsock transfer class
*/
class ftp_fsock extends transfer
{
	var $data_connection;

	/**
	* Standard parameters for FTP session
	*/
	function ftp_fsock($host, $username, $password, $root_path, $port = 21, $timeout = 10)
	{
		$this->host			= $host;
		$this->port			= $port;
		$this->username		= $username;
		$this->password		= $password;
		$this->timeout		= $timeout;

		// Make sure $this->root_path is layed out the same way as the $user->page['root_script_path'] value (/ at the end)
		$this->root_path	= str_replace('\\', '/', $this->root_path);

		if (!empty($root_path))
		{
			$this->root_path = (($root_path[0] != '/' ) ? '/' : '') . $root_path . ((substr($root_path, -1, 1) == '/') ? '' : '/');
		}

		// Init some needed values
		$this->transfer();

		return;
	}

	/**
	* Requests data
	*/
	static public function data()
	{
		global $user;

		return array(
			'host'		=> 'localhost',
			'username'	=> 'anonymous',
			'password'	=> '',
			'root_path'	=> $user->page['root_script_path'],
			'port'		=> 21,
			'timeout'	=> 10
		);
	}

	/**
	* Init FTP Session
	* @access private
	*/
	function _init()
	{
		$errno = 0;
		$errstr = '';

		// connect to the server
		$this->connection = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

		if (!$this->connection || !$this->_check_command())
		{
			return 'ERR_CONNECTING_SERVER';
		}

		@stream_set_timeout($this->connection, $this->timeout);

		// login
		if (!$this->_send_command('USER', $this->username))
		{
			return 'ERR_UNABLE_TO_LOGIN';
		}

		if (!$this->_send_command('PASS', $this->password))
		{
			return 'ERR_UNABLE_TO_LOGIN';
		}

		// change to the root directory
		if (!$this->_chdir($this->root_path))
		{
			return 'ERR_CHANGING_DIRECTORY';
		}

		return true;
	}

	/**
	* Create Directory (MKDIR)
	* @access private
	*/
	function _mkdir($dir)
	{
		return $this->_send_command('MKD', $dir);
	}

	/**
	* Remove directory (RMDIR)
	* @access private
	*/
	function _rmdir($dir)
	{
		return $this->_send_command('RMD', $dir);
	}

	/**
	* Rename File
	* @access private
	*/
	function _rename($old_handle, $new_handle)
	{
		$this->_send_command('RNFR', $old_handle);
		return $this->_send_command('RNTO', $new_handle);
	}

	/**
	* Change current working directory (CHDIR)
	* @access private
	*/
	function _chdir($dir = '')
	{
		if ($dir && $dir !== '/')
		{
			if (substr($dir, -1, 1) == '/')
			{
				$dir = substr($dir, 0, -1);
			}
		}

		return $this->_send_command('CWD', $dir);
	}

	/**
	* change file permissions (CHMOD)
	* @access private
	*/
	function _chmod($file, $perms)
	{
		// Unfortunatly CHMOD is not expecting an octal value...
		// We need to transform the integer (which was an octal) to an octal representation (to get the int) and then pass as is. ;)
		return $this->_send_command('SITE CHMOD', base_convert($perms, 10, 8) . ' ' . $file);
	}

	/**
	* Upload file to location (PUT)
	* @access private
	*/
	function _put($from_file, $to_file)
	{
		// We only use the BINARY file mode to cicumvent rewrite actions from ftp server (mostly linefeeds being replaced)
		// 'I' == BINARY
		// 'A' == ASCII
		if (!$this->_send_command('TYPE', 'I'))
		{
			return false;
		}

		// open the connection to send file over
		if (!$this->_open_data_connection())
		{
			return false;
		}

		$this->_send_command('STOR', $to_file, false);

		// send the file
		$fp = @fopen($from_file, 'rb');
		while (!@feof($fp))
		{
			@fwrite($this->data_connection, @fread($fp, 4096));
		}
		@fclose($fp);

		// close connection
		$this->_close_data_connection();

		return $this->_check_command();
	}

	/**
	* Delete file (DELETE)
	* @access private
	*/
	function _delete($file)
	{
		return $this->_send_command('DELE', $file);
	}

	/**
	* Close ftp session (CLOSE)
	* @access private
	*/
	function _close()
	{
		if (!$this->connection)
		{
			return false;
		}

		return $this->_send_command('QUIT');
	}

	/**
	* Return current working directory (CWD)
	* At the moment not used by parent class
	* @access private
	*/
	function _cwd()
	{
		$this->_send_command('PWD', '', false);
		return preg_replace('#^[0-9]{3} "(.+)" .+\r\n#', '\\1', $this->_check_command(true));
	}

	/**
	* Return list of files in a given directory (LS)
	* @access private
	*/
	function _ls($dir = './')
	{
		if (!$this->_open_data_connection())
		{
			return false;
		}

		$this->_send_command('NLST', $dir);

		$list = array();
		while (!@feof($this->data_connection))
		{
			$filename = preg_replace('#[\r\n]#', '', @fgets($this->data_connection, 512));

			if ($filename !== '')
			{
				$list[] = $filename;
			}
		}
		$this->_close_data_connection();

		// Clear buffer
		$this->_check_command();

		// See bug #46295 - Some FTP daemons don't like './'
		if ($dir === './' && empty($list))
		{
			// Let's try some alternatives
			$list = $this->_ls('.');

			if (empty($list))
			{
				$list = $this->_ls('');
			}

			return $list;
		}

		// Remove path if prepended
		foreach ($list as $key => $item)
		{
			// Use same separator for item and dir
			$item = str_replace('\\', '/', $item);
			$dir = str_replace('\\', '/', $dir);

			if (!empty($dir) && strpos($item, $dir) === 0)
			{
				$item = substr($item, strlen($dir));
			}

			$list[$key] = $item;
		}

		return $list;
	}

	/**
	* Send a command to server (FTP fsock only function)
	* @access private
	*/
	function _send_command($command, $args = '', $check = true)
	{
		if (!empty($args))
		{
			$command = "$command $args";
		}

		fwrite($this->connection, $command . "\r\n");

		if ($check === true && !$this->_check_command())
		{
			return false;
		}

		return true;
	}

	/**
	* Opens a connection to send data (FTP fosck only function)
	* @access private
	*/
	function _open_data_connection()
	{
		// Try to find out whether we have a IPv4 or IPv6 (control) connection
		if (function_exists('stream_socket_get_name'))
		{
			$socket_name = stream_socket_get_name($this->connection, true);
			$server_ip = substr($socket_name, 0, strrpos($socket_name, ':'));
		}

		if (!isset($server_ip) || preg_match(get_preg_expression('ipv4'), $server_ip))
		{
			// Passive mode
			$this->_send_command('PASV', '', false);

			if (!$ip_port = $this->_check_command(true))
			{
				return false;
			}

			// open the connection to start sending the file
			if (!preg_match('#[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+#', $ip_port, $temp))
			{
				// bad ip and port
				return false;
			}

			$temp = explode(',', $temp[0]);
			$server_ip = $temp[0] . '.' . $temp[1] . '.' . $temp[2] . '.' . $temp[3];
			$server_port = $temp[4] * 256 + $temp[5];
		}
		else
		{
			// Extended Passive Mode - RFC2428
			$this->_send_command('EPSV', '', false);

			if (!$epsv_response = $this->_check_command(true))
			{
				return false;
			}

			// Response looks like "229 Entering Extended Passive Mode (|||12345|)"
			// where 12345 is the tcp port for the data connection
			if (!preg_match('#\(\|\|\|([0-9]+)\|\)#', $epsv_response, $match))
			{
				return false;
			}
			$server_port = (int) $match[1];

			// fsockopen expects IPv6 address in square brackets
			$server_ip = "[$server_ip]";
		}

		$errno = 0;
		$errstr = '';

		if (!$this->data_connection = @fsockopen($server_ip, $server_port, $errno, $errstr, $this->timeout))
		{
			return false;
		}
		@stream_set_timeout($this->data_connection, $this->timeout);

		return true;
	}

	/**
	* Closes a connection used to send data
	* @access private
	*/
	function _close_data_connection()
	{
		return @fclose($this->data_connection);
	}

	/**
	* Check to make sure command was successful (FTP fsock only function)
	* @access private
	*/
	function _check_command($return = false)
	{
		$response = '';

		do
		{
			$result = @fgets($this->connection, 512);
			$response .= $result;
		}
		while (substr($result, 3, 1) !== ' ');

		if (!preg_match('#^[123]#', $response))
		{
			return false;
		}

		return ($return) ? $response : true;
	}
}
