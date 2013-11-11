<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace phpbb\console\command\extension;

abstract class command extends \phpbb\console\command\command
{
	/** @var \phpbb\extension\manager */
	protected $manager;

	function __construct(\phpbb\extension\manager $manager)
	{
		$this->manager = $manager;

		parent::__construct();
	}
}
