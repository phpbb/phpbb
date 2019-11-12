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

/**
 * @package acp
 */
class contact
{
	public $u_action;

	public function main($id, $mode)
	{
		$this->language->add_lang(['acp/board', 'posting']);

		$this->tpl_name = 'acp_contact';
		$this->page_title = 'ACP_CONTACT_SETTINGS';
		$form_name = 'acp_contact';
		add_form_key($form_name);
		$error = '';

		if (!function_exists('display_custom_bbcodes'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}

		/* @var $config_text \phpbb\config\db_text */
		$config_text = $phpbb_container->get('config_text');

		$contact_admin_data			= $this->config_text->get_array([
			'contact_admin_info',
			'contact_admin_info_uid',
			'contact_admin_info_bitfield',
			'contact_admin_info_flags',
		]);

		$contact_admin_info			= $contact_admin_data['contact_admin_info'];
		$contact_admin_info_uid		= $contact_admin_data['contact_admin_info_uid'];
		$contact_admin_info_bitfield= $contact_admin_data['contact_admin_info_bitfield'];
		$contact_admin_info_flags	= $contact_admin_data['contact_admin_info_flags'];

		if ($this->request->is_set_post('submit') || $this->request->is_set_post('preview'))
		{
			if (!check_form_key($form_name))
			{
				$error = $this->language->lang('FORM_INVALID');
			}

			$contact_admin_info = $this->request->variable('contact_admin_info', '', true);

			generate_text_for_storage(
				$contact_admin_info,
				$contact_admin_info_uid,
				$contact_admin_info_bitfield,
				$contact_admin_info_flags,
				!$this->request->variable('disable_bbcode', false),
				!$this->request->variable('disable_magic_url', false),
				!$this->request->variable('disable_smilies', false)
			);

			if (empty($error) && $this->request->is_set_post('submit'))
			{
				$this->config->set('contact_admin_form_enable', $this->request->variable('contact_admin_form_enable', false));

				$this->config_text->set_array([
					'contact_admin_info'			=> $contact_admin_info,
					'contact_admin_info_uid'		=> $contact_admin_info_uid,
					'contact_admin_info_bitfield'	=> $contact_admin_info_bitfield,
					'contact_admin_info_flags'		=> $contact_admin_info_flags,
				]);

				trigger_error($this->language->lang('CONTACT_US_INFO_UPDATED') . adm_back_link($this->u_action));
			}
		}

		$contact_admin_info_preview = '';
		if ($this->request->is_set_post('preview'))
		{
			$contact_admin_info_preview = generate_text_for_display($contact_admin_info, $contact_admin_info_uid, $contact_admin_info_bitfield, $contact_admin_info_flags);
		}

		$contact_admin_edit = generate_text_for_edit($contact_admin_info, $contact_admin_info_uid, $contact_admin_info_flags);

		/** @var \phpbb\controller\helper $controller_helper */
		$controller_helper = $phpbb_container->get('controller.helper');

		$this->template->assign_vars([
			'ERRORS'			=> $error,
			'CONTACT_ENABLED'	=> $this->config['contact_admin_form_enable'],

			'CONTACT_US_INFO'			=> $contact_admin_edit['text'],
			'CONTACT_US_INFO_PREVIEW'	=> $contact_admin_info_preview,

			'S_BBCODE_DISABLE_CHECKED'		=> !$contact_admin_edit['allow_bbcode'],
			'S_SMILIES_DISABLE_CHECKED'		=> !$contact_admin_edit['allow_smilies'],
			'S_MAGIC_URL_DISABLE_CHECKED'	=> !$contact_admin_edit['allow_urls'],

			'BBCODE_STATUS'			=> $this->language->lang('BBCODE_IS_ON', '<a href="' . $this->controller_helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
			'SMILIES_STATUS'		=> $this->language->lang('SMILIES_ARE_ON'),
			'IMG_STATUS'			=> $this->language->lang('IMAGES_ARE_ON'),
			'FLASH_STATUS'			=> $this->language->lang('FLASH_IS_ON'),
			'URL_STATUS'			=> $this->language->lang('URL_IS_ON'),

			'S_BBCODE_ALLOWED'		=> true,
			'S_SMILIES_ALLOWED'		=> true,
			'S_BBCODE_IMG'			=> true,
			'S_BBCODE_FLASH'		=> true,
			'S_LINKS_ALLOWED'		=> true,
		]);

		// Assigning custom bbcodes
		display_custom_bbcodes();
	}
}
