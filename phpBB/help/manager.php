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

namespace phpbb\help;

/**
 * Class help page manager
 */
class manager
{
	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var bool */
	protected $switched_column;

	/**
	 * Constructor
	 *
	 * @param \phpbb\event\dispatcher_interface $dispatcher
	 * @param \phpbb\language\language $language
	 * @param \phpbb\template\template $template
	 */
	public function __construct(\phpbb\event\dispatcher_interface $dispatcher, \phpbb\language\language $language, \phpbb\template\template $template)
	{
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->template = $template;
	}

	/**
	 * Add a new faq block
	 *
	 * @param string $block_name	Name or language key with the name of the block
	 * @param bool $switch_column	Switch the column of the menu
	 * @param array $questions		Array of frequently asked questions
	 */
	public function add_block($block_name, $switch_column = false, $questions = array())
	{
		/**
		 * You can use this event to add a block before the current one.
		 *
		 * @event core.help_manager_add_block_before
		 * @var	string	block_name		Language key of the block headline
		 * @var	bool	switch_column	Should we switch the menu column before this headline
		 * @var	array	questions		Array with questions
		 * @since 3.2.0-a1
		 */
		$vars = array('block_name', 'switch_column', 'questions');
		extract($this->dispatcher->trigger_event('core.help_manager_add_block_before', compact($vars)));

		$this->template->assign_block_vars('faq_block', array(
			'BLOCK_TITLE'		=> $this->language->lang($block_name),
			'SWITCH_COLUMN'		=> !$this->switched_column && $switch_column,
		));

		foreach ($questions as $question => $answer)
		{
			$this->add_question($question, $answer);
		}

		$this->switched_column = $this->switched_column || $switch_column;

		/**
		 * You can use this event to add a block after the current one.
		 *
		 * @event core.help_manager_add_block_after
		 * @var	string	block_name		Language key of the block headline
		 * @var	bool	switch_column	Should we switch the menu column before this headline
		 * @var	array	questions		Array with questions
		 * @since 3.2.0-a1
		 */
		$vars = array('block_name', 'switch_column', 'questions');
		extract($this->dispatcher->trigger_event('core.help_manager_add_block_after', compact($vars)));
	}

	/**
	 * Add a new faq question
	 *
	 * @param string $question	Question or language key with the question of the block
	 * @param string $answer	Answer or language key with the answer of the block
	 */
	public function add_question($question, $answer)
	{
		/**
		 * You can use this event to add a question before the current one.
		 *
		 * @event core.help_manager_add_question_before
		 * @var	string	question	Language key of the question
		 * @var	string	answer		Language key of the answer
		 * @since 3.2.0-a1
		 */
		$vars = array('question', 'answer');
		extract($this->dispatcher->trigger_event('core.help_manager_add_question_before', compact($vars)));

		$this->template->assign_block_vars('faq_block.faq_row', array(
			'FAQ_QUESTION'		=> $this->language->lang($question),
			'FAQ_ANSWER'		=> $this->language->lang($answer),
		));

		/**
		 * You can use this event to add a question after the current one.
		 *
		 * @event core.help_manager_add_question_after
		 * @var	string	question	Language key of the question
		 * @var	string	answer		Language key of the answer
		 * @since 3.2.0-a1
		 */
		$vars = array('question', 'answer');
		extract($this->dispatcher->trigger_event('core.help_manager_add_question_after', compact($vars)));
	}

	/**
	 * Returns whether the block titles switched side
	 * @return bool
	 */
	public function switched_column()
	{
		return $this->switched_column;
	}
}
