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
	protected static $format_cache = array();

	/**
	* Constructs a new instance of \phpbb\datetime, expanded to include an argument to inject
	* the user context and modify the timezone to the users selected timezone if one is not set.
	*
	* @param user $user object for context.
	* @param string $time String in a format accepted by strtotime().
	* @param \DateTimeZone|null $timezone Time zone of the time.
	*/
	public function __construct($user, $time = 'now', \DateTimeZone|null $timezone = null)
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

		// Use the format variants with translated am/pm literals if the language provides them
		if ($format['meridiem'] !== null)
		{
			$format = array_merge($format, $format['meridiem'][parent::format('a')]);
		}

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
	protected static function format_cache($format, $user)
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
				'meridiem'		=> null,
			);

			// Short representation of month in format? Some languages use different terms for the long and short format of May
			if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
			{
				self::$format_cache[$lang][$format]['lang']['May'] = $user->lang['datetime']['May_short'];
			}

			// Ante/post meridiem in format? Prepare format variants with the translated am/pm as escaped literals
			if (isset($user->lang['datetime']['MERIDIEM']) && is_array($user->lang['datetime']['MERIDIEM']))
			{
				$meridiem = array();
				foreach (array('am', 'pm') as $key)
				{
					$meridiem[$key] = array(
						'format_short'	=> self::localise_meridiem(self::$format_cache[$lang][$format]['format_short'], $key, $user->lang['datetime']['MERIDIEM']),
						'format_long'	=> self::localise_meridiem(self::$format_cache[$lang][$format]['format_long'], $key, $user->lang['datetime']['MERIDIEM']),
					);
				}

				// Only keep the variants if they differ from the original format, e.g. not for English
				if ($meridiem['am']['format_long'] !== self::$format_cache[$lang][$format]['format_long'] || $meridiem['pm']['format_long'] !== self::$format_cache[$lang][$format]['format_long'])
				{
					self::$format_cache[$lang][$format]['meridiem'] = $meridiem;
				}
			}
		}

		return self::$format_cache[$lang][$format];
	}

	/**
	* Replaces the unescaped am/pm format characters (a and A) with the escaped translation
	*
	* @param string $format Date format
	* @param string $meridiem Either 'am' or 'pm'
	* @param array $translations Translations of am, pm, AM and PM
	* @return string Date format with translated am/pm literals
	*/
	protected static function localise_meridiem($format, $meridiem, array $translations)
	{
		$localised = '';
		$length = strlen($format);

		for ($i = 0; $i < $length; $i++)
		{
			$char = $format[$i];

			if ($char === '\\')
			{
				// Escaped character, keep it together with its backslash
				$localised .= substr($format, $i, 2);
				$i++;
				continue;
			}

			$key = ($char === 'A') ? strtoupper($meridiem) : $meridiem;
			if (($char === 'a' || $char === 'A') && isset($translations[$key]) && $translations[$key] !== $key)
			{
				// Escape all ASCII characters so they are not interpreted as format characters
				$localised .= preg_replace('/[\x00-\x7F]/', '\\\\$0', $translations[$key]);
				continue;
			}

			$localised .= $char;
		}

		return $localised;
	}
}
