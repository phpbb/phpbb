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
	var $bbcode_tpl = array();

	function bbcode($bitfield = 0)
	{
		if ($bitfield)
		{
			$this->bbcode_bitfield = $bitfield;
			$this->bbcode_cache_init();
		}
	}

	function bbcode_second_pass(&$message, $bbcode_uid = '', $bbcode_bitfield = '')
	{
		if ($bbcode_uid)
		{
			$this->bbcode_uid = $bbcode_uid;
		}

		if ($bbcode_bitfield)
		{
			$this->bbcode_bitfield = $bbcode_bitfield;
		}
		elseif (!$this->bbcode_bitfield)
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
			if ($this->bbcode_bitfield & pow(2, $bbcode_id))
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
			if (isset($this->bbcode_cache[$bbcode_id]) || !($this->bbcode_bitfield & pow(2, $bbcode_id)))
			{
				continue;
			}
			$bbcode_ids[] = $bbcode_id;

			// WARNING: hardcoded values. it assumes that bbcodes with bbcode_id > 11 are user-defined bbcodes
			if ($bbcode_id > 11)
			{
				$sql .= $bbcode_id . ',';
			}
		}
/*
		if ($sql)
		{
			global $db;
			$rowset = array();

			$sql = 'SELECT bbcode_id, second_pass_regexp, second_pass_replacement
				FROM ' . BBCODES_TABLE . '
				WHERE bbcode_id IN (' . substr($sql, 0, -1) . ')';

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
							'[quote:$uid]'			=>	$this->bbcode_tpl('quote_open'),
							'[/quote:$uid]'			=>	$this->bbcode_tpl('quote_close')
						),
						'preg' => array(
							'#\[quote="(.*?)":$uid\]#'	=>	$this->bbcode_tpl('quote_username_open')
						)
					);
				break;
				case 1:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[b:$uid]'				=>	'<span style="font-weight: bold">',
							'[/b:$uid]'				=>	'</span>'
						)
					);
				break;
				case 2:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[i:$uid]'				=>	'<span style="font-style: italic">',
							'[/i:$uid]'				=>	'</span>'
						)
					);
				break;
				case 3:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[url:$uid\](.*?)\[/url:$uid\]#s'		=>	'<a href="\1" target="_blank">\1</a>',
							'#\[url=(.*?):$uid\](.*?)\[/url:$uid\]#s'	=>	'<a href="\1" target="_blank">\2</a>'
						)
					);
				break;
				case 4:
					if ($user->data['user_viewimg'])
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[img:$uid\](.*?)\[/img:$uid\]#s'		=>	'<img src="\1" border="0" />'
							)
						);
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[img:$uid\](.*?)\[/img:$uid\]#s'		=>	'<a href="\1">[ img ]</a>'
							)
						);
					}
					break;
				case 5:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[size=(.*?):$uid\](.*?)\[/size:$uid\]#s'	=>	'<span style="font-size: \1px; line-height: normal">\2</span>'
						)
					);
				break;
				case 6:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[color=(.*?):$uid\](.*?)\[/color:$uid\]#s'	=>	'<span style="color: \1">\2</span>'
						)
					);
				break;
				case 7:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[u:$uid]'				=>	'<span style="text-decoration: underline">',
							'[/u:$uid]'				=>	'</span>'
						)
					);
				break;
				case 8:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[code(?:=([a-z]+))?:$uid\](.*?)\[/code:$uid\]#ise'	=>	"\$this->bbcode_second_pass_code('\\1', '\\2')"
						)
					);
				break;
				case 9:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[list:$uid]'			=>	'<ul>',
							'[/list:u:$uid]'		=>	'</ul>',
							'[/list:o:$uid]'		=>	'</ol>',
							'[*:$uid]'				=>	'<li>',
							'[/*:$uid]'				=>	'</li>',
							'[/*:m:$uid]'			=>	'</li>',
						),
						'preg' => array(
							'#\[list=(.+?):$uid\]#e'	=>	"\$this->bbcode_ordered_list('\\1')",
						)
					);
				break;
				case 10:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[email:$uid\](.*?)\[/email:$uid\]#is'		=>	'<a href="mailto:\1">\1</a>',
							'#\[email=(.*?):$uid\](.*?)\[/email:$uid\]#is'	=>	'<a href="mailto:\1">\2</a>'
						)
					);
				break;
				case 11:
					if ($user->data['user_viewflash'])
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[flash:$uid\](.*?)\[/flash:$uid\]#'	=>	$this->bbcode_tpl('flash')
							)
						);
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[flash:$uid\](.*?)\[/flash:$uid\]#s'		=>	'<a href="\1">[ flash ]</a>'
							)
						);
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

	function bbcode_tpl($tpl_name)
	{
		if (empty($this->bbcode_tpl))
		{
			global $template, $user;

			$tpl_filename = $template->make_filename('bbcode.html');

			if (!$fp = @fopen($tpl_filename, 'rb'))
			{
				trigger_error('Could not load bbcode template');
			}
			$tpl = fread($fp, filesize($tpl_filename));
			@fclose($fp);

			// replace \ with \\ and then ' with \'.
			$tpl = str_replace('\\', '\\\\', $tpl);
			$tpl = str_replace("'", "\'", $tpl);
			
			// strip newlines.
			$tpl  = str_replace("\n", '', $tpl);

			// Turn template blocks into PHP assignment statements for the values of $bbcode_tpl..
			$tpl = preg_replace('#<!-- BEGIN (.*?) -->(.*?)<!-- END (.*?) -->#', "\n" . "\$this->bbcode_tpl['\\1'] = trim('\\2');", $tpl);

			$this->bbcode_tpl = array();
			eval($tpl);

			foreach ($this->bbcode_tpl as $key => $val)
			{
				$this->bbcode_tpl[$key] = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : ucwords(strtolower('\\1'))", $this->bbcode_tpl[$key]);
			}

			$this->bbcode_tpl['quote_username_open'] = str_replace('{USERNAME}', '\\1', $this->bbcode_tpl['quote_username_open']);
			$this->bbcode_tpl['flash'] = str_replace('{URL}', '\\1', $this->bbcode_tpl['flash']);
		}

		return $this->bbcode_tpl[$tpl_name];
	}
	
	function bbcode_ordered_list($type)
	{
		if ($type == 'i')
		{
			$type = 'lower-roman';
			$start = 1;
		}
		elseif ($type == 'I')
		{
			$type = 'upper-roman';
			$start = 1;
		}
		elseif (preg_match('#^(disc|circle|square)$#i', $type))
		{
			$type = strtolower($type);
			$start = 1;
		}
		elseif (preg_match('#^[a-z]$#', $type))
		{
			$type = 'lower-alpha';
			$start = ord($type) - 96;
		}
		elseif (preg_match('#[A-Z]#', $type))
		{
			$type = 'upper-alpha';
			$start = ord($type) - 64;
		}
		elseif (is_numeric($type))
		{
			$type = 'arabic-numbers';
			$start = intval($chr);
		}
		else
		{
			$type = 'arabic-numbers';
			$start = 1;
		}
		return '<ol style="list-style-type: ' . $type . '" start="' . $start . '">';
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