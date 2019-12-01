<?php
//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

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
