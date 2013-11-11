<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\mimetype;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package mimetype
*/

class guesser
{
	/**
	* @const Default priority for mimetype guessers
	*/
	const PRIORITY_DEFAULT = 0;

	/**
	* @var mimetype guessers
	*/
	protected $guessers;

	/**
	* Construct a mimetype guesser object
	*
	* @param array $mimetype_guessers Mimetype guesser service collection
	*/
	public function __construct($mimetype_guessers)
	{
		$this->register_guessers($mimetype_guessers);
	}

	/**
	* Register MimeTypeGuessers and sort them by priority
	*
	* @param array $mimetype_guessers Mimetype guesser service collection
	*
	* @throws \LogicException If incorrect or not mimetype guessers have
	*			been supplied to class
	*/
	protected function register_guessers($mimetype_guessers)
	{
		foreach ($mimetype_guessers as $guesser)
		{
			$is_supported = (method_exists($guesser, 'is_supported')) ? 'is_supported' : '';
			$is_supported = (method_exists($guesser, 'isSupported')) ? 'isSupported' : $is_supported;

			if (empty($is_supported))
			{
				throw new \LogicException('Incorrect mimetype guesser supplied.');
			}

			if ($guesser->$is_supported())
			{
				$this->guessers[] = $guesser;
			}
		}

		if (empty($this->guessers))
		{
			throw new \LogicException('No mimetype guesser supplied.');
		}

		// Sort guessers by priority
		usort($this->guessers, array($this, 'sort_priority'));
	}

	/**
	* Sort the priority of supplied guessers
	* This is a compare function for usort. A guesser with higher priority
	* should be used first and vice versa. usort() orders the array values
	* from low to high depending on what the comparison function returns
	* to it. Return value should be smaller than 0 if value a is smaller
	* than value b. This has been reversed in the comparision function in
	* order to sort the guessers from high to low.
	* Method has been set to public in order to allow proper testing.
	*
	* @param object $guesser_a Mimetype guesser a
	* @param object $guesser_b Mimetype guesser b
	*
	* @return int 	If both guessers have the same priority 0, bigger
	*		than 0 if first guesser has lower priority, and lower
	*		than 0 if first guesser has higher priority
	*/
	public function sort_priority($guesser_a, $guesser_b)
	{
		$priority_a = (int) (method_exists($guesser_a, 'get_priority')) ? $guesser_a->get_priority() : self::PRIORITY_DEFAULT;
		$priority_b = (int) (method_exists($guesser_b, 'get_priority')) ? $guesser_b->get_priority() : self::PRIORITY_DEFAULT;

		return $priority_b - $priority_a;
	}

	/**
	* Guess mimetype of supplied file
	*
	* @param string $file Path to file
	*
	* @return string Guess for mimetype of file
	*/
	public function guess($file, $file_name = '')
	{
		if (!is_file($file))
		{
			return false;
		}

		if (!is_readable($file))
		{
			return false;
		}

		foreach ($this->guessers as $guesser)
		{
			$mimetype = $guesser->guess($file, $file_name);

			// Try to guess something that is not the fallback application/octet-stream
			if ($mimetype !== null && $mimetype !== 'application/octet-stream')
			{
				return $mimetype;
			}
		}
		// Return any mimetype if we got a result or the fallback value
		return (!empty($mimetype)) ? $mimetype : 'application/octet-stream';
	}
}
