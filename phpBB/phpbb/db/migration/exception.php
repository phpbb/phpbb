<?php
/**
*
* @package db
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration;

/**
* The migrator is responsible for applying new migrations in the correct order.
*
* @package db
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
