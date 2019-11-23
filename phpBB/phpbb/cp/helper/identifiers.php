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

namespace phpbb\cp\helper;

class identifiers
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var array phpBB tables */
	protected $tables;

	/** @var int The forum identifier */
	protected $forum_id = 0;

	/** @var int The topic identifier */
	protected $topic_id = 0;

	/** @var int The post identifier */
	protected $post_id = 0;

	/** @var string The control panel (acp|mcp|ucp) */
	protected $panel = '';

	/**
	 * Constructor.
	 *
	 * @param \phpbb\db\driver\driver_interface	$db			Database object
	 * @param \phpbb\request\request			$request	Request object
	 * @param array								$tables		phpBB tables
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, $tables)
	{
		$this->db		= $db;
		$this->request	= $request;
		$this->tables	= $tables;
	}

	/**
	 * Get the forum identifier.
	 *
	 * @return int
	 */
	public function get_forum_id()
	{
		return (int) $this->forum_id;
	}

	/**
	 * Get the topic identifier.
	 *
	 * @return int
	 */
	public function get_topic_id()
	{
		return (int) $this->topic_id;
	}

	/**
	 * Get the post identifier.
	 *
	 * @return int
	 */
	public function get_post_id()
	{
		return (int) $this->post_id;
	}

	/**
	 * Get the identifiers needed for MCP authorisation and menu item links.
	 *
	 * @param string	$panel		The control panel (acp|mcp|ucp)
	 * @return void
	 */
	public function get_identifiers($panel)
	{
		if ($panel === 'mcp')
		{
			if ($panel !== $this->panel)
			{
				$this->forum_id = $this->request->variable('f', 0);
				$this->topic_id = $this->request->variable('t', 0);
				$this->post_id = $this->request->variable('p', 0);

				if (!empty($this->topic_id) || !empty($this->post_id))
				{
					if ($this->post_id)
					{
						$sql = 'SELECT forum_id, topic_id
							FROM ' . $this->tables['posts'] . '
							WHERE post_id = ' . (int) $this->post_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$this->forum_id = $row !== false ? (int) $row['forum_id'] : 0;
						$this->topic_id = $row !== false ? (int) $row['topic_id'] : 0;
						$this->post_id = $row !== false ? (int) $this->post_id : 0;
					}
					else if ($this->topic_id)
					{
						$sql = 'SELECT forum_id
							FROM ' . $this->tables['topics'] . '
							WHERE topic_id = ' . (int) $this->topic_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$this->forum_id = $row !== false ? (int) $row['forum_id'] : 0;
						$this->topic_id = $row !== false ? (int) $this->topic_id : 0;
					}
				}
			}
		}
		else
		{
			$this->forum_id = 0;
			$this->topic_id = 0;
			$this->post_id = 0;
		}

		$this->panel = $panel;
	}

	/**
	 * Get identifier parameters for the MCP.
	 *
	 * @param string	$panel		The control panel (acp|mcp|ucp)
	 * @return array
	 */
	public function get_params($panel)
	{
		if ($panel === 'mcp')
		{
			return (array) array_filter([
				'f' => (int) $this->forum_id,
				't' => (int) $this->topic_id,
				'p' => (int) $this->post_id,
			]);
		}
		else
		{
			return [];
		}
	}
}
