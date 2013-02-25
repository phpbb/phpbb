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
		$config = new phpbb_config(array_merge($defaults, $new_config));

		$this->template_path = $this->test_path . '/templates';
		$this->parent_template_path = $this->test_path . '/parent_templates';
		$this->style_resource_locator = new phpbb_style_resource_locator();
		$this->style_provider = new phpbb_style_path_provider();
		$this->template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, new phpbb_template_context());
		$this->style = new phpbb_style($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->style_provider, $this->template);
		$this->style->set_custom_style('tests', array($this->template_path, $this->parent_template_path), array(), '');
	}
}
