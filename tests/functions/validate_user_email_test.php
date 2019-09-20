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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/../mock/user.php';
require_once dirname(__FILE__) . '/validate_data_helper.php';

class phpbb_functions_validate_user_email_test extends phpbb_database_test_case
{
	protected $db;
	protected $user;
	protected $helper;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/validate_email.xml');
	}

	protected function setUp(): void
	{
		global $cache, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		parent::setUp();

		$cache = new \phpbb\cache\driver\file();
		$cache->purge();
		$this->db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$language = new phpbb\language\language(new phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->user = new phpbb\user($language, '\phpbb\datetime');
		$this->helper = new phpbb_functions_validate_data_helper($this);
	}

	/**
	* Get validation prerequesites
	*
	* @param bool $check_mx Whether mx records should be checked
	*/
	protected function set_validation_prerequisites($check_mx)
	{
		global $config, $db, $user;

		$config['email_check_mx'] = $check_mx;
		$db = $this->db;
		$user = $this->user;
	}

	public static function validate_user_email_data()
	{
		return array(
			array('empty', array(), ''),
			array('allowed', array(), 'foobar@example.com'),
			array('valid_complex', array(), "'%$~test@example.com"),
			array('invalid', array('EMAIL_INVALID'), 'fööbar@example.com'),
			array('taken', array('EMAIL_TAKEN'), 'admin@example.com'),
			array('banned', ['just because'], 'banned2@example.com'),
			array('banned', ['EMAIL_BANNED'], 'banned@example.com')
		);
	}

	/**
	* @dataProvider validate_user_email_data
	*/
	public function test_validate_user_email($case, $errors, $email)
	{
		$this->set_validation_prerequisites(false);

		$this->helper->assert_valid_data(array(
			$case => array(
				$errors,
				$email,
				array('user_email'),
			),
		));
	}

	public static function validate_user_email_mx_data()
	{
		return array(
			array('valid', array(), 'foobar@phpbb.com'),
			array('no_mx', array('DOMAIN_NO_MX_RECORD'), 'test@does-not-exist.phpbb.com'),
		);
	}

	/**
	* @dataProvider validate_user_email_mx_data
	* @group slow
	*/
	public function test_validate_user_email_mx($case, $errors, $email)
	{
		$this->set_validation_prerequisites(true);

		$this->helper->assert_valid_data(array(
			$case => array(
				$errors,
				$email,
				array('user_email'),
			),
		));
	}
}
