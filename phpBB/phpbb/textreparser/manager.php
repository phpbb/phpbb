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

namespace phpbb\textreparser;

class manager
{
	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\config\db_text
	 */
	protected $config_text;

	/**
	 * @var \phpbb\di\service_collection
	 */
	protected $reparsers;

	/**
	 * @var array
	 */
	protected $resume_data;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config			$config
	 * @param \phpbb\config\db_text			$config_text
	 * @param \phpbb\di\service_collection	$reparsers
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\config\db_text $config_text, \phpbb\di\service_collection $reparsers)
	{
		$this->config = $config;
		$this->config_text = $config_text;
		$this->reparsers = $reparsers;
	}

	/**
	 * Loads resume data from the database
	 *
	 * @param string	$name	Name of the reparser to which the resume data belongs
	 *
	 * @return array
	 */
	public function get_resume_data($name)
	{
		if ($this->resume_data === null)
		{
			$resume_data = $this->config_text->get('reparser_resume');
			$this->resume_data = !empty($resume_data) ? unserialize($resume_data) : array();
		}

		return isset($this->resume_data[$name]) ? $this->resume_data[$name] : array();
	}

	/**
	 * Updates the resume data in the database
	 *
	 * Resume data must contain the following elements:
	 *  - range-min:  lowest record ID
	 *  - range-max:  current record ID
	 *  - range-size: number of records to process at a time
	 *
	 * Resume data may contain the following elements:
	 *  - filter-callback:    a callback that accepts a record as argument and returns a boolean
	 *  - filter-text-like:   a SQL LIKE predicate applied on the text, if applicable, e.g. '<r%'
	 *  - filter-text-regexp: a PCRE regexp that matches against the text
	 *
	 * @see reparser_interface::reparse()
	 *
	 * @param string	$name		Name of the reparser to which the resume data belongs
	 * @param array		$data		Resume data
	 * @param bool		$update_db	True if the resume data should be written to the database, false if not. (default: true)
	 */
	public function update_resume_data(string $name, array $data, bool $update_db = true)
	{
		// Prevent overwriting the old, stored array
		if ($this->resume_data === null)
		{
			$this->get_resume_data('');
		}

		$this->resume_data[$name] = $data;

		if ($update_db)
		{
			$this->config_text->set('reparser_resume', serialize($this->resume_data));
		}
	}

	/**
	 * Sets the interval for a text_reparser cron task
	 *
	 * @param string	$name		Name of the reparser to schedule
	 * @param int		$interval	Interval in seconds, 0 to disable the cron task
	 */
	public function schedule($name, $interval)
	{
		if (isset($this->reparsers[$name]) && isset($this->config[$name . '_cron_interval']))
		{
			$this->config->set($name . '_cron_interval', $interval);
		}
	}

	/**
	 * Sets the interval for all text_reparser cron tasks
	 *
	 * @param int	$interval	Interval in seconds, 0 to disable the cron task
	 */
	public function schedule_all($interval)
	{
		// This way we don't construct every registered reparser
		$reparser_array = array_keys($this->reparsers->getArrayCopy());

		foreach ($reparser_array as $reparser)
		{
			$this->schedule($reparser, $interval);
		}
	}

	/**
	 * Finds a reparser by name.
	 *
	 * If there is no reparser with the specified name, null is returned.
	 *
	 * @param string $name Name of the reparser to look up.
	 * @return string|null A reparser service name, or null.
	 */
	public function find_reparser(string $name)
	{
		foreach ($this->reparsers as $service => $reparser)
		{
			if ($reparser->get_name() == $name)
			{
				return $service;
			}
		}
		return null;
	}
}
