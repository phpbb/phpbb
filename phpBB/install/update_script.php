<?php

@set_time_limit(2400);
ob_end_clean();
//
//
//
$db = $dbhost = $dbuser = $dbpasswd = $dbport = $dbname = '';

define('IN_PHPBB', 1);
$phpbb_root_path='./../';

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);
include($phpbb_root_path . 'db/' . $dbms . '.'.$phpEx);

$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

//
// Updates to this version ...
//
$version = '2.1.0 [20020820]';

// ----------------
// BEGIN VARS DEFNS
//
define('ACL_DENY', 2);
define('ACL_ALLOW', 4);
define('ACL_PERMIT', 8);

define('AUTH_ALL', 0);
define('AUTH_REG', 1);
define('AUTH_ACL', 2);
define('AUTH_MOD', 3);
define('AUTH_ADMIN', 5);

define('ANONYMOUS', 1);

$auth_map = array(
	'auth_view' => 'forum_list',
	'auth_read' => 'forum_read',
	'auth_post' => 'forum_post',
	'auth_reply' => 'forum_reply',
	'auth_edit' => 'forum_edit',
	'auth_delete' => 'forum_delete',
	'auth_pollcreate' => 'forum_poll',
	'auth_vote' => 'forum_vote',
	'auth_announce' => 'forum_announce',
	'auth_sticky' => 'forum_sticky',
	'auth_attachments' => 'forum_attach',
	'auth_download' => 'forum_download',
);

$auth_options = array(
	'forum_list',
	'forum_read',
	'forum_post',
	'forum_reply',
	'forum_edit',
	'forum_delete',
	'forum_poll',
	'forum_vote',
	'forum_announce',
	'forum_sticky',
	'forum_attach',
	'forum_download',
	'forum_html',
	'forum_bbcode',
	'forum_smilies',
	'forum_img',
	'forum_flash',
	'forum_sigs',
	'forum_search',
	'forum_email',
	'forum_rate',
	'forum_print',
	'forum_ignoreflood',
	'forum_ignorequeue'
);

$auth_mod_options = array(
	'mod_edit',
	'mod_delete',
	'mod_move',
	'mod_lock',
	'mod_split',
	'mod_merge',
	'mod_approve',
	'mod_unrate',
	'mod_auth'
);

$auth_admin_options = array(
	'admin_general',
	'admin_user',
	'admin_group',
	'admin_forum',
	'admin_post',
	'admin_ban',
	'admin_auth',
	'admin_email',
	'admin_styles',
	'admin_backup',
	'admin_clearlogs'
);

$new_groups = array(
	'guest_id' => "INSERT INTO " . $table_prefix . "groups (group_name, group_type) VALUES ('GUESTS', 0)",
	'reg_inactive_id' => "INSERT INTO " . $table_prefix . "groups (group_name, group_type) VALUES ('REGISTERED_INACTIVE', 0)",
	'reg_id' => "INSERT INTO " . $table_prefix . "groups (group_name, group_type) VALUES ('REGISTERED', 0)",
	'super_mod_id' => "INSERT INTO " . $table_prefix . "groups (group_name, group_type) VALUES ('SUPER_MODERATORS', 0)",
	'admin_id' => "INSERT INTO " . $table_prefix . "groups (group_name, group_type) VALUES ('ADMINISTRATORS', 0)"
);
//
// END VAR DEFNS
// -------------

$sql = "SELECT config_value
	FROM " . $table_prefix . "config
	WHERE config_name = 'version'";
$result = $db->sql_query($sql);

$row = $db->sql_fetchrow($result);
$this_version = $row['config_value'];

//
// Output page header
//
page_header();

?>

<h1>Pre-schema update data changes</h1>

<?php

switch ( $this_version )
{
	case '.0.0':
	case '.0.1':
	case '.0.2':
	case '.1.0 [20020402]':
	case '.1.0 [20020420]':
	case '.1.0 [20020421]':
	case '.1.0 [20020430]':
	case '2.1.0 [20020430]':
		gen_str_init('* Updating from <b><= [20020430]</b>');

		$anon_id_tbl = array(
			'banlist' => array(
				'ban_userid'
			),
			'posts' => array(
				'poster_id'
			),
			'topics' => array(
				'topic_poster'
			),
			'user_group' => array(
				'user_id'
			)
		);

		$sql = '';
		foreach ( $anon_id_tbl as $table => $field_ary )
		{
			foreach ( $field_ary as $field )
			{
				$sql = "UPDATE " . $table_prefix . "$table
					SET $field = " . ANONYMOUS . "
					WHERE $field = -1";
				$db->sql_query($sql);
			}
		}

		unset($sql);

		gen_str_ok();
		break;

	default;
		print "<span class=\"updtext\">* No updates needed</span><br />\n";
}

?>

<h1>Updating current schema</h1>

<?php

//
// Get schema
//
$schema = get_schema();

$table_def = $schema['table_def'];
$field_def = $schema['field_def'];
$key_def = $schema['key_def'];
$create_def = $schema['create_def'];

//
// Create array with tables in 'old' database
//
$result = $db->sql_query('SHOW TABLES');

$currenttables = array();
while( $table = $db->sql_fetchrow($result) )
{
	$currenttables[] = $table[0];
}

//
// Check what tables we need to CREATE
//
foreach( $table_def as $table => $definition )
{
	if ( !in_array($table, $currenttables) )
	{
		gen_str_init("* Creating <b>$table</b>");

		$db->sql_query($definition);

		gen_str_ok();
	}
}

//
// Loop tables in schema
//
foreach ( $field_def as $table => $table_def )
{
	// Loop fields in table
	gen_str_init("* Updating table <b>$table</b>");

	$sql = "SHOW FIELDS
		FROM $table";
	$result = $db->sql_query($sql);

	$current_fields = array();
	while ( $row = $db->sql_fetchrow($result) )
	{
		$current_fields[] = $row['Field'];
	}

	$alter_sql = "ALTER TABLE $table ";
	if ( is_array($table_def) )
	{
		foreach ( $table_def as $field => $definition )
		{
			if ( $field == '' )
			{
				//
				// Skip empty fields if any (shouldn't be needed)
				//
				continue;
			}

			$type = $definition['type'];
			$size = $definition['size'];

			$default = isset($definition['default']) ? "DEFAULT " . $definition['default'] : '';

			$notnull = $definition['notnull'] == 1 ? 'NOT NULL' : '';

			$auto_increment = $definition['auto_increment'] == 1 ? 'auto_increment' : '';

			$oldfield = isset($rename[$table][$field]) ? $rename[$table][$field] : $field;

			//
			// If the current is not a key of $current_def and it is not a field that is
			// to be renamed then the field doesn't currently exist.
			//
			$changes[] = ( !in_array($field, $current_fields) && $oldfield == $field ) ? " ADD $field " . $create_def[$table][$field] : " CHANGE $oldfield $field " . $create_def[$table][$field];
		}
	}

	$alter_sql .= join(',', $changes);

	unset($changes);
	unset($current_fields);

	$sql = "SHOW INDEX
		FROM $table";
	$result = $db->sql_query($sql);

	$indices = array();
	while( $row = $db->sql_fetchrow($result) )
	{
		$indices[] = $row['Key_name'];
	}

	if ( is_array($key_def[$table]) )
	{
		foreach ( $key_def[$table] as $key_name => $key_field )
		{
			if ( !in_array($key_name, $indices) )
			{
				$alter_sql .= ( $key_name == 'PRIMARY' ) ? ", ADD PRIMARY KEY ($key_field)" : ", ADD INDEX $key_name ($key_field)";
			}
		}
	}

	$db->sql_query($alter_sql);
	$alter_sql = '';

	gen_str_ok();

}

?>

<h1>Updating table data</h1>

<?php


switch ( $this_version )
{
	case '.0.0':
	case '.0.1':
	case '.0.2':
	case '.1.0 [20020402]':
		gen_str_init('* Inserting <b>config</b> data');

		$sql_ary = array();
		$sql_ary[] = "INSERT INTO " . $table_prefix . "config (config_name, config_value)
			VALUES ('session_gc', '3600')";
		$sql_ary[] = "INSERT INTO " . $table_prefix . "config (config_name, config_value)
			VALUES ('session_last_gc', '0')";

		$db->sql_return_on_error(true);
		for($i = 0; $i < count($sql_ary); $i++)
		{
			$db->sql_query($sql_ary[$i]);
		}
		$db->sql_return_on_error(false);

		gen_str_ok();

	case '.1.0 [20020420]':
	case '.1.0 [20020421]':
		gen_str_init('* Inserting <b>config</b> data');

		$sql = "SELECT COUNT(user_id) AS total_users, MAX(user_id) AS newest_user_id
			FROM " . $table_prefix . "users
			WHERE user_id <> " . ANONYMOUS;
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$user_count = $row['total_users'];
		$newest_user_id = $row['newest_user_id'];

		$sql = "SELECT username
			FROM " . $table_prefix . "users
			WHERE user_id = $newest_user_id";
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$newest_username = $row['username'];

		$sql_ary = array();
		$sql_ary[] = "INSERT INTO " . $table_prefix . "config (config_name, config_value)
			VALUES ('newest_user_id', $newest_user_id)";
		$sql_ary[] = "INSERT INTO " . $table_prefix . "config (config_name, config_value)
			VALUES ('newest_username', '$newest_username')";
		$sql_ary[] = "INSERT INTO " . $table_prefix . "config (config_name, config_value)
			VALUES ('num_users', $user_count)";

		$db->sql_return_on_error(true);
		for($i = 0; $i < count($sql_ary); $i++)
		{
			$db->sql_query($sql_ary[$i]);
		}
		$db->sql_return_on_error(false);

		gen_str_ok();

	case '.1.0 [20020430]':
	case '2.1.0 [20020430]':

		gen_str_init('* Decoding <b>banlist.ban_ip</b>');

		$sql = "SELECT ban_id, ban_ip
			FROM " . $table_prefix . "banlist
			WHERE ban_ip NOT LIKE '%.%'";
		$result = $db->sql_query($sql);

		$sql_ary = array();
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$sql = "UPDATE " . $table_prefix . "banlist
					SET ban_ip = '" . str_replace('255', '*', decode_ip($row['ban_ip'])) . "'
					WHERE ban_id = " . $row['ban_id'];
				$db->sql_query($sql);
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		gen_str_ok();

		$upd_ip_sql = array(
			'privmsgs' => array(
				'privmsgs_ip'
			),
			'vote_voters' => array(
				'vote_user_ip'
			),
		);
//			'posts' => array(
//				'poster_ip',
//			),

		$batchsize = 1000;
		foreach ( $upd_ip_sql as $table => $field_ary )
		{
			foreach ( $field_ary as $field )
			{
				gen_str_init("* Decoding <b>$table.$field</b>");

				$db->sql_return_on_error(true);
				$sql = "SELECT MAX($field) AS max_id
					FROM " . $table_prefix . "$table";
				if ( $result = $db->sql_query($sql) )
				{
					$db->sql_return_on_error(false);

					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					$maxid = $row['max_id'];

					for($i = 0; $i <= $maxid; $i += $batchsize)
					{
						$batchstart = $i;
						$batchend = $i + $batchsize;

						$sql = "SELECT DISTINCT $field
							FROM " . $table_prefix . "$table
							WHERE $field NOT LIKE '%.%'
								BETWEEN $batchstart
									AND $batchend";
						$result = $db->sql_query($sql);

						if ( $row = $db->sql_fetchrow($result) )
						{
							do
							{
								$sql = "UPDATE " . $table_prefix . "$table
									SET $field = '" . decode_ip($row[$field]) . "'
									WHERE $field LIKE '" . $row[$field] . "'";
								$db->sql_query($sql);
							}
							while ( $row = $db->sql_fetchrow($result) );
						}
						$db->sql_freeresult($result);
					}

					gen_str_ok();
				}
				else
				{
					gen_str_skip();
				}
				$db->sql_return_on_error(false);
			}
		}

		gen_str_init('* Inserting <b>config</b> data');

		$sql_ary = array(
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('ldap_server', '')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('ldap_base_dn', '')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('ldap_uid', '')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('limit_load', '2.0')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('active_sessions', '0')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('search_interval','0')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('min_search_chars','3')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('max_search_chars','20')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('pm_max_boxes','4')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('pm_max_msgs','50')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('max_post_chars', '0')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('max_post_smilies', '0')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('board_disable_msg','')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('email_enable','1')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('auth_method','db')",
			"INSERT INTO " . $table_prefix . "config (config_name, config_value) VALUES ('icons_path','images/icons')",
		);

		$db->sql_return_on_error(true);
		for($i = 0; $i < count($sql_ary); $i++)
		{
			$db->sql_query($sql_ary[$i]);
		}

		gen_str_ok();
		gen_str_init('* Inserting <b>style</b> data');

		$sql_ary = array(
			"INSERT INTO " . $table_prefix . "styles (style_id, template_id, theme_id, imageset_id, style_name) VALUES (1, 1, 1, 1, 'subSilver')",

			"INSERT INTO " . $table_prefix . "styles_imageset (imageset_id, imageset_name, imageset_path, post_new, post_locked, post_pm, reply_new, reply_pm, reply_locked, icon_quote, icon_edit, icon_search, icon_profile, icon_pm, icon_email, icon_www, icon_icq, icon_aim, icon_yim, icon_msnm, icon_no_email, icon_no_www, icon_no_icq, icon_no_aim, icon_no_yim, icon_no_msnm, icon_delete, icon_ip, goto_post, goto_post_new, goto_post_latest, goto_post_newest, forum, forum_new, forum_locked, folder, folder_new, folder_hot, folder_hot_new, folder_locked, folder_locked_new, folder_sticky, folder_sticky_new, folder_announce, folder_announce_new, topic_watch, topic_unwatch, poll_left, poll_center, poll_right, rating) VALUES (1, 'subSilver &copy; phpBB Group', 'subSilver', '\"imagesets/subSilver/{LANG}/post.gif\" width=\"82\" height=\"25\" border=\"0\"', '\"imagesets/subSilver/{LANG}/reply-locked.gif\" width=\"82\" height=\"25\" border=\"0\"', '\"imagesets/subSilver/{LANG}/post.gif\" width=\"82\" height=\"25\" border=\"0\"', '\"imagesets/subSilver/{LANG}/reply.gif\" width=\"88\" height=\"27\" border=\"0\"', '\"imagesets/subSilver/{LANG}/reply.gif\" width=\"88\" height=\"27\" border=\"0\"', '\"imagesets/subSilver/{LANG}/reply-locked.gif\" width=\"82\" height=\"25\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_quote.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_edit.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_search.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_profile.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_pm.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_email.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_www.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_icq_add.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_aim.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_yim.gif\" width=\"59\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_msnm.gif\" width=\"59\" height=\"18\" border=\"0\"', '', '', '', '', '', '', '\"imagesets/subSilver/icon_delete.gif\" width=\"16\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/{LANG}/icon_ip.gif\" width=\"16\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/icon_minipost.gif\" width=\"12\" height=\"9\" border=\"0\"', '\"imagesets/subSilver/icon_minipost_new.gif\" width=\"12\" height=\"9\" border=\"0\"', '\"imagesets/subSilver/icon_latest_reply.gif\" width=\"18\" height=\"9\" border=\"0\"', '\"imagesets/subSilver/icon_newest_reply.gif\" width=\"18\" height=\"9\" border=\"0\"', '\"imagesets/subSilver/folder_big.gif\" width=\"46\" height=\"25\" border=\"0\"', '\"imagesets/subSilver/folder_new_big.gif\" width=\"46\" height=\"25\" border=\"0\"', '\"imagesets/subSilver/folder_locked_big.gif\" width=\"46\" height=\"25\" border=\"0\"', '\"imagesets/subSilver/folder.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_new.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_hot.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_new_hot.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_lock.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_lock_new.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_sticky.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_sticky_new.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_announce.gif\" width=\"19\" height=\"18\" border=\"0\"', '\"imagesets/subSilver/folder_announce_new.gif\" width=\"19\" height=\"18\" border=\"0\"', '', '', '\"imagesets/subSilver/voting_lcap.gif\" width=\"4\" height=\"12\" border=\"0\"', '\"imagesets/subSilver/voting_rcap.gif\" height=\"12\" border=\"0\"', '\"imagesets/subSilver/voting_bar

.gif\" width=\"4\" height=\"12\" border=\"0\"', '\"imagesets/subSilver/ratings/{RATE}.gif\" width=\"45\" height=\"17\" border=\"0\"')",

			"INSERT INTO " . $table_prefix . "styles_template (template_id, template_name, template_path, poll_length, pm_box_length, compile_crc) VALUES (1, 'subSilver &copy; phpBB Group', 'subSilver', 205, 175, '')",

			"INSERT INTO " . $table_prefix . "styles_theme (theme_id, css_data, css_external) VALUES (1, 'th	{ background-image: url(templates/subSilver/images/cellpic3.gif) }\r\ntd.cat { background-image: url(templates/subSilver/images/cellpic1.gif) }\r\ntd.rowpic { background-image: url(templates/subSilver/images/cellpic2.jpg); background-repeat: repeat-y }\r\ntd.icqback { background-image: url(templates/subSilver/images/icon_icq_add.gif); background-repeat: no-repeat }\r\ntd.catHead,td.catSides,td.catLeft,td.catRight,td.catBottom { background-image: url(templates/subSilver/images/cellpic1.gif) }\r\nth.thTop { background-image: url(templates/subSilver/images/cellpic3.gif) }', 'subSilver/subSilver.css')",

				"INSERT INTO " . $table_prefix . "icons (icons_id, icons_url, icons_width, icons_height) VALUES (1, '', 0, 0)",
		);

		for($i = 0; $i < count($sql_ary); $i++)
		{
			$db->sql_query($sql_ary[$i]);
		}

		gen_str_ok();
		gen_str_init('* Updating <b>style</b> defaults');

		$sql_ary = array(
			"UPDATE " . $table_prefix . "users SET user_style = 1",
			"UPDATE " . $table_prefix . "config SET config_value = '1' WHERE config_name = 'default_style'",
		);

		if ( SQL_LAYER == 'mysql' || SQL_LAYER == 'mysql4' )
		{
			$sql_ary[] = "ALTER TABLE " . $table_prefix . "users AUTO_INCREMENT = 1";
		}

		$sql_ary[] = "UPDATE " . $table_prefix . "users SET user_id = 0 WHERE username = 'Anonymous'";

		for($i = 0; $i < count($sql_ary); $i++)
		{
			$db->sql_query($sql_ary[$i]);
		}
		$db->sql_return_on_error(false);

		gen_str_ok();
		gen_str_init('* Updating <b>permissions</b>');

		//
		// Grab user id of first user with user_level of ADMIN
		//
		$sql = "SELECT user_id
			FROM " . $table_prefix . "users
			WHERE user_level = 1
			ORDER BY user_id ASC";
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$founder_id = $row['user_id'];

		//
		// Grab forum auth information
		//
		$sql = "SELECT *
			FROM " . $table_prefix . "forums";
		$result = $db->sql_query($sql);

		$forum_access = array();
		while ( $row = $db->sql_fetchrow($result) )
		{
			$forum_access[] = $row;
		}
		$db->sql_freeresult($result);

		//
		// Grab user auth information
		//
		$sql = "SELECT ug.user_id, aa.*
			FROM " . $table_prefix . "auth_access aa, " . $table_prefix . "user_group ug, " . $table_prefix . "groups g
			WHERE g.group_id = aa.group_id
				AND g.group_single_user = 1
				AND ug.group_id = g.group_id";
		$result = $db->sql_query($sql);

		$user_access = array();
		while ( $row = $db->sql_fetchrow($result) )
		{
			$user_access[$row['forum_id']] = $row;
		}
		$db->sql_freeresult($result);

		//
		// Grab group auth information
		//
		$sql = "SELECT g.group_id, aa.*
			FROM " . $table_prefix . "auth_access aa, " . $table_prefix . "groups g
			WHERE g.group_id = aa.group_id
				AND g.group_single_user <> 1";
		$result = $db->sql_query($sql);

		$group_access = array();
		while ( $row = $db->sql_fetchrow($result) )
		{
			$group_access[$row['forum_id']] = $row;
		}
		$db->sql_freeresult($result);

		//
		// Now delete 'single user groups' since they are no
		// longer needed. Need to clear both user_group and groups
		//
		$sql = "SELECT group_id
			FROM " . $table_prefix . "groups
			WHERE group_single_user <> 1";
		$result = $db->sql_query($sql);

		$delete_ug_sql = '';
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$delete_ug_sql .= ( ( $delete_ug_sql != '' ) ? ', ' : '' ) . $row['group_id'];
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		//
		// Clean up user_group and groups
		//
		if ( $delete_ug_sql )
		{
			$sql = "DELETE FROM " . $table_prefix . "user_group
				WHERE group_id NOT IN ($delete_ug_sql)";
			$db->sql_query($sql);
		}

		$sql = "DELETE FROM " . $table_prefix . "groups
			WHERE group_single_user = 1";
		$db->sql_query($sql);

		unset($delete_ug_sql);

		//
		// Set a founder admin ... we'll assume it's the first user with admin level access
		//
		$sql = "UPDATE " . $table_prefix . "users
			SET user_founder = 1
			WHERE user_id = $founder_id";
		$db->sql_query($sql);

		//
		// Add all auth options to auth_options table
		//
		$all_options = array_merge($auth_options, array_merge($auth_mod_options, $auth_admin_options));
		$option_ids = array();

		foreach ( $all_options as $value )
		{
			$sql = "INSERT INTO " . $table_prefix . "auth_options (auth_value)
				VALUES ('$value')";
			$db->sql_query($sql);

			$option_ids[$value] = $db->sql_nextid();
		}

		foreach ( $new_groups as $k => $sql )
		{
			$db->sql_query($sql);
			$$k = $db->sql_nextid();
		}

		//
		// Update user_group table
		//
		$sql = "INSERT INTO " . $table_prefix . "user_group (user_id, group_id)
			SELECT user_id, $guest_id
				FROM " . $table_prefix . "users
				WHERE user_id = " . ANONYMOUS;
		$db->sql_query($sql);

		$sql = "INSERT INTO " . $table_prefix . "user_group (user_id, group_id)
			SELECT user_id, $reg_inactive_id
				FROM " . $table_prefix . "users
				WHERE user_id <> " . ANONYMOUS . "
					AND user_active <> 1";
		$db->sql_query($sql);

		$sql = "INSERT INTO " . $table_prefix . "user_group (user_id, group_id)
			SELECT user_id, $reg_id
				FROM " . $table_prefix . "users
				WHERE user_id <> " . ANONYMOUS . "
					AND user_active = 1";
		$db->sql_query($sql);

		$sql = "INSERT INTO " . $table_prefix . "user_group (user_id, group_id)
			SELECT user_id, $admin_id
				FROM " . $table_prefix . "users
				WHERE user_level = 1";
		$db->sql_query($sql);

		//
		// Construct equivalence entries
		//
		$group_acl = array();
		$user_acl = array();

		foreach ( $forum_access as $forum )
		{
			foreach( $auth_map as $k => $v )
			{
				switch ( $forum[$k] )
				{
					case AUTH_ALL:
						$group_acl[$guest_id][$forum['forum_id']][$v] = ACL_ALLOW;
						$group_acl[$reg_inactive_id][$forum['forum_id']][$v] = ACL_ALLOW;
						$group_acl[$reg_id][$forum['forum_id']][$v] = ACL_ALLOW;
						break;

					case AUTH_REG:
						$group_acl[$reg_id][$forum['forum_id']][$v] = ACL_ALLOW;
						break;

					case AUTH_ACL:
						foreach( $group_access as $forum_id => $access )
						{
							if ( $forum_id == $forum['forum_id'] )
							{
								if ( !empty($access[$k]) )
								{
									$group_acl[$access['group_id']][$forum['forum_id']][$v] = ACL_ALLOW;
								}
							}
						}

						foreach( $user_access as $forum_id => $access )
						{
							if ( $forum_id == $forum['forum_id'] )
							{
								if ( !empty($access[$k]) )
								{
									$user_acl[$access['user_id']][$forum['forum_id']][$v] = ACL_ALLOW;
								}
							}
						}
						break;

					case AUTH_MOD:
						$group_acl[$super_mod_id][$forum['forum_id']][$v] = ACL_ALLOW;

						foreach( $group_access as $forum_id => $access )
						{
							if ( $forum_id == $forum['forum_id'] )
							{
								if ( !empty($access[$k]) )
								{
									$group_acl[$access['group_id']][$forum['forum_id']][$v] = ACL_ALLOW;
								}
							}
						}

						foreach( $user_access as $forum_id => $access )
						{
							if ( $forum_id == $forum['forum_id'] )
							{
								if ( !empty($access[$k]) )
								{
									$user_acl[$access['user_id']][$forum['forum_id']][$v] = ACL_ALLOW;
								}
							}
						}
						break;
				}
			}
		}

		//
		// First add admin access, phpBB 2.0.x stored admin info in the user_level
		// field. We'll pull all user_id's with ADMIN user_level and add them to the
		// auth_user table. By default all admin options (bar clear logs) are enabled
		//
		$sql_ary = array();
		$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_groups (group_id, forum_id, auth_option_id, auth_allow_deny)
			SELECT $admin_id, 0, auth_option_id, " . ACL_ALLOW . "
				FROM " . $table_prefix . "auth_options
				WHERE auth_value LIKE 'admin_%'
					AND auth_value NOT LIKE 'admin_clearlogs'";

		$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_groups (group_id, forum_id, auth_option_id, auth_allow_deny)
			SELECT $admin_id, 0, auth_option_id, " . ACL_PERMIT . "
				FROM " . $table_prefix . "auth_options
				WHERE auth_value LIKE 'forum_%'";

		$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_groups (group_id, forum_id, auth_option_id, auth_allow_deny)
			SELECT $admin_id, 0, auth_option_id, " . ACL_PERMIT . "
				FROM " . $table_prefix . "auth_options
				WHERE auth_value LIKE 'mod_%'";

		//
		// Do Moderators
		//
		foreach( $user_access as $forum_id => $access )
		{
			if ( !empty($access['auth_mod']) )
			{
				$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_users (user_id, forum_id, auth_option_id, auth_allow_deny)
					SELECT " . $access['user_id'] . ", $forum_id, auth_option_id, " . ACL_ALLOW . "
						FROM " . $table_prefix . "auth_options
						WHERE auth_value LIKE 'mod_%'
							AND auth_value NOT LIKE 'mod_auth'";
				$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_users (user_id, forum_id, auth_option_id, auth_allow_deny)
					SELECT " . $access['user_id'] . ", $forum_id, auth_option_id, " . ACL_PERMIT . "
						FROM " . $table_prefix . "auth_options
						WHERE auth_value LIKE 'forum_%'";
			}
		}

		foreach( $group_access as $forum_id => $access )
		{
			if ( !empty($access['auth_mod']) )
			{
				$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_groups (group_id, forum_id, auth_option_id, auth_allow_deny)
					SELECT " . $access['group_id'] . ", $forum_id, auth_option_id, " . ACL_ALLOW . "
						FROM " . $table_prefix . "auth_options
						WHERE auth_value LIKE 'mod_%'
							AND auth_value NOT LIKE 'mod_auth'";
				$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_groups (group_id, forum_id, auth_option_id, auth_allow_deny)
					SELECT " . $access['group_id'] . ", $forum_id, auth_option_id, " . ACL_PERMIT . "
						FROM " . $table_prefix . "auth_options
						WHERE auth_value LIKE 'forum_%'";
			}
		}

		//
		// Rest of access list
		//
		foreach ( $user_acl as $user_id => $user_acl_ary )
		{
			foreach ( $user_acl_ary as $forum_id => $auth )
			{
				foreach ( $auth as $auth_value => $allow )
				{
					$auth_option_id = $option_ids[$auth_value];
					$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_users (user_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($user_id, $forum_id, $auth_option_id, $allow)";
				}
			}
		}

		foreach ( $group_acl as $group_id => $group_acl_ary )
		{
			foreach ( $group_acl_ary as $forum_id => $auth )
			{
				foreach ( $auth as $auth_value => $allow )
				{
					$auth_option_id = $option_ids[$auth_value];
					$sql_ary[] = "INSERT INTO " . $table_prefix . "auth_groups (group_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($group_id, $forum_id,  $auth_option_id, $allow)";
				}
			}
		}

		foreach ( $sql_ary as $sql )
		{
			$db->sql_query($sql);
		}

		gen_str_ok();

	case '2.1.0 [20020816]':

		$sql_ary = array();

		gen_str_init('* Updating <b>post checksums</b>');

		$sql_ary[] = "UPDATE " . $table_prefix . "posts_text
			SET post_checksum = MD5(post_text)";

		foreach ( $sql_ary as $sql )
		{
			$db->sql_query($sql);
		}

		gen_str_ok();
		gen_str_init('* Updating <b>forum post info</b>');

		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id
					FROM " . $table_prefix . "forums f, " . $table_prefix . "posts p, " . $table_prefix . "users u
					WHERE p.post_id = f.forum_last_post_id(+)
						AND u.user_id = p.poster_id(+)";
				break;

			default:
				$sql = "SELECT f.forum_id, p.post_time, p.post_username, u.username, u.user_id
					FROM (( " . $table_prefix . "forums f
					LEFT JOIN " . $table_prefix . "posts p ON p.post_id = f.forum_last_post_id )
					LEFT JOIN " . $table_prefix . "users u ON u.user_id = p.poster_id )";
				break;
		}
		$result = $db->sql_query($sql);

		$sql_ary = array();
		while ( $row = $db->sql_fetchrow($result) )
		{
			$forum_id = $row['forum_id'];

			$sql_ary[] = "UPDATE " . $table_prefix . "forums
				SET forum_last_poster_id = " . ( ( $row['user_id'] ) ? $row['user_id'] : ANONYMOUS ) . ", forum_last_poster_name = '" . addslashes($row['post_username']) . "', forum_last_post_time = " . $row['post_time'] . "
				WHERE forum_id = $forum_id";

			$sql = "SELECT t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_username, p2.post_username AS post_username2, p2.post_time
				FROM " . $table_prefix . "topics t, " . $table_prefix . "users u, " . $table_prefix . "posts p, " . $table_prefix . "posts p2, " . $table_prefix . "users u2
				WHERE t.forum_id = $forum_id
					AND u.user_id = t.topic_poster
					AND p.post_id = t.topic_first_post_id
					AND p2.post_id = t.topic_last_post_id
					AND u2.user_id = p2.poster_id";
			$result2 = $db->sql_query($sql);

			while ( $row2 = $db->sql_fetchrow($result2) )
			{
				$sql_ary[] = "UPDATE " . $table_prefix . "topics
					SET topic_first_poster_name = '" . addslashes($row2['post_username']) . "', topic_last_poster_id = " . ( ( $row2['id2'] ) ? $row2['id2'] : ANONYMOUS ) . ", topic_last_post_time = " . $row2['post_time'] . ", topic_last_poster_name = '" . addslashes($row2['post_username2']) . "'
					WHERE topic_id = " . $row2['topic_id'];
			}
			$db->sql_freeresult($result2);

			unset($row2);
		}
		$db->sql_freeresult($result);

		foreach ( $sql_ary as $sql )
		{
			$db->sql_query($sql);
		}

		gen_str_ok();

	case '2.1.0 [20020817]':

		$sql = "INSERT INTO phpbb_config (config_name, config_value)
			VALUES ('ip_check', '4')";
		$db->sql_query($sql);

	default;
		print "<span class=\"updtext\">* No updates needed</span><br />\n";
}

$sql = "UPDATE " . $table_prefix . "config
	SET config_value = '$version'
	WHERE config_name = 'version'";
$result = $db->sql_query($sql);

?>

<br clear="all" />

<span class="updtext">* Update completed to version <?php echo $version; ?></span>

<br clear="all" /><br />

<?php

page_footer();
exit;

//
// END MAIN PROG
//

// ---------------
// Begin functions
//

//
// Decode encoded IPs
//
function decode_ip($int_ip)
{
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}

//
// Common page header
//
function page_header()
{

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="../admin/subSilver.css" type="text/css">
<style type="text/css">
<!--
th		{ background-image: url('../admin/images/cellpic3.gif') }
td.cat	{ background-image: url('../admin/images/cellpic1.gif') }

h1 { margin-left:15px }

.ok { font-family: 'Courier New',courier; color:green; font-size:10pt }
.skip { font-family: 'Courier New',courier; color:blue; font-size:10pt }
.updtext { font-family: 'Courier New', courier; font-size:10pt;margin-left:25px}

//-->
</style>
<title>Update Script</title>
</head>
<body>

<a name="top"></a>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><img src="../admin/images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></td>
		<td width="100%" background="../admin/images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle">Update Script</span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<br clear="all" /><br />

<?
	return;
}

//
// Common page footer
//
function page_footer()
{
?>

<div align="center"><span class="copyright">Powered by phpBB &copy; 2002 <a href="http://www.phpbb.com/" target="_phpbb" class="copyright">phpBB Group</a></span></div>

<br clear="all" />

</body>
</html>
<?
	return;
}

function gen_str_init($str)
{
	print '<span class="updtext">' . str_pad($str . ' ', 60, '.') . ' </span>';
	flush();
}

function gen_str_ok()
{
	print "<span class=\"ok\"><b>OK</b></span><br />\n";
	flush();
}

function gen_str_skip()
{
	print "<span class=\"skip\"><b>SKIPPED</b></span><br />\n";
	flush();
}

//
//
//
function get_schema()
{
	global $table_prefix, $dbms;

	$schemafile = file('schemas/mysql_schema.sql');
	$tabledata = 0;

	for($i=0; $i < count($schemafile); $i++)
	{
		$line = $schemafile[$i];

		if ( preg_match('/^CREATE TABLE (\w+)/i', $line, $matches) )
		{
			// Start of a new table definition, set some variables and go to the next line.
			$tabledata = 1;
			// Replace the 'phpbb_' prefix by the user defined prefix.
			$table = str_replace('phpbb_', $table_prefix, $matches[1]);
			$table_def[$table] = "CREATE TABLE $table (\n";
			continue;
		}

		if ( preg_match('/^\);/', $line) )
		{
			// End of the table definition
			// After this we will skip everything until the next 'CREATE' line
			$tabledata = 0;
			$table_def[$table] .= ')'; // We don't need the closing semicolon
		}

		if ( $tabledata == 1 )
		{
			// We are inside a table definition, parse this line.
			// Add the current line to the complete table definition:
			$table_def[$table] .= $line;
			if ( preg_match('/^\s*(\w+)\s+(\w+)\(([\d,]+)\)(.*)$/', $line, $matches) )
			{
				// This is a column definition
				$field = $matches[1];
				$type = $matches[2];
				$size = $matches[3];

				preg_match('/DEFAULT (NULL|\'.*?\')[,\s](.*)$/i', $matches[4], $match);
				$default = $match[1];

				$notnull = ( preg_match('/NOT NULL/i', $matches[4]) ) ? 1 : 0;
				$auto_increment = ( preg_match('/auto_increment/i', $matches[4]) ) ? 1 : 0;

				$field_def[$table][$field] = array(
					'type' => $type,
					'size' => $size,
					'default' => $default,
					'notnull' => $notnull,
					'auto_increment' => $auto_increment
				);
			}

			if ( preg_match('/\s*PRIMARY\s+KEY\s*\((.*)\).*/', $line, $matches) )
			{
				// Primary key
				$key_def[$table]['PRIMARY'] = $matches[1];
			}
			else if ( preg_match('/\s*KEY\s+(\w+)\s*\((.*)\)/', $line, $matches) )
			{
				// Normal key
				$key_def[$table][$matches[1]] = $matches[2];
			}
			else if ( preg_match('/^\s*(\w+)\s*(.*?),?\s*$/', $line, $matches) )
			{
				// Column definition
				$create_def[$table][$matches[1]] = $matches[2];
			}
			else
			{
				// It's a bird! It's a plane! It's something we didn't expect ;(
			}
		}
	}

	$schema['field_def'] = $field_def;
	$schema['table_def'] = $table_def;
	$schema['create_def'] = $create_def;
	$schema['key_def'] = $key_def;

	return $schema;
}

//
//
//
function get_inserts()
{
	global $table_prefix, $dbms;

	$insertfile = file('schemas/mysql_basic.sql');

	for($i = 0; $i < count($insertfile); $i++)
	{
		if ( preg_match('/(INSERT INTO (\w+)\s.*);/i', str_replace('phpbb_', $table_prefix, $insertfile[$i]), $matches) )
		{
			$returnvalue[$matches[2]][] = $matches[1];
		}
	}

	return $returnvalue;
}
//
// End functions
// -------------

?>