<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace phpbb\console\command\config;

abstract class command extends \phpbb\console\command\command
{
	/** @var \phpbb\config\config */
	protected $config;

	function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;

		parent::__construct();
	}
}
