<?php
/***************************************************************************
*                           upgrade_20.php  -  description
*                              -------------------
*     begin                : Sat Oct 14 2000
*     copyright            : (C) 2000 by James Atkinson
*     email                : james@totalgeek.org
* 
*     $id$
* 
****************************************************************************/
  
/***************************************************************************
*
*   This program is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 2 of the License, or
*   (at your option) any later version.
*
***************************************************************************/
include('extention.inc');
include('config.' . $phpEx);

function convert_ip($ip) 
{
	if (strstr($ip, ".")) 
	{
		$ip_sep = explode(".", $ip);
		$return = (( $ip_sep[0] * 0xFFFFFF + $ip_sep[0] ) + ( $ip_sep[1] *   0xFFFF + $ip_sep[1] ) + ( $ip_sep[2] *     0xFF + $ip_sep[2] ) + ( $ip_sep[3] ) );
	}
	else 
	{
		$return = sprintf( "%d.%d.%d.%d", ( ( $ip >> 24 ) & 0xFF ), ( ( $ip >> 16 ) & 0xFF ), ( ( $ip >>  8 ) & 0xFF ), ( ( $ip       ) & 0xFF ) );
	}
	return($return);
}                                                                                                            

function convert_date($date_in)
{
	list($date, $time) = split(" ", $date_in);
	list($year, $month, $day) = split("-", $date);
	list($hours, $minutes) = split(":", $time);
	$timestamp = mktime($hours, $minutes, 0, $month, $day, $year);             

	return($timestamp);
}

function drop_column($db, $table, $column)
{
	$sql = "alter table $table drop $column";
	if (!$r = mysql_query($sql, $db))
		echo "<font color=\"#FF0000\">ERROR! count not drop column $column from table $table.  Reason: <b>" . mysql_error().  "</B></FONT>";      
}

function change_column($db, $table, $column, $type, $null)
{
	$sql = "alter table $table change $column $column $type $null";
	if (!$r = mysql_query($sql, $db))
		echo "<font color=\"#FF0000\">ERROR! count not change column $column from table $table.  Reason: <b>" . mysql_error().  "</B></FONT>";      
}

function add_column($db, $table, $column, $type, $null)
{
	$sql = "alter table $table add $column $type $null";
	if (!$r = mysql_query($sql, $db))
		echo "<font color=\"#FF0000\">ERROR! count not add column $column from table $table.  Reason: <b>" . mysql_error().  "</B></FONT>";      
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
          <HTML>
          <HEAD>
          <TITLE>phpBB - Database upgrade 1.2 to 2.0 Developers edition</TITLE>
          </HEAD>
          <BODY BGCOLOR="#000000" TEXT="#FFFFFF" LINK="#11C6BD" VLINK="#11C6BD">


<?php
if($next) 
{
	switch($next) 
	{
		case 'backup':

			echo "<H2>Step 1: Backup the tables to be modified.</H2><BR>";

			if(!$db = mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
				die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbhost. Using username $dbuser.<BR>Please go back and try again.</font>");

			if(!@mysql_select_db("$dbname", $db))
				die("<font color=\"#FF0000\">Database $dbname could not be found</font>"); 

			$tables = array("posts"      => "Y", 
								 "priv_msgs"  => "Y", 
								 "sessions"   => "Y", 
								 "topics"     => "Y", 
								 "banlist"    => "Y",
								 "config"     => "N",
								 "forums"     => "N",
								 "users"      => "N",
								 "access"     => "N",
								 "smiles"     => "N",
								 "words"      => "N",
								 "forum_mods" => "N");

			while (list($table_name, $drop_table) = each($tables)) 
			{
				echo "Backing up the $table_name table... <BR>";

				$backup_name = $table_name . "_backup";
				$table_create = "CREATE TABLE $backup_name (\n";

				$r = mysql_query("show fields from $table_name", $db);

				if (0 != mysql_errno($db)) 
				{
					die("<font color=\"#FF0000\">Error, could not backup the table $table_name to $backup_name</font>");
				} 
				else 
				{
					while ($row = mysql_fetch_array($r)) 
					{
						$table_create .= "	$row[Field] $row[Type]";
						if (isset($row["Default"]) && (!empty($row["Default"]) || $row["Default"] == "0"))
							$table_create .= " DEFAULT '$row[Default]'";
						if ($row["Null"] != "YES")
							$table_create .= " NOT NULL";
						if ($row["Extra"] != "")
							$table_create .= " $row[Extra]";
						$table_create .= ",\n";
					}
			/* The code above leaves extra ',' at the end of the row.  use ereg_replace to remove it */
					$table_create = ereg_replace(",\n$", "", $table_create);

					echo "&nbsp;&nbsp;&nbsp; Extracted the table columns ...<br>\n";

					unset($index);
					$r = mysql_query("SHOW KEYS FROM $table_name", $db);
					while($row = mysql_fetch_array($r)) 
					{
						$key_name = $row['Key_name'];
						if (($key_name != "PRIMARY") && ($row['Non_unique'] == 0))
							$key_name = "UNIQUE|$key_name";
						if (!isset($index[$key_name]))
							$index[$key_name] = array();
						$index[$key_name][] = $row['Column_name'];
					}

					while(list($x, $columns) = @each($index)) 
					{
						$table_create .= ",\n";
						if ($x == "PRIMARY")
							$table_create .= "   PRIMARY KEY (" . implode($columns, ", ") . ")";
						elseif (substr($x,0,6) == "UNIQUE")
							$table_create .= "   UNIQUE " .substr($x,7). " (" . implode($columns, ", ") . ")";
						else
							$table_create .= "   KEY $x (" . implode($columns, ", ") . ")";
					}

					echo "&nbsp;&nbsp;&nbsp; Extracted the table indexes ...<br>\n";

					$table_create .= "\n)";
					mysql_query($table_create, $db);
					echo "&nbsp;&nbsp;&nbsp; Created the backup table $backup_name ...<br>\n";

					mysql_query("insert into $backup_name select * from $table_name", $db);
					echo "&nbsp;&nbsp;&nbsp; Copied the data from $table_name to $backup_name...<br>\n";

					if ($drop_table == 'Y')
					{
						mysql_query("drop table $table_name", $db);
						echo "&nbsp;&nbsp;&nbsp; Dropped table $table_name...<br>\n";
					}
				}
			}
?>
Backups completed ok.<P>
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
<INPUT TYPE="HIDDEN" NAME="next" VALUE="create">
<INPUT TYPE="SUBMIT" VALUE="Next >">
<?php
		break;

	case 'create':

		echo "<H2>Step 2: Create the tables in the new format.</H2><BR>";
		if(!$db = mysql_connect("$dbhost", "$dbuser", "$dbpasswd")) 
			die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbhost. Using username $dbuser.<BR>Please go back and try again.</font>"); 

		if(!@mysql_select_db("$dbname", $db))
			die("<font color=\"#FF0000\">Database $dbname could not be found</font>"); 

   	$tables = array ("posts" => "CREATE TABLE posts (
                       post_id int(10) DEFAULT '0' NOT NULL auto_increment,
                       topic_id int(10) DEFAULT '0' NOT NULL,
                       forum_id int(10) DEFAULT '0' NOT NULL,
                       poster_id int(10) NOT NULL,
                       post_time int(10) NOT NULL,
                       poster_ip int(10) NOT NULL,
                       KEY(forum_id),
                       KEY(topic_id),
                       KEY(poster_id),
                       PRIMARY KEY (post_id))", 
							"post_text" => "CREATE TABLE posts_text (
												 post_id int(10) NOT NULL,
												 post_text text,
												 PRIMARY KEY(post_id))",
                    "pmsg" => "CREATE TABLE priv_msgs (
                          msg_id int(10) DEFAULT '0' NOT NULL auto_increment,
                          from_userid int(10) DEFAULT '0' NOT NULL,
                          to_userid int(10) DEFAULT '0' NOT NULL,
                          msg_time int(10) NOT NULL,
                          poster_ip int(10) NOT NULL,
                          msg_status int(10) DEFAULT '0' NOT NULL,
                          msg_text text NOT NULL,
                          PRIMARY KEY (msg_id),
                          KEY to_userid (to_userid)
                          )",
           "sessions" => "CREATE TABLE sessions (
                        sess_id int(10) unsigned DEFAULT '0' NOT NULL,
                        user_id int(10) DEFAULT '0' NOT NULL,
                        start_time int(10) unsigned DEFAULT '0' NOT NULL,
                        remote_ip int(10) NOT NULL,
								username varchar(40),
								forum int(10),
                        PRIMARY KEY (sess_id),
                        KEY start_time (start_time),
                        KEY remote_ip (remote_ip)
                        )",    
           "topics" => "CREATE TABLE topics (
                         topic_id int(10) DEFAULT '0' NOT NULL auto_increment,
                         topic_title varchar(100) NOT NULL,
                         topic_poster int(10) NOT NULL,
                         topic_time int(10) NOT NULL,
                         topic_views int(10) DEFAULT '0' NOT NULL,
                         forum_id int(10) NOT NULL,
                         topic_status tinyint(3) DEFAULT '0' NOT NULL,
                         topic_notify tinyint(3) DEFAULT '0',
                         KEY(forum_id),
                         PRIMARY KEY (topic_id))",
           "banlist" => "CREATE TABLE banlist(
                          ban_id int(10) NOT NULL AUTO_INCREMENT DEFAULT '0',
                          ban_userid int(10),
                          ban_ip int(10),
                          ban_start int(10),
                          ban_end int(10),
                          ban_time_type int(10),
                          PRIMARY KEY(ban_id))");

		while(list($name, $table) = each($tables)) 
		{
			echo "Creating table $name &nbsp; ";
			if(!$r = mysql_query($table, $db))
				die("<font color=\"#FF0000\">ERROR! Could not create table. Reason: <b>". mysql_error()."</b>");
			echo "[OK]<BR>";
			flush();
		}                   

		drop_column($db, "config", "admin_passwd");
		change_column($db, "config", "allow_html", "tinyint(3)", "null");
		change_column($db, "config", "allow_bbcode", "tinyint(3)", "null");
		change_column($db, "config", "allow_sig", "tinyint(3)", "null");
		change_column($db, "config", "allow_namechange", "tinyint(3)", "null");
		change_column($db, "config", "override_themes", "tinyint(3)", "null");

		echo "Altered table config <br>";

	   drop_column($db, "forums", "forum_moderator");
	   change_column($db, "forums", "forum_access", "tinyint(3)", "null");
	   change_column($db, "forums", "forum_type", "tinyint(3)", "null");
	   add_column($db, "forums", "forum_posts", "int(10)", "default '0' not null");
	   add_column($db, "forums", "forum_topics", "int(10)", "default '0' not null");

		echo "Altered table forums <br>";

		change_column($db, "users", "user_aim", "varchar(255)", "null");
		change_column($db, "users", "user_yim", "varchar(255)", "null");
		change_column($db, "users", "user_msnm", "varchar(255)", "null");
		change_column($db, "users", "user_email", "varchar(255)", "null");
		change_column($db, "users", "user_viewemail", "tinyint(3)", "null");
		change_column($db, "users", "user_attachsig", "tinyint(3)", "null");
		change_column($db, "users", "user_desmile", "tinyint(3)", "null");
		change_column($db, "users", "user_html", "tinyint(3)", "null");
		change_column($db, "users", "user_bbcode", "tinyint(3)", "null");
		add_column($db, "users", "user_notify", "tinyint(3)", "null");

		echo "Altered table users <br>";

		change_column($db, "access", "access_title", "varchar(30)", "not null");

		echo "Altered table access <br>";

		change_column($db, "smiles", "code", "varchar(50)", "not null");
		change_column($db, "smiles", "smile_url", "varchar(100)", "not null");
		change_column($db, "smiles", "emotion", "varchar(75)", "not null");

		echo "Altered table smiles <br>";

		change_column($db, "words", "word", "varchar(100)", "not null");
		change_column($db, "words", "replacement", "varchar(100)", "not null");

		echo "Altered table words <br>";

		add_column($db, "forum_mods", "mod_notify", "tinyint(3)", "null");

		echo "Altered table forum_mods <br>";
?>
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
<INPUT TYPE="HIDDEN" NAME="next" VALUE="convert">
<INPUT TYPE="SUBMIT" VALUE="Next >">

<?php
		break;

	case 'convert':

		echo "<H2>Step 4: Convert the data to the new table format.</H2><BR>";

		if(!$db = mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
			die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbhost. Using username $dbuser.<BR>Please go back and try again.</font>");

		if(!@mysql_select_db("$dbname", $db))
			die("<font color=\"#FF0000\">Database $dbname could not be found</font>"); 

		$r = mysql_query("select * from posts_backup", $db);

		echo "Converting posts and creating posts_text ...<br>";

		while ($row = mysql_fetch_array($r)) 
		{
			$post_time = convert_date($row['post_time']);
			$post_ip   = convert_ip($row['poster_ip']);

			$post_id   = $row['post_id'];
			$topic_id  = $row['topic_id'];
			$forum_id  = $row['forum_id'];
			$poster_id = $row['poster_id'];
			$post_text = $row['post_text'];

			$sql = "insert posts (post_id, topic_id,  forum_id, poster_id, post_time, poster_ip)
					  values ($post_id, $topic_id, $forum_id, $poster_id, $post_time, $post_ip)";
			mysql_query($sql, $db);
			if (mysql_errno($db) != 0)
				echo "The following error occured converting posts " . mysql_error($db) . "<br>";

			$sql = "insert posts_text (post_id, post_text) values ($post_id, '$post_text')";
			mysql_query($sql, $db);
			if (mysql_errno($db) != 0)
				echo "The following error occured converting posts_text " . mysql_error($db) . "<br>";
		}

		$r = mysql_query("select * from priv_msgs_backup", $db);
      echo mysql_error($db);

		echo "Converting priv_msgs ..<br>";

		while ($row = mysql_fetch_array($r)) 
		{
			$msg_time  = convert_date($row['msg_time']);
			$poster_ip = convert_ip('0.0.0.0');

			$msg_id       = $row['msg_id'];
			$from_userid  = $row['from_userid'];
			$to_userid    = $row['to_userid'];
			$msg_status   = $row['msg_status'];
			$msg_text     = $row['msg_text'];

			$sql = "insert priv_msgs (msg_id, from_userid,  to_userid, msg_time, poster_ip, msg_status, msg_text)
					  values ($msg_id, $from_userid, $to_userid, $msg_time, $poster_ip, $msg_status, '$msg_text')";
			mysql_query($sql, $db);
			if (mysql_errno($db) != 0)
				echo "The following error occured converting priv_msgs " . mysql_error($db) . "<br>";

		}

		$r = mysql_query("select * from sessions_backup", $db);

		echo "Converting sessions ..<br>";

		while ($row = mysql_fetch_array($r)) 
		{
			$start_time = convert_date($row['start_time']);
			$remote_ip  = convert_ip($row['remote_ip']);

			$sess_id  = $row['sess_id'];
			$user_id  = $row['user_id'];

			$sql = "insert sessions (sess_id, user_id,  start_time, remote_ip)
					  values ($sess_id, $user_id, $start_time, $remote_ip)";
			mysql_query($sql, $db);
			if (mysql_errno($db) != 0)
				echo "The following error occured converting sessions " . mysql_error($db) . "<br>";

		}

		$r = mysql_query("select * from topics_backup", $db);

		echo "Converting topics ..<br>";

		while ($row = mysql_fetch_array($r)) 
		{
			$topic_time = convert_date($row['topic_time']);

			$topic_id     = $row['topic_id'];
			$topic_title  = $row['topic_title'];
			$topic_poster = $row['topic_poster'];
			$topic_views  = $row['topic_views'];
			$topic_id     = $row['topic_id'];
			$topic_status = $row['topic_status'];
			$topic_notify = $row['topic_notify'];

			$sql = "insert topics (topic_id, topic_title, topic_poster, topic_time, topic_views, forum_id, topic_status, topic_notify)
					  values ($topic_id, '$topic_title', $topic_poster, $topic_time, $topic_views, $forum_id, $topic_status, $topic_notify)";
			mysql_query($sql, $db);
			if (mysql_errno($db) != 0)
				echo "The following error occured converting topics " . mysql_error($db) . "<br>";

		}

		$r = mysql_query("select * from banlist_backup", $db);

		echo "Converting banlist ..<br>";

		while ($row = mysql_fetch_array($r)) 
		{
			$ban_start = convert_date($row['ban_start']);
			$ban_end   = convert_date($row['ban_end']);
			$ban_ip    = convert_ip($row['ban_ip']);

			$ban_id        = $row['ban_id'];
			$ban_userid    = $row['ban_userid'];
			$ban_time_type = $row['ban_time_type'];

			$sql = "insert banlist (ban_id, ban_userid, ban_ip, ban_start, ban_end, ban_time_type)
					  values ($ban_id, $ban_userid, $ban_ip, $ban_start, $ban_end, $ban_time_type)";
			mysql_query($sql, $db);
			if (mysql_errno($db) != 0)
				echo "The following error occured converting banlist " . mysql_error($db) . "<br>";

		}

?>
All Done.
<?php
		break;

	} // switch
} // if next
else 
{
?>
	<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
      Welcome!  This script will upgrade your phpBB v1.2 database to version 2.0.<br>
		The upgrade will perform the following functions:

		<UL>
         <LI>Back up all modified database tables.
			<LI>Create the new tables
			<LI>Convert the data
		</UL>
		<INPUT TYPE="HIDDEN" NAME="next" VALUE="backup">
		<INPUT TYPE="SUBMIT" VALUE="Next >">
   </FORM>
<?php      
}
?>
</BODY>
</HTML>
