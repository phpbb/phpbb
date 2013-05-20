<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_update
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('install');

		$this->tpl_name = 'acp_update';
		$this->page_title = 'ACP_VERSION_CHECK';

		// Get current and latest version
		$errstr = '';
		$errno = 0;

		$info = obtain_latest_version_info(request_var('versioncheck_force', false));

		if (empty($info))
		{
			trigger_error('VERSIONCHECK_FAIL', E_USER_WARNING);
		}

		$info = explode("\n", $info);
		$latest_version = trim($info[0]);

		$announcement_url = trim($info[1]);
		$announcement_url = (strpos($announcement_url, '&amp;') === false) ? str_replace('&', '&amp;', $announcement_url) : $announcement_url;
		$update_link = append_sid($phpbb_root_path . 'install/index.' . $phpEx, 'mode=update');

		// next feature release
		$next_feature_version = $next_feature_announcement_url = false;
		if (isset($info[2]) && trim($info[2]) !== '')
		{
			$next_feature_version = trim($info[2]);
			$next_feature_announcement_url = trim($info[3]);
		}

		// Determine automatic update...
		$sql = 'SELECT config_value
			FROM ' . CONFIG_TABLE . "
			WHERE config_name = 'version_update_from'";
		$result = $db->sql_query($sql);
		$version_update_from = (string) $db->sql_fetchfield('config_value');
		$db->sql_freeresult($result);

		$current_version = (!empty($version_update_from)) ? $version_update_from : $config['version'];

		$template->assign_vars(array(
			'S_UP_TO_DATE'		=> phpbb_version_compare($latest_version, $config['version'], '<='),
			'S_UP_TO_DATE_AUTO'	=> phpbb_version_compare($latest_version, $current_version, '<='),
			'S_VERSION_CHECK'	=> true,
			'U_ACTION'			=> $this->u_action,
			'U_VERSIONCHECK_FORCE' => append_sid($this->u_action . '&amp;versioncheck_force=1'),

			'LATEST_VERSION'	=> $latest_version,
			'CURRENT_VERSION'	=> $config['version'],
			'AUTO_VERSION'		=> $version_update_from,
			'NEXT_FEATURE_VERSION'	=> $next_feature_version,

			'UPDATE_INSTRUCTIONS'	=> sprintf($user->lang['UPDATE_INSTRUCTIONS'], $announcement_url, $update_link),
			'UPGRADE_INSTRUCTIONS'	=> $next_feature_version ? $user->lang('UPGRADE_INSTRUCTIONS', $next_feature_version, $next_feature_announcement_url) : false,
		));
	}
}

?>