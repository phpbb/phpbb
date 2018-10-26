<?php
/***************************************************************************
*                               admin_smilies.php
*                              -------------------
*     begin                : Thu May 31, 2001
*     copyright            : (C) 2001 The phpBB Group
*     email                : support@phpbb.com
*
*     $Id: admin_smilies.php,v 1.25 2013/06/26 09:15:22 orynider Exp $
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

/*
* Security and Page header
*/
@define('IN_PHPBB', 1);
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// First we do the setmodules stuff for the admin cp.
if( !empty($setmodules) )
{
	$filename = basename(__FILE__);
	$module['General']['Smilies']= $filename;

	return;
}
$no_page_header = false;

/**
* Get only GET vars
*/
function get($var_name, $default, $multibyte = false)
{
	$return = $default;
	if (isset($_GET[$var_name]))
	{
		$temp_post_var = isset($_POST[$var_name]) ? $_POST[$var_name] : '';
		$_POST[$var_name] = $_GET[$var_name];
		$return = isset($_REQUEST[$var_name]) ? $_REQUEST[$var_name] : $default;
		$_POST[$var_name] = $temp_post_var;
	}
	return $return;
}

if (isset($_GET['export_pack']))
{
	if (get('export_pack', '') == "send" )
	{
		$no_page_header = true;
	}
}

/*
* Load default header
*/
include_once('./pagestart.' . $phpEx);

$cancel = is_post('cancel');

// Load default header
if ($no_page_header !== true)
{
	include_once('./page_header_admin.' . $phpEx);
}

if ($cancel)
{
	redirect('admin/' . append_sid("admin_smilies.$phpEx", true));
}

//
// Check to see what mode we should operate in.
//
if (is_request('mode'))
{
	$mode = request_var('mode', MX_TYPE_NO_TAGS);
	$mode = htmlspecialchars($mode);
}
else
{
	$mode = "";
}

@define('DB_BACKEND', 'phpbb2');

switch (DB_BACKEND)
{
	case 'internal':
		$smiley_path_url = PHPBB_URL; //change this to PORTAL_URL when shared folder will be removed
		$smiley_root_path =	$phpbb_root_path; //same here
		$fields = 'smilies';
		$smiley_url = 'smile_url';
		$emotion = 'emoticon';
		$table = SMILIES_TABLE;
		$delimeter  = '=+:';
	break;
	case 'phpbb2':
		$smiley_path_url = PHPBB_URL;
		$smiley_root_path =	$phpbb_root_path;
		$fields = 'smilies';
		$smiley_url = 'smile_url';
		$emotion = 'emoticon';
		$table = SMILIES_TABLE;
		$delimeter  = '=+:';
	break;
	case 'phpbb3':
		$smiley_path_url = PHPBB_URL;
		$smiley_root_path =	$phpbb_root_path;
		$fields = 'smiley';
		$smiley_url = 'smiley_url';
		$emotion = 'emotion';
		$table = SMILIES_TABLE;
		$delimeter  = ', ';
		$board_config['smilies_path'] = str_replace("smiles", "smilies", $board_config['smilies_path']);
	break;
}

//
// Check whatever DB is Level2
//
switch (DB_BACKEND)
{
	case 'internal':
	case 'phpbb2':			
		$sql = "SELECT * FROM " . SMILIES_TABLE . "	ORDER BY {$fields}_order";
		$result = $db->sql_query($sql);
	break;

	case 'phpbb3':
		$sql = "SELECT *
			FROM " . $table . "
			ORDER BY {$fields}_order";
		$result = $db->sql_query($sql);
	break;
}
if(!$result || !$smilies = $db->sql_fetchrowset($result))
{
	@define('DB_LEVEL', 'phpbb2');	
	
	$redirect_url = append_sid("admin_smilies.$phpEx?add_smilies_order=alter_table", true);
	$message_info = '<p><span style="color: red;">Your smilies DB Table is at Level 2 and so You will not be able to arange smilies order...</p><i><p>Upgrading to Level 3 is not reversible! If you are aware of that, please click this link to proceed:</i></span> <a href="' . $redirect_url . '">click here to begin</a></p>'; 
	
	print($message_info);
	
	if (is_request('add_smilies_order'))
	{
		$sql = "ALTER TABLE " . $table_prefix . "smilies ADD smilies_order INT(5) NOT NULL";
		
		// We could add error handling here...
		$result = $db->sql_query($sql);					
		if (!($result))
		{		
			message_die(CRITICAL_ERROR, "Could not upgradate table smilies to Level 3", '', __LINE__, __FILE__, $sql);
		}
		
		$message = $lang['Virtual_Go'] . "<br /><br />" . sprintf($lang['Click_return_smileadmin'], "<a href=\"" . append_sid("admin_smilies.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid($phpbb_root_path . "admin/index.$phpEx?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);		
	}	
}
else	
{
	@define('DB_LEVEL', 'phpbb3');
}	

//
// Read a listing of uploaded smilies for use in the add or edit smliey code...
//
$dir = @opendir($smiley_root_path . $board_config['smilies_path']);

while($file = @readdir($dir))
{
	if( !@is_dir($smiley_root_path . $board_config['smilies_path'] . '/' . $file) )
	{
		$img_size = @getimagesize($smiley_root_path . $board_config['smilies_path'] . '/' . $file);

		if( $img_size[0] && $img_size[1] )
		{
			$smiley_images[] = $file;
		}
		else if( stristr($file, '.pak$') )
		{
			$smiley_paks[] = $file;
		}
	}
}

@closedir($dir);

//
// Select main mode
//
if (is_request('import_pack'))
{
	//
	// Import a list a "Smiley Pack"
	//
	$smile_pak = request_var('smile_pak', '');
	$clear_current = request_var('clear_current', '');
	$replace_existing = request_var('replace', '');

	if ( !empty($smile_pak) )
	{
		//
		// The user has already selected a smile_pak file.. Import it.
		//
		if( !empty($clear_current)  )
		{
			switch ($db->sql_layer)
			{
				case 'sqlite':
				case 'firebird':
					$db->sql_query('DELETE FROM ' . $table);
				break;

				default:
					$db->sql_query('TRUNCATE TABLE ' . $table);
				break;
			}
		}
		else
		{
			$sql = "SELECT code
				FROM ". $table;
			if( !$result = $db->sql_query($sql) )
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

		$smiley_order = 0;

		$fcontents = @file($smiley_root_path . $board_config['smilies_path'] . '/'. $smile_pak);

		if( empty($fcontents) )
		{
			message_die(GENERAL_ERROR, "Couldn't read smiley pak file", "", __LINE__, __FILE__, $sql);
		}

		for($i = 0; $i < count($fcontents); $i++)
		{
			switch (DB_BACKEND)
			{
				case 'internal':
				case 'phpbb2':
					$smile_data = explode($delimeter, trim(addslashes($fcontents[$i])));
					$count_data = 2;
				break;

				case 'phpbb3':
					$smile_data = explode($delimeter, trim($fcontents[$i]));
					$smile_data = str_replace("'", "", $smile_data);
					$smile_data = str_replace(",", "", $smile_data);
					$count_data = 5;
				break;
			}

			for($j = $count_data; $j < count($smile_data); $j++)
			{
				//
				// Replace > and < with the proper html_entities for matching.
				//
				$smile_data[$j] = str_replace("<", "&lt;", $smile_data[$j]);
				$smile_data[$j] = str_replace(">", "&gt;", $smile_data[$j]);
				$k = $smile_data[$j];

				// Stripslash here because it got addslashed before... (on export)
				$smile_url = stripslashes($smile_data[0]);
				$smiley_width = stripslashes($smile_data[1]);
				$smiley_height = stripslashes($smile_data[2]);
				$display_on_posting = stripslashes($smile_data[3]);

				if (isset($smile_data[4]) && isset($smile_data[5]))
				{
					$smile_emotion = stripslashes($smile_data[4]);
					$smile_code = stripslashes($smile_data[5]);
				}

				if( $smiles[$k] == 1 )
				{
					if( !empty($replace_existing) )
					{
						switch (DB_BACKEND)
						{
							case 'internal':
							case 'phpbb2':
								$sql = "UPDATE " . $table . "
									SET smile_url = '" . str_replace("\'", "''", $smile_data[0]) . "', emoticon = '" . str_replace("\'", "''", $smile_data[1]) . "'
									WHERE code = '" . str_replace("\'", "''", $smile_data[$j]) . "'";
								$result = $db->sql_query($sql);
							break;

							case 'phpbb3':
								$sql = array(
									'emotion'			=> $smile_emotion,
									$fields . '_url'	=> $smile_url,
									$fields . '_height'	=> (int) $smiley_height,
									$fields . '_width'	=> (int) $smiley_width,
									$fields . '_order'	=> (int) $smiley_order,
									'display_on_posting'=> (int) $display_on_posting,
								);

								$sql = "UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql) . "
									WHERE code = '" . $db->sql_escape($smile_code) . "'";
								$result = $db->sql_query($sql);
							break;
						}
					}
					else
					{
						$sql = '';
					}
				}
				else
				{
					switch (DB_BACKEND)
					{
						case 'internal':
						case 'phpbb2':
							$sql = "INSERT INTO " . $table . " (code, smile_url, emoticon)
								VALUES('" . str_replace("\'", "''", $smile_data[$j]) . "', '" . str_replace("\'", "''", $smile_data[0]) . "', '" . str_replace("\'", "''", $smile_data[1]) . "')";
							$result = $db->sql_query($sql);
						break;

						case 'phpbb3':
							++$smiley_order;
							$sql = array(
								$fields . '_url'	=> $smile_url,
								$fields . '_height'	=> (int) $smiley_height,
								$fields . '_width'	=> (int) $smiley_width,
								$fields . '_order'	=> (int) $smiley_order,
								'display_on_posting'=> (int) $display_on_posting,
							);

							$sql = array_merge($sql, array(
								'code'				=> $smile_code,
								'emotion'			=> $smile_emotion,
							));

							$result = $db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $sql));
						break;
					}
				}

				if( $sql != '' )
				{
					if( !$result )
					{
						message_die(GENERAL_ERROR, "Couldn't update smilies!", "", __LINE__, __FILE__, $sql);
					}
				}
			}
		}

		$message = $lang['smiley_import_success'] . "<br /><br />" . sprintf($lang['Click_return_smileadmin'], "<a href=\"" . append_sid("admin_smilies.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid($phpbb_root_path . "admin/index.$phpEx?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);

	}
	else
	{
		//
		// Display the script to get the smile_pak cfg file...
		//
		$smile_paks_select = "<select name='smile_pak'><option value=''>" . $lang['Select_pak'] . "</option>";
		while( list($key, $value) = @each($smiley_paks) )
		{
			if ( !empty($value) )
			{
				$smile_paks_select .= "<option>" . $value . "</option>";
			}
		}
		$smile_paks_select .= "</select>";

		$hidden_vars = "<input type='hidden' name='mode' value='import'/>";

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
else if (is_request('export_pack'))
{
	//
	// Export our smiley config as a smiley pak...
	//
	if (request_get_var('export_pack', '', MX_TYPE_NO_TAGS) == "send" )
	{
		$gen_simple_header = true;

		switch (DB_BACKEND)
		{
			case 'internal':
			case 'phpbb2':
				$sql = "SELECT *
					FROM " . SMILIES_TABLE;
			break;

			case 'phpbb3':
				$sql = 'SELECT *
					FROM ' . SMILIES_TABLE . '
					ORDER BY smiley_order';
			break;
		}

		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Could not get smiley list", "", __LINE__, __FILE__, $sql);
		}

		$resultset = $db->sql_fetchrowset($result);

		$smile_pak = "";
		switch (DB_BACKEND)
		{
			case 'internal':
			case 'phpbb2':
				for($i = 0; $i < count($resultset); $i++ )
				{
					$smile_pak .= $resultset[$i][$smiley_url] . $delimeter;
					$smile_pak .= $resultset[$i]['emoticon'] . $delimeter;
					$smile_pak .= $resultset[$i]['code'] . "\n";
				}
			break;

			case 'phpbb3':
				for($i = 0; $i < count($resultset); $i++ )
				{
					$smile_pak .= "'" . addslashes($resultset[$i][$smiley_url]) . "'" . $delimeter;
					$smile_pak .= "'" . addslashes($resultset[$i][$fields . '_width']) . "'" . $delimeter;
					$smile_pak .= "'" . addslashes($resultset[$i][$fields . '_height']) . "'" . $delimeter;
					$smile_pak .= "'" . addslashes($resultset[$i]['display_on_posting']) . "'" . $delimeter;
					$smile_pak .= "'" . addslashes($resultset[$i][$emotion]) . "'" . $delimeter;
					$smile_pak .= "'" . addslashes($resultset[$i]['code']) . "'" . $delimeter . "\n";
				}
			break;
		}
		$db->sql_freeresult($result);

		if ($smile_pak != '')
		{
			garbage_collection();
			header('Pragma: public');

			// Send out the Headers
			@header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');			
			@header('Content-Type: text/x-delimtext; name="smilies.pak"');
			@header('Content-Disposition: inline; filename="smilies.pak"');
			echo $smile_pak;

			flush();
			exit;
		}
		else
		{
			message_die(GENERAL_MESSAGE, 'Error');
		}
	}
	$message = sprintf($lang['export_smiles'], "<a href=\"" . append_sid("admin_smilies.$phpEx?export_pack=send", true) . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_smileadmin'], "<a href=\"" . append_sid("admin_smilies.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid($phpbb_root_path . "admin/index.$phpEx?pane=right") . "\">", "</a>");

	message_die(GENERAL_MESSAGE, $message);

}
else if (is_request('add'))
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
		"L_SMILEY_EXPLAIN" => $lang['smile_desc'],
		"L_SMILEY_CODE" => $lang['smiley_code'],
		"L_SMILEY_URL" => $lang['smiley_url'],
		"L_SMILEY_EMOTION" => $lang['smiley_emot'],
		"L_WIDTH" => $lang['Width'],
		"L_HEIGHT" => $lang['Height'],
		"L_ORDER" => $lang['Order'],
		"L_SUBMIT" => $lang['Submit'],
		"L_RESET" => $lang['Reset'],

		"SMILEY_IMG" => $smiley_root_path . $board_config['smilies_path'] . '/' . $smiley_images[0],

		'SMILEY_WIDTH'		=> (DB_BACKEND === 'phpbb3') ? '' : '',
		'SMILEY_HEIGHT'		=> (DB_BACKEND === 'phpbb3') ? '' : '',
		'SMILEY_ORDER'		=> (DB_BACKEND === 'phpbb3') ? '' : '',

		'POSTING_CHECKED'	=> (is_request('add')) ? ' checked="checked"' : '',


		"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"),
		"S_HIDDEN_FIELDS" => $s_hidden_fields,
		"S_FILENAME_OPTIONS" => $filename_list,
		"S_SMILEY_BASEDIR" => $smiley_root_path . $board_config['smilies_path'])
	);

	$template->pparse("body");
}
else if ( $mode != "" )
{
	// Get the submitted data being careful to ensure the the data we receive and process is only the data we are looking for.

	
	switch( $mode )
	{
		case 'delete':		
			
			//
			// Admin has selected to delete a smiley.
			//
			$smiley_id = ( !empty($_POST['id']) ) ? $_POST['id'] : $_GET['id'];		
			$smiley_id = request_var('id', $smiley_id);				
			
			if (is_post('confirm'))
			{
				$sql = "DELETE FROM " . $table . "
					WHERE {$fields}_id = " . $smiley_id;					
				$result = $db->sql_query($sql);
				if( !$result )
				{
					message_die(GENERAL_ERROR, "Couldn't delete smiley", "", __LINE__, __FILE__, $sql);
				}

				$message = $lang['smiley_del_success'] . "<br /><br />" . sprintf($lang['Click_return_smileadmin'], "<a href=\"" . append_sid("admin_smilies.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid($phpbb_root_path . "admin/index.$phpEx?pane=right") . "\">", "</a>");

				$cache->destroy('_smileys');
				$db->clear_cache('smileys_');
				message_die(GENERAL_MESSAGE, $message);;
			}
			else
			{
				// Present the confirmation screen to the user
				$template->set_filenames(array(
					'body' => 'admin/confirm_body.tpl')
				);

				$hidden_fields = '<input type="hidden" name="mode" value="delete" /><input type="hidden" name="id" value="' . $smiley_id . '" />';

				$template->assign_vars(array(
					'MESSAGE_TITLE' => $lang['Confirm'],
					'MESSAGE_TEXT' => $lang['Confirm_delete_smiley'] . " Id: $smiley_id ?",

					'L_YES' => $lang['Yes'],
					'L_NO' => $lang['No'],

					'S_CONFIRM_ACTION' => append_sid("admin_smilies.$phpEx"),
					'S_HIDDEN_FIELDS' => $hidden_fields)
				);
				$template->pparse('body');
			}
			break;

		case 'edit':
			//
			// Admin has selected to edit a smiley.
			//
			$smiley_id = ( !empty($_POST['id']) ) ? $_POST['id'] : $_GET['id'];			
			$smiley_id = request_var('id', $smiley_id);			
			$sql = "SELECT *
				FROM " . $table . "
				 WHERE {$fields}_id = " . $smiley_id;

			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, 'Could not obtain ' . $emotion . ' information', "", __LINE__, __FILE__, $sql);
			}
			$smile_data = $db->sql_fetchrow($result);

			$filename_list = "";
			for( $i = 0; $i < count($smiley_images); $i++ )
			{
				if( $smiley_images[$i] == $smile_data[$smiley_url] )
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

			$s_hidden_fields = '<input type="hidden" name="mode" value="save" /><input type="hidden" name="id" value="' . $smile_data[$fields . '_id'] . '" />';

			$template->assign_vars(array(
				'SMILEY_URL'		=> addslashes($smile_data[$smiley_url]),
				'SMILEY_CODE'		=> addslashes($smile_data['code']),
				'SMILEY_EMOTICON'	=> addslashes($smile_data[$emotion]),
				'S_ID'				=> (isset($smile_data[$fields . '_id'])) ? true : false,
				'ID'				=> (isset($smile_data[$fields . '_id'])) ? $smile_data[$fields . '_id'] : 0,
				'SMILEY_WIDTH'		=> (DB_BACKEND === 'phpbb3') ? $smile_data[$fields.'_width'] : '',
				'SMILEY_HEIGHT'		=> (DB_BACKEND === 'phpbb3') ? $smile_data[$fields.'_height'] : '',
				'SMILEY_ORDER'		=> (DB_BACKEND === 'phpbb3') ? $smile_data[$fields.'_order'] : '',

				'POSTING_CHECKED'	=> (!empty($smile_data['display_on_posting']) || is_request('add')) ? ' checked="checked"' : '',

				"L_SMILEY_TITLE" => $lang['smiley_title'],
				"L_SMILEY_CONFIG" => $lang['smiley_config'],
				"L_SMILEY_EXPLAIN" => $lang['smile_desc'],
				"L_SMILEY_CODE" => $lang['smiley_code'],
				"L_SMILEY_URL" => $lang['smiley_url'],
				"L_SMILEY_EMOTION" => $lang['smiley_emot'],
				"L_WIDTH" => $lang['Width'],
				"L_HEIGHT" => $lang['Height'],
				"L_ORDER" => $lang['Order'],
				"L_SUBMIT" => $lang['Submit'],
				"L_RESET" => $lang['Reset'],

				"SMILEY_IMG" => $smiley_path_url . $board_config['smilies_path'] . '/' . $smiley_edit_img,

				"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"),
				"S_HIDDEN_FIELDS" => $s_hidden_fields,
				"S_FILENAME_OPTIONS" => $filename_list,
				"S_SMILEY_BASEDIR" => $smiley_path_url . $board_config['smilies_path'])
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
			$smile_code = request_post_var('smile_code', ':)');
			$smile_url = request_post_var('smile_url', MX_TYPE_NO_TAGS);
			$smile_url = phpbb_ltrim(basename($smile_url), "'");
			$smile_emotion = request_post_var('smile_emotion', MX_TYPE_NO_HTML);
			$smile_id = ( isset($_POST['smile_id']) ) ? intval($_POST['smile_id']) : 0;			
			$smile_id = request_post_var('id', $smile_id, MX_TYPE_INT);
			$smile_code = trim($smile_code);
			$smile_url = trim($smile_url);

			if (DB_BACKEND === 'phpbb3')
			{
				$smiley_width = request_post_var($fields.'_width', MX_TYPE_NO_HTML);
				$smiley_height = request_post_var($fields.'_height', MX_TYPE_NO_HTML);
				$smiley_order = request_post_var($fields.'_order', MX_TYPE_NO_HTML);
			}


			// If no code was entered complain ...
			if ($smile_code == '' || $smile_url == '')
			{
				message_die(GENERAL_MESSAGE, $lang['Fields_empty']);
			}

			//
			// Convert < and > to proper htmlentities for parsing.
			//
			$smile_code = str_replace('<', '&lt;', $smile_code);
			$smile_code = str_replace('>', '&gt;', $smile_code);

			//
			// Proceed with updating the smiley table.
			//
			switch (DB_BACKEND)
			{
				case 'internal':
				case 'phpbb2':
					$sql = "UPDATE " . $table . "
						SET code = '" . str_replace("\'", "''", $smile_code) . "', smile_url = '" . str_replace("\'", "''", $smile_url) . "', emoticon = '" . str_replace("\'", "''", $smile_emotion) . "'
						WHERE smilies_id = $smile_id";
				break;

				case 'phpbb3':
					$sql = array(
						'emotion' => $smile_emotion,
						$fields . '_url' => $smile_url,
						$fields . '_height' => (int) $smiley_height,
						$fields . '_width' => (int) $smiley_width,
						$fields . '_order' => (int) $smiley_order,
						'display_on_posting' => (int) $display_on_posting,
					);

					$sql = "UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql) . "
						WHERE code = '" . $db->sql_escape($smile_code) . "'";
				break;
			}

			if( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, "Couldn't update smilies info", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['smiley_edit_success'] . "<br /><br />" . sprintf($lang['Click_return_smileadmin'], "<a href=\"" . append_sid("admin_smilies.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid($phpbb_root_path . "admin/index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
			break;

		case "savenew":
			//
			// Admin has submitted changes while adding a new smiley.
			//

			//
			// Get the submitted data being careful to ensure the the data
			// we recieve and process is only the data we are looking for.
			//
			$smile_code = request_post_var('smile_code', ':)');
			$smile_url = request_post_var('smile_url', '');
			$smile_url = phpbb_ltrim(basename($smile_url), "'");
			$smile_emotion = request_post_var('smile_emotion', MX_TYPE_NO_HTML);
			$smile_code = trim($smile_code);
			$smile_url = trim($smile_url);

			if ((DB_BACKEND === 'phpbb3') || (DB_LEVEL === 'phpbb3'))
			{
				$smiley_width = request_post_var($fields.'_width', MX_TYPE_NO_HTML);
				$smiley_height = request_post_var($fields.'_height', MX_TYPE_NO_HTML);
				$smiley_order = request_post_var($fields.'_order', MX_TYPE_NO_HTML);
				$display_on_posting = request_var('display', 1, MX_TYPE_INT);
			}

			// If no code was entered complain ...
			if ($smile_code == '' || $smile_url == '')
			{
				message_die(GENERAL_MESSAGE, $lang['Fields_empty']);
			}

			//
			// Convert < and > to proper htmlentities for parsing.
			//
			$smile_code = str_replace('<', '&lt;', $smile_code);
			$smile_code = str_replace('>', '&gt;', $smile_code);

			//
			// Save the data to the smiley table.
			//
			switch (DB_BACKEND)
			{
				case 'internal':
				case 'phpbb2':
					$sql = "INSERT INTO " . $table . " (code, smile_url, emoticon)
						VALUES ('" . str_replace("\'", "''", $smile_code) . "', '" . str_replace("\'", "''", $smile_url) . "', '" . str_replace("\'", "''", $smile_emotion) . "')";
					$result = $db->sql_query($sql);
				break;

				case 'phpbb3':
					$sql = array(
						'code'				=> $smile_code,
						'emotion'			=> $smile_emotion,
						$fields . '_url'	=> $smile_url,
						$fields . '_height'	=> (int) $smiley_height,
						$fields . '_width'	=> (int) $smiley_width,
						$fields . '_order'	=> (int) $smiley_order,
						'display_on_posting'=> (int) $display_on_posting,
					);
					$result = $db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $sql));
				break;
			}


			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't insert new smiley", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['smiley_add_success'] . "<br /><br />" . sprintf($lang['Click_return_smileadmin'], "<a href=\"" . append_sid("admin_smilies.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid($phpbb_root_path . "admin/index.$phpEx?pane=right") . "\">", "</a>");

			$cache->destroy('_smileys');
			$db->clear_cache('smileys_');
			message_die(GENERAL_MESSAGE, $message);
			break;
	}
}
else
{
	// Smilies Order BEGIN
	$option = request_get_var('option', '');
	$insert_position = request_post_var('insert_position', '');
	if(($option == 'select') && isset($_POST['insert_position']))
	{
		set_config("{$fields}_insert", $insert_position);
		$cache->destroy('_smileys');
		$db->clear_cache('smileys_');
	}

	if($config['smilies_insert'] == TOP_LIST)
	{
		$pos_top_checked = ' selected="selected"';
		$pos_bot_checked = '';
	}
	else
	{
		$pos_top_checked = '';
		$pos_bot_checked = ' selected="selected"';
	}
	$position_select = '<select name="insert_position"><option value="' . TOP_LIST . '"' . $pos_top_checked . '>' . $lang['before'] . '</option><option value="' . BOTTOM_LIST . '"' . $pos_bot_checked . '>' . $lang['after'] . '</option></select>';

	$move = request_get_var('move', '');
	$send = request_get_var('send', '');
	$id = request_get_var('id', 0);
	
	if(isset($_GET['move']) && isset($_GET['id']))
	{
		$moveit = ($move == 'up') ? -15 : 15;
		$sql = "UPDATE " . SMILIES_TABLE . "
			SET {$fields}_order = {$fields}_order + $moveit
			WHERE {$fields}_id = " . $id;
		$result = $db->sql_query($sql);

		$i = 10;
		$inc = 10;

		$sql = "SELECT *
			FROM " . SMILIES_TABLE . "
			ORDER BY {$fields}_order";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row[$fields.'_order'] != $i)
			{
				$sql = "UPDATE " . SMILIES_TABLE . "
					SET {$fields}_order = $i
					WHERE {$fields}_id = " . $row[$fields.'_id'];
				$db->sql_query($sql);
			}
			$i += $inc;
		}
	$cache->destroy('_smileys');
	$db->clear_cache('smileys_');
	}
	elseif(isset($_GET['send']) && isset($_GET['id']))
	{
		if($send == 'top')
		{
			$sql = "SELECT MIN({$fields}_order) AS smilies_extreme
				FROM " . SMILIES_TABLE;
			$shift_it = -10;
		}
		else
		{
			$sql = "SELECT MAX({$fields}_order) AS smilies_extreme
				FROM " . SMILIES_TABLE;
			$shift_it = 10;
		}
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$order_extreme = $row[$fields.'_extreme'] + $shift_it;

		$sql = "UPDATE " . SMILIES_TABLE . "
			SET {$fields}_order = $order_extreme
			WHERE {$fields}_id = " . $id;
		$result = $db->sql_query($sql);
		$cache->destroy('_smileys');
		$db->clear_cache('smileys_');
	}
	// Smilies Order END
	
	//
	// This is the main display of the page before the admin has selected
	// any options.
	//
	switch (DB_BACKEND)
	{
		case 'internal':
		case 'phpbb2':			
			$sql2 = "SELECT * FROM " . $table;
			$sql3 = "SELECT * FROM " . SMILIES_TABLE . "	ORDER BY {$fields}_order";
			$sql = (DB_LEVEL == 'phpbb2') ? $sql2 : $sql3;
			$result = $db->sql_query($sql);
		break;

		case 'phpbb3':
			$sql = "SELECT *
				FROM " . $table . "
				ORDER BY {$fields}_order";
			$result = $db->sql_query($sql);
		break;
	}
	if( !$result )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain smileys from database", "", __LINE__, __FILE__, $sql);
	}

	if(!$smilies= $db->sql_fetchrowset($result))
	 {
		$sql = "SELECT *
			FROM " . SMILIES_TABLE;		
		$result = $db->sql_query($sql);
		$smilies = $db->sql_fetchrowset($result);		
	 }	
	$s_hidden_fields = '<input type="hidden" name="mode" value="savenew" />';

	$template->set_filenames(array(
		"body" => "admin/smile_list_body.tpl")
	);

	$template->assign_vars(array(
		"L_ACTION" => $lang['Action'],
		"L_SMILEY_TITLE" => $lang['smiley_title'],
		"L_SMILEY_TEXT" => $lang['smile_desc'],
		"L_DELETE" => $lang['Delete'],
		"L_EDIT" => $lang['Edit'],
		"L_SMILEY_ADD" => $lang['smile_add'],
		"L_CODE" => $lang['Code'],
		"L_EMOT" => $lang['Emotion'],
		'L_WIDTH' => $lang['Width'],
		'L_HEIGHT' => $lang['Height'],
		'L_ORDER' => $lang['Order'],
		"L_SMILE" => $lang['Smile'],
		"L_IMPORT_PACK" => $lang['import_smile_pack'],
		"L_EXPORT_PACK" => $lang['export_smile_pack'],
		
		// Smilies ORDER BEGIN
		'L_MOVE' => $lang['Move'],
		'L_MOVE_UP' => $lang['MOVE_UP'],
		'L_MOVE_DOWN' => $lang['MOVE_DOWN'],
		'L_MOVE_TOP' => $lang['Move_top'],
		'L_MOVE_END' => $lang['Move_end'],
		'L_POSITION_NEW_SMILIES' => $lang['position_new_smilies'],
		'L_SMILEY_CHANGE_POSITION' => $lang['smiley_change_position'],
		'L_SMILEY_CONFIG' => $lang['smiley_config'],

		'POSITION_SELECT' => $position_select,
		'S_POSITION_ACTION' => append_sid('admin_smilies.' . $phpEx . '?option=select'),
		// Smilies ORDER END
		
		"S_HIDDEN_FIELDS" => $s_hidden_fields,
		"S_SMILEY_ACTION" => append_sid("admin_smilies.$phpEx"))
	);

	//
	// Loop throuh the rows of smilies setting block vars for the template.
	//
	for($i = 0; $i < $c = count($smilies); $i++)
	{
		//
		// Replace htmlentites for < and > with actual character.
		//
		$smilies[$i]['code'] = str_replace('&lt;', '<', $smilies[$i]['code']);
		$smilies[$i]['code'] = str_replace('&gt;', '>', $smilies[$i]['code']);

		$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

		$template->assign_block_vars("smiles", array(
			"ROW_COLOR" => "#" . $row_color,
			"ROW_CLASS" => $row_class,

			"SMILEY_IMG" =>  $smiley_path_url . $board_config['smilies_path'] . '/' . $smilies[$i][$smiley_url],
			"CODE" => $smilies[$i]['code'],
			"EMOT" => $smilies[$i][$emotion],

			'WIDTH'		=> (DB_BACKEND === 'phpbb3') ? $smilies[$i][$fields .'_width'] : '',
			'HEIGHT'	=> (DB_BACKEND === 'phpbb3') ? $smilies[$i][$fields . '_height'] : '',
			'ORDER'	=> (DB_BACKEND === 'phpbb3') ? $smilies[$i][$fields .'_order'] : '',
			
			// Smilies ORDER BEGIN
			'U_SMILEY_MOVE_UP' => append_sid('admin_smilies.' . $phpEx . '?move=up&amp;id=' . $smilies[$i]['smilies_id']),
			'U_SMILEY_MOVE_DOWN' => append_sid('admin_smilies.' . $phpEx . '?move=down&amp;id=' . $smilies[$i]['smilies_id']),
			'U_SMILEY_MOVE_TOP' => append_sid('admin_smilies.' . $phpEx . '?send=top&amp;id=' . $smilies[$i]['smilies_id']),
			'U_SMILEY_MOVE_END' => append_sid('admin_smilies.' . $phpEx . '?send=end&amp;id=' . $smilies[$i]['smilies_id']),
			// Smilies ORDER END
			
			"U_SMILEY_EDIT" => append_sid("admin_smilies.$phpEx?mode=edit&amp;id=" . $smilies[$i][$fields . '_id']),
			"U_SMILEY_DELETE" => append_sid("admin_smilies.$phpEx?mode=delete&amp;id=" . $smilies[$i][$fields . '_id']))
		);
	}

	//
	// Spit out the page.
	//
	$template->pparse("body");
}

//
// Page Footer
//
include_once($phpbb_root_path . 'admin/page_footer_admin.' . $phpEx);

?>