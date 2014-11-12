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
	->add('develop-olympus', '3.0-next (olympus)')
	->addFromTags('release-3.1.*')
	->add('develop-ascraeus', '3.1-next (ascraeus)')
	->add('develop')
	*/
	->add('develop-olympus')
	->add('develop-ascraeus')
;

return new Sami\Sami($iterator, $config);
