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

abstract class guesser_base implements guesser_interface
{
	/**
	* @var int Guesser Priority
	*/
	protected $priority;

	/**
	* @inheritdoc
	*/
	public function get_priority()
	{
		return $this->priority;
	}

	/**
	* @inheritdoc
	*/
	public function set_priority($priority)
	{
		$this->priority = $priority;
	}
}
