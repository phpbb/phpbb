<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
error_reporting(E_ALL);

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);


/**
* Let's download some files we need
*/
download('http://www.unicode.org/Public/UNIDATA/NormalizationTest.txt');
download('http://www.unicode.org/Public/UNIDATA/UnicodeData.txt');

/**
* Those are the tests we run
*/
$test_suite = array(
	/**
	* NFC
	*   c2 ==  NFC(c1) ==  NFC(c2) ==  NFC(c3)
	*   c4 ==  NFC(c4) ==  NFC(c5)
	*/
	'NFC'	=>	array(
		'c2'	=>	array('c1', 'c2', 'c3'),
		'c4'	=>	array('c4', 'c5')
	),

	/**
	* NFD
	*   c3 ==  NFD(c1) ==  NFD(c2) ==  NFD(c3)
	*   c5 ==  NFD(c4) ==  NFD(c5)
	*/
	'NFD'	=>	array(
		'c3'	=>	array('c1', 'c2', 'c3'),
		'c5'	=>	array('c4', 'c5')
	),

	/**
	* NFKC
	*   c4 == NFKC(c1) == NFKC(c2) == NFKC(c3) == NFKC(c4) == NFKC(c5)
	*/
	'NFKC'	=>	array(
		'c4'	=>	array('c1', 'c2', 'c3', 'c4', 'c5')
	),

	/**
	* NFKD
	*   c5 == NFKD(c1) == NFKD(c2) == NFKD(c3) == NFKD(c4) == NFKD(c5)
	*/
	'NFKD'	=>	array(
		'c5'	=>	array('c1', 'c2', 'c3', 'c4', 'c5')
	)
);

require_once($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);

$i = $n = 0;
$failed = false;
$tested_chars = array();

$fp = fopen($phpbb_root_path . 'develop/NormalizationTest.txt', 'rb');
while (!feof($fp))
{
	$line = fgets($fp);
	++$n;

	if ($line[0] == '@')
	{
		if ($i)
		{
			echo "done\n";
		}

		$i = 0;
		echo "\n", substr($line, 1), "\n\n";
		continue;
	}

	if (!strpos(' 0123456789ABCDEF', $line[0]))
	{
		continue;
	}

	if (++$i % 100 == 0)
	{
		echo $i, ' ';
	}

	list($c1, $c2, $c3, $c4, $c5) = explode(';', $line);

	if (!strpos($c1, ' '))
	{
		/**
		* We are currently testing a single character, we add it to the list of
		* characters we have processed so that we can exclude it when testing
		* for invariants
		*/
		$tested_chars[$c1] = 1;
	}

	foreach ($test_suite as $form => $serie)
	{
		foreach ($serie as $expected => $tests)
		{
			$hex_expected = ${$expected};
			$utf_expected = hexseq_to_utf($hex_expected);

			foreach ($tests as $test)
			{
				$utf_result = $utf_expected;
				call_user_func(array('utf_normalizer', $form), $utf_result);

				if (strcmp($utf_expected, $utf_result))
				{
					$failed = true;
					$hex_result = utf_to_hexseq($utf_result);

					echo "\nFAILED $expected == $form($test) ($hex_expected != $hex_result)";
				}
			}
		}

		if ($failed)
		{
			die("\n\nFailed at line $n\n");
		}
	}
}
fclose($fp);

/**
* Test for invariants
*/
echo "\n\nTesting for invariants...\n\n";

$fp = fopen($phpbb_root_path . 'develop/UnicodeData.txt', 'rt');

$n = 0;
while (!feof($fp))
{
	if (++$n % 100 == 0)
	{
		echo $n, ' ';
	}

	$line = fgets($fp, 1024);

	if (!$pos = strpos($line, ';'))
	{
		continue;
	}

	$hex_tested = $hex_expected = substr($line, 0, $pos);

	if (isset($tested_chars[$hex_tested]))
	{
		continue;
	}

	$utf_expected = hex_to_utf($hex_expected);

	if ($utf_expected >= UTF8_SURROGATE_FIRST
	 && $utf_expected <= UTF8_SURROGATE_LAST)
	{
		/**
		* Surrogates are illegal on their own, we expect the normalizer
		* to return a replacement char
		*/
		$utf_expected = UTF8_REPLACEMENT;
		$hex_expected = utf_to_hexseq($utf_expected);
	}

	foreach (array('nfc', 'nfkc', 'nfd', 'nfkd') as $form)
	{
		$utf_result = $utf_expected;
		utf_normalizer::$form($utf_result);
		$hex_result = utf_to_hexseq($utf_result);
//		echo "$form($utf_expected) == $utf_result\n";

		if (strcmp($utf_expected, $utf_result))
		{
			$failed = 1;

			echo "\nFAILED $hex_expected == $form($hex_tested) ($hex_expected != $hex_result)";
		}
	}

	if ($failed)
	{
		die("\n\nFailed at line $n\n");
	}
}
fclose($fp);

die("\n\nALL TESTS PASSED SUCCESSFULLY\n");

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
* Convert a UTF string to a sequence of codepoints in hexadecimal
*
* @param	string	$utf	UTF string
* @return	integer			Unicode codepoints in hex
*/
function utf_to_hexseq($str)
{
	$pos = 0;
	$len = strlen($str);
	$ret = array();

	while ($pos < $len)
	{
		$c = $str[$pos];
		switch ($c & "\xF0")
		{
			case "\xC0":
			case "\xD0":
				$utf_char = substr($str, $pos, 2);
				$pos += 2;
				break;

			case "\xE0":
				$utf_char = substr($str, $pos, 3);
				$pos += 3;
				break;

			case "\xF0":
				$utf_char = substr($str, $pos, 4);
				$pos += 4;
				break;

			default:
				$utf_char = $c;
				++$pos;
		}

		$hex = dechex(utf_to_cp($utf_char));

		if (!isset($hex[3]))
		{
			$hex = substr('000' . $hex, -4);
		}

		$ret[] = $hex;
	}

	return strtr(implode(' ', $ret), 'abcdef', 'ABCDEF');
}

/**
* Convert a UTF-8 char to its codepoint
*
* @param	string	$utf_char	UTF-8 char
* @return	integer				Unicode codepoint
*/
function utf_to_cp($utf_char)
{
	switch (strlen($utf_char))
	{
		case 1:
			return ord($utf_char);

		case 2:
			return ((ord($utf_char[0]) & 0x1F) << 6) | (ord($utf_char[1]) & 0x3F);

		case 3:
			return ((ord($utf_char[0]) & 0x0F) << 12) | ((ord($utf_char[1]) & 0x3F) << 6) | (ord($utf_char[2]) & 0x3F);

		case 4:
			return ((ord($utf_char[0]) & 0x07) << 18) | ((ord($utf_char[1]) & 0x3F) << 12) | ((ord($utf_char[2]) & 0x3F) << 6) | (ord($utf_char[3]) & 0x3F);

		default:
			die('UTF-8 chars can only be 1-4 bytes long');
	}
}

/**
* Return a UTF string formed from a sequence of codepoints in hexadecimal
*
* @param	string	$seq		Sequence of codepoints, separated with a space
* @return	string				UTF-8 string
*/
function hexseq_to_utf($seq)
{
	return implode('', array_map('hex_to_utf', explode(' ', $seq)));
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