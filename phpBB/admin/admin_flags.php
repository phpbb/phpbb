<?php
/***************************************************************************
 *                              admin_flags.php
 *                            -------------------
 *   begin                : Thursday, February 6, 2003
 *   written by Nuttzy
 *  @copyright (c) RMcGirr83, Nuttzy, FlorinCB aka orynider
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

define('IN_PHPBB', 1);

if( !empty($setmodules) )
{
	$file = basename(__FILE__);
	$module['Forum_Display']['Flags'] = "$file";
	return;
}

//
// Let's set the root dir for phpBB
//
$phpbb_root_path = "./../";
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);
/* FLAG-start * /
@define('FLAG_TABLE', $table_prefix.'flags');
/* FLAG-end */
if( isset($_GET['mode']) || isset($_POST['mode']) )
{
	$mode = ($_GET['mode']) ? $_GET['mode'] : $_POST['mode'];
}
else 
{
	//
	// These could be entered via a form button
	//
	if( isset($_POST['add']) )
	{
		$mode = "add";
	}
	else if( isset($_POST['save']) )
	{
		$mode = "save";
	}
	else
	{
		$mode = "";
	}
}

// if we are are doing a delete make sure we got confirmation
if ( $mode == 'do_delete')
{
	// user bailed out, return to flag admin
	if ( !$_POST['confirm'] )
	{
		$mode = '' ;
	}
}

/* START Include language file */
$language = ($user->user_language_name) ? $user->user_language_name : (($board_config['default_lang']) ? $board_config['default_lang'] : 'english');

if ((@include $phpbb_root_path . "language/lang_" . $language . "/lang_admin_flags.$phpEx") === false)
{
	if ((@include $phpbb_root_path . "language/lang_english/lang_admin_flags.$phpEx") === false)
	{
		message_die(CRITICAL_ERROR, 'Language file ' . $phpbb_root_path . "language/lang_" . $language . "/lang_admin_flags.$phpEx" . ' couldn\'t be opened.');
	}
	$language = 'english'; 
} 

if( $mode != "" )
{
	if( $mode == "edit" || $mode == "add" )
	{
		//
		// They want to add a new flag, show the form.
		//
		$flag_id = ( isset($_GET['id']) ) ? intval($_GET['id']) : 0;
		
		$s_hidden_fields = "";
		
		if( $mode == "edit" )
		{
			if( empty($flag_id) )
			{
				message_die(GENERAL_MESSAGE, $lang['Must_select_flag']);
			}

			$sql = "SELECT * FROM " . FLAG_TABLE . "
				WHERE flag_id = $flag_id";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Couldn't obtain flag data", "", __LINE__, __FILE__, $sql);
			}
			
			$flag_info = $db->sql_fetchrow($result);
			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $flag_id . '" />';

		}

		$s_hidden_fields .= '<input type="hidden" name="mode" value="save" />';

		$template->set_filenames(array(
			"body" => "admin/flags_edit_body.tpl")
		);

		$template->assign_vars(array(
			"FLAG" => $flag_info['flag_name'],
			"IMAGE" => ( $flag_info['flag_image'] != "" ) ? $flag_info['flag_image'] : "",
			"IMAGE_DISPLAY" => ( $flag_info['flag_image'] != "" ) ? '<img src="../images/flags/' . $flag_info['flag_image'] . '" />' : "",
			
			"L_FLAGS_TITLE" => $lang['Flags_title'],
			"L_FLAGS_TEXT" => $lang['Flags_explain'],
			"L_FLAG_NAME" => $lang['Flag_name'],
			"L_FLAG_IMAGE" => $lang['Flag_image'],
			"L_FLAG_IMAGE_EXPLAIN" => $lang['Flag_image_explain'],
			"L_SUBMIT" => $lang['Submit'],
			"L_RESET" => $lang['Reset'],
			
			"S_FLAG_ACTION" => append_sid("admin_flags.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);
		
	}
	else if( $mode == "save" )
	{
		//
		// Ok, they sent us our info, let's update it.
		//		
		$flag_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : 0;
		$flag_name = ( isset($_POST['title']) ) ? trim($_POST['title']) : "";
		$flag_image = ( (isset($_POST['flag_image'])) ) ? trim($_POST['flag_image']) : "";

		if( $flag_name == "" )
		{
			message_die(GENERAL_MESSAGE, $lang['Must_select_flag']);
		}

		//
		// The flag image has to be a jpg, gif or png
		//
		if($flag_image != "")
		{
			if ( !preg_match("/(\.gif|\.png|\.jpg)$/is", $flag_image))
			{
				$flag_image = "";
			}
		}

		if ($flag_id)
		{
			$sql = "UPDATE " . FLAG_TABLE . "
				SET flag_name = '" . str_replace("\'", "''", $flag_name) . "', flag_image = '" . str_replace("\'", "''", $flag_image) . "'
				WHERE flag_id = $flag_id";

			$message = $lang['Flag_updated'];
		}
		else
		{
			$sql = "INSERT INTO " . FLAG_TABLE . " (flag_name, flag_image)
				VALUES ('" . str_replace("\'", "''", $flag_name) . "', '" . str_replace("\'", "''", $flag_image) . "')";

			$message = $lang['Flag_added'];
		}
		
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update/insert into flags table", "", __LINE__, __FILE__, $sql);
		}

		$message .= "<br /><br />" . sprintf($lang['Click_return_flagadmin'], "<a href=\"" . append_sid("admin_flags.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);

	}
	else if( $mode == 'delete' )
	{
		if( isset($_POST['id']) || isset($_GET['id']) )
		{
			$flag_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : intval($_GET['id']);
		}
		else
		{
			$flag_id = 0;
		}
		$hidden_fields = '<input type="hidden" name="id" value="' . $flag_id . '" /><input type="hidden" name="mode" value="do_delete" />';

		//
		// Set template files
		//
		$template->set_filenames(array(
			'body' => 'confirm_body.tpl')
		);

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $lang['Flag_confirm'],
			'MESSAGE_TEXT' => $lang['Confirm_delete_flag'],

			'L_YES' => $lang['Yes'],
			'L_NO' => $lang['No'],

			'S_CONFIRM_ACTION' => append_sid("admin_flags.$phpEx"),
			'S_HIDDEN_FIELDS' => $hidden_fields)
		);

	}
	else if( $mode == 'do_delete' )
	{
		//
		// Ok, they want to delete their flag
		//	
		if( isset($_POST['id']) || isset($_GET['id']) )
		{
			$flag_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : intval($_GET['id']);
		}
		else
		{
			$flag_id = 0;
		}
		
		if( $flag_id )
		{
			// get the doomed flag's info
			$sql = "SELECT * FROM " . FLAG_TABLE . " 
				WHERE flag_id = $flag_id" ;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't get flag data", "", __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);
			$flag_image = $row['flag_image'] ;


			// delete the flag
			$sql = "DELETE FROM " . FLAG_TABLE . "
				WHERE flag_id = $flag_id";		
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete flag data", "", __LINE__, __FILE__, $sql);
			}
			
			// update the users who where using this flag			
			$sql = "UPDATE " . USERS_TABLE . " 
				SET user_from_flag = 'blank.gif' 
				WHERE user_from_flag = '$flag_image'";
			if( !$result = $db->sql_query($sql) ) 
			{
				message_die(GENERAL_ERROR, $lang['No_update_flags'], "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['Flag_removed'] . "<br /><br />" . sprintf($lang['Click_return_flagadmin'], "<a href=\"" . append_sid("admin_flags.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['Must_select_flag']);
		}
	}
	else
	{
		//
		// They didn't feel like giving us any information. Oh, too bad, we'll just display the
		// list then...
		//
		$template->set_filenames(array(
			"body" => "admin/flags_list_body.tpl")
		);
		
		$sql = "SELECT * FROM " . FLAG_TABLE . "
			ORDER BY flag_name";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain flags data", "", __LINE__, __FILE__, $sql);
		}
		
		$flag_rows = $db->sql_fetchrowset($result);
		$flag_count = count($flag_rows);
		
		$template->assign_vars(array(
			"L_FLAGS_TITLE" => $lang['Flags_title'],
			"L_FLAGS_TEXT" => $lang['Flags_explain'],
			"L_FLAG" => $lang['Flag_name'],

			"L_EDIT" => $lang['Edit'],
			"L_DELETE" => $lang['Delete'],
			"L_ADD_FLAG" => $lang['Add_new_flag'],
			"L_ACTION" => $lang['Action'],
			
			"S_FLAGS_ACTION" => append_sid("admin_flags.$phpEx"))
		);
		
		for( $i = 0; $i < $flag_count; $i++)
		{
			$flag = $flag_rows[$i]['flag_name'];
			$flag_id = $flag_rows[$i]['flag_id'];
			
			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
	
			$template->assign_block_vars("flags", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,

				"FLAG" => $flag,
				"IMAGE_DISPLAY" => ( $flag_rows[$i]['flag_image'] != "" ) ? '<img src="../images/flags/' . $flag_rows[$i]['flag_image'] . '" />' : "",

				"U_FLAG_EDIT" => append_sid("admin_flags.$phpEx?mode=edit&amp;id=$flag_id"),
				"U_FLAG_DELETE" => append_sid("admin_flags.$phpEx?mode=delete&amp;id=$flag_id"))
			);
		}
	}
}
else
{	
	/**
	 * function decode_lang from mx_traslator phpBB3 Extension
	 *
	 * $default_lang = $user->decode_country_name($board_config['default_lang']);
	 *
	 * @param unknown_type $lang
	 * @return unknown
	 */
	function decode_country_name($file_dir, $lang_country = 'country')
	{
		/* known languages */
		switch($file_dir)			
		{
				case 'aa':
					$lang_name = 'afar';
					$country_name = 'AFAR'; //Ethiopia
				break;

				case 'ab':
					$lang_name = 'abkhazian';
					$country_name = '';
				break;

				case 'ad':
					$lang_name = 'Angola';
					$country_name = 'ANGOLA';
				break;

				case 'ae':
					$lang_name = 'avestan';
					$country_name = 'United Arab Emirates';
				break;

				case 'af':
					$country_name = 'AFGHANISTAN'; // langs: pashto and dari
					$lang_name = 'AFRIKAANS'; // speakers: 6,855,082 - 13,4%
				break;

				case 'ag':
					$lang_name = ' english-creole';
					$country_name = 'Antigua &amp; Barbuda';
				break;
				
				case 'ai':
					$lang_name = 'Anguilla';
					$country_name = 'Anguilla';
				break;
				
				case 'ak':
					$lang_name = 'akan';
					$country_name = '';
				break;

				case 'al':
					$lang_name = 'albanian';
					$country_name = 'ALBANIA';
				break;


				case 'am':
					$lang_name = 'amharic';
					//$lang_name = 'armenian';
					$country_name = 'Armenia';
				break;

				case 'an':
					$lang_name = 'aragonese'; //
					//$country_name = 'Andorra';
					$country_name = 'Netherland Antilles';
				break;
				
				case 'ao':
					$lang_name = 'angolian';
					$country_name = 'Angola';
				break;
				
				case 'ap':
					$lang_name = 'angika';
					$country_name = 'Anga'; //India
				break;

				case 'ar':
					$lang_name = 'arabic';
					$country_name = 'Argentina';
				break;



				case 'as':
					$lang_name = 'assamese';
					$country_name = 'American Samoa';
				break;

				case 'at':
					$lang_name = 'german';
					$country_name = 'Austria';
				break;

				case 'av':
					$lang_name = 'avaric';
					$country_name = '';
				break;

				case 'ay':
					$lang_name = 'aymara';
					$country_name = '';
				break;

				case 'aw':
					$lang_name = 'aruba';
					$country_name = 'Aruba';
				break;

				case 'au':
					$lang_name = 'en-au'; //
					$country_name = 'Australia';
				break;

				case 'az':
					$lang_name = 'azerbaijani';
					$country_name = 'Azerbaijan';
				break;

				case 'ba':
					$lang_name = 'bashkir'; //Baskortostán (Rusia)
					$country_name = 'Bosnia &amp; Herzegovina'; //Bosnian, Croatian, Serbian
				break;

				case 'bb':
					$lang_name = 'Barbados';
					$country_name = 'Barbados';
				break;

				case 'bd':
					$lang_name = 'Bangladesh';
					$country_name = 'Bangladesh';
				break;

				case 'be':
					$lang_name = 'belarusian';
					$country_name = 'Belgium';
				break;

				case 'bf':
					$lang_name = 'Burkina Faso';
					$country_name = 'Burkina Faso';
				break;
				
				case 'bg':
					$lang_name = 'bulgarian';
					$country_name = 'Bulgaria';
				break;

				case 'bh':
					$lang_name = 'bhojpuri'; // Bihar (India) 
					$country_name = 'Bahrain'; // Mamlakat al-Ba?rayn (arabic)
				break;

				case 'bi':
					$lang_name = 'bislama';
					$country_name = 'Burundi';
				break;


				case 'bj':
					$lang_name = 'Benin';
					$country_name = 'Benin';
				break;
								
				case 'bl':
					$lang_name = 'Bonaire';
					$country_name = 'Bonaire';
				break;				
				
				case 'bm':
					$lang_name = 'bambara';
					$country_name = 'Bermuda';
				break;

				case 'bn':
					$country_name = 'Brunei';
					$lang_name = 'bengali';

				break;
				case 'bo':
					$lang_name = 'tibetan';
					$country_name = 'Bolivia';
				break;


				case 'br':
					$lang_name = 'breton';
					$country_name = 'Brazil'; //pt
				break;


				case 'bs':
					$lang_name = 'bosnian';
					$country_name = 'Bahamas';
				break;

				case 'bt':
					$lang_name = 'Bhutan';
					$country_name = 'Bhutan';
				break;

				case 'bw':
					$lang_name = 'Botswana';
					$country_name = 'Botswana';
				break;

				case 'bz':
					$lang_name = 'Belize';
					$country_name = 'Belize';
				break;

				case 'by':
					$lang_name = 'belarusian';
					$country_name = 'Belarus';
				break;


				case 'cm':
					$lang_name = 'Cameroon';
					$country_name = 'Cameroon';
				break;

				case 'ca':
					$lang_name = 'catalan';
					$country_name = 'Canada';
				break;

				case 'cd':
					$lang_name = 'Congo Democratic Republic';
					$country_name = 'Congo Democratic Republic';
				break;


				case 'cf':
					$lang_name = 'Central African Republic';
					$country_name = 'Central African Republic';
				break;

				case 'cg':
					$lang_name = 'Congo';
					$country_name = 'Congo';
				break;

				case 'ci':
					$lang_name = 'Cote D-Ivoire';
					$country_name = 'Cote D-Ivoire';
				break;

				case 'cl':
					$lang_name = 'Chile';
					$country_name = 'Chile';
				break;

				case 'cn':
					$lang_name = 'China';
					$country_name = 'CHINA';
				break;

				case 'co':
					$lang_name = 'corsican'; // Corsica
					$country_name = 'Columbia';
				break;




				case 'cr':
					$lang_name = 'cree';
					$country_name = 'Costa Rica';
				break;

				case 'cs':
					$lang_name = 'czech';
					$country_name = 'Czech Republic';
				break;

				case 'cu':
					$lang_name = 'slavonic';
					$country_name = 'Cuba'; //langs: 
				break;

				case 'cv':
					$country_name = 'Cape Verde';
					$lang_name = 'chuvash';

				break;

				case 'cy':
					$lang_name = 'Cyprus';
					$country_name = 'Cyprus';
				break;

				case 'cz':
					$lang_name = 'Czech Republic';
					$country_name = 'Czech Republic';
				break;

				case 'da':
					$lang_name = 'danish';
					$country_name = 'Denmark';
				break;

				case 'de':
					$lang_name = 'german';
					$country_name = 'Germany';
				break;
				
				case 'dk':
					$lang_name = 'danish';
					$country_name = 'Denmark';
				break;


				case 'dm':
					$lang_name = 'Dominica';
					$country_name = 'Dominica';
				break;

				case 'do':
					$lang_name = 'Dominican Republic';
					$country_name = 'Dominican Republic';
				break;

				case 'dj':
					$lang_name = 'Djibouti';
					$country_name = 'Djibouti';
				break;

				case 'dv':
					$lang_name = 'divehi';
					$country_name = '';
				break;

				case 'dz':
					$lang_name = 'dzongkha';
					$country_name = 'Algeria';
				break;

				case 'tl':
					$country_name = 'East Timor';
					$lang_name = 'East Timor';
				break;

				case 'ec':
					$country_name = 'Ecuador';
					$lang_name = 'Ecuador';
				break;

				case 'eg':
					$country_name = 'Egypt';
					$lang_name = 'Egypt';
				break;





				case 'ee':
					$lang_name = 'Estonia';
					$country_name = 'Estonia';
				break;

				case 'en_us':
					$lang_name = 'en-us';
					$country_name = 'United States of America';
				break;

				case 'eo':
					$lang_name = 'esperanto';
					$country_name = '';
				break;

				case 'er':
					$lang_name = 'Eritrea';
					$country_name = 'Eritrea';
				break;

				case 'es':
					$lang_name = 'spanish';
					$country_name = 'Spain';
				break;

				case 'et':
					$lang_name = 'estonian';
					$country_name = 'ESTONIA';
				break;

				case 'eu':
					$lang_name = 'basque';
					$country_name = '';
				break;

				case 'fa':
					$lang_name = 'persian';
					$country_name = '';
				break;

				case 'ff':
					$lang_name = 'fulah';
					$country_name = '';
				break;



				case 'fi':
					$lang_name = 'finnish';
					$country_name = 'Finland';
				break;

				case 'fj':
					$lang_name = 'fijian';
					$country_name = 'Fiji';
				break;

				case 'fk':
					$lang_name = 'falklandian';
					$country_name = 'Falkland Islands';
				break;


				case 'fm':
					$lang_name = 'Micronesia';
					$country_name = 'Micronesia';
				break;

				case 'fo':
					$lang_name = 'faroese';
					$country_name = 'Faroe Islands';
				break;

				case 'fr':
					$lang_name = 'french';
					$country_name = 'France';
				break;

				case 'fy':
					$lang_name = 'frisian';
					$country_name = '';
				break;

				case 'ga':
					$lang_name = 'irish';
					$country_name = 'Gabon';
				break;

				case 'gb':
					$lang_name = 'Great Britain';
					$country_name = 'Great Britain';
				break;

				case 'gd':
					$lang_name = 'scottish';
					$country_name = 'GRENADA';
				break;

				case 'ge':
					$lang_name = 'Georgia';
					$country_name = 'Georgia';
				break;

				case 'gm':
					$lang_name = 'Gambia';
					$country_name = 'Gambia';
				break;



				case 'gh':
					$lang_name = 'Ghana';
					$country_name = 'Ghana';
				break;



				case 'gr':
					$lang_name = 'Greece';
					$country_name = 'Greece';
				break;

				case 'gl':
					$lang_name = 'galician';
					$country_name = 'Greenland';
				break;

				case 'gd':
					$lang_name = 'Grenada';
					$country_name = 'Grenada';
				break;

				case 'gt':
					$lang_name = 'Guatemala';
					$country_name = 'Guatemala';
				break;

				case 'gn':
					$lang_name = 'Guinea';
					$country_name = 'Guinea';
				break;

				case 'gq':
					$lang_name = 'Equatorial Guinea';
					$country_name = 'Equatorial Guinea';
				break;

				case 'gu':
					$lang_name = 'gujarati';
					$country_name = 'Guam';
				break;

				case 'gv':
					$lang_name = 'manx';
					$country_name = '';
				break;
				
				case 'gw':
					$lang_name = 'Guinea Bissau';
					$country_name = 'Guinea Bissau';
				break;

				case 'gy':
					$lang_name = 'Guyana';
					$country_name = 'Guyana';
				break;

				case 'ha':
					$country_name = '';
					$lang_name = 'hausa';
				break;


				case 'he':
					$country_name = 'Israel';
					$lang_name = 'hebrew';
				break;

				case 'hi':
					$lang_name = 'hindi';
					$country_name = '';
				break;

				case 'ho':
					$lang_name = 'hiri_motu';
					$country_name = '';
				break;

				case 'hk':
					$lang_name = 'Hong Kong';
					$country_name = 'Hong Kong';
				break;

				case 'hn':
					$country_name = 'Honduras';
					$lang_name = 'Honduras';
				break;

				case 'hr':
					$lang_name = 'croatian';
					$country_name = 'Croatia';
				break;

				case 'ht':
					$lang_name = 'haitian';
					$country_name = 'Haiti';
				break;

				case 'ho':
					$lang_name = 'hiri_motu';
					$country_name = '';
				break;

				case 'hu':
					$lang_name = 'hungarian';
					$country_name = 'Hungary';
				break;

				case 'hy':
					$lang_name = 'armenian';
					$country_name = '';
				break;

				case 'hz':
					$lang_name = 'herero';
					$country_name = '';
				break;

				case 'ia':
					$lang_name = 'interlingua';
					$country_name = '';
				break;

				case 'id':
					$lang_name = 'indonesian';
					$country_name = 'Indonesia';
				break;

				case 'ie':
					$lang_name = 'interlingue';
					$country_name = 'Ireland';
				break;

				case 'ig':
					$lang_name = 'igbo';
					$country_name = '';
				break;

				case 'ii':
					$lang_name = 'sichuan_yi';
					$country_name = '';
				break;

				case 'ik':
					$lang_name = 'inupiaq';
					$country_name = '';
				break;

				case 'il':
					$lang_name = 'ibrit';
					$country_name = 'Israel';
				break;

				case 'im':
					$lang_name = 'Isle of Man';
					$country_name = 'Isle of Man';
				break;

				case 'in':
					$lang_name = 'India';
					$country_name = 'India';
				break;


				case 'it':
					$lang_name = 'italian';
					$country_name = 'Italy';
				break;

				case 'iq':
					$lang_name = 'Iraq';
					$country_name = 'Iraq';
				break;

				case 'ir':
					$lang_name = 'Iran';
					$country_name = 'Iran';
				break;

				case 'is':
					$lang_name = 'Iceland';
					$country_name = 'Iceland';
				break;


				case 'jv':
					$lang_name = 'javanese';
					$country_name = '';
				break;

				case 'jm':
					$lang_name = 'Jamaica';
					$country_name = 'Jamaica';
				break;

				case 'jp':
					$lang_name = 'japanese';
					$country_name = 'Japan';
				break;

				case 'jo':
					$lang_name = 'Jordan';
					$country_name = 'Jordan';
				break;

				case 'kh':
					$lang_name = 'Cambodia';
					$country_name = 'Cambodia';
				break;

				case 'ke':
					$lang_name = 'Kenya';
					$country_name = 'Kenya';
				break;

				case 'ki':
					$lang_name = 'Kiribati';
					$country_name = 'Kiribati';
				break;

				case 'km':
					$lang_name = 'Comoros';
					$country_name = 'Comoros';
				break;

				case 'kn':
					$lang_name = 'kannada';
					$country_name = 'St Kitts-Nevis';
				break;

				case 'ko':
					$lang_name = 'korean';
					$country_name = 'Korea North';
				break;

				case 'ky':
					$lang_name = 'Cayman Islands';
					$country_name = 'Cayman Islands';
				break;

				case 'kz':
					$lang_name = 'Kazakhstan';
					$country_name = 'Kazakhstan';
				break;

				case 'ks':
					$lang_name = 'kashmiri'; //Kashmir
					$country_name = 'Korea South';
				break;

				case 'kw':
					$lang_name = 'Kuwait';
					$country_name = 'Kuwait';
				break;

				case 'kg':
					$lang_name = 'Kyrgyzstan';
					$country_name = 'Kyrgyzstan';
				break;

				case 'la':
					$lang_name = 'Laos';
					$country_name = 'Laos';
				break;

				case 'lk':
					$lang_name = 'Sri Lanka';
					$country_name = 'Sri Lanka';
				break;

				case 'lv':
					$lang_name = 'Latvia';
					$country_name = 'Latvia';
				break;

				case 'lb':
					$lang_name = 'Lebanon';
					$country_name = 'Lebanon';
				break;

				case 'ls':
					$lang_name = 'Lesotho';
					$country_name = 'Lesotho';
				break;

				case 'lr':
					$lang_name = 'Liberia';
					$country_name = 'Liberia';
				break;

				case 'ly':
					$lang_name = 'Libya';
					$country_name = 'Libya';
				break;

				case 'li':
					$lang_name = 'Liechtenstein';
					$country_name = 'Liechtenstein';
				break;

				case 'lt':
					$country_name = 'Lithuania';
					$lang_name = 'Lithuania';
				break;

				case 'lu':
					$lang_name = 'Luxembourg';
					$country_name = 'Luxembourg';
				break;

				case 'mo':
					$lang_name = 'Macau';
					$country_name = 'Macau';
				break;

				case 'mk':
					$lang_name = 'Macedonia';
					$country_name = 'Macedonia';
				break;
				case 'mg':
					$lang_name = 'Madagascar';
					$country_name = 'Madagascar';
				break;

				case 'mw':
					$country_name = 'Malawi';
					$lang_name = 'Malawi';
				break;

				case 'my':
					$lang_name = 'Malaysia';
					$country_name = 'Malaysia';
				break;

				case 'mv':
					$lang_name = 'Maldives';
					$country_name = 'Maldives';
				break;

				case 'ml':
					$lang_name = 'Mali';
					$country_name = 'Mali';
				break;

				case 'mt':
					$lang_name = 'Malta';
					$country_name = 'Malta';
				break;

				case 'mh':
					$lang_name = 'Marshall Islands';
					$country_name = 'Marshall Islands';
				break;

				case 'mr':
					$lang_name = 'Mauritania';
					$country_name = 'Mauritania';
				break;

				case 'mu':
					$lang_name = 'Mauritius';
					$country_name = 'Mauritius';
				break;

				case 'mx':
					$lang_name = 'Mexico';
					$country_name = 'Mexico';
				break;

				case 'md':
					$country_name = 'Moldova';
					$lang_name = 'Moldova';
				break;

				case 'mc':
					$country_name = 'Monaco';
					$lang_name = 'Monaco';
				break;

				case 'mn':
					$lang_name = 'Mongolia';
					$country_name = 'Mongolia';
				break;

				case 'ms':
					$lang_name = 'Montserrat';
					$country_name = 'Montserrat';
				break;

				case 'ma':
					$lang_name = 'Morocco';
					$country_name = 'Morocco';
				break;

				case 'mz':
					$lang_name = 'Mozambique';
					$country_name = 'Mozambique';
				break;

				case 'mm':
					$lang_name = 'Myanmar';
					$country_name = 'Myanmar';
				break;

				case 'na':
					$lang_name = 'Nambia';
					$country_name = 'Nambia';
				break;

				case 'nk':
					$lang_name = 'Korea North';
					$country_name = 'Korea North';
				break;

				case 'nr':
					$lang_name = 'Nauru';
					$country_name = 'Nauru';
				break;
				case 'np':
					$lang_name = 'Nepal';
					$country_name = 'Nepal';
				break;

				case 'nl':
					$lang_name = 'Netherlands';
					$country_name = 'Netherlands';
				break;
				case 'nz':
					$lang_name = 'New Zealand';
					$country_name = 'New Zealand';
				break;
				case 'ni':
					$lang_name = 'Nicaragua';
					$country_name = 'Nicaragua';
				break;
				case 'ne':
					$lang_name = 'Niger';
					$country_name = 'Niger';
				break;
				case 'ng':
					$lang_name = 'Nigeria';
					$country_name = 'Nigeria';
				break;
				case 'nf':
					$lang_name = 'Norfolk Island';
					$country_name = 'Norfolk Island';
				break;
				case 'no':
					$lang_name = 'Norway';
					$country_name = 'Norway';
				break;

				case 'oc':
					$lang_name = 'occitan';
					$country_name = '';
				break;

				case 'oj':
					$lang_name = 'ojibwa';
					$country_name = '';
				break;

				case 'om':
					$lang_name = 'Oman';
					$country_name = 'Oman';
				break;

				case 'or':
					$lang_name = 'oriya';
					$country_name = '';
				break;

				case 'os':
					$lang_name = 'ossetian';
					$country_name = '';
				break;

				case 'pa':
					$country_name = 'Panama';
					$lang_name = 'Panama';
				break;


				case 'pe':
					$country_name = 'Peru';
					$lang_name = 'Peru';
				break;

				case 'ph':
					$lang_name = 'Philippines';
					$country_name = 'Philippines';
				break;

				case 'pg':
					$country_name = 'Papua New Guinea';
					$lang_name = 'Papua New Guinea';
				break;

				case 'pi':
					$lang_name = 'pali';
					$country_name = '';
				break;


				case 'pl':
					$lang_name = 'Poland';
					$country_name = 'Poland';
				break;

				case 'pn':
					$lang_name = 'Pitcairn Island';
					$country_name = 'Pitcairn Island';
				break;

				case 'pr':
					$lang_name = 'Puerto Rico';
					$country_name = 'Puerto Rico';
				break;

				case 'pt':
					$lang_name = 'Portugal';
					$country_name = 'Portugal';
				break;



				case 'pk':
					$lang_name = 'Pakistan';
					$country_name = 'Pakistan';
				break;

				case 'pw':
					$country_name = 'Palau Island';
					$lang_name = 'Palau Island';
				break;

				case 'ps':
					$country_name = 'Palestine';
					$lang_name = 'Palestine';
				break;

				case 'py':
					$country_name = 'Paraguay';
					$lang_name = 'Paraguay';
				break;

				case 'qa':
					$lang_name = 'Qatar';
					$country_name = 'Qatar';
				break;

				case 'ro':
					$country_name = 'Romania';
					$lang_name = 'romanian';
				break;

				case 'rn':
					$lang_name = 'kirundi';

				break;

				case 'rm':
					$country_name = '';
					$lang_name = 'romansh'; //Switzerland
				break;


				case 'ri':
					$country_name = 'romani';
					$lang_name = 'romani';
				break;

				case 'ru':
					$country_name = 'Russia';
					$lang_name = 'Russia';
				break;
				case 'rw':
					$country_name = 'Rwanda';
					$lang_name = 'Rwanda';
				break;
				case 'ws':
					$country_name = 'Samoa';
					$lang_name = 'Samoa';
				break;
				case 'sm':
					$lang_name = 'San Marino';
					$country_name = 'San Marino';
				break;
				case 'st':
					$lang_name = 'Sao Tome &amp; Principe';
					$country_name = 'Sao Tome &amp; Principe';
				break;
				case 'sa':
					$lang_name = 'arabic';
					$country_name = 'Saudi Arabia';
				break;
				case 'sn':
					$lang_name = 'Senegal';
					$country_name = 'Senegal';
				break;
				case 'sc':
					$lang_name = 'Seychelles';
				break;
				case 'sl':
					$country_name = 'Sierra Leone';
					$lang_name = 'Sierra Leone';
				break;
				case 'sg':
					$country_name = 'Singapore';
					$lang_name = 'Singapore';
				break;
				case 'sk':
					$country_name = 'Slovakia';
					$lang_name = 'Slovakia';
				break;
				case 'si':
					$country_name = 'Slovenia';
					$country_name = 'Slovenia';
				break;
				case 'sb':
					$lang_name = 'Solomon Islands';
					$country_name = 'Solomon Islands';
				break;
				case 'so':
					$lang_name = 'Somalia';
					$country_name = 'Somalia';
				break;

				case 'sv':
					$lang_name = 'El Salvador';
					$country_name = 'El Salvador';
				break;

				case 'za':
					$lang_name = 'zhuang';
					$country_name = 'South Africa';
				break;

				case 'sh':
					$country_name = 'St Helena';
					$country_name = 'St Helena';
				break;
				case 'kn':
					$lang_name = 'St Kitts-Nevis';
					$country_name = 'St Kitts-Nevis';
				break;
				case 'lc':
					$lang_name = 'St Lucia';
					$country_name = 'St Lucia';
				break;
				case 'vc':
					$country_name = 'St Vincent &amp; Grenadines';
					$lang_name = 'St Vincent &amp; Grenadines';
				break;
				case 'sd':
					$lang_name = 'Sudan';
					$country_name = 'Sudan';
				break;
				case 'sr':
					$lang_name = 'Suriname';
					$country_name = 'Suriname';
				break;
				case 'sz':
					$lang_name = 'Swaziland';
					$country_name = 'Swaziland';
				break;
				case 'se':
					$lang_name = 'Sweden';
					$country_name = 'Sweden';
				break;
				case 'ch':
					$lang_name = 'Switzerland';
					$country_name = 'Switzerland';
				break;
				case 'sy':
					$lang_name = 'Syria';
					$country_name = 'Syria';
				break;
				case 'td':
					$lang_name = 'Chad';
					$country_name = 'Chad';
				break;
				case 'tw':
					$lang_name = 'Taiwan';
					$country_name = 'Taiwan';
				break;
				case 'tj':
					$lang_name = 'Tajikistan';
					$country_name = 'Tajikistan';
				break;
				case 'tz':
					$country_name = 'Tanzania';
					$lang_name = 'Tanzania';
				break;
				case 'th':
					$country_name = 'Thailand';
					$lang_name = 'Thailand';
				break;
				case 'tg':
					$lang_name = 'Togo';
					$country_name = 'Togo';
				break;
				case 'to':
					$country_name = 'Tonga';
					$lang_name = 'Tonga';
				break;
				case 'tt':
					$country_name = 'Trinidad &amp; Tobago';
					$lang_name = 'Trinidad &amp; Tobago';
				break;
				case 'tn':
					$lang_name = 'Tunisia';
					$country_name = 'Tunisia';
				break;

				case 'tr':
					$lang_name = 'Turkey';
					$country_name = 'Turkey';
				break;

				case 'tm':
					$lang_name = 'Turkmenistan';
					$country_name = 'Turkmenistan';
				break;

				case 'tc':
					$lang_name = 'Turks &amp; Caicos Is';
					$country_name = 'Turks &amp; Caicos Is';
				break;

				case 'tv':
					$lang_name = 'Tuvalu';
					$country_name = 'Tuvalu';
				break;

				case 'ug':
					$lang_name = 'Uganda';
					$country_name = 'Uganda';
				break;

				case 'ua':
					$lang_name = 'Ukraine';
					$country_name = 'Ukraine';
				break;

				case 'us':
					$lang_name = 'en-us';
					$country_name = 'United States of America';
				break;

				case 'uy':
					$lang_name = 'Uruguay';
					$country_name = 'Uruguay';
				break;

					$lang_name = 'Uzbekistan';
					$country_name = 'Uzbekistan';
				break;

				case 'vu':
					$lang_name = 'Vanuatu';
					$country_name = 'Vanuatu';
				break;

				case 've':
					$lang_name = 'Venezuela';
					$country_name = 'Venezuela';
				break;

				case 'vn':
					$lang_name = 'Vietnam';
					$country_name = 'Vietnam';
				break;

				case 'vg':
					$lang_name = 'Virgin Islands (Brit)';
					$country_name = 'Virgin Islands (Brit)';
				break;

				case 'vi':
					$lang_name = 'Virgin Islands (USA)';
					$country_name = 'Virgin Islands (USA)';
				break;
				case 'wls':
					$lang_name = 'Wales';
					$country_name = 'Wales';
				break;
				case 'eh':
					$lang_name = 'Western Sahara';
					$country_name = 'Western Sahara';
				break;
				case 'ye':
					$lang_name = 'Yemen';
					$country_name = 'Yemen';
				break;
				case 'zm':
					$lang_name = 'Zambia';
					$country_name = 'Zambia';
				break;
				case 'zw':
					$lang_name = 'Zimbabwe';
					$country_name = 'Zimbabwe';
				break;
				case 'zu':
					$lang_name = 'zulu';
					$country_name = 'zulu';
				break;
				default:
					$lang_name = $file_dir;
					$country_name = $file_dir;
				break;
		}
		return ($lang_country == 'country') ? $country_name : $lang_name;
	}
	
	/**
	 * Returns flag files list from an specific directory path
	 */
	if (!class_exists('phpbb_db_tools') && !class_exists('tools'))
	{
		global $phpbb_root_path, $phpEx;
		require($phpbb_root_path . 'includes/db/tools.' . $phpEx);
	}

	if (class_exists('phpbb_db_tools'))
	{
		$db_tools = new phpbb_db_tools($db);					
	}				
	elseif (class_exists('tools'))
	{
		$db_tools = new tools($db);					
	}	 
	
	$template->assign_vars(array(
		"L_FLAGS_TITLE" => $lang['Flags_title'],
		"L_FLAGS_TEXT" => $lang['Flags_explain'],
		"L_FLAG" => $lang['Flag_name'],
		"L_FLAG_PIC" => $lang['Flag_pic'],
		"L_EDIT" => $lang['Edit'],
		"L_DELETE" => $lang['Delete'],
		"L_ADD_FLAG" => $lang['Add_new_flag'],
		"L_ACTION" => $lang['Action'],
		
		"S_FLAGS_ACTION" => append_sid("admin_flags.$phpEx"))
	);	 

	//
	// Show the default page
	//
	$template->set_filenames(array(
		"body" => "admin/flags_list_body.tpl")
	);
	
	// get all countries installed
	$countries = array();
	$flag_rows = array();
	
	if (!is_object($db_tools) || (is_object($db_tools) && $db_tools->sql_table_exists($table_prefix . 'flags')))
	{ 	
		$sql = "SELECT * FROM " . FLAG_TABLE . "
			ORDER BY flag_id ASC";
		$sql = "SELECT *
			FROM " . FLAG_TABLE;			
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain flags data", "", __LINE__, __FILE__, $sql);
		}
		$flag_count = $db->sql_numrows($result);
		$flag_rows = $db->sql_fetchrowset($result);
		
		for( $i = 0; $i < $flag_count; $i++)
		{
			$flag = $flag_rows[$i]['flag_name'];
			$flag_id = $flag_rows[$i]['flag_id'];
				
			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
		
			$template->assign_block_vars("flags", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
					
				"FLAG" => $flag,
				"IMAGE_DISPLAY" => ( $flag_rows[$i]['flag_image'] != "" ) ? '<img src="../images/flags/' . $flag_rows[$i]['flag_image'] . '" />' : "",

				"U_FLAG_EDIT" => append_sid("admin_flags.$phpEx?mode=edit&amp;id=$flag_id"),
				"U_FLAG_DELETE" => append_sid("admin_flags.$phpEx?mode=delete&amp;id=$flag_id"))
			);
		}	
	}
	else
	{ 
		$flag_id = 1;
		//$flag_count = (bool) count(glob($phpbb_root_path . '/images/flags', GLOB_BRACE));
		$sql_ary[] = array();			
		$dir = @opendir($phpbb_root_path . '/images/flags');		
		while ($flag = @readdir($dir))
		{		
			if (preg_match('#^png#i', substr(strrchr($flag, '.'), 1)) && !is_file($phpbb_root_path . '/images/flags' . $flag) && !is_link($phpbb_root_path . '/images/flags' . $flag))
			{							
				$flag_id++;					
				$filename = basename($flag);
				$displayname = substr($filename, 0, strrpos($filename, '.'));
				//$displayname = trim(str_replace(substr(strrchr($flag_file, '.'), 1), '', $flag_file));				
				
				$displayname = preg_replace("/^(.*?)_(.*)$/", "\\1 [ \\2 ]", $displayname);
				$flag_name = preg_replace("/\[(.*?)_(.*)\]/", "[ \\1 - \\2 ]", $displayname);
			
				$country_name = ucfirst(decode_country_name(strtolower($flag_name)));	
									
				$flags[$flag_id] = $flag;				
				$countries[$flag] = $country_name;									
			}
		}
		@closedir($dir);		
		
		$flag_id = 1;		
		$flag_count = (bool) count($countries);	
 				
		foreach($countries as $flag => $country_name)
		{
			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];					
			
			$sql_ary[$flag_id] = array(
				'flag_id'			=> $flag_id,
				'flag_name'		=> $country_name,
				'flag_image'		=> $flag
			);								
			
			//$sql_ary = $db->sql_query('INSERT INTO ' . FLAG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));			
			$template->assign_block_vars("flags", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"FLAG" => isset($lang[$country_name]) ? $lang[$country_name] : $country_name,
				"IMAGE_DISPLAY" => ($flag) ? '<img src="../images/flags/' . $flag . '" />' : "",

				"U_FLAG_EDIT" => append_sid("admin_flags.$phpEx?mode=edit&amp;id=$flag_id"),
				"U_FLAG_DELETE" => append_sid("admin_flags.$phpEx?mode=delete&amp;id=$flag_id"))
			);
			$flag_id++;						
		}				
		
		$redirect_url = append_sid("admin_flags.$phpEx?add_flags=create_table", true);
		$message_info = '<p><span style="color: red;">Your flags table is not added to the DB and so You will not be able to enable all features that come in this pannel...</p><i><p>Adding the table is not reversible from this pannel! If you are aware of that, please click this link to proceed:</i></span> <a href="' . $redirect_url . '">click here to begin</a></p>'; 
		
		print($message_info);		
				
		if (is_request('add_flags'))
		{		
			//$sql = "DROP TABLE " . $table_prefix . "flags";
			$sql = "CREATE TABLE " . FLAG_TABLE . " (
				flag_id int(10) NOT NULL auto_increment,
				flag_name varchar(25) default NULL,
				flag_image varchar(25) default NULL,
				PRIMARY KEY (flag_id)
			)";
			
			// We could add error handling here...
			$result = $db->sql_query($sql);					
			if (!($result))
			{		
				message_die(CRITICAL_ERROR, "Could not add flags table to the DB", '', __LINE__, __FILE__, $sql);
			}
			/** /
			$sql_ary = array(
				array(
					'flag_name'		=> 'Afghanistan',
					'flag_image'	=> 'AF.png',
				),
				array(
					'flag_name'		=> 'Albania',
					'flag_image'	=> 'AL.png',
				),
				array(
					'flag_name'		=> 'Algeria',
					'flag_image'	=> 'DZ.png',
				),
				array(
					'flag_name'		=> 'American Samoa',
					'flag_image'	=> 'AS.png',
				),
				array(
					'flag_name'		=> 'Andorra',
					'flag_image'	=> 'AD.png',
				),
				array(
					'flag_name'		=> 'Angola',
					'flag_image'	=> 'AO.png',
				),
				array(
					'flag_name'		=> 'Anguilla',
					'flag_image'	=> 'AI.png',
				),
				array(
					'flag_name'		=> 'Antigua &amp; Barbuda',
					'flag_image'	=> 'AG.png',
				),
				array(
					'flag_name'		=> 'Argentina',
					'flag_image'	=> 'AR.png',
				),
				array(
					'flag_name'		=> 'Armenia',
					'flag_image'	=> 'AM.png',
				),
				array(
					'flag_name'		=> 'Aruba',
					'flag_image'	=> 'AW.png',
				),
				array(
					'flag_name'		=> 'Australia',
					'flag_image'	=> 'AU.png',
				),
				array(
					'flag_name'		=> 'Austria',
					'flag_image'	=> 'AT.png',
				),
				array(
					'flag_name'		=> 'Azerbaijan',
					'flag_image'	=> 'AZ.png',
				),
				array(
					'flag_name'		=> 'Bahamas',
					'flag_image'	=> 'BS.png',
				),
				array(
					'flag_name'		=> 'Bahrain',
					'flag_image'	=> 'BH.png',
				),
				array(
					'flag_name'		=> 'Bangladesh',
					'flag_image'	=> 'BD.png',
				),
				array(
					'flag_name'		=> 'Barbados',
					'flag_image'	=> 'BB.png',
				),
				array(
					'flag_name'		=> 'Belarus',
					'flag_image'	=> 'BY.png',
				),
				array(
					'flag_name'		=> 'Belgium',
					'flag_image'	=> 'BE.png',
				),
				array(
					'flag_name'		=> 'Belize',
					'flag_image'	=> 'BZ.png',
				),
				array(
					'flag_name'		=> 'Benin',
					'flag_image'	=> 'BJ.png',
				),
				array(
					'flag_name'		=> 'Bermuda',
					'flag_image'	=> 'BM.png',
				),
				array(
					'flag_name'		=> 'Bhutan',
					'flag_image'	=> 'BT.png',
				),
				array(
					'flag_name'		=> 'Bolivia',
					'flag_image'	=> 'BO.png',
				),
				array(
					'flag_name'		=> 'Bonaire',
					'flag_image'	=> 'BL.png',
				),
				array(
					'flag_name'		=> 'Bosnia &amp; Herzegovina',
					'flag_image'	=> 'BA.png',
				),
				array(
					'flag_name'		=> 'Botswana',
					'flag_image'	=> 'BW.png',
				),
				array(
					'flag_name'		=> 'Brazil',
					'flag_image'	=> 'BR.png',
				),
				array(
					'flag_name'		=> 'Brunei',
					'flag_image'	=> 'BN.png',
				),
				array(
					'flag_name'		=> 'Bulgaria',
					'flag_image'	=> 'BG.png',
				),
				array(
					'flag_name'		=> 'Burkina Faso',
					'flag_image'	=> 'BF.png',
				),
				array(
					'flag_name'		=> 'Burundi',
					'flag_image'	=> 'BI.png',
				),
				array(
					'flag_name'		=> 'Cambodia',
					'flag_image'	=> 'KH.png',
				),
				array(
					'flag_name'		=> 'Cameroon',
					'flag_image'	=> 'CM.png',
				),
				array(
					'flag_name'		=> 'Canada',
					'flag_image'	=> 'CA.png',
				),
				array(
					'flag_name'		=> 'Cape Verde',
					'flag_image'	=> 'CV.png',
				),
				array(
					'flag_name'		=> 'Cayman Islands',
					'flag_image'	=> 'KY.png',
				),
				array(
					'flag_name'		=> 'Central African Republic',
					'flag_image'	=> 'CF.png',
				),
				array(
					'flag_name'		=> 'Chad',
					'flag_image'	=> 'TD.png',
				),
				array(
					'flag_name'		=> 'Chile',
					'flag_image'	=> 'CL.png',
				),
				array(
					'flag_name'		=> 'China',
					'flag_image'	=> 'CN.png',
				),
				array(
					'flag_name'		=> 'Columbia',
					'flag_image'	=> 'CO.png',
				),
				array(
					'flag_name'		=> 'Comoros',
					'flag_image'	=> 'KM.png',
				),
				array(
					'flag_name'		=> 'Congo',
					'flag_image'	=> 'CG.png',
				),
				array(
					'flag_name'		=> 'Congo Democratic Republic',
					'flag_image'	=> 'CD.png',
				),
				array(
					'flag_name'		=> 'Costa Rica',
					'flag_image'	=> 'CR.png',
				),
				array(
					'flag_name'		=> 'Cote D-Ivoire',
					'flag_image'	=> 'CI.png',
				),
				array(
					'flag_name'		=> 'Croatia',
					'flag_image'	=> 'HR.png',
				),
				array(
					'flag_name'		=> 'Cuba',
					'flag_image'	=> 'CU.png',
				),
				array(
					'flag_name'		=> 'Cyprus',
					'flag_image'	=> 'CY.png',
				),
				array(
					'flag_name'		=> 'Czech Republic',
					'flag_image'	=> 'CZ.png',
				),
				array(
					'flag_name'		=> 'Denmark',
					'flag_image'	=> 'DK.png',
				),
				array(
					'flag_name'		=> 'Djibouti',
					'flag_image'	=> 'DJ.png',
				),
				array(
					'flag_name'		=> 'Dominica',
					'flag_image'	=> 'DM.png',
				),
				array(
					'flag_name'		=> 'Dominican Republic',
					'flag_image'	=> 'DO.png',
				),
				array(
					'flag_name'		=> 'East Timor',
					'flag_image'	=> 'TL.png',
				),
				array(
					'flag_name'		=> 'Ecuador',
					'flag_image'	=> 'EC.png',
				),
				array(
					'flag_name'		=> 'Egypt',
					'flag_image'	=> 'EG.png',
				),
				array(
					'flag_name'		=> 'El Salvador',
					'flag_image'	=> 'SV.png',
				),
				array(
					'flag_name'		=> 'Equatorial Guinea',
					'flag_image'	=> 'GQ.png',
				),
				array(
					'flag_name'		=> 'Eritrea',
					'flag_image'	=> 'ER.png',
				),
				array(
					'flag_name'		=> 'Estonia',
					'flag_image'	=> 'EE.png',
				),
				array(
					'flag_name'		=> 'Ethiopia',
					'flag_image'	=> 'ET.png',
				),
				array(
					'flag_name'		=> 'Falkland Islands',
					'flag_image'	=> 'FK.png',
				),
				array(
					'flag_name'		=> 'Faroe Islands',
					'flag_image'	=> 'FO.png',
				),
				array(
					'flag_name'		=> 'Fiji',
					'flag_image'	=> 'FJ.png',
				),
				array(
					'flag_name'		=> 'Finland',
					'flag_image'	=> 'FI.png',
				),
				array(
					'flag_name'		=> 'France',
					'flag_image'	=> 'FR.png',
				),
				array(
					'flag_name'		=> 'Gabon',
					'flag_image'	=> 'GA.png',
				),
				array(
					'flag_name'		=> 'Gambia',
					'flag_image'	=> 'GM.png',
				),
				array(
					'flag_name'		=> 'Georgia',
					'flag_image'	=> 'GE.png',
				),
				array(
					'flag_name'		=> 'Germany',
					'flag_image'	=> 'DE.png',
				),
				array(
					'flag_name'		=> 'Ghana',
					'flag_image'	=> 'GH.png',
				),
				array(
					'flag_name'		=> 'Great Britain',
					'flag_image'	=> 'GB.png',
				),
				array(
					'flag_name'		=> 'Greece',
					'flag_image'	=> 'GR.png',
				),
				array(
					'flag_name'		=> 'Greenland',
					'flag_image'	=> 'GL.png',
				),
				array(
					'flag_name'		=> 'Grenada',
					'flag_image'	=> 'GD.png',
				),
				array(
					'flag_name'		=> 'Guam',
					'flag_image'	=> 'GU.png',
				),
				array(
					'flag_name'		=> 'Guatemala',
					'flag_image'	=> 'GT.png',
				),
				array(
					'flag_name'		=> 'Guinea',
					'flag_image'	=> 'GN.png',
				),
				array(
					'flag_name'		=> 'Guinea Bissau',
					'flag_image'	=> 'GW.png',
				),
				array(
					'flag_name'		=> 'Guyana',
					'flag_image'	=> 'GY.png',
				),
				array(
					'flag_name'		=> 'Haiti',
					'flag_image'	=> 'HT.png',
				),
				array(
					'flag_name'		=> 'Honduras',
					'flag_image'	=> 'HN.png',
				),
				array(
					'flag_name'		=> 'Hong Kong',
					'flag_image'	=> 'HK.png',
				),
				array(
					'flag_name'		=> 'Hungary',
					'flag_image'	=> 'HU.png',
				),
				array(
					'flag_name'		=> 'Iceland',
					'flag_image'	=> 'IS.png',
				),
				array(
					'flag_name'		=> 'India',
					'flag_image'	=> 'IN.png',
				),
				array(
					'flag_name'		=> 'Indonesia',
					'flag_image'	=> 'ID.png',
				),
				array(
					'flag_name'		=> 'Iran',
					'flag_image'	=> 'IR.png',
				),
				array(
					'flag_name'		=> 'Iraq',
					'flag_image'	=> 'IQ.png',
				),
				array(
					'flag_name'		=> 'Ireland',
					'flag_image'	=> 'IE.png',
				),
				array(
					'flag_name'		=> 'Isle of Man',
					'flag_image'	=> 'IM.png',
				),
				array(
					'flag_name'		=> 'Israel',
					'flag_image'	=> 'IL.png',
				),
				array(
					'flag_name'		=> 'Italy',
					'flag_image'	=> 'IT.png',
				),
				array(
					'flag_name'		=> 'Jamaica',
					'flag_image'	=> 'JM.png',
				),
				array(
					'flag_name'		=> 'Japan',
					'flag_image'	=> 'JP.png',
				),
				array(
					'flag_name'		=> 'Jordan',
					'flag_image'	=> 'JO.png',
				),
				array(
					'flag_name'		=> 'Kazakhstan',
					'flag_image'	=> 'KZ.png',
				),
				array(
					'flag_name'		=> 'Kenya',
					'flag_image'	=> 'KE.png',
				),
				array(
					'flag_name'		=> 'Kiribati',
					'flag_image'	=> 'KI.png',
				),
				array(
					'flag_name'		=> 'Korea North',
					'flag_image'	=> 'NK.png',
				),
				array(
					'flag_name'		=> 'Korea South',
					'flag_image'	=> 'KS.png',
				),
				array(
					'flag_name'		=> 'Kuwait',
					'flag_image'	=> 'KW.png',
				),
				array(
					'flag_name'		=> 'Kyrgyzstan',
					'flag_image'	=> 'KG.png',
				),
				array(
					'flag_name'		=> 'Laos',
					'flag_image'	=> 'LA.png',
				),
				array(
					'flag_name'		=> 'Latvia',
					'flag_image'	=> 'LV.png',
				),
				array(
					'flag_name'		=> 'Lebanon',
					'flag_image'	=> 'LB.png',
				),
				array(
					'flag_name'		=> 'Lesotho',
					'flag_image'	=> 'LS.png',
				),
				array(
					'flag_name'		=> 'Liberia',
					'flag_image'	=> 'LR.png',
				),
				array(
					'flag_name'		=> 'Libya',
					'flag_image'	=> 'LY.png',
				),
				array(
					'flag_name'		=> 'Liechtenstein',
					'flag_image'	=> 'LI.png',
				),
				array(
					'flag_name'		=> 'Lithuania',
					'flag_image'	=> 'LT.png',
				),
				array(
					'flag_name'		=> 'Luxembourg',
					'flag_image'	=> 'LU.png',
				),
				array(
					'flag_name'		=> 'Macau',
					'flag_image'	=> 'MO.png',
				),
				array(
					'flag_name'		=> 'Macedonia',
					'flag_image'	=> 'MK.png',
				),
				array(
					'flag_name'		=> 'Madagascar',
					'flag_image'	=> 'MG.png',
				),
				array(
					'flag_name'		=> 'Malawi',
					'flag_image'	=> 'MW.png',
				),
				array(
					'flag_name'		=> 'Malaysia',
					'flag_image'	=> 'MY.png',
				),
				array(
					'flag_name'		=> 'Maldives',
					'flag_image'	=> 'MV.png',
				),
				array(
					'flag_name'		=> 'Mali',
					'flag_image'	=> 'ML.png',
				),
				array(
					'flag_name'		=> 'Malta',
					'flag_image'	=> 'MT.png',
				),
				array(
					'flag_name'		=> 'Marshall Islands',
					'flag_image'	=> 'MH.png',
				),
				array(
					'flag_name'		=> 'Mauritania',
					'flag_image'	=> 'MR.png',
				),
				array(
					'flag_name'		=> 'Mauritius',
					'flag_image'	=> 'MU.png',
				),
				array(
					'flag_name'		=> 'Mexico',
					'flag_image'	=> 'MX.png',
				),
				array(
					'flag_name'		=> 'Micronesia',
					'flag_image'	=> 'FM.png',
				),
				array(
					'flag_name'		=> 'Moldova',
					'flag_image'	=> 'MD.png',
				),
				array(
					'flag_name'		=> 'Monaco',
					'flag_image'	=> 'MC.png',
				),
				array(
					'flag_name'		=> 'Mongolia',
					'flag_image'	=> 'MN.png',
				),
				array(
					'flag_name'		=> 'Montserrat',
					'flag_image'	=> 'MS.png',
				),
				array(
					'flag_name'		=> 'Morocco',
					'flag_image'	=> 'MA.png',
				),
				array(
					'flag_name'		=> 'Mozambique',
					'flag_image'	=> 'MZ.png',
				),
				array(
					'flag_name'		=> 'Myanmar',
					'flag_image'	=> 'MM.png',
				),
				array(
					'flag_name'		=> 'Nambia',
					'flag_image'	=> 'NA.png',
				),
				array(
					'flag_name'		=> 'Nauru',
					'flag_image'	=> 'NR.png',
				),
				array(
					'flag_name'		=> 'Nepal',
					'flag_image'	=> 'NP.png',
				),
				array(
					'flag_name'		=> 'Netherland Antilles',
					'flag_image'	=> 'AN.png',
				),
				array(
					'flag_name'		=> 'Netherlands',
					'flag_image'	=> 'NL.png',
				),
				array(
					'flag_name'		=> 'New Zealand',
					'flag_image'	=> 'NZ.png',
				),
				array(
					'flag_name'		=> 'Nicaragua',
					'flag_image'	=> 'NI.png',
				),
				array(
					'flag_name'		=> 'Niger',
					'flag_image'	=> 'NE.png',
				),
				array(
					'flag_name'		=> 'Nigeria',
					'flag_image'	=> 'NG.png',
				),
				array(
					'flag_name'		=> 'Norfolk Island',
					'flag_image'	=> 'NF.png',
				),
				array(
					'flag_name'		=> 'Norway',
					'flag_image'	=> 'NO.png',
				),
				array(
					'flag_name'		=> 'Oman',
					'flag_image'	=> 'OM.png',
				),
				array(
					'flag_name'		=> 'Pakistan',
					'flag_image'	=> 'PK.png',
				),
				array(
					'flag_name'		=> 'Palau Island',
					'flag_image'	=> 'PW.png',
				),
				array(
					'flag_name'		=> 'Palestine',
					'flag_image'	=> 'PS.png',
				),
				array(
					'flag_name'		=> 'Panama',
					'flag_image'	=> 'PA.png',
				),
				array(
					'flag_name'		=> 'Papua New Guinea',
					'flag_image'	=> 'PG.png',
				),
				array(
					'flag_name'		=> 'Paraguay',
					'flag_image'	=> 'PY.png',
				),
				array(
					'flag_name'		=> 'Peru',
					'flag_image'	=> 'PE.png',
				),
				array(
					'flag_name'		=> 'Philippines',
					'flag_image'	=> 'PH.png',
				),
				array(
					'flag_name'		=> 'Pitcairn Island',
					'flag_image'	=> 'PN.png',
				),
				array(
					'flag_name'		=> 'Poland',
					'flag_image'	=> 'PL.png',
				),
				array(
					'flag_name'		=> 'Portugal',
					'flag_image'	=> 'PT.png',
				),
				array(
					'flag_name'		=> 'Puerto Rico',
					'flag_image'	=> 'PR.png',
				),
				array(
					'flag_name'		=> 'Qatar',
					'flag_image'	=> 'QA.png',
				),
				array(
					'flag_name'		=> 'Romania',
					'flag_image'	=> 'RO.png',
				),
				array(
					'flag_name'		=> 'Russia',
					'flag_image'	=> 'RU.png',
				),
				array(
					'flag_name'		=> 'Rwanda',
					'flag_image'	=> 'RW.png',
				),
				array(
					'flag_name'		=> 'Samoa',
					'flag_image'	=> 'WS.png',
				),
				array(
					'flag_name'		=> 'San Marino',
					'flag_image'	=> 'SM.png',
				),
				array(
					'flag_name'		=> 'Sao Tome &amp; Principe',
					'flag_image'	=> 'ST.png',
				),
				array(
					'flag_name'		=> 'Saudi Arabia',
					'flag_image'	=> 'SA.png',
				),
				array(
					'flag_name'		=> 'Senegal',
					'flag_image'	=> 'SN.png',
				),
				array(
					'flag_name'		=> 'Seychelles',
					'flag_image'	=> 'SC.png',
				),
				array(
					'flag_name'		=> 'Sierra Leone',
					'flag_image'	=> 'SL.png',
				),
				array(
					'flag_name'		=> 'Singapore',
					'flag_image'	=> 'SG.png',
				),
				array(
					'flag_name'		=> 'Slovakia',
					'flag_image'	=> 'SK.png',
				),
				array(
					'flag_name'		=> 'Slovenia',
					'flag_image'	=> 'SI.png',
				),
				array(
					'flag_name'		=> 'Solomon Islands',
					'flag_image'	=> 'SB.png',
				),
				array(
					'flag_name'		=> 'Somalia',
					'flag_image'	=> 'SO.png',
				),
				array(
					'flag_name'		=> 'South Africa',
					'flag_image'	=> 'ZA.png',
				),
				array(
					'flag_name'		=> 'Spain',
					'flag_image'	=> 'ES.png',
				),
				array(
					'flag_name'		=> 'Sri Lanka',
					'flag_image'	=> 'LK.png',
				),
				array(
					'flag_name'		=> 'St Helena',
					'flag_image'	=> 'SH.png',
				),
				array(
					'flag_name'		=> 'St Kitts-Nevis',
					'flag_image'	=> 'KN.png',
				),
				array(
					'flag_name'		=> 'St Lucia',
					'flag_image'	=> 'LC.png',
				),
				array(
					'flag_name'		=> 'St Vincent &amp; Grenadines',
					'flag_image'	=> 'VC.png',
				),
				array(
					'flag_name'		=> 'Sudan',
					'flag_image'	=> 'SD.png',
				),
				array(
					'flag_name'		=> 'Suriname',
					'flag_image'	=> 'SR.png',
				),
				array(
					'flag_name'		=> 'Swaziland',
					'flag_image'	=> 'SZ.png',
				),
				array(
					'flag_name'		=> 'Sweden',
					'flag_image'	=> 'SE.png',
				),
					array(
					'flag_name'		=> 'Switzerland',
					'flag_image'	=> 'CH.png',
				),
				array(
					'flag_name'		=> 'Syria',
					'flag_image'	=> 'SY.png',
				),
				array(
					'flag_name'		=> 'Taiwan',
					'flag_image'	=> 'TW.png',
				),
				array(
					'flag_name'		=> 'Tajikistan',
					'flag_image'	=> 'TJ.png',
				),
				array(
					'flag_name'		=> 'Tanzania',
					'flag_image'	=> 'TZ.png',
				),
				array(
					'flag_name'		=> 'Thailand',
					'flag_image'	=> 'TH.png',
				),
				array(
					'flag_name'		=> 'Togo',
					'flag_image'	=> 'TG.png',
				),
				array(
					'flag_name'		=> 'Tonga',
					'flag_image'	=> 'TO.png',
				),
				array(
					'flag_name'		=> 'Trinidad &amp; Tobago',
					'flag_image'	=> 'TT.png',
				),
				array(
					'flag_name'		=> 'Tunisia',
					'flag_image'	=> 'TN.png',
				),
				array(
					'flag_name'		=> 'Turkey',
					'flag_image'	=> 'TR.png',
				),
				array(
					'flag_name'		=> 'Turkmenistan',
					'flag_image'	=> 'TM.png',
				),
				array(
					'flag_name'		=> 'Turks &amp; Caicos Is',
					'flag_image'	=> 'TC.png',
				),
				array(
					'flag_name'		=> 'Tuvalu',
					'flag_image'	=> 'TV.png',
				),
				array(
					'flag_name'		=> 'Uganda',
					'flag_image'	=> 'UG.png',
				),
				array(
					'flag_name'		=> 'Ukraine',
					'flag_image'	=> 'UA.png',
				),
				array(
					'flag_name'		=> 'United Arab Emirates',
					'flag_image'	=> 'AE.png',
				),
				array(
					'flag_name'		=> 'United States of America',
					'flag_image'	=> 'US.png',
				),
				array(
					'flag_name'		=> 'Uruguay',
					'flag_image'	=> 'UY.png',
				),
				array(
					'flag_name'		=> 'Uzbekistan',
					'flag_image'	=> 'UZ.png',
				),
				array(
					'flag_name'		=> 'Vanuatu',
					'flag_image'	=> 'VU.png',
				),
				array(
					'flag_name'		=> 'Venezuela',
					'flag_image'	=> 'VE.png',
				),
				array(
					'flag_name'		=> 'Vietnam',
					'flag_image'	=> 'VN.png',
				),
				array(
					'flag_name'		=> 'Virgin Islands (Brit)',
					'flag_image'	=> 'VG.png',
				),
				array(
					'flag_name'		=> 'Virgin Islands (USA)',
					'flag_image'	=> 'VI.png',
				),
				array(
					'flag_name'		=> 'Wales',
					'flag_image'	=> 'WLS.png',
				),
				array(
					'flag_name'		=> 'Western Sahara',
					'flag_image'	=> 'EH.png',
				),
				array(
					'flag_name'		=> 'Yemen',
					'flag_image'	=> 'YE.png',
				),
				array(
					'flag_name'		=> 'Zambia',
					'flag_image'	=> 'ZM.png',
				),
				array(
					'flag_name'		=> 'Zimbabwe',
					'flag_image'	=> 'ZW.png',
				),
			); 
			/**/			
			foreach ($sql_ary as $ary)
			{
				if (!is_array($ary))
				{
					message_die(CRITICAL_ERROR, "First array of multidimensional insert is a strring.", '', __LINE__, __FILE__, $ary);
				}

				$result = $db->sql_query('INSERT INTO ' . FLAG_TABLE . ' ' . $db->sql_build_array('INSERT', $ary));
			}			
			if (!($result))
			{		
				message_die(CRITICAL_ERROR, "Could not add flags table to the DB", '', __LINE__, __FILE__, print_r($sql_ary, true));
			}			
			
			$message = $lang['Virtual_Go'] . "<br /><br />" . sprintf($lang['Click_return_flagadmin'], "<a href=\"" . append_sid("admin_flags.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid($phpbb_root_path . "admin/index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);			
		}
	}	
}
		
$template->pparse("body");

include('./page_footer_admin.'.$phpEx);

?>