<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_test_case_with_tree extends phpbb_template_template_test_case
{
	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = $this->config_defaults();
		$config = new \phpbb\config\config(array_merge($defaults, $new_config));

		$this->phpbb_filesystem = new \phpbb\filesystem(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$phpbb_root_path,
			$phpEx
		);

		$this->template_path = $this->test_path . '/templates';
		$this->parent_template_path = $this->test_path . '/parent_templates';
		$this->template = new phpbb\template\twig\twig($this->phpbb_filesystem, $config, $user, new phpbb\template\context());
		$this->template->set_custom_style('tests', array($this->template_path, $this->parent_template_path));
	}
}
