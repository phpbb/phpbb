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

if (php_sapi_name() != 'cli')
{
	die("This program must be run from the command line.\n");
}

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

set_time_limit(0);

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

echo "Checking for required files\n";
download('http://www.unicode.org/Public/UNIDATA/UnicodeData.txt');
echo "\n";

/**
* Generate the files needed by the search indexer
*/
echo "Generating search indexer tables\n";

$fp = fopen($phpbb_root_path . 'develop/UnicodeData.txt', 'rt');

$map = array();
while ($line = fgets($fp, 1024))
{
	/**
	* The current line is split, $m[0] hold the codepoint in hexadecimal and
	* all other fields numbered as in http://www.unicode.org/Public/UNIDATA/UCD.html#UnicodeData.txt
	*/
	$m = explode(';', $line);

	/**
	* @var	integer	$cp			Current char codepoint
	* @var	string	$utf_char	UTF-8 representation of current char
	*/
	$cp = hexdec($m[0]);
	$utf_char = cp_to_utf($cp);

	/**
	* $m[2] holds the "General Category" of the character
	* @link http://www.unicode.org/Public/UNIDATA/UCD.html#General_Category_Values
	*/
	switch ($m[2][0])
	{
		case 'L':
			/**
			* We allow all letters and map them to their lowercased counterpart on the fly
			*/
			$map_to_hex = (isset($m[13][0])) ? $m[13] : $m[0];

			if (preg_match('#^LATIN.*(?:LETTER|LIGATURE) ([A-Z]{2}(?![A-Z]))$#', $m[1], $capture))
			{
				/**
				* Special hack for some latin ligatures. Using the name of a character
				* is bad practice, but for now it works well enough.
				*
				* @todo Note that ligatures with combining marks such as U+01E2 are
				* not supported at this time
				*/
				$map[$cp] = strtolower($capture[1]);
			}
			else if (isset($m[13][0]))
			{
				/**
				* If the letter has a lowercased form, use it
				*/
				$map[$cp] = hex_to_utf($m[13]);
			}
			else
			{
				/**
				* In all other cases, map the letter to itself
				*/
				$map[$cp] = $utf_char;
			}
			break;

		case 'M':
			/**
			* We allow all marks, they are mapped to themselves
			*/
			$map[$cp] = $utf_char;
			break;

		case 'N':
			/**
			* We allow all numbers, but we map them to their numeric value whenever
			* possible. The numeric value (field #8) is in ASCII already
			*
			* @todo Note that fractions such as U+00BD will be converted to something
			* like "1/2", with a slash. However, "1/2" entered in ASCII is converted
			* to "1 2". This will have to be fixed.
			*/
			$map[$cp] = (isset($m[8][0])) ? $m[8] : $utf_char;
			break;

		default:
			/**
			* Everything else is ignored, skip to the next line
			*/
			continue 2;
	}
}
fclose($fp);

/**
* Add some cheating
*/
$cheats = array(
	'00DF'	=>	'ss',		#	German sharp S
	'00C5'	=>	'ae',		#	Capital A with diaeresis
	'00E4'	=>	'ae',		#	Small A with diaeresis
	'00D6'	=>	'oe',		#	Capital O with diaeresis
	'00F6'	=>	'oe',		#	Small O with diaeresis
	'00DC'	=>	'ue',		#	Capital U with diaeresis
	'00FC'	=>	'ue',		#	Small U with diaeresis
);

/**
* Add our "cheat replacements" to the map
*/
foreach ($cheats as $hex => $map_to)
{
	$map[hexdec($hex)] = $map_to;
}

/**
* Split the map into smaller blocks
*/
$file_contents = array();
foreach ($map as $cp => $map_to)
{
	$file_contents[$cp >> 11][cp_to_utf($cp)] = $map_to;
}
unset($map);

foreach ($file_contents as $idx => $contents)
{
	echo "Writing to search_indexer_$idx.$phpEx\n";
	$fp = fopen($phpbb_root_path . 'includes/utf/data/search_indexer_' . $idx . '.' . $phpEx, 'wb');
	fwrite($fp, '<?php return ' . my_var_export($contents) . ';');
	fclose($fp);
}
echo "\n*** Search indexer tables done\n\n";


die("\nAll done!\n");


////////////////////////////////////////////////////////////////////////////////
//                             Internal functions                             //
////////////////////////////////////////////////////////////////////////////////

/**
* Return a parsable string representation of a variable
*
* This is function is limited to array/strings/integers
*
* @param	mixed	$var		Variable
* @return	string				PHP code representing the variable
*/
function my_var_export($var)
{
	if (is_array($var))
	{
		$lines = array();

		foreach ($var as $k => $v)
		{
			$lines[] = my_var_export($k) . '=>' . my_var_export($v);
		}

		return 'array(' . implode(',', $lines) . ')';
	}
	else if (is_string($var))
	{
		return "'" . str_replace(array('\\', "'"), array('\\\\', "\\'"), $var) . "'";
	}
	else
	{
		return $var;
	}
}

/**
* Download a file to the develop/ dir
*
* @param	string	$url		URL of the file to download
* @return	null
*/
function download($url)
{
	global $phpbb_root_path;

	if (file_exists($phpbb_root_path . 'develop/' . basename($url)))
	{
		return;
	}

	echo 'Downloading from ', $url, ' ';

	if (!$fpr = fopen($url, 'rb'))
	{
		die("Can't download from $url\nPlease download it yourself and put it in the develop/ dir, kthxbai");
	}

	if (!$fpw = fopen($phpbb_root_path . 'develop/' . basename($url), 'wb'))
	{
		die("Can't open develop/" . basename($url) . " for output... please check your permissions or something");
	}

	$i = 0;
	$chunk = 32768;
	$done = '';

	while (!feof($fpr))
	{
		$i += fwrite($fpw, fread($fpr, $chunk));
		echo str_repeat("\x08", strlen($done));

		$done = ($i >> 10) . ' KiB';
		echo $done;
	}
	fclose($fpr);
	fclose($fpw);

	echo "\n";
}

/**
* Convert a codepoint in hexadecimal to a UTF-8 char
*
* @param	string	$hex		Codepoint, in hexadecimal
* @return	string				UTF-8 char
*/
function hex_to_utf($hex)
{
	return cp_to_utf(hexdec($hex));
}

/**
* Convert a codepoint to a UTF-8 char
*
* @param	integer	$cp			Unicode codepoint
* @return	string				UTF-8 string
*/
function cp_to_utf($cp)
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
