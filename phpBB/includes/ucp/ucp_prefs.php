<?php
/** 
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* ucp_prefs
* Changing user preferences
* @package ucp
*/
class ucp_prefs
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;

		$submit = (isset($_POST['submit'])) ? true : false;
		$error = $data = array();
		$s_hidden_fields = '';

		switch ($mode)
		{
			case 'personal':

				$data = array(
					'notifymethod'	=> $user->data['user_notify_type'],
					'dateformat'	=> $user->data['user_dateformat'],
					'lang'			=> $user->data['user_lang'],
					'style'			=> $user->data['user_style'],
					'tz'			=> $user->data['user_timezone'],
				);

				if ($submit)
				{
					$var_ary = array(
						'dateformat'	=> (string) $config['default_dateformat'],
						'lang'			=> (string) $config['default_lang'],
						'tz'			=> (float) $config['board_timezone'],
						'style'			=> (int) $config['default_style'],
						'dst'			=> (bool) $config['board_dst'],
						'viewemail'		=> false,
						'massemail'		=> true,
						'hideonline'	=> false,
						'notifymethod'	=> 0,
						'notifypm'		=> true,
						'popuppm'		=> false,
						'allowpm'		=> true,
					);

					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					$var_ary = array(
						'dateformat'	=> array('string', false, 3, 30),
						'lang'			=> array('match', false, '#^[a-z_\-]{2,}$#i'),
						'tz'			=> array('num', false, -14, 14),
					);

					$error = validate_data($data, $var_ary);

					if (!sizeof($error))
					{
						$user->optionset('popuppm', $data['popuppm']);

						$sql_ary = array(
							'user_allow_pm'			=> $data['allowpm'],
							'user_allow_viewemail'	=> $data['viewemail'],
							'user_allow_massemail'	=> $data['massemail'],
							'user_allow_viewonline'	=> ($auth->acl_get('u_hideonline')) ? !$data['hideonline'] : $user->data['user_allow_viewonline'],
							'user_notify_type'		=> $data['notifymethod'],
							'user_notify_pm'		=> $data['notifypm'],
							'user_options'			=> $user->data['user_options'],

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

						meta_refresh(3, $this->u_action);
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
				}

				$dateformat_options = '';

				foreach ($user->lang['dateformats'] as $format => $null)
				{
					$dateformat_options .= '<option value="' . $format . '"' . (($format == $data['dateformat']) ? ' selected="selected"' : '') . '>';
					$dateformat_options .= $user->format_date(time(), $format, true) . ((strpos($format, '|') !== false) ? ' [' . $user->lang['RELATIVE_DAYS'] . ']' : '');
					$dateformat_options .= '</option>';
				}

				$s_custom = false;

				$dateformat_options .= '<option value="custom"';
				if (!in_array($data['dateformat'], array_keys($user->lang['dateformats'])))
				{
					$dateformat_options .= ' selected="selected"';
					$s_custom = true;
				}
				$dateformat_options .= '>' . $user->lang['CUSTOM_DATEFORMAT'] . '</option>';

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',

					'S_NOTIFY_EMAIL'	=> ($data['notifymethod'] == NOTIFY_EMAIL) ? true : false,
					'S_NOTIFY_IM'		=> ($data['notifymethod'] == NOTIFY_IM) ? true : false,
					'S_NOTIFY_BOTH'		=> ($data['notifymethod'] == NOTIFY_BOTH) ? true : false,
					'S_VIEW_EMAIL'		=> (isset($data['viewemail'])) ? $data['viewemail'] : $user->data['user_allow_viewemail'],
					'S_MASS_EMAIL'		=> (isset($data['massemail'])) ? $data['massemail'] : $user->data['user_allow_massemail'],
					'S_ALLOW_PM'		=> (isset($data['allowpm'])) ? $data['allowpm'] : $user->data['user_allow_pm'],
					'S_HIDE_ONLINE'		=> (isset($data['hideonline'])) ? $data['hideonline'] : !$user->data['user_allow_viewonline'],
					'S_NOTIFY_PM'		=> (isset($data['notifypm'])) ? $data['notifypm'] : $user->data['user_notify_pm'],
					'S_POPUP_PM'		=> (isset($data['popuppm'])) ? $data['popuppm'] : $user->optionget('popuppm'),
					'S_DST'				=> (isset($data['dst'])) ? $data['dst'] : $user->data['user_dst'],

					'DATE_FORMAT'			=> $data['dateformat'],
					'S_DATEFORMAT_OPTIONS'	=> $dateformat_options,
					'S_CUSTOM_DATEFORMAT'	=> $s_custom,
					'DEFAULT_DATEFORMAT'	=> $config['default_dateformat'],
					'A_DEFAULT_DATEFORMAT'	=> addslashes($config['default_dateformat']),

					'S_LANG_OPTIONS'	=> language_select($data['lang']),
					'S_STYLE_OPTIONS'	=> style_select($data['style']),
					'S_TZ_OPTIONS'		=> tz_select($data['tz']),
					'S_CAN_HIDE_ONLINE'	=> ($auth->acl_get('u_hideonline')) ? true : false,
					'S_SELECT_NOTIFY'	=> ($config['jab_enable'] && $user->data['user_jabber'] && @extension_loaded('xml')) ? true : false)
				);

			break;

			case 'view':

				$data = array(
					'topic_sk'		=> (!empty($user->data['user_topic_sortby_type'])) ? $user->data['user_topic_sortby_type'] : 't',
					'topic_sd'		=> (!empty($user->data['user_topic_sortby_dir'])) ? $user->data['user_topic_sortby_dir'] : 'd',
					'topic_st'		=> (!empty($user->data['user_topic_show_days'])) ? $user->data['user_topic_show_days'] : 0,

					'post_sk'		=> (!empty($user->data['user_post_sortby_type'])) ? $user->data['user_post_sortby_type'] : 't',
					'post_sd'		=> (!empty($user->data['user_post_sortby_dir'])) ? $user->data['user_post_sortby_dir'] : 'a',
					'post_st'		=> (!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0,
				);

				if ($submit)
				{
					$var_ary = array_merge($data, array(
						'images'	=> true,
						'flash'		=> false,
						'smilies'	=> true,
						'sigs'		=> true,
						'avatars'	=> true,
						'wordcensor'=> false,
					));

					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					$var_ary = array(
						'topic_sk'	=> array('string', false, 1, 1),
						'topic_sd'	=> array('string', false, 1, 1),
						'post_sk'	=> array('string', false, 1, 1),
						'post_sd'	=> array('string', false, 1, 1),
					);

					$error = validate_data($data, $var_ary);

					if (!sizeof($error))
					{
						$user->optionset('viewimg', $data['images']);
						$user->optionset('viewflash', $data['flash']);
						$user->optionset('viewsmilies', $data['smilies']);
						$user->optionset('viewsigs', $data['sigs']);
						$user->optionset('viewavatars', $data['avatars']);

						if ($auth->acl_get('u_chgcensors'))
						{
							$user->optionset('viewcensors', $data['wordcensor']);
						}

						$sql_ary = array(
							'user_options'				=> $user->data['user_options'],
							'user_topic_sortby_type'	=> $data['topic_sk'],
							'user_post_sortby_type'		=> $data['post_sk'],
							'user_topic_sortby_dir'		=> $data['topic_sd'],
							'user_post_sortby_dir'		=> $data['post_sd'],

							'user_topic_show_days'	=> $data['topic_st'],
							'user_post_show_days'	=> $data['post_st'],
						);

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
				}

				$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

				// Topic ordering options
				$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

				$sort_by_topic_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);
				$sort_by_topic_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_time', 'r' => 't.topic_replies', 's' => 't.topic_title', 'v' => 't.topic_views');

				// Post ordering options
				$limit_post_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

				$sort_by_post_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
				$sort_by_post_sql = array('a' => 'u.username', 't' => 'p.post_id', 's' => 'p.post_subject');

				$_options = array('topic', 'post');
				foreach ($_options as $sort_option)
				{
					${'s_limit_' . $sort_option . '_days'} = '<select name="' . $sort_option . '_st">';
					foreach (${'limit_' . $sort_option . '_days'} as $day => $text)
					{
						$selected = ($data[$sort_option . '_st'] == $day) ? ' selected="selected"' : '';
						${'s_limit_' . $sort_option . '_days'} .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
					}
					${'s_limit_' . $sort_option . '_days'} .= '</select>';

					${'s_sort_' . $sort_option . '_key'} = '<select name="' . $sort_option . '_sk">';
					foreach (${'sort_by_' . $sort_option . '_text'} as $key => $text)
					{
						$selected = ($data[$sort_option . '_sk'] == $key) ? ' selected="selected"' : '';
						${'s_sort_' . $sort_option . '_key'} .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
					}
					${'s_sort_' . $sort_option . '_key'} .= '</select>';

					${'s_sort_' . $sort_option . '_dir'} = '<select name="' . $sort_option . '_sd">';
					foreach ($sort_dir_text as $key => $value)
					{
						$selected = ($data[$sort_option . '_sd'] == $key) ? ' selected="selected"' : '';
						${'s_sort_' . $sort_option . '_dir'} .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
					}
					${'s_sort_' . $sort_option . '_dir'} .= '</select>';
				}

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',

					'S_IMAGES'			=> (isset($data['images'])) ? $data['images'] : $user->optionget('viewimg'),
					'S_FLASH'			=> (isset($data['flash'])) ? $data['flash'] : $user->optionget('viewflash'),
					'S_SMILIES'			=> (isset($data['smilies'])) ? $data['smilies'] : $user->optionget('viewsmilies'),
					'S_SIGS'			=> (isset($data['sigs'])) ? $data['sigs'] : $user->optionget('viewsigs'),
					'S_AVATARS'			=> (isset($data['avatars'])) ? $data['avatars'] : $user->optionget('viewavatars'),
					'S_DISABLE_CENSORS'	=> (isset($data['wordcensor'])) ? $data['wordcensor'] : $user->optionget('viewcensors'),

					'S_CHANGE_CENSORS'		=> ($auth->acl_get('u_chgcensors')) ? true : false,

					'S_TOPIC_SORT_DAYS'		=> $s_limit_topic_days,
					'S_TOPIC_SORT_KEY'		=> $s_sort_topic_key,
					'S_TOPIC_SORT_DIR'		=> $s_sort_topic_dir,
					'S_POST_SORT_DAYS'		=> $s_limit_post_days,
					'S_POST_SORT_KEY'		=> $s_sort_post_key,
					'S_POST_SORT_DIR'		=> $s_sort_post_dir)
				);

			break;

			case 'post':

				$data = array(
					'bbcode'	=> $user->optionget('bbcode'),
					'smilies'	=> $user->optionget('smilies'),
					'sig'		=> $user->optionget('attachsig'),
					'notify'	=> $user->data['user_notify'],
				);

				if ($submit)
				{
					$var_ary = $data;

					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					$user->optionset('bbcode', $data['bbcode']);
					$user->optionset('smilies', $data['smilies']);
					$user->optionset('attachsig', $data['sig']);

					$sql_ary = array(
						'user_options'	=> $user->data['user_options'],
						'user_notify'	=> $data['notify'],
					);

					$sql = 'UPDATE ' . USERS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE user_id = ' . $user->data['user_id'];
					$db->sql_query($sql);

					meta_refresh(3, $this->u_action);
					$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
					trigger_error($message);
				}

				$template->assign_vars(array(
					'S_BBCODE'	=> $data['bbcode'],
					'S_SMILIES'	=> $data['smilies'],
					'S_SIG'		=> $data['sig'],
					'S_NOTIFY'	=> $data['notify'])
				);
			break;
		}

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['UCP_PREFS_' . strtoupper($mode)],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action)
		);

		$this->tpl_name = 'ucp_prefs_' . $mode;
		$this->page_title = 'UCP_PREFS_' . strtoupper($mode);
	}
}

?>