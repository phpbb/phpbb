<?php
/***************************************************************************
 *                             update_to_xxx.php
 *                            -------------------
 *   begin                : Wednesday, May 16, 2002
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

function _sql($sql, &$errored, &$error_ary, $echo_dot = true)
{
	global $db;

	if (!($result = $db->sql_query($sql)))
	{
		$errored = true;
		$error_ary['sql'][] = (is_array($sql)) ? $sql[$i] : $sql;
		$error_ary['error_code'][] = $db->sql_error();
	}

	if ($echo_dot)
	{
		echo ". \n";
		flush();
	}

	return $result;
}

@set_time_limit(120);

define('IN_PHPBB', 1);
$phpbb_root_path = './../';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
if(!isset($dbms))
{
	die("Please read: <a href='../docs/INSTALL.html'>INSTALL.html</a> before attempting to update.");
}
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
include($phpbb_root_path . 'includes/functions_search.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);


//
//
//
$updates_to_version = ".0.11";
//
//
//

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;">
<meta http-equiv="Content-Style-Type" content="text/css">
<style type="text/css">
<!--

font,th,td,p,body { font-family: "Courier New", courier; font-size: 11pt }

a:link,a:active,a:visited { color : #006699; }
a:hover		{ text-decoration: underline; color : #DD6900;}

hr	{ height: 0px; border: solid #D1D7DC 0px; border-top-width: 1px;}

.maintitle,h1,h2	{font-weight: bold; font-size: 22px; font-family: "Trebuchet MS",Verdana, Arial, Helvetica, sans-serif; text-decoration: none; line-height : 120%; color : #000000;}

.ok {color:green}

/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("../templates/subSilver/formIE.css");
-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000" link="#006699" vlink="#5584AA">

<table width="100%" border="0" cellspacing="0" cellpadding="10" align="center">
	<tr>
		<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><img src="../templates/subSilver/images/logo_phpBB.gif" border="0" alt="Forum Home" vspace="1" /></td>
				<td align="center" width="100%" valign="middle"><span class="maintitle">Updating to latest stable release</span></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<h2>Information</h2>

<?php

echo '<p>Database type &nbsp; &nbsp;:: <b>' . SQL_LAYER . '</b><br />';

$sql = "SELECT config_value
	FROM " . CONFIG_TABLE . "
	WHERE config_name = 'version'";
if (!($result = $db->sql_query($sql)))
{
	die("Couldn't obtain version info");
}

$row = $db->sql_fetchrow($result);

$sql = array();

switch ($row['config_value'])
{
	case '':
		echo 'Previous version :: <b>&lt; RC-3</b></p><br />';
		break;
	case 'RC-3':
		echo 'Previous version :: <b>RC-3</b></p><br />';
		break;
	case 'RC-4':
		echo 'Previous version :: <b>RC-4</b></p><br />';
		break;
	default:
		echo 'Previous version :: <b>2' . $row['config_value'] . '</b><br />';
		break;
}

echo 'Updated version &nbsp;:: <b>2' . $updates_to_version . '</b></p>' ."\n";

//
// Schema updates
//
switch ($row['config_value'])
{
	case '':
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				$sql[] = "ALTER TABLE " . USERS_TABLE . " DROP
					COLUMN user_autologin_key";

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
				$sql[] = "ALTER TABLE " . USERS_TABLE . " DROP
					COLUMN user_autologin_key";

				$sql[] = "ALTER TABLE " . USERS_TABLE . " ADD
					user_session_time int NOT NULL,
					user_session_page smallint NOT NULL,
					CONSTRAINT [DF_" . $table_prefix . "users_user_session_time] DEFAULT (0) FOR [user_session_time],
					CONSTRAINT [DF_" . $table_prefix . "users_user_session_page] DEFAULT (0) FOR [user_session_page]";
				$sql[] = "CREATE INDEX [IX_" . $table_prefix . "users]
					ON [" . USERS_TABLE . "]([user_session_time]) ON [PRIMARY]";

				/* ---------------------------------------------------------------------
					DROP FORUM TABLE -- if this may cause you problems you can safely
					comment it out, remember to manually remove the IDENTITY setting on
					the forum_id column
				   --------------------------------------------------------------------- */
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
				/* --------------------------------------------------------------
					END OF DROP FORUM -- don't remove anything after this point!
				   -------------------------------------------------------------- */

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
				$sql[] = "ALTER TABLE " . USERS_TABLE . " DROP
					COLUMN user_autologin_key";

				$sql[] = "ALTER TABLE " . USERS_TABLE . " ADD
					user_session_time int NOT NULL,
					user_session_page smallint NOT NULL";
				$sql[] = "CREATE INDEX user_session_time
					ON " . USERS_TABLE . " (user_session_time)";

				$sql[] = "ALTER TABLE " . TOPICS_TABLE . " ADD
					topic_first_post_id int NULL";
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

	case 'RC-3':
	case 'RC-4':
	case '.0.0':
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				$sql[] = "ALTER TABLE " . USERS_TABLE . "
					MODIFY COLUMN user_id  mediumint(8) NOT NULL,
					MODIFY COLUMN user_timezone decimal(5,2) DEFAULT '0' NOT NULL";
				break;
			case 'postgresql':
				$sql[] = "ALTER TABLE " . USERS_TABLE . "
					RENAME COLUMN user_timezone TO user_timezone_old";
				$sql[] = "ALTER TABLE " . USERS_TABLE . "
					ADD COLUMN user_timezone decimal(5)";
				break;
			case 'mssql':
			case 'mssql-odbc':
				$sql[] = "ALTER TABLE " . USERS_TABLE . "
					ALTER COLUMN [user_timezone] [decimal] (5,2) NOT NULL";
				break;
		}

	case '.0.1':
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				$sql[] = "ALTER TABLE " . GROUPS_TABLE . "
					MODIFY COLUMN group_id mediumint(8) NOT NULL auto_increment";
				break;
			case 'mssql':
			case 'mssql-odbc':
				/* ---------------------------------------------------------------------
					DROP GROUP TABLE -- if this may cause you problems you can safely
					comment it out, remember to manually add the IDENTITY setting on
					the group_id column
				   --------------------------------------------------------------------- */
				$sql[] = "CREATE TABLE Tmp_" . GROUPS_TABLE . "
					(group_id int IDENTITY (1, 1) NOT NULL, group_type smallint NULL, group_name varchar(50) NOT NULL, group_description varchar(255) NOT NULL, group_moderator int NULL, group_single_user smallint NOT NULL) ON [PRIMARY]";
				$sql[] = "SET IDENTITY_INSERT " . GROUPS_TABLE . " ON";
				$sql[] = "INSERT INTO Tmp_" . GROUPS_TABLE . " (group_id, group_type, group_name, group_description, group_moderator, group_single_user)
					SELECT group_id, group_type, group_name, group_description, group_moderator, group_single_user FROM " . GROUPS_TABLE . " TABLOCKX";
				$sql[] = "SET IDENTITY_INSERT " . GROUPS_TABLE . " OFF";
				$sql[] = "DROP TABLE " . GROUPS_TABLE;
				$sql[] = "EXECUTE sp_rename N'Tmp_" . GROUPS_TABLE . "', N'" . GROUPS_TABLE . "', 'OBJECT'";
				$sql[] = "ALTER TABLE " . GROUPS_TABLE . " ADD
					CONSTRAINT [PK_" . $table_prefix . "groups] PRIMARY KEY CLUSTERED (group_id) ON [PRIMARY]";
				$sql[] = "CREATE INDEX [IX_" . $table_prefix . "groups]
					ON " . GROUPS_TABLE . " (group_single_user) ON [PRIMARY]";
				/* --------------------------------------------------------------
					END OF DROP GROUP -- don't remove anything after this point!
				   -------------------------------------------------------------- */
				break;
			
		}

	case '.0.3':

		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				// Add indexes to post_id in search match table (+ word_id for MS Access)
				$sql[] = "ALTER TABLE " . SEARCH_MATCH_TABLE . " 
					ADD INDEX post_id (post_id)";

				// Modify user_timezone to decimal(5,2) for mysql ... mysql4/mssql/pgsql/msaccess
				// should be completely unaffected
				// Change default user_notify to 0 
				$sql[] = "ALTER TABLE " . USERS_TABLE . " 
					MODIFY COLUMN user_timezone decimal(5,2) DEFAULT '0' NOT NULL, 
					MODIFY COLUMN user_notify tinyint(1) DEFAULT '0' NOT NULL";

				// Adjust field type for prune_days, prune_freq ... was too small
				$sql[] = "ALTER TABLE " . PRUNE_TABLE . " 
					MODIFY COLUMN prune_days smallint(5) UNSIGNED NOT NULL, 
					MODIFY COLUMN prune_freq smallint(5) UNSIGNED NOT NULL";
				break;

			case 'msaccess':
				// Add indexes to post_id in search match table (+ word_id for MS Access)
				$sql[] = "CREATE INDEX " . SEARCH_MATCH_TABLE . " 
					ON " . SEARCH_MATCH_TABLE . " ([post_id])";
				$sql[] = "CREATE INDEX " . SEARCH_MATCH_TABLE . "_1 
					ON " . SEARCH_MATCH_TABLE . " ([word_id])";
				break;

			case 'postgresql':
				// Add indexes to post_id in search match table (+ word_id for MS Access)
				$sql[] = "CREATE INDEX post_id_" . SEARCH_MATCH_TABLE . " 
					ON " . SEARCH_MATCH_TABLE . " (post_id)";

				// Regenerate groups table with incremented group_id for pgsql 
				// ... missing in 2.0.3 ...
				$sql[] = "CREATE SEQUENCE " . GROUPS_TABLE . "_id_seq start 3 increment 1 maxvalue 2147483647 minvalue 1 cache 1";
				$sql[] = "CREATE TABLE tmp_" . GROUPS_TABLE . " 
					AS SELECT group_id, group_name, group_type, group_description, group_moderator, group_single_user 
						FROM " . GROUPS_TABLE;
				$sql[] = "DROP TABLE " . GROUPS_TABLE;
				$sql[] = "CREATE TABLE phpbb_groups (group_id int DEFAULT nextval('" . GROUPS_TABLE . "_id_seq'::text) NOT NULL, group_name varchar(40) NOT NULL, group_type int2 DEFAULT '1' NOT NULL, group_description varchar(255) NOT NULL, group_moderator int4 DEFAULT '0' NOT NULL, group_single_user int2 DEFAULT '0' NOT NULL, CONSTRAINT phpbb_groups_pkey PRIMARY KEY (group_id))";
				$sql[] = "INSERT INTO " . GROUPS_TABLE . " (group_id, group_name, group_type, group_description, group_moderator, group_single_user) 
					SELECT group_id, group_name, group_type, group_description, group_moderator, group_single_user 
						FROM tmp_" . GROUPS_TABLE;
				$sql[] = "DROP TABLE tmp_" . GROUPS_TABLE;
				break;
		}

	case '.0.4':

		switch (SQL_LAYER)
		{
			case 'mssql':
			case 'mssql-odbc':
				// Add missing defaults to MSSQL post table schema, failed in previous updates
				$sql[] = "ALTER TABLE [" . POSTS_TABLE . "] WITH NOCHECK ADD
					CONSTRAINT [DF_" . POSTS_TABLE . "_enable_bbcode] DEFAULT (1) FOR [enable_bbcode],
					CONSTRAINT [DF_" . POSTS_TABLE . "_enable_html] DEFAULT (0) FOR [enable_html],
					CONSTRAINT [DF_" . POSTS_TABLE . "_enable_smilies] DEFAULT (1) FOR [enable_smilies],
					CONSTRAINT [DF_" . POSTS_TABLE . "_enable_sig] DEFAULT (1) FOR [enable_sig],
					CONSTRAINT [DF_" . POSTS_TABLE . "_post_edit_count] DEFAULT (0) FOR [post_edit_count]";
				break;
		}

		// Add tables for visual confirmation ... saves me the trouble of writing a seperate
		// script :D
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				$sql[] = 'CREATE TABLE ' . $table_prefix . 'confirm (confirm_id char(32) DEFAULT \'\' NOT NULL, session_id char(32) DEFAULT \'\' NOT NULL, code char(6) DEFAULT \'\' NOT NULL, PRIMARY KEY (session_id, confirm_id))';
				break;

			case 'mssql':
			case 'mssql-odbc':
				$sql[] = 'CREATE TABLE [' . $table_prefix . 'confirm] ([confirm_id] [char] (32) NOT NULL , [session_id] [char] (32) NOT NULL , [code] [char] (6) NOT NULL ) ON [PRIMARY]';
				$sql[] = 'ALTER TABLE [' . $table_prefix . 'confirm] WITH NOCHECK ADD CONSTRAINT [PK_' . $table_prefix . 'confirm] PRIMARY KEY  CLUSTERED ( [session_id],[confirm_id])  ON [PRIMARY]';
				$sql[] = 'ALTER TABLE [' . $table_prefix . 'confirm] WITH NOCHECK ADD CONSTRAINT [DF_' . $table_prefix . 'confirm_confirm_id] DEFAULT (\'\') FOR [confirm_id], CONSTRAINT [DF_' . $table_prefix . 'confirm_session_id] DEFAULT (\'\') FOR [session_id], CONSTRAINT [DF_' . $table_prefix . 'confirm_code] DEFAULT (\'\') FOR [code]';
				break;

			case 'msaccess':
				$sql[] = 'CREATE TABLE ' . $table_prefix . 'confirm (confirm_id char(32) NOT NULL, session_id char(32) NOT NULL, code char(6) NOT NULL)';
				$sql[] = 'ALTER TABLE ' . $table_prefix . 'confirm ADD (PRIMARY KEY (session_id, confirm_id))';
				break;

			case 'postgresql':
				$sql[] = 'CREATE TABLE ' . $table_prefix . 'confirm (confirm_id char(32) DEFAULT \'\' NOT NULL,  session_id char(32) DEFAULT \'\' NOT NULL, code char(6) DEFAULT \'\' NOT NULL, CONSTRAINT phpbb_confirm_pkey PRIMARY KEY (session_id, confirm_id))';
				break;
		}

		break;
}

echo "<h2>Updating database schema</h2>\n";
echo "<p>Progress :: <b>";
flush();

$error_ary = array();
$errored = false;
if (count($sql))
{
	for ($i = 0; $i < count($sql); $i++)
	{
		_sql($sql[$i], $errored, $error_ary);
	}

	echo "</b> <b class=\"ok\">Done</b><br />Result &nbsp; :: \n";

	if ($errored)
	{
		echo " <b>Some queries failed, the statements and errors are listing below</b>\n<ul>";

		for ($i = 0; $i < count($error_ary['sql']); $i++)
		{
			echo "<li>Error :: <b>" . $error_ary['error_code'][$i]['message'] . "</b><br />";
			echo "SQL &nbsp; :: <b>" . $error_ary['sql'][$i] . "</b><br /><br /></li>";
		}

		echo "</ul>\n<p>This is probably nothing to worry about, update will continue. Should this fail to complete you may need to seek help at our development board. See <a href=\"docs\README.html\">README</a> for details on how to obtain advice.</p>\n";
	}
	else
	{
		echo "<b>No errors</b>\n";
	}
}
else
{
	echo " No updates required</b></p>\n";
}

//
// Data updates
//
unset($sql);
$error_ary = array();
$errored = false;

echo "<h2>Updating data</h2>\n";
echo "<p>Progress :: <b>";
flush();

switch ($row['config_value'])
{
	case '':
		$sql = "SELECT themes_id
			FROM " . THEMES_TABLE . "
			WHERE template_name = 'subSilver'";
		$result = _sql($sql, $errored, $error_ary);

		if ($row = $db->sql_fetchrow($result))
		{
			$theme_id = $row['themes_id'];

			$sql = "UPDATE " . THEMES_TABLE . "
				SET head_stylesheet = 'subSilver.css', body_background = '', body_bgcolor = 'E5E5E5', body_text = '000000', body_link = '006699', body_vlink = '5493B4', body_alink = '', body_hlink = 'DD6900', tr_color1 = 'EFEFEF', tr_color2 = 'DEE3E7', tr_color3 = 'D1D7DC', tr_class1 = '', tr_class2 = '', tr_class3 = '', th_color1 = '98AAB1', th_color2 = '006699', th_color3 = 'FFFFFF', th_class1 = 'cellpic1.gif', th_class2 = 'cellpic3.gif', th_class3 = 'cellpic2.jpg', td_color1 = 'FAFAFA', td_color2 = 'FFFFFF', td_color3 = '', td_class1 = 'row1', td_class2 = 'row2', td_class3 = '', fontface1 = 'Verdana, Arial, Helvetica, sans-serif', fontface2 = 'Trebuchet MS', fontface3 = 'Courier, ''Courier New'', sans-serif', fontsize1 = 10, fontsize2 = 11, fontsize3 = 12, fontcolor1 = '444444', fontcolor2 = '006600', fontcolor3 = 'FFA34F', span_class1 = '', span_class2 = '', span_class3 = ''
				WHERE themes_id = $theme_id";
			_sql($sql, $errored, $error_ary);

			$sql = "DELETE FROM " . THEMES_NAME_TABLE . "
				WHERE themes_id = $theme_id";
			_sql($sql, $errored, $error_ary);

			$sql = "INSERT INTO " . THEMES_NAME_TABLE . " (themes_id, tr_color1_name, tr_color2_name, tr_color3_name, tr_class1_name, tr_class2_name, tr_class3_name, th_color1_name, th_color2_name, th_color3_name, th_class1_name, th_class2_name, th_class3_name, td_color1_name, td_color2_name, td_color3_name, td_class1_name, td_class2_name, td_class3_name, fontface1_name, fontface2_name, fontface3_name, fontsize1_name, fontsize2_name, fontsize3_name, fontcolor1_name, fontcolor2_name, fontcolor3_name, span_class1_name, span_class2_name, span_class3_name)
				VALUES ($theme_id, 'The lightest row colour', 'The medium row color', 'The darkest row colour', '', '', '', 'Border round the whole page', 'Outer table border', 'Inner table border', 'Silver gradient picture', 'Blue gradient picture', 'Fade-out gradient on index', 'Background for quote boxes', 'All white areas', '', 'Background for topic posts', '2nd background for topic posts', '', 'Main fonts', 'Additional topic title font', 'Form fonts', 'Smallest font size', 'Medium font size', 'Normal font size (post body etc)', 'Quote & copyright text', 'Code text colour', 'Main table header text colour', '', '', '')";
			_sql($sql, $errored, $error_ary);
		}
		$db->sql_freeresult($result);

		$sql = "SELECT MIN(post_id) AS first_post_id, topic_id
			FROM " . POSTS_TABLE . "
			GROUP BY topic_id
			ORDER BY topic_id ASC";
		$result = _sql($sql, $errored, $error_ary);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$sql = "UPDATE " . TOPICS_TABLE . "
					SET topic_first_post_id = " . $row['first_post_id'] . "
					WHERE topic_id = " . $row['topic_id'];
				_sql($sql, $errored, $error_ary);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		$sql = "SELECT DISTINCT u.user_id
			FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug, " . AUTH_ACCESS_TABLE . " aa
			WHERE aa.auth_mod = 1
				AND ug.group_id = aa.group_id
				AND u.user_id = ug.user_id
				AND u.user_level <> " . ADMIN;
		$result = _sql($sql, $errored, $error_ary);

		$mod_user = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$mod_user[] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		if (count($mod_user))
		{
			$sql = "UPDATE " . USERS_TABLE . "
				SET user_level = " . MOD . "
				WHERE user_id IN (" . implode(', ', $mod_user) . ")";
			_sql($sql, $errored, $error_ary);
		}

		$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
			VALUES ('server_name', 'www.myserver.tld')";
		_sql($sql, $errored, $error_ary);

		$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
			VALUES ('script_path', '/phpBB2/')";
		_sql($sql, $errored, $error_ary);

		$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
			VALUES ('server_port', '80')";
		_sql($sql, $errored, $error_ary);

		$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
			VALUES ('record_online_users', '1')";
		_sql($sql, $errored, $error_ary);

		$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
			VALUES ('record_online_date', '" . time() . "')";
		_sql($sql, $errored, $error_ary);

	case 'RC-3':
	case 'RC-4':
	case '.0.0':
	case '.0.1':
		if (SQL_LAYER == 'postgresql')
		{
			$sql = "SELECT user_id, user_timezone_old
				FROM " . USERS_TABLE;
			$result = _sql($sql, $errored, $error_ary);

			while ($row = $db->sql_fetchrow($result))
			{
				$sql = "UPDATE " . USERS_TABLE . "
					SET user_timezone = " . $row['user_timezone_old'] . "
					WHERE user_id = " . $row['user_id'];
				_sql($sql, $errored, $error_ary);
			}
			$db->sql_freeresult($result);
		}

		$sql = "SELECT topic_id, topic_moved_id
			FROM " . TOPICS_TABLE . "
			WHERE topic_moved_id <> 0
				AND topic_status = " . TOPIC_MOVED;
		$result = _sql($sql, $errored, $error_ary);

		$topic_ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_ary[$row['topic_id']] = $row['topic_moved_id'];
		}
		$db->sql_freeresult($result);

		while (list($topic_id, $topic_moved_id) = each($topic_ary))
		{
			$sql = "SELECT MAX(post_id) AS last_post, MIN(post_id) AS first_post, COUNT(post_id) AS total_posts
				FROM " . POSTS_TABLE . "
				WHERE topic_id = $topic_moved_id";
			$result = _sql($sql, $errored, $error_ary);

			$sql = ($row = $db->sql_fetchrow($result)) ? "UPDATE " . TOPICS_TABLE . "	SET topic_replies = " . ($row['total_posts'] - 1) . ", topic_first_post_id = " . $row['first_post'] . ", topic_last_post_id = " . $row['last_post'] . " WHERE topic_id = $topic_id" : "DELETE FROM " . TOPICS_TABLE . " WHERE topic_id = " . $row['topic_id'];
			_sql($sql, $errored, $error_ary);
		}

		unset($sql);

		sync('all forums');

	case '.0.2':

	case '.0.3':

		// Topics will resync automatically

		// Remove stop words from search match and search words
		$dirname = 'language';
		$dir = opendir($phpbb_root_path . $dirname);

		while ($file = readdir($dir))
		{
			if (preg_match("#^lang_#i", $file) && !is_file($phpbb_root_path . $dirname . "/" . $file) && !is_link($phpbb_root_path . $dirname . "/" . $file) && file_exists($phpbb_root_path . $dirname . "/" . $file . '/search_stopwords.txt'))
			{

				$stopword_list = trim(preg_replace('#([\w\.\-_\+\'±µ-ÿ\\\]+?)[ \n\r]*?(,|$)#', '\'\1\'\2', str_replace("'", "\'", implode(', ', file($phpbb_root_path . $dirname . "/" . $file . '/search_stopwords.txt')))));

				$sql = "SELECT word_id 
					FROM " . SEARCH_WORD_TABLE . " 
					WHERE word_text IN ($stopword_list)";
				$result = _sql($sql, $errored, $error_ary);

				$word_id_sql = '';
				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$word_id_sql .= (($word_id_sql != '') ? ', ' : '') . $row['word_id'];
					}
					while ($row = $db->sql_fetchrow($result));

					$sql = "DELETE FROM " . SEARCH_WORD_TABLE . " 
						WHERE word_id IN ($word_id_sql)";
					_sql($sql, $errored, $error_ary);

					$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . " 
						WHERE word_id IN ($word_id_sql)";
					_sql($sql, $errored, $error_ary);
				}
				$db->sql_freeresult($result);
			}
		}
		closedir($dir);

		// Mark common words ...
		remove_common('global', 4/10);

		// remove superfluous polls ... grab polls with topics then delete polls
		// not in that list
		$sql = "SELECT v.vote_id 
			FROM " . TOPICS_TABLE . " t, " . VOTE_DESC_TABLE . " v
			WHERE v.topic_id = t.topic_id";
		$result = _sql($sql, $errored, $error_ary);

		$vote_id_sql = '';
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$vote_id_sql .= (($vote_id_sql != '') ? ', ' : '') . $row['vote_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			$sql = "DELETE FROM " . VOTE_DESC_TABLE . " 
				WHERE vote_id NOT IN ($vote_id_sql)";
			_sql($sql, $errored, $error_ary);

			$sql = "DELETE FROM " . VOTE_RESULTS_TABLE . " 
				WHERE vote_id NOT IN ($vote_id_sql)";
			_sql($sql, $errored, $error_ary);

			$sql = "DELETE FROM " . VOTE_USERS_TABLE . " 
				WHERE vote_id NOT IN ($vote_id_sql)";
			_sql($sql, $errored, $error_ary);
		}
		$db->sql_freeresult($result);

		// update pm counters
		$sql = "SELECT privmsgs_to_userid, COUNT(privmsgs_id) AS unread_count 
			FROM " . PRIVMSGS_TABLE . " 
			WHERE privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " 
			GROUP BY privmsgs_to_userid";
		$result = _sql($sql, $errored, $error_ary);

		if ($row = $db->sql_fetchrow($result))
		{
			$update_users = array();
			do
			{
				$update_users[$row['unread_count']][] = $row['privmsgs_to_userid'];
			}
			while ($row = $db->sql_fetchrow($result));

			while (list($num, $user_ary) = each($update_users))
			{
				$user_ids = implode(', ', $user_ary);

				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_unread_privmsg = $num 
					WHERE user_id IN ($user_ids)";
				_sql($sql, $errored, $error_ary);
			}
			unset($update_list);
		}
		$db->sql_freeresult($result);

		$sql = "SELECT privmsgs_to_userid, COUNT(privmsgs_id) AS new_count 
			FROM " . PRIVMSGS_TABLE . " 
			WHERE privmsgs_type = " . PRIVMSGS_NEW_MAIL . " 
			GROUP BY privmsgs_to_userid";
		$result = _sql($sql, $errored, $error_ary);

		if ($row = $db->sql_fetchrow($result))
		{
			$update_users = array();
			do
			{
				$update_users[$row['new_count']][] = $row['privmsgs_to_userid'];
			}
			while ($row = $db->sql_fetchrow($result));

			while (list($num, $user_ary) = each($update_users))
			{
				$user_ids = implode(', ', $user_ary);

				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_new_privmsg = $num 
					WHERE user_id IN ($user_ids)";
				_sql($sql, $errored, $error_ary);
			}
			unset($update_list);
		}
		$db->sql_freeresult($result);

		// Remove superfluous watched topics
		$sql = "SELECT t.topic_id 
			FROM " . TOPICS_TABLE . " t, " . TOPICS_WATCH_TABLE . " w
			WHERE w.topic_id = t.topic_id";
		$result = _sql($sql, $errored, $error_ary);

		$topic_id_sql = '';
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$topic_id_sql .= (($topic_id_sql != '') ? ', ' : '') . $row['topic_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . " 
				WHERE topic_id NOT IN ($topic_id_sql)";
			_sql($sql, $errored, $error_ary);
		}
		$db->sql_freeresult($result);

		// Reset any email addresses which are non-compliant ... something
		// not done in the upgrade script and thus which may affect some 
		// mysql users
		switch (SQL_LAYER)
		{
			case 'mysql':
				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_email = '' 
					WHERE user_email NOT REGEXP '^[a-zA-Z0-9_\+\.\-]+@.*[a-zA-Z0-9_\-]+\.[a-zA-Z]{2,}$'";
				_sql($sql, $errored, $error_ary);
		}

	case '.0.4':

		// Add the confirmation code switch ... save time and trouble elsewhere
		$sql = 'INSERT INTO ' . CONFIG_TABLE . " (config_name, config_value)
			VALUES ('enable_confirm', '0')";
		_sql($sql, $errored, $error_ary);

		$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value)
			VALUES ('sendmail_fix', '0')";
		_sql($sql, $errored, $error_ary);

	case '.0.5':
		
		$sql = "SELECT user_id, username 
			FROM " . USERS_TABLE;
		$result = _sql($sql, $errored, $error_ary);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!preg_match('#(&gt;)|(&lt;)|(&quot)|(&amp;)#', $row['username']))
			{
				if ($row['username'] != htmlspecialchars($row['username']))
				{
					$sql = "UPDATE " . USERS_TABLE . "
						SET username = '" . str_replace("'", "''", htmlspecialchars($row['username'])) . "'
						WHERE user_id = " . $row['user_id'];
					_sql($sql, $errored, $error_ary);
				}
			}
		}
		$db->sql_freeresult($result);
		
		break;

	default:
		echo " No updates where required</b></p>\n";
		break;
}

echo "<h2>Updating version and optimizing tables</h2>\n";
echo "<p>Progress :: <b>";
flush();

// update the version
$sql = "UPDATE " . CONFIG_TABLE . "
	SET config_value = '$updates_to_version'
	WHERE config_name = 'version'";
_sql($sql, $errored, $error_ary);

// Optimize/vacuum analyze the tables where appropriate 
// this should be done for each version in future along with 
// the version number update
switch (SQL_LAYER)
{
	case 'mysql':
	case 'mysql4':
		$sql = 'OPTIMIZE TABLE ' . $table_prefix . 'auth_access, ' . $table_prefix . 'banlist, ' . $table_prefix . 'categories, ' . $table_prefix . 'config, ' . $table_prefix . 'disallow, ' . $table_prefix . 'forum_prune, ' . $table_prefix . 'forums, ' . $table_prefix . 'groups, ' . $table_prefix . 'posts, ' . $table_prefix . 'posts_text, ' . $table_prefix . 'privmsgs, ' . $table_prefix . 'privmsgs_text, ' . $table_prefix . 'ranks, ' . $table_prefix . 'search_results, ' . $table_prefix . 'search_wordlist, ' . $table_prefix . 'search_wordmatch, ' . $table_prefix . 'smilies, ' . $table_prefix . 'themes, ' . $table_prefix . 'themes_name, ' . $table_prefix . 'topics, ' . $table_prefix . 'topics_watch, ' . $table_prefix . 'user_group, ' . $table_prefix . 'users, ' . $table_prefix . 'vote_desc, ' . $table_prefix . 'vote_results, ' . $table_prefix . 'vote_voters, ' . $table_prefix . 'words';
		_sql($sql, $errored, $error_ary);
		break;

	case 'postgresql':
		_sql("VACUUM ANALYZE", $errored, $error_ary);
		break;
}

echo "</b> <b class=\"ok\">Done</b><br />Result &nbsp; :: \n";

if ($errored)
{
	echo " <b>Some queries failed, the statements and errors are listing below</b>\n<ul>";

	for ($i = 0; $i < count($error_ary['sql']); $i++)
	{
		echo "<li>Error :: <b>" . $error_ary['error_code'][$i]['message'] . "</b><br />";
		echo "SQL &nbsp; :: <b>" . $error_ary['sql'][$i] . "</b><br /><br /></li>";
	}

	echo "</ul>\n<p>This is probably nothing to worry about, update will continue. Should this fail to complete you may need to seek help at our development board. See <a href=\"docs\README.html\">README</a> for details on how to obtain advice.</p>\n";
}
else
{
	echo "<b>No errors</b>\n";
}

echo "<h2>Update completed</h2>\n";
echo "\n<p>You should now visit the General Configuration settings page in the <a href=\"../admin/\">Administration Panel</a> and check the General Configuration of the board. If you updated from versions prior to RC-3 you <b>must</b> update some entries. If you do not do this emails sent from the board will contain incorrect information. Don't forget to delete this file!</p>\n";

?>

<br clear="all" />

</body>
</html>
