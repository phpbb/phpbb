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
			'AVATAR_REMOTE_WIDTH' => ((in_array($row['avatar_type'], array(AVATAR_REMOTE, $this->get_name(), 'remote'))) && $row['avatar_width']) ? $row['avatar_width'] : $request->variable('avatar_remote_width', ''),
			'AVATAR_REMOTE_HEIGHT' => ((in_array($row['avatar_type'], array(AVATAR_REMOTE, $this->get_name(), 'remote'))) && $row['avatar_height']) ? $row['avatar_height'] : $request->variable('avatar_remote_width', ''),
			'AVATAR_REMOTE_URL' => ((in_array($row['avatar_type'], array(AVATAR_REMOTE, $this->get_name(), 'remote'))) && $row['avatar']) ? $row['avatar'] : '',
		));

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function process_form($request, $template, $user, $row, &$error)
	{
		global $phpbb_dispatcher;

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

		/**
		 * Event to make custom validation of avatar upload
		 *
		 * @event core.ucp_profile_avatar_upload_validation
		 * @var	string	url		Image url
		 * @var	string	width	Image width
		 * @var	string	height	Image height
		 * @var	array	error	Error message array
		 * @since 3.2.9-RC1
		 */
		$vars = array('url', 'width', 'height', 'error');
		extract($phpbb_dispatcher->trigger_event('core.ucp_profile_avatar_upload_validation', compact($vars)));

		if (!empty($error))
		{
			return false;
		}

		// Check if this url looks alright
		// Do not allow specifying the port (see RFC 3986) or IP addresses
		if (!preg_match('#^(http|https|ftp)://(?:(.*?\.)*?[a-z0-9\-]+?\.[a-z]{2,4}|(?:\d{1,3}\.){3,5}\d{1,3}):?([0-9]*?).*?\.('. implode('|', $this->allowed_extensions) . ')$#i', $url) ||
			preg_match('@^(http|https|ftp)://[^/:?#]+:[0-9]+[/:?#]@i', $url) ||
			preg_match('#^(http|https|ftp)://(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])#i', $url) ||
			preg_match('#^(http|https|ftp)://(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){0,5}(?:[\dA-F]{1,4}(?::[\dA-F]{1,4})?|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:)|(?:::))#i', $url))
		{
			$error[] = 'AVATAR_URL_INVALID';
			return false;
		}

		// Get image dimensions
		if (($width <= 0 || $height <= 0) && (($image_data = $this->imagesize->getImageSize($url)) === false))
		{
			$error[] = 'UNABLE_GET_IMAGE_SIZE';
			return false;
		}

		if (!empty($image_data) && ($image_data['width'] <= 0 || $image_data['height'] <= 0))
		{
			$error[] = 'AVATAR_NO_SIZE';
			return false;
		}

		$width = ($width && $height) ? $width : $image_data['width'];
		$height = ($width && $height) ? $height : $image_data['height'];

		if ($width <= 0 || $height <= 0)
		{
			$error[] = 'AVATAR_NO_SIZE';
			return false;
		}

		$types = \phpbb\files\upload::image_types();
		$extension = strtolower(\phpbb\files\filespec::get_extension($url));

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

		if (!empty($image_data) && (!isset($types[$image_data['type']]) || !in_array($extension, $types[$image_data['type']])))
		{
			if (!isset($types[$image_data['type']]))
			{
				$error[] = 'UNABLE_GET_IMAGE_SIZE';
			}
			else
			{
				$error[] = array('IMAGE_FILETYPE_MISMATCH', $types[$image_data['type']][0], $extension);
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
