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
	protected $phpbb_extension_migrator;

	static protected $fixtures = array(
		'foo/bar/config/routing.yml',
		'foo/bar/config/services.yml',
		'foo/bar/controller/controller.php',
		'foo/bar/styles/prosilver/template/foo_bar_body.html',
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
			$phpbb_root_path . 'ext/foo/bar/styles/prosilver/template',
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
			copy(
				"tests/functional/fixtures/ext/$fixture",
				"{$phpbb_root_path}ext/$fixture");
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
			unlink("{$phpbb_root_path}ext/$fixture");
		}

		rmdir("{$phpbb_root_path}ext/foo/bar/config");
		rmdir("{$phpbb_root_path}ext/foo/bar/controller");
		rmdir("{$phpbb_root_path}ext/foo/bar/styles/prosilver/template");
		rmdir("{$phpbb_root_path}ext/foo/bar/styles/prosilver");
		rmdir("{$phpbb_root_path}ext/foo/bar/styles");
		rmdir("{$phpbb_root_path}ext/foo/bar");
		rmdir("{$phpbb_root_path}ext/foo");
	}

	public function setUp()
	{
		parent::setUp();

		$this->phpbb_extension_migrator = $this->get_extension_migrator();

		$this->purge_cache();
	}

	/**
	* Check a controller for extension foo/bar.
	*/
	public function test_foo_bar()
	{
		$this->phpbb_extension_migrator->enable('foo/bar');
		$crawler = $this->request('GET', 'app.php?controller=foo/bar');
		$this->assert_response_success();
		$this->assertContains("foo/bar controller handle() method", $crawler->filter('body')->text());
		$this->phpbb_extension_migrator->purge('foo/bar');
	}

	/**
	* Check the output of a controller using the template system
	*/
	public function test_controller_with_template()
	{
		$this->phpbb_extension_migrator->enable('foo/bar');
		$crawler = $this->request('GET', 'app.php?controller=foo/template');
		$this->assert_response_success();
		$this->assertContains("I am a variable", $crawler->filter('#content')->text());
		$this->phpbb_extension_migrator->purge('foo/bar');
	}

	/**
	* Check the error produced by calling a controller without a required
	* argument.
	*/
	public function test_missing_argument()
	{
		$this->phpbb_extension_migrator->enable('foo/bar');
		$crawler = $this->request('GET', 'app.php?controller=foo/baz');
		$this->assertEquals(500, $this->client->getResponse()->getStatus());
		$this->assertContains('Missing value for argument #1: test in class phpbb_ext_foo_bar_controller:baz', $crawler->filter('body')->text());
		$this->phpbb_extension_migrator->purge('foo/bar');
	}

	/**
	* Check the status code resulting from an exception thrown by a controller
	*/
	public function test_exception_should_result_in_500_status_code()
	{
		$this->phpbb_extension_migrator->enable('foo/bar');
		$crawler = $this->request('GET', 'app.php?controller=foo/exception');
		$this->assertEquals(500, $this->client->getResponse()->getStatus());
		$this->assertContains('Exception thrown from foo/exception route', $crawler->filter('body')->text());
		$this->phpbb_extension_migrator->purge('foo/bar');
	}

	/**
	* Check the error produced by extension at ./ext/does/not/exist.
	*
	* If an extension is disabled, its routes are not loaded. Because we
	* are not looking for a controller based on a specified extension,
	* we don't know the difference between a route in a disabled
	* extension and a route that is not defined anyway; it is the same
	* error message.
	*/
	public function test_error_ext_disabled_or_404()
	{
		$crawler = $this->request('GET', 'app.php?controller=does/not/exist');
		$this->assertEquals(404, $this->client->getResponse()->getStatus());
		$this->assertContains('No route found for "GET /does/not/exist"', $crawler->filter('body')->text());
	}
}
