<html>
<body>
<?php

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
   
$sql = array();

switch ( SQL_LAYER )
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
			MODIFY topic_moved_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,  
			ADD COLUMN topic_first_post_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL, 
			ADD INDEX (topic_first_post_id)";

		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN tr_class1_name varchar(50) NULL, 
			ADD COLUMN tr_class2_name varchar(50) NULL, 
			ADD COLUMN tr_class3_name varchar(50) NULL, 
			ADD COLUMN th_class1_name varchar(50) NULL, 
			ADD COLUMN th_class2_name varchar(50) NULL, 
			ADD COLUMN th_class3_name varchar(50) NULL, 
			ADD COLUMN td_class1_name varchar(50) NULL, 
			ADD COLUMN td_class2_name varchar(50) NULL, 
			ADD COLUMN td_class3_name varchar(50) NULL, 
			ADD COLUMN span_class1_name varchar(50) NULL, 
			ADD COLUMN span_class2_name varchar(50) NULL, 
			ADD COLUMN span_class3_name varchar(50) NULL";
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
				
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . " 
			ADD COLUMN topic_first_post_id int4";
		$sql[] = "CREATE INDEX topic_first_post_id_" . $table_prefix . "topics_index 
			ON " . TOPICS_TABLE . " (topic_first_post_id)";

		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN tr_class1_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN tr_class2_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN tr_class3_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN th_class1_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN th_class2_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN th_class3_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN td_class1_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN td_class2_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN td_class3_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN span_class1_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN span_class2_name varchar(50) NULL"; 
		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " 
			ADD COLUMN span_class3_name varchar(50) NULL";
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
	
		$sql[] = "DROP INDEX " . TOPICS_TABLE . ".IX_" . $table_prefix . "topics";
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . " ADD 
			topic_first_post_id int NOT NULL, 
			CONSTRAINT [DF_" . $table_prefix . "topics_topic_first_post_id] DEFAULT (0) FOR [topic_first_post_id]";
		$sql[] = "CREATE  INDEX [IX_" . $table_prefix . "topics] 
			ON [" . TOPICS_TABLE . "]([forum_id], [topic_type], [topic_first_post_id], [topic_last_post_id]) ON [PRIMARY]";

		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " ADD
			tr_class1_name varchar(50) NULL, 
			tr_class2_name varchar(50) NULL, 
			tr_class3_name varchar(50) NULL, 
			th_class1_name varchar(50) NULL, 
			th_class2_name varchar(50) NULL, 
			th_class3_name varchar(50) NULL, 
			td_class1_name varchar(50) NULL, 
			td_class2_name varchar(50) NULL, 
			td_class3_name varchar(50) NULL, 
			span_class1_name varchar(50) NULL, 
			span_class2_name varchar(50) NULL, 
			span_class3_name varchar(50) NULL";
		break;

	case 'msaccess':
		$sql[] = "ALTER TABLE " . USERS_TABLE . " ADD 
			user_session_time int NOT NULL, 
			user_session_page smallint NOT NULL";
		$sql[] = "CREATE INDEX user_session_time 
			ON " . USERS_TABLE . " (user_session_time)";
	
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . " ADD 
			topic_first_post_id int NOT NULL";
		$sql[] = "CREATE INDEX topic_first_post_id 
			ON " . TOPICS_TABLE . " (topic_first_post_id)";

		$sql[] = "ALTER TABLE " . THEMES_NAME_TABLE . " ADD
			tr_class1_name varchar(50) NULL, 
			tr_class2_name varchar(50) NULL, 
			tr_class3_name varchar(50) NULL, 
			th_class1_name varchar(50) NULL, 
			th_class2_name varchar(50) NULL, 
			th_class3_name varchar(50) NULL, 
			td_class1_name varchar(50) NULL, 
			td_class2_name varchar(50) NULL, 
			td_class3_name varchar(50) NULL, 
			span_class1_name varchar(50) NULL, 
			span_class2_name varchar(50) NULL, 
			span_class3_name varchar(50) NULL";
		break;

	default:
		die("No DB LAYER found!");
		break;
}

$errored = false;
for($i = 0; $i < count($sql); $i++)
{
	echo "Running :: " . $sql[$i];

	if ( !($result = $db->sql_query($sql[$i])) )
	{
		$errored = true;
		$error = $db->sql_error();
		echo " -> <b>FAILED</b> ---> <u>" . $error['message'] . "</u><br /><br />\n\n";
	}
	else
	{
		echo " -> <b>COMPLETED</b><br /><br />\n\n";
	}
}

if ( $errored )
{
	echo "\n<br /><b>Some queries failed! This is probably nothing to worry about, update will attempt to continue. Should this fail you may need to seek help at our development board (see README)</b><br /><br />\n\n";
}

$sql = "SELECT themes_id 
	FROM " . THEMES_TABLE . " 
	WHERE template_name = 'subSilver'";
if( !($result = $db->sql_query($sql)) )
{
	die("Couldn't obtain subSilver id");
}

if( $row = $db->sql_fetchrow($result) )
{
	$theme_id = $row['themes_id'];

	$sql = "UPDATE " . THEMES_TABLE . " 
		SET head_stylesheet = 'subSilver.css', body_background = '', body_bgcolor = 'E5E5E5', body_text = '000000', body_link = '006699', body_vlink = '5493B4', body_alink = '', body_hlink = 'DD6900', tr_color1 = 'EFEFEF', tr_color2 = 'DEE3E7', tr_color3 = 'D1D7DC', tr_class1 = '', tr_class2 = '', tr_class3 = '', th_color1 = '98AAB1', th_color2 = '006699', th_color3 = 'FFFFFF', th_class1 = 'cellpic1.gif', th_class2 = 'cellpic3.gif', th_class3 = 'cellpic2.jpg', td_color1 = 'FAFAFA', td_color2 = 'FFFFFF', td_color3 = '', td_class1 = 'row1', td_class2 = 'row2', td_class3 = '', fontface1 = 'Verdana, Arial, Helvetica, sans-serif', fontface2 = 'Trebuchet MS', fontface3 = 'Courier, ''Courier New'', sans-serif', fontsize1 = 10, fontsize2 = 11, fontsize3 = 12, fontcolor1 = '444444', fontcolor2 = '006600', fontcolor3 = 'FFA34F', span_class1 = '', span_class2 = '', span_class3 = ''
		WHERE themes_id = $theme_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		die("Couldn't update subSilver theme");
	}

	$sql = "INSERT INTO " . THEMES_NAME_TABLE . " (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, tr_class1_name, tr_class2_name, tr_class3_name, th_color1_name, th_color2_name, th_color3_name, th_class1_name, th_class2_name, th_class3_name, td_color1_name, td_color2_name, td_color3_name, td_class1_name, td_class2_name, td_class3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, span_class1_name, span_class2_name, span_class3_name) 
		VALUES ($theme_id, 'The lightest row colour', 'The medium row color', 'The darkest row colour', '', '', '', 'Border round the whole page', 'Outer table border', 'Inner table border', 'Silver gradient picture', 'Blue gradient picture', 'Fade-out gradient on index', 'Background for quote boxes', 'All white areas', '', 'Background for topic posts', '2nd background for topic posts', '', 'Main fonts', 'Additional topic title font', 'Form fonts', 'Smallest font size', 'Medium font size', 'Normal font size (post body etc)', 'Quote & copyright text', 'Code text colour', 'Main table header text colour', '', '', '')";
	if ( !($result = $db->sql_query($sql)) )
	{
		echo "WARNING >> Couldn't insert subSilver name info<br />\n";
	}
}

print "<br />Updating topic first post info<br />";

$sql = "SELECT MIN(post_id) AS first_post_id, topic_id
	FROM " . POSTS_TABLE . "
	GROUP BY topic_id
	ORDER BY topic_id ASC";
if ( !($result = $db->sql_query($sql)) )
{
	die("Couldn't obtain first post id list");
}

if ( $row = $db->sql_fetchrow($result) )
{
	do
	{
		$post_id = $row['first_post_id'];
		$topic_id = $row['topic_id'];

		$sql = "UPDATE " . TOPICS_TABLE . " 
			SET topic_first_post_id = $post_id 
			WHERE topic_id = $topic_id";
		if ( !$db->sql_query($sql) )
		{
			die("Couldn't update topic first post id in topic :: $topic_id");
		}
	}
	while ( $row = $db->sql_fetchrow($result) );
}

print "<br />Updating moderator user_level<br />";
$sql = "SELECT DISTINCT u.user_id 
	FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug, " . AUTH_ACCESS_TABLE . " aa 
	WHERE aa.auth_mod = 1
		AND ug.group_id = aa.group_id
		AND u.user_id = ug.user_id 
		AND u.user_level <> 1";
if ( !$db->sql_query($sql) )
{
	die("Couldn't obtain moderator user ids");
}

if ( $row = $db->sql_fetchrow($result) )
{
	do
	{
		$sql = "UPDATE " . USERS_TABLE . " 
			SET user_level = " . MOD . " 
			WHERE user_id = " . $row['user_id'];
		if ( !$db->sql_query($sql) )
		{
			die("Couldn't update user level");
		}
	}
	while ( $row = $db->sql_fetchrow($result) );

}


print "<br />Updating config settings<br />";

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('server_name', 'www.myserver.tld')";
if( !$db->sql_query($sql) )
{  
	die("Couldn't insert config key 'record_online_date'");
}

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('script_path', '/phpBB2/')";
if( !$db->sql_query($sql) )
{  
	die("Couldn't insert config key 'record_online_date'");
}

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('server_port', '80')";
if( !$db->sql_query($sql) )
{  
	die("Couldn't insert config key 'record_online_date'");
}

$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value )
	VALUES ('version', 'RC-3')";
if ( !$db->sql_query($sql) )
{
	die("Couldn't insert new config var");
}

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('record_online_users', '1')";
if( !$db->sql_query($sql) )
{
	die("Couldn't insert config key 'record_online_users'");
}

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('record_online_date', '" . time() . "')";
if( !$db->sql_query($sql) )
{  
	die("Couldn't insert config key 'record_online_date'");
}

echo "\n<br />\n<b>COMPLETE!</b><br />\n";
echo "\n<p>You should now visit the General Configuration settings page in the <a href=\"admin/\">Administration Panel</a> and update the 'Server' settings. If you do not do this emails sent from the board will contain incorrect information. Don't forget to delete this file!</p>\n";
?>
</body>
</html>