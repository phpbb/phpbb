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

class ucp_prefs extends ucp
{
	function main($module_id)
	{
		global $config, $db, $user, $SID, $template, $phpEx;

		$submode = ($_REQUEST['mode']) ? htmlspecialchars($_REQUEST['mode']) : 'personal';

		// Setup internal subsection display
		$submodules['PERSONAL']	= "module_id=$module_id&amp;mode=personal";
		$submodules['VIEW']		= "module_id=$module_id&amp;mode=view";
		$submodules['POST']		= "module_id=$module_id&amp;mode=post";

		ucp::subsection($submodules, $submode);
		unset($submodules);

		switch($submode)
		{
			case 'view':
				break;

			case 'post':
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

		ucp::output($user->lang['UCP_PROFILE'], 'ucp_prefs.html');
	}
}

?>