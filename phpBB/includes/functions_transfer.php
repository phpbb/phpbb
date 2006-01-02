<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package phpBB3
* Transfer class, wrapper for ftp/sftp/ssh
* @todo check for available extensions
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

		$this->file_perms	= 644;
		$this->dir_perms	= 777;

		// We use the store directory as temporary path to circumvent open basedir restrictions
		$this->tmp_path = $phpbb_root_path . 'store/';
	}
	
	/**
	* Write file to location
	*/
	function write_file($destination_file = '', $contents = '')
	{
		global $phpbb_root_path;

		$destination_file = $this->root_path . '/' . str_replace($phpbb_root_path, '', $destination_file);

		// need to create a temp file and then move that temp file.
		// ftp functions can only move files around and can't create.
		// This means that the users will need to have access to write
		// temporary files or have write access on a folder within phpBB
		// like the cache folder.  If the user can't do either, then
		// he/she needs to use the fsock ftp method
		$temp_name = tempnam($this->tmp_path, 'transfer_');
		@unlink($temp_name);

		$fp = @fopen($temp_name, 'w');

		if (!$fp)
		{
			trigger_error('Unable to create temporary file ' . $temp_name);
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

			if ($dir[$i] == '..' || $dir[$i] == '.')
			{
				continue;
			}
			$cur_dir = $dir[$i] . '/';

			if (!file_exists($phpbb_root_path . $dirs . $cur_dir))
			{
				// make the directory
				$result = $this->_mkdir($dir[$i]);
				$this->_chmod($dir[$i], $this->dir_perms);
			}

			$this->_chdir($this->root_path . '/' . $dirs . $dir[$i]);
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
		$to_loc = $this->root_path . '/' . str_replace($phpbb_root_path, '', $to_loc);

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
		
		$file = $this->root_path . '/' . str_replace($phpbb_root_path, '', $file);

		return $this->_delete($file);
	}
	
	/**
	* Remove directory
	* @todo remove child directories?
	*/
	function remove_dir($dir)
	{
		global $phpbb_root_path;
		
		$dir = $this->root_path . '/' . str_replace($phpbb_root_path, '', $dir);
		
		return $this->_rmdir($dir);
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
	function methods()
	{
		$methods = array();

		if (@extension_loaded('ftp'))
		{
			$methods[] = 'ftp';
		}

		return $methods;
	}
}

/**
* @package phpBB3
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
		$this->root_path	= (($root_path{0} != '/' ) ? '/' : '') . ((substr($root_path, -1, 1) == '/') ? substr($root_path, 0, -1) : $root_path);

		return;
	}

	/**
	* Init FTP Session
	*/
	function _init()
	{
		// connect to the server
		$this->connection = @ftp_connect($this->host, $this->port, $this->timeout);

		if (!$this->connection)
		{
			return false;
		}

		// attempt to turn pasv mode on
		@ftp_pasv($this->connection, true);

		// login to the server
		if (!@ftp_login($this->connection, $this->username, $this->password))
		{
			return false;
		}

		// change to the root directory
		if (!$this->_chdir($this->root_path))
		{
			return 'Unable to change directory';
		}

		return true;
	}

	/**
	* Create Directory (MKDIR)
	*/
	function _mkdir($dir)
	{
		return @ftp_mkdir($this->connection, $dir);
	}

	/**
	* Remove directory (RMDIR)
	*/
	function _rmdir($dir)
	{
		return @ftp_rmdir($this->connection, $dir);
	}

	/**
	* Change current working directory (CHDIR)
	*/
	function _chdir($dir = '')
	{
		if (substr($dir, -1, 1) == '/')
		{
			$dir = substr($dir, 0, -1);
		}

		return @ftp_chdir($this->connection, $dir);
	}

	/**
	* change file permissions (CHMOD)
	*/
	function _chmod($file, $perms)
	{
		$chmod_cmd = 'CHMOD 0' . $perms . ' ' . $file;
		$err = $this->_site($chmod_cmd);

		return $err;
	}

	/**
	* Upload file to location (PUT)
	*/
	function _put($from_file, $to_file)
	{
		// get the file extension
		$file_extension = strtolower(substr(strrchr($to_file, '.'), 1));

		// extension list for files that need to be transfered as binary.
		// Taken from the old EasyMOD which was taken from the attachment MOD
//		$extensions = array('ace', 'ai', 'aif', 'aifc', 'aiff', 'ar', 'asf', 'asx', 'au', 'avi', 'doc', 'dot', 'gif', 'gtar', 'gz', 'ivf', 'jpeg', 'jpg', 'm3u', 'mid', 'midi', 'mlv', 'mp2', 'mp3', 'mp2v', 'mpa', 'mpe', 'mpeg', 'mpg', 'mpv2', 'pdf', 'png', 'ppt', 'ps', 'rar', 'rm', 'rmi', 'snd', 'swf', 'tga', 'tif', 'wav', 'wax', 'wm', 'wma', 'wmv', 'wmx', 'wvx', 'xls', 'zip') ;
//		$is_binary = in_array($file_extension, $extensions);
//		$mode = ($is_binary) ? FTP_BINARY : FTP_ASCII;

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
	*/
	function _delete($file)
	{
		return @ftp_delete($this->connection, $file);
	}
	
	/**
	* Close ftp session (CLOSE)
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
	*/
	function _cwd()
	{
		return @ftp_pwd($this->connection);
	}

	/**
	* Return list of files in a given directory (LS)
	* At the moment not used by parent class
	*/
	function _ls($dir = './')
	{
		return @ftp_nlist($this->connection, $dir);
	}

	/**
	* FTP SITE command (ftp-only function)
	*/
	function _site($command)
	{
		return @ftp_site($this->connection, $command);
	}
}

?>