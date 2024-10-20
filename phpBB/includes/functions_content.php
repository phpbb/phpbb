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
* gen_sort_selects()
* make_jumpbox()
* bump_topic_allowed()
* get_context()
* phpbb_clean_search_string()
* decode_message()
* strip_bbcode()
* generate_text_for_display()
* generate_text_for_storage()
* generate_text_for_edit()
* make_clickable_callback()
* make_clickable()
* censor_text()
* bbcode_nl2br()
* smiley_text()
* parse_attachments()
* extension_allowed()
* truncate_string()
* get_username_string()
* class bitfield
*/

/**
* Generate sort selection fields
*/
function gen_sort_selects(&$limit_days, &$sort_by_text, &$sort_days, &$sort_key, &$sort_dir, &$s_limit_days, &$s_sort_key, &$s_sort_dir, &$u_sort_param, $def_st = false, $def_sk = false, $def_sd = false)
{
	global $user, $phpbb_dispatcher;

	$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

	$sorts = array(
		'st'	=> array(
			'key'		=> 'sort_days',
			'default'	=> $def_st,
			'options'	=> $limit_days,
			'output'	=> &$s_limit_days,
		),

		'sk'	=> array(
			'key'		=> 'sort_key',
			'default'	=> $def_sk,
			'options'	=> $sort_by_text,
			'output'	=> &$s_sort_key,
		),

		'sd'	=> array(
			'key'		=> 'sort_dir',
			'default'	=> $def_sd,
			'options'	=> $sort_dir_text,
			'output'	=> &$s_sort_dir,
		),
	);
	$u_sort_param  = '';

	foreach ($sorts as $name => $sort_ary)
	{
		$key = $sort_ary['key'];
		$selected = ${$sort_ary['key']};

		// Check if the key is selectable. If not, we reset to the default or first key found.
		// This ensures the values are always valid. We also set $sort_dir/sort_key/etc. to the
		// correct value, else the protection is void. ;)
		if (!isset($sort_ary['options'][$selected]))
		{
			if ($sort_ary['default'] !== false)
			{
				$selected = ${$key} = $sort_ary['default'];
			}
			else
			{
				@reset($sort_ary['options']);
				$selected = ${$key} = key($sort_ary['options']);
			}
		}

		$sort_ary['output'] = '<select name="' . $name . '" id="' . $name . '">';
		foreach ($sort_ary['options'] as $option => $text)
		{
			$sort_ary['output'] .= '<option value="' . $option . '"' . (($selected == $option) ? ' selected="selected"' : '') . '>' . $text . '</option>';
		}
		$sort_ary['output'] .= '</select>';

		$u_sort_param .= ($selected !== $sort_ary['default']) ? ((strlen($u_sort_param)) ? '&amp;' : '') . "{$name}={$selected}" : '';
	}

	/**
	 * Run code before generated sort selects are returned
	 *
	 * @event core.gen_sort_selects_after
	 * @var	int      limit_days     Days limit
	 * @var	array    sort_by_text   Sort by text options
	 * @var	int      sort_days      Sort by days flag
	 * @var	string   sort_key       Sort key
	 * @var	string   sort_dir       Sort dir
	 * @var	string   s_limit_days   String of days limit
	 * @var	string   s_sort_key     String of sort key
	 * @var	string   s_sort_dir     String of sort dir
	 * @var	string   u_sort_param   Sort URL params
	 * @var	bool     def_st         Default sort days
	 * @var	bool     def_sk         Default sort key
	 * @var	bool     def_sd         Default sort dir
	 * @var	array    sorts          Sorts
	 * @since 3.1.9-RC1
	 */
	$vars = array(
		'limit_days',
		'sort_by_text',
		'sort_days',
		'sort_key',
		'sort_dir',
		's_limit_days',
		's_sort_key',
		's_sort_dir',
		'u_sort_param',
		'def_st',
		'def_sk',
		'def_sd',
		'sorts',
	);
	extract($phpbb_dispatcher->trigger_event('core.gen_sort_selects_after', compact($vars)));

	return;
}

/**
* Generate Jumpbox
*/
function make_jumpbox($action, $forum_id = false, $select_all = false, $acl_list = false, $force_display = false)
{
	global $config, $auth, $template, $user, $db, $phpbb_path_helper, $phpbb_dispatcher;

	// We only return if the jumpbox is not forced to be displayed (in case it is needed for functionality)
	if (!$config['load_jumpbox'] && $force_display === false)
	{
		return;
	}

	$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
		FROM ' . FORUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql, 600);

	$rowset = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$rowset[(int) $row['forum_id']] = $row;
	}
	$db->sql_freeresult($result);

	$right = $padding = 0;
	$padding_store = array('0' => 0);
	$display_jumpbox = false;
	$iteration = 0;

	/**
	* Modify the jumpbox forum list data
	*
	* @event core.make_jumpbox_modify_forum_list
	* @var	array	rowset	Array with the forums list data
	* @since 3.1.10-RC1
	*/
	$vars = array('rowset');
	extract($phpbb_dispatcher->trigger_event('core.make_jumpbox_modify_forum_list', compact($vars)));

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	foreach ($rowset as $row)
	{
		if ($row['left_id'] < $right)
		{
			$padding++;
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			// Ok, if the $padding_store for this parent is empty there is something wrong. For now we will skip over it.
			// @todo digging deep to find out "how" this can happen.
			$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : $padding;
		}

		$right = $row['right_id'];

		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
		{
			// Non-postable forum with no subforums, don't display
			continue;
		}

		if (!$auth->acl_get('f_list', $row['forum_id']))
		{
			// if the user does not have permissions to list this forum skip
			continue;
		}

		if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
		{
			continue;
		}

		$tpl_ary = array();
		if (!$display_jumpbox)
		{
			$tpl_ary[] = array(
				'FORUM_ID'		=> ($select_all) ? 0 : -1,
				'FORUM_NAME'	=> ($select_all) ? $user->lang['ALL_FORUMS'] : $user->lang['SELECT_FORUM'],
				'S_FORUM_COUNT'	=> $iteration,
				'LINK'			=> $phpbb_path_helper->append_url_params($action, array('f' => $forum_id)),
			);

			$iteration++;
			$display_jumpbox = true;
		}

		$tpl_ary[] = array(
			'FORUM_ID'		=> $row['forum_id'],
			'FORUM_NAME'	=> $row['forum_name'],
			'SELECTED'		=> ($row['forum_id'] == $forum_id) ? ' selected="selected"' : '',
			'S_FORUM_COUNT'	=> $iteration,
			'S_IS_CAT'		=> ($row['forum_type'] == FORUM_CAT) ? true : false,
			'S_IS_LINK'		=> ($row['forum_type'] == FORUM_LINK) ? true : false,
			'S_IS_POST'		=> ($row['forum_type'] == FORUM_POST) ? true : false,
			'LINK'			=> $phpbb_path_helper->append_url_params($action, array('f' => $row['forum_id'])),
		);

		/**
		 * Modify the jumpbox before it is assigned to the template
		 *
		 * @event core.make_jumpbox_modify_tpl_ary
		 * @var	array	row				The data of the forum
		 * @var	array	tpl_ary			Template data of the forum
		 * @since 3.1.10-RC1
		 */
		$vars = array(
			'row',
			'tpl_ary',
		);
		extract($phpbb_dispatcher->trigger_event('core.make_jumpbox_modify_tpl_ary', compact($vars)));

		$template->assign_block_vars_array('jumpbox_forums', $tpl_ary);

		unset($tpl_ary);

		for ($i = 0; $i < $padding; $i++)
		{
			$template->assign_block_vars('jumpbox_forums.level', array());
		}
		$iteration++;
	}
	unset($padding_store, $rowset);

	$url_parts = $phpbb_path_helper->get_url_parts($action);

	$template->assign_vars(array(
		'S_DISPLAY_JUMPBOX'			=> $display_jumpbox,
		'S_JUMPBOX_ACTION'			=> $action,
		'HIDDEN_FIELDS_FOR_JUMPBOX'	=> build_hidden_fields($url_parts['params']),
	));

	return;
}

/**
* Bump Topic Check - used by posting and viewtopic
*/
function bump_topic_allowed($forum_id, $topic_bumped, $last_post_time, $topic_poster, $last_topic_poster)
{
	global $config, $auth, $user, $phpbb_dispatcher;

	/**
	 * Event to run code before the topic bump checks
	 *
	 * @event core.bump_topic_allowed_before
	 * @var	int		forum_id			ID of the forum
	 * @var	int		topic_bumped		Flag indicating if the topic was already bumped (0/1)
	 * @var	int		last_post_time		The time of the topic last post
	 * @var	int		topic_poster		User ID of the topic author
	 * @var	int		last_topic_poster	User ID of the topic last post author
	 * @since 3.3.14-RC1
	 */
	$vars = [
		'forum_id',
		'topic_bumped',
		'last_post_time',
		'topic_poster',
		'last_topic_poster',
	];
	extract($phpbb_dispatcher->trigger_event('core.bump_topic_allowed_before', compact($vars)));

	// Check permission and make sure the last post was not already bumped
	if (!$auth->acl_get('f_bump', $forum_id) || $topic_bumped)
	{
		return false;
	}

	// Check bump time range, is the user really allowed to bump the topic at this time?
	$bump_time = ($config['bump_type'] == 'm') ? $config['bump_interval'] * 60 : (($config['bump_type'] == 'h') ? $config['bump_interval'] * 3600 : $config['bump_interval'] * 86400);

	// Check bump time
	if ($last_post_time + $bump_time > time())
	{
		return false;
	}

	// Check bumper, only topic poster and last poster are allowed to bump
	if ($topic_poster != $user->data['user_id'] && $last_topic_poster != $user->data['user_id'])
	{
		return false;
	}

	/**
	 * Event to run code after the topic bump checks
	 *
	 * @event core.bump_topic_allowed_after
	 * @var	int		forum_id			ID of the forum
	 * @var	int		topic_bumped		Flag indicating if the topic was already bumped (0/1)
	 * @var	int		last_post_time		The time of the topic last post
	 * @var	int		topic_poster		User ID of the topic author
	 * @var	int		last_topic_poster	User ID of the topic last post author
	 * @var	int		bump_time			Bump time range
	 * @since 3.3.14-RC1
	 */
	$vars = [
		'forum_id',
		'topic_bumped',
		'last_post_time',
		'topic_poster',
		'last_topic_poster',
		'bump_time',
	];
	extract($phpbb_dispatcher->trigger_event('core.bump_topic_allowed_after', compact($vars)));

	// A bump time of 0 will completely disable the bump feature... not intended but might be useful.
	return $bump_time;
}

/**
* Generates a text with approx. the specified length which contains the specified words and their context
*
* @param	string	$text	The full text from which context shall be extracted
* @param	array	$words	An array of words which should be contained in the result, has to be a valid part of a PCRE pattern (escape with preg_quote!)
* @param	int		$length	The desired length of the resulting text, however the result might be shorter or longer than this value
*
* @return	string			Context of the specified words separated by "..."
*/
function get_context(string $text, array $words, int $length = 400): string
{
	if ($length <= 0)
	{
		return $text;
	}

	// We need to turn the entities back into their original form, to not cut the message in between them
	$text = htmlspecialchars_decode($text);

	// Replace all spaces/invisible characters with single spaces
	$text = preg_replace("/[\p{Z}\h\v]+/u", ' ', $text);

	$text_length = utf8_strlen($text);

	// Get first occurrence of each word
	$word_indexes = [];
	foreach ($words as $word)
	{
		$pos = utf8_stripos($text, $word);

		if ($pos !== false)
		{
			$word_indexes[$pos] = $word;
		}
	}
	if (!empty($word_indexes))
	{
		ksort($word_indexes);

		// Size of the fragment of text per word
		$num_indexes = count($word_indexes);
		$characters_per_word = (int) ($length / $num_indexes) + 2; // 2 to leave one character of margin at the sides to don't cut words

		// Get text fragment indexes
		$fragments = [];
		foreach ($word_indexes as $index => $word)
		{
			$word_length = utf8_strlen($word);
			$start = max(0, min($text_length - 1 - $characters_per_word, (int) ($index + ($word_length / 2) - ($characters_per_word / 2))));
			$end = $start + $characters_per_word;

			// Check if we can merge this fragment into the previous fragment
			if (!empty($fragments))
			{
				[$prev_start, $prev_end] = end($fragments);

				if ($prev_end + $characters_per_word >= $index + $word_length)
				{
					array_pop($fragments);
					$start = $prev_start;
					$end = $prev_end + $characters_per_word;
				}
			}

			$fragments[] = [$start, $end];
		}
	}
	else
	{
		// There is no coincidences, so we just create a fragment with the first $length characters
		$fragments[] = [0, $length];
		$end = $length;
	}

	$output = [];
	foreach ($fragments as [$start, $end])
	{
		$fragment = utf8_substr($text, $start, $end - $start + 1);

		$fragment_start = 0;
		$fragment_end = $end - $start + 1;

		// Find the first valid alphanumeric character in the fragment to don't cut words
		if ($start > 0 && preg_match('/[^\p{L}\p{N}][\p{L}\p{N}]/u', $fragment, $matches, PREG_OFFSET_CAPTURE))
		{
			$fragment_start = utf8_strlen(substr($fragment, 0, (int) $matches[0][1])) + 1;
		}

		// Find the last valid alphanumeric character in the fragment to don't cut words
		if ($end < $text_length - 1 && preg_match_all('/[\p{L}\p{N}][^\p{L}\p{N}]/u', $fragment, $matches, PREG_OFFSET_CAPTURE))
		{
			$fragment_end = utf8_strlen(substr($fragment, 0, end($matches[0])[1]));
		}

		$output[] = utf8_substr($fragment, $fragment_start, $fragment_end - $fragment_start + 1);
	}

	return ($fragments[0][0] !== 0 ? '... ' : '') . utf8_htmlspecialchars(implode(' ... ', $output)) . ($end < $text_length - 1 ? ' ...' : '');
}

/**
* Cleans a search string by removing single wildcards from it and replacing multiple spaces with a single one.
*
* @param string $search_string The full search string which should be cleaned.
*
* @return string The cleaned search string without any wildcards and multiple spaces.
*/
function phpbb_clean_search_string($search_string)
{
	// This regular expressions matches every single wildcard.
	// That means one after a whitespace or the beginning of the string or one before a whitespace or the end of the string.
	$search_string = preg_replace('#(?<=^|\s)\*+(?=\s|$)#', '', $search_string);
	$search_string = trim($search_string);
	$search_string = preg_replace(array('#\s+#u', '#\*+#u'), array(' ', '*'), $search_string);
	return $search_string;
}

/**
* Decode text whereby text is coming from the db and expected to be pre-parsed content
* We are placing this outside of the message parser because we are often in need of it...
*
* NOTE: special chars are kept encoded
*
* @param string &$message Original message, passed by reference
* @param string $bbcode_uid BBCode UID
* @return null
*/
function decode_message(&$message, $bbcode_uid = '')
{
	global $phpbb_container, $phpbb_dispatcher;

	/**
	 * Use this event to modify the message before it is decoded
	 *
	 * @event core.decode_message_before
	 * @var string	message_text	The message content
	 * @var string	bbcode_uid		The message BBCode UID
	 * @since 3.1.9-RC1
	 */
	$message_text = $message;
	$vars = array('message_text', 'bbcode_uid');
	extract($phpbb_dispatcher->trigger_event('core.decode_message_before', compact($vars)));
	$message = $message_text;

	if (preg_match('#^<[rt][ >]#', $message))
	{
		$message = htmlspecialchars($phpbb_container->get('text_formatter.utils')->unparse($message), ENT_COMPAT);
	}
	else
	{
		if ($bbcode_uid)
		{
			$match = array('<br />', "[/*:m:$bbcode_uid]", ":u:$bbcode_uid", ":o:$bbcode_uid", ":$bbcode_uid");
			$replace = array("\n", '', '', '', '');
		}
		else
		{
			$match = array('<br />');
			$replace = array("\n");
		}

		$message = str_replace($match, $replace, $message);

		$match = get_preg_expression('bbcode_htm');
		$replace = array('\1', '\1', '\2', '\2', '\1', '', '');

		$message = preg_replace($match, $replace, $message);
	}

	/**
	* Use this event to modify the message after it is decoded
	*
	* @event core.decode_message_after
	* @var string	message_text	The message content
	* @var string	bbcode_uid		The message BBCode UID
	* @since 3.1.9-RC1
	*/
	$message_text = $message;
	$vars = array('message_text', 'bbcode_uid');
	extract($phpbb_dispatcher->trigger_event('core.decode_message_after', compact($vars)));
	$message = $message_text;
}

/**
* Strips all bbcode from a text in place
*/
function strip_bbcode(&$text, $uid = '')
{
	global $phpbb_container;

	if (preg_match('#^<[rt][ >]#', $text))
	{
		$text = utf8_htmlspecialchars($phpbb_container->get('text_formatter.utils')->clean_formatting($text));
	}
	else
	{
		if (!$uid)
		{
			$uid = '[0-9a-z]{5,}';
		}

		$text = preg_replace("#\[\/?[a-z0-9\*\+\-]+(?:=(?:&quot;.*&quot;|[^\]]*))?(?::[a-z])?(\:$uid)\]#", ' ', $text);

		$match = get_preg_expression('bbcode_htm');
		$replace = array('\1', '\1', '\2', '\1', '', '');

		$text = preg_replace($match, $replace, $text);
	}
}

/**
* For display of custom parsed text on user-facing pages
* Expects $text to be the value directly from the database (stored value)
*/
function generate_text_for_display($text, $uid, $bitfield, $flags, $censor_text = true)
{
	static $bbcode;
	global $auth, $config, $user;
	global $phpbb_dispatcher, $phpbb_container;

	if ($text === '')
	{
		return '';
	}

	/**
	* Use this event to modify the text before it is parsed
	*
	* @event core.modify_text_for_display_before
	* @var string	text			The text to parse
	* @var string	uid				The BBCode UID
	* @var string	bitfield		The BBCode Bitfield
	* @var int		flags			The BBCode Flags
	* @var bool		censor_text		Whether or not to apply word censors
	* @since 3.1.0-a1
	*/
	$vars = array('text', 'uid', 'bitfield', 'flags', 'censor_text');
	extract($phpbb_dispatcher->trigger_event('core.modify_text_for_display_before', compact($vars)));

	if (preg_match('#^<[rt][ >]#', $text))
	{
		$renderer = $phpbb_container->get('text_formatter.renderer');

		// Temporarily switch off viewcensors if applicable
		$old_censor = $renderer->get_viewcensors();

		// Check here if the user is having viewing censors disabled (and also allowed to do so).
		if (!$user->optionget('viewcensors') && $config['allow_nocensors'] && $auth->acl_get('u_chgcensors'))
		{
			$censor_text = false;
		}

		if ($old_censor !== $censor_text)
		{
			$renderer->set_viewcensors($censor_text);
		}

		$text = $renderer->render($text);

		// Restore the previous value
		if ($old_censor !== $censor_text)
		{
			$renderer->set_viewcensors($old_censor);
		}
	}
	else
	{
		if ($censor_text)
		{
			$text = censor_text($text);
		}

		// Parse bbcode if bbcode uid stored and bbcode enabled
		if ($uid && ($flags & OPTION_FLAG_BBCODE))
		{
			if (!class_exists('bbcode'))
			{
				global $phpbb_root_path, $phpEx;
				include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
			}

			if (empty($bbcode))
			{
				$bbcode = new bbcode($bitfield);
			}
			else
			{
				$bbcode->bbcode_set_bitfield($bitfield);
			}

			$bbcode->bbcode_second_pass($text, $uid);
		}

		$text = bbcode_nl2br($text);
		$text = smiley_text($text, !($flags & OPTION_FLAG_SMILIES));
	}

	/**
	* Use this event to modify the text after it is parsed
	*
	* @event core.modify_text_for_display_after
	* @var string	text		The text to parse
	* @var string	uid			The BBCode UID
	* @var string	bitfield	The BBCode Bitfield
	* @var int		flags		The BBCode Flags
	* @since 3.1.0-a1
	*/
	$vars = array('text', 'uid', 'bitfield', 'flags');
	extract($phpbb_dispatcher->trigger_event('core.modify_text_for_display_after', compact($vars)));

	return $text;
}

/**
* For parsing custom parsed text to be stored within the database.
* This function additionally returns the uid and bitfield that needs to be stored.
* Expects $text to be the value directly from $request->variable() and in it's non-parsed form
*
* @param string $text The text to be replaced with the parsed one
* @param string $uid The BBCode uid for this parse
* @param string $bitfield The BBCode bitfield for this parse
* @param int $flags The allow_bbcode, allow_urls and allow_smilies compiled into a single integer.
* @param bool $allow_bbcode If BBCode is allowed (i.e. if BBCode is parsed)
* @param bool $allow_urls If urls is allowed
* @param bool $allow_smilies If smilies are allowed
* @param bool $allow_img_bbcode
* @param bool $allow_flash_bbcode
* @param bool $allow_quote_bbcode
* @param bool $allow_url_bbcode
* @param string $mode Mode to parse text as, e.g. post or sig
*
* @return array	An array of string with the errors that occurred while parsing
*/
function generate_text_for_storage(&$text, &$uid, &$bitfield, &$flags, $allow_bbcode = false, $allow_urls = false, $allow_smilies = false, $allow_img_bbcode = true, $allow_flash_bbcode = true, $allow_quote_bbcode = true, $allow_url_bbcode = true, $mode = 'post')
{
	global $phpbb_root_path, $phpEx, $phpbb_dispatcher;

	/**
	* Use this event to modify the text before it is prepared for storage
	*
	* @event core.modify_text_for_storage_before
	* @var string	text			The text to parse
	* @var string	uid				The BBCode UID
	* @var string	bitfield		The BBCode Bitfield
	* @var int		flags			The BBCode Flags
	* @var bool		allow_bbcode	Whether or not to parse BBCode
	* @var bool		allow_urls		Whether or not to parse URLs
	* @var bool		allow_smilies	Whether or not to parse Smilies
	* @var bool		allow_img_bbcode	Whether or not to parse the [img] BBCode
	* @var bool		allow_flash_bbcode	Whether or not to parse the [flash] BBCode
	* @var bool		allow_quote_bbcode	Whether or not to parse the [quote] BBCode
	* @var bool		allow_url_bbcode	Whether or not to parse the [url] BBCode
	* @var string	mode				Mode to parse text as, e.g. post or sig
	* @since 3.1.0-a1
	* @changed 3.2.0-a1 Added mode
	*/
	$vars = array(
		'text',
		'uid',
		'bitfield',
		'flags',
		'allow_bbcode',
		'allow_urls',
		'allow_smilies',
		'allow_img_bbcode',
		'allow_flash_bbcode',
		'allow_quote_bbcode',
		'allow_url_bbcode',
		'mode',
	);
	extract($phpbb_dispatcher->trigger_event('core.modify_text_for_storage_before', compact($vars)));

	$uid = $bitfield = '';
	$flags = (($allow_bbcode) ? OPTION_FLAG_BBCODE : 0) + (($allow_smilies) ? OPTION_FLAG_SMILIES : 0) + (($allow_urls) ? OPTION_FLAG_LINKS : 0);

	if (!class_exists('parse_message'))
	{
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
	}

	$message_parser = new parse_message($text);
	$message_parser->parse($allow_bbcode, $allow_urls, $allow_smilies, $allow_img_bbcode, $allow_flash_bbcode, $allow_quote_bbcode, $allow_url_bbcode, true, $mode);

	$text = $message_parser->message;
	$uid = $message_parser->bbcode_uid;

	// If the bbcode_bitfield is empty, there is no need for the uid to be stored.
	if (!$message_parser->bbcode_bitfield)
	{
		$uid = '';
	}

	$bitfield = $message_parser->bbcode_bitfield;

	/**
	* Use this event to modify the text after it is prepared for storage
	*
	* @event core.modify_text_for_storage_after
	* @var string	text			The text to parse
	* @var string	uid				The BBCode UID
	* @var string	bitfield		The BBCode Bitfield
	* @var int		flags			The BBCode Flags
	* @var string	message_parser	The message_parser object
	* @since 3.1.0-a1
	* @changed 3.1.11-RC1			Added message_parser to vars
	*/
	$vars = array('text', 'uid', 'bitfield', 'flags', 'message_parser');
	extract($phpbb_dispatcher->trigger_event('core.modify_text_for_storage_after', compact($vars)));

	return $message_parser->warn_msg;
}

/**
* For decoding custom parsed text for edits as well as extracting the flags
* Expects $text to be the value directly from the database (pre-parsed content)
*/
function generate_text_for_edit($text, $uid, $flags)
{
	global $phpbb_dispatcher;

	/**
	* Use this event to modify the text before it is decoded for editing
	*
	* @event core.modify_text_for_edit_before
	* @var string	text			The text to parse
	* @var string	uid				The BBCode UID
	* @var int		flags			The BBCode Flags
	* @since 3.1.0-a1
	*/
	$vars = array('text', 'uid', 'flags');
	extract($phpbb_dispatcher->trigger_event('core.modify_text_for_edit_before', compact($vars)));

	decode_message($text, $uid);

	/**
	* Use this event to modify the text after it is decoded for editing
	*
	* @event core.modify_text_for_edit_after
	* @var string	text			The text to parse
	* @var int		flags			The BBCode Flags
	* @since 3.1.0-a1
	*/
	$vars = array('text', 'flags');
	extract($phpbb_dispatcher->trigger_event('core.modify_text_for_edit_after', compact($vars)));

	return array(
		'allow_bbcode'	=> ($flags & OPTION_FLAG_BBCODE) ? 1 : 0,
		'allow_smilies'	=> ($flags & OPTION_FLAG_SMILIES) ? 1 : 0,
		'allow_urls'	=> ($flags & OPTION_FLAG_LINKS) ? 1 : 0,
		'text'			=> $text
	);
}

/**
* A subroutine of make_clickable used with preg_replace
* It places correct HTML around an url, shortens the displayed text
* and makes sure no entities are inside URLs
*/
function make_clickable_callback($type, $whitespace, $url, $relative_url, $class)
{
	$orig_url		= $url;
	$orig_relative	= $relative_url;
	$append			= '';
	$url			= html_entity_decode($url, ENT_COMPAT);
	$relative_url	= html_entity_decode($relative_url, ENT_COMPAT);

	// make sure no HTML entities were matched
	$chars = array('<', '>', '"');
	$split = false;

	foreach ($chars as $char)
	{
		$next_split = strpos($url, $char);
		if ($next_split !== false)
		{
			$split = ($split !== false) ? min($split, $next_split) : $next_split;
		}
	}

	if ($split !== false)
	{
		// an HTML entity was found, so the URL has to end before it
		$append			= substr($url, $split) . $relative_url;
		$url			= substr($url, 0, $split);
		$relative_url	= '';
	}
	else if ($relative_url)
	{
		// same for $relative_url
		$split = false;
		foreach ($chars as $char)
		{
			$next_split = strpos($relative_url, $char);
			if ($next_split !== false)
			{
				$split = ($split !== false) ? min($split, $next_split) : $next_split;
			}
		}

		if ($split !== false)
		{
			$append			= substr($relative_url, $split);
			$relative_url	= substr($relative_url, 0, $split);
		}
	}

	// if the last character of the url is a punctuation mark, exclude it from the url
	$last_char = ($relative_url) ? $relative_url[strlen($relative_url) - 1] : $url[strlen($url) - 1];

	switch ($last_char)
	{
		case '.':
		case '?':
		case '!':
		case ':':
		case ',':
			$append = $last_char;
			if ($relative_url)
			{
				$relative_url = substr($relative_url, 0, -1);
			}
			else
			{
				$url = substr($url, 0, -1);
			}
		break;

		// set last_char to empty here, so the variable can be used later to
		// check whether a character was removed
		default:
			$last_char = '';
		break;
	}

	$short_url = (utf8_strlen($url) > 55) ? utf8_substr($url, 0, 39) . ' ... ' . utf8_substr($url, -10) : $url;

	switch ($type)
	{
		case MAGIC_URL_LOCAL:
			$tag			= 'l';
			$relative_url	= preg_replace('/[&?]sid=[0-9a-f]{32}$/', '', preg_replace('/([&?])sid=[0-9a-f]{32}&/', '$1', $relative_url));
			$url			= $url . '/' . $relative_url;
			$text			= $relative_url;

			// this url goes to http://domain.tld/path/to/board/ which
			// would result in an empty link if treated as local so
			// don't touch it and let MAGIC_URL_FULL take care of it.
			if (!$relative_url)
			{
				return $whitespace . $orig_url . '/' . $orig_relative; // slash is taken away by relative url pattern
			}
		break;

		case MAGIC_URL_FULL:
			$tag	= 'm';
			$text	= $short_url;
		break;

		case MAGIC_URL_WWW:
			$tag	= 'w';
			$url	= 'http://' . $url;
			$text	= $short_url;
		break;

		case MAGIC_URL_EMAIL:
			$tag	= 'e';
			$text	= $short_url;
			$url	= 'mailto:' . $url;
		break;
	}

	$url	= htmlspecialchars($url, ENT_COMPAT);
	$text	= htmlspecialchars($text, ENT_COMPAT);
	$append	= htmlspecialchars($append, ENT_COMPAT);

	$html	= "$whitespace<!-- $tag --><a$class href=\"$url\">$text</a><!-- $tag -->$append";

	return $html;
}

/**
 * Replaces magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
 * Cuts down displayed size of link if over 50 chars, turns absolute links
 * into relative versions when the server/script path matches the link
 *
 * @param string		$text		Message text to parse URL/email entries
 * @param bool|string	$server_url	The server URL. If false, the board URL will be used
 * @param string		$class		CSS class selector to add to the parsed URL entries
 *
 * @return string	A text with parsed URL/email entries
 */
function make_clickable($text, $server_url = false, string $class = 'postlink')
{
	if ($server_url === false)
	{
		$server_url = generate_board_url();
	}

	static $static_class;
	static $magic_url_match_args;

	if (!isset($magic_url_match_args[$server_url]) || $static_class != $class)
	{
		$static_class = $class;
		$class = ($static_class) ? ' class="' . $static_class . '"' : '';
		$local_class = ($static_class) ? ' class="' . $static_class . '-local"' : '';

		if (!is_array($magic_url_match_args))
		{
			$magic_url_match_args = array();
		}

		// Check if the match for this $server_url and $class already exists
		$element_exists = false;
		if (isset($magic_url_match_args[$server_url]))
		{
			array_walk_recursive($magic_url_match_args[$server_url], function($value) use (&$element_exists, $static_class)
				{
					if ($value == $static_class)
					{
						$element_exists = true;
						return;
					}
				}
			);
		}

		// Only add new $server_url and $class matches if not exist
		if (!$element_exists)
		{
			// relative urls for this board
			$magic_url_match_args[$server_url][] = [
				'#(^|[\n\t (>.])(' . preg_quote($server_url, '#') . ')/(' . get_preg_expression('relative_url_inline') . ')#iu',
				MAGIC_URL_LOCAL,
				$local_class,
				$static_class,
			];

			// matches a xxxx://aaaaa.bbb.cccc. ...
			$magic_url_match_args[$server_url][] = [
				'#(^|[\n\t (>.])(' . get_preg_expression('url_inline') . ')#iu',
				MAGIC_URL_FULL,
				$class,
				$static_class,
			];

			// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
			$magic_url_match_args[$server_url][] = [
				'#(^|[\n\t (>])(' . get_preg_expression('www_url_inline') . ')#iu',
				MAGIC_URL_WWW,
				$class,
				$static_class,
			];
		}

		if (!isset($magic_url_match_args[$server_url]['email']))
		{
			// matches an email@domain type address at the start of a line, or after a space or after what might be a BBCode.
			$magic_url_match_args[$server_url]['email'] = [
				'/(^|[\n\t (>])(' . get_preg_expression('email') . ')/iu',
				MAGIC_URL_EMAIL,
				'',
			];
		}
	}

	foreach ($magic_url_match_args[$server_url] as $magic_args)
	{
		if (preg_match($magic_args[0], $text, $matches))
		{
			// Only apply $class from the corresponding function call argument (excepting emails which never has a class)
			if ($magic_args[1] != MAGIC_URL_EMAIL && $magic_args[3] != $static_class)
			{
				continue;
			}

			$text = preg_replace_callback($magic_args[0], function($matches) use ($magic_args)
			{
				$relative_url = isset($matches[3]) ? $matches[3] : '';
				return make_clickable_callback($magic_args[1], $matches[1], $matches[2], $relative_url, $magic_args[2]);
			}, $text);
		}
	}

	return $text;
}

/**
* Censoring
*/
function censor_text($text)
{
	static $censors;

	// Nothing to do?
	if ($text === '')
	{
		return '';
	}

	// We moved the word censor checks in here because we call this function quite often - and then only need to do the check once
	if (!isset($censors) || !is_array($censors))
	{
		global $config, $user, $auth, $cache;

		// We check here if the user is having viewing censors disabled (and also allowed to do so).
		if (!$user->optionget('viewcensors') && $config['allow_nocensors'] && $auth->acl_get('u_chgcensors'))
		{
			$censors = array();
		}
		else
		{
			$censors = $cache->obtain_word_list();
		}
	}

	if (count($censors))
	{
		return preg_replace($censors['match'], $censors['replace'], $text);
	}

	return $text;
}

/**
* custom version of nl2br which takes custom BBCodes into account
*/
function bbcode_nl2br($text)
{
	// custom BBCodes might contain carriage returns so they
	// are not converted into <br /> so now revert that
	$text = str_replace(array("\n", "\r"), array('<br />', "\n"), $text);
	return $text;
}

/**
* Smiley processing
*/
function smiley_text($text, $force_option = false)
{
	global $config, $user, $phpbb_path_helper, $phpbb_dispatcher;

	if ($force_option || !$config['allow_smilies'] || !$user->optionget('viewsmilies'))
	{
		return preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $text);
	}
	else
	{
		$root_path = $phpbb_path_helper->get_web_root_path();

		/**
		* Event to override the root_path for smilies
		*
		* @event core.smiley_text_root_path
		* @var string root_path root_path for smilies
		* @since 3.1.11-RC1
		*/
		$vars = array('root_path');
		extract($phpbb_dispatcher->trigger_event('core.smiley_text_root_path', compact($vars)));
		return preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/(.*?) \/><!\-\- s\1 \-\->#', '<img class="smilies" src="' . $root_path . $config['smilies_path'] . '/\2 />', $text);
	}
}

/**
* General attachment parsing
*
* @param mixed $forum_id The forum id the attachments are displayed in (false if in private message)
* @param string &$message The post/private message
* @param array &$attachments The attachments to parse for (inline) display. The attachments array will hold templated data after parsing.
* @param array &$update_count_ary The attachment counts to be updated - will be filled
* @param bool $preview If set to true the attachments are parsed for preview. Within preview mode the comments are fetched from the given $attachments array and not fetched from the database.
*/
function parse_attachments($forum_id, &$message, &$attachments, &$update_count_ary, $preview = false)
{
	if (!count($attachments))
	{
		return;
	}

	global $template, $cache, $user, $phpbb_dispatcher;
	global $extensions, $config, $phpbb_root_path, $phpEx;

	//
	$compiled_attachments = array();

	if (!isset($template->filename['attachment_tpl']))
	{
		$template->set_filenames(array(
			'attachment_tpl'	=> 'attachment.html')
		);
	}

	if (empty($extensions) || !is_array($extensions))
	{
		$extensions = $cache->obtain_attach_extensions($forum_id);
	}

	// Look for missing attachment information...
	$attach_ids = array();
	foreach ($attachments as $pos => $attachment)
	{
		// If is_orphan is set, we need to retrieve the attachments again...
		if (!isset($attachment['extension']) && !isset($attachment['physical_filename']))
		{
			$attach_ids[(int) $attachment['attach_id']] = $pos;
		}
	}

	// Grab attachments (security precaution)
	if (count($attach_ids))
	{
		global $db;

		$new_attachment_data = array();

		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('attach_id', array_keys($attach_ids));
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!isset($attach_ids[$row['attach_id']]))
			{
				continue;
			}

			// If we preview attachments we will set some retrieved values here
			if ($preview)
			{
				$row['attach_comment'] = $attachments[$attach_ids[$row['attach_id']]]['attach_comment'];
			}

			$new_attachment_data[$attach_ids[$row['attach_id']]] = $row;
		}
		$db->sql_freeresult($result);

		$attachments = $new_attachment_data;
		unset($new_attachment_data);
	}

	// Make sure attachments are properly ordered
	ksort($attachments);

	foreach ($attachments as $attachment)
	{
		if (!count($attachment))
		{
			continue;
		}

		// We need to reset/empty the _file block var, because this function might be called more than once
		$template->destroy_block_vars('_file');

		$block_array = array();

		// Some basics...
		$attachment['extension'] = strtolower(trim($attachment['extension']));
		$filename = $phpbb_root_path . $config['upload_path'] . '/' . utf8_basename($attachment['physical_filename']);

		$upload_icon = '';
		$download_link = '';
		$display_cat = false;

		if (isset($extensions[$attachment['extension']]))
		{
			if ($user->img('icon_topic_attach', '') && !$extensions[$attachment['extension']]['upload_icon'])
			{
				$upload_icon = $user->img('icon_topic_attach', '');
			}
			else if ($extensions[$attachment['extension']]['upload_icon'])
			{
				$upload_icon = '<img src="' . $phpbb_root_path . $config['upload_icons_path'] . '/' . trim($extensions[$attachment['extension']]['upload_icon']) . '" alt="" />';
			}
		}

		$filesize = get_formatted_filesize($attachment['filesize'], false);

		$comment = bbcode_nl2br(censor_text($attachment['attach_comment']));

		$block_array += array(
			'UPLOAD_ICON'		=> $upload_icon,
			'FILESIZE'			=> $filesize['value'],
			'SIZE_LANG'			=> $filesize['unit'],
			'DOWNLOAD_NAME'		=> utf8_basename($attachment['real_filename']),
			'COMMENT'			=> $comment,
		);

		$denied = false;

		if (!extension_allowed($forum_id, $attachment['extension'], $extensions))
		{
			$denied = true;

			$block_array += array(
				'S_DENIED'			=> true,
				'DENIED_MESSAGE'	=> sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension'])
			);
		}

		if (!$denied)
		{
			$display_cat = $extensions[$attachment['extension']]['display_cat'];

			if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = ATTACHMENT_CATEGORY_THUMB;
				}
				else
				{
					if ($config['img_display_inlined'])
					{
						if ($config['img_link_width'] || $config['img_link_height'])
						{
							$dimension = @getimagesize($filename);

							// If the dimensions could not be determined or the image being 0x0 we display it as a link for safety purposes
							if ($dimension === false || empty($dimension[0]) || empty($dimension[1]))
							{
								$display_cat = ATTACHMENT_CATEGORY_NONE;
							}
							else
							{
								$display_cat = ($dimension[0] <= $config['img_link_width'] && $dimension[1] <= $config['img_link_height']) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE;
							}
						}
					}
					else
					{
						$display_cat = ATTACHMENT_CATEGORY_NONE;
					}
				}
			}

			// Make some descisions based on user options being set.
			if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !$user->optionget('viewimg'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			$download_link = append_sid("{$phpbb_root_path}download/file.$phpEx", 'id=' . $attachment['attach_id']);
			$l_downloaded_viewed = 'VIEWED_COUNTS';

			switch ($display_cat)
			{
				// Images
				case ATTACHMENT_CATEGORY_IMAGE:
					$inline_link = append_sid("{$phpbb_root_path}download/file.$phpEx", 'id=' . $attachment['attach_id']);
					$download_link .= '&amp;mode=view';

					$block_array += array(
						'S_IMAGE'		=> true,
						'U_INLINE_LINK'		=> $inline_link,
					);

					$update_count_ary[] = $attachment['attach_id'];
				break;

				// Images, but display Thumbnail
				case ATTACHMENT_CATEGORY_THUMB:
					$thumbnail_link = append_sid("{$phpbb_root_path}download/file.$phpEx", 'id=' . $attachment['attach_id'] . '&amp;t=1');
					$download_link .= '&amp;mode=view';

					$block_array += array(
						'S_THUMBNAIL'		=> true,
						'THUMB_IMAGE'		=> $thumbnail_link,
					);

					$update_count_ary[] = $attachment['attach_id'];
				break;

				default:
					$l_downloaded_viewed = 'DOWNLOAD_COUNTS';

					$block_array += array(
						'S_FILE'		=> true,
					);
				break;
			}

			if (!isset($attachment['download_count']))
			{
				$attachment['download_count'] = 0;
			}

			$block_array += array(
				'U_DOWNLOAD_LINK'		=> $download_link,
				'L_DOWNLOAD_COUNT'		=> $user->lang($l_downloaded_viewed, (int) $attachment['download_count']),
			);
		}

		$update_count = $update_count_ary;
		/**
		* Use this event to modify the attachment template data.
		*
		* This event is triggered once per attachment.
		*
		* @event core.parse_attachments_modify_template_data
		* @var array	attachment		Array with attachment data
		* @var array	block_array		Template data of the attachment
		* @var int		display_cat		Attachment category data
		* @var string	download_link	Attachment download link
		* @var array	extensions		Array with attachment extensions data
		* @var mixed 	forum_id 		The forum id the attachments are displayed in (false if in private message)
		* @var bool		preview			Flag indicating if we are in post preview mode
		* @var array	update_count	Array with attachment ids to update download count
		* @since 3.1.0-RC5
		*/
		$vars = array(
			'attachment',
			'block_array',
			'display_cat',
			'download_link',
			'extensions',
			'forum_id',
			'preview',
			'update_count',
		);
		extract($phpbb_dispatcher->trigger_event('core.parse_attachments_modify_template_data', compact($vars)));
		$update_count_ary = $update_count;
		unset($update_count, $display_cat, $download_link);

		$template->assign_block_vars('_file', $block_array);

		$compiled_attachments[] = $template->assign_display('attachment_tpl');
	}

	$attachments = $compiled_attachments;
	unset($compiled_attachments);

	$unset_tpl = array();

	preg_match_all('#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#', $message, $matches, PREG_PATTERN_ORDER);

	$replace = array();
	foreach ($matches[0] as $num => $capture)
	{
		$index = $matches[1][$num];

		$replace['from'][] = $matches[0][$num];
		$replace['to'][] = (isset($attachments[$index])) ? $attachments[$index] : sprintf($user->lang['MISSING_INLINE_ATTACHMENT'], $matches[2][array_search($index, $matches[1])]);

		$unset_tpl[] = $index;
	}

	if (isset($replace['from']))
	{
		$message = str_replace($replace['from'], $replace['to'], $message);
	}

	$unset_tpl = array_unique($unset_tpl);

	// Sort correctly
	if ($config['display_order'])
	{
		// Ascending sort
		krsort($attachments);
	}
	else
	{
		// Descending sort
		ksort($attachments);
	}

	// Needed to let not display the inlined attachments at the end of the post again
	foreach ($unset_tpl as $index)
	{
		unset($attachments[$index]);
	}
}

/**
* Check if extension is allowed to be posted.
*
* @param mixed $forum_id The forum id to check or false if private message
* @param string $extension The extension to check, for example zip.
* @param array &$extensions The extension array holding the information from the cache (will be obtained if empty)
*
* @return bool False if the extension is not allowed to be posted, else true.
*/
function extension_allowed($forum_id, $extension, &$extensions)
{
	if (empty($extensions))
	{
		global $cache;
		$extensions = $cache->obtain_attach_extensions($forum_id);
	}

	return (!isset($extensions['_allowed_'][$extension])) ? false : true;
}

/**
* Truncates string while retaining special characters if going over the max length
* The default max length is 60 at the moment
* The maximum storage length is there to fit the string within the given length. The string may be further truncated due to html entities.
* For example: string given is 'a "quote"' (length: 9), would be a stored as 'a &quot;quote&quot;' (length: 19)
*
* @param string $string The text to truncate to the given length. String is specialchared.
* @param int $max_length Maximum length of string (multibyte character count as 1 char / Html entity count as 1 char)
* @param int $max_store_length Maximum character length of string (multibyte character count as 1 char / Html entity count as entity chars).
* @param bool $allow_reply Allow Re: in front of string
* 	NOTE: This parameter can cause undesired behavior (returning strings longer than $max_store_length) and is deprecated.
* @param string $append String to be appended
*/
function truncate_string($string, $max_length = 60, $max_store_length = 255, $allow_reply = false, $append = '')
{
	$strip_reply = false;
	$stripped = false;
	if ($allow_reply && strpos($string, 'Re: ') === 0)
	{
		$strip_reply = true;
		$string = substr($string, 4);
	}

	$_chars = utf8_str_split(html_entity_decode($string, ENT_COMPAT));
	$chars = array_map('utf8_htmlspecialchars', $_chars);

	// Now check the length ;)
	if (count($chars) > $max_length)
	{
		// Cut off the last elements from the array
		$string = implode('', array_slice($chars, 0, $max_length - utf8_strlen($append)));
		$stripped = true;
	}

	// Due to specialchars, we may not be able to store the string...
	if (utf8_strlen($string) > $max_store_length)
	{
		// let's split again, we do not want half-baked strings where entities are split
		$_chars = utf8_str_split(html_entity_decode($string, ENT_COMPAT));
		$chars = array_map('utf8_htmlspecialchars', $_chars);

		do
		{
			array_pop($chars);
			$string = implode('', $chars);
		}
		while (!empty($chars) && utf8_strlen($string) > $max_store_length);
	}

	if ($strip_reply)
	{
		$string = 'Re: ' . $string;
	}

	if ($append != '' && $stripped)
	{
		$string = $string . $append;
	}

	return $string;
}

/**
* Get username details for placing into templates.
* This function caches all modes on first call, except for no_profile and anonymous user - determined by $user_id.
*
* @html Username spans and links
*
* @param string $mode Can be profile (for getting an url to the profile), username (for obtaining the username), colour (for obtaining the user colour), full (for obtaining a html string representing a coloured link to the users profile) or no_profile (the same as full but forcing no profile link)
* @param int $user_id The users id
* @param string $username The users name
* @param string $username_colour The users colour
* @param string $guest_username optional parameter to specify the guest username. It will be used in favor of the GUEST language variable then.
* @param string $custom_profile_url optional parameter to specify a profile url. The user id get appended to this url as &amp;u={user_id}
*
* @return string A string consisting of what is wanted based on $mode.
*/
function get_username_string($mode, $user_id, $username, $username_colour = '', $guest_username = false, $custom_profile_url = false)
{
	static $_profile_cache;
	global $phpbb_dispatcher;

	// We cache some common variables we need within this function
	if (empty($_profile_cache))
	{
		global $phpbb_root_path, $phpEx;

		/** @html Username spans and links for usage in the template */
		$_profile_cache['base_url'] = append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u={USER_ID}');
		$_profile_cache['tpl_noprofile'] = '<span class="username">{USERNAME}</span>';
		$_profile_cache['tpl_noprofile_colour'] = '<span style="color: {USERNAME_COLOUR};" class="username-coloured">{USERNAME}</span>';
		$_profile_cache['tpl_profile'] = '<a href="{PROFILE_URL}" class="username">{USERNAME}</a>';
		$_profile_cache['tpl_profile_colour'] = '<a href="{PROFILE_URL}" style="color: {USERNAME_COLOUR};" class="username-coloured">{USERNAME}</a>';
	}

	global $user, $auth;

	// This switch makes sure we only run code required for the mode
	switch ($mode)
	{
		case 'full':
		case 'no_profile':
		case 'colour':

			// Build correct username colour
			$username_colour = ($username_colour) ? '#' . $username_colour : '';

			// Return colour
			if ($mode == 'colour')
			{
				$username_string = $username_colour;
				break;
			}

		// no break;

		case 'username':

			// Build correct username
			if ($guest_username === false)
			{
				$username = ($username) ? $username : $user->lang['GUEST'];
			}
			else
			{
				$username = ($user_id && $user_id != ANONYMOUS) ? $username : ((!empty($guest_username)) ? $guest_username : $user->lang['GUEST']);
			}

			// Return username
			if ($mode == 'username')
			{
				$username_string = $username;
				break;
			}

		// no break;

		case 'profile':

			// Build correct profile url - only show if not anonymous and permission to view profile if registered user
			// For anonymous the link leads to a login page.
			if ($user_id && $user_id != ANONYMOUS && ($user->data['user_id'] == ANONYMOUS || $auth->acl_get('u_viewprofile')))
			{
				$profile_url = ($custom_profile_url !== false) ? $custom_profile_url . '&amp;u=' . (int) $user_id : str_replace(array('={USER_ID}', '=%7BUSER_ID%7D'), '=' . (int) $user_id, $_profile_cache['base_url']);
			}
			else
			{
				$profile_url = '';
			}

			// Return profile
			if ($mode == 'profile')
			{
				$username_string = $profile_url;
				break;
			}

		// no break;
	}

	if (!isset($username_string))
	{
		if (($mode == 'full' && !$profile_url) || $mode == 'no_profile')
		{
			$username_string = str_replace(array('{USERNAME_COLOUR}', '{USERNAME}'), array($username_colour, $username), (!$username_colour) ? $_profile_cache['tpl_noprofile'] : $_profile_cache['tpl_noprofile_colour']);
		}
		else
		{
			$username_string = str_replace(array('{PROFILE_URL}', '{USERNAME_COLOUR}', '{USERNAME}'), array($profile_url, $username_colour, $username), (!$username_colour) ? $_profile_cache['tpl_profile'] : $_profile_cache['tpl_profile_colour']);
		}
	}

	/**
	* Use this event to change the output of get_username_string()
	*
	* @event core.modify_username_string
	* @var string mode				profile|username|colour|full|no_profile
	* @var int user_id				String or array of additional url
	*								parameters
	* @var string username			The user's username
	* @var string username_colour	The user's colour
	* @var string guest_username	Optional parameter to specify the
	*								guest username.
	* @var string custom_profile_url Optional parameter to specify a
	*								profile url.
	* @var string username_string	The string that has been generated
	* @var array _profile_cache		Array of original return templates
	* @since 3.1.0-a1
	*/
	$vars = array(
		'mode',
		'user_id',
		'username',
		'username_colour',
		'guest_username',
		'custom_profile_url',
		'username_string',
		'_profile_cache',
	);
	extract($phpbb_dispatcher->trigger_event('core.modify_username_string', compact($vars)));

	return $username_string;
}

/**
 * Add an option to the quick-mod tools.
 *
 * @param string $url The recepting URL for the quickmod actions.
 * @param string $option The language key for the value of the option.
 * @param string $lang_string The language string to use.
 */
function phpbb_add_quickmod_option($url, $option, $lang_string)
{
	global $template, $user, $phpbb_path_helper;

	$lang_string = $user->lang($lang_string);
	$template->assign_block_vars('quickmod', array(
		'VALUE'		=> $option,
		'TITLE'		=> $lang_string,
		'LINK'		=> $phpbb_path_helper->append_url_params($url, array('action' => $option)),
	));
}

/**
* Concatenate an array into a string list.
*
* @param array $items Array of items to concatenate
* @param object $user The phpBB $user object.
*
* @return string String list. Examples: "A"; "A and B"; "A, B, and C"
*/
function phpbb_generate_string_list($items, $user)
{
	if (empty($items))
	{
		return '';
	}

	$count = count($items);
	$last_item = array_pop($items);
	$lang_key = 'STRING_LIST_MULTI';

	if ($count == 1)
	{
		return $last_item;
	}
	else if ($count == 2)
	{
		$lang_key = 'STRING_LIST_SIMPLE';
	}
	$list = implode($user->lang['COMMA_SEPARATOR'], $items);

	return $user->lang($lang_key, $list, $last_item);
}

class bitfield
{
	var $data;

	function __construct($bitfield = '')
	{
		$this->data = base64_decode($bitfield);
	}

	/**
	*/
	function get($n)
	{
		// Get the ($n / 8)th char
		$byte = $n >> 3;

		if (strlen($this->data) >= $byte + 1)
		{
			$c = $this->data[$byte];

			// Lookup the ($n % 8)th bit of the byte
			$bit = 7 - ($n & 7);
			return (bool) (ord($c) & (1 << $bit));
		}
		else
		{
			return false;
		}
	}

	function set($n)
	{
		$byte = $n >> 3;
		$bit = 7 - ($n & 7);

		if (strlen($this->data) >= $byte + 1)
		{
			$this->data[$byte] = $this->data[$byte] | chr(1 << $bit);
		}
		else
		{
			$this->data .= str_repeat("\0", $byte - strlen($this->data));
			$this->data .= chr(1 << $bit);
		}
	}

	function clear($n)
	{
		$byte = $n >> 3;

		if (strlen($this->data) >= $byte + 1)
		{
			$bit = 7 - ($n & 7);
			$this->data[$byte] = $this->data[$byte] &~ chr(1 << $bit);
		}
	}

	function get_blob()
	{
		return $this->data;
	}

	function get_base64()
	{
		return base64_encode($this->data);
	}

	function get_bin()
	{
		$bin = '';
		$len = strlen($this->data);

		for ($i = 0; $i < $len; ++$i)
		{
			$bin .= str_pad(decbin(ord($this->data[$i])), 8, '0', STR_PAD_LEFT);
		}

		return $bin;
	}

	function get_all_set()
	{
		return array_keys(array_filter(str_split($this->get_bin())));
	}

	function merge($bitfield)
	{
		$this->data = $this->data | $bitfield->get_blob();
	}
}

/**
 * Formats the quote according to the given BBCode status setting
 *
 * @param phpbb\language\language				$language Language class
 * @param parse_message 						$message_parser Message parser class
 * @param phpbb\textformatter\utils_interface	$text_formatter_utils Text formatter utilities
 * @param bool 									$bbcode_status The status of the BBCode setting
 * @param array 								$quote_attributes The attributes of the quoted post
 * @param string 								$message_link Link of the original quoted post
 */
function phpbb_format_quote($language, $message_parser, $text_formatter_utils, $bbcode_status, $quote_attributes, $message_link = '')
{
	if ($bbcode_status)
	{
		$quote_text = $text_formatter_utils->generate_quote(
			censor_text($message_parser->message),
			$quote_attributes
		);

		$message_parser->message = $quote_text . "\n\n";
	}
	else
	{
		$offset = 0;
		$quote_string = "&gt; ";
		$message = censor_text(trim($message_parser->message));
		// see if we are nesting. It's easily tricked but should work for one level of nesting
		if (strpos($message, "&gt;") !== false)
		{
			$offset = 10;
		}
		$message = utf8_wordwrap($message, 75 + $offset, "\n");

		$message = $quote_string . $message;
		$message = str_replace("\n", "\n" . $quote_string, $message);

		$message_parser->message = $quote_attributes['author'] . " " . $language->lang('WROTE') . ":\n" . $message . "\n";
	}

	if ($message_link)
	{
		$message_parser->message = $message_link . $message_parser->message;
	}
}
