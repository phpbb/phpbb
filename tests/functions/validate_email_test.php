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
require_once dirname(__FILE__) . '/common_validate_data.php';

class phpbb_functions_validate_email_test extends phpbb_database_test_case
{
	protected $db;
	protected $user;
	protected $common;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/validate_email.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->user = new phpbb_mock_user;
		$this->common = new phpbb_functions_common_validate_data;
	}

	public function test_validate_email()
	{
		global $config, $db, $user;

		$config['email_check_mx'] = true;
		$db = $this->db;
		$user = $this->user;
		$user->optionset('banned_users', array('banned@example.com'));

		$this->common->validate_data_check(array(
			'empty'			=> '',
			'allowed'		=> 'foobar@example.com',
			'invalid'		=> 'fööbar@example.com',
			'valid_complex'		=> "'%$~test@example.com",
			'taken'			=> 'admin@example.com',
			'banned'		=> 'banned@example.com',
			'no_mx'			=> 'test@wwrrrhhghgghgh.ttv',
		),
		array(
			'empty'			=> array('email'),
			'allowed'		=> array('email', 'foobar@example.com'),
			'invalid'		=> array('email'),
			'valid_complex'		=> array('email'),
			'taken'			=> array('email'),
			'banned'		=> array('email'),
			'no_mx'			=> array('email'),
		),
		array(
			'empty'			=> array(),
			'allowed'		=> array(),
			'invalid'		=> array('EMAIL_INVALID'),
			'valid_complex'		=> array(),
			'taken'			=> array('EMAIL_TAKEN'),
			'banned'		=> array('EMAIL_BANNED'),
			'no_mx'			=> array('DOMAIN_NO_MX_RECORD'),
		));
	}
}
