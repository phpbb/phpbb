<?php
/**
*
* @package avatar
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Handles avatars hosted at gravatar.com
* @package avatars
*/
class phpbb_avatar_driver_gravatar extends phpbb_avatar_driver
{
	const GRAVATAR_URL = 'https://secure.gravatar.com/avatar/';

	/**
	* @inheritdoc
	*/
	public $custom_html = true;

	/**
	* @inheritdoc
	*/
	public function get_data($row, $ignore_config = false)
	{
		if ($ignore_config || $this->config['allow_avatar_gravatar'])
		{
			return array(
				'src' => $row['avatar'],
				'width' => $row['avatar_width'],
				'height' => $row['avatar_height'],
			);
		}
		else
		{
			return array(
				'src' => '',
				'width' => 0,
				'height' => 0,
			);
		}
	}
	
	/**
	* @inheritdoc
	*/
	public function get_custom_html($row, $ignore_config = false, $alt = '')
	{
		$html = '<img src="' . $this->get_gravatar_url($row) . '" ' .
			($row['avatar_width'] ? ('width="' . $row['avatar_width'] . '" ') : '') .
			($row['avatar_height'] ? ('height="' . $row['avatar_height'] . '" ') : '') .
			'alt="' . ((!empty($user->lang[$alt])) ? $user->lang[$alt] : $alt) . '" />';
		return $html;
	}

	/**
	* @inheritdoc
	*/
	public function prepare_form($template, $row, &$error)
	{
		$template->assign_vars(array(
			'AVATAR_GRAVATAR_WIDTH' => (($row['avatar_type'] == $this->get_name() || $row['avatar_type'] == 'gravatar') && $row['avatar_width']) ? $row['avatar_width'] : $this->request->variable('avatar_gravatar_width', 0),
			'AVATAR_GRAVATAR_HEIGHT' => (($row['avatar_type'] == $this->get_name() || $row['avatar_type'] == 'gravatar') && $row['avatar_height']) ? $row['avatar_height'] : $this->request->variable('avatar_gravatar_width', 0),
			'AVATAR_GRAVATAR_EMAIL' => (($row['avatar_type'] == $this->get_name() || $row['avatar_type'] == 'gravatar') && $row['avatar']) ? $row['avatar'] : '',
		));

		return true;
	}

	/**
	* @inheritdoc
	**/
	public function prepare_form_acp()
	{
		return array(
			'allow_avatar_gravatar'	=> array('lang' => 'ALLOW_GRAVATAR',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
		);
	}

	/**
	* @inheritdoc
	*/
	public function process_form($template, $row, &$error)
	{
		$row['avatar'] = $this->request->variable('avatar_gravatar_email', '');
		$row['avatar_width'] = $this->request->variable('avatar_gravatar_width', 0);
		$row['avatar_height'] = $this->request->variable('avatar_gravatar_height', 0);

		require_once($this->phpbb_root_path . 'includes/functions_user' . $this->phpEx);

		$error = array_merge($error, validate_data(array(
			'email' => $row['avatar'],
		), array(
			'email' => array(
				array('string', false, 6, 60),
				array('email')),
		)));

		if (!empty($error))
		{
			return false;
		}

		// Make sure getimagesize works...
		if (function_exists('getimagesize') && ($row['avatar_width'] <= 0 || $row['avatar_height'] <= 0))
		{
			/**
			* default to the minimum of the maximum allowed avatar size if the size
			* is not or only partially entered
			*/
			$row['avatar_width'] = $row['avatar_height'] = min($this->config['avatar_max_width'], $this->config['avatar_max_height']);
			$url = $this->get_gravatar_url($row);

			if (($row['avatar_width'] <= 0 || $row['avatar_height'] <= 0) && (($image_data = @getimagesize($url)) === false))
			{
				$error[] = 'UNABLE_GET_IMAGE_SIZE';
				return false;
			}

			if (!empty($image_data) && ($image_data[0] <= 0 || $image_data[1] <= 0))
			{
				$error[] = 'AVATAR_NO_SIZE';
				return false;
			}

			$row['avatar_width'] = ($row['avatar_width'] && $row['avatar_height']) ? $row['avatar_width'] : $image_data[0];
			$row['avatar_height'] = ($row['avatar_width'] && $row['avatar_height']) ? $row['avatar_height'] : $image_data[1];
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
	* Build gravatar URL for output on page
	*
	* @return string The gravatar URL
	*/
	protected function get_gravatar_url($row)
	{
		$url = self::GRAVATAR_URL;
		$url .=  md5(strtolower(trim($row['avatar'])));

		if ($row['avatar_width'] || $row['avatar_height'])
		{
			$url .= '?s=' . max($row['avatar_width'], $row['avatar_height']);
		}

		return $url;
	}
}
