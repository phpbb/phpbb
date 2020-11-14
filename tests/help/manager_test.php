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

	protected function setUp(): void
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
		$question_ary = $question_ary_upper = $template_call_args = [];
		foreach ($questions as $question => $answer)
		{
			$question_ary = array_merge($question_ary, [[$question], [$answer]]);
			$question_ary_upper = array_merge($question_ary_upper, [strtoupper($question), strtoupper($answer)]);
			$template_call_args = array_merge($template_call_args, [['faq_block.faq_row', [
					'FAQ_QUESTION' => strtoupper($question),
					'FAQ_ANSWER' => strtoupper($answer),
				]
			]]);
		}

		$this->language->expects($this->exactly(count($questions)*2 + 1))
			->method('lang')
			->withConsecutive([$block_name], ...$question_ary)
			->will($this->onConsecutiveCalls(strtoupper($block_name), ...$question_ary_upper));

		$this->template->expects($this->exactly(count($questions) + 1))
			->method('assign_block_vars')
			->withConsecutive(
				['faq_block', [
					'BLOCK_TITLE' => strtoupper($block_name),
					'SWITCH_COLUMN' => $switch_expected,
				]],
				...$template_call_args
			);

		$this->manager->add_block($block_name, $switch, $questions);

		$this->assertEquals($switch_expected, $this->manager->switched_column());
	}

	public function add_question_data()
	{
		return array(
			array('question1', 'answer1'),
			array('question2', 'answer2'),
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
		$this->language->expects($this->exactly(2))
			->method('lang')
			->withConsecutive(
				[$question],
				[$answer]
			)
			->will($this->onConsecutiveCalls(strtoupper($question), strtoupper($answer)));

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
		$block_name = ['abc', 'def'];
		$switch_expected = [true, false];

		$this->language->expects($this->exactly(2))
			->method('lang')
			->withConsecutive([$block_name[0]], [$block_name[1]])
			->will($this->onConsecutiveCalls(strtoupper($block_name[0]), strtoupper($block_name[1])));

		$this->template->expects($this->exactly(2))
			->method('assign_block_vars')
			->withConsecutive(
				['faq_block', [
					'BLOCK_TITLE' => strtoupper($block_name[0]),
					'SWITCH_COLUMN' => $switch_expected[0],
				]],
				['faq_block', [
					'BLOCK_TITLE' => strtoupper($block_name[1]),
					'SWITCH_COLUMN' => $switch_expected[1],
				]]
		
			);

		$this->manager->add_block($block_name[0], true);
		$this->assertTrue($this->manager->switched_column());

		// Add a second block with switch
		$this->manager->add_block($block_name[1], true);
		$this->assertTrue($this->manager->switched_column());
	}
}
