<?php
/**
*
* @package acp
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
class acp_revisions
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache, $request;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix, $file_uploads;

		$user->add_lang('acp/revisions');

		$form_key = 'acp_revisions';
		add_form_key($form_key);

		$action = $request->variable('action', '');

		switch ($mode)
		{
			default:
			case 'settings':
				$this->tpl_name = 'acp_revisions_settings';
				$this->page_title = 'ACP_REVISION_SETTINGS';
				if ($request->is_set_post('submit'))
				{
					$settings = array(
						'track_post_revisions',
						'max_revisions_age',
						'max_revisions_per_post',
						'max_revisions_per_wiki_post',
						'revisions_allow_wiki',
						'revision_cron_age_frequency',
						'revision_cron_excess_frequency',
					);

					foreach ($settings as $setting)
					{
						$new_value = $request->variable($setting, '');

						if (isset($config[$setting]) && $request->is_set_post($setting) && $new_value != $config[$setting])
						{
							$config->set($setting, $new_value);
						}
					}

					trigger_error($user->lang('REVISION_SETTINGS_UPDATED') . adm_back_link($u->action));
				}

				// Settings form options
				$template->assign_vars(array(
					'S_REVISION_HISTORY_YES_SELECTED'	=> $config['track_post_revisions'] ? ' selected="selected"' : '',
					'S_REVISION_HISTORY_NO_SELECTED'	=> !$config['track_post_revisions'] ? ' selected="selected"' : '',
					'S_REVISION_WIKI_YES_SELECTED'		=> $config['revisions_allow_wiki'] ? ' selected="selected"' : '',
					'S_REVISION_WIKI_NO_SELECTED'		=> !$config['revisions_allow_wiki'] ? ' selected="selected"' : '',

					'REVISIONS_EXCESS_PRUNE_FREQUENCY'	=> $config['revision_cron_age_frequency'],
					'REVISIONS_OLD_PRUNE_FREQUENCY'		=> $config['revision_cron_excess_frequency'],
					'REVISIONS_MAX_AGE'					=> $config['post_revisions_max_age'],
					'REVISIONS_PER_POST'				=> $config['revisions_per_post_max'],
					'REVISIONS_PER_WIKI_POST'			=> $config['revisions_per_wiki_post_max'],
				));
			break;

			case 'purge':
				$this->tpl_name = 'acp_revisions_purge';
				$this->page_title = 'ACP_REVISIONS_PURGE';

				if ($request->is_set_post('confirm'))
				{
					if ($request->variable('purge_confirm') == $user->lang('PURGE_REVISIONS_CONFIRM_WORD'))
					{
						if (!function_exists('phpbb_purge_post_revisions'))
						{
							include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
						}

						phpbb_purge_post_revisions($db);

						add_log('admin', 'LOG_PURGE_REVISIONS');

						trigger_error($user->lang('REVISIONS_PURGED_SUCCESS') . adm_back_link($this->u_action));
					}

					$template->assign_vars(array(
						'L_PURGE_REVISIONS_CONFIRM'	=> $user->lang('PURGE_REVISIONS_CONFIRM', $user->lang('PURGE_REVISIONS_CONFIRM_WORD')),
					));
				}
			break;
		}
	}
}
