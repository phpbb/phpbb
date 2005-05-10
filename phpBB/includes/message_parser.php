<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// case-insensitive strpos() - needed for some functions
if (!function_exists('stripos'))
{
	function stripos($haystack, $needle)
	{
		if (preg_match('#' . preg_quote($needle, '#') . '#i', $haystack, $m))
		{
			return strpos($haystack, $m[0]);
		}

		return false;
	}
}

if (!class_exists('bbcode'))
{
	include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
}

/**
* @package phpBB3
* BBCODE FIRSTPASS
* BBCODE first pass class (functions for parsing messages for db storage)
*/
class bbcode_firstpass extends bbcode
{
	var $message = '';
	var $warn_msg = array();
	var $parsed_items = array();

	// Parse BBCode
	function parse_bbcode()
	{
		if (!$this->bbcodes)
		{
			$this->bbcode_init();
		}

		global $user;
		$this->bbcode_bitfield = 0;

		$size = strlen($this->message);
		foreach ($this->bbcodes as $bbcode_name => $bbcode_data)
		{
			if (isset($bbcode_data['disabled']) && $bbcode_data['disabled'])
			{
				foreach ($bbcode_data['regexp'] as $regexp => $replacement)
				{
					if (preg_match($regexp, $this->message))
					{
						$this->warn_msg[] = $user->lang['UNAUTHORISED_BBCODE'] . '[' . $bbcode_name . ']';
						continue;
					}
				}
			}
			else
			{
				foreach ($bbcode_data['regexp'] as $regexp => $replacement)
				{
					$this->message = preg_replace($regexp, $replacement, $this->message);
				}
			}

			// Because we add bbcode_uid to all tags, the message length
			// will increase whenever a tag is found
			$new_size = strlen($this->message);
			if ($size != $new_size)
			{
				$this->bbcode_bitfield |= (1 << $bbcode_data['bbcode_id']);
				$size = $new_size;
			}
		}
	}

	function bbcode_init()
	{
		static $rowset;

		// This array holds all bbcode data. BBCodes will be processed in this
		// order, so it is important to keep [code] in first position and
		// [quote] in second position.
		$this->bbcodes = array(
			'code'		=>	array('bbcode_id' => 8, 'regexp' => array('#\[code(?:=([a-z]+))?\](.+\[/code\])#ise' => "\$this->bbcode_code('\$1', '\$2')")),
			'quote'		=>	array('bbcode_id' => 0, 'regexp' => array('#\[quote(?:=&quot;(.*?)&quot;)?\](.+)\[/quote\]#ise' => "\$this->bbcode_quote('\$0')")),
			'attachment'=>	array('bbcode_id' => 12, 'regexp' => array('#\[attachment=([0-9]+)\](.*?)\[/attachment\]#ise' => "\$this->bbcode_attachment('\$1', '\$2')")),
			'b'			=>	array('bbcode_id' => 1, 'regexp' => array('#\[b\](.*?)\[/b\]#ise' => "\$this->bbcode_strong('\$1')")),
			'i'			=>	array('bbcode_id' => 2, 'regexp' => array('#\[i\](.*?)\[/i\]#ise' => "\$this->bbcode_italic('\$1')")),
			'url'		=>	array('bbcode_id' => 3, 'regexp' => array('#\[url=?(.*?)?\](.*?)\[/url\]#ise' => "\$this->validate_url('\$1', '\$2')")),
			'img'		=>	array('bbcode_id' => 4, 'regexp' => array('#\[img\](https?://)([a-z0-9\-\.,\?!%\*_:;~\\&$@/=\+]+)\[/img\]#ie' => "\$this->bbcode_img('\$1\$2')")),
			'size'		=>	array('bbcode_id' => 5, 'regexp' => array('#\[size=([\-\+]?[1-2]?[0-9])\](.*?)\[/size\]#is' => "\$this->bbcode_size('\$1', '\$2')")),
			'color'		=>	array('bbcode_id' => 6, 'regexp' => array('!\[color=(#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/color\]!is' => "\$this->bbcode_color('\$1', '\$2')")),
			'u'			=>	array('bbcode_id' => 7, 'regexp' => array('#\[u\](.*?)\[/u\]#is' => "\$this->bbcode_underline('\$1')")),
			'list'		=>	array('bbcode_id' => 9, 'regexp' => array('#\[list(=[a-z|0-9|(?:disc|circle|square))]+)?\].*\[/list\]#ise' => "\$this->bbcode_parse_list('\$0')")),
			'email'		=>	array('bbcode_id' => 10, 'regexp' => array('#\[email=?(.*?)?\](.*?)\[/email\]#ise' => "\$this->validate_email('\$1', '\$2')")),
			'flash'		=>	array('bbcode_id' => 11, 'regexp' => array('#\[flash=([0-9]+),([0-9]+)\](.*?)\[/flash\]#ie' => "\$this->bbcode_flash('\$1', '\$2', '\$3')"))
		);

		$this->parsed_items = array('code' => 0, 'quote' => 0, 'attachment' => 0, 'url' => 0, 'email' => 0, 'img' => 0, 'flash' => 0);

		if (!is_array($rowset))
		{
			global $db;
			$rowset = array();

			$sql = 'SELECT bbcode_id, bbcode_tag, first_pass_match, first_pass_replace
				FROM ' . BBCODES_TABLE;

			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$rowset[] = $row;
			}
		}
		
		foreach ($rowset as $row)
		{
			$this->bbcodes[$row['bbcode_tag']] = array(
				'bbcode_id'	=> intval($row['bbcode_id']),
				'regexp'	=> array($row['first_pass_match'] => str_replace('$uid', $this->bbcode_uid, $row['first_pass_replace']))
			);
		}
	}

	function bbcode_size($stx, $in)
	{
		$in = trim($in);

		if (!$in)
		{
			return '';
		}

		return '[size' . $stx . ':' . $this->bbcode_uid . ']' . $in . '[/size:' . $this->bbcode_uid . ']';
	}

	function bbcode_color($stx, $in)
	{
		$in = trim($in);

		if (!$in)
		{
			return '';
		}

		return '[color=' . $stx . ':' . $this->bbcode_uid . ']' . $in . '[/color:' . $this->bbcode_uid . ']';
	}
	
	function bbcode_underline($in)
	{
		$in = trim($in);

		if (!$in)
		{
			return '';
		}

		return '[u:' . $this->bbcode_uid . ']' . $in . '[/u:' . $this->bbcode_uid . ']';
	}

	function bbcode_strong($in)
	{
		$in = trim($in);

		if (!$in)
		{
			return '';
		}

		return '[b:' . $this->bbcode_uid . ']' . $in . '[/b:' . $this->bbcode_uid . ']';
	}
	
	function bbcode_italic($in)
	{
		$in = trim($in);

		if (!$in)
		{
			return '';
		}

		return '[i:' . $this->bbcode_uid . ']' . $in . '[/i:' . $this->bbcode_uid . ']';
	}

	function bbcode_img($in)
	{
		$in = trim($in);
		
		if (!$in)
		{
			return '';
		}

		$this->parsed_items['img']++;

		$out = '[img:' . $this->bbcode_uid . ']' . $in . '[/img:' . $this->bbcode_uid . ']';
		return $out;
	}

	function bbcode_flash($width, $height, $in)
	{
		$in = trim($in);

		if (!$in)
		{
			return '';
		}

		$this->parsed_items['flash']++;
	
		$out = '[flash=' . $width . ',' . $height . ':' . $this->bbcode_uid . ']' . $in . '[/flash:' . $this->bbcode_uid . ']';
		return $out;
	}

	// Hardcode inline attachments [ia]
	function bbcode_attachment($stx, $in)
	{
		$in = trim($in);

		if (!$in)
		{
			return '';
		}

		$this->parsed_items['attachment']++;

		$out = '[attachment=' . $stx . ':' . $this->bbcode_uid . ']<!-- ia' . $stx . ' -->' . $in . '<!-- ia' . $stx . ' -->[/attachment:' . $this->bbcode_uid . ']';
		return $out;
	}

	// Expects the argument to start right after the opening [code] tag and to end with [/code]
	function bbcode_code($stx, $in)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$in = str_replace("\r\n", "\n", str_replace('\"', '"', trim($in)));

		if (!$in)
		{
			return '';
		}

		$this->parsed_items['code']++;
		
		// We remove the hardcoded elements from the code block here because it is not used in code blocks
		// Having it here saves us one preg_replace per message containing [code] blocks
		// Additionally, magic url parsing should go after parsing bbcodes, but for safety those are stripped out too...
		$htm_match = array(
			'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
			'#<!\-\- m \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- m \-\->#',
			'#<!\-\- w \-\-><a href="http:\/\/(.*?)" target="_blank">.*?</a><!\-\- w \-\->#',
			'#<!\-\- l \-\-><a href="(.*?)">.*?</a><!\-\- l \-\->#',
			'#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
			'#<!\-\- h \-\-><(.*?)><!\-\- h \-\->#',
			'#&\#([0-9]+);#',
		);
		$htm_replace = array('\1', '\1', '\1', '\1', '\1', '&lt;\1&gt;', '&amp;#\1;');

		$out = '';

		do
		{
			$pos = stripos($in, '[/code]') + 7;
			$code = substr($in, 0, $pos);
			$in = substr($in, $pos);
			
			// $code contains everything that was between code tags (including the ending tag) but we're trying to grab as much extra text as possible, as long as it does not contain open [code] tags
			while ($in)
			{
				$pos = stripos($in, '[/code]') + 7;
				$buffer = substr($in, 0, $pos);

				if (preg_match('#\[code(?:=([a-z]+))?\]#i', $buffer))
				{
					break;
				}
				else
				{
					$in = substr($in, $pos);
					$code .= $buffer;
				}
			}

			$code = substr($code, 0, -7);
//			$code = preg_replace('#^[\r\n]*(.*?)[\n\r\s\t]*$#s', '$1', $code);
			$code = preg_replace($htm_match, $htm_replace, $code);

			switch (strtolower($stx))
			{
				case 'php':
					$remove_tags = false;
					$str_from = array('&lt;', '&gt;');
					$str_to = array('<', '>');

					$code = str_replace($str_from, $str_to, $code);
					if (!preg_match('/^\<\?.*?\?\>/is', $code))
					{
						$remove_tags = true;
						$code = "<?php $code ?>";
					}

					$conf = array('highlight.bg', 'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string');
					foreach ($conf as $ini_var)
					{
						ini_set($ini_var, str_replace('highlight.', 'syntax', $ini_var));
					}

					// Because highlight_string is specialcharing the text (but we already did this before), we have to reverse this in order to get correct results
					$code = strtr($code, array_flip(get_html_translation_table(HTML_ENTITIES)));

					ob_start();
					highlight_string($code);
					$code = ob_get_contents();
					ob_end_clean();

					$str_from = array('<font color="syntax', '</font>', '<code>', '</code>','[', ']', '.', ':');
					$str_to = array('<span class="syntax', '</span>', '', '', '&#91;', '&#93;', '&#46;', '&#58');

					if ($remove_tags)
					{
						$str_from[] = '<span class="syntaxdefault">&lt;?php </span>';
						$str_to[] = '';
						$str_from[] = '<span class="syntaxdefault">&lt;?php ';
						$str_to[] = '<span class="syntaxdefault">';
					}

					$code = str_replace($str_from, $str_to, $code);
					$code = preg_replace('#^(<span class="[a-z_]+">)\n?(.*?)\n?(</span>)$#is', '$1$2$3', $code);

					if ($remove_tags)
					{
						$code = preg_replace('#(<span class="[a-z]+">)?\?&gt;</span>#', '', $code);
					}

					$code = preg_replace('#^<span class="[a-z]+"><span class="([a-z]+)">(.*)</span></span>#s', '<span class="$1">$2</span>', $code);
					$code = preg_replace('#(?:[\n\r\s\t]|&nbsp;)*</span>$#', '</span>', $code);

					$out .= "[code=$stx:" . $this->bbcode_uid . ']' . trim($code) . '[/code:' . $this->bbcode_uid . ']';
				break;

				default:
					$str_from = array('<', '>', '[', ']', '.', ':');
					$str_to = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58');

					$out .= '[code:' . $this->bbcode_uid . ']' . str_replace($str_from, $str_to, $code) . '[/code:' . $this->bbcode_uid . ']';
			}

			if (preg_match('#(.*?)\[code(?:=([a-z]+))?\](.+)#is', $in, $m))
			{
				$out .= $m[1];
				$stx = $m[2];
				$in = $m[3];
			}
		}
		while ($in);

		return $out;
	}

	// Expects the argument to start with a tag
	function bbcode_parse_list($in)
	{
		$in = trim($in);

		if (!$in)
		{
			return '';
		}
		
		$in = str_replace('\"', '"', $in);
		$out = '[';

		// Grab item_start with no item_end
		$in = preg_replace('#\[\*\](.*?)(\[\/list\]|\[list(=?(?:[0-9]|[a-z]|))\]|\[\*\])#is', '[*:' . $this->bbcode_uid . ']\1[/*:m:' . $this->bbcode_uid . ']\2', $in);

		// Grab them again as backreference
		$in = preg_replace('#\[\*\](.*?)(\[\/list\]|\[list(=?(?:[0-9]|[a-z]|))\]|\[\*\])(^\[\/*\])#is', '[*:' . $this->bbcode_uid . ']\1[/*:m:' . $this->bbcode_uid . ']\2', $in);
		
		// Grab end tag following start tag
		$in = preg_replace('#\[\/\*:m:' . $this->bbcode_uid . '\](\n|)\[\*\]#is', '[/*:m:' . $this->bbcode_uid . '][*:' . $this->bbcode_uid . ']', $in);
		
		// Replace end tag
		$in = preg_replace('#\[\/\*\]#i', '[/*:' . $this->bbcode_uid . ']', $in);

		// $tok holds characters to stop at. Since the string starts with a '[' we'll get everything up to the first ']' which should be the opening [list] tag
		$tok = ']';
		$out = '[';

		$in = substr($in, 1);
		$list_end_tags = array();

		do
		{
			$pos = strlen($in);
			for ($i = 0; $i < strlen($tok); ++$i)
			{
				$tmp_pos = strpos($in, $tok{$i});

				if ($tmp_pos !== false && $tmp_pos < $pos)
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
				if ($buffer == '/list' && sizeof($list_end_tags))
				{
					$out .= array_pop($list_end_tags) . ']';
					$tok = '[';
				}
				elseif (preg_match('#list(=?(?:[0-9]|[a-z]|))#i', $buffer, $m))
				{
					// sub-list, add a closing tag
					if (!$m[1] || preg_match('/^(disc|square|circle)$/i', $m[1]))
					{
						array_push($list_end_tags, '/list:u:' . $this->bbcode_uid);
					}
					else
					{
						array_push($list_end_tags, '/list:o:' . $this->bbcode_uid);
					}
					$out .= $buffer . ':' . $this->bbcode_uid . ']';
					$tok = '[';
				}
				else
				{
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

		if (sizeof($list_end_tags))
		{
			$out .= '[' . implode('][', $list_end_tags) . ']';
		}

		return $out;
	}

	// Expects the argument to start with a tag
	function bbcode_quote($in)
	{
		global $config, $user;

		$in = trim($in);

		if (!$in)
		{
			return '';
		}
		
		$tok = ']';
		$out = '[';

		// Add newline at the end and in front of each quote block to prevent parsing errors (urls, smilies, etc.)
		$in = preg_replace(array('#\[quote(=?.*?)\]([^\n])#is', '#([^\n])\[\/quote\]#is'), array("[quote\\1]\n\\2", "\\1\n[/quote]"), $in);
		$in = preg_replace(array('#\[quote(=?.*?)\]([^\n])#is', '#([^\n])\[\/quote\]#is'), array("[quote\\1]\n\\2", "\\1\n[/quote]"), $in);

		$in = substr(str_replace('\"', '"', $in), 1);
		$close_tags = $error_ary = array();
		$buffer = '';

		do
		{
			$pos = strlen($in);
			for ($i = 0; $i < strlen($tok); ++$i)
			{
				$tmp_pos = strpos($in, $tok{$i});
				if ($tmp_pos !== false && $tmp_pos < $pos)
				{
					$pos = $tmp_pos;
				}
			}

			$buffer .= substr($in, 0, $pos);
			$tok = $in{$pos};
			$in = substr($in, $pos + 1);

			if ($tok == ']')
			{
				if ($buffer == '/quote' && sizeof($close_tags))
				{
					// we have found a closing tag

					$out .= array_pop($close_tags) . ']';
					$tok = '[';
					$buffer = '';
				}
				elseif (preg_match('#^quote(?:=&quot;(.*?)&quot;)?$#is', $buffer, $m))
				{
					$this->parsed_items['quote']++;

					// the buffer holds a valid opening tag
					if ($config['max_quote_depth'] && sizeof($close_tags) >= $config['max_quote_depth'])
					{
						// there are too many nested quotes
						$error_ary['quote_depth'] = sprintf($user->lang['QUOTE_DEPTH_EXCEEDED'], $config['max_quote_depth']);

						$out .= $buffer . $tok;
						$tok = '[]';
						$buffer = '';

						continue;
					}

					array_push($close_tags, '/quote:' . $this->bbcode_uid);

					if (isset($m[1]) && $m[1])
					{
						$username = preg_replace('#\[(?!b|i|u|color|url|email|/b|/i|/u|/color|/url|/email)#iU', '&#91;$1', $m[1]);
						$end_tags = array();
						$error = false;

						preg_match_all('#\[((?:/)?(?:[a-z]+))#i', $username, $tags);
						foreach ($tags[1] as $tag)
						{
							if ($tag{0} != '/')
							{
								$end_tags[] = '/' . $tag;
							}
							else
							{
								$end_tag = array_pop($end_tags);
								if ($end_tag != $tag)
								{
									$error = true;
								}
								else
								{
									$error = false;
								}
							}
						}
						if ($error)
						{
							$username = str_replace('[', '&#91;', str_replace(']', '&#93;', $m[1]));
						}

						$out .= 'quote=&quot;' . $username . '&quot;:' . $this->bbcode_uid . ']';
					}
					else
					{
						$out .= 'quote:' . $this->bbcode_uid . ']';
					}

					$tok = '[';
					$buffer = '';
				}
				elseif (preg_match('#^quote=&quot;(.*?)#is', $buffer, $m))
				{
					// the buffer holds an invalid opening tag
					$buffer .= ']';
				}
				else
				{
					$out .= $buffer . $tok;
					$tok = '[]';
					$buffer = '';
				}
			}
			else
			{
				$out .= $buffer . $tok;
				$tok = ($tok == '[') ? ']' : '[]';
				$buffer = '';
			}
		}
		while ($in);

		if (sizeof($close_tags))
		{
			$out .= '[' . implode('][', $close_tags) . ']';
		}

		foreach ($error_ary as $error_msg)
		{
			$this->warn_msg[] = $error_msg;
		}

		return $out;
	}

	function validate_email($var1, $var2)
	{
		$txt = stripslashes($var2);
		$email = ($var1) ? stripslashes($var1) : stripslashes($var2);

		$validated = true;

		if (!preg_match('!([a-z0-9]+[a-z0-9\-\._]*@(?:(?:[0-9]{1,3}\.){3,5}[0-9]{1,3}|[a-z0-9]+[a-z0-9\-\._]*\.[a-z]+))!i', $email))
		{
			$validated = false;
		}

		if (!$validated)
		{
			return '[email' . (($var1) ? "=$var1" : '') . ']' . $var2 . '[/email]';
		}

		$this->parsed_items['email']++;
		
		if ($var1)
		{
			$retval = '[email=' . $email . ':' . $this->bbcode_uid . ']' . $txt . '[/email:' . $this->bbcode_uid . ']';
		}
		else
		{
			$retval = '[email:' . $this->bbcode_uid . ']' . $email . '[/email:' . $this->bbcode_uid . ']';
		}
		return $retval;
	}

	function validate_url($var1, $var2)
	{
		global $config;
		
		$var1 = trim($var1);
		$var2 = trim($var2);

		$url = ($var1) ? stripslashes($var1) : stripslashes($var2);
		$valid = false;

		if (!$url || ($var1 && !$var2))
		{
			return '';
		}

		$server_protocol = ( $config['cookie_secure'] ) ? 'https://' : 'http://';
		$server_port = ( $config['server_port'] <> 80 ) ? ':' . trim($config['server_port']) . '/' : '/';

		// relative urls for this board
		if (preg_match('#' . $server_protocol . trim($config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '$1', trim($config['script_path'])) . '/([^ \t\n\r<"\']+)#i', $url) ||
			preg_match('#([\w]+?://.*?[^ \t\n\r<"\']*)#i', $url) ||
			preg_match('#(www\.[\w\-]+\.[\w\-.\~]+(?:/[^ \t\n\r<"\']*)?)#i', $url))
		{
			$valid = true;
		}

		if ($valid)
		{
			$this->parsed_items['url']++;
		
			if (!preg_match('#^[\w]+?://.*?#i', $url))
			{
				$url = 'http://' . $url;
			}

			return ($var1) ? '[url=' . $url . ':' . $this->bbcode_uid . ']' . stripslashes($var2) . '[/url:' . $this->bbcode_uid . ']' : '[url:' . $this->bbcode_uid . ']' . $url . '[/url:' . $this->bbcode_uid . ']'; 
		}

		return '[url' . (($var1) ? '=' . stripslashes($var1) : '') . ']' . stripslashes($var2) . '[/url]';
	}
}

/**
* @package phpBB3
* Main message parser for posting, pm, etc. takes raw message
* and parses it for attachments, html, bbcode and smilies
*/
class parse_message extends bbcode_firstpass
{
	var $attachment_data = array();
	var $filename_data = array();

	// Helps ironing out user error
	var $message_status = '';

	var $allow_img_bbcode = true;
	var $allow_flash_bbcode = true;
	var $allow_quote_bbcode = true;

	// Init - give message here or manually
	function parse_message($message = '')
	{
		// Init BBCode UID
		$this->bbcode_uid = substr(md5(time()), 0, BBCODE_UID_LEN);

		if ($message)
		{
			$this->message = $message;
		}
	}

	// Parse Message : public
	function parse($allow_html, $allow_bbcode, $allow_magic_url, $allow_smilies, $allow_img_bbcode = true, $allow_flash_bbcode = true, $allow_quote_bbcode = true, $update_this_message = true, $mode = 'post')
	{
		global $config, $db, $user;

		$mode = ($mode != 'post') ? 'sig' : 'post';

		$this->allow_img_bbcode = $allow_img_bbcode;
		$this->allow_flash_bbcode = $allow_flash_bbcode;
		$this->allow_quote_bbcode = $allow_quote_bbcode;

		// If false, then $this->message won't be altered, the text will be returned instead.
		if (!$update_this_message)
		{
			$tmp_message = $this->message;
			$return_message = &$this->message;
		}

		if ($this->message_status == 'display')
		{
			$this->decode_message();
		}

		// Do some general 'cleanup' first before processing message,
		// e.g. remove excessive newlines(?), smilies(?)
		// Transform \r\n and \r into \n
		$match = array('#\r\n?#', '#sid=[a-z0-9]*?&amp;?#', "#([\n][\s]+){3,}#", '#(script|about|applet|activex|chrome):#i');
		$replace = array("\n", '', "\n\n", "\\1&#058;");
		$this->message = preg_replace($match, $replace, trim($this->message));

		// Message length check. -1 disables this check completely, even allows empty messsages.
		if ($config['max_' . $mode . '_chars'] != -1)
		{
			$msg_len = ($mode == 'post') ? strlen($this->message) : strlen(preg_replace('#\[\/?[a-z\*\+\-]+(=[\S]+)?\]#is', ' ', $this->message));
	
			if (!$msg_len || ($config['max_' . $mode . '_chars'] && $msg_len > $config['max_' . $mode . '_chars']))
			{
				$this->warn_msg[] = (!$msg_len) ? $user->lang['TOO_FEW_CHARS'] : $user->lang['TOO_MANY_CHARS'];
				return $this->warn_msg;
			}
		}

		// Parse HTML
		if ($allow_html && $config['allow_html_tags'])
		{
			$this->html($config['allow_html_tags']);
		}

		// Parse smilies
		if ($allow_smilies)
		{
			$this->smilies($config['max_' . $mode . '_smilies']);
		}

		$num_urls = 0;

		// Parse BBCode
		if ($allow_bbcode && strpos($this->message, '[') !== false)
		{
			$this->bbcode_init();
			$disallow = array('img', 'flash', 'quote');
			foreach ($disallow as $bool)
			{
				if (!${'allow_' . $bool . '_bbcode'})
				{
					$this->bbcodes[$bool]['disabled'] = true;
				}
			}
			$this->parse_bbcode();
	
			$num_urls += $this->parsed_items['url'];
		}

		// Parse URL's
		if ($allow_magic_url)
		{
			$this->magic_url((($config['cookie_secure']) ? 'https://' : 'http://'), $config['server_name'], $config['server_port'], $config['script_path']);
	
			if ($config['max_' . $mode . '_urls'])
			{
				$num_urls += preg_match_all('#\<!-- (l|m|w|e) --\>.*?\<!-- \1 --\>#', $this->message, $matches);
			}
		}

		// Check number of links
		if ($config['max_' . $mode . '_urls'] && $num_urls > $config['max_' . $mode . '_urls'])
		{
			$this->warn_msg[] = sprintf($user->lang['TOO_MANY_URLS'], $config['max_' . $mode . '_urls']);
			return $this->warn_msg;
		}

		if (!$update_this_message)
		{
			unset($this->message);
			$this->message = $tmp_message;
			return $return_message;
		}

		$this->message_status = 'parsed';
		return;
	}

	// Formatting text for display
	function format_display($allow_html, $allow_bbcode, $allow_magic_url, $allow_smilies, $update_this_message = true)
	{
		// If false, then the parsed message get returned but internal message not processed.
		if (!$update_this_message)
		{
			$tmp_message = $this->message;
			$return_message = &$this->message;
		}

		if ($this->message_status == 'plain')
		{
			// Force updating message - of course.
			$this->parse($allow_html, $allow_bbcode, $allow_magic_url, $allow_smilies, $this->allow_img_bbcode, $this->allow_flash_bbcode, $this->allow_quote_bbcode, true);
		}

		// Parse BBcode
		if ($allow_bbcode)
		{
			$this->bbcode_cache_init();

			// We are giving those parameters to be able to use the bbcode class on its own
			$this->bbcode_second_pass($this->message, $this->bbcode_uid);
		}

		$this->message = smiley_text($this->message, !$allow_smilies);

		// Replace naughty words such as farty pants
		$this->message = str_replace("\n", '<br />', censor_text($this->message));

		if (!$update_this_message)
		{
			unset($this->message);
			$this->message = $tmp_message;
			return $return_message;
		}

		$this->message_status = 'display';
		return;
	}	
	
	// Decode message to be placed back into form box
	function decode_message($custom_bbcode_uid = '', $update_this_message = true)
	{
		// If false, then the parsed message get returned but internal message not processed.
		if (!$update_this_message)
		{
			$tmp_message = $this->message;
			$return_message = &$this->message;
		}

		($custom_bbcode_uid) ? decode_message($this->message, $custom_bbcode_uid) : decode_message($this->message, $this->bbcode_uid);

		if (!$update_this_message)
		{
			unset($this->message);
			$this->message = $tmp_message;
			return $return_message;
		}

		$this->message_status = 'plain';
	}
	
	// Parse HTML
	function html($allowed_tags)
	{
		// If $allow_html is true then "allowed_tags" are converted back from entity
		// form, others remain
		$allowed_tags = split(',', $allowed_tags);
			
		if (sizeof($allowed_tags))
		{
			$this->message = preg_replace('#&lt;(\/?)(' . str_replace('*', '.*?', implode('|', $allowed_tags)) . ')&gt;#is', '<!-- h --><$1$2><!-- h -->', $this->message);
		}
	}

	// Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
	// Cuts down displayed size of link if over 50 chars, turns absolute links
	// into relative versions when the server/script path matches the link
	function magic_url($server_protocol, $server_name, $server_port, $script_path)
	{
		static $match;
		static $replace;
		
		$server_port = ($server_port <> 80 ) ? ':' . trim($server_port) . '/' : '/';

		if (!is_array($match))
		{
			$match = $replace = array();
			// Be sure to not let the matches cross over. ;)
	
			// relative urls for this board
			$match[] = '#(^|[\n ]|\()(' . preg_quote($server_protocol . trim($server_name) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '$1', trim($script_path)), '#') . ')/(.*?([^ \t\n\r<"\'\)]*)?)#i';
			$replace[] = '$1<!-- l --><a href="$2/$3">$3</a><!-- l -->';
	
			// matches a xxxx://aaaaa.bbb.cccc. ...
			$match[] = '#(^|[\n ]|\()([\w]+?://.*?([^ \t\n\r<"\'\)]*)?)#ie';
			$replace[] = "'\$1<!-- m --><a href=\"\$2\" target=\"_blank\">' . ((strlen('\$2') > 55) ? substr(str_replace('&amp;', '&', '\$2'), 0, 39) . ' ... ' . substr(str_replace('&amp;', '&', '\$2'), -10) : '\$2') . '</a><!-- m -->'";
	
			// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
			$match[] = '#(^|[\n ]|\()(www\.[\w\-]+\.[\w\-.\~]+(?:/[^ \t\n\r<"\'\)]*)?)#ie';
			$replace[] = "'\$1<!-- w --><a href=\"http://\$2\" target=\"_blank\">' . ((strlen('\$2') > 55) ? substr(str_replace('&amp;', '&', '\$2'), 0, 39) . ' ... ' . substr(str_replace('&amp;', '&', '\$2'), -10) : '\$2') . '</a><!-- w -->'";
	
			// matches an email@domain type address at the start of a line, or after a space.
			$match[] = '#(^|[\n ]|\()([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)#ie';
			$replace[] = "'\$1<!-- e --><a href=\"mailto:\$2\">' . ((strlen('\$2') > 55) ? substr('\$2', 0, 39) . ' ... ' . substr('\$2', -10) : '\$2') . '</a><!-- e -->'";
		}
		
		/* IMPORTANT NOTE (Developer inability to do advanced regular expressions) - Acyd Burn:  
			Transforming &lt; (<) to <&amp;lt; in order to bypass the inability of preg_replace 
			supporting multi-character sequences (POSIX - [..]). Since all message text is specialchared by
			default a match against < will always fail, since it is a &lt; sequence within the text.
			Replacing with <&amp;lt; and switching back thereafter produces no problems, because < will never show up with &amp;lt; in
			the same text (due to this specialcharing). The < is put in front of &amp;lt; to let the url break gracefully.
			I hope someone can lend me a hand here, telling me how to achive the wanted result without switching to ereg_replace.
		*/
		$this->message = preg_replace($match, $replace, str_replace('&lt;', '<&amp;lt;', $this->message));
		$this->message = str_replace('<&amp;lt;', '&lt;', $this->message);
	}

	// Parse Smilies
	function smilies($max_smilies = 0)
	{
		global $db, $user, $phpbb_root_path;
		static $match;
		static $replace;

		// NOTE: There is a memory leak in this block somewhere :\
		// See if the static arrays have already been filled on an earlier invocation
		if (!is_array($match))
		{
			// NOTE: obtain_* function? chaching the table contents?
	
			// For now setting the ttl to 10 minutes
			switch (SQL_LAYER)
			{
				case 'mssql':
				case 'mssql-odbc':
					$sql = 'SELECT * 
						FROM ' . SMILIES_TABLE . '
						ORDER BY LEN(code) DESC';
					break;
	
				// LENGTH supported by MySQL, IBM DB2, Oracle and Access for sure...
				default:
					$sql = 'SELECT * 
						FROM ' . SMILIES_TABLE . '
						ORDER BY LENGTH(code) DESC';
					break;
			}
			$result = $db->sql_query($sql, 600);
	
			if ($row = $db->sql_fetchrow($result))
			{
				$match = $replace = array();
	
				do
				{
					// (assertion)
					$match[] = '#(?<=^|[\n ]|\.)' . preg_quote($row['code'], '#') . '#';
					$replace[] = '<!-- s' . $row['code'] . ' --><img src="{SMILIES_PATH}/' . $row['smiley_url'] . '" border="0" alt="' . $row['emotion'] . '" title="' . $row['emotion'] . '" /><!-- s' . $row['code'] . ' -->';
				}
				while ($row = $db->sql_fetchrow($result));
			}
			else
			{
				$match = $replace = array();
			}
			$db->sql_freeresult($result);
		}
		
		if (sizeof($match))
		{
			if ($max_smilies)
			{
				$num_matches = preg_match_all('#' . str_replace('#', '', implode('|', $match)) . '#', $this->message, $matches);
				unset($matches);

				if ($num_matches !== false && $num_matches > $max_smilies)
				{
					$this->warn_msg[] = sprintf($user->lang['TOO_MANY_SMILIES'], $max_smilies);
					return;
				}
			}

			$this->message = trim(preg_replace($match, $replace, $this->message));
		}
	}

	// Parse Attachments
	function parse_attachments($form_name, $mode, $forum_id, $submit, $preview, $refresh, $is_message = false)
	{
		global $config, $auth, $user, $phpbb_root_path;

		$error = array();

		$num_attachments = sizeof($this->attachment_data);
		$this->filename_data['filecomment'] = request_var('filecomment', '', true);
		$upload_file = (isset($_FILES[$form_name]) && $_FILES[$form_name]['name'] != 'none' && trim($_FILES[$form_name]['name'])) ? true : false;

		$add_file		= (isset($_POST['add_file']));
		$delete_file	= (isset($_POST['delete_file']));
		$edit_comment	= (isset($_POST['edit_comment']));

		$cfg = array();
		$cfg['max_attachments'] = ($is_message) ? $config['max_attachments_pm'] : $config['max_attachments'];
		$forum_id = ($is_message) ? 0 : $forum_id;

		if ($submit && in_array($mode, array('post', 'reply', 'quote', 'edit')) && $upload_file)
		{
			if ($num_attachments < $cfg['max_attachments'] || $auth->acl_gets('m_', 'a_'))
			{
				$filedata = upload_attachment($form_name, $forum_id, false, '', $is_message);
				$error = $filedata['error'];

				if ($filedata['post_attach'] && !sizeof($error))
				{
					$new_entry = array(
						'physical_filename'	=> $filedata['physical_filename'],
						'comment'			=> $this->filename_data['filecomment'],
						'real_filename'		=> $filedata['real_filename'],
						'extension'			=> $filedata['extension'],
						'mimetype'			=> $filedata['mimetype'],
						'filesize'			=> $filedata['filesize'],
						'filetime'			=> $filedata['filetime'],
						'attach_id'			=> 0,
						'thumbnail'			=> $filedata['thumbnail']
					);

					$this->attachment_data = array_merge(array(0 => $new_entry), $this->attachment_data);
					$this->message = preg_replace('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#e', "'[attachment='.(\\1 + 1).']\\2[/attachment]'", $this->message);
					
					$this->filename_data['filecomment'] = '';

					// This Variable is set to false here, because Attachments are entered into the
					// Database in two modes, one if the id_list is 0 and the second one if post_attach is true
					// Since post_attach is automatically switched to true if an Attachment got added to the filesystem,
					// but we are assigning an id of 0 here, we have to reset the post_attach variable to false.
					//
					// This is very relevant, because it could happen that the post got not submitted, but we do not
					// know this circumstance here. We could be at the posting page or we could be redirected to the entered
					// post. :)
					$filedata['post_attach'] = false;
				}
			}
			else
			{
				$error[] = sprintf($user->lang['TOO_MANY_ATTACHMENTS'], $cfg['max_attachments']);
			}
		}

		if ($preview || $refresh || sizeof($error))
		{
			// Perform actions on temporary attachments
			if ($delete_file)
			{
				$index = (int) key($_POST['delete_file']);

				// delete selected attachment
				if (!$this->attachment_data[$index]['attach_id'])
				{
					phpbb_unlink($this->attachment_data[$index]['physical_filename'], 'file');

					if ($this->attachment_data[$index]['thumbnail'])
					{
						phpbb_unlink($this->attachment_data[$index]['physical_filename'], 'thumbnail');
					}
				}
				else
				{
					delete_attachments('attach', array(intval($this->attachment_data[$index]['attach_id'])));
				}
				
				unset($this->attachment_data[$index]);
				$this->message = preg_replace('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#e', "(\\1 == \$index) ? '' : ((\\1 > \$index) ? '[attachment=' . (\\1 - 1) . ']\\2[/attachment]' : '\\0')", $this->message);

				// Reindex Array
				$this->attachment_data = array_values($this->attachment_data);
			}
			else if ($edit_comment || $add_file || $preview)
			{
				if ($edit_comment)
				{
					$actual_comment_list = request_var('comment_list', '');

					foreach ($actual_comment_list as $index => $entry)
					{
						$this->attachment_data[$index]['comment'] = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', $entry);
					}
				}
				
				if (($add_file || $preview) && $upload_file)
				{
					if ($num_attachments < $cfg['max_attachments'] || $auth->acl_gets('m_', 'a_'))
					{
						$filedata = upload_attachment($form_name, $forum_id, false, '', $is_message);
						$error = array_merge($error, $filedata['error']);

						if (!sizeof($error))
						{
							$new_entry = array(
								'physical_filename'	=> $filedata['physical_filename'],
								'comment'			=> $this->filename_data['filecomment'],
								'real_filename'		=> $filedata['real_filename'],
								'extension'			=> $filedata['extension'],
								'mimetype'			=> $filedata['mimetype'],
								'filesize'			=> $filedata['filesize'],
								'filetime'			=> $filedata['filetime'],
								'attach_id'			=> 0,
								'thumbnail'			=> $filedata['thumbnail']
							);

							$this->attachment_data = array_merge(array(0 => $new_entry), $this->attachment_data);
							$this->message = preg_replace('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#e', "'[attachment='.(\\1 + 1).']\\2[/attachment]'", $this->message);
							$this->filename_data['filecomment'] = '';
						}
					}
					else
					{
						$error[] = sprintf($user->lang['TOO_MANY_ATTACHMENTS'], $cfg['max_attachments']);
					}
				}
			}
		}

		foreach ($error as $error_msg)
		{
			$this->warn_msg[] = $error_msg;
		}
	}

	// Get Attachment Data
	function get_submitted_attachment_data()
	{
		$this->filename_data['filecomment'] = request_var('filecomment', '', true);
		$this->attachment_data = (isset($_POST['attachment_data'])) ? $_POST['attachment_data'] : array();

		// 
		$data_prepare = array('physical_filename' => 's', 'real_filename' => 's', 'comment' => 's', 'extension' => 's', 'mimetype' => 's',
							'filesize' => 'i', 'filetime' => 'i', 'attach_id' => 'i', 'thumbnail' => 'i');
		foreach ($this->attachment_data as $pos => $var_ary)
		{
			foreach ($data_prepare as $var => $type)
			{
				if ($type == 's')
				{
					$this->attachment_data[$pos][$var] = htmlspecialchars(trim(stripslashes(preg_replace(array("#[ \xFF]{2,}#s", "#[\r\n]{2,}#s"), array(' ', "\n"), $this->attachment_data[$pos][$var]))));
				}
				else
				{
					$this->attachment_data[$pos][$var] = (int) $this->attachment_data[$pos][$var];
				}
			}
		}
	}
	
	// Parse Poll
	function parse_poll(&$poll)
	{
		global $auth, $user, $config;

		$poll_max_options = $poll['poll_max_options'];

		// Parse Poll Option text ;)
		$tmp_message = $this->message;
		$this->message = $poll['poll_option_text'];
		$bbcode_bitfield = $this->bbcode_bitfield;

		$poll['poll_option_text'] = $this->parse($poll['enable_html'], $poll['enable_bbcode'], $poll['enable_urls'], $poll['enable_smilies'], $poll['img_status'], false, false, false);
		
		$this->bbcode_bitfield |= $bbcode_bitfield;
		$this->message = $tmp_message;

		// Parse Poll Title
		$tmp_message = $this->message;
		$this->message = $poll['poll_title'];
		$bbcode_bitfield = $this->bbcode_bitfield;

		$poll['poll_title'] = $this->parse($poll['enable_html'], $poll['enable_bbcode'], $poll['enable_urls'], $poll['enable_smilies'], $poll['img_status'], false, false, false);

		$this->bbcode_bitfield |= $bbcode_bitfield;
		$this->message = $tmp_message;

		unset($tmp_message);

		$poll['poll_options'] = explode("\n", trim($poll['poll_option_text']));
		$poll['poll_options_size'] = sizeof($poll['poll_options']);
			
		if (sizeof($poll['poll_options']) == 1)
		{
			$this->warn_msg[] = $user->lang['TOO_FEW_POLL_OPTIONS'];
		}
		else if ($poll['poll_options_size'] > (int) $config['max_poll_options'])
		{
			$this->warn_msg[] = $user->lang['TOO_MANY_POLL_OPTIONS'];
		}
		else if ($poll_max_options > $poll['poll_options_size'])
		{
			$this->warn_msg[] = $user->lang['TOO_MANY_USER_OPTIONS'];
		}

		if (!$poll['poll_title'] && $poll['poll_options_size'])
		{
			$this->warn_msg[] = $user->lang['NO_POLL_TITLE'];
		}

		$poll['poll_max_options'] = ($poll['poll_max_options'] < 1) ? 1 : (($poll['poll_max_options'] > $config['max_poll_options']) ? $config['max_poll_options'] : $poll['poll_max_options']);
	}
}

/**
* @package phpBB3
* Parses a given message and updates/maintains the fulltext tables
* @todo replace fulltext_search in message_parser with search modules
*/
class fulltext_search
{
	function split_words($mode, $text)
	{
		global $user, $config;

		static $drop_char_match, $drop_char_replace, $stopwords, $replace_synonym, $match_synonym;

		// Is the fulltext indexer disabled? If yes then we need not 
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (!$config['load_search_upd'])
		{
			return;
		}

		if (!is_array($drop_char_match))
		{
			$drop_char_match =   array('-', '^', '$', ';', '#', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '\'', '!', '*');
			$drop_char_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ',  ' ', ' ');

			if ($fp = @fopen($user->lang_path . '/search_stopwords.txt', 'rb'))
			{
				$stopwords = explode("\n", str_replace("\r\n", "\n", fread($fp, filesize($user->lang_path . '/search_stopwords.txt'))));
			}
			fclose($fp);

			if ($fp = @fopen($user->lang_path . '/search_synonyms.txt', 'rb'))
			{
				preg_match_all('#^(.*?) (.*?)$#ms', fread($fp, filesize($user->lang_path . '/search_synonyms.txt')), $match);
				$replace_synonym = &$match[1];
				$match_synonym = &$match[2];
			}
			fclose($fp);
		}

		$match = array();
		// Comments for hardcoded bbcode elements (urls, smilies, html)
		$match[] = '#<!\-\- .* \-\->(.*?)<!\-\- .* \-\->#is';
		// New lines, carriage returns
		$match[] = "#[\n\r]+#";
		// NCRs like &nbsp; etc.
		$match[] = '#(&amp;|&)[\#a-z0-9]+?;#i';
		// Do not index code
		$match[] = '#\[code=?.*?(\:?[0-9a-z]{5,})\].*?\[\/code(\:?[0-9a-z]{5,})\]#is';
		// BBcode
		$match[] = '#\[\/?[a-z\*\+\-]+=?.*?(\:?[0-9a-z]{5,})\]#';
		// Sequences > min_search_chars & < max_search_chars
//		$match[] = '#\s([\b]{1,' . $config['min_search_chars'] . '}|[\b]{' . $config['max_search_chars'] . ',})\s#is';
//		$match[] = '#\s((&\#[0-9]+;){1,' . $config['min_search_chars'] . '}|(&\#[0-9]+;){' . $config['max_search_chars'] . ',})\s#is';
		// Filter out ; and # but not &#[0-9]+;
//		$match[] = '#(&\#[0-9]+;)|;|\#|&#';

		$text = preg_replace($match, ' ', ' ' . strtolower(trim($text)) . ' ');
		$text = str_replace(array(' + ', ' - '), array(' and ', ' not '), $text);

		// Filter out non-alphabetical chars
		$text = str_replace($drop_char_match, $drop_char_replace, $text);

		// Split words
		$text = explode(' ', preg_replace('#\s+#', ' ', trim($text)));

		if (sizeof($stopwords))
		{
			$stopped_words = array_intersect($text, $stopwords);
			$text = array_diff($text, $stopwords);
		}

		if (sizeof($replace_synonym))
		{
			$text = str_replace($replace_synonym, $match_synonym, $text);
		}

		foreach ($text as $index => $word)
		{
			if (strlen($word) < $config['min_search_chars'] || strlen($word) > $config['max_search_chars'])
			{
				unset($text[$index]);
			}
		}

		return $text;
	}

	function add($mode, $post_id, &$message, &$subject)
	{
		global $config, $db;

		// Is the fulltext indexer disabled? If yes then we need not 
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (!$config['load_search_upd'])
		{
			return;
		}

		// Split old and new post/subject to obtain array of 'words'
		$split_text = $this->split_words('post', $message);
		$split_title = ($subject) ? $this->split_words('post', $subject) : array();

		$words = array();
		if ($mode == 'edit')
		{
			$words['add']['post'] = array();
			$words['add']['title'] = array();
			$words['del']['post'] = array();
			$words['del']['title'] = array();

			$sql = 'SELECT w.word_id, w.word_text, m.title_match
				FROM ' . SEARCH_WORD_TABLE . ' w, ' . SEARCH_MATCH_TABLE . " m
				WHERE m.post_id = $post_id 
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
			$sql = 'SELECT word_id, word_text
				FROM ' . SEARCH_WORD_TABLE . '
				WHERE word_text IN (' . implode(', ', preg_replace('#^(.*)$#', '\'$1\'', $unique_add_words)) . ")";
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
					case 'mysql':
						$sql = 'INSERT INTO ' . SEARCH_WORD_TABLE . ' (word_text)
							VALUES ' . implode(', ', preg_replace('#^(.*)$#', '(\'$1\')', $new_words));
						$db->sql_query($sql);
						break;

					case 'mysql4':
					case 'mysqli':
					case 'mssql':
					case 'sqlite':
						$sql = 'INSERT INTO ' . SEARCH_WORD_TABLE . ' (word_text) ' . implode(' UNION ALL ', preg_replace('#^(.*)$#', "SELECT '\$1'",  $new_words));
						$db->sql_query($sql);
						break;

					default:
						foreach ($new_words as $word)
						{
							$sql = 'INSERT INTO ' . SEARCH_WORD_TABLE . " (word_text)
								VALUES ('$word')";
							$db->sql_query($sql);
						}
						break;
				}
			}
			unset($new_words);
		}

		foreach ($words['del'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			if (sizeof($word_ary))
			{
				$sql_in = array();
				foreach ($word_ary as $word)
				{
					$sql_in[] = $cur_words[$word_in][$word];
				}

				$sql = 'DELETE FROM ' . SEARCH_MATCH_TABLE . ' 
					WHERE word_id IN (' . implode(', ', $sql_in) . ') 
						AND post_id = ' . intval($post_id) . " 
						AND title_match = $title_match";
				$db->sql_query($sql);
				unset($sql_in);
			}
		}

		foreach ($words['add'] as $word_in => $word_ary)
		{
			$title_match = ($word_in == 'title') ? 1 : 0;

			if (sizeof($word_ary))
			{
				$sql = 'INSERT INTO ' . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) 
					SELECT $post_id, word_id, $title_match 
					FROM " . SEARCH_WORD_TABLE . ' 
					WHERE word_text IN (' . implode(', ', preg_replace('#^(.*)$#', '\'$1\'', $word_ary)) . ')';
				$db->sql_query($sql);
			}
		}

		unset($words);

		// Run the cleanup infrequently, once per session cleanup
		if ($config['search_last_gc'] < time() - $config['search_gc'])
		{
			$this->search_tidy();
		}
	}

	// Tidy up indexes, tag 'common words', remove
	// words no longer referenced in the match table, etc.
	function search_tidy()
	{
		global $db, $config;

		// Is the fulltext indexer disabled? If yes then we need not 
		// carry on ... it's okay ... I know when I'm not wanted boo hoo
		if (!$config['load_search_upd'])
		{
			return;
		}

		// Remove common (> 60% of posts ) words
		$sql = 'SELECT SUM(forum_posts) AS total_posts 
			FROM ' . FORUMS_TABLE;
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row['total_posts'] >= 100)
		{
			$sql = 'SELECT word_id
				FROM ' . SEARCH_MATCH_TABLE . '
				GROUP BY word_id
				HAVING COUNT(word_id) > ' . floor($row['total_posts'] * 0.6);
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$sql_in = array();
				do
				{
					$sql_in[] = $row['word_id'];
				}
				while ($row = $db->sql_fetchrow($result));
				
				$sql_in = implode(', ', $sql_in);

				$sql = 'UPDATE ' . SEARCH_WORD_TABLE . "
					SET word_common = 1
					WHERE word_id IN ($sql_in)";
				$db->sql_query($sql);

				$sql = 'DELETE FROM ' . SEARCH_MATCH_TABLE . "
					WHERE word_id IN ($sql_in)";
				$db->sql_query($sql);
				unset($sql_in);
			}
			$db->sql_freeresult($result);
		}

		// Remove words with no matches ... this is a potentially nasty query
		$sql = 'SELECT w.word_id
			FROM ' . SEARCH_WORD_TABLE . ' w
			LEFT JOIN ' . SEARCH_MATCH_TABLE . ' m ON w.word_id = m.word_id
			WHERE w.word_common = 0 AND m.word_id IS NULL
			GROUP BY m.word_id';
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = $row['word_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			$sql = 'DELETE FROM ' . SEARCH_WORD_TABLE . '
				WHERE word_id IN (' . implode(', ', $sql_in) . ')';
			$db->sql_query($sql);
			unset($sql_in);
		}
		$db->sql_freeresult($result);

		set_config('search_last_gc', time());
	}
}

?>