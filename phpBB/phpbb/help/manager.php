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
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var bool */
	protected $switched_column;

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template $template
	 * @param \phpbb\language\language $language
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\language\language $language)
	{
		$this->template = $template;
		$this->language = $language;
	}

	/**
	 * Add a new faq block
	 *
	 * @param string $block_name	Name or language key with the name of the block
	 * @param bool $switch_column	Switch the column of the menu
	 */
	public function add_block($block_name, $switch_column = false, $questions = array())
	{
		$this->template->assign_block_vars('faq_block', array(
			'BLOCK_TITLE'		=> $this->language->lang($block_name),
			'SWITCH_COLUMN'		=> !$this->switched_column && $switch_column,
		));

		foreach ($questions as $question => $answer)
		{
			$this->add_question($question, $answer);
		}

		$this->switched_column = $this->switched_column || $switch_column;
	}

	/**
	 * Add a new faq question
	 *
	 * @param string $question	Question or language key with the question of the block
	 * @param string $answer	Answer or language key with the answer of the block
	 */
	public function add_question($question, $answer)
	{
		$this->template->assign_block_vars('faq_block.faq_row', array(
			'FAQ_QUESTION'		=> $this->language->lang($question),
			'FAQ_ANSWER'		=> $this->language->lang($answer),
		));
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
