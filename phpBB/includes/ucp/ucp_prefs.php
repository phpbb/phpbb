<?php
/***************************************************************************
 *                               ucp_prefs.php
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

class ucp_prefs extends ucp
{
	function main($id)
	{
		global $censors, $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$submode = ($_REQUEST['mode']) ? htmlspecialchars($_REQUEST['mode']) : 'personal';

		// Setup internal subsection display
		$submodules['PERSONAL']	= "i=$id&amp;mode=personal";
		$submodules['VIEW']		= "i=$id&amp;mode=view";
		$submodules['POST']		= "i=$id&amp;mode=post";

		$this->menu($id, $submodules, $submode);
		unset($submodules);

		switch($submode)
		{
			case 'personal':

				if (isset($_POST['submit']))
				{
					$data = array();
					$normalise = array(
						'string' => array(
							'dateformat'=> '3,15',
							'lang'		=> '2,5',
						), 
						'int'	=> array('dst', 'style'),
						'float' => array('tz'),
						'bool'	=> array('viewemail', 'massemail', 'hideonline', 'notifypm', 'popuppm')
					);
					$data = $this->normalise_data($_POST, $normalise);

					$validate = array(
						'reqd'	=> array('lang', 'tz', 'dateformat', 'style'), 
						'match'	=> array(
							'lang'		=> ($data['lang']) ? '#^[a-z_]+$#i' : '', 
						),
					);
					$this->validate_data($data, $validate);

					if (!sizeof($this->error))
					{
						$sql_ary = array(
							'user_allow_viewemail'	=> $data['viewemail'], 
							'user_allow_massemail'	=> $data['massemail'], 
							'user_allow_viewonline'	=> ($auth->acl_get('u_hideonline')) ? !$data['hideonline'] : $user->data['user_allow_viewonline'], 
							'user_notify_pm'		=> $data['notifypm'],
							'user_popup_pm'			=> $data['popuppm'],
							'user_dst'				=> $data['dst'],
							'user_dateformat'		=> $data['dateformat'],
							'user_lang'				=> $data['lang'],
							'user_timezone'			=> $data['tz'],
							'user_style'			=> $data['style'],
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode");
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode\">", '</a>');
						trigger_error($message);
					}

					//
					extract($data);
					unset($data);
				}

				$viewemail = (isset($viewemail)) ? $viewemail : $user->data['user_allow_viewemail'];
				$view_email_yes = ($viewemail) ? ' checked="checked"' : '';
				$view_email_no = (!$viewemail) ? ' checked="checked"' : '';
				$massemail = (isset($massemail)) ? $massemail : $user->data['user_allow_massemail'];
				$mass_email_yes = ($massemail) ? ' checked="checked"' : '';
				$mass_email_no = (!$massemail) ? ' checked="checked"' : '';
				$hideonline = (isset($hideonline)) ? $hideonline : !$user->data['user_allow_viewonline'];
				$hide_online_yes = ($hideonline) ? ' checked="checked"' : '';
				$hide_online_no = (!$hideonline) ? ' checked="checked"' : '';
				$notifypm = (isset($notifypm)) ? $notifypm : $user->data['user_notify_pm'];
				$notify_pm_yes = ($notifypm) ? ' checked="checked"' : '';
				$notify_pm_no = (!$notifypm) ? ' checked="checked"' : '';
				$popuppm = (isset($popuppm)) ? $popuppm : $user->data['user_popup_pm'];
				$popup_pm_yes = ($popuppm) ? ' checked="checked"' : '';
				$popup_pm_no = (!$popuppm) ? ' checked="checked"' : '';
				$dst = (isset($dst)) ? $dst : $user->data['user_dst'];
				$dst_yes = ($dst) ? ' checked="checked"' : '';
				$dst_no = (!$dst) ? ' checked="checked"' : '';
				$dateformat = (isset($dateformat)) ? $dateformat : $user->data['user_dateformat'];
				
				$lang = (isset($lang)) ? $lang : $user->data['user_lang'];
				$style = (isset($style)) ? $style : $user->data['user_style'];
				$tz = (isset($tz)) ? $tz : $user->data['user_timezone'];

				$template->assign_vars(array( 
					'ERROR'				=> (sizeof($this->error)) ? implode('<br />', $this->error) : '',

					'VIEW_EMAIL_YES'	=> $view_email_yes, 
					'VIEW_EMAIL_NO'		=> $view_email_no, 
					'ADMIN_EMAIL_YES'	=> $mass_email_yes, 
					'ADMIN_EMAIL_NO'	=> $mass_email_no, 
					'HIDE_ONLINE_YES'	=> $hide_online_yes, 
					'HIDE_ONLINE_NO'	=> $hide_online_no, 
					'NOTIFY_PM_YES'		=> $notify_pm_yes, 
					'NOTIFY_PM_NO'		=> $notify_pm_no, 
					'POPUP_PM_YES'		=> $popup_pm_yes, 
					'POPUP_PM_NO'		=> $popup_pm_no, 
					'DST_YES'			=> $dst_yes, 
					'DST_NO'			=> $dst_no, 

					'DATE_FORMAT'		=> $dateformat, 

					'S_LANG_OPTIONS'	=> language_select($lang), 
					'S_STYLE_OPTIONS'	=> style_select($style),
					'S_TZ_OPTIONS'		=> tz_select($tz),
					'S_CAN_HIDE_ONLINE'	=> true, 	
					)
				);
				break;

			case 'view':

				if (isset($_POST['submit']))
				{
					$data = array();
					$normalise = array(
						'string'	=> array(
							'sk'	=> '1,1', 
							'sd'	=> '1,1', 
						),
						'int'	=> array('st', 'minkarma'), 
						'bool'	=> array('images', 'flash', 'smilies', 'sigs', 'avatars', 'wordcensor'), 
					);
					$data = $this->normalise_data($_POST, $normalise);

					if (!sizeof($this->error))
					{
						$sql_ary = array(
							'user_viewimg'		=> $data['images'],
							'user_viewflash'	=> $data['flash'],
							'user_viewsmilies'	=> $data['smilies'],
							'user_viewsigs'		=> $data['sigs'],
							'user_viewavatars'	=> $data['avatars'],
							'user_viewcensors'	=> ($auth->acl_get('u_chgcensors')) ? $data['wordcensor'] : $user->data['user_viewcensors'],
							'user_sortby_type'	=> $data['sk'],
							'user_sortby_dir'	=> $data['sd'],
							'user_show_days'	=> $data['st'], 
							'user_min_karma'	=> $data['minkarma'], 
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode");
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode\">", '</a>');
						trigger_error($message);
					}

					//
					extract($data);
					unset($data);
				}

				$sk = (isset($sk)) ? $sk : ((!empty($user->data['user_sortby_type'])) ? $user->data['user_sortby_type'] : 't');
				$sd = (isset($sd)) ? $sd : ((!empty($user->data['user_sortby_dir'])) ? $user->data['user_sortby_dir'] : 'd');
				$st = (isset($st)) ? $st : ((!empty($user->data['user_show_days'])) ? $user->data['user_show_days'] : 0);

				// Topic ordering display
				$limit_days = array(0 => $user->lang['ALL_TOPICS'], 0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);

				$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);
				$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_time', 'r' => 't.topic_replies', 's' => 't.topic_title', 'v' => 't.topic_views');

				$s_limit_days = $s_sort_key = $s_sort_dir = '';
				gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, &$s_limit_days, &$s_sort_key, &$s_sort_dir);

				$s_min_karma_options = '';
				$minkarma = (isset($minkarma)) ? $minkarma : $user->data['user_min_karma'];
				for ($i = -5; $i < 6; $i++)
				{
					$selected = ($i == $minkarma) ? ' selected="selected"' : '';
					$s_min_karma_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$images = (isset($images)) ? $images : $user->data['user_viewimg'];
				$images_yes = ($images) ? ' checked="checked"' : '';
				$images_no = (!$images) ? ' checked="checked"' : '';
				$flash = (isset($flash)) ? $flash : $user->data['user_viewflash'];
				$flash_yes = ($flash) ? ' checked="checked"' : '';
				$flash_no = (!$flash) ? ' checked="checked"' : '';
				$smilies = (isset($smilies)) ? $smilies : $user->data['user_viewsmilies'];
				$smilies_yes = ($smilies) ? ' checked="checked"' : '';
				$smilies_no = (!$smilies) ? ' checked="checked"' : '';
				$sigs = (isset($sigs)) ? $sigs : $user->data['user_viewsigs'];
				$sigs_yes = ($sigs) ? ' checked="checked"' : '';
				$sigs_no = (!$sigs) ? ' checked="checked"' : '';
				$avatars = (isset($avatars)) ? $avatars : $user->data['user_viewavatars'];
				$avatars_yes = ($avatars) ? ' checked="checked"' : '';
				$avatars_no = (!$avatars) ? ' checked="checked"' : '';
				$wordcensor = (isset($wordcensor)) ? $wordcensor : $user->data['user_viewcensors'];
				$wordcensor_yes = ($wordcensor) ? ' checked="checked"' : '';
				$wordcensor_no = (!$wordcensor) ? ' checked="checked"' : '';

				$template->assign_vars(array( 
					'ERROR'				=> (sizeof($this->error)) ? implode('<br />', $this->error) : '',

					'VIEW_IMAGES_YES'		=> $images_yes, 
					'VIEW_IMAGES_NO'		=> $images_no, 
					'VIEW_FLASH_YES'		=> $flash_yes, 
					'VIEW_FLASH_NO'			=> $flash_no, 
					'VIEW_SMILIES_YES'		=> $smilies_yes, 
					'VIEW_SMILIES_NO'		=> $smilies_no, 
					'VIEW_SIGS_YES'			=> $sigs_yes, 
					'VIEW_SIGS_NO'			=> $sigs_no, 
					'VIEW_AVATARS_YES'		=> $avatars_yes, 
					'VIEW_AVATARS_NO'		=> $avatars_no,
					'DISABLE_CENSORS_YES'	=> $wordcensor_yes, 
					'DISABLE_CENSORS_NO'	=> $wordcensor_no,

					'S_MIN_KARMA_OPTIONS'	=> $s_min_karma_options, 
					'S_CHANGE_CENSORS'		=> ($auth->acl_get('u_chgcensors')) ? true : false, 
					'S_SELECT_SORT_DAYS'	=> $s_limit_days,
					'S_SELECT_SORT_KEY'		=> $s_sort_key, 
					'S_SELECT_SORT_DIR'		=> $s_sort_dir)
				);

				break;

			case 'post':

				if (isset($_POST['submit']))
				{
					$data = array();
					$normalise = array(
						'bool'	=> array('bbcode', 'html', 'smilies', 'sig', 'notify'),
					);
					$data = $this->normalise_data($_POST, $normalise);

					if (!sizeof($this->error))
					{
						$sql_ary = array(
							'user_allowbbcode'	=> $data['bbcode'],
							'user_allowhtml'	=> $data['html'],
							'user_allowsmile'	=> $data['smilies'],
							'user_attachsig'	=> $data['sig'],
							'user_notify'		=> $data['notify'],
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode");
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode\">", '</a>');
						trigger_error($message);
					}

					//
					extract($data);
					unset($data);
				}
				
				$bbcode = (isset($bbcode)) ? $bbcode : $user->data['user_allowbbcode'];
				$bbcode_yes = ($bbcode) ? ' checked="checked"' : '';
				$bbcode_no = (!$bbcode) ? ' checked="checked"' : '';
				$html = (isset($html)) ? $html : $user->data['user_allowhtml'];
				$html_yes = ($html) ? ' checked="checked"' : '';
				$html_no = (!$html) ? ' checked="checked"' : '';
				$smilies = (isset($smilies)) ? $smilies : $user->data['user_allowsmile'];
				$smilies_yes = ($smilies) ? ' checked="checked"' : '';
				$smilies_no = (!$smilies) ? ' checked="checked"' : '';
				$sig = (isset($sig)) ? $sig : $user->data['user_attachsig'];
				$sig_yes = ($sig) ? ' checked="checked"' : '';
				$sig_no = (!$sig) ? ' checked="checked"' : '';
				$notify = (isset($notify)) ? $notify : $user->data['user_notify'];
				$notify_yes = ($notify) ? ' checked="checked"' : '';
				$notify_no = (!$notify) ? ' checked="checked"' : '';

				$template->assign_vars(array( 
					'ERROR'				=> (sizeof($this->error)) ? implode('<br />', $this->error) : '',

					'DEFAULT_BBCODE_YES'	=> $bbcode_yes, 
					'DEFAULT_BBCODE_NO'		=> $bbcode_no, 
					'DEFAULT_HTML_YES'		=> $html_yes, 
					'DEFAULT_HTML_NO'		=> $html_no, 
					'DEFAULT_SMILIES_YES'	=> $smilies_yes, 
					'DEFAULT_SMILIES_NO'	=> $smilies_no, 
					'DEFAULT_SIG_YES'		=> $sig_yes, 
					'DEFAULT_SIG_NO'		=> $sig_no, 
					'DEFAULT_NOTIFY_YES'	=> $notify_yes, 
					'DEFAULT_NOTIFY_NO'		=> $notify_no,)
				);
				break;
		}

		$template->assign_vars(array( 
			'L_TITLE'	=> $user->lang['UCP_' . strtoupper($submode)],

			'S_DISPLAY_' . strtoupper($submode)	=> true, 
			'S_HIDDEN_FIELDS'					=> $s_hidden_fields,
			'S_UCP_ACTION'						=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode")
		);

		$this->display($user->lang['UCP_PROFILE'], 'ucp_prefs.html');
	}
}

?>