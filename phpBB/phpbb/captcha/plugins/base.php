<?php

namespace phpbb\captcha\plugins;

use phpbb\captcha\plugins\plugin_interface;
use phpbb\db\driver\driver_interface;

abstract class base implements plugin_interface
{
	/** @var driver_interface */
	protected driver_interface $db;

	/** @var bool Resolved state of captcha */
	protected bool $solved = false;

	/** @var string Confirm code */
	protected string $confirm_code = '';

	/** @var string Confirm id hash */
	protected string $confirm_id = '';

	/**
	 * Constructor for abstract captcha base class
	 *
	 * @param driver_interface $db
	 */
	public function __construct(driver_interface $db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritDoc
	 */
	public function garbage_collect(int $confirm_type = 0): void
	{
		$sql = 'SELECT DISTINCT c.session_id
			FROM ' . CONFIRM_TABLE . ' c
			LEFT JOIN ' . SESSIONS_TABLE . ' s ON (c.session_id = s.session_id)
			WHERE s.session_id IS NULL' .
			((empty($type)) ? '' : ' AND c.confirm_type = ' . (int) $type);
		$result = $this->db->sql_query($sql);

		if ($row = $this->db->sql_fetchrow($result))
		{
			$sql_in = [];
			do
			{
				$sql_in[] = (string) $row['session_id'];
			}
			while ($row = $this->db->sql_fetchrow($result));

			if (count($sql_in))
			{
				$sql = 'DELETE FROM ' . CONFIRM_TABLE . '
					WHERE ' . $this->db->sql_in_set('session_id', $sql_in);
				$this->db->sql_query($sql);
			}
		}
		$this->db->sql_freeresult($result);
	}
}
