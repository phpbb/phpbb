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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_contact
{
	public $u_action;

	public function main($id, $mode)
	{
		global $user, $request, $template;
		global $config, $phpbb_root_path, $phpEx, $phpbb_container;

		$user->add_lang(array('acp/board', 'posting'));

		$this->tpl_name = 'acp_contact';
		$this->page_title = 'ACP_CONTACT_SETTINGS';
		$form_name = 'acp_contact';
		add_form_key($form_name);
		$error = '';

		if (!function_exists('display_custom_bbcodes'))
		{
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		}
		if (!class_exists('parse_message'))
		{
			include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		}

		$config_text = $phpbb_container->get('config_text');

		$contact_admin_data			= $config_text->get_array(array(
			'contact_admin_info',
			'contact_admin_info_uid',
			'contact_admin_info_bitfield',
			'contact_admin_info_flags',
		));

		$contact_admin_info			= $contact_admin_data['contact_admin_info'];
		$contact_admin_info_uid		= $contact_admin_data['contact_admin_info_uid'];
		$contact_admin_info_bitfield= $contact_admin_data['contact_admin_info_bitfield'];
		$contact_admin_info_flags	= $contact_admin_data['contact_admin_info_flags'];

		if ($request->is_set_post('submit') || $request->is_set_post('preview'))
		{
			if (!check_form_key($form_name))
			{
				$error = $user->lang('FORM_INVALID');
			}

			$contact_admin_info = $request->variable('contact_admin_info', '', true);

			generate_text_for_storage(
				$contact_admin_info,
				$contact_admin_info_uid,
				$contact_admin_info_bitfield,
				$contact_admin_info_flags,
				!$request->variable('disable_bbcode', false),
				!$request->variable('disable_magic_url', false),
				!$request->variable('disable_smilies', false)
			);

			if (empty($error) && $request->is_set_post('submit'))
			{
				$config->set('contact_admin_form_enable', $request->variable('contact_admin_form_enable', false));

				$config_text->set_array(array(
					'contact_admin_info'			=> $contact_admin_info,
					'contact_admin_info_uid'		=> $contact_admin_info_uid,
					'contact_admin_info_bitfield'	=> $contact_admin_info_bitfield,
					'contact_admin_info_flags'		=> $contact_admin_info_flags,
				));

				trigger_error($user->lang['CONTACT_US_INFO_UPDATED'] . adm_back_link($this->u_action));
			}
		}

		$contact_admin_info_preview = '';
		if ($request->is_set_post('preview'))
		{
			$contact_admin_info_preview = generate_text_for_display($contact_admin_info, $contact_admin_info_uid, $contact_admin_info_bitfield, $contact_admin_info_flags);
		}

		$contact_admin_edit = generate_text_for_edit($contact_admin_info, $contact_admin_info_uid, $contact_admin_info_flags);

		$template->assign_vars(array(
			'ERRORS'			=> $error,
			'CONTACT_ENABLED'	=> $config['contact_admin_form_enable'],

			'CONTACT_US_INFO'			=> $contact_admin_edit['text'],
			'CONTACT_US_INFO_PREVIEW'	=> $contact_admin_info_preview,

			'S_BBCODE_DISABLE_CHECKED'		=> !$contact_admin_edit['allow_bbcode'],
			'S_SMILIES_DISABLE_CHECKED'		=> !$contact_admin_edit['allow_smilies'],
			'S_MAGIC_URL_DISABLE_CHECKED'	=> !$contact_admin_edit['allow_urls'],

			'BBCODE_STATUS'			=> $user->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
			'SMILIES_STATUS'		=> $user->lang['SMILIES_ARE_ON'],
			'IMG_STATUS'			=> $user->lang['IMAGES_ARE_ON'],
			'FLASH_STATUS'			=> $user->lang['FLASH_IS_ON'],
			'URL_STATUS'			=> $user->lang['URL_IS_ON'],

			'S_BBCODE_ALLOWED'		=> true,
			'S_SMILIES_ALLOWED'		=> true,
			'S_BBCODE_IMG'			=> true,
			'S_BBCODE_FLASH'		=> true,
			'S_LINKS_ALLOWED'		=> true,
		));

		// Assigning custom bbcodes
		display_custom_bbcodes();
	}
}
