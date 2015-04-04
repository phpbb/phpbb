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

namespace phpbb\avatar\driver;

/**
* Handles avatars uploaded to the board
*/
class upload extends \phpbb\avatar\driver\driver
{
	/**
	* @var \phpbb\mimetype\guesser
	*/
	protected $mimetype_guesser;

	/**
	* Construct a driver object
	*
	* @param \phpbb\config\config $config phpBB configuration
	* @param string $phpbb_root_path Path to the phpBB root
	* @param string $php_ext PHP file extension
	* @param \phpbb_path_helper $path_helper phpBB path helper
	* @param \phpbb\mimetype\guesser $mimetype_guesser Mimetype guesser
	* @param \phpbb\cache\driver\driver_interface $cache Cache driver
	*/
	public function __construct(\phpbb\config\config $config, $phpbb_root_path, $php_ext, \phpbb\path_helper $path_helper, \phpbb\mimetype\guesser $mimetype_guesser, \phpbb\cache\driver\driver_interface $cache = null)
	{
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->path_helper = $path_helper;
		$this->mimetype_guesser = $mimetype_guesser;
		$this->cache = $cache;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_data($row, $ignore_config = false)
	{
		$root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $this->path_helper->get_web_root_path();

		return array(
			'src' => $root_path . 'download/file.' . $this->php_ext . '?avatar=' . $row['avatar'],
			'width' => $row['avatar_width'],
			'height' => $row['avatar_height'],
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function prepare_form($request, $template, $user, $row, &$error)
	{
		if (!$this->can_upload())
		{
			return false;
		}

		$template->assign_vars(array(
			'S_UPLOAD_AVATAR_URL' => ($this->config['allow_avatar_remote_upload']) ? true : false,
			'AVATAR_UPLOAD_SIZE' => $this->config['avatar_filesize'],
		));

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function process_form($request, $template, $user, $row, &$error)
	{
		if (!$this->can_upload())
		{
			return false;
		}

		if (!class_exists('fileupload'))
		{
			include($this->phpbb_root_path . 'includes/functions_upload.' . $this->php_ext);
		}

		$upload = new \fileupload('AVATAR_', $this->allowed_extensions, $this->config['avatar_filesize'], $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], (isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

		$url = $request->variable('avatar_upload_url', '');
		$upload_file = $request->file('avatar_upload_file');

		if (!empty($upload_file['name']))
		{
			$file = $upload->form_upload('avatar_upload_file', $this->mimetype_guesser);
		}
		else if (!empty($this->config['allow_avatar_remote_upload']) && !empty($url))
		{
			if (!preg_match('#^(http|https|ftp)://#i', $url))
			{
				$url = 'http://' . $url;
			}

			if (!function_exists('validate_data'))
			{
				require($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$validate_array = validate_data(
				array(
					'url' => $url,
				),
				array(
					'url' => array('string', true, 5, 255),
				)
			);

			$error = array_merge($error, $validate_array);

			if (!empty($error))
			{
				return false;
			}

			$file = $upload->remote_upload($url, $this->mimetype_guesser);
		}
		else
		{
			return false;
		}

		$prefix = $this->config['avatar_salt'] . '_';
		$file->clean_filename('avatar', $prefix, $row['id']);

		$destination = $this->config['avatar_path'];

		// Adjust destination path (no trailing slash)
		if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
		{
			$destination = substr($destination, 0, -1);
		}

		$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
		if ($destination && ($destination[0] == '/' || $destination[0] == "\\"))
		{
			$destination = '';
		}

		// Move file and overwrite any existing image
		$file->move_file($destination, true);

		if (sizeof($file->error))
		{
			$file->remove();
			$error = array_merge($error, $file->error);
			return false;
		}

		return array(
			'avatar' => $row['id'] . '_' . time() . '.' . $file->get('extension'),
			'avatar_width' => $file->get('width'),
			'avatar_height' => $file->get('height'),
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function prepare_form_acp($user)
	{
		return array(
			'allow_avatar_remote_upload'=> array('lang' => 'ALLOW_REMOTE_UPLOAD', 'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
			'avatar_filesize'		=> array('lang' => 'MAX_FILESIZE',			'validate' => 'int:0',	'type' => 'number:0', 'explain' => true, 'append' => ' ' . $user->lang['BYTES']),
			'avatar_path'			=> array('lang' => 'AVATAR_STORAGE_PATH',	'validate' => 'rpath',	'type' => 'text:20:255', 'explain' => true),
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function delete($row)
	{
		$ext = substr(strrchr($row['avatar'], '.'), 1);
		$filename = $this->phpbb_root_path . $this->config['avatar_path'] . '/' . $this->config['avatar_salt'] . '_' . $row['id'] . '.' . $ext;

		if (file_exists($filename))
		{
			@unlink($filename);
		}

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_template_name()
	{
		return 'ucp_avatar_options_upload.html';
	}

	/**
	* Check if user is able to upload an avatar
	*
	* @return bool True if user can upload, false if not
	*/
	protected function can_upload()
	{
		return (file_exists($this->phpbb_root_path . $this->config['avatar_path']) && phpbb_is_writable($this->phpbb_root_path . $this->config['avatar_path']) && (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on'));
	}
}
