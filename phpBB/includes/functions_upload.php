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
* Responsible for holding all file relevant information, as well as doing file-specific operations.
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
	var $image_info = array();

	var $destination_file = '';
	var $destination_path = '';

	var $file_moved = false;
	var $init_error = false;
	var $local = false;

	var $error = array();

	var $upload = '';

	/**
	 * The plupload object
	 * @var \phpbb\plupload\plupload
	 */
	protected $plupload;

	/**
	 * phpBB Mimetype guesser
	 * @var \phpbb\mimetype\guesser
	 */
	protected $mimetype_guesser;

	/**
	* File Class
	* @access private
	*/
	function filespec($upload_ary, $upload_namespace, \phpbb\mimetype\guesser $mimetype_guesser = null, \phpbb\plupload\plupload $plupload = null)
	{
		if (!isset($upload_ary))
		{
			$this->init_error = true;
			return;
		}

		$this->filename = $upload_ary['tmp_name'];
		$this->filesize = $upload_ary['size'];
		$name = (STRIP) ? stripslashes($upload_ary['name']) : $upload_ary['name'];
		$name = trim(utf8_basename($name));
		$this->realname = $this->uploadname = $name;
		$this->mimetype = $upload_ary['type'];

		// Opera adds the name to the mime type
		$this->mimetype	= (strpos($this->mimetype, '; name') !== false) ? str_replace(strstr($this->mimetype, '; name'), '', $this->mimetype) : $this->mimetype;

		if (!$this->mimetype)
		{
			$this->mimetype = 'application/octet-stream';
		}

		$this->extension = strtolower(self::get_extension($this->realname));

		// Try to get real filesize from temporary folder (not always working) ;)
		$this->filesize = (@filesize($this->filename)) ? @filesize($this->filename) : $this->filesize;

		$this->width = $this->height = 0;
		$this->file_moved = false;

		$this->local = (isset($upload_ary['local_mode'])) ? true : false;
		$this->upload = $upload_namespace;
		$this->plupload = $plupload;
		$this->mimetype_guesser = $mimetype_guesser;
	}

	/**
	* Cleans destination filename
	*
	* @param real|unique|unique_ext $mode real creates a realname, filtering some characters, lowering every character. Unique creates an unique filename
	* @param string $prefix Prefix applied to filename
	* @param string $user_id The user_id is only needed for when cleaning a user's avatar
	* @access public
	*/
	function clean_filename($mode = 'unique', $prefix = '', $user_id = '')
	{
		if ($this->init_error)
		{
			return;
		}

		switch ($mode)
		{
			case 'real':
				// Remove every extension from filename (to not let the mime bug being exposed)
				if (strpos($this->realname, '.') !== false)
				{
					$this->realname = substr($this->realname, 0, strpos($this->realname, '.'));
				}

				// Replace any chars which may cause us problems with _
				$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');

				$this->realname = rawurlencode(str_replace($bad_chars, '_', strtolower($this->realname)));
				$this->realname = preg_replace("/%(\w{2})/", '_', $this->realname);

				$this->realname = $prefix . $this->realname . '.' . $this->extension;
			break;

			case 'unique':
				$this->realname = $prefix . md5(unique_id());
			break;

			case 'avatar':
				$this->extension = strtolower($this->extension);
				$this->realname = $prefix . $user_id . '.' . $this->extension;

			break;

			case 'unique_ext':
			default:
				$this->realname = $prefix . md5(unique_id()) . '.' . $this->extension;
			break;
		}
	}

	/**
	* Get property from file object
	*/
	function get($property)
	{
		if ($this->init_error || !isset($this->$property))
		{
			return false;
		}

		return $this->$property;
	}

	/**
	* Check if file is an image (mimetype)
	*
	* @return true if it is an image, false if not
	*/
	function is_image()
	{
		return (strpos($this->mimetype, 'image/') === 0);
	}

	/**
	* Check if the file got correctly uploaded
	*
	* @return true if it is a valid upload, false if not
	*/
	function is_uploaded()
	{
		$is_plupload = $this->plupload && $this->plupload->is_active();

		if (!$this->local && !$is_plupload && !is_uploaded_file($this->filename))
		{
			return false;
		}

		if (($this->local || $is_plupload) && !file_exists($this->filename))
		{
			return false;
		}

		return true;
	}

	/**
	* Remove file
	*/
	function remove()
	{
		if ($this->file_moved)
		{
			@unlink($this->destination_file);
		}
	}

	/**
	* Get file extension
	*
	* @param string Filename that needs to be checked
	* @return string Extension of the supplied filename
	*/
	static public function get_extension($filename)
	{
		if (strpos($filename, '.') === false)
		{
			return '';
		}

		$filename = explode('.', $filename);
		return array_pop($filename);
	}

	/**
	* Get mimetype
	*
	* @param string $filename Filename that needs to be checked
	* @return string Mimetype of supplied filename
	*/
	function get_mimetype($filename)
	{
		if ($this->mimetype_guesser !== null)
		{
			$mimetype = $this->mimetype_guesser->guess($filename, $this->uploadname);

			if ($mimetype !== 'application/octet-stream')
			{
				$this->mimetype = $mimetype;
			}
		}

		return $this->mimetype;
	}

	/**
	* Get filesize
	*/
	function get_filesize($filename)
	{
		return @filesize($filename);
	}


	/**
	* Check the first 256 bytes for forbidden content
	*/
	function check_content($disallowed_content)
	{
		if (empty($disallowed_content))
		{
			return true;
		}

		$fp = @fopen($this->filename, 'rb');

		if ($fp !== false)
		{
			$ie_mime_relevant = fread($fp, 256);
			fclose($fp);
			foreach ($disallowed_content as $forbidden)
			{
				if (stripos($ie_mime_relevant, '<' . $forbidden) !== false)
				{
					return false;
				}
			}
		}
		return true;
	}

	/**
	* Move file to destination folder
	* The phpbb_root_path variable will be applied to the destination path
	*
	* @param string $destination Destination path, for example $config['avatar_path']
	* @param bool $overwrite If set to true, an already existing file will be overwritten
	* @param bool $skip_image_check If set to true, the check for the file to be a valid image is skipped
	* @param string $chmod Permission mask for chmodding the file after a successful move. The mode entered here reflects the mode defined by {@link phpbb_chmod()}
	*
	* @access public
	*/
	function move_file($destination, $overwrite = false, $skip_image_check = false, $chmod = false)
	{
		global $user, $phpbb_root_path;

		if (sizeof($this->error))
		{
			return false;
		}

		$chmod = ($chmod === false) ? CHMOD_READ | CHMOD_WRITE : $chmod;

		// We need to trust the admin in specifying valid upload directories and an attacker not being able to overwrite it...
		$this->destination_path = $phpbb_root_path . $destination;

		// Check if the destination path exist...
		if (!file_exists($this->destination_path))
		{
			@unlink($this->filename);
			return false;
		}

		$upload_mode = (@ini_get('open_basedir') || @ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'on') ? 'move' : 'copy';
		$upload_mode = ($this->local) ? 'local' : $upload_mode;
		$this->destination_file = $this->destination_path . '/' . utf8_basename($this->realname);

		// Check if the file already exist, else there is something wrong...
		if (file_exists($this->destination_file) && !$overwrite)
		{
			@unlink($this->filename);
			$this->error[] = $user->lang($this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR', $this->destination_file);
			$this->file_moved = false;
			return false;
		}
		else
		{
			if (file_exists($this->destination_file))
			{
				@unlink($this->destination_file);
			}

			switch ($upload_mode)
			{
				case 'copy':

					if (!@copy($this->filename, $this->destination_file))
					{
						if (!@move_uploaded_file($this->filename, $this->destination_file))
						{
							$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
						}
					}

				break;

				case 'move':

					if (!@move_uploaded_file($this->filename, $this->destination_file))
					{
						if (!@copy($this->filename, $this->destination_file))
						{
							$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
						}
					}

				break;

				case 'local':

					if (!@copy($this->filename, $this->destination_file))
					{
						$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
					}

				break;
			}

			// Remove temporary filename
			@unlink($this->filename);

			if (sizeof($this->error))
			{
				return false;
			}

			phpbb_chmod($this->destination_file, $chmod);
		}

		// Try to get real filesize from destination folder
		$this->filesize = (@filesize($this->destination_file)) ? @filesize($this->destination_file) : $this->filesize;

		// Get mimetype of supplied file
		$this->mimetype = $this->get_mimetype($this->destination_file);

		if ($this->is_image() && !$skip_image_check)
		{
			$this->width = $this->height = 0;

			if (($this->image_info = @getimagesize($this->destination_file)) !== false)
			{
				$this->width = $this->image_info[0];
				$this->height = $this->image_info[1];

				if (!empty($this->image_info['mime']))
				{
					$this->mimetype = $this->image_info['mime'];
				}

				// Check image type
				$types = fileupload::image_types();

				if (!isset($types[$this->image_info[2]]) || !in_array($this->extension, $types[$this->image_info[2]]))
				{
					if (!isset($types[$this->image_info[2]]))
					{
						$this->error[] = sprintf($user->lang['IMAGE_FILETYPE_INVALID'], $this->image_info[2], $this->mimetype);
					}
					else
					{
						$this->error[] = sprintf($user->lang['IMAGE_FILETYPE_MISMATCH'], $types[$this->image_info[2]][0], $this->extension);
					}
				}

				// Make sure the dimensions match a valid image
				if (empty($this->width) || empty($this->height))
				{
					$this->error[] = $user->lang['ATTACHED_IMAGE_NOT_IMAGE'];
				}
			}
			else
			{
				$this->error[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
			}
		}

		$this->file_moved = true;
		$this->additional_checks();
		unset($this->upload);

		return true;
	}

	/**
	* Performing additional checks
	*/
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
			$max_filesize = get_formatted_filesize($this->upload->max_filesize, false);

			$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']);

			return false;
		}

		if (!$this->upload->valid_dimensions($this))
		{
			$this->error[] = $user->lang($this->upload->error_prefix . 'WRONG_SIZE',
				$user->lang('PIXELS', (int) $this->upload->min_width),
				$user->lang('PIXELS', (int) $this->upload->min_height),
				$user->lang('PIXELS', (int) $this->upload->max_width),
				$user->lang('PIXELS', (int) $this->upload->max_height),
				$user->lang('PIXELS', (int) $this->width),
				$user->lang('PIXELS', (int) $this->height));

			return false;
		}

		return true;
	}
}

/**
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
* File upload class
* Init class (all parameters optional and able to be set/overwritten separately) - scope is global and valid for all uploads
*/
class fileupload
{
	var $allowed_extensions = array();
	var $disallowed_content = array('body', 'head', 'html', 'img', 'plaintext', 'a href', 'pre', 'script', 'table', 'title');
	var $max_filesize = 0;
	var $min_width = 0;
	var $min_height = 0;
	var $max_width = 0;
	var $max_height = 0;
	var $error_prefix = '';

	/** @var int Timeout for remote upload */
	var $upload_timeout = 6;

	/**
	* Init file upload class.
	*
	* @param string $error_prefix Used error messages will get prefixed by this string
	* @param array $allowed_extensions Array of allowed extensions, for example array('jpg', 'jpeg', 'gif', 'png')
	* @param int $max_filesize Maximum filesize
	* @param int $min_width Minimum image width (only checked for images)
	* @param int $min_height Minimum image height (only checked for images)
	* @param int $max_width Maximum image width (only checked for images)
	* @param int $max_height Maximum image height (only checked for images)
	* @param bool|array $disallowed_content If enabled, the first 256 bytes of the file must not
	*										contain any of its values. Defaults to false.
	*
	*/
	function fileupload($error_prefix = '', $allowed_extensions = false, $max_filesize = false, $min_width = false, $min_height = false, $max_width = false, $max_height = false, $disallowed_content = false)
	{
		$this->set_allowed_extensions($allowed_extensions);
		$this->set_max_filesize($max_filesize);
		$this->set_allowed_dimensions($min_width, $min_height, $max_width, $max_height);
		$this->set_error_prefix($error_prefix);
		$this->set_disallowed_content($disallowed_content);
	}

	/**
	* Reset vars
	*/
	function reset_vars()
	{
		$this->max_filesize = 0;
		$this->min_width = $this->min_height = $this->max_width = $this->max_height = 0;
		$this->error_prefix = '';
		$this->allowed_extensions = array();
		$this->disallowed_content = array();
	}

	/**
	* Set allowed extensions
	*/
	function set_allowed_extensions($allowed_extensions)
	{
		if ($allowed_extensions !== false && is_array($allowed_extensions))
		{
			$this->allowed_extensions = $allowed_extensions;
		}
	}

	/**
	* Set allowed dimensions
	*/
	function set_allowed_dimensions($min_width, $min_height, $max_width, $max_height)
	{
		$this->min_width = (int) $min_width;
		$this->min_height = (int) $min_height;
		$this->max_width = (int) $max_width;
		$this->max_height = (int) $max_height;
	}

	/**
	* Set maximum allowed filesize
	*/
	function set_max_filesize($max_filesize)
	{
		if ($max_filesize !== false && (int) $max_filesize)
		{
			$this->max_filesize = (int) $max_filesize;
		}
	}

	/**
	* Set disallowed strings
	*/
	function set_disallowed_content($disallowed_content)
	{
		if ($disallowed_content !== false && is_array($disallowed_content))
		{
			$this->disallowed_content = array_diff($disallowed_content, array(''));
		}
	}

	/**
	* Set error prefix
	*/
	function set_error_prefix($error_prefix)
	{
		$this->error_prefix = $error_prefix;
	}

	/**
	* Form upload method
	* Upload file from users harddisk
	*
	* @param string $form_name Form name assigned to the file input field (if it is an array, the key has to be specified)
	* @param \phpbb\mimetype\guesser $mimetype_guesser Mimetype guesser
	* @param \phpbb\plupload\plupload $plupload The plupload object
	*
	* @return object $file Object "filespec" is returned, all further operations can be done with this object
	* @access public
	*/
	function form_upload($form_name, \phpbb\mimetype\guesser $mimetype_guesser = null, \phpbb\plupload\plupload $plupload = null)
	{
		global $user, $request;

		$upload = $request->file($form_name);
		unset($upload['local_mode']);

		if ($plupload)
		{
			$result = $plupload->handle_upload($form_name);
			if (is_array($result))
			{
				$upload = array_merge($upload, $result);
			}
		}

		$file = new filespec($upload, $this, $mimetype_guesser, $plupload);

		if ($file->init_error)
		{
			$file->error[] = '';
			return $file;
		}

		// Error array filled?
		if (isset($upload['error']))
		{
			$error = $this->assign_internal_error($upload['error']);

			if ($error !== false)
			{
				$file->error[] = $error;
				return $file;
			}
		}

		// Check if empty file got uploaded (not catched by is_uploaded_file)
		if (isset($upload['size']) && $upload['size'] == 0)
		{
			$file->error[] = $user->lang[$this->error_prefix . 'EMPTY_FILEUPLOAD'];
			return $file;
		}

		// PHP Upload filesize exceeded
		if ($file->get('filename') == 'none')
		{
			$max_filesize = @ini_get('upload_max_filesize');
			$unit = 'MB';

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$max_filesize = (int) $max_filesize;

				$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
			}

			$file->error[] = (empty($max_filesize)) ? $user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf($user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], $max_filesize, $user->lang[$unit]);
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

	/**
	* Move file from another location to phpBB
	*/
	function local_upload($source_file, $filedata = false, \phpbb\mimetype\guesser $mimetype_guesser = null)
	{
		global $user, $request;

		$upload = array();

		$upload['local_mode'] = true;
		$upload['tmp_name'] = $source_file;

		if ($filedata === false)
		{
			$upload['name'] = utf8_basename($source_file);
			$upload['size'] = 0;
		}
		else
		{
			$upload['name'] = $filedata['realname'];
			$upload['size'] = $filedata['size'];
			$upload['type'] = $filedata['type'];
		}

		$file = new filespec($upload, $this, $mimetype_guesser);

		if ($file->init_error)
		{
			$file->error[] = '';
			return $file;
		}

		if (isset($upload['error']))
		{
			$error = $this->assign_internal_error($upload['error']);

			if ($error !== false)
			{
				$file->error[] = $error;
				return $file;
			}
		}

		// PHP Upload filesize exceeded
		if ($file->get('filename') == 'none')
		{
			$max_filesize = @ini_get('upload_max_filesize');
			$unit = 'MB';

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$max_filesize = (int) $max_filesize;

				$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
			}

			$file->error[] = (empty($max_filesize)) ? $user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf($user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], $max_filesize, $user->lang[$unit]);
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = $user->lang[$this->error_prefix . 'NOT_UPLOADED'];
			return $file;
		}

		$this->common_checks($file);
		$request->overwrite('local', $upload, \phpbb\request\request_interface::FILES);

		return $file;
	}

	/**
	* Remote upload method
	* Uploads file from given url
	*
	* @param string $upload_url URL pointing to file to upload, for example http://www.foobar.com/example.gif
	* @param \phpbb\mimetype\guesser $mimetype_guesser Mimetype guesser
	* @return object $file Object "filespec" is returned, all further operations can be done with this object
	* @access public
	*/
	function remote_upload($upload_url, \phpbb\mimetype\guesser $mimetype_guesser = null)
	{
		global $user, $phpbb_root_path;

		$upload_ary = array();
		$upload_ary['local_mode'] = true;

		if (!preg_match('#^(https?://).*?\.(' . implode('|', $this->allowed_extensions) . ')$#i', $upload_url, $match))
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
		$path = $url['path'];
		$port = (!empty($url['port'])) ? (int) $url['port'] : 80;

		$upload_ary['type'] = 'application/octet-stream';

		$url['path'] = explode('.', $url['path']);
		$ext = array_pop($url['path']);

		$url['path'] = implode('', $url['path']);
		$upload_ary['name'] = utf8_basename($url['path']) . (($ext) ? '.' . $ext : '');
		$filename = $url['path'];
		$filesize = 0;

		$remote_max_filesize = $this->max_filesize;
		if (!$remote_max_filesize)
		{
			$max_filesize = @ini_get('upload_max_filesize');

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$remote_max_filesize = (int) $max_filesize;

				switch ($unit)
				{
					case 'g':
						$remote_max_filesize *= 1024;
					// no break
					case 'm':
						$remote_max_filesize *= 1024;
					// no break
					case 'k':
						$remote_max_filesize *= 1024;
					// no break
				}
			}
		}

		$errno = 0;
		$errstr = '';

		if (!($fsock = @fsockopen($host, $port, $errno, $errstr)))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'NOT_UPLOADED']);
			return $file;
		}

		// Make sure $path not beginning with /
		if (strpos($path, '/') === 0)
		{
			$path = substr($path, 1);
		}

		fputs($fsock, 'GET /' . $path . " HTTP/1.1\r\n");
		fputs($fsock, "HOST: " . $host . "\r\n");
		fputs($fsock, "Connection: close\r\n\r\n");

		// Set a proper timeout for the socket
		socket_set_timeout($fsock, $this->upload_timeout);

		$get_info = false;
		$data = '';
		$length = false;
		$timer_stop = time() + $this->upload_timeout;

		while ((!$length || $filesize < $length) && !@feof($fsock))
		{
			if ($get_info)
			{
				if ($length)
				{
					// Don't attempt to read past end of file if server indicated length
					$block = @fread($fsock, min($length - $filesize, 1024));
				}
				else
				{
					$block = @fread($fsock, 1024);
				}

				$filesize += strlen($block);

				if ($remote_max_filesize && $filesize > $remote_max_filesize)
				{
					$max_filesize = get_formatted_filesize($remote_max_filesize, false);

					$file = new fileerror(sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']));
					return $file;
				}

				$data .= $block;
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
					if (stripos($line, 'content-type: ') !== false)
					{
						$upload_ary['type'] = rtrim(str_replace('content-type: ', '', strtolower($line)));
					}
					else if ($this->max_filesize && stripos($line, 'content-length: ') !== false)
					{
						$length = (int) str_replace('content-length: ', '', strtolower($line));

						if ($remote_max_filesize && $length && $length > $remote_max_filesize)
						{
							$max_filesize = get_formatted_filesize($remote_max_filesize, false);

							$file = new fileerror(sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']));
							return $file;
						}
					}
					else if (stripos($line, '404 not found') !== false)
					{
						$file = new fileerror($user->lang[$this->error_prefix . 'URL_NOT_FOUND']);
						return $file;
					}
				}
			}

			$stream_meta_data = stream_get_meta_data($fsock);

			// Cancel upload if we exceed timeout
			if (!empty($stream_meta_data['timed_out']) || time() >= $timer_stop)
			{
				$file = new fileerror($user->lang[$this->error_prefix . 'REMOTE_UPLOAD_TIMEOUT']);
				return $file;
			}
		}
		@fclose($fsock);

		if (empty($data))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'EMPTY_REMOTE_DATA']);
			return $file;
		}

		$tmp_path = (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') ? false : $phpbb_root_path . 'cache';
		$filename = tempnam($tmp_path, unique_id() . '-');

		if (!($fp = @fopen($filename, 'wb')))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'NOT_UPLOADED']);
			return $file;
		}

		$upload_ary['size'] = fwrite($fp, $data);
		fclose($fp);
		unset($data);

		$upload_ary['tmp_name'] = $filename;

		$file = new filespec($upload_ary, $this, $mimetype_guesser);
		$this->common_checks($file);

		return $file;
	}

	/**
	* Assign internal error
	* @access private
	*/
	function assign_internal_error($errorcode)
	{
		global $user;

		switch ($errorcode)
		{
			case 1:
				$max_filesize = @ini_get('upload_max_filesize');
				$unit = 'MB';

				if (!empty($max_filesize))
				{
					$unit = strtolower(substr($max_filesize, -1, 1));
					$max_filesize = (int) $max_filesize;

					$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
				}

				$error = (empty($max_filesize)) ? $user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf($user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], $max_filesize, $user->lang[$unit]);
			break;

			case 2:
				$max_filesize = get_formatted_filesize($this->max_filesize, false);

				$error = sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']);
			break;

			case 3:
				$error = $user->lang[$this->error_prefix . 'PARTIAL_UPLOAD'];
			break;

			case 4:
				$error = $user->lang[$this->error_prefix . 'NOT_UPLOADED'];
			break;

			case 6:
				$error = 'Temporary folder could not be found. Please check your PHP installation.';
			break;

			default:
				$error = false;
			break;
		}

		return $error;
	}

	/**
	* Perform common checks
	*/
	function common_checks(&$file)
	{
		global $user;

		// Filesize is too big or it's 0 if it was larger than the maxsize in the upload form
		if ($this->max_filesize && ($file->get('filesize') > $this->max_filesize || $file->get('filesize') == 0))
		{
			$max_filesize = get_formatted_filesize($this->max_filesize, false);

			$file->error[] = sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']);
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

		// MIME Sniffing
		if (!$this->valid_content($file))
		{
			$file->error[] = sprintf($user->lang[$this->error_prefix . 'DISALLOWED_CONTENT']);
		}
	}

	/**
	* Check for allowed extension
	*/
	function valid_extension(&$file)
	{
		return (in_array($file->get('extension'), $this->allowed_extensions)) ? true : false;
	}

	/**
	* Check for allowed dimension
	*/
	function valid_dimensions(&$file)
	{
		if (!$this->max_width && !$this->max_height && !$this->min_width && !$this->min_height)
		{
			return true;
		}

		if (($file->get('width') > $this->max_width && $this->max_width) ||
			($file->get('height') > $this->max_height && $this->max_height) ||
			($file->get('width') < $this->min_width && $this->min_width) ||
			($file->get('height') < $this->min_height && $this->min_height))
		{
			return false;
		}

		return true;
	}

	/**
	* Check if form upload is valid
	*/
	function is_valid($form_name)
	{
		global $request;
		$upload = $request->file($form_name);

		return (!empty($upload) && $upload['name'] !== 'none');
	}


	/**
	* Check for bad content (IE mime-sniffing)
	*/
	function valid_content(&$file)
	{
		return ($file->check_content($this->disallowed_content));
	}

	/**
	* Get image type/extension mapping
	*
	* @return array Array containing the image types and their extensions
	*/
	static public function image_types()
	{
		$result = array(
			IMAGETYPE_GIF		=> array('gif'),
			IMAGETYPE_JPEG		=> array('jpg', 'jpeg'),
			IMAGETYPE_PNG		=> array('png'),
			IMAGETYPE_SWF		=> array('swf'),
			IMAGETYPE_PSD		=> array('psd'),
			IMAGETYPE_BMP		=> array('bmp'),
			IMAGETYPE_TIFF_II	=> array('tif', 'tiff'),
			IMAGETYPE_TIFF_MM	=> array('tif', 'tiff'),
			IMAGETYPE_JPC		=> array('jpg', 'jpeg'),
			IMAGETYPE_JP2		=> array('jpg', 'jpeg'),
			IMAGETYPE_JPX		=> array('jpg', 'jpeg'),
			IMAGETYPE_JB2		=> array('jpg', 'jpeg'),
			IMAGETYPE_IFF		=> array('iff'),
			IMAGETYPE_WBMP		=> array('wbmp'),
			IMAGETYPE_XBM		=> array('xbm'),
		);

		if (defined('IMAGETYPE_SWC'))
		{
			$result[IMAGETYPE_SWC] = array('swc');
		}

		return $result;
	}
}
