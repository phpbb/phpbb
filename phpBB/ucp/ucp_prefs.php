<?php
/***************************************************************************
 *                               ucp_prefs.php
 *                            -------------------
 *   begin                : Saturday, Feb 21, 2003
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

class ucp_prefs extends ucp
{
	function main($id)
	{
		global $config, $db, $user, $SID, $template, $phpEx;

		$submode = ($_REQUEST['mode']) ? htmlspecialchars($_REQUEST['mode']) : 'personal';

		// Setup internal subsection display
		$submodules['PERSONAL']	= "i=$id&amp;mode=personal";
		$submodules['VIEW']		= "i=$id&amp;mode=view";
		$submodules['POST']		= "i=$id&amp;mode=post";

		$this->subsection($submodules, $submode);
		unset($submodules);

		switch($submode)
		{
			case 'personal':
				$template->assign_vars(array( 
					'VIEW_EMAIL_YES'	=> ($user->data['user_viewemail ']) ? ' checked="checked"' : '', 
					'VIEW_EMAIL_NO'		=> (!$user->data['user_viewemail ']) ? ' checked="checked"' : '', 
					'DATE_FORMAT'	=> $user->data['user_dateformat'], 

					'S_LANG_OPTIONS'	=> language_select($user->data['user_lang']), 
					'S_STYLE_OPTIONS'	=> style_select($user->data['user_style']),
					'S_TZ_OPTIONS'		=> tz_select($user->data['user_timezone']),)
				);
				break;

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
			'S_UCP_ACTION'						=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode")
		);

		$this->output($user->lang['UCP_PROFILE'], 'ucp_prefs.html');
	}
}

?>