<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
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

	public function test_validate_email()
	{
		$this->set_validation_prerequisites(false);

		$this->helper->assert_valid_data(array(
			'empty' => array(
				array(),
				'',
				array('email'),
			),
			'allowed' => array(
				array(),
				'foobar@example.com',
				array('email', 'foobar@example.com'),
			),
			'invalid' => array(
				array('EMAIL_INVALID'),
				'fööbar@example.com',
				array('email'),
			),
			'valid_complex' => array(
				array(),
				"'%$~test@example.com",
				array('email'),
			),
			'taken' => array(
				array('EMAIL_TAKEN'),
				'admin@example.com',
				array('email'),
			),
			'banned' => array(
				array('EMAIL_BANNED'),
				'banned@example.com',
				array('email'),
			),
		));
	}

	/**
	* @group slow
	*/
	public function test_validate_email_mx()
	{
		$this->set_validation_prerequisites(true);

		$this->helper->assert_valid_data(array(
			'valid' => array(
				array(),
				'foobar@phpbb.com',
				array('email'),
			),
			'no_mx' => array(
				array('DOMAIN_NO_MX_RECORD'),
				'test@does-not-exist.phpbb.com',
				array('email'),
			),
		));
	}
}
