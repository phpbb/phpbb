<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (php_sapi_name() != 'cli')
{
	die("This program must be run from the command line.\n");
}

set_time_limit(0);

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

echo "Checking for required files\n";
download('http://www.unicode.org/Public/UNIDATA/CompositionExclusions.txt');
download('http://www.unicode.org/Public/UNIDATA/DerivedNormalizationProps.txt');
download('http://www.unicode.org/Public/UNIDATA/UnicodeData.txt');
echo "\n";

require_once($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);
$file_contents = array();

/**
* Generate some Hangul/Jamo stuff
*/
echo "\nGenerating Hangul and Jamo tables\n";
for ($i = 0; $i < UNICODE_HANGUL_LCOUNT; ++$i)
{
	$utf_char = cp_to_utf(UNICODE_HANGUL_LBASE + $i);
	$file_contents['utf_normalizer_common']['utfJamoIndex'][$utf_char] = $i * UNICODE_HANGUL_VCOUNT * UNICODE_HANGUL_TCOUNT + UNICODE_HANGUL_SBASE;
	$file_contents['utf_normalizer_common']['utfJamoType'][$utf_char] = UNICODE_JAMO_L;
}

for ($i = 0; $i < UNICODE_HANGUL_VCOUNT; ++$i)
{
	$utf_char = cp_to_utf(UNICODE_HANGUL_VBASE + $i);
	$file_contents['utf_normalizer_common']['utfJamoIndex'][$utf_char] = $i * UNICODE_HANGUL_TCOUNT;
	$file_contents['utf_normalizer_common']['utfJamoType'][$utf_char] = UNICODE_JAMO_V;
}

for ($i = 0; $i < UNICODE_HANGUL_TCOUNT; ++$i)
{
	$utf_char = cp_to_utf(UNICODE_HANGUL_TBASE + $i);
	$file_contents['utf_normalizer_common']['utfJamoIndex'][$utf_char] = $i;
	$file_contents['utf_normalizer_common']['utfJamoType'][$utf_char] = UNICODE_JAMO_T;
}

/**
* Load the CompositionExclusions table
*/
echo "Loading CompositionExclusion\n";
$fp = fopen('CompositionExclusions.txt', 'rt');

$exclude = array();
while (!feof($fp))
{
	$line = fgets($fp, 1024);

	if (!strpos(' 0123456789ABCDEFabcdef', $line[0]))
	{
		continue;
	}

	$cp = strtok($line, ' ');

	if ($pos = strpos($cp, '..'))
	{
		$start = hexdec(substr($cp, 0, $pos));
		$end = hexdec(substr($cp, $pos + 2));

		for ($i = $start; $i < $end; ++$i)
		{
			$exclude[$i] = 1;
		}
	}
	else
	{
		$exclude[hexdec($cp)] = 1;
	}
}
fclose($fp);

/**
* Load QuickCheck tables
*/
echo "Generating QuickCheck tables\n";
$fp = fopen('DerivedNormalizationProps.txt', 'rt');

while (!feof($fp))
{
	$line = fgets($fp, 1024);

	if (!strpos(' 0123456789ABCDEFabcdef', $line[0]))
	{
		continue;
	}

	$p = array_map('trim', explode(';', strtok($line, '#')));

	/**
	* Capture only NFC_QC, NFKC_QC
	*/
	if (!preg_match('#^NFK?C_QC$#', $p[1]))
	{
		continue;
	}

	if ($pos = strpos($p[0], '..'))
	{
		$start = hexdec(substr($p[0], 0, $pos));
		$end = hexdec(substr($p[0], $pos + 2));
	}
	else
	{
		$start = $end = hexdec($p[0]);
	}

	if ($start >= UTF8_HANGUL_FIRST && $end <= UTF8_HANGUL_LAST)
	{
		/**
		* We do not store Hangul syllables in the array
		*/
		continue;
	}

	if ($p[2] == 'M')
	{
		$val = UNICODE_QC_MAYBE;
	}
	else
	{
		$val = UNICODE_QC_NO;
	}

	if ($p[1] == 'NFKC_QC')
	{
		$file = 'utf_nfkc_qc';
	}
	else
	{
		$file = 'utf_nfc_qc';
	}

	for ($i = $start; $i <= $end; ++$i)
	{
		$file_contents[$file]['utfCheck' . substr($p[1], 0, -3)][cp_to_utf($i)] = $val;
	}
}
fclose($fp);

/**
* Do mappings
*/
echo "Loading Unicode decomposition mappings\n";
$fp = fopen($phpbb_root_path . 'develop/UnicodeData.txt', 'rt');

$map = array();
while (!feof($fp))
{
	$p = explode(';', fgets($fp, 1024));
	$cp = hexdec($p[0]);

	if (!empty($p[3]))
	{
		/**
		* Store combining class > 0
		*/
		$file_contents['utf_normalizer_common']['utfCombiningClass'][cp_to_utf($cp)] = (int) $p[3];
	}

	if (!isset($p[5]) || !preg_match_all('#[0-9A-F]+#', strip_tags($p[5]), $m))
	{
		continue;
	}

	if (strpos($p[5], '>'))
	{
		$map['NFKD'][$cp] = implode(' ', array_map('hexdec', $m[0]));
	}
	else
	{
		$map['NFD'][$cp] = $map['NFKD'][$cp] = implode(' ', array_map('hexdec', $m[0]));
	}
}
fclose($fp);

/**
* Build the canonical composition table
*/
echo "Generating the Canonical Composition table\n";
foreach ($map['NFD'] as $cp => $decomp_seq)
{
	if (!strpos($decomp_seq, ' ') || isset($exclude[$cp]))
	{
		/**
		* Singletons are excluded from canonical composition
		*/
		continue;
	}

	$utf_seq = implode('', array_map('cp_to_utf', explode(' ', $decomp_seq)));

	if (!isset($file_contents['utf_canonical_comp']['utfCanonicalComp'][$utf_seq]))
	{
		$file_contents['utf_canonical_comp']['utfCanonicalComp'][$utf_seq] = cp_to_utf($cp);
	}
}

/**
* Decompose the NF[K]D mappings recursively and prepare the file contents
*/
echo "Generating the Canonical and Compatibility Decomposition tables\n\n";
foreach ($map as $type => $decomp_map)
{
	foreach ($decomp_map as $cp => $decomp_seq)
	{
		$decomp_map[$cp] = decompose($decomp_map, $decomp_seq);
	}
	unset($decomp_seq);

	if ($type == 'NFKD')
	{
		$file = 'utf_compatibility_decomp';
		$var = 'utfCompatibilityDecomp';
	}
	else
	{
		$file = 'utf_canonical_decomp';
		$var = 'utfCanonicalDecomp';
	}

	/**
	* Generate the corresponding file
	*/
	foreach ($decomp_map as $cp => $decomp_seq)
	{
		$file_contents[$file][$var][cp_to_utf($cp)] = implode('', array_map('cp_to_utf', explode(' ', $decomp_seq)));
	}
}

/**
* Generate and/or alter the files
*/
foreach ($file_contents as $file => $contents)
{
	$php = '';
	foreach ($contents as $var => $val)
	{
		$php .= '$GLOBALS[' . my_var_export($var) . ']=' . my_var_export($val) . ";\n";
	}

	/**
	* Generate a new file (overwrite if applicable
	*/
	echo "Writing to $file.$phpEx\n";

	if (!$fp = fopen($phpbb_root_path . 'includes/utf/data/' . $file . '.' . $phpEx, 'wb'))
	{
		trigger_error('Cannot open ' . $file . ' for write');
	}

	fwrite($fp, '<?php
/**
* @package phpBB3
*/
' . $php);
	fclose($fp);
}

die("\nAll done!\n");


////////////////////////////////////////////////////////////////////////////////
//                             Internal functions                             //
////////////////////////////////////////////////////////////////////////////////

/**
* Decompose a sequence recusively
*
* @param	array	$decomp_map	Decomposition mapping, passed by reference
* @param	string	$decomp_seq	Decomposition sequence as decimal codepoints separated with a space
* @return	string				Decomposition sequence, fully decomposed
*/
function decompose(&$decomp_map, $decomp_seq)
{
	$ret = array();
	foreach (explode(' ', $decomp_seq) as $cp)
	{
		if (isset($decomp_map[$cp]))
		{
			$ret[] = decompose($decomp_map, $decomp_map[$cp]);
		}
	else
		{
			$ret[] = $cp;
		}
	}

	return implode(' ', $ret);
}

/**
* Convert a codepoint to a UTF char
*
* @param	integer	$cp	Unicode codepoint
* @return	string		UTF string
*/
function cp_to_utf($cp)
{
	if ($cp > 0xFFFF)
	{
		return chr(0xF0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3F)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	}
	elseif ($cp > 0x7FF)
	{
		return chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	}
	elseif ($cp > 0x7F)
	{
		return chr(0xC0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3F));
	}
	else
	{
		return chr($cp);
	}
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
	elseif (is_string($var))
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
* @return	void
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