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
download('http://unicode.org/Public/UNIDATA/CaseFolding.txt');
echo "\n";


/**
* Load the CaseFolding table
*/
echo "Loading CaseFolding\n";
$unidata = file_get_contents('CaseFolding.txt');


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

preg_match_all('/^([0-9A-F]+); ([CFS]); ([0-9A-F]+(?: [0-9A-F]+)*);/im', $unidata, $array, PREG_SET_ORDER);

$uniarray = array();

foreach ($array as $value)
{
    $uniarray[$value[2]][utf8_chr(hexdec((string)$value[1]))] = implode(array_map('utf8_chr', array_map('hexdec', explode(' ', $value[3]))));
}

foreach ($uniarray as $idx => $contents)
{
	echo "Writing to case_fold_$idx.$phpEx\n";
	$fp = fopen($phpbb_root_path . 'includes/utf/data/case_fold_' . strtolower($idx) . '.' . $phpEx, 'wb');
	fwrite($fp, '<?php return ' . my_var_export($contents) . ';');
	fclose($fp);
}

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
