<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\avatar\driver;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Handles avatars selected from the board gallery
* @package phpBB3
*/
class local extends \phpbb\avatar\driver\driver
{
	/**
	* @inheritdoc
	*/
	public function get_data($row)
	{
		return array(
			'src' => $this->path_helper->get_web_root_path() . $this->config['avatar_gallery_path'] . '/' . $row['avatar'],
			'width' => $row['avatar_width'],
			'height' => $row['avatar_height'],
		);
	}

	/**
	* @inheritdoc
	*/
	public function prepare_form($request, $template, $user, $row, &$error)
	{
		$avatar_list = $this->get_avatar_list($user);
		$category = $request->variable('avatar_local_cat', '');

		foreach ($avatar_list as $cat => $null)
		{
			if (!empty($avatar_list[$cat]))
			{
				$template->assign_block_vars('avatar_local_cats', array(
					'NAME' => $cat,
					'SELECTED' => ($cat == $category),
				));
			}

			if ($cat != $category)
			{
				unset($avatar_list[$cat]);
			}
		}

		if (!empty($avatar_list[$category]))
		{
			$template->assign_vars(array(
				'AVATAR_LOCAL_SHOW' => true,
			));

			$table_cols = isset($row['avatar_gallery_cols']) ? $row['avatar_gallery_cols'] : 4;
			$row_count = $col_count = $avatar_pos = 0;
			$avatar_count = sizeof($avatar_list[$category]);

			reset($avatar_list[$category]);

			while ($avatar_pos < $avatar_count)
			{
				$img = current($avatar_list[$category]);
				next($avatar_list[$category]);

				if ($col_count == 0)
				{
					++$row_count;
					$template->assign_block_vars('avatar_local_row', array(
					));
				}

				$template->assign_block_vars('avatar_local_row.avatar_local_col', array(
					'AVATAR_IMAGE'  => $this->phpbb_root_path . $this->config['avatar_gallery_path'] . '/' . $img['file'],
					'AVATAR_NAME' 	=> $img['name'],
					'AVATAR_FILE' 	=> $img['filename'],
				));

				$template->assign_block_vars('avatar_local_row.avatar_local_option', array(
					'AVATAR_FILE' 		=> $img['filename'],
					'S_OPTIONS_AVATAR'	=> $img['filename']
				));

				$col_count = ($col_count + 1) % $table_cols;

				++$avatar_pos;
			}
		}

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function prepare_form_acp($user)
	{
		return array(
			'avatar_gallery_path'	=> array('lang' => 'AVATAR_GALLERY_PATH',	'validate' => 'rpath',	'type' => 'text:20:255', 'explain' => true),
		);
	}

	/**
	* @inheritdoc
	*/
	public function process_form($request, $template, $user, $row, &$error)
	{
		$avatar_list = $this->get_avatar_list($user);
		$category = $request->variable('avatar_local_cat', '');

		$file = $request->variable('avatar_local_file', '');

		if (empty($category) || empty($file))
		{
			$error[] = 'NO_AVATAR_SELECTED';
			return false;
		}

		if (!isset($avatar_list[$category][urldecode($file)]))
		{
			$error[] = 'AVATAR_URL_NOT_FOUND';
			return false;
		}

		return array(
			'avatar' => ($category != $user->lang['MAIN']) ? $category . '/' . $file : $file,
			'avatar_width' => $avatar_list[$category][urldecode($file)]['width'],
			'avatar_height' => $avatar_list[$category][urldecode($file)]['height'],
		);
	}

	/**
	* Get a list of avatars that are locally available
	* Results get cached for 24 hours (86400 seconds)
	*
	* @param \phpbb\user $user User object
	*
	* @return array Array containing the locally available avatars
	*/
	protected function get_avatar_list($user)
	{
		$avatar_list = ($this->cache == null) ? false : $this->cache->get('avatar_local_list');

		if ($avatar_list === false)
		{
			$avatar_list = array();
			$path = $this->phpbb_root_path . $this->config['avatar_gallery_path'];

			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS), \RecursiveIteratorIterator::SELF_FIRST);
			foreach ($iterator as $file_info)
			{
				$file_path = $file_info->getPath();
				$image = $file_info->getFilename();

				// Match all images in the gallery folder
				if (preg_match('#^[^&\'"<>]+\.(?:' . implode('|', $this->allowed_extensions) . ')$#i', $image) && is_file($file_path . '/' . $image))
				{
					if (function_exists('getimagesize'))
					{
						$dims = getimagesize($file_path . '/' . $image);
					}
					else
					{
						$dims = array(0, 0);
					}
					$cat = ($path == $file_path) ? $user->lang['MAIN'] : str_replace("$path/", '', $file_path);
					$avatar_list[$cat][$image] = array(
						'file'      => ($cat != $user->lang['MAIN']) ? rawurlencode($cat) . '/' . rawurlencode($image) : rawurlencode($image),
						'filename'  => rawurlencode($image),
						'name'      => ucfirst(str_replace('_', ' ', preg_replace('#^(.*)\..*$#', '\1', $image))),
						'width'     => $dims[0],
						'height'    => $dims[1],
					);
				}
			}
			ksort($avatar_list);

			if ($this->cache != null)
			{
				$this->cache->put('avatar_local_list', $avatar_list, 86400);
			}
		}

		return $avatar_list;
	}
}
