<?php
/***************************************************************************
 *                            usercp_profile.php
 *                            -------------------
 *   begin                : Saturday, Feb 21, 2003
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

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
	exit;
}

//
// Setup internal subsection display
//
$modules['Registration_information'] = "module_id=$selected_module&amp;mode=editprofile&amp;u=" . $user->data['user_id'];
$modules['Preferances'] = "module_id=$selected_module&amp;mode=preferancese&amp;u=" . $user->data['user_id'];            
$modules['Avatar_settings'] = "module_id=$selected_module&amp;u=" . $user->data['user_id'];                               
$modules['Signature_settings'] = "module_id=$selected_module&amp;mode=signaturee&amp;u=" . $user->data['user_id'];       

foreach($modules as $section_title => $module_link)
{
	$template->assign_block_vars('ucp_subsections', array('U_SUBSECTION' => "ucp.$phpEx$SID&amp;" . $module_link ,
		'SUBSECTION' => $user->lang[$section_title],
		'IS_MULTI_SECTIONS' => (count($modules) > 1) ? TRUE : FALSE));
}

$mode = ($_GET['mode']) ? $_GET['mode'] : $_POST['mode'];
$submit = ($_GET['submit']) ? $_GET['submit'] : $_POST['submit'];

switch($mode)
{
	case 'editprofile':
	default: 
		
	if($submit)
	{
		//
		// Save basic profile information
		//
		
		
	}
	else
	{
		//
		// Get userdata
		//
		$username = $user->data['username'];
		$email = $user->data['user_email'];
		
		$s_hidden_fields = '<input type="hidden" name="inc" value="' . $include . '"><input type="hidden" name="mode" value="editprofile">'; 
		//
		// Show edit profile info form
		//
		$template->assign_vars(array(
			'IS_PROFILE' => TRUE,
			'USERNAME' => $username,
			'EMAIL' => $email,
			
			'L_CURRENT_PASSWORD' => $user->lang['Current_password'],
			'L_NEW_PASSWORD' => ($mode == 'register') ? $user->lang['Password'] : $user->lang['New_password'],
			'L_CONFIRM_PASSWORD' => $user->lang['Confirm_password'],
			'L_CONFIRM_PASSWORD_EXPLAIN' => ($mode == 'editprofile') ? $user->lang['Confirm_password_explain'] : '',
			'L_PASSWORD_IF_CHANGED' => ($mode == 'editprofile') ? $user->lang['password_if_changed'] : '',
			'L_PASSWORD_CONFIRM_IF_CHANGED' => ($mode == 'editprofile') ? $user->lang['password_confirm_if_changed'] : '',
			'L_SUBMIT' => $user->lang['Submit'],
			'L_RESET' => $user->lang['Reset'],
			'L_ITEMS_REQUIRED' => $user->lang['Items_required'],
	
			'S_CONFIRM_CODE' => ($config['enable_confirm']) ? 1 : 0,
			'S_HIDDEN_FIELDS' => $s_hidden_fields,
			'S_PROFILE_ACTION' => "ucp.$phpEx$SID")
		);
		
		
	}
	
	
	break;
	case 'preferancese':
	

	break;
	case 'signaturee':


	break;
}


include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'usercp_profile.html')
);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);


?>
