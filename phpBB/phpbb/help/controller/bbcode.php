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

namespace phpbb\help\controller;

/**
 * BBCode help page
 */
class bbcode extends controller
{
	/**
	 * @return string The title of the page
	 */
	public function display()
	{
		$this->language->add_lang('help/bbcode');

		$this->manager->add_block(
			'HELP_BBCODE_BLOCK_INTRO',
			false,
			array(
				'HELP_BBCODE_INTRO_BBCODE_QUESTION' => 'HELP_BBCODE_INTRO_BBCODE_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_BBCODE_BLOCK_TEXT',
			false,
			array(
				'HELP_BBCODE_TEXT_BASIC_QUESTION' => 'HELP_BBCODE_TEXT_BASIC_ANSWER',
				'HELP_BBCODE_TEXT_COLOR_QUESTION' => 'HELP_BBCODE_TEXT_COLOR_ANSWER',
				'HELP_BBCODE_TEXT_COMBINE_QUESTION' => 'HELP_BBCODE_TEXT_COMBINE_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_BBCODE_BLOCK_QUOTES',
			false,
			array(
				'HELP_BBCODE_QUOTES_TEXT_QUESTION' => 'HELP_BBCODE_QUOTES_TEXT_ANSWER',
				'HELP_BBCODE_QUOTES_CODE_QUESTION' => 'HELP_BBCODE_QUOTES_CODE_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_BBCODE_BLOCK_LISTS',
			false,
			array(
				'HELP_BBCODE_LISTS_UNORDERER_QUESTION' => 'HELP_BBCODE_LISTS_UNORDERER_ANSWER',
				'HELP_BBCODE_LISTS_ORDERER_QUESTION' => 'HELP_BBCODE_LISTS_ORDERER_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_BBCODE_BLOCK_LINKS',
			true,
			array(
				'HELP_BBCODE_LINKS_BASIC_QUESTION' => 'HELP_BBCODE_LINKS_BASIC_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_BBCODE_BLOCK_IMAGES',
			false,
			array(
				'HELP_BBCODE_IMAGES_BASIC_QUESTION' => 'HELP_BBCODE_IMAGES_BASIC_ANSWER',
				'HELP_BBCODE_IMAGES_ATTACHMENT_QUESTION' => 'HELP_BBCODE_IMAGES_ATTACHMENT_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_BBCODE_BLOCK_OTHERS',
			false,
			array(
				'HELP_BBCODE_OTHERS_CUSTOM_QUESTION' => 'HELP_BBCODE_OTHERS_CUSTOM_ANSWER',
			)
		);

		return $this->language->lang('BBCODE_GUIDE');
	}
}
