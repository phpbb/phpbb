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

namespace phpbb\ucp\controller;

/**
 * Changing user preferences
 */
class settings
{
	var $u_action;

	public function main($id, $mode)
	{

		$submit = ($this->request->is_set_post('submit')) ? true : false;
		$error = $data = [];
		$s_hidden_fields = '';

		switch ($mode)
		{
			case 'personal':
				add_form_key('ucp_prefs_personal');
				$data = [
					'notifymethod'	=> $this->request->variable('notifymethod', $this->user->data['user_notify_type']),
					'dateformat'	=> $this->request->variable('dateformat', $this->user->data['user_dateformat'], true),
					'lang'			=> basename($this->request->variable('lang', $this->user->data['user_lang'])),
					'user_style'		=> $this->request->variable('user_style', (int) $this->user->data['user_style']),
					'tz'			=> $this->request->variable('tz', $this->user->data['user_timezone']),

					'viewemail'		=> $this->request->variable('viewemail', (bool) $this->user->data['user_allow_viewemail']),
					'massemail'		=> $this->request->variable('massemail', (bool) $this->user->data['user_allow_massemail']),
					'hideonline'	=> $this->request->variable('hideonline', (bool) !$this->user->data['user_allow_viewonline']),
					'allowpm'		=> $this->request->variable('allowpm', (bool) $this->user->data['user_allow_pm']),
				];

				if ($data['notifymethod'] == NOTIFY_IM && (!$this->config['jab_enable'] || !$this->user->data['user_jabber'] || !@extension_loaded('xml')))
				{
					// Jabber isnt enabled, or no jabber field filled in. Update the users table to be sure its correct.
					$data['notifymethod'] = NOTIFY_BOTH;
				}

				/**
				 * Add UCP edit global settings data before they are assigned to the template or submitted
				 *
				 * To assign data to the template, use $this->template->assign_vars()
				 *
				 * @event core.ucp_prefs_personal_data
				 * @var bool	submit		Do we display the form only
				 *							or did the user press submit
				 * @var array	data		Array with current ucp options data
				 * @var array	error		Array with list of errors
				 * @since 3.1.0-a1
				 * @changed 3.1.4-RC1 Added error variable to the event
				 */
				$vars = ['submit', 'data', 'error'];
				extract($this->dispatcher->trigger_event('core.ucp_prefs_personal_data', compact($vars)));

				if ($submit)
				{
					if ($this->config['override_user_style'])
					{
						$data['user_style'] = (int) $this->config['default_style'];
					}
					else if (!phpbb_style_is_active($data['user_style']))
					{
						$data['user_style'] = (int) $this->user->data['user_style'];
					}

					$error = array_merge(validate_data($data, [
						'dateformat'	=> ['string', false, 1, 64],
						'lang'			=> ['language_iso_name'],
						'tz'			=> ['timezone'],
					]), $error);

					if (!check_form_key('ucp_prefs_personal'))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!count($error))
					{
						$sql_ary = [
							'user_allow_pm'			=> $data['allowpm'],
							'user_allow_viewemail'	=> $data['viewemail'],
							'user_allow_massemail'	=> $data['massemail'],
							'user_allow_viewonline'	=> ($this->auth->acl_get('u_hideonline')) ? !$data['hideonline'] : $this->user->data['user_allow_viewonline'],
							'user_notify_type'		=> $data['notifymethod'],
							'user_options'			=> $this->user->data['user_options'],

							'user_dateformat'		=> $data['dateformat'],
							'user_lang'				=> $data['lang'],
							'user_timezone'			=> $data['tz'],
							'user_style'			=> $data['user_style'],
						];

						/**
						 * Update UCP edit global settings data on form submit
						 *
						 * @event core.ucp_prefs_personal_update_data
						 * @var array	data		Submitted display options data
						 * @var array	sql_ary		Display options data we update
						 * @since 3.1.0-a1
						 */
						$vars = ['data', 'sql_ary'];
						extract($this->dispatcher->trigger_event('core.ucp_prefs_personal_update_data', compact($vars)));

						$sql = 'UPDATE ' . $this->tables['users'] . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $this->user->data['user_id'];
						$this->db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						$message = $this->language->lang('PREFERENCES_UPDATED') . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = array_map([$user, 'lang'], $error);
				}

				$dateformat_options = '';

				foreach ($this->language->lang('dateformats') as $format => $null)
				{
					$dateformat_options .= '<option value="' . $format . '"' . (($format == $data['dateformat']) ? ' selected="selected"' : '') . '>';
					$dateformat_options .= $this->user->format_date(time(), $format, false) . ((strpos($format, '|') !== false) ? $this->language->lang('VARIANT_DATE_SEPARATOR') . $this->user->format_date(time(), $format, true) : '');
					$dateformat_options .= '</option>';
				}

				$s_custom = false;

				$dateformat_options .= '<option value="custom"';
				if (!isset($this->language->lang('dateformats')[$data['dateformat']]))
				{
					$dateformat_options .= ' selected="selected"';
					$s_custom = true;
				}
				$dateformat_options .= '>' . $this->language->lang('CUSTOM_DATEFORMAT') . '</option>';

				phpbb_timezone_select($template, $user, $data['tz'], true);

				// check if there are any user-selectable languages
				$sql = 'SELECT COUNT(lang_id) as languages_count
								FROM ' . $this->tables['lang'];
				$result = $this->db->sql_query($sql);
				if ($this->db->sql_fetchfield('languages_count') > 1)
				{
					$s_more_languages = true;
				}
				else
				{
					$s_more_languages = false;
				}
				$this->db->sql_freeresult($result);

				// check if there are any user-selectable styles
				$sql = 'SELECT COUNT(style_id) as styles_count
								FROM ' . $this->tables['styles'] . '
								WHERE style_active = 1';
				$result = $this->db->sql_query($sql);
				if ($this->db->sql_fetchfield('styles_count') > 1)
				{
					$s_more_styles = true;
				}
				else
				{
					$s_more_styles = false;
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
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
					'DEFAULT_DATEFORMAT'	=> $this->config['default_dateformat'],
					'A_DEFAULT_DATEFORMAT'	=> addslashes($this->config['default_dateformat']),

					'S_MORE_LANGUAGES'	=> $s_more_languages,
					'S_MORE_STYLES'			=> $s_more_styles,

					'S_LANG_OPTIONS'		=> language_select($data['lang']),
					'S_STYLE_OPTIONS'		=> ($this->config['override_user_style']) ? '' : style_select($data['user_style']),
					'S_CAN_HIDE_ONLINE'		=> ($this->auth->acl_get('u_hideonline')) ? true : false,
					'S_SELECT_NOTIFY'		=> ($this->config['jab_enable'] && $this->user->data['user_jabber'] && @extension_loaded('xml')) ? true : false]
				);

			break;

			case 'view':

				add_form_key('ucp_prefs_view');

				$data = [
					'topic_sk'		=> $this->request->variable('topic_sk', (!empty($this->user->data['user_topic_sortby_type'])) ? $this->user->data['user_topic_sortby_type'] : 't'),
					'topic_sd'		=> $this->request->variable('topic_sd', (!empty($this->user->data['user_topic_sortby_dir'])) ? $this->user->data['user_topic_sortby_dir'] : 'd'),
					'topic_st'		=> $this->request->variable('topic_st', (!empty($this->user->data['user_topic_show_days'])) ? (int) $this->user->data['user_topic_show_days'] : 0),

					'post_sk'		=> $this->request->variable('post_sk', (!empty($this->user->data['user_post_sortby_type'])) ? $this->user->data['user_post_sortby_type'] : 't'),
					'post_sd'		=> $this->request->variable('post_sd', (!empty($this->user->data['user_post_sortby_dir'])) ? $this->user->data['user_post_sortby_dir'] : 'a'),
					'post_st'		=> $this->request->variable('post_st', (!empty($this->user->data['user_post_show_days'])) ? (int) $this->user->data['user_post_show_days'] : 0),

					'images'		=> $this->request->variable('images', (bool) $this->user->optionget('viewimg')),
					'flash'			=> $this->request->variable('flash', (bool) $this->user->optionget('viewflash')),
					'smilies'		=> $this->request->variable('smilies', (bool) $this->user->optionget('viewsmilies')),
					'sigs'			=> $this->request->variable('sigs', (bool) $this->user->optionget('viewsigs')),
					'avatars'		=> $this->request->variable('avatars', (bool) $this->user->optionget('viewavatars')),
					'wordcensor'	=> $this->request->variable('wordcensor', (bool) $this->user->optionget('viewcensors')),
				];

				/**
				 * Add UCP edit display options data before they are assigned to the template or submitted
				 *
				 * To assign data to the template, use $this->template->assign_vars()
				 *
				 * @event core.ucp_prefs_view_data
				 * @var bool	submit		Do we display the form only
				 *							or did the user press submit
				 * @var array	data		Array with current ucp options data
				 * @since 3.1.0-a1
				 */
				$vars = ['submit', 'data'];
				extract($this->dispatcher->trigger_event('core.ucp_prefs_view_data', compact($vars)));

				if ($submit)
				{
					$error = validate_data($data, [
						'topic_sk'	=> [
							['string', false, 1, 1],
							['match', false, '#(a|r|s|t|v)#'],
						],
						'topic_sd'	=> [
							['string', false, 1, 1],
							['match', false, '#(a|d)#'],
						],
						'post_sk'	=> [
							['string', false, 1, 1],
							['match', false, '#(a|s|t)#'],
						],
						'post_sd'	=> [
							['string', false, 1, 1],
							['match', false, '#(a|d)#'],
						],
					]);

					if (!check_form_key('ucp_prefs_view'))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!count($error))
					{
						$this->user->optionset('viewimg', $data['images']);
						$this->user->optionset('viewflash', $data['flash']);
						$this->user->optionset('viewsmilies', $data['smilies']);
						$this->user->optionset('viewsigs', $data['sigs']);
						$this->user->optionset('viewavatars', $data['avatars']);

						if ($this->auth->acl_get('u_chgcensors'))
						{
							$this->user->optionset('viewcensors', $data['wordcensor']);
						}

						$sql_ary = [
							'user_options'				=> $this->user->data['user_options'],
							'user_topic_sortby_type'	=> $data['topic_sk'],
							'user_post_sortby_type'		=> $data['post_sk'],
							'user_topic_sortby_dir'		=> $data['topic_sd'],
							'user_post_sortby_dir'		=> $data['post_sd'],

							'user_topic_show_days'	=> $data['topic_st'],
							'user_post_show_days'	=> $data['post_st'],
						];

						/**
						 * Update UCP edit display options data on form submit
						 *
						 * @event core.ucp_prefs_view_update_data
						 * @var array	data		Submitted display options data
						 * @var array	sql_ary		Display options data we update
						 * @since 3.1.0-a1
						 */
						$vars = ['data', 'sql_ary'];
						extract($this->dispatcher->trigger_event('core.ucp_prefs_view_update_data', compact($vars)));

						$sql = 'UPDATE ' . $this->tables['users'] . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $this->user->data['user_id'];
						$this->db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						$message = $this->language->lang('PREFERENCES_UPDATED') . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = array_map([$user, 'lang'], $error);
				}

				$sort_dir_text = ['a' => $this->language->lang('ASCENDING'), 'd' => $this->language->lang('DESCENDING')];

				// Topic ordering options
				$limit_topic_days = [0 => $this->language->lang('ALL_TOPICS'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];

				$sort_by_topic_text = ['a' => $this->language->lang('AUTHOR'), 't' => $this->language->lang('POST_TIME'), 'r' => $this->language->lang('REPLIES'), 's' => $this->language->lang('SUBJECT'), 'v' => $this->language->lang('VIEWS')];
				$sort_by_topic_sql = ['a' => 't.topic_first_poster_name', 't' => ['t.topic_last_post_time', 't.topic_last_post_id'], 'r' => 't.topic_posts_approved', 's' => 't.topic_title', 'v' => 't.topic_views'];

				// Post ordering options
				$limit_post_days = [0 => $this->language->lang('ALL_POSTS'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];

				$sort_by_post_text = ['a' => $this->language->lang('AUTHOR'), 't' => $this->language->lang('POST_TIME'), 's' => $this->language->lang('SUBJECT')];
				$sort_by_post_sql = ['a' => 'u.username_clean', 't' => 'p.post_id', 's' => 'p.post_subject'];

				$_options = ['topic', 'post'];
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
				 * @var bool	submit				Do we display the form only
				 *									or did the user press submit
				 * @var array	data				Array with current ucp options data
				 * @var array	sort_dir_text		Array with sort dir language strings
				 * @var array	limit_topic_days	Topic ordering options
				 * @var array	sort_by_topic_text	Topic ordering language strings
				 * @var array	sort_by_topic_sql	Topic ordering sql
				 * @var array	limit_post_days		Post ordering options
				 * @var array	sort_by_post_text	Post ordering language strings
				 * @var array	sort_by_post_sql	Post ordering sql
				 * @var array	_options			Sort options
				 * @var string	s_limit_topic_days	Sort limit topic by days select box
				 * @var string	s_sort_topic_key	Sort topic key select box
				 * @var string	s_sort_topic_dir	Sort topic dir select box
				 * @var string	s_limit_post_days	Sort limit post by days select box
				 * @var string	s_sort_post_key		Sort post key select box
				 * @var string	s_sort_post_dir		Sort post dir select box
				 * @since 3.1.8-RC1
				 */
				$vars = [
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
				];
				extract($this->dispatcher->trigger_event('core.ucp_prefs_view_after', compact($vars)));

				$this->template->assign_vars([
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',

					'S_IMAGES'			=> $data['images'],
					'S_FLASH'			=> $data['flash'],
					'S_SMILIES'			=> $data['smilies'],
					'S_SIGS'			=> $data['sigs'],
					'S_AVATARS'			=> $data['avatars'],
					'S_DISABLE_CENSORS'	=> $data['wordcensor'],

					'S_CHANGE_CENSORS'		=> ($this->auth->acl_get('u_chgcensors') && $this->config['allow_nocensors']) ? true : false,

					'S_TOPIC_SORT_DAYS'		=> $s_limit_topic_days,
					'S_TOPIC_SORT_KEY'		=> $s_sort_topic_key,
					'S_TOPIC_SORT_DIR'		=> $s_sort_topic_dir,
					'S_POST_SORT_DAYS'		=> $s_limit_post_days,
					'S_POST_SORT_KEY'		=> $s_sort_post_key,
					'S_POST_SORT_DIR'		=> $s_sort_post_dir]
				);

			break;

			case 'post':

				$data = [
					'bbcode'	=> $this->request->variable('bbcode', $this->user->optionget('bbcode')),
					'smilies'	=> $this->request->variable('smilies', $this->user->optionget('smilies')),
					'sig'		=> $this->request->variable('sig', $this->user->optionget('attachsig')),
					'notify'	=> $this->request->variable('notify', (bool) $this->user->data['user_notify']),
				];
				add_form_key('ucp_prefs_post');

				/**
				 * Add UCP edit posting defaults data before they are assigned to the template or submitted
				 *
				 * To assign data to the template, use $this->template->assign_vars()
				 *
				 * @event core.ucp_prefs_post_data
				 * @var bool	submit		Do we display the form only
				 *							or did the user press submit
				 * @var array	data		Array with current ucp options data
				 * @since 3.1.0-a1
				 */
				$vars = ['submit', 'data'];
				extract($this->dispatcher->trigger_event('core.ucp_prefs_post_data', compact($vars)));

				if ($submit)
				{
					if (check_form_key('ucp_prefs_post'))
					{
						$this->user->optionset('bbcode', $data['bbcode']);
						$this->user->optionset('smilies', $data['smilies']);
						$this->user->optionset('attachsig', $data['sig']);

						$sql_ary = [
							'user_options'	=> $this->user->data['user_options'],
							'user_notify'	=> $data['notify'],
						];

						/**
						 * Update UCP edit posting defaults data on form submit
						 *
						 * @event core.ucp_prefs_post_update_data
						 * @var array	data		Submitted display options data
						 * @var array	sql_ary		Display options data we update
						 * @since 3.1.0-a1
						 */
						$vars = ['data', 'sql_ary'];
						extract($this->dispatcher->trigger_event('core.ucp_prefs_post_update_data', compact($vars)));

						$sql = 'UPDATE ' . $this->tables['users'] . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $this->user->data['user_id'];
						$this->db->sql_query($sql);

						$msg = $this->language->lang('PREFERENCES_UPDATED');
					}
					else
					{
						$msg = $this->language->lang('FORM_INVALID');
					}
					meta_refresh(3, $this->u_action);
					$message = $msg . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
					trigger_error($message);
				}

				$this->template->assign_vars([
					'S_BBCODE'	=> $data['bbcode'],
					'S_SMILIES'	=> $data['smilies'],
					'S_SIG'		=> $data['sig'],
					'S_NOTIFY'	=> $data['notify']]
				);
			break;
		}

		/**
		 * Modify UCP preferences data before the page load
		 *
		 * @event core.ucp_prefs_modify_common
		 * @var array	data		Array with current/submitted UCP options data
		 * @var array	error		Errors data
		 * @var string	mode		UCP prefs operation mode
		 * @var string	s_hidden_fields		Hidden fields data
		 * @since 3.1.0-RC3
		 */
		$vars = [
			'data',
			'error',
			'mode',
			's_hidden_fields',
		];
		extract($this->dispatcher->trigger_event('core.ucp_prefs_modify_common', compact($vars)));

		$this->template->assign_vars([
			'L_TITLE'			=> $this->language->lang('UCP_PREFS_' . strtoupper($mode)),

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action]
		);

		$this->tpl_name = 'ucp_prefs_' . $mode;
		$this->page_title = 'UCP_PREFS_' . strtoupper($mode);
	}
}
