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
* Provides a set of items found in extensions.
*
* This abstract class is essentially a wrapper around item-specific
* finding logic. It handles storing the extension manager via constructor
* for the finding logic to use to find the items, and provides an
* iterator interface over the items found by the finding logic.
*
* Items could be anything, for example template paths or cron task names.
* Derived classes completely define what the items are.
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
	* Finds items using the extension manager.
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
