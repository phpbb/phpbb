<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2006 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
* @todo make sure the replacements are called correctly
* already done: strtolower, strtoupper, ucfirst, str_split, strrpos, strlen (hopefully!)
* remaining:	clean_username, htmlentities (no longer needed for internal data?), htmlspecialchars (using charset), html_entity_decode (own function to reverse htmlspecialchars and not htmlentities)
*				substr, strpos, strspn, chr, ord
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* UTF-8 tools
*
* Whenever possible, these functions will try to use PHP's built-in functions or
* extensions, otherwise they will default to custom routines.
*
* @package phpBB3
*/

if (!extension_loaded('xml'))
{
	/**
	* Implementation of PHP's native utf8_encode for people without XML support
	* This function exploits some nice things that ISO-8859-1 and UTF-8 have in common
	*
	* @param string $str ISO-8859-1 encoded data
	* @return string UTF-8 encoded data
	*/
	function utf8_encode($str)
	{
		$out = '';
		for ($i = 0, $len = strlen($str); $i < $len; $i++)
		{
			$letter = $str[$i];
			$num = ord($letter);
			if ($num < 0x80)
			{
				$out .= $letter;
			}
			else if ($num < 0xC0)
			{
				$out .= "\xC2$letter";
			}
			else
			{
				$chr = chr($num - 64);
				$out .= "\xC3$chr";
			}
		}
		return $out;
	}

	/**
	* Implementation of PHP's native utf8_decode for people without XML support
	*
	* @author GetID3()
	* @param string $string UTF-8 encoded data
	* @return string ISO-8859-1 encoded data
	*/
	function utf8_decode($string)
	{
		$newcharstring = '';
		$offset = 0;
		$stringlength = strlen($string);

		while ($offset < $stringlength)
		{
			$ord = ord($string{$offset});
			if (($ord | 0x07) == 0xF7)
			{
				// 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
				$charval = (($ord & 0x07) << 18) &
							((ord($string{($offset + 1)}) & 0x3F) << 12) &
							((ord($string{($offset + 2)}) & 0x3F) <<  6) &
							(ord($string{($offset + 3)}) & 0x3F);
				$offset += 4;
			}
			else if (($ord | 0x0F) == 0xEF)
			{
				// 1110bbbb 10bbbbbb 10bbbbbb
				$charval = (($ord & 0x0F) << 12) &
							((ord($string{($offset + 1)}) & 0x3F) <<  6) &
							(ord($string{($offset + 2)}) & 0x3F);
				$offset += 3;
			}
			else if (($ord | 0x1F) == 0xDF)
			{
				// 110bbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x1F) <<  6) &
							(ord($string{($offset + 1)}) & 0x3F);
				$offset += 2;
			}
			else if (($ord | 0x7F) == 0x7F)
			{
				// 0bbbbbbb
				$charval = $ord;
				$offset += 1;
			}
			else
			{
				// error? throw some kind of warning here?
				$charval = false;
				$offset += 1;
			}

			if ($charval !== false)
			{
				$newcharstring .= (($charval < 256) ? chr($charval) : '?');
			}
		}

		return $newcharstring;
	}
}

// mbstring is old and has it's functions around for older versions of PHP.
// if mbstring is not loaded, we go into native mode.
if (extension_loaded('mbstring'))
{
	/**
	* UTF-8 aware alternative to strrpos
	* Find position of last occurrence of a char in a string
	*
	* Notes:
	* - offset for mb_strrpos was added in 5.2.0, we emulate if it is lower
	* 
	* @author Harry Fuecks
	* @param string haystack
	* @param string needle
	* @param integer (optional) offset (from left)
	* @return mixed integer position or FALSE on failure
	* @ignore
	*/
	if (version_compare(phpversion(), '5.2.0', '>='))
	{
		function utf8_strrpos($str,	$needle, $offset = null)
		{
			// Emulate behaviour of strrpos rather than raising warning
			if (empty($str))
			{
				return false;
			}

			return mb_strrpos($str, $search);
		}
	}
	else
	{
		function utf8_strrpos($str,	$needle, $offset = null)
		{
			// offset for mb_strrpos was added in 5.2.0
			if ($offset === false)
			{
				// Emulate behaviour of strrpos rather than raising warning
				if (empty($str))
				{
					return false;
				}

				return mb_strrpos($str, $search);
			}
			else
			{
				if (!is_int($offset))
				{
					trigger_error('utf8_strrpos expects parameter 3 to be long', E_USER_WARNING);
					return false;
				}

				$str = mb_substr($str, $offset);

				if (false !== ($pos = mb_strrpos($str, $search)))
				{
					return $pos + $offset;
				}

				return false;
			}
		}
	}

	/**
	* UTF-8 aware alternative to strpos
	* Find position of first occurrence of a string
	*
	* @author Harry Fuecks
	* @param string haystack
	* @param string needle
	* @param integer offset in characters (from left)
	* @return mixed integer position or FALSE on failure
	*/
	function utf8_strpos($str, $needle, $offset = null)
	{
		if ($offset === false)
		{
			return mb_strpos($str, $needle);
		}
		else
		{
			return mb_strpos($str, $needle, $offset);
		}
	}

	/**
	* UTF-8 aware alternative to strtolower
	* @ignore
	*/
	function utf8_strtolower($str)
	{
		return mb_strtolower($str);
	}

	/**
	* UTF-8 aware alternative to strtoupper
	* @ignore
	*/
	function utf8_strtoupper($str)
	{
		return mb_strtoupper($str);
	}

	/**
	* UTF-8 aware alternative to substr
	* Return part of a string given character offset (and optionally length)
	* 
	* @author Harry Fuecks
	* @param string
	* @param integer number of UTF-8 characters offset (from left)
	* @param integer (optional) length in UTF-8 characters from offset
	* @return mixed string or FALSE if failure
	*/
	function utf8_substr($str, $offset,	$length	= null)
	{
		if ($length === false)
		{
			return mb_substr($str, $offset);
		}
		else
		{
			return mb_substr($str, $offset, $length);
		}
	}

	/**
	* Return the length (in characters) of a UTF-8 string
	*
	* @param	string	$text		UTF-8 string
	* @return	integer				Length (in chars) of given string
	*/
	function utf8_strlen($text)
	{
		return mb_strlen($text, 'utf-8');
	}
}
else
{
	/**
	* UTF-8 aware alternative to strrpos
	* Find position of last occurrence of a char in a string
	* 
	* @author Harry Fuecks
	* @param string haystack
	* @param string needle
	* @param integer (optional) offset (from left)
	* @return mixed integer position or FALSE on failure
	*/
	function utf8_strrpos($str,	$needle, $offset = null)
	{
		if (is_null($offset))
		{
			$ar	= explode($needle, $str);
			
			if (sizeof($ar) > 1)
			{
				// Pop off the end of the string where the last	match was made
				array_pop($ar);
				$str = join($needle, $ar);

				return utf8_strlen($str);
			}
			return false;
		}
		else
		{
			if (!is_int($offset))
			{
				trigger_error('utf8_strrpos	expects	parameter 3	to be long', E_USER_WARNING);
				return false;
			}

			$str = utf8_substr($str, $offset);

			if (false !== ($pos = utf8_strrpos($str, $needle)))
			{
				return $pos	+ $offset;
			}

			return false;
		}
	}

	/**
	* UTF-8 aware alternative to strpos
	* Find position of first occurrence of a string
	*
	* @author Harry Fuecks
	* @param string haystack
	* @param string needle
	* @param integer offset in characters (from left)
	* @return mixed integer position or FALSE on failure
	*/
	function utf8_strpos($str, $needle, $offset = null)
	{
		// native
		if (is_null($offset))
		{
			$ar = explode($needle, $str);
			if (sizeof($ar) > 1)
			{
				return utf8_strlen($ar[0]);
			}
			return false;
		}
		else
		{
			if (!is_int($offset))
			{
				trigger_error('utf8_strpos:  Offset must  be an integer', E_USER_ERROR);
				return false;
			}

			$str = utf8_substr($str, $offset);

			if (false !== ($pos = utf8_strpos($str, $needle)))
			{
				return $pos + $offset;
			}

			return false;
		}
	}

	$UTF8_UPPER_TO_LOWER = array(
		0x0041=>0x0061, 0x03A6=>0x03C6, 0x0162=>0x0163, 0x00C5=>0x00E5, 0x0042=>0x0062,
		0x0139=>0x013A, 0x00C1=>0x00E1, 0x0141=>0x0142, 0x038E=>0x03CD, 0x0100=>0x0101,
		0x0490=>0x0491, 0x0394=>0x03B4, 0x015A=>0x015B, 0x0044=>0x0064, 0x0393=>0x03B3,
		0x00D4=>0x00F4, 0x042A=>0x044A, 0x0419=>0x0439, 0x0112=>0x0113, 0x041C=>0x043C,
		0x015E=>0x015F, 0x0143=>0x0144, 0x00CE=>0x00EE, 0x040E=>0x045E, 0x042F=>0x044F,
		0x039A=>0x03BA, 0x0154=>0x0155, 0x0049=>0x0069, 0x0053=>0x0073, 0x1E1E=>0x1E1F,
		0x0134=>0x0135, 0x0427=>0x0447, 0x03A0=>0x03C0, 0x0418=>0x0438, 0x00D3=>0x00F3,
		0x0420=>0x0440, 0x0404=>0x0454, 0x0415=>0x0435, 0x0429=>0x0449, 0x014A=>0x014B,
		0x0411=>0x0431, 0x0409=>0x0459, 0x1E02=>0x1E03, 0x00D6=>0x00F6, 0x00D9=>0x00F9,
		0x004E=>0x006E, 0x0401=>0x0451, 0x03A4=>0x03C4, 0x0423=>0x0443, 0x015C=>0x015D,
		0x0403=>0x0453, 0x03A8=>0x03C8, 0x0158=>0x0159, 0x0047=>0x0067, 0x00C4=>0x00E4,
		0x0386=>0x03AC, 0x0389=>0x03AE, 0x0166=>0x0167, 0x039E=>0x03BE, 0x0164=>0x0165,
		0x0116=>0x0117, 0x0108=>0x0109, 0x0056=>0x0076, 0x00DE=>0x00FE, 0x0156=>0x0157,
		0x00DA=>0x00FA, 0x1E60=>0x1E61, 0x1E82=>0x1E83, 0x00C2=>0x00E2, 0x0118=>0x0119,
		0x0145=>0x0146, 0x0050=>0x0070, 0x0150=>0x0151, 0x042E=>0x044E, 0x0128=>0x0129,
		0x03A7=>0x03C7, 0x013D=>0x013E, 0x0422=>0x0442, 0x005A=>0x007A, 0x0428=>0x0448,
		0x03A1=>0x03C1, 0x1E80=>0x1E81, 0x016C=>0x016D, 0x00D5=>0x00F5, 0x0055=>0x0075,
		0x0176=>0x0177, 0x00DC=>0x00FC, 0x1E56=>0x1E57, 0x03A3=>0x03C3, 0x041A=>0x043A,
		0x004D=>0x006D, 0x016A=>0x016B, 0x0170=>0x0171, 0x0424=>0x0444, 0x00CC=>0x00EC,
		0x0168=>0x0169, 0x039F=>0x03BF, 0x004B=>0x006B, 0x00D2=>0x00F2, 0x00C0=>0x00E0,
		0x0414=>0x0434, 0x03A9=>0x03C9, 0x1E6A=>0x1E6B, 0x00C3=>0x00E3, 0x042D=>0x044D,
		0x0416=>0x0436, 0x01A0=>0x01A1, 0x010C=>0x010D, 0x011C=>0x011D, 0x00D0=>0x00F0,
		0x013B=>0x013C, 0x040F=>0x045F, 0x040A=>0x045A, 0x00C8=>0x00E8, 0x03A5=>0x03C5,
		0x0046=>0x0066, 0x00DD=>0x00FD, 0x0043=>0x0063, 0x021A=>0x021B, 0x00CA=>0x00EA,
		0x0399=>0x03B9, 0x0179=>0x017A, 0x00CF=>0x00EF, 0x01AF=>0x01B0, 0x0045=>0x0065,
		0x039B=>0x03BB, 0x0398=>0x03B8, 0x039C=>0x03BC, 0x040C=>0x045C, 0x041F=>0x043F,
		0x042C=>0x044C, 0x00DE=>0x00FE, 0x00D0=>0x00F0, 0x1EF2=>0x1EF3, 0x0048=>0x0068,
		0x00CB=>0x00EB, 0x0110=>0x0111, 0x0413=>0x0433, 0x012E=>0x012F, 0x00C6=>0x00E6,
		0x0058=>0x0078, 0x0160=>0x0161, 0x016E=>0x016F, 0x0391=>0x03B1, 0x0407=>0x0457,
		0x0172=>0x0173, 0x0178=>0x00FF, 0x004F=>0x006F, 0x041B=>0x043B, 0x0395=>0x03B5,
		0x0425=>0x0445, 0x0120=>0x0121, 0x017D=>0x017E, 0x017B=>0x017C, 0x0396=>0x03B6,
		0x0392=>0x03B2, 0x0388=>0x03AD, 0x1E84=>0x1E85, 0x0174=>0x0175, 0x0051=>0x0071,
		0x0417=>0x0437, 0x1E0A=>0x1E0B, 0x0147=>0x0148, 0x0104=>0x0105, 0x0408=>0x0458,
		0x014C=>0x014D, 0x00CD=>0x00ED, 0x0059=>0x0079, 0x010A=>0x010B, 0x038F=>0x03CE,
		0x0052=>0x0072, 0x0410=>0x0430, 0x0405=>0x0455, 0x0402=>0x0452, 0x0126=>0x0127,
		0x0136=>0x0137, 0x012A=>0x012B, 0x038A=>0x03AF, 0x042B=>0x044B, 0x004C=>0x006C,
		0x0397=>0x03B7, 0x0124=>0x0125, 0x0218=>0x0219, 0x00DB=>0x00FB, 0x011E=>0x011F,
		0x041E=>0x043E, 0x1E40=>0x1E41, 0x039D=>0x03BD, 0x0106=>0x0107, 0x03AB=>0x03CB,
		0x0426=>0x0446, 0x00DE=>0x00FE, 0x00C7=>0x00E7, 0x03AA=>0x03CA, 0x0421=>0x0441,
		0x0412=>0x0432, 0x010E=>0x010F, 0x00D8=>0x00F8, 0x0057=>0x0077, 0x011A=>0x011B,
		0x0054=>0x0074, 0x004A=>0x006A, 0x040B=>0x045B, 0x0406=>0x0456, 0x0102=>0x0103,
		0x039B=>0x03BB, 0x00D1=>0x00F1, 0x041D=>0x043D, 0x038C=>0x03CC, 0x00C9=>0x00E9,
		0x00D0=>0x00F0, 0x0407=>0x0457, 0x0122=>0x0123,
	);

	$UTF8_LOWER_TO_UPPER = array(
		0x0061=>0x0041, 0x03C6=>0x03A6, 0x0163=>0x0162, 0x00E5=>0x00C5, 0x0062=>0x0042,
		0x013A=>0x0139, 0x00E1=>0x00C1, 0x0142=>0x0141, 0x03CD=>0x038E, 0x0101=>0x0100,
		0x0491=>0x0490, 0x03B4=>0x0394, 0x015B=>0x015A, 0x0064=>0x0044, 0x03B3=>0x0393,
		0x00F4=>0x00D4, 0x044A=>0x042A, 0x0439=>0x0419, 0x0113=>0x0112, 0x043C=>0x041C,
		0x015F=>0x015E, 0x0144=>0x0143, 0x00EE=>0x00CE, 0x045E=>0x040E, 0x044F=>0x042F,
		0x03BA=>0x039A, 0x0155=>0x0154, 0x0069=>0x0049, 0x0073=>0x0053, 0x1E1F=>0x1E1E,
		0x0135=>0x0134, 0x0447=>0x0427, 0x03C0=>0x03A0, 0x0438=>0x0418, 0x00F3=>0x00D3,
		0x0440=>0x0420, 0x0454=>0x0404, 0x0435=>0x0415, 0x0449=>0x0429, 0x014B=>0x014A,
		0x0431=>0x0411, 0x0459=>0x0409, 0x1E03=>0x1E02, 0x00F6=>0x00D6, 0x00F9=>0x00D9,
		0x006E=>0x004E, 0x0451=>0x0401, 0x03C4=>0x03A4, 0x0443=>0x0423, 0x015D=>0x015C,
		0x0453=>0x0403, 0x03C8=>0x03A8, 0x0159=>0x0158, 0x0067=>0x0047, 0x00E4=>0x00C4,
		0x03AC=>0x0386, 0x03AE=>0x0389, 0x0167=>0x0166, 0x03BE=>0x039E, 0x0165=>0x0164,
		0x0117=>0x0116, 0x0109=>0x0108, 0x0076=>0x0056, 0x00FE=>0x00DE, 0x0157=>0x0156,
		0x00FA=>0x00DA, 0x1E61=>0x1E60, 0x1E83=>0x1E82, 0x00E2=>0x00C2, 0x0119=>0x0118,
		0x0146=>0x0145, 0x0070=>0x0050, 0x0151=>0x0150, 0x044E=>0x042E, 0x0129=>0x0128,
		0x03C7=>0x03A7, 0x013E=>0x013D, 0x0442=>0x0422, 0x007A=>0x005A, 0x0448=>0x0428,
		0x03C1=>0x03A1, 0x1E81=>0x1E80, 0x016D=>0x016C, 0x00F5=>0x00D5, 0x0075=>0x0055,
		0x0177=>0x0176, 0x00FC=>0x00DC, 0x1E57=>0x1E56, 0x03C3=>0x03A3, 0x043A=>0x041A,
		0x006D=>0x004D, 0x016B=>0x016A, 0x0171=>0x0170, 0x0444=>0x0424, 0x00EC=>0x00CC,
		0x0169=>0x0168, 0x03BF=>0x039F, 0x006B=>0x004B, 0x00F2=>0x00D2, 0x00E0=>0x00C0,
		0x0434=>0x0414, 0x03C9=>0x03A9, 0x1E6B=>0x1E6A, 0x00E3=>0x00C3, 0x044D=>0x042D,
		0x0436=>0x0416, 0x01A1=>0x01A0, 0x010D=>0x010C, 0x011D=>0x011C, 0x00F0=>0x00D0,
		0x013C=>0x013B, 0x045F=>0x040F, 0x045A=>0x040A, 0x00E8=>0x00C8, 0x03C5=>0x03A5,
		0x0066=>0x0046, 0x00FD=>0x00DD, 0x0063=>0x0043, 0x021B=>0x021A, 0x00EA=>0x00CA,
		0x03B9=>0x0399, 0x017A=>0x0179, 0x00EF=>0x00CF, 0x01B0=>0x01AF, 0x0065=>0x0045,
		0x03BB=>0x039B, 0x03B8=>0x0398, 0x03BC=>0x039C, 0x045C=>0x040C, 0x043F=>0x041F,
		0x044C=>0x042C, 0x00FE=>0x00DE, 0x00F0=>0x00D0, 0x1EF3=>0x1EF2, 0x0068=>0x0048,
		0x00EB=>0x00CB, 0x0111=>0x0110, 0x0433=>0x0413, 0x012F=>0x012E, 0x00E6=>0x00C6,
		0x0078=>0x0058, 0x0161=>0x0160, 0x016F=>0x016E, 0x03B1=>0x0391, 0x0457=>0x0407,
		0x0173=>0x0172, 0x00FF=>0x0178, 0x006F=>0x004F, 0x043B=>0x041B, 0x03B5=>0x0395,
		0x0445=>0x0425, 0x0121=>0x0120, 0x017E=>0x017D, 0x017C=>0x017B, 0x03B6=>0x0396,
		0x03B2=>0x0392, 0x03AD=>0x0388, 0x1E85=>0x1E84, 0x0175=>0x0174, 0x0071=>0x0051,
		0x0437=>0x0417, 0x1E0B=>0x1E0A, 0x0148=>0x0147, 0x0105=>0x0104, 0x0458=>0x0408,
		0x014D=>0x014C, 0x00ED=>0x00CD, 0x0079=>0x0059, 0x010B=>0x010A, 0x03CE=>0x038F,
		0x0072=>0x0052, 0x0430=>0x0410, 0x0455=>0x0405, 0x0452=>0x0402, 0x0127=>0x0126,
		0x0137=>0x0136, 0x012B=>0x012A, 0x03AF=>0x038A, 0x044B=>0x042B, 0x006C=>0x004C,
		0x03B7=>0x0397, 0x0125=>0x0124, 0x0219=>0x0218, 0x00FB=>0x00DB, 0x011F=>0x011E,
		0x043E=>0x041E, 0x1E41=>0x1E40, 0x03BD=>0x039D, 0x0107=>0x0106, 0x03CB=>0x03AB,
		0x0446=>0x0426, 0x00FE=>0x00DE, 0x00E7=>0x00C7, 0x03CA=>0x03AA, 0x0441=>0x0421,
		0x0432=>0x0412, 0x010F=>0x010E, 0x00F8=>0x00D8, 0x0077=>0x0057, 0x011B=>0x011A,
		0x0074=>0x0054, 0x006A=>0x004A, 0x045B=>0x040B, 0x0456=>0x0406, 0x0103=>0x0102,
		0x03BB=>0x039B, 0x00F1=>0x00D1, 0x043D=>0x041D, 0x03CC=>0x038C, 0x00E9=>0x00C9,
		0x00F0=>0x00D0, 0x0457=>0x0407, 0x0123=>0x0122,
	);

	/**
	* UTF-8 aware alternative to strtolower
	* Make a string lowercase
	* Note: The concept of a characters "case" only exists is some alphabets
	* such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	* not exist in the Chinese alphabet, for example. See Unicode Standard
	* Annex #21: Case Mappings
	* 
	* @author Andreas Gohr <andi@splitbrain.org>
	* @param string
	* @return mixed either string in lowercase or FALSE is UTF-8 invalid
	*/
	function utf8_strtolower($string)
	{
		global $UTF8_UPPER_TO_LOWER;

		$uni = utf8_to_unicode($string);

		if (!$uni)
		{
			return false;
		}

		for ($i = 0, $cnt = sizeof($uni); $i < $cnt; $i++)
		{
			if (isset($UTF8_UPPER_TO_LOWER[$uni[$i]]))
			{
				$uni[$i] = $UTF8_UPPER_TO_LOWER[$uni[$i]];
			}
		}

		return utf8_from_unicode($uni);
	}

	/**
	* UTF-8 aware alternative to strtoupper
	* Make a string uppercase
	* Note: The concept of a characters "case" only exists is some alphabets
	* such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	* not exist in the Chinese alphabet, for example. See Unicode Standard
	* Annex #21: Case Mappings
	* 
	* @author Andreas Gohr <andi@splitbrain.org>
	* @param string
	* @return mixed either string in lowercase or FALSE is UTF-8 invalid
	*/
	function utf8_strtoupper($str)
	{
		global $UTF8_LOWER_TO_UPPER;

		$uni = utf8_to_unicode($string);

		if (!$uni)
		{
			return false;
		}

		for ($i = 0, $cnt = sizeof($uni); $i < $cnt; $i++)
		{
			if (isset($UTF8_LOWER_TO_UPPER[$uni[$i]]))
			{
				$uni[$i] = $UTF8_LOWER_TO_UPPER[$uni[$i]];
			}
		}

		return utf8_from_unicode($uni);
	}

	/**
	* UTF-8 aware alternative to substr
	* Return part of a string given character offset (and optionally length)
	* 
	* @author Harry Fuecks
	* @param string
	* @param integer number of UTF-8 characters offset (from left)
	* @param integer (optional) length in UTF-8 characters from offset
	* @return mixed string or FALSE if failure
	*/
	function utf8_substr($str, $offset,	$length	= null)
	{
		if ($offset >= 0 && $length >= 0)
		{
			if ($length === null)
			{
				$length = '*';
			}
			else
			{
				if (!preg_match('/^[0-9]+$/', $length))
				{
					trigger_error('utf8_substr expects parameter 3 to be long', E_USER_WARNING);
					return false;
				}

				$strlen = strlen(utf8_decode($str));
				if ($offset > $strlen)
				{
					return '';
				}

				if (($offset + $length) > $strlen)
				{
					$length = '*';
				}
				else
				{
					$length = '{' . $length . '}';
				}
			}

			if (!preg_match('/^[0-9]+$/', $offset))
			{
				trigger_error('utf8_substr expects parameter 2 to be long', E_USER_WARNING);
				return false;
			}

			$pattern = '/^.{' . $offset . '}(.' . $length . ')/us';

			preg_match($pattern, $str, $matches);

			if (isset($matches[1]))
			{
				return $matches[1];
			}

			return false;
		}
		else
		{
			// Handle negatives using different, slower technique
			// From: http://www.php.net/manual/en/function.substr.php#44838
			preg_match_all('/./u', $str, $ar);

			if ($length !== null)
			{
				return join('', array_slice($ar[0], $offset, $length));
			}
			else
			{
				return join('', array_slice($ar[0], $offset));
			}
		}
	}

	/**
	* Return the length (in characters) of a UTF-8 string
	*
	* @param	string	$text		UTF-8 string
	* @return	integer				Length (in chars) of given string
	*/
	function utf8_strlen($text)
	{
		// Since utf8_decode is replacing multibyte characters to ? strlen works fine
		return strlen(utf8_decode($text));
	}
}

/**
* UTF-8 aware alternative to str_split
* Convert a string to an array
* 
* @author Harry Fuecks
* @param string UTF-8 encoded
* @param int number to characters to split string by
* @return string characters in string reverses
*/
function utf8_str_split($str, $split_len = 1)
{
	if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)
	{
		return false;
	}

	$len = utf8_strlen($str);
	if ($len <= $split_len)
	{
		return array($str);
	}
	
	preg_match_all('/.{' . $split_len . '}|[^\x00]{1,' . $split_len . '}$/us', $str, $ar);
	return $ar[0];
}

/**
* UTF-8 aware alternative to strcspn
* Find length of initial segment not matching mask
* 
* @author Harry Fuecks
* @param string
* @return int
*/
function utf8_strspn($str, $mask, $start = null, $length = null)
{
	$mask = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $mask);

	if ($start !== null || $length !== null)
	{
		$str = utf8_substr($str, $start, $length);
	}

	preg_match('/^[' . $mask . ']+/u', $str, $matches);

	if (isset($matches[0]))
	{
		return utf8_strlen($matches[0]);
	}

	return 0;
}

/**
* UTF-8 aware alternative to ucfirst
* Make a string's first character uppercase
* 
* @author Harry Fuecks
* @param string
* @return string with first character as upper case (if applicable)
*/
function utf8_ucfirst($str)
{
	switch (utf8_strlen($str))
	{
		case 0:
			return '';
		break;

		case 1:
			return utf8_strtoupper($str);
		break;

		default:
			preg_match('/^(.{1})(.*)$/us', $str, $matches);
			return utf8_strtoupper($matches[1]) . $matches[2];
		break;
	}
}

/**
* Recode a string to UTF-8
*
* If the encoding is not supported, the string is returned as-is
*
* @param	string	$string		Original string
* @param	string	$encoding	Original encoding (lowered)
* @return	string				The string, encoded in UTF-8
*/
function utf8_recode($string, $encoding)
{
	$encoding = strtolower($encoding);

	if ($encoding == 'utf-8' || !is_string($string) || !isset($string[0]))
	{
		return $string;
	}

	// start with something simple
	if ($encoding == 'iso-8859-1')
	{
		return utf8_encode($string);
	}

	// First, try iconv()
	if (function_exists('iconv'))
	{
		$ret = @iconv($encoding, 'utf-8', $string);

		if (isset($ret[0]))
		{
			return $ret;
		}
	}

	// Try the mb_string extension
	if (function_exists('mb_convert_encoding'))
	{
		$ret = @mb_convert_encoding($string, 'utf-8', $encoding);

		if (isset($ret[0]))
		{
			return $ret;
		}
	}

	// Try the recode extension
	if (function_exists('recode_string'))
	{
		$ret = @recode_string($encoding . '..utf-8', $string);

		if (isset($ret[0]))
		{
			return $ret;
		}
	}

	// If nothing works, check if we have a custom transcoder available
	if (!preg_match('#^[a-z0-9\\-]+$#', $encoding))
	{
		// Make sure the encoding name is alphanumeric, we don't want it to be abused into loading arbitrary files
		trigger_error('Unknown encoding: ' . $encoding, E_USER_ERROR);
	}

	global $phpbb_root_path;

	if (!file_exists($phpbb_root_path . 'includes/utf/data/'))
	{
		return $string;
	}

	die('Finish me!! ' . basename(__FILE__) . ' at line ' . __LINE__);
}

/**
* Replace all UTF-8 chars that are not in ASCII with their NCR
*
* @param	string	$text		UTF-8 string in NFC
* @return	string				ASCII string using NCRs for non-ASCII chars
*/
function utf8_encode_ncr($text)
{
	return preg_replace_callback('#[\\xC2-\\xF4][\\x80-\\xBF]?[\\x80-\\xBF]?[\\x80-\\xBF]+#', 'utf8_encode_ncr_callback', $text);
}

/**
* Callback used in encode_ncr()
*
* Takes a UTF-8 char and replaces it with its NCR. Attention, $m is an array
*
* @param	array	$m			0-based numerically indexed array passed by preg_replace_callback()
* @return	string				A HTML NCR if the character is valid, or the original string otherwise
*/
function utf8_encode_ncr_callback($m)
{
	return '&#' . utf8_ord($m[0]) . ';';
}

/**
* Enter description here...
*
* @param string $chr UTF-8 char
* @return integer UNICODE code point
*/
function utf8_ord($chr)
{
	switch (strlen($chr))
	{
		case 1:
			return ord($chr);
		break;

		case 2:
			return ((ord($chr[0]) & 0x1F) << 6) | (ord($chr[1]) & 0x3F);
		break;

		case 3:
			return ((ord($chr[0]) & 0x0F) << 12) | ((ord($chr[1]) & 0x3F) << 6) | (ord($chr[2]) & 0x3F);
		break;

		case 4:
			return ((ord($chr[0]) & 0x07) << 18) | ((ord($chr[1]) & 0x3F) << 12) | ((ord($chr[2]) & 0x3F) << 6) | (ord($chr[3]) & 0x3F);
		break;

		default:
			return $m;
	}
}

/**
* Converts an NCR to a UTF-8 char
*
* @param integer $cp UNICODE code point
* @return string UTF-8 char
*/
function utf8_chr($cp)
{
	if ($cp > 0xFFFF)
	{
		return chr(0xF0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3F)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	}
	else if ($cp > 0x7FF)
	{
		return chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	}
	else if ($cp > 0x7F)
	{
		return chr(0xC0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3F));
	}
	else
	{
		return chr($cp);
	}
}

/**
* Convert Numeric Character References to UTF-8 chars
*
* Notes:
*  - we do not convert NCRs recursively, if you pass &#38;#38; it will return &#38;
*  - we DO NOT check for the existence of the Unicode characters, therefore an entity
*    may be converted to an inexistent codepoint
*
* @param	string	$text		String to convert, encoded in UTF-8 (no normal form required)
* @return	string				UTF-8 string where NCRs have been replaced with the actual chars
*/
function utf8_decode_ncr($text)
{
	return preg_replace_callback('/&#([0-9]{1,6}|x[0-9A-F]{1,5});/i', 'utf8_decode_ncr_callback', $text);
}

/**
* Callback used in decode_ncr()
*
* Takes a NCR (in decimal or hexadecimal) and returns a UTF-8 char. Attention, $m is an array.
* It will ignore most of invalid NCRs, but not all!
*
* @param	array	$m			0-based numerically indexed array passed by preg_replace_callback()
* @return	string				UTF-8 char
*/
function utf8_decode_ncr_callback($m)
{
	$cp = (strncasecmp($m[1], 'x', 1)) ? $m[1] : hexdec(substr($m[1], 1));

	return utf8_chr($cp);
}

/**
* Takes an UTF-8 string and returns an array of ints representing the
* Unicode characters.
* 
* @param  string  UTF-8 encoded string
* @return array array of UNICODE code points
*/
function utf8_to_unicode($string)
{
	$unicode = array();
	$offset = 0;
	$stringlength = strlen($string);

	while ($offset < $stringlength)
	{
		$ord = ord($string{$offset});
		if (($ord | 0x07) == 0xF7)
		{
			// 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
			$charval = (($ord & 0x07) << 18) &
						((ord($string{($offset + 1)}) & 0x3F) << 12) &
						((ord($string{($offset + 2)}) & 0x3F) <<  6) &
						(ord($string{($offset + 3)}) & 0x3F);
			$offset += 4;
		}
		else if (($ord | 0x0F) == 0xEF)
		{
			// 1110bbbb 10bbbbbb 10bbbbbb
			$charval = (($ord & 0x0F) << 12) &
						((ord($string{($offset + 1)}) & 0x3F) <<  6) &
						(ord($string{($offset + 2)}) & 0x3F);
			$offset += 3;
		}
		else if (($ord | 0x1F) == 0xDF)
		{
			// 110bbbbb 10bbbbbb
			$charval = (($ord & 0x1F) <<  6) &
						(ord($string{($offset + 1)}) & 0x3F);
			$offset += 2;
		}
		else if (($ord | 0x7F) == 0x7F)
		{
			// 0bbbbbbb
			$charval = $ord;
			$offset += 1;
		}
		else
		{
			// error? throw some kind of warning here?
			$charval = false;
			$offset += 1;
		}
		if ($charval !== false)
		{
			$unicode[] = $charval;
		}
	}
	return $unicode;
}

/**
* Takes an array of ints representing the Unicode characters and returns
* a UTF-8 string.
*
* @param array $array array of unicode code points representing a string
* @return string UTF-8 character string
*/
function utf8_from_unicode($array)
{
	$str = '';
	foreach ($array as $value)
	{
		$str .= utf8_chr($value);
	}
	return $str;
}

?>