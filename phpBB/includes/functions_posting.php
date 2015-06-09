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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Fill smiley templates (or just the variables) with smilies, either in a window or inline
*/
function generate_smilies($mode, $forum_id)
{
	global $db, $user, $config, $template, $phpbb_dispatcher;
	global $phpEx, $phpbb_root_path, $phpbb_container, $phpbb_path_helper;

	$base_url = append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=smilies&amp;f=' . $forum_id);
	$pagination = $phpbb_container->get('pagination');
	$start = request_var('start', 0);

	if ($mode == 'window')
	{
		if ($forum_id)
		{
			$sql = 'SELECT forum_style
				FROM ' . FORUMS_TABLE . "
				WHERE forum_id = $forum_id";
			$result = $db->sql_query_limit($sql, 1);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$user->setup('posting', (int) $row['forum_style']);
		}
		else
		{
			$user->setup('posting');
		}

		page_header($user->lang['SMILIES']);

		$sql = 'SELECT COUNT(smiley_id) AS item_count
			FROM ' . SMILIES_TABLE . '
			GROUP BY smiley_url';
		$result = $db->sql_query($sql, 3600);

		$smiley_count = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			++$smiley_count;
		}
		$db->sql_freeresult($result);

		$template->set_filenames(array(
			'body' => 'posting_smilies.html')
		);

		$start = $pagination->validate_start($start, $config['smilies_per_page'], $smiley_count);
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $smiley_count, $config['smilies_per_page'], $start);
	}

	$display_link = false;
	if ($mode == 'inline')
	{
		$sql = 'SELECT smiley_id
			FROM ' . SMILIES_TABLE . '
			WHERE display_on_posting = 0';
		$result = $db->sql_query_limit($sql, 1, 0, 3600);

		if ($row = $db->sql_fetchrow($result))
		{
			$display_link = true;
		}
		$db->sql_freeresult($result);
	}

	if ($mode == 'window')
	{
		$sql = 'SELECT smiley_url, MIN(emotion) as emotion, MIN(code) AS code, smiley_width, smiley_height, MIN(smiley_order) AS min_smiley_order
			FROM ' . SMILIES_TABLE . '
			GROUP BY smiley_url, smiley_width, smiley_height
			ORDER BY min_smiley_order';
		$result = $db->sql_query_limit($sql, $config['smilies_per_page'], $start, 3600);
	}
	else
	{
		$sql = 'SELECT *
			FROM ' . SMILIES_TABLE . '
			WHERE display_on_posting = 1
			ORDER BY smiley_order';
		$result = $db->sql_query($sql, 3600);
	}

	$smilies = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if (empty($smilies[$row['smiley_url']]))
		{
			$smilies[$row['smiley_url']] = $row;
		}
	}
	$db->sql_freeresult($result);

	if (sizeof($smilies))
	{
		$root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $phpbb_path_helper->get_web_root_path();

		foreach ($smilies as $row)
		{
			$template->assign_block_vars('smiley', array(
				'SMILEY_CODE'	=> $row['code'],
				'A_SMILEY_CODE'	=> addslashes($row['code']),
				'SMILEY_IMG'	=> $root_path . $config['smilies_path'] . '/' . $row['smiley_url'],
				'SMILEY_WIDTH'	=> $row['smiley_width'],
				'SMILEY_HEIGHT'	=> $row['smiley_height'],
				'SMILEY_DESC'	=> $row['emotion'])
			);
		}
	}

	/**
	* This event is called after the smilies are populated
	*
	* @event core.generate_smilies_after
	* @var	string	mode			Mode of the smilies: window|inline
	* @var	int		forum_id		The forum ID we are currently in
	* @var	bool	display_link	Shall we display the "more smilies" link?
	* @since 3.1.0-a1
	*/
	$vars = array('mode', 'forum_id', 'display_link');
	extract($phpbb_dispatcher->trigger_event('core.generate_smilies_after', compact($vars)));

	if ($mode == 'inline' && $display_link)
	{
		$template->assign_vars(array(
			'S_SHOW_SMILEY_LINK' 	=> true,
			'U_MORE_SMILIES' 		=> $base_url,
		));
	}

	if ($mode == 'window')
	{
		page_footer();
	}
}

/**
* Update last post information
* Should be used instead of sync() if only the last post information are out of sync... faster
*
* @param	string	$type				Can be forum|topic
* @param	mixed	$ids				topic/forum ids
* @param	bool	$return_update_sql	true: SQL query shall be returned, false: execute SQL
*/
function update_post_information($type, $ids, $return_update_sql = false)
{
	global $db;

	if (empty($ids))
	{
		return;
	}
	if (!is_array($ids))
	{
		$ids = array($ids);
	}

	$update_sql = $empty_forums = $not_empty_forums = array();

	if ($type != 'topic')
	{
		$topic_join = ', ' . TOPICS_TABLE . ' t';
		$topic_condition = 'AND t.topic_id = p.topic_id AND t.topic_visibility = ' . ITEM_APPROVED;
	}
	else
	{
		$topic_join = '';
		$topic_condition = '';
	}

	if (sizeof($ids) == 1)
	{
		$sql = 'SELECT MAX(p.post_id) as last_post_id
			FROM ' . POSTS_TABLE . " p $topic_join
			WHERE " . $db->sql_in_set('p.' . $type . '_id', $ids) . "
				$topic_condition
				AND p.post_visibility = " . ITEM_APPROVED;
	}
	else
	{
		$sql = 'SELECT p.' . $type . '_id, MAX(p.post_id) as last_post_id
			FROM ' . POSTS_TABLE . " p $topic_join
			WHERE " . $db->sql_in_set('p.' . $type . '_id', $ids) . "
				$topic_condition
				AND p.post_visibility = " . ITEM_APPROVED . "
			GROUP BY p.{$type}_id";
	}
	$result = $db->sql_query($sql);

	$last_post_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if (sizeof($ids) == 1)
		{
			$row[$type . '_id'] = $ids[0];
		}

		if ($type == 'forum')
		{
			$not_empty_forums[] = $row['forum_id'];

			if (empty($row['last_post_id']))
			{
				$empty_forums[] = $row['forum_id'];
			}
		}

		$last_post_ids[] = $row['last_post_id'];
	}
	$db->sql_freeresult($result);

	if ($type == 'forum')
	{
		$empty_forums = array_merge($empty_forums, array_diff($ids, $not_empty_forums));

		foreach ($empty_forums as $void => $forum_id)
		{
			$update_sql[$forum_id][] = 'forum_last_post_id = 0';
			$update_sql[$forum_id][] = "forum_last_post_subject = ''";
			$update_sql[$forum_id][] = 'forum_last_post_time = 0';
			$update_sql[$forum_id][] = 'forum_last_poster_id = 0';
			$update_sql[$forum_id][] = "forum_last_poster_name = ''";
			$update_sql[$forum_id][] = "forum_last_poster_colour = ''";
		}
	}

	if (sizeof($last_post_ids))
	{
		$sql = 'SELECT p.' . $type . '_id, p.post_id, p.post_subject, p.post_time, p.poster_id, p.post_username, u.user_id, u.username, u.user_colour
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.poster_id = u.user_id
				AND ' . $db->sql_in_set('p.post_id', $last_post_ids);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$update_sql[$row["{$type}_id"]][] = $type . '_last_post_id = ' . (int) $row['post_id'];
			$update_sql[$row["{$type}_id"]][] = "{$type}_last_post_subject = '" . $db->sql_escape($row['post_subject']) . "'";
			$update_sql[$row["{$type}_id"]][] = $type . '_last_post_time = ' . (int) $row['post_time'];
			$update_sql[$row["{$type}_id"]][] = $type . '_last_poster_id = ' . (int) $row['poster_id'];
			$update_sql[$row["{$type}_id"]][] = "{$type}_last_poster_colour = '" . $db->sql_escape($row['user_colour']) . "'";
			$update_sql[$row["{$type}_id"]][] = "{$type}_last_poster_name = '" . (($row['poster_id'] == ANONYMOUS) ? $db->sql_escape($row['post_username']) : $db->sql_escape($row['username'])) . "'";
		}
		$db->sql_freeresult($result);
	}
	unset($empty_forums, $ids, $last_post_ids);

	if ($return_update_sql || !sizeof($update_sql))
	{
		return $update_sql;
	}

	$table = ($type == 'forum') ? FORUMS_TABLE : TOPICS_TABLE;

	foreach ($update_sql as $update_id => $update_sql_ary)
	{
		$sql = "UPDATE $table
			SET " . implode(', ', $update_sql_ary) . "
			WHERE {$type}_id = $update_id";
		$db->sql_query($sql);
	}

	return;
}

/**
* Generate Topic Icons for display
*/
function posting_gen_topic_icons($mode, $icon_id)
{
	global $phpbb_root_path, $config, $template, $cache;

	// Grab icons
	$icons = $cache->obtain_icons();

	if (!$icon_id)
	{
		$template->assign_var('S_NO_ICON_CHECKED', ' checked="checked"');
	}

	if (sizeof($icons))
	{
		$root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $phpbb_root_path;

		foreach ($icons as $id => $data)
		{
			if ($data['display'])
			{
				$template->assign_block_vars('topic_icon', array(
					'ICON_ID'		=> $id,
					'ICON_IMG'		=> $root_path . $config['icons_path'] . '/' . $data['img'],
					'ICON_WIDTH'	=> $data['width'],
					'ICON_HEIGHT'	=> $data['height'],

					'S_CHECKED'			=> ($id == $icon_id) ? true : false,
					'S_ICON_CHECKED'	=> ($id == $icon_id) ? ' checked="checked"' : '')
				);
			}
		}

		return true;
	}

	return false;
}

/**
* Build topic types able to be selected
*/
function posting_gen_topic_types($forum_id, $cur_topic_type = POST_NORMAL)
{
	global $auth, $user, $template, $topic_type;

	$toggle = false;

	$topic_types = array(
		'sticky'	=> array('const' => POST_STICKY, 'lang' => 'POST_STICKY'),
		'announce'	=> array('const' => POST_ANNOUNCE, 'lang' => 'POST_ANNOUNCEMENT'),
		'global'	=> array('const' => POST_GLOBAL, 'lang' => 'POST_GLOBAL')
	);

	$topic_type_array = array();

	foreach ($topic_types as $auth_key => $topic_value)
	{
		// We do not have a special post global announcement permission
		$auth_key = ($auth_key == 'global') ? 'announce' : $auth_key;

		if ($auth->acl_get('f_' . $auth_key, $forum_id))
		{
			$toggle = true;

			$topic_type_array[] = array(
				'VALUE'			=> $topic_value['const'],
				'S_CHECKED'		=> ($cur_topic_type == $topic_value['const']) ? ' checked="checked"' : '',
				'L_TOPIC_TYPE'	=> $user->lang[$topic_value['lang']]
			);
		}
	}

	if ($toggle)
	{
		$topic_type_array = array_merge(array(0 => array(
			'VALUE'			=> POST_NORMAL,
			'S_CHECKED'		=> ($cur_topic_type == POST_NORMAL) ? ' checked="checked"' : '',
			'L_TOPIC_TYPE'	=> $user->lang['POST_NORMAL'])),

			$topic_type_array
		);

		foreach ($topic_type_array as $array)
		{
			$template->assign_block_vars('topic_type', $array);
		}

		$template->assign_vars(array(
			'S_TOPIC_TYPE_STICKY'	=> ($auth->acl_get('f_sticky', $forum_id)),
			'S_TOPIC_TYPE_ANNOUNCE'	=> ($auth->acl_get('f_announce', $forum_id)))
		);
	}

	return $toggle;
}

//
// Attachment related functions
//

/**
* Upload Attachment - filedata is generated here
* Uses upload class
*
* @param string			$form_name		The form name of the file upload input
* @param int			$forum_id		The id of the forum
* @param bool			$local			Whether the file is local or not
* @param string			$local_storage	The path to the local file
* @param bool			$is_message		Whether it is a PM or not
* @param \filespec		$local_filedata	A filespec object created for the local file
* @param \phpbb\mimetype\guesser	$mimetype_guesser	The mimetype guesser object if used
* @param \phpbb\plupload\plupload	$plupload		The plupload object if one is being used
*
* @return object filespec
*/
function upload_attachment($form_name, $forum_id, $local = false, $local_storage = '', $is_message = false, $local_filedata = false, \phpbb\mimetype\guesser $mimetype_guesser = null, \phpbb\plupload\plupload $plupload = null)
{
	global $auth, $user, $config, $db, $cache;
	global $phpbb_root_path, $phpEx, $phpbb_dispatcher;

	$filedata = array(
		'error'	=> array()
	);

	include_once($phpbb_root_path . 'includes/functions_upload.' . $phpEx);
	$upload = new fileupload();

	if ($config['check_attachment_content'] && isset($config['mime_triggers']))
	{
		$upload->set_disallowed_content(explode('|', $config['mime_triggers']));
	}
	else if (!$config['check_attachment_content'])
	{
		$upload->set_disallowed_content(array());
	}

	$filedata['post_attach'] = $local || $upload->is_valid($form_name);

	if (!$filedata['post_attach'])
	{
		$filedata['error'][] = $user->lang['NO_UPLOAD_FORM_FOUND'];
		return $filedata;
	}

	$extensions = $cache->obtain_attach_extensions((($is_message) ? false : (int) $forum_id));
	$upload->set_allowed_extensions(array_keys($extensions['_allowed_']));

	$file = ($local) ? $upload->local_upload($local_storage, $local_filedata, $mimetype_guesser) : $upload->form_upload($form_name, $mimetype_guesser, $plupload);

	if ($file->init_error)
	{
		$filedata['post_attach'] = false;
		return $filedata;
	}

	// Whether the uploaded file is in the image category
	$is_image = (isset($extensions[$file->get('extension')]['display_cat'])) ? $extensions[$file->get('extension')]['display_cat'] == ATTACHMENT_CATEGORY_IMAGE : false;

	if (!$auth->acl_get('a_') && !$auth->acl_get('m_', $forum_id))
	{
		// Check Image Size, if it is an image
		if ($is_image)
		{
			$file->upload->set_allowed_dimensions(0, 0, $config['img_max_width'], $config['img_max_height']);
		}

		// Admins and mods are allowed to exceed the allowed filesize
		if (!empty($extensions[$file->get('extension')]['max_filesize']))
		{
			$allowed_filesize = $extensions[$file->get('extension')]['max_filesize'];
		}
		else
		{
			$allowed_filesize = ($is_message) ? $config['max_filesize_pm'] : $config['max_filesize'];
		}

		$file->upload->set_max_filesize($allowed_filesize);
	}

	$file->clean_filename('unique', $user->data['user_id'] . '_');

	// Are we uploading an image *and* this image being within the image category?
	// Only then perform additional image checks.
	$file->move_file($config['upload_path'], false, !$is_image);

	// Do we have to create a thumbnail?
	$filedata['thumbnail'] = ($is_image && $config['img_create_thumbnail']) ? 1 : 0;

	if (sizeof($file->error))
	{
		$file->remove();
		$filedata['error'] = array_merge($filedata['error'], $file->error);
		$filedata['post_attach'] = false;

		return $filedata;
	}

	// Make sure the image category only holds valid images...
	if ($is_image && !$file->is_image())
	{
		$file->remove();

		if ($plupload && $plupload->is_active())
		{
			$plupload->emit_error(104, 'ATTACHED_IMAGE_NOT_IMAGE');
		}

		// If this error occurs a user tried to exploit an IE Bug by renaming extensions
		// Since the image category is displaying content inline we need to catch this.
		trigger_error($user->lang['ATTACHED_IMAGE_NOT_IMAGE']);
	}

	$filedata['filesize'] = $file->get('filesize');
	$filedata['mimetype'] = $file->get('mimetype');
	$filedata['extension'] = $file->get('extension');
	$filedata['physical_filename'] = $file->get('realname');
	$filedata['real_filename'] = $file->get('uploadname');
	$filedata['filetime'] = time();

	/**
	* Event to modify uploaded file before submit to the post
	*
	* @event core.modify_uploaded_file
	* @var	array	filedata	Array containing uploaded file data
	* @var	bool	is_image	Flag indicating if the file is an image
	* @since 3.1.0-RC3
	*/
	$vars = array(
		'filedata',
		'is_image',
	);
	extract($phpbb_dispatcher->trigger_event('core.modify_uploaded_file', compact($vars)));

	// Check our complete quota
	if ($config['attachment_quota'])
	{
		if ($config['upload_dir_size'] + $file->get('filesize') > $config['attachment_quota'])
		{
			$filedata['error'][] = $user->lang['ATTACH_QUOTA_REACHED'];
			$filedata['post_attach'] = false;

			$file->remove();

			return $filedata;
		}
	}

	// Check free disk space
	if ($free_space = @disk_free_space($phpbb_root_path . $config['upload_path']))
	{
		if ($free_space <= $file->get('filesize'))
		{
			if ($auth->acl_get('a_'))
			{
				$filedata['error'][] = $user->lang['ATTACH_DISK_FULL'];
			}
			else
			{
				$filedata['error'][] = $user->lang['ATTACH_QUOTA_REACHED'];
			}
			$filedata['post_attach'] = false;

			$file->remove();

			return $filedata;
		}
	}

	// Create Thumbnail
	if ($filedata['thumbnail'])
	{
		$source = $file->get('destination_file');
		$destination = $file->get('destination_path') . '/thumb_' . $file->get('realname');

		if (!create_thumbnail($source, $destination, $file->get('mimetype')))
		{
			$filedata['thumbnail'] = 0;
		}
	}

	return $filedata;
}

/**
* Calculate the needed size for Thumbnail
*/
function get_img_size_format($width, $height)
{
	global $config;

	// Maximum Width the Image can take
	$max_width = ($config['img_max_thumb_width']) ? $config['img_max_thumb_width'] : 400;

	if ($width > $height)
	{
		return array(
			round($width * ($max_width / $width)),
			round($height * ($max_width / $width))
		);
	}
	else
	{
		return array(
			round($width * ($max_width / $height)),
			round($height * ($max_width / $height))
		);
	}
}

/**
* Return supported image types
*/
function get_supported_image_types($type = false)
{
	if (@extension_loaded('gd'))
	{
		$format = imagetypes();
		$new_type = 0;

		if ($type !== false)
		{
			// Type is one of the IMAGETYPE constants - it is fetched from getimagesize()
			switch ($type)
			{
				// GIF
				case IMAGETYPE_GIF:
					$new_type = ($format & IMG_GIF) ? IMG_GIF : false;
				break;

				// JPG, JPC, JP2
				case IMAGETYPE_JPEG:
				case IMAGETYPE_JPC:
				case IMAGETYPE_JPEG2000:
				case IMAGETYPE_JP2:
				case IMAGETYPE_JPX:
				case IMAGETYPE_JB2:
					$new_type = ($format & IMG_JPG) ? IMG_JPG : false;
				break;

				// PNG
				case IMAGETYPE_PNG:
					$new_type = ($format & IMG_PNG) ? IMG_PNG : false;
				break;

				// WBMP
				case IMAGETYPE_WBMP:
					$new_type = ($format & IMG_WBMP) ? IMG_WBMP : false;
				break;
			}
		}
		else
		{
			$new_type = array();
			$go_through_types = array(IMG_GIF, IMG_JPG, IMG_PNG, IMG_WBMP);

			foreach ($go_through_types as $check_type)
			{
				if ($format & $check_type)
				{
					$new_type[] = $check_type;
				}
			}
		}

		return array(
			'gd'		=> ($new_type) ? true : false,
			'format'	=> $new_type,
			'version'	=> (function_exists('imagecreatetruecolor')) ? 2 : 1
		);
	}

	return array('gd' => false);
}

/**
* Create Thumbnail
*/
function create_thumbnail($source, $destination, $mimetype)
{
	global $config;

	$min_filesize = (int) $config['img_min_thumb_filesize'];
	$img_filesize = (file_exists($source)) ? @filesize($source) : false;

	if (!$img_filesize || $img_filesize <= $min_filesize)
	{
		return false;
	}

	$dimension = @getimagesize($source);

	if ($dimension === false)
	{
		return false;
	}

	list($width, $height, $type, ) = $dimension;

	if (empty($width) || empty($height))
	{
		return false;
	}

	list($new_width, $new_height) = get_img_size_format($width, $height);

	// Do not create a thumbnail if the resulting width/height is bigger than the original one
	if ($new_width >= $width && $new_height >= $height)
	{
		return false;
	}

	$used_imagick = false;

	// Only use imagemagick if defined and the passthru function not disabled
	if ($config['img_imagick'] && function_exists('passthru'))
	{
		if (substr($config['img_imagick'], -1) !== '/')
		{
			$config['img_imagick'] .= '/';
		}

		@passthru(escapeshellcmd($config['img_imagick']) . 'convert' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '') . ' -quality 85 -geometry ' . $new_width . 'x' . $new_height . ' "' . str_replace('\\', '/', $source) . '" "' . str_replace('\\', '/', $destination) . '"');

		if (file_exists($destination))
		{
			$used_imagick = true;
		}
	}

	if (!$used_imagick)
	{
		$type = get_supported_image_types($type);

		if ($type['gd'])
		{
			// If the type is not supported, we are not able to create a thumbnail
			if ($type['format'] === false)
			{
				return false;
			}

			switch ($type['format'])
			{
				case IMG_GIF:
					$image = @imagecreatefromgif($source);
				break;

				case IMG_JPG:
					@ini_set('gd.jpeg_ignore_warning', 1);
					$image = @imagecreatefromjpeg($source);
				break;

				case IMG_PNG:
					$image = @imagecreatefrompng($source);
				break;

				case IMG_WBMP:
					$image = @imagecreatefromwbmp($source);
				break;
			}

			if (empty($image))
			{
				return false;
			}

			if ($type['version'] == 1)
			{
				$new_image = imagecreate($new_width, $new_height);

				if ($new_image === false)
				{
					return false;
				}

				imagecopyresized($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			}
			else
			{
				$new_image = imagecreatetruecolor($new_width, $new_height);

				if ($new_image === false)
				{
					return false;
				}

				// Preserve alpha transparency (png for example)
				@imagealphablending($new_image, false);
				@imagesavealpha($new_image, true);

				imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			}

			// If we are in safe mode create the destination file prior to using the gd functions to circumvent a PHP bug
			if (@ini_get('safe_mode') || @strtolower(ini_get('safe_mode')) == 'on')
			{
				@touch($destination);
			}

			switch ($type['format'])
			{
				case IMG_GIF:
					imagegif($new_image, $destination);
				break;

				case IMG_JPG:
					imagejpeg($new_image, $destination, 90);
				break;

				case IMG_PNG:
					imagepng($new_image, $destination);
				break;

				case IMG_WBMP:
					imagewbmp($new_image, $destination);
				break;
			}

			imagedestroy($new_image);
		}
		else
		{
			return false;
		}
	}

	if (!file_exists($destination))
	{
		return false;
	}

	phpbb_chmod($destination, CHMOD_READ | CHMOD_WRITE);

	return true;
}

/**
* Assign Inline attachments (build option fields)
*/
function posting_gen_inline_attachments(&$attachment_data)
{
	global $template;

	if (sizeof($attachment_data))
	{
		$s_inline_attachment_options = '';

		foreach ($attachment_data as $i => $attachment)
		{
			$s_inline_attachment_options .= '<option value="' . $i . '">' . utf8_basename($attachment['real_filename']) . '</option>';
		}

		$template->assign_var('S_INLINE_ATTACHMENT_OPTIONS', $s_inline_attachment_options);

		return true;
	}

	return false;
}

/**
* Generate inline attachment entry
*/
function posting_gen_attachment_entry($attachment_data, &$filename_data, $show_attach_box = true)
{
	global $template, $config, $phpbb_root_path, $phpEx, $user;

	// Some default template variables
	$template->assign_vars(array(
		'S_SHOW_ATTACH_BOX'	=> $show_attach_box,
		'S_HAS_ATTACHMENTS'	=> sizeof($attachment_data),
		'FILESIZE'			=> $config['max_filesize'],
		'FILE_COMMENT'		=> (isset($filename_data['filecomment'])) ? $filename_data['filecomment'] : '',
	));

	if (sizeof($attachment_data))
	{
		// We display the posted attachments within the desired order.
		($config['display_order']) ? krsort($attachment_data) : ksort($attachment_data);

		foreach ($attachment_data as $count => $attach_row)
		{
			$hidden = '';
			$attach_row['real_filename'] = utf8_basename($attach_row['real_filename']);

			foreach ($attach_row as $key => $value)
			{
				$hidden .= '<input type="hidden" name="attachment_data[' . $count . '][' . $key . ']" value="' . $value . '" />';
			}

			$download_link = append_sid("{$phpbb_root_path}download/file.$phpEx", 'mode=view&amp;id=' . (int) $attach_row['attach_id'], true, ($attach_row['is_orphan']) ? $user->session_id : false);

			$template->assign_block_vars('attach_row', array(
				'FILENAME'			=> utf8_basename($attach_row['real_filename']),
				'A_FILENAME'		=> addslashes(utf8_basename($attach_row['real_filename'])),
				'FILE_COMMENT'		=> $attach_row['attach_comment'],
				'ATTACH_ID'			=> $attach_row['attach_id'],
				'S_IS_ORPHAN'		=> $attach_row['is_orphan'],
				'ASSOC_INDEX'		=> $count,
				'FILESIZE'			=> get_formatted_filesize($attach_row['filesize']),

				'U_VIEW_ATTACHMENT'	=> $download_link,
				'S_HIDDEN'			=> $hidden)
			);
		}
	}

	return sizeof($attachment_data);
}

//
// General Post functions
//

/**
* Load Drafts
*/
function load_drafts($topic_id = 0, $forum_id = 0, $id = 0, $pm_action = '', $msg_id = 0)
{
	global $user, $db, $template, $auth;
	global $phpbb_root_path, $phpbb_dispatcher, $phpEx;

	$topic_ids = $forum_ids = $draft_rows = array();

	// Load those drafts not connected to forums/topics
	// If forum_id == 0 AND topic_id == 0 then this is a PM draft
	if (!$topic_id && !$forum_id)
	{
		$sql_and = ' AND d.forum_id = 0 AND d.topic_id = 0';
	}
	else
	{
		$sql_and = '';
		$sql_and .= ($forum_id) ? ' AND d.forum_id = ' . (int) $forum_id : '';
		$sql_and .= ($topic_id) ? ' AND d.topic_id = ' . (int) $topic_id : '';
	}

	$sql = 'SELECT d.*, f.forum_id, f.forum_name
		FROM ' . DRAFTS_TABLE . ' d
		LEFT JOIN ' . FORUMS_TABLE . ' f ON (f.forum_id = d.forum_id)
			WHERE d.user_id = ' . $user->data['user_id'] . "
			$sql_and
		ORDER BY d.save_time DESC";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['topic_id'])
		{
			$topic_ids[] = (int) $row['topic_id'];
		}
		$draft_rows[] = $row;
	}
	$db->sql_freeresult($result);

	if (!sizeof($draft_rows))
	{
		return;
	}

	$topic_rows = array();
	if (sizeof($topic_ids))
	{
		$sql = 'SELECT topic_id, forum_id, topic_title, topic_poster
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_id', array_unique($topic_ids));
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_rows[$row['topic_id']] = $row;
		}
		$db->sql_freeresult($result);
	}

	/**
	* Drafts found and their topics
	* Edit $draft_rows in order to add or remove drafts loaded
	*
	* @event core.load_drafts_draft_list_result
	* @var	array	draft_rows			The drafts query result. Includes its forum id and everything about the draft
	* @var	array	topic_ids			The list of topics got from the topics table
	* @var	array	topic_rows			The topics that draft_rows references
	* @since 3.1.0-RC3
	*/
	$vars = array('draft_rows', 'topic_ids', 'topic_rows');
	extract($phpbb_dispatcher->trigger_event('core.load_drafts_draft_list_result', compact($vars)));

	unset($topic_ids);

	$template->assign_var('S_SHOW_DRAFTS', true);

	foreach ($draft_rows as $draft)
	{
		$link_topic = $link_forum = $link_pm = false;
		$insert_url = $view_url = $title = '';

		if (isset($topic_rows[$draft['topic_id']])
			&& (
				($topic_rows[$draft['topic_id']]['forum_id'] && $auth->acl_get('f_read', $topic_rows[$draft['topic_id']]['forum_id']))
				||
				(!$topic_rows[$draft['topic_id']]['forum_id'] && $auth->acl_getf_global('f_read'))
			))
		{
			$topic_forum_id = ($topic_rows[$draft['topic_id']]['forum_id']) ? $topic_rows[$draft['topic_id']]['forum_id'] : $forum_id;

			$link_topic = true;
			$view_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $topic_forum_id . '&amp;t=' . $draft['topic_id']);
			$title = $topic_rows[$draft['topic_id']]['topic_title'];

			$insert_url = append_sid("{$phpbb_root_path}posting.$phpEx", 'f=' . $topic_forum_id . '&amp;t=' . $draft['topic_id'] . '&amp;mode=reply&amp;d=' . $draft['draft_id']);
		}
		else if ($draft['forum_id'] && $auth->acl_get('f_read', $draft['forum_id']))
		{
			$link_forum = true;
			$view_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $draft['forum_id']);
			$title = $draft['forum_name'];

			$insert_url = append_sid("{$phpbb_root_path}posting.$phpEx", 'f=' . $draft['forum_id'] . '&amp;mode=post&amp;d=' . $draft['draft_id']);
		}
		else
		{
			// Either display as PM draft if forum_id and topic_id are empty or if access to the forums has been denied afterwards...
			$link_pm = true;
			$insert_url = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=$id&amp;mode=compose&amp;d={$draft['draft_id']}" . (($pm_action) ? "&amp;action=$pm_action" : '') . (($msg_id) ? "&amp;p=$msg_id" : ''));
		}

		$template->assign_block_vars('draftrow', array(
			'DRAFT_ID'		=> $draft['draft_id'],
			'DATE'			=> $user->format_date($draft['save_time']),
			'DRAFT_SUBJECT'	=> $draft['draft_subject'],

			'TITLE'			=> $title,
			'U_VIEW'		=> $view_url,
			'U_INSERT'		=> $insert_url,

			'S_LINK_PM'		=> $link_pm,
			'S_LINK_TOPIC'	=> $link_topic,
			'S_LINK_FORUM'	=> $link_forum)
		);
	}
}

/**
* Topic Review
*/
function topic_review($topic_id, $forum_id, $mode = 'topic_review', $cur_post_id = 0, $show_quote_button = true)
{
	global $user, $auth, $db, $template, $cache;
	global $config, $phpbb_root_path, $phpEx, $phpbb_container, $phpbb_dispatcher;

	$phpbb_content_visibility = $phpbb_container->get('content.visibility');
	$sql_sort = ($mode == 'post_review') ? 'ASC' : 'DESC';

	// Go ahead and pull all data for this topic
	$sql = 'SELECT p.post_id
		FROM ' . POSTS_TABLE . ' p' . "
		WHERE p.topic_id = $topic_id
			AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id, 'p.') . '
			' . (($mode == 'post_review') ? " AND p.post_id > $cur_post_id" : '') . '
			' . (($mode == 'post_review_edit') ? " AND p.post_id = $cur_post_id" : '') . '
		ORDER BY p.post_time ' . $sql_sort . ', p.post_id ' . $sql_sort;
	$result = $db->sql_query_limit($sql, $config['posts_per_page']);

	$post_list = array();

	while ($row = $db->sql_fetchrow($result))
	{
		$post_list[] = $row['post_id'];
	}

	$db->sql_freeresult($result);

	if (!sizeof($post_list))
	{
		return false;
	}

	// Handle 'post_review_edit' like 'post_review' from now on
	if ($mode == 'post_review_edit')
	{
		$mode = 'post_review';
	}

	$sql_ary = array(
		'SELECT'	=> 'u.username, u.user_id, u.user_colour, p.*, z.friend, z.foe',

		'FROM'		=> array(
			USERS_TABLE		=> 'u',
			POSTS_TABLE		=> 'p',
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(ZEBRA_TABLE => 'z'),
				'ON'	=> 'z.user_id = ' . $user->data['user_id'] . ' AND z.zebra_id = p.poster_id',
			),
		),

		'WHERE'		=> $db->sql_in_set('p.post_id', $post_list) . '
			AND u.user_id = p.poster_id',
	);

	$sql = $db->sql_build_query('SELECT', $sql_ary);
	$result = $db->sql_query($sql);

	$rowset = array();
	$has_attachments = false;
	while ($row = $db->sql_fetchrow($result))
	{
		$rowset[$row['post_id']] = $row;

		if ($row['post_attachment'])
		{
			$has_attachments = true;
		}
	}
	$db->sql_freeresult($result);

	// Grab extensions
	$extensions = $attachments = array();
	if ($has_attachments && $auth->acl_get('u_download') && $auth->acl_get('f_download', $forum_id))
	{
		$extensions = $cache->obtain_attach_extensions($forum_id);

		// Get attachments...
		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_msg_id', $post_list) . '
				AND in_message = 0
			ORDER BY filetime DESC, post_msg_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$attachments[$row['post_msg_id']][] = $row;
		}
		$db->sql_freeresult($result);
	}

	for ($i = 0, $end = sizeof($post_list); $i < $end; ++$i)
	{
		// A non-existing rowset only happens if there was no user present for the entered poster_id
		// This could be a broken posts table.
		if (!isset($rowset[$post_list[$i]]))
		{
			continue;
		}

		$row = $rowset[$post_list[$i]];

		$poster_id		= $row['user_id'];
		$post_subject	= $row['post_subject'];

		$decoded_message = false;

		if ($show_quote_button && $auth->acl_get('f_reply', $forum_id))
		{
			$decoded_message = censor_text($row['post_text']);
			decode_message($decoded_message, $row['bbcode_uid']);

			$decoded_message = bbcode_nl2br($decoded_message);
		}

		$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0);
		$parse_flags |= ($row['enable_smilies'] ? OPTION_FLAG_SMILIES : 0);
		$message = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);

		if (!empty($attachments[$row['post_id']]))
		{
			$update_count = array();
			parse_attachments($forum_id, $message, $attachments[$row['post_id']], $update_count);
		}

		$post_subject = censor_text($post_subject);

		$post_anchor = ($mode == 'post_review') ? 'ppr' . $row['post_id'] : 'pr' . $row['post_id'];
		$u_show_post = append_sid($phpbb_root_path . 'viewtopic.' . $phpEx, "f=$forum_id&amp;t=$topic_id&amp;p={$row['post_id']}&amp;view=show#p{$row['post_id']}");

		$post_row = array(
			'POST_AUTHOR_FULL'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
			'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
			'POST_AUTHOR'			=> get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
			'U_POST_AUTHOR'			=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),

			'S_HAS_ATTACHMENTS'	=> (!empty($attachments[$row['post_id']])) ? true : false,
			'S_FRIEND'			=> ($row['friend']) ? true : false,
			'S_IGNORE_POST'		=> ($row['foe']) ? true : false,
			'L_IGNORE_POST'		=> ($row['foe']) ? sprintf($user->lang['POST_BY_FOE'], get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']), "<a href=\"{$u_show_post}\" onclick=\"phpbb.toggleDisplay('{$post_anchor}', 1); return false;\">", '</a>') : '',

			'POST_SUBJECT'		=> $post_subject,
			'MINI_POST_IMG'		=> $user->img('icon_post_target', $user->lang['POST']),
			'POST_DATE'			=> $user->format_date($row['post_time']),
			'MESSAGE'			=> $message,
			'DECODED_MESSAGE'	=> $decoded_message,
			'POST_ID'			=> $row['post_id'],
			'U_MINI_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $row['post_id']) . '#p' . $row['post_id'],
			'U_MCP_DETAILS'		=> ($auth->acl_get('m_info', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main&amp;mode=post_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',
			'POSTER_QUOTE'		=> ($show_quote_button && $auth->acl_get('f_reply', $forum_id)) ? addslashes(get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['post_username'])) : '',
		);

		$current_row_number = $i;

		/**
		* Event to modify the template data block for topic reviews
		*
		* @event core.topic_review_modify_row
		* @var	string	mode				The review mode
		* @var	int		topic_id			The topic that is being reviewed
		* @var	int		forum_id			The topic's forum
		* @var	int		cur_post_id			Post offset id
		* @var	int		current_row_number	Number of the current row being iterated
		* @var	array	post_row			Template block array of the current post
		* @var	array	row					Array with original post and user data
		* @since 3.1.4-RC1
		*/
		$vars = array(
			'mode',
			'topic_id',
			'forum_id',
			'cur_post_id',
			'current_row_number',
			'post_row',
			'row',
		);
		extract($phpbb_dispatcher->trigger_event('core.topic_review_modify_row', compact($vars)));

		$template->assign_block_vars($mode . '_row', $post_row);

		// Display not already displayed Attachments for this post, we already parsed them. ;)
		if (!empty($attachments[$row['post_id']]))
		{
			foreach ($attachments[$row['post_id']] as $attachment)
			{
				$template->assign_block_vars($mode . '_row.attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment)
				);
			}
		}

		unset($rowset[$post_list[$i]]);
	}

	if ($mode == 'topic_review')
	{
		$template->assign_var('QUOTE_IMG', $user->img('icon_post_quote', $user->lang['REPLY_WITH_QUOTE']));
	}

	return true;
}

//
// Post handling functions
//

/**
* Delete Post
*/
function delete_post($forum_id, $topic_id, $post_id, &$data, $is_soft = false, $softdelete_reason = '')
{
	global $db, $user, $auth, $phpbb_container;
	global $config, $phpEx, $phpbb_root_path;

	// Specify our post mode
	$post_mode = 'delete';
	if (($data['topic_first_post_id'] === $data['topic_last_post_id']) && ($data['topic_posts_approved'] + $data['topic_posts_unapproved'] + $data['topic_posts_softdeleted'] == 1))
	{
		$post_mode = 'delete_topic';
	}
	else if ($data['topic_first_post_id'] == $post_id)
	{
		$post_mode = 'delete_first_post';
	}
	else if ($data['topic_last_post_id'] == $post_id)
	{
		$post_mode = 'delete_last_post';
	}
	$sql_data = array();
	$next_post_id = false;

	include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

	$db->sql_transaction('begin');

	// we must make sure to update forums that contain the shadow'd topic
	if ($post_mode == 'delete_topic')
	{
		$shadow_forum_ids = array();

		$sql = 'SELECT forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_moved_id', $topic_id);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (!isset($shadow_forum_ids[(int) $row['forum_id']]))
			{
				$shadow_forum_ids[(int) $row['forum_id']] = 1;
			}
			else
			{
				$shadow_forum_ids[(int) $row['forum_id']]++;
			}
		}
		$db->sql_freeresult($result);
	}

	$phpbb_content_visibility = $phpbb_container->get('content.visibility');

	// (Soft) delete the post
	if ($is_soft && ($post_mode != 'delete_topic'))
	{
		$phpbb_content_visibility->set_post_visibility(ITEM_DELETED, $post_id, $topic_id, $forum_id, $user->data['user_id'], time(), $softdelete_reason, ($data['topic_first_post_id'] == $post_id), ($data['topic_last_post_id'] == $post_id));
	}
	else if (!$is_soft)
	{
		if (!delete_posts('post_id', array($post_id), false, false, false))
		{
			// Try to delete topic, we may had an previous error causing inconsistency
			if ($post_mode == 'delete_topic')
			{
				delete_topics('topic_id', array($topic_id), false);
			}
			trigger_error('ALREADY_DELETED');
		}
	}

	$db->sql_transaction('commit');

	// Collect the necessary information for updating the tables
	$sql_data[FORUMS_TABLE] = $sql_data[TOPICS_TABLE] = '';
	switch ($post_mode)
	{
		case 'delete_topic':

			foreach ($shadow_forum_ids as $updated_forum => $topic_count)
			{
				// counting is fun! we only have to do sizeof($forum_ids) number of queries,
				// even if the topic is moved back to where its shadow lives (we count how many times it is in a forum)
				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET forum_topics_approved = forum_topics_approved - ' . $topic_count . '
					WHERE forum_id = ' . $updated_forum;
				$db->sql_query($sql);
				update_post_information('forum', $updated_forum);
			}

			if ($is_soft)
			{
				$topic_row = array();
				$phpbb_content_visibility->set_topic_visibility(ITEM_DELETED, $topic_id, $forum_id, $user->data['user_id'], time(), $softdelete_reason);
			}
			else
			{
				delete_topics('topic_id', array($topic_id), false);

				$phpbb_content_visibility->remove_topic_from_statistic($data, $sql_data);

				$update_sql = update_post_information('forum', $forum_id, true);
				if (sizeof($update_sql))
				{
					$sql_data[FORUMS_TABLE] .= ($sql_data[FORUMS_TABLE]) ? ', ' : '';
					$sql_data[FORUMS_TABLE] .= implode(', ', $update_sql[$forum_id]);
				}
			}

		break;

		case 'delete_first_post':
			$sql = 'SELECT p.post_id, p.poster_id, p.post_time, p.post_username, u.username, u.user_colour
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
				WHERE p.topic_id = $topic_id
					AND p.poster_id = u.user_id
					AND p.post_visibility = " . ITEM_APPROVED . '
				ORDER BY p.post_time ASC, p.post_id ASC';
			$result = $db->sql_query_limit($sql, 1);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				// No approved post, so the first is a not-approved post (unapproved or soft deleted)
				$sql = 'SELECT p.post_id, p.poster_id, p.post_time, p.post_username, u.username, u.user_colour
					FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
					WHERE p.topic_id = $topic_id
						AND p.poster_id = u.user_id
					ORDER BY p.post_time ASC, p.post_id ASC";
				$result = $db->sql_query_limit($sql, 1);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
			}

			$next_post_id = (int) $row['post_id'];

			$sql_data[TOPICS_TABLE] = $db->sql_build_array('UPDATE', array(
				'topic_poster'				=> (int) $row['poster_id'],
				'topic_first_post_id'		=> (int) $row['post_id'],
				'topic_first_poster_colour'	=> $row['user_colour'],
				'topic_first_poster_name'	=> ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'],
				'topic_time'				=> (int) $row['post_time'],
			));
		break;

		case 'delete_last_post':
			if (!$is_soft)
			{
				// Update last post information when hard deleting. Soft delete already did that by itself.
				$update_sql = update_post_information('forum', $forum_id, true);
				if (sizeof($update_sql))
				{
					$sql_data[FORUMS_TABLE] = (($sql_data[FORUMS_TABLE]) ? $sql_data[FORUMS_TABLE] . ', ' : '') . implode(', ', $update_sql[$forum_id]);
				}

				$sql_data[TOPICS_TABLE] = (($sql_data[TOPICS_TABLE]) ? $sql_data[TOPICS_TABLE] . ', ' : '') . 'topic_bumped = 0, topic_bumper = 0';

				$update_sql = update_post_information('topic', $topic_id, true);
				if (!empty($update_sql))
				{
					$sql_data[TOPICS_TABLE] .= ', ' . implode(', ', $update_sql[$topic_id]);
					$next_post_id = (int) str_replace('topic_last_post_id = ', '', $update_sql[$topic_id][0]);
				}
			}

			if (!$next_post_id)
			{
				$sql = 'SELECT MAX(post_id) as last_post_id
					FROM ' . POSTS_TABLE . "
					WHERE topic_id = $topic_id
						AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id);
				$result = $db->sql_query($sql);
				$next_post_id = (int) $db->sql_fetchfield('last_post_id');
				$db->sql_freeresult($result);
			}
		break;

		case 'delete':
			$sql = 'SELECT post_id
				FROM ' . POSTS_TABLE . "
				WHERE topic_id = $topic_id
					AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id) . '
					AND post_time > ' . $data['post_time'] . '
				ORDER BY post_time ASC, post_id ASC';
			$result = $db->sql_query_limit($sql, 1);
			$next_post_id = (int) $db->sql_fetchfield('post_id');
			$db->sql_freeresult($result);
		break;
	}

	if (($post_mode == 'delete') || ($post_mode == 'delete_last_post') || ($post_mode == 'delete_first_post'))
	{
		if (!$is_soft)
		{
			$phpbb_content_visibility->remove_post_from_statistic($data, $sql_data);
		}

		$sql = 'SELECT 1 AS has_attachments
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query_limit($sql, 1);
		$has_attachments = (int) $db->sql_fetchfield('has_attachments');
		$db->sql_freeresult($result);

		if (!$has_attachments)
		{
			$sql_data[TOPICS_TABLE] = (($sql_data[TOPICS_TABLE]) ? $sql_data[TOPICS_TABLE] . ', ' : '') . 'topic_attachment = 0';
		}
	}

	$db->sql_transaction('begin');

	$where_sql = array(
		FORUMS_TABLE	=> "forum_id = $forum_id",
		TOPICS_TABLE	=> "topic_id = $topic_id",
		USERS_TABLE		=> 'user_id = ' . $data['poster_id'],
	);

	foreach ($sql_data as $table => $update_sql)
	{
		if ($update_sql)
		{
			$db->sql_query("UPDATE $table SET $update_sql WHERE " . $where_sql[$table]);
		}
	}

	// Adjust posted info for this user by looking for a post by him/her within this topic...
	if ($post_mode != 'delete_topic' && $config['load_db_track'] && $data['poster_id'] != ANONYMOUS)
	{
		$sql = 'SELECT poster_id
			FROM ' . POSTS_TABLE . '
			WHERE topic_id = ' . $topic_id . '
				AND poster_id = ' . $data['poster_id'];
		$result = $db->sql_query_limit($sql, 1);
		$poster_id = (int) $db->sql_fetchfield('poster_id');
		$db->sql_freeresult($result);

		// The user is not having any more posts within this topic
		if (!$poster_id)
		{
			$sql = 'DELETE FROM ' . TOPICS_POSTED_TABLE . '
				WHERE topic_id = ' . $topic_id . '
					AND user_id = ' . $data['poster_id'];
			$db->sql_query($sql);
		}
	}

	$db->sql_transaction('commit');

	if ($data['post_reported'] && ($post_mode != 'delete_topic'))
	{
		sync('topic_reported', 'topic_id', array($topic_id));
	}

	return $next_post_id;
}

/**
* Submit Post
* @todo Split up and create lightweight, simple API for this.
*/
function submit_post($mode, $subject, $username, $topic_type, &$poll, &$data, $update_message = true, $update_search_index = true)
{
	global $db, $auth, $user, $config, $phpEx, $template, $phpbb_root_path, $phpbb_container, $phpbb_dispatcher;

	/**
	* Modify the data for post submitting
	*
	* @event core.modify_submit_post_data
	* @var	string	mode				Variable containing posting mode value
	* @var	string	subject				Variable containing post subject value
	* @var	string	username			Variable containing post author name
	* @var	int		topic_type			Variable containing topic type value
	* @var	array	poll				Array with the poll data for the post
	* @var	array	data				Array with the data for the post
	* @var	bool	update_message		Flag indicating if the post will be updated
	* @var	bool	update_search_index	Flag indicating if the search index will be updated
	* @since 3.1.0-a4
	*/
	$vars = array(
		'mode',
		'subject',
		'username',
		'topic_type',
		'poll',
		'data',
		'update_message',
		'update_search_index',
	);
	extract($phpbb_dispatcher->trigger_event('core.modify_submit_post_data', compact($vars)));

	// We do not handle erasing posts here
	if ($mode == 'delete')
	{
		return false;
	}

	$current_time = time();

	if ($mode == 'post')
	{
		$post_mode = 'post';
		$update_message = true;
	}
	else if ($mode != 'edit')
	{
		$post_mode = 'reply';
		$update_message = true;
	}
	else if ($mode == 'edit')
	{
		$post_mode = ($data['topic_posts_approved'] + $data['topic_posts_unapproved'] + $data['topic_posts_softdeleted'] == 1) ? 'edit_topic' : (($data['topic_first_post_id'] == $data['post_id']) ? 'edit_first_post' : (($data['topic_last_post_id'] == $data['post_id']) ? 'edit_last_post' : 'edit'));
	}

	// First of all make sure the subject and topic title are having the correct length.
	// To achieve this without cutting off between special chars we convert to an array and then count the elements.
	$subject = truncate_string($subject, 120);
	$data['topic_title'] = truncate_string($data['topic_title'], 120);

	// Collect some basic information about which tables and which rows to update/insert
	$sql_data = $topic_row = array();
	$poster_id = ($mode == 'edit') ? $data['poster_id'] : (int) $user->data['user_id'];

	// Retrieve some additional information if not present
	if ($mode == 'edit' && (!isset($data['post_visibility']) || !isset($data['topic_visibility']) || $data['post_visibility'] === false || $data['topic_visibility'] === false))
	{
		$sql = 'SELECT p.post_visibility, t.topic_type, t.topic_posts_approved, t.topic_posts_unapproved, t.topic_posts_softdeleted, t.topic_visibility
			FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE t.topic_id = p.topic_id
				AND p.post_id = ' . $data['post_id'];
		$result = $db->sql_query($sql);
		$topic_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$data['topic_visibility'] = $topic_row['topic_visibility'];
		$data['post_visibility'] = $topic_row['post_visibility'];
	}

	// This variable indicates if the user is able to post or put into the queue
	$post_visibility = ITEM_APPROVED;

	// Check the permissions for post approval.
	// Moderators must go through post approval like ordinary users.
	if (!$auth->acl_get('f_noapprove', $data['forum_id']))
	{
		// Post not approved, but in queue
		$post_visibility = ITEM_UNAPPROVED;
		switch ($post_mode)
		{
			case 'edit_first_post':
			case 'edit':
			case 'edit_last_post':
			case 'edit_topic':
				$post_visibility = ITEM_REAPPROVE;
			break;
		}
	}

	// MODs/Extensions are able to force any visibility on posts
	if (isset($data['force_approved_state']))
	{
		$post_visibility = (in_array((int) $data['force_approved_state'], array(ITEM_APPROVED, ITEM_UNAPPROVED, ITEM_DELETED, ITEM_REAPPROVE))) ? (int) $data['force_approved_state'] : $post_visibility;
	}
	if (isset($data['force_visibility']))
	{
		$post_visibility = (in_array((int) $data['force_visibility'], array(ITEM_APPROVED, ITEM_UNAPPROVED, ITEM_DELETED, ITEM_REAPPROVE))) ? (int) $data['force_visibility'] : $post_visibility;
	}

	// Start the transaction here
	$db->sql_transaction('begin');

	// Collect Information
	switch ($post_mode)
	{
		case 'post':
		case 'reply':
			$sql_data[POSTS_TABLE]['sql'] = array(
				'forum_id'			=> $data['forum_id'],
				'poster_id'			=> (int) $user->data['user_id'],
				'icon_id'			=> $data['icon_id'],
				'poster_ip'			=> $user->ip,
				'post_time'			=> $current_time,
				'post_visibility'	=> $post_visibility,
				'enable_bbcode'		=> $data['enable_bbcode'],
				'enable_smilies'	=> $data['enable_smilies'],
				'enable_magic_url'	=> $data['enable_urls'],
				'enable_sig'		=> $data['enable_sig'],
				'post_username'		=> (!$user->data['is_registered']) ? $username : '',
				'post_subject'		=> $subject,
				'post_text'			=> $data['message'],
				'post_checksum'		=> $data['message_md5'],
				'post_attachment'	=> (!empty($data['attachment_data'])) ? 1 : 0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $data['bbcode_uid'],
				'post_postcount'	=> ($auth->acl_get('f_postcount', $data['forum_id'])) ? 1 : 0,
				'post_edit_locked'	=> $data['post_edit_locked']
			);
		break;

		case 'edit_first_post':
		case 'edit':

		case 'edit_last_post':
		case 'edit_topic':

			// If edit reason is given always display edit info

			// If editing last post then display no edit info
			// If m_edit permission then display no edit info
			// If normal edit display edit info

			// Display edit info if edit reason given or user is editing his post, which is not the last within the topic.
			if ($data['post_edit_reason'] || (!$auth->acl_get('m_edit', $data['forum_id']) && ($post_mode == 'edit' || $post_mode == 'edit_first_post')))
			{
				$data['post_edit_reason']		= truncate_string($data['post_edit_reason'], 255, 255, false);

				$sql_data[POSTS_TABLE]['sql']	= array(
					'post_edit_time'	=> $current_time,
					'post_edit_reason'	=> $data['post_edit_reason'],
					'post_edit_user'	=> (int) $data['post_edit_user'],
				);

				$sql_data[POSTS_TABLE]['stat'][] = 'post_edit_count = post_edit_count + 1';
			}
			else if (!$data['post_edit_reason'] && $mode == 'edit' && $auth->acl_get('m_edit', $data['forum_id']))
			{
				$sql_data[POSTS_TABLE]['sql'] = array(
					'post_edit_reason'	=> '',
				);
			}

			// If the person editing this post is different to the one having posted then we will add a log entry stating the edit
			// Could be simplified by only adding to the log if the edit is not tracked - but this may confuse admins/mods
			if ($user->data['user_id'] != $poster_id)
			{
				$log_subject = ($subject) ? $subject : $data['topic_title'];
				add_log('mod', $data['forum_id'], $data['topic_id'], 'LOG_POST_EDITED', $log_subject, (!empty($username)) ? $username : $user->lang['GUEST'], $data['post_edit_reason']);
			}

			if (!isset($sql_data[POSTS_TABLE]['sql']))
			{
				$sql_data[POSTS_TABLE]['sql'] = array();
			}

			$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
				'forum_id'			=> $data['forum_id'],
				'poster_id'			=> $data['poster_id'],
				'icon_id'			=> $data['icon_id'],
				// We will change the visibility later
				//'post_visibility'	=> $post_visibility,
				'enable_bbcode'		=> $data['enable_bbcode'],
				'enable_smilies'	=> $data['enable_smilies'],
				'enable_magic_url'	=> $data['enable_urls'],
				'enable_sig'		=> $data['enable_sig'],
				'post_username'		=> ($username && $data['poster_id'] == ANONYMOUS) ? $username : '',
				'post_subject'		=> $subject,
				'post_checksum'		=> $data['message_md5'],
				'post_attachment'	=> (!empty($data['attachment_data'])) ? 1 : 0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $data['bbcode_uid'],
				'post_edit_locked'	=> $data['post_edit_locked'])
			);

			if ($update_message)
			{
				$sql_data[POSTS_TABLE]['sql']['post_text'] = $data['message'];
			}

		break;
	}
	$topic_row = array();

	// And the topic ladies and gentlemen
	switch ($post_mode)
	{
		case 'post':
			$sql_data[TOPICS_TABLE]['sql'] = array(
				'topic_poster'				=> (int) $user->data['user_id'],
				'topic_time'				=> $current_time,
				'topic_last_view_time'		=> $current_time,
				'forum_id'					=> $data['forum_id'],
				'icon_id'					=> $data['icon_id'],
				'topic_posts_approved'		=> ($post_visibility == ITEM_APPROVED) ? 1 : 0,
				'topic_posts_softdeleted'	=> ($post_visibility == ITEM_DELETED) ? 1 : 0,
				'topic_posts_unapproved'	=> ($post_visibility == ITEM_UNAPPROVED) ? 1 : 0,
				'topic_visibility'			=> $post_visibility,
				'topic_delete_user'			=> ($post_visibility != ITEM_APPROVED) ? (int) $user->data['user_id'] : 0,
				'topic_title'				=> $subject,
				'topic_first_poster_name'	=> (!$user->data['is_registered'] && $username) ? $username : (($user->data['user_id'] != ANONYMOUS) ? $user->data['username'] : ''),
				'topic_first_poster_colour'	=> $user->data['user_colour'],
				'topic_type'				=> $topic_type,
				'topic_time_limit'			=> ($topic_type == POST_STICKY || $topic_type == POST_ANNOUNCE) ? ($data['topic_time_limit'] * 86400) : 0,
				'topic_attachment'			=> (!empty($data['attachment_data'])) ? 1 : 0,
			);

			if (isset($poll['poll_options']) && !empty($poll['poll_options']))
			{
				$poll_start = ($poll['poll_start']) ? $poll['poll_start'] : $current_time;
				$poll_length = $poll['poll_length'] * 86400;
				if ($poll_length < 0)
				{
					$poll_start = $poll_start + $poll_length;
					if ($poll_start < 0)
					{
						$poll_start = 0;
					}
					$poll_length = 1;
				}

				$sql_data[TOPICS_TABLE]['sql'] = array_merge($sql_data[TOPICS_TABLE]['sql'], array(
					'poll_title'		=> $poll['poll_title'],
					'poll_start'		=> $poll_start,
					'poll_max_options'	=> $poll['poll_max_options'],
					'poll_length'		=> $poll_length,
					'poll_vote_change'	=> $poll['poll_vote_change'])
				);
			}

			$sql_data[USERS_TABLE]['stat'][] = "user_lastpost_time = $current_time" . (($auth->acl_get('f_postcount', $data['forum_id']) && $post_visibility == ITEM_APPROVED) ? ', user_posts = user_posts + 1' : '');

			if ($post_visibility == ITEM_APPROVED)
			{
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics_approved = forum_topics_approved + 1';
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts_approved = forum_posts_approved + 1';
			}
			else if ($post_visibility == ITEM_UNAPPROVED)
			{
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics_unapproved = forum_topics_unapproved + 1';
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts_unapproved = forum_posts_unapproved + 1';
			}
			else if ($post_visibility == ITEM_DELETED)
			{
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics_softdeleted = forum_topics_softdeleted + 1';
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts_softdeleted = forum_posts_softdeleted + 1';
			}
		break;

		case 'reply':
			$sql_data[TOPICS_TABLE]['stat'][] = 'topic_last_view_time = ' . $current_time . ',
				topic_bumped = 0,
				topic_bumper = 0' .
				(($post_visibility == ITEM_APPROVED) ? ', topic_posts_approved = topic_posts_approved + 1' : '') .
				(($post_visibility == ITEM_UNAPPROVED) ? ', topic_posts_unapproved = topic_posts_unapproved + 1' : '') .
				(($post_visibility == ITEM_DELETED) ? ', topic_posts_softdeleted = topic_posts_softdeleted + 1' : '') .
				((!empty($data['attachment_data']) || (isset($data['topic_attachment']) && $data['topic_attachment'])) ? ', topic_attachment = 1' : '');

			$sql_data[USERS_TABLE]['stat'][] = "user_lastpost_time = $current_time" . (($auth->acl_get('f_postcount', $data['forum_id']) && $post_visibility == ITEM_APPROVED) ? ', user_posts = user_posts + 1' : '');

			if ($post_visibility == ITEM_APPROVED)
			{
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts_approved = forum_posts_approved + 1';
			}
			else if ($post_visibility == ITEM_UNAPPROVED)
			{
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts_unapproved = forum_posts_unapproved + 1';
			}
			else if ($post_visibility == ITEM_DELETED)
			{
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts_softdeleted = forum_posts_softdeleted + 1';
			}
		break;

		case 'edit_topic':
		case 'edit_first_post':
			if (isset($poll['poll_options']))
			{
				$poll_start = ($poll['poll_start'] || empty($poll['poll_options'])) ? $poll['poll_start'] : $current_time;
				$poll_length = $poll['poll_length'] * 86400;
				if ($poll_length < 0)
				{
					$poll_start = $poll_start + $poll_length;
					if ($poll_start < 0)
					{
						$poll_start = 0;
					}
					$poll_length = 1;
				}
			}

			$sql_data[TOPICS_TABLE]['sql'] = array(
				'forum_id'					=> $data['forum_id'],
				'icon_id'					=> $data['icon_id'],
				'topic_title'				=> $subject,
				'topic_first_poster_name'	=> $username,
				'topic_type'				=> $topic_type,
				'topic_time_limit'			=> ($topic_type == POST_STICKY || $topic_type == POST_ANNOUNCE) ? ($data['topic_time_limit'] * 86400) : 0,
				'poll_title'				=> (isset($poll['poll_options'])) ? $poll['poll_title'] : '',
				'poll_start'				=> (isset($poll['poll_options'])) ? $poll_start : 0,
				'poll_max_options'			=> (isset($poll['poll_options'])) ? $poll['poll_max_options'] : 1,
				'poll_length'				=> (isset($poll['poll_options'])) ? $poll_length : 0,
				'poll_vote_change'			=> (isset($poll['poll_vote_change'])) ? $poll['poll_vote_change'] : 0,
				'topic_last_view_time'		=> $current_time,

				'topic_attachment'			=> (!empty($data['attachment_data'])) ? 1 : (isset($data['topic_attachment']) ? $data['topic_attachment'] : 0),
			);

		break;
	}

	/**
	* Modify sql query data for post submitting
	*
	* @event core.submit_post_modify_sql_data
	* @var	array	data				Array with the data for the post
	* @var	array	poll				Array with the poll data for the post
	* @var	string	post_mode			Variable containing posting mode value
	* @var	bool	sql_data			Array with the data for the posting SQL query
	* @var	string	subject				Variable containing post subject value
	* @var	int		topic_type			Variable containing topic type value
	* @var	string	username			Variable containing post author name
	* @since 3.1.3-RC1
	*/
	$vars = array(
		'data',
		'poll',
		'post_mode',
		'sql_data',
		'subject',
		'topic_type',
		'username',
	);
	extract($phpbb_dispatcher->trigger_event('core.submit_post_modify_sql_data', compact($vars)));

	// Submit new topic
	if ($post_mode == 'post')
	{
		$sql = 'INSERT INTO ' . TOPICS_TABLE . ' ' .
			$db->sql_build_array('INSERT', $sql_data[TOPICS_TABLE]['sql']);
		$db->sql_query($sql);

		$data['topic_id'] = $db->sql_nextid();

		$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
			'topic_id' => $data['topic_id'])
		);
		unset($sql_data[TOPICS_TABLE]['sql']);
	}

	// Submit new post
	if ($post_mode == 'post' || $post_mode == 'reply')
	{
		if ($post_mode == 'reply')
		{
			$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
				'topic_id' => $data['topic_id'],
			));
		}

		$sql = 'INSERT INTO ' . POSTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data[POSTS_TABLE]['sql']);
		$db->sql_query($sql);
		$data['post_id'] = $db->sql_nextid();

		if ($post_mode == 'post' || $post_visibility == ITEM_APPROVED)
		{
			$sql_data[TOPICS_TABLE]['sql'] = array(
				'topic_last_post_id'		=> $data['post_id'],
				'topic_last_post_time'		=> $current_time,
				'topic_last_poster_id'		=> $sql_data[POSTS_TABLE]['sql']['poster_id'],
				'topic_last_poster_name'	=> ($user->data['user_id'] == ANONYMOUS) ? $sql_data[POSTS_TABLE]['sql']['post_username'] : $user->data['username'],
				'topic_last_poster_colour'	=> $user->data['user_colour'],
				'topic_last_post_subject'	=> (string) $subject,
			);
		}

		if ($post_mode == 'post')
		{
			$sql_data[TOPICS_TABLE]['sql']['topic_first_post_id'] = $data['post_id'];
		}

		// Update total post count and forum information
		if ($post_visibility == ITEM_APPROVED)
		{
			if ($post_mode == 'post')
			{
				set_config_count('num_topics', 1, true);
			}
			set_config_count('num_posts', 1, true);

			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_post_id = ' . $data['post_id'];
			$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_post_subject = '" . $db->sql_escape($subject) . "'";
			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_post_time = ' . $current_time;
			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_poster_id = ' . (int) $user->data['user_id'];
			$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_poster_name = '" . $db->sql_escape((!$user->data['is_registered'] && $username) ? $username : (($user->data['user_id'] != ANONYMOUS) ? $user->data['username'] : '')) . "'";
			$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_poster_colour = '" . $db->sql_escape($user->data['user_colour']) . "'";
		}

		unset($sql_data[POSTS_TABLE]['sql']);
	}

	// Update the topics table
	if (isset($sql_data[TOPICS_TABLE]['sql']))
	{
		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data[TOPICS_TABLE]['sql']) . '
			WHERE topic_id = ' . $data['topic_id'];
		$db->sql_query($sql);

		unset($sql_data[TOPICS_TABLE]['sql']);
	}

	// Update the posts table
	if (isset($sql_data[POSTS_TABLE]['sql']))
	{
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data[POSTS_TABLE]['sql']) . '
			WHERE post_id = ' . $data['post_id'];
		$db->sql_query($sql);

		unset($sql_data[POSTS_TABLE]['sql']);
	}

	// Update Poll Tables
	if (isset($poll['poll_options']))
	{
		$cur_poll_options = array();

		if ($mode == 'edit')
		{
			$sql = 'SELECT *
				FROM ' . POLL_OPTIONS_TABLE . '
				WHERE topic_id = ' . $data['topic_id'] . '
				ORDER BY poll_option_id';
			$result = $db->sql_query($sql);

			$cur_poll_options = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$cur_poll_options[] = $row;
			}
			$db->sql_freeresult($result);
		}

		$sql_insert_ary = array();

		for ($i = 0, $size = sizeof($poll['poll_options']); $i < $size; $i++)
		{
			if (strlen(trim($poll['poll_options'][$i])))
			{
				if (empty($cur_poll_options[$i]))
				{
					// If we add options we need to put them to the end to be able to preserve votes...
					$sql_insert_ary[] = array(
						'poll_option_id'	=> (int) sizeof($cur_poll_options) + 1 + sizeof($sql_insert_ary),
						'topic_id'			=> (int) $data['topic_id'],
						'poll_option_text'	=> (string) $poll['poll_options'][$i]
					);
				}
				else if ($poll['poll_options'][$i] != $cur_poll_options[$i])
				{
					$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . "
						SET poll_option_text = '" . $db->sql_escape($poll['poll_options'][$i]) . "'
						WHERE poll_option_id = " . $cur_poll_options[$i]['poll_option_id'] . '
							AND topic_id = ' . $data['topic_id'];
					$db->sql_query($sql);
				}
			}
		}

		$db->sql_multi_insert(POLL_OPTIONS_TABLE, $sql_insert_ary);

		if (sizeof($poll['poll_options']) < sizeof($cur_poll_options))
		{
			$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . '
				WHERE poll_option_id > ' . sizeof($poll['poll_options']) . '
					AND topic_id = ' . $data['topic_id'];
			$db->sql_query($sql);
		}

		// If edited, we would need to reset votes (since options can be re-ordered above, you can't be sure if the change is for changing the text or adding an option
		if ($mode == 'edit' && sizeof($poll['poll_options']) != sizeof($cur_poll_options))
		{
			$db->sql_query('DELETE FROM ' . POLL_VOTES_TABLE . ' WHERE topic_id = ' . $data['topic_id']);
			$db->sql_query('UPDATE ' . POLL_OPTIONS_TABLE . ' SET poll_option_total = 0 WHERE topic_id = ' . $data['topic_id']);
		}
	}

	// Submit Attachments
	if (!empty($data['attachment_data']) && $data['post_id'] && in_array($mode, array('post', 'reply', 'quote', 'edit')))
	{
		$space_taken = $files_added = 0;
		$orphan_rows = array();

		foreach ($data['attachment_data'] as $pos => $attach_row)
		{
			$orphan_rows[(int) $attach_row['attach_id']] = array();
		}

		if (sizeof($orphan_rows))
		{
			$sql = 'SELECT attach_id, filesize, physical_filename
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('attach_id', array_keys($orphan_rows)) . '
					AND is_orphan = 1
					AND poster_id = ' . $user->data['user_id'];
			$result = $db->sql_query($sql);

			$orphan_rows = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$orphan_rows[$row['attach_id']] = $row;
			}
			$db->sql_freeresult($result);
		}

		foreach ($data['attachment_data'] as $pos => $attach_row)
		{
			if ($attach_row['is_orphan'] && !isset($orphan_rows[$attach_row['attach_id']]))
			{
				continue;
			}

			if (!$attach_row['is_orphan'])
			{
				// update entry in db if attachment already stored in db and filespace
				$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
					SET attach_comment = '" . $db->sql_escape($attach_row['attach_comment']) . "'
					WHERE attach_id = " . (int) $attach_row['attach_id'] . '
						AND is_orphan = 0';
				$db->sql_query($sql);
			}
			else
			{
				// insert attachment into db
				if (!@file_exists($phpbb_root_path . $config['upload_path'] . '/' . utf8_basename($orphan_rows[$attach_row['attach_id']]['physical_filename'])))
				{
					continue;
				}

				$space_taken += $orphan_rows[$attach_row['attach_id']]['filesize'];
				$files_added++;

				$attach_sql = array(
					'post_msg_id'		=> $data['post_id'],
					'topic_id'			=> $data['topic_id'],
					'is_orphan'			=> 0,
					'poster_id'			=> $poster_id,
					'attach_comment'	=> $attach_row['attach_comment'],
				);

				$sql = 'UPDATE ' . ATTACHMENTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $attach_sql) . '
					WHERE attach_id = ' . $attach_row['attach_id'] . '
						AND is_orphan = 1
						AND poster_id = ' . $user->data['user_id'];
				$db->sql_query($sql);
			}
		}

		if ($space_taken && $files_added)
		{
			set_config_count('upload_dir_size', $space_taken, true);
			set_config_count('num_files', $files_added, true);
		}
	}

	$first_post_has_topic_info = ($post_mode == 'edit_first_post' &&
			(($post_visibility == ITEM_DELETED && $data['topic_posts_softdeleted'] == 1) ||
			($post_visibility == ITEM_UNAPPROVED && $data['topic_posts_unapproved'] == 1) ||
			($post_visibility == ITEM_REAPPROVE && $data['topic_posts_unapproved'] == 1) ||
			($post_visibility == ITEM_APPROVED && $data['topic_posts_approved'] == 1)));
	// Fix the post's and topic's visibility and first/last post information, when the post is edited
	if (($post_mode != 'post' && $post_mode != 'reply') && $data['post_visibility'] != $post_visibility)
	{
		// If the post was not approved, it could also be the starter,
		// so we sync the starter after approving/restoring, to ensure that the stats are correct
		// Same applies for the last post
		$is_starter = ($post_mode == 'edit_first_post' || $post_mode == 'edit_topic' || $data['post_visibility'] != ITEM_APPROVED);
		$is_latest = ($post_mode == 'edit_last_post' || $post_mode == 'edit_topic' || $data['post_visibility'] != ITEM_APPROVED);

		$phpbb_content_visibility = $phpbb_container->get('content.visibility');
		$phpbb_content_visibility->set_post_visibility($post_visibility, $data['post_id'], $data['topic_id'], $data['forum_id'], $user->data['user_id'], time(), '', $is_starter, $is_latest);
	}
	else if ($post_mode == 'edit_last_post' || $post_mode == 'edit_topic' || $first_post_has_topic_info)
	{
		if ($post_visibility == ITEM_APPROVED || $data['topic_visibility'] == $post_visibility)
		{
			// only the subject can be changed from edit
			$sql_data[TOPICS_TABLE]['stat'][] = "topic_last_post_subject = '" . $db->sql_escape($subject) . "'";

			// Maybe not only the subject, but also changing anonymous usernames. ;)
			if ($data['poster_id'] == ANONYMOUS)
			{
				$sql_data[TOPICS_TABLE]['stat'][] = "topic_last_poster_name = '" . $db->sql_escape($username) . "'";
			}

			if ($post_visibility == ITEM_APPROVED)
			{
				// this does not _necessarily_ mean that we must update the info again,
				// it just means that we might have to
				$sql = 'SELECT forum_last_post_id, forum_last_post_subject
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . (int) $data['forum_id'];
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				// this post is the latest post in the forum, better update
				if ($row['forum_last_post_id'] == $data['post_id'] && ($row['forum_last_post_subject'] !== $subject || $data['poster_id'] == ANONYMOUS))
				{
					// the post's subject changed
					if ($row['forum_last_post_subject'] !== $subject)
					{
						$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_post_subject = '" . $db->sql_escape($subject) . "'";
					}

					// Update the user name if poster is anonymous... just in case a moderator changed it
					if ($data['poster_id'] == ANONYMOUS)
					{
						$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_poster_name = '" . $db->sql_escape($username) . "'";
					}
				}
			}
		}
	}

	// Update forum stats
	$where_sql = array(
		POSTS_TABLE		=> 'post_id = ' . $data['post_id'],
		TOPICS_TABLE	=> 'topic_id = ' . $data['topic_id'],
		FORUMS_TABLE	=> 'forum_id = ' . $data['forum_id'],
		USERS_TABLE		=> 'user_id = ' . $poster_id
	);

	foreach ($sql_data as $table => $update_ary)
	{
		if (isset($update_ary['stat']) && implode('', $update_ary['stat']))
		{
			$sql = "UPDATE $table SET " . implode(', ', $update_ary['stat']) . ' WHERE ' . $where_sql[$table];
			$db->sql_query($sql);
		}
	}

	// Delete topic shadows (if any exist). We do not need a shadow topic for an global announcement
	if ($topic_type == POST_GLOBAL)
	{
		$sql = 'DELETE FROM ' . TOPICS_TABLE . '
			WHERE topic_moved_id = ' . $data['topic_id'];
		$db->sql_query($sql);
	}

	// Committing the transaction before updating search index
	$db->sql_transaction('commit');

	// Delete draft if post was loaded...
	$draft_id = request_var('draft_loaded', 0);
	if ($draft_id)
	{
		$sql = 'DELETE FROM ' . DRAFTS_TABLE . "
			WHERE draft_id = $draft_id
				AND user_id = {$user->data['user_id']}";
		$db->sql_query($sql);
	}

	// Index message contents
	if ($update_search_index && $data['enable_indexing'])
	{
		// Select the search method and do some additional checks to ensure it can actually be utilised
		$search_type = $config['search_type'];

		if (!class_exists($search_type))
		{
			trigger_error('NO_SUCH_SEARCH_MODULE');
		}

		$error = false;
		$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

		if ($error)
		{
			trigger_error($error);
		}

		$search->index($mode, $data['post_id'], $data['message'], $subject, $poster_id, $data['forum_id']);
	}

	// Topic Notification, do not change if moderator is changing other users posts...
	if ($user->data['user_id'] == $poster_id)
	{
		if (!$data['notify_set'] && $data['notify'])
		{
			$sql = 'INSERT INTO ' . TOPICS_WATCH_TABLE . ' (user_id, topic_id)
				VALUES (' . $user->data['user_id'] . ', ' . $data['topic_id'] . ')';
			$db->sql_query($sql);
		}
		else if (($config['email_enable'] || $config['jab_enable']) && $data['notify_set'] && !$data['notify'])
		{
			$sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . '
				WHERE user_id = ' . $user->data['user_id'] . '
					AND topic_id = ' . $data['topic_id'];
			$db->sql_query($sql);
		}
	}

	if ($mode == 'post' || $mode == 'reply' || $mode == 'quote')
	{
		// Mark this topic as posted to
		markread('post', $data['forum_id'], $data['topic_id']);
	}

	// Mark this topic as read
	// We do not use post_time here, this is intended (post_time can have a date in the past if editing a message)
	markread('topic', $data['forum_id'], $data['topic_id'], time());

	//
	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		$sql = 'SELECT mark_time
			FROM ' . FORUMS_TRACK_TABLE . '
			WHERE user_id = ' . $user->data['user_id'] . '
				AND forum_id = ' . $data['forum_id'];
		$result = $db->sql_query($sql);
		$f_mark_time = (int) $db->sql_fetchfield('mark_time');
		$db->sql_freeresult($result);
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		$f_mark_time = false;
	}

	if (($config['load_db_lastread'] && $user->data['is_registered']) || $config['load_anon_lastread'] || $user->data['is_registered'])
	{
		// Update forum info
		$sql = 'SELECT forum_last_post_time
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $data['forum_id'];
		$result = $db->sql_query($sql);
		$forum_last_post_time = (int) $db->sql_fetchfield('forum_last_post_time');
		$db->sql_freeresult($result);

		update_forum_tracking_info($data['forum_id'], $forum_last_post_time, $f_mark_time, false);
	}

	// If a username was supplied or the poster is a guest, we will use the supplied username.
	// Doing it this way we can use "...post by guest-username..." in notifications when
	// "guest-username" is supplied or ommit the username if it is not.
	$username = ($username !== '' || !$user->data['is_registered']) ? $username : $user->data['username'];

	// Send Notifications
	$notification_data = array_merge($data, array(
		'topic_title'		=> (isset($data['topic_title'])) ? $data['topic_title'] : $subject,
		'post_username'		=> $username,
		'poster_id'			=> $poster_id,
		'post_text'			=> $data['message'],
		'post_time'			=> $current_time,
		'post_subject'		=> $subject,
	));

	$phpbb_notifications = $phpbb_container->get('notification_manager');

	if ($post_visibility == ITEM_APPROVED)
	{
		switch ($mode)
		{
			case 'post':
				$phpbb_notifications->add_notifications(array(
					'notification.type.quote',
					'notification.type.topic',
				), $notification_data);
			break;

			case 'reply':
			case 'quote':
				$phpbb_notifications->add_notifications(array(
					'notification.type.quote',
					'notification.type.bookmark',
					'notification.type.post',
				), $notification_data);
			break;

			case 'edit_topic':
			case 'edit_first_post':
			case 'edit':
			case 'edit_last_post':
				$phpbb_notifications->update_notifications(array(
					'notification.type.quote',
					'notification.type.bookmark',
					'notification.type.topic',
					'notification.type.post',
				), $notification_data);
			break;
		}
	}
	else if ($post_visibility == ITEM_UNAPPROVED)
	{
		switch ($mode)
		{
			case 'post':
				$phpbb_notifications->add_notifications('notification.type.topic_in_queue', $notification_data);
			break;

			case 'reply':
			case 'quote':
				$phpbb_notifications->add_notifications('notification.type.post_in_queue', $notification_data);
			break;

			case 'edit_topic':
			case 'edit_first_post':
			case 'edit':
			case 'edit_last_post':
				// Nothing to do here
			break;
		}
	}
	else if ($post_visibility == ITEM_REAPPROVE)
	{
		switch ($mode)
		{
			case 'edit_topic':
			case 'edit_first_post':
				$phpbb_notifications->add_notifications('notification.type.topic_in_queue', $notification_data);

				// Delete the approve_post notification so we can notify the user again,
				// when his post got reapproved
				$phpbb_notifications->delete_notifications('notification.type.approve_post', $notification_data['post_id']);
			break;

			case 'edit':
			case 'edit_last_post':
				$phpbb_notifications->add_notifications('notification.type.post_in_queue', $notification_data);

				// Delete the approve_post notification so we can notify the user again,
				// when his post got reapproved
				$phpbb_notifications->delete_notifications('notification.type.approve_post', $notification_data['post_id']);
			break;

			case 'post':
			case 'reply':
			case 'quote':
				// Nothing to do here
			break;
		}
	}
	else if ($post_visibility == ITEM_DELETED)
	{
		switch ($mode)
		{
			case 'post':
			case 'reply':
			case 'quote':
			case 'edit_topic':
			case 'edit_first_post':
			case 'edit':
			case 'edit_last_post':
				// Nothing to do here
			break;
		}
	}

	$params = $add_anchor = '';

	if ($post_visibility == ITEM_APPROVED)
	{
		$params .= '&amp;t=' . $data['topic_id'];

		if ($mode != 'post')
		{
			$params .= '&amp;p=' . $data['post_id'];
			$add_anchor = '#p' . $data['post_id'];
		}
	}
	else if ($mode != 'post' && $post_mode != 'edit_first_post' && $post_mode != 'edit_topic')
	{
		$params .= '&amp;t=' . $data['topic_id'];
	}

	$url = (!$params) ? "{$phpbb_root_path}viewforum.$phpEx" : "{$phpbb_root_path}viewtopic.$phpEx";
	$url = append_sid($url, 'f=' . $data['forum_id'] . $params) . $add_anchor;

	/**
	* This event is used for performing actions directly after a post or topic
	* has been submitted. When a new topic is posted, the topic ID is
	* available in the $data array.
	*
	* The only action that can be done by altering data made available to this
	* event is to modify the return URL ($url).
	*
	* @event core.submit_post_end
	* @var	string	mode				Variable containing posting mode value
	* @var	string	subject				Variable containing post subject value
	* @var	string	username			Variable containing post author name
	* @var	int		topic_type			Variable containing topic type value
	* @var	array	poll				Array with the poll data for the post
	* @var	array	data				Array with the data for the post
	* @var	int		post_visibility		Variable containing up to date post visibility
	* @var	bool	update_message		Flag indicating if the post will be updated
	* @var	bool	update_search_index	Flag indicating if the search index will be updated
	* @var	string	url					The "Return to topic" URL
	*
	* @since 3.1.0-a3
	* @change 3.1.0-RC3 Added vars mode, subject, username, topic_type,
	*		poll, update_message, update_search_index
	*/
	$vars = array(
		'mode',
		'subject',
		'username',
		'topic_type',
		'poll',
		'data',
		'post_visibility',
		'update_message',
		'update_search_index',
		'url',
	);
	extract($phpbb_dispatcher->trigger_event('core.submit_post_end', compact($vars)));

	return $url;
}

/**
* Handle topic bumping
* @param int $forum_id The ID of the forum the topic is being bumped belongs to
* @param int $topic_id The ID of the topic is being bumping
* @param array $post_data Passes some topic parameters:
*				- 'topic_title'
*				- 'topic_last_post_id'
*				- 'topic_last_poster_id'
*				- 'topic_last_post_subject'
*				- 'topic_last_poster_name'
*				- 'topic_last_poster_colour'
* @param int $bump_time The time at which topic was bumped, usually it is a current time as obtained via time().
* @return string An URL to the bumped topic, example: ./viewtopic.php?forum_id=1&amptopic_id=2&ampp=3#p3
*/
function phpbb_bump_topic($forum_id, $topic_id, $post_data, $bump_time = false)
{
	global $config, $db, $user, $phpEx, $phpbb_root_path;

	if ($bump_time === false)
	{
		$bump_time = time();
	}

	// Begin bumping
	$db->sql_transaction('begin');

	// Update the topic's last post post_time
	$sql = 'UPDATE ' . POSTS_TABLE . "
		SET post_time = $bump_time
		WHERE post_id = {$post_data['topic_last_post_id']}
			AND topic_id = $topic_id";
	$db->sql_query($sql);

	// Sync the topic's last post time, the rest of the topic's last post data isn't changed
	$sql = 'UPDATE ' . TOPICS_TABLE . "
		SET topic_last_post_time = $bump_time,
			topic_bumped = 1,
			topic_bumper = " . $user->data['user_id'] . "
		WHERE topic_id = $topic_id";
	$db->sql_query($sql);

	// Update the forum's last post info
	$sql = 'UPDATE ' . FORUMS_TABLE . "
		SET forum_last_post_id = " . $post_data['topic_last_post_id'] . ",
			forum_last_poster_id = " . $post_data['topic_last_poster_id'] . ",
			forum_last_post_subject = '" . $db->sql_escape($post_data['topic_last_post_subject']) . "',
			forum_last_post_time = $bump_time,
			forum_last_poster_name = '" . $db->sql_escape($post_data['topic_last_poster_name']) . "',
			forum_last_poster_colour = '" . $db->sql_escape($post_data['topic_last_poster_colour']) . "'
		WHERE forum_id = $forum_id";
	$db->sql_query($sql);

	// Update bumper's time of the last posting to prevent flood
	$sql = 'UPDATE ' . USERS_TABLE . "
		SET user_lastpost_time = $bump_time
		WHERE user_id = " . $user->data['user_id'];
	$db->sql_query($sql);

	$db->sql_transaction('commit');

	// Mark this topic as posted to
	markread('post', $forum_id, $topic_id, $bump_time);

	// Mark this topic as read
	markread('topic', $forum_id, $topic_id, $bump_time);

	// Update forum tracking info
	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		$sql = 'SELECT mark_time
			FROM ' . FORUMS_TRACK_TABLE . '
			WHERE user_id = ' . $user->data['user_id'] . '
				AND forum_id = ' . $forum_id;
		$result = $db->sql_query($sql);
		$f_mark_time = (int) $db->sql_fetchfield('mark_time');
		$db->sql_freeresult($result);
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		$f_mark_time = false;
	}

	if (($config['load_db_lastread'] && $user->data['is_registered']) || $config['load_anon_lastread'] || $user->data['is_registered'])
	{
		// Update forum info
		$sql = 'SELECT forum_last_post_time
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $forum_id;
		$result = $db->sql_query($sql);
		$forum_last_post_time = (int) $db->sql_fetchfield('forum_last_post_time');
		$db->sql_freeresult($result);

		update_forum_tracking_info($forum_id, $forum_last_post_time, $f_mark_time, false);
	}

	add_log('mod', $forum_id, $topic_id, 'LOG_BUMP_TOPIC', $post_data['topic_title']);

	$url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id&amp;p={$post_data['topic_last_post_id']}") . "#p{$post_data['topic_last_post_id']}";

	return $url;
}

/**
* Show upload popup (progress bar)
*/
function phpbb_upload_popup($forum_style = 0)
{
	global $template, $user;

	($forum_style) ? $user->setup('posting', $forum_style) : $user->setup('posting');

	page_header($user->lang['PROGRESS_BAR']);

	$template->set_filenames(array(
			'popup'	=> 'posting_progress_bar.html')
	);

	$template->assign_vars(array(
			'PROGRESS_BAR'	=> $user->img('upload_bar', $user->lang['UPLOAD_IN_PROGRESS']))
	);

	$template->display('popup');

	garbage_collection();
	exit_handler();
}

/**
* Do the various checks required for removing posts as well as removing it
*/
function phpbb_handle_post_delete($forum_id, $topic_id, $post_id, &$post_data, $is_soft = false, $delete_reason = '')
{
	global $user, $auth, $config, $request;
	global $phpbb_root_path, $phpEx;

	$perm_check = ($is_soft) ? 'softdelete' : 'delete';

	// If moderator removing post or user itself removing post, present a confirmation screen
	if ($auth->acl_get("m_$perm_check", $forum_id) || ($post_data['poster_id'] == $user->data['user_id'] && $user->data['is_registered'] && $auth->acl_get("f_$perm_check", $forum_id) && $post_id == $post_data['topic_last_post_id'] && !$post_data['post_edit_locked'] && ($post_data['post_time'] > time() - ($config['delete_time'] * 60) || !$config['delete_time'])))
	{
		$s_hidden_fields = array(
			'p'		=> $post_id,
			'f'		=> $forum_id,
			'mode'	=> ($is_soft) ? 'soft_delete' : 'delete',
		);

		if (confirm_box(true))
		{
			$data = array(
				'topic_first_post_id'	=> $post_data['topic_first_post_id'],
				'topic_last_post_id'	=> $post_data['topic_last_post_id'],
				'topic_posts_approved'		=> $post_data['topic_posts_approved'],
				'topic_posts_unapproved'	=> $post_data['topic_posts_unapproved'],
				'topic_posts_softdeleted'	=> $post_data['topic_posts_softdeleted'],
				'topic_visibility'		=> $post_data['topic_visibility'],
				'topic_type'			=> $post_data['topic_type'],
				'post_visibility'		=> $post_data['post_visibility'],
				'post_reported'			=> $post_data['post_reported'],
				'post_time'				=> $post_data['post_time'],
				'poster_id'				=> $post_data['poster_id'],
				'post_postcount'		=> $post_data['post_postcount'],
			);

			$next_post_id = delete_post($forum_id, $topic_id, $post_id, $data, $is_soft, $delete_reason);
			$post_username = ($post_data['poster_id'] == ANONYMOUS && !empty($post_data['post_username'])) ? $post_data['post_username'] : $post_data['username'];

			if ($next_post_id === false)
			{
				add_log('mod', $forum_id, $topic_id, (($is_soft) ? 'LOG_SOFTDELETE_TOPIC' : 'LOG_DELETE_TOPIC'), $post_data['topic_title'], $post_username, $delete_reason);

				$meta_info = append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id");
				$message = $user->lang['POST_DELETED'];
			}
			else
			{
				add_log('mod', $forum_id, $topic_id, (($is_soft) ? 'LOG_SOFTDELETE_POST' : 'LOG_DELETE_POST'), $post_data['post_subject'], $post_username, $delete_reason);

				$meta_info = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id&amp;p=$next_post_id") . "#p$next_post_id";
				$message = $user->lang['POST_DELETED'];

				if (!$request->is_ajax())
				{
					$message .= '<br /><br />' . $user->lang('RETURN_TOPIC', '<a href="' . $meta_info . '">', '</a>');
				}
			}

			meta_refresh(3, $meta_info);
			if (!$request->is_ajax())
			{
				$message .= '<br /><br />' . $user->lang('RETURN_FORUM', '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id) . '">', '</a>');
			}
			trigger_error($message);
		}
		else
		{
			global $user, $template, $request;

			$can_delete = $auth->acl_get('m_delete', $forum_id) || ($post_data['poster_id'] == $user->data['user_id'] && $user->data['is_registered'] && $auth->acl_get('f_delete', $forum_id));
			$can_softdelete = $auth->acl_get('m_softdelete', $forum_id) || ($post_data['poster_id'] == $user->data['user_id'] && $user->data['is_registered'] && $auth->acl_get('f_softdelete', $forum_id));

			$template->assign_vars(array(
				'S_SOFTDELETED'			=> $post_data['post_visibility'] == ITEM_DELETED,
				'S_CHECKED_PERMANENT'	=> $request->is_set_post('delete_permanent') ? ' checked="checked"' : '',
				'S_ALLOWED_DELETE'		=> $can_delete,
				'S_ALLOWED_SOFTDELETE'	=> $can_softdelete,
			));

			$l_confirm = 'DELETE_POST';
			if ($post_data['post_visibility'] == ITEM_DELETED)
			{
				$l_confirm .= '_PERMANENTLY';
				$s_hidden_fields['delete_permanent'] = '1';
			}
			else if (!$can_softdelete)
			{
				$s_hidden_fields['delete_permanent'] = '1';
			}

			confirm_box(false, $l_confirm, build_hidden_fields($s_hidden_fields), 'confirm_delete_body.html');
		}
	}

	// If we are here the user is not able to delete - present the correct error message
	if ($post_data['poster_id'] != $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id))
	{
		trigger_error('DELETE_OWN_POSTS');
	}

	if ($post_data['poster_id'] == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id) && $post_id != $post_data['topic_last_post_id'])
	{
		trigger_error('CANNOT_DELETE_REPLIED');
	}

	trigger_error('USER_CANNOT_DELETE');
}
