<?php
/***************************************************************************
 *                              bbcode.php
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

class bbcode
{
	var $bbcode_uid = '';
	var $bbcode_bitfield = 0;
	var $bbcode_cache = array();
	var $bbcode_template = array();

	function bbcode($bitfield = 0)
	{
		if ($bitfield)
		{
			$this->bbcode_bitfield = $bitfield;
			$this->bbcode_cache_init();
		}
	}

	function bbcode_second_pass(&$message, $bbcode_uid = '', $bbcode_bitfield = FALSE)
	{
		if ($bbcode_uid)
		{
			$this->bbcode_uid = $bbcode_uid;
		}

		if ($bbcode_bitfield !== FALSE)
		{
			$this->bbcode_bitfield = $bbcode_bitfield;
		}
		if (!$this->bbcode_bitfield)
		{
			return $message;
		}

		if (empty($this->bbcode_cache))
		{
			$this->bbcode_cache_init();
		}

		$str = array('search' => array(), 'replace' => array());
		$preg = array('search' => array(), 'replace' => array());

		$bitlen = strlen(decbin($this->bbcode_bitfield));
		for ($bbcode_id = 0; $bbcode_id < $bitlen; ++$bbcode_id)
		{
			if ($this->bbcode_bitfield & (1 << $bbcode_id))
			{
				foreach ($this->bbcode_cache[$bbcode_id] as $type => $array)
				{
					foreach ($array as $search => $replace)
					{
						${$type}['search'][] = str_replace('$uid', $this->bbcode_uid, $search);
						${$type}['replace'][] = $replace;

					}
				}
			}
		}

		if (count($str['search']))
		{
			$message = str_replace($str['search'], $str['replace'], $message);
		}
		if (count($preg['search']))
		{
			$message = preg_replace($preg['search'], $preg['replace'], $message);
		}

		return $message;
	}
	
	//
	// bbcode_cache_init()
	//
	// requires: $this->bbcode_bitfield
	// sets: $this->bbcode_cache with bbcode templates needed for bbcode_bitfield
	//
	function bbcode_cache_init()
	{
		global $user;

		$sql = '';

		$bbcode_ids = array();
		$bitlen = strlen(decbin($this->bbcode_bitfield));

		for ($bbcode_id = 0; $bbcode_id < $bitlen; ++$bbcode_id)
		{
			if (isset($this->bbcode_cache[$bbcode_id]) || !($this->bbcode_bitfield & (1 << $bbcode_id)))
			{
				// do not try to re-cache it if it's already in
				continue;
			}
			$bbcode_ids[$bbcode_id] = $bbcode_id;

			// WARNING: hardcoded values. it assumes that bbcodes with bbcode_id > 11 are user-defined bbcodes
			if ($bbcode_id > 11)
			{
				$sql .= (($sql) ? ',' : '') . $bbcode_id . ',';
			}
		}
/*
		if ($sql)
		{
			global $db;
			$rowset = array();

			$sql = 'SELECT bbcode_id, second_pass_regexp, second_pass_replacement
				FROM ' . BBCODES_TABLE . "
				WHERE bbcode_id IN ($sql);

			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$rowset[$row['bbcode_id']] = $row;
			}
			$db->sql_freeresult($result);
		}
*/

		foreach ($bbcode_ids as $bbcode_id)
		{
			switch ($bbcode_id)
			{
				case 0:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[quote:$uid]'	=>	$this->bbcode_tpl('quote_open', $bbcode_id),
							'[/quote:$uid]'	=>	$this->bbcode_tpl('quote_close', $bbcode_id)
						),
						'preg' => array(
							'#\[quote="(.*?)":$uid\]#'	=>	$this->bbcode_tpl('quote_username_open', $bbcode_id)
						)
					);
				break;
				case 1:
					$this->bbcode_cache[$bbcode_id] = array('str' => array(
						'[b:$uid]'	=>	$this->bbcode_tpl('b_open', $bbcode_id),
						'[/b:$uid]'	=>	$this->bbcode_tpl('b_close', $bbcode_id)
					));
				break;
				case 2:
					$this->bbcode_cache[$bbcode_id] = array('str' => array(
						'[i:$uid]'	=>	$this->bbcode_tpl('i_open', $bbcode_id),
						'[/i:$uid]'	=>	$this->bbcode_tpl('i_close', $bbcode_id)
					));
				break;
				case 3:
					$this->bbcode_cache[$bbcode_id] = array('preg' => array(
						'#\[url:$uid\]((.*?))\[/url:$uid\]#s'		=>	$this->bbcode_tpl('url', $bbcode_id),
						'#\[url=([^\[]+?):$uid\](.*?)\[/url:$uid\]#s'	=>	$this->bbcode_tpl('url', $bbcode_id)
					));
				break;
				case 4:
					if ($user->data['user_viewimg'])
					{
						$this->bbcode_cache[$bbcode_id] = array('preg' => array(
							'#\[img:$uid\](.*?)\[/img:$uid\]#s'		=>	$this->bbcode_tpl('img', $bbcode_id)
						));
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array('preg' => array(
							'#\[img:$uid\](.*?)\[/img:$uid\]#s'		=>	str_replace('\\2', '[ img ]', $this->bbcode_tpl('url', $bbcode_id))
						));
					}
				break;
				case 5:
					$this->bbcode_cache[$bbcode_id] = array('preg' => array(
						'#\[size=([\-\+]?[1-2]?[0-9]):$uid\](.*?)\[/size:$uid\]#s'	=>	$this->bbcode_tpl('size', $bbcode_id)
					));
				break;
				case 6:
					$this->bbcode_cache[$bbcode_id] = array('preg' => array(
						'!\[color=(#[0-9A-F]{6}|[a-z\-]+):$uid\](.*?)\[/color:$uid\]!s'	=>	$this->bbcode_tpl('color', $bbcode_id)
					));
				break;
				case 7:
					$this->bbcode_cache[$bbcode_id] = array('str' => array(
						'[u:$uid]'	=>	$this->bbcode_tpl('u_open', $bbcode_id),
						'[/u:$uid]'	=>	$this->bbcode_tpl('u_close', $bbcode_id)
					));
				break;
				case 8:
					$this->bbcode_cache[$bbcode_id] = array('preg' => array(
						'#\[code(?:=([a-z]+))?:$uid\](.*?)\[/code:$uid\]#ise'	=>	"\$this->bbcode_second_pass_code('\\1', '\\2')"
					));
				break;
				case 9:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[list:$uid]'			=>	$this->bbcode_tpl('ulist_open_default', $bbcode_id),
							'[/list:u:$uid]'		=>	$this->bbcode_tpl('ulist_close', $bbcode_id),
							'[/list:o:$uid]'		=>	$this->bbcode_tpl('olist_close', $bbcode_id),
							'[*:$uid]'				=>	$this->bbcode_tpl('listitem', $bbcode_id),
							'[/*:$uid]'				=>	$this->bbcode_tpl('listitem_close', $bbcode_id),
							'[/*:m:$uid]'			=>	$this->bbcode_tpl('listitem_close', $bbcode_id)
						),
						'preg' => array(
							'#\[list=([^\[]+):$uid\]#e'	=>	"\$this->bbcode_list('\\1')",
						)
					);
				break;
				case 10:
					$this->bbcode_cache[$bbcode_id] = array('preg' => array(
							'#\[email:$uid\]((.*?))\[/email:$uid\]#is'		=>	$this->bbcode_tpl('email', $bbcode_id),
							'#\[email=([^\[]+):$uid\](.*?)\[/email:$uid\]#is'	=>	$this->bbcode_tpl('email', $bbcode_id)
					));
				break;
				case 11:
					if ($user->data['user_viewflash'])
					{
						$this->bbcode_cache[$bbcode_id] = array('preg' => array(
							'#\[flash=([0-9]+),([0-9]+):$uid\](.*?)\[/flash:$uid\]#'	=>	$this->bbcode_tpl('flash', $bbcode_id)
						));
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array('preg' => array(
							'#\[flash=([0-9]+),([0-9]+):$uid\](.*?)\[/flash:$uid\]#'	=>	str_replace('\\1', '\\3', str_replace('\\2', '[ flash ]', $this->bbcode_tpl('url', $bbcode_id)))
						));
					}
				break;
				default:
					if (isset($rowset[$bbcode_id]))
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array($rowset[$bbcode_id]['second_pass_regexp'], $rowset[$bbcode_id]['second_pass_replacement'])
						);
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array();
					}
			}
		}
	}

	function bbcode_tpl($tpl_name, $bbcode_id = -1)
	{
		global $template, $user;

		if (empty($bbcode_hardtpl))
		{
			static $bbcode_hardtpl = array(
				'b_open'		=>	'<span style="font-weight: bold">',
				'b_close'		=>	'</span>',
				'i_open'		=>	'<span style="font-style: italic">',
				'i_close'		=>	'</span>',
				'u_open'		=>	'<span style="text-decoration: underline">',
				'u_close'		=>	'</span>',
				'url'			=>	'<a href="\1" target="_blank">\2</a>',
				'img'			=>	'<img src="\1" border="0" />',
				'size'			=>	'<span style="font-size: \1px; line-height: normal">\2</span>',
				'color'			=>	'<span style="color: \1">\2</span>',
				'email'			=>	'<a href="mailto:\1">\2</a>'
			);
		}

		if ($bbcode_id != -1 && !($user->theme['primary']['bbcode_bitfield'] & (1 << $bbcode_id)))
		{
			return $bbcode_hardtpl[$tpl_name];
		}

		if (empty($this->bbcode_template))
		{
			global $user;

			$tpl_filename = (file_exists($phpbb_root_path . 'styles/' . $user->theme['primary']['template_path'] . '/template/bbcode.html')) ? $phpbb_root_path . 'styles/' . $user->theme['primary']['template_path'] . '/template/bbcode.html' : $phpbb_root_path . 'styles/' . $user->theme['secondary']['template_path'] . '/template/bbcode.html';

			if (!($fp = @fopen($tpl_filename, 'rb')))
			{
				trigger_error('Could not load bbcode template');
			}
			$tpl = fread($fp, filesize($tpl_filename));
			@fclose($fp);

			// replace \ with \\ and then ' with \'.
			$tpl = str_replace('\\', '\\\\', $tpl);
			$tpl = str_replace("'", "\'", $tpl);
			
			// strip newlines and indent
			$tpl = preg_replace("/\n[\n\r\s\t]*/", '', $tpl);

			// Turn template blocks into PHP assignment statements for the values of $bbcode_tpl..
			$tpl = preg_replace('#<!-- BEGIN (.*?) -->(.*?)<!-- END (.*?) -->#', "\n" . "\$this->bbcode_template['\\1'] = \$this->bbcode_tpl_replace('\\1','\\2');", $tpl);

			$this->bbcode_template = array();
			eval($tpl);
		}

		return $this->bbcode_template[$tpl_name];
	}
	
	function bbcode_tpl_replace($tpl_name, $tpl)
	{
		static $replacements = array(
			'quote_username_open'	=>	array('{USERNAME}'	=>	'\\1'),
			'color'					=>	array('{COLOR}'		=>	'\\1', 'TEXT'			=>	'\\2'),
			'size'					=>	array('{SIZE}'		=>	'\\1', 'TEXT'			=>	'\\2'),
			'img'					=>	array('{URL}'		=>	'\\1'),
			'flash'					=>	array('{WIDTH}'		=>	'\\1', '{HEIGHT}'		=>	'\\2', '{URL}'		=>	'\\3'),
			'url'					=>	array('{URL}'		=>	'\\1', '{DESCRIPTION}'	=>	'\\2'),
			'email'					=>	array('{EMAIL}'		=>	'\\1', '{DESCRIPTION}'	=>	'\\2')
		);

		$tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : ucwords(strtolower('\\1'))", $tpl);

		if (!empty($replacements[$tpl_name]))
		{
			$tpl = strtr($tpl, $replacements[$tpl_name]);
		}

		return trim($tpl);
	}
	
	function bbcode_list($type)
	{
		if ($type == '')
		{
			$tpl = 'ulist_open_default';
			$type = 'default';
			$start = 0;
		}
		elseif ($type == 'i')
		{
			$tpl = 'olist_open';
			$type = 'lower-roman';
			$start = 1;
		}
		elseif ($type == 'I')
		{
			$tpl = 'olist_open';
			$type = 'upper-roman';
			$start = 1;
		}
		elseif (preg_match('#^(disc|circle|square)$#i', $type))
		{
			$tpl = 'ulist_open';
			$type = strtolower($type);
			$start = 1;
		}
		elseif (preg_match('#^[a-z]$#', $type))
		{
			$tpl = 'olist_open';
			$type = 'lower-alpha';
			$start = ord($type) - 96;
		}
		elseif (preg_match('#[A-Z]#', $type))
		{
			$tpl = 'olist_open';
			$type = 'upper-alpha';
			$start = ord($type) - 64;
		}
		elseif (is_numeric($type))
		{
			$tpl = 'olist_open';
			$type = 'arabic-numbers';
			$start = intval($chr);
		}
		else
		{
			$tpl = 'olist_open';
			$type = 'arabic-numbers';
			$start = 1;
		}

		return str_replace('{LIST_TYPE}', $type, $this->bbcode_tpl($tpl));
	}

	function bbcode_second_pass_code($type, $code)
	{
		$code = stripslashes($code);

		switch ($type)
		{
			case 'php':
			default:
				$code = str_replace("\t", '&nbsp; &nbsp;', $code);
				$code = str_replace('  ', '&nbsp; ', $code);
				$code = str_replace('  ', ' &nbsp;', $code);
		}

		$code = $this->bbcode_tpl('code_open') . $code . $this->bbcode_tpl('code_close');

		return $code;
	}
}
?>