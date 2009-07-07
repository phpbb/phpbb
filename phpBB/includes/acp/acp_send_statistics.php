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

include($phpbb_root_path . 'includes/questionnaire/questionnaire.' . $phpEx);

/**
* @package acp
*/
class acp_send_statistics
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $template;

		$collect_url = "http://www.phpbb.com/stats/getstatdata.php";

		$this->tpl_name = 'acp_send_statistics';
		$this->page_title = 'ACP_SEND_STATISTICS';

		$collector = new phpbb_questionnaire_data_collector();

		// Add data provider
		$collector->add_data_provider(new phpbb_questionnaire_php_data_provider());
		$collector->add_data_provider(new phpbb_questionnaire_system_data_provider());
		$collector->add_data_provider(new phpbb_questionnaire_phpbb_data_provider($config));

		$template->assign_vars(array(
			'U_COLLECT_STATS'	=> $collect_url,
			'RAW_DATA'			=> $collector->get_data_for_form(),
		));

		$raw = $collector->get_data_raw();

		foreach ($raw as $provider => $data)
		{
			$template->assign_block_vars('providers', array(
				'NAME'	=> htmlspecialchars($provider),
			));

			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					$value = utf8_wordwrap(serialize($value), 75, "\n", true);
				}

				$template->assign_block_vars('providers.values', array(
					'KEY'	=> utf8_htmlspecialchars($key),
					'VALUE'	=> utf8_htmlspecialchars($value),
				));
			}
		}
	}
}

?>