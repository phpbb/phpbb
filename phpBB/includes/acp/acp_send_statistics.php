<?php
/**
*
* @package acp
* @version $Id: acp_ranks.php 8479 2008-03-29 00:22:48Z naderman $
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
include($phpbb_root_path . 'includes/questionnaire/questionnaire_phpbb.' . $phpEx);


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

		$c = new Questionnaire_Data_Collector();
		$c->addDataProvider(new Questionnaire_PHP_Data_Provider());
		$c->addDataProvider(new Questionnaire_System_Data_Provider());
		$c->addDataProvider(new questionnaire_phpbb_data_provider($config));

		$template->assign_vars(array(
			'U_COLLECT_STATS'	=> $collect_url,
			'RAW_DATA' => $c->getDataForForm(),
		));

		$raw = $c->getDataRaw();

		foreach ($raw as $provider => $data)
		{
			$template->assign_block_vars('providers', array(
				'NAME'	=> htmlentities($provider),
			));

			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					$value = utf8_wordwrap(serialize($value), 75, "\n", true);
				}

				$template->assign_block_vars('providers.values', array(
					'KEY'	=> htmlentities($key),
					'VALUE'	=> htmlentities($value),
				));
			}
		}
	}

	/**
	* Output the data as an HTML Definition List.
	*
	* @param   mixed
	* @param   string
	* @param   string
	* @return  void
	*/
	function data_printer($value, $key)
	{
		echo '<dt>', htmlentities($key), '</dt>', $ident, "\t", '<dd>';
		if (is_array($value))
		{
			$value = htmlentities(serialize($value));
			echo '<dl>';
			echo '</dl>';
		} else {
			echo htmlentities($value);
		}
		echo '</dd>';
	}
}

?>