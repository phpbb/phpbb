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
function format_display(&$message, &$signature, $uid, $siguid, $enable_html, $enable_bbcode, $enable_url, $enable_smilies, $enable_sig, $bbcode = '')
{
	global $auth, $forum_id, $config, $user, $phpbb_root_path;

	if (!$bbcode)
	{
		global $bbcode;
	}

	// Second parse bbcode here
	if ($enable_bbcode)
	{
		$bbcode->bbcode_second_pass($message, $uid);
	}

	// If we allow users to disable display of emoticons we'll need an appropriate 
	// check and preg_replace here
	$message = smilie_text($message, !$enable_smilies);

	// Replace naughty words such as farty pants
	$message = str_replace("\n", '<br />', censor_text($message));

	// Signature
	if ($enable_sig && $config['allow_sig'] && $signature && $auth->acl_get('f_sigs', $forum_id))
	{
		$signature = trim($signature);

		$bbcode->bbcode_second_pass($signature, $siguid);
		$signature = smilie_text($signature);

		$signature = str_replace("\n", '<br />', censor_text($signature));
	}
	else
	{
		$signature = '';
	}

	return $message;
}

// Three simple functions we use for bbcode/smilie/url capable text

// prepare text to be inserted into db...
function parse_text_insert($text, $allow_bbcode, $allow_smilies, $allow_magic_url, &$text_flags)
{
	global $message_parser;

	$text_flags += ($allow_bbcode) ? 1 : 0;
	$text_flags += ($allow_smilies) ? 2 : 0;
	$text_flags += ($allow_magic_url) ? 4 : 0;

	$match = array('#\r\n?#', '#sid=[a-z0-9]*?&amp;?#', "#([\n][\s]+){3,}#", '#&amp;(\#[0-9]+;)#');
	$replace = array("\n", '', "\n\n", '&\1');
	$text = preg_replace($match, $replace, $text);

	// Parse BBCode
	if (!method_exists('parse_message', 'parse_message') || !isset($message_parser))
	{
		global $phpbb_root_path, $phpEx;
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		$message_parser = new parse_message();
	}

	$message_parser->message = $text;

	if ($allow_bbcode && strpos($text, '[') !== false)
	{
		$message_parser->bbcode_init();
		$message_parser->bbcode();
	}

	// Parse Emoticons
	$message_parser->emoticons($allow_smilies);

	// Parse URL's
	$message_parser->magic_url($allow_magic_url);
	
	$text_flags = $text_flags . ':' . $message_parser->bbcode_uid . ':' . $message_parser->bbcode_bitfield;

	return $message_parser->message;
}

// prepare text to be displayed/previewed...
function parse_text_display($text, $text_rules)
{
	global $bbcode, $user;

	$text_flags = explode(':', $text_rules);

	$allow_bbcode = (int) $text_flags[0] & 1;
	$allow_smilies = (int) $text_flags[0] & 2;
	$allow_magic_url = (int) $text_flags[0] & 4;

	$bbcode_uid = trim($text_flags[1]);
	$bbcode_bitfield = (int) $text_flags[2];

	if (!$bbcode && $allow_bbcode)
	{
		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
		$bbcode = new bbcode();
	}

	// Second parse bbcode here
	if ($allow_bbcode)
	{
		$bbcode->bbcode_second_pass($text, $bbcode_uid, $bbcode_bitfield);
	}

	// If we allow users to disable display of emoticons we'll need an appropriate 
	// check and preg_replace here
	if ($allow_smilies)
	{
		$text = smilie_text($text, !$allow_smilies);
	}

	// Replace naughty words such as farty pants
	$text = str_replace("\n", '<br />', censor_text($text));

	return $text;
}

// prepare text to be displayed within a form (fetched from db)
function parse_text_form_display($text, $text_rules)
{
	// We use decode_text here...
	$text_rules = explode(':', $text_rules);
	$bbcode_uid = trim($text_rules[1]);

	decode_text($text, $bbcode_uid);

	return $text;
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
function upload_attachment($forum_id, $filename, $local = false, $local_storage = '', $is_message = false)
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
	if (!extension_allowed($forum_id, $filedata['extension']))
	{
		$filedata['error'][] = sprintf($user->lang['DISALLOWED_EXTENSION'], $filedata['extension']);
		$filedata['post_attach'] = false;
		return $filedata;
	}

	$cfg = array();
	$cfg['max_filesize'] = ($is_message) ? $config['max_filesize_pm'] : $config['max_filesize'];

	$allowed_filesize = ($extensions[$filedata['extension']]['max_filesize'] != 0) ? $extensions[$filedata['extension']]['max_filesize'] : $cfg['max_filesize'];
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

	// TODO - Check Free Disk Space - need testing under windows
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

// DECODE TEXT -> This will/should be handled by bbcode.php eventually
function decode_text(&$message, $bbcode_uid = '')
{
	global $config;

	$server_protocol = ($config['cookie_secure']) ? 'https://' : 'http://';
	$server_port = ($config['server_port'] <> 80) ? ':' . trim($config['server_port']) . '/' : '/';

	$match = array(
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

	$message = ($bbcode_uid) ? str_replace($match, $replace, $message) : str_replace('<br />', "\n", $message);

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

	return;
}

// Temp Function - strtolower - borrowed from php.net
function phpbb_strtolower($string)
{
	$new_string = '';

	for ($i = 0; $i < strlen($string); $i++) 
	{
		if (ord(substr($string, $i, 1)) > 0xa0) 
		{
			$new_string .= strtolower(substr($string, $i, 2));
			$i++;
		} 
		else 
		{
			$new_string .= strtolower($string{$i});
		}
	}

	return $new_string;
}

function posting_gen_topic_icons($mode, $icon_id)
{
	global $phpbb_root_path, $config, $template;

	// Grab icons
	$icons = array();
	obtain_icons($icons);

	if (sizeof($icons))
	{
		foreach ($icons as $id => $data)
		{
			if ($data['display'])
			{
				$template->assign_block_vars('topic_icon', array(
					'ICON_ID'		=> $id,
					'ICON_IMG'		=> $phpbb_root_path . $config['icons_path'] . '/' . $data['img'],
					'ICON_WIDTH'	=> $data['width'],
					'ICON_HEIGHT' 	=> $data['height'],
	
					'S_ICON_CHECKED' => ($id == $icon_id) ? ' checked="checked"' : '')
				);
			}
		}

		return true;
	}

	return false;
}

function posting_gen_inline_attachments($message_parser)
{
	global $template;

	if (sizeof($message_parser->attachment_data))
	{
		$s_inline_attachment_options = '';
		
		foreach ($message_parser->attachment_data as $i => $attachment)
		{
			$s_inline_attachment_options .= '<option value="' . $i . '">' . $attachment['real_filename'] . '</option>';
		}

		$template->assign_var('S_INLINE_ATTACHMENT_OPTIONS', $s_inline_attachment_options);

		return true;
	}

	return false;
}

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
		// Temp - we do not have a special post global announcement permission
		$auth_key = ($auth_key == 'global') ? 'announce' : $auth_key;

		if ($auth->acl_get('f_' . $auth_key, $forum_id))
		{
			$toggle = true;

			$topic_type_array[] = array(
				'VALUE'			=> $topic_value['const'],
				'S_CHECKED'		=> ($cur_topic_type == $topic_value['const'] || ($forum_id == 0 && $topic_value['const'] == POST_GLOBAL)) ? ' checked="checked"' : '',
				'L_TOPIC_TYPE'	=> $user->lang[$topic_value['lang']]
			);
		}
	}

	if ($toggle)
	{
		$topic_type_array = array_merge(array(0 => array(
			'VALUE'			=> POST_NORMAL,
			'S_CHECKED'		=> ($topic_type == POST_NORMAL) ? ' checked="checked"' : '',
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

function posting_gen_attachment_entry($message_parser)
{
	global $template, $config, $phpbb_root_path, $SID, $phpEx;
		
	$template->assign_vars(array(
		'S_SHOW_ATTACH_BOX'	=> true)
	);

	if (sizeof($message_parser->attachment_data))
	{
		$template->assign_vars(array(
			'S_HAS_ATTACHMENTS'	=> true)
		);
		
		$count = 0;
		foreach ($message_parser->attachment_data as $attach_row)
		{
			$hidden = '';
			$attach_row['real_filename'] = stripslashes($attach_row['real_filename']);

			foreach ($attach_row as $key => $value)
			{
				$hidden .= '<input type="hidden" name="attachment_data[' . $count . '][' . $key . ']" value="' . $value . '" />';
			}
			
			$download_link = (!$attach_row['attach_id']) ? $config['upload_dir'] . '/' . $attach_row['physical_filename'] : $phpbb_root_path . "download.$phpEx$SID&id=" . intval($attach_row['attach_id']);
				
			$template->assign_block_vars('attach_row', array(
				'FILENAME'			=> $attach_row['real_filename'],
				'ATTACH_FILENAME'	=> $attach_row['physical_filename'],
				'FILE_COMMENT'		=> $attach_row['comment'],
				'ATTACH_ID'			=> $attach_row['attach_id'],
				'ASSOC_INDEX'		=> $count,

				'U_VIEW_ATTACHMENT' => $download_link,
				'S_HIDDEN'			=> $hidden)
			);

			$count++;
		}
	}

	$template->assign_vars(array(
		'FILE_COMMENT'	=> $message_parser->filename_data['filecomment'], 
		'FILESIZE'		=> $config['max_filesize'],
		'FILENAME'		=> $message_parser->filename_data['filename'])
	);

	return sizeof($message_parser->attachment_data);
}

// Load Drafts
function load_drafts($topic_id = 0, $forum_id = 0, $id = 0)
{
	global $user, $db, $template, $phpEx, $SID, $auth;

	// Only those fitting into this forum...
	if ($forum_id || $topic_id)
	{
		$sql = 'SELECT d.draft_id, d.topic_id, d.forum_id, d.draft_subject, d.save_time, f.forum_name
			FROM ' . DRAFTS_TABLE . ' d, ' . FORUMS_TABLE . ' f
				WHERE d.user_id = ' . $user->data['user_id'] . '
				AND f.forum_id = d.forum_id ' . 
				(($forum_id) ? " AND f.forum_id = $forum_id" : '') . '
			ORDER BY d.save_time DESC';
	}
	else
	{
		$sql = 'SELECT *
			FROM ' . DRAFTS_TABLE . '
				WHERE user_id = ' . $user->data['user_id'] . '
				AND forum_id = 0
				AND topic_id = 0
			ORDER BY save_time DESC';
	}
	$result = $db->sql_query($sql);

	$draftrows = $topic_ids = array();

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['topic_id'])
		{
			$topic_ids[] = (int) $row['topic_id'];
		}
		$draftrows[] = $row;
	}
	$db->sql_freeresult($result);
				
	if (sizeof($topic_ids))
	{
		$sql = 'SELECT topic_id, forum_id, topic_title
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id IN (' . implode(',', array_unique($topic_ids)) . ')';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_rows[$row['topic_id']] = $row;
		}
		$db->sql_freeresult($result);
	}
	unset($topic_ids);
	
	if (sizeof($draftrows))
	{
		$row_count = 0;
		$template->assign_var('S_SHOW_DRAFTS', true);

		foreach ($draftrows as $draft)
		{
			$link_topic = $link_forum = $link_pm = false;
			$insert_url = $view_url = $title = '';

			if (isset($topic_rows[$draft['topic_id']]) && $auth->acl_get('f_read', $topic_rows[$draft['topic_id']]['forum_id']))
			{
				$link_topic = true;
				$view_url = "viewtopic.$phpEx$SID&amp;f=" . $topic_rows[$draft['topic_id']]['forum_id'] . "&amp;t=" . $draft['topic_id'];
				$title = $topic_rows[$draft['topic_id']]['topic_title'];

				$insert_url = "posting.$phpEx$SID&amp;f=" . $topic_rows[$draft['topic_id']]['forum_id'] . '&amp;t=' . $draft['topic_id'] . '&amp;mode=reply&amp;d=' . $draft['draft_id'];
			}
			else if ($auth->acl_get('f_read', $draft['forum_id']))
			{
				$link_forum = true;
				$view_url = "viewforum.$phpEx$SID&amp;f=" . $draft['forum_id'];
				$title = $draft['forum_name'];

				$insert_url = "posting.$phpEx$SID&amp;f=" . $draft['forum_id'] . '&amp;mode=post&amp;d=' . $draft['draft_id'];
			}
			else
			{
				$link_pm = true;
				$insert_url = "ucp.$phpEx$SID&amp;i=$id&amp;mode=compose&amp;d=" . $draft['draft_id'];
			}
						
			$template->assign_block_vars('draftrow', array(
				'DRAFT_ID'		=> $draft['draft_id'],
				'DATE'			=> $user->format_date($draft['save_time']),
				'DRAFT_SUBJECT'	=> $draft['draft_subject'],

				'TITLE'			=> $title,
				'U_VIEW'		=> $view_url,
				'U_INSERT'		=> $insert_url,

				'S_ROW_COUNT'	=> $row_count++,
				'S_LINK_PM'		=> $link_pm,
				'S_LINK_TOPIC'	=> $link_topic,
				'S_LINK_FORUM'	=> $link_forum)
			);
		}
	}
}

?>