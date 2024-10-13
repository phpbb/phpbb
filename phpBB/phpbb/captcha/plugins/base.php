<?php

namespace phpbb\captcha\plugins;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use phpbb\user;

abstract class base implements plugin_interface
{
	/** @var config */
	protected config $config;

	/** @var driver_interface */
	protected driver_interface $db;

	/** @var request_interface */
	protected request_interface $request;

	/** @var user */
	protected user $user;

	/** @var int Attempts at solving the CAPTCHA */
	protected int $attempts = 0;

	/** @var bool Resolved state of captcha */
	protected bool $solved = false;

	/** @var string Confirm id hash */
	protected string $confirm_id = '';

	/** @var confirm_type Confirmation type */
	protected confirm_type $type = confirm_type::UNDEFINED;

	/** @var string Last error message */
	protected string $last_error = '';

	/**
	 * Constructor for abstract captcha base class
	 *
	 * @param config $config
	 * @param driver_interface $db
	 * @param request_interface $request
	 * @param user $user
	 */
	public function __construct(config $config, driver_interface $db, request_interface $request, user $user)
	{
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
	}

	/**
	 * {@inheritDoc}
	 */
	public function init(confirm_type $type): void
	{
		$this->confirm_id = $this->request->variable('confirm_id', '');
		$this->type = $type;

		if (empty($this->confirm_id) || !$this->load_confirm_data())
		{
			// we have no confirm ID, better get ready to display something
			$this->generate_confirm_data();
		}
	}

	/**
	 * Look up attempts from confirm table
	 */
	protected function load_confirm_data(): bool
	{
		$sql = 'SELECT attempts
			FROM ' . CONFIRM_TABLE . "
			WHERE confirm_id = '" . $this->db->sql_escape($this->confirm_id) . "'
				AND session_id = '" . $this->db->sql_escape($this->user->session_id) . "'
				AND confirm_type = " . (int) $this->type;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			$this->attempts = $row['attempts'];

			return true;
		}

		return false;
	}

	/**
	 * Generate confirm data for tracking attempts
	 *
	 * @return void
	 */
	protected function generate_confirm_data(): void
	{
		$this->confirm_id = md5(unique_id());

		$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
					'confirm_id'	=> $this->confirm_id,
					'session_id'	=> (string) $this->user->session_id,
					'confirm_type'	=> $this->type
			));
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_hidden_fields(): array
	{
		return ['confirm_id' => $this->confirm_id];
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_solved(): bool
	{
		return $this->solved;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_error(): string
	{
		return $this->last_error;
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
