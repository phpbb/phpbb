<?php
/***************************************************************************
 *                           message_parser.php
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

// Main message parser for posting, pm, etc. takes raw message
// and parses it for attachments, html, bbcode and smilies
class parse_message
{
	var $bbcode_tpl = null;
	var $message_mode = 0; // MSG_POST/MSG_PM

	function parse_message($message_type)
	{
		$this->message_mode = $message_type;
	}
	
	function parse(&$message, $html, $bbcode, $uid, $url, $smilies)
	{
		global $config, $db, $user, $_FILE;

		$warn_msg = '';

		// Do some general 'cleanup' first before processing message,
		// e.g. remove excessive newlines(?), smilies(?)
		$match = array('#sid=[a-z0-9]*?&?#', "#([\r\n][\s]+){3,}#");
		$replace = array('', "\n\n");

		$message = trim(preg_replace($match, $replace, $message));

		// Message length check
		if (!strlen($message) || (intval($config['max_post_chars']) && strlen($message) > intval($config['max_post_chars'])))
		{
			$warn_msg .= (($warn_msg != '') ? '<br />' : '') . (!strlen($message)) ? $user->lang['TOO_FEW_CHARS'] : $user->lang['TOO_MANY_CHARS'];
		}

		// Smiley check
		if (intval($config['max_post_smilies']) && $smilies )
		{
			$sql = "SELECT code	
			FROM " . SMILIES_TABLE;
			$result = $db->sql_query($sql);

			$match = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if (preg_match_all('#('. preg_quote($row['code'], '#') . ')#', $message, $matches))
				{
					$match++;
				}

				if ($match > intval($config['max_post_smilies']))
				{
					$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $user->lang['TOO_MANY_SMILIES'];
					break;
				}
			}
			$db->sql_freeresult($result);
			unset($matches);
		}

		if ($warn_msg)
		{
			return $warn_msg;
		}

		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->html($message, $html);
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->bbcode($message, $bbcode, $uid);
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->emoticons($message, $smilies);
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->magic_url($message, trim($url));
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->attach($_FILE);

		return $warn_msg;
	}

	function html(&$message, $html)
	{
		global $config;

		$message = str_replace(array('<', '>'), array('&lt;', '&gt;'), $message);

		if ($html)
		{
			// If $html is true then "allowed_tags" are converted back from entity
			// form, others remain
			$allowed_tags = split(',', $config['allow_html_tags']);

			if (sizeof($allowed_tags))
			{
				$message = preg_replace('#&lt;(\/?)(' . str_replace('*', '.*?', implode('|', $allowed_tags)) . ')&gt;#is', '<\1\2>', $message);
			}
		}

		return;
	}

	function bbcode(&$message, $bbcode, $uid)
	{
		global $config;

	}

	// Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
	// Cuts down displayed size of link if over 50 chars, turns absolute links
	// into relative versions when the server/script path matches the link
	function magic_url(&$message, $url)
	{
		global $config;

		if ($url)
		{
			$server_protocol = ( $config['cookie_secure'] ) ? 'https://' : 'http://';
			$server_port = ( $config['server_port'] <> 80 ) ? ':' . trim($config['server_port']) . '/' : '/';

			$match = array();
			$replace = array();

			// relative urls for this board
			$match[] = '#' . $server_protocol . trim($config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '\1', trim($config['script_path'])) . '/([^\t\n\r <"\']+)#i';
			$replace[] = '<!-- l --><a href="\1" target="_blank">\1</a><!-- l -->';

			// matches a xxxx://aaaaa.bbb.cccc. ...
			$match[] = '#(^|[\n ])([\w]+?://.*?[^\t\n\r<"]*)#ie';
			$replace[] = "'\\1<!-- m --><a href=\"\\2\" target=\"_blank\">' . ( ( strlen(str_replace(' ', '%20', '\\2')) > 55 ) ?substr(str_replace(' ', '%20', '\\2'), 0, 39) . ' ... ' . substr(str_replace(' ', '%20', '\\2'), -10) : str_replace(' ', '%20', '\\2') ) . '</a><!-- m -->'";

			// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
			$match[] = '#(^|[\n ])(www\.[\w\-]+\.[\w\-.\~]+(?:/[^\t\n\r<"]*)?)#ie';
			$replace[] = "'\\1<!-- w --><a href=\"http://\\2\" target=\"_blank\">' . ( ( strlen(str_replace(' ', '%20', '\\2')) > 55 ) ? substr(str_replace(' ', '%20', '\\2'), 0, 39) . ' ... ' . substr(str_replace(' ', '%20', '\\2'), -10) : str_replace(' ', '%20', '\\2') ) . '</a><!-- w -->'";

			// matches an email@domain type address at the start of a line, or after a space.
			$match[] = '#(^|[\n ])([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)#ie';
			$replace[] = "'\\1<!-- e --><a href=\"mailto:\\2\">' . ( ( strlen('\\2') > 55 ) ?substr('\\2', 0, 39) . ' ... ' . substr('\\2', -10) : '\\2' ) . '</a><!-- e -->'";

			$message = preg_replace($match, $replace, $message);
		}
	}

	function emoticons(&$message, $smile)
	{
		global $db, $user;

		$sql = "SELECT * 
		FROM " . SMILIES_TABLE;
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$match = $replace = array();
			do
			{
				$match[] = "#(?<=.\W|\W.|^\W)" . preg_quote($row['code'], '#') . "(?=.\W|\W.|\W$)#";
				$replace[] = '<!-- s' . $row['code'] . ' --><img src="{SMILE_PATH}/' . $row['smile_url'] . '" border="0" alt="' . $row['emoticon'] . '" title="' . $row['emoticon'] . '" /><!-- s' . $row['code'] . ' -->';
			}
			while ($row = $db->sql_fetchrow($result));

			$message = preg_replace($match, $replace, ' ' . $message . ' ');
		}
		$db->sql_freeresult($result);

		return;
	}

	function attach($file_ary)
	{
		global $config;

	}

	// Format text to be displayed - from viewtopic.php
	function format_display($message, $html, $bbcode, $uid, $url, $smilies, $sig)
	{
		global $auth, $forum_id, $config, $censors;

		// If the board has HTML off but the post has HTML
		// on then we process it, else leave it alone
		if ($html && $auth->acl_get('f_bbcode', $forum_id))
		{
			$message = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $message);
		}

		// Second parse bbcode here

		// If we allow users to disable display of emoticons
		// we'll need an appropriate check and preg_replace here
		$message = (empty($smilies) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $message);


		// Replace naughty words such as farty pants
		if (sizeof($censors))
		{
			$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $message . '<'), 1, -1));
		}

		$message = nl2br($message);

		/* Signature
		$user_sig = ($sig && $signature != '' && $config['allow_sig']) ? $row['user_sig'] : '';

			if ($user_sig != '' && $auth->acl_gets('f_sigs', 'm_', 'a_', $forum_id))
			{
				if (!$auth->acl_get('f_html', $forum_id) && $user->data['user_allowhtml'])
				{
					$user_sig = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $user_sig);
				}

				$user_cache[$poster_id]['sig'] = (empty($row['user_allowsmile']) || empty($config['enable_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $user_cache[$poster_id]['sig']) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $user_cache[$poster_id]['sig']);

				if (count($censors))
				{
					$user_sig = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $user_sig . '<'), 1, -1));
				}

				$user_cache[$poster_id]['sig'] = '<br />_________________<br />' . nl2br($user_cache[$poster_id]['sig']);
			}
			else
			{
				$user_cache[$poster_id]['sig'] = '';
			}
		*/
		
		$message = (empty($smilies) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $message);
	
		return($message);
	}

	// Submit Post
	function submit_post($mode, $message, $subject, $username, $topic_type, $bbcode_uid, $poll, $misc_info)
	{
		global $db, $auth, $user, $config, $phpEx, $SID, $template;

		$search = new fulltext_search();
		$current_time = time();

		$db->sql_transaction();

		// Initial Topic table info
		if ( ($mode == 'post') || ($mode == 'edit' && $misc_info['topic_first_post_id'] == $misc_info['post_id']))
		{
			$topic_sql = array(
				'forum_id' 					=> $misc_info['forum_id'],
				'topic_title' 				=> stripslashes($subject),
				'topic_time'				=> $current_time,
				'topic_type'				=> $topic_type,
				'topic_approved'			=> (($misc_info['enable_moderate']) && !$auth->acl_gets('f_ignorequeue', 'm_', 'a_', $misc_info['forum_id'])) ? 0 : 1, 
				'icon_id'					=> $misc_info['icon_id'],
				'topic_poster'				=> intval($user->data['user_id']), 
				'topic_first_poster_name'	=> ($username != '') ? stripslashes($username) : (($user->data['user_id'] == ANONYMOUS) ? '' : stripslashes($user->data['username'])), 
			);

			if (!empty($poll['poll_options']))
			{
				$topic_sql = array_merge($topic_sql, array(
					'poll_title'			=> stripslashes($poll['poll_title']),
					'poll_start'			=> (!empty($poll['poll_start'])) ? $poll['poll_start'] : $current_time,
					'poll_length'			=> $poll['poll_length'] * 3600
				));
			}
			$sql = ($mode == 'post') ? 'INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $topic_sql) : 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $topic_sql) . ' WHERE topic_id = ' . $misc_info['topic_id'];
			$db->sql_query($sql);

			$misc_info['topic_id'] = ($mode == 'post') ? $db->sql_nextid() : $misc_info['topic_id'];
		}

		// Post table info
		$post_sql = array(
			'topic_id' 			=> $misc_info['topic_id'],
			'forum_id' 			=> $misc_info['forum_id'],
			'poster_id' 		=> ($mode == 'edit') ? $misc_info['poster_id'] : intval($user->data['user_id']),
			'post_username'		=> ($username != '') ? stripslashes($username) : '', 
			'post_subject'		=> stripslashes($subject),
			'icon_id'			=> $misc_info['icon_id'], 
			'poster_ip' 		=> $user->ip,
			'post_time' 		=> $current_time,
			'post_approved' 	=> ($misc_info['enable_moderate'] && !$auth->acl_gets('f_ignorequeue', 'm_', 'a_', $misc_info['forum_id'])) ? 0 : 1,
			'post_edit_time' 	=> ($mode == 'edit' && $misc_info['poster_id'] == $user->data['user_id']) ? $current_time : 0,
			'enable_sig' 		=> $misc_info['enable_html'],
			'enable_bbcode' 	=> $misc_info['enable_bbcode'],
			'enable_html' 		=> $misc_info['enable_html'],
			'enable_smilies' 	=> $misc_info['enable_smilies'],
			'enable_magic_url' 	=> $misc_info['enable_urls'],
			'bbcode_uid'		=> $bbcode_uid,
		);

		if ($mode != 'edit' || $misc_info['message_md5'] != $misc_info['post_checksum'])
		{
			$post_sql = array_merge($post_sql, array(
				'post_checksum' => $misc_info['message_md5'],
				'post_text' 	=> stripslashes($message), 
				'post_encoding' => $user->lang['ENCODING'] 
			));
		}
		$sql = ($mode == 'edit' && $misc_info['poster_id'] == intval($user->data['user_id'])) ? 'UPDATE ' . POSTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $post_sql) . ' , post_edit_count = post_edit_count + 1 WHERE post_id = ' . $misc_info['post_id'] : 'INSERT INTO ' . POSTS_TABLE . ' ' . $db->sql_build_array('INSERT', $post_sql);
		$db->sql_query($sql);

		$misc_info['post_id'] = ($mode == 'edit') ? $misc_info['post_id'] : $db->sql_nextid();

		// poll options
		if (!empty($poll['poll_options']))
		{
			$cur_poll_options = array();
			if (!empty($poll['poll_start']) && $mode == 'edit')
			{
				$sql = "SELECT * FROM " . POLL_OPTIONS_TABLE . " 
					WHERE topic_id = " . $misc_info['topic_id'] . "
					ORDER BY poll_option_id";
				$result = $db->sql_query($sql);

				while ($cur_poll_options[] = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);
			}

			for ($i = 0; $i < sizeof($poll['poll_options']); $i++)
			{
				if (trim($poll['poll_options'][$i]) != '')
				{
					if (empty($cur_poll_options[$i]))
					{
						$sql = "INSERT INTO " . POLL_OPTIONS_TABLE . "  (topic_id, poll_option_text)
							VALUES (" . $misc_info['topic_id'] . ", '" . $db->sql_escape($poll['poll_options'][$i]) . "')";
						$db->sql_query($sql);
					}
					else if ($poll['poll_options'][$i] != $cur_poll_options[$i])
					{
						$sql = "UPDATE " . POLL_OPTIONS_TABLE . " 
							SET poll_option_text = '" . $db->sql_escape($poll['poll_options'][$i]) . "'
							WHERE poll_option_id = " . $cur_poll_options[$i]['poll_option_id'];
						$db->sql_query($sql);
					}
				}
			}
		}

		// Fulltext parse
		if ($mode != 'edit' || $misc_info['message_md5'] != $misc_info['post_checksum'])
		{
			$result = $search->add($mode, $misc_info['post_id'], $message, $subject);
		}

		// Sync forums, topics and users ...
		if ($mode != 'edit')
		{
			// Update forums: last post info, topics, posts ... we need to update
			// each parent too ...
			$forum_ids = $misc_info['forum_id'];
			if (!empty($misc_info['forum_parents']))
			{
				$misc_info['forum_parents'] = unserialize($misc_info['forum_parents']);
				foreach ($misc_info['forum_parents'] as $parent_forum_id => $parent_name)
				{
					$forum_ids .= ', ' . $parent_forum_id;
				}
			}

			$forum_topics_sql = ($mode == 'post') ? ', forum_topics = forum_topics + 1' : '';
			$forum_sql = array(
				'forum_last_post_id' 	=> $misc_info['post_id'],
				'forum_last_post_time' 	=> $current_time,
				'forum_last_poster_id' 	=> intval($user->data['user_id']),
				'forum_last_poster_name'=> ($user->data['user_id'] == ANONYMOUS) ? stripslashes($username) : $user->data['username'],
			);

			$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $forum_sql) . ', forum_posts = forum_posts + 1' . $forum_topics_sql . ' WHERE forum_id IN (' . $forum_ids . ')';
			$db->sql_query($sql);

			// Update topic: first/last post info, replies
			$topic_sql = array(
				'topic_last_post_id' 	=> $misc_info['post_id'],
				'topic_last_post_time' 	=> $current_time,
				'topic_last_poster_id' 	=> intval($user->data['user_id']),
				'topic_last_poster_name'=> ($username != '') ? stripslashes($username) : (($user->data['user_id'] == ANONYMOUS) ? '' : stripslashes($user->data['username'])),
			);

			if ($mode == 'post')
			{
				$topic_sql = array_merge($topic_sql, array(
					'topic_first_post_id' 		=> $misc_info['post_id'],
				));
			}

			$topic_replies_sql = ($mode == 'reply') ? ', topic_replies = topic_replies + 1' : '';
			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $topic_sql) . $topic_replies_sql . ' WHERE topic_id = ' . $misc_info['topic_id'];
			$db->sql_query($sql);

			// Update user post count ... if appropriate
			if (!empty($misc_info['enable_post_count']) && $user->data['user_id'] != ANONYMOUS)
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_posts = user_posts + 1
					WHERE user_id = ' . intval($user->data['user_id']);
				$db->sql_query($sql);
			}

			// post counts for index, etc.
			if ($mode == 'post')
			{
				set_config('num_topics', $config['num_topics'] + 1, TRUE);
			}

			set_config('num_posts', $config['num_posts'] + 1, TRUE);
		}

		// Topic Notification
		if ((!$misc_info['notify_set']) && ($misc_info['notify']))
		{
			$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id)
				VALUES (" . $user->data['user_id'] . ", " . $misc_info['topic_id'] . ")";
			$db->sql_query($sql);
		}
		else if (($misc_info['notify_set']) && (!$misc_info['notify']))
		{
			$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
				WHERE user_id = " . $user->data['user_id'] . "
					AND topic_id = " . $misc_info['topic_id'];
			$db->sql_query($sql);
		}
		
		// Mark this topic as read and posted to.
		$mark_mode = ($mode == 'reply' || $mode == 'quote') ? 'post' : 'topic';
		markread($mark_mode, $misc_info['forum_id'], $misc_info['topic_id'], $misc_info['post_id']);

		$db->sql_transaction('commit');

		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="5; url=viewtopic.' . $phpEx . $SID . '&amp;f=' . $misc_info['forum_id'] . '&amp;p=' . $misc_info['post_id'] . '#' . $misc_info['post_id'] . '">')
		);

		$message = (!empty($misc_info['enable_moderate'])) ? 'POST_STORED_MOD' : 'POST_STORED';
		$message = $user->lang[$message] . '<br /><br />' . sprintf($user->lang['VIEW_MESSAGE'], '<a href="viewtopic.' . $phpEx . $SID .'&p=' . $misc_info['post_id'] . '#' . $misc_info['post_id'] . '">', '</a>') . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID .'&amp;f=' . $misc_info['forum_id'] . '">', '</a>');
		trigger_error($message);
	}
}

// Parses a given message and updates/maintains the fulltext tables
class fulltext_search
{
	function split_words(&$text)
	{
		global $user, $config;

		static $drop_char_match, $drop_char_replace, $stopwords, $synonyms;

		if (empty($drop_char_match))
		{
			$drop_char_match =   array('^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!', '*');
			$drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ', ' ',  ' ', ' ', ' ');
			$stopwords = @file($user->lang_path . '/search_stopwords.txt');
			$synonyms = @file($user->lang_path . '/search_synonyms.txt');
		}

		$match = array();
		// New lines, carriage returns
		$match[] = "#[\n\r]+#";
		// NCRs like &nbsp; etc.
		$match[] = '#&[\#a-z0-9]+?;#i';
		// URL's
		$match[] = '#\b[\w]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?#';
		// BBcode
		$match[] = '#\[img:[a-z0-9]{10,}\].*?\[\/img:[a-z0-9]{10,}\]#';
		$match[] = '#\[\/?url(=.*?)?\]#';
		$match[] = '#\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]#';
		// Sequences < min_search_chars & < max_search_chars
		$match[] = '#\b([a-z0-9]{1,' . $config['min_search_chars'] . '}|[a-z0-9]{' . $config['max_search_chars'] . ',})\b#is';

		$text = preg_replace($match, ' ', ' ' . strtolower($text) . ' ');

		// Filter out non-alphabetical chars
		$text = str_replace($drop_char_match, $drop_char_replace, $text);

		if (!empty($stopwords_list))
		{
			$text = str_replace($stopwords, '', $text);
		}

		if (!empty($synonyms))
		{
			for ($j = 0; $j < count($synonyms); $j++)
			{
				list($replace_synonym, $match_synonym) = split(' ', trim(strtolower($synonyms[$j])));
				if ( $mode == 'post' || ( $match_synonym != 'not' && $match_synonym != 'and' && $match_synonym != 'or' ) )
				{
					$text =  preg_replace('#\b' . trim($match_synonym) . '\b#', ' ' . trim($replace_synonym) . ' ', $text);
				}
			}
		}

		preg_match_all('/\b([\w]+)\b/', $text, $split_entries);
		return array_unique($split_entries[1]);
	}

	function add(&$mode, &$post_id, &$message, &$subject)
	{
		global $config, $db;

//		$mtime = explode(' ', microtime());
//		$starttime = $mtime[1] + $mtime[0];

		// Split old and new post/subject to obtain array of 'words'
		$split_text = $this->split_words($message);
		$split_title = ($subject) ? $this->split_words($subject) : array();

		$words = array();
		if ($mode == 'edit')
		{
			$sql = "SELECT w.word_id, w.word_text, m.title_match
				FROM " . SEARCH_WORD_TABLE . " w, " . SEARCH_MATCH_TABLE . " m
				WHERE m.post_id = " . intval($post_id) . "
					AND w.word_id = m.word_id";
			$result = $db->sql_query($sql);
			$cur_words = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$which = ($row['title_match']) ? 'title' : 'post';
				$cur_words[$which][$row['word_text']] = $row['word_id'];
			}
			$db->sql_freeresult($result);

			$words['add']['post'] = array_diff($split_text, array_keys($cur_words['post']));
			$words['add']['title'] = array_diff($split_title, array_keys($cur_words['title']));
			$words['del']['post'] = array_diff(array_keys($cur_words['post']), $split_text);
			$words['del']['title'] = array_diff(array_keys($cur_words['title']), $split_title);
		}
		else
		{
			$words['add']['post'] = $split_text;
			$words['add']['title'] = $split_title;
			$words['del']['post'] = array();
			$words['del']['title'] = array();
		}
		unset($split_text);
		unset($split_title);

		// Get unique words from the above arrays
		$unique_add_words = array_unique(array_merge($words['add']['post'], $words['add']['title']));

		// We now have unique arrays of all words to be added and removed and
		// individual arrays of added and removed words for text and title. What
		// we need to do now is add the new words (if they don't already exist)
		// and then add (or remove) matches between the words and this post
		if (sizeof($unique_add_words))
		{
			$sql = "SELECT word_id, word_text
				FROM " . SEARCH_WORD_TABLE . "
				WHERE word_text IN (" . implode(', ', preg_replace('#^(.*)$#', '\'\1\'', $unique_add_words)) . ")";
			$result = $db->sql_query($sql);

			$word_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$word_ids[$row['word_text']] = $row['word_id'];
			}
			$db->sql_freeresult($result);

			$new_words = array_diff($unique_add_words, array_keys($word_ids));
			unset($unique_add_words);

			if (sizeof($new_words))
			{
				switch (SQL_LAYER)
				{
					case 'postgresql':
					case 'msaccess':
					case 'mssql-odbc':
					case 'oracle':
					case 'db2':
						foreach ($new_words as $word)
						{
							$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text)
								VALUES ('" . $word . "')";
							$db->sql_query($sql);
						}

						break;
					case 'mysql':
					case 'mysql4':
						$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text)
							VALUES " . implode(', ', preg_replace('#^(.*)$#', '(\'\1\')',  $new_words));
						$db->sql_query($sql);
						break;
					case 'mssql':
						$sql = "INSERT INTO " . SEARCH_WORD_TABLE . " (word_text)
							VALUES " . implode(' UNION ALL ', preg_replace('#^(.*)$#', 'SELECT \'\1\'',  $new_words));
						$db->sql_query($sql);
						break;
				}
			}
			unset($new_words);
		}

		foreach ($words['del'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			$sql = '';
			if (sizeof($word_ary))
			{
				foreach ($word_ary as $word)
				{
					$sql .= (($sql != '') ? ', ' : '') . $cur_words[$word_in][$word];
				}
				$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . " WHERE word_id IN ($sql) AND post_id = " . intval($post_id) . " AND title_match = $title_match";
				$db->sql_query($sql);
			}
		}

		foreach ($words['add'] as $word_in => $word_ary)
		{
			$title_match = ( $word_in == 'title' ) ? 1 : 0;

			if (sizeof($word_ary))
			{
				$sql = "INSERT INTO " . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) SELECT $post_id, word_id, $title_match FROM " . SEARCH_WORD_TABLE . " WHERE word_text IN (" . implode(', ', preg_replace('#^(.*)$#', '\'\1\'', $word_ary)) . ")";
				$db->sql_query($sql);
			}
		}

		unset($words);

//		$mtime = explode(' ', microtime());
//		echo "Search parser time taken >> " . ($mtime[1] + $mtime[0] - $starttime);

		// Run the cleanup infrequently, once per session cleanup
		if ($config['search_last_gc'] < time() - $config['search_gc'])
		{
//			$this->search_tidy();
		}
	}

	// Tidy up indexes, tag 'common words', remove
	// words no longer referenced in the match table, etc.
	function search_tidy()
	{
		global $db;

		// Remove common (> 60% of posts ) words
		$result = $db->sql_query("SELECT SUM(forum_posts) AS total_posts FROM " . FORUMS_TABLE);

		$row = $db->sql_fetchrow($result);

		if ($row['total_posts'] >= 100)
		{
			$sql = "SELECT word_id
				FROM " . SEARCH_MATCH_TABLE . "
				GROUP BY word_id
				HAVING COUNT(word_id) > " . floor($row['total_posts'] * 0.6);
			$result = $db->sql_query($sql);

			$in_sql = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$in_sql .= (( $in_sql != '') ? ', ' : '') . $row['word_id'];
			}
			$db->sql_freeresult($result);

			if ($in_sql)
			{
				$sql = "UPDATE " . SEARCH_WORD_TABLE . "
					SET word_common = " . TRUE . "
					WHERE word_id IN ($in_sql)";
				$db->sql_query($sql);

				$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "
					WHERE word_id IN ($in_sql)";
				$db->sql_query($sql);
			}
		}

		// Remove words with no matches ... this is a potentially nasty query
		$sql = "SELECT w.word_id
			FROM ( " . SEARCH_WORD_TABLE . " w
			LEFT JOIN " . SEARCH_MATCH_TABLE . " m ON w.word_id = m.word_id
				AND m.word_id IS NULL
			GROUP BY m.word_id";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$in_sql = '';
			do
			{
				$in_sql .= ', ' . $row['word_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			$sql = 'DELETE FROM ' . SEARCH_WORD_TABLE . '
				WHERE word_id IN (' . substr($in_sql, 2) . ')';
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);
	}
}

?>