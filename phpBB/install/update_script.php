<html>
<body>
<?php

// --------------------------------
//
function decode_ip($int_ip)
{
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}
//
// --------------------------------

define('IN_PHPBB', 1);
$phpbb_root_path='./../';
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
		case '.1.0 [20020430]':
			switch ( SQL_LAYER )
			{
				case 'mysql':
				case 'mysql4':
					$sql[] = "ALTER TABLE " . BANLIST_TABLE . " 
						MODIFY ban_email char(60) NULL, 
						MODIFY ban_ip char(40) NOT NULL";
					$sql[] = "ALTER TABLE " . DISALLOW_TABLE . " 
						MODIFY disallow_username char(30) NOT NULL";
					$sql[] = "ALTER TABLE " . POSTS_TABLE . " 
						MODIFY poster_ip char(40) NOT NULL, 
						MODIFY post_username char(30) NULL";
					$sql[] = "ALTER TABLE " . PRIVMSGS_TABLE . " 
						MODIFY privmsgs_subject char(60) NOT NULL, 
						MODIFY privmsgs_ip char(40) NOT NULL";
					$sql[] = "ALTER TABLE " . SESSIONS_TABLE . " 
						MODIFY session_ip char(40) NOT NULL";
					$sql[] = "ALTER TABLE " . USERS_TABLE . " 
						ADD COLUMN user_ip char(40) NOT NULL";
					$sql[] = "ALTER TABLE " . VOTE_USERS_TABLE . " 
						MODIFY COLUMN vote_user_ip char(40) NOT NULL";
					break;

				case 'mssql-odbc':
				case 'mssql':
					$sql[] = "";
					break;

				case 'postgresql':
					$sql[] = "";
				default:
					die("No DB LAYER found!");
					break;
			}

			break;
		default;
			echo 'No updates made<br /><br />';
	}

	if ( count($sql) )
	{
		for($i = 0; $i < count($sql); $i++)
		{
			if ( !$db->sql_query($sql[$i]) )
			{
				die("Couldn't run update >> " . $sql[$i]);
			}
		}
	}

	$sql_update = array();

	switch ( $row['config_value'] )
	{
		case '.1.0 [20020430]':

			$sql = "SELECT ban_id, ban_ip 
				FROM " . BANLIST_TABLE;
			if ( !($result = $db->sql_query($sql)) )
			{
				die("Couldn't select data >> " . $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					$ban_ip = str_replace('255', '256', decode_ip($row['ban_ip']));
					$sql_update[] = "UPDATE " . BANLIST_TABLE . " 
						SET ban_ip = '$ban_ip'
						WHERE ban_id = " . $row['ban_id'];
				}
				while ( $row = $db->sql_fetchrow($result) );
			}

			$sql = "SELECT post_id, poster_ip 
				FROM " . POSTS_TABLE;
			if ( !($result = $db->sql_query($sql)) )
			{
				die("Couldn't select data >> " . $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					$sql_update[] = "UPDATE " . POSTS_TABLE . " 
						SET poster_ip = '" . decode_ip($row['poster_ip']) . "'
						WHERE post_id = " . $row['post_id'];
				}
				while ( $row = $db->sql_fetchrow($result) );
			}

			$sql = "SELECT privmsgs_id, privmsgs_ip 
				FROM " . PRIVMSGS_TABLE;
			if ( !($result = $db->sql_query($sql)) )
			{
				die("Couldn't select data >> " . $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					$sql_update[] = "UPDATE " . PRIVMSGS_TABLE . " 
						SET privmsgs_ip = '" . decode_ip($row['privmsgs_ip']) . "'
						WHERE privmsgs_id = " . $row['privmsgs_id'];
				}
				while ( $row = $db->sql_fetchrow($result) );
			}

			$sql = "SELECT session_id, session_ip 
				FROM " . SESSIONS_TABLE;
			if ( !($result = $db->sql_query($sql)) )
			{
				die("Couldn't select data >> " . $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					$sql_update[] = "UPDATE " . SESSIONS_TABLE . " 
						SET session_ip = '" . decode_ip($row['session_ip']) . "'
						WHERE session_id = '" . $row['session_id'] . "'";
				}
				while ( $row = $db->sql_fetchrow($result) );
			}

			$sql = "SELECT vote_id, vote_user_id, vote_user_ip 
				FROM " . VOTE_USERS_TABLE;
			if ( !($result = $db->sql_query($sql)) )
			{
				die("Couldn't select data >> " . $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					$sql_update[] = "UPDATE " . VOTE_USERS_TABLE . " 
						SET vote_user_ip = '" . decode_ip($row['vote_user_ip']) . "'
						WHERE vote_id = " . $row['vote_id'] . " 
							AND vote_user_id = " . $row['vote_user_id'];
				}
				while ( $row = $db->sql_fetchrow($result) );
			}
			break;
	}

	if ( count($sql_update) )
	{
		echo 'Updating existing data :: ';
		flush();

		for($i = 0; $i < count($sql_update); $i++)
		{
			if ( !$db->sql_query($sql_update[$i]) )
			{
				die("Couldn't run update >> " . $sql_update[$i]);
			}
		}

		echo "DONE<br /><br />\n";
	}
}

$sql = "UPDATE " . CONFIG_TABLE . " 
	SET config_value = '.1.0 [20020905]' 
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
