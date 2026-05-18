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

namespace phpbb\db\migration\data\v33x;

use phpbb\db\migration\migration;

class ai_crawler_bots extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\bot_update_v2',
			'\phpbb\db\migration\data\v33x\guest_session_config',
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'add_bots']]],
		];
	}

	public function add_bots(): void
	{
		$bots = [
			// 'BotName' => 'Agent partial name',
			// OpenAI
			'OAI-SearchBot [Bot]'			=> 'OAI-SearchBot/',
			'OAI-AdsBot [Bot]'				=> 'OAI-AdsBot/',
			'GPTBot [Bot]'					=> 'GPTBot/',
			'ChatGPT-User [Bot]'			=> 'ChatGPT-User/',

			// Anthropic
			'ClaudeBot [Bot]'				=> 'ClaudeBot/',
			'Claude-User [Bot]'				=> 'Claude-User/',
			'Claude-SearchBot [Bot]'		=> 'Claude-SearchBot/',
			'Claude-Web [Bot]'				=> 'Claude-Web/',

			// Google Gemini
			'Google-Extended [Bot]'			=> 'Google-Extended',
			'Google-CloudVertexBot [Bot]'	=> 'Google-CloudVertexBot/',

			// Meta
			'FacebookBot [Bot]'				=> 'FacebookBot/',
			'FacebookExternalHit [Bot]'		=> 'facebookexternalhit/',
			'Meta-ExternalAgent [Bot]'		=> 'meta-externalagent/',
			'Meta-ExternalAds [Bot]'		=> 'meta-externalads/',
			'Meta-ExternalFetcher [Bot]'	=> 'meta-externalfetcher/',
			'Meta-WebIndexer [Bot]'			=> 'meta-webindexer/',

			// Perplexity AI
			'PerplexityBot [Bot]'			=> 'PerplexityBot/',
			'Perplexity-User [Bot]'			=> 'Perplexity-User',

			// ByteDance / TikTok
			'Bytespider [Bot]'				=> 'Bytespider',
			'TikTokSpider [Bot]'			=> 'TikTokSpider',

			// Apple Intelligence
			'Applebot [Bot]'				=> 'Applebot/',
			'Applebot-Extended [Bot]'	=> 'Applebot-Extended',

			// Cohere
			'Cohere [Bot]'					=> 'cohere-ai',
			'Cohere Training Crawler [Bot]'	=> 'cohere-training-data-crawler',

			// Diffbot
			'Diffbot [Bot]'				=> 'Diffbot',

			// Common Crawl (used by many AI training pipelines)
			'CCBot [Bot]'				=> 'CCBot/',
		];

		$group_row = [];

		foreach ($bots as $bot_name => $bot_agent)
		{
			$bot_name_clean = utf8_clean_string($bot_name);

			$sql = 'SELECT user_id
				FROM ' . $this->table_prefix . 'users
				WHERE ' . $this->db->sql_build_array('SELECT', ['username_clean' => $bot_name_clean]);
			$result = $this->db->sql_query($sql);
			$bot_exists = (bool) $this->db->sql_fetchfield('user_id');
			$this->db->sql_freeresult($result);

			if ($bot_exists)
			{
				continue;
			}

			if (!count($group_row))
			{
				$sql = 'SELECT group_id, group_colour
					FROM ' . $this->table_prefix . 'groups
					WHERE ' . $this->db->sql_build_array('SELECT', ['group_name' => 'BOTS']);
				$result = $this->db->sql_query($sql);
				$group_row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// Default fallback, should never get here
				if (!count($group_row))
				{
					$group_row['group_id'] = 6;
					$group_row['group_colour'] = '9E8DA7';
				}
			}

			if (!function_exists('user_add'))
			{
				include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$user_row = [
				'user_type'				=> USER_IGNORE,
				'group_id'				=> $group_row['group_id'],
				'username'				=> $bot_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> $group_row['group_colour'],
				'user_email'			=> '',
				'user_lang'				=> $this->config['default_lang'],
				'user_style'			=> $this->config['default_style'],
				'user_timezone'			=> 0,
				'user_dateformat'		=> $this->config['default_dateformat'],
				'user_allow_massemail'	=> 0,
			];

			$user_id = user_add($user_row);

			$sql = 'INSERT INTO ' . $this->table_prefix . 'bots ' . $this->db->sql_build_array('INSERT', [
				'bot_active'	=> 1,
				'bot_name'		=> $bot_name,
				'user_id'		=> (int) $user_id,
				'bot_agent'		=> $bot_agent,
				'bot_ip'		=> '',
			]);
			$this->db->sql_query($sql);
		}
	}
}
