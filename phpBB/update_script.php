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
		case '.0.0':
		case '.1.0 [20020402]':
			echo 'Updating from [20020402] :: ';
			flush();

			$sql[] = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value) 
				VALUES ('session_gc', '3600')";
			$sql[] = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value) 
				VALUES ('session_last_gc', '0')";

			echo '<span style="color:green">DONE</span><br /><br />';
			break;

		case '.1.0 [20020420]':
			switch ( SQL_LAYER )
			{
				case 'mysql':
				case 'mysql4':
					$sql[] = "CREATE TABLE " . $table_prefix . "forums_watch (forum_id smallint(5) UNSIGNED NOT NULL DEFAULT '0', user_id mediumint(8) NOT NULL DEFAULT '0', notify_status tinyint(1) NOT NULL default '0', KEY forum_id (forum_id), KEY user_id (user_id), KEY notify_status (notify_status))";
					break;

				case 'mssql-odbc':
				case 'mssql':
					$sql[] = "CREATE TABLE [" . $table_prefix . "forums_watch] ([forum_id] [int] NOT NULL , [user_id] [int] NOT NULL , [notify_status] [smallint] NOT NULL ) ON [PRIMARY]";
					$sql[] = "CREATE  INDEX [IX_" . $table_prefix . "forums_watch] ON [" . $table_prefix . "forums_watch]([forum_id], [user_id]) ON [PRIMARY]";
					break;

				case 'postgresql':
					$sql[] = "CREATE TABLE " . $table_prefix . "forums_watch (forum_id int4, user_id int4, notify_status int2 NOT NULL default '0')";
					$sql[] = "CREATE  INDEX forum_id_" . $table_prefix . "forums_watch_index ON " . $table_prefix . "forums_watch (forum_id)";
					$sql[] = "CREATE  INDEX user_id_" . $table_prefix . "forums_watch_index ON " . $table_prefix . "forums_watch (user_id)";
				default:
					die("No DB LAYER found!");
					break;
			}
			break;
		case '.1.0 [20020421]':
			$user_data_sql = "SELECT COUNT(user_id) AS total_users, MAX(user_id) AS newest_user_id FROM " . USERS_TABLE . " WHERE user_id <> " . ANONYMOUS;
			if($result = $db->sql_query($user_data_sql))
			{
				$row = $db->sql_fetchrow($result);
				$user_count = $row['total_users'];
				$newest_user_id = $row['newest_user_id'];
				
				$username_sql = "SELECT username FROM " . USERS_TABLE . " WHERE user_id = $newest_user_id";
				if(!$result = $db->sql_query($username_sql))
				{
					die('Could not get username to update to [20020430]');
				}
				$row = $db->sql_fetchrow($result);
				$newest_username = $row['username'];
			}
			else
			{
				die('Could not get user count for update to [20020430]');
			}
				
			$sql[] = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
				VALUES ('newest_user_id', $newest_user_id)";
			$sql[] = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
				VALUES ('newest_username', '$newest_username')";
			$sql[] = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
				VALUES ('num_users', $user_count)";
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
				die("Couldn't run update >> " . $sql[$i]);
			}
		}
	}
}

$sql = "UPDATE " . CONFIG_TABLE . " 
	SET config_value = '.1.0 [20020430]' 
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
