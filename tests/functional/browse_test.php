<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @group functional
*/
class phpbb_functional_browse_test extends phpbb_functional_test_case
{
	public function test_index()
	{
		$crawler = $this->request('GET', 'index.php');
		$this->assertGreaterThan(0, $crawler->filter('.topiclist')->count());
	}

	public function test_viewforum()
	{
		$crawler = $this->request('GET', 'viewforum.php?f=2');
		$this->assertGreaterThan(0, $crawler->filter('.topiclist')->count());
	}
}
