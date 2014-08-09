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

require __DIR__ . '/../build/' . basename(__FILE__);

// Removing the versions array key will make Sami use the current branch.
unset($config['versions']);

return new Sami\Sami($iterator, $config);
