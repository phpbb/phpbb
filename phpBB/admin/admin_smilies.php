<?php
/***************************************************************************
*                               admin_smilies.php
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

//
// Read a listing of uploaded smilies for use in the add or edit smliey code...
//
$dir = opendir($phpbb_root_path . $board_config['smilies_path']);
while($file = readdir($dir))
{
	if(!is_dir($phpbb_root_path . $board_config['smilies_path'] . '/' . $file))
	{
		$smiley_images[] = $file;
	}
}

switch($mode)
{
	case 'delete':
		//
		// Admin has selected to delete a smiley.
		//

		$smiley_id = ( !empty($HTTP_GET_VARS['id']) ) ? $HTTP_GET_VARS['id'] : $HTTP_POST_VARS['id'];

		$sql = "DELETE FROM " . SMILIES_TABLE . "
			WHERE smilies_id = " . $smiley_id;
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, $lang['smile_remove_err'], "", __LINE__, __FILE__, $sql);
		}

		$template->set_filenames(array(
			"body" => "admin/smile_result_body.tpl")
		);

		$template->assign_vars(array(
			"U_SMILEY_ADMIN" => append_sid("admin_smilies.$phpEx"),

			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_TEXT" => $lang['smiley_return'],
			"L_SMILEY_ACTION" => $lang['smiley_del_success'])
		);
		//
		// Spit out some feedback to the user.
		//
		$template->pparse("body");
		break;

	case 'edit':
		//
		// Admin has selected to edit a smiley.
		//

		$smiley_id = ( !empty($HTTP_GET_VARS['id']) ) ? $HTTP_GET_VARS['id'] : $HTTP_POST_VARS['id'];

		$sql = "SELECT *
			FROM " . SMILIES_TABLE . "
			WHERE smilies_id = " . $smiley_id;
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, $lang['smile_edit_err'], "", __LINE__, __FILE__, $sql);
		}
		$smile_data = $db->sql_fetchrow($result);

		$filename_list = "";
		for( $i = 0; $i < count($smiley_images); $i++ )
		{
			if( $smiley_images[$i] == $smile_data['smile_url'] )
			{
				$smiley_selected = "selected=\"selected\"";
				$smiley_edit_img = $smiley_images[$i];
			}
			else
			{
				$smiley_selected = "";
			}

			$filename_list .= '<option value="' . $smiley_images[$i] . '"' . $smiley_selected . '>' . $smiley_images[$i] . '</option>';
		}

		$template->set_filenames(array(
			"body" => "admin/smile_edit_body.tpl")
		);

		$s_hidden_fields = '<input type="hidden" name="mode" value="save" /><input type="hidden" name="smile_id" value="' . $smile_data['smilies_id'] . '" />';

		$template->assign_vars(array(
			"SMILEY_CODE" => $smile_data['code'],
			"SMILEY_EMOTICON" => $smile_data['emoticon'],

			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_CONFIG" => $lang['smiley_config'],
			"L_SMILEY_EXPLAIN" => $lang['smiley_instr'],
			"L_SMILEY_CODE" => $lang['smiley_code'],
			"L_SMILEY_URL" => $lang['smiley_url'],
			"L_SMILEY_EMOTION" => $lang['smiley_emot'],
			"L_SUBMIT" => $lang['Submit_changes'],
			"L_RESET" => $lang['Reset_changes'],

			"SMILEY_IMG" => $phpbb_root_path . '/' . $board_config['smilies_path'] . '/' . $smiley_edit_img, 

			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_FILENAME_OPTIONS" => $filename_list, 
			"S_SMILEY_BASEDIR" => $phpbb_root_path . '/' . $board_config['smilies_path'])
		);

		$template->pparse("body");
		break;

	case "add":
		//
		// Admin has selected to add a smiley.
		//

		$template->set_filenames(array(
			"body" => "admin/smile_edit_body.tpl")
		);

		$filename_list = "";
		for( $i = 0; $i < count($smiley_images); $i++ )
		{
			$filename_list .= '<option value="' . $smiley_images[$i] . '">' . $smiley_images[$i] . '</option>';
		}

		$s_hidden_fields = '<input type="hidden" name="mode" value="savenew" />';

		$template->assign_vars(array(
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_CONFIG" => $lang['smiley_config'],
			"L_SMILEY_EXPLAIN" => $lang['smiley_instr'],
			"L_SMILEY_CODE" => $lang['smiley_code'],
			"L_SMILEY_URL" => $lang['smiley_url'],
			"L_SMILEY_EMOTION" => $lang['smiley_emot'],
			"L_SUBMIT" => $lang['Submit_changes'],
			"L_RESET" => $lang['Reset_changes'],

			"SMILEY_IMG" => $phpbb_root_path . '/' . $board_config['smilies_path'] . '/' . $smiley_images[0], 

			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"), 
			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_FILENAME_OPTIONS" => $filename_list, 
			"S_SMILEY_BASEDIR" => $phpbb_root_path . '/' . $board_config['smilies_path'])
		);

		$template->pparse("body");
		break;

	case "save":
		//
		// Admin has submitted changes while editing a smiley.
		//

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
		$sql = "UPDATE " . SMILIES_TABLE . "
			SET code = '$smile_code', smile_url = '$smile_url', emoticon = '$smile_emotion'
			WHERE smilies_id = $smile_id";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, $lang['smile_edit_err'], "", __LINE__, __FILE__, $sql);
		}

		$template->set_filenames(array(
			"body" => "admin/smile_result_body.tpl")
		);

		$template->assign_vars(array(
			"U_SMILEY_ADMIN" => append_sid("admin_smilies.$phpEx"), 

			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_TEXT" => $lang['smiley_return'],
			"L_SMILEY_ACTION" => $lang['smiley_edit_success'])
		);

		$template->pparse("body");
		break;

	case "savenew":
		//
		// Admin has submitted changes while adding a new smiley.
		//

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
		$sql = "INSERT INTO " . SMILIES_TABLE . " (code, smile_url, emoticon)
			VALUES ('$smile_code', '$smile_url', '$smile_emotion')";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, $lang['smile_edit_err'], "", __LINE__, __FILE__, $sql);
		}

		$template->set_filenames(array(
			"body" => "admin/smile_result_body.tpl")
		);

		$template->assign_vars(array(
			"U_SMILEY_ADMIN" => append_sid("admin_smilies.$phpEx"), 

			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_TEXT" => $lang['smiley_return'],
			"L_SMILEY_ACTION" => $lang['smiley_add_success'])
		);

		$template->pparse("body");
		break;

	default:
		//
		// This is the main display of the page before the admin has selected
		// any options.
		//
		$sql = "SELECT *
			FROM " . SMILIES_TABLE;
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, $lang['smile_load_err'], "", __LINE__, __FILE__, $sql);
		}

		$smilies = $db->sql_fetchrowset($result);

		$template->set_filenames(array(
			"body" => "admin/smile_list_body.tpl")
		);

		$s_hidden_fields = '<input type="hidden" name="mode" value="add">';

		$template->assign_vars(array(
			"L_ACTION" => $lang['Action'],
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_TEXT" => $lang['smile_desc'],
			"L_DELETE" => $lang['Delete'],
			"L_EDIT" => $lang['Edit'],
			"L_SMILEY_ADD" => $lang['smile_add'],
			"L_CODE" => $lang['Code'],
			"L_EMOT" => $lang['Emotion'],
			"L_SMILE" => $lang['Smile'], 
			
			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"))
		);

		//
		// Loop throuh the rows of smilies setting block vars for the template.
		//
		for($i = 0; $i < count($smilies); $i++)
		{
			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars("smiles", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"SMILEY_IMG" =>  $phpbb_root_path . '/' . $board_config['smilies_path'] . '/' . $smilies[$i]['smile_url'], 
				"CODE" => $smilies[$i]['code'],
				"EMOT" => $smilies[$i]['emoticon'],
				
				"U_SMILEY_EDIT" => append_sid("admin_smilies.$phpEx?mode=edit&amp;id=" . $smilies[$i]['smilies_id']), 
				"U_SMILEY_DELETE" => append_sid("admin_smilies.$phpEx?mode=delete&amp;id=" . $smilies[$i]['smilies_id']))
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