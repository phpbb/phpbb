<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_includejs_test extends phpbb_template_template_test_case
{
	public function test_includejs_compilation()
	{
		// Reset the engine state
		$this->setup_engine();

		// Prepare correct result
		$dir = dirname(__FILE__);
		$scripts = array(
			'<script src="' . $dir . '/templates/parent_and_child.html"></script>',
			'<script src="' . $dir . '/parent_templates/parent_only.html"></script>',
			'<script src="' . $dir . '/templates/child_only.html"></script>'
		);

		// Run test
		$cache_file = $this->template->cachepath . 'includejs.html.php';
		$this->run_template('includejs.html', array('PARENT' => 'parent_only.html'), array(), array(), implode('', $scripts), $cache_file);
	}

	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = $this->config_defaults();
		$config = new phpbb_config(array_merge($defaults, $new_config));

		$this->template_path = dirname(__FILE__) . '/templates';
		$this->parent_template_path = dirname(__FILE__) . '/parent_templates';
		$this->style_resource_locator = new phpbb_style_resource_locator();
		$this->style_provider = new phpbb_style_path_provider();
		$this->template = new phpbb_style_template($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->style_provider);
		$this->style = new phpbb_style($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->style_provider, $this->template);
		$this->style->set_custom_style('tests', array($this->template_path, $this->parent_template_path), '');
		$this->template->set_filenames(array('body' => 'includejs.html'));
	}
}
