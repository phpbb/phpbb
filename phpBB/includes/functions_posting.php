<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : functions_posting.php
// STARTED   : Sun Jul 14, 2002
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// Fill smiley templates (or just the variables) with smileys, either in a window or inline
function generate_smilies($mode, $forum_id)
{
	global $SID, $auth, $db, $user, $config, $template;
	global $phpEx, $phpbb_root_path;

	if ($mode == 'window')
	{
		if (!$forum_id && !$topic_id)
		{
			trigger_error('NO_TOPIC');
		}
		
		$sql = 'SELECT forum_style
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$user->setup('posting', (int) $row['forum_style']);

		page_header($user->lang['SMILIES']);

		$template->set_filenames(array(
			'body' => 'posting_smilies.html')
		);
	}

	$display_link = false;
	if ($mode == 'inline')
	{
		$sql = 'SELECT smile_id
			FROM ' . SMILIES_TABLE . '
			WHERE display_on_posting = 0';
		$result = $db->sql_query_limit($sql, 1, 0, 3600);

		if ($row = $db->sql_fetchrow($result))
		{
			$display_link = true;
		}
		$db->sql_freeresult($result);
	}

	$sql = 'SELECT *
		FROM ' . SMILIES_TABLE . 
		(($mode == 'inline') ? ' WHERE display_on_posting = 1 ' : '') . '
		GROUP BY smile_url
		ORDER BY smile_order';
	$result = $db->sql_query($sql, 3600);

	while ($row = $db->sql_fetchrow($result))
	{
		$template->assign_block_vars('emoticon', array(
			'SMILEY_CODE' 	=> $row['code'],
			'SMILEY_IMG' 	=> $config['smilies_path'] . '/' . $row['smile_url'],
			'SMILEY_WIDTH' 	=> $row['smile_width'],
			'SMILEY_HEIGHT' => $row['smile_height'],
			'SMILEY_DESC' 	=> $row['emoticon'])
		);
	}
	$db->sql_freeresult($result);

	if ($mode == 'inline' && $display_link)
	{
		$template->assign_vars(array(
			'S_SHOW_EMOTICON_LINK' 	=> true,
			'U_MORE_SMILIES' 		=> $phpbb_root_path . "posting.$phpEx$SID&amp;mode=smilies&amp;f=$forum_id")
		);
	}

	if ($mode == 'window')
	{
		page_footer();
	}
}

// Format text to be displayed - from viewtopic.php - centralizing this would be nice ;)
function format_display(&$message, &$signature, $uid, $siguid, $html, $bbcode, $url, $smilies, $sig)
{
	global $auth, $forum_id, $config, $user, $bbcode, $phpbb_root_path;

	// Second parse bbcode here
	$bbcode->bbcode_second_pass($message, $uid);

	// If we allow users to disable display of emoticons we'll need an appropriate 
	// check and preg_replace here
	$message = smilie_text($message, !$smilies);

	// Replace naughty words such as farty pants
/*	if (sizeof($censors))
	{
		$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $message . '<'), 1, -1));
	}*/
	$message = str_replace("\n", '<br />', censor_text($message));

	// Signature
	if ($sig && $config['allow_sig'] && $signature && $auth->acl_get('f_sigs', $forum_id))
	{
		$signature = trim($signature);

		$bbcode->bbcode_second_pass($signature, $siguid);
		$signature = smilie_text($signature);

/*		if (sizeof($censors))
		{
			$signature = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $signature . '<'), 1, -1));
		}*/
		$signature = str_replace("\n", '<br />', censor_text($signature));
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

	$update_sql = array();

	$sql = 'SELECT MAX(post_id) as last_post_id
		FROM ' . POSTS_TABLE . "
		WHERE post_approved = 1
			AND {$type}_id = $id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if ($row['last_post_id'])
	{
		$sql = 'SELECT p.post_id, p.poster_id, p.post_time, u.username, p.post_username
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.poster_id = u.user_id
				AND p.post_id = ' . $row['last_post_id'];
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$update_sql[] = $type . '_last_post_id = ' . (int) $row['post_id'];
		$update_sql[] =	$type . '_last_post_time = ' . (int) $row['post_time'];
		$update_sql[] = $type . '_last_poster_id = ' . (int) $row['poster_id'];
		$update_sql[] = "{$type}_last_poster_name = '" . (($row['poster_id'] == ANONYMOUS) ? $db->sql_escape($row['post_username']) : $db->sql_escape($row['username'])) . "'";
	}
	else if ($type == 'forum')
	{
		$update_sql[] = 'forum_last_post_id = 0';
		$update_sql[] =	'forum_last_post_time = 0';
		$update_sql[] = 'forum_last_poster_id = 0';
		$update_sql[] = "forum_last_poster_name = ''";
	}

	return $update_sql;
}

// Upload Attachment - filedata is generated here
function upload_attachment($filename, $local = false, $local_storage = '')
{
	global $auth, $user, $config, $db;

	$filedata = array();
	$filedata['error'] = array();
	$filedata['post_attach'] = ($filename) ? true : false;

	if (!$filedata['post_attach'])
	{
		return $filedata;
	}

	$r_file = $filename;
	$file = (!$local) ? $_FILES['fileupload']['tmp_name'] : $local_storage;
	$filedata['mimetype'] = (!$local) ? $_FILES['fileupload']['type'] : 'application/octet-stream';
		
	// Opera adds the name to the mime type
	$filedata['mimetype']	= ( strstr($filedata['mimetype'], '; name') ) ? str_replace(strstr($filedata['mimetype'], '; name'), '', $filedata['mimetype']) : $filedata['mimetype'];
	$filedata['extension']	= array_pop(explode('.', strtolower($filename)));
	$filedata['filesize']	= (!@filesize($file)) ? intval($_FILES['size']) : @filesize($file);

	$extensions = array();
	obtain_attach_extensions($extensions);

	// Check Extension
	if (!in_array($filedata['extension'], $extensions['_allowed_']))
	{
		$filedata['error'][] = sprintf($user->lang['DISALLOWED_EXTENSION'], $filedata['extension']);
		$filedata['post_attach'] = false;
		return $filedata;
	} 

	$allowed_filesize = ($extensions[$filedata['extension']]['max_filesize'] != 0) ? $extensions[$filedata['extension']]['max_filesize'] : $config['max_filesize'];
	$cat_id = $extensions[$filedata['extension']]['display_cat'];

	// check Filename
	if (preg_match("#[\\/:*?\"<>|]#i", $filename))
	{ 
		$filedata['error'][] = sprintf($user->lang['INVALID_FILENAME'], $filename);
		$filedata['post_attach'] = false;
		return $filedata;
	}

	// check php upload-size
	if ($file == 'none')
	{
		$filedata['error'][] = (@ini_get('upload_max_filesize') == '') ? $user->lang['ATTACHMENT_PHP_SIZE_NA'] : sprintf($user->lang['ATTACHMENT_PHP_SIZE_OVERRUN'], @ini_get('upload_max_filesize'));
		$filedata['post_attach'] = false;
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
				$filedata['error'][] = sprintf($user->lang['ERROR_IMAGESIZE'], $config['img_max_width'], $config['img_max_height']);
				$filedata['post_attach'] = false;
				return $filedata;
			}
		}
	}

	// check Filesize 
	if ($allowed_filesize && $filedata['filesize'] > $allowed_filesize && !$auth->acl_gets('m_', 'a_'))
	{
		$size_lang = ($allowed_filesize >= 1048576) ? $user->lang['MB'] : ( ($allowed_filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );

		$allowed_filesize = ($allowed_filesize >= 1048576) ? round($allowed_filesize / 1048576 * 100) / 100 : (($allowed_filesize >= 1024) ? round($allowed_filesize / 1024 * 100) / 100 : $allowed_filesize);
			
		$filedata['error'][] = sprintf($user->lang['ATTACHMENT_TOO_BIG'], $allowed_filesize, $size_lang);
		$filedata['post_attach'] = false;
		return $filedata;
	}

	// Check our complete quota
	if ($config['attachment_quota'])
	{
		if ($config['upload_dir_size'] + $filedata['filesize'] > $config['attachment_quota'])
		{
			$filedata['error'][] = $user->lang['ATTACH_QUOTA_REACHED'];
			$filedata['post_attach'] = false;
			return $filedata;
		}
	}

	// TODO - Check Free Disk Space - need testing under windows [commented out]
	if ($free_space = disk_free_space($config['upload_dir']))
	{
		if ($free_space <= $filedata['filesize'])
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
	$upload_mode = ($local) ? 'local' : $upload_mode;

	// Ok, upload the File
	$result = move_uploaded_attachment($upload_mode, $file, $filedata);

	if ($result)
	{
		$filedata['error'][] = $result;
		$filedata['post_attach'] = false;
	}
	return $filedata;
}

// Move/Upload File - could be used for Avatars too ?
function move_uploaded_attachment($upload_mode, $source_filename, &$filedata)
{
	global $user, $config, $phpbb_root_path;

	$destination_filename = $filedata['destination_filename'];
	$thumbnail = (isset($filedata['thumbnail'])) ? $filedata['thumbnail'] : false;

	switch ($upload_mode)
	{
		case 'copy':
			if ( !@copy($source_filename, $config['upload_dir'] . '/' . $destination_filename) ) 
			{
				if ( !@move_uploaded_file($source_filename, $config['upload_dir'] . '/' . $destination_filename) ) 
				{
					return sprintf($user->lang['GENERAL_UPLOAD_ERROR'], $config['upload_dir'] . '/' . $destination_filename);
				}
			} 
			@chmod($config['upload_dir'] . '/' . $destination_filename, 0666);
			break;

		case 'move':
			if ( !@move_uploaded_file($source_filename, $config['upload_dir'] . '/' . $destination_filename) ) 
			{ 
				if ( !@copy($source_filename, $config['upload_dir'] . '/' . $destination_filename) ) 
				{
					return sprintf($user->lang['GENERAL_UPLOAD_ERROR'], $config['upload_dir'] . '/' . $destination_filename);
				}
			} 
			@chmod($config['upload_dir'] . '/' . $destination_filename, 0666);
			break;

		case 'local':
			if (!@copy($source_filename, $config['upload_dir'] . '/' . $destination_filename))
			{
				return sprintf($user->lang['GENERAL_UPLOAD_ERROR'], $config['upload_dir'] . '/' . $destination_filename);
			}
			@chmod($config['upload_dir'] . '/' . $destination_filename, 0666);
			@unlink($source_filename);
			break;
	}

	if ($filedata['thumbnail'])
	{
		$source = $config['upload_dir'] . '/' . $destination_filename;
		$destination = $config['upload_dir'] . '/thumb_' . $destination_filename;

		if (!create_thumbnail($source_filename, $destination_filename, $filedata['mimetype']))
		{
			if (!create_thumbnail($source, $destination, $filedata['mimetype']))
			{
				$filedata['thumbnail'] = 0;
			}
		}
	}

	return;
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

	$img_filesize = (file_exists($source)) ? @filesize($source) : false;

	if (!$img_filesize || $img_filesize <= $min_filesize)
	{
		return false;
	}
    
	$size = getimagesize($source);

	if ($size[0] == 0 && $size[1] == 0)
	{
		return false;
	}

	$new_size = get_img_size_format($size[0], $size[1]);

	$tmp_path = $old_file = '';

	$used_imagick = false;

	if ($config['img_imagick']) 
	{
		if (is_array($size) && count($size) > 0) 
		{
			passthru($config['img_imagick'] . 'convert' . ((defined('PHP_OS') && preg_match('#win#i', PHP_OS)) ? '.exe' : '') . ' -quality 85 -antialias -sample ' . $new_size[0] . 'x' . $new_size[1] . ' "' . str_replace('\\', '/', $source) . '" +profile "*" "' . str_replace('\\', '/', $new_file) . '"');
			if (file_exists($new_file))
			{
				$used_imagick = true;
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
		return false;
	}

	
	@chmod($new_file, 0666);

	return true;
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

	$message = ($bbcode_uid) ? str_replace($search, $replace, $message) : str_replace('<br />', "\n", $message);

	// HTML
	if ($config['allow_html_tags'])
	{
		// If $html is true then "allowed_tags" are converted back from entity
		// form, others remain
		$allowed_tags = split(',', $config['allow_html_tags']);
			
		if (sizeof($allowed_tags))
		{
			$message = preg_replace('#\<(\/?)(' . str_replace('*', '.*?', implode('|', $allowed_tags)) . ')\>#is', '&lt;$1$2&gt;', $message);
		}
	}

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