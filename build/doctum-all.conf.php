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

require __DIR__ . '/doctum-checkout.conf.php';

$config['versions'] = Doctum\Version\GitVersionCollection::create(__DIR__ . '/../')
	->add('3.3.x')
	->add('master')
;

return new Doctum\Doctum($iterator, $config);
