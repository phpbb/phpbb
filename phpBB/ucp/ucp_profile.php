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


class ucp_profile extends ucp
{
	function main($module_id)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpEx;

		$submode = ($_REQUEST['mode']) ? htmlspecialchars($_REQUEST['mode']) : 'reg_details';

		$submodules['REG_DETAILS']	= "module_id=$module_id&amp;mode=reg_details";
		$submodules['PROFILE']		= "module_id=$module_id&amp;mode=profile";
		$submodules['SIGNATURE']	= "module_id=$module_id&amp;mode=signature";       
		$submodules['AVATAR']		= "module_id=$module_id&amp;mode=avatar";                               

		$user->lang = array_merge($user->lang, array(
			'UCP_REG_DETAILS'	=> 'Registration details', 
			'UCP_PROFILE'		=> 'Your Profile', 
			'UCP_SIGNATURE'		=> 'Your signature', 
			'UCP_AVATAR'		=> 'Your avatar')
		);

		ucp::subsection($submodules, $submode);
		unset($submodules);

		switch ($submode)
		{
			case 'reg_details':
				$template->assign_vars(array(
					'USERNAME'	=> $user->data['username'],
				
					'S_CHANGE_USERNAME' => $auth->acl_get('u_chgname'), )
				);
				break;

			case 'profile':
				break;

			case 'signature':
				$template->assign_vars(array(
					'SIGNATURE'	=> $user->data['signature'])
				);
				break;

			case 'avatar':
				break;

			default: 
				break;
		}

		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['UCP_' . strtoupper($submode)],

			'S_DISPLAY_' . strtoupper($submode)	=> true, 
			'S_HIDDEN_FIELDS'					=> $s_hidden_fields,
			'S_UCP_ACTION'						=> "ucp.$phpEx$SID&amp;module_id=$module_id&amp;mode=$submode")
		);

		ucp::output($user->lang['UCP_PROFILE'], 'ucp_profile.html');
	}
}

?>