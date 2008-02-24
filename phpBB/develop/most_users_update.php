<html>
<body>
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

	echo "\n<br >\n" . $sql = "INSERT INTO " . CONFIG_TABLE . "
		(config_name, config_value) VALUES ('record_online_users', '1')";
	if( !$result = $db->sql_query($sql) )
	{

		message_die(GENERAL_ERROR, "Couldn't insert config key 'record_online_users'", "", __LINE__, __FILE__, $sql);
	}

	echo "\n<br >\n" . $sql = "INSERT INTO " . CONFIG_TABLE . "
		(config_name, config_value) VALUES ('record_online_date', '".time()."')";
	if( !$result = $db->sql_query($sql) )
	{  
		message_die(GENERAL_ERROR, "Couldn't insert config key 'record_online_date'", "", __LINE__, __FILE__, $sql);
	}

	echo "\n<br />\nCOMPLETE";

?>
</body>
</html>
