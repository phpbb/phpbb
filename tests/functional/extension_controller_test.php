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

	static private $helper;

	static protected $fixtures = array(
		'foo/bar/config/',
		'foo/bar/controller/',
		'foo/bar/styles/prosilver/template/',
	);

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(self);
		self::$helper->copy_ext_fixtures(dirname(__FILE__) . '/fixtures/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass()
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	public function setUp()
	{
		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();
	}

	/**
	* Check a controller for extension foo/bar.
	*/
	public function test_foo_bar()
	{
		$this->phpbb_extension_manager->enable('foo/bar');
		$crawler = self::request('GET', 'app.php?controller=foo/bar', array(), false);
		self::assert_response_status_code();
		$this->assertContains("foo/bar controller handle() method", $crawler->filter('body')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
	}

	/**
	* Check the output of a controller using the template system
	*/
	public function test_controller_with_template()
	{
		$this->phpbb_extension_manager->enable('foo/bar');
		$crawler = self::request('GET', 'app.php?controller=foo/template');
		$this->assertContains("I am a variable", $crawler->filter('#content')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
	}

	/**
	* Check the error produced by calling a controller without a required
	* argument.
	*/
	public function test_missing_argument()
	{
		$this->phpbb_extension_manager->enable('foo/bar');
		$crawler = self::request('GET', 'app.php?controller=foo/baz', array(), false);
		$this->assert_response_html(500);
		$this->assertContains('Missing value for argument #1: test in class phpbb_ext_foo_bar_controller:baz', $crawler->filter('body')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
	}

	/**
	* Check the status code resulting from an exception thrown by a controller
	*/
	public function test_exception_should_result_in_500_status_code()
	{
		$this->phpbb_extension_manager->enable('foo/bar');
		$crawler = self::request('GET', 'app.php?controller=foo/exception', array(), false);
		$this->assert_response_html(500);
		$this->assertContains('Exception thrown from foo/exception route', $crawler->filter('body')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
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
		$crawler = self::request('GET', 'app.php?controller=does/not/exist', array(), false);
		$this->assert_response_html(404);
		$this->assertContains('No route found for "GET /does/not/exist"', $crawler->filter('body')->text());
	}
}
