<?php
/**
*
* @package avatar
* @copyright (c) 2005, 2009 phpBB Group
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
* Handles avatars selected from the board gallery
* @package avatars
*/
class phpbb_avatar_driver_local extends phpbb_avatar_driver
{
	/**
	* Get the avatar url and dimensions
	*
	* @param $ignore_config Whether $user or global avatar visibility settings
	*        should be ignored
	* @return array Avatar data
	*/
	public function get_data($user_row, $ignore_config = false)
	{
		if ($ignore_config || $this->config['allow_avatar_local'])
		{
			return array(
				'src' => $this->phpbb_root_path . $this->config['avatar_gallery_path'] . '/' . $user_row['user_avatar'],
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
	* @TODO
	**/
	public function handle_form($template, &$error = array(), $submitted = false)
	{
		if ($submitted) {
			$error[] = 'TODO';
			return '';
		}

		$avatar_list = array();
		$path = $this->phpbb_root_path . $this->config['avatar_gallery_path'];

		$dh = @opendir($path);

		if (!$dh)
		{
			return $avatar_list;
		}

		while (($cat = readdir($dh)) !== false) {
			if ($cat[0] != '.' && preg_match('#^[^&"\'<>]+$#i', $cat) && is_dir("$path/$cat"))
			{
				if ($ch = @opendir("$path/$cat"))
				{
					while (($image = readdir($ch)) !== false)
					{
						if (preg_match('#^[^&\'"<>]+\.(?:gif|png|jpe?g)$#i', $image))
						{
							$avatar_list[$cat][] = array(
								'file'      => rawurlencode($cat) . '/' . rawurlencode($image),
								'filename'  => rawurlencode($image),
								'name'      => ucfirst(str_replace('_', ' ', preg_replace('#^(.*)\..*$#', '\1', $image))),
							);
						}
					}
					@closedir($ch);
				}
			}
		}
		@closedir($dh);

		@ksort($avatar_list);

		$category = request_var('av_local_cat', '');
		$categories = array_keys($avatar_list);

		foreach ($categories as $cat)
		{
			if (!empty($avatar_list[$cat]))
			{
				$template->assign_block_vars('av_local_cats', array(
					'NAME' => $cat,
					'SELECTED' => ($cat == $category),
				));
			}
		}

		if (!empty($avatar_list[$category]))
		{
			foreach ($avatar_list[$category] as $img => $data)
			{
				$template->assign_block_vars('av_local_imgs', array(
					'AVATAR_IMAGE'  => $path . '/' . $data['file'],
					'AVATAR_NAME' => $data['name'],
					'AVATAR_FILE' => $data['filename'],
				));
			}
		}

		return true;
	}
}
