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

echo 'IPv4: ' . $ipv4 . "<br />\nIPv6: " . $ipv6 . "<br />\n";

// URL regular expressions

$pct_encoded = "%[\dA-F]{2}";
$unreserved = 'a-z0-9\-._~';
$sub_delims = '!$&\'()*+,;=';
$pchar = "(?:[$unreserved$sub_delims:@|]+|$pct_encoded)"; // rfc: no "|"

$scheme = '[a-z][a-z\d+\-.]*';
$reg_name = "(?:[$unreserved$sub_delims:@|]+|$pct_encoded)+"; // rfc: * instead of + and no "|" and no "@" and no ":" (included instead of userinfo)
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
$query = "(?:[$unreserved$sub_delims:@/?|]+|$pct_encoded)*"; // pchar | "/" | "?", rfc: no "|"
$fragment = $query;

$url =  "$scheme:$hier_part(?:\?$query)?(?:\#$fragment)?";
echo 'URL: ' . $url . "<br />\n";

// no scheme, shortened authority, but host has to start with www.
$www_url =  "www\.$reg_name(?::$port)?$path_abempty(?:\?$query)?(?:\#$fragment)?";
echo 'www.URL: ' . $www_url . "<br />\n";

// no schema and no authority
$relative_url = "$segment$path_abempty(?:\?$query)?(?:\#$fragment)?";
echo 'relative URL: ' . $relative_url . "<br />\n";

?>