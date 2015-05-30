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

class phpbb_help_manager_test extends phpbb_test_case
{
	/** @var \phpbb\help\manager */
	protected $manager;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\language\language */
	protected $language;

	public function setUp()
	{
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();

		$this->manager = new \phpbb\help\manager(
			new \phpbb_mock_event_dispatcher(),
			$this->language,
			$this->template
		);
	}

	public function add_block_data()
	{
		return array(
			array('abc', false, array(), false),
			array('def', true, array(), true),
			array(
				'abc',
				false,
				array(
					'question1' => 'answer1',
					'question2' => 'answer2',
					'question3' => 'answer3',
				),
				false
			),
		);
	}

	/**
	 * @dataProvider add_block_data
	 *
	 * @param string $block_name
	 * @param bool $switch
	 * @param array $questions
	 * @param bool $switch_expected
	 */
	public function test_add_block($block_name, $switch, $questions, $switch_expected)
	{
		$this->language->expects($this->at(0))
			->method('lang')
			->with($block_name)
			->willReturn(strtoupper($block_name));
		$lang_call_count = 1;
		foreach ($questions as $question => $answer)
		{
			$this->language->expects($this->at($lang_call_count))
				->method('lang')
				->with($question)
				->willReturn(strtoupper($question));
			$lang_call_count++;

			$this->language->expects($this->at($lang_call_count))
				->method('lang')
				->with($answer)
				->willReturn(strtoupper($answer));
			$lang_call_count++;
		}

		$this->template->expects($this->at(0))
			->method('assign_block_vars')
			->with('faq_block', array(
				'BLOCK_TITLE' => strtoupper($block_name),
				'SWITCH_COLUMN' => $switch_expected,
			));
		$template_call_count = 1;
		foreach ($questions as $question => $answer)
		{
			$this->template->expects($this->at($template_call_count))
				->method('assign_block_vars')
				->with('faq_block.faq_row', array(
					'FAQ_QUESTION' => strtoupper($question),
					'FAQ_ANSWER' => strtoupper($answer),
				));
			$template_call_count++;
		}

		$this->manager->add_block($block_name, $switch, $questions);

		$this->assertEquals($switch_expected, $this->manager->switched_column());
	}

	public function add_question_data()
	{
		return array(
			array('abc', false, false),
			array('def', true, true),
		);
	}

	/**
	 * @dataProvider add_question_data
	 *
	 * @param string $question
	 * @param string $answer
	 */
	public function test_add_question($question, $answer)
	{
		$this->language->expects($this->at(0))
			->method('lang')
			->with($question)
			->willReturn(strtoupper($question));
		$this->language->expects($this->at(1))
			->method('lang')
			->with($answer)
			->willReturn(strtoupper($answer));

		$this->template->expects($this->once())
			->method('assign_block_vars')
			->with('faq_block.faq_row', array(
				'FAQ_QUESTION' => strtoupper($question),
				'FAQ_ANSWER' => strtoupper($answer),
			));

		$this->manager->add_question($question, $answer);
	}

	public function test_add_block_double_switch()
	{
		$block_name = 'abc';
		$switch_expected = true;

		$this->language->expects($this->at(0))
			->method('lang')
			->with($block_name)
			->willReturn(strtoupper($block_name));

		$this->template->expects($this->at(0))
			->method('assign_block_vars')
			->with('faq_block', array(
				'BLOCK_TITLE' => strtoupper($block_name),
				'SWITCH_COLUMN' => $switch_expected,
			));

		$this->manager->add_block($block_name, true);
		$this->assertTrue($this->manager->switched_column());

		// Add a second block with switch
		$block_name = 'def';
		$switch_expected = false;

		$this->language->expects($this->at(0))
			->method('lang')
			->with($block_name)
			->willReturn(strtoupper($block_name));

		$this->template->expects($this->at(0))
			->method('assign_block_vars')
			->with('faq_block', array(
				'BLOCK_TITLE' => strtoupper($block_name),
				'SWITCH_COLUMN' => $switch_expected,
			));

		$this->manager->add_block($block_name, true);
		$this->assertTrue($this->manager->switched_column());
	}
}
