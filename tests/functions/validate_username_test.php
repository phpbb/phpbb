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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';
require_once dirname(__FILE__) . '/../mock/cache.php';
require_once dirname(__FILE__) . '/validate_data_helper.php';

class phpbb_functions_validate_data_test extends phpbb_database_test_case
{
	protected $db;
	protected $cache;
	protected $helper;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/validate_username.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->cache = new phpbb_mock_cache;
		$this->helper = new phpbb_functions_validate_data_helper($this);
	}

	public function validate_username_data()
	{
		return array(
			array('USERNAME_CHARS_ANY', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array(),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array(),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array(),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('USERNAME_TAKEN'),
			)),
			array('USERNAME_ALPHA_ONLY', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array('INVALID_CHARS'),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array('INVALID_CHARS'),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('INVALID_CHARS'),
			)),
			array('USERNAME_ALPHA_SPACERS', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array(),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array('INVALID_CHARS'),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('USERNAME_TAKEN'),
			)),
			array('USERNAME_LETTER_NUM', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array('INVALID_CHARS'),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array('INVALID_CHARS'),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('INVALID_CHARS'),
			)),
			array('USERNAME_LETTER_NUM_SPACERS', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array(),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array(),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('USERNAME_TAKEN'),
			)),
			array('USERNAME_ASCII', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array(),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array(),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array('INVALID_CHARS'),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('USERNAME_TAKEN'),
			)),
		);
	}

	/**
	* @dataProvider validate_username_data
	*/
	public function test_validate_username($allow_name_chars, $expected)
	{
		global $cache, $config, $db;

		$db = $this->db;
		$cache = $this->cache;
		$cache->put('_disallowed_usernames', array('barfoo'));

		$config['allow_name_chars'] = $allow_name_chars;

		$this->helper->assert_valid_data(array(
			'foobar_allow' => array(
				$expected['foobar_allow'],
				'foobar',
				array('username', 'foobar'),
			),
			'foobar_ascii' => array(
				$expected['foobar_ascii'],
				'foobar',
				array('username'),
			),
			'foobar_any' => array(
				$expected['foobar_any'],
				'f*~*^=oo_bar1',
				array('username'),
			),
			'foobar_alpha' => array(
				$expected['foobar_alpha'],
				'fo0Bar',
				array('username'),
			),
			'foobar_alpha_spacers' => array(
				$expected['foobar_alpha_spacers'],
				'Fo0-[B]_a+ R',
				array('username'),
			),
			'foobar_letter_num' => array(
				$expected['foobar_letter_num'],
				'fo0Bar0',
				array('username'),
			),
			'foobar_letter_num_sp' => array(
				$expected['foobar_letter_num_sp'],
				'Fö0-[B]_a+ R',
				array('username'),
			),
			'foobar_quot' => array(
				$expected['foobar_quot'],
				'"foobar"',
				array('username'),
			),
			'barfoo_disallow' => array(
				$expected['barfoo_disallow'],
				'barfoo',
				array('username'),
			),
			'admin_taken' => array(
				$expected['admin_taken'],
				'admin',
				array('username'),
			),
			'group_taken' => array(
				$expected['group_taken'],
				'foobar_group',
				array('username'),
			),
		));
	}
}
