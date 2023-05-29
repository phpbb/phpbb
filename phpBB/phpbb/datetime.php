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

namespace phpbb;

/**
* phpBB custom extensions to the PHP DateTime class
* This handles the relative formats phpBB employs
*/
class datetime extends \DateTime
{
	/**
	* String used to wrap the date segment which should be replaced by today/tomorrow/yesterday
	*/
	const RELATIVE_WRAPPER = '|';

	/**
	* @var user User who is the context for this DateTime instance
	*/
	protected $user;

	/**
	* @var array Date formats are preprocessed by phpBB, to save constant recalculation they are cached.
	*/
	static protected $format_cache = array();

	/**
	* Constructs a new instance of \phpbb\datetime, expanded to include an argument to inject
	* the user context and modify the timezone to the users selected timezone if one is not set.
	*
	* @param user $user object for context.
	* @param string $time String in a format accepted by strtotime().
	* @param \DateTimeZone $timezone Time zone of the time.
	*/
	public function __construct($user, $time = 'now', \DateTimeZone $timezone = null)
	{
		$this->user	= $user;
		$timezone	= $timezone ?: $this->user->timezone;

		parent::__construct($time, $timezone);
	}

	/**
	* Formats the current date time into the specified format
	*
	* @param string $format Optional format to use for output, defaults to users chosen format
	* @param boolean $force_absolute Force output of a non relative date
	* @return string Formatted date time
	*/
	#[\ReturnTypeWillChange]
	public function format($format = '', $force_absolute = false)
	{
		$format		= $format ? $format : $this->user->date_format;

		if (substr($this->user->lang_name, 0,2) != 'en')
		{
			$format = preg_replace('/([^\\\])S/','$1', $format);
		}

		$format		= self::format_cache($format, $this->user);
		$relative	= ($format['is_short'] && !$force_absolute);
		$now		= new self($this->user, 'now', $this->user->timezone);

		$timestamp	= $this->getTimestamp();
		$now_ts		= $now->getTimeStamp();

		$delta		= $now_ts - $timestamp;

		if ($relative)
		{
			/*
			* Check the delta is less than or equal to 1 hour
			* and the delta not more than a minute in the past
			* and the delta is either greater than -5 seconds or timestamp
			* and current time are of the same minute (they must be in the same hour already)
			* finally check that relative dates are supported by the language pack
			*/
			if ($delta <= 3600 && $delta > -60 &&
				($delta >= -5 || (($now_ts / 60) % 60) == (($timestamp / 60) % 60))
				&& isset($this->user->lang['datetime']['AGO']))
			{
				return $this->user->lang(array('datetime', 'AGO'), max(0, (int) floor($delta / 60)));
			}
			else
			{
				$midnight = clone $now;
				$midnight->setTime(0, 0, 0);

				$midnight	= $midnight->getTimestamp();

				if ($timestamp < $midnight + 2 * 86400)
				{
					$day = false;

					if ($timestamp >= $midnight + 86400)
					{
						$day = 'TOMORROW';
					}
					else if ($timestamp >= $midnight)
					{
						$day = 'TODAY';
					}
					else if ($timestamp >= $midnight - 86400)
					{
						$day = 'YESTERDAY';
					}

					if ($day !== false)
					{
						// Format using the short formatting and finally swap out the relative token placeholder with the correct value
						return str_replace(self::RELATIVE_WRAPPER . self::RELATIVE_WRAPPER, $this->user->lang['datetime'][$day], strtr(parent::format($format['format_short']), $format['lang']));
					}
				}
			}
		}

		return strtr(parent::format($format['format_long']), $format['lang']);
	}

	/**
	* Magic method to convert DateTime object to string
	*
	* @return string Formatted date time, according to the users default settings.
	*/
	public function __toString()
	{
		return $this->format();
	}

	/**
	* Pre-processes the specified date format
	*
	* @param string $format Output format
	* @param user $user User object to use for localisation
	* @return array Processed date format
	*/
	static protected function format_cache($format, $user)
	{
		$lang = $user->lang_name;

		if (!isset(self::$format_cache[$lang]))
		{
			self::$format_cache[$lang] = array();
		}

		if (!isset(self::$format_cache[$lang][$format]))
		{
			// Is the user requesting a friendly date format (i.e. 'Today 12:42')?
			self::$format_cache[$lang][$format] = array(
				'is_short'		=> strpos($format, self::RELATIVE_WRAPPER) !== false,
				'format_short'	=> substr($format, 0, strpos($format, self::RELATIVE_WRAPPER)) . self::RELATIVE_WRAPPER . self::RELATIVE_WRAPPER . substr(strrchr($format, self::RELATIVE_WRAPPER), 1),
				'format_long'	=> str_replace(self::RELATIVE_WRAPPER, '', $format),
				'lang'			=> array_filter($user->lang['datetime'], 'is_string'),
			);

			// Short representation of month in format? Some languages use different terms for the long and short format of May
			if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
			{
				self::$format_cache[$lang][$format]['lang']['May'] = $user->lang['datetime']['May_short'];
			}
		}

		return self::$format_cache[$lang][$format];
	}
}
