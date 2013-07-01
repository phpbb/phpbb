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
		$template_text = '<!-- PHP -->echo "test";<!-- ENDPHP -->';

		$cache_dir = dirname($this->template->cachepath) . '/';
		$fp = fopen($cache_dir . 'php.html', 'w');
		fputs($fp, $template_text);
		fclose($fp);

		$this->setup_engine(array('tpl_allow_php' => true));

		$this->style->set_custom_style('tests', $cache_dir, array(), '');

		$this->run_template('php.html', array(), array(), array(), 'test');
	}
}
