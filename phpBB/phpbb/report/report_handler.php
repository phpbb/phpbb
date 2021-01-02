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

namespace phpbb\report;

abstract class report_handler implements report_handler_interface
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var \phpbb\notification\manager
	 */
	protected $notifications;

	/**
	 * @var array
	 */
	protected $report_data;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface	$db
	 * @param \phpbb\event\dispatcher_interface	$dispatcher
	 * @param \phpbb\config\config				$config
	 * @param \phpbb\auth\auth					$auth
	 * @param \phpbb\user						$user
	 * @param \phpbb\notification\manager		$notification
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\event\dispatcher_interface $dispatcher, \phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\user $user, \phpbb\notification\manager $notification)
	{
		$this->db				= $db;
		$this->dispatcher		= $dispatcher;
		$this->config			= $config;
		$this->auth				= $auth;
		$this->user				= $user;
		$this->notifications	= $notification;
		$this->report_data		= array();
	}

	/**
	 * Creates a report entity in the database
	 *
	 * @param	array	$report_data
	 * @return	int	the ID of the created entity
	 */
	protected function create_report(array $report_data)
	{
		$sql_ary = array(
			'reason_id'							=> (int) $report_data['reason_id'],
			'post_id'							=> $report_data['post_id'],
			'pm_id'								=> $report_data['pm_id'],
			'user_id'							=> (int) $this->user->data['user_id'],
			'user_notify'						=> (int) $report_data['user_notify'],
			'report_closed'						=> 0,
			'report_time'						=> (int) time(),
			'report_text'						=> (string) $report_data['report_text'],
			'reported_post_text'				=> $report_data['reported_post_text'],
			'reported_post_uid'					=> $report_data['reported_post_uid'],
			'reported_post_bitfield'			=> $report_data['reported_post_bitfield'],
			'reported_post_enable_bbcode'		=> $report_data['reported_post_enable_bbcode'],
			'reported_post_enable_smilies'		=> $report_data['reported_post_enable_smilies'],
			'reported_post_enable_magic_url'	=> $report_data['reported_post_enable_magic_url'],
		);

		$sql = 'INSERT INTO ' . REPORTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		return $this->db->sql_nextid();
	}
}
