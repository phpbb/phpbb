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

/*
	TODO list:
	- fix [flash], add width/height parameters?
	- check that PHP syntax highlightning works well
	- add other languages?
	- add validation regexp to [email], [flash]
	- add validation regexp to [quote] with username
	- add ACL check for [img]/[flash]/others (what to do when an unauthorised tag is found? do nothing/return an error message?)
*/

// case-insensitive strpos() - needed for some functions
if (!function_exists('stripos'))
{
	function stripos($haystack, $needle)
	{
		if (preg_match('#' . preg_quote($needle, '#') . '#i', $haystack, $m))
		{
			return strpos($haystack, $m[0]);
		}

		return FALSE;
	}
}

// Main message parser for posting, pm, etc. takes raw message
// and parses it for attachments, html, bbcode and smilies
class parse_message
{
	var $bbcode_tpl = null;
	var $message_mode = 0; // MSG_POST/MSG_PM
	var $bbcode_uid = '';
	var $bbcode_bitfield = 0;
	var $bbcode_array = array();
	var $message = '';

	function parse_message($message_type)
	{
		$this->message_mode = $message_type;
		$this->bbcode_uid = substr(md5(time()), 0, BBCODE_UID_LEN);
	}
	
	function parse($html, $bbcode, $url, $smilies)
	{
		global $config, $db, $user;

		$warn_msg = '';

		// Do some general 'cleanup' first before processing message,
		// e.g. remove excessive newlines(?), smilies(?)
		$match = array('#sid=[a-z0-9]*?&?#', "#([\r\n][\s]+){3,}#");
		$replace = array('', "\n\n");

		$this->message = trim(preg_replace($match, $replace, $this->message));

		// Message length check
		if (!strlen($this->message) || (intval($config['max_post_chars']) && strlen($this->message) > intval($config['max_post_chars'])))
		{
			$warn_msg .= (($warn_msg != '') ? '<br />' : '') . (!strlen($this->message)) ? $user->lang['TOO_FEW_CHARS'] : $user->lang['TOO_MANY_CHARS'];
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
				if (preg_match_all('#('. preg_quote($row['code'], '#') . ')#', $this->message, $matches))
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

		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->html($html);
		if ($bbcode)
		{
			$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->bbcode();
		}
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->emoticons($smilies);
		$warn_msg .= (($warn_msg != '') ? '<br />' : '') . $this->magic_url($url);

		return $warn_msg;
	}

	function html($html)
	{
		global $config;

		$this->message = str_replace(array('<', '>'), array('&lt;', '&gt;'), $this->message);

		if ($html)
		{
			// If $html is true then "allowed_tags" are converted back from entity
			// form, others remain
			$allowed_tags = split(',', $config['allow_html_tags']);

			if (sizeof($allowed_tags))
			{
				$this->message = preg_replace('#&lt;(\/?)(' . str_replace('*', '.*?', implode('|', $allowed_tags)) . ')&gt;#is', '<\1\2>', $this->message);
			}
		}

		return;
	}

	function bbcode()
	{
		if (empty($this->bbcode_array))
		{
			$this->bbcode_init();
		}

		$this->bbcode_bitfield = 0;
		$size = strlen($this->message);
		foreach ($this->bbcode_array as $bbcode_id => $row)
		{
			$parse = FALSE;
			foreach ($row as $regex => $replacement)
			{
				$this->message = preg_replace($regex, $replacement, $this->message);
			}

			// Since we add bbcode_uid to all tags, the message length will increase whenever a tag is found
			$new_size = strlen($this->message);
			if ($size != $new_size)
			{
				$this->bbcode_bitfield += pow(2, $bbcode_id);
				$size = $new_size;
			}
		}
	}

	function bbcode_init()
	{
		// Always parse [code] first
		// [quote] moved to the second position
		$this->bbcode_array = array(
			8	=> array('#\[code(?:=([a-z]+))?\](.+\[/code\])#ise'	=>	"\$this->bbcode_code('\\1', '\\2')"),
			0	=> array('#\[quote(?:="(.*?)")?\](.+?)\[/quote\]#ise'=>	"'[quote:" . $this->bbcode_uid . "' . \$this->bbcode_quote_username('\\1') . ']\\2[/quote:" . $this->bbcode_uid . "]'"),
// TODO: validation regexp
			11	=> array('#\[flash\](.*?)\[/flash\]#i'				=>	'[flash:' . $this->bbcode_uid . ']\1[/flash:' . $this->bbcode_uid . ']'),
			10	=> array('#\[email(=.*?)?\](.*?)\[/email\]#ise'		=>	"\$this->validate_email('\\1', '\\2')"),
			9	=> array('#\[list(=[a-z|0-1]+)?\].*\[/list\]#ise'	=>	"\$this->bbcode_list('\\0')"),
			7	=> array('#\[u\](.*?)\[/u\]#is'						=>	'[u:' . $this->bbcode_uid . ']\1[/u:' . $this->bbcode_uid . ']'),
			6	=> array('!\[color=(#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/color\]!is'
																	=>	'[color=\1:' . $this->bbcode_uid . ']\2[/color:' . $this->bbcode_uid . ']'),
			5	=> array('#\[size=([\-\+]?[1-2]?[0-9])\](.*?)\[/size\]#is'
																	=>	'[size=\1:' . $this->bbcode_uid . ']\2[/size:' . $this->bbcode_uid . ']'),
			4	=> array('#\[img\](https?://)([a-z0-9\-\.,\?!%\*_:;~\\&$@/=\+]+)\[/img\]#i'
																	=>	'[img:' . $this->bbcode_uid . ']\1\2[/img:' . $this->bbcode_uid . ']'),
			3	=> array('#\[url=?(.*?)?\](.*?)\[/url\]#ise'		=>	"\$this->validate_url('\\1', '\\2')"),
			2	=> array('#\[i\](.*?)\[/i\]#is'						=>	'[i:' . $this->bbcode_uid . ']\1[/i:' . $this->bbcode_uid . ']'),
			1	=> array('#\[b\](.*?)\[/b\]#is'						=>	'[b:' . $this->bbcode_uid . ']\1[/b:' . $this->bbcode_uid . ']')
		);

/**************
		global $db;
		$result = $db->sql_query('SELECT bbcode_id, first_pass_regexp, first_pass_replacement FROM ' . BBCODES_TABLE);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->bbcode_array[$row['bbcode_id']] = array($row['first_pass_regexp'] => $row['first_pass_replacement']);
		}
**************/
	}


	function bbcode_quote_username($username)
	{
		if (!$username)
		{
			return '';
		}

		// Will do some stuff at some point (will hopefully prevent from breaking out of quotes)
		$username = stripslashes($username);
		return '="' . $username . '"';
	}

	// Expects the argument to start right after the opening [code] tag and to end with [/code]
	function bbcode_code($type, $in)
	{
		$str_from = array('<', '>', '"', ':', '[', ']', '(', ')', '{', '}', '.', '@');
		$str_to = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;', '&#46;', '&#64;');

		// if I remember correctly, preg_replace() will slash passed vars
		$in = stripslashes($in);
		$out = '';

		do
		{
			$pos = stripos($in, '[/code]') + 7;
			$buffer = substr($in, 0, $pos);
			$in = substr($in, $pos);

			// $buffer contains everything that was between code tags (including the ending tag) but we're trying to grab as much extra text as possible, as long as it does not contain open [code] tags
			while ($in)
			{
				$pos = stripos($in, '[/code]') + 7;
				$sub_buffer = substr($in, 0, $pos);

				if (preg_match('#\[code(?:=([a-z]+))?\]#i', $sub_buffer))
				{
					break;
				}
				else
				{
					$in = substr($in, $pos);
					$buffer .= $sub_buffer;
				}
			}

			$buffer = substr($buffer, 0, -7);
			switch ($type)
			{
				case 'php':
					$out .= '[code=php:' . $this->bbcode_uid . ']' . str_replace($str_from, $str_to, $buffer) . '[/code:' . $this->bbcode_uid . ']';
				break;
			
				default:
					$out .= '[code:' . $this->bbcode_uid . ']' . str_replace($str_from, $str_to, $buffer) . '[/code:' . $this->bbcode_uid . ']';

			}

			if (preg_match('#(.*?)\[code(?:=[a-z]+)?\](.+)#is', $in, $m))
			{
				$out .= $m[1];
				$in = $m[2];
			}
		}
		while ($in);

		return $out;
	}

	// Expects the argument to start with a tag
	function bbcode_list($in)
	{
		// $tok holds characters to stop at. Since the string starts with a '[' we'll get everything up to the first ']' which should be the opening [list] tag
		$tok = ']';
		$out = '[';

		$in = substr(stripslashes($in), 1);
		$close_tags = array();

		do
		{
			$pos = strlen($in);
			for ($i = 0; $i < strlen($tok); ++$i)
			{
				$tmp_pos = strpos($in, $tok{$i});
				if ($tmp_pos !== FALSE && $tmp_pos < $pos)
				{
					$pos = $tmp_pos;
				}
			}

			$buffer = substr($in, 0, $pos);
			$tok = $in{$pos};
			$in = substr($in, $pos + 1);

			if ($tok == ']')
			{
				// if $tok is ']' the buffer holds a tag

				if ($buffer == '/list' && count($close_tags))
				{
					// valid [/list] tag
					$tag = array_pop($close_tags);
					$out .= $tag;
					$tok = '[';
				}
				elseif (preg_match('/list(=?(?:[0-9]|[a-z]|))/i', $buffer, $m))
				{
					// sub-list, add a closing tag
					array_push($close_tags, (($m[1]) ? '/list:o:' . $this->bbcode_uid : '/list:u:' . $this->bbcode_uid));
					$out .= $buffer . ':' . $this->bbcode_uid . ']';
					$tok = '[';
				}
				else
				{
					if ($buffer == '*' && count($close_tags))
					{
						// the buffer holds a bullet tag and we have a [list] tag open
						$buffer = '*:' . $this->bbcode_uid;
					}

					$out .= $buffer . $tok;
					$tok = '[]';
				}
			}
			else
			{
				// Not within a tag, just add buffer to the return string

				$out .= $buffer . $tok;
				$tok = ($tok == '[') ? ']' : '[]';
			}
		}
		while ($in);

		// do we have some tags open? close them now
		if (count($close_tags))
		{
			$out .= '[' . implode('][', $close_tags) . ']';
		}

		return $out;
	}

	function validate_email($var1, $var2)
	{
		$var1 = stripslashes($var1);
		$var2 = stripslashes($var2);

		$retval = '[email' . $var1 . ':' . $this->bbcode_uid . ']' . $var2 . '[/email:' . $this->bbcode_uid . ']';
		return $retval;
	}

	function validate_url($var1, $var2)
	{
		$url = (empty($var1)) ? stripslashes($var2) : stripslashes($var1);

		// Put validation regexps here
		$valid = FALSE;
		if (preg_match('#^http(s?)://#i', $url))
		{
			$valid = TRUE;
		}
		if ($valid)
		{
			return (empty($var1)) ? '[url:' . $this->bbcode_uid . ']' . $url . '[/url:' . $this->bbcode_uid . ']' : "[url=$url:" . $this->bbcode_uid . ']' . stripslashes($var2) . '[/url:' . $this->bbcode_uid . ']';
		}
		return '[url' . $var1 . ']' . stripslashes($var2) . '[/url]';
	}

	// Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
	// Cuts down displayed size of link if over 50 chars, turns absolute links
	// into relative versions when the server/script path matches the link
	function magic_url($url)
	{
		global $config;

		if ($url)
		{
			$server_protocol = ( $config['cookie_secure'] ) ? 'https://' : 'http://';
			$server_port = ( $config['server_port'] <> 80 ) ? ':' . trim($config['server_port']) . '/' : '/';

			$match = array();
			$replace = array();

			// relative urls for this board
			$match[] = '#' . $server_protocol . trim($config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '\1', trim($config['script_path'])) . '/([^ \t\n\r <"\']+)#i';
			$replace[] = '<!-- l --><a href="\1" target="_blank">\1</a><!-- l -->';

			// matches a xxxx://aaaaa.bbb.cccc. ...
			$match[] = '#(^|[\n ])([\w]+?://.*?[^ \t\n\r<"]*)#ie';
			$replace[] = "'\\1<!-- m --><a href=\"\\2\" target=\"_blank\">' . ((strlen('\\2') > 55) ? substr('\\2', 0, 39) . ' ... ' . substr('\\2', -10) : '\\2') . '</a><!-- m -->'";

			// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
			$match[] = '#(^|[\n ])(www\.[\w\-]+\.[\w\-.\~]+(?:/[^ \t\n\r<"]*)?)#ie';
			$replace[] = "'\\1<!-- w --><a href=\"http://\\2\" target=\"_blank\">' . ((strlen('\\2') > 55) ? substr(str_replace(' ', '%20', '\\2'), 0, 39) . ' ... ' . substr('\\2', -10) : '\\2') . '</a><!-- w -->'";

			// matches an email@domain type address at the start of a line, or after a space.
			$match[] = '#(^|[\n ])([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)#ie';
			$replace[] = "'\\1<!-- e --><a href=\"mailto:\\2\">' . ((strlen('\\2') > 55) ? substr('\\2', 0, 39) . ' ... ' . substr('\\2', -10) : '\\2') . '</a><!-- e -->'";

			$this->message = preg_replace($match, $replace, $this->message);
		}
	}

	function emoticons($smile)
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

			$this->message = preg_replace($match, $replace, ' ' . $this->message . ' ');
		}
		$db->sql_freeresult($result);

		return;
	}

	function parse_attachments($mode, $post_id, $submit, $preview, $refresh, &$attachment_data)
	{
		global $config, $_FILE, $_POST, $auth, $user;

		$error = false;
		$error_msg = '';

		$num_attachments = count($attachment_data['attach_id']);
		$attachment_data['filecomment'] = ( isset($_POST['filecomment']) ) ? trim( strip_tags($_POST['filecomment'])) : '';
		$attachment_data['filename'] = ( $_FILES['fileupload']['name'] != 'none' ) ? trim($_FILES['fileupload']['name']) : '';
		
		$add_file = ( isset($_POST['add_file']) ) ? true : false;
		$delete_file = ( isset($_POST['delete_file']) ) ? true : false;
		$edit_comment = ( isset($_POST['edit_comment']) ) ? true : false;

		if ( $submit && ($mode == 'post' || $mode == 'reply' || $mode == 'edit') && $attachment_data['filename'] != '')
		{
			if ( $num_attachments < $config['max_attachments'] ) //|| $auth->acl_gets('m_', 'a_', $forum_id) )
			{
				$filedata = upload_attachment($attachment_data['filename']);
				
				if ($filedata['error'])
				{
					$error = true;
					$error_msg .= (!empty($error_msg)) ? '<br />' . $filedata['err_msg'] : $filedata['err_msg'];
				}

				if (($filedata['post_attach']) && (!$error))
				{
					array_unshift($attachment_data['physical_filename'], $filedata['destination_filename']);
					array_unshift($attachment_data['comment'], $attachment_data['filecomment']);
					array_unshift($attachment_data['real_filename'], $filedata['filename']);
					array_unshift($attachment_data['extension'], $filedata['extension']);
					array_unshift($attachment_data['mimetype'], $filedata['mimetype']);
					array_unshift($attachment_data['filesize'], $filedata['filesize']);
					array_unshift($attachment_data['filetime'], $filedata['filetime']);
					array_unshift($attachment_data['attach_id'], '-1');
					array_unshift($attachment_data['thumbnail'], $filedata['thumbnail']);

					$attachment_data['filecomment'] = '';

					// This Variable is set to FALSE here, because the Attachment Mod enter Attachments into the
					// Database in two modes, one if the id_list is -1 and the second one if post_attach is true
					// Since post_attach is automatically switched to true if an Attachment got added to the filesystem,
					// but we are assigning an id of -1 here, we have to reset the post_attach variable to FALSE.
					//
					// This is very relevant, because it could happen that the post got not submitted, but we do not
					// know this circumstance here. We could be at the posting page or we could be redirected to the entered
					// post. :)
					$filedata['post_attach'] = false;
				}
			}
			else
			{
				$error = true;
				$error_msg .= (!empty($error_msg)) ? '<br />' : '' . sprintf($user->lang['TOO_MANY_ATTACHMENTS'], $config['max_attachments']);
			}
		}

		if ($preview || $refresh || $error)
		{
			// Perform actions on temporary attachments
			if ($delete_file)
			{
				// store old values
				$actual_list = ( isset($_POST['attachment_list']) ) ? $_POST['attachment_list'] : array();
				$actual_comment_list = ( isset($_POST['comment_list']) ) ? $_POST['comment_list'] : array();
				$actual_filename_list = ( isset($_POST['filename_list']) ) ? $_POST['filename_list'] : array();
				$actual_extension_list = ( isset($_POST['extension_list']) ) ? $_POST['extension_list'] : array();
				$actual_mimetype_list = ( isset($_POST['mimetype_list']) ) ? $_POST['mimetype_list'] : array();
				$actual_filesize_list = ( isset($_POST['filesize_list']) ) ? $_POST['filesize_list'] : array();
				$actual_filetime_list = ( isset($_POST['filetime_list']) ) ? $_POST['filetime_list'] : array();
				$actual_id_list = ( isset($_POST['attach_id_list']) ) ? $_POST['attach_id_list'] : array();
				$actual_thumbnail_list = ( isset($_POST['attach_thumbnail_list']) ) ? $_POST['attach_thumbnail_list'] : array();

				// clean values
				
				$attachment_data['physical_filename'] = array();
				$attachment_data['comment'] = array();
				$attachment_data['real_filename'] = array();
				$attachment_data['extension'] = array();
				$attachment_data['mimetype'] = array();
				$attachment_data['filesize'] = array();
				$attachment_data['filetime'] = array();
				$attachment_data['attach_id'] = array();
				$attachment_data['thumbnail'] = array();

				// restore values :)
				if( isset($_POST['attachment_list']) )
				{
					for ($i = 0; $i < count($actual_list); $i++)
					{
						$restore = false;
						if ($delete_file)
						{
							if (!isset($_POST['delete_file'][$actual_list[$i]]))
							{
								$restore = true;
							}
						}

						if ($restore)
						{
							$attachment_data['physical_filename'][] = $actual_list[$i];
							$attachment_data['comment'][] = $actual_comment_list[$i];
							$attachment_data['real_filename'][] = $actual_filename_list[$i];
							$attachment_data['extension'][] = $actual_extension_list[$i];
							$attachment_data['mimetype'][] = $actual_mimetype_list[$i];
							$attachment_data['filesize'][] = $actual_filesize_list[$i];
							$attachment_data['filetime'][] = $actual_filetime_list[$i];
							$attachment_data['attach_id'][] = $actual_id_list[$i];
							$attachment_data['thumbnail'][] = $actual_thumbnail_list[$i];
						}
						else
						{
							// delete selected attachment
							if ($actual_id_list[$i] == '-1')
							{
								phpbb_unlink($actual_list[$i]);

								if ($actual_thumbnail_list[$i] == 1)
								{
									phpbb_unlink('t_' . $actual_list[$i], 'thumbnail');
								}
							}
							else
							{
								delete_attachment($post_id, $actual_id_list[$i]);
							}
						}
					}
				}
			}
			else if ( ($edit_comment) || ($add_file) || ($preview) )
			{
				if ($edit_comment)
				{
					$actual_comment_list = ( isset($_POST['comment_list']) ) ? $_POST['comment_list'] : '';
				
					$attachment_data['comment'] = array();

					for ($i = 0; $i < count($attachment_data['physical_filename']); $i++)
					{
						$attachment_data['comment'][$i] = $actual_comment_list[$i];
					}
				}
				
				if ((($add_file) || ($preview) ) && ($attachment_data['filename'] != '') )
				{
					if ( $num_attachments < $config['max_attachments'] ) //|| $auth->acl_gets('m_', 'a_', $forum_id) )
					{
						$filedata = upload_attachment($attachment_data['filename']);
				
						if ($filedata['error'])
						{
							$error = true;
							$error_msg .= (!empty($error_msg)) ? '<br />' . $filedata['err_msg'] : $filedata['err_msg'];
						}

						if (!$error)
						{
							array_unshift($attachment_data['physical_filename'], $filedata['destination_filename']);
							array_unshift($attachment_data['comment'], $attachment_data['filecomment']);
							array_unshift($attachment_data['real_filename'], $filedata['filename']);
							array_unshift($attachment_data['extension'], $filedata['extension']);
							array_unshift($attachment_data['mimetype'], $filedata['mimetype']);
							array_unshift($attachment_data['filesize'], $filedata['filesize']);
							array_unshift($attachment_data['filetime'], $filedata['filetime']);
							array_unshift($attachment_data['attach_id'], '-1');
							array_unshift($attachment_data['thumbnail'], $filedata['thumbnail']);

							$attachment_data['filecomment'] = '';
						}
					}
					else
					{
						$error = true;
						$error_msg .= (!empty($error_msg)) ? '<br />' : '' . sprintf($user->lang['TOO_MANY_ATTACHMENTS'], $config['max_attachments']);
					}
				}
			}
		}

		return ($error_msg);
	}

	// Parse Poll
	function parse_poll(&$poll, $poll_data)
	{
		global $auth, $forum_id, $user, $config;

		// poll_options, poll_options_size
		$err_msg = '';

		// Process poll options
		if (!empty($poll_data['poll_option_text']) && (($auth->acl_get('f_poll', $forum_id) && !$poll_data['poll_last_vote']) || $auth->acl_get('m_edit', $forum_id)))
		{
			if (($result = $this->parse($poll_data['poll_option_text'], $poll_data['enable_html'], $poll_data['enable_bbcode'], $poll_data['bbcode_uid'], $poll_data['enable_urls'], $poll_data['enable_smilies'], false)) != '')
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result;
			}

			$poll['poll_options'] = explode("\n", trim($poll_data['poll_option_text']));
			$poll['poll_options_size'] = sizeof($poll['poll_options']);
			
			if (sizeof($poll['poll_options']) == 1)
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['TOO_FEW_POLL_OPTIONS'];
			}
			else if (sizeof($poll['poll_options']) > intval($config['max_poll_options']))
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['TOO_MANY_POLL_OPTIONS'];
			}
			else if (sizeof($poll['poll_options']) < $poll['poll_options_size'])
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['NO_DELETE_POLL_OPTIONS'];
			}

			$poll['poll_title'] = (!empty($poll_data['poll_title'])) ? trim(htmlspecialchars(strip_tags($poll_data['poll_title']))) : '';
			$poll['poll_length'] = (!empty($poll_data['poll_length'])) ? intval($poll_data['poll_length']) : 0;
		}
		$poll['poll_start'] = $poll_data['poll_start'];

		return ($err_msg);
	}
}

// Parses a given message and updates/maintains the fulltext tables
class fulltext_search
{
	function split_words(&$text)
	{
		global $user, $config;

		static $drop_char_match, $drop_char_replace, $stopwords, $synonyms;

		// Is the fulltext indexer disabled? If yes then we need not 
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (empty($config['load_search_upd']))
		{
			return;
		}

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

		// Is the fulltext indexer disabled? If yes then we need not 
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (empty($config['load_search_upd']))
		{
			return;
		}

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
		global $db, $config;

		// Is the fulltext indexer disabled? If yes then we need not 
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (empty($config['load_search_upd']))
		{
			return;
		}

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