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
* Handles avatars selected from the board gallery
* @package avatars
*/
class phpbb_avatar_driver_local extends phpbb_avatar_driver
{
	/**
	* @inheritdoc
	*/
	public function get_data($row, $ignore_config = false)
	{
		if ($ignore_config || $this->config['allow_avatar_local'])
		{
			return array(
				'src' => $this->phpbb_root_path . $this->config['avatar_gallery_path'] . '/' . $row['avatar'],
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
	public function prepare_form($template, $row, &$error)
	{
		$avatar_list = $this->get_avatar_list();
		$category = $this->request->variable('av_local_cat', '');

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
			$template->assign_vars(array(
				'AV_LOCAL_SHOW' => true,
			));

			$table_cols = isset($row['av_gallery_cols']) ? $row['av_gallery_cols'] : 4;
			$row_count = $col_count = $av_pos = 0;
			$av_count = sizeof($avatar_list[$category]);

			reset($avatar_list[$category]);

			while ($av_pos < $av_count)
			{
				$img = current($avatar_list[$category]);
				next($avatar_list[$category]);

				if ($col_count == 0)
				{
					++$row_count;
					$template->assign_block_vars('av_local_row', array(
					));
				}

				$template->assign_block_vars('av_local_row.av_local_col', array(
					'AVATAR_IMAGE'  => $this->phpbb_root_path . $this->config['avatar_gallery_path'] . '/' . $img['file'],
					'AVATAR_NAME' 	=> $img['name'],
					'AVATAR_FILE' 	=> $img['filename'],
				));

				$template->assign_block_vars('av_local_row.av_local_option', array(
					'AVATAR_FILE' 		=> $img['filename'],
					'S_OPTIONS_AVATAR'	=> $img['filename']
				));

				$col_count = ($col_count + 1) % $table_cols;

				++$av_pos;
			}
		}

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function process_form($template, $row, &$error)
	{
		$avatar_list = $this->get_avatar_list();
		$category = $this->request->variable('av_local_cat', '');

		$file = $this->request->variable('av_local_file', '');
		if (!isset($avatar_list[$category][urldecode($file)]))
		{
			$error[] = 'AVATAR_URL_NOT_FOUND';
			return false;
		}

		return array(
			'avatar' => $category . '/' . $file,
			'avatar_width' => $avatar_list[$category][urldecode($file)]['width'],
			'avatar_height' => $avatar_list[$category][urldecode($file)]['height'],
		);
	}

	/**
	* @TODO
	*/
	private function get_avatar_list()
	{
		$avatar_list = ($this->cache == null) ? false : $this->cache->get('av_local_list');

		if (!$avatar_list)
		{
			$avatar_list = array();
			$path = $this->phpbb_root_path . $this->config['avatar_gallery_path'];

			$dh = @opendir($path);

			if ($dh)
			{
				while (($cat = readdir($dh)) !== false)
				{
					if ($cat[0] != '.' && preg_match('#^[^&"\'<>]+$#i', $cat) && is_dir("$path/$cat"))
					{
						if ($ch = @opendir("$path/$cat"))
						{
							while (($image = readdir($ch)) !== false)
							{
								// Match all images in the gallery folder
								if (preg_match('#^[^&\'"<>]+\.(?:gif|png|jpe?g)$#i', $image))
								{
									if (function_exists('getimagesize'))
									{
										$dims = getimagesize($this->phpbb_root_path . $this->config['avatar_gallery_path'] . '/' . $cat . '/' . $image);
									}
									else
									{
										$dims = array(0, 0);
									}
									$avatar_list[$cat][$image] = array(
										'file'      => rawurlencode($cat) . '/' . rawurlencode($image),
										'filename'  => rawurlencode($image),
										'name'      => ucfirst(str_replace('_', ' ', preg_replace('#^(.*)\..*$#', '\1', $image))),
										'width'     => $dims[0],
										'height'    => $dims[1],
									);
								}
							}
							@closedir($ch);
						}
					}
				}
				@closedir($dh);
			}

			@ksort($avatar_list);

			if ($this->cache != null)
			{
				$this->cache->put('av_local_list', $avatar_list);
			}
		}

		return $avatar_list;
	}
}
