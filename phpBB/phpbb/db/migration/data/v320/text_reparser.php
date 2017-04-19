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

namespace phpbb\db\migration\data\v320;

use phpbb\textreparser\manager;
use phpbb\textreparser\reparser_interface;

class text_reparser extends \phpbb\db\migration\container_aware_migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\contact_admin_form',
			'\phpbb\db\migration\data\v320\allowed_schemes_links',
		);
	}

	public function effectively_installed()
	{
		return isset($this->config['reparse_lock']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('reparse_lock', 0, true)),
			array('config.add', array('text_reparser.pm_text_cron_interval', 10)),
			array('config.add', array('text_reparser.pm_text_last_cron', 0)),
			array('config.add', array('text_reparser.poll_option_cron_interval', 10)),
			array('config.add', array('text_reparser.poll_option_last_cron', 0)),
			array('config.add', array('text_reparser.poll_title_cron_interval', 10)),
			array('config.add', array('text_reparser.poll_title_last_cron', 0)),
			array('config.add', array('text_reparser.post_text_cron_interval', 10)),
			array('config.add', array('text_reparser.post_text_last_cron', 0)),
			array('config.add', array('text_reparser.user_signature_cron_interval', 10)),
			array('config.add', array('text_reparser.user_signature_last_cron', 0)),
			array('custom', array(array($this, 'reparse'))),
		);
	}

	public function reparse($resume_data)
	{
		/** @var manager $reparser_manager */
		$reparser_manager = $this->container->get('text_reparser.manager');

		/** @var reparser_interface[] $reparsers */
		$reparsers = $this->container->get('text_reparser_collection');

		// Initialize all reparsers
		foreach ($reparsers as $name => $reparser)
		{
			$reparser_manager->update_resume_data($name, 1, $reparser->get_max_id(), 100);
		}

		// Sometimes a cron job is too much
		$limit = 100;
		$fast_reparsers = array(
			'text_reparser.contact_admin_info',
			'text_reparser.forum_description',
			'text_reparser.forum_rules',
			'text_reparser.group_description',
		);

		if (!is_array($resume_data))
		{
			$resume_data = array(
				'reparser'	=> 0,
				'current'	=> $this->container->get($fast_reparsers[0])->get_max_id(),
			);
		}

		$fast_reparsers_size = count($fast_reparsers);
		$processed_records = 0;
		while ($processed_records < $limit && $resume_data['reparser'] < $fast_reparsers_size)
		{
			$reparser = $this->container->get($fast_reparsers[$resume_data['reparser']]);

			// New reparser
			if ($resume_data['current'] === 0)
			{
				$resume_data['current'] = $reparser->get_max_id();
			}

			$start = max(1, $resume_data['current'] + 1 - ($limit - $processed_records));
			$end = max(1, $resume_data['current']);
			$reparser->reparse_range($start, $end);

			$processed_records += $end - $start + 1;
			$resume_data['current'] = $start - 1;

			if ($start === 1)
			{
				// Prevent CLI command from running these reparsers again
				$reparser_manager->update_resume_data($fast_reparsers[$resume_data['reparser']], 1, 0, $limit);

				$resume_data['reparser']++;
			}
		}

		if ($resume_data['reparser'] === $fast_reparsers_size)
		{
			return true;
		}

		return $resume_data;
	}
}
