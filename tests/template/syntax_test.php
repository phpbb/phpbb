<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';
require_once dirname(__FILE__) . '/../../phpBB/develop/template_validator.php';

class phpbb_template_syntax_test extends phpbb_template_template_test_case
{
	public function template_data()
	{
		global $phpbb_root_path;
		return array(
			array(
				$phpbb_root_path . 'adm/style',
			),
			array(
				$phpbb_root_path . 'styles/prosilver/template',
			),
			array(
				$phpbb_root_path . 'styles/subsilver2/template',
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_templates($path)
	{
		$this->check_directory($path);
	}

	protected function check_directory($dir)
	{
		foreach (new DirectoryIterator($dir) as $file)
		{
			$filename = $file->getFilename();
			if ($file->isDot())
			{
				continue;
			}
			elseif ($file->isDir())
			{
				$this->check_directory($file->getPathname());
			}
			elseif ($file->isFile() && preg_match('/\.html$/', $file->getFilename()))
			{
				$this->validate_template($file->getPathname());
			}
		}
	}

	protected function validate_template($filename)
	{
		$validator = new template_validator($filename);
		$this->assertFalse($validator->validate(), "Validating template $filename");
	}
}
