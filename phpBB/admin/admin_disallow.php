<?php
/***************************************************************************
 *                            admin_disallow.php
 *                            -------------------
 *   begin                : Tuesday, Oct 05, 2001
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
 ***************************************************************************/

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['Users']['Disallow'] = append_sid($filename);

	return;
}

//
// Include required files, get $phpEx and check permissions
//
$phpbb_root_dir = "./../";
require('pagestart.inc');

//
// Check to see what mode we shold operate in.
//
if( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = "";
}
$output_info = '';
switch( $mode )
{
	case $lang['Delete']:
		$disallowed_id = ( isset($HTTP_POST_VARS['disallowed_id']) ) ? intval( $HTTP_POST_VARS['disallowed_id'] ) : intval( $HTTP_GET_VARS['disallowed_id'] );
		
		$sql = 'DELETE FROM '.DISALLOW_TABLE.' WHERE disallow_id = '.$disallowed_id;
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Couldn't removed disallowed user.", "",__LINE__, __FILE__, $sql);
		}
		$output_info = $lang['disallowed_deleted'];
		break;
	case $lang['Add']:
		$disallowed_user = ( isset($HTTP_POST_VARS['disallowed_user']) ) ? $HTTP_POST_VARS['disallowed_user'] : $HTTP_GET_VARS['disallowed_user'];
		$disallowed_user = preg_replace( '/\*/', '%', $disallowed_user );
		if( !validate_username( $disallowed_user ) )
		{
			$output_info = $lang['disallowed_already'];
		}
		else
		{
			$sql = 'INSERT INTO '.DISALLOW_TABLE."(disallow_username) VALUES('".$disallowed_user."')";
			$result = $db->sql_query( $sql );
			if ( !$result )
			{
				message_die(GENERAL_ERROR, "Could not add disallowed user.", "",__LINE__, __FILE__, $sql);
			}
			$output_info = $lang['disallow_successful'];
		}
		break;
}
//
// Grab the current list of disallowed usernames...
//
$sql = 'SELECT * FROM '.DISALLOW_TABLE;
$result = $db->sql_query($sql);
if( !$result )
{
	message_die( GENERAL_ERROR, "Couldn't get disallowed users.", "", __LINE__, __FILE__, $sql );
}
$disallowed = $db->sql_fetchrowset($result);

//
// Ok now generate the info for the template, which will be put out no matter
// what mode we are in.
//
$disallow_select = "<SELECT NAME=\"disallowed_id\">";
if ( trim($disallowed) == '' )
{
	$disallow_select .= '<option value="">'.$lang['no_disallowed'].'</option>';
}
else 
{
	$disallow_select .= "<OPTION value=\"\">".$lang['Select'].' '.$lang['Username']."</OPTION>";
	$user = array();
	for( $i = 0; $i < count($disallowed); $i++ )
	{
		$disallowed[$i]['disallow_username'] = preg_replace( '/%/', '*', $disallowed[$i]['disallow_username']);
		$disallow_select .= '<option value="'.$disallowed[$i]['disallow_id'].'">'.$disallowed[$i]['disallow_username'].'</option>';
	}
}
$disallow_select .= '</SELECT>';
$template->set_filenames(array(
	"body" => "admin/disallow_body.tpl")
);
$template->assign_vars(array(
	"S_DISALLOW_SELECT" => $disallow_select,
	"L_INFO" => $output_info,
	"L_DISALLOW_TITLE" => $lang['Disallow_control'],
	"L_DELETE" => $lang['Delete'],
	"L_ADD" => $lang['Add'],
	"L_RESET" => $lang['Reset'],
	"S_FORM_ACTION" => append_sid('admin_disallow.php'),
	"L_EXPLAIN" => $lang['disallow_instructs'],
	"L_DEL_DISALLOW" => $lang['del_disallow'],
	"L_DEL_EXPLAIN" => $lang['del_disallow_explain'],
	"L_ADD_DISALLOW" => $lang['add_disallow'],
	"L_ADD_EXPLAIN" => $lang['add_disallow_explain'],
	"L_USERNAME" => $lang['Username'])
);
$template->pparse("body");
?>
