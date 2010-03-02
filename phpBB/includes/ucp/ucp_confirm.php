<?php
/** 
*
* @package VC
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* ucp_confirm
* Visual confirmation
*
* Note to potential users of this code ...
*
* Remember this is released under the _GPL_ and is subject
* to that licence. Do not incorporate this within software 
* released or distributed in any way under a licence other
* than the GPL. We will be watching ... ;)
*
* @package VC
*/
class ucp_confirm
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $phpbb_root_path, $config, $phpEx;

		// Do we have an id? No, then just exit
		$confirm_id = request_var('id', '');
		$type = request_var('type', 0);

		if (!$confirm_id || !$type)
		{
			exit;
		}

		// Try and grab code for this id and session
		$sql = 'SELECT code  
			FROM ' . CONFIRM_TABLE . " 
			WHERE session_id = '" . $db->sql_escape($user->session_id) . "' 
				AND confirm_id = '" . $db->sql_escape($confirm_id) . "'
				AND confirm_type = $type";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// If we have a row then grab data else create a new id
		if (!$row)
		{
			exit;
		}

		// Some people might want the olde style CAPTCHA even if they have GD enabled, this also saves us from people who have GD but no TTF
		$policy_modules = array('policy_entropy', 'policy_3dbitmap');

		if (function_exists('imagettfbbox') && function_exists('imagettftext'))
		{
			$policy_modules = array_merge($policy_modules, array('policy_overlap', 'policy_shape', 'policy_cells', 'policy_stencil', 'policy_composite'));
		}

		foreach ($policy_modules as $key => $name)
		{
			if ($config[$name] === '0')
			{
				unset($policy_modules[$key]);
			}
		}

		$policy = '';
		if (@extension_loaded('gd') && sizeof($policy_modules))
		{
			$change_lang	= request_var('change_lang', '');

			if ($change_lang)
			{
				$lang = $change_lang;
				$user->lang_name = $lang = $change_lang;
				$user->lang_path = $phpbb_root_path . 'language/' . $lang . '/';
				$user->lang = array();
				$user->add_lang(array('common', 'ucp'));
			}
			include($phpbb_root_path . 'includes/captcha/captcha_gd.' . $phpEx);
			$policy = $policy_modules[array_rand($policy_modules)];
		}
		else
		{
			include($phpbb_root_path . 'includes/captcha/captcha_non_gd.' . $phpEx);
		}

		$captcha = new captcha();
		$captcha->execute($row['code'], $policy);
		exit;
	}
}

?>