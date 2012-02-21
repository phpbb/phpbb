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
class phpbb_functional_extension_controller_test extends phpbb_functional_test_case
{
	public function setUp()
	{
		global $phpbb_extension_manager;

		$phpbb_extension_manager->enable('foobar');
		$phpbb_extension_manager->enable('foo_bar');
		$phpbb_extension_manager->enable('error_class');
		$phpbb_extension_manager->enable('error_classtype');
	}

	public function tearDown()
	{
		global $phpbb_extension_manager;

		$phpbb_extension_manager->purge('foobar');
		$phpbb_extension_manager->purge('foo_bar');
		$phpbb_extension_manager->purge('error_class');
		$phpbb_extension_manager->purge('error_classtype');
	}

	/**
	* Check an extension at ./ext/foobar/ which should have the class
	* phpbb_ext_foobar_controller
	*/
	public function test_foobar()
	{
		$crawler = $this->request('GET', 'index.php?ext=foobar');
		$this->assertGreaterThan(0, $crawler->filter('#welcome')->count());
	}

	/**
	* Check an extension at ./ext/foo/bar/ which should have the class
	* phpbb_ext_foo_bar_controller
	*/
	public function test_foo_bar()
	{
		$crawler = $this->request('GET', 'index.php?ext=foo/bar');
		$this->assertGreaterThan(0, $crawler->filter('#welcome')->count());
	}

	/**
	* Check the error produced by extension at ./ext/error/class which has class
	* phpbb_ext_foobar_controller
	*/
	public function test_error_class_name()
	{
		$crawler = $this->request('GET', 'index.php?ext=error/class');
		$this->assertGreaterThan(0, $crawler->filter('html:contains("The extension <strong>error_class</strong> is missing a controller class and cannot be accessed through the front-end.")')->count());
	}

	/**
	* Check the error produced by extension at ./ext/error/classtype which has class
	* phpbb_ext_error_classtype_controller but does not implement phpbb_extension_controller_interface
	*/
	public function test_error_class_type()
	{
		$crawler = $this->request('GET', 'index.php?ext=error/classtype');
		$this->assertGreaterThan(0, $crawler->filter('html:contains("The extension controller class <strong>phpbb_ext_error_classtype_controller</strong> is not an instance of the phpbb_extension_controller_interface.")')->count());
	}

	/**
	* Check the error produced by extension at ./ext/error/disabled that is (obviously)
	* a disabled extension
	*/
	public function test_error_ext_disabled()
	{
		$crawler = $this->request('GET', 'index.php?ext=error/disabled');
		$this->assertGreaterThan(0, $crawler->filter('html:contains("The extension <strong>error_classtype</strong> is not enabled.")')->count());
	}

	/**
	* Check the error produced by extension at ./ext/error/404 that is (obviously)
	* not existant
	*/
	public function test_error_ext_missing()
	{
		$crawler = $this->request('GET', 'index.php?ext=error/404');
		$this->assertGreaterThan(0, $crawler->filter('html:contains("The extension <strong>error_404</strong> does not exist.")')->count());
	}
}
