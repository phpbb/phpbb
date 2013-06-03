<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

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
				'group_taken'		=> array('USERNAME_TAKEN')
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
				'group_taken'		=> array('INVALID_CHARS')
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
				'group_taken'		=> array('USERNAME_TAKEN')
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
				'group_taken'		=> array('INVALID_CHARS')
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
				'group_taken'		=> array('USERNAME_TAKEN')
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
				'group_taken'		=> array('USERNAME_TAKEN')
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

		$this->helper->assert_validate_data($expected, array(
			'foobar_allow'		=> 'foobar',
			'foobar_ascii'		=> 'foobar',
			'foobar_any'		=> 'f*~*^=oo_bar1',
			'foobar_alpha'		=> 'fo0Bar',
			'foobar_alpha_spacers'	=> 'Fo0-[B]_a+ R',
			'foobar_letter_num'	=> 'fo0Bar0',
			'foobar_letter_num_sp'	=> 'Fö0-[B]_a+ R',
			'foobar_quot'		=> '"foobar"',
			'barfoo_disallow'	=> 'barfoo',
			'admin_taken'		=> 'admin',
			'group_taken'		=> 'foobar_group',
		),
		array(
			'foobar_allow'		=> array('username', 'foobar'),
			'foobar_ascii'		=> array('username'),
			'foobar_any'		=> array('username'),
			'foobar_alpha'		=> array('username'),
			'foobar_alpha_spacers'	=> array('username'),
			'foobar_letter_num'	=> array('username'),
			'foobar_letter_num_sp'	=> array('username'),
			'foobar_quot'		=> array('username'),
			'barfoo_disallow'	=> array('username'),
			'admin_taken'		=> array('username'),
			'group_taken'		=> array('username'),
		));
	}
}
