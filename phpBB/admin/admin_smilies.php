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
// Load default header
//
$phpbb_root_dir = "./../";
if( $HTTP_GET_VARS['mode'] == 'export' && $HTTP_GET_VARS['send_file'] == 1 )
{
	$no_page_header = 1;
}
require('pagestart.inc');
$delimeter  = '=+:';
//
// Check to see what mode we should operate in.
//
if( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = "";
}

//
// Read a listing of uploaded smilies for use in the add or edit smliey code...
//
$dir = @opendir($phpbb_root_path . $board_config['smilies_path']);

while($file = @readdir($dir))
{
	if( !@is_dir($phpbb_root_path . $board_config['smilies_path'] . '/' . $file) )
	{
		if( !is_null(@getimagesize($phpbb_root_path . $board_config['smilies_path'] . '/' . $file)) )
		{
			$smiley_images[] = $file;
		}
		elseif(eregi('.pak$', $file) )
		{	
			$smiley_paks[] = $file;
		}
	}
}

@closedir($dir);

//
// Select main mode
//
switch($mode)
{
	case 'import':
		//
		// Import a list a "Smiley Pack"
		//
		$smile_pak = ( !empty($HTTP_POST_VARS['smile_pak']) ) ? $HTTP_POST_VARS['smile_pak'] : $HTTP_GET_VARS['smile_pak'];
		$clear_current = ( !empty($HTTP_POST_VARS['clear_current']) ) ? $HTTP_POST_VARS['clear_current'] : $HTTP_GET_VARS['clear_current'];
		$replace_existing = ( ! empty($HTTP_POST_VARS['replace']) ) ? $HTTP_POST_VARS['replace'] : $HTTP_GET_VARS['replace'];
		if ( !empty($smile_pak) )
		{
			//
			// The user has already selected a smile_pak file.. Import it.
			//
			if( $clear_current == 'Y' )
			{
				$sql = "DELETE FROM " . SMILIES_TABLE;
				$result = $db->sql_query($sql);
				if( !$result )
		      {
    		     message_die(GENERAL_ERROR, "Couldn't delete current smilies", "", __LINE__, __FILE__, $sql);
      		}

			}
			else
			{
				$sql = "SELECT code FROM ". SMILIES_TABLE;
				$result = $db->sql_query($sql);
				if( !$result )
     			{
		         message_die(GENERAL_ERROR, "Couldn't get current smilies", "", __LINE__, __FILE__, $sql);
      		}

				$cur_smilies = $db->sql_fetchrowset($result);
				for( $i = 0; $i < count($cur_smilies); $i++ )
				{
					$k = $cur_smilies[$i]['code'];
					$smiles[$k] = 1;
				}
			}
			$fcontents = @file($phpbb_root_path . $board_config['smilies_path'] . '/'. $smile_pak);
			if( empty($fcontents) )
			{
				message_die(GENERAL_ERROR, "Couldn't read smiley pak file", "", __LINE__, __FILE__, $sql);
			}
			for( $i = 0; $i < count($fcontents); $i++ )
			{
				if( !get_magic_quotes_runtime() )
				{
					$fcontents[$i] = addslashes($fcontents[$i]);
				}
				$smile_data = explode($delimeter, trim($fcontents[$i]));
				for( $j = 2; $j < count($smile_data); $j++)
				{
					$k = $smile_data[$j];
					if( $smiles[$k] == 1 )
					{
						if( $replace_existing == 'Y' )
						{
							$sql = "UPDATE " . SMILIES_TABLE . " 
										SET smile_url = '$smile_data[0]',
											emoticon = '$smile_data[1]'
										WHERE code = '$smile_data[$j]'";
							
						}
						else
						{
							$sql = '';
						}
					}
					else
					{
						$sql = "INSERT INTO " . SMILIES_TABLE . " (
										code,
										smile_url,
										emoticon )
									VALUES(
										'$smile_data[$j]',
										'$smile_data[0]',
										'$smile_data[1]')";
							
					}
					if( $sql != '' )
					{
						$result = $db->sql_query($sql);
						if( !$result )
				      {
         				message_die(GENERAL_ERROR, "Couldn't update smilies!", "", __LINE__, __FILE__, $sql);
      				}

					}
				}
			}
			//
			// Ok the smilies have been imported do something...
			//
			$template->set_filenames(array(
				"body" => "admin/admin_message_body.tpl")
			);
			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['smiley_title'],
				"MESSAGE_TEXT" => $lang['smiley_import_success'])
			);
			$template->pparse("body");
			
		}
		else
		{
			//
			// Display the script to get the smile_pak cfg file...
			//
			$smile_paks_select = "<SELECT NAME='smile_pak'><option value=''>".$lang['Select_pak']."</option>";
			while( list($key, $value) = each($smiley_paks) )
			{
				if ( !empty($value) ) 
				{
					$smile_paks_select .= "<option>".$value."</option>";
				}
			}
			$smile_paks_select .= "</select>";
			$replace_opt = "<input type='radio' name='replace' value='Y'>&nbsp;".$lang['replace_existing'];
			$keep_opt = "<input type='radio' name='replace' value='N'>&nbsp;".$lang['keep_existing'];
			$del_exist = "<input type='checkbox' name='clear_current' value='Y'>";
			$hidden_vars = "<input type='hidden' name='mode' value='import'>";	
			$template->set_filenames(array(
				"body" => "admin/smile_import_body.tpl")
			);
			$template->assign_vars(array(
				"S_SMILEY_ACTION" => $PHPSELF,
				"L_SMILEY_TITLE" => $lang['smiley_title'],
				"L_SMILEY_EXPLAIN" => $lang['smiley_import_inst'],
				"L_SMILEY_IMPORT" => $lang['smiley_import'],
				"L_SELECT_LBL" => $lang['choose_smile_pak'],
				"S_SMILE_SELECT" => $smile_paks_select,
				"L_IMPORT" => $lang['import'],
				"L_CONFLICTS" => $lang['smile_conflicts'],
				"S_REPLACE_OPT" => $replace_opt,
				"S_KEEP_OPT" => $keep_opt,
				"L_DEL_EXISTING" => $lang['del_existing_smileys'],
				"S_DEL_EXISTING" => $del_exist,
				"S_HIDDEN_FIELDS" => $hidden_vars)
			);
			$template->pparse("body");
		}
		break;
		
	case 'export':
		//
		// Export our smiley config as a smiley pak...
		//
		if ( $send_file == 1 )
		{	
			$smile_pak = '';
			$sql = "SELECT * FROM " . SMILIES_TABLE;
			$result = $db->sql_query($sql);
			if( !$result )
	      {
  		   	message_die(GENERAL_ERROR, "Couldn't delete smiley", "", __LINE__, __FILE__, $sql);
      	}

			$resultset = $db->sql_fetchrowset($result);
			for($i = 0; $i < count($resultset); $i++ )
			{
				$smile_pak .= $resultset[$i]['smile_url'] . $delimeter;
				$smile_pak .= $resultset[$i]['emoticon'] . $delimeter;
				$smile_pak .= $resultset[$i]['code'] . "\n";
			}
			header("Content-Type: text/x-delimtext; name=\"smiles.pak\"");
			header("Content-disposition: attachment; filename=smiles.pak");
			echo $smile_pak;
			exit;
		}
		$template->set_filenames(array(
			"body" => "admin/admin_message_body.tpl")
		);

		$template->assign_vars(array(
			"MESSAGE_TITLE" => $lang['smiley_title'],
			"MESSAGE_TEXT" => $lang['export_smiles'])
		);
		$template->pparse('body');
		
		break;
		
	case 'delete':
		//
		// Admin has selected to delete a smiley.
		//

		$smiley_id = ( !empty($HTTP_POST_VARS['id']) ) ? $HTTP_POST_VARS['id'] : $HTTP_GET_VARS['id'];

		$sql = "DELETE FROM " . SMILIES_TABLE . "
			WHERE smilies_id = " . $smiley_id;
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Couldn't delete smiley", "", __LINE__, __FILE__, $sql);
		}

		$template->set_filenames(array(
			"body" => "admin/admin_message_body.tpl")
		);

		$template->assign_vars(array(
			"MESSAGE_TITLE" => $lang['smiley_title'],
			"MESSAGE_TEXT" => $lang['smiley_del_success'])
		);
		$template->pparse("body");
		break;

	case 'edit':
		//
		// Admin has selected to edit a smiley.
		//

		$smiley_id = ( !empty($HTTP_POST_VARS['id']) ) ? $HTTP_POST_VARS['id'] : $HTTP_GET_VARS['id'];

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
			"L_SMILEY_EXPLAIN" => $lang['smile_desc'],
			"L_SMILEY_CODE" => $lang['smiley_code'],
			"L_SMILEY_URL" => $lang['smiley_url'],
			"L_SMILEY_EMOTION" => $lang['smiley_emot'],
			"L_SUBMIT" => $lang['Submit_changes'],
			"L_RESET" => $lang['Reset_changes'],

			"SMILEY_IMG" => $phpbb_root_path . $board_config['smilies_path'] . '/' . $smiley_edit_img, 

			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_FILENAME_OPTIONS" => $filename_list, 
			"S_SMILEY_BASEDIR" => $phpbb_root_path . $board_config['smilies_path'])
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

			"SMILEY_IMG" => $phpbb_root_path . $board_config['smilies_path'] . '/' . $smiley_images[0], 

			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"), 
			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_FILENAME_OPTIONS" => $filename_list, 
			"S_SMILEY_BASEDIR" => $phpbb_root_path . $board_config['smilies_path'])
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
		$smile_code = ( isset($HTTP_POST_VARS['smile_code']) ) ? $HTTP_POST_VARS['smile_code'] : $HTTP_GET_VARS['smile_code'];
		$smile_url = ( isset($HTTP_POST_VARS['smile_url']) ) ? $HTTP_POST_VARS['smile_url'] : $HTTP_GET_VARS['smile_url'];
		$smile_emotion = ( isset($HTTP_POST_VARS['smile_emotion']) ) ? $HTTP_POST_VARS['smile_emotion'] : $HTTP_GET_VARS['smile_emotion'];
		$smile_id = ( isset($HTTP_POST_VARS['smile_id']) ) ? intval($HTTP_POST_VARS['smile_id']) : intval($HTTP_GET_VARS['smile_id']);

		//
		// Proceed with updating the smiley table.
		//
		$sql = "UPDATE " . SMILIES_TABLE . "
			SET code = '$smile_code', smile_url = '$smile_url', emoticon = '$smile_emotion'
			WHERE smilies_id = $smile_id";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Couldn't update smilies info", "", __LINE__, __FILE__, $sql);
		}

		$template->set_filenames(array(
			"body" => "admin/admin_message_body.tpl")
		);

		$template->assign_vars(array(
			"MESSAGE_TITLE" => $lang['smiley_title'],
			"MESSAGE_TEXT" => $lang['smiley_edit_success'])
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
		$smile_code = ( isset($HTTP_POST_VARS['smile_code']) ) ? $HTTP_POST_VARS['smile_code'] : $HTTP_GET_VARS['smile_code'];
		$smile_url = ( isset($HTTP_POST_VARS['smile_url']) ) ? $HTTP_POST_VARS['smile_url'] : $HTTP_GET_VARS['smile_url'];
		$smile_emotion = ( isset($HTTP_POST_VARS['smile_emotion']) ) ? $HTTP_POST_VARS['smile_emotion'] : $HTTP_GET_VARS['smile_emotion'];

		//
		// Save the data to the smiley table.
		//
		$sql = "INSERT INTO " . SMILIES_TABLE . " (code, smile_url, emoticon)
			VALUES ('$smile_code', '$smile_url', '$smile_emotion')";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Couldn't insert new smiley", "", __LINE__, __FILE__, $sql);
		}

		$template->set_filenames(array(
			"body" => "admin/admin_message_body.tpl")
		);

		$template->assign_vars(array(
			"MESSAGE_TITLE" => $lang['smiley_title'],
			"MESSAGE_TEXT" => $lang['smiley_add_success'])
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
			message_die(GENERAL_ERROR, "Couldn't obtain smileys from database", "", __LINE__, __FILE__, $sql);
		}

		$smilies = $db->sql_fetchrowset($result);

		$template->set_filenames(array(
			"body" => "admin/smile_list_body.tpl")
		);

		$s_hidden_fields = '<input type="hidden" name="mode" value="add">';
		$s_import = '<a href="' . $PHPSELF . '?mode=import"><input type="button" value="'. $lang['import_smile_pack'] . '"></a>';
		$s_export = '<a href="' . $PHPSELF . '?mode=export"><input type="button" value="'. $lang['export_smile_pack'] . '"></a>';
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
			"S_IMPORT" => $s_import,
			"S_EXPORT" => $s_export,
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

				"SMILEY_IMG" =>  $phpbb_root_path . $board_config['smilies_path'] . '/' . $smilies[$i]['smile_url'], 
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
