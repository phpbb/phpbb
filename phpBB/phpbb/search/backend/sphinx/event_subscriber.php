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

namespace phpbb\search\backend\sphinx;

use phpbb\config\config;
use phpbb\event\data;
use phpbb\search\backend\fulltext_sphinx;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Keeps the Sphinx Real-Time index synchronized on moderation and user lifecycle events.
 */
class event_subscriber implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var fulltext_sphinx */
	protected $sphinx_backend;

	/**
	 * Constructor
	 *
	 * @param config			$config
	 * @param fulltext_sphinx	$sphinx_backend
	 */
	public function __construct(config $config, fulltext_sphinx $sphinx_backend)
	{
		$this->config = $config;
		$this->sphinx_backend = $sphinx_backend;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'core.set_post_visibility_after'	=> 'on_set_post_visibility_after',
			'core.move_topics_after'			=> 'on_topics_changed',
			'core.move_posts_sync_after'		=> 'on_topics_changed',
			'core.mcp_change_poster_after'		=> 'on_mcp_change_poster_after',
			'core.delete_user_after'			=> 'on_delete_user_after',
		];
	}

	/**
	 * Checks whether Sphinx Real-Time index synchronization is currently active.
	 *
	 * @return bool
	 */
	protected function is_active(): bool
	{
		return $this->config['search_type'] === fulltext_sphinx::class
			&& (bool) $this->config['load_search'];
	}

	/**
	 * Updates post visibility and topic metadata in the RT index after post/topic visibility changes.
	 *
	 * @param data $event
	 * @return void
	 */
	public function on_set_post_visibility_after(data $event): void
	{
		if (!$this->is_active())
		{
			return;
		}

		$topic_id = (int) ($event['topic_id'] ?? 0);
		if ($topic_id > 0)
		{
			$this->sphinx_backend->sync_topics_by_ids([$topic_id]);
		}
	}

	/**
	 * Updates forum_id, topic_id, topic_first_post, and topic_last_post_time (or adds/removes posts
	 * based on destination forum indexing settings) when topics move or posts are moved/split/merged.
	 *
	 * @param data $event
	 * @return void
	 */
	public function on_topics_changed(data $event): void
	{
		if (!$this->is_active() || empty($event['topic_ids']) || !is_array($event['topic_ids']))
		{
			return;
		}

		$this->sphinx_backend->sync_topics_by_ids(array_map('intval', $event['topic_ids']));
	}

	/**
	 * Updates poster_id in the RT index after a moderator changes a post's author.
	 *
	 * @param data $event
	 * @return void
	 */
	public function on_mcp_change_poster_after(data $event): void
	{
		if (!$this->is_active())
		{
			return;
		}

		$post_id = (int) ($event['post_info']['post_id'] ?? 0);
		if ($post_id > 0)
		{
			$this->sphinx_backend->sync_posts_by_ids([$post_id]);
		}
	}

	/**
	 * Updates poster_id to ANONYMOUS in the RT index when a user is deleted while retaining posts.
	 *
	 * @param data $event
	 * @return void
	 */
	public function on_delete_user_after(data $event): void
	{
		if (!$this->is_active() || ($event['mode'] ?? '') !== 'retain')
		{
			return;
		}

		if (!empty($event['user_ids']) && is_array($event['user_ids']))
		{
			$this->sphinx_backend->update_poster_id_for_users(
				array_map('intval', $event['user_ids']),
				(int) ANONYMOUS
			);
		}
	}
}
