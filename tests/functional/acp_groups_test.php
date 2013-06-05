<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/common_groups_test.php';

/**
* @group functional
*/
class phpbb_functional_acp_groups_test extends phpbb_functional_common_groups_test
{
	protected function get_url()
	{
		return 'adm/index.php?i=groups&mode=manage&action=edit';
	}
}
