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

require_once __DIR__ . '/../../phpBB/includes/functions_user.php';
require_once __DIR__ . '/../mock/user.php';
require_once __DIR__ . '/validate_data_helper.php';

class phpbb_functions_validate_user_email_test extends phpbb_database_test_case
{
	protected $db;
	protected $user;
	protected $helper;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/validate_email.xml');
	}

	protected function setUp(): void
	{
		global $cache, $phpbb_container, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		parent::setUp();

		$phpbb_container = new phpbb_mock_container_builder();
		$config = new \phpbb\config\config([]);
		$this->db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$language = new phpbb\language\language(new phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->user = new phpbb\user($language, '\phpbb\datetime');
		$this->user->data['user_email'] = '';
		$this->helper = new phpbb_functions_validate_data_helper($this);

		$cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\dummy(),
			$config,
			$this->db,
			$phpbb_dispatcher,
			$phpbb_root_path,
			$phpEx
		);
		$cache->get_driver()->purge();

		$ban_type_email = new \phpbb\ban\type\email($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_ip = new \phpbb\ban\type\ip($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$phpbb_container->set('ban.type.ip', $ban_type_ip);
		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');
		$collection->add('ban.type.ip');
		$phpbb_log = new \phpbb\log\dummy();

		$ban_manager = new \phpbb\ban\manager($collection, $cache->get_driver(), $this->db, $language, $phpbb_log, $this->user, 'phpbb_bans', 'phpbb_users');
		$phpbb_container->set('ban.manager', $ban_manager);
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
