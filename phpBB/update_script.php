<html>
<body>
<?php

define('IN_PHPBB', 1);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

$sql = "SELECT config_value  
	FROM " . CONFIG_TABLE . " 
	WHERE config_name = 'version'";
if ( !($result = $db->sql_query($sql)) )
{
	die("Couldn't obtain version info");
}

if ( $row = $db->sql_fetchrow($result) )
{
	if ( $row['config_value'] != '.1.0 [20020402]' )
	{
		$sql = "UPDATE " . CONFIG_TABLE . " 
			SET config_value = '.1.0 [20020402]' 
			WHERE config_name = 'version'";
		if ( !($result = $db->sql_query($sql)) )
		{
			die("Couldn't update version info");
		}

		die("UPDATING COMPLETE");
	}
}


echo "\n<br />\n<b>COMPLETE!</b><br />\n";
echo "\n<p>Don't forget to delete this file!</p>\n";
?>
</body>
</html>
