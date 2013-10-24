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
	* Register MimeTypeGuessers
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
