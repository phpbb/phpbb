<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp_pm.php
// STARTED   : Sat Mar 27, 2004
// COPYRIGHT : © 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// TODO for 2.2:
//
// * Utilise more code from posting, modularise as appropriate
// * Give option of recieving a receipt upon reading (sender)
// * Give option of not sending a receipt upon reading (recipient)
// * Archive inbox to text file? to email?
// * Review of post when replying/quoting
// * Introduce post/post thread forwarding
// * Introduce (option of) emailing entire PM when notifying user of new message

class ucp_pm extends module
{
	function ucp_pm($id, $mode)
	{
		global $user, $template, $phpbb_root_path, $auth, $phpEx, $db, $SID, $config;
		
		if ($user->data['user_id'] == ANONYMOUS)
		{
			trigger_error('NO_PM');
		}

		// Is PM disabled?
		if (!$config['allow_privmsg'])
		{
			trigger_error('PM_DISABLED');
		}

		$user->add_lang('posting');
		$template->assign_var('S_PRIVMSGS', true);

		trigger_error('No, not yet. :P');
		
		$template->assign_vars(array( 
			'L_TITLE'			=> $user->lang['UCP_PM_' . strtoupper($mode)],
			'S_UCP_ACTION'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;action=$action")
		);

		$this->display($user->lang['UCP_PM'], $tpl_file);
	}
}

?>