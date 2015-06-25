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

namespace phpbb\di;

/**
* Iterator which loads the services when they are requested
*/
class service_collection_iterator extends \ArrayIterator
{
	/**
	* @var \phpbb\di\service_collection
	*/
	protected $collection;

	/**
	* Construct an ArrayIterator for service_collection
	*
	* @param \phpbb\di\service_collection $collection The collection to iterate over
	* @param int $flags Flags to control the behaviour of the ArrayObject object.
	* @see ArrayObject::setFlags()
	*/
	public function __construct(service_collection $collection, $flags = 0)
	{
		parent::__construct($collection, $flags);
		$this->collection = $collection;
	}

	/**
	* {@inheritdoc}
	*/
	public function current()
	{
		return $this->collection->offsetGet($this->key());
	}
}
