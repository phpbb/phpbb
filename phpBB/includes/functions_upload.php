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
* Responsible for holding all file relevant informations, as well as doing file-specific operations.
* The {@link fileupload fileupload class} can be used to upload several files, each of them being this object to operate further on.
*/
class filespec
{
	var $filename = '';
	var $realname = '';
	var $uploadname = '';
	var $mimetype = '';
	var $extension = '';
	var $filesize = 0;
	var $width = 0;
	var $height = 0;

	var $destination_file = '';
	var $destination_path = '';

	var $file_moved = false;
	var $init_error = false;
	var $local = false;

	var $error = array();

	var $upload = '';

	/**
	* File Class
	*
	* @access private
	*
	*/
	function filespec($upload_ary, $upload_namespace)
	{
		if (!isset($upload_ary))
		{
			$this->init_error = true;
			return;
		}

		$this->filename = $upload_ary['tmp_name'];
		$this->filesize = $upload_ary['size'];
		$this->realname = $this->uploadname = trim(basename($upload_ary['name']));
		$this->mimetype = $upload_ary['type'];

		// Opera adds the name to the mime type
		$this->mimetype	= (strpos($this->mimetype, '; name') !== false) ? str_replace(strstr($this->mimetype, '; name'), '', $this->mimetype) : $this->mimetype;
		$this->extension = array_pop(explode('.', strtolower($this->realname)));

		// Try to get real filesize from temporary folder (not always working) ;)
		$this->filesize = (@filesize($this->filename)) ? @filesize($this->filename) : $this->filesize;

		$this->width = $this->height = 0;
		$this->file_moved = false;

		$this->local = (isset($upload_ary['local_mode'])) ? true : false;
		$this->upload = $upload_namespace;
	}

	/**
	* Cleans destination filename
	* 
	* @access public
	* @param real|unique $mode real creates a realname, filtering some characters, lowering every character. Unique creates an unique filename
	* @param string $prefix Prefix applied to filename
	*/
	function clean_filename($mode = 'unique', $prefix = '')
	{
		if ($this->init_error)
		{
			return;
		}
		
		switch ($mode)
		{
			case 'real':
				// Replace any chars which may cause us problems with _
				$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');
				$this->realname = $prefix . str_replace($bad_chars, '_', strtolower($this->realname)) . '_.' . $this->extension;
				break;

			case 'unique':
			default:
				$this->realname = $prefix . uniqid(rand()) . '.' . $this->extension;
		}
	}

	function get($property)
	{
		if ($this->init_error)
		{
			return;
		}
		
		if (!isset($this->$property))
		{
			return false;
		}
		
		return $this->$property;
	}

	function is_image()
	{
		return (strpos($this->mimetype, 'image/') !== false) ? true : false;
	}

	function is_uploaded()
	{
		return (file_exists($this->filename) && is_uploaded_file($this->filename)) ? true : false;
	}

	function remove()
	{
		if ($this->file_moved)
		{
			@unlink($this->destination_file);
		}
	}

	/**
	* Move file to destination folder
	* 
	* The phpbb_root_path variable will be applied to the destination path
	*
	* @access public
	* @param string $destination_path Destination path, for example $config['avatar_path']
	* @param octal $chmod Permission mask for chmodding the file after a successful move
	*/
	function move_file($destination, $chmod = 0666)
	{
		global $user, $phpbb_root_path;

		if (sizeof($this->error))
		{
			return false;
		}

		// Adjust destination path (no trailing slash)
		if ($destination{(sizeof($destination)-1)} == '/' || $destination{(sizeof($destination)-1)} == '\\')
		{
			$destination = substr($destination, 0, sizeof($destination)-2);
		}
		
		$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
		if ($destination && ($destination{0} == '/' || $destination{0} == "\\"))
		{
			$destination = '';
		}

		$this->destination_path = $phpbb_root_path . $destination;

		$upload_mode = (@ini_get('open_basedir') || @ini_get('safe_mode')) ? 'move' : 'copy';
		$upload_mode = ($this->local) ? 'local' : $upload_mode;
		$this->destination_file = $this->destination_path . '/' . basename($this->realname);

		switch ($upload_mode)
		{
			case 'copy':
				if (!@copy($this->filename, $this->destination_file)) 
				{
					if (!@move_uploaded_file($this->filename, $this->destination_file)) 
					{
						$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
						return false;
					}
				}
				else
				{
					@unlink($this->filename);
				}
				break;

			case 'move':
				if (!@move_uploaded_file($this->filename, $this->destination_file)) 
				{ 
					if (!@copy($this->filename, $this->destination_file)) 
					{
						$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
						return false;
					}
					else
					{
						@unlink($this->filename);
					}
				} 
				break;

			case 'local':
				if (!@copy($this->filename, $this->destination_file)) 
				{
					$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
					return false;
				}
				@unlink($this->filename);
				break;
		}

		@chmod($this->destination_file, $chmod);
		
		// Try to get real filesize from destination folder
		$this->filesize = (@filesize($this->destination_file)) ? @filesize($this->destination_file) : $this->filesize;

		if ($this->is_image())
		{
			list($this->width, $this->height) = @getimagesize($this->destination_file);
		}

		$this->file_moved = true;
		$this->additional_checks();
		unset($this->upload);
	}

	function additional_checks()
	{
		global $user;

		if (!$this->file_moved)
		{
			return false;
		}
		
		// Filesize is too big or it's 0 if it was larger than the maxsize in the upload form
		if ($this->upload->max_filesize && ($this->get('filesize') > $this->upload->max_filesize || $this->filesize == 0))
		{
			$size_lang = ($this->upload->max_filesize >= 1048576) ? $user->lang['MB'] : (($this->upload->max_filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );
			$max_filesize = ($this->upload->max_filesize >= 1048576) ? round($this->upload->max_filesize / 1048576 * 100) / 100 : (($this->upload->max_filesize >= 1024) ? round($this->upload->max_filesize / 1024 * 100) / 100 : $this->upload->max_filesize);
			
			$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'WRONG_FILESIZE'], $max_filesize, $size_lang);
			return;
		}

		if (!$this->upload->valid_dimensions($this))
		{
			$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'WRONG_SIZE'], $this->min_width, $this->min_height, $this->max_width, $this->max_height);
		}
	}
}

/**
* @package phpBB3
* Class for assigning error messages before a real filespec class can be assigned
*/
class fileerror extends filespec
{
	function fileerror($error_msg)
	{
		$this->error[] = $error_msg;
	}
}

/**
* @package phpBB3
* File upload class
*
* Init class (all parameters optional and able to be set/overwritten seperatly) - scope is global and valid for all uploads
*/
class fileupload
{
	var $allowed_extensions = array();
	var $max_filesize = 0;
	var $min_width = 0;
	var $min_height = 0;
	var $max_width = 0;
	var $max_height = 0;
	var $error_prefix = '';

	/**
	*
	* @param string $error_prefix Used error messages will get prefixed by this string
	* @param array $allowed_extensions Array of allowed extensions, for example array('jpg', 'jpeg', 'gif', 'png')
	* @param int $max_filesize Maximum filesize
	* @param int $min_width Minimum image width (only checked for images)
	* @param int $min_height Minimum image height (only checked for images)
	* @param int $max_width Maximum image width (only checked for images)
	* @param int $max_height Maximum image height (only checked for images)
	*
	*/
	function fileupload($error_prefix = '', $allowed_extensions = false, $max_filesize = false, $min_width = false, $min_height = false, $max_width = false, $max_height = false)
	{
		$this->set_allowed_extensions($allowed_extensions);
		$this->set_max_filesize($max_filesize);
		$this->set_allowed_dimensions($min_width, $min_height, $max_width, $max_height);
		$this->set_error_prefix($error_prefix);
	}

	// Reset vars
	function reset_vars()
	{
		$this->max_filesize = 0;
		$this->min_width = $this->min_height = $this->max_width = $this->max_height = 0;
		$this->error_prefix = '';
		$this->allowed_extensions = array();
	}

	// Set allowed extensions
	function set_allowed_extensions($allowed_extensions)
	{
		if ($allowed_extensions !== false && is_array($allowed_extensions))
		{
			$this->allowed_extensions = $allowed_extensions;
		}
	}

	// Set allowed dimensions
	function set_allowed_dimensions($min_width, $min_height, $max_width, $max_height)
	{
		$this->min_width = (int) $min_width;
		$this->min_height = (int) $min_height;
		$this->max_width = (int) $max_width;
		$this->max_height = (int) $max_height;
	}

	// Set maximum allowed filesize
	function set_max_filesize($max_filesize)
	{
		if ($max_filesize !== false && (int) $max_filesize)
		{
			$this->max_filesize = (int) $max_filesize;
		}
	}

	// Set error prefix
	function set_error_prefix($error_prefix)
	{
		$this->error_prefix = $error_prefix;
	}

	/**
	* Form upload method
	*
	* Upload file from users harddisk
	*
	* @access public
	* @param string $form_name Form name assigned to the file input field (if it is an array, the key has to be specified)
	* @return object $file Object "filespec" is returned, all further operations can be done with this object
	*/
	function form_upload($form_name)
	{
		global $user;

		unset($_FILES[$form_name]['local_mode']);
		$file = new filespec($_FILES[$form_name], $this);

		if ($file->init_error)
		{
			$file->error[] = '';
			return $file;
		}
			
		if (isset($_FILES[$form_name]['error']))
		{
			$error = $this->assign_internal_error($_FILES[$form_name]['error']);

			if ($error !== false)
			{
				$file->error[] = $error;
				return $file;
			}
		}

		// PHP Upload filesize exceeded
		if ($file->get('filename') == 'none')
		{
			$file->error[] = (@ini_get('upload_max_filesize') == '') ? $user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf($user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], @ini_get('upload_max_filesize'));
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = $user->lang[$this->error_prefix . 'NOT_UPLOADED'];
			return $file;
		}

		$this->common_checks($file);

		return $file;
	}

	// Move file from another location to phpBB
	function local_upload($source_file)
	{
	}

	/**
	* Remote upload method
	*
	* Uploads file from given url
	*
	* @access public
	* @param string $upload_url URL pointing to file to upload, for example http://www.foobar.com/example.gif
	* @return object $file Object "filespec" is returned, all further operations can be done with this object
	*/
	function remote_upload($upload_url)
	{
		global $user, $phpbb_root_path;
		
		$upload_ary = array();
		$upload_ary['local_mode'] = true;

		if (!preg_match('#^(http://).*?\.(' . implode('|', $this->allowed_extensions) . ')$#i', $upload_url, $match))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'URL_INVALID']);
			return $file;
		}
 
		if (empty($match[2]))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'URL_INVALID']);
			return $file;
		}

		$url = parse_url($upload_url);

		$host = $url['host'];
		$path = dirname($url['path']);
		$port = (!empty($url['port'])) ? $url['port'] : 80;
			
		$upload_ary['type'] = 'application/octet-stream';
		$upload_ary['name'] = basename($url['path']) . '.' . array_pop(explode('.', $url['path']));
		$filename = $url['path'];
		$filesize = 0;

		if (!($fsock = @fsockopen($host, $port, $errno, $errstr)))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'NOT_UPLOADED']);
			return $file;
		}

		fputs($fsock, 'GET /' . $filename . " HTTP/1.1\r\n");
		fputs($fsock, "HOST: " . $host . "\r\n");
		fputs($fsock, "Connection: close\r\n\r\n");

		$get_info = false;
		$data = '';
		while (!@feof($fsock))
		{
			if ($get_info)
			{
				$data .= @fread($fsock, 1024);
			}
			else
			{
				$line = @fgets($fsock, 1024);

				if ($line == "\r\n")
				{
					$get_info = true;
				}
				else
				{
					if (strpos($line, 'Content-Type: ') !== false)
					{
						$upload_ary['type'] = rtrim(str_ireplace('Content-Type: ', '', $line));
					}
				}
			}
		}
		@fclose($fsock);

		if (empty($data))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'EMPTY_REMOTE_DATA']);
			return $file;
		}
		unset($url_ary);

		$tmp_path = (!@ini_get('safe_mode')) ? false : $phpbb_root_path . 'cache';
		$filename = tempnam($tmp_path, uniqid(rand()) . '-');

		if (!($fp = @fopen($filename, 'wb')))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'NOT_UPLOADED']);
			return $file;
		}
		$upload_ary['size'] = fwrite($fp, $data);
		fclose($fp);
		unset($data);

		$upload_ary['tmp_name'] = $filename;

		$file = new filespec($upload_ary, $this);
		$this->common_checks($file);

		return $file;
	}

	// Private::assign_internal_error
	function assign_internal_error($errorcode)
	{
		global $user;

		switch ($errorcode)
		{
			case 1:
				$error = (@ini_get('upload_max_filesize') == '') ? $user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf($user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], @ini_get('upload_max_filesize'));
				break;
			case 2:
				$error = sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $this->max_filesize);
				break;			
			case 3:
				$error = 'The uploaded file was only partially uploaded';
				break;
			case 4:
				$error = $user->lang[$this->error_prefix . 'NOT_UPLOADED'];
				break;
			case 6:
				$error = 'Temporary folder could not be found. Please check your PHP installation.';
				break;
			default:
				$error = false;
		}

		return $error;
	}
	
	// Private::common_checks
	function common_checks(&$file)
	{
		global $user;

		// Filesize is too big or it's 0 if it was larger than the maxsize in the upload form
		if ($this->max_filesize && ($file->get('filesize') > $this->max_filesize || $file->get('filesize') == 0))
		{
			$file->error[] = sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $this->max_filesize);
		}

		// check Filename
		if (preg_match("#[\\/:*?\"<>|]#i", $file->get('realname')))
		{ 
			$file->error[] = sprintf($user->lang[$this->error_prefix . 'INVALID_FILENAME'], $file->get('realname'));
		}

		// Invalid Extension
		if (!$this->valid_extension($file))
		{
			$file->error[] = sprintf($user->lang[$this->error_prefix . 'DISALLOWED_EXTENSION'], $file->get('extension'));
		}
	}

	function valid_extension(&$file)
	{
		return (in_array($file->get('extension'), $this->allowed_extensions)) ? true : false;
	}

	function valid_dimensions(&$file)
	{
		if (($file->get('width') > $this->max_width && $this->max_width) || 
			($file->get('height') > $this->max_height && $this->max_height) || 
			($file->get('width') < $this->min_width && $this->min_width) ||
			($file->get('height') < $this->min_height && $this->min_height) || 
			!$file->get('width') || !$file->get('height'))
		{
			return false;
		}

		return true;
	}

	function is_valid($form_name)
	{
		return (isset($_FILES[$form_name]) && $_FILES[$form_name]['name'] != 'none') ? true : false;
	}
}

?>