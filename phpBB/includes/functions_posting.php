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

	// TODO: To be added to the schema
	$config['max_smilies_inline'] = 20;

	if ($mode == 'window')
	{
		$page_title = $user->lang['TOPIC_REVIEW'] . " - $topic_title";
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			'body' => 'posting_smilies.html')
		);
	}

	$where_sql = ($mode == 'inline') ? 'WHERE display_on_posting = 1 ' : '';
	$sql = "SELECT emoticon, code, smile_url, smile_width, smile_height
	FROM " . SMILIES_TABLE . "
	$where_sql
	ORDER BY smile_order";

	$result = $db->sql_query($sql);

	$num_smilies = 0;
	$smile_array = array();
	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			if (!in_array($row['smile_url'], $smile_array))
			{
				if ($mode == 'window' || ($mode == 'inline' && $num_smilies < $config['max_smilies_inline']))
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

		if ($mode == 'inline' && $num_smilies >= $config['max_smilies_inline'])
		{
			$template->assign_vars(array(
				'S_SHOW_EMOTICON_LINK' 	=> true,
				'U_MORE_SMILIES' 		=> "posting.$phpEx$SID&amp;mode=smilies")
			);
		}
	}

	if ($mode == 'window')
	{
		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}

// Generate Topic Icons
function generate_topic_icons($mode, $enable_icons)
{
	global $template, $config;

	if (!$enable_icons)
	{
		return (false);
	}
	
	$result = false;

	// Grab icons
	$icons = array();
	obtain_icons($icons);

	if (sizeof($icons))
	{
		$result = true;

		foreach ($icons as $id => $data)
		{
			if ($data['display'])
			{
				$template->assign_block_vars('topic_icon', array(
					'ICON_ID'		=> $id,
					'ICON_IMG'		=> $phpbb_root_path . $config['icons_path'] . '/' . $data['img'],
					'ICON_WIDTH'	=> $data['width'],
					'ICON_HEIGHT' 	=> $data['height'],

					'S_ICON_CHECKED' => ($id == $icon_id && $mode != 'reply') ? ' checked="checked"' : '')
				);
			}
		}
	}

	return ($result);
}

// DECODE TEXT -> This will/should be handled by bbcode.php eventually
function decode_text(&$message)
{
	global $config, $censors;

	$server_protocol = ($config['cookie_secure']) ? 'https://' : 'http://';
	$server_port = ($config['server_port'] <> 80) ? ':' . trim($config['server_port']) . '/' : '/';

	$match = array(
		'#<!\-\- b \-\-><b>(.*?)</b><!\-\- b \-\->#s',
		'#<!\-\- u \-\-><u>(.*?)</u><!\-\- u \-\->#s',
		'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
		'#<!\-\- m \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- m \-\->#',
		'#<!\-\- w \-\-><a href="http:\/\/(.*?)" target="_blank">.*?</a><!\-\- w \-\->#',
		'#<!\-\- l \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- l \-\->#',
		'#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
	);

	$replace = array(
		'[b]\1[/b]',
		'[u]\1[/u]',
		'\1',
		'\1',
		'\1',
		$server_protocol . trim($config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '\1', trim($config['script_path'])) . '/\1',
		'\1',
	);

	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

	$message = preg_replace($match, $replace, $message);

	return;
}

// Quote Text
function quote_text(&$message, $username = '')
{
	$message = ' [quote' . ( (empty($username)) ? ']' : '="]' . addslashes(trim($username)) . '"]') . trim($message) . '[/quote] ';
}

?>