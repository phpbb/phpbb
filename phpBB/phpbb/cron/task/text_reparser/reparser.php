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

namespace phpbb\cron\task\text_reparser;

/**
 * Reparse text cron task
 */
class reparser extends \phpbb\cron\task\base
{
	const MIN = 1;
	const SIZE = 100;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\config\db_text
	 */
	protected $config_text;

	/**
	 * @var \phpbb\lock\db
	 */
	protected $reparse_lock;

	/**
	 * @var \phpbb\di\service_collection
	 */
	protected $reparsers;

	/**
	 * @var string
	 */
	protected $reparser_name;

	/**
	 * @var array
	 */
	protected $resume_data;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config			$config
	 * @param \phpbb\config\db_text			$config_text
	 * @param \phpbb\lock\db				$reparse_lock
	 * @param \phpbb\di\service_collection	$reparsers
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\config\db_text $config_text, \phpbb\lock\db $reparse_lock, \phpbb\di\service_collection $reparsers)
	{
		$this->config = $config;
		$this->config_text = $config_text;
		$this->reparse_lock = $reparse_lock;
		$this->reparsers = $reparsers;
	}

	/**
	 * Sets the reparser for this cron task
	 *
	 * @param string	$reparser
	 */
	public function set_reparser($reparser)
	{
		if (isset($this->reparsers[$reparser]))
		{
			$this->reparser_name = preg_replace('(^text_reparser\\.)', '', $reparser);
		}
		else if (isset($this->reparsers['text_reparser.' . $reparser]))
		{
			$this->reparser_name = $reparser;
		}

		if ($this->resume_data === null)
		{
			$this->load_resume_data();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_runnable()
	{
		if ($this->resume_data === null)
		{
			$this->load_resume_data();
		}

		if (empty($this->resume_data[$this->reparser_name]['range-max']) || $this->resume_data[$this->reparser_name]['range-max'] === $this->resume_data[$this->reparser_name]['range-min'])
		{
			return true;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function should_run()
	{
		if (!empty($this->config['reparse_lock']))
		{
			$last_run = explode(' ', $this->config['reparse_lock']);

			if ($last_run[0] + 3600 >= time())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		if ($this->reparse_lock->acquire())
		{
			if ($this->resume_data === null)
			{
				$this->load_resume_data();
			}

			/**
			 * @var \phpbb\textreparser\reparser_interface $reparser
			 */
			$reparser = isset($this->reparsers[$this->reparser_name]) ? $this->reparsers[$this->reparser_name] : $this->reparsers['text_reparser.' . $this->reparser_name];

			$min = !empty($this->resume_data[$this->reparser_name]['range-min']) ? $this->resume_data[$this->reparser_name]['range-min'] : self::MIN;
			$current = !empty($this->resume_data[$this->reparser_name]['range-max']) ? $this->resume_data[$this->reparser_name]['range-max'] : $reparser->get_max_id();
			$size = !empty($this->resume_data[$this->reparser_name]['range-size']) ? $this->resume_data[$this->reparser_name]['range-size'] : self::SIZE;

			if ($current >= $min)
			{
				$start = max($min, $current + 1 - $size);
				$end = max($min, $current);

				$reparser->reparse_range($start, $end);

				$this->update_resume_data($this->reparser_name, $min, $start - 1, $size);
			}

			$this->reparse_lock->release();
		}
	}

	/**
	 * Load the resume data from the database
	 */
	protected function load_resume_data()
	{
		$resume_data = $this->config_text->get('reparser_resume');
		$this->resume_data = (empty($resume_data)) ? array() : unserialize($resume_data);
	}

	/**
	 * Save the resume data to the database
	 */
	protected function save_resume_data()
	{
		$this->config_text->set('reparser_resume', serialize($this->resume_data));
	}

	/**
	 * Save the resume data to the database
	 *
	 * @param string	$name		Reparser name
	 * @param int		$min		Lowest record ID
	 * @param int		$current	Current ID
	 * @param int		$size		Number of records to process at a time
	 */
	protected function update_resume_data($name, $min, $current, $size)
	{
		$this->resume_data[$name] = array(
			'range-min'  => $min,
			'range-max'  => $current,
			'range-size' => $size,
		);
		$this->save_resume_data();
	}
}
