<?php
/***************************************************************************
*                               admin_icons.php
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

if ( !empty($setmodules) )
{
	if ( !$acl->get_acl_admin('general') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['General']['Emoticons'] = $filename . $SID . '&amp;mode=emoticons';

	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have general permissions?
//
if ( !$acl->get_acl_admin('general') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

//
// Check to see what mode we should operate in.
//
if ( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = '';
}

$delimeter  = '=+:';

//
// Read a listing of uploaded smilies for use in the add or edit smliey code...
//
$dir = @opendir($phpbb_root_path . $board_config['smilies_path']);

while( $file = @readdir($dir) )
{
	if ( is_file($phpbb_root_path . $board_config['smilies_path'] . '/' . $file) )
	{
		$img_size = @getimagesize($phpbb_root_path . $board_config['smilies_path'] . '/' . $file);

		if ( $img_size[0] && $img_size[1] )
		{
			$smiley_images[] = $file;
		}
		else if( eregi('.pak$', $file) )
		{	
			$smiley_paks[] = $file;
		}
	}
}

@closedir($dir);

//
// Select main mode
//
if ( isset($HTTP_GET_VARS['import_pack']) || isset($HTTP_POST_VARS['import_pack']) )
{
	//
	// Import a list a "Smiley Pack"
	//
	$smile_pak = ( isset($HTTP_POST_VARS['smile_pak']) ) ? $HTTP_POST_VARS['smile_pak'] : $HTTP_GET_VARS['smile_pak'];
	$clear_current = ( isset($HTTP_POST_VARS['clear_current']) ) ? $HTTP_POST_VARS['clear_current'] : $HTTP_GET_VARS['clear_current'];
	$replace_existing = ( isset($HTTP_POST_VARS['replace']) ) ? intval($HTTP_POST_VARS['replace']) : intval($HTTP_GET_VARS['replace']);

	if ( !empty($smile_pak) )
	{
		//
		// The user has already selected a smile_pak file.. Import it.
		//
		if ( !empty($clear_current)  )
		{
			$sql = "DELETE 
				FROM " . SMILIES_TABLE;
			$db->sql_query($sql);
		}
		else
		{
			$sql = "SELECT code 
				FROM ". SMILIES_TABLE;
			$result = $db->sql_query($sql);

			$cur_smilies = $db->sql_fetchrowset($result);

			for( $i = 0; $i < count($cur_smilies); $i++ )
			{
				$k = $cur_smilies[$i]['code'];
				$smiles[$k] = 1;
			}
		}

		$fcontents = @file($phpbb_root_path . $board_config['smilies_path'] . '/'. $smile_pak);

		if ( empty($fcontents) )
		{
			message_die(ERROR, "Couldn't read smiley pak file", "", __LINE__, __FILE__, $sql);
		}

		for( $i = 0; $i < count($fcontents); $i++ )
		{
			$smile_data = explode($delimeter, trim(addslashes($fcontents[$i])));

			for( $j = 2; $j < count($smile_data); $j++)
			{
				//
				// Replace > and < with the proper html_entities for matching.
				//
				$smile_data[$j] = htmlentities($smile_data[$j]);
				$k = $smile_data[$j];

				if ( $smiles[$k] == 1 )
				{
					if ( !empty($replace_existing) )
					{
						$sql = "UPDATE " . SMILIES_TABLE . " 
							SET smile_url = '" . str_replace("\'", "''", $smile_data[0]) . "', emoticon = '" . str_replace("\'", "''", $smile_data[1]) . "' 
							WHERE code = '" . str_replace("\'", "''", $smile_data[$j]) . "'";
					}
					else
					{
						$sql = '';
					}
				}
				else
				{
					$sql = "INSERT INTO " . SMILIES_TABLE . " (code, smile_url, emoticon)
						VALUES('" . str_replace("\'", "''", $smile_data[$j]) . "', '" . str_replace("\'", "''", $smile_data[0]) . "', '" . str_replace("\'", "''", $smile_data[1]) . "')";
				}

				if ( $sql != '' )
				{
					$db->sql_query($sql);
				}
			}
		}

		message_die(MESSAGE, $lang['smiley_import_success']);
		
	}
	else
	{
		//
		// Display the script to get the smile_pak cfg file...
		//
		$smile_paks_select = "<select name='smile_pak'><option value=''>" . $lang['Select_pak'] . "</option>";

		foreach ( $smiley_paks as $key => $value )
		{
			if ( !empty($value) ) 
			{
				$smile_paks_select .= "<option>" . $value . "</option>";
			}
		}
		$smile_paks_select .= "</select>";

		$hidden_vars = "<input type='hidden' name='mode' value='import'>";	

		$template->set_filenames(array(
			"body" => "admin/smile_import_body.tpl")
		);

		$template->assign_vars(array(
			"L_SMILEY_TITLE" => $lang['smiley_title'],
			"L_SMILEY_EXPLAIN" => $lang['smiley_import_inst'],
			"L_SMILEY_IMPORT" => $lang['smiley_import'],
			"L_SELECT_LBL" => $lang['choose_smile_pak'],
			"L_IMPORT" => $lang['import'],
			"L_CONFLICTS" => $lang['smile_conflicts'],
			"L_DEL_EXISTING" => $lang['del_existing_smileys'], 
			"L_REPLACE_EXISTING" => $lang['replace_existing'], 
			"L_KEEP_EXISTING" => $lang['keep_existing'], 

			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"),
			"S_SMILE_SELECT" => $smile_paks_select,
			"S_HIDDEN_FIELDS" => $hidden_vars)
		);

		$template->pparse("body");
	}
}
else if ( isset($HTTP_POST_VARS['export_pack']) || isset($HTTP_GET_VARS['export_pack']) )
{
	//
	// Export our smiley config as a smiley pak...
	//
	if ( $HTTP_GET_VARS['export_pack'] == "send" )
	{	
		$sql = "SELECT * 
			FROM " . SMILIES_TABLE;
		$result = $db->sql_query($sql);

		$resultset = $db->sql_fetchrowset($result);

		$smile_pak = '';
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

	message_die(MESSAGE, sprintf($lang['export_smiles'], '<a href="' . "admin_smilies.$phpEx$SID&amp;export_pack=send" . '">', '</a>'));

}
else if( isset($HTTP_POST_VARS['add']) )
{
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
		"L_SUBMIT" => $lang['Submit'],
		"L_RESET" => $lang['Reset'],

		"SMILEY_IMG" => $phpbb_root_path . $board_config['smilies_path'] . '/' . $smiley_images[0], 

		"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"), 
		"S_HIDDEN_FIELDS" => $s_hidden_fields, 
		"S_FILENAME_OPTIONS" => $filename_list, 
		"S_SMILEY_BASEDIR" => $phpbb_root_path . $board_config['smilies_path'])
	);

	$template->pparse("body");
}

//
//
//
switch( $mode )
{
	case 'delete':

		$smiley_id = ( !empty($HTTP_POST_VARS['id']) ) ? intval($HTTP_POST_VARS['id']) : intval($HTTP_GET_VARS['id']);

		$sql = "DELETE FROM " . SMILIES_TABLE . "
			WHERE smilies_id = " . $smiley_id;
		$db->sql_query($sql);

		message_die(GENERAL_MESSAGE, $lang['smiley_del_success']);
		break;

	case 'edit':

		$smiley_id = ( !empty($HTTP_POST_VARS['id']) ) ? intval($HTTP_POST_VARS['id']) : intval($HTTP_GET_VARS['id']);

		$sql = "SELECT *
			FROM " . SMILIES_TABLE . "
			WHERE smilies_id = " . $smiley_id;
		$result = $db->sql_query($sql);

		$smile_data = $db->sql_fetchrow($result);

		$filename_list = "";
		for( $i = 0; $i < count($smiley_images); $i++ )
		{
//			$selected = 
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
			"L_SUBMIT" => $lang['Submit'],
			"L_RESET" => $lang['Reset'],

			"SMILEY_IMG" => $phpbb_root_path . $board_config['smilies_path'] . '/' . $smiley_edit_img, 

			"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
			"S_FILENAME_OPTIONS" => $filename_list, 
			"S_SMILEY_BASEDIR" => $phpbb_root_path . $board_config['smilies_path'])
		);

		$template->pparse("body");
		break;

	case 'save':

		//
		// Get the submitted data, being careful to ensure that we only
		// accept the data we are looking for.
		//
		$smile_code = ( isset($HTTP_POST_VARS['smile_code']) ) ? $HTTP_POST_VARS['smile_code'] : $HTTP_GET_VARS['smile_code'];
		$smile_url = ( isset($HTTP_POST_VARS['smile_url']) ) ? $HTTP_POST_VARS['smile_url'] : $HTTP_GET_VARS['smile_url'];
		$smile_emotion = ( isset($HTTP_POST_VARS['smile_emotion']) ) ? $HTTP_POST_VARS['smile_emotion'] : $HTTP_GET_VARS['smile_emotion'];
		$smile_id = ( isset($HTTP_POST_VARS['smile_id']) ) ? intval($HTTP_POST_VARS['smile_id']) : intval($HTTP_GET_VARS['smile_id']);

		$smile_code = htmlspecialchars($smile_code);

		//
		// Proceed with updating the smiley table.
		//
		$sql = "UPDATE " . SMILIES_TABLE . "
			SET code = '" . str_replace("\'", "''", $smile_code) . "', smile_url = '" . str_replace("\'", "''", $smile_url) . "', emoticon = '" . str_replace("\'", "''", $smile_emotion) . "'
			WHERE smilies_id = $smile_id";
		$db->sql_query($sql);

		message_die(MESSAGE, $lang['smiley_edit_success']);
		break;

	case 'savenew':

		//
		// Get the submitted data being careful to ensure the the data
		// we recieve and process is only the data we are looking for.
		//
		$smile_code = ( isset($HTTP_POST_VARS['smile_code']) ) ? $HTTP_POST_VARS['smile_code'] : $HTTP_GET_VARS['smile_code'];
		$smile_url = ( isset($HTTP_POST_VARS['smile_url']) ) ? $HTTP_POST_VARS['smile_url'] : $HTTP_GET_VARS['smile_url'];
		$smile_emotion = ( isset($HTTP_POST_VARS['smile_emotion']) ) ? $HTTP_POST_VARS['smile_emotion'] : $HTTP_GET_VARS['smile_emotion'];

		$smile_code = htmlspecialchars($smile_code);

		//
		// Save the data to the smiley table.
		//
		$sql = "INSERT INTO " . SMILIES_TABLE . " (code, smile_url, emoticon)
			VALUES ('" . str_replace("\'", "''", $smile_code) . "', '" . str_replace("\'", "''", $smile_url) . "', '" . str_replace("\'", "''", $smile_emotion) . "')";
		$db->sql_query($sql);

		message_die(MESSAGE, $lang['smiley_add_success']);
		break;

	default:

		$sql = "SELECT *
			FROM " . SMILIES_TABLE;
		$result = $db->sql_query($sql);

		page_header($lang['Emoticons']);

?>

<h1><?php echo $lang['Emoticons']; ?></h1>

<p><?php echo $lang['Emoticons_explain']; ?></p>

<form method="post" action="<?php echo "admin_smilies.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $lang['Code']; ?></th>
		<th><?php echo $lang['Smile']; ?></th>
		<th><?php echo $lang['Emotion']; ?></th>
		<th colspan="2"><?php echo $lang['Action']; ?></th>
	</tr>
<?php
			
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$row_class = ( $row_class != 'row1' ) ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo htmlspecialchars($row['code']); ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><img src="<?php echo './../' . $board_config['smilies_path'] . '/' . $row['smile_url']; ?>" width="<?php echo $row['smile_width']; ?>" height="<?php echo $row['smile_height']; ?>" alt="<?php echo htmlspecialchars($row['code']); ?>" /></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['emoticon']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=edit&amp;id=" . $row['smilies_id']; ?>"><?php echo $lang['Edit']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_smilies.$phpEx$SID&amp;mode=delete&amp;id=" . $row['smilies_id']; ?>"><?php echo $lang['Delete']; ?></a></td>
	</tr>
<?php

			}
			while ( $row = $db->sql_fetchrow($result) );
		}

?>
	<tr>
		<td class="cat" colspan="5" align="center"><input type="submit" name="add" value="<?php echo $lang['smile_add']; ?>" class="mainoption" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="import_pack" value="<?php echo $lang['import_smile_pack']; ?>">&nbsp;&nbsp;<input class="liteoption" type="submit" name="export_pack" value="<?php echo $lang['export_smile_pack']; ?>"></td>
	</tr>
</table></form>

<?php

		page_footer();

		break;
}

?>