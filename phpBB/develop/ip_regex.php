<?php
$dec_octet = '(?:\d?\d|1\d\d|2[0-4]\d|25[0-5])';
$h16 = '[\dA-F]{1,4}';
$ipv4 = "(?:$dec_octet\.){3}$dec_octet";
$ls32 = "(?:$h16:$h16|$ipv4)";

$ipv6_construct = array(
	array(false,	'',		'{6}',	$ls32),
	array(false,	'::',	'{5}',	$ls32),
	array('',		':',	'{4}',	$ls32),
	array('{1,2}',	':',	'{3}',	$ls32),
	array('{1,3}',	':',	'{2}',	$ls32),
	array('{1,4}',	':',	'',		$ls32),
	array('{1,5}',	':',	false,	$ls32),
	array('{1,6}',	':',	false,	$h16),
	array('{1,7}',	':',	false,	'')
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

echo 'IPv4: ' . $ipv4 . "<br />\nIPv6: " . $ipv6;
?>