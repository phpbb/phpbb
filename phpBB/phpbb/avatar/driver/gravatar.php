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
* Handles avatars hosted at gravatar.com
*/
class gravatar extends \phpbb\avatar\driver\driver
{
	/**
	* The URL for the gravatar service
	*/
	const GRAVATAR_URL = '//gravatar.com/avatar/';

	/**
	* {@inheritdoc}
	*/
	public function get_data($row)
	{
		return array(
			'src' => $this->get_gravatar_url($row),
			'width' => $row['avatar_width'],
			'height' => $row['avatar_height'],
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_custom_html($user, $row, $alt = '')
	{
		return '<img src="' . $this->get_gravatar_url($row) . '" ' .
			($row['avatar_width'] ? ('width="' . $row['avatar_width'] . '" ') : '') .
			($row['avatar_height'] ? ('height="' . $row['avatar_height'] . '" ') : '') .
			'alt="' . ((!empty($user->lang[$alt])) ? $user->lang[$alt] : $alt) . '" />';
	}

	/**
	* {@inheritdoc}
	*/
	public function prepare_form($request, $template, $user, $row, &$error)
	{
		$template->assign_vars(array(
			'AVATAR_GRAVATAR_WIDTH' => (($row['avatar_type'] == $this->get_name() || $row['avatar_type'] == 'gravatar') && $row['avatar_width']) ? $row['avatar_width'] : $request->variable('avatar_gravatar_width', ''),
			'AVATAR_GRAVATAR_HEIGHT' => (($row['avatar_type'] == $this->get_name() || $row['avatar_type'] == 'gravatar') && $row['avatar_height']) ? $row['avatar_height'] : $request->variable('avatar_gravatar_height', ''),
			'AVATAR_GRAVATAR_EMAIL' => (($row['avatar_type'] == $this->get_name() || $row['avatar_type'] == 'gravatar') && $row['avatar']) ? $row['avatar'] : '',
		));

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function process_form($request, $template, $user, $row, &$error)
	{
		$row['avatar'] = $request->variable('avatar_gravatar_email', '');
		$row['avatar_width'] = $request->variable('avatar_gravatar_width', 0);
		$row['avatar_height'] = $request->variable('avatar_gravatar_height', 0);

		if (empty($row['avatar']))
		{
			return false;
		}

		if (!function_exists('validate_data'))
		{
			require($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$validate_array = validate_data(
			array(
				'email' => $row['avatar'],
			),
			array(
				'email' => array(
					array('string', false, 6, 60),
					array('email'),
				),
			)
		);

		$error = array_merge($error, $validate_array);

		if (!empty($error))
		{
			return false;
		}

		// Get image dimensions if they are not set
		if ($row['avatar_width'] <= 0 || $row['avatar_height'] <= 0)
		{
			/**
			* default to the minimum of the maximum allowed avatar size if the size
			* is not or only partially entered
			*/
			$row['avatar_width'] = $row['avatar_height'] = min($this->config['avatar_max_width'], $this->config['avatar_max_height']);
			$url = $this->get_gravatar_url($row);

			if (($row['avatar_width'] <= 0 || $row['avatar_height'] <= 0) && (($image_data = $this->imagesize->getImageSize($url)) === false))
			{
				$error[] = 'UNABLE_GET_IMAGE_SIZE';
				return false;
			}

			if (!empty($image_data) && ($image_data['width'] <= 0 || $image_data['width'] <= 0))
			{
				$error[] = 'AVATAR_NO_SIZE';
				return false;
			}

			$row['avatar_width'] = ($row['avatar_width'] && $row['avatar_height']) ? $row['avatar_width'] : $image_data['width'];
			$row['avatar_height'] = ($row['avatar_width'] && $row['avatar_height']) ? $row['avatar_height'] : $image_data['height'];
		}

		if ($row['avatar_width'] <= 0 || $row['avatar_height'] <= 0)
		{
			$error[] = 'AVATAR_NO_SIZE';
			return false;
		}

		if ($this->config['avatar_max_width'] || $this->config['avatar_max_height'])
		{
			if ($row['avatar_width'] > $this->config['avatar_max_width'] || $row['avatar_height'] > $this->config['avatar_max_height'])
			{
				$error[] = array('AVATAR_WRONG_SIZE', $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], $row['avatar_width'], $row['avatar_height']);
				return false;
			}
		}

		if ($this->config['avatar_min_width'] || $this->config['avatar_min_height'])
		{
			if ($row['avatar_width'] < $this->config['avatar_min_width'] || $row['avatar_height'] < $this->config['avatar_min_height'])
			{
				$error[] = array('AVATAR_WRONG_SIZE', $this->config['avatar_min_width'], $this->config['avatar_min_height'], $this->config['avatar_max_width'], $this->config['avatar_max_height'], $row['avatar_width'], $row['avatar_height']);
				return false;
			}
		}

		return array(
			'avatar' => $row['avatar'],
			'avatar_width' => $row['avatar_width'],
			'avatar_height' => $row['avatar_height'],
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_template_name()
	{
		return 'ucp_avatar_options_gravatar.html';
	}

	/**
	* Build gravatar URL for output on page
	*
	* @param array $row User data or group data that has been cleaned with
	*        \phpbb\avatar\manager::clean_row
	* @return string Gravatar URL
	*/
	protected function get_gravatar_url($row)
	{
		global $phpbb_dispatcher;

		$url = self::GRAVATAR_URL;
		$url .= hash('sha256', strtolower(trim($row['avatar'])));

		if ($row['avatar_width'] || $row['avatar_height'])
		{
			$url .= '?s=' . max($row['avatar_width'], $row['avatar_height']);
		}

		/**
		* Modify gravatar url
		*
		* @event core.get_gravatar_url_after
		* @var	string	row	User data or group data
		* @var	string	url	Gravatar URL
		* @since 3.1.7-RC1
		*/
		$vars = array('row', 'url');
		extract($phpbb_dispatcher->trigger_event('core.get_gravatar_url_after', compact($vars)));

		return $url;
	}
}
