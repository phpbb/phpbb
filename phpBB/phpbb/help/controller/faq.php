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
 * FAQ help page
 */
class faq extends controller
{
	/**
	 * @return string The title of the page
	 */
	public function display()
	{
		$this->language->add_lang('help/faq');

		$this->template->assign_block_vars('navlinks', array(
			'BREADCRUMB_NAME'	=> $this->language->lang('FAQ_EXPLAIN'),
			'U_BREADCRUMB'		=> $this->helper->route('phpbb_help_faq_controller'),
		));

		$this->manager->add_block(
			'HELP_FAQ_BLOCK_LOGIN',
			false,
			array(
				'HELP_FAQ_LOGIN_REGISTER_QUESTION' => 'HELP_FAQ_LOGIN_REGISTER_ANSWER',
				'HELP_FAQ_LOGIN_COPPA_QUESTION' => 'HELP_FAQ_LOGIN_COPPA_ANSWER',
				'HELP_FAQ_LOGIN_CANNOT_REGISTER_QUESTION' => 'HELP_FAQ_LOGIN_CANNOT_REGISTER_ANSWER',
				'HELP_FAQ_LOGIN_REGISTER_CONFIRM_QUESTION' => 'HELP_FAQ_LOGIN_REGISTER_CONFIRM_ANSWER',
				'HELP_FAQ_LOGIN_CANNOT_LOGIN_QUESTION' => 'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANSWER',
				'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANYMORE_QUESTION' => 'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANYMORE_ANSWER',
				'HELP_FAQ_LOGIN_LOST_PASSWORD_QUESTION' => 'HELP_FAQ_LOGIN_LOST_PASSWORD_ANSWER',
				'HELP_FAQ_LOGIN_AUTO_LOGOUT_QUESTION' => 'HELP_FAQ_LOGIN_AUTO_LOGOUT_ANSWER',
				'HELP_FAQ_LOGIN_DELETE_COOKIES_QUESTION' => 'HELP_FAQ_LOGIN_DELETE_COOKIES_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_USERSETTINGS',
			false,
			array(
				'HELP_FAQ_USERSETTINGS_CHANGE_SETTINGS_QUESTION' => 'HELP_FAQ_USERSETTINGS_CHANGE_SETTINGS_ANSWER',
				'HELP_FAQ_USERSETTINGS_HIDE_ONLINE_QUESTION' => 'HELP_FAQ_USERSETTINGS_HIDE_ONLINE_ANSWER',
				'HELP_FAQ_USERSETTINGS_TIMEZONE_QUESTION' => 'HELP_FAQ_USERSETTINGS_TIMEZONE_ANSWER',
				'HELP_FAQ_USERSETTINGS_SERVERTIME_QUESTION' => 'HELP_FAQ_USERSETTINGS_SERVERTIME_ANSWER',
				'HELP_FAQ_USERSETTINGS_LANGUAGE_QUESTION' => 'HELP_FAQ_USERSETTINGS_LANGUAGE_ANSWER',
				'HELP_FAQ_USERSETTINGS_AVATAR_QUESTION' => 'HELP_FAQ_USERSETTINGS_AVATAR_ANSWER',
				'HELP_FAQ_USERSETTINGS_AVATAR_DISPLAY_QUESTION' => 'HELP_FAQ_USERSETTINGS_AVATAR_DISPLAY_ANSWER',
				'HELP_FAQ_USERSETTINGS_RANK_QUESTION' => 'HELP_FAQ_USERSETTINGS_RANK_ANSWER',
				'HELP_FAQ_USERSETTINGS_EMAIL_LOGIN_QUESTION' => 'HELP_FAQ_USERSETTINGS_EMAIL_LOGIN_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_POSTING',
			false,
			array(
				'HELP_FAQ_POSTING_CREATE_QUESTION' => 'HELP_FAQ_POSTING_CREATE_ANSWER',
				'HELP_FAQ_POSTING_EDIT_DELETE_QUESTION' => 'HELP_FAQ_POSTING_EDIT_DELETE_ANSWER',
				'HELP_FAQ_POSTING_SIGNATURE_QUESTION' => 'HELP_FAQ_POSTING_SIGNATURE_ANSWER',
				'HELP_FAQ_POSTING_POLL_CREATE_QUESTION' => 'HELP_FAQ_POSTING_POLL_CREATE_ANSWER',
				'HELP_FAQ_POSTING_POLL_ADD_QUESTION' => 'HELP_FAQ_POSTING_POLL_ADD_ANSWER',
				'HELP_FAQ_POSTING_POLL_EDIT_QUESTION' => 'HELP_FAQ_POSTING_POLL_EDIT_ANSWER',
				'HELP_FAQ_POSTING_FORUM_RESTRICTED_QUESTION' => 'HELP_FAQ_POSTING_FORUM_RESTRICTED_ANSWER',
				'HELP_FAQ_POSTING_NO_ATTACHMENTS_QUESTION' => 'HELP_FAQ_POSTING_NO_ATTACHMENTS_ANSWER',
				'HELP_FAQ_POSTING_WARNING_QUESTION' => 'HELP_FAQ_POSTING_WARNING_ANSWER',
				'HELP_FAQ_POSTING_REPORT_QUESTION' => 'HELP_FAQ_POSTING_REPORT_ANSWER',
				'HELP_FAQ_POSTING_DRAFT_QUESTION' => 'HELP_FAQ_POSTING_DRAFT_ANSWER',
				'HELP_FAQ_POSTING_QUEUE_QUESTION' => 'HELP_FAQ_POSTING_QUEUE_ANSWER',
				'HELP_FAQ_POSTING_BUMP_QUESTION' => 'HELP_FAQ_POSTING_BUMP_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_FORMATTING',
			false,
			array(
				'HELP_FAQ_FORMATTING_BBOCDE_QUESTION' => 'HELP_FAQ_FORMATTING_BBOCDE_ANSWER',
				'HELP_FAQ_FORMATTING_HTML_QUESTION' => 'HELP_FAQ_FORMATTING_HTML_ANSWER',
				'HELP_FAQ_FORMATTING_SMILIES_QUESTION' => 'HELP_FAQ_FORMATTING_SMILIES_ANSWER',
				'HELP_FAQ_FORMATTING_IMAGES_QUESTION' => 'HELP_FAQ_FORMATTING_IMAGES_ANSWER',
				'HELP_FAQ_FORMATTING_GLOBAL_ANNOUNCE_QUESTION' => 'HELP_FAQ_FORMATTING_GLOBAL_ANNOUNCE_ANSWER',
				'HELP_FAQ_FORMATTING_ANNOUNCEMENT_QUESTION' => 'HELP_FAQ_FORMATTING_ANNOUNCEMENT_ANSWER',
				'HELP_FAQ_FORMATTING_STICKIES_QUESTION' => 'HELP_FAQ_FORMATTING_STICKIES_ANSWER',
				'HELP_FAQ_FORMATTING_LOCKED_QUESTION' => 'HELP_FAQ_FORMATTING_LOCKED_ANSWER',
				'HELP_FAQ_FORMATTING_ICONS_QUESTION' => 'HELP_FAQ_FORMATTING_ICONS_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_GROUPS',
			true,
			array(
				'HELP_FAQ_GROUPS_ADMINISTRATORS_QUESTION' => 'HELP_FAQ_GROUPS_ADMINISTRATORS_ANSWER',
				'HELP_FAQ_GROUPS_MODERATORS_QUESTION' => 'HELP_FAQ_GROUPS_MODERATORS_ANSWER',
				'HELP_FAQ_GROUPS_USERGROUPS_QUESTION' => 'HELP_FAQ_GROUPS_USERGROUPS_ANSWER',
				'HELP_FAQ_GROUPS_USERGROUPS_JOIN_QUESTION' => 'HELP_FAQ_GROUPS_USERGROUPS_JOIN_ANSWER',
				'HELP_FAQ_GROUPS_USERGROUPS_LEAD_QUESTION' => 'HELP_FAQ_GROUPS_USERGROUPS_LEAD_ANSWER',
				'HELP_FAQ_GROUPS_COLORS_QUESTION' => 'HELP_FAQ_GROUPS_COLORS_ANSWER',
				'HELP_FAQ_GROUPS_DEFAULT_QUESTION' => 'HELP_FAQ_GROUPS_DEFAULT_ANSWER',
				'HELP_FAQ_GROUPS_TEAM_QUESTION' => 'HELP_FAQ_GROUPS_TEAM_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_PMS',
			false,
			array(
				'HELP_FAQ_PMS_CANNOT_SEND_QUESTION' => 'HELP_FAQ_PMS_CANNOT_SEND_ANSWER',
				'HELP_FAQ_PMS_UNWANTED_QUESTION' => 'HELP_FAQ_PMS_UNWANTED_ANSWER',
				'HELP_FAQ_PMS_SPAM_QUESTION' => 'HELP_FAQ_PMS_SPAM_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_FRIENDS',
			false,
			array(
				'HELP_FAQ_FRIENDS_BASIC_QUESTION' => 'HELP_FAQ_FRIENDS_BASIC_ANSWER',
				'HELP_FAQ_FRIENDS_MANAGE_QUESTION' => 'HELP_FAQ_FRIENDS_MANAGE_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_SEARCH',
			false,
			array(
				'HELP_FAQ_SEARCH_FORUM_QUESTION' => 'HELP_FAQ_SEARCH_FORUM_ANSWER',
				'HELP_FAQ_SEARCH_NO_RESULT_QUESTION' => 'HELP_FAQ_SEARCH_NO_RESULT_ANSWER',
				'HELP_FAQ_SEARCH_BLANK_QUESTION' => 'HELP_FAQ_SEARCH_BLANK_ANSWER',
				'HELP_FAQ_SEARCH_MEMBERS_QUESTION' => 'HELP_FAQ_SEARCH_MEMBERS_ANSWER',
				'HELP_FAQ_SEARCH_OWN_QUESTION' => 'HELP_FAQ_SEARCH_OWN_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_BOOKMARKS',
			false,
			array(
				'HELP_FAQ_BOOKMARKS_DIFFERENCE_QUESTION' => 'HELP_FAQ_BOOKMARKS_DIFFERENCE_ANSWER',
				'HELP_FAQ_BOOKMARKS_TOPIC_QUESTION' => 'HELP_FAQ_BOOKMARKS_TOPIC_ANSWER',
				'HELP_FAQ_BOOKMARKS_FORUM_QUESTION' => 'HELP_FAQ_BOOKMARKS_FORUM_ANSWER',
				'HELP_FAQ_BOOKMARKS_REMOVE_QUESTION' => 'HELP_FAQ_BOOKMARKS_REMOVE_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_WEBPUSH',
			false,
			array(
				'HELP_FAQ_WEBPUSH_WHAT_QUESTION'    => 'HELP_FAQ_WEBPUSH_WHAT_ANSWER',
				'HELP_FAQ_WEBPUSH_HOW_QUESTION'     => 'HELP_FAQ_WEBPUSH_HOW_ANSWER',
				'HELP_FAQ_WEBPUSH_SESSION_QUESTION' => 'HELP_FAQ_WEBPUSH_SESSION_ANSWER',
				'HELP_FAQ_WEBPUSH_SUBBING_QUESTION' => 'HELP_FAQ_WEBPUSH_SUBBING_ANSWER',
				'HELP_FAQ_WEBPUSH_GENERAL_QUESTION' => 'HELP_FAQ_WEBPUSH_GENERAL_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_ATTACHMENTS',
			false,
			array(
				'HELP_FAQ_ATTACHMENTS_ALLOWED_QUESTION' => 'HELP_FAQ_ATTACHMENTS_ALLOWED_ANSWER',
				'HELP_FAQ_ATTACHMENTS_OWN_QUESTION' => 'HELP_FAQ_ATTACHMENTS_OWN_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_ISSUES',
			false,
			array(
				'HELP_FAQ_ISSUES_WHOIS_PHPBB_QUESTION' => 'HELP_FAQ_ISSUES_WHOIS_PHPBB_ANSWER',
				'HELP_FAQ_ISSUES_FEATURE_QUESTION' => 'HELP_FAQ_ISSUES_FEATURE_ANSWER',
				'HELP_FAQ_ISSUES_LEGAL_QUESTION' => 'HELP_FAQ_ISSUES_LEGAL_ANSWER',
				'HELP_FAQ_ISSUES_ADMIN_QUESTION' => 'HELP_FAQ_ISSUES_ADMIN_ANSWER',
			)
		);

		return $this->language->lang('FAQ_EXPLAIN');
	}
}
