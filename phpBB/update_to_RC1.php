<html>
<body>
<?php

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

$sql = array();

switch(SQL_LAYER)
{
	case 'mysql':
	case 'mysql4':
		$sql[] = "ALTER TABLE " . USERS_TABLE . " 
			ADD COLUMN user_session_time int(11) DEFAULT '0' NOT NULL, 
			ADD COLUMN user_session_page smallint(5) DEFAULT '0' NOT NULL, 
			ADD INDEX (user_session_time)";
		$sql[] = "ALTER TABLE " . SEARCH_TABLE . " 
			MODIFY search_id int(11) NOT NULL";
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . " 
			MODIFY topic_moved_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL";
		break;

	case 'postgresql':
		$sql[] = "ALTER TABLE " . USERS_TABLE . " 
			ADD COLUMN user_session_time int4";
		$sql[] = "ALTER TABLE " . USERS_TABLE . " 
			ADD COLUMN user_session_page int2";
		$sql[] = "ALTER TABLE " . USERS_TABLE . " 
			ALTER COLUMN user_session_time SET DEFAULT '0'";
		$sql[] = "ALTER TABLE " . USERS_TABLE . " 
			ALTER COLUMN user_session_page SET DEFAULT '0'";
		$sql[] = "CREATE INDEX user_session_time_" . $table_prefix . "users_index 
			ON " . USERS_TABLE . " (user_session_time)";
		break;

	case 'mssql-odbc':
	case 'mssql':
		$sql[] = "ALTER TABLE " . USERS_TABLE . " ADD 
			user_session_time int NOT NULL, 
			user_session_page smallint NOT NULL, 
			CONSTRAINT [DF_" . $table_prefix . "users_user_session_time] DEFAULT (0) FOR [user_session_time],
			CONSTRAINT [DF_" . $table_prefix . "users_user_session_page] DEFAULT (0) FOR [user_session_page]";
		$sql[] = "CREATE INDEX [IX_" . $table_prefix . "users] 
			ON [" . USERS_TABLE . "]([user_session_time]) ON [PRIMARY]";
		break;

	case 'msaccess':
		$sql[] = "ALTER TABLE " . USERS_TABLE . " ADD 
			user_session_time int NOT NULL, 
			user_session_page smallint NOT NULL";
		$sql[] = "CREATE INDEX user_session_time 
			ON " . USERS_TABLE . " (user_session_time)";
		break;

	default:
		die("No DB LAYER found!");
		break;
}

	$errored = false;
	for($i = 0; $i < count($sql); $i++)
	{
		echo "Running >>> " . $sql[$i];

		$result = $db->sql_query($sql[$i]);

		if( !$result )
		{
			$errored = true;
			$error = $db->sql_error();
			echo " :: <b>FAILED</b> <u>( " . $error['message'] . " )</u><br /><br />\n\n";
		}
		else
		{
			echo " :: <b>COMPLETED</b><br /><br />\n\n";
		}
	}

	if( $errored )
	{
		echo "\n<br /><br />Errors occured! Please check and correct issues as required<br />\n";
	}
	else
	{
		echo "\n<br /><br />\nCOMPLETE! Please delete this file before continuing!<br />\n";
	}

?>
</body>
</html>