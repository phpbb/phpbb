<?php
/***************************************************************************
 *                            usercp_modules.php
 *                            -------------------
 *   begin                : Tuesday, Jan 14, 2003
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
 
//
// This file sets up basic module information for the user control panel
// any files added to the UCP must be added to this file inorder for them to
// show up in the UCP menu. 
//

$ucp_modules['UCP_Main']['UCP_Main'] = '';
$ucp_modules['UCP_Main']['Default'] = '';

$ucp_modules['UCP_Profile']['Default'] = 'inc=ucp/usercp_profile.' . $phpEx . "&amp;mode=editprofile&amp;u=" . $user->data['user_id'];
$ucp_modules['UCP_Profile']['Registration_information'] = 'inc=ucp/usercp_profile.' . $phpEx . "&amp;mode=editprofile&amp;u=" . $user->data['user_id'];
$ucp_modules['UCP_Profile']['Preferances'] = 'inc=ucp/usercp_profile.' . $phpEx . "&amp;mode=preferancese&amp;u=" . $user->data['user_id'];
$ucp_modules['UCP_Profile']['Avatar_settings'] = 'inc=ucp/usercp_avatar.' . $phpEx . "&amp;u=" . $user->data['user_id'];
$ucp_modules['UCP_Profile']['Signature_settings'] = 'inc=ucp/usercp_profile.' . $phpEx . "&amp;mode=signaturee&amp;u=" . $user->data['user_id'];

$ucp_modules['UCP_Lists']['Default'] = 'inc=ucp/usercp_lists.' . $phpEx . "&amp;mode=settings";
$ucp_modules['UCP_Lists']['Lists_settings'] = 'inc=ucp/usercp_lists.' . $phpEx . "&amp;mode=settings";
$ucp_modules['UCP_Lists']['While_list'] = 'inc=ucp/usercp_lists.' . $phpEx . "&amp;mode=white";
$ucp_modules['UCP_Lists']['Black_list'] = 'inc=ucp/usercp_lists.' . $phpEx . "amp;mode=black";

$ucp_modules['UCP_Priv_messages']['Default'] = '';
$ucp_modules['UCP_Priv_messages']['Private_messages'] = '';


//
// This bit of code prints out the module tabs on the UCP pages, each UCP page
// must call this file to print out the tabs.
//
// Also note that each user cp file handles it own sub-section displays.
//

foreach($ucp_modules as $section_title => $sections)
{
	$template->assign_block_vars('ucp_sections', array('U_SECTION' => "ucp.$phpEx$SID&amp;" . $sections['Default'] ,
		'SECTION' => $user->lang[$section_title]));
}
