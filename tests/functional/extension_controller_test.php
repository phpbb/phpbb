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
	protected $phpbb_extension_manager;

	static protected $fixtures = array(
		'foo/bar/config/routing.yml',
		'foo/bar/config/services.yml',
		'foo/bar/controller/controller.php',
		'foo/bar/ext.php',
	);

	/**
	* This should only be called once before the tests are run.
	* This is used to copy the fixtures to the phpBB install
	*/
	static public function setUpBeforeClass()
	{
		global $phpbb_root_path;
		parent::setUpBeforeClass();

		$directories = array(
			$phpbb_root_path . 'ext/foo/bar/',
			$phpbb_root_path . 'ext/foo/bar/config/',
			$phpbb_root_path . 'ext/foo/bar/controller/',
		);

		foreach ($directories as $dir)
		{
			if (!is_dir($dir))
			{
				mkdir($dir, 0777, true);
			}
		}
	
		foreach (self::$fixtures as $fixture)
		{
			if (!copy("tests/functional/fixtures/ext/$fixture", "{$phpbb_root_path}ext/$fixture"))
			{
				echo 'Could not copy file ' . $fixture;
			}
		}
	}

	/**
	* This should only be called once after the tests are run.
	* This is used to remove the fixtures from the phpBB install
	*/
	static public function tearDownAfterClass()
	{
		global $phpbb_root_path;
		foreach (self::$fixtures as $fixture)
		{
			if (!unlink("{$phpbb_root_path}ext/$fixture"))
			{
				echo 'Could not delete file ' . $fixture;
			}
		}

		rmdir("{$phpbb_root_path}ext/foo/bar/config");
		rmdir("{$phpbb_root_path}ext/foo/bar/controller");
		rmdir("{$phpbb_root_path}ext/foo/bar");
		rmdir("{$phpbb_root_path}ext/foo");
	}

	public function setUp()
	{
		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();
	}

	/**
	* Check a controller for extension foo/bar
	*/
	public function test_foo_bar()
	{
		$this->phpbb_extension_manager->enable('foo/bar');
		$crawler = $this->request('GET', 'app.php/foo/bar');
		$this->assertContains("foo/bar controller handle() method", $crawler->filter('body')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
	}

	public function test_missing_argument()
	{
		$this->phpbb_extension_manager->enable('foo/bar');
		$crawler = $this->request('GET', 'app.php/foo/baz');
		$this->assertContains('Missing value for argument #1: test in class phpbb_ext_foo_bar_controller:baz', $crawler->filter('body')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
	}

	/**
	* Check the error produced by extension at ./ext/does/not/exist
	*
	* If an extension is disabled, its routes are not loaded. Because we
	* are not looking for a controller based on a specified extension,
	* we don't know the difference between a route in a disabled
	* extension and a route that is not defined anyway; it is the same
	* error message.
	*/
	public function test_error_ext_disabled_or_404()
	{
		$crawler = $this->request('GET', 'app.php/does/not/exist');
		$this->assertContains('No route found for "GET /does/not/exist"', $crawler->filter('body')->text());
	}
}
