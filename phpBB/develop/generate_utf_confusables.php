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
download('http://unicode.org/reports/tr39/data/confusables.txt');
download('http://unicode.org/Public/UNIDATA/CaseFolding.txt');
echo "\n";


/**
* Load the confusables table
*/
echo "Loading confusables\n";
$unidata = file_get_contents('confusables.txt');

/**
* Load the CaseFolding table
*/
echo "Loading CaseFolding\n";
$casefolds = file_get_contents('CaseFolding.txt');


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

preg_match_all('/^([0-9A-F]+) ;\s((?:[0-9A-F]+ )*);.*?$/im', $unidata, $array, PREG_SET_ORDER);
preg_match_all('/^([0-9A-F]+); ([CFS]); ([0-9A-F]+(?: [0-9A-F]+)*);/im', $casefolds, $casefold_array);

// some that we defined ourselves
$uniarray = array(
		"\xC2\xA1"			=>	"\x69",	// EXCLAMATION MARK, INVERTED => LATIN SMALL LETTER I
		"\xC7\x83"			=>	"\x21",	// LATIN LETTER RETROFLEX CLICK => EXCLAMATION MARK
		"\xCE\xB1"			=>	"\x61",	// GREEK SMALL LETTER ALPHA => LATIN SMALL LETTER A
		"\xE1\x9A\x80"		=>	"\x20",	// OGHAM SPACE MARK

		"\xC2\xAD"			=>	'',		// HYPHEN, SOFT => empty string
		"\xDB\x9D"			=>	'',		// ARABIC END OF AYAH
		"\xDC\x8F"			=>	'',		// SYRIAC ABBREVIATION MARK
		"\xE1\xA0\x86"		=>	'',		// MONGOLIAN TODO SOFT HYPHEN
		"\xE1\xA0\x8E"		=>	'',		// MONGOLIAN VOWEL SEPARATOR
		"\xE2\x80\x8B"		=>	'',		// ZERO WIDTH SPACE
		"\xE2\x80\x8C"		=>	'',		// ZERO WIDTH NON-JOINER
		"\xE2\x80\x8D"		=>	'',		// ZERO WIDTH JOINER
		"\xE2\x80\xA8"		=>	'',		// LINE SEPARATOR
		"\xE2\x80\xA9"		=>	'',		// PARAGRAPH SEPARATOR
		"\xE2\x81\xA0"		=>	'',		// WORD JOINER
		"\xE2\x81\xA1"		=>	'',		// FUNCTION APPLICATION
		"\xE2\x81\xA2"		=>	'',		// INVISIBLE TIMES
		"\xE2\x81\xA3"		=>	'',		// INVISIBLE SEPARATOR
		"\xE2\x81\xAA"		=>	'',		// [CONTROL CHARACTERS]
		"\xE2\x81\xAB"		=>	'',		// [CONTROL CHARACTERS]
		"\xE2\x81\xAC"		=>	'',		// [CONTROL CHARACTERS]
		"\xE2\x81\xAD"		=>	'',		// [CONTROL CHARACTERS]
		"\xE2\x81\xAE"		=>	'',		// [CONTROL CHARACTERS]
		"\xE2\x81\xAF"		=>	'',		// [CONTROL CHARACTERS]
		"\xEF\xBB\xBF"		=>	'',		// ZERO WIDTH NO-BREAK SPACE
		"\xEF\xBF\xB9"		=>	'',		// [CONTROL CHARACTERS]
		"\xEF\xBF\xBA"		=>	'',		// [CONTROL CHARACTERS]
		"\xEF\xBF\xBB"		=>	'',		// [CONTROL CHARACTERS]
		"\xEF\xBF\xBC"		=>	'',		// [CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB3"	=>	'',		// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB4"	=>	'',		// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB5"	=>	'',		// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB6"	=>	'',		// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB7"	=>	'',		// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB8"	=>	'',		// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB9"	=>	'',		// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xBA"	=>	'',		// [MUSICAL CONTROL CHARACTERS]
);

$copy = $uniarray;

/**
* @todo we need to check that the $uniarray does not reverse any of the mappings defined in the unicode definition
*/

foreach ($array as $value)
{
	$temp_hold = implode(array_map('utf8_chr', array_map('hexdec', explode(' ', trim($value[2])))));

	if (isset($copy[utf8_chr(hexdec((string)$value[1]))]))
	{
		$num = '';
		$string = utf8_chr(hexdec((string)$value[1]));
		for ($i = 0; $i < strlen($string); $i++)
		{
			$num .= '\x' . str_pad(base_convert(ord($string[$i]), 10, 16), 2, '0', STR_PAD_LEFT);
		}
		echo $num . "\n";
		if ($uniarray[$string] != $temp_hold)
		{
			echo "  --> $string\n";
			echo "  --> " . $temp_hold . "\n";
		}
	}

	// do some tests for things that transform into something with the number one
	if (strpos($temp_hold, utf8_chr(0x0031)) !== false)
	{
		// any kind of letter L?
		if (strpos($value[0], 'LETTER L') !== false || strpos($value[0], 'IOTA') !== false || strpos($value[0], 'SMALL L ') !== false || preg_match('/SMALL LIGATURE [^L]*L /', $value[0]))
		{
			// replace all of the mappings that transform some sort of letter l to number one instead to some sort of letter l to latin small letter l
			$temp_hold = str_replace(utf8_chr(0x0031), utf8_chr(0x006C), $temp_hold);
		}
	}

	// uppercased chars that were folded do not exist in this universe,
	// no amount of normalization could ever "trick" this into not working
	if (in_array($value[1], $casefold_array[1]))
	{
		continue;
	}

	$uniarray[utf8_chr(hexdec((string)$value[1]))] = $temp_hold;
}

echo "Writing to confusables.$phpEx\n";

$fp = fopen($phpbb_root_path . 'includes/utf/data/confusables.' . $phpEx, 'wb');
fwrite($fp, '<?php return ' . my_var_export($uniarray) . ';');
fclose($fp);

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
