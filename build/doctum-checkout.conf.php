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

$iterator = Symfony\Component\Finder\Finder::create()
	->files()
	->name('*.php')
	->in(__DIR__ . '/../phpBB/')
	->notPath('#^cache/#')
	->notPath('#^develop/#')
	->notPath('#^ext/#')
	->notPath('#^vendor/#')
	->notPath('data');

return new Doctum\Doctum($iterator, [
	'title'                => 'phpBB API Documentation',
	'build_dir'            => __DIR__ . '/api/output/%version%',
	'cache_dir'            => __DIR__ . '/api/cache/%version%',
	'default_opened_level' => 2,
]);
