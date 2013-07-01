<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_php_test extends phpbb_template_template_test_case
{
	public function test_php()
	{
		$this->setup_engine(array('tpl_allow_php' => true));

		$this->run_template('php.html', array(), array(), array(), 'test');
	}
}