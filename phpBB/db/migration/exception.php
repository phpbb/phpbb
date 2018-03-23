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

namespace phpbb\db\migration;

/**
* The migrator is responsible for applying new migrations in the correct order.
*/
class exception extends \Exception
{
	/**
	* Extra parameters sent to exception to aid in debugging
	* @var array
	*/
	protected $parameters;

	/**
	* Throw an exception.
	*
	* First argument is the error message.
	* Additional arguments will be output with the error message.
	*/
	public function __construct()
	{
		$parameters = func_get_args();
		$message = array_shift($parameters);
		parent::__construct($message);

		$this->parameters = $parameters;
	}

	/**
	* Output the error as a string
	*
	* @return string
	*/
	public function __toString()
	{
		return $this->message . ': ' . var_export($this->parameters, true);
	}

	/**
	* Get the parameters
	*
	* @return array
	*/
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	* Get localised message (with $user->lang())
	*
	* @param \phpbb\user $user
	* @return string
	*/
	public function getLocalisedMessage(\phpbb\user $user)
	{
		$parameters = $this->getParameters();
		array_unshift($parameters, $this->getMessage());

		return call_user_func_array(array($user, 'lang'), $parameters);
	}
}
