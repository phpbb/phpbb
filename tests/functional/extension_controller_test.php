<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @group functional
*/
class phpbb_functional_extension_controller_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	private static $helper;

	protected static $fixtures = array(
		'./',
	);

	static public function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(__CLASS__);
		self::$helper->copy_ext_fixtures(__DIR__ . '/fixtures/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	protected static function setup_extensions()
	{
		return ['foo/bar', 'foo/foo'];
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->purge_cache();
	}

	protected function tearDown(): void
	{
		$this->uninstall_ext('foo/bar');
		$this->uninstall_ext('foo/foo');

		parent::tearDown();
	}

	/**
	* Check a controller for extension foo/bar.
	*/
	public function test_foo_bar()
	{
		$crawler = self::request('GET', 'app.php/foo/bar', array(), false);
		self::assert_response_status_code();
		$this->assertStringContainsString("foo/bar controller handle() method", $crawler->filter('body')->text());
	}

	/**
	* Check a controller for extension foo/bar.
	*/
	public function test_routing_resources()
	{
		$crawler = self::request('GET', 'app.php/foo/foo', array(), false);
		self::assert_response_status_code();
		$this->assertStringContainsString("foo/foo controller handle() method", $crawler->filter('body')->text());
	}

	/**
	* Check the output of a controller using the template system
	*/
	public function test_controller_with_template()
	{
		$crawler = self::request('GET', 'app.php/foo/template');
		$this->assertStringContainsString("I am a variable", $crawler->filter('#content')->text());
	}

	/**
	* Check includejs/includecss when the request_uri is a subdirectory
	*/
	public function test_controller_template_include_js_css()
	{
		$crawler = self::request('GET', 'app.php/help/faq');
		$this->assertStringContainsString("./../../assets/javascript/core.js", $crawler->filter('body')->html());
	}

	/**
	* Check the error produced by calling a controller without a required
	* argument.
	*/
	public function test_missing_argument()
	{
		$crawler = self::request('GET', 'app.php/foo/baz', array(), false);
		$this->assert_response_html(500);
		$this->assertStringContainsString('Controller "foo\bar\controller\controller::baz" requires that you provide a value for the "$test" argument', $crawler->filter('body')->text());
	}

	/**
	* Check the status code resulting from an exception thrown by a controller
	*/
	public function test_exception_should_result_in_500_status_code()
	{
		$crawler = self::request('GET', 'app.php/foo/exception', array(), false);
		$this->assert_response_html(500);
		$this->assertStringContainsString('Exception thrown from foo/exception route', $crawler->filter('body')->text());
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
		$crawler = self::request('GET', 'app.php/does/not/exist', array(), false);
		$this->assert_response_html(404);

		// Since version 5.3.0-BETA1, Symfony shows full URI when route not found. See https://github.com/symfony/symfony/pull/39893
		$full_uri = self::$client->getRequest()->getUri();
		$this->assertStringContainsString('No route found for "GET ' . $full_uri . '"', $crawler->filter('body')->text());
	}

	/**
	 * Check the redirect after using the login_box() form
	 */
	public function test_login_redirect()
	{
		$this->markTestIncomplete('Session table contains incorrect data for controllers on CI,'
			. 'therefore the redirect fails.');

		$crawler = self::request('GET', 'app.php/foo/login_redirect');
		$this->assertContainsLang('LOGIN', $crawler->filter('h2')->text());
		$form = $crawler->selectButton('login')->form(array(
			'username'	=> 'admin',
			'password'	=> 'adminadmin',
		));
		$this->assertStringStartsWith('./app.php/foo/login_redirect', $form->get('redirect')->getValue());

		$crawler = self::submit($form);
		$this->assertStringContainsString("I am a variable", $crawler->filter('#content')->text(), 'Unsuccessful redirect after using login_box()');
	}

	/**
	* Check the output of a controller using the template system
	*/
	public function test_redirect()
	{
		$crawler = self::request('GET', 'app.php/foo/redirect');

		$nodes = $crawler->filter('div')->extract(array('id'));

		foreach ($nodes as $redirect)
		{
			if (strpos($redirect, 'redirect_expected') !== 0)
			{
				continue;
			}

			$row_num = str_replace('redirect_expected_', '', $redirect);

			$redirect = $crawler->filter('#redirect_' . $row_num)->text();
			$this->assertEquals($crawler->filter('#redirect_expected_' .  $row_num)->text(), $redirect);
		}
	}
}
