<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp_profile.php
// STARTED   : Mon May 19, 2003
// COPYRIGHT : © 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

class ucp_profile extends module
{
	function ucp_profile($id, $mode)
	{
		global $censors, $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$preview	= (!empty($_POST['preview'])) ? true : false;
		$submit		= (!empty($_POST['submit'])) ? true : false;
		$delete		= (!empty($_POST['delete'])) ? true : false;
		$error = $data = array();

		switch ($mode)
		{
			case 'reg_details':

				if ($submit)
				{
					$var_ary = array(
						'username'			=> $user->data['username'], 
						'email'				=> $user->data['user_email'], 
						'email_confirm'		=> (string) '',
						'new_password'		=> (string) '', 
						'cur_password'		=> (string) '', 
						'password_confirm'	=> (string) '', 
					);

					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					$var_ary = array(
						'username'			=> array(
							array('string', false, $config['min_name_chars'], $config['max_name_chars']), 
							array('username', $username)),
						'password_confirm'	=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']), 
						'new_password'		=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']), 
						'cur_password'		=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']), 
						'email'				=> array(
							array('string', false, 6, 60), 
							array('email', $email)), 
						'email_confirm'		=> array('string', true, 6, 60), 
					);

					$error = validate_data($data, $var_ary);
					extract($data);
					unset($data);

					if ($auth->acl_get('u_chgpasswd') && $new_password && md5($password_confirm) != $user->data['user_password'])
					{
						$error[] = 'NEW_PASSWORD_ERROR';
					}
					if ((($auth->acl_get('u_chgemail') && $email != $user->data['user_email']) || ($username != $user->data['username'] && $auth->acl_get('u_chgname') && $config['allow_namechange'])) && md5($cur_password) != $user->data['user_password'])
					{
						$error[] = 'CUR_PASSWORD_ERROR';
					}
					if ($auth->acl_get('u_chgemail') && $email != $user->data['user_email'] && $email_confirm != $email)
					{
						$error[] = 'NEW_EMAIL_ERROR';
					}

					if (!sizeof($error))
					{
						$sql_ary = array(
							'username'		=> ($auth->acl_get('u_chgname') && $config['allow_namechange']) ? $username : $user->data['username'], 
							'user_email'	=> ($auth->acl_get('u_chgemail')) ? $email : $user->data['user_email'], 
							'user_password'	=> ($auth->acl_get('u_chgpasswd') && $new_password) ? md5($new_password) : $user->data['user_password']
						);

						if ($config['email_enable'] && $email != $user->data['user_email'] && ($config['require_activation'] == USER_ACTIVATION_SELF || $config['require_activation'] == USER_ACTIVATION_ADMIN))
						{
							include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

							$server_url = generate_board_url();

							$user_actkey = gen_rand_string(10);
							$key_len = 54 - (strlen($server_url));
							$key_len = ($key_len > 6) ? $key_len : 6;
							$user_actkey = substr($user_actkey, 0, $key_len);

							$messenger = new messenger();

							$messenger->template($email_template, $lang);
							$messenger->subject($subject);

							$messenger->replyto($user->data['board_contact']);
							$messenger->to($email, $username);

							$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
							$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
							$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
							$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

							$messenger->assign_vars(array(
								'SITENAME'		=> $config['sitename'],
								'WELCOME_MSG'	=> sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename']),
								'USERNAME'		=> $username,
								'PASSWORD'		=> $password_confirm,
								'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

								'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&k=$user_actkey")
							);

							$messenger->send(NOTIFY_EMAIL);

							if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
							{
								// Grab an array of user_id's with a_user permissions
								$admin_ary = discover_auth(false, 'a_user', false);

								$sql = 'SELECT user_id, username, user_email, user_jabber, user_notify_type
									FROM ' . USERS_TABLE . ' 
									WHERE user_id IN (' . implode(', ', $admin_ary[0]['a_user']) .')';
								$result = $db->sql_query($sql);

								while ($row = $db->sql_fetchrow($result))
								{
									$messenger->use_template('admin_activate', $row['user_lang']);
									$messenger->replyto($config['board_contact']);
									$messenger->to($row['user_email'], $row['username']);
									$messenger->im($row['user_jabber'], $row['username']);

									$messenger->assign_vars(array(
										'USERNAME'		=> $row['username'],
										'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

										'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&k=$user_actkey")
									);

									$messenger->send($row['user_notify_type']);
								}
								$db->sql_freeresult($result);
							}

							$messenger->queue->save();

							$sql_ary += array(
								'user_type'		=> USER_INACTIVE,
								'user_actkey'	=> $user_actkey
							);
						}

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' 
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						// Need to update config, forum, topic, posting, messages, etc.
						if ($username != $user->data['username'] && $auth->acl_get('u_chgname') && $config['allow_namechange'])
						{
							update_username($user->data['username'], $username);
						}

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');
						trigger_error($message);
					}
				}

				$user_char_ary = array('.*' => 'USERNAME_CHARS_ANY', '[\w]+' => 'USERNAME_ALPHA_ONLY', '[\w_\+\. \-\[\]]+' => 'USERNAME_ALPHA_SPACERS');

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',

					'USERNAME'			=> (isset($username)) ? $username : $user->data['username'], 
					'EMAIL'				=> (isset($email)) ? $email : $user->data['user_email'], 
					'PASSWORD_CONFIRM'	=> (isset($password_confirm)) ? $password_confirm : '', 
					'NEW_PASSWORD'		=> (isset($new_password)) ? $new_password : '', 
					'CUR_PASSWORD'		=> '', 

					'L_USERNAME_EXPLAIN'		=> sprintf($user->lang[$user_char_ary[str_replace('\\\\', '\\', $config['allow_name_chars'])] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']), 
					'L_CHANGE_PASSWORD_EXPLAIN'	=> sprintf($user->lang['CHANGE_PASSWORD_EXPLAIN'], $config['min_pass_chars'], $config['max_pass_chars']), 
				
					'S_CHANGE_USERNAME' => ($config['allow_namechange'] && $auth->acl_get('u_chgname')) ? true : false, 
					'S_CHANGE_EMAIL'	=> ($auth->acl_get('u_chgemail')) ? true : false,
					'S_CHANGE_PASSWORD'	=> ($auth->acl_get('u_chgpasswd')) ? true : false)
				);
				break;

			case 'profile_info':

				if ($submit)
				{
					$var_ary = array(
						'icq'			=> (string) '', 
						'aim'			=> (string) '', 
						'msn'			=> (string) '', 
						'yim'			=> (string) '', 
						'jabber'		=> (string) '', 
						'website'		=> (string) '', 
						'location'		=> (string) '',
						'occupation'	=> (string) '',
						'interests'		=> (string) '',
						'bday_day'		=> 0,
						'bday_month'	=> 0,
						'bday_year'		=> 0,
					);

					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					$var_ary = array(
						'icq'			=> array(
							array('string', true, 3, 15), 
							array('match', true, '#^[0-9]+$#i')), 
						'aim'			=> array('string', true, 5, 255), 
						'msn'			=> array('string', true, 5, 255), 
						'jabber'		=> array(
							array('string', true, 5, 255), 
							array('match', true, '#^[a-z0-9\.\-_\+]+?@(.*?\.)*?[a-z0-9\-_]+?\.[a-z]{2,4}(/.*)?$#i')),
						'yim'			=> array('string', true, 5, 255), 
						'website'		=> array(
							array('string', true, 12, 255), 
							array('match', true, '#^http[s]?://(.*?\.)*?[a-z0-9\-]+\.[a-z]{2,4}#i')), 
						'location'		=> array('string', true, 2, 255), 
						'occupation'	=> array('string', true, 2, 500), 
						'interests'		=> array('string', true, 2, 500), 
						'bday_day'		=> array('num', true, 1, 31),
						'bday_month'	=> array('num', true, 1, 12),
						'bday_year'		=> array('num', true, 1901, gmdate('Y', time())),
					);

					$error = validate_data($data, $var_ary);
					extract($data);
					unset($data);

					if (!sizeof($error))
					{
						$sql_ary = array(
							'user_icq'		=> $icq,
							'user_aim'		=> $aim,
							'user_msnm'		=> $msn,
							'user_yim'		=> $yim,
							'user_jabber'	=> $jabber,
							'user_website'	=> $website,
							'user_from'		=> $location,
							'user_occ'		=> $occupation,
							'user_interests'=> $interests,
							'user_birthday'	=> sprintf('%2d-%2d-%4d', $bday_day, $bday_month, $bday_year),
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');
						trigger_error($message);
					}
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
					'ERROR'		=> (sizeof($error)) ? implode('<br />', $error) : '',

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

				$var_ary = array(
					'enable_html'		=> (bool) $config['allow_html'], 
					'enable_bbcode'		=> (bool) $config['allow_bbcode'], 
					'enable_smilies'	=> (bool) $config['allow_smilies'],
					'enable_urls'		=> true,  
					'signature'			=> (string) $user->data['user_sig'], 

				);

				foreach ($var_ary as $var => $default)
				{
					$$var = request_var($var, $default);
				}

				if ($submit)
				{
					if (strlen($signature) > $config['max_sig_chars'])
					{
						$error[] = $user->lang['SIGNATURE_TOO_LONG'];
					}

					if (!sizeof($error))
					{
						include($phpbb_root_path . 'includes/message_parser.'.$phpEx);

						$message_parser = new parse_message();

						$message_parser->message = $signature;
						$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies);

						$sql_ary = array(
							'user_sig'					=> (string) $message_parser->message, 
							'user_sig_bbcode_uid'		=> (string) $message_parser->bbcode_uid, 
							'user_sig_bbcode_bitfield'	=> (int) $message_parser->bbcode_bitfield
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' 
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');
						trigger_error($message);
					}
				}

				$signature_preview = '';
				if ($preview)
				{
					$signature_preview = $signature;

					// Fudge-o-rama ...
					include($phpbb_root_path . 'includes/message_parser.'.$phpEx);

					$message_parser = new parse_message();
					$message_parser->message = $signature_preview;
					$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies);
					$signature_preview = $message_parser->message;

					if ($enable_bbcode)
					{
						include($phpbb_root_path . 'includes/bbcode.'.$phpEx);
						$bbcode = new bbcode($message_parser->bbcode_bitfield);

						$bbcode->bbcode_second_pass($signature_preview, $message_parser->bbcode_uid);
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

				$html_status = ($config['allow_html']) ? true : false; 
				$bbcode_status = ($config['allow_bbcode']) ? true : false; 
				$smilies_status = ($config['allow_smilies']) ? true : false; 
				$img_status = ($config['allow_img']) ? true : false; 
				$flash_status = ($config['allow_flash']) ? true : false; 

				decode_text($signature, $user->data['user_sig_bbcode_uid']);

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '', 
					'SIGNATURE'			=> $signature,
					'SIGNATURE_PREVIEW'	=> $signature_preview, 
					
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

				$display_gallery = (isset($_POST['displaygallery'])) ? true : false;
				$avatar_category = request_var('category', '');

				// Can we upload? 
				$can_upload = ($config['allow_avatar_upload'] && file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && $auth->acl_get('u_chgavatar') && (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on')) ? true : false;

				if ($submit)
				{
					$var_ary = array(
						'uploadurl'		=> (string) '', 
						'remotelink'	=> (string) '', 
						'width'			=> (string) '',
						'height'		=> (string) '', 
					);

					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					$var_ary = array(
						'uploadurl'		=> array('string', true, 5, 255), 
						'remotelink'	=> array('string', true, 5, 255), 
						'width'			=> array('string', true, 1, 3), 
						'height'		=> array('string', true, 1, 3), 
					);

					$error = validate_data($data, $var_ary);

					if (!sizeof($error))
					{
						$data['user_id'] = $user->data['user_id'];

						if (!empty($_FILES['uploadfile']['tmp_name']) && $can_upload)
						{
							$data = avatar_upload($data, $error);
						}
						else if ($data['uploadurl'] && $can_upload)
						{
							$data = avatar_upload($data, $error);
						}
						else if ($data['remotelink'] && $auth->acl_get('u_chgavatar') && $config['allow_avatar_remote'])
						{
							$data = avatar_remote($data, $error);
						}
						else if ($delete && $auth->acl_get('u_chgavatar'))
						{
							$data['filename'] = $data['width'] = $data['height'] = '';
						}
					}

					if (!sizeof($error))
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
								avatar_delete($user->data['user_avatar']);
							}
						}

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');
						trigger_error($message);
					}

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
							$avatar_img = $phpbb_root_path . $config['avatar_path'] . '/';
							break;
						case AVATAR_GALLERY:
							$avatar_img = $phpbb_root_path . $config['avatar_gallery_path'] . '/';
							break;
					}
					$avatar_img .= $user->data['user_avatar'];

					$avatar_img = '<img src="' . $avatar_img . '" width="' . $user->data['user_avatar_width'] . '" height="' . $user->data['user_avatar_height'] . '" border="0" alt="" />';
				}

				$template->assign_vars(array(
					'ERROR'			=> (sizeof($error)) ? implode('<br />', $error) : '', 
					'AVATAR'		=> $avatar_img, 
					'AVATAR_SIZE'	=> $config['avatar_filesize'], 

					'S_FORM_ENCTYPE'	=> ($can_upload) ? ' enctype="multipart/form-data"' : '', 

					'L_AVATAR_EXPLAIN'	=> sprintf($user->lang['AVATAR_EXPLAIN'], $config['avatar_max_width'], $config['avatar_max_height'], round($config['avatar_filesize'] / 1024)),)
				);

				if ($display_gallery && $auth->acl_get('u_chgavatar') && $config['allow_avatar_local'])
				{
					$avatar_list = avatar_gallery($category, $error);

					$category = (!$category) ? key($avatar_list) : $category;

					$s_category_options = '';
					foreach (array_keys($avatar_list) as $cat)
					{
						$s_category_options .= '<option value="' . $cat . '">' . $cat . '</option>';
					}

					$template->assign_vars(array(
						'S_DISPLAY_GALLERY'	=> true,
						'S_CAT_OPTIONS'		=> $s_category_options)
					);

					foreach ($avatar_list[$category] as $avatar_row_ary)
					{
						$template->assign_block_vars('avatar_row', array());

						foreach ($avatar_row_ary as $avatar_col_ary)
						{
							$template->assign_block_vars('avatar_row.avatar_column', array(
								'AVATAR_IMAGE'	=> $phpbb_root_path . $config['avatar_gallery_path'] . '/' . $avatar_col_ary['file'],
								'AVATAR_NAME'	=> $avatar_col_ary['name'])
							);

							$template->assign_block_vars('avatar_row.avatar_option_column', array(
								'AVATAR_IMAGE'	=> $phpbb_root_path . $config['avatar_gallery_path'] . '/' . $avatar_col_ary['file'],)
							);
						}
					}
				}
				else
				{
					$template->assign_vars(array(
						'AVATAR'		=> $avatar_img, 
						'AVATAR_SIZE'	=> $config['avatar_filesize'], 
						'WIDTH'			=> (isset($width)) ? $width : $user->data['user_avatar_width'], 
						'HEIGHT'		=> (isset($height)) ? $height : $user->data['user_avatar_height'], 

						'S_UPLOAD_AVATAR_FILE'	=> $can_upload,
						'S_UPLOAD_AVATAR_URL'	=> $can_upload, 
						'S_LINK_AVATAR'			=> ($auth->acl_get('u_chgavatar') && $config['allow_avatar_remote']) ? true : false, 
						'S_GALLERY_AVATAR'		=> ($auth->acl_get('u_chgavatar') && $config['allow_avatar_local']) ? true : false,
						'S_AVATAR_CAT_OPTIONS'	=> $s_categories, 
						'S_AVATAR_PAGE_OPTIONS'	=> $s_pages,)
					);
				}

				break;
		}

		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['UCP_' . strtoupper($mode)],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode")
		);

		$this->display($user->lang['UCP_PROFILE'], 'ucp_profile_' . $mode . '.html');
	}
}

?>