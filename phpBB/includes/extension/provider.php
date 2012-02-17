<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Provides a set of items found in extensions
*
* @package extension
*/
abstract class phpbb_extension_provider implements IteratorAggregate
{
	/**
	* Array holding all found items
	* @var array|null
	*/
	protected $items = null;

	/**
	* An extension manager to search for items in extensions
	* @var phpbb_extension_manager
	*/
	protected $extension_manager;

	/**
	* Constructor. Loads all available items.
	*
	* @param phpbb_extension_manager $extension_manager phpBB extension manager
	*/
	public function __construct(phpbb_extension_manager $extension_manager)
	{
		$this->extension_manager = $extension_manager;
	}

	/**
	* Finds template paths using the extension manager.
	*
	* @return array     List of task names
	*/
	abstract protected function find();

	/**
	* Retrieve an iterator over all items
	*
	* @return ArrayIterator An iterator for the array of template paths
	*/
	public function getIterator()
	{
		if ($this->items === null)
		{
			$this->items = $this->find();
		}

		return new ArrayIterator($this->items);
	}
}
