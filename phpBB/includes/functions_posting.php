<?php
/***************************************************************************
 *                           functions_posting.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

// Fill smiley templates (or just the variables) with smileys, either in a window or inline
function generate_smilies($mode)
{
	global $SID, $auth, $db, $user, $config, $template;
	global $starttime, $phpEx, $phpbb_root_path;

	$max_smilies_inline = 20;

	if ($mode == 'window')
	{
		$user->setup(false);

		page_header($user->lang['SMILIES'] . ' - ' . $topic_title);

		$template->set_filenames(array(
			'body' => 'posting_smilies.html')
		);
	}

	$sql = 'SELECT *
		FROM ' . SMILIES_TABLE . 
		(($mode == 'inline') ? ' WHERE display_on_posting = 1 ' : '') . '
		ORDER BY smile_order';
	$result = $db->sql_query($sql);

	$num_smilies = 0;
	$smile_array = array();
	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			if (!in_array($row['smile_url'], $smile_array))
			{
				if ($mode == 'window' || ($mode == 'inline' && $num_smilies < $max_smilies_inline))
				{
					$template->assign_block_vars('emoticon', array(
						'SMILEY_CODE' 	=> $row['code'],
						'SMILEY_IMG' 	=> $config['smilies_path'] . '/' . $row['smile_url'],
						'SMILEY_WIDTH' 	=> $row['smile_width'],
						'SMILEY_HEIGHT' => $row['smile_height'],
						'SMILEY_DESC' 	=> $row['emoticon'])
					);
				}

				$smile_array[] = $row['smile_url'];
				$num_smilies++;
			}
		}
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);

		if ($mode == 'inline' && $num_smilies >= $max_smilies_inline)
		{
			$template->assign_vars(array(
				'S_SHOW_EMOTICON_LINK' 	=> true,
				'U_MORE_SMILIES' 		=> "posting.$phpEx$SID&amp;mode=smilies")
			);
		}
	}

	if ($mode == 'window')
	{
		page_footer();
	}
}

// Format text to be displayed - from viewtopic.php - centralizing this would be nice ;)
function format_display(&$message, &$signature, $uid, $siguid, $html, $bbcode, $url, $smilies, $sig)
{
	global $auth, $forum_id, $config, $censors, $user, $bbcode, $phpbb_root_path;

	// Second parse bbcode here
	$message = $bbcode->bbcode_second_pass($message, $uid);

	// If we allow users to disable display of emoticons we'll need an appropriate 
	// check and preg_replace here
	$message = (empty($smilies) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $message);

	// Replace naughty words such as farty pants
	if (sizeof($censors))
	{
		$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $message . '<'), 1, -1));
	}

	$message = str_replace("\n", '<br />', $message);

	// Signature
	if ($sig && $config['allow_sig'] && $signature && $auth->acl_get('f_sigs', $forum_id))
	{
		$signature = trim($signature);

		$signature = $bbcode->bbcode_second_pass($signature, $siguid);

		$signature = (!$config['enable_smilies']) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $signature) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $signature);

		if (sizeof($censors))
		{
			$signature = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $signature . '<'), 1, -1));
		}

		$signature = str_replace("\n", '<br />', $signature);
	}
	else
	{
		$signature = '';
	}

	return $message;
}

// Update Last Post Informations
function update_last_post_information($type, $id)
{
	global $db;

	switch ($type)
	{
		case 'forum':
			$sql_select_add = ', f.forum_parents';
			$sql_table_add = ', ' . FORUMS_TABLE . ' f';
			$sql_where_add = 'AND t.forum_id = f.forum_id AND f.forum_id = ' . $id;
			$sql_update_table = FORUMS_TABLE;
			break;

		case 'topic':
			$sql_select_add = '';
			$sql_table_add = '';
			$sql_where_add = 'AND t.topic_id = ' . $id;
			$sql_update_table = TOPICS_TABLE;
			break;
		default:
			return;
	}

	$sql = "SELECT p.post_id, p.poster_id, p.post_time, u.username, p.post_username $sql_select_add 
		FROM " . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u, ' . TOPICS_TABLE . " t $sql_table_add 
		WHERE p.post_approved = 1 
			AND t.topic_approved = 1 
			AND p.poster_id = u.user_id 
			AND t.topic_id = p.topic_id 
			$sql_where_add 
		ORDER BY p.post_time DESC";
	$result = $db->sql_query_limit($sql, 1);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$update_sql = array(
		$type . '_last_post_id'		=> (int) $row['post_id'],
		$type . '_last_post_time'	=> (int) $row['post_time'],
		$type . '_last_poster_id'	=> (int) $row['poster_id'],
		$type . '_last_poster_name' => (string) ($row['poster_id'] == ANONYMOUS) ? trim($row['post_username']) : trim($row['username'])
	);

	$sql = 'UPDATE ' . $sql_update_table . ' 
		SET ' . $db->sql_build_array('UPDATE', $update_sql) . ' 
		WHERE ' . (($type == 'forum') ? "forum_id = $id" : "topic_id = $id");
	$db->sql_query($sql);
}

// Delete Attachment
function delete_attachment($post_id_array = -1, $attach_id_array = -1, $page = 'post', $user_id = -1)
{
	global $db;

	if ($post_id_array == -1 && $attach_id_array == -1 && $page == -1)
	{
		return;
	}

	// Generate Array, if it's not an array
	if ($post_id_array == -1 && $attach_id_array != -1)
	{
		$post_id_array = array();

		if (!is_array($attach_id_array))
		{
			$attach_id_array = (strstr($attach_id_array, ',')) ? explode(',', str_replace(', ', ',', $attach_id_array)) : array((int) $attach_id_array);
		}
	
		// Get the post_ids to fill the array
		$sql = 'SELECT ' . (($page == 'privmsgs') ? 'privmsgs_id' : 'post_id') . ' as id 
			FROM ' . ATTACHMENTS_TABLE . ' 
			WHERE attach_id IN (' . implode(', ', $attach_id_array) . ')
			GROUP BY id';
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			return;
		}

		do
		{
			$post_id_array[] = $row['id'];
		}
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);
	}
		
	if (!is_array($post_id_array))
	{
		if (trim($post_id_array) == '')
		{
			return;
		}

		$post_id_array = (strstr($post_id_array, ',')) ? explode(',', str_replace(', ', ',', $attach_id_array)) : array((int) $post_id_array);
	}
		
	if (!count($post_id_array))
	{
		return;
	}

	// First of all, determine the post id and attach_id
	if ($attach_id_array == -1)
	{
		$attach_id_array = array();

		// Get the attach_ids to fill the array
		$sql = 'SELECT attach_id 
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . (($page == 'privmsgs') ? 'privmsgs_id' : 'post_id') . ' IN (' . implode(', ', $post_id_array) . ')
			GROUP BY attach_id';
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			return;
		}

		do
		{
			$attach_id_array[] = $row['attach_id'];
		}
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);
	}
	
	if (!is_array($attach_id_array))
	{
		$attach_id_array = (strstr($post_id_array, ',')) ? explode(',', str_replace(', ', ',', $attach_id_array)) : array((int) $attach_id_array);
	}

	if (!count($attach_id_array))
	{
		return;
	}

	$sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . ' 
		WHERE attach_id IN (' . implode(', ', $attach_id_array) . ") 
			AND post_id IN (" . implode(', ', $post_id_array) . ')';
	$db->sql_query($sql);
	
	foreach ($attach_id_array as $attach_id)
	{
		$sql = 'SELECT attach_id 
			FROM ' . ATTACHMENTS_TABLE . " 
			WHERE attach_id = $attach_id";
		$select_result = $db->sql_query($sql);			

		if (!is_array($db->sql_fetchrow($select_result)))
		{
			$sql = 'SELECT attach_id, physical_filename, thumbnail
				FROM ' . ATTACHMENTS_DESC_TABLE . "
				WHERE attach_id = $attach_id";
			$result = $db->sql_query($sql);	
		
			// delete attachments
			while ($row = $db->sql_fetchrow($result))
			{
				phpbb_unlink($row['physical_filename'], 'file', $config['use_ftp_upload']);
				if ($row['thumbnail'])
				{
					phpbb_unlink($row['physical_filename'], 'thumbnail', $config['use_ftp_upload']);
				}
					
				$sql = 'DELETE FROM ' . ATTACHMENTS_DESC_TABLE . '
					WHERE attach_id = ' . $row['attach_id'];
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);
		}
		$db->sql_freeresult($select_result);
	}
	
	// Now Sync the Topic/PM
	if ($page == 'privmsgs')
	{
		foreach ($post_id_array as $privmsgs_id)
		{
			$sql = 'SELECT attach_id 
				FROM ' . ATTACHMENTS_TABLE . ' 
				WHERE privmsgs_id = ' . $privmsgs_id;
			$select_result = $db->sql_query($sql);

			if (!is_array($db->sql_fetchrow($select_result)))
			{
				$sql = 'UPDATE ' . PRIVMSGS_TABLE . ' 
					SET privmsgs_attachment = 0 
					WHERE privmsgs_id = ' . $privmsgs_id;
				$db->sql_query($sql);
			}
			$db->sql_freeresult($select_result);
		}
	}
	else
	{
		$sql = 'SELECT topic_id 
			FROM ' . POSTS_TABLE . ' 
			WHERE post_id IN (' . implode(', ', $post_id_array) . ') 
			GROUP BY topic_id';
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_id = $row['topic_id'];

			$sql = 'SELECT post_id 
				FROM ' . POSTS_TABLE . "
				WHERE topic_id = $topic_id
				GROUP BY post_id";
			$result2 = $db->sql_query($sql);		
			
			$post_ids = array();

			while ($post_row = $db->sql_fetchrow($result2))
			{
				$post_ids[] = $post_row['post_id'];
			}
			$db->sql_freeresult($result2);

			if (count($post_ids))
			{
				$post_id_sql = implode(', ', $post_ids);
	
				$sql = 'SELECT attach_id 
					FROM ' . ATTACHMENTS_TABLE . "
					WHERE post_id IN ($post_id_sql)";
				$select_result = $db->sql_query_limit($sql, 1);
				$set_id = (!is_array($db->sql_fetchrow($select_result))) ? 0 : 1;
				$db->sql_freeresult($select_result);

				$sql = 'UPDATE ' . TOPICS_TABLE . " 
					SET topic_attachment = $set_id 
					WHERE topic_id = $topic_id";
				$db->sql_query($sql);
				
				foreach ($post_ids as $post_id)
				{
					$sql = 'SELECT attach_id 
						FROM ' . ATTACHMENTS_TABLE . " 
						WHERE post_id = $post_id";
					$select_result = $db->sql_query_limit($sql, 1);
					$set_id = (!is_array($db->sql_fetchrow($select_result))) ? 0 : 1;
					$db->sql_freeresult($select_result);
		
					$sql = 'UPDATE ' . POSTS_TABLE . " 
						SET post_attachment = $set_id
						WHERE post_id = $post_id";
					$db->sql_query($sql);
				}
			}
		}
		$db->sql_freeresult($result);
	}
}

// Upload Attachment - filedata is generated here
function upload_attachment($filename)
{
	global $auth, $user, $config, $db;

	$filedata = array();
	$filedata['error'] = array();
	$filedata['post_attach'] = ($filename != '') ? TRUE : FALSE;

	if (!$filedata['post_attach'])
	{
		return $filedata;
	}

	$r_file = $filename;
	$file = $_FILES['fileupload']['tmp_name'];
	$filedata['mimetype'] = $_FILES['fileupload']['type'];
		
	// Opera add the name to the mime type
	$filedata['mimetype']	= ( strstr($filedata['mimetype'], '; name') ) ? str_replace(strstr($filedata['mimetype'], '; name'), '', $filedata['mimetype']) : $filedata['mimetype'];
	$filedata['extension']	= array_pop(explode('.', strtolower($filename)));
	$filedata['filesize']	= (!@filesize($file)) ? intval($_FILES['size']) : @filesize($file);

	$extensions = array();
	obtain_attach_extensions($extensions);

	// Check Extension
	if (!in_array($filedata['extension'], $extensions['_allowed_']))
	{
		$filedata['error'][] = sprintf($user->lang['DISALLOWED_EXTENSION'], $filedata['extension']);
		$filedata['post_attach'] = FALSE;
		return $filedata;
	} 

	$allowed_filesize = ($extensions[$filedata['extension']]['max_filesize'] != 0) ? $extensions[$filedata['extension']]['max_filesize'] : $config['max_filesize'];
	$cat_id = $extensions[$filedata['extension']]['display_cat'];

	// check Filename
	if (preg_match("#[\\/:*?\"<>|]#i", $filename))
	{ 
		$filedata['error'][] = sprintf($user->lang['INVALID_FILENAME'], $filename);
		$filedata['post_attach'] = FALSE;
		return $filedata;
	}

	// check php upload-size
	if ( ($file == 'none') ) 
	{
		$filedata['error'][] = (@ini_get('upload_max_filesize') == '') ? $user->lang['ATTACHMENT_PHP_SIZE_NA'] : sprintf($user->lang['ATTACHMENT_PHP_SIZE_OVERRUN'], @ini_get('upload_max_filesize'));
		$filedata['post_attach'] = FALSE;
		return $filedata;
	}

	// Check Image Size, if it is an image
	if (!$auth->acl_gets('m_', 'a_') && $cat_id == IMAGE_CAT)
	{
		list($width, $height) = getimagesize($file);

		if ($width != 0 && $height != 0 && $config['img_max_width'] && $config['img_max_height'])
		{
			if ($width > $config['img_max_width'] || $height > $config['img_max_height'])
			{
				$filedata['error'][] = sprintf($user->lang['Error_imagesize'], $config['img_max_width'], $config['img_max_height']);
				$filedata['post_attach'] = false;
				return $filedata;
			}
		}
	}

	// check Filesize 
	if ($allowed_filesize != 0 && $filedata['filesize'] > $allowed_filesize && !$auth->acl_gets('m_', 'a_'))
	{
		$size_lang = ($allowed_filesize >= 1048576) ? $user->lang['MB'] : ( ($allowed_filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );

		$allowed_filesize = ($allowed_filesize >= 1048576) ? round($allowed_filesize / 1048576 * 100) / 100 : (($allowed_filesize >= 1024) ? round($allowed_filesize / 1024 * 100) / 100 : $allowed_filesize);
			
		$filedata['error'][] = sprintf($user->lang['ATTACHMENT_TOO_BIG'], $allowed_filesize, $size_lang);
		$filedata['post_attach'] = false;
		return $filedata;
	}

	// Check our complete quota
	if ($config['attachment_quota'] != 0)
	{
		if ($config['total_filesize'] + $filedata['filesize'] > $config['attachment_quota'])
		{
			$filedata['error'][] = $user->lang['ATTACH_QUOTA_REACHED'];
			$filedata['post_attach'] = false;
			return $filedata;
		}
	}

	$filedata['thumbnail'] = 0;
				
	// Prepare Values
	$filedata['filetime'] = time(); 
	$filedata['filename'] = stripslashes($r_file);

	$filedata['destination_filename'] = strtolower($filedata['filename']);
	$filedata['destination_filename'] = $user->data['user_id'] . '_' . $filedata['filetime'] . '.' . $filedata['extension'];
				
	$filedata['filename'] = str_replace("'", "\'", $filedata['filename']);
			
	// Do we have to create a thumbnail ?
	if ($cat_id == IMAGE_CAT && $config['img_create_thumbnail'])
	{
		$filedata['thumbnail'] = 1;
	}

	// Descide the Upload method
	$upload_mode = (@ini_get('open_basedir') || @ini_get('safe_mode')) ? 'move' : 'copy';

	// Ok, upload the File
	$result = move_uploaded_attachment($upload_mode, $file, $filedata);

	if ($result != '')
	{
		$filedata['error'][] = $result;
		$filedata['post_attach'] = false;
	}
	return $filedata;
}

// Move/Upload File - could be used for Avatars too ?
function move_uploaded_attachment($upload_mode, $source_filename, &$filedata)
{
	global $user, $config;

	$destination_filename = $filedata['destination_filename'];
	$thumbnail = (isset($filedata['thumbnail'])) ? $filedata['thumbnail'] : false;

	switch ($upload_mode)
	{
		case 'copy':
			if ( !@copy($source_filename, $config['upload_dir'] . '/' . $destination_filename) ) 
			{
				if ( !@move_uploaded_file($source_filename, $config['upload_dir'] . '/' . $destination_filename) ) 
				{
					return sprintf($user->lang['GENERAL_UPLOAD_ERROR'], './' . $config['upload_dir'] . '/' . $destination_filename);
				}
			} 
			@chmod($config['upload_dir'] . '/' . $destination_filename, 0666);
			break;

		case 'move':
			if ( !@move_uploaded_file($source_filename, $config['upload_dir'] . '/' . $destination_filename) ) 
			{ 
				if ( !@copy($source_file, $config['upload_dir'] . '/' . $destination_filename) ) 
				{
					return sprintf($user->lang['GENERAL_UPLOAD_ERROR'], './' . $config['upload_dir'] . '/' . $destination_filename);
				}
			} 
			@chmod($config['upload_dir'] . '/' . $destination_filename, 0666);
			break;
	}

	if ($filedata['thumbnail'])
	{
		$source = $config['upload_dir'] . '/' . $destination_filename;
		$destination = $config['upload_dir'] . '/thumbs/t_' . $destination_filename;

		if (!create_thumbnail($source, $destination, $filedata['mimetype']))
		{
			if (!create_thumbnail($source_filename, $destination_filename, $filedata['mimetype']))
			{
				$filedata['thumbnail'] = 0;
			}
		}
	}

	return;
}

// Delete File
function phpbb_unlink($filename, $mode = 'file', $use_ftp = false)
{
	global $config, $user;

	$filename = ($mode == 'thumbnail') ? $config['upload_dir'] . '/thumbs/t_' . $filename : $config['upload_dir'] . '/' . $filename;
	$deleted = @unlink($filename);

	if (file_exists($filename))
	{
		$filesys = str_replace('/','\\', $filename);
		$deleted = @system("del $filesys");

		if (file_exists($filename)) 
		{
			@chmod($filename, 0777);
			if (!($deleted = @unlink($filename)))
			{
				$deleted = @system("del $filename");
			}
		}
	}

	return $deleted;
}

// Calculate the needed size for Thumbnail
// I am sure i had this grabbed from some site... source: unknown
function get_img_size_format($width, $height)
{
	// Change these two values to define the Thumbnail Size
	$max_width = 400;
	$max_height = 200;
	
	if ($height > $max_height) 
	{
		$new_width = ($max_height / $height) * $width;
		$new_height = $max_height;

		if ($new_width > $max_width) 
		{
			$new_height = ($max_width / $new_width) * $new_height;
			$new_width = $max_width;
		}
	} 
	else if ($width > $max_width)
	{
		$new_height = ($max_width / $width) * $height;
		$new_width = $max_width;
		
		if ($new_height > $max_height) 
		{
			$new_width = ($max_height / $new_height) * $new_width;
			$new_height = $max_height;
		}
	} 
	else	
	{
		$new_width = $width;
		$new_height = $height;
	}

	return array(
		round($new_width),
		round($new_height)
	);
}

function get_supported_image_types()
{
	$types = array();

	if (@extension_loaded('gd'))
	{
		if (@function_exists('imagegif'))
		{
			$types[] = '1';
		}
		if (@function_exists('imagejpeg'))
		{
			$types[] = '2';
		}
		if (@function_exists('imagepng'))
		{
			$types[] = '3';
		}
    }
	return $types;
}

// Create Thumbnail
function create_thumbnail($source, $new_file, $mimetype) 
{
	global $config;

	$source = realpath($source);
	$min_filesize = (int) $config['img_min_thumb_filesize'];

	$img_filesize = (file_exists($source)) ? @filesize($source) : FALSE;

	if (!$img_filesize || $img_filesize <= $min_filesize)
	{
		return FALSE;
	}
    
	$size = getimagesize($source);

	if ($size[0] == 0 && $size[1] == 0)
	{
		return FALSE;
	}

	$new_size = get_img_size_format($size[0], $size[1]);

	$tmp_path = $old_file = '';

	$used_imagick = FALSE;

	if ($config['img_imagick']) 
	{
		if (is_array($size) && count($size) > 0) 
		{
			passthru($config['img_imagick'] . 'convert' . ((defined('PHP_OS') && preg_match('#win#i', PHP_OS)) ? '.exe' : '') . ' -quality 85 -antialias -sample ' . $new_size[0] . 'x' . $new_size[1] . ' "' . str_replace('\\', '/', $source) . '" +profile "*" "' . str_replace('\\', '/', $new_file) . '"');
			if (file_exists($new_file))
			{
				$used_imagick = TRUE;
			}
		}
	} 

	if (!$used_imagick) 
	{
		$type = $size[2];
		$supported_types = get_supported_image_types();
		
		if (in_array($type, $supported_types))
		{
			switch ($type) 
			{
				case '1' :
					$image = imagecreatefromgif($source);
					$new_image = imagecreate($new_size[0], $new_size[1]);
					imagecopyresized($new_image, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $size[0], $size[1]);
					imagegif($new_image, $new_file);
					break;

				case '2' :
					$image = imagecreatefromjpeg($source);
					$new_image = imagecreate($new_size[0], $new_size[1]);
					imagecopyresized($new_image, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $size[0], $size[1]);
					imagejpeg($new_image, $new_file, 90);
					break;

				case '3' :
					$image = imagecreatefrompng($source);
					$new_image = imagecreate($new_size[0], $new_size[1]);
					imagecopyresized($new_image, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $size[0], $size[1]);
					imagepng($new_image, $new_file);
					break;
			}
		}
	}

	if (!file_exists($new_file))
	{
		return FALSE;
	}

	@chmod($new_file, 0666);
	
	return TRUE;
}


//
// TODO
//

// DECODE TEXT -> This will/should be handled by bbcode.php eventually
function decode_text(&$message, $bbcode_uid)
{
	global $config;

	$server_protocol = ($config['cookie_secure']) ? 'https://' : 'http://';
	$server_port = ($config['server_port'] <> 80) ? ':' . trim($config['server_port']) . '/' : '/';

	$search = array(
		'<br />',
		"[/*:m:$bbcode_uid]",
		":u:$bbcode_uid",
		":o:$bbcode_uid",
		":$bbcode_uid"
	);
	$replace = array(
		"\n",
		'',
		'',
		'',
		''
	);
	$message = str_replace($search, $replace, $message);

	$match = array(
		'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
		'#<!\-\- m \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- m \-\->#',
		'#<!\-\- w \-\-><a href="http:\/\/(.*?)" target="_blank">.*?</a><!\-\- w \-\->#',
		'#<!\-\- l \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- l \-\->#',
		'#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
		'#<.*?>#s'
	);
	$replace = array(
		'\1',
		'\1',
		'\1',
		$server_protocol . trim($config['server_name']) . $server_port . preg_replace('#^\/?(.*?)(\/)?$#', '\1', trim($config['script_path'])) . '/\1',
		'\1',
		''
	);
	$message = preg_replace($match, $replace, $message);

	return;
}

?>