<html>
<body>
<?php

$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

$sql = array();

switch ( SQL_LAYER )
{
	case 'mysql':
	case 'mysql4':
		$sql[] = "ALTER TABLE " . RANKS_TABLE . " DROP 
			COLUMN rank_max";

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

		/* DROP FORUM TABLE -- if this may cause you problems you can safely 
		   comment it out, remember to manually remove the IDENTITY setting on 
		   the forum_id column */
		$sql [] = "ALTER TABLE " . FORUMS_TABLE . " DROP 
			CONSTRAINT [DF_" . $table_prefix . "forums_forum_posts], 
			CONSTRAINT [DF_" . $table_prefix . "forums_forum_topics], 
			CONSTRAINT [DF_" . $table_prefix . "forums_forum_last_post_id], 
			CONSTRAINT [DF_" . $table_prefix . "forums_prune_enable], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_view], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_read], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_post], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_reply], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_edit], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_delete], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_sticky], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_announce], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_vote], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_pollcreate], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_attachments]";
		$sql[] = "CREATE TABLE Tmp_" . FORUMS_TABLE . " 
			(forum_id int NOT NULL, cat_id int NOT NULL, forum_name varchar(100) NOT NULL, forum_desc varchar(255) NULL, forum_status smallint NOT NULL, forum_order int NOT NULL, forum_posts int NOT NULL, forum_topics smallint NOT NULL, forum_last_post_id int NOT NULL, prune_next int NULL, prune_enable smallint NOT NULL, auth_view smallint NOT NULL, auth_read smallint NOT NULL, auth_post smallint NOT NULL, auth_reply smallint NOT NULL, auth_edit smallint NOT NULL, auth_delete smallint NOT NULL,	auth_sticky smallint NOT NULL, auth_announce smallint NOT NULL, auth_vote smallint NOT NULL, auth_pollcreate smallint NOT NULL, auth_attachments smallint NOT NULL) ON [PRIMARY]";
		$sql[] = "ALTER TABLE [Tmp_" . FORUMS_TABLE . "] WITH NOCHECK ADD 
			CONSTRAINT [DF_" . $table_prefix . "forums_forum_posts] DEFAULT (0) FOR [forum_posts], 
			CONSTRAINT [DF_" . $table_prefix . "forums_forum_topics] DEFAULT (0) FOR [forum_topics], 
			CONSTRAINT [DF_" . $table_prefix . "forums_forum_last_post_id] DEFAULT (0) FOR [forum_last_post_id], 
			CONSTRAINT [DF_" . $table_prefix . "forums_prune_enable] DEFAULT (0) FOR [prune_enable], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_view] DEFAULT (0) FOR [auth_view], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_read] DEFAULT (0) FOR [auth_read], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_post] DEFAULT (0) FOR [auth_post], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_reply] DEFAULT (0) FOR [auth_reply], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_edit] DEFAULT (0) FOR [auth_edit], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_delete] DEFAULT (0) FOR [auth_delete], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_sticky] DEFAULT (0) FOR [auth_sticky], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_announce] DEFAULT (0) FOR [auth_announce], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_vote] DEFAULT (0) FOR [auth_vote], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_pollcreate] DEFAULT (0) FOR [auth_pollcreate], 
			CONSTRAINT [DF_" . $table_prefix . "forums_auth_attachments] DEFAULT (0) FOR [auth_attachments]";
		$sql[] = "INSERT INTO Tmp_" . FORUMS_TABLE . " (forum_id, cat_id, forum_name, forum_desc, forum_status, forum_order, forum_posts, forum_topics, forum_last_post_id, prune_next, prune_enable, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_sticky, auth_announce, auth_vote, auth_pollcreate, auth_attachments)
				SELECT forum_id, cat_id, forum_name, forum_desc, forum_status, forum_order, forum_posts, forum_topics, forum_last_post_id, prune_next, prune_enable, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_sticky, auth_announce, auth_vote, auth_pollcreate, auth_attachments FROM " . FORUMS_TABLE . " TABLOCKX";
		$sql[] = "DROP TABLE " . FORUMS_TABLE;
		$sql[] = "EXECUTE sp_rename N'Tmp_" . FORUMS_TABLE . "', N'" . FORUMS_TABLE . "', 'OBJECT'";
		$sql[] = "ALTER TABLE " . FORUMS_TABLE . " ADD 
			CONSTRAINT [PK_" . $table_prefix . "forums] PRIMARY KEY CLUSTERED (forum_id) ON [PRIMARY]";
		$sql[] = "CREATE NONCLUSTERED INDEX [IX_" . $table_prefix . "forums]
			ON " . FORUMS_TABLE . " (cat_id, forum_order, forum_last_post_id) ON [PRIMARY]";
		/* END OF DROP FORUM -- don't remove anything after this point! */

		$sql[] = "DROP INDEX " . RANKS_TABLE . ".IX_" . $table_prefix . "ranks";
		$sql[] = "ALTER TABLE " . RANKS_TABLE . " DROP 
			COLUMN rank_max";
		$sql[] = "CREATE  INDEX [IX_" . $table_prefix . "ranks] 
			ON [" . RANKS_TABLE . "]([rank_min], [rank_special]) ON [PRIMARY]";

		$sql[] = "DROP INDEX " . TOPICS_TABLE . ".IX_" . $table_prefix . "topics";
		$sql[] = "ALTER TABLE " . TOPICS_TABLE . " ADD 
			topic_first_post_id int NULL, 
			CONSTRAINT [DF_" . $table_prefix . "topics_topic_first_post_id] FOR [topic_first_post_id]";
		$sql[] = "CREATE  INDEX [IX_" . $table_prefix . "topics] 
			ON [" . TOPICS_TABLE . "]([forum_id], [topic_type], [topic_first_post_id], [topic_last_post_id]) ON [PRIMARY]";

		$sql[] = "ALTER TABLE " . SEARCH_WORD_TABLE . " DROP 
			CONSTRAINT [PK_" . $table_prefix . "search_wordlist]";
		$sql[] = "CREATE UNIQUE INDEX [IX_" . $table_prefix . "search_wordlist] 
			ON [" . SEARCH_WORD_TABLE . "]([word_text]) WITH IGNORE_DUP_KEY ON [PRIMARY]";
		$sql[] = "CREATE  INDEX [IX_" . $table_prefix . "search_wordlist_1] 
			ON [" . SEARCH_WORD_TABLE . "]([word_common]) ON [PRIMARY]";

		$sql[] = "CREATE INDEX [IX_" . $table_prefix . "search_wordmatch_1] 
			ON [" . SEARCH_MATCH_TABLE . "]([word_id]) ON [PRIMARY]";

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
	flush();

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
	flush();
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

print "<br />Updating topic first post info<br />\n";
flush();

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

print "<br />Updating moderator user_level<br />\n";
flush();

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

$mod_user = array();
while ( $row = $db->sql_fetchrow($result) )
{
	$mod_user[] = $row['user_id'];
}

if ( count($mod_user) )
{
	$sql = "UPDATE " . USERS_TABLE . " 
		SET user_level = " . MOD . " 
		WHERE user_id IN (" . implode(", ", $mod_user) . ")"; 
	if ( !$db->sql_query($sql) )
	{
		die("Couldn't update user level");
	}
}

print "<br />Updating config settings<br />\n";
flush();

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('server_name', 'www.myserver.tld')";
if( !$db->sql_query($sql) )
{  
	print "Failed inserting server_name config ... probably exists already<br />\n";
}

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('script_path', '/phpBB2/')";
if( !$db->sql_query($sql) )
{  
	print "Failed inserting script_path config ... probably exists already<br />\n";
}

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('server_port', '80')";
if( !$db->sql_query($sql) )
{  
	print "Failed inserting server_port config ... probably exists already<br />\n";
}

$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value )
	VALUES ('version', 'RC-3')";
if ( !$db->sql_query($sql) )
{
	print "Failed inserting version config ... probably exists already<br />\n";
}

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('record_online_users', '1')";
if( !$db->sql_query($sql) )
{
	print "Failed inserting record_online_users config ... probably exists already<br />\n";
}

$sql = "INSERT INTO " . CONFIG_TABLE . "
	(config_name, config_value) VALUES ('record_online_date', '" . time() . "')";
if( !$db->sql_query($sql) )
{  
	print "Failed inserting record_online_date config ... probably exists already<br />\n";
}

echo "\n<br />\n<b>COMPLETE!</b><br />\n";
echo "\n<p>You should now visit the General Configuration settings page in the <a href=\"admin/\">Administration Panel</a> and update the 'Server' settings. If you do not do this emails sent from the board will contain incorrect information. Don't forget to delete this file!</p>\n";
?>
</body>
</html>
