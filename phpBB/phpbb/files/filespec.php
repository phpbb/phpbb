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

use phpbb\language\language;

/**
 * Responsible for holding all file relevant information, as well as doing file-specific operations.
 * The {@link fileupload fileupload class} can be used to upload several files, each of them being this object to operate further on.
 */
class filespec
{
	/** @var string File name */
	protected $filename = '';

	/** @var string Real name of file */
	protected $realname = '';

	/** @var string Upload name of file */
	protected $uploadname = '';

	/** @var string Mimetype of file */
	protected $mimetype = '';

	/** @var string File extension */
	protected $extension = '';

	/** @var int File size */
	protected $filesize = 0;

	/** @var int Width of file */
	protected $width = 0;

	/** @var int Height of file */
	protected $height = 0;

	/** @var array Image info including type and size */
	protected $image_info = array();

	/** @var string Destination file name */
	protected $destination_file = '';

	/** @var string Destination file path */
	protected $destination_path = '';

	/** @var bool Whether file was moved */
	protected $file_moved = false;

	/** @var bool Whether file is local */
	protected $local = false;

	/** @var bool Class initialization flag */
	protected $class_initialized = false;

	/** @var array Error array */
	public $error = array();

	/** @var upload Instance of upload class  */
	public $upload;

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \bantu\IniGetWrapper\IniGetWrapper ini_get() wrapper class */
	protected $php_ini;

	/** @var \FastImageSize\FastImageSize */
	protected $imagesize;

	/** @var language Language class */
	protected $language;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var \phpbb\plupload\plupload The plupload object */
	protected $plupload;

	/** @var \phpbb\mimetype\guesser phpBB Mimetype guesser */
	protected $mimetype_guesser;

	/**
	 * File upload class
	 *
	 * @param \phpbb\filesystem\filesystem_interface	$phpbb_filesystem Filesystem
	 * @param language					$language Language
	 * @param \bantu\IniGetWrapper\IniGetWrapper			$php_ini ini_get() wrapper
	 * @param \FastImageSize\FastImageSize $imagesize Imagesize class
	 * @param string					$phpbb_root_path phpBB root path
	 * @param \phpbb\mimetype\guesser	$mimetype_guesser Mime type guesser
	 * @param \phpbb\plupload\plupload	$plupload Plupload
	 */
	public function __construct(\phpbb\filesystem\filesystem_interface $phpbb_filesystem, language $language, \bantu\IniGetWrapper\IniGetWrapper $php_ini, \FastImageSize\FastImageSize $imagesize, $phpbb_root_path, \phpbb\mimetype\guesser $mimetype_guesser = null, \phpbb\plupload\plupload $plupload = null)
	{
		$this->filesystem = $phpbb_filesystem;
		$this->language = $language;
		$this->php_ini = $php_ini;
		$this->imagesize = $imagesize;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->plupload = $plupload;
		$this->mimetype_guesser = $mimetype_guesser;
	}

	/**
	 * Set upload ary
	 *
	 * @param array $upload_ary Upload ary
	 *
	 * @return filespec This instance of the filespec class
	 */
	public function set_upload_ary($upload_ary)
	{
		if (!isset($upload_ary) || !count($upload_ary))
		{
			return $this;
		}

		$this->class_initialized = true;
		$this->filename = $upload_ary['tmp_name'];
		$this->filesize = $upload_ary['size'];
		$name = $upload_ary['name'];
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
		$this->filesize = ($this->get_filesize($this->filename)) ?: $this->filesize;

		$this->width = $this->height = 0;
		$this->file_moved = false;

		$this->local = (isset($upload_ary['local_mode'])) ? true : false;

		return $this;
	}

	/**
	 * Set the upload namespace
	 *
	 * @param upload $namespace Instance of upload class
	 *
	 * @return filespec This instance of the filespec class
	 */
	public function set_upload_namespace($namespace)
	{
		$this->upload = $namespace;

		return $this;
	}

	/**
	 * Check if class members were not properly initialised yet
	 *
	 * @return bool True if there was an init error, false if not
	 */
	public function init_error()
	{
		return !$this->class_initialized;
	}

	/**
	 * Set error in error array
	 *
	 * @param mixed $error Content for error array
	 *
	 * @return \phpbb\files\filespec This instance of the filespec class
	 */
	public function set_error($error)
	{
		$this->error[] = $error;

		return $this;
	}

	/**
	 * Cleans destination filename
	 *
	 * @param string $mode Either real, unique, or unique_ext. Real creates a
	 *				realname, filtering some characters, lowering every
	 *				character. Unique creates a unique filename.
	 * @param string $prefix Prefix applied to filename
	 * @param string $user_id The user_id is only needed for when cleaning a user's avatar
	 */
	public function clean_filename($mode = 'unique', $prefix = '', $user_id = '')
	{
		if ($this->init_error())
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
		}
	}

	/**
	 * Get property from file object
	 *
	 * @param string $property Name of property
	 *
	 * @return mixed Content of property
	 */
	public function get($property)
	{
		if ($this->init_error() || !isset($this->$property))
		{
			return false;
		}

		return $this->$property;
	}

	/**
	 * Check if file is an image (mime type)
	 *
	 * @return bool true if it is an image, false if not
	 */
	public function is_image()
	{
		return (strpos($this->mimetype, 'image/') === 0);
	}

	/**
	 * Check if the file got correctly uploaded
	 *
	 * @return bool true if it is a valid upload, false if not
	 */
	public function is_uploaded()
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
	public function remove()
	{
		if ($this->file_moved)
		{
			@unlink($this->destination_file);
		}
	}

	/**
	 * Get file extension
	 *
	 * @param string $filename Filename that needs to be checked
	 *
	 * @return string Extension of the supplied filename
	 */
	static public function get_extension($filename)
	{
		$filename = utf8_basename($filename);

		if (strpos($filename, '.') === false)
		{
			return '';
		}

		$filename = explode('.', $filename);
		return array_pop($filename);
	}

	/**
	 * Get mime type
	 *
	 * @param string $filename Filename that needs to be checked
	 * @return string Mime type of supplied filename
	 */
	public function get_mimetype($filename)
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
	 * Get file size
	 *
	 * @param string $filename File name of file to check
	 *
	 * @return int File size
	 */
	public function get_filesize($filename)
	{
		return @filesize($filename);
	}


	/**
	 * Check the first 256 bytes for forbidden content
	 *
	 * @param array $disallowed_content Array containg disallowed content
	 *
	 * @return bool False if disallowed content found, true if not
	 */
	public function check_content($disallowed_content)
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
	 * @param string|bool $chmod Permission mask for chmodding the file after a successful move.
	 *				The mode entered here reflects the mode defined by {@link phpbb_chmod()}
	 *
	 * @return bool True if file was moved, false if not
	 * @access public
	 */
	public function move_file($destination, $overwrite = false, $skip_image_check = false, $chmod = false)
	{
		if (count($this->error))
		{
			return false;
		}

		$chmod = ($chmod === false) ? CHMOD_READ | CHMOD_WRITE : $chmod;

		// We need to trust the admin in specifying valid upload directories and an attacker not being able to overwrite it...
		$this->destination_path = $this->phpbb_root_path . $destination;

		// Check if the destination path exist...
		if (!file_exists($this->destination_path))
		{
			@unlink($this->filename);
			return false;
		}

		$upload_mode = ($this->php_ini->getBool('open_basedir') || $this->php_ini->getBool('safe_mode')) ? 'move' : 'copy';
		$upload_mode = ($this->local) ? 'local' : $upload_mode;
		$this->destination_file = $this->destination_path . '/' . utf8_basename($this->realname);

		// Check if the file already exist, else there is something wrong...
		if (file_exists($this->destination_file) && !$overwrite)
		{
			@unlink($this->filename);
			$this->error[] = $this->language->lang($this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR', $this->destination_file);
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
							$this->error[] = $this->language->lang($this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR', $this->destination_file);
						}
					}

				break;

				case 'move':

					if (!@move_uploaded_file($this->filename, $this->destination_file))
					{
						if (!@copy($this->filename, $this->destination_file))
						{
							$this->error[] = $this->language->lang($this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR', $this->destination_file);
						}
					}

				break;

				case 'local':

					if (!@copy($this->filename, $this->destination_file))
					{
						$this->error[] = $this->language->lang($this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR', $this->destination_file);
					}

				break;
			}

			// Remove temporary filename
			@unlink($this->filename);

			if (count($this->error))
			{
				return false;
			}

			try
			{
				$this->filesystem->phpbb_chmod($this->destination_file, $chmod);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Do nothing
			}
		}

		// Try to get real filesize from destination folder
		$this->filesize = ($this->get_filesize($this->destination_file)) ?: $this->filesize;

		// Get mimetype of supplied file
		$this->mimetype = $this->get_mimetype($this->destination_file);

		if ($this->is_image() && !$skip_image_check)
		{
			$this->width = $this->height = 0;

			$this->image_info = $this->imagesize->getImageSize($this->destination_file, $this->mimetype);

			if ($this->image_info !== false)
			{
				$this->width = $this->image_info['width'];
				$this->height = $this->image_info['height'];

				// Check image type
				$types = upload::image_types();

				if (!isset($types[$this->image_info['type']]) || !in_array($this->extension, $types[$this->image_info['type']]))
				{
					if (!isset($types[$this->image_info['type']]))
					{
						$this->error[] = $this->language->lang('IMAGE_FILETYPE_INVALID', $this->image_info['type'], $this->mimetype);
					}
					else
					{
						$this->error[] = $this->language->lang('IMAGE_FILETYPE_MISMATCH', $types[$this->image_info['type']][0], $this->extension);
					}
				}

				// Make sure the dimensions match a valid image
				if (empty($this->width) || empty($this->height))
				{
					$this->error[] = $this->language->lang('ATTACHED_IMAGE_NOT_IMAGE');
				}
			}
			else
			{
				$this->error[] = $this->language->lang('UNABLE_GET_IMAGE_SIZE');
			}
		}

		$this->file_moved = true;
		$this->additional_checks();
		unset($this->upload);

		return true;
	}

	/**
	 * Performing additional checks
	 *
	 * @return bool False if issue was found, true if not
	 */
	public function additional_checks()
	{
		if (!$this->file_moved)
		{
			return false;
		}

		// Filesize is too big or it's 0 if it was larger than the maxsize in the upload form
		if ($this->upload->max_filesize && ($this->get('filesize') > $this->upload->max_filesize || $this->filesize == 0))
		{
			$max_filesize = get_formatted_filesize($this->upload->max_filesize, false);

			$this->error[] = $this->language->lang($this->upload->error_prefix . 'WRONG_FILESIZE', $max_filesize['value'], $max_filesize['unit']);

			return false;
		}

		if (!$this->upload->valid_dimensions($this))
		{
			$this->error[] = $this->language->lang($this->upload->error_prefix . 'WRONG_SIZE',
				$this->language->lang('PIXELS', (int) $this->upload->min_width),
				$this->language->lang('PIXELS', (int) $this->upload->min_height),
				$this->language->lang('PIXELS', (int) $this->upload->max_width),
				$this->language->lang('PIXELS', (int) $this->upload->max_height),
				$this->language->lang('PIXELS', (int) $this->width),
				$this->language->lang('PIXELS', (int) $this->height));

			return false;
		}

		return true;
	}
}
