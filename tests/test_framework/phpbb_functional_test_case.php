<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
use Symfony\Component\BrowserKit\CookieJar;

require_once __DIR__ . '/../../phpBB/includes/functions_install.php';
require_once __DIR__ . '/../../phpBB/includes/acm/acm_file.php';
require_once __DIR__ . '/../../phpBB/includes/cache.php';

class phpbb_functional_test_case extends phpbb_test_case
{
	static protected $client;
	static protected $cookieJar;
	static protected $root_url;

	protected $cache = null;
	protected $db = null;

	/**
	* Session ID for current test's session (each test makes its own)
	* @var string
	*/
	protected $sid;

	/**
	* Language array used by phpBB
	* @var array
	*/
	protected $lang = array();

	static protected $config = array();
	static protected $already_installed = false;

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$config = phpbb_test_case_helpers::get_test_config();
		self::$root_url = self::$config['phpbb_functional_url'];

		if (!isset(self::$config['phpbb_functional_url']))
		{
			self::markTestSkipped('phpbb_functional_url was not set in test_config and wasn\'t set as PHPBB_FUNCTIONAL_URL environment variable either.');
		}

		if (!self::$already_installed)
		{
			self::install_board();
			self::$already_installed = true;
		}
	}

	public function setUp()
	{
		parent::setUp();

		$this->bootstrap();

		self::$cookieJar = new CookieJar;
		self::$client = new Goutte\Client(array(), null, self::$cookieJar);
		// Reset the curl handle because it is 0 at this point and not a valid
		// resource
		self::$client->getClient()->getCurlMulti()->reset(true);

		// Clear the language array so that things
		// that were added in other tests are gone
		$this->lang = array();
		$this->add_lang('common');
		$this->purge_cache();
	}

	/**
	* Perform a request to page
	*
	* @param string	$method		HTTP Method
	* @param string	$path		Page path, relative from phpBB root path
	* @param array $form_data	An array of form field values
	* @param bool	$assert_response_html	Should we perform standard assertions for a normal html page
	* @return Symfony\Component\DomCrawler\Crawler
	*/
	static public function request($method, $path, $form_data = array(), $assert_response_html = true)
	{
		$crawler = self::$client->request($method, self::$root_url . $path, $form_data);

		if ($assert_response_html)
		{
			self::assert_response_html();
		}

		return $crawler;
	}

	/**
	* Submits a form
	*
	* @param Symfony\Component\DomCrawler\Form $form A Form instance
	* @param array $values An array of form field values
	* @param bool	$assert_response_html	Should we perform standard assertions for a normal html page
	* @return Symfony\Component\DomCrawler\Crawler
	*/
	static public function submit(Symfony\Component\DomCrawler\Form $form, array $values = array(), $assert_response_html = true)
	{
		$crawler = self::$client->submit($form, $values);

		if ($assert_response_html)
		{
			self::assert_response_html();
		}

		return $crawler;
	}

	/**
	* Get Client Content
	*
	* @return string HTML page
	*/
	static public function get_content()
	{
		return self::$client->getResponse()->getContent();
	}

	// bootstrap, called after board is set up
	// once per test case class
	// test cases can override this
	protected function bootstrap()
	{
	}

	protected function get_db()
	{
		global $phpbb_root_path, $phpEx;
		// so we don't reopen an open connection
		if (!($this->db instanceof dbal))
		{
			if (!class_exists('dbal_' . self::$config['dbms']))
			{
				include($phpbb_root_path . 'includes/db/' . self::$config['dbms'] . ".$phpEx");
			}
			$sql_db = 'dbal_' . self::$config['dbms'];
			$this->db = new $sql_db();
			$this->db->sql_connect(self::$config['dbhost'], self::$config['dbuser'], self::$config['dbpasswd'], self::$config['dbname'], self::$config['dbport']);
		}
		return $this->db;
	}

	protected function get_cache_driver()
	{
		if (!$this->cache)
		{
			$this->cache = new cache();
		}

		return $this->cache;
	}

	protected function purge_cache()
	{
		$cache = $this->get_cache_driver();

		$cache->purge();
		$cache->unload();
		$cache->load();
	}

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->backupStaticAttributesBlacklist += array(
			'phpbb_functional_test_case' => array('config', 'already_installed'),
		);
	}

	static protected function install_board()
	{
		global $phpbb_root_path, $phpEx;

		self::$config['table_prefix'] = 'phpbb_';
		self::recreate_database(self::$config);

		$config_file = $phpbb_root_path . "config.$phpEx";
		$config_file_dev = $phpbb_root_path . "config_dev.$phpEx";
		$config_file_test = $phpbb_root_path . "config_test.$phpEx";

		if (file_exists($config_file))
		{
			if (!file_exists($config_file_dev))
			{
				rename($config_file, $config_file_dev);
			}
			else
			{
				unlink($config_file);
			}
		}

		self::$cookieJar = new CookieJar;
		self::$client = new Goutte\Client(array(), null, self::$cookieJar);
		// Set client manually so we can increase the cURL timeout
		self::$client->setClient(new Guzzle\Http\Client('', array(
			Guzzle\Http\Client::DISABLE_REDIRECTS	=> true,
			'curl.options'	=> array(
				CURLOPT_TIMEOUT	=> 120,
			),
		)));

		// Reset the curl handle because it is 0 at this point and not a valid
		// resource
		self::$client->getClient()->getCurlMulti()->reset(true);

		$parseURL = parse_url(self::$config['phpbb_functional_url']);

		$crawler = self::request('GET', 'install/index.php?mode=install');
		self::assertContains('Welcome to Installation', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// install/index.php?mode=install&sub=requirements
		$crawler = self::submit($form);
		self::assertContains('Installation compatibility', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// install/index.php?mode=install&sub=database
		$crawler = self::submit($form);
		self::assertContains('Database configuration', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form(array(
			// Installer uses 3.0-style dbms name
			'dbms'			=> str_replace('phpbb_db_driver_', '',  self::$config['dbms']),
			'dbhost'		=> self::$config['dbhost'],
			'dbport'		=> self::$config['dbport'],
			'dbname'		=> self::$config['dbname'],
			'dbuser'		=> self::$config['dbuser'],
			'dbpasswd'		=> self::$config['dbpasswd'],
			'table_prefix'	=> self::$config['table_prefix'],
		));

		// install/index.php?mode=install&sub=database
		$crawler = self::submit($form);
		self::assertContains('Successful connection', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// install/index.php?mode=install&sub=administrator
		$crawler = self::submit($form);
		self::assertContains('Administrator configuration', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form(array(
			'default_lang'	=> 'en',
			'admin_name'	=> 'admin',
			'admin_pass1'	=> 'adminadmin',
			'admin_pass2'	=> 'adminadmin',
			'board_email1'	=> 'nobody@example.com',
			'board_email2'	=> 'nobody@example.com',
		));

		// install/index.php?mode=install&sub=administrator
		$crawler = self::submit($form);
		self::assertContains('Tests passed', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// We have to skip install/index.php?mode=install&sub=config_file
		// because that step will create a config.php file if phpBB has the
		// permission to do so. We have to create the config file on our own
		// in order to get the DEBUG constants defined.
		$config_php_data = phpbb_create_config_file_data(self::$config, self::$config['dbms'], array(), true, true);
		$config_created = file_put_contents($config_file, $config_php_data) !== false;
		if (!$config_created)
		{
			self::markTestSkipped("Could not write $config_file file.");
		}

		// We also have to create a install lock that is normally created by
		// the installer. The file will be removed by the final step of the
		// installer.
		$install_lock_file = $phpbb_root_path . 'cache/install_lock';
		$lock_created = file_put_contents($install_lock_file, '') !== false;
		if (!$lock_created)
		{
			self::markTestSkipped("Could not create $lock_created file.");
		}
		@chmod($install_lock_file, 0666);

		// install/index.php?mode=install&sub=advanced
		$form_data = $form->getValues();
		unset($form_data['submit']);

		$crawler = self::request('POST', 'install/index.php?mode=install&sub=advanced', $form_data);
		self::assertContains('The settings on this page are only necessary to set if you know that you require something different from the default.', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form(array(
			'email_enable'		=> true,
			'smtp_delivery'		=> true,
			'smtp_host'			=> 'nxdomain.phpbb.com',
			'smtp_auth'			=> 'PLAIN',
			'smtp_user'			=> 'nxuser',
			'smtp_pass'			=> 'nxpass',
			'cookie_secure'		=> false,
			'force_server_vars'	=> false,
			'server_protocol'	=> $parseURL['scheme'] . '://',
			'server_name'		=> 'localhost',
			'server_port'		=> isset($parseURL['port']) ? (int) $parseURL['port'] : 80,
			'script_path'		=> $parseURL['path'],
		));

		// install/index.php?mode=install&sub=create_table
		$crawler = self::submit($form);
		self::assertContains('The database tables used by phpBB', $crawler->filter('#main')->text());
		self::assertContains('have been created and populated with some initial data.', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// install/index.php?mode=install&sub=final
		$crawler = self::submit($form);
		self::assertContains('You have successfully installed', $crawler->text());

		copy($config_file, $config_file_test);
	}

	static private function recreate_database($config)
	{
		$db_conn_mgr = new phpbb_database_test_connection_manager($config);
		$db_conn_mgr->recreate_db();
	}

	/**
	* Creates a new style
	*
	* @param string $style_id Style ID
	* @param string $style_path Style directory
	* @param string $parent_style_id Parent style id. Default = 1
	* @param string $parent_style_path Parent style directory. Default = 'prosilver'
	*/
	protected function add_style($style_id, $style_path, $parent_style_id = 1, $parent_style_path = 'prosilver')
	{
		global $phpbb_root_path;

		$db = $this->get_db();
		$sql = 'INSERT INTO ' . STYLES_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'style_id' => $style_id,
			'style_name' => $style_path,
			'style_copyright' => '',
			'style_active' => 1,
			'template_id' => $style_id,
			'theme_id' => $style_id,
			'imageset_id' => $style_id,
		));
		$db->sql_query($sql);

		$sql = 'INSERT INTO ' . STYLES_IMAGESET_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'imageset_id' => $style_id,
			'imageset_name' => $style_path,
			'imageset_copyright' => '',
			'imageset_path' => $style_path,
		));
		$db->sql_query($sql);

		$sql = 'INSERT INTO ' . STYLES_TEMPLATE_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'template_id' => $style_id,
			'template_name' => $style_path,
			'template_copyright' => '',
			'template_path' => $style_path,
			'bbcode_bitfield' => 'kNg=',
			'template_inherits_id' => $parent_style_id,
			'template_inherit_path' => $parent_style_path,
		));
		$db->sql_query($sql);

		$sql = 'INSERT INTO ' . STYLES_THEME_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'theme_id' => $style_id,
			'theme_name' => $style_path,
			'theme_copyright' => '',
			'theme_path' => $style_path,
			'theme_storedb' => 0,
			'theme_mtime' => 0,
			'theme_data' => '',
		));
		$db->sql_query($sql);

		if ($style_path != 'prosilver' && $style_path != 'subsilver2')
		{
			@mkdir($phpbb_root_path . 'styles/' . $style_path, 0777);
			@mkdir($phpbb_root_path . 'styles/' . $style_path . '/template', 0777);
		}
	}

	/**
	* Remove temporary style created by add_style()
	*
	* @param string $style_id Style ID
	* @param string $style_path Style directory
	*/
	protected function delete_style($style_id, $style_path)
	{
		global $phpbb_root_path;

		$db = $this->get_db();
		$db->sql_query('DELETE FROM ' . STYLES_TABLE . ' WHERE style_id = ' . $style_id);
		$db->sql_query('DELETE FROM ' . STYLES_IMAGESET_TABLE . ' WHERE imageset_id = ' . $style_id);
		$db->sql_query('DELETE FROM ' . STYLES_TEMPLATE_TABLE . ' WHERE template_id = ' . $style_id);
		$db->sql_query('DELETE FROM ' . STYLES_THEME_TABLE . ' WHERE theme_id = ' . $style_id);

		if ($style_path != 'prosilver' && $style_path != 'subsilver2')
		{
			@rmdir($phpbb_root_path . 'styles/' . $style_path . '/template');
			@rmdir($phpbb_root_path . 'styles/' . $style_path);
		}
	}

	/**
	* Creates a new user with limited permissions
	*
	* @param string $username Also doubles up as the user's password
	* @return int ID of created user
	*/
	protected function create_user($username)
	{
		// Required by unique_id
		global $config;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;

		// Required by user_add
		global $db, $cache;
		$db = $this->get_db();
		if (!function_exists('phpbb_mock_null_cache'))
		{
			require_once(__DIR__ . '/../mock/null_cache.php');
		}
		$cache = new phpbb_mock_null_cache;

		if (!function_exists('utf_clean_string'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');
		}
		if (!function_exists('user_add'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/functions_user.php');
		}

		$user_row = array(
			'username' => $username,
			'group_id' => 2,
			'user_email' => 'nobody@example.com',
			'user_type' => 0,
			'user_lang' => 'en',
			'user_timezone' => 0,
			'user_dateformat' => '',
			'user_password' => phpbb_hash($username . $username),
		);
		return user_add($user_row);
	}

	protected function login($username = 'admin')
	{
		$this->add_lang('ucp');

		$crawler = self::request('GET', 'ucp.php');
		$this->assertContains($this->lang('LOGIN_EXPLAIN_UCP'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();
		$crawler = self::submit($form, array('username' => $username, 'password' => $username . $username));
		$this->assertContains($this->lang('LOGIN_REDIRECT'), $crawler->filter('html')->text());

		$cookies = self::$cookieJar->all();

		// The session id is stored in a cookie that ends with _sid - we assume there is only one such cookie
		foreach ($cookies as $cookie);
		{
			if (substr($cookie->getName(), -4) == '_sid')
			{
				$this->sid = $cookie->getValue();
			}
		}
	}

	/**
	* Login to the ACP
	* You must run login() before calling this.
	*/
	protected function admin_login($username = 'admin')
	{
		$this->add_lang('acp/common');

		// Requires login first!
		if (empty($this->sid))
		{
			$this->fail('$this->sid is empty. Make sure you call login() before admin_login()');
			return;
		}

		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid);
		$this->assertContains($this->lang('LOGIN_ADMIN_CONFIRM'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();

		foreach ($form->getValues() as $field => $value)
		{
			if (strpos($field, 'password_') === 0)
			{
				$crawler = self::submit($form, array('username' => $username, $field => $username . $username));
				$this->assertContains($this->lang('LOGIN_ADMIN_SUCCESS'), $crawler->filter('html')->text());

				$cookies = self::$cookieJar->all();

				// The session id is stored in a cookie that ends with _sid - we assume there is only one such cookie
				foreach ($cookies as $cookie);
				{
					if (substr($cookie->getName(), -4) == '_sid')
					{
						$this->sid = $cookie->getValue();
					}
				}

				break;
			}
		}
	}

	protected function add_lang($lang_file)
	{
		if (is_array($lang_file))
		{
			foreach ($lang_file as $file)
			{
				$this->add_lang($file);
			}
		}

		$lang_path = __DIR__ . "/../../phpBB/language/en/$lang_file.php";

		$lang = array();

		if (file_exists($lang_path))
		{
			include($lang_path);
		}

		$this->lang = array_merge($this->lang, $lang);
	}

	protected function lang()
	{
		$args = func_get_args();
		$key = $args[0];

		if (empty($this->lang[$key]))
		{
			throw new RuntimeException('Language key "' . $key . '" could not be found.');
		}

		$args[0] = $this->lang[$key];

		return call_user_func_array('sprintf', $args);
	}

	/**
	* Perform some basic assertions for the page
	*
	* Checks for debug/error output before the actual page content and the status code
	*
	* @param mixed $status_code		Expected status code, false to disable check
	* @return null
	*/
	static public function assert_response_html($status_code = 200)
	{
		if ($status_code !== false)
		{
			self::assert_response_status_code($status_code);
		}

		// Any output before the doc type means there was an error
		$content = self::$client->getResponse()->getContent();
		self::assertStringStartsWith('<!DOCTYPE', trim($content), 'Output found before DOCTYPE specification.');
	}

	/**
	* Heuristic function to check that the response is success.
	*
	* When php decides to die with a fatal error, it still sends 200 OK
	* status code. This assertion tries to catch that.
	*
	* @param int $status_code	Expected status code
	* @return null
	*/
	static public function assert_response_status_code($status_code = 200)
	{
		self::assertEquals($status_code, self::$client->getResponse()->getStatus());
	}

	/**
	* Creates a topic
	*
	* Be sure to login before creating
	*
	* @param int $forum_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @return array post_id, topic_id
	*/
	public function create_topic($forum_id, $subject, $message, $additional_form_data = array())
	{
		$posting_url = "posting.php?mode=post&f={$forum_id}&sid={$this->sid}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return self::submit_post($posting_url, 'POST_TOPIC', $form_data);
	}

	/**
	* Creates a post
	*
	* Be sure to login before creating
	*
	* @param int $forum_id
	* @param int $topic_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @return array post_id, topic_id
	*/
	public function create_post($forum_id, $topic_id, $subject, $message, $additional_form_data = array())
	{
		$posting_url = "posting.php?mode=reply&f={$forum_id}&t={$topic_id}&sid={$this->sid}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return self::submit_post($posting_url, 'POST_REPLY', $form_data);
	}

	/**
	* Helper for submitting posts
	*
	* @param string $posting_url
	* @param string $posting_contains
	* @param array $form_data
	* @return array post_id, topic_id
	*/
	protected function submit_post($posting_url, $posting_contains, $form_data)
	{
		$this->add_lang('posting');

		$crawler = self::request('GET', $posting_url);
		$this->assertContains($this->lang($posting_contains), $crawler->filter('html')->text());

		$hidden_fields = array(
			$crawler->filter('[type="hidden"]')->each(function ($node, $i) {
				return array('name' => $node->getAttribute('name'), 'value' => $node->getAttribute('value'));
			}),
		);

		foreach ($hidden_fields as $fields)
		{
			foreach($fields as $field)
			{
				$form_data[$field['name']] = $field['value'];
			}
		}

		// Bypass time restriction that said that if the lastclick time (i.e. time when the form was opened)
		// is not at least 2 seconds before submission, cancel the form
		$form_data['lastclick'] = 0;

		// I use a request because the form submission method does not allow you to send data that is not
		// contained in one of the actual form fields that the browser sees (i.e. it ignores "hidden" inputs)
		// Instead, I send it as a request with the submit button "post" set to true.
		$crawler = self::request('POST', $posting_url, $form_data);
		$this->assertContains($this->lang('POST_STORED'), $crawler->filter('html')->text());
		$url = $crawler->selectLink($this->lang('VIEW_MESSAGE', '', ''))->link()->getUri();

		return array(
			'topic_id'	=> $this->get_parameter_from_link($url, 't'),
			'post_id'	=> $this->get_parameter_from_link($url, 'p'),
		);
	}

	/**
	* Returns the requested parameter from a URL
	*
	* @param	string	$url
	* @param	string	$parameter
	* @return		string	Value of the parameter in the URL, null if not set
	*/
	public function get_parameter_from_link($url, $parameter)
	{
		if (strpos($url, '?') === false)
		{
			return null;
		}

		$url_parts = explode('?', $url);
		if (isset($url_parts[1]))
		{
			$url_parameters = $url_parts[1];
			if (strpos($url_parameters, '#') !== false)
			{
				$url_parameters = explode('#', $url_parameters);
				$url_parameters = $url_parameters[0];
			}

			foreach (explode('&', $url_parameters) as $url_param)
			{
				list($param, $value) = explode('=', $url_param);
				if ($param == $parameter)
				{
					return $value;
				}
			}
		}
		return null;
	}
}
