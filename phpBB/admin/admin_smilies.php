<?php
/***************************************************************************
*                             admin_smilies.php 
*                              -------------------
*     begin                : Thu May 31, 2001
*     copyright            : (C) 2001 The phpBB Group        
*     email                : support@phpbb.com                           
* 
*     $Id$
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

/**************************************************************************
*	This file will be used for modifying the smiley settings for a board.
**************************************************************************/

//
// First we do the setmodules stuff for the admin cp.
//
if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['General']['Smilies'] = $filename;

	return;
}

//
// Include required files register $phpEx, and check permisions 
//
require('pagestart.inc'); 

//
// Check to see what mode we should operate in.
//
$mode = ($HTTP_GET_VARS['mode']) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];
switch($mode)
{
	//
	// Admin has selected to delete a smiley.
	//
	case 'delete':
		//
		// Get the data that should be passed.
		//
		$smiley_id = ($HTTP_GET_VARS['id']) ? $HTTP_GET_VARS['id']: $HTTP_POST_VARS['id'];
		$sql = 'DELETE FROM ' . SMILIES_TABLE . ' 
			WHERE smilies_id = ' . $smiley_id;
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, $lang['smile_remove_err'], "", __LINE__, __FILE__, $sql);
		}
		$template->set_filenames(array(
			"body" => "admin/smile_action.tpl")
		);
		$template->assign_vars(array(
			"S_SMILEY_URL" => append_sid("admin_smilies.$phpEx"),
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_TEXT" => $lang['smiley_return'],
			"L_SMILEY_ACTION" => $lang['smiley_del_success'])
		);
		//
		// Spit out some feedback to the user.
		//
		$template->pparse("body");
		break;
	//
	// Admin has selected to edit a smiley.
	//
	case 'edit':
		//
		// Get the data for the selected smiley.
		//
		$smiley_id = ($HTTP_GET_VARS['id']) ? $HTTP_GET_VARS['id']: $HTTP_POST_VARS['id'];
		$sql = 'SELECT * 
			FROM ' . SMILIES_TABLE . ' 
			WHERE smilies_id = ' . $smiley_id;
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, $lang['smile_edit_err'], "", __LINE__, __FILE__, $sql);
		}
		$template->set_filenames(array(
			"body" => "admin/smile_edit.tpl")
		);
		$smile_data = $db->sql_fetchrow($result);
		$template->assign_vars(array(
			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"),
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_INSTR" => $lang['smile_instr'],
			"L_SMILEY_CODE_LBL" => $lang['smiley_code'],
			"L_SMILEY_URL_LBL" => $lang['smiley_url'],
			"L_SMILEY_EMOTION_LBL" => $lang['smiley_emot'],
			"L_SUBMIT" => $lang['Submit_changes'],
			"L_RESET" => $lang['Reset_changes'],
			"SMILEY_CODE_VAL" => $smile_data['code'],
			"SMILEY_ID_VAL" => $smile_data['smilies_id'],
			"SMILEY_URL_VAL" => $smile_data['smile_url'],
			"SMILEY_EMOTION" => $smile_data['emoticon'],
			"S_HIDDEN_VAR" => "save")
		);
		//
		// Spit out the edit form.
		//
		$template->pparse("body");
		break;
	//
	// Admin has selected to add a smiley.
	//
	case "add":
		$template->set_filenames(array(
			"body" => "admin/smile_edit.tpl")
		);
		$template->assign_vars(array(
			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"),
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_CONFIG" => $lang['smiley_config'],
			"L_SMILEY_INSTR" => $lang['smiley_instr'],
			"L_SMILEY_CODE_LBL" => $lang['smiley_code'],
			"L_SMILEY_URL_LBL" => $lang['smiley_url'],
			"L_SMILEY_EMOTION_LBL" => $lang['smiley_emot'],
			"L_SUBMIT" => $lang['Submit_changes'],
			"L_RESET" => $lang['Reset_changes'],
			"S_HIDDEN_VAR" => "savenew")
		);
		//
		// Spit out the add form.
		//
		$template->pparse("body");
		break;
	//
	// Admin has submitted changes while editing a smiley.
	//
	case "save":
		//
		// Get the submitted data, being careful to ensure that we only
		// accept the data we are looking for.
		//
		$smile_code = ($HTTP_POST_VARS['smile_code']) ? $HTTP_POST_VARS['smile_code'] : $HTTP_GET_VARS['smile_code'];
		$smile_url = ($HTTP_POST_VARS['smile_url']) ? $HTTP_POST_VARS['smile_url'] : $HTTP_GET_VARS['smile_url'];
		$smile_emotion = ($HTTP_POST_VARS['smile_emotion']) ? $HTTP_POST_VARS['smile_emotion'] : $HTTP_GET_VARS['smile_emotion'];
		$smile_id = intval(($HTTP_POST_VARS['smile_id']) ? $HTTP_POST_VARS['smile_id'] : $HTTP_GET_VARS['smile_id']);

		//
		// Proceed with updating the smiley table.
		//
		$sql = 'UPDATE ' . SMILIES_TABLE . " 
			SET code='$smile_code', smile_url='$smile_url', emoticon='$smile_emotion' 
			WHERE smilies_id = $smile_id";
		$result = $db->sql_query($sql);
		if( !$result ) 
		{
			message_die(GENERAL_ERROR, $lang['smile_edit_err'], "", __LINE__, __FILE__, $sql);
		}
		$template->set_filenames(array(
			"body" => "admin/smile_action.tpl")
		);
		
		$template->assign_vars(array(
			"S_SMILEY_URL" => append_sid("admin_smilies.$phpEx"),
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_TEXT" => $lang['smiley_return'],
			"L_SMILEY_ACTION" => $lang['smiley_edit_success'])
		);
		//
		// Spit out a results page..
		//
		$template->pparse("body");
		break;
	//
	// Admin has submitted changes while adding a new smiley.
	//
	case "savenew":
		//
		// Get the submitted data being careful to ensure the the data
		// we recieve and process is only the data we are looking for.
		//
		$smile_code = ($HTTP_POST_VARS['smile_code']) ? $HTTP_POST_VARS['smile_code'] : $HTTP_GET_VARS['smile_code'];
		$smile_url = ($HTTP_POST_VARS['smile_url']) ? $HTTP_POST_VARS['smile_url'] : $HTTP_GET_VARS['smile_url'];
		$smile_emotion = ($HTTP_POST_VARS['smile_emotion']) ? $HTTP_POST_VARS['smile_emotion'] : $HTTP_GET_VARS['smile_emotion'];

		//
		// Save the data to the smiley table.
		//
		$sql = 'INSERT INTO ' . SMILIES_TABLE . " (code, smile_url, emoticon) 
			VALUES ('$smile_code', '$smile_url', '$smile_emotion')";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, $lang['smile_edit_err'], "", __LINE__, __FILE__, $sql);
		}
		$template->set_filenames(array(
			"body" => "admin/smile_action.tpl")
		);
		$template->assign_vars(array(
			"S_SMILEY_URL" => append_sid("admin_smilies.$phpEx"),
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_TEXT" => $lang['smiley_return'],
			"L_SMILEY_ACTION" => $lang['smiley_add_success'])
		);
		//
		// Spit out a results page.
		//
		$template->pparse("body");
		break;
	//
	// This is the main display of the page before the admin has selected
	// any options.
	//
	default:
		//
		// Get a listing of smileys.
		//
		$sql = 'SELECT * 
			FROM ' . SMILIES_TABLE;
		$result = $db->sql_query($sql);
		if( !$result )
		{	
			message_die(GENERAL_ERROR, $lang['smile_load_err'], "", __LINE__, __FILE__, $sql);
		}
		$smilies = $db->sql_fetchrowset($result);
		$total_smilies = $db->sql_numrows($result);
		$template->set_filenames(array(
			"body" => "admin/admin_smile.tpl")
		);
		//
		// Set the main text variables for the page.
		//
		$my_path = append_sid("admin_smilies.$phpEx");
		if(!ereg('\?', $my_path))
		{
			$my_path .= '?';  
		}
		$template->assign_vars(array(
			"S_SMILEY_URL" => $my_path,
			"S_SMILEY_BASEDIR" => $phpbb_root_path . '/' . $board_config['smilies_path'],
			"L_ACTION" => $lang['Action'],
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_TEXT" => $lang['smile_desc'],
			"L_DELETE" => $lang['Delete'],
			"L_EDIT" => $lang['Edit'],
			"L_SMILEY_ADD" => $lang['smile_add'],
			"L_CODE" => $lang['Code'],
			"L_EMOT" => $lang['Emotion'],
			"L_SMILE" => $lang['Smile'])
		);
		//
		// Loop throuch the rows of smilies setting block vars for the template.
		//
		for( $i = 0; $i < $total_smilies; $i++ )
		{
			$template->assign_block_vars("smiles", array(
				"ID" => $smilies[$i]['smilies_id'],
				"CODE" => $smilies[$i]['code'],
				"URL" => $smilies[$i]['smile_url'],
				"EMOT" => $smilies[$i]['emoticon'])
			);
		}
		//
		// Spit out the page.
		//
		$template->pparse("body");
		break;
}
//
// Page Footer
//
include('page_footer_admin.'.$phpEx);

?>