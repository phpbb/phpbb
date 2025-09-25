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

namespace phpbb\db\migration\data\v400;

class v400a1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '4.0.0-a1', '>=');
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\v3315',
			'\phpbb\db\migration\data\v400\add_mention_settings',
			'\phpbb\db\migration\data\v400\remove_remote_avatar',
			'\phpbb\db\migration\data\v400\increase_avatar_size',
			'\phpbb\db\migration\data\v400\remove_img_link',
			'\phpbb\db\migration\data\v400\rename_duplicated_index_names',
			'\phpbb\db\migration\data\v400\add_bbcode_font_icon',
			'\phpbb\db\migration\data\v400\font_awesome_6_upgrade',
			'\phpbb\db\migration\data\v400\remove_broken_captcha',
			'\phpbb\db\migration\data\v400\turnstile_captcha',
			'\phpbb\db\migration\data\v400\add_audio_files_attachment_group',
			'\phpbb\db\migration\data\v400\add_webpush_token',
			'\phpbb\db\migration\data\v400\remove_remote_upload',
			'\phpbb\db\migration\data\v400\remove_notify_type',
			'\phpbb\db\migration\data\v400\remove_template_php',
			'\phpbb\db\migration\data\v400\storage_adapter_local_subfolders_remove',
			'\phpbb\db\migration\data\v400\add_webpush_options',
			'\phpbb\db\migration\data\v400\remove_max_img_size',
			'\phpbb\db\migration\data\v400\hidpi_smilies',
			'\phpbb\db\migration\data\v400\extensions_composer_2',
			'\phpbb\db\migration\data\v400\add_storage_permission',
			'\phpbb\db\migration\data\v400\hidpi_icons',
			'\phpbb\db\migration\data\v400\qa_captcha',
			'\phpbb\db\migration\data\v400\search_backend_update',
			'\phpbb\db\migration\data\v400\remove_flash_v2',
			'\phpbb\db\migration\data\v400\add_disable_board_access_config',
			'\phpbb\db\migration\data\v400\remove_dbms_version_config',
			'\phpbb\db\migration\data\v400\ban_table_p2',
			'\phpbb\db\migration\data\v400\extensions_composer_3',
			'\phpbb\db\migration\data\v400\acp_storage_module',
			'\phpbb\db\migration\data\v400\add_video_files_attachment_group',
			'\phpbb\db\migration\data\v400\remove_attachment_download_mode',
			'\phpbb\db\migration\data\v400\remove_smtp_auth_method',
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['version', '4.0.0-a1']],
		];
	}
}
