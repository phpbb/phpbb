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

if (!class_exists('bbcode'))
{
	include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
}

/**
* BBCODE FIRSTPASS
* BBCODE first pass class (functions for parsing messages for db storage)
*/
class bbcode_firstpass extends bbcode
{
	var $message = '';
	var $warn_msg = array();
	var $parsed_items = array();

	/**
	* Parse BBCode
	*/
	function parse_bbcode()
	{
		if (!$this->bbcodes)
		{
			$this->bbcode_init();
		}

		global $user;

		$this->bbcode_bitfield = '';
		$bitfield = new bitfield();

		foreach ($this->bbcodes as $bbcode_name => $bbcode_data)
		{
			if (isset($bbcode_data['disabled']) && $bbcode_data['disabled'])
			{
				foreach ($bbcode_data['regexp'] as $regexp => $replacement)
				{
					if (preg_match($regexp, $this->message))
					{
						$this->warn_msg[] = sprintf($user->lang['UNAUTHORISED_BBCODE'] , '[' . $bbcode_name . ']');
						continue;
					}
				}
			}
			else
			{
				foreach ($bbcode_data['regexp'] as $regexp => $replacement)
				{
					// The pattern gets compiled and cached by the PCRE extension,
					// it should not demand recompilation
					if (preg_match($regexp, $this->message))
					{
						$this->message = preg_replace($regexp, $replacement, $this->message);
						$bitfield->set($bbcode_data['bbcode_id']);
					}
				}
			}
		}

		$this->bbcode_bitfield = $bitfield->get_base64();
	}

	/**
	* Prepare some bbcodes for better parsing
	*/
	function prepare_bbcodes()
	{
		// Ok, seems like users instead want the no-parsing of urls, smilies, etc. after and before and within quote tags being tagged as "not a bug".
		// Fine by me ;) Will ease our live... but do not come back and cry at us, we won't hear you.

		/* Add newline at the end and in front of each quote block to prevent parsing errors (urls, smilies, etc.)
		if (strpos($this->message, '[quote') !== false && strpos($this->message, '[/quote]') !== false)
		{
			$this->message = str_replace("\r\n", "\n", $this->message);

			// We strip newlines and spaces after and before quotes in quotes (trimming) and then add exactly one newline
			$this->message = preg_replace('#\[quote(=&quot;.*?&quot;)?\]\s*(.*?)\s*\[/quote\]#siu', '[quote\1]' . "\n" . '\2' ."\n[/quote]", $this->message);
		}
		*/

		// Add other checks which needs to be placed before actually parsing anything (be it bbcodes, smilies, urls...)
	}

	/**
	* Init bbcode data for later parsing
	*/
	function bbcode_init($allow_custom_bbcode = true)
	{
		global $phpbb_dispatcher;

		static $rowset;

		// This array holds all bbcode data. BBCodes will be processed in this
		// order, so it is important to keep [code] in first position and
		// [quote] in second position.
		// To parse multiline URL we enable dotall option setting only for URL text
		// but not for link itself, thus [url][/url] is not affected.
		$this->bbcodes = array(
			'code'			=> array('bbcode_id' => 8,	'regexp' => array('#\[code(?:=([a-z]+))?\](.+\[/code\])#uise' => "\$this->bbcode_code('\$1', '\$2')")),
			'quote'			=> array('bbcode_id' => 0,	'regexp' => array('#\[quote(?:=&quot;(.*?)&quot;)?\](.+)\[/quote\]#uise' => "\$this->bbcode_quote('\$0')")),
			'attachment'	=> array('bbcode_id' => 12,	'regexp' => array('#\[attachment=([0-9]+)\](.*?)\[/attachment\]#uise' => "\$this->bbcode_attachment('\$1', '\$2')")),
			'b'				=> array('bbcode_id' => 1,	'regexp' => array('#\[b\](.*?)\[/b\]#uise' => "\$this->bbcode_strong('\$1')")),
			'i'				=> array('bbcode_id' => 2,	'regexp' => array('#\[i\](.*?)\[/i\]#uise' => "\$this->bbcode_italic('\$1')")),
			'url'			=> array('bbcode_id' => 3,	'regexp' => array('#\[url(=(.*))?\](?(1)((?s).*(?-s))|(.*))\[/url\]#uiUe' => "\$this->validate_url('\$2', ('\$3') ? '\$3' : '\$4')")),
			'img'			=> array('bbcode_id' => 4,	'regexp' => array('#\[img\](.*)\[/img\]#uiUe' => "\$this->bbcode_img('\$1')")),
			'size'			=> array('bbcode_id' => 5,	'regexp' => array('#\[size=([\-\+]?\d+)\](.*?)\[/size\]#uise' => "\$this->bbcode_size('\$1', '\$2')")),
			'color'			=> array('bbcode_id' => 6,	'regexp' => array('!\[color=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)\](.*?)\[/color\]!uise' => "\$this->bbcode_color('\$1', '\$2')")),
			'u'				=> array('bbcode_id' => 7,	'regexp' => array('#\[u\](.*?)\[/u\]#uise' => "\$this->bbcode_underline('\$1')")),
			'list'			=> array('bbcode_id' => 9,	'regexp' => array('#\[list(?:=(?:[a-z0-9]|disc|circle|square))?].*\[/list]#uise' => "\$this->bbcode_parse_list('\$0')")),
			'email'			=> array('bbcode_id' => 10,	'regexp' => array('#\[email=?(.*?)?\](.*?)\[/email\]#uise' => "\$this->validate_email('\$1', '\$2')")),
			'flash'			=> array('bbcode_id' => 11,	'regexp' => array('#\[flash=([0-9]+),([0-9]+)\](.*?)\[/flash\]#uie' => "\$this->bbcode_flash('\$1', '\$2', '\$3')"))
		);

		// Zero the parsed items array
		$this->parsed_items = array();

		foreach ($this->bbcodes as $tag => $bbcode_data)
		{
			$this->parsed_items[$tag] = 0;
		}

		if (!$allow_custom_bbcode)
		{
			return;
		}

		if (!is_array($rowset))
		{
			global $db;
			$rowset = array();

			$sql = 'SELECT *
				FROM ' . BBCODES_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$rowset[] = $row;
			}
			$db->sql_freeresult($result);
		}

		foreach ($rowset as $row)
		{
			$this->bbcodes[$row['bbcode_tag']] = array(
				'bbcode_id'	=> (int) $row['bbcode_id'],
				'regexp'	=> array($row['first_pass_match'] => str_replace('$uid', $this->bbcode_uid, $row['first_pass_replace']))
			);
		}

		$bbcodes = $this->bbcodes;

		/**
		* Event to modify the bbcode data for later parsing
		*
		* @event core.modify_bbcode_init
		* @var array	bbcodes		Array of bbcode data for use in parsing
		* @var array	rowset		Array of bbcode data from the database
		* @since 3.1.0-a3
		*/
		$vars = array('bbcodes', 'rowset');
		extract($phpbb_dispatcher->trigger_event('core.modify_bbcode_init', compact($vars)));

		$this->bbcodes = $bbcodes;
	}

	/**
	* Making some pre-checks for bbcodes as well as increasing the number of parsed items
	*/
	function check_bbcode($bbcode, &$in)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$in = str_replace("\r\n", "\n", str_replace('\"', '"', $in));

		// Trimming here to make sure no empty bbcodes are parsed accidently
		if (trim($in) == '')
		{
			return false;
		}

		$this->parsed_items[$bbcode]++;

		return true;
	}

	/**
	* Transform some characters in valid bbcodes
	*/
	function bbcode_specialchars($text)
	{
		$str_from = array('<', '>', '[', ']', '.', ':');
		$str_to = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58;');

		return str_replace($str_from, $str_to, $text);
	}

	/**
	* Parse size tag
	*/
	function bbcode_size($stx, $in)
	{
		global $user, $config;

		if (!$this->check_bbcode('size', $in))
		{
			return $in;
		}

		if ($config['max_' . $this->mode . '_font_size'] && $config['max_' . $this->mode . '_font_size'] < $stx)
		{
			$this->warn_msg[] = $user->lang('MAX_FONT_SIZE_EXCEEDED', (int) $config['max_' . $this->mode . '_font_size']);

			return '[size=' . $stx . ']' . $in . '[/size]';
		}

		// Do not allow size=0
		if ($stx <= 0)
		{
			return '[size=' . $stx . ']' . $in . '[/size]';
		}

		return '[size=' . $stx . ':' . $this->bbcode_uid . ']' . $in . '[/size:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse color tag
	*/
	function bbcode_color($stx, $in)
	{
		if (!$this->check_bbcode('color', $in))
		{
			return $in;
		}

		return '[color=' . $stx . ':' . $this->bbcode_uid . ']' . $in . '[/color:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse u tag
	*/
	function bbcode_underline($in)
	{
		if (!$this->check_bbcode('u', $in))
		{
			return $in;
		}

		return '[u:' . $this->bbcode_uid . ']' . $in . '[/u:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse b tag
	*/
	function bbcode_strong($in)
	{
		if (!$this->check_bbcode('b', $in))
		{
			return $in;
		}

		return '[b:' . $this->bbcode_uid . ']' . $in . '[/b:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse i tag
	*/
	function bbcode_italic($in)
	{
		if (!$this->check_bbcode('i', $in))
		{
			return $in;
		}

		return '[i:' . $this->bbcode_uid . ']' . $in . '[/i:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse img tag
	*/
	function bbcode_img($in)
	{
		global $user, $config;

		if (!$this->check_bbcode('img', $in))
		{
			return $in;
		}

		$in = trim($in);
		$error = false;

		$in = str_replace(' ', '%20', $in);

		// Checking urls
		if (!preg_match('#^' . get_preg_expression('url') . '$#iu', $in) && !preg_match('#^' . get_preg_expression('www_url') . '$#iu', $in))
		{
			return '[img]' . $in . '[/img]';
		}

		// Try to cope with a common user error... not specifying a protocol but only a subdomain
		if (!preg_match('#^[a-z0-9]+://#i', $in))
		{
			$in = 'http://' . $in;
		}

		if ($config['max_' . $this->mode . '_img_height'] || $config['max_' . $this->mode . '_img_width'])
		{
			$stats = @getimagesize(htmlspecialchars_decode($in));

			if ($stats === false)
			{
				$error = true;
				$this->warn_msg[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
			}
			else
			{
				if ($config['max_' . $this->mode . '_img_height'] && $config['max_' . $this->mode . '_img_height'] < $stats[1])
				{
					$error = true;
					$this->warn_msg[] = $user->lang('MAX_IMG_HEIGHT_EXCEEDED', (int) $config['max_' . $this->mode . '_img_height']);
				}

				if ($config['max_' . $this->mode . '_img_width'] && $config['max_' . $this->mode . '_img_width'] < $stats[0])
				{
					$error = true;
					$this->warn_msg[] = $user->lang('MAX_IMG_WIDTH_EXCEEDED', (int) $config['max_' . $this->mode . '_img_width']);
				}
			}
		}

		if ($error || $this->path_in_domain($in))
		{
			return '[img]' . $in . '[/img]';
		}

		return '[img:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($in) . '[/img:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse flash tag
	*/
	function bbcode_flash($width, $height, $in)
	{
		global $user, $config;

		if (!$this->check_bbcode('flash', $in))
		{
			return $in;
		}

		$in = trim($in);
		$error = false;

		// Do not allow 0-sizes generally being entered
		if ($width <= 0 || $height <= 0)
		{
			return '[flash=' . $width . ',' . $height . ']' . $in . '[/flash]';
		}

		$in = str_replace(' ', '%20', $in);

		// Make sure $in is a URL.
		if (!preg_match('#^' . get_preg_expression('url') . '$#iu', $in) &&
			!preg_match('#^' . get_preg_expression('www_url') . '$#iu', $in))
		{
			return '[flash=' . $width . ',' . $height . ']' . $in . '[/flash]';
		}

		// Apply the same size checks on flash files as on images
		if ($config['max_' . $this->mode . '_img_height'] || $config['max_' . $this->mode . '_img_width'])
		{
			if ($config['max_' . $this->mode . '_img_height'] && $config['max_' . $this->mode . '_img_height'] < $height)
			{
				$error = true;
				$this->warn_msg[] = $user->lang('MAX_FLASH_HEIGHT_EXCEEDED', (int) $config['max_' . $this->mode . '_img_height']);
			}

			if ($config['max_' . $this->mode . '_img_width'] && $config['max_' . $this->mode . '_img_width'] < $width)
			{
				$error = true;
				$this->warn_msg[] = $user->lang('MAX_FLASH_WIDTH_EXCEEDED', (int) $config['max_' . $this->mode . '_img_width']);
			}
		}

		if ($error || $this->path_in_domain($in))
		{
			return '[flash=' . $width . ',' . $height . ']' . $in . '[/flash]';
		}

		return '[flash=' . $width . ',' . $height . ':' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($in) . '[/flash:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse inline attachments [ia]
	*/
	function bbcode_attachment($stx, $in)
	{
		if (!$this->check_bbcode('attachment', $in))
		{
			return $in;
		}

		return '[attachment=' . $stx . ':' . $this->bbcode_uid . ']<!-- ia' . $stx . ' -->' . trim($in) . '<!-- ia' . $stx . ' -->[/attachment:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse code text from code tag
	* @access private
	*/
	function bbcode_parse_code($stx, &$code)
	{
		switch (strtolower($stx))
		{
			case 'php':

				$remove_tags = false;

				$str_from = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58;', '&#058;');
				$str_to = array('<', '>', '[', ']', '.', ':', ':');
				$code = str_replace($str_from, $str_to, $code);

				if (!preg_match('/\<\?.*?\?\>/is', $code))
				{
					$remove_tags = true;
					$code = "<?php $code ?>";
				}

				$conf = array('highlight.bg', 'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string');
				foreach ($conf as $ini_var)
				{
					@ini_set($ini_var, str_replace('highlight.', 'syntax', $ini_var));
				}

				// Because highlight_string is specialcharing the text (but we already did this before), we have to reverse this in order to get correct results
				$code = htmlspecialchars_decode($code);
				$code = highlight_string($code, true);

				$str_from = array('<span style="color: ', '<font color="syntax', '</font>', '<code>', '</code>','[', ']', '.', ':');
				$str_to = array('<span class="', '<span class="syntax', '</span>', '', '', '&#91;', '&#93;', '&#46;', '&#58;');

				if ($remove_tags)
				{
					$str_from[] = '<span class="syntaxdefault">&lt;?php </span>';
					$str_to[] = '';
					$str_from[] = '<span class="syntaxdefault">&lt;?php&nbsp;';
					$str_to[] = '<span class="syntaxdefault">';
				}

				$code = str_replace($str_from, $str_to, $code);
				$code = preg_replace('#^(<span class="[a-z_]+">)\n?(.*?)\n?(</span>)$#is', '$1$2$3', $code);

				if ($remove_tags)
				{
					$code = preg_replace('#(<span class="[a-z]+">)?\?&gt;(</span>)#', '$1&nbsp;$2', $code);
				}

				$code = preg_replace('#^<span class="[a-z]+"><span class="([a-z]+)">(.*)</span></span>#s', '<span class="$1">$2</span>', $code);
				$code = preg_replace('#(?:\s++|&nbsp;)*+</span>$#u', '</span>', $code);

				// remove newline at the end
				if (!empty($code) && substr($code, -1) == "\n")
				{
					$code = substr($code, 0, -1);
				}

				return "[code=$stx:" . $this->bbcode_uid . ']' . $code . '[/code:' . $this->bbcode_uid . ']';
			break;

			default:
				return '[code:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($code) . '[/code:' . $this->bbcode_uid . ']';
			break;
		}
	}

	/**
	* Parse code tag
	* Expects the argument to start right after the opening [code] tag and to end with [/code]
	*/
	function bbcode_code($stx, $in)
	{
		if (!$this->check_bbcode('code', $in))
		{
			return $in;
		}

		// We remove the hardcoded elements from the code block here because it is not used in code blocks
		// Having it here saves us one preg_replace per message containing [code] blocks
		// Additionally, magic url parsing should go after parsing bbcodes, but for safety those are stripped out too...
		$htm_match = get_preg_expression('bbcode_htm');
		unset($htm_match[4], $htm_match[5]);
		$htm_replace = array('\1', '\1', '\2', '\1');

		$out = $code_block = '';
		$open = 1;

		while ($in)
		{
			// Determine position and tag length of next code block
			preg_match('#(.*?)(\[code(?:=([a-z]+))?\])(.+)#is', $in, $buffer);
			$pos = (isset($buffer[1])) ? strlen($buffer[1]) : false;
			$tag_length = (isset($buffer[2])) ? strlen($buffer[2]) : false;

			// Determine position of ending code tag
			$pos2 = stripos($in, '[/code]');

			// Which is the next block, ending code or code block
			if ($pos !== false && $pos < $pos2)
			{
				// Open new block
				if (!$open)
				{
					$out .= substr($in, 0, $pos);
					$in = substr($in, $pos);
					$stx = (isset($buffer[3])) ? $buffer[3] : '';
					$code_block = '';
				}
				else
				{
					// Already opened block, just append to the current block
					$code_block .= substr($in, 0, $pos) . ((isset($buffer[2])) ? $buffer[2] : '');
					$in = substr($in, $pos);
				}

				$in = substr($in, $tag_length);
				$open++;
			}
			else
			{
				// Close the block
				if ($open == 1)
				{
					$code_block .= substr($in, 0, $pos2);
					$code_block = preg_replace($htm_match, $htm_replace, $code_block);

					// Parse this code block
					$out .= $this->bbcode_parse_code($stx, $code_block);
					$code_block = '';
					$open--;
				}
				else if ($open)
				{
					// Close one open tag... add to the current code block
					$code_block .= substr($in, 0, $pos2 + 7);
					$open--;
				}
				else
				{
					// end code without opening code... will be always outside code block
					$out .= substr($in, 0, $pos2 + 7);
				}

				$in = substr($in, $pos2 + 7);
			}
		}

		// if now $code_block has contents we need to parse the remaining code while removing the last closing tag to match up.
		if ($code_block)
		{
			$code_block = substr($code_block, 0, -7);
			$code_block = preg_replace($htm_match, $htm_replace, $code_block);

			$out .= $this->bbcode_parse_code($stx, $code_block);
		}

		return $out;
	}

	/**
	* Parse list bbcode
	* Expects the argument to start with a tag
	*/
	function bbcode_parse_list($in)
	{
		if (!$this->check_bbcode('list', $in))
		{
			return $in;
		}

		// $tok holds characters to stop at. Since the string starts with a '[' we'll get everything up to the first ']' which should be the opening [list] tag
		$tok = ']';
		$out = '[';

		// First character is [
		$in = substr($in, 1);
		$list_end_tags = $item_end_tags = array();

		do
		{
			$pos = strlen($in);

			for ($i = 0, $tok_len = strlen($tok); $i < $tok_len; ++$i)
			{
				$tmp_pos = strpos($in, $tok[$i]);

				if ($tmp_pos !== false && $tmp_pos < $pos)
				{
					$pos = $tmp_pos;
				}
			}

			$buffer = substr($in, 0, $pos);
			$tok = $in[$pos];

			$in = substr($in, $pos + 1);

			if ($tok == ']')
			{
				// if $tok is ']' the buffer holds a tag
				if (strtolower($buffer) == '/list' && sizeof($list_end_tags))
				{
					// valid [/list] tag, check nesting so that we don't hit false positives
					if (sizeof($item_end_tags) && sizeof($item_end_tags) >= sizeof($list_end_tags))
					{
						// current li tag has not been closed
						$out = preg_replace('/\n?\[$/', '[', $out) . array_pop($item_end_tags) . '][';
					}

					$out .= array_pop($list_end_tags) . ']';
					$tok = '[';
				}
				else if (preg_match('#^list(=[0-9a-z]+)?$#i', $buffer, $m))
				{
					// sub-list, add a closing tag
					if (empty($m[1]) || preg_match('/^=(?:disc|square|circle)$/i', $m[1]))
					{
						array_push($list_end_tags, '/list:u:' . $this->bbcode_uid);
					}
					else
					{
						array_push($list_end_tags, '/list:o:' . $this->bbcode_uid);
					}
					$out .= 'list' . substr($buffer, 4) . ':' . $this->bbcode_uid . ']';
					$tok = '[';
				}
				else
				{
					if (($buffer == '*' || substr($buffer, -2) == '[*') && sizeof($list_end_tags))
					{
						// the buffer holds a bullet tag and we have a [list] tag open
						if (sizeof($item_end_tags) >= sizeof($list_end_tags))
						{
							if (substr($buffer, -2) == '[*')
							{
								$out .= substr($buffer, 0, -2) . '[';
							}
							// current li tag has not been closed
							if (preg_match('/\n\[$/', $out, $m))
							{
								$out = preg_replace('/\n\[$/', '[', $out);
								$buffer = array_pop($item_end_tags) . "]\n[*:" . $this->bbcode_uid;
							}
							else
							{
								$buffer = array_pop($item_end_tags) . '][*:' . $this->bbcode_uid;
							}
						}
						else
						{
							$buffer = '*:' . $this->bbcode_uid;
						}

						$item_end_tags[] = '/*:m:' . $this->bbcode_uid;
					}
					else if ($buffer == '/*')
					{
						array_pop($item_end_tags);
						$buffer = '/*:' . $this->bbcode_uid;
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
		if (sizeof($item_end_tags))
		{
			$out .= '[' . implode('][', $item_end_tags) . ']';
		}
		if (sizeof($list_end_tags))
		{
			$out .= '[' . implode('][', $list_end_tags) . ']';
		}

		return $out;
	}

	/**
	* Parse quote bbcode
	* Expects the argument to start with a tag
	*/
	function bbcode_quote($in)
	{
		global $config, $user;

		$in = str_replace("\r\n", "\n", str_replace('\"', '"', trim($in)));

		if (!$in)
		{
			return '';
		}

		// To let the parser not catch tokens within quote_username quotes we encode them before we start this...
		$in = preg_replace('#quote=&quot;(.*?)&quot;\]#ie', "'quote=&quot;' . str_replace(array('[', ']', '\\\"'), array('&#91;', '&#93;', '\"'), '\$1') . '&quot;]'", $in);

		$tok = ']';
		$out = '[';

		$in = substr($in, 1);
		$close_tags = $error_ary = array();
		$buffer = '';

		do
		{
			$pos = strlen($in);
			for ($i = 0, $tok_len = strlen($tok); $i < $tok_len; ++$i)
			{
				$tmp_pos = strpos($in, $tok[$i]);
				if ($tmp_pos !== false && $tmp_pos < $pos)
				{
					$pos = $tmp_pos;
				}
			}

			$buffer .= substr($in, 0, $pos);
			$tok = $in[$pos];
			$in = substr($in, $pos + 1);

			if ($tok == ']')
			{
				if (strtolower($buffer) == '/quote' && sizeof($close_tags) && substr($out, -1, 1) == '[')
				{
					// we have found a closing tag
					$out .= array_pop($close_tags) . ']';
					$tok = '[';
					$buffer = '';

					/* Add space at the end of the closing tag if not happened before to allow following urls/smilies to be parsed correctly
					* Do not try to think for the user. :/ Do not parse urls/smilies if there is no space - is the same as with other bbcodes too.
					* Also, we won't have any spaces within $in anyway, only adding up spaces -> #10982
					if (!$in || $in[0] !== ' ')
					{
						$out .= ' ';
					}*/
				}
				else if (preg_match('#^quote(?:=&quot;(.*?)&quot;)?$#is', $buffer, $m) && substr($out, -1, 1) == '[')
				{
					$this->parsed_items['quote']++;

					// the buffer holds a valid opening tag
					if ($config['max_quote_depth'] && sizeof($close_tags) >= $config['max_quote_depth'])
					{
						if ($config['max_quote_depth'] == 1)
						{
							// Depth 1 - no nesting is allowed
							$error_ary['quote_depth'] = $user->lang('QUOTE_NO_NESTING');
						}
						else
						{
							// There are too many nested quotes
							$error_ary['quote_depth'] = $user->lang('QUOTE_DEPTH_EXCEEDED', (int) $config['max_quote_depth']);
						}

						$out .= $buffer . $tok;
						$tok = '[]';
						$buffer = '';

						continue;
					}

					array_push($close_tags, '/quote:' . $this->bbcode_uid);

					if (isset($m[1]) && $m[1])
					{
						$username = str_replace(array('&#91;', '&#93;'), array('[', ']'), $m[1]);
						$username = preg_replace('#\[(?!b|i|u|color|url|email|/b|/i|/u|/color|/url|/email)#iU', '&#91;$1', $username);

						$end_tags = array();
						$error = false;

						preg_match_all('#\[((?:/)?(?:[a-z]+))#i', $username, $tags);
						foreach ($tags[1] as $tag)
						{
							if ($tag[0] != '/')
							{
								$end_tags[] = '/' . $tag;
							}
							else
							{
								$end_tag = array_pop($end_tags);
								$error = ($end_tag != $tag) ? true : false;
							}
						}

						if ($error)
						{
							$username = $m[1];
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
				else if (preg_match('#^quote=&quot;(.*?)#is', $buffer, $m))
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
/**
*				Old quote code working fine, but having errors listed in bug #3572
*
*				$out .= $buffer . $tok;
*				$tok = ($tok == '[') ? ']' : '[]';
*				$buffer = '';
*/

				$out .= $buffer . $tok;

				if ($tok == '[')
				{
					// Search the text for the next tok... if an ending quote comes first, then change tok to []
					$pos1 = stripos($in, '[/quote');
					// If the token ] comes first, we change it to ]
					$pos2 = strpos($in, ']');
					// If the token [ comes first, we change it to [
					$pos3 = strpos($in, '[');

					if ($pos1 !== false && ($pos2 === false || $pos1 < $pos2) && ($pos3 === false || $pos1 < $pos3))
					{
						$tok = '[]';
					}
					else if ($pos3 !== false && ($pos2 === false || $pos3 < $pos2))
					{
						$tok = '[';
					}
					else
					{
						$tok = ']';
					}
				}
				else
				{
					$tok = '[]';
				}
				$buffer = '';
			}
		}
		while ($in);

		$out .= $buffer;

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

	/**
	* Validate email
	*/
	function validate_email($var1, $var2)
	{
		$var1 = str_replace("\r\n", "\n", str_replace('\"', '"', trim($var1)));
		$var2 = str_replace("\r\n", "\n", str_replace('\"', '"', trim($var2)));

		$txt = $var2;
		$email = ($var1) ? $var1 : $var2;

		$validated = true;

		if (!preg_match('/^' . get_preg_expression('email') . '$/i', $email))
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
			$retval = '[email=' . $this->bbcode_specialchars($email) . ':' . $this->bbcode_uid . ']' . $txt . '[/email:' . $this->bbcode_uid . ']';
		}
		else
		{
			$retval = '[email:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($email) . '[/email:' . $this->bbcode_uid . ']';
		}

		return $retval;
	}

	/**
	* Validate url
	*
	* @param string $var1 optional url parameter for url bbcode: [url(=$var1)]$var2[/url]
	* @param string $var2 url bbcode content: [url(=$var1)]$var2[/url]
	*/
	function validate_url($var1, $var2)
	{
		global $config;

		$var1 = str_replace("\r\n", "\n", str_replace('\"', '"', trim($var1)));
		$var2 = str_replace("\r\n", "\n", str_replace('\"', '"', trim($var2)));

		$url = ($var1) ? $var1 : $var2;

		if ($var1 && !$var2)
		{
			$var2 = $var1;
		}

		if (!$url)
		{
			return '[url' . (($var1) ? '=' . $var1 : '') . ']' . $var2 . '[/url]';
		}

		$valid = false;

		$url = str_replace(' ', '%20', $url);

		// Checking urls
		if (preg_match('#^' . get_preg_expression('url') . '$#iu', $url) ||
			preg_match('#^' . get_preg_expression('www_url') . '$#iu', $url) ||
			preg_match('#^' . preg_quote(generate_board_url(), '#') . get_preg_expression('relative_url') . '$#iu', $url))
		{
			$valid = true;
		}

		if ($valid)
		{
			$this->parsed_items['url']++;

			// if there is no scheme, then add http schema
			if (!preg_match('#^[a-z][a-z\d+\-.]*:/{2}#i', $url))
			{
				$url = 'http://' . $url;
			}

			// Is this a link to somewhere inside this board? If so then remove the session id from the url
			if (strpos($url, generate_board_url()) !== false && strpos($url, 'sid=') !== false)
			{
				$url = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}&amp;/', '\1', $url);
				$url = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}$/', '', $url);
				$url = append_sid($url);
			}

			return ($var1) ? '[url=' . $this->bbcode_specialchars($url) . ':' . $this->bbcode_uid . ']' . $var2 . '[/url:' . $this->bbcode_uid . ']' : '[url:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($url) . '[/url:' . $this->bbcode_uid . ']';
		}

		return '[url' . (($var1) ? '=' . $var1 : '') . ']' . $var2 . '[/url]';
	}

	/**
	* Check if url is pointing to this domain/script_path/php-file
	*
	* @param string $url the url to check
	* @return true if the url is pointing to this domain/script_path/php-file, false if not
	*
	* @access private
	*/
	function path_in_domain($url)
	{
		global $config, $phpEx, $user;

		if ($config['force_server_vars'])
		{
			$check_path = $config['script_path'];
		}
		else
		{
			$check_path = ($user->page['root_script_path'] != '/') ? substr($user->page['root_script_path'], 0, -1) : '/';
		}

		// Is the user trying to link to a php file in this domain and script path?
		if (strpos($url, ".{$phpEx}") !== false && strpos($url, $check_path) !== false)
		{
			$server_name = $user->host;

			// Forcing server vars is the only way to specify/override the protocol
			if ($config['force_server_vars'] || !$server_name)
			{
				$server_name = $config['server_name'];
			}

			// Check again in correct order...
			$pos_ext = strpos($url, ".{$phpEx}");
			$pos_path = strpos($url, $check_path);
			$pos_domain = strpos($url, $server_name);

			if ($pos_domain !== false && $pos_path >= $pos_domain && $pos_ext >= $pos_path)
			{
				// Ok, actually we allow linking to some files (this may be able to be extended in some way later...)
				if (strpos($url, '/' . $check_path . '/download/file.' . $phpEx) !== 0)
				{
					return false;
				}

				return true;
			}
		}

		return false;
	}
}

/**
* Main message parser for posting, pm, etc. takes raw message
* and parses it for attachments, bbcode and smilies
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
	var $allow_url_bbcode = true;

	var $mode;

	/**
	* The plupload object used for dealing with attachments
	* @var \phpbb\plupload\plupload
	*/
	protected $plupload;

	/**
	* The mimetype guesser object used for attachment mimetypes
	* @var \phpbb\mimetype\guesser
	*/
	protected $mimetype_guesser;

	/**
	* Init - give message here or manually
	*/
	function parse_message($message = '')
	{
		// Init BBCode UID
		$this->bbcode_uid = substr(base_convert(unique_id(), 16, 36), 0, BBCODE_UID_LEN);
		$this->message = $message;
	}

	/**
	* Parse Message
	*/
	function parse($allow_bbcode, $allow_magic_url, $allow_smilies, $allow_img_bbcode = true, $allow_flash_bbcode = true, $allow_quote_bbcode = true, $allow_url_bbcode = true, $update_this_message = true, $mode = 'post')
	{
		global $config, $db, $user, $phpbb_dispatcher;

		$this->mode = $mode;

		foreach (array('chars', 'smilies', 'urls', 'font_size', 'img_height', 'img_width') as $key)
		{
			if (!isset($config['max_' . $mode . '_' . $key]))
			{
				$config['max_' . $mode . '_' . $key] = 0;
			}
		}

		$this->allow_img_bbcode = $allow_img_bbcode;
		$this->allow_flash_bbcode = $allow_flash_bbcode;
		$this->allow_quote_bbcode = $allow_quote_bbcode;
		$this->allow_url_bbcode = $allow_url_bbcode;

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
		$match = array('#(script|about|applet|activex|chrome):#i');
		$replace = array("\\1&#058;");
		$this->message = preg_replace($match, $replace, trim($this->message));

		// Store message length...
		$message_length = ($mode == 'post') ? utf8_strlen($this->message) : utf8_strlen(preg_replace('#\[\/?[a-z\*\+\-]+(=[\S]+)?\]#ius', ' ', $this->message));

		// Maximum message length check. 0 disables this check completely.
		if ((int) $config['max_' . $mode . '_chars'] > 0 && $message_length > (int) $config['max_' . $mode . '_chars'])
		{
			$this->warn_msg[] = $user->lang('CHARS_' . strtoupper($mode) . '_CONTAINS', $message_length) . '<br />' . $user->lang('TOO_MANY_CHARS_LIMIT', (int) $config['max_' . $mode . '_chars']);
			return (!$update_this_message) ? $return_message : $this->warn_msg;
		}

		// Minimum message length check for post only
		if ($mode === 'post')
		{
			if (!$message_length || $message_length < (int) $config['min_post_chars'])
			{
				$this->warn_msg[] = (!$message_length) ? $user->lang['TOO_FEW_CHARS'] : ($user->lang('CHARS_POST_CONTAINS', $message_length) . '<br />' . $user->lang('TOO_FEW_CHARS_LIMIT', (int) $config['min_post_chars']));
				return (!$update_this_message) ? $return_message : $this->warn_msg;
			}
		}

		/**
		* This event can be used for additional message checks/cleanup before parsing
		*
		* @event core.message_parser_check_message
		* @var bool		allow_bbcode			Do we allow BBCodes
		* @var bool		allow_magic_url			Do we allow magic urls
		* @var bool		allow_smilies			Do we allow smilies
		* @var bool		allow_img_bbcode		Do we allow image BBCode
		* @var bool		allow_flash_bbcode		Do we allow flash BBCode
		* @var bool		allow_quote_bbcode		Do we allow quote BBCode
		* @var bool		allow_url_bbcode		Do we allow url BBCode
		* @var bool		update_this_message		Do we alter the parsed message
		* @var string	mode					Posting mode
		* @var string	message					The message text to parse
		* @var string	bbcode_bitfield			The bbcode_bitfield before parsing
		* @var string	bbcode_uid				The bbcode_uid before parsing
		* @var bool		return					Do we return after the event is triggered if $warn_msg is not empty
		* @var array	warn_msg				Array of the warning messages
		* @since 3.1.2-RC1
		* @change 3.1.3-RC1 Added vars $bbcode_bitfield and $bbcode_uid
		*/
		$message = $this->message;
		$warn_msg = $this->warn_msg;
		$return = false;
		$bbcode_bitfield = $this->bbcode_bitfield;
		$bbcode_uid = $this->bbcode_uid;
		$vars = array(
			'allow_bbcode',
			'allow_magic_url',
			'allow_smilies',
			'allow_img_bbcode',
			'allow_flash_bbcode',
			'allow_quote_bbcode',
			'allow_url_bbcode',
			'update_this_message',
			'mode',
			'message',
			'bbcode_bitfield',
			'bbcode_uid',
			'return',
			'warn_msg',
		);
		extract($phpbb_dispatcher->trigger_event('core.message_parser_check_message', compact($vars)));
		$this->message = $message;
		$this->warn_msg = $warn_msg;
		$this->bbcode_bitfield = $bbcode_bitfield;
		$this->bbcode_uid = $bbcode_uid;
		if ($return && !empty($this->warn_msg))
		{
			return (!$update_this_message) ? $return_message : $this->warn_msg;
		}

		// Prepare BBcode (just prepares some tags for better parsing)
		if ($allow_bbcode && strpos($this->message, '[') !== false)
		{
			$this->bbcode_init();
			$disallow = array('img', 'flash', 'quote', 'url');
			foreach ($disallow as $bool)
			{
				if (!${'allow_' . $bool . '_bbcode'})
				{
					$this->bbcodes[$bool]['disabled'] = true;
				}
			}

			$this->prepare_bbcodes();
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
			$this->parse_bbcode();
			$num_urls += $this->parsed_items['url'];
		}

		// Parse URL's
		if ($allow_magic_url)
		{
			$this->magic_url(generate_board_url());

			if ($config['max_' . $mode . '_urls'])
			{
				$num_urls += preg_match_all('#\<!-- ([lmwe]) --\>.*?\<!-- \1 --\>#', $this->message, $matches);
			}
		}

		// Check for out-of-bounds characters that are currently
		// not supported by utf8_bin in MySQL
		if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $this->message, $matches))
		{
			$character_list = implode('<br />', $matches[0]);
			$this->warn_msg[] = $user->lang('UNSUPPORTED_CHARACTERS_MESSAGE', $character_list);
			return $update_this_message ? $this->warn_msg : $return_message;
		}

		// Check for "empty" message. We do not check here for maximum length, because bbcode, smilies, etc. can add to the length.
		// The maximum length check happened before any parsings.
		if ($mode === 'post' && utf8_clean_string($this->message) === '')
		{
			$this->warn_msg[] = $user->lang['TOO_FEW_CHARS'];
			return (!$update_this_message) ? $return_message : $this->warn_msg;
		}

		// Check number of links
		if ($config['max_' . $mode . '_urls'] && $num_urls > $config['max_' . $mode . '_urls'])
		{
			$this->warn_msg[] = sprintf($user->lang['TOO_MANY_URLS'], $config['max_' . $mode . '_urls']);
			return (!$update_this_message) ? $return_message : $this->warn_msg;
		}

		if (!$update_this_message)
		{
			unset($this->message);
			$this->message = $tmp_message;
			return $return_message;
		}

		$this->message_status = 'parsed';
		return false;
	}

	/**
	* Formatting text for display
	*/
	function format_display($allow_bbcode, $allow_magic_url, $allow_smilies, $update_this_message = true)
	{
		global $phpbb_dispatcher;

		// If false, then the parsed message get returned but internal message not processed.
		if (!$update_this_message)
		{
			$tmp_message = $this->message;
			$return_message = &$this->message;
		}

		if ($this->message_status == 'plain')
		{
			// Force updating message - of course.
			$this->parse($allow_bbcode, $allow_magic_url, $allow_smilies, $this->allow_img_bbcode, $this->allow_flash_bbcode, $this->allow_quote_bbcode, $this->allow_url_bbcode, true);
		}

		// Replace naughty words such as farty pants
		$this->message = censor_text($this->message);

		// Parse BBcode
		if ($allow_bbcode)
		{
			$this->bbcode_cache_init();

			// We are giving those parameters to be able to use the bbcode class on its own
			$this->bbcode_second_pass($this->message, $this->bbcode_uid);
		}

		$this->message = bbcode_nl2br($this->message);
		$this->message = smiley_text($this->message, !$allow_smilies);

		$text = $this->message;
		$uid = $this->bbcode_uid;

		/**
		* Event to modify the text after it is parsed
		*
		* @event core.modify_format_display_text_after
		* @var string	text				The message text to parse
		* @var string	uid					The bbcode uid
		* @var bool		allow_bbcode		Do we allow bbcodes
		* @var bool		allow_magic_url		Do we allow magic urls
		* @var bool		allow_smilies		Do we allow smilies
		* @var bool		update_this_message	Do we update the internal message
		*									with the parsed result
		* @since 3.1.0-a3
		*/
		$vars = array('text', 'uid', 'allow_bbcode', 'allow_magic_url', 'allow_smilies', 'update_this_message');
		extract($phpbb_dispatcher->trigger_event('core.modify_format_display_text_after', compact($vars)));

		$this->message = $text;
		$this->bbcode_uid = $uid;

		if (!$update_this_message)
		{
			unset($this->message);
			$this->message = $tmp_message;
			return $return_message;
		}

		$this->message_status = 'display';
		return false;
	}

	/**
	* Decode message to be placed back into form box
	*/
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
		return false;
	}

	/**
	* Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
	* Cuts down displayed size of link if over 50 chars, turns absolute links
	* into relative versions when the server/script path matches the link
	*/
	function magic_url($server_url)
	{
		// We use the global make_clickable function
		$this->message = make_clickable($this->message, $server_url);
	}

	/**
	* Parse Smilies
	*/
	function smilies($max_smilies = 0)
	{
		global $db, $user;
		static $match;
		static $replace;

		// See if the static arrays have already been filled on an earlier invocation
		if (!is_array($match))
		{
			$match = $replace = array();

			// NOTE: obtain_* function? chaching the table contents?

			// For now setting the ttl to 10 minutes
			switch ($db->get_sql_layer())
			{
				case 'mssql':
				case 'mssql_odbc':
				case 'mssqlnative':
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

			while ($row = $db->sql_fetchrow($result))
			{
				if (empty($row['code']))
				{
					continue;
				}

				// (assertion)
				$match[] = preg_quote($row['code'], '#');
				$replace[] = '<!-- s' . $row['code'] . ' --><img src="{SMILIES_PATH}/' . $row['smiley_url'] . '" alt="' . $row['code'] . '" title="' . $row['emotion'] . '" /><!-- s' . $row['code'] . ' -->';
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($match))
		{
			if ($max_smilies)
			{
				// 'u' modifier has been added to correctly parse smilies within unicode strings
				// For details: http://tracker.phpbb.com/browse/PHPBB3-10117
				$num_matches = preg_match_all('#(?<=^|[\n .])(?:' . implode('|', $match) . ')(?![^<>]*>)#u', $this->message, $matches);
				unset($matches);

				if ($num_matches !== false && $num_matches > $max_smilies)
				{
					$this->warn_msg[] = sprintf($user->lang['TOO_MANY_SMILIES'], $max_smilies);
					return;
				}
			}

			// Make sure the delimiter # is added in front and at the end of every element within $match
			// 'u' modifier has been added to correctly parse smilies within unicode strings
			// For details: http://tracker.phpbb.com/browse/PHPBB3-10117

			$this->message = trim(preg_replace(explode(chr(0), '#(?<=^|[\n .])' . implode('(?![^<>]*>)#u' . chr(0) . '#(?<=^|[\n .])', $match) . '(?![^<>]*>)#u'), $replace, $this->message));
		}
	}

	/**
	* Parse Attachments
	*/
	function parse_attachments($form_name, $mode, $forum_id, $submit, $preview, $refresh, $is_message = false)
	{
		global $config, $auth, $user, $phpbb_root_path, $phpEx, $db, $request;

		$error = array();

		$num_attachments = sizeof($this->attachment_data);
		$this->filename_data['filecomment'] = utf8_normalize_nfc(request_var('filecomment', '', true));
		$upload = $request->file($form_name);
		$upload_file = (!empty($upload) && $upload['name'] !== 'none' && trim($upload['name']));

		$add_file		= (isset($_POST['add_file'])) ? true : false;
		$delete_file	= (isset($_POST['delete_file'])) ? true : false;

		// First of all adjust comments if changed
		$actual_comment_list = utf8_normalize_nfc(request_var('comment_list', array(''), true));

		foreach ($actual_comment_list as $comment_key => $comment)
		{
			if (!isset($this->attachment_data[$comment_key]))
			{
				continue;
			}

			if ($this->attachment_data[$comment_key]['attach_comment'] != $actual_comment_list[$comment_key])
			{
				$this->attachment_data[$comment_key]['attach_comment'] = $actual_comment_list[$comment_key];
			}
		}

		$cfg = array();
		$cfg['max_attachments'] = ($is_message) ? $config['max_attachments_pm'] : $config['max_attachments'];
		$forum_id = ($is_message) ? 0 : $forum_id;

		if ($submit && in_array($mode, array('post', 'reply', 'quote', 'edit')) && $upload_file)
		{
			if ($num_attachments < $cfg['max_attachments'] || $auth->acl_get('a_') || $auth->acl_get('m_', $forum_id))
			{
				$filedata = upload_attachment($form_name, $forum_id, false, '', $is_message);
				$error = $filedata['error'];

				if ($filedata['post_attach'] && !sizeof($error))
				{
					$sql_ary = array(
						'physical_filename'	=> $filedata['physical_filename'],
						'attach_comment'	=> $this->filename_data['filecomment'],
						'real_filename'		=> $filedata['real_filename'],
						'extension'			=> $filedata['extension'],
						'mimetype'			=> $filedata['mimetype'],
						'filesize'			=> $filedata['filesize'],
						'filetime'			=> $filedata['filetime'],
						'thumbnail'			=> $filedata['thumbnail'],
						'is_orphan'			=> 1,
						'in_message'		=> ($is_message) ? 1 : 0,
						'poster_id'			=> $user->data['user_id'],
					);

					$db->sql_query('INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

					$new_entry = array(
						'attach_id'		=> $db->sql_nextid(),
						'is_orphan'		=> 1,
						'real_filename'	=> $filedata['real_filename'],
						'attach_comment'=> $this->filename_data['filecomment'],
						'filesize'		=> $filedata['filesize'],
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
				$error[] = $user->lang('TOO_MANY_ATTACHMENTS', (int) $cfg['max_attachments']);
			}
		}

		if ($preview || $refresh || sizeof($error))
		{
			if (isset($this->plupload) && $this->plupload->is_active())
			{
				$json_response = new \phpbb\json_response();
			}

			// Perform actions on temporary attachments
			if ($delete_file)
			{
				include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

				$index = array_keys(request_var('delete_file', array(0 => 0)));
				$index = (!empty($index)) ? $index[0] : false;

				if ($index !== false && !empty($this->attachment_data[$index]))
				{
					// delete selected attachment
					if ($this->attachment_data[$index]['is_orphan'])
					{
						$sql = 'SELECT attach_id, physical_filename, thumbnail
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE attach_id = ' . (int) $this->attachment_data[$index]['attach_id'] . '
								AND is_orphan = 1
								AND poster_id = ' . $user->data['user_id'];
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if ($row)
						{
							phpbb_unlink($row['physical_filename'], 'file');

							if ($row['thumbnail'])
							{
								phpbb_unlink($row['physical_filename'], 'thumbnail');
							}

							$db->sql_query('DELETE FROM ' . ATTACHMENTS_TABLE . ' WHERE attach_id = ' . (int) $this->attachment_data[$index]['attach_id']);
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
					if (isset($this->plupload) && $this->plupload->is_active())
					{
						$json_response->send($this->attachment_data);
					}
				}
			}
			else if (($add_file || $preview) && $upload_file)
			{
				if ($num_attachments < $cfg['max_attachments'] || $auth->acl_gets('m_', 'a_', $forum_id))
				{
					$filedata = upload_attachment($form_name, $forum_id, false, '', $is_message, false, $this->mimetype_guesser, $this->plupload);
					$error = array_merge($error, $filedata['error']);

					if (!sizeof($error))
					{
						$sql_ary = array(
							'physical_filename'	=> $filedata['physical_filename'],
							'attach_comment'	=> $this->filename_data['filecomment'],
							'real_filename'		=> $filedata['real_filename'],
							'extension'			=> $filedata['extension'],
							'mimetype'			=> $filedata['mimetype'],
							'filesize'			=> $filedata['filesize'],
							'filetime'			=> $filedata['filetime'],
							'thumbnail'			=> $filedata['thumbnail'],
							'is_orphan'			=> 1,
							'in_message'		=> ($is_message) ? 1 : 0,
							'poster_id'			=> $user->data['user_id'],
						);

						$db->sql_query('INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

						$new_entry = array(
							'attach_id'		=> $db->sql_nextid(),
							'is_orphan'		=> 1,
							'real_filename'	=> $filedata['real_filename'],
							'attach_comment'=> $this->filename_data['filecomment'],
							'filesize'		=> $filedata['filesize'],
						);

						$this->attachment_data = array_merge(array(0 => $new_entry), $this->attachment_data);
						$this->message = preg_replace('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#e', "'[attachment='.(\\1 + 1).']\\2[/attachment]'", $this->message);
						$this->filename_data['filecomment'] = '';

						if (isset($this->plupload) && $this->plupload->is_active())
						{
							$download_url = append_sid("{$phpbb_root_path}download/file.{$phpEx}", 'mode=view&amp;id=' . $new_entry['attach_id']);

							// Send the client the attachment data to maintain state
							$json_response->send(array('data' => $this->attachment_data, 'download_url' => $download_url));
						}
					}
				}
				else
				{
					$error[] = $user->lang('TOO_MANY_ATTACHMENTS', (int) $cfg['max_attachments']);
				}

				if (!empty($error) && isset($this->plupload) && $this->plupload->is_active())
				{
					// If this is a plupload (and thus ajax) request, give the
					// client the first error we have
					$json_response->send(array(
						'jsonrpc' => '2.0',
						'id' => 'id',
						'error' => array(
							'code' => 105,
							'message' => current($error),
						),
					));
				}
			}
		}

		foreach ($error as $error_msg)
		{
			$this->warn_msg[] = $error_msg;
		}
	}

	/**
	* Get Attachment Data
	*/
	function get_submitted_attachment_data($check_user_id = false)
	{
		global $user, $db, $phpbb_root_path, $phpEx, $config;
		global $request;

		$this->filename_data['filecomment'] = utf8_normalize_nfc(request_var('filecomment', '', true));
		$attachment_data = $request->variable('attachment_data', array(0 => array('' => '')), true, \phpbb\request\request_interface::POST);
		$this->attachment_data = array();

		$check_user_id = ($check_user_id === false) ? $user->data['user_id'] : $check_user_id;

		if (!sizeof($attachment_data))
		{
			return;
		}

		$not_orphan = $orphan = array();

		foreach ($attachment_data as $pos => $var_ary)
		{
			if ($var_ary['is_orphan'])
			{
				$orphan[(int) $var_ary['attach_id']] = $pos;
			}
			else
			{
				$not_orphan[(int) $var_ary['attach_id']] = $pos;
			}
		}

		// Regenerate already posted attachments
		if (sizeof($not_orphan))
		{
			// Get the attachment data, based on the poster id...
			$sql = 'SELECT attach_id, is_orphan, real_filename, attach_comment, filesize
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('attach_id', array_keys($not_orphan)) . '
					AND poster_id = ' . $check_user_id;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$pos = $not_orphan[$row['attach_id']];
				$this->attachment_data[$pos] = $row;
				$this->attachment_data[$pos]['attach_comment'] = $attachment_data[$pos]['attach_comment'];

				unset($not_orphan[$row['attach_id']]);
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($not_orphan))
		{
			trigger_error('NO_ACCESS_ATTACHMENT', E_USER_ERROR);
		}

		// Regenerate newly uploaded attachments
		if (sizeof($orphan))
		{
			$sql = 'SELECT attach_id, is_orphan, real_filename, attach_comment, filesize
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('attach_id', array_keys($orphan)) . '
					AND poster_id = ' . $user->data['user_id'] . '
					AND is_orphan = 1';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$pos = $orphan[$row['attach_id']];
				$this->attachment_data[$pos] = $row;
				$this->attachment_data[$pos]['attach_comment'] = $attachment_data[$pos]['attach_comment'];

				unset($orphan[$row['attach_id']]);
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($orphan))
		{
			trigger_error('NO_ACCESS_ATTACHMENT', E_USER_ERROR);
		}

		ksort($this->attachment_data);
	}

	/**
	* Parse Poll
	*/
	function parse_poll(&$poll)
	{
		global $auth, $user, $config;

		$poll_max_options = $poll['poll_max_options'];

		// Parse Poll Option text ;)
		$tmp_message = $this->message;
		$this->message = $poll['poll_option_text'];
		$bbcode_bitfield = $this->bbcode_bitfield;

		$poll['poll_option_text'] = $this->parse($poll['enable_bbcode'], ($config['allow_post_links']) ? $poll['enable_urls'] : false, $poll['enable_smilies'], $poll['img_status'], false, false, $config['allow_post_links'], false, 'poll');

		$bbcode_bitfield = base64_encode(base64_decode($bbcode_bitfield) | base64_decode($this->bbcode_bitfield));
		$this->message = $tmp_message;

		// Parse Poll Title
		$tmp_message = $this->message;
		$this->message = $poll['poll_title'];
		$this->bbcode_bitfield = $bbcode_bitfield;

		$poll['poll_options'] = explode("\n", trim($poll['poll_option_text']));
		$poll['poll_options_size'] = sizeof($poll['poll_options']);

		if (!$poll['poll_title'] && $poll['poll_options_size'])
		{
			$this->warn_msg[] = $user->lang['NO_POLL_TITLE'];
		}
		else
		{
			if (utf8_strlen(preg_replace('#\[\/?[a-z\*\+\-]+(=[\S]+)?\]#ius', ' ', $this->message)) > 100)
			{
				$this->warn_msg[] = $user->lang['POLL_TITLE_TOO_LONG'];
			}
			$poll['poll_title'] = $this->parse($poll['enable_bbcode'], ($config['allow_post_links']) ? $poll['enable_urls'] : false, $poll['enable_smilies'], $poll['img_status'], false, false, $config['allow_post_links'], false, 'poll');
			if (strlen($poll['poll_title']) > 255)
			{
				$this->warn_msg[] = $user->lang['POLL_TITLE_COMP_TOO_LONG'];
			}
		}

		$this->bbcode_bitfield = base64_encode(base64_decode($bbcode_bitfield) | base64_decode($this->bbcode_bitfield));
		$this->message = $tmp_message;
		unset($tmp_message);

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

		$poll['poll_max_options'] = ($poll['poll_max_options'] < 1) ? 1 : (($poll['poll_max_options'] > $config['max_poll_options']) ? $config['max_poll_options'] : $poll['poll_max_options']);
	}

	/**
	* Setter function for passing the plupload object
	*
	* @param \phpbb\plupload\plupload $plupload The plupload object
	*
	* @return null
	*/
	public function set_plupload(\phpbb\plupload\plupload $plupload)
	{
		$this->plupload = $plupload;
	}

	/**
	* Setter function for passing the mimetype_guesser object
	*
	* @param \phpbb\mimetype\guesser $mimetype_guesser The mimetype_guesser object
	*
	* @return null
	*/
	public function set_mimetype_guesser(\phpbb\mimetype\guesser $mimetype_guesser)
	{
		$this->mimetype_guesser = $mimetype_guesser;
	}
}
