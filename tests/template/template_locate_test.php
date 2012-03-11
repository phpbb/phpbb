<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_locate_test extends phpbb_template_template_test_case
{
	/**
	 * @todo put test data into templates/xyz.test
	 */
	public static function template_data()
	{
		return array(
			// First element of the array is test name - keep them distinct
			array(
				'find only parent template',
				'parent_only.html',
				true,
				'parent_only.html',
				dirname(__FILE__) . '/parent_templates/parent_only.html',
			),
			array(
				'find child template',
				'child_only.html',
				true,
				'child_only.html',
				dirname(__FILE__) . '/templates/child_only.html',
			),
			array(
				'both parent and child templates exist',
				'parent_and_child.html',
				true,
				'parent_and_child.html',
				dirname(__FILE__) . '/templates/parent_and_child.html',
			),
			array(
				'array of templates - first template exists in parent style, second in child',
				array('parent_only.html', 'child_only.html'),
				true,
				'child_only.html',
				dirname(__FILE__) . '/parent_templates/parent_only.html',
			),
			array(
				'array of templates - first template exists in child style, second in both',
				array('child_only.html', 'parent_and_child.html'),
				true,
				'child_only.html',
				dirname(__FILE__) . '/templates/child_only.html',
			),
			array(
				'template does not exist',
				'non_existing_file.html',
				true,
				false,
				dirname(__FILE__) . '/templates/non_existing_file.html',
			),
			array(
				'js files do not exist',
				array('non_existing_file.js', 'non_existing_file2.js'),
				false,
				false,
				dirname(__FILE__) . '/templates/non_existing_file.js',
			),
			array(
				'js file does not exist',
				'non_existing_file.js',
				false,
				false,
				dirname(__FILE__) . '/templates/non_existing_file.js',
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_locator($name, $file, $is_template, $expected1, $expected2)
	{
		$result1 = $this->template->template_exists($file, true);
		$this->assertEquals($expected1, $result1);

		if (is_array($file))
		{
			// Array is intended for first test, use first array item for second test
			$file = $file[0];
		}
		$result2 = ($is_template) ? $this->template->locate_template($file) : $this->template->locate_resource($file);
		$this->assertEquals($expected2, $result2);
	}

	protected function setup_engine()
	{
		global $phpbb_root_path, $phpEx, $config, $user;

		$this->template_path = dirname(__FILE__) . '/templates';
		$this->parent_template_path = dirname(__FILE__) . '/parent_templates';
		$this->template_locator = new phpbb_template_locator();
		$this->template_provider = new phpbb_template_path_provider();
		$this->template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user, $this->template_locator, $this->template_provider);
		$this->template->set_custom_style('tests', array($this->template_path, $this->parent_template_path), '');
	}
}
