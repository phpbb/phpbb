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
* Handles avatars hosted remotely
*/
class remote extends \phpbb\avatar\driver\driver
{
	/**
	* {@inheritdoc}
	*/
	public function get_data($row)
	{
		return array(
			'src' => $row['avatar'],
			'width' => $row['avatar_width'],
			'height' => $row['avatar_height'],
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function prepare_form($request, $template, $user, $row, &$error)
	{
		$template->assign_vars(array(
			'AVATAR_REMOTE_WIDTH' => ((in_array($row['avatar_type'], array(AVATAR_REMOTE, $this->get_name(), 'remote'))) && $row['avatar_width']) ? $row['avatar_width'] : $request->variable('avatar_remote_width', 0),
			'AVATAR_REMOTE_HEIGHT' => ((in_array($row['avatar_type'], array(AVATAR_REMOTE, $this->get_name(), 'remote'))) && $row['avatar_height']) ? $row['avatar_height'] : $request->variable('avatar_remote_width', 0),
			'AVATAR_REMOTE_URL' => ((in_array($row['avatar_type'], array(AVATAR_REMOTE, $this->get_name(), 'remote'))) && $row['avatar']) ? $row['avatar'] : '',
		));

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function process_form($request, $template, $user, $row, &$error)
	{
		$url = $request->variable('avatar_remote_url', '');
		$width = $request->variable('avatar_remote_width', 0);
		$height = $request->variable('avatar_remote_height', 0);

		if (empty($url))
		{
			return false;
		}

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

		// Check if this url looks alright
		// This isn't perfect, but it's what phpBB 3.0 did, and might as well make sure everything is compatible
		if (!preg_match('#^(http|https|ftp)://(?:(.*?\.)*?[a-z0-9\-]+?\.[a-z]{2,4}|(?:\d{1,3}\.){3,5}\d{1,3}):?([0-9]*?).*?\.('. implode('|', $this->allowed_extensions) . ')$#i', $url))
		{
			$error[] = 'AVATAR_URL_INVALID';
			return false;
		}

		// Make sure getimagesize works...
		if (function_exists('getimagesize'))
		{
			if (($width <= 0 || $height <= 0) && (($image_data = @getimagesize($url)) === false))
			{
				$error[] = 'UNABLE_GET_IMAGE_SIZE';
				return false;
			}

			if (!empty($image_data) && ($image_data[0] <= 0 || $image_data[1] <= 0))
			{
				$error[] = 'AVATAR_NO_SIZE';
				return false;
			}

			$width = ($width && $height) ? $width : $image_data[0];
			$height = ($width && $height) ? $height : $image_data[1];
		}

		if ($width <= 0 || $height <= 0)
		{
			$error[] = 'AVATAR_NO_SIZE';
			return false;
		}

		if (!class_exists('fileupload'))
		{
			include($this->phpbb_root_path . 'includes/functions_upload.' . $this->php_ext);
		}

		$types = \fileupload::image_types();
		$extension = strtolower(\filespec::get_extension($url));

		// Check if this is actually an image
		if ($file_stream = @fopen($url, 'r'))
		{
			// Timeout after 1 second
			stream_set_timeout($file_stream, 1);
			// read some data to ensure headers are present
			fread($file_stream, 1024);
			$meta = stream_get_meta_data($file_stream);

			if (isset($meta['wrapper_data']['headers']) && is_array($meta['wrapper_data']['headers']))
			{
				$headers = $meta['wrapper_data']['headers'];
			}
			else if (isset($meta['wrapper_data']) && is_array($meta['wrapper_data']))
			{
				$headers = $meta['wrapper_data'];
			}
			else
			{
				$headers = array();
			}

			foreach ($headers as $header)
			{
				$header = preg_split('/ /', $header, 2);
				if (strtr(strtolower(trim($header[0], ':')), '_', '-') === 'content-type')
				{
					if (strpos($header[1], 'image/') !== 0)
					{
						$error[] = 'AVATAR_URL_INVALID';
						fclose($file_stream);
						return false;
					}
					else
					{
						fclose($file_stream);
						break;
					}
				}
			}
		}
		else
		{
			$error[] = 'AVATAR_URL_INVALID';
			return false;
		}

		if (!empty($image_data) && (!isset($types[$image_data[2]]) || !in_array($extension, $types[$image_data[2]])))
		{
			if (!isset($types[$image_data[2]]))
			{
				$error[] = 'UNABLE_GET_IMAGE_SIZE';
			}
			else
			{
				$error[] = array('IMAGE_FILETYPE_MISMATCH', $types[$image_data[2]][0], $extension);
			}

			return false;
		}

		if ($this->config['avatar_max_width'] || $this->config['avatar_max_height'])
		{
			if ($width > $this->config['avatar_max_width'] || $height > $this->config['avatar_max_height'])
			{
				$error[] = array('AVATAR_WRONG_SIZE', $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], $width, $height);
				return false;
			}
		}

		if ($this->config['avatar_min_width'] || $this->config['avatar_min_height'])
		{
			if ($width < $this->config['avatar_min_width'] || $height < $this->config['avatar_min_height'])
			{
				$error[] = array('AVATAR_WRONG_SIZE', $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], $width, $height);
				return false;
			}
		}

		return array(
			'avatar' => $url,
			'avatar_width' => $width,
			'avatar_height' => $height,
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_template_name()
	{
		return 'ucp_avatar_options_remote.html';
	}
}
