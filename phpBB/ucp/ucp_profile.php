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
		global $config, $db, $user, $auth, $SID, $template, $phpEx;

		$submode = ($_REQUEST['mode']) ? htmlspecialchars($_REQUEST['mode']) : 'reg_details';

		$submodules['REG_DETAILS']	= "i=$id&amp;mode=reg_details";
		$submodules['PROFILE_INFO']	= "i=$id&amp;mode=profile_info";
		$submodules['SIGNATURE']	= "i=$id&amp;mode=signature";       
		$submodules['AVATAR']		= "i=$id&amp;mode=avatar";                               

		$this->subsection($submodules, $submode);
		unset($submodules);

		switch ($submode)
		{
			case 'reg_details':

				$template->assign_vars(array(
					'USERNAME'	=> $user->data['username'], 
					'EMAIL'		=> $user->data['user_email'], 
				
					'S_CHANGE_USERNAME' => $config['allow_namechange'] & $auth->acl_get('u_chgname'), 
					'S_CHANGE_EMAIL'	=> $auth->acl_get('u_chgemail'),
					'S_CHANGE_PASSWORD'	=> $auth->acl_get('u_chgpass'), )
				);
				break;

			case 'profile_info':

				list($day, $month, $year) = explode('-', $user->data['user_birthday']);

				$s_birthday_day_options = '';
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $day) ? ' selected="selected"' : '';
					$s_birthday_day_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_birthday_month_options = '';
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $month) ? ' selected="selected"' : '';
					$s_birthday_month_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				$s_birthday_year_options = '';

				$now = getdate();
				for ($i = $now['year'] - 100; $i < $now['year']; $i++)
				{
					$selected = ($i == $year) ? ' selected="selected"' : '';
					$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				unset($now);

				$template->assign_vars(array(
					'ICQ'		=> $user->data['user_icq'], 
					'YIM'		=> $user->data['user_yim'], 
					'AIM'		=> $user->data['user_aim'], 
					'MSNM'		=> $user->data['user_msnm'], 
					'JABBER'	=> $user->data['user_jabber'], 
					'WEBSITE'	=> $user->data['user_website'], 
					'LOCATION'	=> $user->data['user_from'], 
					'OCCUPATION'=> $user->data['user_occ'], 
					'INTERESTS'	=> $user->data['user_interests'], 

					'S_BIRTHDAY_DAY_OPTIONS'	=> $s_birthday_day_options, 
					'S_BIRTHDAY_MONTH_OPTIONS'	=> $s_birthday_month_options, 
					'S_BIRTHDAY_YEAR_OPTIONS'	=> $s_birthday_year_options,)
				);
				break;

			case 'signature':

				$this->loadfile('includes/functions_posting');

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
						$this->loadfile('includes/message_parser');

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

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode");
						trigger_error('');
					}
				}

				$signature_preview = '';
				if ($_POST['preview'])
				{
					// Fudge-o-rama ...

					global $phpbb_root_path;

					$this->loadfile('includes/message_parser');

					$signature_preview = $signature;

					$message_parser = new parse_message();
					$message_parser->message = trim(stripslashes($signature_preview));
					$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies);
					$signature_preview = $message_parser->message;

					if ($enable_bbcode)
					{
						$this->loadfile('includes/bbcode');
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

				$template->assign_vars(array(
					'AVATAR'	=> '<img src="images/avatars/upload/' . $user->data['user_avatar'] . '" />', 

					'S_UPLOAD_AVATAR_FILE'	=> true,
					'S_UPLOAD_AVATAR_URL'	=> true, 
					'S_LINK_AVATAR'			=> true, 
					'S_GALLERY_AVATAR'		=> true,)
				);

				break;

			default: 
				break;
		}

		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['UCP_' . strtoupper($submode)],

			'S_DISPLAY_' . strtoupper($submode)	=> true, 
			'S_HIDDEN_FIELDS'					=> $s_hidden_fields,
			'S_UCP_ACTION'						=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode")
		);

		$this->output($user->lang['UCP_PROFILE'], 'ucp_profile.html');
	}
}

?>