<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/base_test.php';

/**
* @group functional
*/
class phpbb_functional_search_native_test extends phpbb_functional_search_base_test
{
	protected $search_backend;

	public function setUp()
	{
		parent::setUp();
		$this->search_backend = 'phpbb_search_fulltext_native';
	}
}
