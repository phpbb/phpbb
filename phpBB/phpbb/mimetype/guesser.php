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

namespace phpbb\mimetype;

class guesser
{
	/**
	* @const Default priority for mimetype guessers
	*/
	const PRIORITY_DEFAULT = 0;

	/**
	* @var array guessers
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
	* @param string $file_name The real file name
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

		$mimetype = 'application/octet-stream';

		foreach ($this->guessers as $guesser)
		{
			$mimetype_guess = $guesser->guess($file, $file_name);

			$mimetype = $this->choose_mime_type($mimetype, $mimetype_guess);
		}
		// Return any mimetype if we got a result or the fallback value
		return $mimetype;
	}

	/**
	 * Choose the best mime type based on the current mime type and the guess
	 * If a guesser returns nulls or application/octet-stream, we will keep
	 * the current guess. Guesses with a slash inside them will be favored over
	 * already existing ones. However, any guess that will pass the first check
	 * will always overwrite the default application/octet-stream.
	 *
	 * @param	string	$mime_type	The current mime type
	 * @param	string	$guess		The current mime type guess
	 *
	 * @return string The best mime type based on current mime type and guess
	 */
	public function choose_mime_type($mime_type, $guess)
	{
		if ($guess === null || $guess == 'application/octet-stream')
		{
			return $mime_type;
		}

		if ($mime_type == 'application/octet-stream' || strpos($guess, '/') !== false)
		{
			$mime_type = $guess;
		}

		return $mime_type;
	}
}
