<?

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

$sql = "SELECT *
	FROM " . CONFIG_TABLE;
if(!$result = $db->sql_query($sql))
{
	message_die(CRITICAL_ERROR, "Could not query config information", "", __LINE__, __FILE__, $sql);
}
else
{
	$board_config = $db->sql_fetchrow($result);
}

$newconfigtable = $table_prefix . "newconfig";

$sql = "SELECT config_name, config_value FROM ". CONFIG_TABLE;
if( $result = $db->sql_query($sql) )
{
	die("Don't run this script twice!<br>\n");
}

$sql = "	CREATE TABLE $newconfigtable (
				config_name varchar(255) NOT NULL,
				config_value varchar(255) NOT NULL,
			   PRIMARY KEY (config_name)
			)";
print "Creating temporary table: $newconfigtable<p>\n";
if( !$result = $db->sql_query($sql) )
{
	print("Couldn't create new config table<br>\n");
}

$error = 0;
while (list($name, $value) = each($board_config))
{
	if(is_int($name))
	{
		// Skip numeric array elements (we only want the associative array)
		continue;
	}

	// Rename sys_template
	if ($name == 'sys_template')
	{
		$name = 'board_template';
	}
	// Rename system_timezone
	if ($name == 'system_timezone')
	{
		$name = 'board_timezone';
	}
	print "$name = $value<br>\n";
	$value = addslashes($value);
	$sql = "INSERT INTO $newconfigtable (config_name, config_value) VALUES ('$name', '$value')";
	if( !$result = $db->sql_query($sql) )
	{
		print("Couldn't insert '$name' into new config table");
		$error = 1;
	}
}

if ($error != 1)
{
	print "Dropping old table<p>\n";
	$sql = "DROP TABLE ". CONFIG_TABLE;
	if( !$result = $db->sql_query($sql) )
	{
		die("Couldn't drop old table");
	}
	print "Renaming $newconfigtable to ".CONFIG_TABLE."<p>\n";
	$sql = "ALTER TABLE $newconfigtable RENAME ".CONFIG_TABLE;
	if( !$result = $db->sql_query($sql) )
	{
		die("Couldn't rename new config table");
	}
	print "Renaming ".SESSIONS_TABLE." to ".$table_prefix."sessions<br>\n";
	$sql = "ALTER TABLE ".SESSIONS_TABLE." RENAME ".$table_prefix."sessions";
	if( !$result = $db->sql_query($sql) )
	{
		die("Couldn't rename session table");
	}
	
}

$db->sql_close();

	echo "<BR><BR>COMPLETE<BR>";

?>
