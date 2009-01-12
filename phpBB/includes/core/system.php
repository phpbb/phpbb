<?php
/**
*
* @package core
* @version $Id: core.php 9200 2008-12-15 18:06:53Z acydburn $
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit();
}

/**
* System-specific methods. For example chmod(), unlink()...
*
* @package core
*/
class phpbb_system extends phpbb_plugin_support
{
	private $data = array();

	public $phpbb_required = array();
	public $phpbb_optional = array();

}

?>