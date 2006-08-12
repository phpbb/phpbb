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

if (!class_exists('bbcode'))
{
	include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
}

/**
* BBCODE FIRSTPASS
* BBCODE first pass class (functions for parsing messages for db storage)
* @package phpBB3
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
				$bitfield->set($bbcode_data['bbcode_id']);
				$size = $new_size;
			}
		}

		$this->bbcode_bitfield = $bitfield->get_base64();
	}

	/**
	* Prepare some bbcodes for better parsing
	*/
	function prepare_bbcodes()
	{
		// Add newline at the end and in front of each quote block to prevent parsing errors (urls, smilies, etc.)
		if (strpos($this->message, '[quote') !== false)
		{
			$in = str_replace("\r\n", "\n", $this->message);

			$this->message = preg_replace(array('#\[quote(=&quot;.*?&quot;)?\]([^\n])#is', '#([^\n])\[\/quote\]#is'), array("[quote\\1]\n\\2", "\\1\n[/quote]"), $this->message);
			$this->message = preg_replace(array('#\[quote(=&quot;.*?&quot;)?\]([^\n])#is', '#([^\n])\[\/quote\]#is'), array("[quote\\1]\n\\2", "\\1\n[/quote]"), $this->message);
		}

		// Add other checks which needs to be placed before actually parsing anything (be it bbcodes, smilies, urls...)
	}

	/**
	* Init bbcode data for later parsing
	*/
	function bbcode_init()
	{
		static $rowset;

		// This array holds all bbcode data. BBCodes will be processed in this
		// order, so it is important to keep [code] in first position and
		// [quote] in second position.
		$this->bbcodes = array(
			'code'			=> array('bbcode_id' => 8,	'regexp' => array('#\[code(?:=([a-z]+))?\](.+\[/code\])#ise' => "\$this->bbcode_code('\$1', '\$2')")),
			'quote'			=> array('bbcode_id' => 0,	'regexp' => array('#\[quote(?:=&quot;(.*?)&quot;)?\](.+)\[/quote\]#ise' => "\$this->bbcode_quote('\$0')")),
			'attachment'	=> array('bbcode_id' => 12,	'regexp' => array('#\[attachment=([0-9]+)\](.*?)\[/attachment\]#ise' => "\$this->bbcode_attachment('\$1', '\$2')")),
			'b'				=> array('bbcode_id' => 1,	'regexp' => array('#\[b\](.*?)\[/b\]#ise' => "\$this->bbcode_strong('\$1')")),
			'i'				=> array('bbcode_id' => 2,	'regexp' => array('#\[i\](.*?)\[/i\]#ise' => "\$this->bbcode_italic('\$1')")),
			'url'			=> array('bbcode_id' => 3,	'regexp' => array('#\[url(=(.*))?\](.*)\[/url\]#iUe' => "\$this->validate_url('\$2', '\$3')")),
			'img'			=> array('bbcode_id' => 4,	'regexp' => array('#\[img\](https?://)([a-z0-9\-\.,\?!%\*_:;~\\&$@/=\+]+)\[/img\]#ie' => "\$this->bbcode_img('\$1\$2')")),
			'size'			=> array('bbcode_id' => 5,	'regexp' => array('#\[size=([\-\+]?[1-2]?[0-9])\](.*?)\[/size\]#ise' => "\$this->bbcode_size('\$1', '\$2')")),
			'color'			=> array('bbcode_id' => 6,	'regexp' => array('!\[color=(#[0-9A-Fa-f]{6}|[a-z\-]+)\](.*?)\[/color\]!ise' => "\$this->bbcode_color('\$1', '\$2')")),
			'u'				=> array('bbcode_id' => 7,	'regexp' => array('#\[u\](.*?)\[/u\]#ise' => "\$this->bbcode_underline('\$1')")),
			'list'			=> array('bbcode_id' => 9,	'regexp' => array('#\[list(=[a-z|0-9|(?:disc|circle|square))]+)?\].*\[/list\]#ise' => "\$this->bbcode_parse_list('\$0')")),
			'email'			=> array('bbcode_id' => 10,	'regexp' => array('#\[email=?(.*?)?\](.*?)\[/email\]#ise' => "\$this->validate_email('\$1', '\$2')")),
			'flash'			=> array('bbcode_id' => 11,	'regexp' => array('#\[flash=([0-9]+),([0-9]+)\](.*?)\[/flash\]#ie' => "\$this->bbcode_flash('\$1', '\$2', '\$3')"))
		);

		// Zero the parsed items array
		$this->parsed_items = array();

		foreach ($this->bbcodes as $tag => $bbcode_data)
		{
			$this->parsed_items[$tag] = 0;
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
			return '';
		}

		if ($config['max_' . $this->mode . '_font_size'] && $config['max_' . $this->mode . '_font_size'] < $stx)
		{
			$this->warn_msg[] = sprintf($user->lang['MAX_FONT_SIZE_EXCEEDED'], $config['max_' . $this->mode . '_font_size']);
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
			return '';
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
			return '';
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
			return '';
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
			return '';
		}

		return '[i:' . $this->bbcode_uid . ']' . $in . '[/i:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse img tag
	*/
	function bbcode_img($in)
	{
		global $user, $config, $phpEx;

		if (!$this->check_bbcode('img', $in))
		{
			return '';
		}

		$in = trim($in);

		if ($config['max_' . $this->mode . '_img_height'] || $config['max_' . $this->mode . '_img_width'])
		{
			$stats = @getimagesize($in);

			if ($stats === false)
			{
				$this->warn_msg[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
			}
			else
			{
				if ($config['max_' . $this->mode . '_img_height'] && $config['max_' . $this->mode . '_img_height'] < $stats[1])
				{
					$this->warn_msg[] = sprintf($user->lang['MAX_IMG_HEIGHT_EXCEEDED'], $config['max_' . $this->mode . '_img_height']);
				}

				if ($config['max_' . $this->mode . '_img_width'] && $config['max_' . $this->mode . '_img_width'] < $stats[0])
				{
					$this->warn_msg[] = sprintf($user->lang['MAX_IMG_WIDTH_EXCEEDED'], $config['max_' . $this->mode . '_img_width']);
				}
			}
		}

		if ($this->path_in_domain($in))
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
		global $user, $config, $phpEx;

		if (!$this->check_bbcode('flash', $in))
		{
			return '';
		}

		$in = trim($in);

		// Apply the same size checks on flash files as on images
		if ($config['max_' . $this->mode . '_img_height'] || $config['max_' . $this->mode . '_img_width'])
		{
			if ($config['max_' . $this->mode . '_img_height'] && $config['max_' . $this->mode . '_img_height'] < $height)
			{
				$this->warn_msg[] = sprintf($user->lang['MAX_FLASH_HEIGHT_EXCEEDED'], $config['max_' . $this->mode . '_img_height']);
			}

			if ($config['max_' . $this->mode . '_img_width'] && $config['max_' . $this->mode . '_img_width'] < $width)
			{
				$this->warn_msg[] = sprintf($user->lang['MAX_FLASH_WIDTH_EXCEEDED'], $config['max_' . $this->mode . '_img_width']);
			}
		}

		if ($this->path_in_domain($in))
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
			return '';
		}

		return '[attachment=' . $stx . ':' . $this->bbcode_uid . ']<!-- ia' . $stx . ' -->' . trim($in) . '<!-- ia' . $stx . ' -->[/attachment:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse code tag
	* Expects the argument to start right after the opening [code] tag and to end with [/code]
	*/
	function bbcode_code($stx, $in)
	{
		if (!$this->check_bbcode('code', $in))
		{
			return '';
		}

		// We remove the hardcoded elements from the code block here because it is not used in code blocks
		// Having it here saves us one preg_replace per message containing [code] blocks
		// Additionally, magic url parsing should go after parsing bbcodes, but for safety those are stripped out too...
		$htm_match = array(
			'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
			'#<!\-\- m \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- m \-\->#',
			'#<!\-\- w \-\-><a href="http:\/\/(.*?)" target="_blank">.*?</a><!\-\- w \-\->#',
			'#<!\-\- l \-\-><a href="(.*?)">.*?</a><!\-\- l \-\->#',
			'#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
			'#&\#([0-9]+);#',
		);
		$htm_replace = array('\1', '\1', '\1', '\1', '\1', '&amp;#\1;');

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
					$code = str_replace(array('&lt;', '&gt;'), array('<', '>'), $code);

					if (!preg_match('/\<\?.*?\?\>/is', $code))
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
					$code = html_entity_decode($code);
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
						$code = preg_replace('#(<span class="[a-z]+">)?\?&gt;</span>#', '', $code);
					}

					$code = preg_replace('#^<span class="[a-z]+"><span class="([a-z]+)">(.*)</span></span>#s', '<span class="$1">$2</span>', $code);
					$code = preg_replace('#(?:[\n\r\s\t]|&nbsp;)*</span>$#', '</span>', $code);

					// remove newline at the end
					if (!empty($code) && $code{strlen($code)-1} == "\n")
					{
						$code = substr($code, 0, -1);
					}

					$out .= "[code=$stx:" . $this->bbcode_uid . ']' . $code . '[/code:' . $this->bbcode_uid . ']';
				break;

				default:
					$out .= '[code:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($code) . '[/code:' . $this->bbcode_uid . ']';
				break;
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

	/**
	* Parse list bbcode
	* Expects the argument to start with a tag
	*/
	function bbcode_parse_list($in)
	{
		if (!$this->check_bbcode('list', $in))
		{
			return '';
		}

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
				else if (preg_match('#list(=?(?:[0-9]|[a-z]|))#i', $buffer, $m))
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

		$tok = ']';
		$out = '[';

		$in = substr($in, 1);
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
					// Add space at the end of the closing tag to allow following urls/smilies to be parsed correctly
					$out .= array_pop($close_tags) . '] ';
					$tok = '[';
					$buffer = '';
				}
				else if (preg_match('#^quote(?:=&quot;(.*?)&quot;)?$#is', $buffer, $m))
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
				$out .= $buffer . $tok;
				// $tok = ($tok == '[') ? ']' : '[]';
				$tok = '[]';
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
	*/
	function validate_url($var1, $var2)
	{
		global $config;

		$var1 = str_replace("\r\n", "\n", str_replace('\"', '"', trim($var1)));
		$var2 = str_replace("\r\n", "\n", str_replace('\"', '"', trim($var2)));

		$url = ($var1) ? $var1 : $var2;
		$valid = false;

		if (!$url || ($var1 && !$var2))
		{
			return '';
		}

		// Checking urls
		if (preg_match('#' . preg_quote(generate_board_url(), '#') . '/([^ \t\n\r<"\']+)#i', $url) ||
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

			// We take our test url and stick on the first bit of text we get to check if we are really at the domain. If so, lets go!
			if (strpos($url, generate_board_url()) !== false && strpos($url, 'sid=') !== false)
			{
				$url = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}/', '\1', $url);
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
	* @access: private
	*/
	function path_in_domain($url)
	{
		global $config, $phpEx, $user;

		$check_path = ($user->page['root_script_path'] != '/') ? substr($user->page['root_script_path'], 0, -1) : '/';

		// Is the user trying to link to a php file in this domain and script path?
		if (strpos($url, ".{$phpEx}") !== false && strpos($url, $check_path) !== false)
		{
			$server_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME');

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
				return true;
			}
		}

		return false;
	}
}

/**
* Main message parser for posting, pm, etc. takes raw message
* and parses it for attachments, bbcode and smilies
* @package phpBB3
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

	var $mode;

	/**
	* Init - give message here or manually
	*/
	function parse_message($message = '')
	{
		// Init BBCode UID
		$this->bbcode_uid = substr(md5(time()), 0, BBCODE_UID_LEN);

		if ($message)
		{
			$this->message = $message;
		}
	}

	/**
	* Parse Message
	*/
	function parse($allow_bbcode, $allow_magic_url, $allow_smilies, $allow_img_bbcode = true, $allow_flash_bbcode = true, $allow_quote_bbcode = true, $update_this_message = true, $mode = 'post')
	{
		global $config, $db, $user;

		$mode = ($mode != 'post') ? 'sig' : 'post';

		$this->mode = $mode;

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
		$match = array('#\r\n?#', "#([\n][\s]+){3,}#", '#(script|about|applet|activex|chrome):#i');
		$replace = array("\n", "\n\n", "\\1&#058;");
		$this->message = preg_replace($match, $replace, trim($this->message));

		// Message length check. -1 disables this check completely.
		if ($config['max_' . $mode . '_chars'] != -1)
		{
			$msg_len = ($mode == 'post') ? strlen($this->message) : strlen(preg_replace('#\[\/?[a-z\*\+\-]+(=[\S]+)?\]#is', ' ', $this->message));
	
			if ((!$msg_len && $mode !== 'sig') || $config['max_' . $mode . '_chars'] && $msg_len > $config['max_' . $mode . '_chars'])
			{
				$this->warn_msg[] = (!$msg_len) ? $user->lang['TOO_FEW_CHARS'] : $user->lang['TOO_MANY_CHARS'];
				return $this->warn_msg;
			}
		}

		// Prepare BBcode (just prepares some tags for better parsing)
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
		return false;
	}

	/**
	* Formatting text for display
	*/
	function format_display($allow_bbcode, $allow_magic_url, $allow_smilies, $update_this_message = true)
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
			$this->parse($allow_bbcode, $allow_magic_url, $allow_smilies, $this->allow_img_bbcode, $this->allow_flash_bbcode, $this->allow_quote_bbcode, true);
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
		global $db, $user, $phpbb_root_path;
		static $match;
		static $replace;

		// See if the static arrays have already been filled on an earlier invocation
		if (!is_array($match))
		{
			$match = $replace = array();

			// NOTE: obtain_* function? chaching the table contents?
	
			// For now setting the ttl to 10 minutes
			switch (SQL_LAYER)
			{
				case 'mssql':
				case 'mssql_odbc':
					$sql = 'SELECT * 
						FROM ' . SMILIES_TABLE . '
						ORDER BY LEN(code) DESC';
				break;
	
				case 'firebird':
					$sql = 'SELECT * 
						FROM ' . SMILIES_TABLE . '
						ORDER BY STRLEN(code) DESC';
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
				// (assertion)
				$match[] = '#(?<=^|[\n ]|\.)' . preg_quote($row['code'], '#') . '#';
				$replace[] = '<!-- s' . $row['code'] . ' --><img src="{SMILIES_PATH}/' . $row['smiley_url'] . '" border="0" alt="' . $row['emotion'] . '" title="' . $row['emotion'] . '" /><!-- s' . $row['code'] . ' -->';
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

	/**
	* Parse Attachments
	*/
	function parse_attachments($form_name, $mode, $forum_id, $submit, $preview, $refresh, $is_message = false)
	{
		global $config, $auth, $user, $phpbb_root_path, $phpEx;

		$error = array();

		$num_attachments = sizeof($this->attachment_data);
		$this->filename_data['filecomment'] = request_var('filecomment', '', true);
		$upload_file = (isset($_FILES[$form_name]) && $_FILES[$form_name]['name'] != 'none' && trim($_FILES[$form_name]['name'])) ? true : false;

		$add_file		= (isset($_POST['add_file'])) ? true : false;
		$delete_file	= (isset($_POST['delete_file'])) ? true : false;
		$edit_comment	= (isset($_POST['edit_comment'])) ? true : false;

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
					$new_entry = array(
						'physical_filename'	=> $filedata['physical_filename'],
						'attach_comment'	=> $this->filename_data['filecomment'],
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
				include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

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
					$actual_comment_list = request_var('comment_list', array(''), true);

					$edit_comment = request_var('edit_comment', array(0 => ''));
					$edit_comment = key($edit_comment);
					$this->attachment_data[$edit_comment]['attach_comment'] = $actual_comment_list[$edit_comment];
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
								'attach_comment'	=> $this->filename_data['filecomment'],
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

	/**
	* Get Attachment Data
	*/
	function get_submitted_attachment_data($check_user_id = false)
	{
		global $user, $db, $phpbb_root_path, $phpEx, $config;

		$this->filename_data['filecomment'] = request_var('filecomment', '', true);
		$this->attachment_data = (isset($_POST['attachment_data'])) ? $_POST['attachment_data'] : array();

		$check_user_id = ($check_user_id === false) ? $user->data['user_id'] : $check_user_id;

		// Regenerate data array...
		$attach_ids = $filenames = array();

		foreach ($this->attachment_data as $pos => $var_ary)
		{
			if ($var_ary['attach_id'])
			{
				$attach_ids[(int) $this->attachment_data[$pos]['attach_id']] = $pos;
			}
			else
			{
				$filenames[$pos] = '';
				set_var($filenames[$pos], $this->attachment_data[$pos]['physical_filename'], 'string');
				$filenames[$pos] = basename($filenames[$pos]);
			}
		}

		$this->attachment_data = array();

		// Regenerate already posted attachments...
		if (sizeof($attach_ids))
		{
			// Get the data from the attachments
			$sql = 'SELECT attach_id, physical_filename, real_filename, extension, mimetype, filesize, filetime, thumbnail
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('attach_id', array_keys($attach_ids)) . '
					AND poster_id = ' . $check_user_id;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (isset($attach_ids[$row['attach_id']]))
				{
					$pos = $attach_ids[$row['attach_id']];
					$this->attachment_data[$pos] = $row;
					set_var($this->attachment_data[$pos]['attach_comment'], $_POST['attachment_data'][$pos]['attach_comment'], 'string', true);

					unset($attach_ids[$row['attach_id']]);
				}
			}
			$db->sql_freeresult($result);

			if (sizeof($attach_ids))
			{
				trigger_error($user->lang['NO_ACCESS_ATTACHMENT'], E_USER_ERROR);
			}
		}

		// Regenerate newly uploaded attachments
		if (sizeof($filenames))
		{
			include_once($phpbb_root_path . 'includes/functions_upload.' . $phpEx);

			$sql = 'SELECT attach_id
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('LOWER(physical_filename)', array_map('strtolower', $filenames));
			$result = $db->sql_query_limit($sql, 1);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row)
			{
				trigger_error($user->lang['NO_ACCESS_ATTACHMENT'], E_USER_ERROR);
			}

			foreach ($filenames as $pos => $physical_filename)
			{
				$this->attachment_data[$pos] = array(
					'physical_filename'	=> $physical_filename,
					'extension'			=> strtolower(filespec::get_extension($phpbb_root_path . $config['upload_path'] . '/' . $physical_filename)),
					'filesize'			=> filespec::get_filesize($phpbb_root_path . $config['upload_path'] . '/' . $physical_filename),
					'attach_id'			=> 0,
					'thumbnail'			=> (file_exists($phpbb_root_path . $config['upload_path'] . '/thumb_' . $physical_filename)) ? 1 : 0,
				);

				set_var($this->attachment_data[$pos]['attach_comment'], $_POST['attachment_data'][$pos]['attach_comment'], 'string', true);
				set_var($this->attachment_data[$pos]['real_filename'], $_POST['attachment_data'][$pos]['real_filename'], 'string', true);
				set_var($this->attachment_data[$pos]['filetime'], $_POST['attachment_data'][$pos]['filetime'], 'int');

				if (strpos($_POST['attachment_data'][$pos]['mimetype'], 'image/') !== false)
				{
					set_var($this->attachment_data[$pos]['mimetype'], $_POST['attachment_data'][$pos]['mimetype'], 'string');
				}
				else
				{
					$this->attachment_data[$pos]['mimetype'] = filespec::get_mimetype($phpbb_root_path . $config['upload_path'] . '/' . $physical_filename);
				}
			}
		}
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


		$poll['poll_option_text'] = $this->parse($poll['enable_bbcode'], $poll['enable_urls'], $poll['enable_smilies'], $poll['img_status'], false, false, false);


		$this->message = $tmp_message;

		// Parse Poll Title
		$tmp_message = $this->message;
		$this->message = $poll['poll_title'];


		$poll['poll_title'] = $this->parse($poll['enable_bbcode'], $poll['enable_urls'], $poll['enable_smilies'], $poll['img_status'], false, false, false);


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

?>