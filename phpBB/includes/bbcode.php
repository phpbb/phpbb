<?php
/***************************************************************************
 *                              bbcode.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: bbcode.php,v 1.1 2010/10/10 15:05:27 orynider Exp $
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

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
}

define("BBCODE_UID_LEN", 10);


/**
* @package phpBB3
*/
class bitfield
{
	var $data;

	function bitfield($bitfield = '')
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
* BBCode class
* @package phpBB3
*/
class bbcode
{
	var $bbcode_uid = '';
	var $bbcode_bitfield = '';
	var $bbcode_cache = array();
	var $bbcode_template = array();

	var $bbcodes = array();

	var $template_bitfield;
	var $template_filename = '';

	/**
	* Constructor
	* Init bbcode cache entries if bitfield is specified
	*/
	function bbcode($bitfield = '')
	{
		if ($bitfield)
		{
			$this->bbcode_bitfield = $bitfield;
			$this->bbcode_cache_init();
		}
	}

	/**
	* Second pass bbcodes
	*/
	function bbcode_second_pass(&$message, $bbcode_uid = '', $bbcode_bitfield = false)
	{
		if ($bbcode_uid)
		{
			$this->bbcode_uid = $bbcode_uid;
		}

		if ($bbcode_bitfield !== false)
		{
			$this->bbcode_bitfield = $bbcode_bitfield;

			// Init those added with a new bbcode_bitfield (already stored codes will not get parsed again)
			$this->bbcode_cache_init();
		}

		if (!$this->bbcode_bitfield)
		{
			// Remove the uid from tags that have not been transformed into HTML
			if ($this->bbcode_uid)
			{
				$message = str_replace(':' . $this->bbcode_uid, '', $message);
			}

			return;
		}

		$str = array('search' => array(), 'replace' => array());
		$preg = array('search' => array(), 'replace' => array());

		$bitfield = new bitfield($this->bbcode_bitfield);
		$bbcodes_set = $bitfield->get_all_set();

		$undid_bbcode_specialchars = false;
		foreach ($bbcodes_set as $bbcode_id)
		{
			if (!empty($this->bbcode_cache[$bbcode_id]))
			{
				foreach ($this->bbcode_cache[$bbcode_id] as $type => $array)
				{
					foreach ($array as $search => $replace)
					{
						${$type}['search'][] = str_replace('$uid', $this->bbcode_uid, $search);
						${$type}['replace'][] = $replace;
					}

					if (count($str['search']))
					{
						$message = str_replace($str['search'], $str['replace'], $message);
						$str = array('search' => array(), 'replace' => array());
					}

					if (count($preg['search']))
					{
						// we need to turn the entities back into their original form to allow the
						// search patterns to work properly
						if (!$undid_bbcode_specialchars)
						{
							$message = str_replace(array('&#58;', '&#46;'), array(':', '.'), $message);
							$undid_bbcode_specialchars = true;
						}

						foreach ($preg['search'] as $key => $search)
						{
							if (is_callable($preg['replace'][$key]))
							{
								$message = preg_replace_callback($search, $preg['replace'][$key], $message);
							}
							else
							{
								$message = @preg_replace($search, $preg['replace'][$key], $message);
							}
						}
						//Changed for php 5.6+ $message = preg_replace($preg['search'], $preg['replace'], $message);
						$preg = array('search' => array(), 'replace' => array());
					}
				}
			}
		}

		// Remove the uid from tags that have not been transformed into HTML
		$message = str_replace(':' . $this->bbcode_uid, '', $message);
	}

	/**
	* Init bbcode cache
	*
	* requires: $this->bbcode_bitfield
	* sets: $this->bbcode_cache with bbcode templates needed for bbcode_bitfield
	*/
	function bbcode_cache_init()
	{
		global $user, $phpbb_root_path;

		if (empty($this->template_filename))
		{
			if (empty($user->template_name))
			{
				trigger_error('The style ' . $user->template_name . ' is empty.', E_USER_ERROR);
			}

			$this->template_bitfield = new bitfield($user->theme['bbcode_bitfield']);

			if (@file_exists($this->template_filename = $phpbb_root_path . $user->template_path . $user->template_name . '/template/bbcode.html'))
			{
				$this->template_filename = $phpbb_root_path . $user->template_path . $user->template_name . '/template/bbcode.html';			
			}
			elseif (@file_exists($this->template_filename = $phpbb_root_path . $user->template_path . $user->template_name . '/template/bbcode.tpl'))
			{
				$this->template_filename = $phpbb_root_path . $user->template_path . $user->template_name . '/template/bbcode.tpl';			
			}			
			if (@file_exists($this->template_filename = $phpbb_root_path . $user->template_path . $user->template_name . '/bbcode.tpl'))
			{
				$this->template_filename = $phpbb_root_path . $user->template_path . $user->template_name . '/bbcode.tpl';			
			}
			elseif (@file_exists($this->template_filename = $phpbb_root_path . $user->template_path . $user->template_name . '/bbcode.html'))
			{
				$this->template_filename = $phpbb_root_path . $user->template_path . $user->template_name . '/bbcode.html';			
			}			
			$this->template_filename2 = substr_count($this->template_filename, 'html') ? str_replace(".html", ".tpl", $this->template_filename) : str_replace(".tpl", ".html", $this->template_filename);
			if (!@file_exists($this->template_filename))
			{
				if (!@file_exists($this->template_filename2))
				{
					trigger_error('The file ' . $this->template_filename . ' is missing.', E_USER_ERROR);
				}
				else
				{
					$this->template_filename = $this->template_filename2;
				}				
			}
		}

		$bbcode_ids = $rowset = $sql = array();

		$bitfield = new bitfield($this->bbcode_bitfield);
		$bbcodes_set = $bitfield->get_all_set();

		foreach ($bbcodes_set as $bbcode_id)
		{
			if (isset($this->bbcode_cache[$bbcode_id]))
			{
				// do not try to re-cache it if it's already in
				continue;
			}
			$bbcode_ids[] = $bbcode_id;

			if ($bbcode_id > NUM_CORE_BBCODES)
			{
				$sql[] = $bbcode_id;
			}
		}

		if (sizeof($sql))
		{
			global $db;

			$sql = 'SELECT *
				FROM ' . BBCODES_TABLE . '
				WHERE ' . $db->sql_in_set('bbcode_id', $sql);
			$result = $db->sql_query($sql, 3600);

			while ($row = $db->sql_fetchrow($result))
			{
				// To circumvent replacing newlines with <br /> for the generated html,
				// we use carriage returns here. They are later changed back to newlines
				$row['bbcode_tpl'] = str_replace("\n", "\r", $row['bbcode_tpl']);
				$row['second_pass_replace'] = str_replace("\n", "\r", $row['second_pass_replace']);

				$rowset[$row['bbcode_id']] = $row;
			}
			$db->sql_freeresult($result);
		}

		foreach ($bbcode_ids as $bbcode_id)
		{
			switch ($bbcode_id)
			{
				case 0:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[/quote:$uid]'	=> $this->bbcode_tpl('quote_close', $bbcode_id)
						),
						'preg' => array(
							'#\[quote(?:=&quot;(.*?)&quot;)?:$uid\]((?!\[quote(?:=&quot;.*?&quot;)?:$uid\]).)?#ise'	=> "\$this->bbcode_second_pass_quote('\$1', '\$2')"
						)
					);
				break;

				case 1:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[b:$uid]'	=> $this->bbcode_tpl('b_open', $bbcode_id),
							'[/b:$uid]'	=> $this->bbcode_tpl('b_close', $bbcode_id),
						)
					);
				break;

				case 2:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[i:$uid]'	=> $this->bbcode_tpl('i_open', $bbcode_id),
							'[/i:$uid]'	=> $this->bbcode_tpl('i_close', $bbcode_id),
						)
					);
				break;

				case 3:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[url:$uid\]((.*?))\[/url:$uid\]#s'			=> $this->bbcode_tpl('url', $bbcode_id),
							'#\[url=([^\[]+?):$uid\](.*?)\[/url:$uid\]#s'	=> $this->bbcode_tpl('url', $bbcode_id),
						)
					);
				break;

				case 4:
					if ($user->optionget('viewimg'))
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[img:$uid\](.*?)\[/img:$uid\]#s'		=> $this->bbcode_tpl('img', $bbcode_id),
							)
						);
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[img:$uid\](.*?)\[/img:$uid\]#s'		=> str_replace('$2', '[ img ]', $this->bbcode_tpl('url', $bbcode_id, true)),
							)
						);
					}
				break;

				case 5:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[size=([\-\+]?\d+):$uid\](.*?)\[/size:$uid\]#s'	=> $this->bbcode_tpl('size', $bbcode_id),
						)
					);
				break;

				case 6:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'!\[color=(#[0-9a-f]{6}|[a-z\-]+):$uid\](.*?)\[/color:$uid\]!is'	=> $this->bbcode_tpl('color', $bbcode_id),
						)
					);
				break;

				case 7:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[u:$uid]'	=> $this->bbcode_tpl('u_open', $bbcode_id),
							'[/u:$uid]'	=> $this->bbcode_tpl('u_close', $bbcode_id),
						)
					);
				break;

				case 8:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[code(?:=([a-z]+))?:$uid\](.*?)\[/code:$uid\]#ise'	=> "\$this->bbcode_second_pass_code('\$1', '\$2')",
						)
					);
				break;

				case 9:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#(\[\/?(list|\*):[mou]?:?$uid\])[\n]{1}#'	=> "\$1",
							'#(\[list=([^\[]+):$uid\])[\n]{1}#'			=> "\$1",
							'#\[list=([^\[]+):$uid\]#e'					=> "\$this->bbcode_list('\$1')",
						),
						'str' => array(
							'[list:$uid]'		=> $this->bbcode_tpl('ulist_open_default', $bbcode_id),
							'[/list:u:$uid]'	=> $this->bbcode_tpl('ulist_close', $bbcode_id),
							'[/list:o:$uid]'	=> $this->bbcode_tpl('olist_close', $bbcode_id),
							'[*:$uid]'			=> $this->bbcode_tpl('listitem', $bbcode_id),
							'[/*:$uid]'			=> $this->bbcode_tpl('listitem_close', $bbcode_id),
							'[/*:m:$uid]'		=> $this->bbcode_tpl('listitem_close', $bbcode_id)
						),
					);
				break;

				case 10:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[email:$uid\]((.*?))\[/email:$uid\]#is'			=> $this->bbcode_tpl('email', $bbcode_id),
							'#\[email=([^\[]+):$uid\](.*?)\[/email:$uid\]#is'	=> $this->bbcode_tpl('email', $bbcode_id)
						)
					);
				break;

				case 11:
					if ($user->optionget('viewflash'))
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[flash=([0-9]+),([0-9]+):$uid\](.*?)\[/flash:$uid\]#'	=> $this->bbcode_tpl('flash', $bbcode_id),
							)
						);
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[flash=([0-9]+),([0-9]+):$uid\](.*?)\[/flash:$uid\]#'	=> str_replace('$1', '$3', str_replace('$2', '[ flash ]', $this->bbcode_tpl('url', $bbcode_id, true)))
							)
						);
					}
				break;

				case 12:
					$this->bbcode_cache[$bbcode_id] = array(
						'str'	=> array(
							'[/attachment:$uid]'	=> $this->bbcode_tpl('inline_attachment_close', $bbcode_id)
						),
						'preg'	=> array(
							'#\[attachment=([0-9]+):$uid\]#'	=> $this->bbcode_tpl('inline_attachment_open', $bbcode_id)
						)
					);
				break;

				default:
					if (isset($rowset[$bbcode_id]))
					{
						if ($this->template_bitfield->get($bbcode_id))
						{
							// The bbcode requires a custom template to be loaded
							if (!$bbcode_tpl = $this->bbcode_tpl($rowset[$bbcode_id]['bbcode_tag'], $bbcode_id))
							{
								// For some reason, the required template seems not to be available, use the default template
								$bbcode_tpl = (!empty($rowset[$bbcode_id]['second_pass_replace'])) ? $rowset[$bbcode_id]['second_pass_replace'] : $rowset[$bbcode_id]['bbcode_tpl'];
							}
							else
							{
								// In order to use templates with custom bbcodes we need
								// to replace all {VARS} to corresponding backreferences
								// Note that backreferences are numbered from bbcode_match
								if (preg_match_all('/\{(URL|LOCAL_URL|EMAIL|TEXT|SIMPLETEXT|IDENTIFIER|COLOR|NUMBER)[0-9]*\}/', $rowset[$bbcode_id]['bbcode_match'], $m))
								{
									foreach ($m[0] as $i => $tok)
									{
										$bbcode_tpl = str_replace($tok, '$' . ($i + 1), $bbcode_tpl);
									}
								}
							}
						}
						else
						{
							// Default template
							$bbcode_tpl = (!empty($rowset[$bbcode_id]['second_pass_replace'])) ? $rowset[$bbcode_id]['second_pass_replace'] : $rowset[$bbcode_id]['bbcode_tpl'];
						}

						// Replace {L_*} lang strings
						$bbcode_tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(\$user->lang['\$1'])) ? \$user->lang['\$1'] : ucwords(strtolower(str_replace('_', ' ', '\$1')))", $bbcode_tpl);

						if (!empty($rowset[$bbcode_id]['second_pass_replace']))
						{
							// The custom BBCode requires second-pass pattern replacements
							$this->bbcode_cache[$bbcode_id] = array(
								'preg' => array($rowset[$bbcode_id]['second_pass_match'] => $bbcode_tpl)
							);
						}
						else
						{
							$this->bbcode_cache[$bbcode_id] = array(
								'str' => array($rowset[$bbcode_id]['second_pass_match'] => $bbcode_tpl)
							);
						}
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = false;
					}
				break;
			}
		}
	}

	/**
	* Return bbcode template
	*/
	function bbcode_tpl($tpl_name, $bbcode_id = -1, $skip_bitfield_check = false)
	{
		static $bbcode_hardtpl = array();
		if (empty($bbcode_hardtpl))
		{
			global $user;

			$bbcode_hardtpl = array(
				'b_open'	=> '<span style="font-weight: bold">',
				'b_close'	=> '</span>',
				'i_open'	=> '<span style="font-style: italic">',
				'i_close'	=> '</span>',
				'u_open'	=> '<span style="text-decoration: underline">',
				'u_close'	=> '</span>',
				'img'		=> '<img src="$1" alt="' . $user->lang['IMAGE'] . '" />',
				'size'		=> '<span style="font-size: $1%; line-height: normal">$2</span>',
				'color'		=> '<span style="color: $1">$2</span>',
				'email'		=> '<a href="mailto:$1">$2</a>'
			);
		}

		if ($bbcode_id != -1 && !$skip_bitfield_check && !$this->template_bitfield->get($bbcode_id))
		{
			return (isset($bbcode_hardtpl[$tpl_name])) ? $bbcode_hardtpl[$tpl_name] : false;
		}

		if (empty($this->bbcode_template))
		{
			if (($tpl = file_get_contents($this->template_filename)) === false)
			{
				trigger_error('Could not load bbcode template', E_USER_ERROR);
			}

			// replace \ with \\ and then ' with \'.
			$tpl = str_replace('\\', '\\\\', $tpl);
			$tpl = str_replace("'", "\'", $tpl);

			// strip newlines and indent
			$tpl = preg_replace("/\n[\n\r\s\t]*/", '', $tpl);

			// Turn template blocks into PHP assignment statements for the values of $bbcode_tpl..
			$this->bbcode_template = array();

			$matches = preg_match_all('#<!-- BEGIN (.*?) -->(.*?)<!-- END (?:.*?) -->#', $tpl, $match);

			for ($i = 0; $i < $matches; $i++)
			{
				if (empty($match[1][$i]))
				{
					continue;
				}

				$this->bbcode_template[$match[1][$i]] = $this->bbcode_tpl_replace($match[1][$i], $match[2][$i]);
			}
		}

		return (isset($this->bbcode_template[$tpl_name])) ? $this->bbcode_template[$tpl_name] : ((isset($bbcode_hardtpl[$tpl_name])) ? $bbcode_hardtpl[$tpl_name] : false);
	}

	/**
	* Return bbcode template replacement
	*/
	function bbcode_tpl_replace($tpl_name, $tpl)
	{
		global $user;

		static $replacements = array(
			'quote_username_open'	=> array('{USERNAME}'	=> '$1'),
			'color'					=> array('{COLOR}'		=> '$1', '{TEXT}'			=> '$2'),
			'size'					=> array('{SIZE}'		=> '$1', '{TEXT}'			=> '$2'),

	// Mighty Gorgon - Full Album Pack - BEGIN
	// Get Album PIC based on ID
			'img_num_open'			=> array('{IMG_NUM}'		=> '$1', '{TEXT}'			=> '$2'),

			'album_showpage_open'	=> array('{ALBUM_SHOWPAGE}'		=> '$1', '{TEXT}'			=> '$2'),

			'album_picm_open'		=> array('{ALBUM_PICM}'		=> '$1', '{TEXT}'			=> '$2'),
			'album_thumbnail_open'	=> array('{ALBUM_THUMBNAIL}'		=> '$1', '{TEXT}'			=> '$2'),

	// Mighty Gorgon - Full Album Pack - END

			'email'					=> array('{EMAIL}'		=> '$1', '{DESCRIPTION}'	=> '$2'),

//====================================================================== |
//==== Start Advanced BBCode Box MOD =================================== |
//==== v5.1.0 ========================================================== |
//====
			'spoil_open'		=> array('{L_BBCODEBOX_HIDDEN}'		=> '$1', '{TEXT}'			=> '$2'),
			'spoil_open'		=> array('{L_BBCODEBOX_VIEW}'		=> '$1', '{TEXT}'			=> '$2'),
			'spoil_open'		=> array('{L_BBCODEBOX_HIDE}'		=> '$1', '{TEXT}'			=> '$2'),
			'align_open'		=> array('{ALIG}'		=> '$1', '{TEXT}'			=> '$2'),
			'stream'			=> array('{URL}'		=> '$1', '{TEXT}'			=> '$2'),
			'marq_open'			=> array('{MARQ}'		=> '$1', '{TEXT}'			=> '$2'),
			'web'					=> array('{URL}'		=> '$1', '{TEXT}'			=> '$2'),
			'flash'					=> array('{WIDTH}'		=> '$1', '{TEXT}'			=> '$2'),
			'flash'					=> array('{HEIGHT}'		=> '$1', '{TEXT}'			=> '$2'),
			'flash'					=> array('{URL}'		=> '$1', '{TEXT}'			=> '$2'),
			'video'					=> array('{URL}'		=> '$1', '{TEXT}'			=> '$2'),
			'video'					=> array('{WIDTH}'		=> '$1', '{TEXT}'			=> '$2'),
			'video'					=> array('{HEIGHT}'		=> '$1', '{TEXT}'			=> '$2'),
			'font_open'			=> array('{FONT}'		=> '$1', '{TEXT}'			=> '$2'),
			'poet_open'			=> array('{POET}'		=> '$1', '{TEXT}'			=> '$2'),
			'GVideo'			=> array('{GVIDEOID}'		=> '$1', '{TEXT}'			=> '$2'),
			'GVideo'			=> array('{GVIDEOLINK}'		=> '$1', '{TEXT}'			=> '$2'),
			'youtube'			=> array('{YOUTUBEID}'		=> '$1', '{TEXT}'			=> '$2'),
			'youtube'			=> array('{YOUTUBELINK}'	=> '$1', '{TEXT}'			=> '$2'),
//====



			'img'					=> array('{URL}'		=> '$1'),
			'flash'					=> array('{WIDTH}'		=> '$1', '{HEIGHT}'			=> '$2', '{URL}'	=> '$3'),
			'url'					=> array('{URL}'		=> '$1', '{DESCRIPTION}'	=> '$2'),
			'email'					=> array('{EMAIL}'		=> '$1', '{DESCRIPTION}'	=> '$2')
		);

		$tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(\$user->lang['\$1'])) ? \$user->lang['\$1'] : ucwords(strtolower(str_replace('_', ' ', '\$1')))", $tpl);

		if (!empty($replacements[$tpl_name]))
		{
			$tpl = strtr($tpl, $replacements[$tpl_name]);
		}

		return trim($tpl);
	}

	/**
	* Second parse list bbcode
	*/
	function bbcode_list($type)
	{
		if ($type == '')
		{
			$tpl = 'ulist_open_default';
			$type = 'default';
		}
		else if ($type == 'i')
		{
			$tpl = 'olist_open';
			$type = 'lower-roman';
		}
		else if ($type == 'I')
		{
			$tpl = 'olist_open';
			$type = 'upper-roman';
		}
		else if (preg_match('#^(disc|circle|square)$#i', $type))
		{
			$tpl = 'ulist_open';
			$type = strtolower($type);
		}
		else if (preg_match('#^[a-z]$#', $type))
		{
			$tpl = 'olist_open';
			$type = 'lower-alpha';
		}
		else if (preg_match('#[A-Z]#', $type))
		{
			$tpl = 'olist_open';
			$type = 'upper-alpha';
		}
		else if (is_numeric($type))
		{
			$tpl = 'olist_open';
			$type = 'arabic-numbers';
		}
		else
		{
			$tpl = 'olist_open';
			$type = 'arabic-numbers';
		}

		return str_replace('{LIST_TYPE}', $type, $this->bbcode_tpl($tpl));
	}

	/**
	* Second parse quote tag
	*/
	function bbcode_second_pass_quote($username, $quote)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$quote = str_replace('\"', '"', $quote);
		$username = str_replace('\"', '"', $username);

		// remove newline at the beginning
		if ($quote == "\n")
		{
			$quote = '';
		}

		$quote = (($username) ? str_replace('$1', $username, $this->bbcode_tpl('quote_username_open')) : $this->bbcode_tpl('quote_open')) . $quote;

		return $quote;
	}

	/**
	* Second parse code tag
	*/
	function bbcode_second_pass_code($type, $code)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$code = str_replace('\"', '"', $code);

		switch ($type)
		{
			case 'php':
				// Not the english way, but valid because of hardcoded syntax highlighting
				if (strpos($code, '<span class="syntaxdefault"><br /></span>') === 0)
				{
					$code = substr($code, 41);
				}

			// no break;

			default:
				$code = str_replace("\t", '&nbsp; &nbsp;', $code);
				$code = str_replace('  ', '&nbsp; ', $code);
				$code = str_replace('  ', ' &nbsp;', $code);

				// remove newline at the beginning
				if (!empty($code) && $code[0] == "\n")
				{
					$code = substr($code, 1);
				}
			break;
		}

		$code = $this->bbcode_tpl('code_open') . $code . $this->bbcode_tpl('code_close');

		return $code;
	}
}

// global that holds loaded-and-prepared bbcode templates, so we only have to do
// that stuff once.

$bbcode_tpl = null;

/**
 * Loads bbcode templates from the bbcode.tpl file of the current template set.
 * Creates an array, keys are bbcode names like "b_open" or "url", values
 * are the associated template.
 * Probably pukes all over the place if there's something really screwed
 * with the bbcode.tpl file.
 *
 * Nathan Codding, Sept 26 2001.
 */
function load_bbcode_template()
{
	global $template;
	$tpl_filename = $template->make_filename('bbcode.tpl');
	$tpl = fread(fopen($tpl_filename, 'r'), filesize($tpl_filename));

	// replace \ with \\ and then ' with \'.
	$tpl = str_replace('\\', '\\\\', $tpl);
	$tpl  = str_replace('\'', '\\\'', $tpl);

	// strip newlines.
	$tpl  = str_replace("\n", '', $tpl);

	// Turn template blocks into PHP assignment statements for the values of $bbcode_tpls..
	$tpl = preg_replace('#<!-- BEGIN (.*?) -->(.*?)<!-- END (.*?) -->#', "\n" . '$bbcode_tpls[\'\\1\'] = \'\\2\';', $tpl);

	$bbcode_tpls = array();

	eval($tpl);

	return $bbcode_tpls;
}


/**
 * Prepares the loaded bbcode templates for insertion into preg_replace()
 * or str_replace() calls in the bbencode_second_pass functions. This
 * means replacing template placeholders with the appropriate preg backrefs
 * or with language vars. NOTE: If you change how the regexps work in
 * bbencode_second_pass(), you MUST change this function.
 *
 * Nathan Codding, Sept 26 2001
 *
 */
function prepare_bbcode_template($bbcode_tpl)
{
	global $user, $lang;

	$bbcode_tpl['spoil_open'] = !isset($bbcode_tpl['spoil_open']) ? 'spoil_open' : $bbcode_tpl['spoil_open'];
 	$bbcode_tpl['align_open'] = !isset($bbcode_tpl['align_open']) ? 'align_open' : $bbcode_tpl['align_open'];
 	$bbcode_tpl['stream'] = !isset($bbcode_tpl['stream']) ? 'stream' : $bbcode_tpl['stream'];
 	$bbcode_tpl['marq_open'] = !isset($bbcode_tpl['marq_open']) ? 'marq_open' : $bbcode_tpl['marq_open'];
 	$bbcode_tpl['web'] = !isset($bbcode_tpl['web']) ? 'web' : $bbcode_tpl['web'];
 	$bbcode_tpl['flash'] = !isset($bbcode_tpl['flash']) ? 'flash' : $bbcode_tpl['flash'];
 	$bbcode_tpl['video'] = !isset($bbcode_tpl['video']) ? 'video' : $bbcode_tpl['video'];
 	$bbcode_tpl['font_open'] = !isset($bbcode_tpl['font_open']) ? 'font_open' : $bbcode_tpl['font_open'];
 	$bbcode_tpl['poet_open'] = !isset($bbcode_tpl['poet_open']) ? 'poet_open' : $bbcode_tpl['poet_open'];
 	$bbcode_tpl['GVideo'] = !isset($bbcode_tpl['GVideo']) ? 'GVideo' : $bbcode_tpl['GVideo'];
 	$bbcode_tpl['youtube'] = !isset($bbcode_tpl['youtube']) ? 'youtube' : $bbcode_tpl['youtube'];
 	$bbcode_tpl['albumimg'] = !isset($bbcode_tpl['albumimg']) ? 'albumimg' : $bbcode_tpl['albumimg'];
 	$bbcode_tpl['albumimgl'] = !isset($bbcode_tpl['albumimgl']) ? 'albumimgl' : $bbcode_tpl['albumimgl'];
 	$bbcode_tpl['albumimgr'] = !isset($bbcode_tpl['albumimgr']) ? 'albumimgr' : $bbcode_tpl['albumimgr'];
 	$bbcode_tpl['albumimgc'] = !isset($bbcode_tpl['albumimgc']) ? 'albumimgc' : $bbcode_tpl['albumimgc'];
 	$bbcode_tpl['fullalbumimg'] = !isset($bbcode_tpl['fullalbumimg']) ? 'fullalbumimg' : $bbcode_tpl['fullalbumimg'];
 	$bbcode_tpl['fade_open'] = !isset($bbcode_tpl['fade_open']) ? 'fade_open' : $bbcode_tpl['fade_open'];
 	$bbcode_tpl['fade_close'] = !isset($bbcode_tpl['fade_close']) ? 'fade_close' : $bbcode_tpl['fade_close'];
 	$bbcode_tpl['align_close'] = !isset($bbcode_tpl['align_close']) ? 'align_close' : $bbcode_tpl['align_close'];
 	$bbcode_tpl['marq_close'] = !isset($bbcode_tpl['marq_close']) ? 'marq_close' : $bbcode_tpl['marq_close'];
 	$bbcode_tpl['font_close'] = !isset($bbcode_tpl['font_close']) ? 'font_close' : $bbcode_tpl['font_close'];
 	$bbcode_tpl['hr'] = !isset($bbcode_tpl['hr']) ? 'hr' : $bbcode_tpl['hr'];
 	$bbcode_tpl['spoil_close'] = !isset($bbcode_tpl['spoil_close']) ? 'spoil_close' : $bbcode_tpl['spoil_close'];
	
 	$bbcode_tpl['color_open'] = !isset($bbcode_tpl['color_open']) ? 'color_open' : $bbcode_tpl['size_close'];
 	$bbcode_tpl['size_open'] = !isset($bbcode_tpl['size_open']) ? 'size_open' : $bbcode_tpl['size_open'];
 	$bbcode_tpl['img_num_open'] = !isset($bbcode_tpl['img_num_open']) ? 'img_num_open' : $bbcode_tpl['img_num_open'];
 	$bbcode_tpl['album_showpage_open'] = !isset($bbcode_tpl['album_showpage_open']) ? 'album_showpage_open' : $bbcode_tpl['album_showpage_open'];
 	$bbcode_tpl['album_picm_open'] = !isset($bbcode_tpl['album_picm_open']) ? 'album_picm_open' : $bbcode_tpl['album_picm_open'];
 	$bbcode_tpl['album_thumbnail_open'] = !isset($bbcode_tpl['album_thumbnail_open']) ? 'album_thumbnail_open' : $bbcode_tpl['album_thumbnail_open'];
 	$bbcode_tpl['color_close'] = !isset($bbcode_tpl['color_close']) ? 'color_close' : $bbcode_tpl['color_close'];
 	$bbcode_tpl['size_close'] = !isset($bbcode_tpl['size_close']) ? 'size_close' : $bbcode_tpl['size_close'];
	
	$bbcode_tpl['olist_open'] = str_replace('{LIST_TYPE}', '\\1', $bbcode_tpl['olist_open']);

	$bbcode_tpl['color_open'] = str_replace('{COLOR}', '\\1', $bbcode_tpl['color_open']);

	$bbcode_tpl['size_open'] = str_replace('{SIZE}', '\\1', $bbcode_tpl['size_open']);

	$bbcode_tpl['quote_open'] = str_replace('{L_QUOTE}', $lang['Quote'], $bbcode_tpl['quote_open']);

	$bbcode_tpl['quote_username_open'] = str_replace('{L_QUOTE}', $lang['Quote'], $bbcode_tpl['quote_username_open']);
	$bbcode_tpl['quote_username_open'] = str_replace('{L_WROTE}', $lang['wrote'], $bbcode_tpl['quote_username_open']);
	$bbcode_tpl['quote_username_open'] = str_replace('{USERNAME}', '\\1', $bbcode_tpl['quote_username_open']);

	$bbcode_tpl['code_open'] = str_replace('{L_CODE}', $lang['Code'], $bbcode_tpl['code_open']);

	$bbcode_tpl['img'] = str_replace('{URL}', '\\1', $bbcode_tpl['img']);

	// We do URLs in several different ways..
	$bbcode_tpl['url1'] = str_replace('{URL}', '\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url1'] = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url1']);

	$bbcode_tpl['url2'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url2'] = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url2']);

	$bbcode_tpl['url3'] = str_replace('{URL}', '\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url3'] = str_replace('{DESCRIPTION}', '\\2', $bbcode_tpl['url3']);

	$bbcode_tpl['url4'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url4'] = str_replace('{DESCRIPTION}', '\\3', $bbcode_tpl['url4']);

	// Mighty Gorgon - Full Album Pack - BEGIN
	// Get Album PIC based on ID
	$bbcode_tpl['img_num_open'] = str_replace('{IMG_NUM}', '\\1', $bbcode_tpl['img_num_open']);

	$bbcode_tpl['album_showpage_open'] = str_replace('{ALBUM_SHOWPAGE}', ALBUM_SHOWPAGE, $bbcode_tpl['album_showpage_open']);

	$bbcode_tpl['album_picm_open'] = str_replace('{ALBUM_PICM}', ALBUM_PICM, $bbcode_tpl['album_picm_open']);
	$bbcode_tpl['album_thumbnail_open'] = str_replace('{ALBUM_THUMBNAIL}', ALBUM_THUMBNAIL, $bbcode_tpl['album_thumbnail_open']);

	// Mighty Gorgon - Full Album Pack - END

	$bbcode_tpl['email'] = str_replace('{EMAIL}', '\\1', $bbcode_tpl['email']);

//====================================================================== |
//==== Start Advanced BBCode Box MOD =================================== |
//==== v5.1.0 ========================================================== |
//====
	$bbcode_tpl['spoil_open'] = str_replace('{L_BBCODEBOX_HIDDEN}', $user->lang('BBCode_box_hidden'), $bbcode_tpl['spoil_open']);
	$bbcode_tpl['spoil_open'] = str_replace('{L_BBCODEBOX_VIEW}', $user->lang('BBcode_box_view'), $bbcode_tpl['spoil_open']);
	$bbcode_tpl['spoil_open'] = str_replace('{L_BBCODEBOX_HIDE}', $user->lang('BBcode_box_hide'), $bbcode_tpl['spoil_open']);
	$bbcode_tpl['align_open'] = str_replace('{ALIGN}', '\\1', $bbcode_tpl['align_open']);
	$bbcode_tpl['stream'] = str_replace('{URL}', '\\1', $bbcode_tpl['stream']);
	$bbcode_tpl['marq_open'] = str_replace('{MARQ}', '\\1', $bbcode_tpl['marq_open']);
	$bbcode_tpl['web'] = str_replace('{URL}', '\\1', $bbcode_tpl['web']);
	$bbcode_tpl['flash'] = str_replace('{WIDTH}', '\\1', $bbcode_tpl['flash']);
	$bbcode_tpl['flash'] = str_replace('{HEIGHT}', '\\2', $bbcode_tpl['flash']);
	$bbcode_tpl['flash'] = str_replace('{URL}', '\\3', $bbcode_tpl['flash']);
	$bbcode_tpl['video'] = str_replace('{URL}', '\\3', $bbcode_tpl['video']);
	$bbcode_tpl['video'] = str_replace('{WIDTH}', '\\1', $bbcode_tpl['video']);
	$bbcode_tpl['video'] = str_replace('{HEIGHT}', '\\2', $bbcode_tpl['video']);
	$bbcode_tpl['font_open'] = str_replace('{FONT}', '\\1', $bbcode_tpl['font_open']);
	$bbcode_tpl['poet_open'] = str_replace('{POET}', '\\1', $bbcode_tpl['poet_open']);
	$bbcode_tpl['GVideo'] = str_replace('{GVIDEOID}', '\\1', $bbcode_tpl['GVideo']);
    $bbcode_tpl['GVideo'] = str_replace('{GVIDEOLINK}', $user->lang('GVideo_link'), $bbcode_tpl['GVideo']);
    $bbcode_tpl['youtube'] = str_replace('{YOUTUBEID}', '\\1', $bbcode_tpl['youtube']);
    $bbcode_tpl['youtube'] = str_replace('{YOUTUBELINK}', $user->lang('youtube_link'), $bbcode_tpl['youtube']);
//====
//==== End Advanced BBCode Box MOD ==================================== |
//===================================================================== |


	define("BBCODE_TPL_READY", true);

	return $bbcode_tpl;
}


/**
 * Does second-pass bbencoding. This should be used before displaying the message in
 * a thread. Assumes the message is already first-pass encoded, and we are given the
 * correct UID as used in first-pass encoding.
 */
function bbencode_second_pass($text, $uid)
{
	global $lang, $bbcode_tpl;

	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);

	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// First: If there isn't a "[" and a "]" in the message, don't bother.
	if (! (strpos($text, "[") && strpos($text, "]")) )
	{
		// Remove padding, return.
		$text = substr($text, 1);
		return $text;
	}

	// Only load the templates ONCE..
	if (!defined("BBCODE_TPL_READY"))
	{
		// load templates from file into array.
		$bbcode_tpl = load_bbcode_template();

		// prepare array for use in regexps.
		$bbcode_tpl = prepare_bbcode_template($bbcode_tpl);
	}

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$text = bbencode_second_pass_code($text, $uid, $bbcode_tpl);

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$text = str_replace("[quote:$uid]", $bbcode_tpl['quote_open'], $text);
	$text = str_replace("[/quote:$uid]", $bbcode_tpl['quote_close'], $text);

	// New one liner to deal with opening quotes with usernames...
	// replaces the two line version that I had here before..
	$text = preg_replace("/\[quote:$uid=\"(.*?)\"\]/si", $bbcode_tpl['quote_username_open'], $text);

	// [list] and [list=x] for (un)ordered lists.
	// unordered lists
	$text = str_replace("[list:$uid]", $bbcode_tpl['ulist_open'], $text);
	// li tags
	$text = str_replace("[*:$uid]", $bbcode_tpl['listitem'], $text);
	// ending tags
	$text = str_replace("[/list:u:$uid]", $bbcode_tpl['ulist_close'], $text);
	$text = str_replace("[/list:o:$uid]", $bbcode_tpl['olist_close'], $text);
	// Ordered lists
	$text = preg_replace("/\[list=([a1]):$uid\]/si", $bbcode_tpl['olist_open'], $text);

	// colours
	$text = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", $bbcode_tpl['color_open'], $text);
	$text = str_replace("[/color:$uid]", $bbcode_tpl['color_close'], $text);

	// size
	$text = preg_replace("/\[size=([1-2]?[0-9]):$uid\]/si", $bbcode_tpl['size_open'], $text);
	$text = str_replace("[/size:$uid]", $bbcode_tpl['size_close'], $text);

	// [b] and [/b] for bolding text.
	$text = str_replace("[b:$uid]", $bbcode_tpl['b_open'], $text);
	$text = str_replace("[/b:$uid]", $bbcode_tpl['b_close'], $text);

	// [u] and [/u] for underlining text.
	$text = str_replace("[u:$uid]", $bbcode_tpl['u_open'], $text);
	$text = str_replace("[/u:$uid]", $bbcode_tpl['u_close'], $text);

	// [i] and [/i] for italicizing text.
	$text = str_replace("[i:$uid]", $bbcode_tpl['i_open'], $text);
	$text = str_replace("[/i:$uid]", $bbcode_tpl['i_close'], $text);

	// Patterns and replacements for URL and email tags..
	$patterns = array();
	$replacements = array();

	// [img]image_url_here[/img] code..
	// This one gets first-passed..
	$patterns[] = "#\[img:$uid\]([^?](?:[^\[]+|\[(?!url))*?)\[/img:$uid\]#i";
	$replacements[] = $bbcode_tpl['img'];

	// matches a [url]xxxx://www.phpbb.com[/url] code..
	$patterns[] = "#\[url\]([\w]+?://([\w\#$%&~/.\-;:=,?@\]+]+|\[(?!url=))*?)\[/url\]#is";
	$replacements[] = $bbcode_tpl['url1'];

	// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[url\]((www|ftp)\.([\w\#$%&~/.\-;:=,?@\]+]+|\[(?!url=))*?)\[/url\]#is";
	$replacements[] = $bbcode_tpl['url2'];

	// [url=xxxx://www.phpbb.com]phpBB[/url] code..
	$patterns[] = "#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
	$replacements[] = $bbcode_tpl['url3'];

	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[url=((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
	$replacements[] = $bbcode_tpl['url4'];

	// Mighty Gorgon - Full Album Pack - BEGIN
	// [albumimg]image number here[/albumimg]
	$album_img_patterns[1] = "#\[albumimg:$uid\]([0-9]+)\[/albumimg:$uid\]#si";
	$album_img_replacements[1] = $bbcode_tpl['albumimg'];

	// [albumimgl]image number here[/albumimgl]
	$album_img_patterns[2] = "#\[albumimgl:$uid\]([0-9]+)\[/albumimgl:$uid\]#si";
	$album_img_replacements[2] = $bbcode_tpl['albumimgl'];

	// [albumimgr]image number here[/albumimgr]
	$album_img_patterns[3] = "#\[albumimgr:$uid\]([0-9]+)\[/albumimgr:$uid\]#si";
	$album_img_replacements[3] = $bbcode_tpl['albumimgr'];

	// [albumimgc]image number here[/albumimgc]
	$album_img_patterns[4] = "#\[albumimgc:$uid\]([0-9]+)\[/albumimgc:$uid\]#si";
	$album_img_replacements[4] = $bbcode_tpl['albumimgc'];
	// site image-end

	// [fullalbumimg]image number here[/fullalbumimg]
	$album_img_patterns[5] = "#\[fullalbumimg:$uid\]([0-9]+)\[/fullalbumimg:$uid\]#si";
	$album_img_replacements[5] = $bbcode_tpl['fullalbumimg'];

	$text = preg_replace($album_img_patterns, $album_img_replacements, $text);
	// Mighty Gorgon - Full Album Pack - END

	// [email]user@domain.tld[/email] code..
	$patterns[] = "#\[email\]([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si";
	$replacements[] = $bbcode_tpl['email'];

//====================================================================== |
//==== Start Advanced BBCode Box MOD =================================== |
//==== v5.1.0 ========================================================== |
//====
	// [fade]Faded Text[/fade] code..
	$text = str_replace("[fade:$uid]", $bbcode_tpl['fade_open'], $text);
	$text = str_replace("[/fade:$uid]", $bbcode_tpl['fade_close'], $text);

	// [stream]Sound URL[/stream] code..
	$patterns[] = "#\[stream:$uid\](.*?)\[/stream:$uid\]#si";
	$replacements[] = $bbcode_tpl['stream'];

	// [flash width=X height=X]Flash URL[/flash] code..
	$patterns[] = "#\[flash width=([0-6]?[0-9]?[0-9]) height=([0-4]?[0-9]?[0-9]):$uid\](.*?)\[/flash:$uid\]#si";
	$replacements[] = $bbcode_tpl['flash'];

	// [video width=X height=X]Video URL[/video] code..
	$patterns[] = "#\[video width=([0-6]?[0-9]?[0-9]) height=([0-4]?[0-9]?[0-9]):$uid\](.*?)\[/video:$uid\]#si";
	$replacements[] = $bbcode_tpl['video'];
	$text = preg_replace($patterns, $replacements, $text);

	// [align=left/center/right/justify]Formatted Code[/align] code..
	$text = preg_replace("/\[align=(left|right|center|justify):$uid\]/si", $bbcode_tpl['align_open'], $text);
	$text = str_replace("[/align:$uid]", $bbcode_tpl['align_close'], $text);

	 // [marquee=left/right/up/down]Marquee Code[/marquee] code..
	$text = preg_replace("/\[marq=(left|right|up|down):$uid\]/si", $bbcode_tpl['marq_open'], $text);
	$text = str_replace("[/marq:$uid]", $bbcode_tpl['marq_close'], $text);

	// [font=fonttype]text[/font] code..
	$text = preg_replace("/\[font=(.*?):$uid\]/si", $bbcode_tpl['font_open'], $text);
	$text = str_replace("[/font:$uid]", $bbcode_tpl['font_close'], $text);

	// [hr]
	$text = str_replace("[hr:$uid]", $bbcode_tpl['hr'], $text);

	// [sub]Subscrip[/sub] code..
	$text = str_replace("[sub:$uid]", '<sub>', $text);
	$text = str_replace("[/sub:$uid]", '</sub>', $text);

	// [sup]Superscript[/sup] code..
	$text = str_replace("[sup:$uid]", '<sup>', $text);
	$text = str_replace("[/sup:$uid]", '</sup>', $text);

	// [strike]Strikethrough[/strike] code..
	$text = str_replace("[s:$uid]", '<strike>', $text);
	$text = str_replace("[/s:$uid]", '</strike>', $text);

	// [spoil]Spoiler[/spoil] code..
	$text = str_replace("[spoil:$uid]", $bbcode_tpl['spoil_open'], $text);
	$text = str_replace("[/spoil:$uid]", $bbcode_tpl['spoil_close'], $text);

	// [GVideo]GVideo URL[/GVideo] code..
    $patterns[] = "#\[GVideo\]http://video.google.[A-Za-z0-9.]{2,5}/videoplay\?docid=([0-9A-Za-z-_]*)[^[]*\[/GVideo\]#is";
    $replacements[] = $bbcode_tpl['GVideo'];

    // [youtube]YouTube URL[/youtube] code..
    $patterns[] = "#\[youtube\]http://(?:www\.)?youtube.com/watch\?v=([0-9A-Za-z-_]{11})[^[]*\[/youtube\]#is";
    $replacements[] = $bbcode_tpl['youtube'];

    // [youtube]YouTube URL[/youtube] code..
    $patterns[] = "#\[youtube\]http://(?:uk\.)?youtube.com/watch\?v=([0-9A-Za-z-_]{11})[^[]*\[/youtube\]#is";
    $replacements[] = $bbcode_tpl['youtube'];
//====
//==== End Advanced BBCode Box MOD ==================================== |
//===================================================================== |

	$text = preg_replace($patterns, $replacements, $text);

	// Remove our padding from the string..
	$text = substr($text, 1);

	return $text;

} // bbencode_second_pass()

// Need to initialize the random numbers only ONCE
mt_srand( (double) microtime() * 1000000);

function make_bbcode_uid()
{
	// Unique ID for this message..

	$uid = dss_rand();
	$uid = substr($uid, 0, BBCODE_UID_LEN);

	return $uid;
}

function bbencode_first_pass($text, $uid)
{
	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$text = bbencode_first_pass_pda($text, $uid, '[code]', '[/code]', '', true, '');

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$text = bbencode_first_pass_pda($text, $uid, '[quote]', '[/quote]', '', false, '');
	$text = bbencode_first_pass_pda($text, $uid, '/\[quote=\\\\&quot;(.*?)\\\\&quot;\]/is', '[/quote]', '', false, '', "[quote:$uid=\\\"\\1\\\"]");

	// [list] and [list=x] for (un)ordered lists.
	$open_tag = array();
	$open_tag[0] = "[list]";

	// unordered..
	$text = bbencode_first_pass_pda($text, $uid, $open_tag, "[/list]", "[/list:u]", false, 'replace_listitems');

	$open_tag[0] = "[list=1]";
	$open_tag[1] = "[list=a]";

	// ordered.
	$text = bbencode_first_pass_pda($text, $uid, $open_tag, "[/list]", "[/list:o]",  false, 'replace_listitems');

	// [color] and [/color] for setting text color
	$text = preg_replace("#\[color=(\#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/color\]#si", "[color=\\1:$uid]\\2[/color:$uid]", $text);

	// [size] and [/size] for setting text size
	$text = preg_replace("#\[size=([1-2]?[0-9])\](.*?)\[/size\]#si", "[size=\\1:$uid]\\2[/size:$uid]", $text);

	// [b] and [/b] for bolding text.
	$text = preg_replace("#\[b\](.*?)\[/b\]#si", "[b:$uid]\\1[/b:$uid]", $text);

	// [u] and [/u] for underlining text.
	$text = preg_replace("#\[u\](.*?)\[/u\]#si", "[u:$uid]\\1[/u:$uid]", $text);

	// [i] and [/i] for italicizing text.
	$text = preg_replace("#\[i\](.*?)\[/i\]#si", "[i:$uid]\\1[/i:$uid]", $text);

	// [img]image_url_here[/img] code..
	$text = preg_replace("#\[img\]((http|ftp|https|ftps)://)([^ \?&=\#\"\n\r\t<]*?(\.(jpg|jpeg|gif|png)))\[/img\]#si", "'\[/img:$uid]\\1'" . str_replace(' ', '%20', '\\3') . "'\[/img:$uid]'", $text);

	// Mighty Gorgon - Full Album Pack - BEGIN
	// [albumimg]album image id here[/albumimg]
	$text = preg_replace("#\[fullalbumimg\]([0-9]+)\[/fullalbumimg\]#si", "'[fullalbumimg:$uid]\\1[/fullalbumimg:$uid]'", $text);
	$text = preg_replace("#\[albumimg\]([0-9]+)\[/albumimg\]#si", "'[albumimg:$uid]\\1[/albumimg:$uid]'", $text);
	$text = preg_replace("#\[albumimgl\]([0-9]+)\[/albumimgl\]#si", "'[albumimgl:$uid]\\1[/albumimgl:$uid]'", $text);
	$text = preg_replace("#\[albumimgr\]([0-9]+)\[/albumimgr\]#si", "'[albumimgr:$uid]\\1[/albumimgr:$uid]'", $text);
	$text = preg_replace("#\[albumimgc\]([0-9]+)\[/albumimgc\]#si", "'[albumimgc:$uid]\\1[/albumimgc:$uid]'", $text);
	// Mighty Gorgon - Full Album Pack - END

//====================================================================== |
//==== Start Advanced BBCode Box MOD =================================== |
//==== v5.1.0 ========================================================== |
//====
	// [fade]Faded Text[/fade] code..
	$text = preg_replace("#\[fade\](.*?)\[/fade\]#si", "[fade:$uid]\\1[/fade:$uid]", $text);

	// [align=left/center/right/justify]Formatted Code[/align] code..
	$text = preg_replace("#\[align=(left|right|center|justify)\](.*?)\[/align\]#si", "[align=\\1:$uid]\\2[/align:$uid]", $text);

	 // [marquee=left/right/up/down]Marquee Code[/marquee] code..
	$text = preg_replace("#\[marq=(left|right|up|down)\](.*?)\[/marq\]#si", "[marq=\\1:$uid]\\2[/marq:$uid]", $text);

	// [font=fonttype]text[/font] code..
	$text = preg_replace("#\[font=(.*?)\](.*?)\[/font\]#si", "[font=\\1:$uid]\\2[/font:$uid]", $text);

	// [stream]Sound URL[/stream] code..
	$text = preg_replace("#\[stream\](.*?)\[/stream\]#si", "[stream:$uid]\\1[/stream:$uid]", $text);

	// [flash width=X height=X]Flash URL[/flash] code..
	$text = preg_replace("#\[flash width=([0-6]?[0-9]?[0-9]) height=([0-4]?[0-9]?[0-9])\](([a-z]+?)://([^, \n\r]+))\[\/flash\]#si","[flash width=\\1 height=\\2:$uid\]\\3[/flash:$uid]", $text);

	// [video width=X height=X]Video URL[/video] code..
	$text = preg_replace("#\[video width=([0-6]?[0-9]?[0-9]) height=([0-4]?[0-9]?[0-9])\](([a-z]+?)://([^, \n\r]+))\[\/video\]#si","[video width=\\1 height=\\2:$uid\]\\3[/video:$uid]", $text);

	// [hr]
	$text = preg_replace("#\[hr\]#si", "[hr:$uid]", $text);

	// [strike]Strikethrough[/strike] code..
	$text = preg_replace("#\[s\](.*?)\[/s\]#si", "[s:$uid]\\1[/s:$uid]", $text);

	// [spoil]Spoiler[/spoil] code..
	$text = preg_replace("#\[spoil\](.*?)\[/spoil\]#si", "[spoil:$uid]\\1[/spoil:$uid]", $text);

	// [sub]Subscrip[/sub] code..
	$text = preg_replace("#\[sub\](.*?)\[/sub\]#si", "[sub:$uid]\\1[/sub:$uid]", $text);

	// [sup]Superscript[/sup] code..
	$text = preg_replace("#\[sup\](.*?)\[/sup\]#si", "[sup:$uid]\\1[/sup:$uid]", $text);
//====
//==== End Advanced BBCode Box MOD ==================================== |
//===================================================================== |


	// Remove our padding from the string..
	return substr($text, 1);;

} // bbencode_first_pass()

/**
 * $text - The text to operate on.
 * $uid - The UID to add to matching tags.
 * $open_tag - The opening tag to match. Can be an array of opening tags.
 * $close_tag - The closing tag to match.
 * $close_tag_new - The closing tag to replace with.
 * $mark_lowest_level - boolean - should we specially mark the tags that occur
 * 					at the lowest level of nesting? (useful for [code], because
 *						we need to match these tags first and transform HTML tags
 *						in their contents..
 * $func - This variable should contain a string that is the name of a function.
 *				That function will be called when a match is found, and passed 2
 *				parameters: ($text, $uid). The function should return a string.
 *				This is used when some transformation needs to be applied to the
 *				text INSIDE a pair of matching tags. If this variable is FALSE or the
 *				empty string, it will not be executed.
 * If open_tag is an array, then the pda will try to match pairs consisting of
 * any element of open_tag followed by close_tag. This allows us to match things
 * like [list=A]...[/list] and [list=1]...[/list] in one pass of the PDA.
 *
 * NOTES:	- this function assumes the first character of $text is a space.
 *				- every opening tag and closing tag must be of the [...] format.
 */
function bbencode_first_pass_pda($text, $uid, $open_tag, $close_tag, $close_tag_new, $mark_lowest_level, $func, $open_regexp_replace = false)
{
	$open_tag_count = 0;

	if (!$close_tag_new || ($close_tag_new == ''))
	{
		$close_tag_new = $close_tag;
	}

	$close_tag_length = strlen($close_tag);
	$close_tag_new_length = strlen($close_tag_new);
	$uid_length = strlen($uid);

	$use_function_pointer = ($func && !empty($func));

	$stack = array();

	if (is_array($open_tag))
	{
		if (0 == count($open_tag))
		{
			// No opening tags to match, so return.
			return $text;
		}
		$open_tag_count = count($open_tag);
	}
	else
	{
		// only one opening tag. make it into a 1-element array.
		$open_tag_temp = $open_tag;
		$open_tag = array();
		$open_tag[0] = $open_tag_temp;
		$open_tag_count = 1;
	}

	$open_is_regexp = false;

	if ($open_regexp_replace)
	{
		$open_is_regexp = true;
		if (!is_array($open_regexp_replace))
		{
			$open_regexp_temp = $open_regexp_replace;
			$open_regexp_replace = array();
			$open_regexp_replace[0] = $open_regexp_temp;
		}
	}

	if ($mark_lowest_level && $open_is_regexp)
	{
		message_die(GENERAL_ERROR, "Unsupported operation for bbcode_first_pass_pda().");
	}

	// Start at the 2nd char of the string, looking for opening tags.
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($text)))
	{
		$curr_pos = strpos($text, "[", $curr_pos);

		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending tag.
			$found_start = false;
			$which_start_tag = "";
			$start_tag_index = -1;

			for ($i = 0; $i < $open_tag_count; $i++)
			{
				// Grab everything until the first "]"...
				$possible_start = substr($text, $curr_pos, strpos($text, ']', $curr_pos + 1) - $curr_pos + 1);

				//
				// We're going to try and catch usernames with "[' characters.
				//
				if( preg_match('#\[quote=\\\&quot;#si', $possible_start, $match) && !preg_match('#\[quote=\\\&quot;(.*?)\\\&quot;\]#si', $possible_start) )
				{
					// OK we are in a quote tag that probably contains a ] bracket.
					// Grab a bit more of the string to hopefully get all of it..
					if ($close_pos = strpos($text, '&quot;]', $curr_pos + 14))
					{
						if (strpos(substr($text, $curr_pos + 14, $close_pos - ($curr_pos + 14)), '[quote') === false)
						{
							$possible_start = substr($text, $curr_pos, $close_pos - $curr_pos + 7);
						}
					}
				}

				// Now compare, either using regexp or not.
				if ($open_is_regexp)
				{
					$match_result = array();
					if (preg_match($open_tag[$i], $possible_start, $match_result))
					{
						$found_start = true;
						$which_start_tag = $match_result[0];
						$start_tag_index = $i;
						break;
					}
				}
				else
				{
					// straightforward string comparison.
					if (0 == strcasecmp($open_tag[$i], $possible_start))
					{
						$found_start = true;
						$which_start_tag = $open_tag[$i];
						$start_tag_index = $i;
						break;
					}
				}
			}

			if ($found_start)
			{
				// We have an opening tag.
				// Push its position, the text we matched, and its index in the open_tag array on to the stack, and then keep going to the right.
				$match = array("pos" => $curr_pos, "tag" => $which_start_tag, "index" => $start_tag_index);
				array_push($stack, $match);
				//
				// Rather than just increment $curr_pos
				// Set it to the ending of the tag we just found
				// Keeps error in nested tag from breaking out
				// of table structure..
				//
				$curr_pos += strlen($possible_start);
			}
			else
			{
				// check for a closing tag..
				$possible_end = substr($text, $curr_pos, $close_tag_length);
				if (0 == strcasecmp($close_tag, $possible_end))
				{
					// We have an ending tag.
					// Check if we've already found a matching starting tag.
					if (sizeof($stack) > 0)
					{
						// There exists a starting tag.
						$curr_nesting_depth = sizeof($stack);
						// We need to do 2 replacements now.
						$match = array_pop($stack);
						$start_index = $match['pos'];
						$start_tag = $match['tag'];
						$start_length = strlen($start_tag);
						$start_tag_index = $match['index'];

						if ($open_is_regexp)
						{
							$start_tag = preg_replace($open_tag[$start_tag_index], $open_regexp_replace[$start_tag_index], $start_tag);
						}

						// everything before the opening tag.
						$before_start_tag = substr($text, 0, $start_index);

						// everything after the opening tag, but before the closing tag.
						$between_tags = substr($text, $start_index + $start_length, $curr_pos - $start_index - $start_length);

						// Run the given function on the text between the tags..
						if ($use_function_pointer)
						{
							$between_tags = $func($between_tags, $uid);
						}

						// everything after the closing tag.
						$after_end_tag = substr($text, $curr_pos + $close_tag_length);

						// Mark the lowest nesting level if needed.
						if ($mark_lowest_level && ($curr_nesting_depth == 1))
						{
							if ($open_tag[0] == '[code]')
							{
								$code_entities_match = array('#<#', '#>#', '#"#', '#:#', '#\[#', '#\]#', '#\(#', '#\)#', '#\{#', '#\}#');
								$code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');
								$between_tags = preg_replace($code_entities_match, $code_entities_replace, $between_tags);
							}
							$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$curr_nesting_depth:$uid]";
							$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$curr_nesting_depth:$uid]";
						}
						else
						{
							if ($open_tag[0] == '[code]')
							{
								$text = $before_start_tag . '&#91;code&#93;';
								$text .= $between_tags . '&#91;/code&#93;';
							}
							else
							{
								if ($open_is_regexp)
								{
									$text = $before_start_tag . $start_tag;
								}
								else
								{
									$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$uid]";
								}
								$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$uid]";
							}
						}

						$text .= $after_end_tag;

						// Now.. we've screwed up the indices by changing the length of the string.
						// So, if there's anything in the stack, we want to resume searching just after it.
						// otherwise, we go back to the start.
						if (sizeof($stack) > 0)
						{
							$match = array_pop($stack);
							$curr_pos = $match['pos'];
//							bbcode_array_push($stack, $match);
//							++$curr_pos;
						}
						else
						{
							$curr_pos = 1;
						}
					}
					else
					{
						// No matching start tag found. Increment pos, keep going.
						++$curr_pos;
					}
				}
				else
				{
					// No starting tag or ending tag.. Increment pos, keep looping.,
					++$curr_pos;
				}
			}
		}
	} // while

	return $text;

} // bbencode_first_pass_pda()

/**
 * Does second-pass bbencoding of the [code] tags. This includes
 * running htmlspecialchars() over the text contained between
 * any pair of [code] tags that are at the first level of
 * nesting. Tags at the first level of nesting are indicated
 * by this format: [code:1:$uid] ... [/code:1:$uid]
 * Other tags are in this format: [code:$uid] ... [/code:$uid]
 */
function bbencode_second_pass_code($text, $uid, $bbcode_tpl)
{
	global $lang;

	$code_start_html = $bbcode_tpl['code_open'];
	$code_end_html =  $bbcode_tpl['code_close'];

	// First, do all the 1st-level matches. These need an htmlspecialchars() run,
	// so they have to be handled differently.
	$match_count = preg_match_all("#\[code:1:$uid\](.*?)\[/code:1:$uid\]#si", $text, $matches);

	for ($i = 0; $i < $match_count; $i++)
	{
		$before_replace = $matches[1][$i];
		$after_replace = $matches[1][$i];

		// Replace 2 spaces with "&nbsp; " so non-tabbed code indents without making huge long lines.
		$after_replace = str_replace("  ", "&nbsp; ", $after_replace);
		// now Replace 2 spaces with " &nbsp;" to catch odd #s of spaces.
		$after_replace = str_replace("  ", " &nbsp;", $after_replace);

		// Replace tabs with "&nbsp; &nbsp;" so tabbed code indents sorta right without making huge long lines.
		$after_replace = str_replace("\t", "&nbsp; &nbsp;", $after_replace);

		// now Replace space occurring at the beginning of a line
		$after_replace = preg_replace("/^ {1}/m", '&nbsp;', $after_replace);

		$str_to_match = "[code:1:$uid]" . $before_replace . "[/code:1:$uid]";

		$replacement = $code_start_html;
		$replacement .= $after_replace;
		$replacement .= $code_end_html;

		$text = str_replace($str_to_match, $replacement, $text);
	}

	// Now, do all the non-first-level matches. These are simple.
	$text = str_replace("[code:$uid]", $code_start_html, $text);
	$text = str_replace("[/code:$uid]", $code_end_html, $text);

	return $text;

} // bbencode_second_pass_code()

/**
 * Rewritten by Nathan Codding - Feb 6, 2001.
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *		to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */
function make_clickable($text)
{
	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);

	// pad it with a space so we can match things at the start of the 1st line.
	$ret = ' ' . $text;

	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, comma, double quote or <
	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);

	// matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// zzzz is optional.. will contain everything up to the first space, newline,
	// comma, double quote or <.
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);

	// matches an email@domain type address at the start of a line, or after a space.
	// Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);

	// Remove our padding..
	$ret = substr($ret, 1);

	return($ret);
}

/**
 * Nathan Codding - Feb 6, 2001
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 *
 */
function undo_make_clickable($text)
{
	$text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\" target=\"_blank\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
	$text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);

	return $text;

}

/**
 * Nathan Codding - August 24, 2000.
 * Takes a string, and does the reverse of the PHP standard function
 * htmlspecialchars().
 */
function undo_htmlspecialchars($input)
{
	$input = preg_replace("/&gt;/i", ">", $input);
	$input = preg_replace("/&lt;/i", "<", $input);
	$input = preg_replace("/&quot;/i", "\"", $input);
	$input = preg_replace("/&amp;/i", "&", $input);

	return $input;
}

/**
 * This is used to change a [*] tag into a [*:$uid] tag as part
 * of the first-pass bbencoding of [list] tags. It fits the
 * standard required in order to be passed as a variable
 * function into bbencode_first_pass_pda().
 */
function replace_listitems($text, $uid)
{
	$text = str_replace("[*]", "[*:$uid]", $text);

	return $text;
}

/**
 * Escapes the "/" character with "\/". This is useful when you need
 * to stick a runtime string into a PREG regexp that is being delimited
 * with slashes.
 */
function escape_slashes($input)
{
	$output = str_replace('/', '\/', $input);
	return $output;
}

/**
 * This function does exactly what the PHP4 function array_push() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with our own
 * method of doing it.
 * This function was deprecated in phpBB 2.0.18
 */
function bbcode_array_push(&$stack, $value)
{
   $stack[] = $value;
   return(sizeof($stack));
}

/**
 * This function does exactly what the PHP4 function array_pop() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with our own
 * method of doing it.
 * This function was deprecated in phpBB 2.0.18
 */
function bbcode_array_pop(&$stack)
{
   $arrSize = count($stack);
   $x = 1;

   while(list($key, $val) = each($stack))
   {
      if($x < count($stack))
      {
	 		$tmpArr[] = $val;
      }
      else
      {
	 		$return_val = $val;
      }
      $x++;
   }
   $stack = $tmpArr;

   return($return_val);
}

//
// Smilies code ... would this be better tagged on to the end of bbcode.php?
// Probably so and I'll move it before B2
//
function smilies_pass($message)
{
	static $orig, $repl;

	if (!isset($orig))
	{
		global $db, $board_config;
		$orig = $repl = array();

		$sql = 'SELECT * FROM ' . SMILIES_TABLE;
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain smilies data", "", __LINE__, __FILE__, $sql);
		}
		$smilies = $db->sql_fetchrowset($result);

		if (count($smilies))
		{
			usort($smilies, 'smiley_sort');
		}

		for ($i = 0; $i < count($smilies); $i++)
		{
			$orig[] = "/(?<=.\W|\W.|^\W)" . preg_quote($smilies[$i]['code'], "/") . "(?=.\W|\W.|\W$)/";
			$repl[] = '<img src="'. $board_config['smilies_path'] . '/' . $smilies[$i]['smile_url'] . '" alt="' . $smilies[$i]['emoticon'] . '" border="0" />';
		}
	}

	if (count($orig))
	{
		$message = preg_replace($orig, $repl, ' ' . $message . ' ');
		$message = substr($message, 1, -1);
	}

	return $message;
}

function smiley_sort($a, $b)
{
	if ( strlen($a['code']) == strlen($b['code']) )
	{
		return 0;
	}

	return ( strlen($a['code']) > strlen($b['code']) ) ? -1 : 1;
}

?>