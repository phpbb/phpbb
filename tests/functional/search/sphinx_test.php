<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/base.php';

/**
* @group functional
*/
class phpbb_functional_search_sphinx_test extends phpbb_functional_search_base
{
	protected $search_backend = '\phpbb\search\fulltext_sphinx';

	public function test_search_backend()
	{
		$this->markTestIncomplete('Sphinx Tests are not supported');
	}
}
