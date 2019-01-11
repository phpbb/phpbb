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

namespace phpbb\reader_tracking;

class reader_tracker
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\request\request_interface $request,
		\phpbb\user $user)
	{
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
	}

	/**
	 * Returns tracking information from tracking cookies.
	 *
	 * @return array Tracking information.
	 */
	public function get_tracked_topics()
	{
		$tracking_topics = [];

		if (!($this->config['load_db_lastread'] && $this->user->data['is_registered']) &&
			($this->config['load_anon_lastread'] || $this->user->data['is_registered']))
		{
			$tracking_info = $this->request->variable(
				$this->config['cookie_name'] . '_track',
				'',
				true,
				\phpbb\request\request_interface::COOKIE
			);

			if (empty($tracking_info))
			{
				return $tracking_topics;
			}

			$tracking_topics = tracking_unserialize($tracking_info);

			if (!$this->user->data['is_registered'])
			{
				$this->user->data['user_lastmark'] = (array_key_exists('l', $tracking_topics)) ?
					(int) (base_convert($tracking_topics['l'], 36, 10) + $this->config['board_startdate']) :
					0;
			}
		}

		return $tracking_topics;
	}

	/**
	 * Get last read time from topic data.
	 *
	 * @param array $forum_row		Array of tracking information persisted in the database.
	 * @param array $tracking_info	Array of tracking data either from the database or from a tracking cookie.
	 *
	 * @return int|null Last read time.
	 */
	public function get_last_read_time(array $forum_row, array $tracking_info)
	{
		$forum_id = (int) $forum_row['forum_id'];

		if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
		{
			return (!empty($forum_row['mark_time'])) ? $forum_row['mark_time'] : $this->user->data['user_lastmark'];
		}
		else if ($this->config['load_anon_lastread'] || $this->user->data['is_registered'])
		{
			if (!$this->user->data['is_registered'])
			{
				$this->user->data['user_lastmark'] = (array_key_exists('l', $tracking_info)) ?
					(int) (base_convert($tracking_info['l'], 36, 10) + $this->config['board_startdate']) :
					0;
			}

			return (array_key_exists($forum_id, $tracking_info['f'])) ?
				(int) (base_convert($tracking_info['f'][$forum_id], 36, 10) + $this->config['board_startdate']) :
				$this->user->data['user_lastmark'];
		}

		return null;
	}
}
