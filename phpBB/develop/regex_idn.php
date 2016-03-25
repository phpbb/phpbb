<?php
//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

// IP regular expressions

$dec_octet = '(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])';
$h16 = '[\dA-F]{1,4}';
$ipv4 = "(?:$dec_octet\.){3}$dec_octet";
$ls32 = "(?:$h16:$h16|$ipv4)";

$ipv6_construct = array(
	array(false,	'',		'{6}',	$ls32),
	array(false,	'::',	'{0,5}', "(?:$h16(?::$h16)?|$ipv4)"),
	array('',		':',	'{4}',	$ls32),
	array('{1,2}',	':',	'{3}',	$ls32),
	array('{1,3}',	':',	'{2}',	$ls32),
	array('{1,4}',	':',	'',		$ls32),
	array('{1,5}',	':',	false,	$ls32),
	array('{1,6}',	':',	false,	$h16),
	array('{1,7}',	':',	false,	''),
	array(false, '::', false, '')
);

$ipv6 = '(?:';
foreach ($ipv6_construct as $ip_type)
{
	$ipv6 .= '(?:';
	if ($ip_type[0] !== false)
	{
		$ipv6 .= "(?:$h16:)" . $ip_type[0];
	}
	$ipv6 .= $ip_type[1];
	if ($ip_type[2] !== false)
	{
		$ipv6 .= "(?:$h16:)" . $ip_type[2];
	}
	$ipv6 .= $ip_type[3] . ')|';
}
$ipv6 = substr($ipv6, 0, -1) . ')';

echo 'IPv4: ' . $ipv4 . "<br /><br />\n\nIPv6: " . $ipv6 . "<br /><br />\n\n";

// URL regular expressions

/* IDN2008 characters derivation
** http://unicode.org/faq/idn.html#33	- IDN FAQ: derivation of valid characters in terms of Unicode properties
** http://unicode.org/reports/tr46/		- Unicode Technical Standard #46. Unicode IDNA Compatibility Processing
** http://www.unicode.org/Public/UNIDATA/DerivedNormalizationProps.txt	- Unicode Character Database
*/
/*
** Remove Control Characters and Whitespace (as in IDNA2003)
*/
$no_cc = '\p{C}\p{Z}';
/*
** Remove Symbols, Punctuation, non-decimal Numbers, and Enclosing Marks
*/
$no_symbol = '\p{S}\p{P}\p{Nl}\p{No}\p{Me}';
/*
** Remove characters used for archaic Hangul (Korean) - \p{HST=L} and \p{HST=V}
** as per http://unicode.org/Public/UNIDATA/HangulSyllableType.txt
*/
$no_hangul = '\x{1100}-\x{115F}\x{A960}-\x{A97C}\x{1160}-\x{11A7}\x{D7B0}-\x{D7C6}';
/*
** Remove three blocks of technical or archaic symbols.
*/
$no_cdm = '\x{20D0}-\x{20FF}';						// \p{block=Combining_Diacritical_Marks_For_Symbols}
$no_musical = '\x{1D100}-\x{1D1FF}';					// \p{block=Musical_Symbols}
$no_ancient_greek_musical = '\x{1D200}-\x{1D24F}';	// \p{block=Ancient_Greek_Musical_Notation}	
/* Remove certain exceptions:
** U+0640 ARABIC TATWEEL
** U+07FA NKO LAJANYALAN
** U+302E HANGUL SINGLE DOT TONE MARK
** U+302F HANGUL DOUBLE DOT TONE MARK
** U+3031 VERTICAL KANA REPEAT MARK
** U+3032 VERTICAL KANA REPEAT WITH VOICED SOUND MARK
** ..
** U+3035 VERTICAL KANA REPEAT MARK LOWER HALF
** U+303B VERTICAL IDEOGRAPHIC ITERATION MARK
*/
$no_certain_exceptions = '\x{0640}\x{07FA}\x{302E}\x{302F}\x{3031}-\x{3035}\x{303B}';
/* Add certain exceptions:
** U+00B7 MIDDLE DOT
** U+0375 GREEK LOWER NUMERAL SIGN
** U+05F3 HEBREW PUNCTUATION GERESH
** U+05F4 HEBREW PUNCTUATION GERSHAYIM
** U+30FB KATAKANA MIDDLE DOT
** U+002D HYPHEN-MINUS
** U+06FD ARABIC SIGN SINDHI AMPERSAND
** U+06FE ARABIC SIGN SINDHI POSTPOSITION MEN
** U+0F0B TIBETAN MARK INTERSYLLABIC TSHEG
** U+3007 IDEOGRAPHIC NUMBER ZERO
*/
$add_certain_exceptions = '\x{00B7}\x{0375}\x{05F3}\x{05F4}\x{30FB}\x{002D}\x{06FD}\x{06FE}\x{0F0B}\x{3007}';
/* Add special exceptions (Deviations):
** U+00DF LATIN SMALL LETTER SHARP S
** U+03C2 GREEK SMALL LETTER FINAL SIGMA
** U+200C ZERO WIDTH NON-JOINER
** U+200D ZERO WIDTH JOINER
*/
$add_deviations = '\x{00DF}\x{03C2}\x{200C}\x{200D}';

// Concatenate remove/add regexes respectively
$remove_chars = "$no_cc$no_symbol$no_hangul$no_cdm$no_musical$no_ancient_greek_musical$no_certain_exceptions";
$add_chars = "$add_certain_exceptions$add_deviations";

// Initialize inline mode
$inline = false;

do
{
	$inline = !$inline;

	$pct_encoded = "%[\dA-F]{2}";
	$unreserved = "$add_chars\pL0-9\-._~";
	$sub_delims = ($inline) ? '!$&\'(*+,;=' : '!$&\'()*+,;=';
	$scheme = ($inline) ? '[a-z][a-z\d+]*': '[a-z][a-z\d+\-.]*' ; // avoid automatic parsing of "word" in "last word.http://..."
	$pchar = "(?:[^$remove_chars]*[$unreserved$sub_delims:@|]+|$pct_encoded)"; // rfc: no "|"

	$reg_name = "(?:[^$remove_chars]*[$unreserved$sub_delims:@|]+|$pct_encoded)+"; // rfc: * instead of + and no "|" and no "@" and no ":" (included instead of userinfo)
	//$userinfo = "(?:(?:[$unreserved$sub_delims:]+|$pct_encoded))*";
	$ipv4_simple = '[0-9.]+';
	$ipv6_simple = '\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\]';
	$host = "(?:$reg_name|$ipv4_simple|$ipv6_simple)";
	$port = '\d*';
	//$authority = "(?:$userinfo@)?$host(?::$port)?";
	$authority = "$host(?::$port)?";
	$segment = "$pchar*";
	$path_abempty = "(?:/$segment)*";
	$hier_part = "/{2}$authority$path_abempty";
	$query = "(?:[^$remove_chars]*[$unreserved$sub_delims:@/?|]+|$pct_encoded)*"; // pchar | "/" | "?", rfc: no "|"
	$fragment = $query;

	$url =  "$scheme:$hier_part(?:\?$query)?(?:\#$fragment)?";
	echo (($inline) ? 'URL inline: ' : 'URL: ') . $url . "<br /><br />\n\n";

	// no scheme, shortened authority, but host has to start with www.
	$www_url =  "www\.$reg_name(?::$port)?$path_abempty(?:\?$query)?(?:\#$fragment)?";
	echo (($inline) ? 'www.URL_inline: ' : 'www.URL: ') . $www_url . "<br /><br />\n\n";

	// no schema and no authority
	$relative_url = "$segment$path_abempty(?:\?$query)?(?:\#$fragment)?";
	echo (($inline) ? 'relative URL inline: ' : 'relative URL: ') . $relative_url . "<br /><br />\n\n";
}
while ($inline);
