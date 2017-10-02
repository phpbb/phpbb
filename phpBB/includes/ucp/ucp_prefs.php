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
* ucp_prefs
* Changing user preferences
*/
class ucp_prefs
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_dispatcher, $request;

		$submit = (isset($_POST['submit'])) ? true : false;
		$error = $data = array();
		$s_hidden_fields = '';

		switch ($mode)
		{
			case 'personal':
				add_form_key('ucp_prefs_personal');
				$data = array(
					'notifymethod'	=> $request->variable('notifymethod', $user->data['user_notify_type']),
					'dateformat'	=> $request->variable('dateformat', $user->data['user_dateformat'], true),
					'lang'			=> basename($request->variable('lang', $user->data['user_lang'])),
					'user_style'		=> $request->variable('user_style', (int) $user->data['user_style']),
					'tz'			=> $request->variable('tz', $user->data['user_timezone']),

					'viewemail'		=> $request->variable('viewemail', (bool) $user->data['user_allow_viewemail']),
					'massemail'		=> $request->variable('massemail', (bool) $user->data['user_allow_massemail']),
					'hideonline'	=> $request->variable('hideonline', (bool) !$user->data['user_allow_viewonline']),
					'allowpm'		=> $request->variable('allowpm', (bool) $user->data['user_allow_pm']),
				);

				if ($data['notifymethod'] == NOTIFY_IM && (!$config['jab_enable'] || !$user->data['user_jabber'] || !@extension_loaded('xml')))
				{
					// Jabber isnt enabled, or no jabber field filled in. Update the users table to be sure its correct.
					$data['notifymethod'] = NOTIFY_BOTH;
				}

				/**
				* Add UCP edit global settings data before they are assigned to the template or submitted
				*
				* To assign data to the template, use $template->assign_vars()
				*
				* @event core.ucp_prefs_personal_data
				* @var	bool	submit		Do we display the form only
				*							or did the user press submit
				* @var	array	data		Array with current ucp options data
				* @var	array	error		Array with list of errors
				* @since 3.1.0-a1
				* @changed 3.1.4-RC1 Added error variable to the event
				*/
				$vars = array('submit', 'data', 'error');
				extract($phpbb_dispatcher->trigger_event('core.ucp_prefs_personal_data', compact($vars)));

				if ($submit)
				{
					if ($config['override_user_style'])
					{
						$data['user_style'] = (int) $config['default_style'];
					}
					else if (!phpbb_style_is_active($data['user_style']))
					{
						$data['user_style'] = (int) $user->data['user_style'];
					}

					$error = array_merge(validate_data($data, array(
						'dateformat'	=> array('string', false, 1, 64),
						'lang'			=> array('language_iso_name'),
						'tz'			=> array('timezone'),
					)), $error);

					if (!check_form_key('ucp_prefs_personal'))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!count($error))
					{
						$sql_ary = array(
							'user_allow_pm'			=> $data['allowpm'],
							'user_allow_viewemail'	=> $data['viewemail'],
							'user_allow_massemail'	=> $data['massemail'],
							'user_allow_viewonline'	=> ($auth->acl_get('u_hideonline')) ? !$data['hideonline'] : $user->data['user_allow_viewonline'],
							'user_notify_type'		=> $data['notifymethod'],
							'user_options'			=> $user->data['user_options'],

							'user_dateformat'		=> $data['dateformat'],
							'user_lang'				=> $data['lang'],
							'user_timezone'			=> $data['tz'],
							'user_style'			=> $data['user_style'],
						);

						/**
						* Update UCP edit global settings data on form submit
						*
						* @event core.ucp_prefs_personal_update_data
						* @var	array	data		Submitted display options data
						* @var	array	sql_ary		Display options data we update
						* @since 3.1.0-a1
						*/
						$vars = array('data', 'sql_ary');
						extract($phpbb_dispatcher->trigger_event('core.ucp_prefs_personal_update_data', compact($vars)));

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
				}

				$dateformat_options = '';

				foreach ($user->lang['dateformats'] as $format => $null)
				{
					$dateformat_options .= '<option value="' . $format . '"' . (($format == $data['dateformat']) ? ' selected="selected"' : '') . '>';
					$dateformat_options .= $user->format_date(time(), $format, false) . ((strpos($format, '|') !== false) ? $user->lang['VARIANT_DATE_SEPARATOR'] . $user->format_date(time(), $format, true) : '');
					$dateformat_options .= '</option>';
				}

				$s_custom = false;

				$dateformat_options .= '<option value="custom"';
				if (!isset($user->lang['dateformats'][$data['dateformat']]))
				{
					$dateformat_options .= ' selected="selected"';
					$s_custom = true;
				}
				$dateformat_options .= '>' . $user->lang['CUSTOM_DATEFORMAT'] . '</option>';

				phpbb_timezone_select($template, $user, $data['tz'], true);

				// check if there are any user-selectable languages
				$sql = 'SELECT COUNT(lang_id) as languages_count
								FROM ' . LANG_TABLE;
				$result = $db->sql_query($sql);
				if ($db->sql_fetchfield('languages_count') > 1)
				{
					$s_more_languages = true;
				}
				else
				{
					$s_more_languages = false;
				}
				$db->sql_freeresult($result);

				// check if there are any user-selectable styles
				$sql = 'SELECT COUNT(style_id) as styles_count
								FROM ' . STYLES_TABLE . '
								WHERE style_active = 1';
				$result = $db->sql_query($sql);
				if ($db->sql_fetchfield('styles_count') > 1)
				{
					$s_more_styles = true;
				}
				else
				{
					$s_more_styles = false;
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',

					'S_NOTIFY_EMAIL'	=> ($data['notifymethod'] == NOTIFY_EMAIL) ? true : false,
					'S_NOTIFY_IM'		=> ($data['notifymethod'] == NOTIFY_IM) ? true : false,
					'S_NOTIFY_BOTH'		=> ($data['notifymethod'] == NOTIFY_BOTH) ? true : false,
					'S_VIEW_EMAIL'		=> $data['viewemail'],
					'S_MASS_EMAIL'		=> $data['massemail'],
					'S_ALLOW_PM'		=> $data['allowpm'],
					'S_HIDE_ONLINE'		=> $data['hideonline'],

					'DATE_FORMAT'			=> $data['dateformat'],
					'A_DATE_FORMAT'			=> addslashes($data['dateformat']),
					'S_DATEFORMAT_OPTIONS'	=> $dateformat_options,
					'S_CUSTOM_DATEFORMAT'	=> $s_custom,
					'DEFAULT_DATEFORMAT'	=> $config['default_dateformat'],
					'A_DEFAULT_DATEFORMAT'	=> addslashes($config['default_dateformat']),

					'S_MORE_LANGUAGES'	=> $s_more_languages,
					'S_MORE_STYLES'			=> $s_more_styles,

					'S_LANG_OPTIONS'		=> language_select($data['lang']),
					'S_STYLE_OPTIONS'		=> ($config['override_user_style']) ? '' : style_select($data['user_style']),
					'S_CAN_HIDE_ONLINE'		=> ($auth->acl_get('u_hideonline')) ? true : false,
					'S_SELECT_NOTIFY'		=> ($config['jab_enable'] && $user->data['user_jabber'] && @extension_loaded('xml')) ? true : false)
				);

			break;

			case 'view':

				add_form_key('ucp_prefs_view');

				$data = array(
					'topic_sk'		=> $request->variable('topic_sk', (!empty($user->data['user_topic_sortby_type'])) ? $user->data['user_topic_sortby_type'] : 't'),
					'topic_sd'		=> $request->variable('topic_sd', (!empty($user->data['user_topic_sortby_dir'])) ? $user->data['user_topic_sortby_dir'] : 'd'),
					'topic_st'		=> $request->variable('topic_st', (!empty($user->data['user_topic_show_days'])) ? (int) $user->data['user_topic_show_days'] : 0),

					'post_sk'		=> $request->variable('post_sk', (!empty($user->data['user_post_sortby_type'])) ? $user->data['user_post_sortby_type'] : 't'),
					'post_sd'		=> $request->variable('post_sd', (!empty($user->data['user_post_sortby_dir'])) ? $user->data['user_post_sortby_dir'] : 'a'),
					'post_st'		=> $request->variable('post_st', (!empty($user->data['user_post_show_days'])) ? (int) $user->data['user_post_show_days'] : 0),

					'images'		=> $request->variable('images', (bool) $user->optionget('viewimg')),
					'flash'			=> $request->variable('flash', (bool) $user->optionget('viewflash')),
					'smilies'		=> $request->variable('smilies', (bool) $user->optionget('viewsmilies')),
					'sigs'			=> $request->variable('sigs', (bool) $user->optionget('viewsigs')),
					'avatars'		=> $request->variable('avatars', (bool) $user->optionget('viewavatars')),
					'wordcensor'	=> $request->variable('wordcensor', (bool) $user->optionget('viewcensors')),
				);

				/**
				* Add UCP edit display options data before they are assigned to the template or submitted
				*
				* To assign data to the template, use $template->assign_vars()
				*
				* @event core.ucp_prefs_view_data
				* @var	bool	submit		Do we display the form only
				*							or did the user press submit
				* @var	array	data		Array with current ucp options data
				* @since 3.1.0-a1
				*/
				$vars = array('submit', 'data');
				extract($phpbb_dispatcher->trigger_event('core.ucp_prefs_view_data', compact($vars)));

				if ($submit)
				{
					$error = validate_data($data, array(
						'topic_sk'	=> array(
							array('string', false, 1, 1),
							array('match', false, '#(a|r|s|t|v)#'),
						),
						'topic_sd'	=> array(
							array('string', false, 1, 1),
							array('match', false, '#(a|d)#'),
						),
						'post_sk'	=> array(
							array('string', false, 1, 1),
							array('match', false, '#(a|s|t)#'),
						),
						'post_sd'	=> array(
							array('string', false, 1, 1),
							array('match', false, '#(a|d)#'),
						),
					));

					if (!check_form_key('ucp_prefs_view'))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!count($error))
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

						/**
						* Update UCP edit display options data on form submit
						*
						* @event core.ucp_prefs_view_update_data
						* @var	array	data		Submitted display options data
						* @var	array	sql_ary		Display options data we update
						* @since 3.1.0-a1
						*/
						$vars = array('data', 'sql_ary');
						extract($phpbb_dispatcher->trigger_event('core.ucp_prefs_view_update_data', compact($vars)));

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
				}

				$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

				// Topic ordering options
				$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

				$sort_by_topic_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);
				$sort_by_topic_sql = array('a' => 't.topic_first_poster_name', 't' => array('t.topic_last_post_time', 't.topic_last_post_id'), 'r' => 't.topic_posts_approved', 's' => 't.topic_title', 'v' => 't.topic_views');

				// Post ordering options
				$limit_post_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

				$sort_by_post_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
				$sort_by_post_sql = array('a' => 'u.username_clean', 't' => 'p.post_id', 's' => 'p.post_subject');

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

				/**
				* Run code before view form is displayed
				*
				* @event core.ucp_prefs_view_after
				* @var	bool	submit				Do we display the form only
				*									or did the user press submit
				* @var	array	data				Array with current ucp options data
				* @var	array	sort_dir_text		Array with sort dir language strings
				* @var	array	limit_topic_days	Topic ordering options
				* @var	array	sort_by_topic_text	Topic ordering language strings
				* @var	array	sort_by_topic_sql	Topic ordering sql
				* @var	array	limit_post_days		Post ordering options
				* @var	array	sort_by_post_text	Post ordering language strings
				* @var	array	sort_by_post_sql	Post ordering sql
				* @var	array	_options			Sort options
				* @var	string	s_limit_topic_days	Sort limit topic by days select box
				* @var	string	s_sort_topic_key	Sort topic key select box
				* @var	string	s_sort_topic_dir	Sort topic dir select box
				* @var	string	s_limit_post_days	Sort limit post by days select box
				* @var	string	s_sort_post_key		Sort post key select box
				* @var	string	s_sort_post_dir		Sort post dir select box
				* @since 3.1.8-RC1
				*/
				$vars = array(
					'submit',
					'data',
					'sort_dir_text',
					'limit_topic_days',
					'sort_by_topic_text',
					'sort_by_topic_sql',
					'limit_post_days',
					'sort_by_post_text',
					'sort_by_post_sql',
					'_options',
					's_limit_topic_days',
					's_sort_topic_key',
					's_sort_topic_dir',
					's_limit_post_days',
					's_sort_post_key',
					's_sort_post_dir',
				);
				extract($phpbb_dispatcher->trigger_event('core.ucp_prefs_view_after', compact($vars)));

				$template->assign_vars(array(
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',

					'S_IMAGES'			=> $data['images'],
					'S_FLASH'			=> $data['flash'],
					'S_SMILIES'			=> $data['smilies'],
					'S_SIGS'			=> $data['sigs'],
					'S_AVATARS'			=> $data['avatars'],
					'S_DISABLE_CENSORS'	=> $data['wordcensor'],

					'S_CHANGE_CENSORS'		=> ($auth->acl_get('u_chgcensors') && $config['allow_nocensors']) ? true : false,

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
					'bbcode'	=> $request->variable('bbcode', $user->optionget('bbcode')),
					'smilies'	=> $request->variable('smilies', $user->optionget('smilies')),
					'sig'		=> $request->variable('sig', $user->optionget('attachsig')),
					'notify'	=> $request->variable('notify', (bool) $user->data['user_notify']),
				);
				add_form_key('ucp_prefs_post');

				/**
				* Add UCP edit posting defaults data before they are assigned to the template or submitted
				*
				* To assign data to the template, use $template->assign_vars()
				*
				* @event core.ucp_prefs_post_data
				* @var	bool	submit		Do we display the form only
				*							or did the user press submit
				* @var	array	data		Array with current ucp options data
				* @since 3.1.0-a1
				*/
				$vars = array('submit', 'data');
				extract($phpbb_dispatcher->trigger_event('core.ucp_prefs_post_data', compact($vars)));

				if ($submit)
				{
					if (check_form_key('ucp_prefs_post'))
					{
						$user->optionset('bbcode', $data['bbcode']);
						$user->optionset('smilies', $data['smilies']);
						$user->optionset('attachsig', $data['sig']);

						$sql_ary = array(
							'user_options'	=> $user->data['user_options'],
							'user_notify'	=> $data['notify'],
						);

						/**
						* Update UCP edit posting defaults data on form submit
						*
						* @event core.ucp_prefs_post_update_data
						* @var	array	data		Submitted display options data
						* @var	array	sql_ary		Display options data we update
						* @since 3.1.0-a1
						*/
						$vars = array('data', 'sql_ary');
						extract($phpbb_dispatcher->trigger_event('core.ucp_prefs_post_update_data', compact($vars)));

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						$msg = $user->lang['PREFERENCES_UPDATED'];
					}
					else
					{
						$msg = $user->lang['FORM_INVALID'];
					}
					meta_refresh(3, $this->u_action);
					$message = $msg . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
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

		/**
		* Modify UCP preferences data before the page load
		*
		* @event core.ucp_prefs_modify_common
		* @var	array	data		Array with current/submitted UCP options data
		* @var	array	error		Errors data
		* @var	string	mode		UCP prefs operation mode
		* @var	string	s_hidden_fields		Hidden fields data
		* @since 3.1.0-RC3
		*/
		$vars = array(
			'data',
			'error',
			'mode',
			's_hidden_fields',
		);
		extract($phpbb_dispatcher->trigger_event('core.ucp_prefs_modify_common', compact($vars)));

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['UCP_PREFS_' . strtoupper($mode)],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action)
		);

		$this->tpl_name = 'ucp_prefs_' . $mode;
		$this->page_title = 'UCP_PREFS_' . strtoupper($mode);
	}
}
