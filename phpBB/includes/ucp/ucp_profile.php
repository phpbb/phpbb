<?php
/***************************************************************************
 *                              ucp_profile.php
 *                            -------------------
 *   begin                : Saturday, Feb 21, 2003
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

class ucp_profile extends ucp
{
	function main($id)
	{
		global $censors, $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$submode = ($_REQUEST['mode']) ? htmlspecialchars($_REQUEST['mode']) : 'reg_details';
		$error = '';

		$submodules['REG_DETAILS']	= "i=$id&amp;mode=reg_details";
		$submodules['PROFILE_INFO']	= "i=$id&amp;mode=profile_info";
		$submodules['SIGNATURE']	= "i=$id&amp;mode=signature";       
		$submodules['AVATAR']		= "i=$id&amp;mode=avatar";                               

		$this->subsection($submodules, $submode);
		unset($submodules);

		switch ($submode)
		{
			case 'reg_details':

				if (isset($_POST['submit']))
				{
					$data = array();
					$normalise = array(
						'string' => array(
							'username'			=> $config['min_name_chars'] . ',' . $config['max_name_chars'],
							'password_confirm'	=> $config['min_pass_chars'] . ',' . $config['max_pass_chars'], 
							'new_password'		=> $config['min_pass_chars'] . ',' . $config['max_pass_chars'],
							'cur_password'		=> $config['min_pass_chars'] . ',' . $config['max_pass_chars'], 
							'email'				=> '7,60', 
							'email_confirm'		=> '7,60', 
						)
					);
					$data = $this->normalise_data($_POST, $normalise);

					// md5 current password for checking
					$data['cur_password'] = md5($data['cur_password']);

					$validate = array(
						'reqd'		=> array('username', 'email'), 
						'compare'	=> array(
							'password_confirm'	=> ($data['new_password']) ? $data['new_password'] : '', 
							'cur_password'		=> ($data['new_password'] || $data['email'] != $user->data['user_email'] || $data['username'] != $user->data['username']) ? $user->data['user_password'] : '', 
							'email_confirm'		=> ($data['email'] != $user->data['user_email']) ? $data['email'] : '', 
						),
						'match'		=> array(
							'username'	=> ($data['username'] != $user->data['username']) ? '#^' . str_replace('\\\\', '\\', $config['allow_name_chars']) . '$#iu' : '', 
						), 
						'function'	=> array(
							'username'	=> ($data['username'] != $user->data['username']) ? 'validate_username' : '', 
							'email'		=> ($data['email'] != $user->data['user_email']) ? 'validate_email' : '', 
						), 
					);
					$this->validate_data($data, $validate);

					if (!sizeof($this->error))
					{
						$sql_ary = array(
							'username'		=> ($auth->acl_get('u_chgname') && $config['allow_namechange']) ? $data['username'] : $user->data['username'], 
							'user_email'	=> ($auth->acl_get('u_chgemail')) ? $data['email'] : $user->data['user_email'], 
							'user_password'	=> ($auth->acl_get('u_chgpasswd') && !empty($data['user_password'])) ? md5($data['username']) : $user->data['user_password']
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' 
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						// Need to update config, forum, topic, posting, messages, etc.
						if ($data['username'] != $user->data['username'] && $auth->acl_get('u_chgname') & $config['allow_namechange'])
						{
							$this->update_username($user->data['username'], $data['username']);
						}

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode");
						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode\">", '</a>');
						trigger_error($message);
					}

					//
					extract($data);
					unset($data);
				}

				$user_char_ary = array('.*' => 'USERNAME_CHARS_ANY', '[\w]+' => 'USERNAME_ALPHA_ONLY', '[\w_\+\. \-\[\]]+' => 'USERNAME_ALPHA_SPACERS');

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($this->error)) ? implode('<br />', $this->error) : '',

					'USERNAME'			=> (isset($username)) ? stripslashes($username) : $user->data['username'], 
					'EMAIL'				=> (isset($email)) ? stripslashes($email) : $user->data['user_email'], 
					'NEW_PASSWORD'		=> (isset($new_password)) ? stripslashes($new_password) : '', 
					'CUR_PASSWORD'		=> '', 
					'PASSWORD_CONFIRM'	=> (isset($password_confirm)) ? stripslashes($password_confirm) : '', 

					'L_USERNAME_EXPLAIN'		=> sprintf($user->lang[$user_char_ary[str_replace('\\\\', '\\', $config['allow_name_chars'])] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']), 
					'L_CHANGE_PASSWORD_EXPLAIN'	=> sprintf($user->lang['CHANGE_PASSWORD_EXPLAIN'], $config['min_pass_chars'], $config['max_pass_chars']), 
				
					'S_CHANGE_USERNAME' => ($config['allow_namechange'] && $auth->acl_get('u_chgname')) ? true : false, 
					'S_CHANGE_EMAIL'	=> ($auth->acl_get('u_chgemail')) ? true : false,
					'S_CHANGE_PASSWORD'	=> ($auth->acl_get('u_chgpasswd')) ? true : false)
				);

				break;

			case 'profile_info':

				if (isset($_POST['submit']))
				{
					$data = array();
					$normalise = array(
						'string' => array(
							'icq'		=> '3,15',
							'aim'		=> '5,255',
							'msn'		=> '5,255',
							'yim'		=> '5,255',
							'jabber'	=> '5,255',
							'website'	=> '12,255',
							'location'	=> '2,100', 
							'occupation'=> '2,500', 
							'interests'	=> '2,500', 
						), 
						'int'	=> array('bday_day', 'bday_month', 'bday_year')
					);
					$data = $this->normalise_data($_POST, $normalise);

					$validate = array(
						'match'	=> array(
							'icq'		=> ($data['icq']) ? '#^[0-9]+$#i' : '', 
							'website'	=> ($data['website']) ? '#^http[s]?://(.*?\.)*?[a-z0-9\-]+\.[a-z]{2,4}#i' : '', 
						),
					);
					$this->validate_data($data, $validate);

					if (!sizeof($this->error))
					{
						$sql_ary = array(
							'user_icq'		=> $data['icq'],
							'user_aim'		=> $data['aim'],
							'user_msnm'		=> $data['msn'],
							'user_yim'		=> $data['yim'],
							'user_jabber'	=> $data['jabber'],
							'user_website'	=> $data['website'],
							'user_from'		=> $data['location'],
							'user_occ'		=> $data['occupation'],
							'user_interests'=> $data['interests'],
							'user_birthday'	=> sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']),
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode");
						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode\">", '</a>');
						trigger_error($message);
					}

					//
					extract($data);
					unset($data);
				}

				if (!isset($bday_day))
				{
					list($bday_day, $bday_month, $bday_year) = explode('-', $user->data['user_birthday']);
				}

				$s_birthday_day_options = '<option value="0"' . ((!$bday_day) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $bday_day) ? ' selected="selected"' : '';
					$s_birthday_day_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_birthday_month_options = '<option value="0"' . ((!$bday_month) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $bday_month) ? ' selected="selected"' : '';
					$s_birthday_month_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				$s_birthday_year_options = '';

				$now = getdate();
				$s_birthday_year_options = '<option value="0"' . ((!$bday_year) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = $now['year'] - 100; $i < $now['year']; $i++)
				{
					$selected = ($i == $bday_year) ? ' selected="selected"' : '';
					$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				unset($now);

				$template->assign_vars(array(
					'ERROR'		=> (sizeof($this->error)) ? implode('<br />', $this->error) : '',

					'ICQ'		=> (isset($icq)) ? $icq : $user->data['user_icq'], 
					'YIM'		=> (isset($yim)) ? $yim : $user->data['user_yim'], 
					'AIM'		=> (isset($aim)) ? $aim : $user->data['user_aim'], 
					'MSN'		=> (isset($msn)) ? $msn : $user->data['user_msnm'], 
					'JABBER'	=> (isset($jabber)) ? $jabber : $user->data['user_jabber'], 
					'WEBSITE'	=> (isset($website)) ? $website : $user->data['user_website'], 
					'LOCATION'	=> (isset($location)) ? $location : $user->data['user_from'], 
					'OCCUPATION'=> (isset($occupation)) ? $occupation : $user->data['user_occ'], 
					'INTERESTS'	=> (isset($interests)) ? $interests : $user->data['user_interests'], 

					'S_BIRTHDAY_DAY_OPTIONS'	=> $s_birthday_day_options, 
					'S_BIRTHDAY_MONTH_OPTIONS'	=> $s_birthday_month_options, 
					'S_BIRTHDAY_YEAR_OPTIONS'	=> $s_birthday_year_options,)
				);
				break;

			case 'signature':

				include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);

				$html_status = ($config['allow_html']) ? true : false; 
				$bbcode_status = ($config['allow_bbcode']) ? true : false; 
				$smilies_status = ($config['allow_smilies']) ? true : false; 
				$img_status = ($config['allow_img']) ? true : false; 
				$flash_status = ($config['allow_flash']) ? true : false; 

				$enable_html = (isset($_POST['disable_html'])) ? !$_POST['disable_html'] : $config['allow_html'];
				$enable_bbcode = (isset($_POST['disable_bbcode'])) ? !$_POST['disable_bbcode'] : $config['allow_bbcode'];
				$enable_smilies = (isset($_POST['disable_smilies'])) ? !$_POST['disable_smilies'] : $config['allow_smilies'];
				$enable_urls = (isset($_POST['disable_magic_url'])) ? !$_POST['disable_magic_url'] : 1;

				decode_text($user->data['user_sig'], $user->data['user_sig_bbcode_uid']);
				$signature = (!empty($_POST['signature'])) ? htmlspecialchars($_POST['signature']) : $user->data['user_sig'];

				$error = array();
				if ($_POST['submit'])
				{
					if (strlen($signature) > $config['max_sig_chars'])
					{
						$error[] = $user->lang['SIGNATURE_TOO_LONG'];
					}

					if (!sizeof($error))
					{
						include($phpbb_root_path . 'includes/message_parser.'.$phpEx);

						$message_parser = new parse_message();
						$message_parser->message = trim(stripslashes($signature));
						$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies);
						$signature = $message_parser->message;

						$sql_ary = array(
							'user_sig'					=> $signature, 
							'user_sig_bbcode_uid'		=> $message_parser->bbcode_uid, 
							'user_sig_bbcode_bitfield'	=> $message_parser->bbcode_bitfield
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' 
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode\">", '</a>');
						trigger_error($message);
					}
				}

				$signature_preview = '';
				if ($_POST['preview'])
				{
					// Fudge-o-rama ...
					include($phpbb_root_path . 'includes/message_parser.'.$phpEx);

					$signature_preview = $signature;

					$message_parser = new parse_message();
					$message_parser->message = trim(stripslashes($signature_preview));
					$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies);
					$signature_preview = $message_parser->message;

					if ($enable_bbcode)
					{
						include($phpbb_root_path . 'includes/bbcode.'.$phpEx);
						$bbcode = new bbcode($message_parser->bbcode_bitfield);

						// Second parse bbcode here
						$signature_preview = $bbcode->bbcode_second_pass($signature_preview, $message_parser->bbcode_uid);
					}

					// If we allow users to disable display of emoticons
					// we'll need an appropriate check and preg_replace here
					$signature_preview = (empty($enable_smilies) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $signature_preview) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $signature_preview);

					// Replace naughty words such as farty pants
					if (sizeof($censors))
					{
						$signature_preview = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $signature_preview . '<'), 1, -1));
					}

					$signature_preview = str_replace("\n", '<br />', $signature_preview);
				}

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '', 
					'SIGNATURE'			=> $signature,
					'SIGNATURE_PREVIEW'	=> $signature_preview, 
					
					'S_SIGNATURE_PREVIEW' => ($signature_preview) ? true : false, 

					'S_HTML_CHECKED' 		=> (!$enable_html) ? 'checked="checked"' : '',
					'S_BBCODE_CHECKED' 		=> (!$enable_bbcode) ? 'checked="checked"' : '',
					'S_SMILIES_CHECKED' 	=> (!$enable_smilies) ? 'checked="checked"' : '',
					'S_MAGIC_URL_CHECKED' 	=> (!$enable_urls) ? 'checked="checked"' : '',

					'HTML_STATUS'	=> ($html_status) ? $user->lang['HTML_IS_ON'] : $user->lang['HTML_IS_OFF'],
					'BBCODE_STATUS'	=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>'),
					'SMILIES_STATUS'=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
					'IMG_STATUS'	=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
					'FLASH_STATUS'	=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],

					'L_SIGNATURE_EXPLAIN'	=> sprintf($user->lang['SIGNATURE_EXPLAIN'], $config['max_sig_chars']), 

					'S_HTML_ALLOWED'	=> $config['allow_html'], 
					'S_BBCODE_ALLOWED'	=> $config['allow_bbcode'], 
					'S_SMILIES_ALLOWED'	=> $config['allow_smilies'],)
				);
				break;

			case 'avatar':

				// Can we upload? 
				$can_upload = ($config['allow_avatar_upload'] && file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && $auth->acl_get('u_chgavatar') && (@ini_get('file_uploads') || @ini_get('file_uploads') == 'On')) ? true : false;


				if (isset($_POST['submit']))
				{
					$data = array();
					if (!empty($_FILES['uploadfile']['tmp_name']) && $can_upload)
					{
						$this->avatar_upload($data);
					}
					else if (!empty($_POST['uploadurl']) && $can_upload)
					{
						$normalise = array(
							'string' => array(
								'uploadurl'	=> '1,255',
							)
						);
						$data = $this->normalise_data($_POST, $normalise);

						$this->avatar_upload($data);
					}
					else if (!empty($_POST['remotelink']) && $auth->acl_get('u_chgavatar') && $config['allow_avatar_remote'])
					{
						$normalise = array(
							'string' => array(
								'remotelink'	=> '1,255',
								'width'			=> '1,3',
								'height'		=> '1,3',
							)
						);
						$data = $this->normalise_data($_POST, $normalise);

						$this->avatar_remote($data);
					}
					else if (!empty($_POST['delete']) && $auth->acl_get('u_chgavatar'))
					{
						$data['filename'] = $data['width'] = $data['height'] = '';
					}

					if (!sizeof($this->error))
					{
						// Do we actually have any data to update?
						if (sizeof($data))
						{
							$sql_ary = array(
								'user_avatar'			=> $data['filename'], 
								'user_avatar_type'		=> $data['type'], 
								'user_avatar_width'		=> $data['width'], 
								'user_avatar_height'	=> $data['height'], 
							);

							$sql = 'UPDATE ' . USERS_TABLE . ' 
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' 
								WHERE user_id = ' . $user->data['user_id'];
							$db->sql_query($sql);

							// Delete old avatar if present
							if ($user->data['user_avatar'] != '' && $data['filename'] != $user->data['user_avatar'])
							{
								$this->avatar_delete();
							}
						}

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode");
						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode\">", '</a>');
						trigger_error($message);
					}

					//
					extract($data);
					unset($data);
				}


				// Generate users avatar
				$avatar_img = '';
				if ($user->data['user_avatar'])
				{
					switch ($user->data['user_avatar_type'])
					{
						case AVATAR_UPLOAD:
							$avatar_img = $config['avatar_path'] . '/';
							break;
						case AVATAR_GALLERY:
							$avatar_img = $config['avatar_gallery_path'] . '/';
							break;
					}
					$avatar_img .= $user->data['user_avatar'];

					$avatar_img = '<img src="' . $avatar_img . '" width="' . $user->data['user_avatar_width'] . '" height="' . $user->data['user_avatar_height'] . '" border="0" alt="" />';
				}


				$template->assign_vars(array(
					'ERROR'			=> (sizeof($this->error)) ? implode('<br />', $this->error) : '', 

					'AVATAR'		=> $avatar_img, 
					'AVATAR_SIZE'	=> $config['avatar_filesize'], 
					'AVATAR_URL'	=> (isset($uploadurl)) ? $uploadurl : '', 
					'AVATAR_REMOTE'	=> (isset($remotelink)) ? $remotelink : (($user->data['user_avatar_type'] == AVATAR_REMOTE) ? $user->data['user_avatar'] : ''), 
					'WIDTH'			=> (isset($width)) ? $width : $user->data['user_avatar_width'], 
					'HEIGHT'		=> (isset($height)) ? $height : $user->data['user_avatar_height'], 

					'L_AVATAR_EXPLAIN'	=> sprintf($user->lang['AVATAR_EXPLAIN'], $config['avatar_max_width'], $config['avatar_max_height'], round($config['avatar_filesize'] / 1024)), 

					'S_FORM_ENCTYPE'		=> ($can_upload) ? ' enctype="multipart/form-data"' : '', 
					'S_UPLOAD_AVATAR_FILE'	=> $can_upload,
					'S_UPLOAD_AVATAR_URL'	=> $can_upload, 
					'S_LINK_AVATAR'			=> ($auth->acl_get('u_chgavatar') && $config['allow_avatar_remote']) ? true : false, 
					'S_GALLERY_AVATAR'		=> ($auth->acl_get('u_chgavatar') && $config['allow_avatar_local']) ? true : false,
					'S_AVATAR_CAT_OPTIONS'	=> $s_categories, 
					'S_AVATAR_PAGE_OPTIONS'	=> $s_pages,)
				);

				break;
		}

		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['UCP_' . strtoupper($submode)],

			'S_DISPLAY_' . strtoupper($submode)	=> true, 
			'S_HIDDEN_FIELDS'					=> $s_hidden_fields,
			'S_UCP_ACTION'						=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode")
		);

		$this->display($user->lang['UCP_PROFILE'], 'ucp_profile.html');
	}
}

?>