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

// Common global functions
/**
* Generates an alphanumeric random string of given length
*
* @param int $num_chars Length of random string, defaults to 8.
* This number should be less or equal than 64.
*
* @return string
*/
function gen_rand_string($num_chars = 8)
{
	$range = array_merge(range('A', 'Z'), range(0, 9));
	$size = count($range);

	$output = '';
	for ($i = 0; $i < $num_chars; $i++)
	{
		$rand = random_int(0, $size-1);
		$output .= $range[$rand];
	}

	return $output;
}

/**
* Generates a user-friendly alphanumeric random string of given length
* We remove 0 and O so users cannot confuse those in passwords etc.
*
* @param int $num_chars Length of random string, defaults to 8.
* This number should be less or equal than 64.
*
* @return string
*/
function gen_rand_string_friendly($num_chars = 8)
{
	$range = array_merge(range('A', 'N'), range('P', 'Z'), range(1, 9));
	$size = count($range);

	$output = '';
	for ($i = 0; $i < $num_chars; $i++)
	{
		$rand = random_int(0, $size-1);
		$output .= $range[$rand];
	}

	return $output;
}

/**
* Return unique id
*/
function unique_id()
{
	return strtolower(gen_rand_string(16));
}

/**
* Wrapper for mt_rand() which allows swapping $min and $max parameters.
*
* PHP does not allow us to swap the order of the arguments for mt_rand() anymore.
* (since PHP 5.3.4, see http://bugs.php.net/46587)
*
* @param int $min		Lowest value to be returned
* @param int $max		Highest value to be returned
*
* @return int			Random integer between $min and $max (or $max and $min)
*/
function phpbb_mt_rand($min, $max)
{
	return ($min > $max) ? mt_rand($max, $min) : mt_rand($min, $max);
}

/**
* Wrapper for getdate() which returns the equivalent array for UTC timestamps.
*
* @param int $time		Unix timestamp (optional)
*
* @return array			Returns an associative array of information related to the timestamp.
*						See http://www.php.net/manual/en/function.getdate.php
*/
function phpbb_gmgetdate($time = false)
{
	if ($time === false)
	{
		$time = time();
	}

	// getdate() interprets timestamps in local time.
	// So use UTC timezone temporarily to get UTC date info array.
	$current_timezone = date_default_timezone_get();

	// Set UTC timezone and get respective date info
	date_default_timezone_set('UTC');
	$date_info = getdate($time);

	// Restore timezone back
	date_default_timezone_set($current_timezone);

	return $date_info;
}

/**
* Return formatted string for filesizes
*
* @param mixed	$value			filesize in bytes
*								(non-negative number; int, float or string)
* @param bool	$string_only	true if language string should be returned
* @param array	$allowed_units	only allow these units (data array indexes)
*
* @return mixed					data array if $string_only is false
*/
function get_formatted_filesize($value, $string_only = true, $allowed_units = false)
{
	global $user;

	$available_units = array(
		'tb' => array(
			'min' 		=> 1099511627776, // pow(2, 40)
			'index'		=> 4,
			'si_unit'	=> 'TB',
			'iec_unit'	=> 'TIB',
		),
		'gb' => array(
			'min' 		=> 1073741824, // pow(2, 30)
			'index'		=> 3,
			'si_unit'	=> 'GB',
			'iec_unit'	=> 'GIB',
		),
		'mb' => array(
			'min'		=> 1048576, // pow(2, 20)
			'index'		=> 2,
			'si_unit'	=> 'MB',
			'iec_unit'	=> 'MIB',
		),
		'kb' => array(
			'min'		=> 1024, // pow(2, 10)
			'index'		=> 1,
			'si_unit'	=> 'KB',
			'iec_unit'	=> 'KIB',
		),
		'b' => array(
			'min'		=> 0,
			'index'		=> 0,
			'si_unit'	=> 'BYTES', // Language index
			'iec_unit'	=> 'BYTES',  // Language index
		),
	);

	foreach ($available_units as $si_identifier => $unit_info)
	{
		if (!empty($allowed_units) && $si_identifier != 'b' && !in_array($si_identifier, $allowed_units))
		{
			continue;
		}

		if ($value >= $unit_info['min'])
		{
			$unit_info['si_identifier'] = $si_identifier;

			break;
		}
	}
	unset($available_units);

	for ($i = 0; $i < $unit_info['index']; $i++)
	{
		$value /= 1024;
	}
	$value = round($value, 2);

	// Lookup units in language dictionary
	$unit_info['si_unit'] = (isset($user->lang[$unit_info['si_unit']])) ? $user->lang[$unit_info['si_unit']] : $unit_info['si_unit'];
	$unit_info['iec_unit'] = (isset($user->lang[$unit_info['iec_unit']])) ? $user->lang[$unit_info['iec_unit']] : $unit_info['iec_unit'];

	// Default to IEC
	$unit_info['unit'] = $unit_info['iec_unit'];

	if (!$string_only)
	{
		$unit_info['value'] = $value;

		return $unit_info;
	}

	return $value  . ' ' . $unit_info['unit'];
}

/**
* Determine whether we are approaching the maximum execution time. Should be called once
* at the beginning of the script in which it's used.
* @return	bool	Either true if the maximum execution time is nearly reached, or false
*					if some time is still left.
*/
function still_on_time($extra_time = 15)
{
	static $max_execution_time, $start_time;

	$current_time = microtime(true);

	if (empty($max_execution_time))
	{
		$max_execution_time = (function_exists('ini_get')) ? (int) @ini_get('max_execution_time') : (int) @get_cfg_var('max_execution_time');

		// If zero, then set to something higher to not let the user catch the ten seconds barrier.
		if ($max_execution_time === 0)
		{
			$max_execution_time = 50 + $extra_time;
		}

		$max_execution_time = min(max(10, ($max_execution_time - $extra_time)), 50);

		// For debugging purposes
		// $max_execution_time = 10;

		global $starttime;
		$start_time = (empty($starttime)) ? $current_time : $starttime;
	}

	return (ceil($current_time - $start_time) < $max_execution_time) ? true : false;
}

/**
* Wrapper for version_compare() that allows using uppercase A and B
* for alpha and beta releases.
*
* See http://www.php.net/manual/en/function.version-compare.php
*
* @param string $version1		First version number
* @param string $version2		Second version number
* @param string $operator		Comparison operator (optional)
*
* @return mixed					Boolean (true, false) if comparison operator is specified.
*								Integer (-1, 0, 1) otherwise.
*/
function phpbb_version_compare($version1, $version2, $operator = null)
{
	$version1 = strtolower($version1);
	$version2 = strtolower($version2);

	if (is_null($operator))
	{
		return version_compare($version1, $version2);
	}
	else
	{
		return version_compare($version1, $version2, $operator);
	}
}

// functions used for building option fields

/**
 * Pick a language, any language ...
 *
 * @param string $default	Language ISO code to be selected by default in the dropdown list
 * @param array $langdata	Language data in format of array(array('lang_iso' => string, lang_local_name => string), ...)
 *
 * @return string			HTML options for language selection dropdown list.
 */
function language_select($default = '', array $langdata = [])
{
	global $db;

	if (empty($langdata))
	{
		$sql = 'SELECT lang_iso, lang_local_name
			FROM ' . LANG_TABLE . '
			ORDER BY lang_english_name';
		$result = $db->sql_query($sql);
		$langdata = (array) $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
	}

	$lang_options = '';
	foreach ($langdata as $row)
	{
		$selected = ($row['lang_iso'] == $default) ? ' selected="selected"' : '';
		$lang_options .= '<option value="' . $row['lang_iso'] . '"' . $selected . '>' . $row['lang_local_name'] . '</option>';
	}

	return $lang_options;
}

/**
 * Pick a template/theme combo
 *
 * @param string $default	Style ID to be selected by default in the dropdown list
 * @param bool $all			Flag indicating if all styles data including inactive ones should be fetched
 * @param array $styledata	Style data in format of array(array('style_id' => int, style_name => string), ...)
 *
 * @return string			HTML options for style selection dropdown list.
 */
function style_select($default = '', $all = false, array $styledata = [])
{
	global $db;

	if (empty($styledata))
	{
		$sql_where = (!$all) ? 'WHERE style_active = 1 ' : '';
		$sql = 'SELECT style_id, style_name
			FROM ' . STYLES_TABLE . "
			$sql_where
			ORDER BY style_name";
		$result = $db->sql_query($sql);
		$styledata = (array) $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
	}

	$style_options = '';
	foreach ($styledata as $row)
	{
		$selected = ($row['style_id'] == $default) ? ' selected="selected"' : '';
		$style_options .= '<option value="' . $row['style_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
	}

	return $style_options;
}

/**
* Format the timezone offset with hours and minutes
*
* @param	int		$tz_offset	Timezone offset in seconds
* @param	bool	$show_null	Whether null offsets should be shown
* @return	string		Normalized offset string:	-7200 => -02:00
*													16200 => +04:30
*/
function phpbb_format_timezone_offset($tz_offset, $show_null = false)
{
	$sign = ($tz_offset < 0) ? '-' : '+';
	$time_offset = abs($tz_offset);

	if ($time_offset == 0 && $show_null == false)
	{
		return '';
	}

	$offset_seconds	= $time_offset % 3600;
	$offset_minutes	= $offset_seconds / 60;
	$offset_hours	= ($time_offset - $offset_seconds) / 3600;

	$offset_string	= sprintf("%s%02d:%02d", $sign, $offset_hours, $offset_minutes);
	return $offset_string;
}

/**
* Compares two time zone labels.
* Arranges them in increasing order by timezone offset.
* Places UTC before other timezones in the same offset.
*/
function phpbb_tz_select_compare($a, $b)
{
	$a_sign = $a[3];
	$b_sign = $b[3];
	if ($a_sign != $b_sign)
	{
		return $a_sign == '-' ? -1 : 1;
	}

	$a_offset = substr($a, 4, 5);
	$b_offset = substr($b, 4, 5);
	if ($a_offset == $b_offset)
	{
		$a_name = substr($a, 12);
		$b_name = substr($b, 12);
		if ($a_name == $b_name)
		{
			return 0;
		}
		else if ($a_name == 'UTC')
		{
			return -1;
		}
		else if ($b_name == 'UTC')
		{
			return 1;
		}
		else
		{
			return $a_name < $b_name ? -1 : 1;
		}
	}
	else
	{
		if ($a_sign == '-')
		{
			return $a_offset > $b_offset ? -1 : 1;
		}
		else
		{
			return $a_offset < $b_offset ? -1 : 1;
		}
	}
}

/**
* Return list of timezone identifiers
* We also add the selected timezone if we can create an object with it.
* DateTimeZone::listIdentifiers seems to not add all identifiers to the list,
* because some are only kept for backward compatible reasons. If the user has
* a deprecated value, we add it here, so it can still be kept. Once the user
* changed his value, there is no way back to deprecated values.
*
* @param	string		$selected_timezone		Additional timezone that shall
*												be added to the list of identiers
* @return		array		DateTimeZone::listIdentifiers and additional
*							selected_timezone if it is a valid timezone.
*/
function phpbb_get_timezone_identifiers($selected_timezone)
{
	$timezones = DateTimeZone::listIdentifiers();

	if (!in_array($selected_timezone, $timezones))
	{
		try
		{
			// Add valid timezones that are currently selected but not returned
			// by DateTimeZone::listIdentifiers
			$validate_timezone = new DateTimeZone($selected_timezone);
			$timezones[] = $selected_timezone;
		}
		catch (\Exception $e)
		{
		}
	}

	return $timezones;
}

/**
* Options to pick a timezone and date/time
*
* @param	\phpbb\template\template $template	phpBB template object
* @param	\phpbb\user	$user				Object of the current user
* @param	string		$default			A timezone to select
* @param	boolean		$truncate			Shall we truncate the options text
*
* @return		array		Returns an array containing the options for the time selector.
*/
function phpbb_timezone_select($template, $user, $default = '', $truncate = false)
{
	static $timezones;

	$default_offset = '';
	if (!isset($timezones))
	{
		$unsorted_timezones = phpbb_get_timezone_identifiers($default);

		$timezones = array();
		foreach ($unsorted_timezones as $timezone)
		{
			$tz = new DateTimeZone($timezone);
			$dt = $user->create_datetime('now', $tz);
			$offset = $dt->getOffset();
			$current_time = $dt->format($user->lang['DATETIME_FORMAT'], true);
			$offset_string = phpbb_format_timezone_offset($offset, true);
			$timezones['UTC' . $offset_string . ' - ' . $timezone] = array(
				'tz'		=> $timezone,
				'offset'	=> $offset_string,
				'current'	=> $current_time,
			);
			if ($timezone === $default)
			{
				$default_offset = 'UTC' . $offset_string;
			}
		}
		unset($unsorted_timezones);

		uksort($timezones, 'phpbb_tz_select_compare');
	}

	$tz_select = $opt_group = '';

	foreach ($timezones as $key => $timezone)
	{
		if ($opt_group != $timezone['offset'])
		{
			// Generate tz_select for backwards compatibility
			$tz_select .= ($opt_group) ? '</optgroup>' : '';
			$tz_select .= '<optgroup label="' . $user->lang(array('timezones', 'UTC_OFFSET_CURRENT'), $timezone['offset'], $timezone['current']) . '">';
			$opt_group = $timezone['offset'];
			$template->assign_block_vars('timezone_select', array(
				'LABEL'		=> $user->lang(array('timezones', 'UTC_OFFSET_CURRENT'), $timezone['offset'], $timezone['current']),
				'VALUE'		=> $key . ' - ' . $timezone['current'],
			));

			$selected = (!empty($default_offset) && strpos($key, $default_offset) !== false) ? ' selected="selected"' : '';
			$template->assign_block_vars('timezone_date', array(
				'VALUE'		=> $key . ' - ' . $timezone['current'],
				'SELECTED'	=> !empty($selected),
				'TITLE'		=> $user->lang(array('timezones', 'UTC_OFFSET_CURRENT'), $timezone['offset'], $timezone['current']),
			));
		}

		$label = $timezone['tz'];
		if (isset($user->lang['timezones'][$label]))
		{
			$label = $user->lang['timezones'][$label];
		}
		$title = $user->lang(array('timezones', 'UTC_OFFSET_CURRENT'), $timezone['offset'], $label);

		if ($truncate)
		{
			$label = truncate_string($label, 50, 255, false, '...');
		}

		// Also generate timezone_select for backwards compatibility
		$selected = ($timezone['tz'] === $default) ? ' selected="selected"' : '';
		$tz_select .= '<option title="' . $title . '" value="' . $timezone['tz'] . '"' . $selected . '>' . $label . '</option>';
		$template->assign_block_vars('timezone_select.timezone_options', array(
			'TITLE'			=> $title,
			'VALUE'			=> $timezone['tz'],
			'SELECTED'		=> !empty($selected),
			'LABEL'			=> $label,
		));
	}
	$tz_select .= '</optgroup>';

	return $tz_select;
}

// Functions handling topic/post tracking/marking

/**
* Marks a topic/forum as read
* Marks a topic as posted to
*
* @param string $mode (all, topics, topic, post)
* @param int|bool $forum_id Used in all, topics, and topic mode
* @param int|bool $topic_id Used in topic and post mode
* @param int $post_time 0 means current time(), otherwise to set a specific mark time
* @param int $user_id can only be used with $mode == 'post'
*/
function markread($mode, $forum_id = false, $topic_id = false, $post_time = 0, $user_id = 0)
{
	global $db, $user, $config;
	global $request, $phpbb_container, $phpbb_dispatcher;

	$post_time = ($post_time === 0 || $post_time > time()) ? time() : (int) $post_time;

	$should_markread = true;

	/**
	 * This event is used for performing actions directly before marking forums,
	 * topics or posts as read.
	 *
	 * It is also possible to prevent the marking. For that, the $should_markread parameter
	 * should be set to FALSE.
	 *
	 * @event core.markread_before
	 * @var	string	mode				Variable containing marking mode value
	 * @var	mixed	forum_id			Variable containing forum id, or false
	 * @var	mixed	topic_id			Variable containing topic id, or false
	 * @var	int		post_time			Variable containing post time
	 * @var	int		user_id				Variable containing the user id
	 * @var	bool	should_markread		Flag indicating if the markread should be done or not.
	 * @since 3.1.4-RC1
	 */
	$vars = array(
		'mode',
		'forum_id',
		'topic_id',
		'post_time',
		'user_id',
		'should_markread',
	);
	extract($phpbb_dispatcher->trigger_event('core.markread_before', compact($vars)));

	if (!$should_markread)
	{
		return;
	}

	if ($mode == 'all')
	{
		if (empty($forum_id))
		{
			// Mark all forums read (index page)
			/* @var $phpbb_notifications \phpbb\notification\manager */
			$phpbb_notifications = $phpbb_container->get('notification_manager');

			// Mark all topic notifications read for this user
			$phpbb_notifications->mark_notifications(array(
				'notification.type.topic',
				'notification.type.quote',
				'notification.type.bookmark',
				'notification.type.post',
				'notification.type.approve_topic',
				'notification.type.approve_post',
				'notification.type.forum',
			), false, $user->data['user_id'], $post_time);

			if ($config['load_db_lastread'] && $user->data['is_registered'])
			{
				// Mark all forums read (index page)
				$tables = array(TOPICS_TRACK_TABLE, FORUMS_TRACK_TABLE);
				foreach ($tables as $table)
				{
					$sql = 'DELETE FROM ' . $table . "
						WHERE user_id = {$user->data['user_id']}
							AND mark_time < $post_time";
					$db->sql_query($sql);
				}

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_lastmark = $post_time
					WHERE user_id = {$user->data['user_id']}
						AND user_lastmark < $post_time";
				$db->sql_query($sql);
			}
			else if ($config['load_anon_lastread'] || $user->data['is_registered'])
			{
				$tracking_topics = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
				$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

				unset($tracking_topics['tf']);
				unset($tracking_topics['t']);
				unset($tracking_topics['f']);
				$tracking_topics['l'] = base_convert($post_time - $config['board_startdate'], 10, 36);

				$user->set_cookie('track', tracking_serialize($tracking_topics), $post_time + 31536000);
				$request->overwrite($config['cookie_name'] . '_track', tracking_serialize($tracking_topics), \phpbb\request\request_interface::COOKIE);

				unset($tracking_topics);

				if ($user->data['is_registered'])
				{
					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_lastmark = $post_time
						WHERE user_id = {$user->data['user_id']}
							AND user_lastmark < $post_time";
					$db->sql_query($sql);
				}
			}
		}
	}
	else if ($mode == 'topics')
	{
		// Mark all topics in forums read
		if (!is_array($forum_id))
		{
			$forum_id = array($forum_id);
		}
		else
		{
			$forum_id = array_unique($forum_id);
		}

		/* @var $phpbb_notifications \phpbb\notification\manager */
		$phpbb_notifications = $phpbb_container->get('notification_manager');

		$phpbb_notifications->mark_notifications_by_parent(array(
			'notification.type.topic',
			'notification.type.approve_topic',
		), $forum_id, $user->data['user_id'], $post_time);

		// Mark all post/quote notifications read for this user in this forum
		$topic_ids = array();
		$sql = 'SELECT topic_id
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $forum_id);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_ids[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);

		$phpbb_notifications->mark_notifications_by_parent(array(
			'notification.type.quote',
			'notification.type.bookmark',
			'notification.type.post',
			'notification.type.approve_post',
			'notification.type.forum',
		), $topic_ids, $user->data['user_id'], $post_time);

		// Add 0 to forums array to mark global announcements correctly
		// $forum_id[] = 0;

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND mark_time < $post_time
					AND " . $db->sql_in_set('forum_id', $forum_id);
			$db->sql_query($sql);

			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND " . $db->sql_in_set('forum_id', $forum_id);
			$result = $db->sql_query($sql);

			$sql_update = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_update[] = (int) $row['forum_id'];
			}
			$db->sql_freeresult($result);

			if (count($sql_update))
			{
				$sql = 'UPDATE ' . FORUMS_TRACK_TABLE . "
					SET mark_time = $post_time
					WHERE user_id = {$user->data['user_id']}
						AND mark_time < $post_time
						AND " . $db->sql_in_set('forum_id', $sql_update);
				$db->sql_query($sql);
			}

			if ($sql_insert = array_diff($forum_id, $sql_update))
			{
				$sql_ary = array();
				foreach ($sql_insert as $f_id)
				{
					$sql_ary[] = array(
						'user_id'	=> (int) $user->data['user_id'],
						'forum_id'	=> (int) $f_id,
						'mark_time'	=> $post_time,
					);
				}

				$db->sql_multi_insert(FORUMS_TRACK_TABLE, $sql_ary);
			}
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$tracking = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
			$tracking = ($tracking) ? tracking_unserialize($tracking) : array();

			foreach ($forum_id as $f_id)
			{
				$topic_ids36 = (isset($tracking['tf'][$f_id])) ? $tracking['tf'][$f_id] : array();

				if (isset($tracking['tf'][$f_id]))
				{
					unset($tracking['tf'][$f_id]);
				}

				foreach ($topic_ids36 as $topic_id36)
				{
					unset($tracking['t'][$topic_id36]);
				}

				if (isset($tracking['f'][$f_id]))
				{
					unset($tracking['f'][$f_id]);
				}

				$tracking['f'][$f_id] = base_convert($post_time - $config['board_startdate'], 10, 36);
			}

			if (isset($tracking['tf']) && empty($tracking['tf']))
			{
				unset($tracking['tf']);
			}

			$user->set_cookie('track', tracking_serialize($tracking), $post_time + 31536000);
			$request->overwrite($config['cookie_name'] . '_track', tracking_serialize($tracking), \phpbb\request\request_interface::COOKIE);

			unset($tracking);
		}
	}
	else if ($mode == 'topic')
	{
		if ($topic_id === false || $forum_id === false)
		{
			return;
		}

		/* @var $phpbb_notifications \phpbb\notification\manager */
		$phpbb_notifications = $phpbb_container->get('notification_manager');

		// Mark post notifications read for this user in this topic
		$phpbb_notifications->mark_notifications(array(
			'notification.type.topic',
			'notification.type.approve_topic',
		), $topic_id, $user->data['user_id'], $post_time);

		$phpbb_notifications->mark_notifications_by_parent(array(
			'notification.type.quote',
			'notification.type.bookmark',
			'notification.type.post',
			'notification.type.approve_post',
			'notification.type.forum',
		), $topic_id, $user->data['user_id'], $post_time);

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$sql = 'UPDATE ' . TOPICS_TRACK_TABLE . "
				SET mark_time = $post_time
				WHERE user_id = {$user->data['user_id']}
					AND mark_time < $post_time
					AND topic_id = $topic_id";
			$db->sql_query($sql);

			// insert row
			if (!$db->sql_affectedrows())
			{
				$db->sql_return_on_error(true);

				$sql_ary = array(
					'user_id'		=> (int) $user->data['user_id'],
					'topic_id'		=> (int) $topic_id,
					'forum_id'		=> (int) $forum_id,
					'mark_time'		=> $post_time,
				);

				$db->sql_query('INSERT INTO ' . TOPICS_TRACK_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

				$db->sql_return_on_error(false);
			}
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$tracking = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
			$tracking = ($tracking) ? tracking_unserialize($tracking) : array();

			$topic_id36 = base_convert($topic_id, 10, 36);

			if (!isset($tracking['t'][$topic_id36]))
			{
				$tracking['tf'][$forum_id][$topic_id36] = true;
			}

			$tracking['t'][$topic_id36] = base_convert($post_time - (int) $config['board_startdate'], 10, 36);

			// If the cookie grows larger than 10000 characters we will remove the smallest value
			// This can result in old topics being unread - but most of the time it should be accurate...
			if (strlen($request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE)) > 10000)
			{
				//echo 'Cookie grown too large' . print_r($tracking, true);

				// We get the ten most minimum stored time offsets and its associated topic ids
				$time_keys = array();
				for ($i = 0; $i < 10 && count($tracking['t']); $i++)
				{
					$min_value = min($tracking['t']);
					$m_tkey = array_search($min_value, $tracking['t']);
					unset($tracking['t'][$m_tkey]);

					$time_keys[$m_tkey] = $min_value;
				}

				// Now remove the topic ids from the array...
				foreach ($tracking['tf'] as $f_id => $topic_id_ary)
				{
					foreach ($time_keys as $m_tkey => $min_value)
					{
						if (isset($topic_id_ary[$m_tkey]))
						{
							$tracking['f'][$f_id] = $min_value;
							unset($tracking['tf'][$f_id][$m_tkey]);
						}
					}
				}

				if ($user->data['is_registered'])
				{
					$user->data['user_lastmark'] = intval(base_convert(max($time_keys) + $config['board_startdate'], 36, 10));

					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_lastmark = $post_time
						WHERE user_id = {$user->data['user_id']}
							AND mark_time < $post_time";
					$db->sql_query($sql);
				}
				else
				{
					$tracking['l'] = max($time_keys);
				}
			}

			$user->set_cookie('track', tracking_serialize($tracking), $post_time + 31536000);
			$request->overwrite($config['cookie_name'] . '_track', tracking_serialize($tracking), \phpbb\request\request_interface::COOKIE);
		}
	}
	else if ($mode == 'post')
	{
		if ($topic_id === false)
		{
			return;
		}

		$use_user_id = (!$user_id) ? $user->data['user_id'] : $user_id;

		if ($config['load_db_track'] && $use_user_id != ANONYMOUS)
		{
			$db->sql_return_on_error(true);

			$sql_ary = array(
				'user_id'		=> (int) $use_user_id,
				'topic_id'		=> (int) $topic_id,
				'topic_posted'	=> 1,
			);

			$db->sql_query('INSERT INTO ' . TOPICS_POSTED_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

			$db->sql_return_on_error(false);
		}
	}

	/**
	 * This event is used for performing actions directly after forums,
	 * topics or posts have been marked as read.
	 *
	 * @event core.markread_after
	 * @var	string		mode				Variable containing marking mode value
	 * @var	mixed		forum_id			Variable containing forum id, or false
	 * @var	mixed		topic_id			Variable containing topic id, or false
	 * @var	int			post_time			Variable containing post time
	 * @var	int			user_id				Variable containing the user id
	 * @since 3.2.6-RC1
	 */
	$vars = array(
		'mode',
		'forum_id',
		'topic_id',
		'post_time',
		'user_id',
	);
	extract($phpbb_dispatcher->trigger_event('core.markread_after', compact($vars)));
}

/**
* Get topic tracking info by using already fetched info
*/
function get_topic_tracking($forum_id, $topic_ids, &$rowset, $forum_mark_time, $global_announce_list = false)
{
	global $user;

	$last_read = array();

	if (!is_array($topic_ids))
	{
		$topic_ids = array($topic_ids);
	}

	foreach ($topic_ids as $topic_id)
	{
		if (!empty($rowset[$topic_id]['mark_time']))
		{
			$last_read[$topic_id] = $rowset[$topic_id]['mark_time'];
		}
	}

	$topic_ids = array_diff($topic_ids, array_keys($last_read));

	if (count($topic_ids))
	{
		$mark_time = array();

		if (!empty($forum_mark_time[$forum_id]) && $forum_mark_time[$forum_id] !== false)
		{
			$mark_time[$forum_id] = $forum_mark_time[$forum_id];
		}

		$user_lastmark = (isset($mark_time[$forum_id])) ? $mark_time[$forum_id] : $user->data['user_lastmark'];

		foreach ($topic_ids as $topic_id)
		{
			$last_read[$topic_id] = $user_lastmark;
		}
	}

	return $last_read;
}

/**
* Get topic tracking info from db (for cookie based tracking only this function is used)
*/
function get_complete_topic_tracking($forum_id, $topic_ids, $global_announce_list = false)
{
	global $config, $user, $request;

	$last_read = array();

	if (!is_array($topic_ids))
	{
		$topic_ids = array($topic_ids);
	}

	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		global $db;

		$sql = 'SELECT topic_id, mark_time
			FROM ' . TOPICS_TRACK_TABLE . "
			WHERE user_id = {$user->data['user_id']}
				AND " . $db->sql_in_set('topic_id', $topic_ids);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$last_read[$row['topic_id']] = $row['mark_time'];
		}
		$db->sql_freeresult($result);

		$topic_ids = array_diff($topic_ids, array_keys($last_read));

		if (count($topic_ids))
		{
			$sql = 'SELECT forum_id, mark_time
				FROM ' . FORUMS_TRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND forum_id = $forum_id";
			$result = $db->sql_query($sql);

			$mark_time = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$mark_time[$row['forum_id']] = $row['mark_time'];
			}
			$db->sql_freeresult($result);

			$user_lastmark = (isset($mark_time[$forum_id])) ? $mark_time[$forum_id] : $user->data['user_lastmark'];

			foreach ($topic_ids as $topic_id)
			{
				$last_read[$topic_id] = $user_lastmark;
			}
		}
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		global $tracking_topics;

		if (!isset($tracking_topics) || !count($tracking_topics))
		{
			$tracking_topics = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
			$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
		}

		if (!$user->data['is_registered'])
		{
			$user_lastmark = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate'] : 0;
		}
		else
		{
			$user_lastmark = $user->data['user_lastmark'];
		}

		foreach ($topic_ids as $topic_id)
		{
			$topic_id36 = base_convert($topic_id, 10, 36);

			if (isset($tracking_topics['t'][$topic_id36]))
			{
				$last_read[$topic_id] = base_convert($tracking_topics['t'][$topic_id36], 36, 10) + $config['board_startdate'];
			}
		}

		$topic_ids = array_diff($topic_ids, array_keys($last_read));

		if (count($topic_ids))
		{
			$mark_time = array();

			if (isset($tracking_topics['f'][$forum_id]))
			{
				$mark_time[$forum_id] = base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'];
			}

			$user_lastmark = (isset($mark_time[$forum_id])) ? $mark_time[$forum_id] : $user_lastmark;

			foreach ($topic_ids as $topic_id)
			{
				$last_read[$topic_id] = $user_lastmark;
			}
		}
	}

	return $last_read;
}

/**
* Get list of unread topics
*
* @param int $user_id			User ID (or false for current user)
* @param string $sql_extra		Extra WHERE SQL statement
* @param string $sql_sort		ORDER BY SQL sorting statement
* @param string $sql_limit		Limits the size of unread topics list, 0 for unlimited query
* @param string $sql_limit_offset  Sets the offset of the first row to search, 0 to search from the start
*
* @return int[]		Topic ids as keys, mark_time of topic as value
*/
function get_unread_topics($user_id = false, $sql_extra = '', $sql_sort = '', $sql_limit = 1001, $sql_limit_offset = 0)
{
	global $config, $db, $user, $request;
	global $phpbb_dispatcher;

	$user_id = ($user_id === false) ? (int) $user->data['user_id'] : (int) $user_id;

	// Data array we're going to return
	$unread_topics = array();

	if (empty($sql_sort))
	{
		$sql_sort = 'ORDER BY t.topic_last_post_time DESC, t.topic_last_post_id DESC';
	}

	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		// Get list of the unread topics
		$last_mark = (int) $user->data['user_lastmark'];

		$sql_array = array(
			'SELECT'		=> 't.topic_id, t.topic_last_post_time, tt.mark_time as topic_mark_time, ft.mark_time as forum_mark_time',

			'FROM'			=> array(TOPICS_TABLE => 't'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
					'ON'	=> "tt.user_id = $user_id AND t.topic_id = tt.topic_id",
				),
				array(
					'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
					'ON'	=> "ft.user_id = $user_id AND t.forum_id = ft.forum_id",
				),
			),

			'WHERE'			=> "
				 t.topic_last_post_time > $last_mark AND
				(
				(tt.mark_time IS NOT NULL AND t.topic_last_post_time > tt.mark_time) OR
				(tt.mark_time IS NULL AND ft.mark_time IS NOT NULL AND t.topic_last_post_time > ft.mark_time) OR
				(tt.mark_time IS NULL AND ft.mark_time IS NULL)
				)
				$sql_extra
				$sql_sort",
		);

		/**
		 * Change SQL query for fetching unread topics data
		 *
		 * @event core.get_unread_topics_modify_sql
		 * @var array     sql_array    Fully assembled SQL query with keys SELECT, FROM, LEFT_JOIN, WHERE
		 * @var int       last_mark    User's last_mark time
		 * @var string    sql_extra    Extra WHERE SQL statement
		 * @var string    sql_sort     ORDER BY SQL sorting statement
		 * @since 3.1.4-RC1
		 */
		$vars = array(
			'sql_array',
			'last_mark',
			'sql_extra',
			'sql_sort',
		);
		extract($phpbb_dispatcher->trigger_event('core.get_unread_topics_modify_sql', compact($vars)));

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $sql_limit, $sql_limit_offset);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_id = (int) $row['topic_id'];
			$unread_topics[$topic_id] = ($row['topic_mark_time']) ? (int) $row['topic_mark_time'] : (($row['forum_mark_time']) ? (int) $row['forum_mark_time'] : $last_mark);
		}
		$db->sql_freeresult($result);
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		global $tracking_topics;

		if (empty($tracking_topics))
		{
			$tracking_topics = $request->variable($config['cookie_name'] . '_track', '', false, \phpbb\request\request_interface::COOKIE);
			$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
		}

		if (!$user->data['is_registered'])
		{
			$user_lastmark = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate'] : 0;
		}
		else
		{
			$user_lastmark = (int) $user->data['user_lastmark'];
		}

		$sql = 'SELECT t.topic_id, t.forum_id, t.topic_last_post_time
			FROM ' . TOPICS_TABLE . ' t
			WHERE t.topic_last_post_time > ' . $user_lastmark . "
			$sql_extra
			$sql_sort";
		$result = $db->sql_query_limit($sql, $sql_limit, $sql_limit_offset);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_id = (int) $row['forum_id'];
			$topic_id = (int) $row['topic_id'];
			$topic_id36 = base_convert($topic_id, 10, 36);

			if (isset($tracking_topics['t'][$topic_id36]))
			{
				$last_read = base_convert($tracking_topics['t'][$topic_id36], 36, 10) + $config['board_startdate'];

				if ($row['topic_last_post_time'] > $last_read)
				{
					$unread_topics[$topic_id] = $last_read;
				}
			}
			else if (isset($tracking_topics['f'][$forum_id]))
			{
				$mark_time = base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'];

				if ($row['topic_last_post_time'] > $mark_time)
				{
					$unread_topics[$topic_id] = $mark_time;
				}
			}
			else
			{
				$unread_topics[$topic_id] = $user_lastmark;
			}
		}
		$db->sql_freeresult($result);
	}

	return $unread_topics;
}

/**
* Check for read forums and update topic tracking info accordingly
*
* @param int $forum_id the forum id to check
* @param int $forum_last_post_time the forums last post time
* @param int $f_mark_time the forums last mark time if user is registered and load_db_lastread enabled
* @param int $mark_time_forum false if the mark time needs to be obtained, else the last users forum mark time
*
* @return true if complete forum got marked read, else false.
*/
function update_forum_tracking_info($forum_id, $forum_last_post_time, $f_mark_time = false, $mark_time_forum = false)
{
	global $db, $tracking_topics, $user, $config, $request, $phpbb_container;

	// Determine the users last forum mark time if not given.
	if ($mark_time_forum === false)
	{
		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$mark_time_forum = (!empty($f_mark_time)) ? $f_mark_time : $user->data['user_lastmark'];
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$tracking_topics = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
			$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

			if (!$user->data['is_registered'])
			{
				$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
			}

			$mark_time_forum = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate']) : $user->data['user_lastmark'];
		}
	}

	// Handle update of unapproved topics info.
	// Only update for moderators having m_approve permission for the forum.
	/* @var $phpbb_content_visibility \phpbb\content_visibility */
	$phpbb_content_visibility = $phpbb_container->get('content.visibility');

	// Check the forum for any left unread topics.
	// If there are none, we mark the forum as read.
	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		if ($mark_time_forum >= $forum_last_post_time)
		{
			// We do not need to mark read, this happened before. Therefore setting this to true
			$row = true;
		}
		else
		{
			$sql = 'SELECT t.forum_id
				FROM ' . TOPICS_TABLE . ' t
				LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt
					ON (tt.topic_id = t.topic_id
						AND tt.user_id = ' . $user->data['user_id'] . ')
				WHERE t.forum_id = ' . $forum_id . '
					AND t.topic_last_post_time > ' . $mark_time_forum . '
					AND t.topic_moved_id = 0
					AND ' . $phpbb_content_visibility->get_visibility_sql('topic', $forum_id, 't.') . '
					AND (tt.topic_id IS NULL
						OR tt.mark_time < t.topic_last_post_time)';
			$result = $db->sql_query_limit($sql, 1);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		// Get information from cookie
		if (!isset($tracking_topics['tf'][$forum_id]))
		{
			// We do not need to mark read, this happened before. Therefore setting this to true
			$row = true;
		}
		else
		{
			$sql = 'SELECT t.topic_id
				FROM ' . TOPICS_TABLE . ' t
				WHERE t.forum_id = ' . $forum_id . '
					AND t.topic_last_post_time > ' . $mark_time_forum . '
					AND t.topic_moved_id = 0
					AND ' . $phpbb_content_visibility->get_visibility_sql('topic', $forum_id, 't.');
			$result = $db->sql_query($sql);

			$check_forum = $tracking_topics['tf'][$forum_id];
			$unread = false;

			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($check_forum[base_convert($row['topic_id'], 10, 36)]))
				{
					$unread = true;
					break;
				}
			}
			$db->sql_freeresult($result);

			$row = $unread;
		}
	}
	else
	{
		$row = true;
	}

	if (!$row)
	{
		markread('topics', $forum_id);
		return true;
	}

	return false;
}

/**
* Transform an array into a serialized format
*/
function tracking_serialize($input)
{
	$out = '';
	foreach ($input as $key => $value)
	{
		if (is_array($value))
		{
			$out .= $key . ':(' . tracking_serialize($value) . ');';
		}
		else
		{
			$out .= $key . ':' . $value . ';';
		}
	}
	return $out;
}

/**
* Transform a serialized array into an actual array
*/
function tracking_unserialize($string, $max_depth = 3)
{
	$n = strlen($string);
	if ($n > 10010)
	{
		die('Invalid data supplied');
	}
	$data = $stack = array();
	$key = '';
	$mode = 0;
	$level = &$data;
	for ($i = 0; $i < $n; ++$i)
	{
		switch ($mode)
		{
			case 0:
				switch ($string[$i])
				{
					case ':':
						$level[$key] = 0;
						$mode = 1;
					break;
					case ')':
						unset($level);
						$level = array_pop($stack);
						$mode = 3;
					break;
					default:
						$key .= $string[$i];
				}
			break;

			case 1:
				switch ($string[$i])
				{
					case '(':
						if (count($stack) >= $max_depth)
						{
							die('Invalid data supplied');
						}
						$stack[] = &$level;
						$level[$key] = array();
						$level = &$level[$key];
						$key = '';
						$mode = 0;
					break;
					default:
						$level[$key] = $string[$i];
						$mode = 2;
					break;
				}
			break;

			case 2:
				switch ($string[$i])
				{
					case ')':
						unset($level);
						$level = array_pop($stack);
						$mode = 3;
					break;
					case ';':
						$key = '';
						$mode = 0;
					break;
					default:
						$level[$key] .= $string[$i];
					break;
				}
			break;

			case 3:
				switch ($string[$i])
				{
					case ')':
						unset($level);
						$level = array_pop($stack);
					break;
					case ';':
						$key = '';
						$mode = 0;
					break;
					default:
						die('Invalid data supplied');
					break;
				}
			break;
		}
	}

	if (count($stack) != 0 || ($mode != 0 && $mode != 3))
	{
		die('Invalid data supplied');
	}

	return $level;
}

// Server functions (building urls, redirecting...)

/**
* Append session id to url.
* This function supports hooks.
*
* @param string $url The url the session id needs to be appended to (can have params)
* @param mixed $params String or array of additional url parameters
* @param bool $is_amp Is url using &amp; (true) or & (false)
* @param string $session_id Possibility to use a custom session id instead of the global one
* @param bool $is_route Is url generated by a route.
*
* @return string The corrected url.
*
* Examples:
* <code> append_sid("{$phpbb_root_path}viewtopic.$phpEx?t=1");
* append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=1');
* append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=1', false);
* append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('t' => 1, 'f' => 2));
* </code>
*
*/
function append_sid($url, $params = false, $is_amp = true, $session_id = false, $is_route = false)
{
	global $_SID, $_EXTRA_URL, $phpbb_hook, $phpbb_path_helper;
	global $phpbb_dispatcher;

	if ($params === '' || (is_array($params) && empty($params)))
	{
		// Do not append the ? if the param-list is empty anyway.
		$params = false;
	}

	// Update the root path with the correct relative web path
	if (!$is_route && $phpbb_path_helper instanceof \phpbb\path_helper)
	{
		$url = $phpbb_path_helper->update_web_root_path($url);
	}

	$append_sid_overwrite = false;

	/**
	* This event can either supplement or override the append_sid() function
	*
	* To override this function, the event must set $append_sid_overwrite to
	* the new URL value, which will be returned following the event
	*
	* @event core.append_sid
	* @var	string		url						The url the session id needs
	*											to be appended to (can have
	*											params)
	* @var	mixed		params					String or array of additional
	*											url parameters
	* @var	bool		is_amp					Is url using &amp; (true) or
	*											& (false)
	* @var	bool|string	session_id				Possibility to use a custom
	*											session id (string) instead of
	*											the global one (false)
	* @var	bool|string	append_sid_overwrite	Overwrite function (string
	*											URL) or not (false)
	* @var	bool	is_route					Is url generated by a route.
	* @since 3.1.0-a1
	*/
	$vars = array('url', 'params', 'is_amp', 'session_id', 'append_sid_overwrite', 'is_route');
	extract($phpbb_dispatcher->trigger_event('core.append_sid', compact($vars)));

	if ($append_sid_overwrite)
	{
		return $append_sid_overwrite;
	}

	// The following hook remains for backwards compatibility, though use of
	// the event above is preferred.
	// Developers using the hook function need to globalise the $_SID and $_EXTRA_URL on their own and also handle it appropriately.
	// They could mimic most of what is within this function
	if (!empty($phpbb_hook) && $phpbb_hook->call_hook(__FUNCTION__, $url, $params, $is_amp, $session_id))
	{
		if ($phpbb_hook->hook_return(__FUNCTION__))
		{
			return $phpbb_hook->hook_return_result(__FUNCTION__);
		}
	}

	$params_is_array = is_array($params);

	// Get anchor
	$anchor = '';
	if (strpos($url, '#') !== false)
	{
		list($url, $anchor) = explode('#', $url, 2);
		$anchor = '#' . $anchor;
	}
	else if (!$params_is_array && strpos($params, '#') !== false)
	{
		list($params, $anchor) = explode('#', $params, 2);
		$anchor = '#' . $anchor;
	}

	// Handle really simple cases quickly
	if ($_SID == '' && $session_id === false && empty($_EXTRA_URL) && !$params_is_array && !$anchor)
	{
		if ($params === false)
		{
			return $url;
		}

		$url_delim = (strpos($url, '?') === false) ? '?' : (($is_amp) ? '&amp;' : '&');
		return $url . ($params !== false ? $url_delim. $params : '');
	}

	// Assign sid if session id is not specified
	if ($session_id === false)
	{
		$session_id = $_SID;
	}

	$amp_delim = ($is_amp) ? '&amp;' : '&';
	$url_delim = (strpos($url, '?') === false) ? '?' : $amp_delim;

	// Appending custom url parameter?
	$append_url = (!empty($_EXTRA_URL)) ? implode($amp_delim, $_EXTRA_URL) : '';

	// Use the short variant if possible ;)
	if ($params === false)
	{
		// Append session id
		if (!$session_id)
		{
			return $url . (($append_url) ? $url_delim . $append_url : '') . $anchor;
		}
		else
		{
			return $url . (($append_url) ? $url_delim . $append_url . $amp_delim : $url_delim) . 'sid=' . $session_id . $anchor;
		}
	}

	// Build string if parameters are specified as array
	if (is_array($params))
	{
		$output = array();

		foreach ($params as $key => $item)
		{
			if ($item === NULL)
			{
				continue;
			}

			if ($key == '#')
			{
				$anchor = '#' . $item;
				continue;
			}

			$output[] = $key . '=' . $item;
		}

		$params = implode($amp_delim, $output);
	}

	// Append session id and parameters (even if they are empty)
	// If parameters are empty, the developer can still append his/her parameters without caring about the delimiter
	return $url . (($append_url) ? $url_delim . $append_url . $amp_delim : $url_delim) . $params . ((!$session_id) ? '' : $amp_delim . 'sid=' . $session_id) . $anchor;
}

/**
* Generate board url (example: http://www.example.com/phpBB)
*
* @param bool $without_script_path if set to true the script path gets not appended (example: http://www.example.com)
*
* @return string the generated board url
*/
function generate_board_url($without_script_path = false)
{
	global $config, $user, $request, $symfony_request;

	$server_name = $user->host;

	// Forcing server vars is the only way to specify/override the protocol
	if ($config['force_server_vars'] || !$server_name)
	{
		$server_protocol = ($config['server_protocol']) ? $config['server_protocol'] : (($config['cookie_secure']) ? 'https://' : 'http://');
		$server_name = $config['server_name'];
		$server_port = (int) $config['server_port'];
		$script_path = $config['script_path'];

		$url = $server_protocol . $server_name;
		$cookie_secure = $config['cookie_secure'];
	}
	else
	{
		$server_port = (int) $symfony_request->getPort();

		$forwarded_proto = $request->server('HTTP_X_FORWARDED_PROTO');

		if (!empty($forwarded_proto) && $forwarded_proto === 'https')
		{
			$server_port = 443;
		}
		// Do not rely on cookie_secure, users seem to think that it means a secured cookie instead of an encrypted connection
		$cookie_secure = $request->is_secure() ? 1 : 0;
		$url = (($cookie_secure) ? 'https://' : 'http://') . $server_name;

		$script_path = $user->page['root_script_path'];
	}

	if ($server_port && (($cookie_secure && $server_port <> 443) || (!$cookie_secure && $server_port <> 80)))
	{
		// HTTP HOST can carry a port number (we fetch $user->host, but for old versions this may be true)
		if (strpos($server_name, ':') === false)
		{
			$url .= ':' . $server_port;
		}
	}

	if (!$without_script_path)
	{
		$url .= $script_path;
	}

	// Strip / from the end
	if (substr($url, -1, 1) == '/')
	{
		$url = substr($url, 0, -1);
	}

	return $url;
}

/**
* Redirects the user to another page then exits the script nicely
* This function is intended for urls within the board. It's not meant to redirect to cross-domains.
*
* @param string $url The url to redirect to
* @param bool $return If true, do not redirect but return the sanitized URL. Default is no return.
* @param bool $disable_cd_check If true, redirect() will redirect to an external domain. If false, the redirect point to the boards url if it does not match the current domain. Default is false.
*/
function redirect($url, $return = false, $disable_cd_check = false)
{
	global $user, $phpbb_path_helper, $phpbb_dispatcher;

	if (!$user->is_setup())
	{
		$user->add_lang('common');
	}

	// Make sure no &amp;'s are in, this will break the redirect
	$url = str_replace('&amp;', '&', $url);

	// Determine which type of redirect we need to handle...
	$url_parts = @parse_url($url);

	if ($url_parts === false)
	{
		// Malformed url
		trigger_error('INSECURE_REDIRECT', E_USER_WARNING);
	}
	else if (!empty($url_parts['scheme']) && !empty($url_parts['host']))
	{
		// Attention: only able to redirect within the same domain if $disable_cd_check is false (yourdomain.com -> www.yourdomain.com will not work)
		if (!$disable_cd_check && $url_parts['host'] !== $user->host)
		{
			trigger_error('INSECURE_REDIRECT', E_USER_WARNING);
		}
	}
	else if ($url[0] == '/')
	{
		// Absolute uri, prepend direct url...
		$url = generate_board_url(true) . $url;
	}
	else
	{
		// Relative uri
		$pathinfo = pathinfo($url);

		// Is the uri pointing to the current directory?
		if ($pathinfo['dirname'] == '.')
		{
			$url = str_replace('./', '', $url);

			// Strip / from the beginning
			if ($url && substr($url, 0, 1) == '/')
			{
				$url = substr($url, 1);
			}
		}

		$url = $phpbb_path_helper->remove_web_root_path($url);

		if ($user->page['page_dir'])
		{
			$url = $user->page['page_dir'] . '/' . $url;
		}

		$url = generate_board_url() . '/' . $url;
	}

	// Clean URL and check if we go outside the forum directory
	$url = $phpbb_path_helper->clean_url($url);

	if (!$disable_cd_check && strpos($url, generate_board_url(true) . '/') !== 0)
	{
		trigger_error('INSECURE_REDIRECT', E_USER_WARNING);
	}

	// Make sure no linebreaks are there... to prevent http response splitting for PHP < 4.4.2
	if (strpos(urldecode($url), "\n") !== false || strpos(urldecode($url), "\r") !== false || strpos($url, ';') !== false)
	{
		trigger_error('INSECURE_REDIRECT', E_USER_WARNING);
	}

	// Now, also check the protocol and for a valid url the last time...
	$allowed_protocols = array('http', 'https', 'ftp', 'ftps');
	$url_parts = parse_url($url);

	if ($url_parts === false || empty($url_parts['scheme']) || !in_array($url_parts['scheme'], $allowed_protocols))
	{
		trigger_error('INSECURE_REDIRECT', E_USER_WARNING);
	}

	/**
	* Execute code and/or overwrite redirect()
	*
	* @event core.functions.redirect
	* @var	string	url					The url
	* @var	bool	return				If true, do not redirect but return the sanitized URL.
	* @var	bool	disable_cd_check	If true, redirect() will redirect to an external domain. If false, the redirect point to the boards url if it does not match the current domain.
	* @since 3.1.0-RC3
	*/
	$vars = array('url', 'return', 'disable_cd_check');
	extract($phpbb_dispatcher->trigger_event('core.functions.redirect', compact($vars)));

	if ($return)
	{
		return $url;
	}
	else
	{
		garbage_collection();
	}

	// Behave as per HTTP/1.1 spec for others
	header('Location: ' . $url);
	exit;
}

/**
 * Returns the install redirect path for phpBB.
 *
 * @param string $phpbb_root_path The root path of the phpBB installation.
 * @param string $phpEx The file extension of php files, e.g., "php".
 * @return string The install redirect path.
 */
function phpbb_get_install_redirect(string $phpbb_root_path, string $phpEx): string
{
	$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	if (!$script_name)
	{
		$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	}

	// Add trailing dot to prevent dirname() from returning parent directory if $script_name is a directory
	$script_name = substr($script_name, -1) === '/' ? $script_name . '.' : $script_name;

	// $phpbb_root_path accounts for redirects from e.g. /adm
	$script_path = trim(dirname($script_name)) . '/' . $phpbb_root_path . 'install/app.' . $phpEx;
	// Replace any number of consecutive backslashes and/or slashes with a single slash
	// (could happen on some proxy setups and/or Windows servers)
	return preg_replace('#[\\\\/]{2,}#', '/', $script_path);
}

/**
* Re-Apply session id after page reloads
*/
function reapply_sid($url, $is_route = false)
{
	global $phpEx, $phpbb_root_path;

	if ($url === "index.$phpEx")
	{
		return append_sid("index.$phpEx");
	}
	else if ($url === "{$phpbb_root_path}index.$phpEx")
	{
		return append_sid("{$phpbb_root_path}index.$phpEx");
	}

	// Remove previously added sid
	if (strpos($url, 'sid=') !== false)
	{
		// All kind of links
		$url = preg_replace('/(\?)?(&amp;|&)?sid=[a-z0-9]+/', '', $url);
		// if the sid was the first param, make the old second as first ones
		$url = preg_replace("/$phpEx(&amp;|&)+?/", "$phpEx?", $url);
	}

	return append_sid($url, false, true, false, $is_route);
}

/**
* Returns url from the session/current page with an re-appended SID with optionally stripping vars from the url
*/
function build_url($strip_vars = false)
{
	global $config, $user, $phpbb_path_helper;

	$page = $phpbb_path_helper->get_valid_page($user->page['page'], $config['enable_mod_rewrite']);

	// Append SID
	$redirect = append_sid($page, false, false);

	if ($strip_vars !== false)
	{
		$redirect = $phpbb_path_helper->strip_url_params($redirect, $strip_vars, false);
	}
	else
	{
		$redirect = str_replace('&', '&amp;', $redirect);
	}

	return $redirect . ((strpos($redirect, '?') === false) ? '?' : '');
}

/**
* Meta refresh assignment
* Adds META template variable with meta http tag.
*
* @param int $time Time in seconds for meta refresh tag
* @param string $url URL to redirect to. The url will go through redirect() first before the template variable is assigned
* @param bool $disable_cd_check If true, meta_refresh() will redirect to an external domain. If false, the redirect point to the boards url if it does not match the current domain. Default is false.
*/
function meta_refresh($time, $url, $disable_cd_check = false)
{
	global $template, $refresh_data, $request;

	$url = redirect($url, true, $disable_cd_check);
	if ($request->is_ajax())
	{
		$refresh_data = array(
			'time'	=> $time,
			'url'	=> $url,
		);
	}
	else
	{
		// For XHTML compatibility we change back & to &amp;
		$url = str_replace('&', '&amp;', $url);

		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="' . $time . '; url=' . $url . '" />')
		);
	}

	return $url;
}

/**
* Outputs correct status line header.
*
* Depending on php sapi one of the two following forms is used:
*
* Status: 404 Not Found
*
* HTTP/1.x 404 Not Found
*
* HTTP version is taken from HTTP_VERSION environment variable,
* and defaults to 1.0.
*
* Sample usage:
*
* send_status_line(404, 'Not Found');
*
* @param int $code HTTP status code
* @param string $message Message for the status code
* @return null
*/
function send_status_line($code, $message)
{
	if (substr(strtolower(@php_sapi_name()), 0, 3) === 'cgi')
	{
		// in theory, we shouldn't need that due to php doing it. Reality offers a differing opinion, though
		header("Status: $code $message", true, $code);
	}
	else
	{
		$version = phpbb_request_http_version();
		header("$version $code $message", true, $code);
	}
}

/**
* Returns the HTTP version used in the current request.
*
* Handles the case of being called before $request is present,
* in which case it falls back to the $_SERVER superglobal.
*
* @return string HTTP version
*/
function phpbb_request_http_version()
{
	global $request;

	$version = '';
	if ($request && $request->server('SERVER_PROTOCOL'))
	{
		$version = $request->server('SERVER_PROTOCOL');
	}
	else if (isset($_SERVER['SERVER_PROTOCOL']))
	{
		$version = $_SERVER['SERVER_PROTOCOL'];
	}

	if (!empty($version) && is_string($version) && preg_match('#^HTTP/[0-9]\.[0-9]$#', $version))
	{
		return $version;
	}

	return 'HTTP/1.0';
}

//Form validation


/**
* Add a secret hash   for use in links/GET requests
* @param string  $link_name The name of the link; has to match the name used in check_link_hash, otherwise no restrictions apply
* @return string the hash

*/
function generate_link_hash($link_name)
{
	global $user;

	if (!isset($user->data["hash_$link_name"]))
	{
		$user->data["hash_$link_name"] = substr(sha1($user->data['user_form_salt'] . $link_name), 0, 8);
	}

	return $user->data["hash_$link_name"];
}


/**
* checks a link hash - for GET requests
* @param string $token the submitted token
* @param string $link_name The name of the link
* @return boolean true if all is fine
*/
function check_link_hash($token, $link_name)
{
	return $token === generate_link_hash($link_name);
}

/**
* Add a secret token to the form (requires the S_FORM_TOKEN template variable)
* @param string  $form_name The name of the form; has to match the name used in check_form_key, otherwise no restrictions apply
* @param string  $template_variable_suffix A string that is appended to the name of the template variable to which the form elements are assigned
*/
function add_form_key($form_name, $template_variable_suffix = '')
{
	global $config, $template, $user, $phpbb_dispatcher;

	$now = time();
	$token_sid = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->session_id : '';
	$token = sha1($now . $user->data['user_form_salt'] . $form_name . $token_sid);

	$s_fields = build_hidden_fields(array(
		'creation_time' => $now,
		'form_token'	=> $token,
	));

	/**
	* Perform additional actions on creation of the form token
	*
	* @event core.add_form_key
	* @var	string	form_name					The form name
	* @var	int		now							Current time timestamp
	* @var	string	s_fields					Generated hidden fields
	* @var	string	token						Form token
	* @var	string	token_sid					User session ID
	* @var	string	template_variable_suffix	The string that is appended to template variable name
	*
	* @since 3.1.0-RC3
	* @changed 3.1.11-RC1 Added template_variable_suffix
	*/
	$vars = array(
		'form_name',
		'now',
		's_fields',
		'token',
		'token_sid',
		'template_variable_suffix',
	);
	extract($phpbb_dispatcher->trigger_event('core.add_form_key', compact($vars)));

	$template->assign_var('S_FORM_TOKEN' . $template_variable_suffix, $s_fields);
}

/**
 * Check the form key. Required for all altering actions not secured by confirm_box
 *
 * @param	string	$form_name	The name of the form; has to match the name used
 *								in add_form_key, otherwise no restrictions apply
 * @param	int		$timespan	The maximum acceptable age for a submitted form
 *								in seconds. Defaults to the config setting.
 * @return	bool	True, if the form key was valid, false otherwise
 */
function check_form_key($form_name, $timespan = false)
{
	global $config, $request, $user;

	if ($timespan === false)
	{
		// we enforce a minimum value of half a minute here.
		$timespan = ($config['form_token_lifetime'] == -1) ? -1 : max(30, $config['form_token_lifetime']);
	}

	if ($request->is_set_post('creation_time') && $request->is_set_post('form_token'))
	{
		$creation_time	= abs($request->variable('creation_time', 0));
		$token = $request->variable('form_token', '');

		$diff = time() - $creation_time;

		// If creation_time and the time() now is zero we can assume it was not a human doing this (the check for if ($diff)...
		if (defined('DEBUG_TEST') || $diff && ($diff <= $timespan || $timespan === -1))
		{
			$token_sid = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->session_id : '';
			$key = sha1($creation_time . $user->data['user_form_salt'] . $form_name . $token_sid);

			if ($key === $token)
			{
				return true;
			}
		}
	}

	return false;
}

// Message/Login boxes

/**
* Build Confirm box
* @param boolean $check True for checking if confirmed (without any additional parameters) and false for displaying the confirm box
* @param string|array $title Title/Message used for confirm box.
*		message text is _CONFIRM appended to title.
*		If title cannot be found in user->lang a default one is displayed
*		If title_CONFIRM cannot be found in user->lang the text given is used.
*       If title is an array, the first array value is used as explained per above,
*       all other array values are sent as parameters to the language function.
* @param string $hidden Hidden variables
* @param string $html_body Template used for confirm box
* @param string $u_action Custom form action
*
* @return bool True if confirmation was successful, false if not
*/
function confirm_box($check, $title = '', $hidden = '', $html_body = 'confirm_body.html', $u_action = '')
{
	global $user, $template, $db, $request;
	global $config, $language, $phpbb_path_helper, $phpbb_dispatcher;

	if (isset($_POST['cancel']))
	{
		return false;
	}

	$confirm = ($language->lang('YES') === $request->variable('confirm', '', true, \phpbb\request\request_interface::POST));

	if ($check && $confirm)
	{
		$user_id = $request->variable('confirm_uid', 0);
		$session_id = $request->variable('sess', '');
		$confirm_key = $request->variable('confirm_key', '');

		if ($user_id != $user->data['user_id'] || $session_id != $user->session_id || !$confirm_key || !$user->data['user_last_confirm_key'] || $confirm_key != $user->data['user_last_confirm_key'])
		{
			return false;
		}

		// Reset user_last_confirm_key
		$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = ''
			WHERE user_id = " . $user->data['user_id'];
		$db->sql_query($sql);

		return true;
	}
	else if ($check)
	{
		return false;
	}

	$s_hidden_fields = build_hidden_fields(array(
		'confirm_uid'	=> $user->data['user_id'],
		'sess'			=> $user->session_id,
		'sid'			=> $user->session_id,
	));

	// generate activation key
	$confirm_key = gen_rand_string(10);

	// generate language strings
	if (is_array($title))
	{
		$key = array_shift($title);
		$count = array_shift($title);
		$confirm_title =  $language->is_set($key) ? $language->lang($key, $count, $title) : $language->lang('CONFIRM');
		$confirm_text = $language->is_set($key . '_CONFIRM') ? $language->lang($key . '_CONFIRM', $count, $title) : $key;
	}
	else
	{
		$confirm_title = $language->is_set($title) ? $language->lang($title) : $language->lang('CONFIRM');
		$confirm_text = $language->is_set($title . '_CONFIRM') ? $language->lang($title . '_CONFIRM') : $title;
	}

	if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
	{
		adm_page_header($confirm_title);
	}
	else
	{
		page_header($confirm_title);
	}

	$template->set_filenames(array(
		'body' => $html_body)
	);

	// If activation key already exist, we better do not re-use the key (something very strange is going on...)
	if ($request->variable('confirm_key', ''))
	{
		// This should not occur, therefore we cancel the operation to safe the user
		return false;
	}

	// re-add sid / transform & to &amp; for user->page (user->page is always using &)
	$use_page = ($u_action) ? $u_action : str_replace('&', '&amp;', $user->page['page']);
	$u_action = reapply_sid($phpbb_path_helper->get_valid_page($use_page, $config['enable_mod_rewrite']));
	$u_action .= ((strpos($u_action, '?') === false) ? '?' : '&amp;') . 'confirm_key=' . $confirm_key;

	$template->assign_vars(array(
		'MESSAGE_TITLE'		=> $confirm_title,
		'MESSAGE_TEXT'		=> $confirm_text,

		'YES_VALUE'			=> $language->lang('YES'),
		'S_CONFIRM_ACTION'	=> $u_action,
		'S_HIDDEN_FIELDS'	=> $hidden . $s_hidden_fields,
		'S_AJAX_REQUEST'	=> $request->is_ajax(),
	));

	$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = '" . $db->sql_escape($confirm_key) . "'
		WHERE user_id = " . $user->data['user_id'];
	$db->sql_query($sql);

	if ($request->is_ajax())
	{
		$u_action .= '&confirm_uid=' . $user->data['user_id'] . '&sess=' . $user->session_id . '&sid=' . $user->session_id;
		$data = array(
			'MESSAGE_BODY'		=> $template->assign_display('body'),
			'MESSAGE_TITLE'		=> $confirm_title,
			'MESSAGE_TEXT'		=> $confirm_text,

			'YES_VALUE'			=> $language->lang('YES'),
			'S_CONFIRM_ACTION'	=> str_replace('&amp;', '&', $u_action), //inefficient, rewrite whole function
			'S_HIDDEN_FIELDS'	=> $hidden . $s_hidden_fields
		);

		/**
		 * This event allows an extension to modify the ajax output of confirm box.
		 *
		 * @event core.confirm_box_ajax_before
		 * @var string	u_action		Action of the form
		 * @var array	data			Data to be sent
		 * @var string	hidden			Hidden fields generated by caller
		 * @var string	s_hidden_fields	Hidden fields generated by this function
		 * @since 3.2.8-RC1
		 */
		$vars = array(
			'u_action',
			'data',
			'hidden',
			's_hidden_fields',
		);
		extract($phpbb_dispatcher->trigger_event('core.confirm_box_ajax_before', compact($vars)));

		$json_response = new \phpbb\json_response;
		$json_response->send($data);
	}

	if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
	{
		adm_page_footer();
	}
	else
	{
		page_footer();
	}

	exit; // unreachable, page_footer() above will call exit()
}

/**
* Generate login box or verify password
*/
function login_box($redirect = '', $l_explain = '', $l_success = '', $admin = false, $s_display = true)
{
	global $user, $template, $auth, $phpEx, $phpbb_root_path, $config;
	global $request, $phpbb_container, $phpbb_dispatcher, $phpbb_log;

	$err = '';
	$form_name = 'login';
	$username = $autologin = false;

	// Make sure user->setup() has been called
	if (!$user->is_setup())
	{
		$user->setup();
	}

	/**
	 * This event allows an extension to modify the login process
	 *
	 * @event core.login_box_before
	 * @var string	redirect	Redirect string
	 * @var string	l_explain	Explain language string
	 * @var string	l_success	Success language string
	 * @var	bool	admin		Is admin?
	 * @var bool	s_display	Display full login form?
	 * @var string	err			Error string
	 * @since 3.1.9-RC1
	 */
	$vars = array('redirect', 'l_explain', 'l_success', 'admin', 's_display', 'err');
	extract($phpbb_dispatcher->trigger_event('core.login_box_before', compact($vars)));

	// Print out error if user tries to authenticate as an administrator without having the privileges...
	if ($admin && !$auth->acl_get('a_'))
	{
		// Not authd
		// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
		if ($user->data['is_registered'])
		{
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_ADMIN_AUTH_FAIL');
		}
		send_status_line(403, 'Forbidden');
		trigger_error('NO_AUTH_ADMIN');
	}

	if (empty($err) && ($request->is_set_post('login') || ($request->is_set('login') && $request->variable('login', '') == 'external')))
	{
		// Get credential
		if ($admin)
		{
			$credential = $request->variable('credential', '');

			if (strspn($credential, 'abcdef0123456789') !== strlen($credential) || strlen($credential) != 32)
			{
				if ($user->data['is_registered'])
				{
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_ADMIN_AUTH_FAIL');
				}
				send_status_line(403, 'Forbidden');
				trigger_error('NO_AUTH_ADMIN');
			}

			$password	= $request->untrimmed_variable('password_' . $credential, '', true);
		}
		else
		{
			$password	= $request->untrimmed_variable('password', '', true);
		}

		$username	= $request->variable('username', '', true);
		$autologin	= $request->is_set_post('autologin');
		$viewonline = (int) !$request->is_set_post('viewonline');
		$admin 		= ($admin) ? 1 : 0;
		$viewonline = ($admin) ? $user->data['session_viewonline'] : $viewonline;

		// Check if the supplied username is equal to the one stored within the database if re-authenticating
		if ($admin && utf8_clean_string($username) != utf8_clean_string($user->data['username']))
		{
			// We log the attempt to use a different username...
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_ADMIN_AUTH_FAIL');

			send_status_line(403, 'Forbidden');
			trigger_error('NO_AUTH_ADMIN_USER_DIFFER');
		}

		// Check form key
		if ($password && !defined('IN_CHECK_BAN') && !check_form_key($form_name))
		{
			$result = array(
				'status' => false,
				'error_msg' => 'FORM_INVALID',
			);
		}
		else
		{
			// If authentication is successful we redirect user to previous page
			$result = $auth->login($username, $password, $autologin, $viewonline, $admin);
		}

		// If admin authentication and login, we will log if it was a success or not...
		// We also break the operation on the first non-success login - it could be argued that the user already knows
		if ($admin)
		{
			if ($result['status'] == LOGIN_SUCCESS)
			{
				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_ADMIN_AUTH_SUCCESS');
			}
			else
			{
				// Only log the failed attempt if a real user tried to.
				// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
				if ($user->data['is_registered'])
				{
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_ADMIN_AUTH_FAIL');
				}
			}
		}

		// The result parameter is always an array, holding the relevant information...
		if ($result['status'] == LOGIN_SUCCESS)
		{
			$redirect = $request->variable('redirect', "{$phpbb_root_path}index.$phpEx");

			/**
			* This event allows an extension to modify the redirection when a user successfully logs in
			*
			* @event core.login_box_redirect
			* @var  string	redirect	Redirect string
			* @var	bool	admin		Is admin?
			* @var	array	result		Result from auth provider
			* @since 3.1.0-RC5
			* @changed 3.1.9-RC1 Removed undefined return variable
			* @changed 3.2.4-RC1 Added result
			*/
			$vars = array('redirect', 'admin', 'result');
			extract($phpbb_dispatcher->trigger_event('core.login_box_redirect', compact($vars)));

			// append/replace SID (may change during the session for AOL users)
			$redirect = reapply_sid($redirect);

			// Special case... the user is effectively banned, but we allow founders to login
			if (defined('IN_CHECK_BAN') && $result['user_row']['user_type'] != USER_FOUNDER)
			{
				return;
			}

			redirect($redirect);
		}

		// Something failed, determine what...
		if ($result['status'] == LOGIN_BREAK)
		{
			trigger_error($result['error_msg']);
		}

		// Special cases... determine
		switch ($result['status'])
		{
			case LOGIN_ERROR_PASSWORD_CONVERT:
				$err = sprintf(
					$user->lang[$result['error_msg']],
					($config['email_enable']) ? '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') . '">' : '',
					($config['email_enable']) ? '</a>' : '',
					'<a href="' . phpbb_get_board_contact_link($config, $phpbb_root_path, $phpEx) . '">',
					'</a>'
				);
			break;

			case LOGIN_ERROR_ATTEMPTS:

				$captcha = $phpbb_container->get('captcha.factory')->get_instance($config['captcha_plugin']);
				$captcha->init(CONFIRM_LOGIN);
				// $captcha->reset();

				$template->assign_vars(array(
					'CAPTCHA_TEMPLATE'			=> $captcha->get_template(),
				));
			// no break;

			// Username, password, etc...
			default:
				$err = $user->lang[$result['error_msg']];

				// Assign admin contact to some error messages
				if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
				{
					$err = sprintf($user->lang[$result['error_msg']], '<a href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contactadmin') . '">', '</a>');
				}

			break;
		}

		/**
		 * This event allows an extension to process when a user fails a login attempt
		 *
		 * @event core.login_box_failed
		 * @var array   result      Login result data
		 * @var string  username    User name used to login
		 * @var string  password    Password used to login
		 * @var string  err         Error message
		 * @since 3.1.3-RC1
		 */
		$vars = array('result', 'username', 'password', 'err');
		extract($phpbb_dispatcher->trigger_event('core.login_box_failed', compact($vars)));
	}

	// Assign credential for username/password pair
	$credential = ($admin) ? md5(unique_id()) : false;

	$s_hidden_fields = array(
		'sid'		=> $user->session_id,
	);

	if ($redirect)
	{
		$s_hidden_fields['redirect'] = $redirect;
	}

	if ($admin)
	{
		$s_hidden_fields['credential'] = $credential;
	}

	/* @var $provider_collection \phpbb\auth\provider_collection */
	$provider_collection = $phpbb_container->get('auth.provider_collection');
	$auth_provider = $provider_collection->get_provider();

	$auth_provider_data = $auth_provider->get_login_data();
	if ($auth_provider_data)
	{
		if (isset($auth_provider_data['VARS']))
		{
			$template->assign_vars($auth_provider_data['VARS']);
		}

		if (isset($auth_provider_data['BLOCK_VAR_NAME']))
		{
			foreach ($auth_provider_data['BLOCK_VARS'] as $block_vars)
			{
				$template->assign_block_vars($auth_provider_data['BLOCK_VAR_NAME'], $block_vars);
			}
		}

		$template->assign_vars(array(
			'PROVIDER_TEMPLATE_FILE' => $auth_provider_data['TEMPLATE_FILE'],
		));
	}

	$s_hidden_fields = build_hidden_fields($s_hidden_fields);

	/** @var \phpbb\controller\helper $controller_helper */
	$controller_helper = $phpbb_container->get('controller.helper');

	$login_box_template_data = array(
		'LOGIN_ERROR'		=> $err,
		'LOGIN_EXPLAIN'		=> $l_explain,

		'U_SEND_PASSWORD' 		=> ($config['email_enable'] && $config['allow_password_reset']) ? $controller_helper->route('phpbb_ucp_forgot_password_controller') : '',
		'U_RESEND_ACTIVATION'	=> ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=resend_act') : '',
		'U_TERMS_USE'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=terms'),
		'U_PRIVACY'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=privacy'),
		'UA_PRIVACY'			=> addslashes(append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=privacy')),

		'S_DISPLAY_FULL_LOGIN'	=> ($s_display) ? true : false,
		'S_HIDDEN_FIELDS' 		=> $s_hidden_fields,

		'S_ADMIN_AUTH'			=> $admin,
		'USERNAME'				=> ($admin) ? $user->data['username'] : '',

		'USERNAME_CREDENTIAL'	=> 'username',
		'PASSWORD_CREDENTIAL'	=> ($admin) ? 'password_' . $credential : 'password',
	);

	/**
	 * Event to add/modify login box template data
	 *
	 * @event core.login_box_modify_template_data
	 * @var	int		admin							Flag whether user is admin
	 * @var	string	username						User name
	 * @var	int		autologin						Flag whether autologin is enabled
	 * @var string	redirect						Redirect URL
	 * @var	array	login_box_template_data			Array with the login box template data
	 * @since 3.2.3-RC2
	 */
	$vars = array(
		'admin',
		'username',
		'autologin',
		'redirect',
		'login_box_template_data',
	);
	extract($phpbb_dispatcher->trigger_event('core.login_box_modify_template_data', compact($vars)));

	$template->assign_vars($login_box_template_data);

	page_header($user->lang['LOGIN']);

	$template->set_filenames(array(
		'body' => 'login_body.html')
	);
	make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

	page_footer();
}

/**
* Generate forum login box
*/
function login_forum_box($forum_data)
{
	global $db, $phpbb_container, $request, $template, $user, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

	$password = $request->variable('password', '', true);

	$sql = 'SELECT forum_id
		FROM ' . FORUMS_ACCESS_TABLE . '
		WHERE forum_id = ' . $forum_data['forum_id'] . '
			AND user_id = ' . $user->data['user_id'] . "
			AND session_id = '" . $db->sql_escape($user->session_id) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row)
	{
		return true;
	}

	if ($password)
	{
		// Remove expired authorised sessions
		$sql = 'SELECT f.session_id
			FROM ' . FORUMS_ACCESS_TABLE . ' f
			LEFT JOIN ' . SESSIONS_TABLE . ' s ON (f.session_id = s.session_id)
			WHERE s.session_id IS NULL';
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = (string) $row['session_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			// Remove expired sessions
			$sql = 'DELETE FROM ' . FORUMS_ACCESS_TABLE . '
				WHERE ' . $db->sql_in_set('session_id', $sql_in);
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);

		/* @var $passwords_manager \phpbb\passwords\manager */
		$passwords_manager = $phpbb_container->get('passwords.manager');

		if ($passwords_manager->check($password, $forum_data['forum_password']))
		{
			$sql_ary = array(
				'forum_id'		=> (int) $forum_data['forum_id'],
				'user_id'		=> (int) $user->data['user_id'],
				'session_id'	=> (string) $user->session_id,
			);

			$db->sql_query('INSERT INTO ' . FORUMS_ACCESS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

			return true;
		}

		$template->assign_var('LOGIN_ERROR', $user->lang['WRONG_PASSWORD']);
	}

	/**
	* Performing additional actions, load additional data on forum login
	*
	* @event core.login_forum_box
	* @var	array	forum_data		Array with forum data
	* @var	string	password		Password entered
	* @since 3.1.0-RC3
	*/
	$vars = array('forum_data', 'password');
	extract($phpbb_dispatcher->trigger_event('core.login_forum_box', compact($vars)));

	page_header($user->lang['LOGIN']);

	$template->assign_vars(array(
		'FORUM_NAME'			=> isset($forum_data['forum_name']) ? $forum_data['forum_name'] : '',
		'S_LOGIN_ACTION'		=> build_url(array('f')),
		'S_HIDDEN_FIELDS'		=> build_hidden_fields(array('f' => $forum_data['forum_id'])))
	);

	$template->set_filenames(array(
		'body' => 'login_forum.html')
	);

	make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"), $forum_data['forum_id']);

	page_footer();
}

// Little helpers

/**
* Little helper for the build_hidden_fields function
*/
function _build_hidden_fields($key, $value, $specialchar, $stripslashes)
{
	$hidden_fields = '';

	if (!is_array($value))
	{
		$value = ($stripslashes) ? stripslashes($value) : $value;
		$value = ($specialchar) ? htmlspecialchars($value, ENT_COMPAT, 'UTF-8') : $value;

		$hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
	}
	else
	{
		foreach ($value as $_key => $_value)
		{
			$_key = ($stripslashes) ? stripslashes($_key) : $_key;
			$_key = ($specialchar) ? htmlspecialchars($_key, ENT_COMPAT, 'UTF-8') : $_key;

			$hidden_fields .= _build_hidden_fields($key . '[' . $_key . ']', $_value, $specialchar, $stripslashes);
		}
	}

	return $hidden_fields;
}

/**
* Build simple hidden fields from array
*
* @param array $field_ary an array of values to build the hidden field from
* @param bool $specialchar if true, keys and values get specialchared
* @param bool $stripslashes if true, keys and values get stripslashed
*
* @return string the hidden fields
*/
function build_hidden_fields($field_ary, $specialchar = false, $stripslashes = false)
{
	$s_hidden_fields = '';

	foreach ($field_ary as $name => $vars)
	{
		$name = ($stripslashes) ? stripslashes($name) : $name;
		$name = ($specialchar) ? htmlspecialchars($name, ENT_COMPAT, 'UTF-8') : $name;

		$s_hidden_fields .= _build_hidden_fields($name, $vars, $specialchar, $stripslashes);
	}

	return $s_hidden_fields;
}

/**
* Parse cfg file
*/
function parse_cfg_file($filename, $lines = false)
{
	$parsed_items = array();

	if ($lines === false)
	{
		$lines = file($filename);
	}

	foreach ($lines as $line)
	{
		$line = trim($line);

		if (!$line || $line[0] == '#' || ($delim_pos = strpos($line, '=')) === false)
		{
			continue;
		}

		// Determine first occurrence, since in values the equal sign is allowed
		$key = htmlspecialchars(strtolower(trim(substr($line, 0, $delim_pos))), ENT_COMPAT);
		$value = trim(substr($line, $delim_pos + 1));

		if (in_array($value, array('off', 'false', '0')))
		{
			$value = false;
		}
		else if (in_array($value, array('on', 'true', '1')))
		{
			$value = true;
		}
		else if (!trim($value))
		{
			$value = '';
		}
		else if (($value[0] == "'" && $value[strlen($value) - 1] == "'") || ($value[0] == '"' && $value[strlen($value) - 1] == '"'))
		{
			$value = htmlspecialchars(substr($value, 1, strlen($value)-2), ENT_COMPAT);
		}
		else
		{
			$value = htmlspecialchars($value, ENT_COMPAT);
		}

		$parsed_items[$key] = $value;
	}

	if (isset($parsed_items['parent']) && isset($parsed_items['name']) && $parsed_items['parent'] == $parsed_items['name'])
	{
		unset($parsed_items['parent']);
	}

	return $parsed_items;
}

/**
* Return a nicely formatted backtrace.
*
* Turns the array returned by debug_backtrace() into HTML markup.
* Also filters out absolute paths to phpBB root.
*
* @return string	HTML markup
*/
function get_backtrace()
{
	$output = '<div style="font-family: monospace;">';
	$backtrace = debug_backtrace();

	// We skip the first one, because it only shows this file/function
	unset($backtrace[0]);

	foreach ($backtrace as $trace)
	{
		// Strip the current directory from path
		$trace['file'] = (empty($trace['file'])) ? '(not given by php)' : htmlspecialchars(phpbb_filter_root_path($trace['file']), ENT_COMPAT);
		$trace['line'] = (empty($trace['line'])) ? '(not given by php)' : $trace['line'];

		// Only show function arguments for include etc.
		// Other parameters may contain sensible information
		$argument = '';
		if (!empty($trace['args'][0]) && in_array($trace['function'], array('include', 'require', 'include_once', 'require_once')))
		{
			$argument = htmlspecialchars(phpbb_filter_root_path($trace['args'][0]), ENT_COMPAT);
		}

		$trace['class'] = (!isset($trace['class'])) ? '' : $trace['class'];
		$trace['type'] = (!isset($trace['type'])) ? '' : $trace['type'];

		$output .= '<br />';
		$output .= '<b>FILE:</b> ' . $trace['file'] . '<br />';
		$output .= '<b>LINE:</b> ' . ((!empty($trace['line'])) ? $trace['line'] : '') . '<br />';

		$output .= '<b>CALL:</b> ' . htmlspecialchars($trace['class'] . $trace['type'] . $trace['function'], ENT_COMPAT);
		$output .= '(' . (($argument !== '') ? "'$argument'" : '') . ')<br />';
	}
	$output .= '</div>';
	return $output;
}

/**
* This function returns a regular expression pattern for commonly used expressions
* Use with / as delimiter for email mode and # for url modes
* mode can be: email|bbcode_htm|url|url_inline|www_url|www_url_inline|relative_url|relative_url_inline|ipv4|ipv6
*/
function get_preg_expression($mode)
{
	switch ($mode)
	{
		case 'email':
			// Regex written by James Watts and Francisco Jose Martin Moreno
			// http://fightingforalostcause.net/misc/2006/compare-email-regex.php
			return '((?:[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*(?:[\w\!\#$\%\'\*\+\-\/\=\?\^\`{\|\}\~]|&amp;)+)@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,63})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)';
		break;

		case 'bbcode_htm':
			return array(
				'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
				'#<!\-\- l \-\-><a (?:class="[\w-]+" )?href="(.*?)(?:(&amp;|\?)sid=[0-9a-f]{32})?">.*?</a><!\-\- l \-\->#',
				'#<!\-\- ([mw]) \-\-><a (?:class="[\w-]+" )?href="http://(.*?)">\2</a><!\-\- \1 \-\->#',
				'#<!\-\- ([mw]) \-\-><a (?:class="[\w-]+" )?href="(.*?)">.*?</a><!\-\- \1 \-\->#',
				'#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
				'#<!\-\- .*? \-\->#s',
				'#<.*?>#s',
			);
		break;

		// Whoa these look impressive!
		// The code to generate the following two regular expressions which match valid IPv4/IPv6 addresses
		// can be found in the develop directory

		// @deprecated
		case 'ipv4':
			return '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
		break;

		// @deprecated
		case 'ipv6':
			return '#^(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){0,5}(?:[\dA-F]{1,4}(?::[\dA-F]{1,4})?|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:)|(?:::))$#i';
		break;

		case 'url':
			// generated with regex_idn.php file in the develop folder
			return "[a-z][a-z\d+\-.]*(?<!javascript):/{2}(?:(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'url_http':
			// generated with regex_idn.php file in the develop folder
			return "http[s]?:/{2}(?:(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'url_inline':
			// generated with regex_idn.php file in the develop folder
			return "[a-z][a-z\d+]*(?<!javascript):/{2}(?:(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'www_url':
			// generated with regex_idn.php file in the develop folder
			return "www\.(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'www_url_inline':
			// generated with regex_idn.php file in the develop folder
			return "www\.(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'relative_url':
			// generated with regex_idn.php file in the develop folder
			return "(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'()*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'relative_url_inline':
			// generated with regex_idn.php file in the develop folder
			return "(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[^\p{C}\p{Z}\p{S}\p{P}\p{Nl}\p{No}\p{Me}\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}\x{20D0}-\x{20FF}\x{1D100}-\x{1D1FF}\x{1D200}-\x{1D24F}\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}]*[\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}\x{00DF}\x{03C2}\x{200C}\x{200D}\pL0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'table_prefix':
			return '#^[a-zA-Z][a-zA-Z0-9_]*$#';
		break;

		// Matches the predecing dot
		case 'path_remove_dot_trailing_slash':
			return '#^(?:(\.)?)+(?:(.+)?)+(?:([\\/\\\])$)#';
		break;

		case 'semantic_version':
			// Regular expression to match semantic versions by http://rgxdb.com/
			return '/(?<=^[Vv]|^)(?:(?<major>(?:0|[1-9](?:(?:0|[1-9])+)*))[.](?<minor>(?:0|[1-9](?:(?:0|[1-9])+)*))[.](?<patch>(?:0|[1-9](?:(?:0|[1-9])+)*))(?:-(?<prerelease>(?:(?:(?:[A-Za-z]|-)(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)?|(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)(?:[A-Za-z]|-)(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)?)|(?:0|[1-9](?:(?:0|[1-9])+)*))(?:[.](?:(?:(?:[A-Za-z]|-)(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)?|(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)(?:[A-Za-z]|-)(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)?)|(?:0|[1-9](?:(?:0|[1-9])+)*)))*))?(?:[+](?<build>(?:(?:(?:[A-Za-z]|-)(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)?|(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)(?:[A-Za-z]|-)(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)?)|(?:(?:0|[1-9])+))(?:[.](?:(?:(?:[A-Za-z]|-)(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)?|(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)(?:[A-Za-z]|-)(?:(?:(?:0|[1-9])|(?:[A-Za-z]|-))+)?)|(?:(?:0|[1-9])+)))*))?)$/';
		break;
	}

	return '';
}

/**
* Generate regexp for naughty words censoring
* Depends on whether installed PHP version supports unicode properties
*
* @param string	$word			word template to be replaced
*
* @return string $preg_expr		regex to use with word censor
*/
function get_censor_preg_expression($word)
{
	// Unescape the asterisk to simplify further conversions
	$word = str_replace('\*', '*', preg_quote($word, '#'));

	// Replace asterisk(s) inside the pattern, at the start and at the end of it with regexes
	$word = preg_replace(array('#(?<=[\p{Nd}\p{L}_])\*+(?=[\p{Nd}\p{L}_])#iu', '#^\*+#', '#\*+$#'), array('([\x20]*?|[\p{Nd}\p{L}_-]*?)', '[\p{Nd}\p{L}_-]*?', '[\p{Nd}\p{L}_-]*?'), $word);

	// Generate the final substitution
	$preg_expr = '#(?<![\p{Nd}\p{L}_-])(' . $word . ')(?![\p{Nd}\p{L}_-])#iu';

	return $preg_expr;
}

/**
* Returns the first block of the specified IPv6 address and as many additional
* ones as specified in the length parameter.
* If length is zero, then an empty string is returned.
* If length is greater than 3 the complete IP will be returned
*/
function short_ipv6($ip, $length)
{
	if ($length < 1)
	{
		return '';
	}

	// Handle IPv4 embedded IPv6 addresses
	if (preg_match('/(?:\d{1,3}\.){3}\d{1,3}$/i', $ip))
	{
		$binary_ip = inet_pton($ip);
		$ip_v6 = $binary_ip ? inet_ntop($binary_ip) : $ip;
		$ip = $ip_v6 ?: $ip;
	}

	// extend IPv6 addresses
	$blocks = substr_count($ip, ':') + 1;
	if ($blocks < 9)
	{
		$ip = str_replace('::', ':' . str_repeat('0000:', 9 - $blocks), $ip);
	}
	if ($ip[0] == ':')
	{
		$ip = '0000' . $ip;
	}
	if ($length < 4)
	{
		$ip = implode(':', array_slice(explode(':', $ip), 0, 1 + $length));
	}

	return $ip;
}

/**
* Normalises an internet protocol address,
* also checks whether the specified address is valid.
*
* IPv4 addresses are returned 'as is'.
*
* IPv6 addresses are normalised according to
*	A Recommendation for IPv6 Address Text Representation
*	http://tools.ietf.org/html/draft-ietf-6man-text-addr-representation-07
*
* @param string $address	IP address
*
* @return mixed		false if specified address is not valid,
*					string otherwise
*/
function phpbb_ip_normalise(string $address)
{
	$ip_normalised = false;

	if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
	{
		$ip_normalised = $address;
	}
	else if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
	{
		$ip_normalised = inet_ntop(inet_pton($address));

		// If is ipv4
		if (stripos($ip_normalised, '::ffff:') === 0)
		{
			$ip_normalised = substr($ip_normalised, 7);
		}
	}

	return $ip_normalised;
}

// Handler, header and footer

/**
* Error and message handler, call with trigger_error if read
*/
function msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $cache, $db, $auth, $template, $config, $user, $request;
	global $phpbb_root_path, $msg_title, $msg_long_text, $phpbb_log;
	global $phpbb_container;

	// https://www.php.net/manual/en/language.operators.errorcontrol.php
	// error_reporting() return a different error code inside the error handler after php 8.0
	$suppresed = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE;
	if (PHP_VERSION_ID < 80000)
	{
		$suppresed = 0;
	}

	// Do not display notices if we suppress them via @
	if (error_reporting() == $suppresed && $errno != E_USER_ERROR && $errno != E_USER_WARNING && $errno != E_USER_NOTICE)
	{
		return;
	}

	// Message handler is stripping text. In case we need it, we are possible to define long text...
	if (isset($msg_long_text) && $msg_long_text && !$msg_text)
	{
		$msg_text = $msg_long_text;
	}

	switch ($errno)
	{
		case E_NOTICE:
		case E_WARNING:

			// Check the error reporting level and return if the error level does not match
			// If DEBUG is defined the default level is E_ALL
			if (($errno & ($phpbb_container != null && $phpbb_container->getParameter('debug.show_errors') ? E_ALL : error_reporting())) == 0)
			{
				return;
			}

			if (strpos($errfile, 'cache') === false && strpos($errfile, 'template.') === false)
			{
				$errfile = phpbb_filter_root_path($errfile);
				$msg_text = phpbb_filter_root_path($msg_text);
				$error_name = ($errno === E_WARNING) ? 'PHP Warning' : 'PHP Notice';
				echo '<b>[phpBB Debug] ' . $error_name . '</b>: in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $msg_text . '</b><br />' . "\n";

				// we are writing an image - the user won't see the debug, so let's place it in the log
				if (defined('IMAGE_OUTPUT') || defined('IN_CRON'))
				{
					$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'LOG_IMAGE_GENERATION_ERROR', false, array($errfile, $errline, $msg_text));
				}
				// echo '<br /><br />BACKTRACE<br />' . get_backtrace() . '<br />' . "\n";
			}

			return;

		break;

		case E_USER_ERROR:

			if (!empty($user) && $user->is_setup())
			{
				$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
				$msg_title = (!isset($msg_title)) ? $user->lang['GENERAL_ERROR'] : ((!empty($user->lang[$msg_title])) ? $user->lang[$msg_title] : $msg_title);

				$l_return_index = sprintf($user->lang['RETURN_INDEX'], '<a href="' . $phpbb_root_path . '">', '</a>');
				$l_notify = '';

				if (!empty($config['board_contact']))
				{
					$l_notify = '<p>' . sprintf($user->lang['NOTIFY_ADMIN_EMAIL'], $config['board_contact']) . '</p>';
				}
			}
			else
			{
				$msg_title = 'General Error';
				$l_return_index = '<a href="' . $phpbb_root_path . '">Return to index page</a>';
				$l_notify = '';

				if (!empty($config['board_contact']))
				{
					$l_notify = '<p>Please notify the board administrator or webmaster: <a href="mailto:' . $config['board_contact'] . '">' . $config['board_contact'] . '</a></p>';
				}
			}

			$log_text = $msg_text;
			$backtrace = get_backtrace();
			if ($backtrace)
			{
				$log_text .= '<br /><br />BACKTRACE<br />' . $backtrace;
			}

			if (defined('IN_INSTALL') || ($phpbb_container != null && $phpbb_container->getParameter('debug.show_errors')) || isset($auth) && $auth->acl_get('a_'))
			{
				$msg_text = $log_text;

				// If this is defined there already was some output
				// So let's not break it
				if (defined('IN_DB_UPDATE'))
				{
					echo '<div class="errorbox">' . $msg_text . '</div>';

					$db->sql_return_on_error(true);
					phpbb_end_update($cache, $config);
				}
			}

			if ((defined('IN_CRON') || defined('IMAGE_OUTPUT')) && isset($db))
			{
				// let's avoid loops
				$db->sql_return_on_error(true);
				$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'LOG_GENERAL_ERROR', false, array($msg_title, $log_text));
				$db->sql_return_on_error(false);
			}

			// Do not send 200 OK, but service unavailable on errors
			send_status_line(503, 'Service Unavailable');

			garbage_collection();

			// Try to not call the adm page data...

			echo '<!DOCTYPE html>';
			echo '<html dir="ltr">';
			echo '<head>';
			echo '<meta charset="utf-8">';
			echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
			echo '<title>' . $msg_title . '</title>';
			echo '<style type="text/css">' . "\n" . '/* <![CDATA[ */' . "\n";
			echo '* { margin: 0; padding: 0; } html { font-size: 100%; height: 100%; margin-bottom: 1px; background-color: #E4EDF0; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #536482; background: #E4EDF0; font-size: 62.5%; margin: 0; } ';
			echo 'a:link, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } ';
			echo '#wrap { padding: 0 20px 15px 20px; min-width: 615px; } #page-header { text-align: right; height: 40px; } #page-footer { clear: both; font-size: 1em; text-align: center; } ';
			echo '.panel { margin: 4px 0; background-color: #FFFFFF; border: solid 1px  #A9B8C2; } ';
			echo '#errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 10px; } #errorpage #content h1 { line-height: 1.2em; margin-bottom: 0; color: #DF075C; } ';
			echo '#errorpage #content div { margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #CCCCCC; padding-bottom: 5px; color: #333333; font: bold 1.2em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%; text-align: left; } ';
			echo "\n" . '/* ]]> */' . "\n";
			echo '</style>';
			echo '</head>';
			echo '<body id="errorpage">';
			echo '<div id="wrap">';
			echo '	<div id="page-header">';
			echo '		' . $l_return_index;
			echo '	</div>';
			echo '	<div id="acp">';
			echo '	<div class="panel">';
			echo '		<div id="content">';
			echo '			<h1>' . $msg_title . '</h1>';

			echo '			<div>' . $msg_text . '</div>';

			echo $l_notify;

			echo '		</div>';
			echo '	</div>';
			echo '	</div>';
			echo '	<div id="page-footer">';
			echo '		Powered by <a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited';
			echo '	</div>';
			echo '</div>';
			echo '</body>';
			echo '</html>';

			exit_handler();

			// On a fatal error (and E_USER_ERROR *is* fatal) we never want other scripts to continue and force an exit here.
			exit;
		break;

		case E_USER_WARNING:
		case E_USER_NOTICE:

			define('IN_ERROR_HANDLER', true);

			if (empty($user->data))
			{
				$user->session_begin();
			}

			// We re-init the auth array to get correct results on login/logout
			$auth->acl($user->data);

			if (!$user->is_setup())
			{
				$user->setup();
			}

			if ($msg_text == 'ERROR_NO_ATTACHMENT' || $msg_text == 'NO_FORUM' || $msg_text == 'NO_TOPIC' || $msg_text == 'NO_USER')
			{
				send_status_line(404, 'Not Found');
			}

			$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
			$msg_title = (!isset($msg_title)) ? $user->lang['INFORMATION'] : ((!empty($user->lang[$msg_title])) ? $user->lang[$msg_title] : $msg_title);

			if (!defined('HEADER_INC'))
			{
				if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
				{
					adm_page_header($msg_title);
				}
				else
				{
					page_header($msg_title);
				}
			}

			$template->set_filenames(array(
				'body' => 'message_body.html')
			);

			$template->assign_vars(array(
				'MESSAGE_TITLE'		=> $msg_title,
				'MESSAGE_TEXT'		=> $msg_text,
				'S_USER_WARNING'	=> ($errno == E_USER_WARNING) ? true : false,
				'S_USER_NOTICE'		=> ($errno == E_USER_NOTICE) ? true : false)
			);

			if ($request->is_ajax())
			{
				global $refresh_data;

				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE'		=> $msg_title,
					'MESSAGE_TEXT'		=> $msg_text,
					'S_USER_WARNING'	=> ($errno == E_USER_WARNING) ? true : false,
					'S_USER_NOTICE'		=> ($errno == E_USER_NOTICE) ? true : false,
					'REFRESH_DATA'		=> (!empty($refresh_data)) ? $refresh_data : null
				));
			}

			// We do not want the cron script to be called on error messages
			define('IN_CRON', true);

			if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
			{
				adm_page_footer();
			}
			else
			{
				page_footer();
			}

			exit_handler();
		break;

		// PHP4 compatibility
		case E_DEPRECATED:
			return true;
		break;
	}

	// If we notice an error not handled here we pass this back to PHP by returning false
	// This may not work for all php versions
	return false;
}

/**
* Removes absolute path to phpBB root directory from error messages
* and converts backslashes to forward slashes.
*
* @param string $errfile	Absolute file path
*							(e.g. /var/www/phpbb3/phpBB/includes/functions.php)
*							Please note that if $errfile is outside of the phpBB root,
*							the root path will not be found and can not be filtered.
* @return string			Relative file path
*							(e.g. /includes/functions.php)
*/
function phpbb_filter_root_path($errfile)
{
	global $phpbb_filesystem;

	static $root_path;

	if (empty($root_path))
	{
		if ($phpbb_filesystem)
		{
			$root_path = $phpbb_filesystem->realpath(__DIR__ . '/../');
		}
		else
		{
			$filesystem = new \phpbb\filesystem\filesystem();
			$root_path = $filesystem->realpath(__DIR__ . '/../');
		}
	}

	return str_replace(array($root_path, '\\'), array('[ROOT]', '/'), $errfile);
}

/**
* Queries the session table to get information about online guests
* @param int $item_id Limits the search to the item with this id
* @param string $item The name of the item which is stored in the session table as session_{$item}_id
* @return int The number of active distinct guest sessions
*/
function obtain_guest_count($item_id = 0, $item = 'forum')
{
	global $db, $config;

	if ($item_id)
	{
		$reading_sql = ' AND s.session_' . $item . '_id = ' . (int) $item_id;
	}
	else
	{
		$reading_sql = '';
	}
	$time = (time() - (intval($config['load_online_time']) * 60));

	// Get number of online guests

	if ($db->get_sql_layer() === 'sqlite3')
	{
		$sql = 'SELECT COUNT(session_ip) as num_guests
			FROM (
				SELECT DISTINCT s.session_ip
				FROM ' . SESSIONS_TABLE . ' s
				WHERE s.session_user_id = ' . ANONYMOUS . '
					AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
				$reading_sql .
			')';
	}
	else
	{
		$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests
			FROM ' . SESSIONS_TABLE . ' s
			WHERE s.session_user_id = ' . ANONYMOUS . '
				AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
			$reading_sql;
	}
	$result = $db->sql_query($sql);
	$guests_online = (int) $db->sql_fetchfield('num_guests');
	$db->sql_freeresult($result);

	return $guests_online;
}

/**
* Queries the session table to get information about online users
* @param int $item_id Limits the search to the item with this id
* @param string $item The name of the item which is stored in the session table as session_{$item}_id
* @return array An array containing the ids of online, hidden and visible users, as well as statistical info
*/
function obtain_users_online($item_id = 0, $item = 'forum')
{
	global $db, $config;

	$reading_sql = '';
	if ($item_id !== 0)
	{
		$reading_sql = ' AND s.session_' . $item . '_id = ' . (int) $item_id;
	}

	$online_users = array(
		'online_users'			=> array(),
		'hidden_users'			=> array(),
		'total_online'			=> 0,
		'visible_online'		=> 0,
		'hidden_online'			=> 0,
		'guests_online'			=> 0,
	);

	if ($config['load_online_guests'])
	{
		$online_users['guests_online'] = obtain_guest_count($item_id, $item);
	}

	// a little discrete magic to cache this for 30 seconds
	$time = (time() - (intval($config['load_online_time']) * 60));

	$sql = 'SELECT s.session_user_id, s.session_ip, s.session_viewonline
		FROM ' . SESSIONS_TABLE . ' s
		WHERE s.session_time >= ' . ($time - ((int) ($time % 30))) .
			$reading_sql .
		' AND s.session_user_id <> ' . ANONYMOUS;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		// Skip multiple sessions for one user
		if (!isset($online_users['online_users'][$row['session_user_id']]))
		{
			$online_users['online_users'][$row['session_user_id']] = (int) $row['session_user_id'];
			if ($row['session_viewonline'])
			{
				$online_users['visible_online']++;
			}
			else
			{
				$online_users['hidden_users'][$row['session_user_id']] = (int) $row['session_user_id'];
				$online_users['hidden_online']++;
			}
		}
	}
	$online_users['total_online'] = $online_users['guests_online'] + $online_users['visible_online'] + $online_users['hidden_online'];
	$db->sql_freeresult($result);

	return $online_users;
}

/**
* Uses the result of obtain_users_online to generate a localized, readable representation.
* @param mixed $online_users result of obtain_users_online - array with user_id lists for total, hidden and visible users, and statistics
* @param int $item_id Indicate that the data is limited to one item and not global
* @param string $item The name of the item which is stored in the session table as session_{$item}_id
* @return array An array containing the string for output to the template
*/
function obtain_users_online_string($online_users, $item_id = 0, $item = 'forum')
{
	global $config, $db, $user, $auth, $phpbb_dispatcher;

	$user_online_link = $rowset = array();
	// Need caps version of $item for language-strings
	$item_caps = strtoupper($item);

	if (count($online_users['online_users']))
	{
		$sql_ary = array(
			'SELECT'	=> 'u.username, u.username_clean, u.user_id, u.user_type, u.user_allow_viewonline, u.user_colour',
			'FROM'		=> array(
				USERS_TABLE	=> 'u',
			),
			'WHERE'		=> $db->sql_in_set('u.user_id', $online_users['online_users']),
			'ORDER_BY'	=> 'u.username_clean ASC',
		);

		/**
		* Modify SQL query to obtain online users data
		*
		* @event core.obtain_users_online_string_sql
		* @var	array	online_users	Array with online users data
		*								from obtain_users_online()
		* @var	int		item_id			Restrict online users to item id
		* @var	string	item			Restrict online users to a certain
		*								session item, e.g. forum for
		*								session_forum_id
		* @var	array	sql_ary			SQL query array to obtain users online data
		* @since 3.1.4-RC1
		* @changed 3.1.7-RC1			Change sql query into array and adjust var accordingly. Allows extension authors the ability to adjust the sql_ary.
		*/
		$vars = array('online_users', 'item_id', 'item', 'sql_ary');
		extract($phpbb_dispatcher->trigger_event('core.obtain_users_online_string_sql', compact($vars)));

		$result = $db->sql_query($db->sql_build_query('SELECT', $sql_ary));
		$rowset = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		foreach ($rowset as $row)
		{
			// User is logged in and therefore not a guest
			if ($row['user_id'] != ANONYMOUS)
			{
				if (isset($online_users['hidden_users'][$row['user_id']]))
				{
					$row['username'] = '<em>' . $row['username'] . '</em>';
				}

				if (!isset($online_users['hidden_users'][$row['user_id']]) || $auth->acl_get('u_viewonline') || $row['user_id'] === $user->data['user_id'])
				{
					$user_online_link[$row['user_id']] = get_username_string(($row['user_type'] <> USER_IGNORE) ? 'full' : 'no_profile', $row['user_id'], $row['username'], $row['user_colour']);
				}
			}
		}
	}

	/**
	* Modify online userlist data
	*
	* @event core.obtain_users_online_string_before_modify
	* @var	array	online_users		Array with online users data
	*									from obtain_users_online()
	* @var	int		item_id				Restrict online users to item id
	* @var	string	item				Restrict online users to a certain
	*									session item, e.g. forum for
	*									session_forum_id
	* @var	array	rowset				Array with online users data
	* @var	array	user_online_link	Array with online users items (usernames)
	* @since 3.1.10-RC1
	*/
	$vars = array(
		'online_users',
		'item_id',
		'item',
		'rowset',
		'user_online_link',
	);
	extract($phpbb_dispatcher->trigger_event('core.obtain_users_online_string_before_modify', compact($vars)));

	$online_userlist = implode(', ', $user_online_link);

	if (!$online_userlist)
	{
		$online_userlist = $user->lang['NO_ONLINE_USERS'];
	}

	if ($item_id === 0)
	{
		$online_userlist = $user->lang['REGISTERED_USERS'] . ' ' . $online_userlist;
	}
	else if ($config['load_online_guests'])
	{
		$online_userlist = $user->lang('BROWSING_' . $item_caps . '_GUESTS', $online_users['guests_online'], $online_userlist);
	}
	else
	{
		$online_userlist = sprintf($user->lang['BROWSING_' . $item_caps], $online_userlist);
	}
	// Build online listing
	$visible_online = $user->lang('REG_USERS_TOTAL', (int) $online_users['visible_online']);
	$hidden_online = $user->lang('HIDDEN_USERS_TOTAL', (int) $online_users['hidden_online']);

	if ($config['load_online_guests'])
	{
		$guests_online = $user->lang('GUEST_USERS_TOTAL', (int) $online_users['guests_online']);
		$l_online_users = $user->lang('ONLINE_USERS_TOTAL_GUESTS', (int) $online_users['total_online'], $visible_online, $hidden_online, $guests_online);
	}
	else
	{
		$l_online_users = $user->lang('ONLINE_USERS_TOTAL', (int) $online_users['total_online'], $visible_online, $hidden_online);
	}

	/**
	* Modify online userlist data
	*
	* @event core.obtain_users_online_string_modify
	* @var	array	online_users		Array with online users data
	*									from obtain_users_online()
	* @var	int		item_id				Restrict online users to item id
	* @var	string	item				Restrict online users to a certain
	*									session item, e.g. forum for
	*									session_forum_id
	* @var	array	rowset				Array with online users data
	* @var	array	user_online_link	Array with online users items (usernames)
	* @var	string	online_userlist		String containing users online list
	* @var	string	l_online_users		String with total online users count info
	* @since 3.1.4-RC1
	*/
	$vars = array(
		'online_users',
		'item_id',
		'item',
		'rowset',
		'user_online_link',
		'online_userlist',
		'l_online_users',
	);
	extract($phpbb_dispatcher->trigger_event('core.obtain_users_online_string_modify', compact($vars)));

	return array(
		'online_userlist'	=> $online_userlist,
		'l_online_users'	=> $l_online_users,
	);
}

/**
* Get option bitfield from custom data
*
* @param int	$bit		The bit/value to get
* @param int	$data		Current bitfield to check
* @return bool	Returns true if value of constant is set in bitfield, else false
*/
function phpbb_optionget($bit, $data)
{
	return ($data & 1 << (int) $bit) ? true : false;
}

/**
* Set option bitfield
*
* @param int	$bit		The bit/value to set/unset
* @param bool	$set		True if option should be set, false if option should be unset.
* @param int	$data		Current bitfield to change
*
* @return int	The new bitfield
*/
function phpbb_optionset($bit, $set, $data)
{
	if ($set && !($data & 1 << $bit))
	{
		$data += 1 << $bit;
	}
	else if (!$set && ($data & 1 << $bit))
	{
		$data -= 1 << $bit;
	}

	return $data;
}


/**
* Escapes and quotes a string for use as an HTML/XML attribute value.
*
* This is a port of Python xml.sax.saxutils quoteattr.
*
* The function will attempt to choose a quote character in such a way as to
* avoid escaping quotes in the string. If this is not possible the string will
* be wrapped in double quotes and double quotes will be escaped.
*
* @param string $data The string to be escaped
* @param array $entities Associative array of additional entities to be escaped
* @return string Escaped and quoted string
*/
function phpbb_quoteattr($data, $entities = null)
{
	$data = str_replace('&', '&amp;', $data);
	$data = str_replace('>', '&gt;', $data);
	$data = str_replace('<', '&lt;', $data);

	$data = str_replace("\n", '&#10;', $data);
	$data = str_replace("\r", '&#13;', $data);
	$data = str_replace("\t", '&#9;', $data);

	if (!empty($entities))
	{
		$data = str_replace(array_keys($entities), array_values($entities), $data);
	}

	if (strpos($data, '"') !== false)
	{
		if (strpos($data, "'") !== false)
		{
			$data = '"' . str_replace('"', '&quot;', $data) . '"';
		}
		else
		{
			$data = "'" . $data . "'";
		}
	}
	else
	{
		$data = '"' . $data . '"';
	}

	return $data;
}

/**
* Get user avatar
*
* @param array $user_row Row from the users table
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
* @param bool $lazy If true, will be lazy loaded (requires JS)
*
* @return string Avatar html
*/
function phpbb_get_user_avatar($user_row, $alt = 'USER_AVATAR', $ignore_config = false, $lazy = false)
{
	$row = \phpbb\avatar\manager::clean_row($user_row, 'user');
	return phpbb_get_avatar($row, $alt, $ignore_config, $lazy);
}

/**
* Get group avatar
*
* @param array $group_row Row from the groups table
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
* @param bool $lazy If true, will be lazy loaded (requires JS)
*
* @return string Avatar html
*/
function phpbb_get_group_avatar($group_row, $alt = 'GROUP_AVATAR', $ignore_config = false, $lazy = false)
{
	$row = \phpbb\avatar\manager::clean_row($group_row, 'group');
	return phpbb_get_avatar($row, $alt, $ignore_config, $lazy);
}

/**
* Get avatar
*
* @param array $row Row cleaned by \phpbb\avatar\manager::clean_row
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
* @param bool $lazy If true, will be lazy loaded (requires JS)
*
* @return string Avatar html
*/
function phpbb_get_avatar($row, $alt, $ignore_config = false, $lazy = false)
{
	global $user, $config;
	global $phpbb_container, $phpbb_dispatcher;

	if (!$config['allow_avatar'] && !$ignore_config)
	{
		return '';
	}

	$avatar_data = array(
		'src' => $row['avatar'],
		'width' => $row['avatar_width'],
		'height' => $row['avatar_height'],
	);

	/* @var $phpbb_avatar_manager \phpbb\avatar\manager */
	$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');
	$driver = $phpbb_avatar_manager->get_driver($row['avatar_type'], !$ignore_config);
	$html = '';

	if ($driver)
	{
		$html = $driver->get_custom_html($user, $row, $alt);
		$avatar_data = $driver->get_data($row);
	}
	else
	{
		$avatar_data['src'] = '';
	}

	if (empty($html) && !empty($avatar_data['src']))
	{
		if ($lazy)
		{
			// This path is sent with the base template paths in the assign_vars()
			// call below. We need to correct it in case we are accessing from a
			// controller because the web paths will be incorrect otherwise.
			$phpbb_path_helper = $phpbb_container->get('path_helper');
			$web_path = $phpbb_path_helper->get_web_root_path();

			$theme = "{$web_path}styles/" . rawurlencode($user->style['style_path']) . '/theme';

			$src = 'src="' . $theme . '/images/no_avatar.gif" data-src="' . $avatar_data['src'] . '"';
		}
		else
		{
			$src = 'src="' . $avatar_data['src'] . '"';
		}

		$html = '<img class="avatar" ' . $src . ' ' .
			($avatar_data['width'] ? ('width="' . $avatar_data['width'] . '" ') : '') .
			($avatar_data['height'] ? ('height="' . $avatar_data['height'] . '" ') : '') .
			'alt="' . ((!empty($user->lang[$alt])) ? $user->lang[$alt] : $alt) . '" />';
	}

	/**
	* Event to modify HTML <img> tag of avatar
	*
	* @event core.get_avatar_after
	* @var	array	row				Row cleaned by \phpbb\avatar\manager::clean_row
	* @var	string	alt				Optional language string for alt tag within image, can be a language key or text
	* @var	bool	ignore_config	Ignores the config-setting, to be still able to view the avatar in the UCP
	* @var	array	avatar_data		The HTML attributes for avatar <img> tag
	* @var	string	html			The HTML <img> tag of generated avatar
	* @since 3.1.6-RC1
	*/
	$vars = array('row', 'alt', 'ignore_config', 'avatar_data', 'html');
	extract($phpbb_dispatcher->trigger_event('core.get_avatar_after', compact($vars)));

	return $html;
}

/**
* Generate page header
*/
function page_header($page_title = '', $display_online_list = false, $item_id = 0, $item = 'forum', $send_headers = true)
{
	global $db, $config, $template, $SID, $_SID, $_EXTRA_URL, $user, $auth, $phpEx, $phpbb_root_path;
	global $phpbb_dispatcher, $request, $phpbb_container, $phpbb_admin_path;

	if (defined('HEADER_INC'))
	{
		return;
	}

	define('HEADER_INC', true);

	// A listener can set this variable to `true` when it overrides this function
	$page_header_override = false;

	/**
	* Execute code and/or overwrite page_header()
	*
	* @event core.page_header
	* @var	string	page_title			Page title
	* @var	bool	display_online_list		Do we display online users list
	* @var	string	item				Restrict online users to a certain
	*									session item, e.g. forum for
	*									session_forum_id
	* @var	int		item_id				Restrict online users to item id
	* @var	bool	page_header_override	Shall we return instead of running
	*										the rest of page_header()
	* @since 3.1.0-a1
	*/
	$vars = array('page_title', 'display_online_list', 'item_id', 'item', 'page_header_override');
	extract($phpbb_dispatcher->trigger_event('core.page_header', compact($vars)));

	if ($page_header_override)
	{
		return;
	}

	// gzip_compression
	if ($config['gzip_compress'])
	{
		// to avoid partially compressed output resulting in blank pages in
		// the browser or error messages, compression is disabled in a few cases:
		//
		// 1) if headers have already been sent, this indicates plaintext output
		//    has been started so further content must not be compressed
		// 2) the length of the current output buffer is non-zero. This means
		//    there is already some uncompressed content in this output buffer
		//    so further output must not be compressed
		// 3) if more than one level of output buffering is used because we
		//    cannot test all output buffer level content lengths. One level
		//    could be caused by php.ini output_buffering. Anything
		//    beyond that is manual, so the code wrapping phpBB in output buffering
		//    can easily compress the output itself.
		//
		if (@extension_loaded('zlib') && !headers_sent() && ob_get_level() <= 1 && ob_get_length() == 0)
		{
			ob_start('ob_gzhandler');
		}
	}

	$user->update_session_infos();

	// Generate logged in/logged out status
	if ($user->data['user_id'] != ANONYMOUS)
	{
		$u_login_logout = append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=logout', true, $user->session_id);
		$l_login_logout = $user->lang['LOGOUT'];
	}
	else
	{
		$redirect = $request->variable('redirect', rawurlencode($user->page['page']));
		$u_login_logout = append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login&amp;redirect=' . $redirect);
		$l_login_logout = $user->lang['LOGIN'];
	}

	// Last visit date/time
	$s_last_visit = ($user->data['user_id'] != ANONYMOUS) ? $user->format_date($user->data['session_last_visit']) : '';

	// Get users online list ... if required
	$l_online_users = $online_userlist = $l_online_record = $l_online_time = '';

	if ($config['load_online'] && $config['load_online_time'] && $display_online_list)
	{
		/**
		* Load online data:
		* For obtaining another session column use $item and $item_id in the function-parameter, whereby the column is session_{$item}_id.
		*/
		$item_id = max($item_id, 0);

		$online_users = obtain_users_online($item_id, $item);
		$user_online_strings = obtain_users_online_string($online_users, $item_id, $item);

		$l_online_users = $user_online_strings['l_online_users'];
		$online_userlist = $user_online_strings['online_userlist'];
		$total_online_users = $online_users['total_online'];

		if ($total_online_users > $config['record_online_users'])
		{
			$config->set('record_online_users', $total_online_users, false);
			$config->set('record_online_date', time(), false);
		}

		$l_online_record = $user->lang('RECORD_ONLINE_USERS', (int) $config['record_online_users'], $user->format_date($config['record_online_date'], false, true));

		$l_online_time = $user->lang('VIEW_ONLINE_TIMES', (int) $config['load_online_time']);
	}

	$s_privmsg_new = false;

	// Check for new private messages if user is logged in
	if (!empty($user->data['is_registered']))
	{
		if ($user->data['user_new_privmsg'])
		{
			if (!$user->data['user_last_privmsg'] || $user->data['user_last_privmsg'] > $user->data['session_last_visit'])
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_last_privmsg = ' . $user->data['session_last_visit'] . '
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);

				$s_privmsg_new = true;
			}
			else
			{
				$s_privmsg_new = false;
			}
		}
		else
		{
			$s_privmsg_new = false;
		}
	}

	// Negative forum and topic IDs are not allowed
	$forum_id = max(0, $request->variable('f', 0));
	$topic_id = max(0, $request->variable('t', 0));

	$s_feed_news = false;

	// Get option for news
	if ($config['feed_enable'])
	{
		$sql = 'SELECT forum_id
			FROM ' . FORUMS_TABLE . '
			WHERE ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_NEWS, '<> 0');
		$result = $db->sql_query_limit($sql, 1, 0, 600);
		$s_feed_news = (int) $db->sql_fetchfield('forum_id');
		$db->sql_freeresult($result);
	}

	// This path is sent with the base template paths in the assign_vars()
	// call below. We need to correct it in case we are accessing from a
	// controller because the web paths will be incorrect otherwise.
	/* @var $phpbb_path_helper \phpbb\path_helper */
	$phpbb_path_helper = $phpbb_container->get('path_helper');
	$web_path = $phpbb_path_helper->get_web_root_path();

	// Send a proper content-language to the output
	$user_lang = $user->lang['USER_LANG'];
	if (strpos($user_lang, '-x-') !== false)
	{
		$user_lang = substr($user_lang, 0, strpos($user_lang, '-x-'));
	}

	$s_search_hidden_fields = array();
	if ($_SID)
	{
		$s_search_hidden_fields['sid'] = $_SID;
	}

	if (!empty($_EXTRA_URL))
	{
		foreach ($_EXTRA_URL as $url_param)
		{
			$url_param = explode('=', $url_param, 2);
			$s_search_hidden_fields[$url_param[0]] = $url_param[1];
		}
	}

	$dt = $user->create_datetime();
	$timezone_offset = $user->lang(array('timezones', 'UTC_OFFSET'), phpbb_format_timezone_offset($dt->getOffset()));
	$timezone_name = $user->timezone->getName();
	if (isset($user->lang['timezones'][$timezone_name]))
	{
		$timezone_name = $user->lang['timezones'][$timezone_name];
	}

	// Output the notifications
	$notifications = false;
	if ($config['load_notifications'] && $config['allow_board_notifications'] && $user->data['user_id'] != ANONYMOUS && $user->data['user_type'] != USER_IGNORE)
	{
		/* @var $phpbb_notifications \phpbb\notification\manager */
		$phpbb_notifications = $phpbb_container->get('notification_manager');

		$notifications = $phpbb_notifications->load_notifications('notification.method.board', array(
			'all_unread'	=> true,
			'limit'			=> 5,
		));

		foreach ($notifications['notifications'] as $notification)
		{
			$template->assign_block_vars('notifications', $notification->prepare_for_display());
		}
	}

	/** @var \phpbb\controller\helper $controller_helper */
	$controller_helper = $phpbb_container->get('controller.helper');
	$notification_mark_hash = generate_link_hash('mark_all_notifications_read');

	$s_login_redirect = build_hidden_fields(array('redirect' => $phpbb_path_helper->remove_web_root_path(build_url())));

	// Add form token for login box, in case page is presenting a login form.
	add_form_key('login', '_LOGIN');

	/**
	 * Workaround for missing template variable in pre phpBB 3.2.6 styles.
	 * @deprecated 3.2.7 (To be removed: 4.0.0-a1)
	 */
	$form_token_login = $template->retrieve_var('S_FORM_TOKEN_LOGIN');
	if (!empty($form_token_login))
	{
		$s_login_redirect .= $form_token_login;
		// Remove S_FORM_TOKEN_LOGIN as it's already appended to S_LOGIN_REDIRECT
		$template->assign_var('S_FORM_TOKEN_LOGIN', '');
	}

	// The following assigns all _common_ variables that may be used at any point in a template.
	$template->assign_vars(array(
		'SITENAME'						=> $config['sitename'],
		'SITE_DESCRIPTION'				=> $config['site_desc'],
		'PAGE_TITLE'					=> $page_title,
		'SCRIPT_NAME'					=> str_replace('.' . $phpEx, '', $user->page['page_name']),
		'LAST_VISIT_DATE'				=> sprintf($user->lang['YOU_LAST_VISIT'], $s_last_visit),
		'LAST_VISIT_YOU'				=> $s_last_visit,
		'CURRENT_TIME'					=> sprintf($user->lang['CURRENT_TIME'], $user->format_date(time(), false, true)),
		'TOTAL_USERS_ONLINE'			=> $l_online_users,
		'LOGGED_IN_USER_LIST'			=> $online_userlist,
		'RECORD_USERS'					=> $l_online_record,

		'PRIVATE_MESSAGE_COUNT'			=> (!empty($user->data['user_unread_privmsg'])) ? $user->data['user_unread_privmsg'] : 0,
		'CURRENT_USER_AVATAR'			=> phpbb_get_user_avatar($user->data),
		'CURRENT_USERNAME_SIMPLE'		=> get_username_string('no_profile', $user->data['user_id'], $user->data['username'], $user->data['user_colour']),
		'CURRENT_USERNAME_FULL'			=> get_username_string('full', $user->data['user_id'], $user->data['username'], $user->data['user_colour']),
		'UNREAD_NOTIFICATIONS_COUNT'	=> ($notifications !== false) ? $notifications['unread_count'] : '',
		'NOTIFICATIONS_COUNT'			=> ($notifications !== false) ? $notifications['unread_count'] : '',
		'U_VIEW_ALL_NOTIFICATIONS'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=ucp_notifications'),
		'U_MARK_ALL_NOTIFICATIONS'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=ucp_notifications&amp;mode=notification_list&amp;mark=all&amp;token=' . $notification_mark_hash),
		'U_NOTIFICATION_SETTINGS'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=ucp_notifications&amp;mode=notification_options'),
		'S_NOTIFICATIONS_DISPLAY'		=> $config['load_notifications'] && $config['allow_board_notifications'],

		'S_USER_NEW_PRIVMSG'			=> $user->data['user_new_privmsg'],
		'S_USER_UNREAD_PRIVMSG'			=> $user->data['user_unread_privmsg'],
		'S_USER_NEW'					=> $user->data['user_new'],

		'SID'				=> $SID,
		'_SID'				=> $_SID,
		'SESSION_ID'		=> $user->session_id,
		'ROOT_PATH'			=> $web_path,
		'BOARD_URL'			=> generate_board_url() . '/',

		'L_LOGIN_LOGOUT'	=> $l_login_logout,
		'L_INDEX'			=> ($config['board_index_text'] !== '') ? $config['board_index_text'] : $user->lang['FORUM_INDEX'],
		'L_SITE_HOME'		=> ($config['site_home_text'] !== '') ? $config['site_home_text'] : $user->lang['HOME'],
		'L_ONLINE_EXPLAIN'	=> $l_online_time,

		'U_PRIVATEMSGS'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;folder=inbox'),
		'U_RETURN_INBOX'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;folder=inbox'),
		'U_MEMBERLIST'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx"),
		'U_VIEWONLINE'			=> ($auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel')) ? append_sid("{$phpbb_root_path}viewonline.$phpEx") : '',
		'U_LOGIN_LOGOUT'		=> $u_login_logout,
		'U_INDEX'				=> append_sid("{$phpbb_root_path}index.$phpEx"),
		'U_SEARCH'				=> append_sid("{$phpbb_root_path}search.$phpEx"),
		'U_SITE_HOME'			=> $config['site_home_url'],
		'U_REGISTER'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'),
		'U_PROFILE'				=> append_sid("{$phpbb_root_path}ucp.$phpEx"),
		'U_USER_PROFILE'		=> get_username_string('profile', $user->data['user_id'], $user->data['username'], $user->data['user_colour']),
		'U_MODCP'				=> append_sid("{$phpbb_root_path}mcp.$phpEx", false, true, $user->session_id),
		'U_FAQ'					=> $controller_helper->route('phpbb_help_faq_controller'),
		'U_SEARCH_SELF'			=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=egosearch'),
		'U_SEARCH_NEW'			=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=newposts'),
		'U_SEARCH_UNANSWERED'	=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=unanswered'),
		'U_SEARCH_UNREAD'		=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=unreadposts'),
		'U_SEARCH_ACTIVE_TOPICS'=> append_sid("{$phpbb_root_path}search.$phpEx", 'search_id=active_topics'),
		'U_DELETE_COOKIES'		=> $controller_helper->route('phpbb_ucp_delete_cookies_controller'),
		'U_CONTACT_US'			=> ($config['contact_admin_form_enable'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contactadmin') : '',
		'U_TEAM'				=> (!$auth->acl_get('u_viewprofile')) ? '' : append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=team'),
		'U_TERMS_USE'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=terms'),
		'U_PRIVACY'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=privacy'),
		'UA_PRIVACY'			=> addslashes(append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=privacy')),
		'U_RESTORE_PERMISSIONS'	=> ($user->data['user_perm_from'] && $auth->acl_get('a_switchperm')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=restore_perm') : '',
		'U_FEED'				=> $controller_helper->route('phpbb_feed_index'),

		'S_USER_LOGGED_IN'		=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
		'S_AUTOLOGIN_ENABLED'	=> ($config['allow_autologin']) ? true : false,
		'S_BOARD_DISABLED'		=> ($config['board_disable']) ? true : false,
		'S_REGISTERED_USER'		=> (!empty($user->data['is_registered'])) ? true : false,
		'S_IS_BOT'				=> (!empty($user->data['is_bot'])) ? true : false,
		'S_USER_LANG'			=> $user_lang,
		'S_USER_BROWSER'		=> (isset($user->data['session_browser'])) ? $user->data['session_browser'] : $user->lang['UNKNOWN_BROWSER'],
		'S_USERNAME'			=> $user->data['username'],
		'S_CONTENT_DIRECTION'	=> $user->lang['DIRECTION'],
		'S_CONTENT_FLOW_BEGIN'	=> ($user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
		'S_CONTENT_FLOW_END'	=> ($user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
		'S_CONTENT_ENCODING'	=> 'UTF-8',
		'S_TIMEZONE'			=> sprintf($user->lang['ALL_TIMES'], $timezone_offset, $timezone_name),
		'S_DISPLAY_ONLINE_LIST'	=> ($l_online_time) ? 1 : 0,
		'S_DISPLAY_SEARCH'		=> (!$config['load_search']) ? 0 : (isset($auth) ? ($auth->acl_get('u_search') && $auth->acl_getf_global('f_search')) : 1),
		'S_DISPLAY_PM'			=> ($config['allow_privmsg'] && !empty($user->data['is_registered']) && ($auth->acl_get('u_readpm') || $auth->acl_get('u_sendpm'))) ? true : false,
		'S_DISPLAY_MEMBERLIST'	=> (isset($auth)) ? $auth->acl_get('u_viewprofile') : 0,
		'S_NEW_PM'				=> ($s_privmsg_new) ? 1 : 0,
		'S_REGISTER_ENABLED'	=> ($config['require_activation'] != USER_ACTIVATION_DISABLE) ? true : false,
		'S_FORUM_ID'			=> $forum_id,
		'S_TOPIC_ID'			=> $topic_id,

		'S_LOGIN_ACTION'		=> ((!defined('ADMIN_START')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') : append_sid("{$phpbb_admin_path}index.$phpEx", false, true, $user->session_id)),
		'S_LOGIN_REDIRECT'		=> $s_login_redirect,

		'S_ENABLE_FEEDS'			=> ($config['feed_enable']) ? true : false,
		'S_ENABLE_FEEDS_OVERALL'	=> ($config['feed_overall']) ? true : false,
		'S_ENABLE_FEEDS_FORUMS'		=> ($config['feed_overall_forums']) ? true : false,
		'S_ENABLE_FEEDS_TOPICS'		=> ($config['feed_topics_new']) ? true : false,
		'S_ENABLE_FEEDS_TOPICS_ACTIVE'	=> ($config['feed_topics_active']) ? true : false,
		'S_ENABLE_FEEDS_NEWS'		=> ($s_feed_news) ? true : false,

		'S_LOAD_UNREADS'			=> (bool) $config['load_unreads_search'] && ($config['load_anon_lastread'] || !empty($user->data['is_registered'])),

		'S_SEARCH_HIDDEN_FIELDS'	=> build_hidden_fields($s_search_hidden_fields),

		'T_ASSETS_VERSION'		=> $config['assets_version'],
		'T_ASSETS_PATH'			=> "{$web_path}assets",
		'T_THEME_PATH'			=> "{$web_path}styles/" . rawurlencode($user->style['style_path']) . '/theme',
		'T_TEMPLATE_PATH'		=> "{$web_path}styles/" . rawurlencode($user->style['style_path']) . '/template',
		'T_SUPER_TEMPLATE_PATH'	=> "{$web_path}styles/" . rawurlencode($user->style['style_path']) . '/template',
		'T_IMAGES_PATH'			=> "{$web_path}images/",
		'T_SMILIES_PATH'		=> "{$web_path}{$config['smilies_path']}/",
		'T_AVATAR_PATH'			=> "{$web_path}{$config['avatar_path']}/",
		'T_AVATAR_GALLERY_PATH'	=> "{$web_path}{$config['avatar_gallery_path']}/",
		'T_ICONS_PATH'			=> "{$web_path}{$config['icons_path']}/",
		'T_RANKS_PATH'			=> "{$web_path}{$config['ranks_path']}/",
		'T_UPLOAD_PATH'			=> "{$web_path}{$config['upload_path']}/",
		'T_STYLESHEET_LINK'		=> "{$web_path}styles/" . rawurlencode($user->style['style_path']) . '/theme/stylesheet.css?assets_version=' . $config['assets_version'],
		'T_STYLESHEET_LANG_LINK'=> "{$web_path}styles/" . rawurlencode($user->style['style_path']) . '/theme/' . $user->lang_name . '/stylesheet.css?assets_version=' . $config['assets_version'],

		'T_FONT_AWESOME_LINK'	=> !empty($config['allow_cdn']) && !empty($config['load_font_awesome_url']) ? $config['load_font_awesome_url'] : "{$web_path}assets/css/font-awesome.min.css?assets_version=" . $config['assets_version'],

		'T_JQUERY_LINK'			=> !empty($config['allow_cdn']) && !empty($config['load_jquery_url']) ? $config['load_jquery_url'] : "{$web_path}assets/javascript/jquery-3.7.1.min.js?assets_version=" . $config['assets_version'],
		'S_ALLOW_CDN'			=> !empty($config['allow_cdn']),
		'S_COOKIE_NOTICE'		=> !empty($config['cookie_notice']),

		'T_THEME_NAME'			=> rawurlencode($user->style['style_path']),
		'T_THEME_LANG_NAME'		=> $user->lang_name,
		'T_TEMPLATE_NAME'		=> $user->style['style_path'],
		'T_SUPER_TEMPLATE_NAME'	=> rawurlencode((isset($user->style['style_parent_tree']) && $user->style['style_parent_tree']) ? $user->style['style_parent_tree'] : $user->style['style_path']),
		'T_IMAGES'				=> 'images',
		'T_SMILIES'				=> $config['smilies_path'],
		'T_AVATAR'				=> $config['avatar_path'],
		'T_AVATAR_GALLERY'		=> $config['avatar_gallery_path'],
		'T_ICONS'				=> $config['icons_path'],
		'T_RANKS'				=> $config['ranks_path'],
		'T_UPLOAD'				=> $config['upload_path'],

		'SITE_LOGO_IMG'			=> $user->img('site_logo'),
	));

	$http_headers = array();

	if ($send_headers)
	{
		// An array of http headers that phpBB will set. The following event may override these.
		$http_headers += array(
			// application/xhtml+xml not used because of IE
			'Content-type' => 'text/html; charset=UTF-8',
			'Cache-Control' => 'private, no-cache="set-cookie"',
			'Expires' => gmdate('D, d M Y H:i:s', time()) . ' GMT',
			'Referrer-Policy' => 'strict-origin-when-cross-origin',
		);
		if (!empty($user->data['is_bot']))
		{
			// Let reverse proxies know we detected a bot.
			$http_headers['X-PHPBB-IS-BOT'] = 'yes';
		}
	}

	/**
	* Execute code and/or overwrite _common_ template variables after they have been assigned.
	*
	* @event core.page_header_after
	* @var	string	page_title			Page title
	* @var	bool	display_online_list		Do we display online users list
	* @var	string	item				Restrict online users to a certain
	*									session item, e.g. forum for
	*									session_forum_id
	* @var	int		item_id				Restrict online users to item id
	* @var	array		http_headers			HTTP headers that should be set by phpbb
	*
	* @since 3.1.0-b3
	*/
	$vars = array('page_title', 'display_online_list', 'item_id', 'item', 'http_headers');
	extract($phpbb_dispatcher->trigger_event('core.page_header_after', compact($vars)));

	foreach ($http_headers as $hname => $hval)
	{
		header((string) $hname . ': ' . (string) $hval);
	}

	return;
}

/**
* Check and display the SQL report if requested.
*
* @param \phpbb\request\request_interface		$request	Request object
* @param \phpbb\auth\auth						$auth		Auth object
* @param \phpbb\db\driver\driver_interface		$db			Database connection
 *
 * @deprecated 3.3.1 (To be removed: 4.0.0-a1); use controller helper's display_sql_report()
*/
function phpbb_check_and_display_sql_report(\phpbb\request\request_interface $request, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db)
{
	global $phpbb_container;

	/** @var \phpbb\controller\helper $controller_helper */
	$controller_helper = $phpbb_container->get('controller.helper');

	$controller_helper->display_sql_report();
}

/**
* Generate the debug output string
*
* @param \phpbb\db\driver\driver_interface	$db			Database connection
* @param \phpbb\config\config				$config		Config object
* @param \phpbb\auth\auth					$auth		Auth object
* @param \phpbb\user						$user		User object
* @param \phpbb\event\dispatcher_interface	$phpbb_dispatcher	Event dispatcher
* @return string
*/
function phpbb_generate_debug_output(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\user $user, \phpbb\event\dispatcher_interface $phpbb_dispatcher)
{
	global $phpbb_container;

	$debug_info = array();

	// Output page creation time
	if ($phpbb_container->getParameter('debug.load_time'))
	{
		if (isset($GLOBALS['starttime']))
		{
			$totaltime = microtime(true) - $GLOBALS['starttime'];
			$debug_info[] = sprintf('<span title="SQL time: %.3fs / PHP time: %.3fs">Time: %.3fs</span>', $db->get_sql_time(), ($totaltime - $db->get_sql_time()), $totaltime);
		}
	}

	if ($phpbb_container->getParameter('debug.memory'))
	{
		$memory_usage = memory_get_peak_usage();
		if ($memory_usage)
		{
			$memory_usage = get_formatted_filesize($memory_usage);

			$debug_info[] = 'Peak Memory Usage: ' . $memory_usage;
		}

		$debug_info[] = 'GZIP: ' . (($config['gzip_compress'] && @extension_loaded('zlib')) ? 'On' : 'Off');

		if ($user->load)
		{
			$debug_info[] = 'Load: ' . $user->load;
		}
	}

	if ($phpbb_container->getParameter('debug.sql_explain'))
	{
		$debug_info[] = sprintf('<span title="Cached: %d">Queries: %d</span>', $db->sql_num_queries(true), $db->sql_num_queries());

		if ($auth->acl_get('a_'))
		{
			$debug_info[] = '<a href="' . build_url() . '&amp;explain=1">SQL Explain</a>';
		}
	}

	/**
	* Modify debug output information
	*
	* @event core.phpbb_generate_debug_output
	* @var	array	debug_info		Array of strings with debug information
	*
	* @since 3.1.0-RC3
	*/
	$vars = array('debug_info');
	extract($phpbb_dispatcher->trigger_event('core.phpbb_generate_debug_output', compact($vars)));

	return implode(' | ', $debug_info);
}

/**
* Generate page footer
*
* @param bool $run_cron Whether or not to run the cron
* @param bool $display_template Whether or not to display the template
* @param bool $exit_handler Whether or not to run the exit_handler()
*/
function page_footer($run_cron = true, $display_template = true, $exit_handler = true)
{
	global $phpbb_dispatcher, $phpbb_container, $template;

	// A listener can set this variable to `true` when it overrides this function
	$page_footer_override = false;

	/**
	* Execute code and/or overwrite page_footer()
	*
	* @event core.page_footer
	* @var	bool	run_cron			Shall we run cron tasks
	* @var	bool	page_footer_override	Shall we return instead of running
	*										the rest of page_footer()
	* @since 3.1.0-a1
	*/
	$vars = array('run_cron', 'page_footer_override');
	extract($phpbb_dispatcher->trigger_event('core.page_footer', compact($vars)));

	if ($page_footer_override)
	{
		return;
	}

	/** @var \phpbb\controller\helper $controller_helper */
	$controller_helper = $phpbb_container->get('controller.helper');

	$controller_helper->display_footer($run_cron);

	/**
	* Execute code and/or modify output before displaying the template.
	*
	* @event core.page_footer_after
	* @var	bool display_template	Whether or not to display the template
	* @var	bool exit_handler		Whether or not to run the exit_handler()
	*
	* @since 3.1.0-RC5
	*/
	$vars = array('display_template', 'exit_handler');
	extract($phpbb_dispatcher->trigger_event('core.page_footer_after', compact($vars)));

	if ($display_template)
	{
		$template->display('body');
	}

	garbage_collection();

	if ($exit_handler)
	{
		exit_handler();
	}
}

/**
* Closing the cache object and the database
* Cool function name, eh? We might want to add operations to it later
*/
function garbage_collection()
{
	global $cache, $db;
	global $phpbb_dispatcher;

	if (!empty($phpbb_dispatcher))
	{
		/**
		* Unload some objects, to free some memory, before we finish our task
		*
		* @event core.garbage_collection
		* @since 3.1.0-a1
		*/
		$phpbb_dispatcher->dispatch('core.garbage_collection');
	}

	// Unload cache, must be done before the DB connection if closed
	if (!empty($cache))
	{
		$cache->unload();
	}

	// Close our DB connection.
	if (!empty($db))
	{
		$db->sql_close();
	}
}

/**
* Handler for exit calls in phpBB.
* This function supports hooks.
*
* Note: This function is called after the template has been outputted.
*/
function exit_handler()
{
	global $phpbb_hook;

	if (!empty($phpbb_hook) && $phpbb_hook->call_hook(__FUNCTION__))
	{
		if ($phpbb_hook->hook_return(__FUNCTION__))
		{
			return $phpbb_hook->hook_return_result(__FUNCTION__);
		}
	}

	// As a pre-caution... some setups display a blank page if the flush() is not there.
	(ob_get_level() > 0) ? @ob_flush() : @flush();

	exit;
}

/**
* Handler for init calls in phpBB. This function is called in \phpbb\user::setup();
* This function supports hooks.
*/
function phpbb_user_session_handler()
{
	global $phpbb_hook;

	if (!empty($phpbb_hook) && $phpbb_hook->call_hook(__FUNCTION__))
	{
		if ($phpbb_hook->hook_return(__FUNCTION__))
		{
			return $phpbb_hook->hook_return_result(__FUNCTION__);
		}
	}

	return;
}

/**
* Casts a numeric string $input to an appropriate numeric type (i.e. integer or float)
*
* @param string $input		A numeric string.
*
* @return int|float			Integer $input if $input fits integer,
*							float $input otherwise.
*/
function phpbb_to_numeric($input)
{
	return ($input > PHP_INT_MAX) ? (float) $input : (int) $input;
}

/**
* Get the board contact details (e.g. for emails)
*
* @param \phpbb\config\config	$config
* @param string					$phpEx
* @return string
*/
function phpbb_get_board_contact(\phpbb\config\config $config, $phpEx)
{
	if ($config['contact_admin_form_enable'])
	{
		return generate_board_url() . '/memberlist.' . $phpEx . '?mode=contactadmin';
	}
	else
	{
		return $config['board_contact'];
	}
}

/**
* Get a clickable board contact details link
*
* @param \phpbb\config\config	$config
* @param string					$phpbb_root_path
* @param string					$phpEx
* @return string
*/
function phpbb_get_board_contact_link(\phpbb\config\config $config, $phpbb_root_path, $phpEx)
{
	if ($config['contact_admin_form_enable'] && $config['email_enable'])
	{
		return append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contactadmin');
	}
	else
	{
		return 'mailto:' . htmlspecialchars($config['board_contact'], ENT_COMPAT);
	}
}
