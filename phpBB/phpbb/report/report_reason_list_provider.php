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

class report_reason_list_provider
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface	$db
	 * @param \phpbb\template\template			$template
	 * @param \phpbb\user						$user
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->db		= $db;
		$this->template	= $template;
		$this->user		= $user;
	}

	/**
	 * Sets template variables to render report reasons select HTML input
	 *
	 * @param int	$reason_id
	 * @return null
	 */
	public function display_reasons($reason_id = 0)
	{
		$sql = 'SELECT *
			FROM ' . REPORTS_REASONS_TABLE . '
			ORDER BY reason_order ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
			if (isset($this->user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])]) && isset($this->user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])]))
			{
				$row['reason_description'] = $this->user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])];
				$row['reason_title'] = $this->user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])];
			}

			$this->template->assign_block_vars('reason', array(
				'ID'			=> $row['reason_id'],
				'TITLE'			=> $row['reason_title'],
				'DESCRIPTION'	=> $row['reason_description'],
				'S_SELECTED'	=> ($row['reason_id'] == $reason_id) ? true : false,
			));
		}
		$this->db->sql_freeresult($result);
	}
}
