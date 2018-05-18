<?php

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

//
// Do not change anything below this line.
//
$phpbb_root_path = "../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

echo "Inserting new config vars<br /><br />\n";

echo "server_name :: ";
flush();
$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('server_name', 'www.myserver.tld')";
if( !$db->sql_query($sql) )
{  
	print "Failed inserting server_name config ... probably exists already<br />\n";
}
else
{
	echo "DONE<br />\n";
}

echo "script_path :: ";
flush();
$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('script_path', '/phpBB2/')";
if( !$db->sql_query($sql) )
{  
	print "Failed inserting script_path config ... probably exists already<br />\n";
}
else
{
	echo "DONE<br />\n";
}

echo "server_port :: ";
flush();
$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('server_port', '80')";
if( !$db->sql_query($sql) )
{  
	print "Failed inserting server_port config ... probably exists already<br />\n";
}
else
{
	echo "DONE<br />\n";
}

$db->sql_close();

echo "<br />COMPLETE<br />\n";

?>
