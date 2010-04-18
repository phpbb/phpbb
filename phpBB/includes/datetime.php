<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* phpBB custom extensions to the PHP DateTime class
* This handles the relative formats phpBB employs
*/
class phpbb_datetime extends DateTime
{
	/**
	* String used to wrap the date segment which should be replaced by today/tomorrow/yesterday
	*/
	const RELATIVE_WRAPPER = '|';

	/**
	* @var user User who is the context for this DateTime instance
	*/
	protected $_user;

	/**
	* @var array Date formats are preprocessed by phpBB, to save constact recalculation they are cached.
	*/
	static protected $format_cache = array();

	/**
	* Constructs a new instance of phpbb_datetime, expanded to include an argument to inject
	* the user context and modify the timezone to the users selected timezone if one is not set.
	*
	* @param string $time String in a format accepted by strtotime().
	* @param DateTimeZone $timezone Time zone of the time.
	* @param user User object for context.
	*/
	public function __construct($time = 'now', DateTimeZone $timezone = null, $user = null)
	{
		$this->_user	= $user ? $user : $GLOBALS['user'];

		$timezone		= (!$timezone && $this->_user->tz instanceof DateTimeZone) ? $this->_user->tz : $timezone;

		parent::__construct($time, $timezone);
	}

	/**
	* Returns a UNIX timestamp representation of the date time.
	*
	* @return int UNIX timestamp
	*/
	public function getTimestamp()
	{
		static $compat;

		if (!isset($compat))
		{
			$compat = !method_exists('DateTime', 'getTimestamp');
		}

		return !$compat ? parent::getTimestamp() : (int) parent::format('U');
	}

	/**
	* Formats the current date time into the specified format
	*
	* @param string $format Optional format to use for output, defaults to users chosen format
	* @param boolean $force_absolute Force output of a non relative date
	* @return string Formatted date time
	*/
	public function format($format = '', $force_absolute = false)
	{
		$format		= $format ? $format : $this->_user->date_format;
		$relative	= (strpos($format, self::RELATIVE_WRAPPER) !== false && !$force_absolute);
		$now		= new self('now', $this->_user->tz, $this->_user);
		$delta		= $now->getTimestamp() - $this->getTimestamp();

		if ($relative)
		{
			if ($delta <= 3600 && ($delta >= -5 || (($now->getTimestamp() / 60) % 60) == (($this->getTimestamp() / 60) % 60)) && isset($this->_user->lang['datetime']['AGO']))
			{
				return $this->_user->lang(array('datetime', 'AGO'), max(0, (int) floor($delta / 60)));
			}
			else
			{
				$midnight = clone $now;
				$midnight->setTime(0, 0, 0);

				$midnight = $midnight->getTimestamp();
				$gmepoch = $this->getTimestamp();

				if (!($gmepoch < $midnight - 86400 || $gmepoch > $midnight + 172800))
				{
					$day = false;

					if ($gmepoch > $midnight + 86400)
					{
						$day = 'TOMORROW';
					}
					else if ($gmepoch > $midnight)
					{
						$day = 'TODAY';
					}
					else if ($gmepoch > $midnight - 86400)
					{
						$day = 'YESTERDAY';
					}

					if ($day !== false)
					{
						$format = self::_format_cache($format, $this->_user);

						return str_replace(self::RELATIVE_WRAPPER . self::RELATIVE_WRAPPER, $this->_user->lang['datetime'][$day], strtr(parent::format($format['format_short']), $format['lang']));
					}
				}
			}
		}

		$format = self::_format_cache($format, $this->_user);

		return strtr(parent::format($format['format_long']), $format['lang']);
	}

	/**
	* Pre-processes the specified date format
	*
	* @param string $format Output format
	* @param user $user User object to use for localisation
	* @return array Processed date format
	*/
	static protected function _format_cache($format, $user)
	{
		$lang = $user->lang_name;

		if (!isset(self::$format_cache[$lang]))
		{
			self::$format_cache[$lang] = array();

			if (!isset(self::$format_cache[$lang][$format]))
			{
				// Is the user requesting a friendly date format (i.e. 'Today 12:42')?
				self::$format_cache[$lang][$format] = array(
					'is_short'		=> strpos($format, self::RELATIVE_WRAPPER) !== false,
					'format_short'	=> substr($format, 0, strpos($format, self::RELATIVE_WRAPPER)) . self::RELATIVE_WRAPPER . self::RELATIVE_WRAPPER . substr(strrchr($format, self::RELATIVE_WRAPPER), 1),
					'format_long'	=> str_replace(self::RELATIVE_WRAPPER, '', $format),
					'lang'			=> $user->lang['datetime'],
				);

				// Short representation of month in format? Some languages use different terms for the long and short format of May
				if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
				{
					self::$format_cache[$lang][$format]['lang']['May'] = $user->lang['datetime']['May_short'];
				}
			}
		}

		return self::$format_cache[$lang][$format];
	}
}
