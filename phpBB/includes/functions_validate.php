<?php
/***************************************************************************
 *                          functions_validate.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

//
// Check to see if the username has been taken, or if it is disallowed.
// Also checks if it includes the " character, which we don't allow in usernames.
// Used for registering, changing names, and posting anonymously with a username
//
function validate_username($username)
{
	global $db, $lang, $userdata;

	$username = str_replace("\'", "''", $username);

	$sql = "SELECT username 
		FROM " . USERS_TABLE . " 
		WHERE LOWER(username) = '" . strtolower($username) . "'";
	if ( $result = $db->sql_query($sql) )
	{
		if ( $row = $db->sql_fetchrow($result) )
		{
			if ( ( $userdata['session_logged_in'] && $row['username'] != $userdata['username'] ) || !$userdata['session_logged_in'] )
			{
				return array('error' => true, 'error_msg' => $lang['Username_taken']);
			}
		}
	}

	$sql = "SELECT group_name
		FROM " . GROUPS_TABLE . " 
		WHERE LOWER(group_name) = '" . strtolower($username) . "'";
	if ( $result = $db->sql_query($sql) )
	{
		if ( $row = $db->sql_fetchrow($result) )
		{
			return array('error' => true, 'error_msg' => $lang['Username_taken']);
		}
	}

	$sql = "SELECT disallow_username
		FROM " . DISALLOW_TABLE;
	if ( $result = $db->sql_query($sql) )
	{
		while( $row = $db->sql_fetchrow($result) )
		{
			if ( preg_match("#\b(" . str_replace("\*", ".*?", preg_quote($row['disallow_username'])) . ")\b#i", $username) )
			{
				return array('error' => true, 'error_msg' => $lang['Username_disallowed']);
			}
		}
	}

	$sql = "SELECT word 
		FROM  " . WORDS_TABLE;
	if ( $result = $db->sql_query($sql) )
	{
		while( $row = $db->sql_fetchrow($result) )
		{
			if ( preg_match("#\b(" . str_replace("\*", ".*?", preg_quote($row['word'])) . ")\b#i", $username) )
			{
				return array('error' => true, 'error_msg' => $lang['Username_disallowed']);
			}
		}
	}

	// Don't allow " in username.
	if ( strstr($username, '"') )
	{
		return array('error' => true, 'error_msg' => $lang['Username_invalid']);
	}

	return array('error' => false, 'error_msg' => '');
}

//
// Check to see if email address is banned
// or already present in the DB
//
function validate_email($email)
{
	global $db, $lang;

	if ( $email != '' )
	{
		if ( preg_match('/^[a-z0-9\.\-_\+]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is', $email) )
		{
			$sql = "SELECT ban_email
				FROM " . BANLIST_TABLE;
			if ( $result = $db->sql_query($sql) )
			{
				while( $row = $db->sql_fetchrow($result) )
				{
					$match_email = str_replace('*', '.*?', $row['ban_email']);
					if ( preg_match('/^' . $match_email . '$/is', $email) )
					{
						return array('error' => true, 'error_msg' => $lang['Email_banned']);
					}
				}
			}

			$sql = "SELECT user_email
				FROM " . USERS_TABLE . "
				WHERE user_email = '" . str_replace("\'", "''", $email) . "'";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, "Couldn't obtain user email information.", "", __LINE__, __FILE__, $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				return array('error' => true, 'error_msg' => $lang['Email_taken']);
			}

			return array('error' => false, 'error_msg' => '');
		}
	}

	return array('error' => true, 'error_msg' => $lang['Email_invalid']);
}

//
// Does supplementary validation of optional profile fields. This expects common stuff like trim() and strip_tags()
// to have already been run. Params are passed by-ref, so we can set them to the empty string if they fail.
//
function validate_optional_fields(&$icq, &$aim, &$msnm, &$yim, &$website, &$location, &$occupation, &$interests, &$sig)
{
	$check_var_length = array('aim', 'msnm', 'yim', 'location', 'occupation', 'interests', 'sig');

	for($i = 0; $i < count($check_var_length); $i++)
	{
		if ( strlen($$check_var_length[$i]) < 2 )
		{
			$$check_var_length[$i] = '';
		}
	}

	// ICQ number has to be only numbers.
	if ( !preg_match('/^[0-9]+$/', $icq) )
	{
		$icq = '';
	}
	
	// website has to start with http://, followed by something with length at least 3 that
	// contains at least one dot.
	if ( $website != "" )
	{
		if ( !preg_match('#^http:\/\/#i', $website) )
		{
			$website = 'http://' . $website;
		}

		if ( !preg_match('#^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $website) )
		{
			$website = '';
		}
	}

	return;
}

?>
