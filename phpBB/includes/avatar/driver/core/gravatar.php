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
// @todo: rename classes to phpbb_ext_foo_avatar_driver_foo and similar
class phpbb_avatar_driver_core_gravatar extends phpbb_avatar_driver
{
	/**
	* We'll need to create a different type of avatar for gravatar
	*/
	public $custom_html = true;

	/**
	* @inheritdoc
	*/
	public function get_data($row, $ignore_config = false)
	{
		// @todo: add allow_avatar_gravatar to database_update.php etc.
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
		$html = '<img src="http://www.gravatar.com/avatar/' . md5(strtolower(trim($row['avatar']))) .  
			(($row['avatar_width'] || $row['avatar_height']) ? ('?s=' . max($row['avatar_width'], $row['avatar_height'])) : '') . '" ' .
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
			'AV_GRAVATAR_WIDTH' => (($row['avatar_type'] == __CLASS__ || $row['avatar_type'] == 'gravatar') && $row['avatar_width']) ? $row['avatar_width'] : $this->request->variable('av_local_width', 0),
			'AV_GRAVATAR_HEIGHT' => (($row['avatar_type'] == __CLASS__ || $row['avatar_type'] == 'gravatar') && $row['avatar_height']) ? $row['avatar_height'] : $this->request->variable('av_local_width', 0),
			'AV_GRAVATAR_EMAIL' => (($row['avatar_type'] == __CLASS__ || $row['avatar_type'] == 'gravatar') && $row['avatar']) ? $row['avatar'] : '',
		));

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function process_form($template, $row, &$error)
	{
		$email = $this->request->variable('av_gravatar_email', '');
		$width = $this->request->variable('av_gravatar_width', 0);
		$height = $this->request->variable('av_gravatar_height', 0);
		var_dump($width, $height);

		/*
		if (!preg_match('#^(http|https|ftp)://#i', $email))
		{
			$url = 'http://' . $url;
		}*/
		// @todo: check if we need to check emails

		require_once($this->phpbb_root_path . 'includes/functions_user.' . $this->phpEx);

		$error = array_merge($error, validate_data(array(
			'email' => $email,
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
		if (function_exists('getimagesize'))
		{
			// build URL
			// @todo: add https support
			$url = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email)));

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

		return array(
			'avatar' => $email,
			'avatar_width' => $width,
			'avatar_height' => $height,
		);
	}
}
