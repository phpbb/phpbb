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

// Fill smiley templates (or just the variables) with smileys
// Either in a window or inline
function generate_smilies($mode)
{
	global $SID, $auth, $db, $user, $config, $template;
	global $starttime, $phpEx, $phpbb_root_path;

	$max_smilies_inline = 20;

	if ($mode == 'window')
	{
		page_header($user->lang['SMILIES'] . ' - ' . $topic_title);

		$template->set_filenames(array(
			'body' => 'posting_smilies.html')
		);
	}

	$sql = 'SELECT emoticon, code, smile_url, smile_width, smile_height
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

// Format text to be displayed - from viewtopic.php - centralizing this would be nice ;)
function format_display($message, $html, $bbcode, $uid, $url, $smilies, $sig)
{
	global $auth, $forum_id, $config, $censors, $user, $bbcode, $phpbb_root_path;

	// If the board has HTML off but the post has HTML
	// on then we process it, else leave it alone
	if ($html && $auth->acl_get('f_bbcode', $forum_id))
	{
		$message = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $message);
	}

	// Second parse bbcode here
	$message = $bbcode->bbcode_second_pass($message, $uid);

	// If we allow users to disable display of emoticons
	// we'll need an appropriate check and preg_replace here
	$message = (empty($smilies) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $message);

	// Replace naughty words such as farty pants
	if (sizeof($censors))
	{
		$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $message . '<'), 1, -1));
	}

	$message = str_replace("\n", '<br />', $message);

	// Signature
	$user_sig = ($sig && $config['allow_sig']) ? trim($user->data['user_sig']) : '';
	
	if ($user_sig != '' && $auth->acl_get('f_sigs', $forum_id))
	{
		if (!$auth->acl_get('f_html', $forum_id) && $user->data['user_allowhtml'])
		{
			$user_sig = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $user_sig);
		}

		$user_sig = (empty($user->data['user_allowsmile']) || empty($config['enable_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $user_sig) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $user_sig);

		if (sizeof($censors))
		{
			$user_sig = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $user_sig . '<'), 1, -1));
		}

		$user_sig = '<br />_________________<br />' . str_replace("\n", '<br />', $user_sig);
	}
	else
	{
		$user_sig = '';
	}
		
//	$message = (empty($smilies) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $message);

	$message .= $user_sig;

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

	$sql = "SELECT p.post_id, p.poster_id, p.post_time, u.username, p.post_username " . $sql_select_add . " 
		FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . TOPICS_TABLE . " t " . $sql_table_add . "
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
			if (strstr($attach_id_array, ', '))
			{
				$attach_id_array = explode(', ', $attach_id_array);
			}
			else if (strstr($attach_id_array, ','))
			{
				$attach_id_array = explode(',', $attach_id_array);
			}
			else
			{
				$attach_id = intval($attach_id_array);
				$attach_id_array = array();
				$attach_id_array[] = $attach_id;
			}
		}
	
		// Get the post_ids to fill the array
		$sql = 'SELECT ' . (($page == 'privmsgs') ? 'privmsgs_id' : 'post_id') . ' as id 
			FROM ' . ATTACHMENTS_TABLE . ' 
			WHERE attach_id IN (' . implode(', ', $attach_id_array) . ')
			GROUP BY id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$post_id_array[] = intval($row['id']);
		}
		$db->sql_freeresult($result);
		
		if (count($post_id_array) == 0)
		{
			return;
		}
	}
		
	if (!is_array($post_id_array))
	{
		if (trim($post_id_array) == '')
		{
			return;
		}

		if (strstr($post_id_array, ', '))
		{
			$post_id_array = explode(', ', $post_id_array);
		}
		else if (strstr($post_id_array, ','))
		{
			$post_id_array = explode(',', $post_id_array);
		}
		else
		{
			$post_id = intval($post_id_array);
			$post_id_array = array();
			$post_id_array[] = $post_id;
		}
	}
		
	if (count($post_id_array) == 0)
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

		while ($row = $db->sql_fetchrow($result))
		{
			$attach_id_array[] = intval($row['attach_id']);
		}
		$db->sql_freeresult($result);
		
		if (count($attach_id_array) == 0)
		{
			return;
		}
	}
	
	if (!is_array($attach_id_array))
	{
		if (strstr($attach_id_array, ', '))
		{
			$attach_id_array = explode(', ', $attach_id_array);
		}
		else if (strstr($attach_id_array, ','))
		{
			$attach_id_array = explode(',', $attach_id_array);
		}
		else
		{
			$attach_id = intval($attach_id_array);
			$attach_id_array = array();
			$attach_id_array[] = $attach_id;
		}
	}

	if (count($attach_id_array) == 0)
	{
		return;
	}

	if ($page == 'privmsgs')
	{
		$sql_id = 'privmsgs_id';
		if ($user_id != -1)
		{
			$post_id_array_2 = array();

			$sql = 'SELECT privmsgs_type, privmsgs_to_userid, privmsgs_from_userid
				FROM ' . PRIVMSGS_TABLE . '
				WHERE privmsgs_id IN (' . implode(', ', $post_id_array) . ')';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				switch (intval($row['privmsgs_type']))
				{
					case PRIVMSGS_READ_MAIL:
					case PRIVMSGS_NEW_MAIL:
					case PRIVMSGS_UNREAD_MAIL:
						if ($row['privmsgs_to_userid'] == $user_id)
						{
							$post_id_array_2[] = $privmsgs_id;
						}
						break;

					case PRIVMSGS_SENT_MAIL:
						if ($row['privmsgs_from_userid'] == $user_id)
						{
							$post_id_array_2[] = $privmsgs_id;
						}
						break;
					
					case PRIVMSGS_SAVED_OUT_MAIL:
						if ($row['privmsgs_from_userid'] == $user_id)
						{
							$post_id_array_2[] = $privmsgs_id;
						}
						break;
					
					case PRIVMSGS_SAVED_IN_MAIL:
						if ($row['privmsgs_to_userid'] == $user_id)
						{
							$post_id_array_2[] = $privmsgs_id;
						}
						break;
				}
			}
			$db->sql_freeresult($result);
			$post_id_array = $post_id_array_2;
		}
	}
	else
	{
		$sql_id = 'post_id';
	}

	$sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . ' 
		WHERE attach_id IN (' . implode(', ', $attach_id_array) . ') 
			AND ' . $sql_id . ' IN (' . implode(', ', $post_id_array) . ')';
	$db->sql_query($sql);
	
	foreach ($attach_id_array as $attach_id)
	{
		$sql = 'SELECT attach_id 
			FROM ' . ATTACHMENTS_TABLE . ' 
			WHERE attach_id = ' . $attach_id;
		$select_result = $db->sql_query($sql);			

		if (!is_array($db->sql_fetchrow($select_result)))
		{
			$sql = 'SELECT attach_id, physical_filename, thumbnail
				FROM ' . ATTACHMENTS_DESC_TABLE . '
				WHERE attach_id = ' . $attach_id;
			$result = $db->sql_query($sql);	
		
			// delete attachments
			while ($row = $db->sql_fetchrow($result))
			{
				phpbb_unlink($row['physical_filename'], 'file', $config['use_ftp_upload']);
				if (intval($row['thumbnail']))
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
			$topic_id = intval($row['topic_id']);

			$sql = 'SELECT post_id 
				FROM ' . POSTS_TABLE . ' 
				WHERE topic_id = ' . $topic_id . '
				GROUP BY post_id';
			$result2 = $db->sql_query($sql);		
			
			$post_ids = array();

			while ($post_row = $db->sql_fetchrow($result2))
			{
				$post_ids[] = intval($post_row['post_id']);
			}
			$db->sql_freeresult($result2);

			if (count($post_ids))
			{
				$post_id_sql = implode(', ', $post_ids);
	
				$sql = 'SELECT attach_id 
					FROM ' . ATTACHMENTS_TABLE . ' 
					WHERE post_id IN (' . $post_id_sql . ') ';
				$select_result = $db->sql_query_limit($sql, 1);
				$set_id = ( !is_array($db->sql_fetchrow($select_result))) ? 0 : 1;
				$db->sql_freeresult($select_result);

				$sql = 'UPDATE ' . TOPICS_TABLE . ' 
					SET topic_attachment = ' . $set_id . ' 
					WHERE topic_id = ' . $topic_id;
				$db->sql_query($sql);
				
				foreach ($post_ids as $post_id)
				{
					$sql = 'SELECT attach_id 
						FROM ' . ATTACHMENTS_TABLE . ' 
						WHERE post_id = ' . $post_id;
					$select_result = $db->sql_query_limit($sql, 1);
					$set_id = ( !is_array($db->sql_fetchrow($select_result))) ? 0 : 1;
					$db->sql_freeresult($select_result);
		
					$sql = 'UPDATE ' . POSTS_TABLE . ' 
						SET post_attachment = ' . $set_id . ' 
						WHERE post_id = ' . $post_id;
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
	global $_POST, $_FILES, $auth, $user, $config, $db;

	$filedata = array();
	$filedata['error'] = array();
	$filedata['post_attach'] = ($filename != '') ? true : false;

	if (!$filedata['post_attach'])
	{
		return $filedata;
	}

	$r_file = $filename;
	$file = $_FILES['fileupload']['tmp_name'];
	$filedata['mimetype'] = $_FILES['fileupload']['type'];
		
	// Opera add the name to the mime type
	$filedata['mimetype'] = ( strstr($filedata['mimetype'], '; name') ) ? str_replace(strstr($filedata['mimetype'], '; name'), '', $filedata['mimetype']) : $filedata['mimetype'];
	$filedata['extension'] = array_pop(explode('.', strtolower($filename)));
	$filedata['filesize'] = (!@filesize($file)) ? intval($_FILES['size']) : @filesize($file);

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
	if ( preg_match("/[\\/:*?\"<>|]/i", $filename) )
	{ 
		$filedata['error'][] = sprintf($user->lang['INVALID_FILENAME'], $filename);
		$filedata['post_attach'] = false;
		return $filedata;
	}

	// check php upload-size
	if ( ($file == 'none') ) 
	{
		$filedata['error'][] = (@ini_get('upload_max_filesize') == '') ? $user->lang['ATTACHMENT_PHP_SIZE_NA'] : sprintf($user->lang['ATTACHMENT_PHP_SIZE_OVERRUN'], @ini_get('upload_max_filesize'));
		$filedata['post_attach'] = false;
		return $filedata;
	}

	// Check Image Size, if it is an image
	if (!$auth->acl_gets('m_', 'a_') && $cat_id == IMAGE_CAT)
	{
		list($width, $height) = getimagesize($file);

		if ($width != 0 && $height != 0 && intval($config['img_max_width']) != 0 && intval($config['img_max_height']) != 0)
		{
			if ($width > intval($config['img_max_width']) || $height > intval($attach_config['img_max_height']))
			{
				$filedata['error'][] = sprintf($user->lang['Error_imagesize'], intval($attach_config['img_max_width']), intval($attach_config['img_max_height']));
				$filedata['post_attach'] = false;
				return $filedata;
			}
		}
	}

	// check Filesize 
	if ($allowed_filesize != 0 && $filedata['filesize'] > $allowed_filesize && !$acl->gets('m_', 'a_'))
	{
		$size_lang = ($allowed_filesize >= 1048576) ? $user->lang['MB'] : ( ($allowed_filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );

		if ($allowed_filesize >= 1048576)
		{
			$allowed_filesize = round($allowed_filesize / 1048576 * 100) / 100;
		}
		else if($allowed_filesize >= 1024)
		{
			$allowed_filesize = round($allowed_filesize / 1024 * 100) / 100;
		}
			
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

/*
	// If we are at Private Messaging, check our PM Quota
	if ($this->page == PAGE_PRIVMSGS)
	{
		$to_user = ( isset($_POST['username']) ) ? $_POST['username'] : '';
				
		if (intval($config['pm_filesize_limit']) != 0)
		{
			$total_filesize = get_total_attach_pm_filesize('from_user', $user->data['user_id']);

			if ( ($total_filesize + $filedata['filesize'] > intval($config['pm_filesize_limit'])) ) 
			{
				$error = TRUE;
				if(!empty($error_msg))
				{
					$error_msg .= '<br />';
				}
				$error_msg .= $lang['Attach_quota_sender_pm_reached'];
			}
		}

		// Check Receivers PM Quota
		if ((!empty($to_user)) && ($userdata['user_level'] != ADMIN))
		{
			$sql = "SELECT user_id
				FROM " . USERS_TABLE . "
				WHERE username = '" . $to_user . "'";
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$user_id = intval($row['user_id']);
			$u_data = get_userdata($user_id);
			$this->get_quota_limits($u_data, $user_id);

			if (intval($attach_config['pm_filesize_limit']) != 0)
			{
				$total_filesize = get_total_attach_pm_filesize('to_user', $user_id);
						
				if ($total_filesize + $this->filesize > intval($attach_config['pm_filesize_limit'])) 
				{
					$error = TRUE;
					if(!empty($error_msg))
					{
						$error_msg .= '<br />';
					}
					$error_msg .= sprintf($lang['Attach_quota_receiver_pm_reached'], $to_user);
				}
			}
		}
	}
*/			
	$filedata['thumbnail'] = 0;
				
	// Prepare Values
	$filedata['filetime'] = time(); 
	$filedata['filename'] = stripslashes($r_file);

	$filedata['destination_filename'] = strtolower($filedata['filename']);
	$filedata['destination_filename'] = $user->data['user_id'] . '_' . $filedata['filetime'] . '.' . $filedata['extension'];
				
	$filedata['filename'] = str_replace("'", "\'", $filedata['filename']);
			
/*
	// Do we have to create a thumbnail ?
	if ($cat_id == IMAGE_CAT && $config['img_create_thumbnail'])
	{
		$filedata['thumbnail'] = 1;
	}
*/

	// Upload Attachment
	if (!$config['use_ftp_upload'])
	{
		// Descide the Upload method
		if ( @ini_get('open_basedir') )
		{
			$upload_mode = 'move';
		}
		else if ( @ini_get('safe_mode') )
		{
			$upload_mode = 'move';
		}
		else
		{
			$upload_mode = 'copy';
		}
	}
	else
	{
		$upload_mode = 'ftp';
	}

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

		case 'ftp':
/*
			$conn_id = init_ftp();

			// Binary or Ascii ?
			$mode = FTP_BINARY;
			if ( (preg_match("/text/i", $filedata['mimetype'])) || (preg_match("/html/i", $filedata['mimetype'])) )
			{
				$mode = FTP_ASCII;
			}

			$res = @ftp_put($conn_id, $destination_filename, $source_filename, $mode);
				
			if (!$res)
			{
				@ftp_quit($conn_id);
				return sprintf($user->lang['Ftp_error_upload'], $config['ftp_path']);
			}

			@ftp_site($conn_id, 'CHMOD 0644 ' . $destination_filename);
			@ftp_quit($conn_id);
			break;
*/
	}

	$filedata['thumbnail'] = 0;
/*	if ($filedata['thumbnail'])
	{
		if ($upload_mode == 'ftp')
		{
			$source = $source_filename;
			$destination = 'thumbs/t_' . $destination_filename;
		}
		else
		{
			$source = $config['upload_dir'] . '/' . $destination_filename;
			$destination = phpbb_realpath($config['upload_dir']);
			$destination .= '/thumbs/t_' . $destination_filename;
		}

		if (!create_thumbnail($source, $destination, $filedata['mimetype']))
		{
			if (!create_thumbnail($source_filename, $destination_filename, $filedata['mimetype']))
			{
				$filedata['thumbnail'] = 0;
			}
		}
	}*/
	return '';
}

// Delete File
function phpbb_unlink($filename, $mode = 'file', $use_ftp = false)
{
	global $config, $user;

	if (!$use_ftp)
	{
		$filename = ($mode == 'thumbnail') ? $config['upload_dir'] . '/thumbs/t_' . $filename : $config['upload_dir'] . '/' . $filename;
		$deleted = @unlink($filename);

		if (@file_exists($filename))
		{
			$filesys = eregi_replace('/','\\', $filename);
			$deleted = @system("del $filesys");

			if (@file_exists($filename)) 
			{
				@chmod($filename, 0777);
				$deleted = @unlink($filename);
				if (!$deleted)
				{
					$deleted = @system("del $filename");
				}
			}
		}
	}
	else
	{
/*		$conn_id = attach_init_ftp($mode);

		if ($mode == MODE_THUMBNAIL)
		{
			$filename = 't_' . $filename;
		}
		
		$res = @ftp_delete($conn_id, $filename);
		if (!$res)
		{
			if (defined('DEBUG_EXTRA'))
			{
				$add = ( $mode == MODE_THUMBNAIL ) ? ('/' . THUMB_DIR) : ''; 
				message_die(GENERAL_ERROR, sprintf($lang['Ftp_error_delete'], $attach_config['ftp_path'] . $add));
			}

			return $deleted;
		}

		@ftp_quit($conn_id);

		$deleted = TRUE;*/
	}

	return $deleted;
}






//
// posting.php specific
//


// Submit Post
function submit_post($mode, $message, $subject, $username, $topic_type, $bbcode_uid, $poll, $attachment_data, $filename_data, $post_data)
{
	global $db, $auth, $user, $config, $phpEx, $SID, $template;

	$search = new fulltext_search();
	$current_time = time();

	$post_data['subject'] = $subject;

	$db->sql_transaction();

	// Initial Topic table info
	if ( ($mode == 'post') || ($mode == 'edit' && $post_data['topic_first_post_id'] == $post_data['post_id']))
	{
		$topic_sql = array(
			'forum_id' 					=> $post_data['forum_id'],
			'topic_title' 				=> stripslashes($subject),
			'topic_time'				=> $current_time,
			'topic_type'				=> $topic_type,
			'topic_approved'			=> ($auth->acl_get('f_moderate', $post_data['forum_id']) && !$auth->acl_get('f_ignorequeue', $post_data['forum_id'])) ? 0 : 1, 
			'icon_id'					=> $post_data['icon_id'],
			'topic_attachment'			=> (sizeof($filename_data['physical_filename'])) ? 1 : 0
		);

		if (!empty($poll['poll_options']))
		{
			$topic_sql = array_merge($topic_sql, array(
				'poll_title'			=> stripslashes($poll['poll_title']),
				'poll_start'			=> ($poll['poll_start']) ? $poll['poll_start'] : $current_time, 
				'poll_max_options'		=> $poll['poll_max_options'], 
				'poll_length'			=> $poll['poll_length'] * 86400)
			);
		}

		if ($mode == 'post')
		{
			$topic_sql = array_merge($topic_sql, array(
				'topic_poster'				=> intval($user->data['user_id']),
				'topic_first_poster_name'	=> ($username != '') ? stripslashes($username) : (($user->data['user_id'] == ANONYMOUS) ? '' : stripslashes($user->data['username'])))
			);
		}
		
		$sql = ($mode == 'post') ? 'INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $topic_sql) : 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $topic_sql) . ' WHERE topic_id = ' . $post_data['topic_id'];
		$db->sql_query($sql);

		$post_data['topic_id'] = ($mode == 'post') ? $db->sql_nextid() : $post_data['topic_id'];
	}

	// Post table info
	$post_sql = array(
		'topic_id' 			=> $post_data['topic_id'],
		'forum_id' 			=> $post_data['forum_id'],
		'poster_id' 		=> ($mode == 'edit') ? $post_data['poster_id'] : intval($user->data['user_id']),
		'post_username'		=> ($username != '') ? stripslashes($username) : '', 
		'post_subject'		=> stripslashes($subject),
		'icon_id'			=> $post_data['icon_id'], 
		'poster_ip' 		=> $user->ip,
		'post_approved' 	=> ($auth->acl_get('f_moderate', $post_data['forum_id']) && !$auth->acl_get('f_ignorequeue', $post_data['forum_id'])) ? 0 : 1,
		'post_edit_time' 	=> ($mode == 'edit' && $post_data['poster_id'] == $user->data['user_id']) ? $current_time : 0,
		'enable_sig' 		=> $post_data['enable_sig'],
		'enable_bbcode' 	=> $post_data['enable_bbcode'],
		'enable_html' 		=> $post_data['enable_html'],
		'enable_smilies' 	=> $post_data['enable_smilies'],
		'enable_magic_url' 	=> $post_data['enable_urls'],
		'bbcode_uid'		=> $bbcode_uid,
		'bbcode_bitfield'	=> $post_data['bbcode_bitfield'],
		'post_edit_locked'	=> $post_data['post_edit_locked'],
		'post_text' 		=> $message
	);

	if ($mode != 'edit')
	{
		$post_sql['post_time'] = $current_time;
	}

	if ($mode != 'edit' || $post_data['message_md5'] != $post_data['post_checksum'])
	{
		$post_sql = array_merge($post_sql, array(
			'post_checksum' => $post_data['message_md5'],
			'post_encoding' => $user->lang['ENCODING'])
		);
	}
	
	if ($mode == 'edit')
	{
		$sql = 'UPDATE ' . POSTS_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $post_sql) . 
			(($post_data['poster_id'] == $user->data['user_id']) ? ' , post_edit_count = post_edit_count + 1' : '') . '
			WHERE post_id = ' . $post_data['post_id'];
	}
	else
	{
		$sql = 'INSERT INTO ' . POSTS_TABLE . ' ' . 
			$db->sql_build_array('INSERT', $post_sql);
	}
	$db->sql_query($sql);

	$post_data['post_id'] = ($mode == 'edit') ? $post_data['post_id'] : $db->sql_nextid();

	// Submit Poll
	if (!empty($poll['poll_options']))
	{
		$cur_poll_options = array();
	
		if ($poll['poll_start'] && $mode == 'edit')
		{
			$sql = 'SELECT * FROM ' . POLL_OPTIONS_TABLE . ' 
				WHERE topic_id = ' . $post_data['topic_id'] . '
				ORDER BY poll_option_id';
			$result = $db->sql_query($sql);

			while ($cur_poll_options[] = $db->sql_fetchrow($result));
			$db->sql_freeresult($result);
		}

		for ($i = 0; $i < sizeof($poll['poll_options']); $i++)
		{
			if (trim($poll['poll_options'][$i]) != '')
			{
				if (empty($cur_poll_options[$i]))
				{
					$sql = 'INSERT INTO ' . POLL_OPTIONS_TABLE . "  (poll_option_id, topic_id, poll_option_text)
						VALUES ($i, " . $post_data['topic_id'] . ", '" . $db->sql_escape($poll['poll_options'][$i]) . "')";
					$db->sql_query($sql);
				}
				else if ($poll['poll_options'][$i] != $cur_poll_options[$i])
				{
					$sql = "UPDATE " . POLL_OPTIONS_TABLE . " 
						SET poll_option_text = '" . $db->sql_escape($poll['poll_options'][$i]) . "'
						WHERE poll_option_id = " . $cur_poll_options[$i]['poll_option_id'] . "
							AND topic_id = " . $post_data['topic_id'];
					$db->sql_query($sql);
				}
			}
		}
			
		if (sizeof($poll['poll_options']) < sizeof($cur_poll_options))
		{
			$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . '
				WHERE poll_option_id > ' . sizeof($poll['poll_options']) . ' 
					AND topic_id = ' . $post_data['topic_id'];
			$db->sql_query($sql);
		}
	}

	// Submit Attachments
	if (count($attachment_data) && !empty($post_data['post_id']) && ($mode == 'post' || $mode == 'reply' || $mode == 'edit'))
	{
		foreach ($attachment_data as $attach_row)
		{
			if ($attach_row['attach_id'] != '-1')
			{
				// update entry in db if attachment already stored in db and filespace
				$attach_sql = array(
					'comment' => trim($attach_row['comment'])
				);
			
				$sql = 'UPDATE ' . ATTACHMENTS_DESC_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $attach_sql) . ' WHERE attach_id = ' . intval($attach_row['attach_id']);
				$db->sql_query($sql);
			}
			else
			{
				// insert attachment into db 
				$attach_sql = array(
					'physical_filename' => $attach_row['physical_filename'],
					'real_filename' => $attach_row['real_filename'],
					'comment' => trim($attach_row['comment']),
					'extension' => $attach_row['extension'],
					'mimetype' => $attach_row['mimetype'],
					'filesize' => $attach_row['filesize'],
					'filetime' => $attach_row['filetime'],
					'thumbnail' => $attach_row['thumbnail']
				);

				$sql = 'INSERT INTO ' . ATTACHMENTS_DESC_TABLE . ' ' . $db->sql_build_array('INSERT', $attach_sql);
				$db->sql_query($sql);

				$attach_sql = array(
					'attach_id' => $db->sql_nextid(),
					'post_id' => $post_data['post_id'],
					'privmsgs_id' => 0,
					'user_id_from' => ($mode == 'edit') ? $post_data['poster_id'] : intval($user->data['user_id']),
					'user_id_to' => 0
				);

				$sql = 'INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $attach_sql);
				$db->sql_query($sql);
			}
		}

		if (count($attachment_data))
		{
			$sql = "UPDATE " . POSTS_TABLE . "
				SET post_attachment = 1
				WHERE post_id = " . $post_data['post_id'];
			$db->sql_query($sql);

			$sql = "UPDATE " . TOPICS_TABLE . "
				SET topic_attachment = 1
				WHERE topic_id = " . $post_data['topic_id'];
			$db->sql_query($sql);
		}
	}

	// Fulltext parse
	if ($post_data['message_md5'] != $post_data['post_checksum'])
	{
		$result = $search->add($mode, $post_data['post_id'], $message, $subject);
	}

	// Sync forums, topics and users ...
	if ($mode != 'edit')
	{
		$forum_topics_sql = ($mode == 'post') ? ', forum_topics = forum_topics + 1, forum_topics_real = forum_topics_real + 1' : '';
		$forum_sql = array(
			'forum_last_post_id' 	=> $post_data['post_id'],
			'forum_last_post_time' 	=> $current_time,
			'forum_last_poster_id' 	=> intval($user->data['user_id']),
			'forum_last_poster_name'=> ($user->data['user_id'] == ANONYMOUS) ? stripslashes($username) : $user->data['username'],
		);

		$sql = 'UPDATE ' . FORUMS_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $forum_sql) . ', forum_posts = forum_posts + 1' . $forum_topics_sql . ' 
			WHERE forum_id = ' . $post_data['forum_id'];
		$db->sql_query($sql);

		// Update topic: first/last post info, replies
		$topic_sql = array(
			'topic_last_post_id' 	=> $post_data['post_id'],
			'topic_last_post_time' 	=> $current_time,
			'topic_last_poster_id' 	=> intval($user->data['user_id']),
			'topic_last_poster_name'=> ($username != '') ? stripslashes($username) : (($user->data['user_id'] == ANONYMOUS) ? '' : stripslashes($user->data['username'])),
		);

		if ($mode == 'post')
		{
			$topic_sql = array_merge($topic_sql, array(
				'topic_first_post_id' 		=> $post_data['post_id'],
			));
		}

		$topic_replies_sql = ($mode == 'reply' || $mode == 'quote') ? ', topic_replies = topic_replies + 1, topic_replies_real = topic_replies_real + 1' : '';
		$sql = 'UPDATE ' . TOPICS_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $topic_sql) . $topic_replies_sql . ' 
			WHERE topic_id = ' . $post_data['topic_id'];
		$db->sql_query($sql);

		// Update user post count ... if appropriate
		if ($user->data['user_id'] != ANONYMOUS && $auth->acl_get('f_postcount', $post_data['forum_id']))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_posts = user_posts + 1
				WHERE user_id = ' . intval($user->data['user_id']);
			$db->sql_query($sql);
		}

		// post counts for index, etc.
		if ($mode == 'post')
		{
			set_config('num_topics', $config['num_topics'] + 1, TRUE);
		}

		set_config('num_posts', $config['num_posts'] + 1, TRUE);
	}

	// Topic Notification
	if (($post_data['notify_set'] == 0 || $post_data['notify_set'] == -1) && $post_data['notify'])
	{
		$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id)
			VALUES (" . $user->data['user_id'] . ", " . $post_data['topic_id'] . ")";
		$db->sql_query($sql);
	}
	else if ($post_data['notify_set'] == 1 && !$post_data['notify'])
	{
		$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
			WHERE user_id = " . $user->data['user_id'] . "
				AND topic_id = " . $post_data['topic_id'];
		$db->sql_query($sql);
	}
		
	// Mark this topic as read and posted to.
	$mark_mode = ($mode == 'post' || $mode == 'reply' || $mode == 'quote') ? 'post' : 'topic';
	markread($mark_mode, $post_data['forum_id'], $post_data['topic_id'], $post_data['post_time']);

	$db->sql_transaction('commit');

	// Send Notifications
	if ($mode != 'edit' && $mode != 'delete')
	{
		user_notification($mode, stripslashes($post_data['subject']), $post_data['forum_id'], $post_data['topic_id'], $post_data['post_id']);
	}

	meta_refresh(3, "viewtopic.$phpEx$SID&amp;f=" . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '&amp;p=' . $post_data['post_id'] . '#' . $post_data['post_id']);

	$message = ($auth->acl_get('f_moderate', $post_data['forum_id']) && !$auth->acl_get('f_ignorequeue', $post_data['forum_id'])) ? 'POST_STORED_MOD' : 'POST_STORED';
	$message = $user->lang[$message] . '<br /><br />' . sprintf($user->lang['VIEW_MESSAGE'], '<a href="viewtopic.' . $phpEx . $SID .'&amp;f=' . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '&amp;p=' . $post_data['post_id'] . '#' . $post_data['post_id'] . '">', '</a>') . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID .'&amp;f=' . $post_data['forum_id'] . '">', '</a>');
	trigger_error($message);
}

// User Notification
function user_notification($mode, $subject, $forum_id, $topic_id, $post_id)
{
	global $db, $user, $config, $phpEx;

	$topic_notification = ($mode == 'reply' || $mode == 'quote') ? true : false;
	$newtopic_notification = ($mode == 'post') ? true : false;

	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

	// Get banned User ID's
	$sql = "SELECT ban_userid 
		FROM " . BANLIST_TABLE;
	$result = $db->sql_query($sql);

	$sql_ignore_users = ANONYMOUS . ', ' . $user->data['user_id'];
	while ($row = $db->sql_fetchrow($result))
	{
		if (isset($row['ban_userid']))
		{
			$sql_ignore_users .= ', ' . $row['ban_userid'];
		}
	}

	$allowed_users = array();

	$sql = "SELECT u.user_id
		FROM " . TOPICS_WATCH_TABLE . " tw, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u
		WHERE tw.topic_id = $topic_id 
		AND tw.user_id NOT IN ($sql_ignore_users) 
		AND t.topic_id = tw.topic_id 
		AND u.user_id = tw.user_id";
	$result = $db->sql_query($sql);
	$ids = '';
	
	while ($row = $db->sql_fetchrow($result))
	{
		$ids .= ($ids != '') ? ', ' . $row['user_id'] : $row['user_id'];
	}
	$db->sql_freeresult($result);

	if ($ids != '')
	{
		// TODO: Paul - correct call to check f_read for specific users ?
		$sql = "SELECT a.user_id
			FROM " . ACL_OPTIONS_TABLE . " ao, " . ACL_USERS_TABLE . " a 
			WHERE a.user_id IN (" . $ids . ")
				AND ao.auth_option_id = a.auth_option_id
				AND ao.auth_option = 'f_read'
				AND a.forum_id = " . $forum_id;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$allowed_users[] = $row['user_id'];
		}
		$db->sql_freeresult($result);




		// TODO : Paul
		// Now grab group settings ... users can belong to multiple groups so we grab
		// the minimum setting for all options. ACL_NO overrides ACL_YES so act appropriatley
		$sql = "SELECT ug.user_id, MIN(a.auth_setting) as min_setting
			FROM " . USER_GROUP_TABLE . " ug, " . ACL_OPTIONS_TABLE . " ao, " . ACL_GROUPS_TABLE . " a 
			WHERE ug.user_id IN (" . $ids . ")
				AND a.group_id = ug.group_id
				AND ao.auth_option_id = a.auth_option_id 
				AND ao.auth_option = 'f_read'
				AND a.forum_id = " . $forum_id . "
				GROUP BY ao.auth_option, a.forum_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['min_setting'] == 1)
			{
				$allowed_users[] = $row['user_id'];
			}
		}
		$db->sql_freeresult($result);

		$allowed_users = array_unique($allowed_users);
	}





	//
	if ($topic_notification)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, t.topic_title, f.forum_name
			FROM ' . TOPICS_WATCH_TABLE . ' tw, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u, ' . FORUMS_TABLE . ' f
			WHERE tw.topic_id = ' . $topic_id . '
				AND tw.user_id NOT IN (' . $sql_ignore_users . ') 
				AND tw.notify_status = 0
				AND f.forum_id = ' . $forum_id . '
				AND t.topic_id = tw.topic_id 
				AND u.user_id = tw.user_id';
	}
	else if ($newtopic_notification)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, f.forum_name 
			FROM ' . USERS_TABLE . ' u, ' . FORUMS_WATCH_TABLE . ' fw, ' . FORUMS_TABLE . ' f 
			WHERE fw.forum_id = ' . $forum_id . '
				AND fw.user_id NOT IN (' . $sql_ignore_users . ') 
				AND fw.notify_status = 0
				AND f.forum_id = fw.forum_id
				AND u.user_id = fw.user_id';
	}
	else
	{
		trigger_error('WRONG_NOTIFICATION_MODE');
	}
	$result = $db->sql_query($sql);

	$email_users = array();
	$update_watched_sql_topic = $update_watched_sql_forum = $delete_users_topic = '';
	//
	if ($row = $db->sql_fetchrow($result))
	{
		if ($topic_notification)
		{
			decode_text($row['topic_title']);
			$topic_title = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $row['topic_title']) : $row['topic_title'];
		}
		else
		{
			decode_text($subject);
			$topic_title = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $subject) : $subject;
		}
				
		$which_sql = ($topic_notification) ? 'update_watched_sql_topic' : 'update_watched_sql_forum';
		do
		{
			if (trim($row['user_email']) != '' && in_array($row['user_id'], $allowed_users))
			{
				$row['email_template'] = ($topic_notification) ? 'topic_notify' : 'newtopic_notify';
				$email_users[] = $row;

				$$which_sql .= ($$which_sql != '') ? ', ' . $row['user_id'] : $row['user_id'];
			}
			else if (!in_array($row['user_id'], $allowed_users))
			{
				$delete_users_topic .= ($delete_users_topic != '') ? ', ' . $row['user_id'] : $row['user_id'];
			}
		}
		while ($row = $db->sql_fetchrow($result));
	}
	$db->sql_freeresult($result);
	
	// Handle remaining Notifications (Forum)
	if ($topic_notification)
	{
		$already_notified = ($update_watched_sql_topic == '') ? '' : $update_watched_sql_topic . ', ';
		$already_notified .= ($update_watched_sql_forum == '') ? '' : $update_watched_sql_forum . ', ';

		$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, t.topic_title, f.forum_name 
			FROM ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u, ' . FORUMS_WATCH_TABLE . ' fw, ' . FORUMS_TABLE . ' f 
			WHERE fw.forum_id = ' . $forum_id . '
				AND fw.user_id NOT IN (' . $already_notified . ' ' . $sql_ignore_users . ') 
				AND fw.notify_status = 0
				AND t.topic_id = ' . $topic_id . '
				AND f.forum_id = fw.forum_id
				AND u.user_id = fw.user_id';
		$result = $db->sql_query($sql);
			
		if ($row = $db->sql_fetchrow($result))
		{
			$forum_name = $row['forum_name'];

			do
			{
				if (trim($row['user_email']) != '')
				{
					$row['email_template'] = 'forum_notify';
					$email_users[] = $row;

					$update_watched_sql_forum .= ($update_watched_sql_forum != '') ? ', ' . $row['user_id'] : $row['user_id'];
				}
			}
			while ($row = $db->sql_fetchrow($result));
		}
	}

	// We are using an email queue here, no emails are sent now, only queued.
	// Returned to use the TO-Header, default package size is 100 (should be admin-definable) !?
	if (sizeof($email_users) && $config['email_enable'])
	{
		global $phpbb_root_path, $phpEx;

		@set_time_limit(60);

		include($phpbb_root_path . 'includes/emailer.'.$phpEx);
		$emailer = new emailer(true); // use queue

		$email_list_ary = array();
		foreach ($email_users as $row)
		{ 
			$pos = sizeof($email_list_ary[$row['email_template']]);
			$email_list_ary[$row['email_template']][$pos]['email'] = $row['user_email'];
			$email_list_ary[$row['email_template']][$pos]['name'] = $row['username'];
			$email_list_ary[$row['email_template']][$pos]['lang'] = $row['user_lang'];
		}
		unset($email_users);

		foreach ($email_list_ary as $email_template => $email_list)
		{
			foreach ($email_list as $addr)
			{
				$emailer->template($email_template, $addr['lang']);

				$emailer->replyto($config['board_email']);
				$emailer->to($addr['email'], $addr['name']);

				$emailer->assign_vars(array(
					'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),
					'SITENAME'		=> $config['sitename'],
					'TOPIC_TITLE'	=> trim($topic_title),  
					'FORUM_NAME'	=> trim($forum_name), 

					'U_TOPIC'				=> generate_board_url() . 'viewtopic.'.$phpEx . '?t=' . $topic_id . '&p=' . $post_id . '#' . $post_id,
					'U_FORUM'				=> generate_board_url() . 'viewforum.'.$phpEx . '?f=' . $forum_id,
					'U_STOP_WATCHING_TOPIC' => generate_board_url() . 'viewtopic.'.$phpEx . '?t=' . $topic_id . '&unwatch=topic',
					'U_STOP_WATCHING_FORUM' => generate_board_url() . 'viewforum.'.$phpEx . '?f=' . $forum_id . '&unwatch=forum')
				);

				$emailer->send();
				$emailer->reset();
			}
		}
	
		$emailer->queue->save();
	}
	unset($email_list_ary);
	
	if ($delete_users_topic != '')
	{
		$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
			WHERE topic_id = " . $topic_id . "
				AND user_id IN (" . $delete_users_topic . ")";
		$db->sql_query($sql);
	}

	if ($update_watched_sql_topic != '')
	{
		$sql = "UPDATE " . TOPICS_WATCH_TABLE . "
			SET notify_status = 1
			WHERE topic_id = " . $topic_id . "
				AND user_id IN (" . $update_watched_sql_topic . ")";
		$db->sql_query($sql);
	}

	if ($update_watched_sql_forum != '')
	{
		$sql = "UPDATE " . FORUMS_WATCH_TABLE . "
			SET notify_status = 1
			WHERE forum_id = " . $forum_id . "
				AND user_id IN (" . $update_watched_sql_forum . ")";
		$db->sql_query($sql);
	}
}

?>