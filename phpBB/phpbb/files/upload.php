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

namespace phpbb\files;

/**
 * File upload class
 * Init class (all parameters optional and able to be set/overwritten separately) - scope is global and valid for all uploads
 */
class upload
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
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * Init file upload class.
	 *
	 * @param \phpbb\filesystem\filesystem_interface $filesystem
	 *
	 */
	public function __construct(\phpbb\filesystem\filesystem_interface $filesystem)
	{
//		$this->set_allowed_extensions($allowed_extensions);
//		$this->set_max_filesize($max_filesize);
//		$this->set_allowed_dimensions($min_width, $min_height, $max_width, $max_height);
//		$this->set_error_prefix($error_prefix);
//		$this->set_disallowed_content($disallowed_content);
		$this->filesystem = $filesystem;
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

		return $this;
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

		return $this;
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

		return $this;
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

		return $this;
	}

	/**
	 * Set error prefix
	 */
	function set_error_prefix($error_prefix)
	{
		$this->error_prefix = $error_prefix;

		return $this;
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
		global $user, $request, $phpbb_container;

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

		/** @var \phpbb\files\filespec $file */
		$file = $phpbb_container->get('files.filespec')
			->set_upload_ary($upload)
			->set_upload_namespace($this);

		if ($file->init_error())
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
		global $user, $request, $phpbb_container;

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

		/** @var \phpbb\files\filespec $file */
		$file = $phpbb_container->get('files.filespec')
			->set_upload_ary($upload)
			->set_upload_namespace($this);

		if ($file->init_error())
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
		global $user, $phpbb_root_path, $phpbb_container;

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

		/** @var \phpbb\files\filespec $file */
		$file = $phpbb_container->get('files.filespec')
			->set_upload_ary($upload_ary)
			->set_upload_namespace($this);
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
