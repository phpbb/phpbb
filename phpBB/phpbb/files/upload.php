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

use \phpbb\filesystem\filesystem_interface;
use \phpbb\language\language;
use \phpbb\plupload\plupload;
use \phpbb\request\request_interface;

/**
 * File upload class
 * Init class (all parameters optional and able to be set/overwritten separately) - scope is global and valid for all uploads
 */
class upload
{
	/** @var array Allowed file extensions */
	var $allowed_extensions = array();

	/** @var array Disallowed content */
	var $disallowed_content = array('body', 'head', 'html', 'img', 'plaintext', 'a href', 'pre', 'script', 'table', 'title');

	/** @var int Maximum filesize */
	var $max_filesize = 0;

	/** @var int Minimum width of images */
	var $min_width = 0;

	/** @var int Minimum height of images */
	var $min_height = 0;

	/** @var int Maximum width of images */
	var $max_width = 0;

	/** @var int Maximum height of images */
	var $max_height = 0;

	/** @var string Prefix for language variables of errors */
	var $error_prefix = '';

	/** @var int Timeout for remote upload */
	var $upload_timeout = 6;

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \phpbb\files\factory Files factory */
	protected $factory;

	/** @var \phpbb\language\language Language class */
	protected $language;

	/** @var \phpbb\request\request_interface Request class */
	protected $request;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/**
	 * Init file upload class.
	 *
	 * @param \phpbb\filesystem\filesystem_interface $filesystem
	 * @param \phpbb\files\factory $factory Files factory
	 * @param \phpbb\language\language $language Language class
	 * @param \phpbb\request\request_interface $request Request class
	 * @param string $phpbb_root_path phpBB root path
	 */
	public function __construct(filesystem_interface $filesystem, factory $factory, language $language, request_interface $request, $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->factory = $factory;
		$this->language = $language;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
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
	 *
	 * @param array $allowed_extensions Allowed file extensions
	 *
	 * @return \phpbb\files\upload This instance of upload
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
	 *
	 * @param int $min_width Minimum image width
	 * @param int $min_height Minimum image height
	 * @param int $max_width Maximum image width
	 * @param int $max_height Maximum image height
	 *
	 * @return \phpbb\files\upload This instance of upload
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
	 * Set maximum allowed file size
	 *
	 * @param int $max_filesize Maximum file size
	 *
	 * @return \phpbb\files\upload This instance of upload
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
	 *
	 * @param array $disallowed_content Disallowed content
	 *
	 * @return \phpbb\files\upload This instance of upload
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
	 *
	 * @param string $error_prefix Prefix for language variables of errors
	 *
	 * @return \phpbb\files\upload This instance of upload
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
	 * @param \phpbb\plupload\plupload $plupload The plupload object
	 *
	 * @return filespec $file Object "filespec" is returned, all further operations can be done with this object
	 * @access public
	 */
	function form_upload($form_name, plupload $plupload = null)
	{
		$upload = $this->request->file($form_name);
		unset($upload['local_mode']);

		if ($plupload)
		{
			$result = $plupload->handle_upload($form_name);
			if (is_array($result))
			{
				$upload = array_merge($upload, $result);
			}
		}

		/** @var filespec $file */
		$file = $this->factory->get('filespec')
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
			$file->error[] = $this->language->lang($this->error_prefix . 'EMPTY_FILEUPLOAD');
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

			$file->error[] = (empty($max_filesize)) ? $this->language->lang($this->error_prefix . 'PHP_SIZE_NA') : $this->language->lang($this->error_prefix . 'PHP_SIZE_OVERRUN', $max_filesize, $this->language->lang($unit));
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = $this->language->lang($this->error_prefix . 'NOT_UPLOADED');
			return $file;
		}

		$this->common_checks($file);

		return $file;
	}

	/**
	 * Move file from another location to phpBB
	 *
	 * @param string $source_file Filename of source file
	 * @param array|bool $filedata Array with filedata or false
	 *
	 * @return filespec Object "filespec" is returned, all further operations can be done with this object
	 */
	function local_upload($source_file, $filedata = false)
	{
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

		/** @var filespec $file */
		$file = $this->factory->get('filespec')
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

			$file->error[] = (empty($max_filesize)) ?$this->language->lang($this->error_prefix . 'PHP_SIZE_NA') : $this->language->lang($this->error_prefix . 'PHP_SIZE_OVERRUN', $max_filesize, $this->language->lang($unit));
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = $this->language->lang($this->error_prefix . 'NOT_UPLOADED');
			return $file;
		}

		$this->common_checks($file);
		$this->request->overwrite('local', $upload, request_interface::FILES);

		return $file;
	}

	/**
	 * Remote upload method
	 * Uploads file from given url
	 *
	 * @param string $upload_url URL pointing to file to upload, for example http://www.foobar.com/example.gif
	 * @return filespec $file Object "filespec" is returned, all further operations can be done with this object
	 * @access public
	 */
	function remote_upload($upload_url)
	{
		$upload_ary = array();
		$upload_ary['local_mode'] = true;

		if (!preg_match('#^(https?://).*?\.(' . implode('|', $this->allowed_extensions) . ')$#i', $upload_url, $match))
		{
			return $this->factory->get('filespec')->set_error($this->language->lang($this->error_prefix . 'URL_INVALID'));
		}

		if (empty($match[2]))
		{
			return $this->factory->get('filespec')->set_error($this->language->lang($this->error_prefix . 'URL_INVALID'));
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
			return $this->factory->get('filespec')->set_error($this->language->lang($this->error_prefix . 'NOT_UPLOADED'));
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

					return $this->factory->get('filespec')->set_error($this->language->lang($this->error_prefix . 'WRONG_FILESIZE', $max_filesize['value'], $max_filesize['unit']));
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

							return $this->factory->get('filespec')->set_error($this->language->lang($this->error_prefix . 'WRONG_FILESIZE', $max_filesize['value'], $max_filesize['unit']));
						}
					}
					else if (stripos($line, '404 not found') !== false)
					{
						return $this->factory->get('filespec')->set_error($this->error_prefix . 'URL_NOT_FOUND');
					}
				}
			}

			$stream_meta_data = stream_get_meta_data($fsock);

			// Cancel upload if we exceed timeout
			if (!empty($stream_meta_data['timed_out']) || time() >= $timer_stop)
			{
				return $this->factory->get('filespec')->set_error($this->error_prefix . 'REMOTE_UPLOAD_TIMEOUT');
			}
		}
		@fclose($fsock);

		if (empty($data))
		{
			return $this->factory->get('filespec')->set_error($this->error_prefix . 'EMPTY_REMOTE_DATA');
		}

		$tmp_path = (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') ? false : $this->phpbb_root_path . 'cache';
		$filename = tempnam($tmp_path, unique_id() . '-');

		if (!($fp = @fopen($filename, 'wb')))
		{
			return $this->factory->get('filespec')->set_error($this->error_prefix . 'NOT_UPLOADED');
		}

		$upload_ary['size'] = fwrite($fp, $data);
		fclose($fp);
		unset($data);

		$upload_ary['tmp_name'] = $filename;

		/** @var filespec $file */
		$file = $this->factory->get('filespec')
			->set_upload_ary($upload_ary)
			->set_upload_namespace($this);
		$this->common_checks($file);

		return $file;
	}

	/**
	 * Assign internal error
	 *
	 * @param string $errorcode Error code to assign
	 *
	 * @return string Error string
	 * @access private
	 */
	function assign_internal_error($errorcode)
	{
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

				$error = (empty($max_filesize)) ? $this->language->lang($this->error_prefix . 'PHP_SIZE_NA') : $this->language->lang($this->error_prefix . 'PHP_SIZE_OVERRUN', $max_filesize, $this->language->lang($unit));
				break;

			case 2:
				$max_filesize = get_formatted_filesize($this->max_filesize, false);

				$error = $this->language->lang($this->error_prefix . 'WRONG_FILESIZE', $max_filesize['value'], $max_filesize['unit']);
				break;

			case 3:
				$error = $this->language->lang($this->error_prefix . 'PARTIAL_UPLOAD');
				break;

			case 4:
				$error = $this->language->lang($this->error_prefix . 'NOT_UPLOADED');
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
	 * Perform common file checks
	 *
	 * @param filespec $file Instance of filespec class
	 */
	function common_checks(&$file)
	{
		// Filesize is too big or it's 0 if it was larger than the maxsize in the upload form
		if ($this->max_filesize && ($file->get('filesize') > $this->max_filesize || $file->get('filesize') == 0))
		{
			$max_filesize = get_formatted_filesize($this->max_filesize, false);

			$file->error[] = $this->language->lang($this->error_prefix . 'WRONG_FILESIZE', $max_filesize['value'], $max_filesize['unit']);
		}

		// check Filename
		if (preg_match("#[\\/:*?\"<>|]#i", $file->get('realname')))
		{
			$file->error[] = $this->language->lang($this->error_prefix . 'INVALID_FILENAME', $file->get('realname'));
		}

		// Invalid Extension
		if (!$this->valid_extension($file))
		{
			$file->error[] = $this->language->lang($this->error_prefix . 'DISALLOWED_EXTENSION', $file->get('extension'));
		}

		// MIME Sniffing
		if (!$this->valid_content($file))
		{
			$file->error[] = $this->language->lang($this->error_prefix . 'DISALLOWED_CONTENT');
		}
	}

	/**
	 * Check for allowed extension
	 *
	 * @param filespec $file Instance of filespec class
	 *
	 * @return bool True if extension is allowed, false if not
	 */
	function valid_extension(&$file)
	{
		return (in_array($file->get('extension'), $this->allowed_extensions)) ? true : false;
	}

	/**
	 * Check for allowed dimension
	 *
	 * @param filespec $file Instance of filespec class
	 *
	 * @return bool True if dimensions are valid or no constraints set, false
	 *			if not
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
	 *
	 * @param string $form_name Name of form
	 *
	 * @return bool True if form upload is valid, false if not
	 */
	function is_valid($form_name)
	{
		$upload = $this->request->file($form_name);

		return (!empty($upload) && $upload['name'] !== 'none');
	}


	/**
	 * Check for bad content (IE mime-sniffing)
	 *
	 * @param filespec $file Instance of filespec class
	 *
	 * @return bool True if content is valid, false if not
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
