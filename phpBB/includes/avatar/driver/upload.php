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
* Handles avatars uploaded to the board
* @package avatars
*/
class phpbb_avatar_driver_upload extends phpbb_avatar_driver
{
	/**
	* @inheritdoc
	*/
	public function get_data($user_row, $ignore_config = false)
	{
		if ($ignore_config || $this->config['allow_avatar_upload'])
		{
			return array(
				'src' => $this->phpbb_root_path . 'download/file.' . $this->phpEx . '?avatar=' . $user_row['user_avatar'],
				'width' => $user_row['user_avatar_width'],
				'height' => $user_row['user_avatar_height'],
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
	public function handle_form($template, $user_row, &$error, $submitted = false)
	{
		if ($submitted) {
			$error[] = 'TODO';
			return '';
		}
		else
		{
			$can_upload = (file_exists($this->phpbb_root_path . $this->config['avatar_path']) && phpbb_is_writable($this->phpbb_root_path . $this->config['avatar_path']) && (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on')) ? true : false;
			if ($can_upload)
			{
				$template->assign_vars(array(
					'S_UPLOAD_AVATAR_URL' => ($this->config['allow_avatar_remote_upload']) ? true : false,
					'AV_UPLOAD_SIZE' => $this->config['avatar_filesize'],
				));

				return true;
			}
			else
			{
				return false;
			}
		}
	}
}
