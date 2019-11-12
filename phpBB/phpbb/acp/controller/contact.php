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

namespace phpbb\acp\controller;

class contact
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config			$config			Config object
	 * @param \phpbb\config\db_text			$config_text	Config text object
	 * @param \phpbb\acp\helper\controller	$helper			ACP Controller helper object
	 * @param \phpbb\language\language		$language		Language object
	 * @param \phpbb\request\request		$request		Request object
	 * @param \phpbb\template\template		$template		Template object
	 * @param string						$root_path		phpBB root path
	 * @param string						$php_ext		php File extension
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\config\db_text $config_text,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		$root_path,
		$php_ext
	)
	{
		$this->config		= $config;
		$this->config_text	= $config_text;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->request		= $request;
		$this->template		= $template;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
	}

	public function main()
	{
		$this->language->add_lang(['acp/board', 'posting']);

		$error = '';
		$submit = $this->request->is_set_post('submit');
		$preview = $this->request->is_set_post('preview');

		$form_name = 'acp_contact';
		add_form_key($form_name);

		if (!function_exists('display_custom_bbcodes'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}

		$data = $this->config_text->get_array([
			'contact_admin_info',
			'contact_admin_info_uid',
			'contact_admin_info_bitfield',
			'contact_admin_info_flags',
		]);

		$info			= $data['contact_admin_info'];
		$info_uid		= $data['contact_admin_info_uid'];
		$info_bitfield	= $data['contact_admin_info_bitfield'];
		$info_flags		= $data['contact_admin_info_flags'];

		if ($submit || $preview)
		{
			if (!check_form_key($form_name))
			{
				$error = $this->language->lang('FORM_INVALID');
			}

			$info = $this->request->variable('contact_admin_info', '', true);

			generate_text_for_storage(
				$info,
				$info_uid,
				$info_bitfield,
				$info_flags,
				!$this->request->variable('disable_bbcode', false),
				!$this->request->variable('disable_magic_url', false),
				!$this->request->variable('disable_smilies', false)
			);

			if (empty($error) && $submit)
			{
				$this->config->set('contact_admin_form_enable', $this->request->variable('contact_admin_form_enable', false));

				$this->config_text->set_array([
					'contact_admin_info'			=> $info,
					'contact_admin_info_uid'		=> $info_uid,
					'contact_admin_info_bitfield'	=> $info_bitfield,
					'contact_admin_info_flags'		=> $info_flags,
				]);

				return $this->helper->message_back('CONTACT_US_INFO_UPDATED', 'acp_settings_contact');
			}
		}

		$info_preview = '';

		if ($this->request->is_set_post('preview'))
		{
			$info_preview = generate_text_for_display($info, $info_uid, $info_bitfield, $info_flags);
		}

		$edit = generate_text_for_edit($info, $info_uid, $info_flags);

		$this->template->assign_vars([
			'ERRORS'			=> $error,
			'CONTACT_ENABLED'	=> $this->config['contact_admin_form_enable'],

			'CONTACT_US_INFO'			=> $edit['text'],
			'CONTACT_US_INFO_PREVIEW'	=> $info_preview,

			'S_BBCODE_DISABLE_CHECKED'		=> !$edit['allow_bbcode'],
			'S_SMILIES_DISABLE_CHECKED'		=> !$edit['allow_smilies'],
			'S_MAGIC_URL_DISABLE_CHECKED'	=> !$edit['allow_urls'],

			'BBCODE_STATUS'			=> $this->language->lang('BBCODE_IS_ON', '<a href="' . $this->helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
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

		return $this->helper->render('acp_contact.html', $this->language->lang('ACP_CONTACT_SETTINGS'));
	}
}
