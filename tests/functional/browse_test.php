<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_browse_test extends phpbb_functional_test_case
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$f_path = self::$config['phpbb_functional_path'];
		// we cannot run these tests correctly if the install directory is present
		if (is_dir($f_path . 'install/'))
		{
			rename($f_path . 'install/', $f_path . 'install_/');
		}
		// NOTE: this will need to be renamed back again later if you wish to test again
	}
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
