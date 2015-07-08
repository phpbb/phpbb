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

require __DIR__ . '/sami-checkout.conf.php';

$config['versions'] = Sami\Version\GitVersionCollection::create(__DIR__ . '/../')
	/*
	This would be nice, but currently causes various problems that need
	debugging.
	->addFromTags('release-3.0.*')
	->add('3.0.x', '3.0-next (olympus)')
	->addFromTags('release-3.1.*')
	->add('3.1.x', '3.1-next (ascraeus)')
	->add('master')
	*/
	->add('3.0.x')
	->add('3.1.x')
	->add('master')
;

return new Sami\Sami($iterator, $config);
