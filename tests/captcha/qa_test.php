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

class phpbb_qa_test extends \phpbb_database_test_case
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
		global $db;

		$db = $this->new_dbal();

		parent::setUp();

		$this->request = new \phpbb_mock_request();
		$this->qa = new \phpbb\captcha\plugins\qa($this->request, 'phpbb_captcha_questions', 'phpbb_captcha_answers', 'phpbb_qa_confirm');
	}

	public function test_is_installed()
	{
		$this->assertFalse($this->qa->is_installed());

		$this->qa->install();

		$this->assertTrue($this->qa->is_installed());
	}
}