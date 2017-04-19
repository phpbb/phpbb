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

namespace phpbb\install\module\requirements\task;

/**
 * Installer task that checks if the server meats phpBB requirements
 */
class check_server_environment extends \phpbb\install\task_base
{
	/**
	 * @var \phpbb\install\helper\database
	 */
	protected $database_helper;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $response_helper;

	/**
	 * @var bool
	 */
	protected $tests_passed;

	/**
	 * Constructor
	 *
	 * @param	\phpbb\install\helper\database	$database_helper
	 * @param	\phpbb\install\helper\iohandler\iohandler_interface	$response
	 */
	public function __construct(\phpbb\install\helper\database $database_helper,
								\phpbb\install\helper\iohandler\iohandler_interface $response)
	{
		$this->database_helper	= $database_helper;
		$this->response_helper	= $response;
		$this->tests_passed		= true;

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		//
		// Check requirements
		// The error messages should be set in the check_ functions
		//

		// Check PHP version
		$this->check_php_version();

		// Check for getimagesize()
		$this->check_image_size();

		// Check for PCRE support
		$this->check_pcre();

		// Check for JSON support
		$this->check_json();

		// XML extension support check
		$this->check_xml();

		// Check for dbms support
		$this->check_available_dbms();

		return $this->tests_passed;
	}

	/**
	 * Sets $this->tests_passed
	 *
	 * @param	bool	$is_passed
	 */
	protected function set_test_passed($is_passed)
	{
		// If one test failed, tests_passed should be false
		$this->tests_passed = (!$this->tests_passed) ? false : $is_passed;
	}

	/**
	 * Check if the requirements for PHP version is met
	 */
	protected function check_php_version()
	{
		$php_version = PHP_VERSION;

		if (version_compare($php_version, '5.4') < 0)
		{
			$this->response_helper->add_error_message('PHP_VERSION_REQD', 'PHP_VERSION_REQD_EXPLAIN');

			$this->set_test_passed(false);
			return;
		}

		$this->set_test_passed(true);
	}

	/**
	 * Checks if the installed PHP has getimagesize() available
	 */
	protected function check_image_size()
	{
		if (!@function_exists('getimagesize'))
		{
			$this->response_helper->add_error_message('PHP_GETIMAGESIZE_SUPPORT', 'PHP_GETIMAGESIZE_SUPPORT_EXPLAIN');

			$this->set_test_passed(false);
			return;
		}

		$this->set_test_passed(true);
	}

	/**
	 * Checks if the installed PHP supports PCRE
	 */
	protected function check_pcre()
	{
		if (@preg_match('//u', ''))
		{
			$this->set_test_passed(true);
			return;
		}

		$this->response_helper->add_error_message('PCRE_UTF_SUPPORT', 'PCRE_UTF_SUPPORT_EXPLAIN');

		$this->set_test_passed(false);
	}

	/**
	 * Checks whether PHP's JSON extension is available or not
	 */
	protected function check_json()
	{
		if (@extension_loaded('json'))
		{
			$this->set_test_passed(true);
			return;
		}

		$this->response_helper->add_error_message('PHP_JSON_SUPPORT', 'PHP_JSON_SUPPORT_EXPLAIN');

		$this->set_test_passed(false);
	}

	/**
	 * Checks whether or not the XML PHP extension is available (Required by the text formatter)
	 */
	protected function check_xml()
	{
		if (class_exists('DOMDocument'))
		{
			$this->set_test_passed(true);
			return;
		}

		$this->response_helper->add_error_message('PHP_XML_SUPPORT', 'PHP_XML_SUPPORT_EXPLAIN');

		$this->set_test_passed(false);
	}

	/**
	 * Check if any supported DBMS is available
	 */
	protected function check_available_dbms()
	{
		$available_dbms = $this->database_helper->get_available_dbms(false, true);

		if ($available_dbms['ANY_DB_SUPPORT'])
		{
			$this->set_test_passed(true);
			return;
		}

		$this->response_helper->add_error_message('PHP_SUPPORTED_DB', 'PHP_SUPPORTED_DB_EXPLAIN');

		$this->set_test_passed(false);
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return '';
	}
}
