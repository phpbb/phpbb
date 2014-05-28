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

namespace phpbb\profiler;

use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;
use Psr\Log\LoggerInterface;

class profiler extends \Symfony\Component\HttpKernel\Profiler\Profiler
{
	/**
	* Constructor.
	*
	* @param array                    $data_collectors The list of the daa collectors
	* @param ProfilerStorageInterface $storage A ProfilerStorageInterface instance
	* @param LoggerInterface          $logger  A LoggerInterface instance
	*/
	public function __construct($data_collectors, ProfilerStorageInterface $storage, LoggerInterface $logger = null)
	{
		parent::__construct($storage, $logger);

		foreach ($data_collectors as $data_collector)
		{
			$this->add($data_collector);
		}
	}
}

