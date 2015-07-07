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

class phpbb_captcha_qa_test extends \phpbb_database_test_case
{
	protected $request;

	/** @var \phpbb\captcha\plugins\qa */
	protected $qa;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../fixtures/empty.xml');
	}

	public function setUp()
	{
		global $db, $request, $phpbb_container;

		$db = $this->new_dbal();

		parent::setUp();

		$request = new \phpbb_mock_request();
		$phpbb_container = new \phpbb_mock_container_builder();
		$factory = new \phpbb\db\tools\factory();
		$phpbb_container->set('dbal.tools', $factory->get($db));
		$this->qa = new \phpbb\captcha\plugins\qa('phpbb_captcha_questions', 'phpbb_captcha_answers', 'phpbb_qa_confirm');
	}

	public function test_is_installed()
	{
		$this->assertFalse($this->qa->is_installed());

		$this->qa->install();

		$this->assertTrue($this->qa->is_installed());
	}

	public function test_set_get_name()
	{
		$this->assertNull($this->qa->get_service_name());
		$this->qa->set_name('foobar');
		$this->assertSame('foobar', $this->qa->get_service_name());
	}

	public function data_acp_get_question_input()
	{
		return array(
			array("foobar\ntest\nyes", array(
				'question_text'	=> '',
				'strict'	=> false,
				'lang_iso'	=> '',
				'answers'	=> array('foobar', 'test', 'yes')
			)),
			array("foobar\ntest\n \nyes", array(
				'question_text'	=> '',
				'strict'	=> false,
				'lang_iso'	=> '',
				'answers'	=> array(
					0 => 'foobar',
					1 => 'test',
					3 => 'yes',
				)
			)),
			array('', array(
				'question_text'	=> '',
				'strict'	=> false,
				'lang_iso'	=> '',
				'answers'	=> '',
			)),
		);
	}

	/**
	 * @dataProvider data_acp_get_question_input
	 */
	public function test_acp_get_question_input($value, $expected)
	{
		global $request;
		$request->overwrite('answers', $value);

		$this->assertEquals($expected, $this->qa->acp_get_question_input());
	}
}
