<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
require_once dirname(__FILE__) . '/../style_path_provider_test.php';

class phpbb_extension_subdir_style_path_provider_test extends phpbb_extension_style_path_provider_test
{
	public function setUp()
	{
		$this->relative_root_path = '../';
		$this->root_path = dirname(__FILE__) . '/../';
	}
}
