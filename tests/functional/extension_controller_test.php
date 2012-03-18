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
	/**
	* This should only be called once before the tests are run.
	* This is used to copy the fixtures to the phpBB install
	*/
	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$f_path = self::$config['phpbb_functional_path'];

		// these directories need to be created before the files can be copied
		$directories = array(
			$f_path . 'ext/error/class/',
			$f_path . 'ext/error/classtype/',
			$f_path . 'ext/error/disabled/',
			$f_path . 'ext/foo/bar/',
			$f_path . 'ext/foo/bar/styles/prosilver/template/',
			$f_path . 'ext/foobar/',
			$f_path . 'ext/foobar/styles/prosilver/template/',
		);
		// When you add new tests that require new fixtures, add them to the array.
		foreach ($directories as $dir)
		{
			if (!is_dir($dir))
			{
				mkdir($dir, 0777, true);
			}
		}

		$fixtures = array(
			'error/class/controller.php',
			'error/class/ext.php',
			'error/classtype/controller.php',
			'error/classtype/ext.php',
			'error/disabled/controller.php',
			'error/disabled/ext.php',
			'foo/bar/controller.php',
			'foo/bar/ext.php',
			'foo/bar/styles/prosilver/template/foobar_body.html',
			'foobar/controller.php',
			'foobar/ext.php',
			'foobar/styles/prosilver/template/foobar_body.html',
		);

		foreach ($fixtures as $fixture)
		{
			// we have to use self::$config['phpbb_functional_url'] because $this->root_url is not available in static classes
			if(!copy("tests/functional/fixtures/ext/$fixture", "{$f_path}ext/$fixture"))
			{
				echo 'Could not copy file ' . $fixture;
			}
		}
	}

	public static function tearDownAfterClass()
	{
		$f_path = self::$config['phpbb_functional_path'];

		// @todo delete the fixtures from the $f_path board
		// Note that it might be best to find a public domain function
		// and port it into here instead of writing it from scratch
	}

	public function setUp()
	{
		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();
	}

	/**
	* Check an extension at ./ext/foobar/ which should have the class
	* phpbb_ext_foobar_controller
	*/
	public function test_foobar()
	{
		$this->phpbb_extension_manager->enable('foobar');
		$crawler = $this->request('GET', 'index.php?ext=foobar');
		$this->assertContains("This is for testing purposes.", $crawler->filter('#page-body')->text());
		$this->phpbb_extension_manager->purge('foobar');
	}

	/**
	* Check an extension at ./ext/foo/bar/ which should have the class
	* phpbb_ext_foo_bar_controller
	*/
	public function test_foo_bar()
	{
		$this->phpbb_extension_manager->enable('foo/bar');
		$crawler = $this->request('GET', 'index.php?ext=foo/bar');
		$this->assertContains("This is for testing purposes.", $crawler->filter('#page-body')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
	}

	/**
	* Check the error produced by extension at ./ext/error/class which has class
	* phpbb_ext_foobar_controller
	*/
	public function test_error_class_name()
	{
		$this->phpbb_extension_manager->enable('error/class');
		$crawler = $this->request('GET', 'index.php?ext=error/class');
		$this->assertContains("The extension error/class is missing a controller class and cannot be accessed through the front-end.", $crawler->filter('#message')->text());
		$this->phpbb_extension_manager->purge('error/class');
	}

	/**
	* Check the error produced by extension at ./ext/error/classtype which has class
	* phpbb_ext_error_classtype_controller but does not implement phpbb_extension_controller_interface
	*/
	public function test_error_class_type()
	{
		$this->phpbb_extension_manager->enable('error/classtype');
		$crawler = $this->request('GET', 'index.php?ext=error/classtype');
		$this->assertContains("The extension controller class phpbb_ext_error_classtype_controller is not an instance of the phpbb_extension_controller_interface.", $crawler->filter('#message')->text());
		$this->phpbb_extension_manager->purge('error/classtype');
	}

	/**
	* Check the error produced by extension at ./ext/error/disabled that is (obviously)
	* a disabled extension
	*/
	public function test_error_ext_disabled()
	{
		$crawler = $this->request('GET', 'index.php?ext=error/disabled');
		$this->assertContains("The extension error/disabled is not enabled", $crawler->filter('#message')->text());
	}

	/**
	* Check the error produced by extension at ./ext/error/404 that is (obviously)
	* not existant
	*/
	public function test_error_ext_missing()
	{
		$crawler = $this->request('GET', 'index.php?ext=error/404');
		$this->assertContains("The extension error/404 does not exist.", $crawler->filter('#message')->text());
	}
}
