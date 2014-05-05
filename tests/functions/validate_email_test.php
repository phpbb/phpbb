<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/../mock/user.php';
require_once dirname(__FILE__) . '/validate_data_helper.php';

class phpbb_functions_validate_email_test extends phpbb_database_test_case
{
	protected $db;
	protected $user;
	protected $helper;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/validate_email.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->user = new phpbb_mock_user;
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
		$user->optionset('banned_users', array('banned@example.com'));
	}

	public static function validate_email_data()
	{
		return array(
			array('empty', array('EMAIL_INVALID'), ''),		// email does not allow empty
			array('allowed', array(), 'foobar@example.com'),
			array('valid_complex', array(), "'%$~test@example.com"),
			array('invalid', array('EMAIL_INVALID'), 'fööbar@example.com'),
			array('taken', array(), 'admin@example.com'),	// email does not check taken, should use user_email instead
			array('banned', array(), 'banned@example.com'),	// email does not check ban, should use user_email instead
		);
	}

	/**
	* @dataProvider validate_email_data
	*/
	public function test_validate_email($case, $errors, $email)
	{
		$this->set_validation_prerequisites(false);

		$this->helper->assert_valid_data(array(
			$case => array(
				$errors,
				$email,
				array('email'),
			),
		));
	}

	public static function validate_email_mx_data()
	{
		return array(
			array('valid', array(), 'foobar@phpbb.com'),
			array('no_mx', array('DOMAIN_NO_MX_RECORD'), 'test@does-not-exist.phpbb.com'),
		);
	}

	/**
	* @dataProvider validate_email_mx_data
	* @group slow
	*/
	public function test_validate_email_mx($case, $errors, $email)
	{
		$this->set_validation_prerequisites(true);

		$this->helper->assert_valid_data(array(
			$case => array(
				$errors,
				$email,
				array('email'),
			),
		));
	}
}
