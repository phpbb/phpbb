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
	$sql = array();
	switch ( $row['config_value'] )
	{
		case '.1.0 [20020402]':
			echo 'Updating from [20020402] :: ';
			flush();

			$sql[] = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value) 
				VALUES ('session_gc', '3600')";
			$sql[] = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value) 
				VALUES ('session_last_gc', '0')";

			echo '<span style="color:green">DONE</span><br /><br />';
			break;
		default;
			echo 'No updates made<br /><br />';
	}

	if ( count($sql) )
	{
		for($i = 0; $i < count($sql); $i++)
		{
			if ( !($result = $db->sql_query($sql[$i])) )
			{
				die("Couldn't run update >> " . $sql);
			}
		}
	}
}

$sql = "UPDATE " . CONFIG_TABLE . " 
	SET config_value = '.1.0 [20020420]' 
	WHERE config_name = 'version'";
if ( !($result = $db->sql_query($sql)) )
{
	die("Couldn't update version info");
}

echo "\n<br />\n<b>COMPLETE!</b><br />\n";
echo "\n<p>Don't forget to delete this file!</p>\n";
?>
</body>
</html>
